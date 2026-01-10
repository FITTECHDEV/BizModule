<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SyncOdooUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:odoo-users
                        {--dry-run : Run without making changes}
                        {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users from Odoo (PostgreSQL) to Azure_Users (SQL Server)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $isForced = $this->option('force');

        $this->info('===========================================');
        $this->info('🔄 Odoo to Azure User Sync');
        $this->info('===========================================');
        
        if ($isDryRun) {
            $this->warn('⚠️  DRY RUN MODE - No changes will be made');
        }

        Log::info('=== ODOO USER SYNC STARTED ===', [
            'dry_run' => $isDryRun,
            'forced' => $isForced,
            'timestamp' => now()->toDateTimeString()
        ]);

        // Track statistics
        $stats = [
            'processed' => 0,
            'inserted' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => []
        ];

        try {
            // STEP 1: Fetch all users from Odoo (PostgreSQL)
            $this->info('📥 Step 1: Fetching users from Odoo...');
            
            $odooUsers = DB::connection('pgsql')->select('SELECT * FROM vw_getuserdetails');
            
            $totalUsers = count($odooUsers);
            $this->info("✅ Found {$totalUsers} users in Odoo");
            Log::info("Odoo users fetched", ['count' => $totalUsers]);

            if ($totalUsers === 0) {
                $this->warn('⚠️  No users found in vw_getuserdetails. Sync aborted.');
                Log::warning('No users found in Odoo view');
                return 0;
            }

            // Confirmation prompt (skip if forced or dry-run)
            if (!$isDryRun && !$isForced) {
                if (!$this->confirm("Do you want to sync {$totalUsers} users?", true)) {
                    $this->info('❌ Sync cancelled by user');
                    Log::info('Sync cancelled by user');
                    return 0;
                }
            }

            // STEP 2: Process each user
            $this->info('⚙️  Step 2: Processing users...');
            $progressBar = $this->output->createProgressBar($totalUsers);
            $progressBar->start();

            foreach ($odooUsers as $index => $user) {
                $stats['processed']++;
                
                try {
                    // Check if user already exists in SQL Server
                    $existingUser = DB::connection('sqlsrv')
                        ->table('Azure_Users')
                        ->where('userPrincipalName', $user->userprincipalname)
                        ->first();

                    if (!$existingUser) {
                        // USER DOES NOT EXIST - INSERT
                        if (!$isDryRun) {
                            $this->insertUser($user);
                        }
                        $stats['inserted']++;
                        
                        Log::info("User inserted", [
                            'upn' => $user->userprincipalname,
                            'type' => $user->odoologintype ?? 'Internal',
                            'dry_run' => $isDryRun
                        ]);
                    } else {
                        // USER EXISTS - UPDATE
                        if (!$isDryRun) {
                            $this->updateUser($user);
                        }
                        $stats['updated']++;
                        
                        Log::info("User updated", [
                            'upn' => $user->userprincipalname,
                            'type' => $user->odoologintype ?? 'Internal',
                            'dry_run' => $isDryRun
                        ]);
                    }
                    
                    $progressBar->advance();
                    
                } catch (Exception $e) {
                    // Handle individual record failure
                    $stats['failed']++;
                    $errorMsg = substr($e->getMessage(), 0, 100);
                    $stats['errors'][] = [
                        'user' => $user->userprincipalname ?? 'Unknown',
                        'error' => $errorMsg
                    ];
                    
                    $this->warn("\n⚠️  Failed: {$user->userprincipalname} - {$errorMsg}");
                    Log::error("Failed to sync user", [
                        'upn' => $user->userprincipalname ?? 'Unknown',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    $progressBar->advance();
                }
            }

            $progressBar->finish();
            $this->newLine(2);

            // STEP 3: Display final statistics
            $this->info('===========================================');
            $this->info('📊 Sync Summary');
            $this->info('===========================================');
            $this->info("Total Processed: {$stats['processed']}");
            $this->info("✅ Inserted: {$stats['inserted']}");
            $this->info("🔄 Updated: {$stats['updated']}");
            $this->info("❌ Failed: {$stats['failed']}");
            
            if ($isDryRun) {
                $this->warn('⚠️  This was a DRY RUN - no changes were made');
            }

            Log::info('=== ODOO USER SYNC COMPLETED ===', $stats);

            // Show errors if any
            if (count($stats['errors']) > 0) {
                $this->newLine();
                $this->error('❌ Errors Encountered:');
                foreach ($stats['errors'] as $error) {
                    $this->error("  • {$error['user']}: {$error['error']}");
                }
            }

            $this->newLine();
            $this->info('✅ Sync completed successfully!');

            return 0;

        } catch (Exception $e) {
            // Handle critical failure
            $this->error('===========================================');
            $this->error('❌ Critical Error');
            $this->error('===========================================');
            $this->error("Error: {$e->getMessage()}");
            $this->error("File: {$e->getFile()}");
            $this->error("Line: {$e->getLine()}");
            
            Log::error('=== ODOO USER SYNC FAILED ===', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }
    }

    /**
     * Insert a new user into Azure_Users table
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
            $user->odoologintype ?? 'Internal'
        ]);
    }

    /**
     * Update an existing user in Azure_Users table
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
            $user->odoologintype ?? 'Internal',
            $user->userprincipalname
        ]);
    }
}