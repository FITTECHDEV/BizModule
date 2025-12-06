<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AzureRole extends Model
{
    use HasFactory;

    /**
     * The database connection to use
     */
    // protected $connection = 'azure_management';

    /**
     * The table associated with the model.
     */
    protected $table = 'azure_roles';

    protected $fillable = [
        'name',
        'description',
        'permissions',
        'is_active'
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get all users with this role
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(AzureUser::class, 'user_roles')
                    ->withPivot('assigned_at')
                    ->withTimestamps();
    }

    /**
     * Get all modules associated with this role
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(AzureModule::class, 'module_roles')
                    ->withTimestamps();
    }

    /**
     * Scope: Only active roles
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}