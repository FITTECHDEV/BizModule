<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CreateEmailAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // No authentication required as per requirements
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $minPasswordLength = config('cpanel.email.min_password_length', 8);
        $maxQuota = config('cpanel.email.max_quota', 1000);

        return [
            'email' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9._-]+$/',
                'max:64',
            ],
            'password' => [
                'required',
                'string',
                Password::min($minPasswordLength)
                    ->letters()
                    ->numbers()
                    ->mixedCase()
                    ->symbols(),
            ],
            'domain' => [
                'nullable',
                'string',
                'regex:/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            ],
            'quota' => [
                'nullable',
                'integer',
                'min:0',
                "max:{$maxQuota}",
            ],
            'send_welcome_email' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email username is required',
            'email.regex' => 'Email username can only contain letters, numbers, dots, hyphens, and underscores',
            'email.max' => 'Email username cannot exceed 64 characters',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least :min characters',
            'domain.regex' => 'Invalid domain format',
            'quota.integer' => 'Quota must be a number',
            'quota.min' => 'Quota must be at least 0 (0 means unlimited)',
            'quota.max' => 'Quota cannot exceed :max MB',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => 'email username',
            'quota' => 'disk quota',
        ];
    }

    /**
     * Get the validated email with domain.
     *
     * @return string
     */
    public function getFullEmail(): string
    {
        $domain = $this->input('domain', config('cpanel.default_domain'));
        return $this->input('email') . '@' . $domain;
    }

    /**
     * Get the validated quota or default.
     *
     * @return int
     */
    public function getQuota(): int
    {
        return $this->input('quota', config('cpanel.default_quota'));
    }
}