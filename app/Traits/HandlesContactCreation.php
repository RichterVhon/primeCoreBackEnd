<?php

namespace App\Traits;

use App\Models\Contact;

trait HandlesContactCreation
{
    public function createContact(array $data): Contact
    {
        $contact = Contact::create([
            'contact_person' => $data['contact_person'],
            'position' => $data['position'] ?? null,
            'contact_number' => $data['contact_number'] ?? null,
            'email_address' => $data['email_address'] ?? null,
        ]);

        // pivot to acc
        if (!empty($data['accounts'])) {
            $pivotPayload = collect($data['accounts'])->mapWithKeys(fn ($entry) => [
                $entry['account_id'] => ['company_name' => $entry['company_name'] ?? null]
            ]);

            $contact->accounts()->syncWithoutDetaching($pivotPayload);
        }

        return $contact->load([
            'accounts' => fn ($q) => $q->withPivot('company_name')
        ]);
    }
}
