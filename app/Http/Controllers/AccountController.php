<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Enums\AccountRole;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
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
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        $query = Account::query();

        if ($request->filled('search')) {
            $query->search($request->input('search'), Account::searchableFields());
        }

        $query->applyFilters($request->only(Account::filterableFields()));
        $query->orderByRaw("ISNULL($sortField), $sortField $sortDirection");

        $accounts = $query
            ->with(['contacts']) // Include pivot contacts
            ->paginate(10)
            ->appends($request->query());

        return response()->json([
            'data' => $accounts->getCollection()->map(function ($account) {
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




        // return response()->json([
        //     'data' => $accounts->items(),
        //     'meta' => [
        //         'current_page' => $accounts->currentPage(),
        //         'per_page' => $accounts->perPage(),
        //         'total' => $accounts->total(),
        //         'last_page' => $accounts->lastPage(),
        //         'next_page_url' => $accounts->nextPageUrl(),
        //         'prev_page_url' => $accounts->previousPageUrl()
        //     ]
        // ]);
    }

    public function show($id): JsonResponse
    {
        $account = Account::with(['contacts' => fn($q) => $q->withPivot('company_name')])
            ->findOrFail($id);

        return response()->json(['data' => $account]);
    }

    public function store(StoreAccountRequest $request): JsonResponse
    {
        $account = DB::transaction(function () use ($request) {
            $data = $request->validated();

            // 🏗 Create Account
            $account = Account::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'], // Laravel will auto-hash via model cast
                'role' => $data['role'],
                'status' => $data['status'],
            ]);

            // 🔗 Attach contacts via pivot table, if any
            if (!empty($data['contacts'])) {
                $pivotPayload = collect($data['contacts'])->mapWithKeys(function ($entry) {
                    return [
                        $entry['contact_id'] => ['company_name' => $entry['company_name'] ?? null]
                    ];
                });

                $account->contacts()->syncWithoutDetaching($pivotPayload);
            }

            return $account->load([
                'contacts' => fn($q) => $q->withPivot('company_name')
            ]);
        });

        return response()->json([
            'message' => 'Account successfully created with linked contacts.',
            'data' => $account
        ], 201);
    }


    public function update(Request $request, $id): JsonResponse
    {
        $account = Account::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('accounts')->ignore($account->id),
            ],
            'password' => ['sometimes', Password::defaults()],
            'role' => ['sometimes', new Enum(AccountRole::class)],
            'status' => 'sometimes|boolean',
            'accounts' => 'sometimes|array',
            'accounts.*.account_id' => 'sometimes|exists:accounts,id',
            'accounts.*.company_name' => 'sometimes|string|max:255',
        ]);

        DB::transaction(function () use ($account, $validated) {
            // 🔐 Update core fields
            $account->update([
                'name' => $validated['name'] ?? $account->name,
                'email' => $validated['email'] ?? $account->email,
                'password' => isset($validated['password']) ? bcrypt($validated['password']) : $account->password,
                'role' => $validated['role'] ?? $account->role,
                'status' => $validated['status'] ?? $account->status,
            ]);

            // 🔗 Sync pivot relationships if provided
            if (!empty($validated['accounts'])) {
                $pivotPayload = collect($validated['accounts'])->mapWithKeys(fn($entry) => [
                    $entry['account_id'] => ['company_name' => $entry['company_name'] ?? null]
                ]);

                $account->contacts()->syncWithoutDetaching($pivotPayload);
            }
        });

        return response()->json([
            'message' => 'Account successfully updated.',
            'data' => $account->fresh()->load([
                'contacts' => fn($q) => $q->withPivot('company_name')
            ])
        ]);
    }


    // public function destroy($id): JsonResponse
    // {
    //     Account::findOrFail($id)->delete();

    //     return response()->json(['message' => 'Account successfully deleted.']);
    // }
}
