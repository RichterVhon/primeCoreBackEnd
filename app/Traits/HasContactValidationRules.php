<?php

namespace App\Traits;

trait HasContactValidationRules
{
    public function contactRules(): array
    {
        return [
            'contact_person' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            'email_address' => 'nullable|email|max:255',
            'accounts' => 'array',
            'accounts.*.email' => 'required|email',
            'accounts.*.company_name' => 'nullable|string|max:255',
        ];
    }

    public function ContactRulesforUpdate(): array
    {
        return array_merge($this->contactRules(), [
            'email_address' => 'nullable|email|max:255|unique:contacts,email,' . $this->route('id')
        ]);
    }
}

