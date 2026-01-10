<?php

namespace App\Services;

use App\Models\AzureUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AzureUserDeprovisioningService
{
    protected AzureGraphService $azureService;

    public function __construct(AzureGraphService $azureService)
    {
        $this->azureService = $azureService;
    }

    /**
     * Deactivate a user (soft delete)
     * - Disables user in Azure AD
     * - Removes from all Azure groups
     * - Marks as inactive in local database
     * - Preserves all data for audit trail
     *
     * @param AzureUser $user
     * @param int|null $performedBy User ID of admin performing the action
     * @param string|null $reason Reason for deactivation
     * @return array
     */
    public function deactivateUser(AzureUser $user, ?int $performedBy = null, ?string $reason = null): array
    {
        // Check if user can be deactivated
        if (!$user->canBeDeactivated()) {
            $message = $user->isDeactivated() 
                ? 'User is already deactivated' 
                : 'User cannot be deactivated (not provisioned to Azure)';
            
            // // AzureAuditLogger::logUserDeactivationFailed(
            //     $user->id,
            //     $message,
            //     $performedBy
            // );

            return [
                'success' => false,
                'message' => $message,
                'user_id' => $user->id
            ];
        }

        DB::beginTransaction();

        try {
            // Step 1: Log deactivation start
            // // AzureAuditLogger::logUserDeactivationStarted(
            //     $user->id,
            //     $reason,
            //     $performedBy
            // );

            Log::info('Starting user deactivation process', [
                'user_id' => $user->id,
                'email' => $user->email,
                'azure_id' => $user->azure_id,
                'performed_by' => $performedBy,
                'reason' => $reason
            ]);

            // Step 2: Get all Azure groups user is member of
            $userModules = $user->modules()->get();
            $removedGroups = [];
            $failedGroups = [];

            foreach ($userModules as $module) {
                $azureGroupId = $module->pivot->azure_group_id;
                
                if (empty($azureGroupId)) {
                    Log::warning('Module has no Azure group ID', [
                        'user_id' => $user->id,
                        'module_id' => $module->id,
                        'module_name' => $module->name
                    ]);
                    continue;
                }

                try {
                    // Remove user from Azure group
                    $this->azureService->removeUserFromGroup($user->azure_id, $azureGroupId);
                    
                    $removedGroups[] = [
                        'module' => $module->name,
                        'group_id' => $azureGroupId
                    ];

                    // // AzureAuditLogger::logAzureGroupRemoval(
                    //     $user->id,
                    //     $azureGroupId,
                    //     $module->name,
                    //     $performedBy
                    // );

                    Log::info('Removed user from Azure group', [
                        'user_id' => $user->id,
                        'module' => $module->name,
                        'azure_group_id' => $azureGroupId
                    ]);

                } catch (Exception $e) {
                    $failedGroups[] = [
                        'module' => $module->name,
                        'group_id' => $azureGroupId,
                        'error' => $e->getMessage()
                    ];

                    Log::error('Failed to remove user from Azure group', [
                        'user_id' => $user->id,
                        'module' => $module->name,
                        'azure_group_id' => $azureGroupId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Step 3: Disable user account in Azure AD
            try {
                $this->azureService->disableUserAccount($user->azure_id);
                
                // // AzureAuditLogger::logAzureUserDisabled(
                //     $user->id,
                //     $user->azure_id,
                //     $performedBy
                // );

                Log::info('Disabled user account in Azure AD', [
                    'user_id' => $user->id,
                    'azure_id' => $user->azure_id
                ]);

            } catch (Exception $e) {
                // If we can't disable in Azure, we should rollback everything
                Log::error('Failed to disable user in Azure AD - rolling back', [
                    'user_id' => $user->id,
                    'azure_id' => $user->azure_id,
                    'error' => $e->getMessage()
                ]);

                DB::rollBack();

                // // AzureAuditLogger::logUserDeactivationFailed(
                //     $user->id,
                //     'Failed to disable Azure account: ' . $e->getMessage(),
                //     $performedBy
                // );

                return [
                    'success' => false,
                    'message' => 'Failed to disable user in Azure AD',
                    'error' => $e->getMessage(),
                    'user_id' => $user->id
                ];
            }

            // Step 4: Update local database
            $user->update([
                'is_active' => false,
                'deactivated_at' => now(),
                'status' => 'inactive'
            ]);

            // Step 5: Update module assignments status
            foreach ($userModules as $module) {
                DB::table('azure_user_modules')
                    ->where('user_id', $user->id)
                    ->where('module_id', $module->id)
                    ->update([
                        'external_sync_status' => 'deactivated',
                        'updated_at' => now()
                    ]);
            }

            DB::commit();

            // Step 6: Log successful deactivation
            // // AzureAuditLogger::logUserDeactivationCompleted(
            //     $user->id,
            //     [
            //         'groups_removed' => count($removedGroups),
            //         'groups_failed' => count($failedGroups),
            //         'reason' => $reason
            //     ],
            //     $performedBy
            // );

            Log::info('User deactivation completed successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'groups_removed' => count($removedGroups),
                'groups_failed' => count($failedGroups)
            ]);

            return [
                'success' => true,
                'message' => 'User deactivated successfully',
                'user_id' => $user->id,
                'details' => [
                    'groups_removed' => $removedGroups,
                    'groups_failed' => $failedGroups,
                    'azure_disabled' => true,
                    'deactivated_at' => $user->deactivated_at->toDateTimeString()
                ]
            ];

        } catch (Exception $e) {
            DB::rollBack();

            // // AzureAuditLogger::logUserDeactivationFailed(
            //     $user->id,
            //     $e->getMessage(),
            //     $performedBy
            // );

            Log::error('User deactivation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to deactivate user',
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ];
        }
    }

    /**
     * Reactivate a previously deactivated user
     * - Enables user in Azure AD
     * - Re-adds to appropriate Azure groups based on module assignments
     * - Marks as active in local database
     *
     * @param AzureUser $user
     * @param int|null $performedBy User ID of admin performing the action
     * @param string|null $reason Reason for reactivation
     * @return array
     */
    public function reactivateUser(AzureUser $user, ?int $performedBy = null, ?string $reason = null): array
    {
        // Check if user can be reactivated
        if (!$user->canBeReactivated()) {
            $message = $user->isActive() 
                ? 'User is already active' 
                : 'User cannot be reactivated (not provisioned to Azure)';
            
            // // AzureAuditLogger::logUserReactivationFailed(
            //     $user->id,
            //     $message,
            //     $performedBy
            // );

            return [
                'success' => false,
                'message' => $message,
                'user_id' => $user->id
            ];
        }

        DB::beginTransaction();

        try {
            // Step 1: Log reactivation start
            // // AzureAuditLogger::logUserReactivationStarted(
            //     $user->id,
            //     $reason,
            //     $performedBy
            // );

            Log::info('Starting user reactivation process', [
                'user_id' => $user->id,
                'email' => $user->email,
                'azure_id' => $user->azure_id,
                'performed_by' => $performedBy,
                'reason' => $reason
            ]);

            // Step 2: Enable user account in Azure AD
            try {
                $this->azureService->enableUserAccount($user->azure_id);
                
                // // AzureAuditLogger::logAzureUserEnabled(
                //     $user->id,
                //     $user->azure_id,
                //     $performedBy
                // );

                Log::info('Enabled user account in Azure AD', [
                    'user_id' => $user->id,
                    'azure_id' => $user->azure_id
                ]);

            } catch (Exception $e) {
                Log::error('Failed to enable user in Azure AD', [
                    'user_id' => $user->id,
                    'azure_id' => $user->azure_id,
                    'error' => $e->getMessage()
                ]);

                DB::rollBack();

                // // AzureAuditLogger::logUserReactivationFailed(
                //     $user->id,
                //     'Failed to enable Azure account: ' . $e->getMessage(),
                //     $performedBy
                // );

                return [
                    'success' => false,
                    'message' => 'Failed to enable user in Azure AD',
                    'error' => $e->getMessage(),
                    'user_id' => $user->id
                ];
            }

            // Step 3: Re-add user to Azure groups based on module assignments
            $userModules = $user->modules()->get();
            $addedGroups = [];
            $failedGroups = [];

            foreach ($userModules as $module) {
                $azureGroupId = $module->pivot->azure_group_id;
                
                if (empty($azureGroupId)) {
                    Log::warning('Module has no Azure group ID', [
                        'user_id' => $user->id,
                        'module_id' => $module->id,
                        'module_name' => $module->name
                    ]);
                    continue;
                }

                try {
                    // Add user to Azure group
                    $this->azureService->addUserToGroup($user->azure_id, $azureGroupId);
                    
                    $addedGroups[] = [
                        'module' => $module->name,
                        'group_id' => $azureGroupId
                    ];

                    // // AzureAuditLogger::logAzureGroupAssignment(
                    //     $user->id,
                    //     $azureGroupId,
                    //     $module->name,
                    //     $performedBy
                    // );

                    // Update module assignment status
                    DB::table('azure_user_modules')
                        ->where('user_id', $user->id)
                        ->where('module_id', $module->id)
                        ->update([
                            'external_sync_status' => 'synced',
                            'updated_at' => now()
                        ]);

                    Log::info('Re-added user to Azure group', [
                        'user_id' => $user->id,
                        'module' => $module->name,
                        'azure_group_id' => $azureGroupId
                    ]);

                } catch (Exception $e) {
                    $failedGroups[] = [
                        'module' => $module->name,
                        'group_id' => $azureGroupId,
                        'error' => $e->getMessage()
                    ];

                    // Mark module as failed
                    DB::table('azure_user_modules')
                        ->where('user_id', $user->id)
                        ->where('module_id', $module->id)
                        ->update([
                            'external_sync_status' => 'failed',
                            'updated_at' => now()
                        ]);

                    Log::error('Failed to re-add user to Azure group', [
                        'user_id' => $user->id,
                        'module' => $module->name,
                        'azure_group_id' => $azureGroupId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Step 4: Update local database
            $user->update([
                'is_active' => true,
                'deactivated_at' => null,
                'status' => 'active'
            ]);

            DB::commit();

            // Step 5: Log successful reactivation
            // // AzureAuditLogger::logUserReactivationCompleted(
            //     $user->id,
            //     [
            //         'groups_added' => count($addedGroups),
            //         'groups_failed' => count($failedGroups),
            //         'reason' => $reason
            //     ],
            //     $performedBy
            // );

            Log::info('User reactivation completed successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'groups_added' => count($addedGroups),
                'groups_failed' => count($failedGroups)
            ]);

            return [
                'success' => true,
                'message' => 'User reactivated successfully',
                'user_id' => $user->id,
                'details' => [
                    'groups_added' => $addedGroups,
                    'groups_failed' => $failedGroups,
                    'azure_enabled' => true,
                    'reactivated_at' => now()->toDateTimeString()
                ]
            ];

        } catch (Exception $e) {
            DB::rollBack();

            // // AzureAuditLogger::logUserReactivationFailed(
            //     $user->id,
            //     $e->getMessage(),
            //     $performedBy
            // );

            Log::error('User reactivation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to reactivate user',
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ];
        }
    }

    /**
     * Get deactivation summary for a user
     *
     * @param AzureUser $user
     * @return array
     */
    public function getDeactivationSummary(AzureUser $user): array
    {
        return [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => $user->is_active,
            'deactivated_at' => $user->deactivated_at?->toDateTimeString(),
            'azure_id' => $user->azure_id,
            'total_modules' => $user->modules()->count(),
            'can_be_deactivated' => $user->canBeDeactivated(),
            'can_be_reactivated' => $user->canBeReactivated(),
        ];
    }
}