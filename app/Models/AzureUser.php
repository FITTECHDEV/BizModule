<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class AzureUser extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The database connection to use
     */
    protected $connection = 'sqlsrv';

    /**
     * The table associated with the model.
     */
    protected $table = 'Azure_Users';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'Id';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = true;

    /**
     * The "type" of the primary key ID.
     */
    protected $keyType = 'string';

    /**
     * Get the route key name for Laravel route model binding.
     * Use 'Id' (capital I) to match the database column.
     */
    public function getRouteKeyName()
    {
        return 'Id';
    }

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Id',
        'userPrincipalName',
        'displayName',
        'surname',
        'mail',
        'givenName',
        'telephoneNumber',
        'mobilePhone',
        'jobTitle',
        'department',
        'officeLocation',
        'accountEnabled',
        'userType',
        'usageLocation',
        'country',
        'Status',
        'EmployeCode',
        'companyName',
        'createdDateTime',
        'AzureId',
        'BaseOfficeCode',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'createdDateTime' => 'datetime',
        'accountEnabled' => 'boolean',
    ];

    // =================================================================
    // ATTRIBUTE ACCESSORS - Map to existing columns
    // =================================================================

    /**
     * Accessor: azure_id -> AzureId (existing column)
     */
    public function getAzureIdAttribute()
    {
        return $this->attributes['AzureId'] ?? null;
    }

    /**
     * Mutator: azure_id -> AzureId (existing column)
     */
    public function setAzureIdAttribute($value)
    {
        $this->attributes['AzureId'] = $value;
    }

    /**
     * Accessor: azure_upn -> userPrincipalName (existing column)
     * Since Azure UPN is the same as userPrincipalName
     */
    public function getAzureUpnAttribute()
    {
        return $this->attributes['userPrincipalName'] ?? null;
    }

    /**
     * Accessor: azure_display_name -> displayName (existing column)
     */
    public function getAzureDisplayNameAttribute()
    {
        return $this->attributes['displayName'] ?? null;
    }

    // =================================================================
    // RELATIONSHIPS
    // =================================================================

    /**
     * Get all module assignments for this user
     */
    public function moduleAssignments(): HasMany
    {
        return $this->hasMany(AzureApplicationUserMapping::class, 'user_id', 'userPrincipalName');
    }

    /**
     * Get modules assigned to this user (distinct list)
     */
    public function getModulesAttribute()
    {
        return $this->moduleAssignments()
            ->select('ApplicationName', 'application_id')
            ->distinct()
            ->get();
    }

    // =================================================================
    // QUERY SCOPES
    // =================================================================

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('Status', 'Active')
            ->where('accountEnabled', true);
    }

    /**
     * Scope a query to only include pending users.
     */
    public function scopePending($query)
    {
        return $query->where('Status', 'Pending');
    }

    /**
     * Scope a query to only include inactive users.
     */
    public function scopeInactive($query)
    {
        return $query->where('Status', 'Inactive');
    }

    /**
     * Scope a query by location
     */
    public function scopeByLocation($query, $location)
    {
        return $query->where('officeLocation', $location);
    }

    /**
     * Scope a query to search users by name, email, or employee code.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('displayName', 'LIKE', "%{$term}%")
                ->orWhere('mail', 'LIKE', "%{$term}%")
                ->orWhere('userPrincipalName', 'LIKE', "%{$term}%")
                ->orWhere('EmployeCode', 'LIKE', "%{$term}%");
        });
    }

    // =================================================================
    // ATTRIBUTE ACCESSORS
    // =================================================================

    /**
     * Get the user's initials for avatar.
     */
    public function getInitialsAttribute()
    {
        if (empty($this->displayName)) {
            return 'U';
        }

        $names = explode(' ', trim($this->displayName));

        if (count($names) >= 2) {
            return strtoupper($names[0][0] . $names[1][0]);
        }

        return strtoupper(substr($this->displayName, 0, 2));
    }

    /**
     * Get the user's status badge class.
     */
    public function getStatusBadgeClassAttribute()
    {
        return match ($this->Status) {
            'Active' => 'badge-success',
            'Pending' => 'badge-warning',
            'Inactive' => 'badge-danger',
            default => 'badge-secondary'
        };
    }

    /**
     * Get formatted email (fallback to userPrincipalName)
     */
    public function getEmailAttribute()
    {
        return $this->mail ?? $this->userPrincipalName;
    }

    /**
     * Get formatted name (fallback to email)
     */
    public function getNameAttribute()
    {
        return $this->displayName ?? $this->email;
    }

    // =================================================================
    // STATUS CHECKS
    // =================================================================

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->Status === 'Active' && $this->accountEnabled;
    }

    /**
     * Check if user is pending
     */
    public function isPending(): bool
    {
        return $this->Status === 'Pending';
    }

    /**
     * Check if user is inactive
     */
    public function isInactive(): bool
    {
        return $this->Status === 'Inactive';
    }

    /**
     * Check if user account is enabled
     */
    public function isEnabled(): bool
    {
        return (bool) $this->accountEnabled;
    }

    // =================================================================
    // AZURE PROVISIONING STATUS CHECKS
    // =================================================================

    /**
     * Check if user has been provisioned to Azure AD
     * Uses existing AzureId column
     *
     * @return bool
     */
    public function isProvisionedToAzure(): bool
    {
        return !empty($this->AzureId);
    }

    /**
     * Check if user can be deactivated
     * User must be provisioned to Azure, currently active, and account enabled
     *
     * @return bool
     */
    public function canBeDeactivated(): bool
    {
        return $this->isProvisionedToAzure()
            && $this->Status === 'Active'
            && $this->accountEnabled == 1;
    }

    /**
     * Check if user can be reactivated
     * User must be provisioned to Azure and currently inactive
     *
     * @return bool
     */
    public function canBeReactivated(): bool
    {
        return $this->isProvisionedToAzure()
            && $this->Status === 'Inactive';
    }

    /**
     * Check if user is currently deactivated
     *
     * @return bool
     */
    public function isDeactivated(): bool
    {
        return $this->Status === 'Inactive';
    }

    /**
     * Get the Azure provisioning status with details
     *
     * @return array
     */
    public function getAzureProvisioningStatus(): array
    {
        if (!$this->isProvisionedToAzure()) {
            return [
                'provisioned' => false,
                'status' => 'Not Provisioned',
                'message' => 'User has not been provisioned to Azure AD yet.',
                'azure_id' => null,
                'azure_upn' => null,
            ];
        }

        return [
            'provisioned' => true,
            'status' => $this->Status,
            'account_enabled' => $this->accountEnabled == 1,
            'azure_id' => $this->AzureId,
            'azure_upn' => $this->userPrincipalName,
            'azure_display_name' => $this->displayName,
            'can_deactivate' => $this->canBeDeactivated(),
            'can_reactivate' => $this->canBeReactivated(),
        ];
    }

    /**
     * Check if user needs Azure sync
     * Compares local data with expected Azure state
     *
     * @return bool
     */
    public function needsAzureSync(): bool
    {
        if (!$this->isProvisionedToAzure()) {
            return true; // Not provisioned at all
        }

        // Check if status mismatch (local says Active but we're not sure about Azure)
        if ($this->Status === 'Pending') {
            return true;
        }

        return false;
    }

    /**
     * Get a summary of Azure provisioning issues (if any)
     *
     * @return array
     */
    public function getAzureProvisioningIssues(): array
    {
        $issues = [];

        if (!$this->isProvisionedToAzure()) {
            $issues[] = [
                'type' => 'critical',
                'message' => 'User not provisioned to Azure AD',
                'action' => 'Provision user to Azure',
            ];
        }

        if ($this->Status === 'Pending' && $this->isProvisionedToAzure()) {
            $issues[] = [
                'type' => 'warning',
                'message' => 'User status is Pending but has Azure ID',
                'action' => 'Update status to Active or verify Azure provisioning',
            ];
        }

        if (empty($this->userPrincipalName) && $this->isProvisionedToAzure()) {
            $issues[] = [
                'type' => 'warning',
                'message' => 'Missing User Principal Name',
                'action' => 'Sync UPN from Azure AD',
            ];
        }

        // Check for module assignments without Azure provisioning
        if (!$this->isProvisionedToAzure() && $this->moduleAssignments()->count() > 0) {
            $issues[] = [
                'type' => 'critical',
                'message' => 'Has module assignments but not provisioned to Azure',
                'action' => 'Provision to Azure to enable group/role assignments',
            ];
        }

        return $issues;
    }

    /**
     * Mark user as provisioned to Azure
     * ✅ FIXED: Removed LastModifiedBy and LastModifiedOn (columns don't exist)
     *
     * @param string $azureId Azure AD Object ID
     * @param string|null $azureUpn Azure User Principal Name (optional, uses userPrincipalName)
     * @param string|null $azureDisplayName Display name in Azure (optional, uses displayName)
     * @param string|null $updatedBy Who performed the update (unused - column doesn't exist)
     * @return bool
     */
    public function markAsProvisionedToAzure(
        string $azureId,
        ?string $azureUpn = null,
        ?string $azureDisplayName = null,
        ?string $updatedBy = null
    ): bool {
        return DB::connection('sqlsrv')->update("
            UPDATE Azure_Users
            SET AzureId = ?,
                Status = 'Active'
            WHERE Id = ?
        ", [
            $azureId,
            $this->Id
        ]) > 0;
    }

    /**
     * Clear Azure provisioning data (for rollback scenarios)
     * ✅ FIXED: Removed LastModifiedBy and LastModifiedOn (columns don't exist)
     *
     * @param string|null $updatedBy Who performed the update (unused - column doesn't exist)
     * @return bool
     */
    public function clearAzureProvisioning(?string $updatedBy = null): bool
    {
        return DB::connection('sqlsrv')->update("
            UPDATE Azure_Users
            SET AzureId = NULL,
                Status = 'Pending'
            WHERE Id = ?
        ", [$this->Id]) > 0;
    }

    // =================================================================
    // MODULE ASSIGNMENT HELPERS
    // =================================================================

    /**
     * Get count of assigned modules
     */
    public function getModuleCountAttribute(): int
    {
        return $this->moduleAssignments()
            ->select('ApplicationName')
            ->distinct()
            ->count();
    }

    /**
     * Check if user has a specific module assigned
     */
    public function hasModule(string $moduleName): bool
    {
        return $this->moduleAssignments()
            ->where('ApplicationName', $moduleName)
            ->exists();
    }

    /**
     * Get all roles for a specific module
     */
    public function getRolesForModule(string $moduleName): array
    {
        return $this->moduleAssignments()
            ->where('ApplicationName', $moduleName)
            ->pluck('RoleCode')
            ->unique()
            ->toArray();
    }

    /**
     * Get all locations for a specific module
     */
    public function getLocationsForModule(string $moduleName): array
    {
        return $this->moduleAssignments()
            ->where('ApplicationName', $moduleName)
            ->pluck('OfficeCode')
            ->unique()
            ->toArray();
    }

    // =================================================================
    // UTILITY METHODS
    // =================================================================

    /**
     * Get user's full profile information
     */
    public function getProfileSummary(): array
    {
        return [
            'id' => $this->Id,
            'name' => $this->name,
            'email' => $this->email,
            'employee_code' => $this->EmployeCode,
            'phone' => $this->telephoneNumber,
            'mobile' => $this->mobilePhone,
            'job_title' => $this->jobTitle,
            'department' => $this->department,
            'location' => $this->officeLocation,
            'status' => $this->Status,
            'account_enabled' => $this->accountEnabled,
            'module_count' => $this->module_count,
            'created_at' => $this->createdDateTime,
            'azure_provisioned' => $this->isProvisionedToAzure(),
            'azure_id' => $this->AzureId,
            'azure_upn' => $this->userPrincipalName,
        ];
    }

    /**
     * Update user status
     * ✅ FIXED: Removed LastModifiedBy and LastModifiedOn (columns don't exist)
     */
    public function updateStatus(string $status, ?string $updatedBy = null): bool
    {
        return DB::connection('sqlsrv')->update("
            UPDATE Azure_Users
            SET Status = ?
            WHERE Id = ?
        ", [$status, $this->Id]) > 0;
    }

    /**
     * Disable user account
     * ✅ FIXED: Removed LastModifiedBy and LastModifiedOn (columns don't exist)
     */
    public function disable(?string $updatedBy = null): bool
    {
        return DB::connection('sqlsrv')->update("
            UPDATE Azure_Users
            SET accountEnabled = 0,
                Status = 'Inactive'
            WHERE Id = ?
        ", [$this->Id]) > 0;
    }

    /**
     * Enable user account
     * ✅ FIXED: Removed LastModifiedBy and LastModifiedOn (columns don't exist)
     */
    public function enable(?string $updatedBy = null): bool
    {
        return DB::connection('sqlsrv')->update("
            UPDATE Azure_Users
            SET accountEnabled = 1,
                Status = 'Active'
            WHERE Id = ?
        ", [$this->Id]) > 0;
    }
}