<?php

namespace App\Services;

use App\Models\AzureUser;
use App\Models\AzureModule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AzureUserProvisioningService
{
    private AzureGraphService $azureService;
    private AzureModuleAssignmentService $moduleAssignmentService;

    /**
     * Constructor - Inject dependencies
     */
    public function __construct(
        AzureGraphService $azureService,
        AzureModuleAssignmentService $moduleAssignmentService
    ) {
        $this->azureService = $azureService;
        $this->moduleAssignmentService = $moduleAssignmentService;
    }

    /**
     * Provision a new user (create in local DB + Azure + assign modules)
     * WITH ROLLBACK SUPPORT + AUDIT LOGGING
     * 
     * @param array $userData User information
     * @param array $moduleAssignments Array of ['module_id' => X, 'role_id' => Y, 'location' => Z]
     * @return array Provisioning results
     */
    public function provisionUser(array $userData, array $moduleAssignments): array
    {
        $results = [
            'success' => false,
            'user' => null,
            'azure' => null,
            'modules' => [],
            'errors' => [],
            'partial_failure' => false,
            'rollback_performed' => false
        ];

        $azureUserCreated = false;
        $azureUserId = null;

        DB::beginTransaction();

        try {
            // Step 1: Create user in local database with 'pending' status
            $user = $this->createLocalUser($userData);
            $results['user'] = $user;

            // 🎯 AUDIT: Log provisioning started
            // // AzureAuditLogger::log(
            //     'user.provisioning.started',
            //     $user,
            //     'success',
            //     null,
            //     [
            //         'employee_id' => $user->employee_id,
            //         'email' => $user->email,
            //         'modules_count' => count($moduleAssignments),
            //         'module_ids' => array_column($moduleAssignments, 'module_id'),
            //     ]
            // );

            Log::channel('provisioning')->info('Starting user provisioning', [
                'user_id' => $user->id,
                'employee_id' => $user->employee_id,
                'modules_count' => count($moduleAssignments)
            ]);

            // Step 2: Create or update user in Azure AD
            try {
                if ($user->azure_id) {
                    // Already linked → update existing Azure user
                    $this->azureService->updateUser($user->azure_id, [
                        'name'       => $user->name,
                        'email'      => $user->email,
                        'phone'      => $user->phone,
                        'job_title'  => $user->job_title,
                        'department' => $user->department,
                    ]);

                    $azureUserId = $user->azure_id;
                    $results['azure'] = [
                        'success' => true,
                        'azure_id' => $user->azure_id,
                        'azure_upn' => $user->azure_upn,
                        'action' => 'updated'
                    ];

                    // 🎯 AUDIT: Log Azure update
                    // // AzureAuditLogger::azureUserUpdated(
                    //     $user,
                    //     $user->azure_id,
                    //     [
                    //         'fields_updated' => ['name', 'email', 'phone', 'job_title', 'department']
                    //     ]
                    // );

                    Log::channel('provisioning')->info('User updated in Azure AD', [
                        'user_id' => $user->id,
                        'azure_id' => $user->azure_id
                    ]);
                } else {
                    // Create new Azure user
                    Log::channel('provisioning')->info('Creating new Azure AD user', [
                        'user_id' => $user->id,
                        'employee_id' => $user->employee_id
                    ]);

                    $azureData = $this->azureService->createUser([
                        'name'        => $user->name,
                        'employee_id' => $user->employee_id,
                        'email'       => $user->email,
                        'phone'       => $user->phone,
                        'job_title'   => $user->job_title,
                        'department'  => $user->department,
                        'company_name' => $user->company->name ?? null,
                    ]);

                    $azureUserId = $azureData['azure_id'];
                    $azureUserCreated = true;

                    Log::channel('provisioning')->info('Azure AD user created successfully', [
                        'user_id' => $user->id,
                        'azure_id' => $azureUserId,
                        'azure_upn' => $azureData['azure_upn']
                    ]);

                    // Step 3: Update local database with Azure details
                    try {
                        $user->update([
                            'azure_id'          => $azureData['azure_id'],
                            'azure_upn'         => $azureData['azure_upn'],
                            'azure_display_name' => $azureData['azure_display_name'],
                            'status'            => 'active',
                        ]);

                        Log::channel('provisioning')->info('Local user record updated with Azure details', [
                            'user_id' => $user->id,
                            'azure_id' => $azureData['azure_id']
                        ]);
                    } catch (Exception $dbException) {
                        Log::channel('provisioning')->error('Failed to update local user with Azure details', [
                            'user_id' => $user->id,
                            'azure_id' => $azureUserId,
                            'error' => $dbException->getMessage()
                        ]);

                        throw new Exception('Database update failed after Azure user creation: ' . $dbException->getMessage());
                    }

                    // 🎯 AUDIT: Log successful Azure creation
                    // // AzureAuditLogger::azureUserCreated(
                    //     $user,
                    //     $azureData['azure_id'],
                    //     $azureData['azure_upn'],
                    //     [
                    //         'display_name' => $azureData['azure_display_name'],
                    //         'password_sent' => !empty($user->email),
                    //         'temporary_password_created' => true,
                    //     ]
                    // );

                    $results['azure'] = [
                        'success'  => true,
                        'azure_id' => $azureData['azure_id'],
                        'azure_upn' => $azureData['azure_upn'],
                        'action' => 'created'
                    ];
                }
            } catch (Exception $azureException) {
                $errorMessage = $azureException->getMessage();

                // 🎯 AUDIT: Log Azure creation failure
                // // AzureAuditLogger::azureUserCreationFailed(
                //     $user,
                //     $errorMessage,
                //     [
                //         'employee_id' => $user->employee_id,
                //         'email' => $user->email,
                //         'attempted_action' => $user->azure_id ? 'update' : 'create',
                //     ]
                // );

                if (str_contains($errorMessage, 'userPrincipalName already exists')) {
                    Log::channel('provisioning')->warning('Azure UPN conflict detected, attempting to link', [
                        'user_id' => $user->id,
                        'email' => $user->email
                    ]);

                    $existing = $this->azureService->findUserByUPN($user->email);

                    if ($existing) {
                        $user->update([
                            'azure_id'          => $existing['id'],
                            'azure_upn'         => $existing['userPrincipalName'],
                            'azure_display_name' => $existing['displayName'],
                            'status'            => 'active',
                        ]);

                        $azureUserId = $existing['id'];
                        $results['azure'] = [
                            'success'  => true,
                            'azure_id' => $existing['id'],
                            'azure_upn' => $existing['userPrincipalName'],
                            'action' => 'linked'
                        ];

                        // 🎯 AUDIT: Log successful Azure link
                        // // AzureAuditLogger::log(
                        //     'azure.user.linked',
                        //     $user,
                        //     'success',
                        //     null,
                        //     [
                        //         'azure_id' => $existing['id'],
                        //         'azure_upn' => $existing['userPrincipalName'],
                        //         'reason' => 'UPN conflict resolved by linking to existing user',
                        //     ]
                        // );

                        Log::channel('provisioning')->info('Linked to existing Azure user', [
                            'user_id' => $user->id,
                            'azure_id' => $existing['id']
                        ]);
                    } else {
                        throw new Exception('Azure UPN exists but could not be found for linking');
                    }
                } else {
                    // Non-recoverable Azure error
                    if ($azureUserCreated && $azureUserId) {
                        $this->performAzureRollback($azureUserId, $user);
                        $results['rollback_performed'] = true;
                    }

                    throw $azureException;
                }
            }

            // Step 4: Assign modules
            if (!empty($moduleAssignments)) {
                try {
                    $moduleResults = $this->assignModules($user, $moduleAssignments);
                    $results['modules'] = $moduleResults;

                    $failedModules = array_filter($moduleResults, fn($m) => !$m['success']);

                    if (!empty($failedModules)) {
                        $results['partial_failure'] = true;
                        $results['errors'][] = count($failedModules) . " module assignment(s) failed";

                        // 🎯 AUDIT: Log partial failure
                        // // AzureAuditLogger::log(
                        //     'user.provisioning.partial_failure',
                        //     $user,
                        //     'failed',
                        //     'Some module assignments failed',
                        //     [
                        //         'failed_count' => count($failedModules),
                        //         'total_count' => count($moduleAssignments),
                        //         'successful_count' => count($moduleAssignments) - count($failedModules),
                        //         'failed_modules' => array_map(function($m) {
                        //             return [
                        //                 'module_id' => $m['module_id'] ?? null,
                        //                 'module_code' => $m['module_code'] ?? null,
                        //                 'errors' => $m['errors'] ?? [],
                        //             ];
                        //         }, $failedModules),
                        //     ]
                        // );

                        Log::channel('provisioning')->warning('Partial provisioning failure', [
                            'user_id' => $user->id,
                            'failed_modules' => count($failedModules),
                            'total_modules' => count($moduleAssignments)
                        ]);
                    }
                } catch (Exception $moduleException) {
                    Log::channel('provisioning')->error('Module assignment failed critically', [
                        'user_id' => $user->id,
                        'error' => $moduleException->getMessage()
                    ]);

                    if ($azureUserCreated && $azureUserId) {
                        $this->performAzureRollback($azureUserId, $user);
                        $results['rollback_performed'] = true;
                    }

                    throw $moduleException;
                }
            }

            DB::commit();
            $results['success'] = true;

            // 🎯 AUDIT: Log successful provisioning completion
            $successfulModules = count($moduleAssignments) - count(array_filter($results['modules'], fn($m) => !$m['success']));

            // // AzureAuditLogger::log(
            //     'user.provisioning.completed',
            //     $user,
            //     'success',
            //     null,
            //     [
            //         'azure_id' => $azureUserId,
            //         'azure_upn' => $user->azure_upn,
            //         'modules_assigned' => $successfulModules,
            //         'total_modules' => count($moduleAssignments),
            //         'partial_failure' => $results['partial_failure'],
            //         'azure_action' => $results['azure']['action'] ?? 'unknown',
            //     ]
            // );

            Log::channel('provisioning')->info('User provisioning completed successfully', [
                'user_id' => $user->id,
                'azure_id' => $azureUserId,
                'partial_failure' => $results['partial_failure'],
                'modules_assigned' => $successfulModules
            ]);

            return $results;
        } catch (Exception $e) {
            DB::rollBack();

            // 🎯 AUDIT: Log provisioning failure
            // // AzureAuditLogger::log(
            //     'user.provisioning.failed',
            //     $results['user'] ?? null,
            //     'failed',
            //     $e->getMessage(),
            //     [
            //         'azure_user_created' => $azureUserCreated,
            //         'azure_user_id' => $azureUserId,
            //         'rollback_performed' => $results['rollback_performed'],
            //         'employee_id' => $userData['employee_id'] ?? null,
            //         'email' => $userData['email'] ?? null,
            //         'modules_attempted' => count($moduleAssignments),
            //     ]
            // );

            Log::channel('provisioning')->error('User provisioning failed - transaction rolled back', [
                'user_id' => $results['user']->id ?? null,
                'azure_user_created' => $azureUserCreated,
                'azure_user_id' => $azureUserId,
                'rollback_performed' => $results['rollback_performed'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $results['errors'][] = $e->getMessage();
            $results['success'] = false;

            return $results;
        }
    }

    /**
     * Assign modules to user (calls AzureModuleAssignmentService for each module)
     * NOTE: This is called WITHIN the transaction started by provisionUser()
     * 
     * @param AzureUser $user
     * @param array $moduleAssignments Array of ['module_id' => X, 'role_id' => Y, 'location' => Z]
     * @return array Results for each module assignment
     */
    private function assignModules(AzureUser $user, array $moduleAssignments): array
    {
        $results = [];

        Log::channel('provisioning')->info('Starting module assignments', [
            'user_id' => $user->id,
            'module_count' => count($moduleAssignments)
        ]);

        foreach ($moduleAssignments as $index => $assignment) {
            $moduleId = $assignment['module_id'];
            $roleId = $assignment['role_id'];
            $location = $assignment['location'] ?? null;

            try {
                $module = AzureModule::findOrFail($moduleId);

                Log::channel('provisioning')->info('Assigning module to user', [
                    'user_id' => $user->id,
                    'module_id' => $moduleId,
                    'module_code' => $module->code,
                    'role_id' => $roleId,
                    'location' => $location
                ]);

                // Call AzureModuleAssignmentService (which throws exceptions on critical failures)
                $result = $this->moduleAssignmentService->assignUserToModule(
                    $user,
                    $module,
                    $roleId,
                    $location
                );

                $results[] = array_merge($result, [
                    'module_id' => $moduleId,
                    'module_code' => $module->code,
                    'role_id' => $roleId
                ]);

                if ($result['success']) {
                    Log::channel('provisioning')->info('Module assigned successfully', [
                        'user_id' => $user->id,
                        'module_id' => $moduleId,
                        'module_code' => $module->code
                    ]);
                } else {
                    Log::channel('provisioning')->warning('Module assignment failed', [
                        'user_id' => $user->id,
                        'module_id' => $moduleId,
                        'module_code' => $module->code,
                        'errors' => $result['errors'] ?? []
                    ]);
                }
            } catch (Exception $e) {
                // Critical failure - log and re-throw to trigger parent transaction rollback
                Log::channel('provisioning')->error('Module assignment exception', [
                    'user_id' => $user->id,
                    'module_id' => $moduleId,
                    'error' => $e->getMessage()
                ]);

                $results[] = [
                    'success' => false,
                    'module_id' => $moduleId,
                    'role_id' => $roleId,
                    'errors' => [$e->getMessage()]
                ];

                // Re-throw to trigger transaction rollback
                throw $e;
            }
        }

        return $results;
    }

    /**
     * Perform rollback: Delete Azure AD user if provisioning fails
     * 
     * @param string $azureUserId
     * @param AzureUser $user
     * @return bool
     */
    private function performAzureRollback(string $azureUserId, AzureUser $user): bool
    {
        Log::channel('provisioning')->warning('🔄 INITIATING AZURE ROLLBACK', [
            'azure_user_id' => $azureUserId,
            'local_user_id' => $user->id,
            'employee_id' => $user->employee_id,
            'reason' => 'Provisioning failed after Azure user creation'
        ]);

        try {
            $this->azureService->deleteUser($azureUserId);

            // 🎯 AUDIT: Log successful rollback
            // // AzureAuditLogger::azureRollback(
            //     $user,
            //     $azureUserId,
            //     'Provisioning failed after Azure user creation',
            //     [
            //         'rollback_successful' => true,
            //         'employee_id' => $user->employee_id,
            //     ]
            // );

            Log::channel('provisioning')->info('✅ Azure rollback successful - user deleted', [
                'azure_user_id' => $azureUserId,
                'local_user_id' => $user->id
            ]);

            return true;
        } catch (Exception $rollbackException) {
            // 🎯 AUDIT: Log failed rollback (CRITICAL)
            // // AzureAuditLogger::azureRollbackFailed(
            //     $user,
            //     $azureUserId,
            //     $rollbackException->getMessage(),
            //     [
            //         'action_required' => 'MANUAL CLEANUP REQUIRED',
            //         'azure_user_id' => $azureUserId,
            //         'employee_id' => $user->employee_id,
            //         'manual_action' => 'Delete Azure AD user: ' . $azureUserId,
            //     ]
            // );

            Log::channel('provisioning')->critical('❌ AZURE ROLLBACK FAILED - MANUAL CLEANUP REQUIRED', [
                'azure_user_id' => $azureUserId,
                'local_user_id' => $user->id,
                'employee_id' => $user->employee_id,
                'rollback_error' => $rollbackException->getMessage(),
                'action_required' => 'Manually delete Azure AD user: ' . $azureUserId
            ]);

            return false;
        }
    }

    /**
     * Create user record in local database
     * Status starts as 'pending' until Azure provisioning succeeds
     */
    private function createLocalUser(array $userData): AzureUser
    {
        $user = AzureUser::updateOrCreate(
            ['employee_id' => $userData['employee_id']],
            [
                'name'       => $userData['name'],
                'email'      => $userData['email'],
                'phone'      => $userData['phone'] ?? null,
                'location'   => $userData['location'] ?? null,
                'job_title'  => $userData['job_title'] ?? null,
                'department' => $userData['department'] ?? null,
                'company_id' => $userData['company_id'],
                'password'   => bcrypt(\Illuminate\Support\Str::random(32)),
                'status'     => 'pending',
            ]
        );

        // 🎯 AUDIT: Log local user creation (only if newly created)
        if ($user->wasRecentlyCreated) {
            // // AzureAuditLogger::userCreated($user, [
            //     'employee_id' => $user->employee_id,
            //     'company_id' => $user->company_id,
            //     'initial_status' => 'pending',
            // ]);
        }

        Log::channel('provisioning')->info('Local user record created/updated', [
            'user_id'     => $user->id,
            'employee_id' => $user->employee_id,
            'action'      => $user->wasRecentlyCreated ? 'created' : 'updated',
            'status'      => 'pending'
        ]);

        return $user;
    }

    /**
     * Get provisioning status for a user
     * 
     * @param AzureUser $user
     * @return array
     */
    public function getProvisioningStatus(AzureUser $user): array
    {
        $status = [
            'local_user' => [
                'exists' => true,
                'status' => $user->status,
                'created_at' => $user->created_at,
            ],
            'azure' => [
                'provisioned' => !empty($user->azure_id),
                'azure_id' => $user->azure_id,
                'azure_upn' => $user->azure_upn,
                'synced' => false,
            ],
            'modules' => [],
            'overall_status' => 'unknown',
        ];

        // Check Azure sync status
        if ($user->azure_id) {
            try {
                $azureUser = $this->azureService->getUser($user->azure_id);
                $status['azure']['synced'] = true;
                $status['azure']['account_enabled'] = $azureUser['accountEnabled'] ?? false;
            } catch (Exception $e) {
                $status['azure']['synced'] = false;
                $status['azure']['error'] = $e->getMessage();
                Log::warning('Failed to check Azure sync status', [
                    'user_id' => $user->id,
                    'azure_id' => $user->azure_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Check module assignments
        $moduleAssignments = $user->moduleAssignments()
            ->select('ApplicationName', 'application_id', 'RoleCode', 'RoleID', 'OfficeCode')
            ->get();

        foreach ($user->modules as $module) {
            $moduleStatus = [
                'module_id' => $module->id,
                'module_name' => $module->name,
                'module_code' => $module->code,
                'role_id' => $module->pivot->role_id,
                'location' => $module->pivot->location,
                'assigned_at' => $module->pivot->created_at,
                'azure_provisioned' => !empty($module->pivot->azure_group_id),
                'azure_group_id' => $module->pivot->azure_group_id,
                'provisioned_at' => $module->pivot->provisioned_at,
            ];

            $status['modules'][] = $moduleStatus;
        }

        // Determine overall status
        if ($user->status === 'active' && $user->azure_id && $status['azure']['synced']) {
            $status['overall_status'] = 'fully_provisioned';
        } elseif ($user->status === 'pending' || !$user->azure_id) {
            $status['overall_status'] = 'pending';
        } elseif ($user->status === 'inactive') {
            $status['overall_status'] = 'disabled';
        } else {
            $status['overall_status'] = 'partial';
        }

        return $status;
    }

    /**
     * Update user information (local + Azure)
     * 
     * @param AzureUser $user
     * @param array $updateData
     * @return array
     */
    public function updateUser(AzureUser $user, array $updateData): array
    {
        $results = [
            'success' => false,
            'local_updated' => false,
            'azure_updated' => false,
            'errors' => [],
        ];

        DB::beginTransaction();

        try {
            // Get old values for audit
            $oldValues = $user->only(array_keys($updateData));

            // Update local user
            $user->update($updateData);
            $results['local_updated'] = true;

            // 🎯 AUDIT: Log local user update
            // // AzureAuditLogger::userUpdated($user, $oldValues, $updateData, [
            //     'fields_changed' => array_keys(array_diff_assoc($updateData, $oldValues)),
            // ]);

            Log::info('Local user updated', [
                'user_id' => $user->id,
                'fields_updated' => array_keys($updateData)
            ]);

            // Update Azure if provisioned
            if ($user->azure_id) {
                try {
                    $this->azureService->updateUser($user->azure_id, [
                        'name' => $updateData['name'] ?? $user->name,
                        'email' => $updateData['email'] ?? $user->email,
                        'phone' => $updateData['phone'] ?? $user->phone,
                        'job_title' => $updateData['job_title'] ?? $user->job_title,
                        'department' => $updateData['department'] ?? $user->department,
                    ]);

                    $results['azure_updated'] = true;

                    // 🎯 AUDIT: Log Azure update
                    // // AzureAuditLogger::azureUserUpdated($user, $user->azure_id, [
                    //     'fields_updated' => array_keys($updateData),
                    // ]);

                    Log::info('Azure user updated', [
                        'user_id' => $user->id,
                        'azure_id' => $user->azure_id
                    ]);
                } catch (Exception $e) {
                    $results['errors'][] = 'Azure update failed: ' . $e->getMessage();

                    // 🎯 AUDIT: Log Azure update failure
                    // // AzureAuditLogger::log(
                    //     'azure.user.update.failed',
                    //     $user,
                    //     'failed',
                    //     $e->getMessage(),
                    //     [
                    //         'azure_id' => $user->azure_id,
                    //         'attempted_fields' => array_keys($updateData),
                    //     ]
                    // );

                    Log::warning('Azure update failed but local update succeeded', [
                        'user_id' => $user->id,
                        'azure_id' => $user->azure_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();
            $results['success'] = true;

            return $results;
        } catch (Exception $e) {
            DB::rollBack();

            // 🎯 AUDIT: Log update failure
            // // AzureAuditLogger::log(
            //     'user.update.failed',
            //     $user,
            //     'failed',
            //     $e->getMessage(),
            //     [
            //         'attempted_fields' => array_keys($updateData),
            //     ]
            // );

            Log::error('User update failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            $results['errors'][] = $e->getMessage();
            return $results;
        }
    }

    /**
     * Disable user (local + Azure)
     * 
     * @param AzureUser $user
     * @return array
     */
    public function disableUser(AzureUser $user): array
    {
        $results = [
            'success' => false,
            'local_disabled' => false,
            'azure_disabled' => false,
            'errors' => [],
        ];

        DB::beginTransaction();

        try {
            // Update local user status
            $user->update([
                'status' => 'inactive',
                'disabled_at' => now(),
            ]);
            $results['local_disabled'] = true;

            // 🎯 AUDIT: Log user disabled locally
            // // AzureAuditLogger::userDisabled($user, [
            //     'disabled_at' => now(),
            // ]);

            Log::info('Local user disabled', ['user_id' => $user->id]);

            // Disable in Azure if provisioned
            if ($user->azure_id) {
                try {
                    $this->azureService->disableUser($user->azure_id);
                    $results['azure_disabled'] = true;

                    // 🎯 AUDIT: Log Azure disable
                    // // AzureAuditLogger::log(
                    //     'azure.user.disabled',
                    //     $user,
                    //     'success',
                    //     null,
                    //     ['azure_id' => $user->azure_id]
                    // );

                    Log::info('Azure user disabled', [
                        'user_id' => $user->id,
                        'azure_id' => $user->azure_id
                    ]);
                } catch (Exception $e) {
                    $results['errors'][] = 'Azure disable failed: ' . $e->getMessage();

                    // 🎯 AUDIT: Log Azure disable failure
                    // // AzureAuditLogger::log(
                    //     'azure.user.disable.failed',
                    //     $user,
                    //     'failed',
                    //     $e->getMessage(),
                    //     ['azure_id' => $user->azure_id]
                    // );

                    Log::warning('Azure disable failed but local disable succeeded', [
                        'user_id' => $user->id,
                        'azure_id' => $user->azure_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();
            $results['success'] = true;

            return $results;
        } catch (Exception $e) {
            DB::rollBack();

            // 🎯 AUDIT: Log disable failure
            // // AzureAuditLogger::log(
            //     'user.disable.failed',
            //     $user,
            //     'failed',
            //     $e->getMessage()
            // );

            Log::error('User disable failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            $results['errors'][] = $e->getMessage();
            return $results;
        }
    }

    /**
     * Enable user (local + Azure)
     * 
     * @param AzureUser $user
     * @return array
     */
    public function enableUser(AzureUser $user): array
    {
        $results = [
            'success' => false,
            'local_enabled' => false,
            'azure_enabled' => false,
            'errors' => [],
        ];

        DB::beginTransaction();

        try {
            // Update local user status
            $user->update([
                'status' => 'active',
                'disabled_at' => null,
            ]);
            $results['local_enabled'] = true;

            // 🎯 AUDIT: Log user enabled locally
            // AzureAuditLogger::userEnabled($user);

            Log::info('Local user enabled', ['user_id' => $user->id]);

            // Enable in Azure if provisioned
            if ($user->azure_id) {
                try {
                    $this->azureService->enableUser($user->azure_id);
                    $results['azure_enabled'] = true;

                    // 🎯 AUDIT: Log Azure enable
                    // // AzureAuditLogger::log(
                    //     'azure.user.enabled',
                    //     $user,
                    //     'success',
                    //     null,
                    //     ['azure_id' => $user->azure_id]
                    // );

                    Log::info('Azure user enabled', [
                        'user_id' => $user->id,
                        'azure_id' => $user->azure_id
                    ]);
                } catch (Exception $e) {
                    $results['errors'][] = 'Azure enable failed: ' . $e->getMessage();

                    // 🎯 AUDIT: Log Azure enable failure
                    // // AzureAuditLogger::log(
                    //     'azure.user.enable.failed',
                    //     $user,
                    //     'failed',
                    //     $e->getMessage(),
                    //     ['azure_id' => $user->azure_id]
                    // );

                    Log::warning('Azure enable failed but local enable succeeded', [
                        'user_id' => $user->id,
                        'azure_id' => $user->azure_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();
            $results['success'] = true;

            return $results;
        } catch (Exception $e) {
            DB::rollBack();

            // 🎯 AUDIT: Log enable failure
            // // AzureAuditLogger::log(
            //     'user.enable.failed',
            //     $user,
            //     'failed',
            //     $e->getMessage()
            // );

            Log::error('User enable failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            $results['errors'][] = $e->getMessage();
            return $results;
        }
    }

    /**
     * Retry failed module assignments for a user
     * 
     * @param AzureUser $user
     * @return array
     */
    public function retryFailedModuleAssignments(AzureUser $user): array
    {
        $results = [];

        // Load user's modules with pivot data
        $moduleAssignments = $user->moduleAssignments()->get();

        Log::info('Retrying failed module assignments', [
            'user_id' => $user->id,
            'total_modules' => $user->modules->count()
        ]);

        foreach ($user->modules as $module) {
            $pivot = $module->pivot;

            // Only retry if Azure provisioning failed or is incomplete
            if (empty($pivot->azure_group_id) || $pivot->provision_status === 'failed') {
                try {
                    Log::info('Retrying module assignment', [
                        'user_id' => $user->id,
                        'module_id' => $module->id,
                        'module_code' => $module->code
                    ]);

                    $result = $this->moduleAssignmentService->assignUserToModule(
                        $user,
                        $module,
                        $pivot->role_id,
                        $pivot->location
                    );

                    $results[] = array_merge($result, [
                        'module_id' => $module->id,
                        'module_code' => $module->code,
                        'module_name' => $module->name,
                    ]);

                    // 🎯 AUDIT: Log retry result
                    if ($result['success']) {
                        // // AzureAuditLogger::log(
                        //     'module.assignment.retry.success',
                        //     $user,
                        //     'success',
                        //     null,
                        //     [
                        //         'module_id' => $module->id,
                        //         'module_code' => $module->code,
                        //         'role_id' => $pivot->role_id,
                        //     ]
                        // );
                    } else {
                        // // AzureAuditLogger::log(
                        //     'module.assignment.retry.failed',
                        //     $user,
                        //     'failed',
                        //     implode(', ', $result['errors'] ?? []),
                        //     [
                        //         'module_id' => $module->id,
                        //         'module_code' => $module->code,
                        //         'role_id' => $pivot->role_id,
                        //     ]
                        // );
                    }
                } catch (Exception $e) {
                    Log::error('Module assignment retry failed', [
                        'user_id' => $user->id,
                        'module_id' => $module->id,
                        'error' => $e->getMessage()
                    ]);

                    $results[] = [
                        'success' => false,
                        'module_id' => $module->id,
                        'module_code' => $module->code,
                        'module_name' => $module->name,
                        'errors' => [$e->getMessage()],
                    ];
                }
            }
        }

        return $results;
    }
}
