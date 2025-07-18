<?php

namespace App\Http\Requests;

use App\Traits\HasContactValidationRules;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{   
    use HasContactValidationRules;
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
        $contactId = $this->route('id') ?? optional($this->route('contact'))->id;

        return array_merge(
            $this->ContactRulesforUpdate(),
            [
                'email_address' => [
                    'nullable',
                    'email',
                    'max:255',
                    Rule::unique('contacts', 'email_address')->ignore($contactId),
                ],
            ]
        );
    }
}
