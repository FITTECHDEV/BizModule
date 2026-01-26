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

            // NEW (queries tables directly, no date filter)
            $odooUsers = DB::connection('pgsql')->select("
    SELECT 
        ru.login AS userprincipalname,
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
                'errors' => [],
            ];

            // Fetch users from Odoo
            // NEW (queries tables directly, no date filter)
            // Sync only users created or modified in the last 7 days
            $odooUsers = DB::connection('pgsql')->select("
    SELECT 
        ru.login AS userprincipalname,
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
        AND (he.create_date::date >= CURRENT_DATE - INTERVAL '7 days' 
             OR he.write_date::date >= CURRENT_DATE - INTERVAL '7 days')
");
            Log::info('ODOO USERS FETCHED', ['count' => count($odooUsers)]);


            if (empty($odooUsers)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No users found in Odoo',
                ], 404);
            }

            // Process users in transaction
            DB::connection('sqlsrv')->transaction(function () use ($odooUsers, &$stats) {
                foreach ($odooUsers as $user) {
                    $stats['processed']++;

                    try {
                        $userData = $this->normalizeUserData($user);

                        $existingUser = DB::connection('sqlsrv')
                            ->table('Azure_Users')
                            ->where('userPrincipalName', $userData['userPrincipalName'])
                            ->first();

                        if (! $existingUser) {
                            // NEW USER: Insert with Status = "Pending"
                            $this->insertUserImproved($userData);
                            $stats['inserted']++;
                        } else {
                            // EXISTING USER: Update and set to "Active"
                            $this->updateUserImproved($userData);
                            $stats['updated']++;
                        }
                    } catch (Exception $e) {
                        $stats['failed']++;
                        $stats['errors'][] = [
                            'user' => $userData['userPrincipalName'] ?? 'Unknown',
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
                'message' => 'Sync completed successfully',
                'stats' => $stats,
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
     * UPDATED USERS ARE SET TO STATUS = "Active"
     */
    private function updateUserImproved(array $data): void
    {
        $upn = $data['userPrincipalName'];
        unset($data['userPrincipalName']);

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
