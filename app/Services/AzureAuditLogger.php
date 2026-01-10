<?php

namespace App\Services;

use App\Models\AzureAuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Enhanced Audit Logging Service
 * 
 * Tracks WHO did WHAT, WHEN, WHERE, and the RESULT
 * Integrates with existing audit_logs table structure
 */
class AzureAuditLogger
{
    /**
     * Log an audit event
     * 
     * @param string $action The action performed (e.g., 'user.created', 'azure.user.created')
     * @param mixed $subject The entity affected (User, Module, etc.)
     * @param string $status 'success' or 'failed'
     * @param string|null $errorMessage Error details if failed
     * @param array $metadata Additional context data
     * @param array $oldValues Before state (for updates)
     * @param array $newValues After state (for updates)
     * @return AzureAuditLog
     */
    public static function log(
        string $action,
        $subject = null,
        string $status = 'success',
        ?string $errorMessage = null,
        array $metadata = [],
        array $oldValues = [],
        array $newValues = []
    ): AzureAuditLog {
        
        // Get current authenticated user (who performed the action)
        $performedBy = Auth::id();
        
        // Get request info (where it came from)
        $ipAddress = Request::ip();
        $userAgent = Request::userAgent();
        $url = Request::fullUrl();
        $method = Request::method();
        
        // Subject details (what was affected)
        $auditableType = $subject ? get_class($subject) : null;
        $auditableId = $subject && method_exists($subject, 'getKey') ? $subject->getKey() : null;
        
        // If subject is a User, store their ID
        $userId = null;
        if ($subject && get_class($subject) === 'App\Models\AzureUser') {
            $userId = $subject->id;
        }
        
        // Create audit log
        return AzureAuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'old_values' => !empty($oldValues) ? $oldValues : null,
            'new_values' => !empty($newValues) ? $newValues : null,
            'performed_by' => $performedBy,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'url' => $url,
            'method' => $method,
            'status' => $status,
            'error_message' => $errorMessage,
            'metadata' => !empty($metadata) ? $metadata : null,
        ]);
    }

    /**
     * Log successful user creation
     */
    public static function userCreated($user, array $metadata = []): AzureAuditLog
    {
        return self::log(
            'user.created',
            $user,
            'success',
            null,
            $metadata
        );
    }

    /**
     * Log failed user creation
     */
    public static function userCreationFailed(array $userData, string $error, array $metadata = []): AzureAuditLog
    {
        return self::log(
            'user.created',
            null,
            'failed',
            $error,
            array_merge(['attempted_user' => $userData], $metadata)
        );
    }

    /**
     * Log user update
     */
    public static function userUpdated($user, array $oldValues, array $newValues, array $metadata = []): AzureAuditLog
    {
        return self::log(
            'user.updated',
            $user,
            'success',
            null,
            $metadata,
            $oldValues,
            $newValues
        );
    }

    /**
     * Log user disabled
     */
    public static function userDisabled($user, array $metadata = []): AzureAuditLog
    {
        return self::log(
            'user.disabled',
            $user,
            'success',
            null,
            $metadata
        );
    }

    /**
     * Log user enabled
     */
    public static function userEnabled($user, array $metadata = []): AzureAuditLog
    {
        return self::log(
            'user.enabled',
            $user,
            'success',
            null,
            $metadata
        );
    }

    /**
     * Log Azure user creation
     */
    public static function azureUserCreated($user, string $azureId, string $azureUpn, array $metadata = []): AzureAuditLog
    {
        return self::log(
            'azure.user.created',
            $user,
            'success',
            null,
            array_merge([
                'azure_id' => $azureId,
                'azure_upn' => $azureUpn,
            ], $metadata)
        );
    }

    /**
     * Log Azure user creation failure
     */
    public static function azureUserCreationFailed($user, string $error, array $metadata = []): AzureAuditLog
    {
        return self::log(
            'azure.user.created',
            $user,
            'failed',
            $error,
            $metadata
        );
    }

    /**
     * Log Azure user update
     */
    public static function azureUserUpdated($user, string $azureId, array $metadata = []): AzureAuditLog
    {
        return self::log(
            'azure.user.updated',
            $user,
            'success',
            null,
            array_merge(['azure_id' => $azureId], $metadata)
        );
    }

    /**
     * Log Azure rollback
     */
    public static function azureRollback($user, string $azureId, string $reason, array $metadata = []): AzureAuditLog
    {
        return self::log(
            'azure.user.rollback',
            $user,
            'success',
            null,
            array_merge([
                'azure_id' => $azureId,
                'reason' => $reason,
            ], $metadata)
        );
    }

    /**
     * Log Azure rollback failure
     */
    public static function azureRollbackFailed($user, string $azureId, string $error, array $metadata = []): AzureAuditLog
    {
        return self::log(
            'azure.user.rollback',
            $user,
            'failed',
            $error,
            array_merge(['azure_id' => $azureId], $metadata)
        );
    }

    /**
     * Log module assignment
     */
    public static function moduleAssigned($user, $module, $role, array $metadata = []): AzureAuditLog
    {
        return self::log(
            'module.assigned',
            $user,
            'success',
            null,
            array_merge([
                'module_id' => $module->id,
                'module_name' => $module->name,
                'module_code' => $module->code,
                'role_id' => $role->id,
                'role_name' => $role->name,
            ], $metadata)
        );
    }

    /**
     * Log module assignment failure
     */
    public static function moduleAssignmentFailed($user, $module, $role, string $error, array $metadata = []): AzureAuditLog
    {
        return self::log(
            'module.assigned',
            $user,
            'failed',
            $error,
            array_merge([
                'module_id' => $module->id,
                'module_name' => $module->name,
                'role_id' => $role->id,
                'role_name' => $role->name,
            ], $metadata)
        );
    }

    /**
     * Log Azure group assignment
     */
    public static function azureGroupAssigned($user, string $groupId, string $groupName, array $metadata = []): AzureAuditLog
    {
        return self::log(
            'azure.group.assigned',
            $user,
            'success',
            null,
            array_merge([
                'azure_group_id' => $groupId,
                'azure_group_name' => $groupName,
            ], $metadata)
        );
    }

    /**
     * Log Azure group assignment failure
     */
    public static function azureGroupAssignmentFailed($user, string $groupId, string $groupName, string $error, array $metadata = []): AzureAuditLog
    {
        return self::log(
            'azure.group.assigned',
            $user,
            'failed',
            $error,
            array_merge([
                'azure_group_id' => $groupId,
                'azure_group_name' => $groupName,
            ], $metadata)
        );
    }

    /**
     * Log Azure group removal
     */
    public static function azureGroupRemoved($user, string $groupId, string $groupName, array $metadata = []): AzureAuditLog
    {
        return self::log(
            'azure.group.removed',
            $user,
            'success',
            null,
            array_merge([
                'azure_group_id' => $groupId,
                'azure_group_name' => $groupName,
            ], $metadata)
        );
    }

    /**
     * Log system error
     */
    public static function systemError(string $error, array $metadata = []): AzureAuditLog
    {
        return self::log(
            'system.error',
            null,
            'failed',
            $error,
            $metadata
        );
    }

    /**
     * Get audit logs for a specific user
     */
    public static function getLogsForUser(int $userId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return AzureAuditLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent audit logs
     */
    public static function getRecentLogs(int $limit = 100): \Illuminate\Database\Eloquent\Collection
    {
        return AzureAuditLog::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get failed operations
     */
    public static function getFailedOperations(int $hours = 24, int $limit = 100): \Illuminate\Database\Eloquent\Collection
    {
        return AzureAuditLog::where('status', 'failed')
            ->where('created_at', '>=', now()->subHours($hours))
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit statistics
     */
    public static function getStats(int $days = 7): array
    {
        $since = now()->subDays($days);
        
        return [
            'total_actions' => AzureAuditLog::where('created_at', '>=', $since)->count(),
            'successful_actions' => AzureAuditLog::where('created_at', '>=', $since)
                ->where('status', 'success')->count(),
            'failed_actions' => AzureAuditLog::where('created_at', '>=', $since)
                ->where('status', 'failed')->count(),
            'users_created' => AzureAuditLog::where('created_at', '>=', $since)
                ->where('action', 'user.created')
                ->where('status', 'success')->count(),
            'azure_operations' => AzureAuditLog::where('created_at', '>=', $since)
                ->where('action', 'like', 'azure.%')->count(),
            'rollbacks' => AzureAuditLog::where('created_at', '>=', $since)
                ->where('action', 'azure.user.rollback')->count(),
        ];
    }

    /**
     * Log user deactivation started
     */
    public static function logUserDeactivationStarted(int $userId, ?string $reason = null, ?int $performedBy = null): AzureAuditLog
    {
        $user = \App\Models\AzureUser::find($userId);
        
        return self::log(
            'user.deactivation.started',
            $user,
            'success',
            null,
            [
                'reason' => $reason,
                'performed_by' => $performedBy
            ]
        );
    }

    /**
     * Log user deactivation completed
     */
    public static function logUserDeactivationCompleted(int $userId, array $details = [], ?int $performedBy = null): AzureAuditLog
    {
        $user = \App\Models\AzureUser::find($userId);
        
        return self::log(
            'user.deactivation.completed',
            $user,
            'success',
            null,
            array_merge(['performed_by' => $performedBy], $details)
        );
    }

    /**
     * Log user deactivation failed
     */
    public static function logUserDeactivationFailed(int $userId, string $error, ?int $performedBy = null): AzureAuditLog
    {
        $user = \App\Models\AzureUser::find($userId);
        
        return self::log(
            'user.deactivation.failed',
            $user,
            'failed',
            $error,
            ['performed_by' => $performedBy]
        );
    }

    /**
     * Log user reactivation started
     */
    public static function logUserReactivationStarted(int $userId, ?string $reason = null, ?int $performedBy = null): AzureAuditLog
    {
        $user = \App\Models\AzureUser::find($userId);
        
        return self::log(
            'user.reactivation.started',
            $user,
            'success',
            null,
            [
                'reason' => $reason,
                'performed_by' => $performedBy
            ]
        );
    }

    /**
     * Log user reactivation completed
     */
    public static function logUserReactivationCompleted(int $userId, array $details = [], ?int $performedBy = null): AzureAuditLog
    {
        $user = \App\Models\AzureUser::find($userId);
        
        return self::log(
            'user.reactivation.completed',
            $user,
            'success',
            null,
            array_merge(['performed_by' => $performedBy], $details)
        );
    }

    /**
     * Log user reactivation failed
     */
    public static function logUserReactivationFailed(int $userId, string $error, ?int $performedBy = null): AzureAuditLog
    {
        $user = \App\Models\AzureUser::find($userId);
        
        return self::log(
            'user.reactivation.failed',
            $user,
            'failed',
            $error,
            ['performed_by' => $performedBy]
        );
    }

    /**
     * Log Azure group removal
     */
    public static function logAzureGroupRemoval(int $userId, string $groupId, string $groupName, ?int $performedBy = null): AzureAuditLog
    {
        $user = \App\Models\AzureUser::find($userId);
        
        return self::log(
            'azure.group.removal',
            $user,
            'success',
            null,
            [
                'azure_group_id' => $groupId,
                'azure_group_name' => $groupName,
                'performed_by' => $performedBy
            ]
        );
    }

    /**
     * Log Azure user disabled
     */
    public static function logAzureUserDisabled(int $userId, string $azureId, ?int $performedBy = null): AzureAuditLog
    {
        $user = \App\Models\AzureUser::find($userId);
        
        return self::log(
            'azure.user.disabled',
            $user,
            'success',
            null,
            [
                'azure_id' => $azureId,
                'performed_by' => $performedBy
            ]
        );
    }

    /**
     * Log Azure user enabled
     */
    public static function logAzureUserEnabled(int $userId, string $azureId, ?int $performedBy = null): AzureAuditLog
    {
        $user = \App\Models\AzureUser::find($userId);
        
        return self::log(
            'azure.user.enabled',
            $user,
            'success',
            null,
            [
                'azure_id' => $azureId,
                'performed_by' => $performedBy
            ]
        );
    }

    /**
     * Log Azure group assignment (for reactivation)
     */
    public static function logAzureGroupAssignment(int $userId, string $groupId, string $groupName, ?int $performedBy = null): AzureAuditLog
    {
        $user = \App\Models\AzureUser::find($userId);
        
        return self::log(
            'azure.group.assignment',
            $user,
            'success',
            null,
            [
                'azure_group_id' => $groupId,
                'azure_group_name' => $groupName,
                'performed_by' => $performedBy
            ]
        );
    }
}