<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:azure_users,mail'
            ],
            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],
            'mobile_phone' => [
                'nullable',
                'string',
                'max:50',
            ],
            'location' => [
                'nullable',
                'string',
                'max:255'
            ],
            'company_id' => [
                'nullable',
                'string',
                'max:255'
            ],
            'job_title' => [
                'nullable',
                'string',
                'max:255'
            ],
            'department' => [
                'nullable',
                'string',
                'max:255'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.min' => 'The name must be at least 2 characters.',
            'email.required' => 'The email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim($this->name ?? ''),
            'email' => strtolower(trim($this->email ?? '')),
            'phone' => trim($this->phone ?? ''),
            'mobile_phone' => trim($this->mobile_phone ?? ''),
        ]);
    }
}