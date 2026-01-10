<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class AzureModuleRole extends Model
{
    use HasFactory;

    /**
     * The database connection to use (SQL Server)
     */
    protected $connection = 'sqlsrv';

    /**
     * The table associated with the model.
     */
    protected $table = 'Azure_Users_Role';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'Id';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'Id',
        'ApplicationCode',
        'ApplicationId',
        'RoleId',
        'RoleCode',
        'Role',
        'IsActive',
        'IsDeleted',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'IsActive' => 'boolean',
        'IsDeleted' => 'boolean',
    ];

    // =================================================================
    // ATTRIBUTE ACCESSORS - Map SQL Server columns to Laravel conventions
    // =================================================================

    /**
     * Accessor: module_id -> ApplicationId
     */
    public function getModuleIdAttribute()
    {
        return $this->attributes['ApplicationId'] ?? null;
    }

    /**
     * Accessor: role_id -> RoleId
     */
    public function getRoleIdAttribute()
    {
        return $this->attributes['RoleId'] ?? null;
    }

    /**
     * Accessor: role_code -> RoleCode
     */
    public function getRoleCodeAttribute()
    {
        return $this->attributes['RoleCode'] ?? null;
    }

    /**
     * Accessor: role_name -> Role
     */
    public function getRoleNameAttribute()
    {
        return $this->attributes['Role'] ?? null;
    }

    /**
     * Accessor: application_code -> ApplicationCode
     */
    public function getApplicationCodeAttribute()
    {
        return $this->attributes['ApplicationCode'] ?? null;
    }

    /**
     * Accessor: is_active -> IsActive AND !IsDeleted
     */
    public function getIsActiveAttribute()
    {
        return ($this->attributes['IsActive'] ?? false) && !($this->attributes['IsDeleted'] ?? false);
    }

    // =================================================================
    // RELATIONSHIPS
    // =================================================================

    /**
     * Get the module/application for this role
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(AzureModule::class, 'ApplicationId', 'Id');
    }

    /**
     * Get the application details
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(AzureModule::class, 'ApplicationId', 'Id');
    }

    // Note: Removed relationship to AzureRole since that's a different Laravel table
    // The RoleId here is just a string/integer identifier, not a foreign key

    // =================================================================
    // QUERY SCOPES
    // =================================================================

    /**
     * Scope to get only active roles
     */
    public function scopeActive($query)
    {
        return $query->where('IsActive', true)
                    ->where('IsDeleted', false);
    }

    /**
     * Scope to filter by application code
     */
    public function scopeForApplication($query, string $applicationCode)
    {
        return $query->where('ApplicationCode', $applicationCode);
    }

    /**
     * Scope to filter by module/application ID
     */
    public function scopeForModule($query, string $moduleId)
    {
        return $query->where('ApplicationId', $moduleId);
    }

    /**
     * Scope to find by role ID
     */
    public function scopeByRoleId($query, $roleId)
    {
        return $query->where('RoleId', $roleId);
    }

    /**
     * Scope to find by role code
     */
    public function scopeByRoleCode($query, string $roleCode)
    {
        return $query->where('RoleCode', $roleCode);
    }

    // =================================================================
    // STATIC QUERY HELPERS
    // =================================================================

    /**
     * Get all roles for a specific application
     */
    public static function getRolesForApplication(string $applicationCode): \Illuminate\Support\Collection
    {
        return static::active()
            ->forApplication($applicationCode)
            ->orderBy('Role')
            ->get();
    }

    /**
     * Get all roles for a specific module ID
     */
    public static function getRolesForModule(string $moduleId): \Illuminate\Support\Collection
    {
        return static::active()
            ->forModule($moduleId)
            ->orderBy('Role')
            ->get();
    }

    /**
     * Find a specific role by application and role ID
     */
    public static function findRole(string $applicationCode, $roleId): ?self
    {
        return static::active()
            ->forApplication($applicationCode)
            ->byRoleId($roleId)
            ->first();
    }

    /**
     * Find a specific role by application and role code
     */
    public static function findRoleByCode(string $applicationCode, string $roleCode): ?self
    {
        return static::active()
            ->forApplication($applicationCode)
            ->byRoleCode($roleCode)
            ->first();
    }

    /**
     * Check if a role exists for an application
     */
    public static function roleExists(string $applicationCode, $roleId): bool
    {
        return static::active()
            ->forApplication($applicationCode)
            ->byRoleId($roleId)
            ->exists();
    }

    // =================================================================
    // INSTANCE HELPER METHODS
    // =================================================================

    /**
     * Check if this role is for OPS/OPS UAT (which uses Azure Group IDs)
     */
    public function isOpsRole(): bool
    {
        return in_array($this->ApplicationCode, ['OPS', 'OPS UAT']);
    }

    /**
     * Check if this role is for a specific application
     */
    public function belongsToApplication(string $applicationCode): bool
    {
        return strtoupper($this->ApplicationCode) === strtoupper($applicationCode);
    }

    /**
     * Get the display name for this role
     */
    public function getDisplayName(): string
    {
        return $this->Role ?? $this->RoleCode ?? 'Unknown Role';
    }

    /**
     * Get role summary for logging/debugging
     */
    public function getRoleSummary(): array
    {
        return [
            'id' => $this->Id,
            'application' => $this->ApplicationCode,
            'role_id' => $this->RoleId,
            'role_code' => $this->RoleCode,
            'role_name' => $this->Role,
            'is_active' => $this->is_active,
        ];
    }

    // =================================================================
    // AZURE GROUP INTEGRATION (FOR OPS ROLES)
    // =================================================================

    /**
     * For OPS roles, the RoleCode is actually the Azure Group ID
     * This method provides semantic clarity
     */
    public function getAzureGroupId(): ?string
    {
        if ($this->isOpsRole()) {
            return $this->RoleCode; // For OPS, RoleCode = Azure Group ID (GUID)
        }
        
        return null;
    }

    /**
     * Get Azure group information for OPS roles
     */
    public function getAzureGroupInfo(): ?array
    {
        if (!$this->isOpsRole()) {
            return null;
        }

        return [
            'group_id' => $this->RoleCode, // GUID
            'group_name' => $this->Role,   // Display name
        ];
    }

    // =================================================================
    // VALIDATION METHODS
    // =================================================================

    /**
     * Validate role data integrity
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->ApplicationCode)) {
            $errors[] = 'Application Code is required';
        }

        if (empty($this->ApplicationId)) {
            $errors[] = 'Application ID is required';
        }

        if (empty($this->RoleId)) {
            $errors[] = 'Role ID is required';
        }

        if (empty($this->Role)) {
            $errors[] = 'Role name is required';
        }

        // For OPS roles, RoleCode should be a valid GUID
        if ($this->isOpsRole() && !empty($this->RoleCode)) {
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $this->RoleCode)) {
                $errors[] = 'OPS Role Code must be a valid GUID (Azure Group ID)';
            }
        }

        return $errors;
    }

    /**
     * Check if role data is valid
     */
    public function isValid(): bool
    {
        return empty($this->validate());
    }

    // =================================================================
    // BULK OPERATIONS
    // =================================================================

    /**
     * Get role statistics by application
     */
    public static function getRoleStatistics(): \Illuminate\Support\Collection
    {
        return static::active()
            ->selectRaw('ApplicationCode, COUNT(*) as role_count')
            ->groupBy('ApplicationCode')
            ->orderBy('ApplicationCode')
            ->get();
    }

    /**
     * Get all unique application codes
     */
    public static function getApplicationCodes(): array
    {
        return static::active()
            ->distinct()
            ->pluck('ApplicationCode')
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Check for duplicate role IDs within an application
     */
    public static function findDuplicateRoles(string $applicationCode): \Illuminate\Support\Collection
    {
        return static::active()
            ->forApplication($applicationCode)
            ->selectRaw('RoleId, COUNT(*) as duplicate_count')
            ->groupBy('RoleId')
            ->havingRaw('COUNT(*) > 1')
            ->get();
    }

    // =================================================================
    // LOGGING HELPERS
    // =================================================================

    /**
     * Log role retrieval for debugging
     */
    public function logRetrieval(string $context = 'general'): void
    {
        Log::info("Role retrieved: {$context}", $this->getRoleSummary());
    }

    /**
     * Log role assignment attempt
     */
    public function logAssignment(string $userId, string $context = ''): void
    {
        Log::info("Role assignment attempt", [
            'user_id' => $userId,
            'role_summary' => $this->getRoleSummary(),
            'context' => $context,
        ]);
    }
}