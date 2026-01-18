<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\AzureGraphService;

/**
 * Repair Azure IDs for users that exist in Azure AD but have NULL AzureId in database
 * 
 * This command will:
 * 1. Find all users with NULL AzureId in Azure_Users table
 * 2. Look them up in Azure AD by email (userPrincipalName)
 * 3. Update the database with the correct Azure Object ID
 * 4. Optionally provision users that don't exist in Azure AD
 * 
 * Usage:
 *   php artisan azure:repair-ids --check-only     # Dry run - just check what would be fixed
 *   php artisan azure:repair-ids                   # Actually fix the IDs
 *   php artisan azure:repair-ids --provision       # Also provision missing users to Azure AD
 */
class RepairAzureIds extends Command
{
    protected $signature = 'azure:repair-ids 
                            {--check-only : Only check for issues without fixing}
                            {--provision : Also provision users that don\'t exist in Azure AD}
                            {--email= : Repair specific user by email}';

    protected $description = 'Repair Azure Object IDs for users with NULL AzureId';

    private $azureService;
    private $stats = [
        'total_checked' => 0,
        'missing_azure_id' => 0,
        'found_in_azure' => 0,
        'not_found_in_azure' => 0,
        'repaired' => 0,
        'provisioned' => 0,
        'failed' => 0,
        'errors' => [],
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->azureService = app(AzureGraphService::class);
        $checkOnly = $this->option('check-only');
        $provision = $this->option('provision');
        $specificEmail = $this->option('email');

        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('🔧 Azure Object ID Repair Tool');
        $this->info('═══════════════════════════════════════════════════════════');
        
        if ($checkOnly) {
            $this->warn('⚠️  CHECK-ONLY MODE: No changes will be made');
        }
        if ($provision) {
            $this->info('✨ PROVISION MODE: Will create users in Azure AD if not found');
        }

        $this->line('');
        $this->info('📋 Step 1: Fetching users from database...');

        // Build query
        $query = DB::connection('sqlsrv')
            ->table('Azure_Users')
            ->select([
                'Id',
                'userPrincipalName',
                'displayName',
                'mail',
                'AzureId',
                'Status',
                'OdooUserId',
                'EmployeCode',
            ]);

        // Filter by specific email if provided
        if ($specificEmail) {
            $query->where('userPrincipalName', $specificEmail);
            $this->info("🎯 Filtering for user: {$specificEmail}");
        }

        $users = $query->get();
        $this->stats['total_checked'] = $users->count();

        if ($users->isEmpty()) {
            if ($specificEmail) {
                $this->error("❌ User not found: {$specificEmail}");
                return 1;
            }
            $this->warn('⚠️  No users found in database');
            return 0;
        }

        $this->info("✅ Found {$users->count()} user(s) in database");
        $this->line('');

        // Filter users with NULL AzureId
        $usersWithoutAzureId = $users->filter(fn($u) => empty($u->AzureId));
        $this->stats['missing_azure_id'] = $usersWithoutAzureId->count();

        if ($usersWithoutAzureId->isEmpty()) {
            $this->info('✨ All users already have Azure IDs!');
            $this->displayStats();
            return 0;
        }

        $this->warn("⚠️  Found {$usersWithoutAzureId->count()} user(s) with NULL AzureId");
        $this->line('');
        
        // Display table of users needing repair
        $this->table(
            ['Email', 'Display Name', 'Status', 'Odoo ID', 'Employee Code'],
            $usersWithoutAzureId->map(fn($u) => [
                $u->userPrincipalName,
                $u->displayName ?? 'N/A',
                $u->Status ?? 'N/A',
                $u->OdooUserId ?? 'N/A',
                $u->EmployeCode ?? 'N/A',
            ])->toArray()
        );

        $this->line('');
        $this->info('📋 Step 2: Looking up users in Azure AD...');
        $this->line('');

        // Progress bar
        $bar = $this->output->createProgressBar($usersWithoutAzureId->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
        $bar->setMessage('Starting...');
        $bar->start();

        foreach ($usersWithoutAzureId as $user) {
            $bar->setMessage("Checking: {$user->userPrincipalName}");
            
            try {
                // Try to find user in Azure AD
                $azureUser = $this->azureService->findUserByUPN($user->userPrincipalName);

                if ($azureUser && isset($azureUser['id'])) {
                    // User exists in Azure AD
                    $this->stats['found_in_azure']++;
                    $azureId = $azureUser['id'];

                    if (!$checkOnly) {
                        // Update database with correct Azure ID
                        DB::connection('sqlsrv')
                            ->table('Azure_Users')
                            ->where('Id', $user->Id)
                            ->update([
                                'AzureId' => $azureId,
                                'Status' => 'Active', // Activate user since they exist in Azure
                            ]);

                        $this->stats['repaired']++;
                        
                        Log::info('Azure ID repaired', [
                            'user_id' => $user->Id,
                            'email' => $user->userPrincipalName,
                            'azure_id' => $azureId,
                        ]);
                    }
                } else {
                    // User NOT found in Azure AD
                    $this->stats['not_found_in_azure']++;

                    if ($provision && !$checkOnly) {
                        // Provision user to Azure AD
                        $provisionResult = $this->provisionToAzureAD($user);
                        
                        if ($provisionResult['success']) {
                            $this->stats['provisioned']++;
                            $this->stats['repaired']++;
                        } else {
                            $this->stats['failed']++;
                            $this->stats['errors'][] = [
                                'user' => $user->userPrincipalName,
                                'error' => $provisionResult['error'],
                            ];
                        }
                    }
                }

                $bar->advance();
            } catch (\Exception $e) {
                $this->stats['failed']++;
                $this->stats['errors'][] = [
                    'user' => $user->userPrincipalName,
                    'error' => $e->getMessage(),
                ];
                
                Log::error('Failed to repair Azure ID', [
                    'user_id' => $user->Id,
                    'email' => $user->userPrincipalName,
                    'error' => $e->getMessage(),
                ]);

                $bar->advance();
            }
        }

        $bar->setMessage('Complete!');
        $bar->finish();
        $this->line('');
        $this->line('');

        // Display results
        $this->displayStats();

        // Show detailed errors if any
        if (!empty($this->stats['errors'])) {
            $this->line('');
            $this->error('❌ Errors occurred:');
            $this->table(
                ['User', 'Error'],
                collect($this->stats['errors'])->map(fn($e) => [
                    $e['user'],
                    substr($e['error'], 0, 80),
                ])->toArray()
            );
        }

        // Show next steps
        $this->line('');
        $this->displayNextSteps($checkOnly, $provision);

        return 0;
    }

    /**
     * Provision user to Azure AD
     */
    private function provisionToAzureAD($user): array
    {
        try {
            Log::info('Provisioning user to Azure AD', [
                'user' => $user->userPrincipalName,
            ]);

            // Split name
            $nameParts = explode(' ', trim($user->displayName ?? ''), 2);
            $givenName = $nameParts[0] ?? 'Unknown';
            $surname = $nameParts[1] ?? $nameParts[0] ?? 'User';

            // Build Azure user payload
            $azureUserData = [
                'accountEnabled' => true,
                'displayName' => $user->displayName ?? 'Unknown User',
                'mailNickname' => explode('@', $user->userPrincipalName)[0],
                'userPrincipalName' => $user->userPrincipalName,
                'givenName' => $givenName,
                'surname' => $surname,
                'usageLocation' => 'KE',
                'passwordProfile' => [
                    'forceChangePasswordNextSignIn' => true,
                    'password' => $this->generateSecurePassword(),
                ],
            ];

            if (!empty($user->mail)) {
                $azureUserData['mail'] = $user->mail;
            }

            // Call Azure Graph API
            $result = $this->azureService->createUser($azureUserData);

            // Update database
            DB::connection('sqlsrv')
                ->table('Azure_Users')
                ->where('Id', $user->Id)
                ->update([
                    'AzureId' => $result['id'],
                    'Status' => 'Active',
                ]);

            Log::info('User provisioned to Azure AD successfully', [
                'user' => $user->userPrincipalName,
                'azure_id' => $result['id'],
            ]);

            return [
                'success' => true,
                'azure_id' => $result['id'],
                'error' => null,
            ];
        } catch (\Exception $e) {
            Log::error('Azure AD provisioning failed', [
                'user' => $user->userPrincipalName,
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
     * Generate secure password
     */
    private function generateSecurePassword(): string
    {
        $length = 16;
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '!@#$%^&*()-_=+[]{}|;:,.<>?';

        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        $allChars = $uppercase . $lowercase . $numbers . $special;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        return str_shuffle($password);
    }

    /**
     * Display statistics
     */
    private function displayStats()
    {
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('📊 Summary Statistics');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->line("Total Users Checked:     {$this->stats['total_checked']}");
        $this->line("Missing Azure ID:        {$this->stats['missing_azure_id']}");
        
        if ($this->stats['found_in_azure'] > 0) {
            $this->info("✅ Found in Azure AD:    {$this->stats['found_in_azure']}");
        }
        
        if ($this->stats['not_found_in_azure'] > 0) {
            $this->warn("⚠️  Not in Azure AD:      {$this->stats['not_found_in_azure']}");
        }
        
        if ($this->stats['repaired'] > 0) {
            $this->info("🔧 Repaired:             {$this->stats['repaired']}");
        }
        
        if ($this->stats['provisioned'] > 0) {
            $this->info("✨ Provisioned:          {$this->stats['provisioned']}");
        }
        
        if ($this->stats['failed'] > 0) {
            $this->error("❌ Failed:               {$this->stats['failed']}");
        }
        
        $this->info('═══════════════════════════════════════════════════════════');
    }

    /**
     * Display next steps
     */
    private function displayNextSteps($checkOnly, $provision)
    {
        if ($checkOnly) {
            $this->warn('⚠️  This was a CHECK-ONLY run. To actually fix the issues, run:');
            $this->line('');
            $this->comment('   php artisan azure:repair-ids');
            $this->line('');
            
            if ($this->stats['not_found_in_azure'] > 0) {
                $this->line('To also provision missing users to Azure AD:');
                $this->comment('   php artisan azure:repair-ids --provision');
            }
        } else {
            if ($this->stats['repaired'] > 0) {
                $this->info('✅ Azure IDs have been repaired!');
            }
            
            if ($this->stats['not_found_in_azure'] > 0 && !$provision) {
                $this->line('');
                $this->warn("⚠️  {$this->stats['not_found_in_azure']} user(s) were not found in Azure AD.");
                $this->line('To provision them, run:');
                $this->comment('   php artisan azure:repair-ids --provision');
            }
        }

        $this->line('');
        $this->info('💡 Additional commands:');
        $this->line('');
        $this->comment('   # Check specific user');
        $this->comment('   php artisan azure:repair-ids --email=user@example.com');
        $this->line('');
        $this->comment('   # Dry run with provision preview');
        $this->comment('   php artisan azure:repair-ids --check-only --provision');
    }
}