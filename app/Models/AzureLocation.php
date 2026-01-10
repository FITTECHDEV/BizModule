<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AzureLocation extends Model
{
    use HasFactory;

    /**
     * The database connection to use
     */
    // protected $connection = 'azure_management';

    /**
     * The table associated with the model.
     */
protected $table = 'azure_locations'; 

    protected $fillable = [
        'name',
        'code',
        'country',
        'timezone',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all companies for this location
     */
    public function companies(): HasMany
    {
        return $this->hasMany(AzureCompany::class, 'location', 'code');
    }

    /**
     * Get all users for this location
     */
    public function users(): HasMany
    {
        return $this->hasMany(AzureUser::class, 'location', 'code');
    }

    /**
     * Scope: Only active locations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}