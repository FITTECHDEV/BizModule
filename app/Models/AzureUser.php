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
        'LastModifiedBy',
        'LastModifiedOn',
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
        'LastModifiedOn' => 'datetime',
        'accountEnabled' => 'boolean',
    ];

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
        ];
    }

    /**
     * Update user status
     */
    public function updateStatus(string $status, ?string $updatedBy = null): bool
    {
        return DB::connection('sqlsrv')->update("
            UPDATE Azure_Users 
            SET Status = ?, 
                LastModifiedBy = ?, 
                LastModifiedOn = GETDATE()
            WHERE Id = ?
        ", [$status, $updatedBy ?? 'system', $this->Id]) > 0;
    }

    /**
     * Disable user account
     */
    public function disable(?string $updatedBy = null): bool
    {
        return DB::connection('sqlsrv')->update("
            UPDATE Azure_Users 
            SET accountEnabled = 0,
                Status = 'Inactive',
                LastModifiedBy = ?, 
                LastModifiedOn = GETDATE()
            WHERE Id = ?
        ", [$updatedBy ?? 'system', $this->Id]) > 0;
    }

    /**
     * Enable user account
     */
    public function enable(?string $updatedBy = null): bool
    {
        return DB::connection('sqlsrv')->update("
            UPDATE Azure_Users 
            SET accountEnabled = 1,
                Status = 'Active',
                LastModifiedBy = ?, 
                LastModifiedOn = GETDATE()
            WHERE Id = ?
        ", [$updatedBy ?? 'system', $this->Id]) > 0;
    }
}