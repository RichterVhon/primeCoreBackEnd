<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountContactRequest extends FormRequest
{
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
        return [
            // contacts and pivot data
            'contacts' => 'required|array|min:1',
            'contacts.*.contact_id' => 'required|exists:contacts,id',
            'contacts.*.company_name' => 'nullable|string|max:255'
        ];
    }

    public function messages(): array
    {
        return [
            // kada data, may message to indicate
            'contacts.required' => 'Please provide at least one contact.',
            'contacts.*.contact_id.required' => 'Each contact must have a valid contact ID.',
            'contacts.*.contact_id.exists' => 'Contact ID does not exist in the database.'
        ];
    }
}
