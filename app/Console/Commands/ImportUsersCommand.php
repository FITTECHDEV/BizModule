<?php

namespace App\Console\Commands;

use App\Imports\UsersImport;
use App\Services\ImportLockService;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ImportUsersCommand extends Command
{
    protected $signature = 'import:users {file}';
    protected $description = 'Import users from a CSV or Excel file';

    public function handle()
    {
        Carbon::setLocale('en');
        $filePath = $this->argument('file');
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
}