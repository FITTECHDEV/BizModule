<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use App\Models\Logintracker\AzureUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class UserImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:import 
                            {--file= : CSV file path relative to storage/app directory}
                            {--dry-run : Show what would be imported without actually importing}
                            {--expected-count=307 : Expected number of unique users}
                            {--force : Skip confirmations and proceed with import}';

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
        $fileName = $this->option('file');
        $isDryRun = $this->option('dry-run');
        $expectedCount = (int) $this->option('expected-count');
        $force = $this->option('force');

        // Validate file parameter
        if (!$fileName) {
            $this->error('Please provide a CSV file path using --file=filename.csv');
            $this->line('Example: php artisan user:import --file=users.csv');
            return 1;
        }

        // Construct full file path
        $filePath = storage_path("app/{$fileName}");

        // Validate file exists
        if (!file_exists($filePath)) {
            $this->error("The file {$fileName} does not exist in storage/app directory.");
            $this->line("Looking for: {$filePath}");
            $this->line('Available files in storage/app:');
            $this->listStorageFiles();
            return 1;
        }

        // Check if import is already running
        if (Cache::has('import_in_progress')) {
            $this->error('Import is already in progress. Please wait or clear the lock with:');
            $this->line('php artisan cache:forget import_in_progress');
            return 1;
        }

        $this->displayHeader($isDryRun, $fileName);
        
        try {
            // Set import lock
            if (!$isDryRun) {
                Cache::put('import_in_progress', true, 600); // 10 minute timeout
                Log::info('Import lock acquired', [
                    'lock_key' => 'import_in_progress', 
                    'timeout' => 600,
                    'file' => $fileName
                ]);
            }

            // Pre-validate the file
            $this->info('🔍 Analyzing CSV file...');
            $validation = $this->preValidateImportFile($filePath);
            
            if (!$validation['valid']) {
                $this->error('❌ File validation failed: ' . $validation['message']);
                return 1;
            }

            $this->displayFileAnalysis($validation, $expectedCount);

            // Check for potential issues
            $issues = $this->checkForIssues($validation, $expectedCount);
            if (!empty($issues)) {
                $this->displayIssues($issues);
                
                if (!$force && !$this->confirm('Do you want to continue despite these issues?')) {
                    $this->info('Import cancelled by user.');
                    return 0;
                }
            }

            if ($isDryRun) {
                $this->displayDryRunResults($validation);
                return 0;
            }

            // Get current database state
            $currentDbCount = AzureUser::count();
            $this->line("Current users in database: {$currentDbCount}");

            // Confirm before proceeding (unless forced)
            if (!$force && !$this->confirm('Proceed with the import?')) {
                $this->info('Import cancelled by user.');
                return 0;
            }

            // Perform the actual import
            $this->performImport($filePath, $validation, $currentDbCount, $expectedCount);

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Import failed: ' . $e->getMessage());
            Log::error('User import failed', [
                'file' => $fileName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;

        } finally {
            // Always release the lock
            if (!$isDryRun && Cache::has('import_in_progress')) {
                Cache::forget('import_in_progress');
                Log::info('Import lock released', ['file' => $fileName]);
            }
        }
    }

    /**
     * Display command header
     */
    private function displayHeader($isDryRun, $fileName)
    {
        $this->info('═══════════════════════════════════════');
        $this->info('        USER IMPORT UTILITY');
        $this->info('═══════════════════════════════════════');
        $this->line("Mode: " . ($isDryRun ? '🧪 DRY RUN' : '🚀 LIVE IMPORT'));
        $this->line("File: {$fileName}");
        $this->line("Time: " . now()->format('Y-m-d H:i:s'));
        $this->info('═══════════════════════════════════════');
    }

    /**
     * Pre-validate the import file
     */
    private function preValidateImportFile($filePath)
    {
        try {
            $data = Excel::toArray(new class {}, $filePath);
            
            if (empty($data[0])) {
                return ['valid' => false, 'message' => 'File appears to be empty'];
            }

            $rows = $data[0];
            if (count($rows) < 2) {
                return ['valid' => false, 'message' => 'File must have header row and at least one data row'];
            }

            // Process headers
            $headers = array_change_key_case($rows[0] ?? [], CASE_LOWER);
            $headerKeys = array_keys($headers);
            
            // Check for required columns
            $requiredHeaders = ['userprincipalname', 'displayname'];
            $missingHeaders = array_filter($requiredHeaders, function($header) use ($headerKeys) {
                return !in_array($header, $headerKeys);
            });

            if (!empty($missingHeaders)) {
                return [
                    'valid' => false, 
                    'message' => 'Missing required columns: ' . implode(', ', $missingHeaders) . 
                               '. Available columns: ' . implode(', ', $headerKeys)
                ];
            }

            // Analyze data
            $analysis = $this->analyzeFileData($rows, $headerKeys);

            return array_merge(['valid' => true], $analysis);

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => 'Failed to read file: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Analyze file data
     */
    private function analyzeFileData($rows, $headerKeys)
    {
        $uniqueUsers = [];
        $duplicatesInFile = [];
        $emptyUsers = 0;
        $sampleUsers = [];
        $totalRows = count($rows) - 1;

        // Analyze each row
        for ($i = 1; $i < count($rows); $i++) {
            if (count($rows[$i]) < count($headerKeys)) {
                continue; // Skip malformed rows
            }

            $row = array_combine($headerKeys, array_slice($rows[$i], 0, count($headerKeys)));
            $userPrincipalName = trim($row['userprincipalname'] ?? '');
            $displayName = trim($row['displayname'] ?? '');
            
            if (empty($userPrincipalName)) {
                $emptyUsers++;
                continue;
            }

            $key = strtolower($userPrincipalName);
            
            if (isset($uniqueUsers[$key])) {
                // Track duplicates within the file
                if (!isset($duplicatesInFile[$key])) {
                    $duplicatesInFile[$key] = 1;
                }
                $duplicatesInFile[$key]++;
            } else {
                $uniqueUsers[$key] = $userPrincipalName;
                
                // Collect sample data
                if (count($sampleUsers) < 5) {
                    $sampleUsers[] = [
                        'userPrincipalName' => $userPrincipalName,
                        'displayName' => $displayName,
                        'row' => $i + 1
                    ];
                }
            }
        }

        return [
            'total_rows' => $totalRows,
            'unique_count' => count($uniqueUsers),
            'empty_users' => $emptyUsers,
            'file_duplicates' => $duplicatesInFile,
            'sample_users' => $sampleUsers,
            'headers' => $headerKeys
        ];
    }

    /**
     * Display file analysis results
     */
    private function displayFileAnalysis($validation, $expectedCount)
    {
        $this->info("\n📊 FILE ANALYSIS RESULTS:");
        $this->line("  📄 Total data rows: {$validation['total_rows']}");
        $this->line("  👤 Unique users: {$validation['unique_count']}");
        $this->line("  🎯 Expected users: {$expectedCount}");
        $this->line("  📋 Available columns: " . implode(', ', $validation['headers']));

        if ($validation['empty_users'] > 0) {
            $this->line("  ⚠️  Empty userPrincipalName rows: {$validation['empty_users']} (will be skipped)");
        }

        if (!empty($validation['file_duplicates'])) {
            $this->line("  🔄 Duplicates within file: " . count($validation['file_duplicates']) . " userPrincipalNames");
        }
    }

    /**
     * Check for potential issues
     */
    private function checkForIssues($validation, $expectedCount)
    {
        $issues = [];

        if ($validation['unique_count'] > ($expectedCount * 1.3)) {
            $issues[] = "File contains {$validation['unique_count']} unique users, significantly more than expected {$expectedCount}";
        }

        if ($validation['unique_count'] < ($expectedCount * 0.7)) {
            $issues[] = "File contains {$validation['unique_count']} unique users, significantly less than expected {$expectedCount}";
        }

        if (!empty($validation['file_duplicates'])) {
            $totalDuplicates = array_sum($validation['file_duplicates']);
            $issues[] = "File contains {$totalDuplicates} duplicate entries that will be merged";
        }

        if ($validation['empty_users'] > ($validation['total_rows'] * 0.1)) {
            $issues[] = "High number of empty userPrincipalName entries: {$validation['empty_users']}";
        }

        return $issues;
    }

    /**
     * Display issues found
     */
    private function displayIssues($issues)
    {
        $this->warn("\n⚠️  POTENTIAL ISSUES DETECTED:");
        foreach ($issues as $issue) {
            $this->line("  • {$issue}");
        }
    }

    /**
     * Display dry run results
     */
    private function displayDryRunResults($validation)
    {
        $this->info("\n✅ DRY RUN COMPLETED SUCCESSFULLY");
        
        $this->line("\n📋 Sample users that would be imported:");
        foreach ($validation['sample_users'] as $user) {
            $this->line("  • {$user['userPrincipalName']} → {$user['displayName']} (row {$user['row']})");
        }

        if (!empty($validation['file_duplicates'])) {
            $this->line("\n🔄 File duplicates that would be merged:");
            $count = 0;
            foreach ($validation['file_duplicates'] as $userPrincipalName => $duplicateCount) {
                if ($count++ < 3) { // Show first 3
                    $actualUser = $validation['unique_users'][$userPrincipalName] ?? $userPrincipalName;
                    $this->line("  • {$actualUser} appears {$duplicateCount} times");
                }
            }
            if (count($validation['file_duplicates']) > 3) {
                $remaining = count($validation['file_duplicates']) - 3;
                $this->line("  ... and {$remaining} more");
            }
        }

        $this->info("\n🚀 Run without --dry-run to perform actual import");
    }

    /**
     * Perform the actual import
     */
    private function performImport($filePath, $validation, $currentDbCount, $expectedCount)
    {
        $this->info("\n🚀 STARTING IMPORT...");
        
        $startTime = microtime(true);

        Log::info('Starting user import', [
            'file' => basename($filePath),
            'unique_users_in_file' => $validation['unique_count'],
            'current_db_count' => $currentDbCount,
            'expected_count' => $expectedCount
        ]);

        // Create progress bar
        $progressBar = $this->output->createProgressBar($validation['unique_count']);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        
        try {
            Excel::import(new UsersImport, $filePath);
            $progressBar->finish();
            $this->newLine();

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            // Post-import analysis
            $this->analyzeImportResults($currentDbCount, $validation, $duration);

        } catch (\Exception $e) {
            $progressBar->finish();
            $this->newLine();
            throw $e;
        }
    }

    /**
     * Analyze import results
     */
    private function analyzeImportResults($currentDbCount, $validation, $duration)
    {
        $newDbCount = AzureUser::count();
        $actualImported = $newDbCount - $currentDbCount;
        $updated = $validation['unique_count'] - $actualImported;

        $this->info("\n🎉 IMPORT COMPLETED!");
        $this->info("📊 RESULTS SUMMARY:");
        $this->line("  ⏱️  Duration: {$duration} seconds");
        $this->line("  📈 Users before: {$currentDbCount}");
        $this->line("  📈 Users after: {$newDbCount}");
        $this->line("  ➕ New users created: {$actualImported}");
        $this->line("  🔄 Existing users updated: {$updated}");

        // Check for unexpected results
        if ($newDbCount > ($currentDbCount + $validation['unique_count'] * 1.1)) {
            $this->warn("\n⚠️  WARNING: Database has more users than expected!");
            $this->warn("This might indicate duplicate creation. Run duplicate cleanup:");
            $this->line("php artisan users:cleanup-duplicates --dry-run");
        } else {
            $this->info("\n✅ Import completed successfully with expected results!");
        }

        Log::info('Import completed successfully', [
            'duration_seconds' => $duration,
            'users_created' => $actualImported,
            'users_updated' => $updated,
            'total_users' => $newDbCount
        ]);
    }

    /**
     * List available files in storage directory
     */
    private function listStorageFiles()
    {
        try {
            $files = Storage::disk('local')->files('/');
            $csvFiles = array_filter($files, function($file) {
                return pathinfo($file, PATHINFO_EXTENSION) === 'csv';
            });

            if (empty($csvFiles)) {
                $this->line('  No CSV files found');
            } else {
                foreach ($csvFiles as $file) {
                    $this->line("  • {$file}");
                }
            }
        } catch (\Exception $e) {
            $this->line('  Unable to list files: ' . $e->getMessage());
        }
    }
}