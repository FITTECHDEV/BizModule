<?php

namespace App\Services;

use App\Models\Logintracker\AzureUser;
use App\Models\Logintracker\SigninLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AzureSignInImportService
{
    /** SQL Server max parameter limit */
    private int $maxParams = 2100;
    
    /** Conservative safety buffer under the limit */
    private int $paramBuffer = 300;
    
    /** Maximum rows per super batch */
    private int $superBatchSize;
    
    /** how often to log progress (slices) */
    private int $logEveryN = 5;
    
    /** target table name */
    private string $table = 'Azure_LoginDetails';
    
    /** cached table columns to avoid repeated schema calls */
    private ?array $tableColumns = null;
    
    /** Force single row inserts for very wide tables */
    private bool $forceSingleRowMode = false;
    
    /** Track user matching strategies */
    private array $userStrategies = [];
    
    /** Cache for user lookups */
    private array $userCache = [];

    public function __construct()
    {
        $this->superBatchSize = (int) (env('SIGNIN_IMPORT_SUPER_BATCH', 25));
        $this->logEveryN = (int) (env('SIGNIN_IMPORT_LOG_EVERY_N', 5));
        $this->paramBuffer = (int) (env('SIGNIN_IMPORT_PARAM_BUFFER', 300));
        $this->forceSingleRowMode = env('SIGNIN_IMPORT_FORCE_SINGLE_ROW', false);
        
        $this->userStrategies = [
            'exact_id' => 0,
            'username' => 0,
            'email' => 0,
            'sign_in_identifier' => 0,
            'created_new' => 0
        ];
    }

    /**
     * Import data with proper user relationship handling
     */
    public function importFromArray(array $data): array
    {
        $stats = $this->initStats(count($data));
        $start = microtime(true);

        if (empty($data)) {
            return $stats;
        }

        // Pre-warm user cache for better performance
        $this->prewarmUserCache($data);

        // Process in batches WITHOUT nested transactions
        foreach (array_chunk($data, $this->superBatchSize) as $i => $superBatch) {
            $batchResult = $this->processSuperBatchWithUsers($superBatch, $i + 1);
            
            // Merge stats
            $stats['successful_imports'] += $batchResult['successful_imports'];
            $stats['failed_imports'] += $batchResult['failed_imports'];
            $stats['users_created'] += $batchResult['users_created'];
            $stats['users_found'] += $batchResult['users_found'];
            
            // Merge strategy stats
            foreach ($batchResult['users_matched_by_strategy'] as $strategy => $count) {
                $stats['users_matched_by_strategy'][$strategy] += $count;
            }
        }

        $this->finalizeStats($stats, $start);
        return $stats;
    }

    /**
     * Pre-warm user cache to avoid repeated DB lookups
     */
    private function prewarmUserCache(array $data): void
    {
        $userIds = [];
        $usernames = [];
        $emails = [];
        
        foreach ($data as $row) {
            if (!empty($row['user_id'])) {
                $userIds[] = $row['user_id'];
            }
            if (!empty($row['username'])) {
                $usernames[] = strtolower(trim($row['username']));
            }
            if (!empty($row['sign_in_identifier']) && filter_var($row['sign_in_identifier'], FILTER_VALIDATE_EMAIL)) {
                $emails[] = strtolower(trim($row['sign_in_identifier']));
            }
        }

        // Cache users by ID
        if (!empty($userIds)) {
            $users = AzureUser::whereIn('id', array_unique($userIds))->get();
            foreach ($users as $user) {
                $this->userCache['id'][$user->id] = $user;
            }
        }

        // Cache users by username
        if (!empty($usernames)) {
            $users = AzureUser::whereIn(DB::raw('LOWER(userPrincipalName)'), array_unique($usernames))->get();
            foreach ($users as $user) {
                $key = strtolower($user->userPrincipalName);
                $this->userCache['username'][$key] = $user;
            }
        }

        // Cache users by email
        if (!empty($emails)) {
            $users = AzureUser::whereIn(DB::raw('LOWER(mail)'), array_unique($emails))->get();
            foreach ($users as $user) {
                if ($user->mail) {
                    $key = strtolower($user->mail);
                    $this->userCache['email'][$key] = $user;
                }
            }
        }

        Log::info('User cache prewarmed', [
            'cached_by_id' => count($this->userCache['id'] ?? []),
            'cached_by_username' => count($this->userCache['username'] ?? []),
            'cached_by_email' => count($this->userCache['email'] ?? [])
        ]);
    }

    /**
     * Process super batch with user relationship handling
     */
    private function processSuperBatchWithUsers(array $rows, int $batchNo): array
    {
        $stats = [
            'successful_imports' => 0,
            'failed_imports' => 0,
            'users_created' => 0,
            'users_found' => 0,
            'users_matched_by_strategy' => [
                'exact_id' => 0,
                'username' => 0,
                'email' => 0,
                'sign_in_identifier' => 0,
                'created_new' => 0
            ]
        ];

        $processedRows = [];
        
        foreach ($rows as $rowIndex => $row) {
            try {
                // Find or create user
                $userResult = $this->findOrCreateUser($row);
                
                if ($userResult['found']) {
                    $stats['users_found']++;
                    $stats['users_matched_by_strategy'][$userResult['strategy']]++;
                } elseif ($userResult['created']) {
                    $stats['users_created']++;
                    $stats['users_matched_by_strategy']['created_new']++;
                }

                if ($userResult['user']) {
                    // Prepare signin record with proper user relationship
                    $signinData = $this->prepareSigninRecord($row, $userResult['user']);
                    $processedRows[] = $signinData;
                } else {
                    Log::warning("No user found/created for signin", [
                        'batch' => $batchNo,
                        'row' => $rowIndex,
                        'user_id' => $row['user_id'] ?? null,
                        'username' => $row['username'] ?? null
                    ]);
                    $stats['failed_imports']++;
                }

            } catch (\Exception $e) {
                Log::error("Failed to process row in batch {$batchNo}", [
                    'row_index' => $rowIndex,
                    'error' => $e->getMessage(),
                    'row_data' => array_slice($row, 0, 5) // Only log first 5 fields for brevity
                ]);
                $stats['failed_imports']++;
            }
        }

        // Insert processed rows
        if (!empty($processedRows)) {
            $insertResult = $this->insertSigninRecords($processedRows, $batchNo);
            $stats['successful_imports'] = $insertResult['successful'];
            $stats['failed_imports'] += $insertResult['failed'];
        }

        return $stats;
    }

    /**
     * Find or create user with caching
     */
    private function findOrCreateUser(array $row): array
    {
        // Strategy 1: Exact ID match
        if (!empty($row['user_id'])) {
            $userId = trim($row['user_id']);
            if (isset($this->userCache['id'][$userId])) {
                return ['user' => $this->userCache['id'][$userId], 'found' => true, 'created' => false, 'strategy' => 'exact_id'];
            }
            
            // Check database if not in cache
            $user = AzureUser::find($userId);
            if ($user) {
                $this->userCache['id'][$userId] = $user;
                return ['user' => $user, 'found' => true, 'created' => false, 'strategy' => 'exact_id'];
            }
        }

        // Strategy 2: Username match (case-insensitive)
        if (!empty($row['username'])) {
            $username = strtolower(trim($row['username']));
            if (isset($this->userCache['username'][$username])) {
                return ['user' => $this->userCache['username'][$username], 'found' => true, 'created' => false, 'strategy' => 'username'];
            }
            
            // Check database if not in cache
            $user = AzureUser::where(DB::raw('LOWER(userPrincipalName)'), $username)->first();
            if ($user) {
                $this->userCache['username'][$username] = $user;
                return ['user' => $user, 'found' => true, 'created' => false, 'strategy' => 'username'];
            }
        }

        // Strategy 3: Email match
        if (!empty($row['sign_in_identifier'])) {
            $identifier = trim($row['sign_in_identifier']);
            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                $email = strtolower($identifier);
                if (isset($this->userCache['email'][$email])) {
                    return ['user' => $this->userCache['email'][$email], 'found' => true, 'created' => false, 'strategy' => 'email'];
                }
                
                // Check database if not in cache
                $user = AzureUser::where(DB::raw('LOWER(mail)'), $email)->first();
                if ($user) {
                    $this->userCache['email'][$email] = $user;
                    return ['user' => $user, 'found' => true, 'created' => false, 'strategy' => 'email'];
                }
            }
        }

        // Strategy 4: Sign-in identifier as username
        if (!empty($row['sign_in_identifier'])) {
            $identifier = strtolower(trim($row['sign_in_identifier']));
            if (isset($this->userCache['username'][$identifier])) {
                return ['user' => $this->userCache['username'][$identifier], 'found' => true, 'created' => false, 'strategy' => 'sign_in_identifier'];
            }
            
            $user = AzureUser::where(DB::raw('LOWER(userPrincipalName)'), $identifier)->first();
            if ($user) {
                $this->userCache['username'][$identifier] = $user;
                return ['user' => $user, 'found' => true, 'created' => false, 'strategy' => 'sign_in_identifier'];
            }
        }

        // Strategy 5: Create new user (conservative approach)
        if (env('SIGNIN_IMPORT_CREATE_USERS', false) && !empty($row['username'])) {
            try {
                $userData = [
                    'userPrincipalName' => trim($row['username']),
                    'displayName' => $row['user'] ?? trim($row['username']),
                    'mail' => filter_var($row['username'], FILTER_VALIDATE_EMAIL) ? $row['username'] : null,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                // Use user_id if it looks like a valid UUID
                if (!empty($row['user_id']) && Str::isUuid($row['user_id'])) {
                    $userData['id'] = $row['user_id'];
                }

                $user = AzureUser::create($userData);
                
                // Cache the new user
                $this->userCache['id'][$user->id] = $user;
                $this->userCache['username'][strtolower($user->userPrincipalName)] = $user;
                if ($user->mail) {
                    $this->userCache['email'][strtolower($user->mail)] = $user;
                }

                Log::info('Created new user during import', [
                    'user_id' => $user->id,
                    'username' => $user->userPrincipalName,
                    'source_data' => [
                        'user_id' => $row['user_id'] ?? null,
                        'username' => $row['username'] ?? null
                    ]
                ]);

                return ['user' => $user, 'found' => false, 'created' => true, 'strategy' => 'created_new'];

            } catch (\Exception $e) {
                Log::error('Failed to create new user during import', [
                    'error' => $e->getMessage(),
                    'row_data' => [
                        'user_id' => $row['user_id'] ?? null,
                        'username' => $row['username'] ?? null,
                        'sign_in_identifier' => $row['sign_in_identifier'] ?? null
                    ]
                ]);
            }
        }

        return ['user' => null, 'found' => false, 'created' => false, 'strategy' => 'none'];
    }

    /**
     * Prepare signin record with proper relationships
     */
    private function prepareSigninRecord(array $row, AzureUser $user): array
    {
        $signinData = [];
        $tableColumns = $this->getTableColumns();

        // Map the user properly
        $signinData['user_id'] = $user->id;
        $signinData['username'] = $user->userPrincipalName;

        // Map other fields based on CSV columns
        $columnMappings = [
            'system' => ['system', 'System'],
            'application' => ['application', 'Application', 'app_name'],
            'application_id' => ['application_id', 'ApplicationId', 'app_id'],
            'date_utc' => ['date_utc', 'DateUTC', 'date', 'timestamp'],
            'location' => ['location', 'Location'],
            'device_os' => ['device_os', 'DeviceOS', 'os'],
            'browser' => ['browser', 'Browser'],
            'ip_address' => ['ip_address', 'IPAddress', 'ip']
        ];

        foreach ($columnMappings as $dbColumn => $possibleKeys) {
            if (!in_array($dbColumn, $tableColumns)) continue;
            
            foreach ($possibleKeys as $key) {
                if (isset($row[$key]) && !empty($row[$key])) {
                    $signinData[$dbColumn] = $this->coerceValueForColumn($dbColumn, $row[$key]);
                    break;
                }
            }
        }

        // Generate ID if needed
        if (in_array('id', $tableColumns) && !isset($signinData['id'])) {
            if (!empty($row['id']) && Str::isUuid($row['id'])) {
                $signinData['id'] = $row['id'];
            } else {
                $signinData['id'] = (string) Str::uuid();
            }
        }

        // Add timestamps
        if (in_array('created_at', $tableColumns)) {
            $signinData['created_at'] = now();
        }
        if (in_array('updated_at', $tableColumns)) {
            $signinData['updated_at'] = now();
        }

        // Ensure we only include valid columns
        return array_intersect_key($signinData, array_flip($tableColumns));
    }

    /**
     * Insert signin records with proper error handling
     */
    private function insertSigninRecords(array $records, int $batchNo): array
    {
        $successful = 0;
        $failed = 0;
        
        if ($this->forceSingleRowMode || count($records) <= 5) {
            // Single row mode
            foreach ($records as $record) {
                try {
                    DB::table($this->table)->insert($record);
                    $successful++;
                } catch (\Exception $e) {
                    $failed++;
                    Log::error("Failed to insert signin record", [
                        'batch' => $batchNo,
                        'error' => $e->getMessage(),
                        'user_id' => $record['user_id'] ?? null
                    ]);
                }
            }
        } else {
            // Batch insert
            try {
                DB::table($this->table)->insert($records);
                $successful = count($records);
                
                Log::info("Batch {$batchNo}: inserted {$successful} signin records");
                
            } catch (\Exception $e) {
                Log::error("Batch insert failed, falling back to single rows", [
                    'batch' => $batchNo,
                    'error' => $e->getMessage()
                ]);
                
                // Fallback to single row inserts
                foreach ($records as $record) {
                    try {
                        DB::table($this->table)->insert($record);
                        $successful++;
                    } catch (\Exception $e2) {
                        $failed++;
                    }
                }
            }
        }
        
        return ['successful' => $successful, 'failed' => $failed];
    }

    /**
     * Get table columns with caching
     */
    private function getTableColumns(): array
    {
        if ($this->tableColumns === null) {
            $this->tableColumns = Schema::getColumnListing($this->table);
        }
        return $this->tableColumns;
    }

    /**
     * Coerce values based on column names
     */
    private function coerceValueForColumn(string $col, $val)
    {
        if ($val === '' || $val === null) {
            return null;
        }

        $lower = strtolower((string) $col);
        
        // Date columns
        if (str_contains($lower, 'date') || str_contains($lower, 'time') || 
            $lower === 'date_utc' || $lower === 'created_at' || $lower === 'updated_at') {
            if (strtotime($val) !== false) {
                return Carbon::parse($val)->format('Y-m-d H:i:s');
            }
            return null;
        }
        
        // Boolean columns
        if (str_contains($lower, 'flag') || str_contains($lower, 'enabled') || 
            $lower === 'compliant' || $lower === 'managed') {
            $v = strtolower(trim((string)$val));
            return in_array($v, ['1', 'true', 'yes']) ? 1 : 0;
        }

        return trim((string) $val);
    }

    /**
     * Initialize statistics array
     */
    private function initStats(int $total = 0): array
    {
        return [
            'successful_imports' => 0,
            'failed_imports' => 0,
            'total_records' => $total,
            'skipped_rows' => 0,
            'users_created' => 0,
            'users_found' => 0,
            'users_matched_by_strategy' => [
                'exact_id' => 0,
                'username' => 0,
                'email' => 0,
                'sign_in_identifier' => 0,
                'created_new' => 0
            ],
            'duration_seconds' => 0,
            'records_per_second' => 0,
        ];
    }

    /**
     * Finalize statistics with timing
     */
    private function finalizeStats(array &$stats, float $startTime): void
    {
        $stats['duration_seconds'] = round(microtime(true) - $startTime, 3);
        $stats['records_per_second'] = $stats['duration_seconds'] > 0
            ? round($stats['successful_imports'] / $stats['duration_seconds'], 2)
            : 0;
    }
}