<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AzureSignInImportService;
use App\Services\ImportLockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ImportSigninLogsCommand extends Command
{
    protected $signature = 'import:signin-logs {file} 
                            {--large : Use memory-efficient processing for large files} 
                            {--force : Override import lock if another import is running}';

    protected $description = 'Import Signin Log records from CSV with batch processing';

    private AzureSignInImportService $importService;
    private ImportLockService $lockService;

    private int $maxParams = 2100;
    private int $paramBuffer = 50;

    public function __construct(AzureSignInImportService $importService, ImportLockService $lockService)
    {
        parent::__construct();
        $this->importService = $importService;
        $this->lockService = $lockService;
    }

    public function handle()
    {
        $filePath = $this->argument('file');
        $isLargeFile = $this->option('large');
        $force = $this->option('force');

        if (!file_exists($filePath)) {
            $this->error("❌ File not found: $filePath");
            return 1;
        }

        // Acquire lock to prevent concurrent imports
        if (!$this->lockService->acquireLock('signin_import', 1800) && !$force) {
            $this->error("❌ Another import is already running. Use --force to override.");
            return 1;
        }

        if ($force) {
            Log::warning("⚠️ Import lock overridden with --force", ['file' => $filePath]);
        }

        try {
            $this->info("🚀 Starting Azure Sign-in import from: $filePath");

            // Pre-check: estimate batch size from first row
            $header = $this->getCsvHeader($filePath);
            if ($header) {
                $dbColumns = count($header) + 3; // id, created_at, updated_at
                $batchSize = (int) floor(($this->maxParams - $this->paramBuffer) / $dbColumns);

                if ($batchSize < 10) {
                    $this->warn("⚠️ Detected $dbColumns columns. Batch size will be very small ($batchSize). Import may be slow.");
                    Log::warning("Low batch size detected: $batchSize for $dbColumns columns.");
                }
            }

            if ($isLargeFile) {
                $this->info("📊 Using memory-efficient processing for large file...");
                $stats = $this->importService->importFromLargeCsv($filePath);
            } else {
                $this->info("📊 Processing file with standard batch import...");
                $stats = $this->importService->importFromFile($filePath);
            }

            $this->displayImportStats($stats);

            Log::info("✅ Import completed", [
                'file' => $filePath,
                'stats' => $stats,
                'mode' => $isLargeFile ? 'large' : 'standard'
            ]);

            $this->info("✅ Import completed successfully!");
            return 0;

        } catch (Exception $e) {
            $this->error("❌ Import failed: " . $e->getMessage());
            return 1;
        } finally {
            $this->lockService->releaseLock('signin_import');
        }
    }

    private function getCsvHeader(string $filePath): ?array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return null;
        }
        $header = fgetcsv($handle);
        fclose($handle);
        return $header;
    }

    private function displayImportStats(array $stats): void
    {
        $this->info("📊 Import Statistics:");
        $this->info("   Total Records: " . ($stats['total_records'] ?? 0));
        $this->info("   Successful: " . $stats['successful_imports']);
        $this->info("   Failed: " . $stats['failed_imports']);

        if (isset($stats['skipped_rows'])) {
            $this->info("   Skipped: " . $stats['skipped_rows']);
        }

        $this->info("   Duration: " . round($stats['duration_seconds'], 2) . " seconds");
        $this->info("   Speed: " . round($stats['records_per_second'], 2) . " records/second");
    }

    private function executeChunkedBatchInsert(array $signinLogs, int $batchNumber): int
    {
        $columnCount = count($signinLogs[0]);
        $availableParams = $this->maxParams - $this->paramBuffer;
        $theoreticalChunkSize = max(1, (int) floor($availableParams / $columnCount));

        if ($columnCount >= 80) {
            $safeChunkSize = 1;
        } elseif ($columnCount >= 60) {
            $safeChunkSize = min($theoreticalChunkSize, 2);
        } elseif ($columnCount >= 40) {
            $safeChunkSize = min($theoreticalChunkSize, 3);
        } elseif ($columnCount >= 25) {
            $safeChunkSize = min($theoreticalChunkSize, 5);
        } else {
            $safeChunkSize = min($theoreticalChunkSize, 8);
        }

        Log::debug("Enhanced chunked batch insert", [
            'batch' => $batchNumber,
            'total_rows' => count($signinLogs),
            'columns_per_row' => $columnCount,
            'theoretical_chunk_size' => $theoreticalChunkSize,
            'safe_chunk_size' => $safeChunkSize,
            'max_params_per_chunk' => $safeChunkSize * $columnCount
        ]);

        $insertedCount = 0;

        foreach (array_chunk($signinLogs, $safeChunkSize) as $chunkIndex => $chunk) {
            $paramCount = count($chunk) * $columnCount;

            if ($paramCount >= $availableParams || $paramCount > 1800) {
                Log::warning("Parameter count exceeded safety threshold, using single-row fallback", [
                    'batch' => $batchNumber,
                    'chunk' => $chunkIndex + 1,
                    'param_count' => $paramCount,
                    'safety_threshold' => $availableParams
                ]);

                $insertedCount += $this->executeSingleRowInserts($chunk, $batchNumber);
                continue;
            }

            try {
                DB::table('Azure_LoginDetails')
                    ->timeout(120)
                    ->insert($chunk);

                $insertedCount += count($chunk);

                Log::debug("Enhanced batch chunk inserted", [
                    'batch' => $batchNumber,
                    'chunk' => $chunkIndex + 1,
                    'rows' => count($chunk),
                    'params' => $paramCount,
                    'columns' => $columnCount
                ]);

            } catch (\Exception $e) {
                $isParamLimitError = str_contains($e->getMessage(), '2100') ||
                                     str_contains($e->getMessage(), 'parameter number') ||
                                     str_contains($e->getMessage(), 'maximum of') ||
                                     str_contains($e->getMessage(), 'IMSSP');

                Log::error($isParamLimitError
                    ? "Parameter limit error confirmed, forcing single-row mode"
                    : "Batch chunk failed for other reason, trying single inserts", [
                    'batch' => $batchNumber,
                    'chunk' => $chunkIndex + 1,
                    'param_count' => $paramCount,
                    'error_snippet' => substr($e->getMessage(), 0, 200)
                ]);

                $insertedCount += $this->executeSingleRowInserts($chunk, $batchNumber);
            }
        }

        return $insertedCount;
    }

    // You can define executeSingleRowInserts() here if needed
}
