<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;
use RuntimeException;

class ContractListSpClient
{
    private const CONNECTION = 'sqlsrv';
    private const SP_NAME = 'dbo.sp_Contract_List';

    /**
     * @param array<string,mixed> $payload
     * @return array{ok:bool, message:?string, rows: array<int,array<int,mixed>>}
     */
    public function call(array $payload): array
    {
        $inputJson = json_encode($payload);
        Log::info('Contract List SP - calling sp_Contract_List', [
            'UsedFor' => $payload['UsedFor'] ?? null,
            'json' => $inputJson,
        ]);

        try {
            $pdo = DB::connection(self::CONNECTION)->getPdo();
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC ' . self::SP_NAME . ' @InputJson = ?');
            $stmt->execute([$inputJson]);

            $rows = [];
            // The SP returns rowsets; the List mode returns the primary rows in the first
            // resultset. Fetch numeric arrays to match controller expectations.
            do {
                try {
                    $fetched = $stmt->fetchAll(PDO::FETCH_NUM);
                    if (!empty($fetched)) {
                        $rows = $fetched;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());
        } catch (\Exception $e) {
            Log::error('Contract List SP - execution failed', [
                'UsedFor' => $payload['UsedFor'] ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException('Contract SP call failed: ' . $e->getMessage(), previous: $e);
        }

        Log::info('Contract List SP - raw rows', [
            'row_count' => count($rows),
            'rows' => $rows,
        ]);
        return ['ok' => true, 'message' => null, 'rows' => $rows];
    }
}
