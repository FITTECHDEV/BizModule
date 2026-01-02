<?php

// =============================================================================
// File: app/Http/Requests/UpdateEmailPasswordRequest.php
// =============================================================================

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateEmailPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $minPasswordLength = config('cpanel.email.min_password_length', 8);

        return [
            'email' => [
                'required',
                'email',
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
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required',
            'email.email' => 'Invalid email address format',
            'password.required' => 'New password is required',
        ];
    }
}                                                                                                                                                                                                                                                               