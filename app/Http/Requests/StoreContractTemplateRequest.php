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
            'OperationType' => ['required', 'string', 'in:WorkbookCharges'],
            'SubmittedDatetime' => ['required', 'string'],
            'SubmittedBy' => ['required', 'string'],
            'Action' => ['required', 'string', 'in:Add'],
            'QuotationNo' => ['required', 'string'],
            'QuotationInfoID' => ['required', 'uuid'],
            'ContractID' => ['required', 'uuid'],

            'Charges' => ['required', 'array', 'min:1'],

            'Charges.*.ChargeCatagory' => ['required', 'string'],
            'Charges.*.ChargeRowID' => ['nullable', 'string'],
            'Charges.*.ChargeCode' => ['required', 'string'],
            'Charges.*.ChargeDescription' => ['nullable', 'string'],
            'Charges.*.PartyCode' => ['nullable', 'string'],
            'Charges.*.ChgCurr' => ['required', 'string'],
            'Charges.*.BasedOnCode' => ['required', 'string'],
            'Charges.*.BasedOnUOM' => ['nullable', 'string'],
            'Charges.*.Quantity' => ['required', 'numeric'],
            'Charges.*.CostRate' => ['required', 'numeric'],
            'Charges.*.CostAmount' => ['required', 'numeric'],
            'Charges.*.IncreaseBy' => ['required', 'numeric'],
            'Charges.*.IncreaseType' => ['required', 'string'],
            'Charges.*.SellRate' => ['required', 'numeric'],
            'Charges.*.SellAmount' => ['required', 'numeric'],
            'Charges.*.ChargeRemark' => ['nullable', 'string'],
        ];
    }
}