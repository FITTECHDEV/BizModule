<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleAzure extends Model
{
    use HasFactory;

    /**
     * The database connection to use
     */
    protected $connection = 'sqlsrv';

    /**
     * The table associated with the model.
     */
    protected $table = 'Azure_Users_Role';
    
    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'RoleId';
    
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
        'RoleId',
        'RoleCode',
        'Role',
        'ApplicationCode',
        'IsActive',
        'IsDeleted',
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
        'IsDeleted' => 'boolean',
        'CreatedOn' => 'datetime',
        'LastModifiedOn' => 'datetime',
    ];

    // =================================================================
    // QUERY SCOPES
    // =================================================================

    /**
     * Scope a query to only include active roles.
     */
    public function scopeActive($query)
    {
        return $query->where('IsActive', true)
                     ->where('IsDeleted', false);
    }

    /**
     * Scope a query by application code.
     */
    public function scopeByApplication($query, string $applicationCode)
    {
        return $query->where('ApplicationCode', $applicationCode);
    }

    /**
     * Scope a query to search roles by name or code.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('Role', 'LIKE', "%{$term}%")
              ->orWhere('RoleCode', 'LIKE', "%{$term}%");
        });
    }

    // =================================================================
    // ATTRIBUTE ACCESSORS
    // =================================================================

    /**
     * Get role display name
     */
    public function getNameAttribute(): string
    {
        return $this->Role ?? $this->RoleCode ?? 'Unknown Role';
    }

    /**
     * Get role code
     */
    public function getCodeAttribute(): string
    {
        return $this->RoleCode ?? '';
    }

    // =================================================================
    // STATIC HELPER METHODS
    // =================================================================

    /**
     * Get all roles for a specific application
     */
    public static function getRolesForApplication(string $applicationCode): \Illuminate\Support\Collection
    {
        return self::active()
            ->byApplication($applicationCode)
            ->orderBy('Role', 'asc')
            ->get();
    }

    /**
     * Check if a role exists for an application
     */
    public static function roleExists(string $roleCode, string $applicationCode): bool
    {
        return self::active()
            ->where('RoleCode', $roleCode)
            ->where('ApplicationCode', $applicationCode)
            ->exists();
    }

    /**
     * Get role by code and application
     */
    public static function findByCodeAndApplication(string $roleCode, string $applicationCode): ?self
    {
        return self::active()
            ->where('RoleCode', $roleCode)
            ->where('ApplicationCode', $applicationCode)
            ->first();
    }
}