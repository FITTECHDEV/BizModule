<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class OdooSyncController extends Controller
{
    /**
     * Sync users from Odoo (PostgreSQL) to Azure_Users (SQL Server)
     * ENHANCED: Now provisions users to Azure AD automatically
     */
    public function syncUsers()
    {
        $startTime = now();
        $stats = [
            'processed' => 0,
            'inserted' => 0,
            'updated' => 0,
            'failed' => 0,
            'azure_provisioned' => 0,
            'azure_failed' => 0,
            'azure_skipped' => 0,
            'errors' => [],
        ];

        $this->outputHeader($startTime);

        try {
            // STEP 1: Fetch users from Odoo
            echo "<p class='info'>📥 <strong>Step 1:</strong> Fetching users from Odoo...</p>";
            flush();

            $odooUsers = DB::connection('pgsql')->select("
            SELECT 
                -- ✅ CHANGED: Generate UPN from name as firstname.initial@domain
                LOWER(split_part(he.name::text, ' ', 1)) || '.' || 
                LOWER(LEFT(split_part(he.name::text, ' ', 2), 1)) || 
                '@freight-in-time.com' AS userprincipalname,
                
                -- Store original Odoo login email separately
                ru.login AS odoo_original_email,
                
                he.name AS displayname,
                split_part(he.name::text, ' ', 2) AS surname,
                he.work_email AS mail,
                split_part(he.name::text, ' ', 1) AS givenname,
                NULL AS azureid,
                'Member' AS usertype,
                he.job_title AS jobtitle,
                hd.complete_name AS department,
                1 AS accountenabled,
                ru.company_id,
                CASE
                    WHEN rc.name ~~ '%Kenya%' THEN 'KE'
                    WHEN rc.name ~~ '%Uganda%' THEN 'UG'
                    WHEN rc.name ~~ '%Rwanda%' THEN 'RW'
                    WHEN rc.name ~~ '%South Sudan%' THEN 'SS'
                    WHEN rc.name ~~ '%Djibouti%' THEN 'DJ'
                    WHEN rc.name ~~ '%Tanzania%' THEN 'TZ'
                    WHEN rc.name ~~ '%Ethiopia%' THEN 'ET'
                    ELSE 'NA'
                END AS usagelocation,
                he.private_street || CASE WHEN length(COALESCE(he.private_street2, '')) > 0 THEN ' ' || he.private_street2 ELSE '' END AS streetaddress,
                rcs.name AS state,
                rec.name::json ->> 'en_US' AS country,
                NULL AS officelocation,
                he.private_city AS city,
                he.private_zip AS postalcode,
                he.work_phone AS telephonenumber,
                he.mobile_phone AS mobilephone,
                he.private_email AS alternateemailaddress,
                NULL AS agegroup,
                NULL AS consentprovidedforminor,
                NULL AS legalagegroupclassification,
                rc.name AS companyname,
                NULL AS creationtype,
                NULL AS directorysynced,
                NULL AS invitationstate,
                NULL AS identityissuer,
                he.user_id AS odoouserid,
                he.id AS odooemployeeid,
                ru.login AS odoologinid,
                he.create_date AS odoocreatedon,
                he.registration_number AS employecode,
                CASE WHEN ru.share = false THEN 'Internal' ELSE 'Portal' END AS odoologintype
            FROM hr_employee he
            JOIN res_users ru ON ru.id = he.user_id AND ru.active = true
            LEFT JOIN hr_department hd ON hd.id = he.department_id
            JOIN res_company rc ON rc.id = ru.company_id
            LEFT JOIN res_country_state rcs ON rcs.id = he.private_state_id
            LEFT JOIN res_country rec ON rec.id = he.private_country_id
            WHERE he.active = true
        ");

            $totalUsers = count($odooUsers);

            echo "<p class='success'>✅ Found <strong>{$totalUsers}</strong> users in Odoo</p>";
            flush();

            if ($totalUsers === 0) {
                echo "<p class='error'>⚠️ No users found. Sync aborted.</p></body></html>";
                return;
            }

            // STEP 2: Begin transaction and process users
            echo "<p class='info'>⚙️ <strong>Step 2:</strong> Processing users...</p>";
            echo '<table><tr><th>#</th><th>User Principal Name</th><th>Odoo Email</th><th>User Type</th><th>Action</th><th>Azure AD</th><th>Status</th><th>Note</th></tr>';
            flush();

            // Get Azure service instance
            $azureService = app(\App\Services\AzureGraphService::class);

            DB::connection('sqlsrv')->transaction(function () use ($odooUsers, &$stats, $azureService) {
                foreach ($odooUsers as $index => $user) {
                    $stats['processed']++;
                    $rowNumber = $index + 1;

                    try {
                        // Normalize user data
                        $userData = $this->normalizeUserData($user);

                        // ✅ ENHANCED: Three-tier matching to prevent duplicates
                        // Tier 1: Try to find by OdooUserId (primary identifier)
                        $existingUser = DB::connection('sqlsrv')
                            ->table('Azure_Users')
                            ->where('OdooUserId', $userData['OdooUserId'])
                            ->first();

                        // Tier 2: If not found, try to find by generated UPN (firstname.initial@domain)
                        if (!$existingUser) {
                            $existingUser = DB::connection('sqlsrv')
                                ->table('Azure_Users')
                                ->where('userPrincipalName', $userData['userPrincipalName'])
                                ->whereNull('OdooUserId')  // Only match if not yet linked to Odoo
                                ->first();

                            // If found by UPN but missing OdooUserId, link them
                            if ($existingUser) {
                                DB::connection('sqlsrv')
                                    ->table('Azure_Users')
                                    ->where('Id', $existingUser->Id)
                                    ->update([
                                        'OdooUserId' => $userData['OdooUserId'],
                                        'OdooEmployeeId' => $userData['OdooEmployeeId'],
                                        'OdooLoginId' => $userData['OdooLoginId'],
                                        'OdooCreatedOn' => $userData['OdooCreatedOn'],
                                        'OdooLoginType' => $userData['OdooLoginType'],
                                    ]);

                                Log::info('Linked manually-created user to Odoo by UPN', [
                                    'user' => $userData['userPrincipalName'],
                                    'odoo_user_id' => $userData['OdooUserId'],
                                ]);
                            }
                        }

                        // Tier 3: If still not found, try to find by Odoo original email (fallback)
                        if (!$existingUser && !empty($user->odoo_original_email)) {
                            $existingUser = DB::connection('sqlsrv')
                                ->table('Azure_Users')
                                ->where('userPrincipalName', $user->odoo_original_email)
                                ->whereNull('OdooUserId')  // Only match if not yet linked to Odoo
                                ->first();

                            // If found by old email format, update UPN and link to Odoo
                            if ($existingUser) {
                                DB::connection('sqlsrv')
                                    ->table('Azure_Users')
                                    ->where('Id', $existingUser->Id)
                                    ->update([
                                        'userPrincipalName' => $userData['userPrincipalName'], // Update to new format
                                        'OdooUserId' => $userData['OdooUserId'],
                                        'OdooEmployeeId' => $userData['OdooEmployeeId'],
                                        'OdooLoginId' => $userData['OdooLoginId'],
                                        'OdooCreatedOn' => $userData['OdooCreatedOn'],
                                        'OdooLoginType' => $userData['OdooLoginType'],
                                    ]);

                                Log::info('Linked user by old email format and updated UPN', [
                                    'old_upn' => $user->odoo_original_email,
                                    'new_upn' => $userData['userPrincipalName'],
                                    'odoo_user_id' => $userData['OdooUserId'],
                                ]);
                            }
                        }

                        if (!$existingUser) {
                            // ============================================================
                            // NEW USER: Create in Azure AD first, then DB
                            // ============================================================
                            $azureProvisioningResult = $this->provisionToAzureAD($userData, $azureService);

                            if ($azureProvisioningResult['success']) {
                                // ✅ Only insert if Azure provisioning succeeded
                                $userData['AzureId'] = $azureProvisioningResult['azure_id'];
                                $this->insertUserImproved($userData);
                                $stats['inserted']++;
                                $stats['azure_provisioned']++;

                                $this->outputRow(
                                    $rowNumber,
                                    $userData['userPrincipalName'],
                                    $user->odoo_original_email ?? 'N/A',
                                    $userData['OdooLoginType'],
                                    'INSERT',
                                    '✅ Provisioned',
                                    true,
                                    'Created in Azure AD + DB (Pending)'
                                );
                            } else {
                                // ✅ FIXED: Do NOT insert - skip this user entirely
                                $stats['azure_failed']++;
                                $stats['failed']++; // Count as overall failure
                                $stats['errors'][] = [
                                    'user' => $userData['userPrincipalName'],
                                    'operation' => 'Azure Provisioning (Required)',
                                    'error' => 'User NOT created - Azure provisioning failed: ' . $azureProvisioningResult['error'],
                                ];

                                $this->outputRow(
                                    $rowNumber,
                                    $userData['userPrincipalName'],
                                    $user->odoo_original_email ?? 'N/A',
                                    $userData['OdooLoginType'],
                                    'SKIPPED',
                                    '❌ Failed',
                                    false,
                                    'Azure provisioning failed - user NOT created: ' . substr($azureProvisioningResult['error'], 0, 50)
                                );

                                // Skip to next user
                                flush();
                                continue;
                            }
                        } else {
                            // ============================================================
                            // EXISTING USER: Update DB + Handle Azure AD
                            // ============================================================

                            $previousStatus = $existingUser->Status ?? 'Unknown';
                            $hadAzureId = !empty($existingUser->AzureId);

                            // Update user data
                            $this->updateUserImproved($userData);
                            $stats['updated']++;

                            // Check if user needs Azure provisioning
                            if (!$hadAzureId) {
                                Log::info('Existing user missing AzureId - provisioning now', [
                                    'user' => $userData['userPrincipalName'],
                                ]);

                                $azureProvisioningResult = $this->provisionToAzureAD($userData, $azureService);

                                if ($azureProvisioningResult['success']) {
                                    DB::connection('sqlsrv')
                                        ->table('Azure_Users')
                                        ->where('OdooUserId', $userData['OdooUserId'])
                                        ->update([
                                            'AzureId' => $azureProvisioningResult['azure_id'],
                                        ]);

                                    $stats['azure_provisioned']++;

                                    $this->outputRow(
                                        $rowNumber,
                                        $userData['userPrincipalName'],
                                        $user->odoo_original_email ?? 'N/A',
                                        $userData['OdooLoginType'],
                                        'UPDATE',
                                        '✅ Provisioned',
                                        true,
                                        'Updated + Azure AD provisioned (Active)'
                                    );
                                } else {
                                    $stats['azure_failed']++;
                                    $stats['errors'][] = [
                                        'user' => $userData['userPrincipalName'],
                                        'error' => 'Azure provisioning failed: ' . $azureProvisioningResult['error'],
                                    ];

                                    $this->outputRow(
                                        $rowNumber,
                                        $userData['userPrincipalName'],
                                        $user->odoo_original_email ?? 'N/A',
                                        $userData['OdooLoginType'],
                                        'UPDATE',
                                        '❌ Failed',
                                        true,
                                        'Updated, Azure failed: ' . substr($azureProvisioningResult['error'], 0, 50)
                                    );
                                }
                            } else {
                                $stats['azure_skipped']++;

                                $statusNote = $previousStatus === 'Pending'
                                    ? 'Updated from Pending to Active'
                                    : 'Data refreshed, Status: Active';

                                $this->outputRow(
                                    $rowNumber,
                                    $userData['userPrincipalName'],
                                    $user->odoo_original_email ?? 'N/A',
                                    $userData['OdooLoginType'],
                                    'UPDATE',
                                    '⊘ Exists',
                                    true,
                                    $statusNote
                                );
                            }
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
                            $user->odoo_original_email ?? 'N/A',
                            'Unknown',
                            'FAILED',
                            '❌ Error',
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
     * USES VIEW: Only syncs users created/modified TODAY
     */
    public function syncUsersAjax()
    {
        try {
            Log::info('=== MANUAL ODOO SYNC STARTED ===', [
                'initiated_by' => Auth::id(),
                'time' => now(),
            ]);

            $stats = [
                'processed' => 0,
                'inserted' => 0,
                'updated' => 0,
                'failed' => 0,
                'azure_provisioned' => 0,
                'azure_updated' => 0,
                'azure_failed' => 0,
                'azure_skipped' => 0,
                'errors' => [],
            ];

            // Fetch users from Odoo view (TODAY'S changes only)
            $odooUsers = DB::connection('pgsql')
                ->table('vw_getuserdetails')
                ->get()
                ->toArray();

            // Convert stdClass objects to match expected format
            $odooUsers = array_map(function ($user) {
                return (object) (array) $user;
            }, $odooUsers);

            Log::info('ODOO USERS FETCHED FROM VIEW', [
                'count' => count($odooUsers),
                'date_filter' => 'TODAY only (from vw_getuserdetails)'
            ]);

            if (empty($odooUsers)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No users created/modified today in Odoo',
                ], 404);
            }

            // Get Azure service instance
            $azureService = app(\App\Services\AzureGraphService::class);

            // Process users in transaction
            DB::connection('sqlsrv')->transaction(function () use ($odooUsers, &$stats, $azureService) {
                foreach ($odooUsers as $user) {
                    $stats['processed']++;

                    try {
                        $userData = $this->normalizeUserData($user);

                        // ✅ FIXED: Sequential matching to prevent duplicates
                        // Step 1: Try to find by OdooUserId (primary identifier)
                        $existingUser = DB::connection('sqlsrv')
                            ->table('Azure_Users')
                            ->where('OdooUserId', $userData['OdooUserId'])
                            ->first();

                        // Step 2: If not found, try to find by UPN (for manually-created users)
                        if (!$existingUser) {
                            $existingUser = DB::connection('sqlsrv')
                                ->table('Azure_Users')
                                ->where('userPrincipalName', $userData['userPrincipalName'])
                                ->whereNull('OdooUserId')  // Only match if not yet linked to Odoo
                                ->first();

                            // If found by email but missing OdooUserId, link them
                            if ($existingUser) {
                                DB::connection('sqlsrv')
                                    ->table('Azure_Users')
                                    ->where('Id', $existingUser->Id)
                                    ->update([
                                        'OdooUserId' => $userData['OdooUserId'],
                                        'OdooEmployeeId' => $userData['OdooEmployeeId'],
                                        'OdooLoginId' => $userData['OdooLoginId'],
                                        'OdooCreatedOn' => $userData['OdooCreatedOn'],
                                        'OdooLoginType' => $userData['OdooLoginType'],
                                    ]);

                                Log::info('Linked manually-created user to Odoo (AJAX sync)', [
                                    'user' => $userData['userPrincipalName'],
                                    'odoo_user_id' => $userData['OdooUserId'],
                                ]);
                            }
                        }

                        if (!$existingUser) {
                            // ============================================================
                            // NEW USER: Create in Azure AD first, then DB
                            // ============================================================
                            $azureProvisioningResult = $this->provisionToAzureAD($userData, $azureService);

                            if ($azureProvisioningResult['success']) {
                                // ✅ Only insert if Azure provisioning succeeded
                                $userData['AzureId'] = $azureProvisioningResult['azure_id'];
                                $this->insertUserImproved($userData);
                                $stats['inserted']++;
                                $stats['azure_provisioned']++;

                                Log::info('✅ New user created with AzureId', [
                                    'user' => $userData['userPrincipalName'],
                                    'azure_id' => $azureProvisioningResult['azure_id'],
                                ]);
                            } else {
                                // ✅ FIXED: Do NOT insert - skip this user
                                $stats['azure_failed']++;
                                $stats['failed']++;
                                $stats['errors'][] = [
                                    'user' => $userData['userPrincipalName'],
                                    'operation' => 'Azure Provisioning (Required)',
                                    'error' => 'User NOT created - Azure provisioning failed: ' . $azureProvisioningResult['error'],
                                ];

                                Log::warning('⚠️ User creation skipped - Azure provisioning failed', [
                                    'user' => $userData['userPrincipalName'],
                                    'error' => $azureProvisioningResult['error'],
                                ]);

                                // Skip to next user
                                continue;
                            }
                        } else {
                            // ============================================================
                            // EXISTING USER: Update DB + Handle Azure AD
                            // ============================================================
                            $hadAzureId = !empty($existingUser->AzureId);

                            // Update database
                            $this->updateUserImproved($userData);
                            $stats['updated']++;

                            if (!$hadAzureId) {
                                // User missing AzureId - provision now
                                $azureProvisioningResult = $this->provisionToAzureAD($userData, $azureService);

                                if ($azureProvisioningResult['success']) {
                                    DB::connection('sqlsrv')
                                        ->table('Azure_Users')
                                        ->where('OdooUserId', $userData['OdooUserId'])
                                        ->update([
                                            'AzureId' => $azureProvisioningResult['azure_id'],
                                        ]);

                                    $stats['azure_provisioned']++;
                                } else {
                                    $stats['azure_failed']++;
                                    $stats['errors'][] = [
                                        'user' => $userData['userPrincipalName'],
                                        'operation' => 'Azure Provisioning (Missing AzureId)',
                                        'error' => $azureProvisioningResult['error'],
                                    ];
                                }
                            } else {
                                // User has AzureId - UPDATE Azure AD profile
                                $azureUpdateResult = $this->updateAzureADUser($existingUser->AzureId, $userData, $azureService);

                                if ($azureUpdateResult['success']) {
                                    $stats['azure_updated']++;
                                } else {
                                    $stats['azure_failed']++;
                                    $stats['errors'][] = [
                                        'user' => $userData['userPrincipalName'],
                                        'operation' => 'Azure AD Update',
                                        'error' => $azureUpdateResult['error'],
                                    ];
                                }
                            }
                        }
                    } catch (Exception $e) {
                        $stats['failed']++;
                        $stats['errors'][] = [
                            'user' => $userData['userPrincipalName'] ?? 'Unknown',
                            'operation' => 'Database Operation',
                            'error' => $e->getMessage(),
                        ];

                        Log::error('Failed to sync user', [
                            'user' => $userData['userPrincipalName'] ?? 'Unknown',
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

            Log::info('=== MANUAL ODOO SYNC COMPLETED ===', [
                'stats' => $stats,
                'initiated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => $this->buildAjaxSummaryMessage($stats),
                'stats' => $stats,
                'errors' => $stats['errors'],
            ]);
        } catch (Exception $e) {
            Log::error('=== MANUAL ODOO SYNC FAILED ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Provision user to Azure AD
     * 
     * @param array $userData Normalized user data
     * @param \App\Services\AzureGraphService $azureService
     * @return array ['success' => bool, 'azure_id' => string|null, 'error' => string|null]
     */
    private function provisionToAzureAD(array $userData, $azureService): array
    {
        try {
            Log::info('→ Provisioning user to Azure AD', [
                'user' => $userData['userPrincipalName'],
            ]);

            // Split name into first and last name
            $nameParts = explode(' ', trim($userData['displayName'] ?? ''), 2);
            $givenName = $nameParts[0] ?? 'Unknown';
            $surname = $nameParts[1] ?? $nameParts[0] ?? 'User';

            // Build Azure user payload
            $azureUserData = [
                'accountEnabled' => true,
                'displayName' => $userData['displayName'] ?? 'Unknown User',
                'mailNickname' => explode('@', $userData['userPrincipalName'])[0],
                'userPrincipalName' => $userData['userPrincipalName'],
                'givenName' => $givenName,
                'surname' => $surname,
                'usageLocation' => $userData['usageLocation'] ?? 'KE',
                'passwordProfile' => [
                    'forceChangePasswordNextSignIn' => true,
                    'password' => $this->generateSecurePassword(),
                ],
            ];

            // Add optional fields if present
            if (!empty($userData['mail'])) {
                $azureUserData['mail'] = $userData['mail'];
            }
            if (!empty($userData['mobilePhone'])) {
                $azureUserData['mobilePhone'] = $userData['mobilePhone'];
            }
            if (!empty($userData['telephoneNumber'])) {
                $azureUserData['businessPhones'] = [$userData['telephoneNumber']];
            }
            if (!empty($userData['jobTitle'])) {
                $azureUserData['jobTitle'] = $userData['jobTitle'];
            }
            if (!empty($userData['department'])) {
                $azureUserData['department'] = $userData['department'];
            }
            if (!empty($userData['officeLocation'])) {
                $azureUserData['officeLocation'] = $userData['officeLocation'];
            }

            // Call Azure Graph API
            $result = $azureService->createUser($azureUserData);

            Log::info('✓ User provisioned to Azure AD successfully', [
                'user' => $userData['userPrincipalName'],
                'azure_id' => $result['id'],
            ]);

            return [
                'success' => true,
                'azure_id' => $result['id'],
                'error' => null,
            ];
        } catch (Exception $e) {
            Log::error('✗ Azure AD provisioning failed', [
                'user' => $userData['userPrincipalName'],
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'azure_id' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update existing user in Azure AD with latest Odoo data
     * 
     * @param string $azureId Azure AD user ID
     * @param array $userData Normalized user data from Odoo
     * @param \App\Services\AzureGraphService $azureService
     * @return array ['success' => bool, 'error' => string|null]
     */
    private function updateAzureADUser(string $azureId, array $userData, $azureService): array
    {
        try {
            Log::info('→ Updating user in Azure AD', [
                'azure_id' => $azureId,
                'user' => $userData['userPrincipalName'],
            ]);

            // Map Odoo fields to Azure AD update format
            $azureUpdateData = [
                'displayName' => $userData['displayName'],
                'givenName' => $userData['givenName'],
                'surname' => $userData['surname'],
                'mail' => $userData['mail'],
                'mobilePhone' => $userData['mobilePhone'],
                'telephoneNumber' => $userData['telephoneNumber'],
                'jobTitle' => $userData['jobTitle'],
                'department' => $userData['department'],
                'officeLocation' => $userData['officeLocation'],
                'streetAddress' => $userData['streetAddress'],
                'city' => $userData['city'],
                'state' => $userData['state'],
                'postalCode' => $userData['postalCode'],
                'country' => $userData['country'],
                'usageLocation' => $userData['usageLocation'],
                'companyName' => $userData['companyName'],
                'employeeId' => $userData['EmployeCode'],
            ];

            // Remove null values
            $azureUpdateData = array_filter($azureUpdateData, function ($value) {
                return $value !== null;
            });

            // Call Azure Graph API
            $azureService->updateUser($azureId, $azureUpdateData);

            Log::info('✓ User updated in Azure AD successfully', [
                'azure_id' => $azureId,
                'user' => $userData['userPrincipalName'],
                'fields_updated' => array_keys($azureUpdateData),
            ]);

            return [
                'success' => true,
                'error' => null,
            ];
        } catch (Exception $e) {
            Log::error('✗ Azure AD update failed', [
                'azure_id' => $azureId,
                'user' => $userData['userPrincipalName'],
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate a secure random password for Azure AD
     * Meets Azure AD password complexity requirements
     */
    private function generateSecurePassword(): string
    {
        $length = 16;

        // Character sets
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '!@#$%^&*()-_=+[]{}|;:,.<>?';

        // Ensure at least one character from each set
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        // Fill remaining length with random characters from all sets
        $allChars = $uppercase . $lowercase . $numbers . $special;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Shuffle to avoid predictable pattern
        return str_shuffle($password);
    }

    /**
     * Build summary message for AJAX response
     */
    private function buildAjaxSummaryMessage(array $stats): string
    {
        $parts = [
            "Processed: {$stats['processed']}",
            "Inserted: {$stats['inserted']}",
            "Updated: {$stats['updated']}",
        ];

        if ($stats['azure_provisioned'] > 0) {
            $parts[] = "Azure Provisioned: {$stats['azure_provisioned']}";
        }

        if ($stats['azure_updated'] > 0) {
            $parts[] = "Azure Updated: {$stats['azure_updated']}";
        }

        if ($stats['azure_failed'] > 0) {
            $parts[] = "Azure Failed: {$stats['azure_failed']}";
        }

        if ($stats['failed'] > 0) {
            $parts[] = "DB Failed: {$stats['failed']}";
        }

        return 'Sync completed! ' . implode(' | ', $parts);
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
            'OdooLoginType' => !empty($user->odoologintype) ? $user->odoologintype : 'Internal',
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
                Status, createdDateTime
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
     * SMART UPDATE: Only updates non-NULL/non-empty values from Odoo
     */
    private function updateUserImproved(array $data): void
    {
        $odooUserId = $data['OdooUserId'];
        unset($data['OdooUserId']);

        // ✅ Filter out NULL and empty string values
        $filteredData = array_filter($data, function ($value) {
            // Keep: non-null, non-empty string, or numeric 0
            return $value !== null && $value !== '';
        });

        // ✅ If no fields to update, skip
        if (empty($filteredData)) {
            Log::warning('No non-null fields to update', ['odoo_user_id' => $odooUserId]);
            return;
        }

        // ✅ Build dynamic SET clause
        $setClauses = [];
        $values = [];

        foreach ($filteredData as $field => $value) {
            $setClauses[] = "{$field} = ?";
            $values[] = $value;
        }

        // Always set Status to Active
        $setClauses[] = "Status = 'Active'";

        // Add WHERE clause value
        $values[] = $odooUserId;

        $sql = "UPDATE Azure_Users SET " . implode(', ', $setClauses) . " WHERE OdooUserId = ?";

        DB::connection('sqlsrv')->update($sql, $values);

        Log::info('User updated with smart logic', [
            'odoo_user_id' => $odooUserId,
            'fields_updated' => array_keys($filteredData),
            'skipped_null_fields' => array_keys(array_diff_key($data, $filteredData)),
        ]);
    }

    // ========================================================================
    // OUTPUT HELPER METHODS
    // ========================================================================

    private function outputHeader($startTime): void
    {
        echo '<html><head><title>Odoo User Sync</title>';
        echo '<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h2 { color: #333; }
            .success { color: green; }
            .error { color: red; }
            .info { color: blue; }
            .warning { color: orange; }
            table { border-collapse: collapse; margin: 20px 0; width: 100%; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
            th { background-color: #4CAF50; color: white; }
            .stat-box { background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 5px; }
            .azure-success { color: green; font-weight: bold; }
            .azure-failed { color: red; font-weight: bold; }
            .azure-skipped { color: gray; }
        </style></head><body>';

        echo '<h2>🔄 Odoo to Azure User Sync (Enhanced with Azure AD Provisioning)</h2>';
        echo "<div class='stat-box'>";
        echo '<strong>Start Time:</strong> ' . $startTime->format('Y-m-d H:i:s') . '<br>';
        echo '<strong>Source:</strong> PostgreSQL (Odoo)<br>';
        echo '<strong>Destination:</strong> SQL Server (Azure_Users) + Azure AD<br>';
        echo '<strong>Mode:</strong> Automatic Azure AD provisioning enabled';
        echo '</div><hr>';
    }


    private function outputRow($num, $upn, $odooEmail, $type, $action, $azureStatus, $success, $note = null): void
    {
        $actionClass = $action === 'INSERT' ? 'success' : ($action === 'UPDATE' ? 'info' : 'error');
        $statusClass = $success ? 'success' : 'error';
        $statusIcon = $success ? '✅ Success' : '❌ Failed';

        echo '<tr>';
        echo "<td>{$num}</td>";
        echo "<td>{$upn}</td>";
        echo "<td>{$odooEmail}</td>";
        echo "<td>{$type}</td>";
        echo "<td><span class='{$actionClass}'>{$action}</span></td>";
        echo "<td>{$azureStatus}</td>";
        echo "<td><span class='{$statusClass}'>{$statusIcon}</span></td>";
        echo "<td>" . substr($note ?? '-', 0, 80) . "</td>";
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
        echo "<strong class='success'>✅ Inserted (DB, Status: Pending):</strong> {$stats['inserted']}<br>";
        echo "<strong class='info'>🔄 Updated (DB, Status: Active):</strong> {$stats['updated']}<br>";
        echo "<strong class='error'>❌ Failed:</strong> {$stats['failed']}<br><br>";

        echo '<strong>Azure AD Provisioning:</strong><br>';
        echo "<strong class='azure-success'>✅ Successfully Provisioned:</strong> {$stats['azure_provisioned']}<br>";
        echo "<strong class='azure-failed'>❌ Provisioning Failed:</strong> {$stats['azure_failed']}<br>";
        echo "<strong class='azure-skipped'>⊘ Skipped (Already Exists):</strong> {$stats['azure_skipped']}<br>";
        echo '</div>';

        if (count($stats['errors']) > 0) {
            echo "<h3 class='error'>❌ Errors (" . count($stats['errors']) . "):</h3><table>";
            echo '<tr><th>User</th><th>Error</th></tr>';
            foreach ($stats['errors'] as $error) {
                echo "<tr><td>{$error['user']}</td><td>" . substr($error['error'], 0, 150) . "</td></tr>";
            }
            echo '</table>';
        }

        // Show next steps
        if ($stats['azure_failed'] > 0) {
            echo "<div class='stat-box' style='background: #fff3cd; border: 2px solid #ffc107;'>";
            echo "<strong>⚠️ Next Steps:</strong><br>";
            echo "• {$stats['azure_failed']} user(s) failed Azure AD provisioning<br>";
            echo "• These users are in the database with Status = 'Pending'<br>";
            echo "• You can retry Azure provisioning from the user management page<br>";
            echo "• Or use the 'Activate Pending Users' button to batch retry";
            echo "</div>";
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
}
