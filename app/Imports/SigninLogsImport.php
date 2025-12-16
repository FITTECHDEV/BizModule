<?php

namespace App\Imports;

use App\Models\Logintracker\SigninLog;
use App\Models\Logintracker\System;
use App\Models\Logintracker\AzureUser;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Traits\UuidConversion;

class SigninLogsImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    private $userCache = [];
    // private $systemCache = [];
    private $existingLogs = [];

    /** Import locking mechanism */
    private static $lockKey = 'signin_logs_import_lock';
    private static $lockTimeout = 3600;
    private static $progressKey = 'signin_import_progress';

    /** SQL Server parameter limits - more conservative */
    private int $maxParams = 2100;
    private int $paramBuffer = 500;

    /** Performance settings */
    private bool $useBulkInsert = false;
    private int $bulkThreshold = 50;
    private string $tempDir;

    /** Table structure cache */
    private ?array $tableColumns = null;
    private ?array $requiredColumns = null;

    /**
     * Add validation error tracking property at the top of your class
     */
    private array $validationErrors = [];



    /**
     * Get validation errors for reporting
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors ?? [];
    }


    /** Statistics tracking */
    private array $stats = [
        'processed' => 0,
        'inserted' => 0,
        'skipped' => 0,
        'errors' => 0,
        'bulk_operations' => 0,
        'single_row_fallbacks' => 0,
        'users_created' => 0,
        'users_found' => 0,
        'users_id_generated' => 0, // ADDED THIS LINE
        'users_matched_by_strategy' => [
            'exact_id' => 0,
            'username' => 0,
            'email' => 0,
            'sign_in_identifier' => 0,
            'created_new' => 0
        ]
    ];

    /** Memory management */
    private int $cacheCleanupInterval = 1000;

    use UuidConversion;

    public function isLockOwner(string $lockKey, array $context = []): bool
    {
        $lockData = Cache::get($lockKey);

        return isset($lockData['process']) && $lockData['process'] === (getmypid() ?: 'unknown');
    }

    public function __construct()
    {
        $this->tempDir = sys_get_temp_dir();
        $this->useBulkInsert = env('SIGNIN_USE_BULK_INSERT', false);
        $this->bulkThreshold = env('SIGNIN_BULK_THRESHOLD', 20);
        $this->paramBuffer = env('SIGNIN_IMPORT_PARAM_BUFFER', 500);

        $this->checkAndSetLock();
        $this->validateConfiguration();
        $this->initializeProgress();
        $this->initializeTableStructure();
        // REMOVED: $this->preloadSystemCache();  // No longer needed

        Log::info('SigninLogsImport initialized', [
            'bulk_insert_enabled' => $this->useBulkInsert,
            'bulk_threshold' => $this->bulkThreshold,
            'lock_key' => self::$lockKey
        ]);
    }

    private function checkAndSetLock(): void
    {
        if (Cache::has(self::$lockKey)) {
            $lockData = Cache::get(self::$lockKey);

            if (
                isset($lockData['started_at']) &&
                Carbon::parse($lockData['started_at'])->addSeconds(self::$lockTimeout)->isPast()
            ) {

                Log::warning('Found stale import lock, clearing it', [
                    'lock_data' => $lockData,
                    'lock_age_minutes' => Carbon::parse($lockData['started_at'])->diffInMinutes()
                ]);

                Cache::forget(self::$lockKey);
                Cache::forget(self::$progressKey);
            } else {
                throw new \Exception('Another import is already in progress. Please wait and try again.');
            }
        }

        $lockData = [
            'started_at' => now()->toISOString(),
            'process_id' => getmypid(),
            'server' => gethostname(),
            'process' => getmypid() ?: 'unknown'
        ];

        Cache::put(self::$lockKey, $lockData, self::$lockTimeout);
        Log::info('Import lock acquired', $lockData);
    }

    private function initializeProgress(): void
    {
        Cache::put(self::$progressKey, [
            'started_at' => now()->toISOString(),
            'processed' => 0,
            'total' => 0,
            'status' => 'initializing',
            'current_batch' => 0,
            'total_batches' => 0,
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
        ], self::$lockTimeout);
    }

    private function updateProgress(array $updates): void
    {
        $current = Cache::get(self::$progressKey, []);
        $updated = array_merge($current, $updates, [
            'updated_at' => now()->toISOString(),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
        ]);

        Cache::put(self::$progressKey, $updated, self::$lockTimeout);
    }

    private function validateConfiguration(): void
    {
        $configs = [
            'DB_CONNECTION' => env('DB_CONNECTION'),
            'SIGNIN_EXCEL_CHUNK_SIZE' => env('SIGNIN_EXCEL_CHUNK_SIZE', 200)
        ];

        foreach ($configs as $key => $value) {
            if (empty($value)) {
                Log::warning("Configuration potentially missing: {$key}");
            }
        }

        try {
            DB::connection()->getPdo();
            Log::info('Database connection validated');
        } catch (\Exception $e) {
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * CRITICAL FIX: Enhanced user resolution with better null ID handling
     */
    private function getUserFromCacheOrCreate(string $userPrincipalName, ?string $userDisplayName = null, ?string $userIdFromCsv = null)
    {
        $userPrincipalName = trim($userPrincipalName);

        if (empty($userPrincipalName) || strlen($userPrincipalName) > 255) {
            Log::warning('Invalid username provided', [
                'username' => substr($userPrincipalName, 0, 50),
                'length' => strlen($userPrincipalName)
            ]);
            $this->stats['errors']++;
            return null;
        }

        // Check cache first
        $cacheKey = strtolower($userPrincipalName);
        if (isset($this->userCache[$cacheKey])) {
            $user = $this->userCache[$cacheKey];

            // CRITICAL: Ensure user has valid ID
            if (empty($user->id)) {
                Log::warning('Cached user has no ID, removing from cache and re-resolving', [
                    'username' => $userPrincipalName
                ]);
                unset($this->userCache[$cacheKey]);
            } else {
                $this->stats['users_found']++;
                return $user;
            }
        }

        // Try to find user with comprehensive database check
        $user = $this->findUserWithValidation($userPrincipalName, $userIdFromCsv);

        if ($user && !empty($user->id)) {
            // Cache only users with valid IDs
            $this->userCache[$cacheKey] = $user;
            $this->stats['users_found']++;
            return $user;
        }

        // Create new user if enabled
        if (env('SIGNIN_IMPORT_CREATE_USERS', false)) {
            $newUser = $this->createUserFromSigninData($userPrincipalName, $userDisplayName, $userIdFromCsv);
            if ($newUser && !empty($newUser->id)) {
                $this->userCache[$cacheKey] = $newUser;
                return $newUser;
            }
        }

        Log::warning('No valid user found and creation disabled/failed', [
            'username' => $userPrincipalName,
            'create_users_enabled' => env('SIGNIN_IMPORT_CREATE_USERS', false)
        ]);

        return null;
    }
    /**
     * CRITICAL METHOD: Fixes users with null IDs by generating new UUIDs
     */
    private function fixUserNullId($user)
    {
        if (!$user) {
            return null;
        }

        if (!empty($user->id)) {
            return $user; // Already has valid ID
        }

        try {
            // Generate new UUID
            $newId = (string) Str::uuid();

            // Update the user record in database
            $updated = DB::table('Azure_Users')
                ->where('userPrincipalName', $user->userPrincipalName)
                ->update(['id' => $newId]);

            if ($updated) {
                // Update the model instance
                $user->id = $newId;

                Log::info('✅ Generated ID for user', [
                    'username' => $user->userPrincipalName,
                    'new_id' => $newId
                ]);

                // Track this fix
                $this->stats['users_id_generated'] = ($this->stats['users_id_generated'] ?? 0) + 1;

                return $user;
            } else {
                Log::error('❌ Failed to update user ID in database', [
                    'username' => $user->userPrincipalName
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('❌ Exception while fixing user ID', [
                'username' => $user->userPrincipalName ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }



    /**
     * CRITICAL NEW METHOD: Ensures user has valid ID, generates one if missing
     */
    private function ensureUserHasValidId($user)
    {
        if (!empty($user->id)) {
            return $user; // Already has valid ID
        }

        try {
            // Generate new UUID
            $newId = (string) Str::uuid();

            // Update the user record in database
            DB::table('Azure_Users')
                ->where('userPrincipalName', $user->userPrincipalName)
                ->update(['id' => $newId]);

            // Update the model instance
            $user->id = $newId;
            $user->refresh(); // Reload from database to ensure consistency

            Log::info('Successfully generated ID for existing user', [
                'username' => $user->userPrincipalName,
                'new_id' => $newId,
                'display_name' => $user->displayName ?? 'unknown'
            ]);

            // Track this as a user update
            $this->stats['users_id_generated'] = ($this->stats['users_id_generated'] ?? 0) + 1;

            return $user;
        } catch (\Exception $e) {
            Log::error('Failed to generate ID for user', [
                'username' => $user->userPrincipalName ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => substr($e->getTraceAsString(), 0, 300)
            ]);

            // Return user as-is, let validation handle the null ID
            return $user;
        }
    }


    /**
     * COMPLETELY REWRITTEN findUserByMultipleStrategies method
     * Optimized for SQL Server with proper case-insensitive handling
     */
    private function findUserByMultipleStrategies(string $userPrincipalName, ?string $userIdFromCsv = null)
    {
        $userPrincipalName = trim($userPrincipalName);

        if (empty($userPrincipalName)) {
            Log::warning('Empty userPrincipalName provided for user lookup');
            return null;
        }

        try {
            // Strategy 1: Exact ID match (if provided)
            if (!empty($userIdFromCsv) && $this->isValidUuid($userIdFromCsv)) {
                try {
                    $user = AzureUser::where('id', $userIdFromCsv)->first();
                    if ($user) {
                        $this->stats['users_matched_by_strategy']['exact_id']++;
                        Log::debug("User found via exact ID match", [
                            'username' => $userPrincipalName,
                            'user_id' => $user->id,
                            'strategy' => 'exact_id'
                        ]);
                        return $user;
                    }
                } catch (\Exception $e) {
                    Log::warning('Exact ID lookup failed', [
                        'user_id' => $userIdFromCsv,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Strategy 2: Direct case-insensitive userPrincipalName match
            // Using COLLATE for SQL Server case-insensitive comparison
            try {
                $user = AzureUser::whereRaw('LOWER(RTRIM(LTRIM(userPrincipalName))) = LOWER(?)', [trim($userPrincipalName)])->first();
                if ($user) {
                    $this->stats['users_matched_by_strategy']['username']++;
                    Log::debug("User found via case-insensitive username match", [
                        'search_term' => $userPrincipalName,
                        'found_username' => $user->userPrincipalName,
                        'user_id' => $user->id,
                        'strategy' => 'username_case_insensitive'
                    ]);
                    return $user;
                }
            } catch (\Exception $e) {
                Log::warning('Case-insensitive username lookup failed', [
                    'username' => $userPrincipalName,
                    'error' => $e->getMessage()
                ]);
            }

            // Strategy 3: Email/Mail field match (if it looks like an email)
            if (filter_var($userPrincipalName, FILTER_VALIDATE_EMAIL)) {
                try {
                    $user = AzureUser::whereRaw('LOWER(RTRIM(LTRIM(mail))) = LOWER(?)', [trim($userPrincipalName)])
                        ->orWhereRaw('LOWER(RTRIM(LTRIM(alternateEmailAddress))) = LOWER(?)', [trim($userPrincipalName)])
                        ->first();
                    if ($user) {
                        $this->stats['users_matched_by_strategy']['email']++;
                        Log::debug("User found via email match", [
                            'email' => $userPrincipalName,
                            'found_username' => $user->userPrincipalName,
                            'user_id' => $user->id,
                            'strategy' => 'email_match'
                        ]);
                        return $user;
                    }
                } catch (\Exception $e) {
                    Log::warning('Email lookup failed', [
                        'email' => $userPrincipalName,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Strategy 4: Fallback - try a broader search with LIKE operator
            try {
                $searchPattern = '%' . trim($userPrincipalName) . '%';
                $user = AzureUser::whereRaw('LOWER(userPrincipalName) LIKE LOWER(?)', [$searchPattern])
                    ->orWhereRaw('LOWER(mail) LIKE LOWER(?)', [$searchPattern])
                    ->first();
                if ($user) {
                    $this->stats['users_matched_by_strategy']['sign_in_identifier']++;
                    Log::info("User found via pattern match", [
                        'search_term' => $userPrincipalName,
                        'pattern' => $searchPattern,
                        'found_username' => $user->userPrincipalName,
                        'user_id' => $user->id,
                        'strategy' => 'pattern_match'
                    ]);
                    return $user;
                }
            } catch (\Exception $e) {
                Log::warning('Pattern matching lookup failed', [
                    'username' => $userPrincipalName,
                    'error' => $e->getMessage()
                ]);
            }

            // Log the failed lookup attempt with available users for debugging
            $this->logFailedUserLookup($userPrincipalName, $userIdFromCsv);
        } catch (\Exception $e) {
            Log::error('Critical error in user lookup strategies', [
                'username' => $userPrincipalName,
                'user_id_from_csv' => $userIdFromCsv,
                'error' => $e->getMessage(),
                'trace' => substr($e->getTraceAsString(), 0, 500)
            ]);
        }

        return null;
    }
    private function createUserFromSigninData(string $userPrincipalName, ?string $userDisplayName = null, ?string $userIdFromCsv = null)
    {
        try {
            // Enhanced validation
            if (strlen($userPrincipalName) > 255) {
                Log::warning('Username too long', ['username' => substr($userPrincipalName, 0, 50)]);
                return null;
            }

            // Check for obviously invalid usernames
            if (in_array(strtolower($userPrincipalName), ['null', 'undefined', 'n/a', '', 'unknown'])) {
                Log::warning('Invalid username detected', ['username' => $userPrincipalName]);
                return null;
            }

            // Generate consistent Azure ID
            $azureId = null;
            if (!empty($userIdFromCsv) && $this->isValidUuid($userIdFromCsv)) {
                $azureId = $userIdFromCsv;
            } else {
                $azureId = (string) Str::uuid();
            }

            $userData = [
                'id' => $azureId,
                'azure_id' => $azureId,
                'userPrincipalName' => $userPrincipalName,
                'displayName' => $this->cleanDisplayName($userDisplayName, $userPrincipalName),
                'mail' => $this->extractEmailFromUsername($userPrincipalName),
                'accountEnabled' => true,
                'userType' => 'Member',
                'companyName' => 'Freight in Time',
                'createdDateTime' => now(),
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ];

            // Use updateOrCreate to handle race conditions
            $user = AzureUser::updateOrCreate(
                ['userPrincipalName' => $userPrincipalName],
                $userData
            );

            $this->userCache[$userPrincipalName] = $user;

            if ($user->wasRecentlyCreated) {
                $this->stats['users_created']++;
                $this->stats['users_matched_by_strategy']['created_new']++;
                Log::info('Created new user from signin log', [
                    'username' => $userPrincipalName,
                    'user_id' => $user->id,
                    'display_name' => $userData['displayName']
                ]);
            } else {
                $this->stats['users_found']++;
                $this->stats['users_matched_by_strategy']['username']++;
                Log::debug('Found existing user via updateOrCreate', [
                    'username' => $userPrincipalName,
                    'user_id' => $user->id
                ]);
            }

            return $user;
        } catch (\Exception $e) {
            Log::error('Failed to create user from signin log', [
                'username' => $userPrincipalName,
                'error' => $e->getMessage(),
                'trace' => substr($e->getTraceAsString(), 0, 300)
            ]);
            $this->stats['errors']++;
            return null;
        }
    }

    private function cleanDisplayName(?string $displayName, string $userPrincipalName): string
    {
        if (!empty($displayName) && $displayName !== $userPrincipalName) {
            return substr(trim($displayName), 0, 255);
        }

        $emailPart = strpos($userPrincipalName, '@') !== false
            ? substr($userPrincipalName, 0, strpos($userPrincipalName, '@'))
            : $userPrincipalName;

        $displayName = str_replace(['.', '_', '-'], ' ', $emailPart);
        $displayName = ucwords(strtolower($displayName));

        return substr($displayName, 0, 255);
    }

    private function extractEmailFromUsername(string $userPrincipalName): ?string
    {
        if (filter_var($userPrincipalName, FILTER_VALIDATE_EMAIL)) {
            return substr($userPrincipalName, 0, 255);
        }
        return null;
    }

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            Log::warning('Empty collection provided to SigninLogsImport');
            return;
        }

        $lockService = app(\App\Services\ImportLockService::class);
        $context = $this->getLockContext();

        try {
            if (!$lockService->acquireLock(self::$lockKey, self::$lockTimeout, $context)) {
                throw new \Exception('Another import is already running. Please wait and try again.');
            }

            $this->processCollectionBatches($rows);
        } catch (\Exception $e) {
            Log::error('Import failed with critical error', [
                'error' => $e->getMessage(),
                'stats' => $this->stats,
                'trace' => substr($e->getTraceAsString(), 0, 500)
            ]);

            $this->updateProgress([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'final_stats' => $this->stats
            ]);

            $this->cleanup();
            throw $e;
        } finally {
            if ($lockService->isLockOwner(self::$lockKey, $context)) {
                $lockService->releaseLock(self::$lockKey, $context);
            }
            $this->logFinalStats();
        }
    }

    private function processCollectionBatches(Collection $collection)
    {
        $totalRows = $collection->count();
        $chunkSize = $this->calculateOptimalChunkSize();
        $chunks = $collection->chunk($chunkSize);
        $batchNumber = 0;
        $consecutiveFailures = 0;
        $maxConsecutiveFailures = 5;

        Log::info('Starting signin logs import', [
            'total_rows' => $totalRows,
            'chunk_size' => $chunkSize,
            'total_batches' => $chunks->count()
        ]);

        $this->updateProgress([
            'total' => $totalRows,
            'total_batches' => $chunks->count(),
            'chunk_size' => $chunkSize,
            'status' => 'processing'
        ]);

        foreach ($chunks as $chunkIndex => $chunk) {
            $batchNumber++;
            $startTime = microtime(true);

            try {
                $this->processBatch($chunk, $batchNumber);
                $consecutiveFailures = 0; // Reset failure counter on success

                $duration = microtime(true) - $startTime;
                Log::info("Batch {$batchNumber} completed successfully", [
                    'chunk_size' => $chunk->count(),
                    'duration_seconds' => round($duration, 2),
                    'rows_per_second' => $duration > 0 ? round($chunk->count() / $duration, 2) : 0,
                    'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                    'cumulative_stats' => [
                        'processed' => $this->stats['processed'],
                        'inserted' => $this->stats['inserted'],
                        'skipped' => $this->stats['skipped'],
                        'errors' => $this->stats['errors']
                    ]
                ]);

                // Periodic maintenance
                if ($batchNumber % 10 === 0) {
                    $this->cleanupCaches();
                    Log::info("Periodic maintenance after batch {$batchNumber}", [
                        'cache_cleanup' => 'completed',
                        'stats' => $this->stats,
                        'memory_after_cleanup_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
                    ]);
                }

                $this->updateProgress([
                    'current_batch' => $batchNumber,
                    'status' => 'processing_batch_' . $batchNumber,
                    'processed' => $this->stats['processed'],
                    'inserted' => $this->stats['inserted'],
                    'completion_percentage' => round(($batchNumber / $chunks->count()) * 100, 2)
                ]);
            } catch (\Exception $e) {
                $consecutiveFailures++;

                Log::error("Batch {$batchNumber} failed completely", [
                    'error' => $e->getMessage(),
                    'consecutive_failures' => $consecutiveFailures,
                    'chunk_index' => $chunkIndex,
                    'rows_in_batch' => $chunk->count(),
                    'current_stats' => $this->stats,
                    'trace' => substr($e->getTraceAsString(), 0, 500)
                ]);

                // If too many consecutive failures, abort the import
                if ($consecutiveFailures >= $maxConsecutiveFailures) {
                    Log::critical("Too many consecutive batch failures, aborting import", [
                        'consecutive_failures' => $consecutiveFailures,
                        'max_allowed' => $maxConsecutiveFailures,
                        'final_stats' => $this->stats
                    ]);

                    $this->updateProgress([
                        'status' => 'aborted',
                        'reason' => 'Too many consecutive failures',
                        'final_stats' => $this->stats
                    ]);

                    throw new \Exception("Import aborted due to {$consecutiveFailures} consecutive batch failures");
                }

                // Mark all rows in this batch as errors
                $this->stats['errors'] += $chunk->count();

                // Update progress to reflect the failure
                $this->updateProgress([
                    'current_batch' => $batchNumber,
                    'status' => 'batch_failed_' . $batchNumber,
                    'last_error' => $e->getMessage(),
                    'consecutive_failures' => $consecutiveFailures
                ]);

                continue; // Continue with next batch
            }
        }

        // Invalidate dashboard cache after successful import
        $this->invalidateDashboardCache();

        Log::info('Import processing completed', [
            'total_batches_processed' => $batchNumber,
            'final_stats' => $this->stats
        ]);

        $this->updateProgress([
            'status' => 'completed',
            'final_stats' => $this->stats,
            'completion_percentage' => 100
        ]);
    }


    private function processBatch(Collection $batch, int $batchNumber): void
    {
        if ($batch->isEmpty()) {
            Log::info("Batch {$batchNumber} skipped: no rows to process.");
            return;
        }

        $startTime = microtime(true);
        $duplicateCheckData = [];
        $signinLogsToInsert = [];
        $insertedCount = 0;

        // CRITICAL DEBUG: Log batch structure
        Log::info("🔍 BATCH DEBUG INFO", [
            'batch' => $batchNumber,
            'rows' => $batch->count(),
            'first_row_keys' => $batch->first() ? array_keys($batch->first()->toArray()) : [],
            'first_row_sample' => $batch->first() ? array_slice($batch->first()->toArray(), 0, 5, true) : [],
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
        ]);

        DB::beginTransaction();
        try {
            DB::statement("SET TRANSACTION ISOLATION LEVEL READ COMMITTED");
            $this->preloadData($batch);

            // REMOVED: User system associations array
            // $userSystemAssociations = [];
            $processedRowsInBatch = 0;
            $skippedRowsInBatch = 0;

            foreach ($batch as $rowIndex => $row) {
                $normalizedRow = array_change_key_case($row->toArray(), CASE_LOWER);

                // CRITICAL DEBUG: Log first row processing attempt
                if ($rowIndex === 0) {
                    Log::info("🔍 PROCESSING FIRST ROW", [
                        'batch' => $batchNumber,
                        'normalized_keys' => array_keys($normalizedRow),
                        'has_username' => isset($normalizedRow['username']),
                        'has_date' => isset($normalizedRow['date_utc']) || isset($normalizedRow['date (utc)']),
                        'username_value' => $normalizedRow['username'] ?? 'MISSING'
                    ]);
                }

                $result = $this->processRowSafely($normalizedRow, $batchNumber, $rowIndex);

                if ($result['signin_log']) {
                    $signinLogsToInsert[] = $result['signin_log'];
                    $duplicateCheckData[] = [
                        'username' => $result['signin_log']['username'],
                        'date_utc' => $result['signin_log']['date_utc'],
                        'request_id' => $result['signin_log']['request_id']
                    ];
                    $processedRowsInBatch++;
                } else {
                    $skippedRowsInBatch++;
                }

                // REMOVED: User system association collection
                // if ($result['user_system']) {
                //     $userSystemAssociations[] = $result['user_system'];
                // }

                $this->stats['processed']++;
            }

            Log::info("📊 BATCH PROCESSING SUMMARY", [
                'batch' => $batchNumber,
                'total_rows' => $batch->count(),
                'processed_successfully' => $processedRowsInBatch,
                'skipped' => $skippedRowsInBatch,
                'signin_logs_ready_for_insert' => count($signinLogsToInsert)
            ]);

            if (!empty($signinLogsToInsert)) {
                $this->preloadExistingLogs($duplicateCheckData);
                $insertedCount = $this->executeOptimalInsert($signinLogsToInsert, $batchNumber);
                $this->stats['inserted'] += $insertedCount;

                // REMOVED: User system associations bulk insert
                // $this->bulkInsertUserSystemAssociations($userSystemAssociations);
            } else {
                Log::warning("⚠️ NO SIGNIN LOGS TO INSERT", [
                    'batch' => $batchNumber,
                    'rows_in_batch' => $batch->count(),
                    'processed_count' => $processedRowsInBatch,
                    'skipped_count' => $skippedRowsInBatch
                ]);
            }

            DB::commit();

            $duration = microtime(true) - $startTime;
            Log::info("Batch {$batchNumber} completed", [
                'signin_logs_processed' => count($signinLogsToInsert),
                'signin_logs_inserted' => $insertedCount,
                'duration_seconds' => round($duration, 2)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Batch {$batchNumber} failed", [
                'error' => $e->getMessage(),
                'trace' => substr($e->getTraceAsString(), 0, 500),
                'stats' => $this->stats
            ]);
            $this->stats['errors'] += $batch->count();
            return;
        }

        $this->updateProgress([
            'processed' => $this->stats['processed'],
            'inserted' => $this->stats['inserted'],
            'current_batch' => $batchNumber
        ]);
    }

    private function processRow(array $row, int $batchNumber = 0, int $rowIndex = 0)
    {
        // CRITICAL FIX 1: Log the actual row data for first few rows
        if ($rowIndex < 3) {
            Log::debug("🔍 RAW ROW DATA", [
                'batch' => $batchNumber,
                'row' => $rowIndex,
                'raw_keys' => array_keys($row),
                'sample_values' => array_map(function ($v) {
                    return is_string($v) ? substr($v, 0, 50) : $v;
                }, array_slice($row, 0, 10, true))
            ]);
        }

        $userPrincipalName = trim($row['username'] ?? $row['user_principal_name'] ?? '');
        $userDisplayName = trim($row['user'] ?? $row['display_name'] ?? '');
        $userIdFromCsv = trim($row['user id'] ?? $row['user_id'] ?? $row['userid'] ?? '');

        if (empty($userPrincipalName) || strlen($userPrincipalName) > 255) {
            Log::warning('Invalid username in row', [
                'batch' => $batchNumber,
                'row' => $rowIndex,
                'username' => substr($userPrincipalName, 0, 50),
                'length' => strlen($userPrincipalName)
            ]);
            $this->stats['skipped']++;
            return ['signin_log' => null, 'user_system' => null];
        }

        // CRITICAL FIX 2: Get user and FIX null ID immediately
        $user = $this->getUserFromCacheOrCreate($userPrincipalName, $userDisplayName, $userIdFromCsv);

        // CRITICAL FIX 3: If user exists but has null ID, fix it NOW
        if ($user && empty($user->id)) {
            Log::warning('⚠️ User has NULL ID, attempting to fix', [
                'batch' => $batchNumber,
                'row' => $rowIndex,
                'username' => $userPrincipalName
            ]);

            $user = $this->fixUserNullId($user);
        }

        // CRITICAL FIX 4: Only skip if user is completely unavailable
        if (!$user || empty($user->id)) {
            Log::error('❌ No valid user after all attempts', [
                'batch' => $batchNumber,
                'row' => $rowIndex,
                'username' => $userPrincipalName,
                'user_exists' => $user !== null,
                'user_has_id' => $user ? !empty($user->id) : false
            ]);
            $this->stats['skipped']++;
            return ['signin_log' => null, 'user_system' => null];
        }

        // REMOVED: System mapping logic - no longer needed
        // $application = $row['application'] ?? $row['app'] ?? null;
        // $system = $this->mapApplicationToSystem($application);

        // CRITICAL FIX 5: More flexible date extraction
        $dateUtc = $this->extractDateUtc($row);
        if (!$dateUtc) {
            // Try common variations
            $dateUtc = $row['Date (UTC)'] ?? $row['Date'] ?? $row['date'] ?? null;

            Log::warning('⚠️ Date field not found with standard keys', [
                'batch' => $batchNumber,
                'row' => $rowIndex,
                'username' => $userPrincipalName,
                'available_keys' => array_keys($row),
                'keys_containing_date' => array_filter(array_keys($row), function ($k) {
                    return stripos($k, 'date') !== false;
                })
            ]);
        }

        if (!$dateUtc) {
            $this->stats['skipped']++;
            return ['signin_log' => null, 'user_system' => null];
        }

        $parsedDate = $this->validateAndParseDate($dateUtc, $userPrincipalName);
        if (!$parsedDate) {
            Log::warning('Failed to parse date', [
                'batch' => $batchNumber,
                'row' => $rowIndex,
                'username' => $userPrincipalName,
                'raw_date' => substr($dateUtc, 0, 50)
            ]);
            $this->stats['skipped']++;
            return ['signin_log' => null, 'user_system' => null];
        }

        // CRITICAL FIX 6: More flexible request_id extraction
        $requestId = $row['request_id'] ?? $row['request id'] ?? $row['requestid'] ??
            $row['Request ID'] ?? $row['RequestId'] ?? null;

        if (empty($requestId)) {
            $requestId = 'IMPORT-' . uniqid() . '-' . $batchNumber . '-' . $rowIndex;
        }

        try {
            // UPDATED: Removed $system parameter
            $signinLogData = $this->prepareSigninLogData($row, $user, $parsedDate, $userIdFromCsv);
            $signinLogData['request_id'] = $requestId;

            // CRITICAL FIX 7: Log successful preparation
            if ($rowIndex < 3) {
                Log::info('✅ Row prepared successfully', [
                    'batch' => $batchNumber,
                    'row' => $rowIndex,
                    'username' => $userPrincipalName,
                    'user_id' => $user->id,
                    'date' => $parsedDate,
                    'request_id' => $requestId
                ]);
            }

            if ($this->isDuplicateInDatabase($signinLogData)) {
                Log::debug('Duplicate signin log detected', [
                    'batch' => $batchNumber,
                    'row' => $rowIndex,
                    'username' => $userPrincipalName
                ]);
                $this->stats['skipped']++;
                return ['signin_log' => null, 'user_system' => null];
            }

            if (!$this->validateSigninLogData($signinLogData, $batchNumber, $rowIndex)) {
                $this->stats['skipped']++;
                return ['signin_log' => null, 'user_system' => null];
            }

            // REMOVED: System association logic - no longer needed
            // $userSystemAssociation = null;
            // if ($system !== 'Unknown' && isset($this->systemCache[$system])) {
            //     $userSystemAssociation = [
            //         'user_id' => $user->id,
            //         'application_id' => (string) $this->systemCache[$system]->id,
            //     ];
            // }

            return [
                'signin_log' => $signinLogData,
                'user_system' => null  // No longer creating system associations
            ];
        } catch (\Exception $e) {
            Log::error('❌ Error preparing signin log data', [
                'batch' => $batchNumber,
                'row' => $rowIndex,
                'username' => $userPrincipalName,
                'error' => $e->getMessage(),
                'trace' => substr($e->getTraceAsString(), 0, 300)
            ]);
            $this->stats['errors']++;
            return ['signin_log' => null, 'user_system' => null];
        }
    }



    /**
     * CRITICAL NEW METHOD: Proactively fix all users with null IDs during preload
     */
    private function fixNullUserIds(array $usernames): void
    {
        try {
            // Find users with null IDs
            $usersWithNullIds = AzureUser::whereIn('userPrincipalName', $usernames)
                ->where(function ($query) {
                    $query->whereNull('id')
                        ->orWhere('id', '=', '');
                })
                ->get();

            if ($usersWithNullIds->isEmpty()) {
                Log::info('✅ No users with null IDs found');
                return;
            }

            Log::warning('⚠️ Found users with null IDs, fixing them', [
                'count' => $usersWithNullIds->count()
            ]);

            foreach ($usersWithNullIds as $user) {
                try {
                    $newId = (string) \Illuminate\Support\Str::uuid();

                    DB::table('Azure_Users')
                        ->where('userPrincipalName', $user->userPrincipalName)
                        ->update(['id' => $newId]);

                    // Update the model
                    $user->id = $newId;

                    // Update cache
                    $this->userCache[$user->userPrincipalName] = $user;

                    $this->stats['users_id_generated']++;

                    Log::info('✅ Fixed null ID for user', [
                        'username' => $user->userPrincipalName,
                        'new_id' => $newId
                    ]);
                } catch (\Exception $e) {
                    Log::error('❌ Failed to fix null ID for user', [
                        'username' => $user->userPrincipalName,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('✅ Completed fixing null user IDs', [
                'fixed_count' => $this->stats['users_id_generated']
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Critical error in fixNullUserIds', [
                'error' => $e->getMessage()
            ]);
        }
    }

    // private function processRow(array $row, int $batchNumber = 0, int $rowIndex = 0)
    // {
    //     // CRITICAL FIX 1: Log the actual row data for first few rows
    //     if ($rowIndex < 3) {
    //         Log::debug("🔍 RAW ROW DATA", [
    //             'batch' => $batchNumber,
    //             'row' => $rowIndex,
    //             'raw_keys' => array_keys($row),
    //             'sample_values' => array_map(function($v) {
    //                 return is_string($v) ? substr($v, 0, 50) : $v;
    //             }, array_slice($row, 0, 10, true))
    //         ]);
    //     }

    //     $userPrincipalName = trim($row['username'] ?? $row['user_principal_name'] ?? '');
    //     $userDisplayName = trim($row['user'] ?? $row['display_name'] ?? '');
    //     $userIdFromCsv = trim($row['user id'] ?? $row['user_id'] ?? $row['userid'] ?? '');

    //     if (empty($userPrincipalName) || strlen($userPrincipalName) > 255) {
    //         Log::warning('Invalid username in row', [
    //             'batch' => $batchNumber,
    //             'row' => $rowIndex,
    //             'username' => substr($userPrincipalName, 0, 50),
    //             'length' => strlen($userPrincipalName)
    //         ]);
    //         $this->stats['skipped']++;
    //         return ['signin_log' => null, 'user_system' => null];
    //     }

    //     // CRITICAL FIX 2: Get user and FIX null ID immediately
    //     $user = $this->getUserFromCacheOrCreate($userPrincipalName, $userDisplayName, $userIdFromCsv);

    //     // CRITICAL FIX 3: If user exists but has null ID, fix it NOW
    //     if ($user && empty($user->id)) {
    //         Log::warning('⚠️ User has NULL ID, attempting to fix', [
    //             'batch' => $batchNumber,
    //             'row' => $rowIndex,
    //             'username' => $userPrincipalName
    //         ]);

    //         $user = $this->fixUserNullId($user);
    //     }

    //     // CRITICAL FIX 4: Only skip if user is completely unavailable
    //     if (!$user || empty($user->id)) {
    //         Log::error('❌ No valid user after all attempts', [
    //             'batch' => $batchNumber,
    //             'row' => $rowIndex,
    //             'username' => $userPrincipalName,
    //             'user_exists' => $user !== null,
    //             'user_has_id' => $user ? !empty($user->id) : false
    //         ]);
    //         $this->stats['skipped']++;
    //         return ['signin_log' => null, 'user_system' => null];
    //     }

    //     $application = $row['application'] ?? $row['app'] ?? null;
    //     $system = $this->mapApplicationToSystem($application);

    //     // CRITICAL FIX 5: More flexible date extraction
    //     $dateUtc = $this->extractDateUtc($row);
    //     if (!$dateUtc) {
    //         // Try common variations
    //         $dateUtc = $row['Date (UTC)'] ?? $row['Date'] ?? $row['date'] ?? null;

    //         Log::warning('⚠️ Date field not found with standard keys', [
    //             'batch' => $batchNumber,
    //             'row' => $rowIndex,
    //             'username' => $userPrincipalName,
    //             'available_keys' => array_keys($row),
    //             'keys_containing_date' => array_filter(array_keys($row), function($k) {
    //                 return stripos($k, 'date') !== false;
    //             })
    //         ]);
    //     }

    //     if (!$dateUtc) {
    //         $this->stats['skipped']++;
    //         return ['signin_log' => null, 'user_system' => null];
    //     }

    //     $parsedDate = $this->validateAndParseDate($dateUtc, $userPrincipalName);
    //     if (!$parsedDate) {
    //         Log::warning('Failed to parse date', [
    //             'batch' => $batchNumber,
    //             'row' => $rowIndex,
    //             'username' => $userPrincipalName,
    //             'raw_date' => substr($dateUtc, 0, 50)
    //         ]);
    //         $this->stats['skipped']++;
    //         return ['signin_log' => null, 'user_system' => null];
    //     }

    //     // CRITICAL FIX 6: More flexible request_id extraction
    //     $requestId = $row['request_id'] ?? $row['request id'] ?? $row['requestid'] ??
    //                  $row['Request ID'] ?? $row['RequestId'] ?? null;

    //     if (empty($requestId)) {
    //         $requestId = 'IMPORT-' . uniqid() . '-' . $batchNumber . '-' . $rowIndex;
    //     }

    //     try {
    //         $signinLogData = $this->prepareSigninLogData($row, $user, $system, $parsedDate, $userIdFromCsv);
    //         $signinLogData['request_id'] = $requestId;

    //         // CRITICAL FIX 7: Log successful preparation
    //         if ($rowIndex < 3) {
    //             Log::info('✅ Row prepared successfully', [
    //                 'batch' => $batchNumber,
    //                 'row' => $rowIndex,
    //                 'username' => $userPrincipalName,
    //                 'user_id' => $user->id,
    //                 'date' => $parsedDate,
    //                 'request_id' => $requestId
    //             ]);
    //         }

    //         if ($this->isDuplicateInDatabase($signinLogData)) {
    //             Log::debug('Duplicate signin log detected', [
    //                 'batch' => $batchNumber,
    //                 'row' => $rowIndex,
    //                 'username' => $userPrincipalName
    //             ]);
    //             $this->stats['skipped']++;
    //             return ['signin_log' => null, 'user_system' => null];
    //         }

    //         if (!$this->validateSigninLogData($signinLogData, $batchNumber, $rowIndex)) {
    //             $this->stats['skipped']++;
    //             return ['signin_log' => null, 'user_system' => null];
    //         }

    //         $userSystemAssociation = null;
    //         if ($system !== 'Unknown' && isset($this->systemCache[$system])) {
    //             $userSystemAssociation = [
    //                 'user_id' => $user->id,
    //                 'application_id' => (string) $this->systemCache[$system]->id,
    //             ];
    //         }

    //         return [
    //             'signin_log' => $signinLogData,
    //             'user_system' => $userSystemAssociation
    //         ];

    //     } catch (\Exception $e) {
    //         Log::error('❌ Error preparing signin log data', [
    //             'batch' => $batchNumber,
    //             'row' => $rowIndex,
    //             'username' => $userPrincipalName,
    //             'error' => $e->getMessage(),
    //             'trace' => substr($e->getTraceAsString(), 0, 300)
    //         ]);
    //         $this->stats['errors']++;
    //         return ['signin_log' => null, 'user_system' => null];
    //     }
    // }



    private function invalidateDashboardCache(): void
    {
        try {
            // Clear specific dashboard-related cache keys
            Cache::forget('dashboard_stats_cache');
            Cache::forget('actual_date_range');

            // Clear cache keys with patterns (if cache tagging is available)
            try {
                Cache::tags(['dashboard', 'user_stats', 'signin_logs'])->flush();
                Log::info('Dashboard cache cleared using tags');
            } catch (\Exception $e) {
                // Fallback for cache drivers that don't support tags
                Log::info('Dashboard cache cleared individually (tag support unavailable)');
            }

            Log::info('Dashboard cache invalidated after signin import', [
                'imported_records' => $this->stats['inserted'],
                'users_created' => $this->stats['users_created'],
                'users_found' => $this->stats['users_found']
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to invalidate dashboard cache', [
                'error' => $e->getMessage(),
                'stats' => $this->stats
            ]);
        }
    }

    /**
     * CRITICAL FIX: Real-time database duplicate check
     */
    private function isDuplicateInDatabase(array $signinLogData): bool
    {
        try {
            // Check using the actual primary key constraint fields
            $exists = DB::table('Azure_LoginDetails')
                ->where('date_utc', $signinLogData['date_utc'])
                ->where('request_id', $signinLogData['request_id'])
                ->where('user_id', $signinLogData['user_id'])
                ->exists();

            return $exists;
        } catch (\Exception $e) {
            Log::error('Error checking for duplicate record in database', [
                'error' => $e->getMessage(),
                'user_id' => $signinLogData['user_id'] ?? 'unknown',
                'date_utc' => $signinLogData['date_utc'] ?? 'unknown',
                'request_id' => $signinLogData['request_id'] ?? 'unknown'
            ]);
            // Return false to allow processing - better to attempt insert than skip incorrectly
            return false;
        }
    }

    private function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    // private function preloadSystemCache(): void
    // {
    //     $systems = System::all()->keyBy('name');
    //     $this->systemCache = $systems->toArray();
    //     Log::info('Preloaded system cache', ['count' => count($this->systemCache)]);
    // }

    private function cleanupCaches(): void
    {
        if (
            $this->stats['processed'] > 0 &&
            $this->stats['processed'] % $this->cacheCleanupInterval === 0
        ) {

            $start = microtime(true);
            $memoryBefore = memory_get_usage(true);

            Log::info('Initiating cache cleanup', [
                'processed_rows' => $this->stats['processed'],
                'memory_before_mb' => round($memoryBefore / 1024 / 1024, 2),
                'cache_sizes' => [
                    'users' => count($this->userCache),
                    // 'systems' => count($this->systemCache),
                    'existing_logs' => count($this->existingLogs)
                ]
            ]);

            $this->userCache = array_slice($this->userCache, -500, null, true);
            // $this->systemCache = array_slice($this->systemCache, -100, null, true);
            $this->existingLogs = [];

            gc_collect_cycles();

            $memoryAfter = memory_get_usage(true);
            $duration = round(microtime(true) - $start, 3);

            Log::info('Cache cleanup completed', [
                'memory_after_mb' => round($memoryAfter / 1024 / 1024, 2),
                'memory_delta_mb' => round(($memoryAfter - $memoryBefore) / 1024 / 1024, 2),
                'duration_seconds' => $duration
            ]);
        }
    }

    private function cleanup(): void
    {
        Log::info('Performing cleanup after import failure');

        $this->userCache = [];
        // $this->systemCache = [];  // REMOVED
        $this->existingLogs = [];

        $tempFiles = glob($this->tempDir . '/signin_bulk_*');
        foreach ($tempFiles as $file) {
            if (file_exists($file) && is_writable($file)) {
                unlink($file);
                Log::debug('Cleaned up temporary file: ' . $file);
            }
        }
    }

    private function releaseLock(): void
    {
        Cache::forget(self::$lockKey);
        Cache::forget(self::$progressKey);

        Log::info('Import lock released', [
            'final_stats' => $this->stats
        ]);
    }

    private function getLockContext(): array
    {
        return [
            'source' => 'signin_logs',
            'timestamp' => now()->toDateTimeString(),
            'user_id' => auth()->id() ?? 'system'
        ];
    }

    public function __destruct()
    {
        $this->releaseLock();
    }

    public function chunkSize(): int
    {
        return env('SIGNIN_EXCEL_CHUNK_SIZE', 200);
    }

    private function initializeTableStructure(): void
    {
        try {
            $this->tableColumns = Schema::getColumnListing('Azure_LoginDetails');
            $this->requiredColumns = $this->getRequiredColumns();

            Log::info('Table structure initialized', [
                'total_columns' => count($this->tableColumns),
                'required_columns' => count($this->requiredColumns),
                'required_fields' => array_keys($this->requiredColumns)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to initialize table structure: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getRequiredColumns(): array
    {
        $required = [];

        try {
            $columnInfo = DB::select("
                SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME = 'Azure_LoginDetails'
                  AND IS_NULLABLE = 'NO'
            ");

            foreach ($columnInfo as $column) {
                $required[strtolower($column->COLUMN_NAME)] = [
                    'type' => $column->DATA_TYPE,
                    'default' => $column->COLUMN_DEFAULT
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Could not fetch column constraints: ' . $e->getMessage());
            $required = [
                'date_utc' => ['type' => 'datetime', 'default' => null],
                'request_id' => ['type' => 'varchar', 'default' => null],
                'user_id' => ['type' => 'varchar', 'default' => null],
                'system' => ['type' => 'varchar', 'default' => null]
            ];
        }

        return $required;
    }

    private function calculateOptimalChunkSize(): int
    {
        $columnCount = count($this->tableColumns ?? []);
        $memoryUsageMb = round(memory_get_usage(true) / 1024 / 1024, 2);

        if ($columnCount === 0) {
            Log::warning('Column count unavailable, using fallback chunk size', [
                'memory_usage_mb' => $memoryUsageMb
            ]);
            return $this->fallbackChunkSize ?? 5;
        }

        $safeSize = (int) floor(($this->maxParams - $this->paramBuffer) / $columnCount);

        $maxChunkByColumn = match (true) {
            $columnCount >= 70 => 5,
            $columnCount >= 60 => 10,
            $columnCount >= 50 => 15,
            $columnCount >= 30 => 25,
            default => 50,
        };

        if ($memoryUsageMb > ($this->memoryThresholdMb ?? 150)) {
            $maxChunkByColumn = max(1, floor($maxChunkByColumn / 2));
            Log::info('Memory usage high, reducing chunk size', [
                'memory_usage_mb' => $memoryUsageMb,
                'adjusted_max_chunk' => $maxChunkByColumn
            ]);
        }

        $chunkSize = min($safeSize, $maxChunkByColumn);
        $finalSize = max(1, $chunkSize);

        Log::info('Calculated optimal chunk size', [
            'column_count' => $columnCount,
            'safe_size' => $safeSize,
            'final_chunk_size' => $finalSize,
            'memory_usage_mb' => $memoryUsageMb
        ]);

        return $finalSize;
    }

    private function executeOptimalInsert(array $signinLogs, int $batchNumber): int
    {
        if (empty($signinLogs)) {
            return 0;
        }

        $columnCount = count($signinLogs[0]);
        $rowCount = count($signinLogs);

        Log::debug("Insert strategy analysis", [
            'batch' => $batchNumber,
            'rows' => $rowCount,
            'columns' => $columnCount,
            'bulk_threshold' => $this->bulkThreshold,
            'use_bulk_insert' => $this->useBulkInsert
        ]);

        if ($this->useBulkInsert && $columnCount >= $this->bulkThreshold && $rowCount >= 10) {
            return $this->executeBulkInsert($signinLogs, $batchNumber);
        }

        if ($columnCount < $this->bulkThreshold && $rowCount > 1) {
            return $this->executeChunkedBatchInsert($signinLogs, $batchNumber);
        }

        return $this->executeSingleRowInserts($signinLogs, $batchNumber);
    }

    private function executeBulkInsert(array $signinLogs, int $batchNumber): int
    {
        $this->stats['bulk_operations']++;

        try {
            $tempFile = tempnam($this->tempDir, 'signin_bulk_');
            $csvFile = $tempFile . '.csv';
            rename($tempFile, $csvFile);

            $handle = fopen($csvFile, 'w');
            if (!$handle) {
                throw new \Exception("Cannot create temporary CSV file: {$csvFile}");
            }

            $columns = array_keys($signinLogs[0]);
            foreach ($signinLogs as $row) {
                $values = [];
                foreach ($columns as $col) {
                    $value = $row[$col];
                    if ($value instanceof Carbon) {
                        $values[] = $value->format('Y-m-d H:i:s');
                    } elseif (is_null($value)) {
                        $values[] = '';
                    } else {
                        $values[] = $value;
                    }
                }
                fputcsv($handle, $values);
            }
            fclose($handle);

            $columnList = '(' . implode(', ', array_map(function ($col) {
                return '[' . str_replace(']', ']]', $col) . ']';
            }, $columns)) . ')';

            $sql = "
                BULK INSERT Azure_LoginDetails {$columnList}
                FROM '{$csvFile}'
                WITH (
                    FIELDTERMINATOR = ',',
                    ROWTERMINATOR = '\\n',
                    KEEPNULLS,
                    BATCHSIZE = 1000,
                    TABLOCK
                )
            ";

            DB::statement($sql);

            $insertedCount = count($signinLogs);

            Log::info("BULK INSERT completed successfully", [
                'batch' => $batchNumber,
                'rows' => $insertedCount,
                'columns' => count($columns),
                'temp_file' => $csvFile
            ]);

            unlink($csvFile);
            return $insertedCount;
        } catch (\Exception $e) {
            if (isset($csvFile) && file_exists($csvFile)) {
                unlink($csvFile);
            }

            Log::warning("BULK INSERT failed, falling back to batch insert", [
                'batch' => $batchNumber,
                'error' => $e->getMessage()
            ]);

            return $this->executeChunkedBatchInsert($signinLogs, $batchNumber);
        }
    }

    private function executeChunkedBatchInsert(array $signinLogs, int $batchNumber): int
    {
        $columnCount = count($signinLogs[0]);
        $availableParams = $this->maxParams - $this->paramBuffer;
        $theoreticalChunkSize = max(1, (int) floor($availableParams / $columnCount));

        // Determine safe chunk size based on column count (60 columns total)
        $safeChunkSize = match (true) {
            $columnCount >= 80 => 1,
            $columnCount >= 60 => min($theoreticalChunkSize, 2), // Your case - 60 columns
            $columnCount >= 40 => min($theoreticalChunkSize, 3),
            $columnCount >= 25 => min($theoreticalChunkSize, 5),
            default => min($theoreticalChunkSize, 8),
        };

        Log::debug("Chunked batch insert strategy", [
            'batch' => $batchNumber,
            'total_rows' => count($signinLogs),
            'columns_per_row' => $columnCount,
            'theoretical_chunk_size' => $theoreticalChunkSize,
            'safe_chunk_size' => $safeChunkSize
        ]);

        $insertedCount = 0;

        foreach (array_chunk($signinLogs, $safeChunkSize) as $chunkIndex => $chunk) {
            $paramCount = count($chunk) * $columnCount;

            if ($paramCount >= $availableParams || $paramCount > 1800) {
                Log::warning("Parameter count exceeded safety threshold", [
                    'batch' => $batchNumber,
                    'chunk' => $chunkIndex + 1,
                    'param_count' => $paramCount,
                    'safety_threshold' => $availableParams
                ]);

                $insertedCount += $this->executeSingleRowInserts($chunk, $batchNumber);
                continue;
            }

            try {
                // Safety check: Remove any ID columns from all records in chunk
                $cleanChunk = array_map(function ($record) {
                    unset($record['id'], $record['Id'], $record['ID']);
                    return $record;
                }, $chunk);

                DB::table('Azure_LoginDetails')->timeout(120)->insert($cleanChunk);
                $insertedCount += count($cleanChunk);

                Log::debug("Batch chunk inserted successfully", [
                    'batch' => $batchNumber,
                    'chunk' => $chunkIndex + 1,
                    'rows' => count($cleanChunk),
                    'params' => $paramCount
                ]);
            } catch (\Exception $e) {
                $isParamLimitError = str_contains($e->getMessage(), '2100') ||
                    str_contains($e->getMessage(), 'parameter number') ||
                    str_contains($e->getMessage(), 'maximum of') ||
                    str_contains($e->getMessage(), 'IMSSP');

                Log::error($isParamLimitError
                    ? "Parameter limit error - using single-row fallback"
                    : "Batch chunk failed - trying single inserts", [
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
    /**
     * FIXED: Use proper UPSERT with correct SQL syntax
     */
    private function executeSingleRowInserts(array $signinLogs, int $batchNumber): int
    {
        Log::info("Using single-row UPSERT mode", [
            'batch' => $batchNumber,
            'rows' => count($signinLogs)
        ]);

        $insertedCount = 0;

        foreach ($signinLogs as $index => $signinLog) {
            try {
                // Remove any ID columns that might cause issues
                unset($signinLog['id'], $signinLog['Id'], $signinLog['ID']);

                // First, try to check if record exists (more reliable than MERGE)
                $exists = DB::table('Azure_LoginDetails')
                    ->where('date_utc', $signinLog['date_utc'])
                    ->where('request_id', $signinLog['request_id'])
                    ->where('user_id', $signinLog['user_id'])
                    ->exists();

                if ($exists) {
                    // Record exists, update it
                    $updated = DB::table('Azure_LoginDetails')
                        ->where('date_utc', $signinLog['date_utc'])
                        ->where('request_id', $signinLog['request_id'])
                        ->where('user_id', $signinLog['user_id'])
                        ->update([
                            'date_utc' => $signinLog['date_utc'],
                            // 'system' => $signinLog['system'], // Update system in case it changed
                        ]);

                    if ($updated) {
                        Log::debug("Updated existing record", [
                            'batch' => $batchNumber,
                            'index' => $index,
                            'username' => $signinLog['username'] ?? 'unknown'
                        ]);
                        $this->stats['skipped']++; // Count as skipped since we didn't insert
                    }
                } else {
                    // Record doesn't exist, insert it
                    DB::table('Azure_LoginDetails')->insert($signinLog);
                    $insertedCount++;

                    Log::debug("Inserted new record", [
                        'batch' => $batchNumber,
                        'index' => $index,
                        'username' => $signinLog['username'] ?? 'unknown'
                    ]);
                }
            } catch (\Exception $e) {
                // Handle different types of errors appropriately
                $errorMessage = $e->getMessage();

                if (
                    str_contains($errorMessage, 'duplicate') ||
                    str_contains($errorMessage, 'PRIMARY KEY') ||
                    str_contains($errorMessage, 'UNIQUE constraint')
                ) {

                    Log::info("Duplicate record handled gracefully", [
                        'batch' => $batchNumber,
                        'index' => $index,
                        'username' => $signinLog['username'] ?? 'unknown',
                        'error_type' => 'duplicate'
                    ]);
                    $this->stats['skipped']++;
                } else {
                    Log::error("Single signin log operation failed", [
                        'batch' => $batchNumber,
                        'index' => $index,
                        'error' => $errorMessage,
                        'username' => $signinLog['username'] ?? 'unknown',
                        'error_type' => 'database_error'
                    ]);
                    $this->stats['errors']++;
                }
            }
        }

        Log::info("UPSERT insertion completed", [
            'batch' => $batchNumber,
            'inserted' => $insertedCount,
            'total' => count($signinLogs),
            'success_rate' => count($signinLogs) > 0 ? round(($insertedCount / count($signinLogs)) * 100, 2) . '%' : '0%'
        ]);

        return $insertedCount;
    }





    private function preloadData(Collection $batch)
    {
        $usernames = [];
        $duplicateCheckData = [];

        foreach ($batch as $row) {
            $row = array_change_key_case($row->toArray(), CASE_LOWER);
            $username = trim($row['username'] ?? '');

            if (!empty($username) && strlen($username) <= 255) {
                $usernames[] = $username;

                $dateUtc = $this->extractDateUtc($row);
                if ($dateUtc) {
                    $parsedDate = $this->validateAndParseDate($dateUtc);
                    if ($parsedDate) {
                        $duplicateCheckData[] = [
                            'username' => $username,
                            'date_utc' => $parsedDate,
                            'request_id' => $row['request id'] ?? null
                        ];
                    }
                }
            }
        }

        $this->preloadUsers(array_unique($usernames));
        $this->preloadExistingLogs($duplicateCheckData);
    }

    private function preloadUsers(array $usernames)
    {
        if (empty($usernames)) return;

        foreach (array_chunk($usernames, 500) as $chunk) {
            $existingUsers = AzureUser::whereIn('userPrincipalName', $chunk)
                ->get()
                ->keyBy('userPrincipalName');

            foreach ($existingUsers as $user) {
                $this->userCache[$user->userPrincipalName] = $user;
            }
        }

        Log::info('Preloaded users', ['count' => count($this->userCache)]);
    }

// private function preloadSystems(array $systemNames)
// {
//     if (empty($systemNames)) return;

//     // Deduplicate system names
//     $systemNames = array_unique($systemNames);

//     // ✅ FIX 1: Define existingSystems BEFORE transaction
//     $existingSystems = System::whereIn('name', $systemNames)
//         ->get()
//         ->keyBy('name');

//     foreach ($existingSystems as $system) {
//         $this->systemCache[$system->name] = $system;
//     }

//     $missingSystemNames = array_diff($systemNames, $existingSystems->pluck('name')->toArray());

//     if (!empty($missingSystemNames)) {
//         try {
//             // ✅ FIX 2: Use UUIDs instead of integers
//             DB::transaction(function () use ($missingSystemNames) {
//                 $newSystems = [];
//                 foreach ($missingSystemNames as $systemName) {
//                     $newSystems[] = [
//                         'Id' => (string) \Illuminate\Support\Str::uuid(),  // ✅ UUID
//                         'name' => $systemName
//                     ];
//                 }

//                 DB::table('Azure_SoftwareMaster')->insert($newSystems);

//                 // Reload newly created systems
//                 $newSystemModels = System::whereIn('name', $missingSystemNames)->get()->keyBy('name');
//                 foreach ($newSystemModels as $system) {
//                     $this->systemCache[$system->name] = $system;
//                 }
//             });

//             // ✅ FIX 3: Log AFTER transaction with correct variable scope
//             Log::info('Preloaded systems', [
//                 'existing' => $existingSystems->count(),
//                 'created' => count($missingSystemNames),
//                 'total_cached' => count($this->systemCache)
//             ]);

//         } catch (\Exception $e) {
//             Log::error('Failed to create systems during preload', [
//                 'systems' => $missingSystemNames,
//                 'error' => $e->getMessage()
//             ]);

//             // Fallback: Create systems individually
//             foreach ($missingSystemNames as $systemName) {
//                 try {
//                     DB::transaction(function () use ($systemName) {
//                         $newId = (string) \Illuminate\Support\Str::uuid();  // ✅ UUID

//                         DB::table('Azure_SoftwareMaster')->insert([
//                             'Id' => $newId,
//                             'name' => $systemName
//                         ]);

//                         $system = System::where('name', $systemName)->first();
//                         if ($system) {
//                             $this->systemCache[$systemName] = $system;
//                         }
//                     });

//                 } catch (\Exception $e2) {
//                     Log::warning('Failed to create individual system', [
//                         'system' => $systemName,
//                         'error' => $e2->getMessage()
//                     ]);
//                 }
//             }
//         }
//     } else {
//         Log::info('Preloaded systems', [
//             'existing' => $existingSystems->count(),
//             'created' => 0,
//             'total_cached' => count($this->systemCache)
//         ]);
//     }
// }


// private function bulkInsertUserSystemAssociations(array $associations)
// {
//     if (empty($associations)) {
//         Log::debug('No user-system associations to process');
//         return;
//     }

//     // Deduplicate associations
//     $uniqueAssociations = [];
//     foreach ($associations as $assoc) {
//         if (!isset($assoc['user_id'], $assoc['application_id'])) {
//             Log::warning('Invalid association data', ['association' => $assoc]);
//             continue;
//         }

//         $key = $assoc['user_id'] . '|' . $assoc['application_id'];
//         if (!isset($uniqueAssociations[$key])) {
//             $uniqueAssociations[$key] = [
//                 'user_id' => $assoc['user_id'],
//                 'application_id' => (string) $assoc['application_id'],
//             ];
//         }
//     }

//     if (empty($uniqueAssociations)) {
//         Log::debug('No valid associations after deduplication');
//         return;
//     }

//     Log::info('Processing user-system associations', [
//         'original_count' => count($associations),
//         'unique_count' => count($uniqueAssociations)
//     ]);

//     // CRITICAL FIX: Cast to int and ensure non-negative
//     $maxId = (int) DB::table('Azure_Application_UserMapping')->max('Id');
//     $maxId = max(0, $maxId);
//     $nextId = $maxId + 1;

//     // Process in chunks of 50
//     foreach (array_chunk(array_values($uniqueAssociations), 50) as $chunkIndex => $chunk) {
//         try {
//             $userIds = array_column($chunk, 'user_id');
//             $applicationIds = array_column($chunk, 'application_id');

//             // Check for existing associations
//             $existingPairs = DB::table('Azure_Application_UserMapping')
//                 ->whereIn('user_id', $userIds)
//                 ->whereIn('application_id', $applicationIds)
//                 ->select('user_id', 'application_id')
//                 ->get()
//                 ->mapWithKeys(fn($row) => [$row->user_id . '|' . $row->application_id => true])
//                 ->all();

//             // Filter out existing associations and add explicit IDs
//             $newAssociations = [];
//             foreach ($chunk as $assoc) {
//                 $key = $assoc['user_id'] . '|' . $assoc['application_id'];
//                 if (!isset($existingPairs[$key])) {
//                     $newAssociations[] = [
//                         'Id' => $nextId++,
//                         'user_id' => $assoc['user_id'],
//                         'application_id' => $assoc['application_id']
//                     ];
//                 }
//             }

//             if (!empty($newAssociations)) {
//                 try {
//                     DB::table('Azure_Application_UserMapping')->insert($newAssociations);

//                     Log::info('Inserted user-application associations chunk', [
//                         'chunk_index' => $chunkIndex + 1,
//                         'new_associations' => count($newAssociations),
//                         'skipped_existing' => count($chunk) - count($newAssociations)
//                     ]);
//                 } catch (\Exception $e) {
//                     Log::warning('Batch association insert failed, trying individual inserts', [
//                         'chunk_index' => $chunkIndex + 1,
//                         'chunk_size' => count($newAssociations),
//                         'error' => $e->getMessage()
//                     ]);

//                     $this->insertUserSystemAssociationsIndividually($newAssociations);
//                 }
//             }

//         } catch (\Exception $e) {
//             Log::error('Critical error in association processing', [
//                 'chunk_index' => $chunkIndex + 1,
//                 'error' => $e->getMessage()
//             ]);

//             continue;
//         }
//     }
// }


// private function insertUserSystemAssociationsIndividually(array $associations)
// {
//     $insertedCount = 0;
//     $skippedCount = 0;

//     foreach ($associations as $assoc) {
//         try {
//             $exists = DB::table('Azure_Application_UserMapping')
//                 ->where('user_id', $assoc['user_id'])
//                 ->where('application_id', $assoc['application_id'])
//                 ->exists();

//             if (!$exists) {
//                 // CRITICAL FIX: Cast to int
//                 $maxId = (int) DB::table('Azure_Application_UserMapping')->max('Id');
//                 $maxId = max(0, $maxId);

//                 DB::table('Azure_Application_UserMapping')->insert([
//                     'Id' => $maxId + 1,
//                     'user_id' => $assoc['user_id'],
//                     'application_id' => $assoc['application_id']
//                 ]);
//                 $insertedCount++;
//             } else {
//                 $skippedCount++;
//             }

//         } catch (\Exception $e) {
//             if (str_contains($e->getMessage(), 'duplicate') ||
//                 str_contains($e->getMessage(), 'UNIQUE constraint') ||
//                 str_contains($e->getMessage(), 'PRIMARY KEY constraint')) {
//                 $skippedCount++;
//                 Log::debug('Duplicate user-application association skipped', [
//                     'user_id' => $assoc['user_id'],
//                     'application_id' => $assoc['application_id']
//                 ]);
//             } else {
//                 Log::error('Failed to insert individual user-application association', [
//                     'user_id' => $assoc['user_id'],
//                     'application_id' => $assoc['application_id'],
//                     'error' => $e->getMessage()
//                 ]);
//             }
//         }
//     }

//     if ($insertedCount > 0 || $skippedCount > 0) {
//         Log::info('Individual user-application associations completed', [
//             'inserted' => $insertedCount,
//             'skipped' => $skippedCount
//         ]);
//     }
// }



    /**
     * ENHANCED: More comprehensive preloadExistingLogs with better coverage
     */
    private function preloadExistingLogs(array $duplicateCheckData)
    {
        if (empty($duplicateCheckData)) {
            Log::info('No duplicate check data provided, skipping preload');
            return;
        }

        $totalPreloaded = 0;
        $userIds = [];
        $dates = [];
        $requestIds = [];

        // Extract all identifiers from the check data
        foreach ($duplicateCheckData as $data) {
            if (isset($this->userCache[$data['username']])) {
                $user = $this->userCache[$data['username']];
                if (!empty($user->id)) { // CRITICAL: Only use users with valid IDs
                    $userIds[] = $user->id;
                    if (!empty($data['date_utc'])) {
                        $dates[] = $data['date_utc'];
                    }
                    if (!empty($data['request_id'])) {
                        $requestIds[] = $data['request_id'];
                    }
                }
            }
        }

        if (empty($userIds)) {
            Log::warning('No valid user IDs found for preloading existing logs');
            return;
        }

        // CRITICAL FIX: Use broader date range to catch more potential duplicates
        $uniqueUserIds = array_unique($userIds);
        $uniqueDates = array_unique($dates);
        $uniqueRequestIds = array_unique(array_filter($requestIds)); // Remove empty request IDs

        Log::info('Starting comprehensive preload of existing logs', [
            'unique_users' => count($uniqueUserIds),
            'unique_dates' => count($uniqueDates),
            'unique_request_ids' => count($uniqueRequestIds)
        ]);

        try {
            // Strategy 1: Load by user IDs and date range (broader coverage)
            if (!empty($uniqueUserIds) && !empty($uniqueDates)) {
                foreach (array_chunk($uniqueUserIds, 100) as $userChunk) {
                    $minDate = min($uniqueDates);
                    $maxDate = max($uniqueDates);

                    // Expand date range by 1 day on each side to catch edge cases
                    $expandedMinDate = date('Y-m-d H:i:s', strtotime($minDate . ' -1 day'));
                    $expandedMaxDate = date('Y-m-d H:i:s', strtotime($maxDate . ' +1 day'));

                    $existingLogs = DB::table('Azure_LoginDetails')
                        ->whereIn('user_id', $userChunk)
                        ->whereBetween('date_utc', [$expandedMinDate, $expandedMaxDate])
                        ->select('user_id', 'date_utc', 'request_id')
                        ->get();

                    foreach ($existingLogs as $log) {
                        $key = $log->user_id . '|' . $log->date_utc . '|' . $log->request_id;
                        $this->existingLogs[$key] = true;
                        $totalPreloaded++;
                    }
                }
            }

            // Strategy 2: Load by specific request IDs (if available)
            if (!empty($uniqueRequestIds)) {
                foreach (array_chunk($uniqueRequestIds, 200) as $requestChunk) {
                    $existingLogs = DB::table('Azure_LoginDetails')
                        ->whereIn('request_id', $requestChunk)
                        ->select('user_id', 'date_utc', 'request_id')
                        ->get();

                    foreach ($existingLogs as $log) {
                        $key = $log->user_id . '|' . $log->date_utc . '|' . $log->request_id;
                        if (!isset($this->existingLogs[$key])) {
                            $this->existingLogs[$key] = true;
                            $totalPreloaded++;
                        }
                    }
                }
            }

            // Strategy 3: Recent logs for the users (catch any recent duplicates)
            foreach (array_chunk($uniqueUserIds, 50) as $userChunk) {
                $recentLogs = DB::table('Azure_LoginDetails')
                    ->whereIn('user_id', $userChunk)
                    ->where('date_utc', '>=', date('Y-m-d H:i:s', strtotime('-7 days')))
                    ->select('user_id', 'date_utc', 'request_id')
                    ->get();

                foreach ($recentLogs as $log) {
                    $key = $log->user_id . '|' . $log->date_utc . '|' . $log->request_id;
                    if (!isset($this->existingLogs[$key])) {
                        $this->existingLogs[$key] = true;
                        $totalPreloaded++;
                    }
                }
            }

            Log::info('Enhanced preload completed', [
                'total_preloaded' => $totalPreloaded,
                'unique_keys' => count($this->existingLogs),
                'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
            ]);
        } catch (\Exception $e) {
            Log::error('Error during comprehensive preload of existing logs', [
                'error' => $e->getMessage(),
                'preloaded_so_far' => $totalPreloaded,
                'trace' => substr($e->getTraceAsString(), 0, 300)
            ]);

            // Continue with whatever we managed to preload
            Log::info('Continuing with partial preload', [
                'partial_count' => count($this->existingLogs)
            ]);
        }
    }
    private function prepareSigninLogData(array $row, $user, string $parsedDate, ?string $userIdFromCsv = null): array
    {
        // CRITICAL FIX: Use CSV user ID if valid, otherwise fall back to user->id
        $userIdToStore = (!empty($userIdFromCsv) && $this->isValidUuid($userIdFromCsv))
            ? $userIdFromCsv
            : $user->id;

        // Log::debug('Preparing signin log data', [ /// Tracker Debug
        //     'username' => $row['username'] ?? 'unknown',
        //     'user_id_from_csv' => $userIdFromCsv,
        //     'user_id_from_model' => $user->id,
        //     'user_id_to_store' => $userIdToStore,
        //     'used_csv_id' => (!empty($userIdFromCsv) && $this->isValidUuid($userIdFromCsv))
        // ]);

        return [
            // Required core fields
            'date_utc' => $parsedDate,
            'request_id' => $this->getValueOrDefault($row['request_id'] ?? null, 'IMPORT-' . uniqid()),
            'user_id' => $userIdToStore,

            // User information fields
            'user' => $this->truncateString($row['user'] ?? null, 255),
            'username' => $this->truncateString($row['username'] ?? null, 255),
            'user_type' => $this->truncateString($row['user_type'] ?? null, 255),

            // Application fields
            'application' => $this->truncateString($row['application'] ?? null, 255),
            'application_id' => $this->truncateString($row['application_id'] ?? null, 255),

            // Network/Location fields
            'ip_address' => $this->truncateString($row['ip_address'] ?? null, 45),
            'location' => $this->truncateString($row['location'] ?? null, 255),

            // Status field
            'status' => $this->truncateString($row['status'] ?? null, 255),

            // Device/Client fields
            'client_app' => $this->truncateString($row['client_app'] ?? null, 255),
            'device_id' => $this->truncateString($row['device_id'] ?? null, 255),
            'browser' => $this->truncateString($row['browser'] ?? null, 255),
            'operating_system' => $this->truncateString($row['operating_system'] ?? null, 255),

            // Authentication field
            'authentication_requirement' => $this->truncateString($row['authentication_requirement'] ?? null, 255),

            // Timestamps
            // 'created_at' => now(),
            // 'updated_at' => now(),
        ];
    }

    private function getValueOrDefault($value, $default)
    {
        return ($value === null || $value === '') ? $default : $value;
    }

    private function validateRequiredFields(array $data, int $batchNumber, int $rowIndex): bool
    {
        foreach ($this->requiredColumns as $column => $info) {
            if (
                !array_key_exists($column, $data) ||
                ($data[$column] === null && $info['default'] === null)
            ) {

                Log::warning("Required field missing or null", [
                    'batch' => $batchNumber,
                    'row' => $rowIndex,
                    'field' => $column,
                    'user_id' => $data['user_id'] ?? 'unknown'
                ]);

                return false;
            }
        }

        return true;
    }

    private function truncateString($value, $maxLength)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($maxLength === 'text') {
            return $value;
        }

        $stringValue = (string) $value;
        return strlen($stringValue) > $maxLength ? substr($stringValue, 0, $maxLength) : $stringValue;
    }

    // private function mapApplicationToSystem($application)
    // {
    //     if (empty($application)) {
    //         return 'Unknown';
    //     }

    //     $normalizedApp = strtolower(trim($application));
    //     $systemMapping = config('systemmap', []);
    //     $system = $systemMapping[$normalizedApp] ?? null;

    //     if (!$system) {
    //         foreach ($systemMapping as $appKey => $systemName) {
    //             if (strpos($normalizedApp, $appKey) !== false || strpos($appKey, $normalizedApp) !== false) {
    //                 $system = $systemName;
    //                 break;
    //             }
    //         }
    //     }

    //     return $system ?? 'Unknown';
    // }

    private function convertBitField($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_numeric($value)) {
            return ((int) $value) ? 1 : 0;
        }

        if (is_string($value)) {
            $value = strtolower(trim($value));
            if (in_array($value, ['true', '1', 'yes', 'on', 'enabled'])) {
                return 1;
            } elseif (in_array($value, ['false', '0', 'no', 'off', 'disabled'])) {
                return 0;
            }
        }

        return 0;
    }


    // private function bulkInsertUserSystemAssociations(array $associations)
    // {
    //     if (empty($associations)) {
    //         Log::debug('No user-system associations to process');
    //         return;
    //     }

    //     // Deduplicate associations
    //     $uniqueAssociations = [];
    //     foreach ($associations as $assoc) {
    //         if (!isset($assoc['user_id'], $assoc['application_id'])) {
    //             Log::warning('Invalid association data', ['association' => $assoc]);
    //             continue;
    //         }

    //         $key = $assoc['user_id'] . '|' . $assoc['application_id'];
    //         if (!isset($uniqueAssociations[$key])) {
    //             $uniqueAssociations[$key] = [
    //                 'user_id' => $assoc['user_id'],
    //                 'application_id' => (string) $assoc['application_id'],
    //             ];
    //         }
    //     }

    //     if (empty($uniqueAssociations)) {
    //         Log::debug('No valid associations after deduplication');
    //         return;
    //     }

    //     Log::info('Processing user-system associations', [
    //         'original_count' => count($associations),
    //         'unique_count' => count($uniqueAssociations)
    //     ]);

    //     // Get the current max ID to generate new IDs
    //     $maxId = DB::table('Azure_Application_UserMapping')->max('Id') ?? 0;
    //     $nextId = $maxId + 1;

    //     // Process in chunks of 50 (smaller chunks for safer insertion)
    //     foreach (array_chunk(array_values($uniqueAssociations), 50) as $chunkIndex => $chunk) {
    //         try {
    //             $userIds = array_column($chunk, 'user_id');
    //             $applicationIds = array_column($chunk, 'application_id');

    //             // Check for existing associations
    //             $existingPairs = DB::table('Azure_Application_UserMapping')
    //                 ->whereIn('user_id', $userIds)
    //                 ->whereIn('application_id', $applicationIds)
    //                 ->select('user_id', 'application_id')
    //                 ->get()
    //                 ->mapWithKeys(fn($row) => [$row->user_id . '|' . $row->application_id => true])
    //                 ->all();

    //             // Filter out existing associations and add explicit IDs
    //             $newAssociations = [];
    //             foreach ($chunk as $assoc) {
    //                 $key = $assoc['user_id'] . '|' . $assoc['application_id'];
    //                 if (!isset($existingPairs[$key])) {
    //                     $newAssociations[] = [
    //                         'Id' => $nextId++,  // CRITICAL FIX: Explicit ID
    //                         'user_id' => $assoc['user_id'],
    //                         'application_id' => $assoc['application_id']
    //                     ];
    //                 }
    //             }

    //             if (!empty($newAssociations)) {
    //                 // Try batch insert first
    //                 try {
    //                     DB::table('Azure_Application_UserMapping')->insert($newAssociations);

    //                     Log::info('Inserted user-application associations chunk', [
    //                         'chunk_index' => $chunkIndex + 1,
    //                         'new_associations' => count($newAssociations),
    //                         'skipped_existing' => count($chunk) - count($newAssociations)
    //                     ]);
    //                 } catch (\Exception $e) {
    //                     Log::warning('Batch association insert failed, trying individual inserts', [
    //                         'chunk_index' => $chunkIndex + 1,
    //                         'chunk_size' => count($newAssociations),
    //                         'error' => $e->getMessage()
    //                     ]);

    //                     $this->insertUserSystemAssociationsIndividually($newAssociations);
    //                 }
    //             }

    //         } catch (\Exception $e) {
    //             Log::error('Critical error in association processing', [
    //                 'chunk_index' => $chunkIndex + 1,
    //                 'error' => $e->getMessage()
    //             ]);

    //             // Continue with next chunk instead of failing entirely
    //             continue;
    //         }
    //     }
    // }

    // private function bulkInsertUserSystemAssociations(array $associations)
    // {
    //     $insertedCount = 0;
    //     $skippedCount = 0;

    //     foreach ($associations as $assoc) {
    //         try {
    //             $exists = DB::table('Azure_Application_UserMapping')
    //                 ->where('user_id', $assoc['user_id'])
    //                 ->where('application_id', $assoc['application_id'])
    //                 ->exists();

    //             if (!$exists) {
    //                 // Get fresh max ID for each insert to avoid conflicts
    //                 $maxId = DB::table('Azure_Application_UserMapping')->max('Id') ?? 0;

    //                 DB::table('Azure_Application_UserMapping')->insert([
    //                     'Id' => $maxId + 1,  // CRITICAL FIX: Explicit ID
    //                     'user_id' => $assoc['user_id'],
    //                     'application_id' => $assoc['application_id']
    //                 ]);
    //                 $insertedCount++;
    //             } else {
    //                 $skippedCount++;
    //             }

    //         } catch (\Exception $e) {
    //             if (str_contains($e->getMessage(), 'duplicate') ||
    //                 str_contains($e->getMessage(), 'UNIQUE constraint') ||
    //                 str_contains($e->getMessage(), 'PRIMARY KEY constraint')) {
    //                 $skippedCount++;
    //                 Log::debug('Duplicate user-application association skipped', [
    //                     'user_id' => $assoc['user_id'],
    //                     'application_id' => $assoc['application_id']
    //                 ]);
    //             } else {
    //                 Log::error('Failed to insert individual user-application association', [
    //                     'user_id' => $assoc['user_id'],
    //                     'application_id' => $assoc['application_id'],
    //                     'error' => $e->getMessage()
    //                 ]);
    //             }
    //         }
    //     }

    //     if ($insertedCount > 0 || $skippedCount > 0) {
    //         Log::info('Individual user-application associations completed', [
    //             'inserted' => $insertedCount,
    //             'skipped' => $skippedCount
    //         ]);
    //     }
    // }


    private function extractDateUtc($row)
    {
        $dateFields = [
            'date_utc',
            'date (utc)',
            'date',
            'created_date',
            'created_date',
            'timestamp',
            'signin_date',
            'login_date'
        ];

        foreach ($dateFields as $field) {
            if (isset($row[$field]) && !empty(trim($row[$field]))) {
                return trim($row[$field]);
            }
        }

        return null;
    }

    private function validateAndParseDate($dateString, $username = null)
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            $formats = [
                'Y-m-d H:i:s',
                'Y-m-d\TH:i:s\Z',
                'Y-m-d\TH:i:s.u\Z',
                'Y-m-d\TH:i:s',
                'Y-m-d\TH:i:s.v\Z',
                'm/d/Y H:i:s',
                'd/m/Y H:i:s',
                'Y-m-d',
                'm/d/Y',
                'd/m/Y',
                'c',
                'r'
            ];

            foreach ($formats as $format) {
                try {
                    $date = Carbon::createFromFormat($format, $dateString);
                    if ($date && $date->year >= 2010 && $date->year <= 2030) {
                        return $date->format('Y-m-d H:i:s');
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            $date = Carbon::parse($dateString);
            if ($date && $date->year >= 2010 && $date->year <= 2030) {
                return $date->format('Y-m-d H:i:s');
            }
        } catch (\Exception $e) {
            Log::warning('Failed to parse date', [
                'username' => $username,
                'date_string' => substr($dateString, 0, 100),
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    private function isValidUuid($uuid): bool
    {
        if (!is_string($uuid)) {
            return false;
        }
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid) === 1;
    }

    private function generateNumericId(): int
    {
        $uuid = (string) Str::uuid();
        $numericId = $this->uuidToBigInt($uuid);

        if (!is_numeric($numericId)) {
            Log::error('Invalid numeric ID generated from UUID', ['uuid' => $uuid]);
            throw new \Exception('Failed to generate valid numeric ID');
        }

        return (int) $numericId;
    }


    public function getStats(): array
    {
        $baseStats = [
            'processed' => $this->stats['processed'],
            'inserted' => $this->stats['inserted'],
            'skipped' => $this->stats['skipped'],
            'errors' => $this->stats['errors'],
            'users_created' => $this->stats['users_created'],
            'users_found' => $this->stats['users_found'],
            'users_id_generated' => $this->stats['users_id_generated'] ?? 0, // ADD THIS LINE
            'bulk_operations' => $this->stats['bulk_operations'],
            'single_row_fallbacks' => $this->stats['single_row_fallbacks']
        ];

        $calculatedStats = [
            'success_rate' => $this->stats['processed'] > 0
                ? round(($this->stats['inserted'] / $this->stats['processed']) * 100, 2)
                : 0,
            'error_rate' => $this->stats['processed'] > 0
                ? round(($this->stats['errors'] / $this->stats['processed']) * 100, 2)
                : 0,
            'skip_rate' => $this->stats['processed'] > 0
                ? round(($this->stats['skipped'] / $this->stats['processed']) * 100, 2)
                : 0,
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'user_match_strategies' => $this->stats['users_matched_by_strategy'] ?? [],
            'error_breakdown' => $this->stats['error_types'] ?? []
        ];

        return array_merge($baseStats, $calculatedStats);
    }

    public static function getImportStatus(): array
    {
        $lockData = Cache::get(self::$lockKey);
        $progressData = Cache::get(self::$progressKey, []);

        return [
            'is_running' => Cache::has(self::$lockKey),
            'lock_data' => $lockData,
            'progress' => $progressData,
            'can_start_new' => !Cache::has(self::$lockKey)
        ];
    }

    public static function forceClearLock(): bool
    {
        $cleared = Cache::forget(self::$lockKey);
        $progressCleared = Cache::forget(self::$progressKey);

        Log::warning('Import lock force cleared', [
            'lock_cleared' => $cleared,
            'progress_cleared' => $progressCleared
        ]);

        return $cleared;
    }

    private function acquireImportLock(): bool
    {
        if (Cache::has(self::$lockKey)) {
            $lockData = Cache::get(self::$lockKey);
            if (
                isset($lockData['started_at']) &&
                Carbon::parse($lockData['started_at'])->addSeconds(self::$lockTimeout)->isPast()
            ) {
                Log::warning('Found stale import lock, clearing it', [
                    'lock_data' => $lockData,
                    'lock_age_minutes' => Carbon::parse($lockData['started_at'])->diffInMinutes()
                ]);
                Cache::forget(self::$lockKey);
                Cache::forget(self::$progressKey);
            } else {
                return false;
            }
        }

        $lockData = [
            'started_at' => now()->toISOString(),
            'process_id' => getmypid(),
            'server' => gethostname(),
            'process' => getmypid() ?: 'unknown'
        ];

        Cache::put(self::$lockKey, $lockData, self::$lockTimeout);
        Log::info('Import lock acquired', $lockData);
        return true;
    }

    private function releaseImportLock(): void
    {
        $this->releaseLock();
    }

    private function bulkInsertSigninLogs(array $signinLogs)
    {
        return $this->executeOptimalInsert($signinLogs, 0);
    }

    private function processRowSafely(array $row, int $batchNumber, int $rowIndex): array
    {
        try {
            return $this->processRow($row, $batchNumber, $rowIndex);
        } catch (\Exception $e) {
            // Categorize the error for better debugging
            $errorType = $this->categorizeError($e);

            Log::error("Row processing failed", [
                'batch' => $batchNumber,
                'row' => $rowIndex,
                'error_type' => $errorType,
                'error' => $e->getMessage(),
                'username' => $row['username'] ?? 'unknown',
                'row_sample' => array_slice($row, 0, 3), // First 3 fields only
                'trace' => substr($e->getTraceAsString(), 0, 200)
            ]);

            $this->stats['errors']++;
            $this->stats['error_types'][$errorType] = ($this->stats['error_types'][$errorType] ?? 0) + 1;

            return ['signin_log' => null, 'user_system' => null];
        }
    }

    private function categorizeError(\Exception $e): string
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, 'user') || str_contains($message, 'principal')) {
            return 'user_related';
        } elseif (str_contains($message, 'date') || str_contains($message, 'time')) {
            return 'date_parsing';
        } elseif (str_contains($message, 'database') || str_contains($message, 'sql')) {
            return 'database_error';
        } elseif (str_contains($message, 'validation') || str_contains($message, 'required')) {
            return 'validation_error';
        } elseif (str_contains($message, 'duplicate') || str_contains($message, 'constraint')) {
            return 'duplicate_entry';
        } elseif (str_contains($message, 'memory') || str_contains($message, 'timeout')) {
            return 'resource_error';
        } else {
            return 'unknown';
        }
    }

    private function logFinalStats(): void
    {
        $finalStats = $this->getStats();

        Log::info('Import completed - Final Statistics', [
            'summary' => [
                'total_processed' => $finalStats['processed'],
                'successful_inserts' => $finalStats['inserted'],
                'skipped_records' => $finalStats['skipped'],
                'total_errors' => $finalStats['errors'],
                'success_rate' => $finalStats['success_rate'] . '%',
                'error_rate' => $finalStats['error_rate'] . '%'
            ],
            'user_management' => [
                'users_created' => $finalStats['users_created'],
                'users_found' => $finalStats['users_found'],
                'match_strategies' => $finalStats['user_match_strategies']
            ],
            'performance' => [
                'bulk_operations' => $finalStats['bulk_operations'],
                'single_row_fallbacks' => $finalStats['single_row_fallbacks'],
                'peak_memory_mb' => $finalStats['memory_peak_mb']
            ],
            'error_analysis' => $finalStats['error_breakdown']
        ]);

        // Log warnings for concerning statistics
        if ($finalStats['error_rate'] > 10) {
            Log::warning('High error rate detected', [
                'error_rate' => $finalStats['error_rate'] . '%',
                'recommendation' => 'Review error logs and consider data quality improvements'
            ]);
        }

        if ($finalStats['skip_rate'] > 20) {
            Log::warning('High skip rate detected', [
                'skip_rate' => $finalStats['skip_rate'] . '%',
                'recommendation' => 'Review data format and validation rules'
            ]);
        }
    }

    /**
     * Enhanced validateSigninLogData method with error collection
     */
    private function validateSigninLogData(array $data, int $batchNumber = 0, int $rowIndex = 0): bool
    {
        $requiredFields = ['date_utc', 'user_id'];
        $errors = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === null || $data[$field] === '') {
                $errors[] = "Missing required field: {$field}";
            }
        }

        // Validate field lengths
        if (isset($data['username']) && strlen($data['username']) > 255) {
            $errors[] = "Username too long: " . strlen($data['username']) . " chars";
        }

        if (isset($data['user_agent']) && strlen($data['user_agent']) > 500) {
            $data['user_agent'] = substr($data['user_agent'], 0, 500);
        }

        if (isset($data['date_utc'])) {
            try {
                Carbon::parse($data['date_utc']);
            } catch (\Exception $e) {
                $errors[] = "Invalid date format: " . $data['date_utc'];
            }
        }

        if (!empty($errors)) {
            // Store validation errors for later retrieval
            $errorKey = "batch_{$batchNumber}_row_{$rowIndex}";
            $this->validationErrors[$errorKey] = [
                'batch' => $batchNumber,
                'row' => $rowIndex,
                'errors' => $errors,
                'data_sample' => array_slice($data, 0, 5, true),
                'username' => $data['username'] ?? 'unknown'
            ];

            Log::warning('Data validation failed', [
                'batch' => $batchNumber,
                'row' => $rowIndex,
                'errors' => $errors,
                'data_sample' => array_slice($data, 0, 5, true)
            ]);
            return false;
        }

        return true;
    }

    /**
     * Enhanced logging for failed user lookups to help with debugging
     */
    private function logFailedUserLookup(string $searchTerm, ?string $userIdFromCsv = null): void
    {
        // Only log detailed info for first few failures to avoid spam
        static $failureCount = 0;
        $failureCount++;

        if ($failureCount <= 5) {
            // Get a sample of existing usernames for comparison
            $sampleUsers = AzureUser::select('userPrincipalName', 'id', 'mail')
                ->whereRaw('LOWER(userPrincipalName) LIKE ?', ['%' . strtolower(substr($searchTerm, 0, 3)) . '%'])
                ->limit(3)
                ->get()
                ->toArray();

            Log::warning("User lookup failed - no match found", [
                'search_term' => $searchTerm,
                'user_id_from_csv' => $userIdFromCsv,
                'failure_count' => $failureCount,
                'similar_users_sample' => $sampleUsers,
                'total_users_in_db' => AzureUser::count(),
                'search_term_length' => strlen($searchTerm),
                'contains_at_symbol' => str_contains($searchTerm, '@'),
                'is_email_format' => filter_var($searchTerm, FILTER_VALIDATE_EMAIL) ? 'yes' : 'no'
            ]);
        } else if ($failureCount == 6) {
            Log::info("Suppressing detailed user lookup failure logs after 5 failures");
        }
    }

    /**
     * Add method to get detailed import statistics including validation errors
     */
    public function getDetailedStats(): array
    {
        $baseStats = $this->getStats();

        return array_merge($baseStats, [
            'validation_errors' => [
                'total_validation_failures' => count($this->validationErrors),
                'error_details' => array_slice($this->validationErrors, 0, 10), // First 10 errors only
                'common_error_types' => $this->analyzeCommonErrors()
            ],
            'user_lookup_analysis' => [
                'total_lookup_attempts' => array_sum($this->stats['users_matched_by_strategy']),
                'successful_strategies' => array_filter($this->stats['users_matched_by_strategy'], fn($count) => $count > 0)
            ]
        ]);
    }


    /**
     * Analyze common validation error patterns
     */
    private function analyzeCommonErrors(): array
    {
        $errorTypes = [];

        foreach ($this->validationErrors as $errorData) {
            foreach ($errorData['errors'] as $error) {
                $type = $this->categorizeValidationError($error);
                $errorTypes[$type] = ($errorTypes[$type] ?? 0) + 1;
            }
        }

        return $errorTypes;
    }


    /**
     * Categorize validation errors for better reporting
     */
    private function categorizeValidationError(string $error): string
    {
        if (str_contains($error, 'user_id')) {
            return 'missing_user_id';
        } elseif (str_contains($error, 'date_utc')) {
            return 'missing_or_invalid_date';
        } elseif (str_contains($error, 'system')) {
            return 'missing_system';
        } elseif (str_contains($error, 'too long')) {
            return 'field_length_exceeded';
        } else {
            return 'other_validation_error';
        }
    }

    private function isDuplicateRecord(array $signinLogData): bool
    {
        try {
            // Check using the actual primary key constraint fields
            $exists = DB::table('Azure_LoginDetails')
                ->where('date_utc', $signinLogData['date_utc'])
                ->where('request_id', $signinLogData['request_id'])
                ->where('user_id', $signinLogData['user_id'])
                ->exists();

            if ($exists) {
                Log::debug('Duplicate record detected via database check', [
                    'date_utc' => $signinLogData['date_utc'],
                    'request_id' => $signinLogData['request_id'],
                    'user_id' => $signinLogData['user_id'],
                    'username' => $signinLogData['username'] ?? 'unknown'
                ]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Error checking for duplicate record', [
                'error' => $e->getMessage(),
                'data' => $signinLogData
            ]);
            // Return false to allow processing - better to attempt insert than skip incorrectly
            return false;
        }
    }


    /**
     * CRITICAL FIX: Comprehensive user lookup with validation
     */
    private function findUserWithValidation(string $userPrincipalName, ?string $userIdFromCsv = null)
    {
        try {
            // Strategy 1: Direct username match with ID validation
            $user = AzureUser::whereRaw('LOWER(RTRIM(LTRIM(userPrincipalName))) = LOWER(?)', [trim($userPrincipalName)])
                ->whereNotNull('id')  // CRITICAL: Only get users with valid IDs
                ->where('id', '!=', '') // Additional safety check
                ->first();

            if ($user) {
                Log::debug('User found via username with valid ID', [
                    'username' => $userPrincipalName,
                    'found_username' => $user->userPrincipalName,
                    'user_id' => $user->id
                ]);
                $this->stats['users_matched_by_strategy']['username']++;
                return $user;
            }

            // Strategy 2: Try by exact ID if provided
            if (!empty($userIdFromCsv) && $this->isValidUuid($userIdFromCsv)) {
                $user = AzureUser::where('id', $userIdFromCsv)->first();
                if ($user) {
                    Log::debug('User found via exact ID', [
                        'username' => $userPrincipalName,
                        'user_id' => $user->id
                    ]);
                    $this->stats['users_matched_by_strategy']['exact_id']++;
                    return $user;
                }
            }

            // Strategy 3: Email field match
            if (filter_var($userPrincipalName, FILTER_VALIDATE_EMAIL)) {
                $user = AzureUser::whereRaw('LOWER(RTRIM(LTRIM(mail))) = LOWER(?)', [trim($userPrincipalName)])
                    ->whereNotNull('id')
                    ->where('id', '!=', '')
                    ->first();

                if ($user) {
                    Log::debug('User found via email with valid ID', [
                        'username' => $userPrincipalName,
                        'user_id' => $user->id
                    ]);
                    $this->stats['users_matched_by_strategy']['email']++;
                    return $user;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in user lookup', [
                'username' => $userPrincipalName,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }
}
