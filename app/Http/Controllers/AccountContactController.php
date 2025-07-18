<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

use App\Models\Contact;
use App\Models\Account;
use App\Enums\AccountRole;


class AccountContactController extends Controller
{
    public function index($accountId): JsonResponse
    {
        $user = Auth::user();

        if (!in_array($user->role, [AccountRole::Admin, AccountRole::Agent])) {
            return response()->json([
                'message' => 'Forbidden: Admin or Agent access only'
            ], Response::HTTP_FORBIDDEN);
        }

        $account = Account::with(['contacts' => fn($q) => $q->withPivot('company_name')])
            ->findOrFail($accountId);

        return response()->json([
            'data' => $account->contacts
        ]);
    }

    public function store(Request $request, $accountId): JsonResponse
    {
        $user = Auth::user();

        if (!in_array($user->role, [AccountRole::Admin, AccountRole::Agent])) {
            return response()->json([
                'message' => 'Forbidden: Admin or Agent access only'
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'contacts' => 'required|array',
            'contacts.*.contact_id' => 'required|exists:contacts,id',
            'contacts.*.company_name' => 'nullable|string'
        ]);

        $account = Account::findOrFail($accountId);

        $pivotPayload = collect($validated['contacts'])->mapWithKeys(function ($entry) {
            return [$entry['contact_id'] => ['company_name' => $entry['company_name'] ?? null]];
        });

        $account->contacts()->syncWithoutDetaching($pivotPayload);

        return response()->json([
            'message' => 'Contacts successfully attached to account.',
            'data' => $account->contacts()->withPivot('company_name')->get()
        ]);
    }

    public function update(Request $request, $accountId, $contactId): JsonResponse
    {
        $user = Auth::user();

        if (!in_array($user->role, [AccountRole::Admin])) {
            return response()->json([
                'message' => 'Forbidden: Admin access only'
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'company_name' => 'nullable|string'
        ]);

        $account = Account::findOrFail($accountId);
        $account->contacts()->updateExistingPivot($contactId, $validated);

        return response()->json([
            'message' => 'Pivot data successfully updated.'
        ]);
    }

    public function destroy($accountId, $contactId): JsonResponse
    {
        $user = Auth::user();

        if (!in_array($user->role, [AccountRole::Admin])) {
            return response()->json([
                'message' => 'Forbidden: Admin access only'
            ], Response::HTTP_FORBIDDEN);
        }

        $account = Account::findOrFail($accountId);
        $account->contacts()->detach($contactId);

        return response()->json([
            'message' => 'Contact successfully detached.'
        ]);
    }

    public function fromContact($contactId): JsonResponse
    {
        $contact = Contact::with(['accounts' => fn($q) => $q->withPivot('company_name')])
            ->findOrFail($contactId);

        return response()->json([
            'data' => $contact->accounts
        ]);
    }


    //     public function store(StoreAccountContactRequest $request, Account $account)
    // {
    //     $payload = collect($request->validated()['contacts'])
    //         ->mapWithKeys(fn ($c) => [$c['contact_id'] => ['company_name' => $c['company_name'] ?? null]]);

    //     $account->contacts()->syncWithoutDetaching($payload);

    //     return response()->json([
    //         'message' => 'Contacts linked successfully.',
    //         'data' => $account->load('contacts')
    //     ]);
    // }

    // public function index(Account $account)
    // {
    //     return response()->json($account->contacts()->withPivot('company_name')->get());
    // }

}
