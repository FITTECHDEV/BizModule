<?php
namespace App\Console\Commands;

use App\Imports\UsersImport;
use App\Services\ImportLockService;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ImportUsersCommand extends Command
{
    protected $signature = 'import:users {file} {--validate-only : Only validate the file without importing}';
    protected $description = 'Import users from a CSV or Excel file with data validation';

    public function handle()
    {
        Carbon::setLocale('en');
        $filePath = $this->argument('file');
        $validateOnly = $this->option('validate-only');
        $lockService = app(ImportLockService::class);
        $lockKey = 'users_import_in_progress';

        // Validate file existence and type
        if (!file_exists($filePath)) {
            $this->error("The file {$filePath} does not exist.");
            Log::error("User import failed: File {$filePath} does not exist.");
            return 1;
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (!in_array($extension, ['csv', 'xlsx', 'xls'])) {
            $this->error("Invalid file type: {$extension}. Only CSV or Excel files are supported.");
            Log::error("User import failed: Invalid file type {$extension} for {$filePath}.");
            return 1;
        }

        // NEW: Pre-validate the file data
        if ($this->shouldValidateData()) {
            $validationResults = $this->validateFileData($filePath);
            
            if (!empty($validationResults['errors'])) {
                $this->error("Data validation failed:");
                foreach ($validationResults['errors'] as $error) {
                    $this->error("  - {$error}");
                }
                
                if ($validateOnly) {
                    return 1;
                }
                
                if (!$this->confirm('Continue with import despite validation errors?')) {
                    return 1;
                }
            } else {
                $this->info("✓ File data validation passed");
                if ($validateOnly) {
                    $this->info("File is ready for import");
                    return 0;
                }
            }
        }

        $this->info("Starting user import from {$filePath} at " . Carbon::now('Africa/Nairobi')->toDateTimeString());
        Log::info("Starting user import from: {$filePath}");

        // Check and release stale lock
        $lockService->releaseStaleLock($lockKey);

        // Acquire lock
        if (!$lockService->acquireLock($lockKey)) {
            $this->error('Import skipped: another user import is in progress.');
            Log::warning("User import skipped due to active lock: {$filePath}");
            return 1;
        }

        try {
            Excel::import(new UsersImport, $filePath);
            $lockService->releaseLock($lockKey);
            
            $this->info("Users imported successfully from {$filePath} at " . Carbon::now('Africa/Nairobi')->toDateTimeString());
            Log::info("Users imported successfully from: {$filePath}");
            return 0;
            
        } catch (\Exception $e) {
            $lockService->releaseLock($lockKey);
            $this->error("Error during user import: {$e->getMessage()}");
            Log::error("Error during user import from {$filePath}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }

    /**
     * Determine if we should validate data based on file size or user preference
     */
    private function shouldValidateData(): bool
    {
        // Always validate for now, but you could add logic here
        // to skip validation for very large files
        return true;
    }

    /**
     * Validate the file data before importing
     * This catches potential database errors before they happen
     */
    private function validateFileData(string $filePath): array
    {
        $errors = [];
        $warnings = [];
        $rowCount = 0;
        
        try {
            // Read the file to validate data
            $data = Excel::toArray([], $filePath)[0] ?? [];
            
            // Skip header row if exists
            $hasHeaders = $this->detectHeaders($data);
            if ($hasHeaders) {
                array_shift($data);
            }
            
            foreach ($data as $index => $row) {
                $rowNumber = $index + ($hasHeaders ? 2 : 1);
                $rowCount++;
                
                // Validate userPrincipalName (assuming it's in first column)
                $userPrincipalName = $row[0] ?? '';
                if (empty($userPrincipalName)) {
                    $errors[] = "Row {$rowNumber}: userPrincipalName is required";
                } elseif (strlen($userPrincipalName) > 255) {
                    $errors[] = "Row {$rowNumber}: userPrincipalName too long (" . strlen($userPrincipalName) . " chars, max 255)";
                }
                
                // Validate displayName (assuming it's in second column)
                $displayName = $row[1] ?? '';
                if (!empty($displayName) && strlen($displayName) > 50) {
                    $warnings[] = "Row {$rowNumber}: displayName will be truncated (original: " . strlen($displayName) . " chars, max: 50)";
                }
                
                // Validate ID if provided (assuming it's in third column)
                $userId = $row[2] ?? '';
                if (!empty($userId) && strlen($userId) > 100) {
                    $errors[] = "Row {$rowNumber}: user ID too long (" . strlen($userId) . " chars, max 100)";
                }
                
                // Stop if we have too many errors
                if (count($errors) > 100) {
                    $errors[] = "Validation stopped: too many errors (100+ found)";
                    break;
                }
            }
            
            $this->info("Validated {$rowCount} rows");
            
            if (!empty($warnings)) {
                $this->warn("Validation warnings:");
                foreach (array_slice($warnings, 0, 10) as $warning) {
                    $this->warn("  - {$warning}");
                }
                if (count($warnings) > 10) {
                    $this->warn("  - ... and " . (count($warnings) - 10) . " more warnings");
                }
            }
            
        } catch (\Exception $e) {
            $errors[] = "Failed to validate file: " . $e->getMessage();
        }
        
        return [
            'errors' => $errors,
            'warnings' => $warnings,
            'row_count' => $rowCount
        ];
    }

    /**
     * Simple header detection
     */
    private function detectHeaders(array $data): bool
    {
        if (empty($data)) return false;
        
        $firstRow = $data[0] ?? [];
        
        // Look for common header patterns
        $headerPatterns = ['userPrincipalName', 'displayName', 'email', 'name', 'id'];
        
        foreach ($firstRow as $cell) {
            $cellLower = strtolower($cell ?? '');
            foreach ($headerPatterns as $pattern) {
                if (str_contains($cellLower, $pattern)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Emergency method: Direct insert with validation (use only if model approach fails)
     * This is the method you asked about - use it only as a last resort
     */
    private function directInsertUser(string $userPrincipalName, ?string $displayName, ?string $userId): bool
    {
        try {
            // Validate and truncate data
            if (strlen($userPrincipalName) > 255) {
                Log::error("userPrincipalName too long, skipping user", ['userPrincipalName' => $userPrincipalName]);
                return false;
            }
            
            // This is where you'd use the DB::table approach you mentioned
            DB::table('Azure_Users')->insert([
                'userPrincipalName' => $userPrincipalName,
                'displayName' => Str::limit($displayName ?? '', 50, ''), // Laravel helper for truncation
                'id' => $userId ?? 'user-' . uniqid() . '-' . Str::random(6)
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Direct insert failed", [
                'userPrincipalName' => $userPrincipalName,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}