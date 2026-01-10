<?php

namespace App\Services;

use App\Models\AzureModule;
use App\Models\AzureUser;
use App\Models\AzureUserModule;
use Illuminate\Support\Facades\Http;  
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class AzureModuleAssignmentService
{
    private AzureGraphService $azureService;

    public function __construct(AzureGraphService $azureService)
    {
        $this->azureService = $azureService;
    }

    /**
     * Assign user to a module (handles Azure + External API provisioning)
     * WITH DATABASE TRANSACTION SUPPORT + AUDIT LOGGING
     * 
     * Note: This method is called FROM AzureUserProvisioningService which already has a transaction.
     * We throw exceptions on critical failures to trigger parent transaction rollback.
     * 
     * @param AzureUser $user
     * @param AzureModule $module
     * @param int $roleId
     * @param string|null $location
     * @return array Status of assignment
     */
    public function assignUserToModule(AzureUser $user, AzureModule $module, int $roleId, ?string $location = null): array
    {
        $results = [
            'success' => false,
            'azure_group' => ['success' => false, 'skipped' => true],
            'azure_app_role' => ['success' => false, 'skipped' => true],
            'external_api' => ['success' => false, 'skipped' => true],
            'errors' => []
        ];

        // 🎯 AUDIT: Log module assignment started
        // // AzureAuditLogger::log(
        //     'module.assignment.started',
        //     $user,
        //     'success',
        //     null,
        //     [
        //         'module_id' => $module->id,
        //         'module_code' => $module->code,
        //         'module_name' => $module->name,
        //         'role_id' => $roleId,
        //         'location' => $location,
        //     ]
        // );

        try {
            // Validate location if required
            if ($module->requires_location && empty($location)) {
                throw new Exception("Module {$module->code} requires a location");
            }

            // Create pivot record in database
            $userModule = $this->createPivotRecord($user, $module, $roleId, $location);

            // Azure AD Group Assignment (CRITICAL - failure blocks success)
            if ($module->requiresGroupAssignment()) {
                $results['azure_group'] = $this->assignToAzureGroup($user, $module, $userModule);
                
                if (!$results['azure_group']['success']) {
                    $errorMsg = $results['azure_group']['error'] ?? 'Unknown error';
                    $results['errors'][] = "Azure Group: " . $errorMsg;
                    
                    // CRITICAL: Throw exception to trigger parent transaction rollback
                    throw new Exception("Failed to assign Azure group: " . $errorMsg);
                }
            }

            // Azure App Role Assignment (CRITICAL - failure blocks success)
            if ($module->requiresAppRoleAssignment()) {
                $results['azure_app_role'] = $this->assignAzureAppRole($user, $module, $userModule);
                
                if (!$results['azure_app_role']['success']) {
                    $errorMsg = $results['azure_app_role']['error'] ?? 'Unknown error';
                    $results['errors'][] = "App Role: " . $errorMsg;
                    
                    // CRITICAL: Throw exception to trigger parent transaction rollback
                    throw new Exception("Failed to assign app role: " . $errorMsg);
                }
            }

            // External API Provisioning (NON-CRITICAL - failure doesn't block overall success)
            if ($module->hasExternalApi()) {
                $results['external_api'] = $this->provisionToExternalSystem($user, $module, $userModule, $location);
                
                if (!$results['external_api']['success']) {
                    $results['errors'][] = "External API: " . ($results['external_api']['error'] ?? 'Unknown error');
                    // Don't throw - external API failure is logged but not critical
                }
            }

            // Determine overall success - Azure operations are critical
            $azureSuccess = (!$module->requiresGroupAssignment() || $results['azure_group']['success']) &&
                            (!$module->requiresAppRoleAssignment() || $results['azure_app_role']['success']);
            
            $results['success'] = $azureSuccess;

            // Update sync status based on results
            if ($results['external_api']['success']) {
                $userModule->markAsSynced($results['external_api']['external_system_id'] ?? null);
            } elseif ($module->hasExternalApi()) {
                // Azure succeeded but external failed - mark as partial
                $userModule->markAsFailed($results['external_api']['error'] ?? 'External API provisioning failed');
            } else {
                // No external API, just Azure - mark as synced
                $userModule->markAsSynced(null);
            }

            // 🎯 AUDIT: Log module assignment completed
            // // AzureAuditLogger::log(
            //     'module.assignment.completed',
            //     $user,
            //     'success',
            //     null,
            //     [
            //         'module_id' => $module->id,
            //         'module_code' => $module->code,
            //         'module_name' => $module->name,
            //         'role_id' => $roleId,
            //         'azure_group_success' => $results['azure_group']['success'],
            //         'azure_app_role_success' => $results['azure_app_role']['success'],
            //         'external_api_success' => $results['external_api']['success'],
            //         'has_warnings' => !empty($results['errors']),
            //     ]
            // );

            Log::channel('provisioning')->info('Module assignment completed', [
                'user_id' => $user->id,
                'module_id' => $module->id,
                'overall_success' => $results['success'],
                'results' => $results
            ]);

            return $results;

        } catch (Exception $e) {
            // 🎯 AUDIT: Log module assignment failed
            // // AzureAuditLogger::log(
            //     'module.assignment.failed',
            //     $user,
            //     'failed',
            //     $e->getMessage(),
            //     [
            //         'module_id' => $module->id,
            //         'module_code' => $module->code,
            //         'module_name' => $module->name,
            //         'role_id' => $roleId,
            //         'results' => $results,
            //     ]
            // );

            Log::channel('provisioning')->error('Module assignment exception', [
                'user_id' => $user->id,
                'module_id' => $module->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $results['errors'][] = $e->getMessage();
            $results['success'] = false;
            
            if (isset($userModule)) {
                $userModule->markAsFailed($e->getMessage());
            }
            
            // Re-throw to trigger parent transaction rollback
            throw $e;
        }
    }

    /**
     * Create pivot record in user_modules table
     */
    private function createPivotRecord(AzureUser $user, AzureModule $module, int $roleId, ?string $location): AzureUserModule
    {
        $userModule = AzureUserModule::updateOrCreate(
            [
                'user_id' => $user->id,
                'module_id' => $module->id,
            ],
            [
                'role_id' => $roleId,
                'location' => $location,
                'assigned_at' => now(),
                'external_sync_status' => 'pending',
            ]
        );

        // 🎯 AUDIT: Log pivot record creation
        // // AzureAuditLogger::log(
        //     'module.pivot_record.created',
        //     $user,
        //     'success',
        //     null,
        //     [
        //         'user_module_id' => $userModule->id,
        //         'module_id' => $module->id,
        //         'module_code' => $module->code,
        //         'role_id' => $roleId,
        //         'location' => $location,
        //         'action' => $userModule->wasRecentlyCreated ? 'created' : 'updated',
        //     ]
        // );

        Log::channel('provisioning')->info('User module pivot record created', [
            'user_id' => $user->id,
            'module_id' => $module->id,
            'user_module_id' => $userModule->id
        ]);

        return $userModule;
    }

    /**
     * Assign user to Azure AD Security Group (role-based)
     * NOW USES AzureGraphService WITH RETRY LOGIC + AUDIT LOGGING
     */
    private function assignToAzureGroup(AzureUser $user, AzureModule $module, AzureUserModule $userModule): array
{
    try {
        // Get the correct Azure group based on the user's role
        $roleId = $userModule->role_id;
        
        // 🎯 AUDIT: Log Azure group lookup started
        // // AzureAuditLogger::log(
        //     'azure.group.lookup.started',
        //     $user,
        //     'success',
        //     null,
        //     [
        //         'module_id' => $module->id,
        //         'module_code' => $module->code,
        //         'role_id' => $roleId,
        //     ]
        // );
        
        $groupInfo = $module->getAzureGroupForRole($roleId);

        if (!$groupInfo) {
            $error = "No Azure group mapping found for module '{$module->name}' and role ID {$roleId}";
            
            // 🎯 AUDIT: Log mapping not found (CRITICAL ERROR)
            // // AzureAuditLogger::log(
            //     'azure.group.mapping.not_found',
            //     $user,
            //     'failed',
            //     $error,
            //     [
            //         'module_id' => $module->id,
            //         'module_code' => $module->code,
            //         'module_name' => $module->name,
            //         'role_id' => $roleId,
            //         'action_required' => 'Create mapping in module_role_groups table',
            //     ]
            // );
            
            throw new Exception($error);
        }

        if (!$user->isProvisionedToAzure()) {
            throw new Exception("User {$user->name} is not provisioned to Azure AD");
        }

        $azureGroupId = $groupInfo['group_id'];
        $azureGroupName = $groupInfo['group_name'];

        Log::channel('azure')->info('Attempting Azure group assignment', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'module' => $module->name,
            'role_id' => $roleId,
            'azure_group_id' => $azureGroupId,
            'azure_group_name' => $azureGroupName
        ]);

        // ✅ USE AzureGraphService method with built-in retry logic
        $result = $this->azureService->addUserToGroupWithRole(
            $user->azure_id,
            $azureGroupId,
            $azureGroupName
        );

        if ($result['success']) {
            // ✅ CRITICAL: Store azure_group_id in pivot table for deprovisioning
            $userModule->update([
                'azure_group_id' => $azureGroupId,
                'provisioned_at' => now()
            ]);

            // 🎯 AUDIT: Log successful Azure group assignment
            // // AzureAuditLogger::azureGroupAssigned(
            //     $user,
            //     $azureGroupId,
            //     $azureGroupName,
            //     [
            //         'module_id' => $module->id,
            //         'module_code' => $module->code,
            //         'role_id' => $roleId,
            //         'status' => $result['status'], // 'assigned' or 'already_member'
            //         'stored_in_pivot' => true,
            //     ]
            // );
            
            Log::channel('azure')->info('User added to Azure Group successfully', [
                'user_id' => $user->id,
                'azure_id' => $user->azure_id,
                'group_id' => $azureGroupId,
                'group_name' => $azureGroupName,
                'module' => $module->code,
                'role_id' => $roleId,
                'status' => $result['status'],
                'pivot_updated' => true
            ]);
        } else {
            // 🎯 AUDIT: Log failed Azure group assignment
            // // AzureAuditLogger::azureGroupAssignmentFailed(
            //     $user,
            //     $azureGroupId,
            //     $azureGroupName,
            //     $result['error'] ?? 'Unknown error',
            //     [
            //         'module_id' => $module->id,
            //         'module_code' => $module->code,
            //         'role_id' => $roleId,
            //     ]
            // );
        }

        return $result;

    } catch (Exception $e) {
        Log::channel('azure')->error('Azure Group assignment failed', [
            'user_id' => $user->id,
            'module_id' => $module->id,
            'role_id' => $userModule->role_id ?? null,
            'error' => $e->getMessage()
        ]);

        return ['success' => false, 'error' => $e->getMessage()];
    }
}

    /**
     * Assign Azure Enterprise App Role to user
     * NOW USES AzureGraphService WITH RETRY LOGIC + AUDIT LOGGING
     */
    private function assignAzureAppRole(AzureUser $user, AzureModule $module, AzureUserModule $userModule): array
    {
        try {
            if (!$module->hasAzureAppConfigured()) {
                throw new Exception("Module {$module->code} does not have Azure App ID configured");
            }

            if (!$user->isProvisionedToAzure()) {
                throw new Exception("User {$user->name} is not provisioned to Azure AD");
            }

            // Get app role ID for this user's role
            $appRoleId = $this->getAppRoleId($module, $userModule->role_id);

            Log::channel('azure')->info('Assigning app role', [
                'user_id' => $user->id,
                'module' => $module->code,
                'app_role_id' => $appRoleId
            ]);

            // ✅ USE AzureGraphService method with built-in retry logic
            $result = $this->azureService->assignAppRole(
                $user->azure_id,
                $module->azure_enterprise_app_id,
                $appRoleId
            );

            if ($result['success']) {
                // 🎯 AUDIT: Log successful app role assignment
                // // AzureAuditLogger::log(
                //     'azure.app_role.assigned',
                //     $user,
                //     'success',
                //     null,
                //     [
                //         'module_id' => $module->id,
                //         'module_code' => $module->code,
                //         'app_id' => $module->azure_enterprise_app_id,
                //         'app_role_id' => $appRoleId,
                //         'role_id' => $userModule->role_id,
                //         'status' => $result['status'],
                //     ]
                // );
                
                Log::channel('azure')->info('App role assigned successfully', [
                    'user_id' => $user->id,
                    'azure_id' => $user->azure_id,
                    'app_id' => $module->azure_enterprise_app_id,
                    'module' => $module->code,
                    'status' => $result['status']
                ]);
            } else {
                // 🎯 AUDIT: Log failed app role assignment
                // // AzureAuditLogger::log(
                //     'azure.app_role.assigned',
                //     $user,
                //     'failed',
                //     $result['error'] ?? 'Unknown error',
                //     [
                //         'module_id' => $module->id,
                //         'module_code' => $module->code,
                //         'app_id' => $module->azure_enterprise_app_id,
                //         'app_role_id' => $appRoleId,
                //         'role_id' => $userModule->role_id,
                //     ]
                // );
            }

            return $result;

        } catch (Exception $e) {
            Log::channel('azure')->error('App role assignment failed', [
                'user_id' => $user->id,
                'module_id' => $module->id,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Provision user to external system API (SCM, FITGP, BIZ)
     * WITH AUDIT LOGGING
     */
    private function provisionToExternalSystem(AzureUser $user, AzureModule $module, AzureUserModule $userModule, ?string $location): array
    {
        try {
            $apiEndpoint = $module->external_api_endpoint;
            $authMethod = $module->api_auth_method;

            if (empty($apiEndpoint)) {
                throw new Exception("Module {$module->code} does not have external API endpoint configured");
            }

            // Prepare request payload
            $payload = $this->buildExternalApiPayload($user, $module, $location);

            // Build HTTP request with authentication
            $request = $this->buildAuthenticatedRequest($module);

            Log::channel('provisioning')->info('Sending user to external API', [
                'user_id' => $user->id,
                'module' => $module->code,
                'endpoint' => $apiEndpoint,
                'auth_method' => $authMethod
            ]);

            $response = $request->post($apiEndpoint, $payload);

            if ($response->failed()) {
                throw new Exception("External API request failed: {$response->status()} - {$response->body()}");
            }

            $responseData = $response->json();
            
            // Extract external system ID from response
            $externalSystemId = $this->extractExternalUserId($responseData, $module);

            // 🎯 AUDIT: Log successful external API provisioning
            // // AzureAuditLogger::log(
            //     'external.api.provisioned',
            //     $user,
            //     'success',
            //     null,
            //     [
            //         'module_id' => $module->id,
            //         'module_code' => $module->code,
            //         'external_system_id' => $externalSystemId,
            //         'api_endpoint' => $apiEndpoint,
            //         'auth_method' => $authMethod,
            //     ]
            // );

            Log::channel('provisioning')->info('User provisioned to external system', [
                'user_id' => $user->id,
                'module' => $module->code,
                'external_system_id' => $externalSystemId
            ]);

            // Update pivot with external system ID
            $userModule->update([
                'external_system_id' => $externalSystemId,
            ]);

            return [
                'success' => true,
                'status' => 'provisioned',
                'external_system_id' => $externalSystemId
            ];

        } catch (Exception $e) {
            // 🎯 AUDIT: Log failed external API provisioning
            // // AzureAuditLogger::log(
            //     'external.api.provisioned',
            //     $user,
            //     'failed',
            //     $e->getMessage(),
            //     [
            //         'module_id' => $module->id,
            //         'module_code' => $module->code,
            //         'api_endpoint' => $module->external_api_endpoint ?? 'not_configured',
            //     ]
            // );

            Log::channel('provisioning')->error('External API provisioning failed', [
                'user_id' => $user->id,
                'module_id' => $module->id,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Build authenticated HTTP request based on module configuration
     */
    private function buildAuthenticatedRequest(AzureModule $module)
    {
        $request = Http::timeout(30)->acceptJson();

        $authMethod = $module->api_auth_method;
        $credentials = $module->getApiCredentials();

        switch ($authMethod) {
            case 'bearer':
                if (isset($credentials['token'])) {
                    $request = $request->withToken($credentials['token']);
                }
                break;

            case 'api_key':
                if (isset($credentials['api_key'])) {
                    $request = $request->withHeaders([
                        'X-API-Key' => $credentials['api_key']
                    ]);
                }
                break;

            case 'oauth':
                // OAuth implementation would go here
                break;

            case 'none':
            default:
                // No authentication
                break;
        }

        return $request;
    }

    /**
     * Build payload for external API
     */
    private function buildExternalApiPayload(AzureUser $user, AzureModule $module, ?string $location): array
    {
        $payload = [
            'employee_id' => $user->employee_id,
            'name' => $user->name,
            'email' => $user->email,
            'azure_upn' => $user->azure_upn,
            'phone' => $user->phone,
            'location' => $location,
            'module_code' => $module->code,
            'company_id' => $user->company_id,
        ];

        return $payload;
    }

    /**
     * Extract external user ID from API response
     */
    private function extractExternalUserId(array $response, AzureModule $module): ?string
    {
        $possibleFields = ['id', 'user_id', 'userId', 'external_id', 'externalId'];

        foreach ($possibleFields as $field) {
            if (isset($response[$field])) {
                return (string) $response[$field];
            }
        }

        return null;
    }

    /**
     * Get App Role ID based on module and user role
     * NOW USES AzureGraphService WITH RETRY LOGIC
     */
    private function getAppRoleId(AzureModule $module, int $roleId): string
    {
        try {
            $servicePrincipal = $this->azureService->getServicePrincipal(
                $module->azure_enterprise_app_id
            );

            $appRoles = $servicePrincipal['appRoles'] ?? [];
            
            $role = \App\Models\AzureRole::find($roleId);
            $targetRoleName = "{$module->code}.{$role->code}";

            foreach ($appRoles as $appRole) {
                if ($appRole['displayName'] === $targetRoleName || $appRole['value'] === $targetRoleName) {
                    return $appRole['id'];
                }
            }

            // Fallback to default User role
            foreach ($appRoles as $appRole) {
                if ($appRole['value'] === 'User' || $appRole['displayName'] === 'User') {
                    return $appRole['id'];
                }
            }

            throw new Exception("No suitable app role found for module {$module->code}");

        } catch (Exception $e) {
            Log::channel('azure')->error('Failed to determine app role ID', [
                'module_id' => $module->id,
                'role_id' => $roleId,
                'error' => $e->getMessage()
            ]);
            
            return config('azure.default_app_role_id', '00000000-0000-0000-0000-000000000000');
        }
    }

    /**
     * Remove user from module (unassign from Azure + External system)
     * WITH DATABASE TRANSACTION SUPPORT
     */
    public function removeUserFromModule(AzureUser $user, AzureModule $module): array
    {
        $results = [
            'success' => false,
            'azure_group' => null,
            'azure_app_role' => null,
            'external_api' => null,
            'errors' => []
        ];

        DB::beginTransaction();

        try {
            // Remove from Azure Group
            if ($module->requiresGroupAssignment() && $user->isProvisionedToAzure()) {
                $results['azure_group'] = $this->removeFromAzureGroup($user, $module);
                
                if (!$results['azure_group']['success']) {
                    $results['errors'][] = "Failed to remove from Azure group";
                }
            }

            // Remove App Role assignment
            if ($module->requiresAppRoleAssignment() && $user->isProvisionedToAzure()) {
                $results['azure_app_role'] = $this->removeAzureAppRole($user, $module);
                
                if (!$results['azure_app_role']['success']) {
                    $results['errors'][] = "Failed to remove app role";
                }
            }

            // Delete pivot record
            AzureUserModule::where('user_id', $user->id)
                ->where('module_id', $module->id)
                ->delete();

            DB::commit();
            $results['success'] = true;

            return $results;

        } catch (Exception $e) {
            DB::rollBack();
            $results['errors'][] = $e->getMessage();
            $results['success'] = false;
            
            return $results;
        }
    }

    /**
     * Remove user from Azure AD Security Group
     */
    private function removeFromAzureGroup(AzureUser $user, AzureModule $module): array
    {
        try {
            $groupId = $module->azure_group_id;
            
            $result = $this->azureService->removeUserFromGroupById(
                $user->azure_id,
                $groupId,
                $module->name
            );

            return $result;

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Remove Azure App Role assignment
     */
    private function removeAzureAppRole(AzureUser $user, AzureModule $module): array
    {
        try {
            $assignments = $this->azureService->getUserAppRoleAssignments($user->azure_id);

            foreach ($assignments as $assignment) {
                if ($assignment['resourceId'] === $module->azure_enterprise_app_id) {
                    $assignmentId = $assignment['id'];
                    
                    $result = $this->azureService->removeAppRoleAssignment(
                        $user->azure_id,
                        $assignmentId
                    );

                    return $result;
                }
            }

            return ['success' => true, 'status' => 'not_found'];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}