<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class Office extends Model
{
    // use Cachable;
    use HasFactory;

    protected $table = 'Cust_Office';

    CONST CREATED_AT = 'CreatedOn';
    CONST UPDATED_AT = 'lastModifiedOn';
    CONST DELETED_AT = 'DeletedOn';

    /**
     * Has many tickets
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
