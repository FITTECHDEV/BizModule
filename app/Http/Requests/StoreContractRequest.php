<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'contract_no' => ['required', 'string', 'max:50'],
            'contract_type' => ['required', 'string', 'in:Tariff,Long Term'],
            'contract_name' => ['required', 'string', 'max:500'],
            'status' => ['nullable', 'string'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['required', 'date', 'after_or_equal:effective_from'],
            'description' => ['nullable', 'string', 'max:1000'],
            'remark' => ['nullable', 'string', 'max:1000'],
            'services' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'effective_to.after_or_equal' => 'Effective To must be on or after Effective From.',
            'contract_type.in' => 'Contract Type must be either Tariff or Long Term.',
        ];
    }
}
