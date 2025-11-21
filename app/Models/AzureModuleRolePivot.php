<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AzureModuleRolePivot extends Model
{
    /**
     * The database connection to use
     */
    // protected $connection = 'azure_management';

    /**
     * The table associated with the model.
     */
    protected $table = 'module_roles';

    protected $fillable = [
        'module_id',
        'role_id',
    ];
}