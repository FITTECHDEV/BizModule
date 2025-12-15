<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use App\Models\Logintracker\AzureUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ImportUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:users {file} {--dry-run : Show what would be imported without actually importing} {--expected-count=307 : Expected number of unique users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import users from a CSV file with validation and duplicate prevention';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        $isDryRun = $this->option('dry-run');
        $expectedCount = (int) $this->option('expected-count');
        $lockKey = 'signin_import_in_progress';

        if (!file_exists($filePath)) {
            $this->error("The file {$filePath} does not exist.");
            return 1;
        }

        if (Cache::has($lockKey)) {
            $this->error('Signin import is already in progress. Please wait or clear the lock with:');
            $this->line("php artisan cache:forget {$lockKey}");
            Log::warning('Signin import blocked due to active lock', ['lock_key' => $lockKey]);
            return 1;
        }

        $this->info($isDryRun ? 'DRY RUN MODE - No actual import will occur' : 'LIVE IMPORT MODE');
        $this->info('Importing users from: ' . $filePath);

        try {
            if (!$isDryRun) {
                Cache::put($lockKey, true, 300);
                Log::info('Signin import lock acquired', ['lock_key' => $lockKey, 'file' => $filePath]);
            }

            $this->info('📋 Validating import file...');
            $validation = $this->preValidateImportFile($filePath);

            if (!$validation['valid']) {
                $this->error('❌ Validation failed: ' . $validation['message']);
                return 1;
            }

            $uniqueUsersInFile = $validation['unique_count'];
            $totalRowsInFile = $validation['total_rows'];
            $currentDbCount = AzureUser::count();

            $this->info("📊 File Analysis:");
            $this->line("  Total rows in file: {$totalRowsInFile}");
            $this->line("  Unique users in file: {$uniqueUsersInFile}");
            $this->line("  Current users in database: {$currentDbCount}");
            $this->line("  Expected unique users: {$expectedCount}");

            if ($uniqueUsersInFile > ($expectedCount * 1.2)) {
                $this->warn("⚠️  File contains {$uniqueUsersInFile} unique users, but you expected {$expectedCount}");
                if (!$this->confirm('Do you want to continue?')) {
                    $this->info('Import cancelled.');
                    return 0;
                }
            }

            if ($isDryRun) {
                $this->info('✅ DRY RUN: File validation passed. Use without --dry-run to perform actual import.');
                $this->displaySampleData($validation);
                return 0;
            }

            $this->info('🚀 Starting import...');
            Log::info('Starting user import', [
                'file' => $filePath,
                'unique_users_in_file' => $uniqueUsersInFile,
                'current_db_count' => $currentDbCount,
                'expected_count' => $expectedCount
            ]);

            Excel::import(new UsersImport, $filePath);

            $newDbCount = AzureUser::count();
            $actualImported = $newDbCount - $currentDbCount;

            $this->info('📈 Import Results:');
            $this->line("  Users in database before: {$currentDbCount}");
            $this->line("  Users in database after: {$newDbCount}");
            $this->line("  New users created: {$actualImported}");
            $this->line("  Users updated: " . ($uniqueUsersInFile - $actualImported));

            if ($newDbCount > ($currentDbCount + $expectedCount * 1.5)) {
                $this->warn("⚠️  Database now has {$newDbCount} users, which seems too many!");
                $this->warn("You may have duplicates. Consider running: php artisan users:cleanup-duplicates --dry-run");
                $this->displayDuplicateInfo();
            } elseif ($actualImported === 0 && $uniqueUsersInFile > 0) {
                $this->info("ℹ️  No new users created - all users already existed and were updated if needed.");
            } else {
                $this->info('✅ Import completed successfully!');
            }

            Log::info('Import completed', [
                'users_created' => $actualImported,
                'total_users' => $newDbCount,
                'file' => $filePath
            ]);

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error during import: ' . $e->getMessage());
            Log::error('Signin import failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        } finally {
            if (!$isDryRun && Cache::has($lockKey)) {
                Cache::forget($lockKey);
                Log::info('Signin import lock released', ['lock_key' => $lockKey]);
            }
        }
    }


    /**
     * Pre-validate the import file
     *
     * @param string $filePath
     * @return array
     */
    private function preValidateImportFile($filePath)
    {
        try {
            // Read the file to analyze content
            $data = Excel::toArray(new class {}, $filePath);

            if (empty($data[0])) {
                return ['valid' => false, 'message' => 'File appears to be empty'];
            }

            $rows = $data[0];
            if (count($rows) < 2) {
                return ['valid' => false, 'message' => 'File must have at least a header row and one data row'];
            }

            $headers = array_change_key_case($rows[0] ?? [], CASE_LOWER);

            // Check required headers
            $requiredHeaders = ['userprincipalname', 'displayname'];
            $missingHeaders = [];

            foreach ($requiredHeaders as $required) {
                if (!isset($headers[$required]) && !in_array($required, array_keys($headers))) {
                    $missingHeaders[] = $required;
                }
            }

            if (!empty($missingHeaders)) {
                return [
                    'valid' => false,
                    'message' => 'Missing required columns: ' . implode(', ', $missingHeaders) . '. Found columns: ' . implode(', ', array_keys($headers))
                ];
            }

            // Count unique userPrincipalNames
            $uniqueUsers = [];
            $emptyUsers = 0;
            $sampleUsers = [];
            $totalRows = count($rows) - 1; // Subtract header row

            for ($i = 1; $i < count($rows) && $i <= 1000; $i++) { // Limit to first 1000 rows for performance
                if (count($rows[$i]) < count($headers)) {
                    continue; // Skip malformed rows
                }

                $row = array_combine(
                    array_keys($headers),
                    array_slice($rows[$i], 0, count($headers))
                );

                $userPrincipalName = trim($row['userprincipalname'] ?? '');
                $displayName = trim($row['displayname'] ?? '');

                if (empty($userPrincipalName)) {
                    $emptyUsers++;
                } else {
                    $key = strtolower($userPrincipalName);
                    if (!isset($uniqueUsers[$key])) {
                        $uniqueUsers[$key] = true;

                        // Collect sample data for display
                        if (count($sampleUsers) < 5) {
                            $sampleUsers[] = [
                                'userPrincipalName' => $userPrincipalName,
                                'displayName' => $displayName
                            ];
                        }
                    }
                }
            }

            $uniqueCount = count($uniqueUsers);

            if ($uniqueCount === 0) {
                return ['valid' => false, 'message' => 'No valid userPrincipalName entries found'];
            }

            return [
                'valid' => true,
                'unique_count' => $uniqueCount,
                'total_rows' => $totalRows,
                'empty_users' => $emptyUsers,
                'headers' => $headers,
                'sample_users' => $sampleUsers
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => 'Failed to validate file: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Display sample data from the file
     *
     * @param array $validation
     */
    private function displaySampleData($validation)
    {
        if (isset($validation['sample_users']) && !empty($validation['sample_users'])) {
            $this->info("\n📋 Sample users from file:");
            foreach ($validation['sample_users'] as $user) {
                $this->line("  • {$user['userPrincipalName']} - {$user['displayName']}");
            }
        }

        if (isset($validation['empty_users']) && $validation['empty_users'] > 0) {
            $this->warn("⚠️  Found {$validation['empty_users']} rows with empty userPrincipalName (will be skipped)");
        }
    }

    /**
     * Display information about current duplicates
     */
    private function displayDuplicateInfo()
    {
        try {
            $duplicateCount = AzureUser::select('userPrincipalName')
                ->groupBy('userPrincipalName')
                ->havingRaw('COUNT(*) > 1')
                ->count();

            if ($duplicateCount > 0) {
                $this->warn("Found {$duplicateCount} userPrincipalNames with duplicates in the database.");

                $topDuplicates = AzureUser::select('userPrincipalName', \DB::raw('COUNT(*) as count'))
                    ->groupBy('userPrincipalName')
                    ->havingRaw('COUNT(*) > 1')
                    ->orderBy('count', 'desc')
                    ->limit(3)
                    ->get();

                if ($topDuplicates->count() > 0) {
                    $this->line("Top duplicates:");
                    foreach ($topDuplicates as $duplicate) {
                        $this->line("  • {$duplicate->userPrincipalName}: {$duplicate->count} records");
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignore errors in duplicate display
            Log::warning('Could not display duplicate info: ' . $e->getMessage());
        }
    }
}
