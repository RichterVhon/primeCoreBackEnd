<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;
use App\Enums\AccountRole;

use Illuminate\Validation\Rule;
use App\Traits\HasAccountValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{

    use HasAccountValidationRules;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $accountId = $this->route('id') ?? optional($this->route('account'))->id; // or 'account' if using route model binding

        return array_merge(
            $this->accountRulesforupdate(),
            [
                'email' => [
                    'sometimes',
                    'email',
                    'max:255',
                    Rule::unique('accounts', 'email')->ignore($accountId),
                ],
            ]
        );
    }
}
