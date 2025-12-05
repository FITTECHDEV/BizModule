<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AzureApplicationUserMapping extends Model
{
    use HasFactory;

    /**
     * The database connection to use
     */
    protected $connection = 'sqlsrv';

    /**
     * The table associated with the model.
     */
    protected $table = 'Azure_Application_UserMapping';
    
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
        'user_id',
        'application_id',
        'Username',
        'ApplicationName',
        'FunctionCode',
        'FunctionID',
        'RoleCode',
        'RoleID',
        'OfficeCode',
        'OfficeID',
        'IsActive',
        'IsDelete',
        'CreatedBy',
        'CreatedOn',
        'LastModifiedBy',
        'LastModifiedOn',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'IsActive' => 'boolean',
        'IsDelete' => 'boolean',
        'CreatedOn' => 'datetime',
        'LastModifiedOn' => 'datetime',
    ];

    // =================================================================
    // RELATIONSHIPS
    // =================================================================

    /**
     * Get the user that this assignment belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(AzureUser::class, 'user_id', 'userPrincipalName');
    }

    // =================================================================
    // QUERY SCOPES
    // =================================================================

    /**
     * Scope a query to only include active assignments.
     */
    public function scopeActive($query)
    {
        return $query->where('IsActive', true)
                     ->where('IsDelete', false);
    }

    /**
     * Scope a query by user
     */
    public function scopeByUser($query, string $userPrincipalName)
    {
        return $query->where('user_id', $userPrincipalName);
    }

    /**
     * Scope a query by application
     */
    public function scopeByApplication($query, string $applicationName)
    {
        return $query->where('ApplicationName', $applicationName);
    }

    /**
     * Scope a query by office/location
     */
    public function scopeByOffice($query, string $officeCode)
    {
        return $query->where('OfficeCode', $officeCode);
    }

    /**
     * Scope a query by role
     */
    public function scopeByRole($query, string $roleCode)
    {
        return $query->where('RoleCode', $roleCode);
    }

    /**
     * Scope for SCM modules (with functions)
     */
    public function scopeScmOnly($query)
    {
        return $query->whereNotNull('FunctionCode');
    }

    // =================================================================
    // ATTRIBUTE ACCESSORS
    // =================================================================

    /**
     * Check if this is an SCM assignment (has function)
     */
    public function getIsSCMAttribute(): bool
    {
        return !empty($this->FunctionCode);
    }

    /**
     * Get formatted assignment details
     */
    public function getDetailsAttribute(): string
    {
        $details = "{$this->ApplicationName} - {$this->RoleCode}";
        
        if ($this->is_scm) {
            $details .= " ({$this->FunctionCode})";
        }
        
        $details .= " @ {$this->OfficeCode}";
        
        return $details;
    }

    // =================================================================
    // HELPER METHODS
    // =================================================================

    /**
     * Check if assignment is active
     */
    public function isActive(): bool
    {
        return $this->IsActive && !$this->IsDelete;
    }

    /**
     * Soft delete the assignment
     */
    public function softDelete(?string $deletedBy = null): bool
    {
        $this->IsDelete = true;
        $this->IsActive = false;
        $this->LastModifiedBy = $deletedBy ?? 'system';
        $this->LastModifiedOn = now();
        
        return $this->save();
    }

    /**
     * Activate the assignment
     */
    public function activate(?string $activatedBy = null): bool
    {
        $this->IsActive = true;
        $this->IsDelete = false;
        $this->LastModifiedBy = $activatedBy ?? 'system';
        $this->LastModifiedOn = now();
        
        return $this->save();
    }

    /**
     * Deactivate the assignment
     */
    public function deactivate(?string $deactivatedBy = null): bool
    {
        $this->IsActive = false;
        $this->LastModifiedBy = $deactivatedBy ?? 'system';
        $this->LastModifiedOn = now();
        
        return $this->save();
    }

    // =================================================================
    // STATIC HELPER METHODS
    // =================================================================

    /**
     * Get all assignments for a user
     */
    public static function getUserAssignments(string $userPrincipalName): \Illuminate\Support\Collection
    {
        return self::active()
            ->byUser($userPrincipalName)
            ->orderBy('ApplicationName')
            ->orderBy('OfficeCode')
            ->get();
    }

    /**
     * Check if user has specific module/role/location combination
     */
    public static function hasAssignment(
        string $userPrincipalName,
        string $applicationName,
        string $roleCode,
        string $officeCode
    ): bool {
        return self::active()
            ->byUser($userPrincipalName)
            ->byApplication($applicationName)
            ->byRole($roleCode)
            ->byOffice($officeCode)
            ->exists();
    }

    /**
     * Get assignments count by user
     */
    public static function getUserAssignmentCount(string $userPrincipalName): int
    {
        return self::active()
            ->byUser($userPrincipalName)
            ->count();
    }

    /**
     * Get unique modules for a user
     */
    public static function getUserModules(string $userPrincipalName): array
    {
        return self::active()
            ->byUser($userPrincipalName)
            ->select('ApplicationName')
            ->distinct()
            ->pluck('ApplicationName')
            ->toArray();
    }
}