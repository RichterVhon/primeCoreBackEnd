<?php

namespace App\Traits;

use App\Enums\AccountRole;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

trait HasAccountValidationRules
{
    public function accountRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:accounts,email',
            'password' => ['required', Password::defaults()],
            'role' => ['required', new Enum(AccountRole::class)],
            'status' => 'required|boolean',
            'contacts' => 'array',
            'contacts.*.contact_id' => 'required|exists:contacts,id',
            'contacts.*.company_name' => 'nullable|string|max:255',


        ];
    }

    public function accountRulesforupdate(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:accounts,email',
            'password' => ['sometimes', Password::defaults()],
            'role' => ['sometimes', new Enum(AccountRole::class)],
            'status' => 'sometimes|boolean',
            'contacts' => 'sometimes',
            'contacts.*.contact_id' => 'sometimes|exists:contacts,id',
            'contacts.*.company_name' => 'sometimes|string|max:255',

        ];
    }
}
