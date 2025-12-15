<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Exception;

class OdooSyncController extends Controller
{
    /**
     * Sync users from Odoo (PostgreSQL) to Azure_Users (SQL Server)
     * 
     * Access via: /sync-odoo-users
     */
    public function syncUsers()
    {
        // Track start time
        $startTime = now();

        // Initialize statistics
        $stats = [
            'processed' => 0,
            'inserted' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => []
        ];

        // Start output
        echo "<html><head><title>Odoo User Sync</title>";
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h2 { color: #333; }
            .success { color: green; }
            .error { color: red; }
            .info { color: blue; }
            table { border-collapse: collapse; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #4CAF50; color: white; }
            .stat-box { background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 5px; }
        </style></head><body>";

        echo "<h2>🔄 Odoo to Azure User Sync</h2>";
        echo "<div class='stat-box'>";
        echo "<strong>Start Time:</strong> " . $startTime->format('Y-m-d H:i:s') . "<br>";
        echo "<strong>Source:</strong> PostgreSQL (Odoo) - vw_getuserdetails<br>";
        echo "<strong>Destination:</strong> SQL Server - Azure_Users";
        echo "</div>";
        echo "<hr>";

        try {
            // STEP 1: Fetch all users from Odoo (PostgreSQL)
            echo "<p class='info'>📥 <strong>Step 1:</strong> Fetching users from Odoo...</p>";
            flush();

            $odooUsers = DB::connection('pgsql')->select('SELECT * FROM vw_getuserdetails');

            $totalUsers = count($odooUsers);
            echo "<p class='success'>✅ Found <strong>{$totalUsers}</strong> users in Odoo</p>";
            flush();

            if ($totalUsers === 0) {
                echo "<p class='error'>⚠️ No users found in vw_getuserdetails. Sync aborted.</p>";
                echo "</body></html>";
                return;
            }

            // STEP 2: Process each user
            echo "<p class='info'>⚙️ <strong>Step 2:</strong> Processing users...</p>";
            echo "<table>";
            echo "<tr><th>#</th><th>User Principal Name</th><th>User Type</th><th>Action</th><th>Status</th></tr>";
            flush();

            foreach ($odooUsers as $index => $user) {
                $stats['processed']++;
                $rowNumber = $index + 1;

                try {
                    // Check if user already exists in SQL Server
                    $existingUser = DB::connection('sqlsrv')
                        ->select(
                            "SELECT Id FROM Azure_Users WHERE userPrincipalName = ?",
                            [$user->userprincipalname]
                        );

                    $userType = $user->odoologintype ?? 'Unknown';

                    if (empty($existingUser)) {
                        // USER DOES NOT EXIST - INSERT
                        $this->insertUser($user);
                        $stats['inserted']++;

                        echo "<tr>";
                        echo "<td>{$rowNumber}</td>";
                        echo "<td>{$user->userprincipalname}</td>";
                        echo "<td>{$userType}</td>";
                        echo "<td><span class='success'>INSERT</span></td>";
                        echo "<td><span class='success'>✅ Success</span></td>";
                        echo "</tr>";
                    } else {
                        // USER EXISTS - UPDATE
                        $this->updateUser($user);
                        $stats['updated']++;

                        echo "<tr>";
                        echo "<td>{$rowNumber}</td>";
                        echo "<td>{$user->userprincipalname}</td>";
                        echo "<td>{$userType}</td>";
                        echo "<td><span class='info'>UPDATE</span></td>";
                        echo "<td><span class='success'>✅ Success</span></td>";
                        echo "</tr>";
                    }

                    flush();
                } catch (Exception $e) {
                    // Handle individual record failure
                    $stats['failed']++;
                    $stats['errors'][] = [
                        'user' => $user->userprincipalname ?? 'Unknown',
                        'error' => $e->getMessage()
                    ];

                    echo "<tr>";
                    echo "<td>{$rowNumber}</td>";
                    echo "<td>" . ($user->userprincipalname ?? 'Unknown') . "</td>";
                    echo "<td>" . ($user->odoologintype ?? 'Unknown') . "</td>";
                    echo "<td><span class='error'>FAILED</span></td>";
                    echo "<td><span class='error'>❌ " . substr($e->getMessage(), 0, 50) . "...</span></td>";
                    echo "</tr>";
                    flush();
                }
            }

            echo "</table>";

            // STEP 3: Display final statistics
            $endTime = now();
            $duration = $endTime->diffInSeconds($startTime);

            echo "<hr>";
            echo "<h3>📊 Sync Summary</h3>";
            echo "<div class='stat-box'>";
            echo "<strong>End Time:</strong> " . $endTime->format('Y-m-d H:i:s') . "<br>";
            echo "<strong>Duration:</strong> {$duration} seconds<br><br>";
            echo "<strong>Total Processed:</strong> {$stats['processed']}<br>";
            echo "<strong class='success'>✅ Inserted:</strong> {$stats['inserted']}<br>";
            echo "<strong class='info'>🔄 Updated:</strong> {$stats['updated']}<br>";
            echo "<strong class='error'>❌ Failed:</strong> {$stats['failed']}<br>";
            echo "</div>";

            // Show errors if any
            if (count($stats['errors']) > 0) {
                echo "<h3 class='error'>❌ Errors Encountered:</h3>";
                echo "<table>";
                echo "<tr><th>User</th><th>Error Message</th></tr>";
                foreach ($stats['errors'] as $error) {
                    echo "<tr>";
                    echo "<td>{$error['user']}</td>";
                    echo "<td>{$error['error']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }

            echo "<p class='success'><strong>✅ Sync completed successfully!</strong></p>";
        } catch (Exception $e) {
            // Handle critical failure
            echo "<div class='error'>";
            echo "<h3>❌ Critical Error</h3>";
            echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
            echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
            echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
            echo "</div>";
        }

        echo "</body></html>";
    }

    /**
     * Insert a new user into Azure_Users table
     * Includes OdooLoginType to track Internal vs Portal users
     */
    private function insertUser($user)
    {
        // Convert PostgreSQL datetime string to SQL Server format
        $odooCreatedOn = null;
        if (!empty($user->odoocreatedon)) {
            try {
                $odooCreatedOn = date('Y-m-d H:i:s', strtotime($user->odoocreatedon));
            } catch (Exception $e) {
                $odooCreatedOn = null;
            }
        }

        DB::connection('sqlsrv')->insert("
            INSERT INTO Azure_Users (
                Id, userPrincipalName, displayName, surname, mail, givenName,
                AzureId, userType, jobTitle, department, accountEnabled,
                usageLocation, streetAddress, state, country, officeLocation,
                city, postalCode, telephoneNumber, mobilePhone, alternateEmailAddress,
                ageGroup, consentProvidedForMinor, legalAgeGroupClassification,
                companyName, creationType, directorySynced, invitationState,
                identityIssuer, OdooUserId, OdooEmployeeId,
                OdooLoginId, OdooCreatedOn, EmployeCode, OdooLoginType
            )
            VALUES (
                NEWID(), ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?
            )
        ", [
            $user->userprincipalname,
            $user->displayname,
            $user->surname,
            $user->mail,
            $user->givenname,
            $user->azureid,
            $user->usertype,
            $user->jobtitle,
            $user->department,
            $user->accountenabled,
            $user->usagelocation,
            $user->streetaddress,
            $user->state,
            $user->country,
            $user->officelocation,
            $user->city,
            $user->postalcode,
            $user->telephonenumber,
            $user->mobilephone,
            $user->alternateemailaddress,
            $user->agegroup,
            $user->consentprovidedforminor,
            $user->legalagegroupclassification,
            $user->companyname,
            $user->creationtype,
            $user->directorysynced,
            $user->invitationstate,
            $user->identityissuer,
            $user->odoouserid,
            $user->odooemployeeid,
            $user->odoologinid,
            $odooCreatedOn,
            $user->employecode,
            $user->odoologintype ?? 'Internal'  // NEW: Internal or Portal
        ]);
    }

    /**
     * Update an existing user in Azure_Users table
     * Updates all fields including OdooLoginType
     */
    private function updateUser($user)
    {
        // Convert PostgreSQL datetime string to SQL Server format
        $odooCreatedOn = null;
        if (!empty($user->odoocreatedon)) {
            try {
                $odooCreatedOn = date('Y-m-d H:i:s', strtotime($user->odoocreatedon));
            } catch (Exception $e) {
                $odooCreatedOn = null;
            }
        }

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
                OdooLoginType = ?
            WHERE userPrincipalName = ?
        ", [
            $user->displayname,
            $user->surname,
            $user->mail,
            $user->givenname,
            $user->azureid,
            $user->usertype,
            $user->jobtitle,
            $user->department,
            $user->accountenabled,
            $user->usagelocation,
            $user->streetaddress,
            $user->state,
            $user->country,
            $user->officelocation,
            $user->city,
            $user->postalcode,
            $user->telephonenumber,
            $user->mobilephone,
            $user->alternateemailaddress,
            $user->agegroup,
            $user->consentprovidedforminor,
            $user->legalagegroupclassification,
            $user->companyname,
            $user->creationtype,
            $user->directorysynced,
            $user->invitationstate,
            $user->identityissuer,
            $user->odoouserid,
            $user->odooemployeeid,
            $user->odoologinid,
            $odooCreatedOn,
            $user->employecode,
            $user->odoologintype ?? 'Internal',  // NEW: Internal or Portal
            $user->userprincipalname
        ]);
    }

    /**
     * Test method to see exact column names from PostgreSQL
     * 
     * Access via: /test-odoo-columns
     */
    public function testColumns()
    {
        try {
            // Fetch just ONE user to see column names
            $user = DB::connection('pgsql')->select('SELECT * FROM vw_getuserdetails LIMIT 1');

            if (empty($user)) {
                echo "<h2>No users found in view</h2>";
                return;
            }

            // Get the first user
            $firstUser = $user[0];

            // Display all column names and their values
            echo "<html><head><title>Column Test</title>";
            echo "<style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                table { border-collapse: collapse; width: 100%; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #4CAF50; color: white; }
            </style></head><body>";

            echo "<h2>PostgreSQL Column Names (Exact Case)</h2>";
            echo "<p>Total columns: " . count((array)$firstUser) . "</p>";
            echo "<table>";
            echo "<tr><th>#</th><th>Column Name (Exact)</th><th>Sample Value</th><th>Data Type</th></tr>";

            $counter = 1;
            foreach ($firstUser as $columnName => $value) {
                echo "<tr>";
                echo "<td>{$counter}</td>";
                echo "<td><strong>{$columnName}</strong></td>";
                echo "<td>" . (is_null($value) ? '<em>NULL</em>' : substr($value, 0, 50)) . "</td>";
                echo "<td>" . gettype($value) . "</td>";
                echo "</tr>";
                $counter++;
            }

            echo "</table>";
            echo "</body></html>";
        } catch (Exception $e) {
            echo "<h2>Error</h2>";
            echo "<p>" . $e->getMessage() . "</p>";
        }
    }

    /**
     * Manual sync triggered from web UI (returns redirect instead of HTML)
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function syncUsersManual()
    {
        try {
            Log::info('=== MANUAL ODOO SYNC STARTED ===', [
                'initiated_by' => Auth::id(),
                'timestamp' => now()->toDateTimeString()
            ]);

            $stats = [
                'processed' => 0,
                'inserted' => 0,
                'updated' => 0,
                'failed' => 0,
                'errors' => []
            ];

            // Fetch users from Odoo
            $odooUsers = DB::connection('pgsql')->select('SELECT * FROM vw_getuserdetails');

            $totalUsers = count($odooUsers);

            if ($totalUsers === 0) {
                Log::warning('No users found in Odoo view');
                return redirect()->back()
                    ->with('warning', 'No users found in Odoo database.');
            }

            // Process each user
            foreach ($odooUsers as $user) {
                $stats['processed']++;

                try {
                    $existingUser = DB::connection('sqlsrv')
                        ->table('Azure_Users')
                        ->where('userPrincipalName', $user->userprincipalname)
                        ->first();

                    if (!$existingUser) {
                        $this->insertUser($user);
                        $stats['inserted']++;
                    } else {
                        $this->updateUser($user);
                        $stats['updated']++;
                    }
                } catch (Exception $e) {
                    $stats['failed']++;
                    $stats['errors'][] = [
                        'user' => $user->userprincipalname ?? 'Unknown',
                        'error' => substr($e->getMessage(), 0, 100)
                    ];

                    Log::error('Failed to sync user', [
                        'upn' => $user->userprincipalname ?? 'Unknown',
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('=== MANUAL ODOO SYNC COMPLETED ===', $stats);

            // Cache the last sync time
            Cache::put('last_odoo_sync', now()->toDateTimeString(), 3600);

            // Build success message
            $message = sprintf(
                '✓ Odoo Sync completed! Processed: %d | Inserted: %d | Updated: %d',
                $stats['processed'],
                $stats['inserted'],
                $stats['updated']
            );

            if ($stats['failed'] > 0) {
                $message .= sprintf(' | Failed: %d', $stats['failed']);
            }

            return redirect()->back()
                ->with('success', $message);
        } catch (Exception $e) {
            Log::error('=== MANUAL ODOO SYNC FAILED ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Odoo sync failed: ' . $e->getMessage()]);
        }
    }


    /**
     * Test PostgreSQL connection
     * 
     * Access via: /test-pgsql-connection
     */
    public function testConnection()
    {
        echo "<html><head><title>PostgreSQL Connection Test</title></head><body>";
        echo "<h2>Testing PostgreSQL Connection</h2>";

        try {
            // Test 1: Can we connect?
            echo "<p>🔍 <strong>Test 1:</strong> Testing connection...</p>";
            $connection = DB::connection('pgsql');
            echo "<p class='success'>✅ Connection established!</p>";

            // Test 2: Can we query the database?
            echo "<p>🔍 <strong>Test 2:</strong> Testing database query...</p>";
            $result = $connection->select('SELECT 1 as test');
            echo "<p class='success'>✅ Database query works! Result: " . $result[0]->test . "</p>";

            // Test 3: Does the view exist?
            echo "<p>🔍 <strong>Test 3:</strong> Checking if vw_getuserdetails exists...</p>";
            $viewExists = $connection->select("
            SELECT COUNT(*) as count 
            FROM information_schema.tables 
            WHERE table_name = 'vw_getuserdetails'
        ");

            if ($viewExists[0]->count > 0) {
                echo "<p class='success'>✅ View exists!</p>";

                // Test 4: Try to count records
                echo "<p>🔍 <strong>Test 4:</strong> Counting records in view...</p>";
                $count = $connection->select('SELECT COUNT(*) as total FROM vw_getuserdetails');
                echo "<p class='info'>📊 Total records in view: <strong>" . $count[0]->total . "</strong></p>";

                // Test 5: Try to fetch one record
                if ($count[0]->total > 0) {
                    echo "<p>🔍 <strong>Test 5:</strong> Fetching sample record...</p>";
                    $sample = $connection->select('SELECT * FROM vw_getuserdetails LIMIT 1');
                    echo "<p class='success'>✅ Sample record retrieved!</p>";
                    echo "<pre>" . print_r($sample[0], true) . "</pre>";
                } else {
                    echo "<p class='error'>⚠️ View is empty - no records found!</p>";
                }
            } else {
                echo "<p class='error'>❌ View does NOT exist!</p>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
            echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
            echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
        }

        echo "</body></html>";
    }


    /**
     * Check view definition
     * 
     * Access via: /check-view-definition
     */
    public function checkViewDefinition()
    {
        echo "<html><head><title>View Definition Check</title></head><body>";
        echo "<h2>vw_getuserdetails - View Analysis</h2>";

        try {
            $connection = DB::connection('pgsql');

            // Get view definition
            echo "<h3>View Definition:</h3>";
            $viewDef = $connection->select("
            SELECT pg_get_viewdef('vw_getuserdetails', true) as definition
        ");
            echo "<pre>" . htmlspecialchars($viewDef[0]->definition) . "</pre>";

            // Count users in base res_users table
            echo "<h3>Base Tables Check:</h3>";

            echo "<p>Checking res_users table...</p>";
            $resUsersCount = $connection->select("SELECT COUNT(*) as count FROM res_users");
            echo "<p>📊 <strong>res_users table:</strong> {$resUsersCount[0]->count} users</p>";

            echo "<p>Checking res_partner table...</p>";
            $resPartnerCount = $connection->select("SELECT COUNT(*) as count FROM res_partner");
            echo "<p>📊 <strong>res_partner table:</strong> {$resPartnerCount[0]->count} partners</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
        }

        echo "</body></html>";
    }
}
