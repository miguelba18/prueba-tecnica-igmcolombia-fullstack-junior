<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customerId = $this->route('customer')->id;

        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'document_type' => ['sometimes', 'in:CC,NIT,CE,PA'],
            'document_number' => ['sometimes', 'string', Rule::unique('customers')->ignore($customerId)],
            'email' => ['sometimes', 'email', Rule::unique('customers')->ignore($customerId)],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'document_type.in' => 'El tipo de documento debe ser CC, NIT, CE o PA.',
            'document_number.unique' => 'Este número de documento ya está registrado.',
            'email.email' => 'El email debe ser válido.',
            'email.unique' => 'Este email ya está registrado.',
        ];
    }
}