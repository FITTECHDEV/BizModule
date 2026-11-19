<?php

namespace App\Services\Contract;

class ContractInfoMapper
{
    private const FIELD_MAP = [
        'contract_type' => 'ContactType', // SP typo — see class docblock
        'office_code' => 'OfficeCode',
        'client_code' => 'ClientCode',
        'contract_date' => 'ContractDate',
        'effective_from' => 'EffectiveFrom',
        'effective_to' => 'EffectiveTo',
        'contract_name' => 'ContractName',
        'remark' => 'Remark',
    ];

    /**
     * @param  string  $action  'Add' | 'Update' | 'Delete'
     * @param  array<string,mixed>  $data  Internal field values, keyed per FIELD_MAP.
     *                                     Must include 'contract_id' for Update/Delete.
     * @param  string|null  $submittedBy
     */
    public static function build(string $action, array $data, ?string $submittedBy = null): array
    {
        $payload = [
            'OperationType' => 'ContractInfo',
            'Action' => $action,
            'SubmittedBy' => $submittedBy,
            'SubmittedDatetime' => now()->format('Y-m-d H:i:s'),
        ];

        foreach (self::FIELD_MAP as $internalKey => $spKey) {
            if (array_key_exists($internalKey, $data)) {
                $payload[$spKey] = $data[$internalKey];
            }
        }

        // ContractID is required for Update/Delete, must be absent for Add
        // (the SP explicitly rejects a non-null ContractID on Add).
        if ($action !== 'Add' && !empty($data['contract_id'])) {
            $payload['ContractID'] = $data['contract_id'];
        }

        return $payload;
    }
}
