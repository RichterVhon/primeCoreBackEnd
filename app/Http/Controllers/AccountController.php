<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Enums\AccountRole;
use Illuminate\Http\Request;
use App\Models\AccountContact;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use App\Traits\HandlesAccountCreation;
use Illuminate\Validation\Rules\Password;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;

class AccountController extends Controller
{
    use HandlesAccountCreation;

    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->role !== AccountRole::Admin) {
            return response()->json(['message' => 'Access denied. Admins only'], 403);
        }

        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        $query = Account::query();

        if ($request->filled('search')) {
            $query->search($request->input('search'), Account::searchableFields());
        }

        $rawQuery = $request->query();
        $filterable = Account::filterableFields();

        $filters = [];

        //dump('Raw query keys:', array_keys($request->query()));


        foreach ($rawQuery as $key => $value) {
            //dump("🔍 Checking raw key: {$key}");

            if (in_array($key, $filterable)) {
                //dump("✅ Direct match found: {$key}");
                $filters[$key] = $value;
                continue;
            }

            // Try to match known relationships
            $matched = false;
            foreach ($filterable as $filterKey) {
                $normalized = str_replace('.', '_', $filterKey);
                //dump("🔄 Comparing {$key} with normalized filterable: {$normalized}");

                if ($normalized === $key) {
                    //dump("🎯 Matched normalized key: {$key} → {$filterKey}");
                    $filters[$filterKey] = $value;
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                //dump("❌ No match for key: {$key}");
            }
        }

        //dump('Incoming filters:', $filters);

        $query->applyFilters($filters);
        $query->orderByRaw("ISNULL($sortField), $sortField $sortDirection");

        $accounts = $query
            ->with([
                'contacts' => fn($q) => $q->withPivot('company_name'),
                'listings',
                'clientInquiries',
                'agentInquiries'
            ])
            ->paginate(10)
            ->appends($request->query());

        return response()->json([
            'data' => $accounts->getCollection()->map(function ($account) {
                $allInquiries = collect()
                    ->merge($account->clientInquiries)
                    ->merge($account->agentInquiries)
                    ->unique('id');

                return [
                    'id' => $account->id,
                    'name' => $account->name,
                    'email' => $account->email,
                    'role' => $account->role,
                    'status' => $account->status,
                    'created_at' => $account->created_at,
                    'updated_at' => $account->updated_at,

                    'contacts' => $account->contacts->map(function ($contact) {
                        return [
                            'id' => $contact->id,
                            'contact_person' => $contact->contact_person,
                            'email_address' => $contact->email_address,
                            'company_name' => optional($contact->pivot)->company_name,
                        ];
                    })->values(),

                    'listings' => $account->listings->map(fn($listing) => [
                        'id' => $listing->id,
                        'property_name' => $listing->property_name ?? null,
                        'date_uploaded' => $listing->date_uploaded,
                        'date_last_updated' => $listing->date_last_updated,
                        'category' => $listing->listable_type,
                        'status' => $listing->status,
                    ])->values(),

                    'inquiries' => $allInquiries->map(fn($inquiry) => [
                        'id' => $inquiry->id,
                        'status' => $inquiry->status,
                        'submitted_at' => $inquiry->created_at,
                    ])->values(),
                ];
            }),
            'meta' => [
                'current_page' => $accounts->currentPage(),
                'per_page' => $accounts->perPage(),
                'total' => $accounts->total(),
                'last_page' => $accounts->lastPage(),
                'next_page_url' => $accounts->nextPageUrl(),
                'prev_page_url' => $accounts->previousPageUrl()
            ]
        ]);
    }


    public function show($id): JsonResponse
    {
        $account = Account::findOrFail($id);
        $user = Auth::user();

        // block viewing if logged in account is not admin and account does not belong to thhe currently logged in user
        if (!($user->role === AccountRole::Admin || $user->id === $account->id)) {
            return response()->json([
                'message' => 'You are not authorized to view this account.'
            ], 403);
        }
        $account = Account::with([
            'contacts' => fn($q) => $q->withPivot('company_name'),
            'listings',
            'clientInquiries',
            'agentInquiries'
        ])->findOrFail($id);

        $allInquiries = collect()
            ->merge($account->clientInquiries)
            ->merge($account->agentInquiries)
            ->unique('id');

        return response()->json([
            'data' => [
                'id' => $account->id,
                'name' => $account->name,
                'email' => $account->email,
                'role' => $account->role,
                'status' => $account->status,
                'created_at' => $account->created_at,
                'updated_at' => $account->updated_at,

                'contacts' => $account->contacts->map(fn($contact) => [
                    'id' => $contact->id,
                    'contact_person' => $contact->contact_person,
                    'email_address' => $contact->email_address,
                    'company_name' => optional($contact->pivot)->company_name,
                ])->values(),

                'listings' => $account->listings->map(fn($listing) => [
                    'id' => $listing->id,
                    'property_name' => $listing->property_name ?? null,
                    'date_uploaded' => $listing->date_uploaded,
                    'date_last_updated' => $listing->date_last_updated,
                    'category' => $listing->listable_type,
                    'status' => $listing->status,
                ])->values(),

                'inquiries' => $allInquiries->map(fn($inquiry) => [
                    'id' => $inquiry->id,
                    'status' => $inquiry->status,
                    'submitted_at' => $inquiry->created_at,
                ])->values(),
            ]
        ]);
    }


    // public function store(StoreAccountRequest $request): JsonResponse
    // {
    // ...
    // } moved to RegistedAccountController

    public function update(Request $request, $id): JsonResponse
    {
        $account = Account::findOrFail($id);
        $user = Auth::user();

        // block updating if logged in account is not admin and account does not belong to thhe currently logged in user
        if (!($user->role === AccountRole::Admin || $user->id === $account->id)) {
            return response()->json([
                'message' => 'You are not authorized to update this account.'
            ], 403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('accounts')->ignore($account->id),
            ],
            //'password' => ['sometimes', Password::defaults()], password should not be editable here; rather, thru google api or something
            'role' => ['sometimes', new Enum(AccountRole::class)],
            'status' => 'sometimes|boolean',
            // 'accounts' => 'sometimes|array',
            // 'accounts.*.account_id' => 'sometimes|exists:accounts,id',
            // 'accounts.*.company_name' => 'sometimes|string|max:255',
            // 'contacts' => 'sometimes|array',
            // 'contacts.*.contact_id' => 'required|exists:contacts,id',
            // 'contacts.*.company_name' => 'nullable|string|max:255'
        ]);

        $data = $request->all();

        //only admins should have the power to change an account's role and status
        if ($user->role !== AccountRole::Admin) {
            unset($data['role'], $data['status']);
        }

        DB::transaction(function () use ($account, $data) {
            // 🔐 Update core fields
            $account->update([
                // 'name' => $validated['name'] ?? $account->name,
                // 'email' => $validated['email'] ?? $account->email,
                // //'password' => isset($validated['password']) ? bcrypt($validated['password']) : $account->password,
                // 'role' => $validated['role'] ?? $account->role,
                // 'status' => $validated['status'] ?? $account->status,
                $account->update($data)
            ]);

            // 🔗 Sync pivot relationships if provided
            // if (!empty($validated['accounts'])) {
            //     $pivotPayload = collect($validated['accounts'])->mapWithKeys(fn($entry) => [
            //         $entry['account_id'] => ['company_name' => $entry['company_name'] ?? null]
            //     ]);

            //     $account->contacts()->syncWithoutDetaching($pivotPayload);
            // }
        });

        return response()->json([
            'message' => 'Account successfully updated.',
            'data' => $account->fresh()
        ]);
    }


    public function destroy($id): JsonResponse
    {
        $account = Account::findOrFail($id);
        $user = Auth::user();

        // ✅ Block if not admin AND not the owner
        if (!($user->role === AccountRole::Admin || $user->id === $account->id)) {
            return response()->json([
                'message' => 'You are not authorized to delete this account.'
            ], 403);
        }

        DB::transaction(function () use ($account) {
            // Soft delete the account
            $account->delete();

            // Soft delete pivot relationships
            AccountContact::where('account_id', $account->id)
                ->update(['deleted_at' => now()]);
        });

        return response()->json([
            'message' => 'Account and related contact links soft-deleted successfully.'
        ]);
    }

    public function restore($id): JsonResponse
    {
        $account = Account::withTrashed()->findOrFail($id);

        DB::transaction(function () use ($account) {
            // Restore the account itself
            $account->restore();

            // Restore pivot relationships associated with the account
            AccountContact::withTrashed()
                ->where('account_id', $account->id)
                ->restore();
        });

        return response()->json([
            'message' => 'Account and contact links restored successfully.',
            'data' => $account->fresh()
        ]);
    }

}
