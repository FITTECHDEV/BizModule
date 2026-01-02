<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteMailingListRequest extends FormRequest
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
                'email',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'list_name.required' => 'Mailing list name is required',
            'list_name.email' => 'Invalid mailing list format (must be list@domain.com)',
        ];
    }
}
