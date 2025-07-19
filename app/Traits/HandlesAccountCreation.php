<?php

namespace App\Traits;

use App\Models\Account;

trait HandlesAccountCreation
{
    public function createAccountWithContacts(array $data): Account
    {
        $contacts = collect($data['contacts'] ?? []);

        // create
        $account = Account::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // auto-hashed via mutator
            'role' => $data['role'],
            'status' => $data['status'],
        ]);

        // pivot nanaman
        $pivotPayload = $contacts->mapWithKeys(fn($entry) => [
            $entry['contact_id'] => ['company_name' => $entry['company_name'] ?? null]
        ]);

        if ($pivotPayload->isNotEmpty()) {
            $account->contacts()->syncWithoutDetaching($pivotPayload);
        }

        return $account->load([
            'contacts' => fn($q) => $q->withPivot('company_name')
        ]);
    }
}
