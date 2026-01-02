<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateMailingListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'list_name' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9._-]+$/',
                'max:64',
            ],
            'domain' => [
                'nullable',
                'string',
                'regex:/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            ],
            'password' => [
                'required',
                'string',
                'min:6',
            ],
            'privacy' => [
                'nullable',
                'in:private,public',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'list_name.required' => 'Mailing list name is required',
            'list_name.regex' => 'Mailing list name can only contain letters, numbers, dots, hyphens, and underscores',
            'list_name.max' => 'Mailing list name cannot exceed 64 characters',
            'password.required' => 'Mailing list password is required',
            'password.min' => 'Password must be at least 6 characters',
            'privacy.in' => 'Privacy must be either private or public',
        ];
    }

    public function getFullListName(): string
    {
        $domain = $this->input('domain', config('cpanel.default_domain'));
        return $this->input('list_name') . '@' . $domain;
    }

    public function getPrivacy(): string
    {
        return $this->input('privacy', config('cpanel.mailing_list.default_privacy'));
    }
}
