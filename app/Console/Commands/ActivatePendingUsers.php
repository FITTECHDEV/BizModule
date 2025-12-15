<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ActivatePendingUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'azure:activate-pending-users
                        {--dry-run : Run without making changes}
                        {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate pending users who have been synced with external systems';

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
        $this->info('🔄 Azure User Activation Process');
        $this->info('===========================================');
        
        if ($isDryRun) {
            $this->warn('⚠️  DRY RUN MODE - No changes will be made');
        }

        Log::info('=== USER ACTIVATION STARTED ===', [
            'dry_run' => $isDryRun,
            'forced' => $isForced,
            'timestamp' => now()->toDateTimeString()
        ]);

        // Track statistics
        $stats = [
            'total_pending' => 0,
            'eligible' => 0,
            'activated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'reasons' => []
        ];

        try {
            // STEP 1: Fetch all pending users
            $this->info('📥 Step 1: Fetching pending users...');
            
            $pendingUsers = DB::connection('sqlsrv')
                ->table('Azure_Users')
                ->where('Status', 'Pending')
                ->get();
            
            $stats['total_pending'] = $pendingUsers->count();
            
            $this->info("✅ Found {$stats['total_pending']} pending users");
            Log::info("Pending users fetched", ['count' => $stats['total_pending']]);

            if ($stats['total_pending'] === 0) {
                $this->info('✅ No pending users found. Nothing to do!');
                Log::info('No pending users found');
                return 0;
            }

            // Confirmation prompt (skip if forced or dry-run)
            if (!$isDryRun && !$isForced) {
                if (!$this->confirm("Do you want to process {$stats['total_pending']} pending users?", true)) {
                    $this->info('❌ Activation cancelled by user');
                    Log::info('Activation cancelled by user');
                    return 0;
                }
            }

            // STEP 2: Check eligibility and activate
            $this->info('⚙️  Step 2: Checking eligibility and activating users...');
            $this->newLine();

            $progressBar = $this->output->createProgressBar($stats['total_pending']);
            $progressBar->start();

            foreach ($pendingUsers as $user) {
                try {
                    // Check activation criteria
                    $eligibilityCheck = $this->checkEligibility($user);
                    
                    if ($eligibilityCheck['eligible']) {
                        // User is eligible for activation
                        $stats['eligible']++;
                        
                        if (!$isDryRun) {
                            // Activate the user
                            DB::connection('sqlsrv')
                                ->table('Azure_Users')
                                ->where('Id', $user->Id)
                                ->update([
                                    'Status' => 'Active',
                                    'LastModifiedOn' => DB::raw('GETDATE()'),
                                    'LastModifiedBy' => 'system'
                                ]);
                        }
                        
                        $stats['activated']++;
                        
                        $this->newLine();
                        $this->info("✅ Activated: {$user->userPrincipalName}");
                        $this->info("   Reason: {$eligibilityCheck['reason']}");
                        
                        Log::info('User activated', [
                            'user_id' => $user->Id,
                            'upn' => $user->userPrincipalName,
                            'reason' => $eligibilityCheck['reason'],
                            'synced_systems' => $eligibilityCheck['synced_systems'],
                            'dry_run' => $isDryRun
                        ]);
                        
                    } else {
                        // User not eligible
                        $stats['skipped']++;
                        $stats['reasons'][] = [
                            'upn' => $user->userPrincipalName,
                            'reason' => $eligibilityCheck['reason']
                        ];
                        
                        Log::debug('User skipped', [
                            'user_id' => $user->Id,
                            'upn' => $user->userPrincipalName,
                            'reason' => $eligibilityCheck['reason']
                        ]);
                    }
                    
                    $progressBar->advance();
                    
                } catch (Exception $e) {
                    $stats['failed']++;
                    
                    $this->newLine();
                    $this->error("❌ Failed: {$user->userPrincipalName}");
                    $this->error("   Error: {$e->getMessage()}");
                    
                    Log::error('Failed to activate user', [
                        'user_id' => $user->Id,
                        'upn' => $user->userPrincipalName,
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
            $this->info('📊 Activation Summary');
            $this->info('===========================================');
            $this->info("Total Pending Users: {$stats['total_pending']}");
            $this->info("✅ Eligible for Activation: {$stats['eligible']}");
            $this->info("✅ Successfully Activated: {$stats['activated']}");
            $this->info("⏭️  Skipped (not ready): {$stats['skipped']}");
            $this->info("❌ Failed: {$stats['failed']}");
            
            if ($isDryRun) {
                $this->warn('⚠️  This was a DRY RUN - no changes were made');
            }

            Log::info('=== USER ACTIVATION COMPLETED ===', $stats);

            // Show skip reasons if any
            if (count($stats['reasons']) > 0 && count($stats['reasons']) <= 10) {
                $this->newLine();
                $this->warn('⏭️  Users Skipped (Not Ready):');
                foreach ($stats['reasons'] as $skip) {
                    $this->warn("  • {$skip['upn']}: {$skip['reason']}");
                }
            } elseif (count($stats['reasons']) > 10) {
                $this->newLine();
                $this->warn("⏭️  {$stats['skipped']} users skipped. Check logs for details.");
            }

            $this->newLine();
            $this->info('✅ Activation process completed successfully!');

            return 0;

        } catch (Exception $e) {
            // Handle critical failure
            $this->error('===========================================');
            $this->error('❌ Critical Error');
            $this->error('===========================================');
            $this->error("Error: {$e->getMessage()}");
            $this->error("File: {$e->getFile()}");
            $this->error("Line: {$e->getLine()}");
            
            Log::error('=== USER ACTIVATION FAILED ===', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }
    }

    /**
     * Check if a user is eligible for activation
     * 
     * Criteria:
     * 1. Has AzureId (provisioned to Azure AD)
     * 2. Has at least ONE external system ID (Odoo, SCM, or BIZ)
     * 3. Has at least ONE module assignment
     * 
     * @param object $user
     * @return array ['eligible' => bool, 'reason' => string, 'synced_systems' => array]
     */
    private function checkEligibility($user): array
    {
        $syncedSystems = [];
        $reasons = [];

        // Check 1: Azure AD provisioning
        if (empty($user->AzureId)) {
            return [
                'eligible' => false,
                'reason' => 'Not provisioned to Azure AD (missing AzureId)',
                'synced_systems' => []
            ];
        }

        // Check 2: External system synchronization
        $hasOdoo = !empty($user->OdooUserId);
        $hasSCM = !empty($user->SCMUserID);
        $hasBIZ = !empty($user->BizUserID);

        if ($hasOdoo) {
            $syncedSystems[] = 'Odoo';
        }
        if ($hasSCM) {
            $syncedSystems[] = 'SCM';
        }
        if ($hasBIZ) {
            $syncedSystems[] = 'BIZ';
        }

        if (empty($syncedSystems)) {
            return [
                'eligible' => false,
                'reason' => 'No external system IDs populated (Odoo, SCM, or BIZ)',
                'synced_systems' => []
            ];
        }

        // Check 3: Module assignments
        $moduleCount = DB::connection('sqlsrv')
            ->table('Azure_Application_UserMapping')
            ->where('user_id', $user->userPrincipalName)
            ->where('IsActive', 1)
            ->where('IsDelete', 0)
            ->count();

        if ($moduleCount === 0) {
            return [
                'eligible' => false,
                'reason' => 'No module assignments found',
                'synced_systems' => $syncedSystems
            ];
        }

        // All checks passed!
        return [
            'eligible' => true,
            'reason' => sprintf(
                'Synced with %s (%d module assignment%s)',
                implode(', ', $syncedSystems),
                $moduleCount,
                $moduleCount === 1 ? '' : 's'
            ),
            'synced_systems' => $syncedSystems
        ];
    }
}