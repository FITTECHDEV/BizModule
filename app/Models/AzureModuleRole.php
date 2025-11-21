<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AzureModuleRole extends Model
{
    use HasFactory;

    /**
     * The database connection to use
     */
    // protected $connection = 'azure_management';

    /**
     * The table associated with the model.
     */
    protected $table = 'module_role_groups';

    protected $fillable = [
        'module_id',
        'role_id',
        'azure_group_id',
        'azure_group_name',
    ];

    /**
     * Get the module for this mapping
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(AzureModule::class, 'module_id');
    }

    /**
     * Get the role for this mapping
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(AzureRole::class, 'role_id');
    }
}