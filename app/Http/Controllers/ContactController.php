<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;

class ContactController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        $query = Contact::query()
            ->whereHas(
                'accounts',
                fn($q) =>
                $q->where('accounts.id', $user->id)
                    ->where('account_contact.deleted_at', null) // 👈 pivot filter
            )
            ->with([
                'accounts' => fn($q) =>
                    $q->wherePivotNull('deleted_at') // 👈 safe eager load filter
            ])

            ->orderByRaw("ISNULL($sortField), $sortField $sortDirection");

        if ($request->filled('search')) {
            $query->search($request->input('search'), Contact::searchableFields());
        }

        if (!empty(Contact::filterableFields())) {
            $query->applyFilters($request->only(Contact::filterableFields()));
        }

        $contacts = $query->get();

        // 🗂 All visible contacts
        $myContacts = $contacts->map(fn($c) => [
            'id' => $c->id,
            'contact_person' => $c->contact_person,
            'position' => $c->position,
            'contact_number' => $c->contact_number,
            'email_address' => $c->email_address
        ]);

        // 👥 Shared subset — also linked to other accounts
        $sharedContacts = $contacts->filter(
            fn($c) =>
            $c->accounts->where('id', '!=', $user->id)->isNotEmpty()
        )->map(fn($c) => [
                'id' => $c->id,
                'contact_person' => $c->contact_person,
                'position' => $c->position,
                'contact_number' => $c->contact_number,
                'email_address' => $c->email_address,
                'shared_with' => $c->accounts
                    ->where('id', '!=', $user->id)
                    ->map(fn($acc) => [
                        'name' => $acc->name,
                        'email' => $acc->email
                    ])->values()
            ]);

        return response()->json([
            'data' => [
                'my_contacts' => $myContacts,
                'shared_contacts' => $sharedContacts
            ],
            'meta' => [
                'total_my_contacts' => $myContacts->count(),
                'total_shared_contacts' => $sharedContacts->count()
            ]
        ]);
    }

    public function show($id): JsonResponse
    {
        $user = Auth::user();

        $contact = Contact::with([
            'accounts' => fn($q) =>
                $q->wherePivotNull('deleted_at') // 👈 exclude unlinked accounts
        ])->findOrFail($id);

        if (!$contact->accounts->contains('id', $user->id)) {
            return response()->json([
                'message' => 'Forbidden: You do not have access to this contact.'
            ], Response::HTTP_FORBIDDEN);
        }


        return response()->json([
            'data' => [
                'id' => $contact->id,
                'contact_person' => $contact->contact_person,
                'position' => $contact->position,
                'contact_number' => $contact->contact_number,
                'email_address' => $contact->email_address
            ]
        ]);
    }

    public function store(StoreContactRequest $request): JsonResponse
    {
        $user = Auth::user();

        $data = $request->validated();

        $missingEmails = [];
        $linkedAccountIds = [];

        // Include the authenticated account by default
        $linkedAccountIds[$user->id] = ['company_name' => null];

        // Map emails to account IDs
        if (!empty($data['accounts'])) {
            foreach ($data['accounts'] as $entry) {
                $email = $entry['email'] ?? null;
                $company = $entry['company_name'] ?? null;

                if ($email) {
                    $account = \App\Models\Account::where('email', $email)->first();

                    if ($account) {
                        $linkedAccountIds[$account->id] = ['company_name' => $company];
                    } else {
                        $missingEmails[] = $email;
                    }
                }
            }
        }

        $contact = DB::transaction(function () use ($data, $linkedAccountIds) {
            $contact = \App\Models\Contact::create([
                'contact_person' => $data['contact_person'],
                'position' => $data['position'] ?? null,
                'contact_number' => $data['contact_number'] ?? null,
                'email_address' => $data['email_address'] ?? null,
            ]);

            $contact->accounts()->sync($linkedAccountIds);

            return $contact->load([
                'accounts' => fn($q) => $q->withPivot('company_name')
            ]);
        });

        return response()->json([
            'message' => 'Contact successfully created and linked to accounts.',
            'data' => $contact,
            'unmatched_emails' => $missingEmails
        ], 201);
    }

    public function update(UpdateContactRequest $request, $id): JsonResponse
    {
        $user = Auth::user();

        $contact = Contact::with('accounts')->findOrFail($id);

        // Ensure the user is linked to the contact
        if (!$contact->accounts->contains('id', $user->id)) {
            return response()->json([
                'message' => 'Forbidden: You do not have permission to update this contact.'
            ], Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $missingEmails = [];
        $pivotPayload = [];

        // Include the current account always
        $pivotPayload[$user->id] = ['company_name' => null];

        // Resolve provided emails into account IDs
        if (!empty($data['accounts'])) {
            foreach ($data['accounts'] as $entry) {
                $email = $entry['email'] ?? null;
                $company = $entry['company_name'] ?? null;

                if ($email) {
                    $account = \App\Models\Account::where('email', $email)->first();

                    if ($account) {
                        $pivotPayload[$account->id] = ['company_name' => $company];
                    } else {
                        $missingEmails[] = $email;
                    }
                }
            }
        }

        DB::transaction(function () use ($contact, $data, $pivotPayload) {
            $contact->update([
                'contact_person' => $data['contact_person'],
                'position' => $data['position'] ?? null,
                'contact_number' => $data['contact_number'] ?? null,
                'email_address' => $data['email_address'] ?? null,
            ]);

            // Replace all pivot associations with new set
            $contact->accounts()->sync($pivotPayload);
        });

        $contact->load([
            'accounts' => fn($q) => $q->withPivot('company_name')
        ]);

        return response()->json([
            'message' => 'Contact successfully updated.',
            'data' => $contact,
            'unmatched_emails' => $missingEmails
        ]);
    }

    public function softDelete($id): JsonResponse
    {
        $user = Auth::user();

        $contact = Contact::with(['accounts' => fn($q) => $q->wherePivotNull('deleted_at')])
            ->findOrFail($id);

        // Ensure user is linked via non-deleted pivot
        if (!$contact->accounts->contains('id', $user->id)) {
            return response()->json([
                'message' => 'Forbidden: You do not have access to this contact.'
            ], Response::HTTP_FORBIDDEN);
        }

        // ✅ This directly targets pivot and updates its deleted_at
        DB::table('account_contact')
            ->where('contact_id', $contact->id)
            ->where('account_id', $user->id)
            ->whereNull('deleted_at') // 👈 avoid overwriting already deleted rows
            ->update(['deleted_at' => now()]);

        return response()->json([
            'message' => 'Contact has been removed from your account view.'
        ]);
    }


    public function forceDelete($id): JsonResponse
    {
        $user = Auth::user();
        $contact = Contact::with(['accounts' => fn($q) => $q->wherePivotNull('deleted_at')])
            ->withTrashed()
            ->findOrFail($id);

        // Ensure user still has access
        $isLinked = DB::table('account_contact')
            ->where('contact_id', $contact->id)
            ->where('account_id', $user->id)
            ->whereNull('deleted_at')
            ->exists();

        if (!$isLinked) {
            return response()->json([
                'message' => 'Forbidden: You do not have access to this contact.'
            ], Response::HTTP_FORBIDDEN);
        }

        DB::transaction(function () use ($contact) {
            // 🧨 Soft delete the contact itself
            $contact->delete();

            // 🧨 Soft delete all pivot links for consistency
            DB::table('account_contact')
                ->where('contact_id', $contact->id)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => now()]);
        });

        return response()->json([
            'message' => 'Contact and all account associations have been archived.'
        ]);
    }



    public function restore($id): JsonResponse
    {
        $user = Auth::user();
        $contact = Contact::withTrashed()->findOrFail($id);

        DB::transaction(function () use ($contact, $user) {
            if ($contact->trashed()) {
                // Restore contact globally
                $contact->restore();

                // Restore all pivot links
                DB::table('account_contact')
                    ->where('contact_id', $contact->id)
                    ->whereNotNull('deleted_at')
                    ->update(['deleted_at' => null]);
            } else {
                // Contact exists — just restore pivot for current user
                DB::table('account_contact')
                    ->where('contact_id', $contact->id)
                    ->where('account_id', $user->id)
                    ->whereNotNull('deleted_at')
                    ->update(['deleted_at' => null]);
            }
        });

        $contact->load(['accounts' => fn($q) => $q->wherePivotNull('deleted_at')]);

        return response()->json([
            'message' => 'Contact restored successfully.',
            'data' => [
                'id' => $contact->id,
                'contact_person' => $contact->contact_person,
                'position' => $contact->position,
                'contact_number' => $contact->contact_number,
                'email_address' => $contact->email_address
            ]
        ]);
    }


}
