<?php

namespace App\Services\Contract;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;
use RuntimeException;

/**
 * Generic dispatcher for [dbo].[sp_Contract_Main].
 *
 * This class knows NOTHING about field names for ContractInfo,
 * ContractServiceDetails, or ServiceBasedCharges — those live only in
 * the per-operation Mapper classes (ContractInfoMapper, etc). If the SP's
 * expected JSON keys change, only the relevant Mapper needs to change —
 * never this file, never the controller.
 *
 * Mirrors the PDO execution pattern used in CrmSyncService::run() —
 * pinned PDO handle off a named Laravel connection, EXEC with bound params.
 */
class ContractSpClient
{
    // 'sqlsrv' connection's DB_DATABASE env var resolves to FITSYSUAT in UAT
    // and FITSYS in production — no hardcoding needed, confirmed against
    // config/database.php.
    private const CONTRACT_CONNECTION = 'sqlsrv';

    private const SP_NAME = 'dbo.sp_Contract_Main';

    /**
     * @param  array<string,mixed>  $payload  Fully-shaped payload, already
     *                                        mapped to the SP's exact JSON
     *                                        keys by the caller's Mapper.
     * @return array{status:string, contractNo:string, contractId:?string, remarks:string[]}
     *
     * @throws RuntimeException on connection/execution failure (not on a
     *                          business-logic "Failed" status from the SP —
     *                          that's returned normally so the caller can
     *                          surface the remarks to the user).
     */
    public function call(array $payload): array
    {
        $inputJson = json_encode($payload, JSON_UNESCAPED_UNICODE);

        Log::info('Contract SP - calling sp_Contract_Main', [
            'OperationType' => $payload['OperationType'] ?? null,
            'Action' => $payload['Action'] ?? null,
            'json' => $inputJson,
        ]);

        try {
            $pdo = DB::connection(self::CONTRACT_CONNECTION)->getPdo();

            $sql = 'EXEC ' . self::SP_NAME . ' @InputJson = ?, @OutputJson = ? OUTPUT';
            $stmt = $pdo->prepare($sql);

            $outputJson = null;

            $stmt->bindParam(1, $inputJson, PDO::PARAM_STR);
            // Length param on bindParam is what signals pdo_sqlsrv to treat
            // this as an OUTPUT parameter — must be present, not omitted.
            $stmt->bindParam(2, $outputJson, PDO::PARAM_STR, 4000);

            $stmt->execute();
        } catch (\Exception $e) {
            Log::error('Contract SP - execution failed', [
                'OperationType' => $payload['OperationType'] ?? null,
                'Action' => $payload['Action'] ?? null,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Contract SP call failed: ' . $e->getMessage(), previous: $e);
        }

        Log::info('Contract SP - raw output', [
            'OperationType' => $payload['OperationType'] ?? null,
            'output' => $outputJson,
        ]);

        return $this->parseOutput($outputJson);
    }

    /**
     * @return array{status:string, contractNo:string, contractId:?string, remarks:string[]}
     */
    private function parseOutput(?string $outputJson): array
    {
        if ($outputJson === null || trim($outputJson) === '') {
            // Sub-SP produced no output at all — treat as failure rather
            // than silently returning an empty success. See note in
            // sp_Contract_Main review: success path only guarantees
            // @OutputJson if the delegated sub-SP itself sets it.
            return [
                'status' => 'Failed',
                'contractNo' => '',
                'contractId' => null,
                'remarks' => ['SP returned no output JSON.'],
            ];
        }

        $decoded = json_decode($outputJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Contract SP - output JSON parse error', [
                'error' => json_last_error_msg(),
                'raw' => $outputJson,
            ]);

            return [
                'status' => 'Failed',
                'contractNo' => '',
                'contractId' => null,
                'remarks' => ['Could not parse SP output JSON: ' . json_last_error_msg()],
            ];
        }

        $remarks = [];
        // SP shapes remarks as: [{"Remark": "..."}] under a nested key —
        // exact key name depends on the SELECT ... FOR JSON PATH structure.
        // Flattening defensively since the FOR JSON PATH,WITHOUT_ARRAY_WRAPPER
        // shape nests remarks under a column named after the subquery.
        foreach ($decoded as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    if (isset($item['Remark'])) {
                        $remarks[] = $item['Remark'];
                    }
                }
            }
        }

        return [
            'status' => $decoded['Status'] ?? 'Failed',
            'contractNo' => $decoded['ContractNo'] ?? '',
            'contractId' => $decoded['ContractID'] ?? null,
            'remarks' => $remarks,
        ];
    }
}
