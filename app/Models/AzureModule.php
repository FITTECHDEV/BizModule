<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class AzureModule extends Model
{
    use HasFactory;

    /**
     * The database connection to use
     */
    protected $connection = 'sqlsrv';

    /**
     * The table associated with the model.
     */
    protected $table = 'Azure_SoftwareMaster';

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
        'Name',
        'ApplicationId',
        'IsActive',
        'IsDeleted',
        'CreatedOn',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'IsActive' => 'boolean',
        'IsDeleted' => 'boolean',
        'CreatedOn' => 'datetime',
    ];

    // =================================================================
    // ATTRIBUTE ACCESSORS - Map to service expectations
    // =================================================================

    /**
     * Accessor: name -> Name
     */
    public function getNameAttribute()
    {
        return $this->attributes['Name'] ?? null;
    }

    /**
     * Accessor: code -> ApplicationId
     */
    public function getCodeAttribute()
    {
        return $this->attributes['ApplicationId'] ?? null;
    }

    /**
     * Accessor: is_active -> IsActive AND !IsDeleted
     */
    public function getIsActiveAttribute()
    {
        return ($this->attributes['IsActive'] ?? false) && !($this->attributes['IsDeleted'] ?? false);
    }

    /**
     * Accessor: id -> Id (for services expecting lowercase)
     */
    public function getIdAttribute()
    {
        return $this->attributes['Id'] ?? null;
    }

    // =================================================================
    // RELATIONSHIPS
    // =================================================================

    /**
     * Get all user assignments for this module
     */
    public function userAssignments(): HasMany
    {
        return $this->hasMany(AzureApplicationUserMapping::class, 'application_id', 'ApplicationId');
    }

    /**
     * Get role-to-group mappings for this module
     */
    public function roleGroups(): HasMany
    {
        return $this->hasMany(AzureModuleRole::class, 'ModuleId', 'Id');
    }

    // =================================================================
    // QUERY SCOPES
    // =================================================================

    /**
     * Scope to get only active modules
     */
    public function scopeActive($query)
    {
        return $query->where('IsActive', true)
                    ->where('IsDeleted', false);
    }

    /**
     * Scope to get modules that require Azure provisioning
     * Note: Since we don't have these flags in the table yet,
     * we'll assume all active modules require provisioning
     */
    public function scopeRequiresAzureProvisioning($query)
    {
        return $query->active();
    }

    // =================================================================
    // AZURE INTEGRATION HELPER METHODS
    // =================================================================

    /**
     * Check if module requires Azure provisioning
     * For now, all active modules require provisioning
     */
    public function requiresAzureProvisioning(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if module requires group assignment
     * For now, we'll check if it has role mappings configured
     */
    public function requiresGroupAssignment(): bool
    {
        return $this->is_active && $this->roleGroups()->exists();
    }

    /**
     * Check if module requires app role assignment
     * This would need additional configuration in the future
     */
    public function requiresAppRoleAssignment(): bool
    {
        // TODO: Add configuration table for app role requirements
        return false;
    }

    /**
     * Check if module has Azure group configured
     */
    public function hasAzureGroupConfigured(): bool
    {
        return $this->roleGroups()->exists();
    }

    /**
     * Check if module has Azure app configured
     */
    public function hasAzureAppConfigured(): bool
    {
        // TODO: Add enterprise app configuration
        return false;
    }

    // =================================================================
    // EXTERNAL API HELPER METHODS
    // =================================================================

    /**
     * Check if module has external API
     * TODO: Add external_api_endpoint column or separate config table
     */
    public function hasExternalApi(): bool
    {
        // For now, check if it's one of the known external systems
        $externalModules = ['SCM', 'ODOO', 'BIZ', 'FITGP'];
        return in_array($this->ApplicationId, $externalModules);
    }

    /**
     * Check if API authentication is required
     */
    public function requiresApiAuthentication(): bool
    {
        return $this->hasExternalApi();
    }

    /**
     * Get API credentials (placeholder for future implementation)
     */
    public function getApiCredentials(): ?array
    {
        // TODO: Implement credentials storage
        return null;
    }

    // =================================================================
    // PROVISIONING STATUS METHODS
    // =================================================================

    /**
     * Check if module is fully configured
     */
    public function isFullyConfigured(): bool
    {
        return $this->is_active && $this->hasAzureGroupConfigured();
    }

    /**
     * Get integration type
     */
    public function getIntegrationType(): string
    {
        $types = [];

        if ($this->requiresAzureProvisioning()) {
            $types[] = 'Azure AD';
        }

        if ($this->hasExternalApi()) {
            $types[] = 'External API';
        }

        return empty($types) ? 'None' : implode(' + ', $types);
    }

    /**
     * Get provisioning requirements
     */
    public function getProvisioningRequirements(): array
    {
        return [
            'azure_group' => $this->requiresGroupAssignment(),
            'azure_app_role' => $this->requiresAppRoleAssignment(),
            'external_api' => $this->hasExternalApi(),
            'fully_configured' => $this->isFullyConfigured(),
        ];
    }

    // =================================================================
    // ROLE-TO-GROUP MAPPING METHODS
    // =================================================================

    /**
     * Get the Azure group ID for a specific role
     * This is critical for the AzureModuleAssignmentService
     */
    public function getAzureGroupForRole(int $roleId): ?array
    {
        $mapping = AzureModuleRole::where('ModuleId', $this->Id)
            ->where('RoleId', $roleId)
            ->where('IsActive', true)
            ->first();
        
        if (!$mapping) {
            Log::warning("No Azure group mapping found for module-role combination", [
                'module_id' => $this->Id,
                'module_name' => $this->Name,
                'role_id' => $roleId,
            ]);
            return null;
        }
        
        return [
            'group_id' => $mapping->AzureGroupId,
            'group_name' => $mapping->AzureGroupName ?? 'Unknown Group',
        ];
    }

    /**
     * Check if this module has a mapping for a specific role
     */
    public function hasRoleMapping(int $roleId): bool
    {
        return AzureModuleRole::where('ModuleId', $this->Id)
            ->where('RoleId', $roleId)
            ->where('IsActive', true)
            ->exists();
    }

    // =================================================================
    // STATUS ATTRIBUTE ACCESSORS
    // =================================================================

    /**
     * Get integration status
     */
    public function getIntegrationStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if (!$this->isFullyConfigured()) {
            return 'incomplete';
        }

        return 'ready';
    }

    /**
     * Get integration status badge class for UI
     */
    public function getIntegrationStatusBadgeClassAttribute(): string
    {
        return match($this->integration_status) {
            'ready' => 'badge-success',
            'incomplete' => 'badge-warning',
            'inactive' => 'badge-secondary',
            default => 'badge-secondary',
        };
    }

    // =================================================================
    // UTILITY METHODS
    // =================================================================

    /**
     * Get module summary
     */
    public function getModuleSummary(): array
    {
        return [
            'id' => $this->Id,
            'name' => $this->Name,
            'code' => $this->ApplicationId,
            'is_active' => $this->is_active,
            'requires_azure' => $this->requiresAzureProvisioning(),
            'has_group_mapping' => $this->hasAzureGroupConfigured(),
            'has_external_api' => $this->hasExternalApi(),
            'status' => $this->integration_status,
            'user_count' => $this->userAssignments()->count(),
        ];
    }

    /**
     * Check if a specific application code matches this module
     */
    public function matchesApplicationCode(string $code): bool
    {
        return strtoupper($this->ApplicationId) === strtoupper($code);
    }
}