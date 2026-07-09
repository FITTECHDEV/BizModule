<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contract_template_id' => ['required', 'string'],
            'contract_template_name' => ['required', 'string'],
            'markup_type' => ['required', 'in:Fixed,Percentage'],
            'amount' => ['required_if:markup_type,Fixed', 'nullable', 'numeric', 'min:0'],
            'percentage' => ['required_if:markup_type,Percentage', 'nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
