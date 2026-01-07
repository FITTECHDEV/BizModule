<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class OdooSyncController extends Controller
{
    /**
     * Sync users from Odoo (PostgreSQL) to Azure_Users (SQL Server)
     */
    public function syncUsers()
    {
        $startTime = now();
        $stats = [
            'processed' => 0,
            'inserted' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        $this->outputHeader($startTime);

        try {
            // STEP 1: Fetch users from Odoo
            echo "<p class='info'>📥 <strong>Step 1:</strong> Fetching users from Odoo...</p>";
            flush();

            $odooUsers = DB::connection('pgsql')->select('SELECT * FROM vw_getuserdetails');
            $totalUsers = count($odooUsers);

            echo "<p class='success'>✅ Found <strong>{$totalUsers}</strong> users in Odoo</p>";
            flush();

            if ($totalUsers === 0) {
                echo "<p class='error'>⚠️ No users found. Sync aborted.</p></body></html>";

                return;
            }

            // STEP 2: Begin transaction and process users
            echo "<p class='info'>⚙️ <strong>Step 2:</strong> Processing users...</p>";
            echo '<table><tr><th>#</th><th>User Principal Name</th><th>User Type</th><th>Action</th><th>Status</th><th>Note</th></tr>';
            flush();

            DB::connection('sqlsrv')->transaction(function () use ($odooUsers, &$stats) {
                foreach ($odooUsers as $index => $user) {
                    $stats['processed']++;
                    $rowNumber = $index + 1;

                    try {
                        // Normalize user data
                        $userData = $this->normalizeUserData($user);

                        // Check if user exists
                        $existingUser = DB::connection('sqlsrv')
                            ->table('Azure_Users')
                            ->where('userPrincipalName', $userData['userPrincipalName'])
                            ->first();

                        if (! $existingUser) {
                            // NEW USER: Insert with Status = "Pending"
                            $this->insertUserImproved($userData);
                            $stats['inserted']++;
                            $this->outputRow(
                                $rowNumber,
                                $userData['userPrincipalName'],
                                $userData['OdooLoginType'],
                                'INSERT',
                                true,
                                'Created with Pending status'
                            );
                        } else {
                            // EXISTING USER: Update and set to "Active"
                            $previousStatus = $existingUser->Status ?? 'Unknown';
                            $this->updateUserImproved($userData);
                            $stats['updated']++;

                            $statusNote = $previousStatus === 'Pending'
                                ? 'Updated from Pending to Active'
                                : 'Data refreshed, Status: Active';

                            $this->outputRow(
                                $rowNumber,
                                $userData['userPrincipalName'],
                                $userData['OdooLoginType'],
                                'UPDATE',
                                true,
                                $statusNote
                            );
                        }
                        flush();
                    } catch (Exception $e) {
                        $stats['failed']++;
                        $stats['errors'][] = [
                            'user' => $userData['userPrincipalName'] ?? 'Unknown',
                            'error' => $e->getMessage(),
                        ];

                        $this->outputRow(
                            $rowNumber,
                            $userData['userPrincipalName'] ?? 'Unknown',
                            'Unknown',
                            'FAILED',
                            false,
                            $e->getMessage()
                        );
                        flush();
                    }
                }
            });

            echo '</table>';
            $this->outputSummary($startTime, $stats);
        } catch (Exception $e) {
            $this->outputError($e);
        }

        echo '</body></html>';
    }

    /**
     * Sync users from Odoo via AJAX (for manual sync button)
     */
    public function syncUsersAjax()
    {
        $startTime = now();

        try {
            Log::info('=== MANUAL ODOO SYNC STARTED ===', [
                'initiated_by' => Auth::id(),
                'time' => $startTime,
            ]);

            $stats = [
                'processed' => 0,
                'inserted' => 0,
                'updated' => 0,
                'failed' => 0,
                'errors' => [],
            ];

            // ====================================================================
            // STEP 1: CONNECT TO ODOO DATABASE
            // ====================================================================
            try {
                Log::info('Step 1: Connecting to Odoo database...');

                // Test connection
                DB::connection('pgsql')->getPdo();

                Log::info('✓ Step 1: Connected to Odoo successfully');
            } catch (\PDOException $e) {
                Log::error('✗ Step 1 FAILED: Could not connect to Odoo database', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                ]);

                return response()->json([
                    'success' => false,
                    'error_type' => 'odoo_connection_failed',
                    'retry_possible' => true,
                    'step_failed' => 'odoo_connection',
                    'message' => 'Could not connect to Odoo database',
                    'technical_details' => $e->getMessage(),
                    'troubleshooting' => [
                        'Verify Odoo (PostgreSQL) server is running',
                        'Check DB_PGSQL_HOST, DB_PGSQL_DATABASE in .env file',
                        'Test network connectivity to Odoo server',
                        'Verify database credentials are correct',
                        'Check firewall rules allow connection to PostgreSQL port',
                    ],
                ], 500);
            } catch (\Exception $e) {
                Log::error('✗ Step 1 FAILED: Unexpected connection error', [
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'error_type' => 'connection_error',
                    'retry_possible' => true,
                    'step_failed' => 'odoo_connection',
                    'message' => 'Failed to connect to Odoo',
                    'technical_details' => $e->getMessage(),
                    'troubleshooting' => [
                        'Check your internet connection',
                        'Verify VPN is connected if required',
                        'Contact IT support if issue persists',
                    ],
                ], 500);
            }

            // ====================================================================
            // STEP 2: FETCH USERS FROM ODOO
            // ====================================================================
            try {
                Log::info('Step 2: Fetching users from Odoo view...');

                $odooUsers = DB::connection('pgsql')->select('SELECT * FROM vw_getuserdetails');

                if (empty($odooUsers)) {
                    Log::warning('No users found in Odoo view');

                    return response()->json([
                        'success' => false,
                        'error_type' => 'no_data',
                        'retry_possible' => false,
                        'step_failed' => 'data_fetch',
                        'message' => 'No users found in Odoo',
                        'technical_details' => 'vw_getuserdetails returned 0 rows',
                        'troubleshooting' => [
                            'Check if vw_getuserdetails view exists in Odoo database',
                            'Verify the view has data: SELECT COUNT(*) FROM vw_getuserdetails',
                            'Check if view permissions are correct',
                            'Verify employees exist in Odoo HR module',
                        ],
                    ], 404);
                }

                Log::info('✓ Step 2: Fetched users from Odoo', [
                    'count' => count($odooUsers),
                ]);
            } catch (\Exception $e) {
                Log::error('✗ Step 2 FAILED: Could not fetch users from Odoo', [
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'error_type' => 'odoo_query_failed',
                    'retry_possible' => true,
                    'step_failed' => 'data_fetch',
                    'message' => 'Failed to fetch users from Odoo view',
                    'technical_details' => $e->getMessage(),
                    'troubleshooting' => [
                        'Check if vw_getuserdetails view exists',
                        'Verify database user has SELECT permission on the view',
                        'Run query manually in pgAdmin to test: SELECT * FROM vw_getuserdetails',
                        'Check Odoo database logs for errors',
                    ],
                ], 500);
            }

            // ====================================================================
            // STEP 3: CONNECT TO SQL SERVER
            // ====================================================================
            try {
                Log::info('Step 3: Connecting to SQL Server...');

                // Test connection
                DB::connection('sqlsrv')->getPdo();

                Log::info('✓ Step 3: Connected to SQL Server successfully');
            } catch (\Exception $e) {
                Log::error('✗ Step 3 FAILED: Could not connect to SQL Server', [
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'error_type' => 'sqlserver_connection_failed',
                    'retry_possible' => true,
                    'step_failed' => 'sqlserver_connection',
                    'message' => 'Could not connect to SQL Server database',
                    'technical_details' => $e->getMessage(),
                    'troubleshooting' => [
                        'Verify SQL Server is running',
                        'Check DB_HOST, DB_DATABASE in .env file',
                        'Verify SQL Server credentials are correct',
                        'Check SQL Server allows remote connections',
                        'Verify SQL Server service is started',
                    ],
                ], 500);
            }

            // ====================================================================
            // STEP 4: PROCESS USERS IN TRANSACTION
            // ====================================================================
            try {
                Log::info('Step 4: Processing users...');

                DB::connection('sqlsrv')->transaction(function () use ($odooUsers, &$stats) {
                    foreach ($odooUsers as $user) {
                        $stats['processed']++;

                        try {
                            $userData = $this->normalizeUserData($user);

                            $existingUser = DB::connection('sqlsrv')
                                ->table('Azure_Users')
                                ->where('userPrincipalName', $userData['userPrincipalName'])
                                ->first();

                            if (!$existingUser) {
                                // NEW USER: Insert with Status = "Pending"
                                $this->insertUserImproved($userData);
                                $stats['inserted']++;

                                Log::debug('Inserted new user', [
                                    'email' => $userData['userPrincipalName'],
                                ]);
                            } else {
                                // EXISTING USER: Update and set to "Active"
                                $this->updateUserImproved($userData);
                                $stats['updated']++;

                                Log::debug('Updated existing user', [
                                    'email' => $userData['userPrincipalName'],
                                ]);
                            }
                        } catch (QueryException $e) {
                            $stats['failed']++;
                            $errorDetails = [
                                'user' => $userData['userPrincipalName'] ?? 'Unknown',
                                'error' => $e->getMessage(),
                                'sql_state' => $e->getCode(),
                            ];

                            $stats['errors'][] = $errorDetails;

                            Log::error('Database error syncing user', $errorDetails);
                        } catch (Exception $e) {
                            $stats['failed']++;
                            $errorDetails = [
                                'user' => $userData['userPrincipalName'] ?? 'Unknown',
                                'error' => $e->getMessage(),
                            ];

                            $stats['errors'][] = $errorDetails;

                            Log::error('Failed to sync user', $errorDetails);
                        }
                    }
                });

                Log::info('✓ Step 4: Users processed successfully', [
                    'processed' => $stats['processed'],
                    'inserted' => $stats['inserted'],
                    'updated' => $stats['updated'],
                    'failed' => $stats['failed'],
                ]);
            } catch (\Exception $e) {
                Log::error('✗ Step 4 FAILED: Transaction failed', [
                    'error' => $e->getMessage(),
                    'stats_before_failure' => $stats,
                ]);

                return response()->json([
                    'success' => false,
                    'error_type' => 'database_transaction_failed',
                    'retry_possible' => true,
                    'step_failed' => 'user_processing',
                    'message' => 'Database transaction failed while processing users',
                    'technical_details' => $e->getMessage(),
                    'stats' => $stats,
                    'troubleshooting' => [
                        'Check SQL Server transaction log for details',
                        'Verify database has enough space',
                        'Check for database locks or deadlocks',
                        'Review error details above for specific user failures',
                        'If issue persists, try syncing users in smaller batches',
                    ],
                ], 500);
            }

            // ====================================================================
            // STEP 5: CALCULATE DURATION AND RETURN SUCCESS
            // ====================================================================
            $duration = $startTime->diffInSeconds(now());

            Log::info('=== MANUAL ODOO SYNC COMPLETED ===', [
                'stats' => $stats,
                'duration_seconds' => $duration,
                'initiated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sync completed successfully',
                'stats' => $stats,
                'duration_seconds' => $duration,
                'error_type' => null,
                'retry_possible' => false,
            ]);
        } catch (\Exception $e) {
            $duration = $startTime->diffInSeconds(now());

            Log::error('=== MANUAL ODOO SYNC FAILED ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'duration_seconds' => $duration,
                'initiated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'error_type' => 'unexpected_error',
                'retry_possible' => true,
                'step_failed' => 'unknown',
                'message' => 'An unexpected error occurred during sync',
                'technical_details' => $e->getMessage(),
                'troubleshooting' => [
                    'Check Laravel logs at storage/logs/laravel.log for full error trace',
                    'Verify all database connections are properly configured',
                    'Contact IT support with the error details below',
                    'Try running sync again - this may be a temporary issue',
                ],
            ], 500);
        }
    }

    /**
     * Normalize user data from PostgreSQL to ensure proper types and defaults
     */
    private function normalizeUserData($user): array
    {
        return [
            'userPrincipalName' => $user->userprincipalname ?? null,
            'displayName' => $user->displayname ?? null,
            'surname' => $user->surname ?? null,
            'mail' => $user->mail ?? null,
            'givenName' => $user->givenname ?? null,
            'AzureId' => $user->azureid ?? null,
            'userType' => $user->usertype ?? null,
            'jobTitle' => $user->jobtitle ?? null,
            'department' => $user->department ?? null,
            'accountEnabled' => isset($user->accountenabled) ? ($user->accountenabled ? 1 : 0) : null,
            'usageLocation' => $user->usagelocation ?? null,
            'streetAddress' => $user->streetaddress ?? null,
            'state' => $user->state ?? null,
            'country' => $user->country ?? null,
            'officeLocation' => $user->officelocation ?? null,
            'city' => $user->city ?? null,
            'postalCode' => $user->postalcode ?? null,
            'telephoneNumber' => $user->telephonenumber ?? null,
            'mobilePhone' => $user->mobilephone ?? null,
            'alternateEmailAddress' => $user->alternateemailaddress ?? null,
            'ageGroup' => $user->agegroup ?? null,
            'consentProvidedForMinor' => $user->consentprovidedforminor ?? null,
            'legalAgeGroupClassification' => $user->legalagegroupclassification ?? null,
            'companyName' => $user->companyname ?? null,
            'creationType' => $user->creationtype ?? null,
            'directorySynced' => $user->directorysynced ?? null,
            'invitationState' => $user->invitationstate ?? null,
            'identityIssuer' => $user->identityissuer ?? null,
            'OdooUserId' => $user->odoouserid ?? null,
            'OdooEmployeeId' => $user->odooemployeeid ?? null,
            'OdooLoginId' => $user->odoologinid ?? null,
            'OdooCreatedOn' => $this->formatDateTime($user->odoocreatedon ?? null),
            'EmployeCode' => $user->employecode ?? null,
            'OdooLoginType' => ! empty($user->odoologintype) ? $user->odoologintype : 'Internal',
        ];
    }

    /**
     * Format datetime for SQL Server
     */
    private function formatDateTime($dateTime): ?string
    {
        if (empty($dateTime)) {
            return null;
        }

        try {
            return date('Y-m-d H:i:s', strtotime($dateTime));
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Insert user with improved error handling
     * NEW USERS START WITH STATUS = "Pending"
     */
    private function insertUserImproved(array $data): void
    {
        DB::connection('sqlsrv')->insert("
            INSERT INTO Azure_Users (
                Id, userPrincipalName, displayName, surname, mail, givenName,
                AzureId, userType, jobTitle, department, accountEnabled,
                usageLocation, streetAddress, state, country, officeLocation,
                city, postalCode, telephoneNumber, mobilePhone, alternateEmailAddress,
                ageGroup, consentProvidedForMinor, legalAgeGroupClassification,
                companyName, creationType, directorySynced, invitationState,
                identityIssuer, OdooUserId, OdooEmployeeId,
                OdooLoginId, OdooCreatedOn, EmployeCode, OdooLoginType,
                Status, CreatedDateTime
            )
            VALUES (
                NEWID(), ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?,
                'Pending', GETDATE()
            )
        ", array_values($data));
    }

    /**
     * Update user with improved error handling
     * UPDATED USERS ARE SET TO STATUS = "Active"
     */
    private function updateUserImproved(array $data): void
    {
        $upn = $data['userPrincipalName'];
        unset($data['userPrincipalName']); // Remove from update data

        DB::connection('sqlsrv')->update("
            UPDATE Azure_Users
            SET
                displayName = ?,
                surname = ?,
                mail = ?,
                givenName = ?,
                AzureId = ?,
                userType = ?,
                jobTitle = ?,
                department = ?,
                accountEnabled = ?,
                usageLocation = ?,
                streetAddress = ?,
                state = ?,
                country = ?,
                officeLocation = ?,
                city = ?,
                postalCode = ?,
                telephoneNumber = ?,
                mobilePhone = ?,
                alternateEmailAddress = ?,
                ageGroup = ?,
                consentProvidedForMinor = ?,
                legalAgeGroupClassification = ?,
                companyName = ?,
                creationType = ?,
                directorySynced = ?,
                invitationState = ?,
                identityIssuer = ?,
                OdooUserId = ?,
                OdooEmployeeId = ?,
                OdooLoginId = ?,
                OdooCreatedOn = ?,
                EmployeCode = ?,
                OdooLoginType = ?,
                Status = 'Active'
            WHERE userPrincipalName = ?
        ", array_merge(array_values($data), [$upn]));
    }

    // Output helper methods
    private function outputHeader($startTime): void
    {
        echo '<html><head><title>Odoo User Sync</title>';
        echo '<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h2 { color: #333; }
            .success { color: green; }
            .error { color: red; }
            .info { color: blue; }
            table { border-collapse: collapse; margin: 20px 0; width: 100%; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #4CAF50; color: white; }
            .stat-box { background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 5px; }
        </style></head><body>';

        echo '<h2>🔄 Odoo to Azure User Sync</h2>';
        echo "<div class='stat-box'>";
        echo '<strong>Start Time:</strong> ' . $startTime->format('Y-m-d H:i:s') . '<br>';
        echo '<strong>Source:</strong> PostgreSQL (Odoo) - vw_getuserdetails<br>';
        echo '<strong>Destination:</strong> SQL Server - Azure_Users';
        echo '</div><hr>';
    }

    private function outputRow($num, $upn, $type, $action, $success, $error = null): void
    {
        $actionClass = $action === 'INSERT' ? 'success' : ($action === 'UPDATE' ? 'info' : 'error');
        $statusClass = $success ? 'success' : 'error';
        $statusIcon = $success ? '✅ Success' : '❌ Failed';
        $note = $success ? ($error ?? '-') : substr($error, 0, 50);

        echo '<tr>';
        echo "<td>{$num}</td>";
        echo "<td>{$upn}</td>";
        echo "<td>{$type}</td>";
        echo "<td><span class='{$actionClass}'>{$action}</span></td>";
        echo "<td><span class='{$statusClass}'>{$statusIcon}</span></td>";
        echo "<td>{$note}</td>";
        echo '</tr>';
    }

    private function outputSummary($startTime, $stats): void
    {
        $endTime = now();
        $duration = $endTime->diffInSeconds($startTime);

        echo '<hr><h3>📊 Sync Summary</h3>';
        echo "<div class='stat-box'>";
        echo '<strong>End Time:</strong> ' . $endTime->format('Y-m-d H:i:s') . '<br>';
        echo "<strong>Duration:</strong> {$duration} seconds<br><br>";
        echo "<strong>Total Processed:</strong> {$stats['processed']}<br>";
        echo "<strong class='success'>✅ Inserted (Status: Pending):</strong> {$stats['inserted']}<br>";
        echo "<strong class='info'>🔄 Updated (Status: Active):</strong> {$stats['updated']}<br>";
        echo "<strong class='error'>❌ Failed:</strong> {$stats['failed']}<br>";
        echo '</div>';

        if (count($stats['errors']) > 0) {
            echo "<h3 class='error'>❌ Errors:</h3><table>";
            echo '<tr><th>User</th><th>Error</th></tr>';
            foreach ($stats['errors'] as $error) {
                echo "<tr><td>{$error['user']}</td><td>{$error['error']}</td></tr>";
            }
            echo '</table>';
        }
    }

    private function outputError($e): void
    {
        echo "<div class='error'>";
        echo '<h3>❌ Critical Error</h3>';
        echo '<p><strong>Error:</strong> ' . $e->getMessage() . '</p>';
        echo '<p><strong>File:</strong> ' . $e->getFile() . '</p>';
        echo '<p><strong>Line:</strong> ' . $e->getLine() . '</p>';
        echo '</div>';
    }

    // Keep your existing test methods...
    public function testColumns()
    { /* Your existing code */
    }

    public function syncUsersManual()
    { /* Your existing code */
    }

    public function testConnection()
    { /* Your existing code */
    }

    public function checkViewDefinition()
    { /* Your existing code */
    }
}
