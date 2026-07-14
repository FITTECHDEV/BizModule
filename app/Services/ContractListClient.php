<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;
use RuntimeException;

/**
 * Calls [dbo].[sp_Contract_List], which has a different shape from the
 * sp_Contract_Main family:
 *   - single @InputJson param, no @OutputJson OUTPUT param
 *   - results come back as a plain SELECT result set
 *   - failures come back as a single-column row starting with "Failed - "
 *     instead of a {Status, Remarks} JSON structure
 *
 * Do not reuse ContractSpClient for this — the calling convention is
 * fundamentally different, not just a different OperationType.
 */
class ContractListClient
{
    private const CONTRACT_CONNECTION = 'sqlsrv'; // env-driven DB name, confirmed against config/database.php
    private const SP_NAME = 'dbo.sp_Contract_List';

    /**
     * @return array{ok: bool, message: ?string, rows: array<int,array<string,mixed>>}
     */
    public function call(array $payload): array
    {
        $inputJson = json_encode($payload, JSON_UNESCAPED_UNICODE);

        Log::info('Contract List SP - calling sp_Contract_List', [
            'UsedFor' => $payload['UsedFor'] ?? null,
            'json' => $inputJson,
        ]);

        try {
            $pdo = DB::connection(self::CONTRACT_CONNECTION)->getPdo();
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC ' . self::SP_NAME . ' ?');
            $stmt->execute([$inputJson]);

            $rows = [];
            do {
                try {
                    $fetched = $stmt->fetchAll(PDO::FETCH_NUM);
                } catch (\PDOException $e) {
                    continue;
                }
                if (!empty($fetched)) {
                    $rows = $fetched;
                    break;
                }
            } while ($stmt->nextRowset());
        } catch (\Exception $e) {
            Log::error('Contract List SP - execution failed', ['error' => $e->getMessage()]);
            throw new RuntimeException('Contract List SP call failed: ' . $e->getMessage(), previous: $e);
        }

        Log::info('Contract List SP - raw rows', ['row_count' => count($rows)]);

        // SP signals failure as a single-column, single-row result whose
        // value starts with "Failed - " — detect this before treating
        // the result as real data.
        if (count($rows) === 1 && count($rows[0]) === 1 && is_string($rows[0][0])
            && str_starts_with($rows[0][0], 'Failed')
        ) {
            return ['ok' => false, 'message' => $rows[0][0], 'rows' => []];
        }

        return ['ok' => true, 'message' => null, 'rows' => $rows];
    }
}