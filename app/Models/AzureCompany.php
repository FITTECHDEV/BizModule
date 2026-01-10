<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AzureCompany extends Model
{
    use HasFactory;

    /**
     * The database connection to use
     */
    // protected $connection = 'azure_management';

    /**
     * The table associated with the model.
     */
    protected $table = 'companies';

    protected $fillable = [
        'name',
        'location',
        'code',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all users for this company
     */
    public function users(): HasMany
    {
        return $this->hasMany(AzureUser::class, 'company_id');
    }

    /**
     * Scope: Only active companies
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by location
     */
    public function scopeByLocation($query, $location)
    {
        return $query->where('location', $location);
    }
}