<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;
use RuntimeException;

/**
 * Calls [dbo].[sp_Quote_ContractList].
 *
 * Calling convention (different from ContractListClient / sp_Contract_List):
 *   - @QuotationID uniqueidentifier IN param
 *   - @OutputJson varchar(max) OUTPUT param (SP declares Varchar, not Nvarchar)
 *   - single JSON object result: {Status, Remark, Charges: [...]}
 *
 * Do not reuse ContractListClient for this — different params, no resultset,
 * status comes from the JSON payload, not a sentinel row.
 *
 * Uses the codebase's standard OUTPUT-param pattern (DECLARE + EXEC + SELECT),
 * matching resolveUserOfficesViaSP / sp_Quote_QuotationFormat etc. in
 * QuotationController — PDO's native PARAM_INPUT_OUTPUT bind is not used
 * anywhere else against this sqlsrv driver and fails here with
 * "Invalid direction specified for parameter" / "Invalid precision value".
 */
class ContractListClient
{
    private const CONNECTION = 'sqlsrv'; // env-driven DB name, confirmed against config/database.php
    private const SP_NAME = 'dbo.sp_Quote_ContractList';

    /**
     * @return array{ok: bool, message: ?string, rows: array<int,array<string,mixed>>}
     */
    public function call(string $quotationId): array
    {
        Log::info('Quote Contract List SP - calling sp_Quote_ContractList', [
            'QuotationID' => $quotationId,
        ]);

        $outputJson = null;

        try {
            $pdo = DB::connection(self::CONNECTION)->getPdo();
            $stmt = $pdo->prepare("
                SET NOCOUNT ON;
                DECLARE @OutputJson VARCHAR(MAX);
                EXEC " . self::SP_NAME . " ?, @OutputJson OUTPUT;
                SELECT @OutputJson AS OutputJson;
            ");
            $stmt->execute([$quotationId]);

            do {
                try {
                    $rows = $stmt->fetchAll(PDO::FETCH_OBJ);
                    if (!empty($rows) && isset($rows[0]->OutputJson)) {
                        $outputJson = $rows[0]->OutputJson;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());
        } catch (\Exception $e) {
            Log::error('Quote Contract List SP - execution failed', ['error' => $e->getMessage()]);
            throw new RuntimeException('Quote Contract List SP call failed: ' . $e->getMessage(), previous: $e);
        }

        if ($outputJson === null || trim($outputJson) === '') {
            Log::error('Quote Contract List SP - empty output', ['QuotationID' => $quotationId]);
            return ['ok' => false, 'message' => 'No response from Contract List service', 'rows' => []];
        }

        $decoded = json_decode($outputJson, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            Log::error('Quote Contract List SP - invalid JSON output', [
                'QuotationID' => $quotationId,
                'raw' => $outputJson,
            ]);
            return ['ok' => false, 'message' => 'Invalid response from Contract List service', 'rows' => []];
        }

        $status = $decoded['Status'] ?? null;
        $remark = $decoded['Remark'] ?? 'Unknown error';

        if ($status !== 'Success') {
            return ['ok' => false, 'message' => $remark, 'rows' => []];
        }

        $charges = $decoded['Charges'] ?? [];
        if (!is_array($charges)) {
            $charges = [];
        }

        Log::info('Quote Contract List SP - success', [
            'QuotationID' => $quotationId,
            'row_count' => count($charges),
        ]);

        return ['ok' => true, 'message' => null, 'rows' => $charges];
    }
}