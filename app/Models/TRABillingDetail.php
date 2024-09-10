<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TRABillingDetail extends Model
{
    use HasFactory;

    protected $table = 'TRA_BillingDetails';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'LastModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public function traBillingHeader()
    {
        return $this->hasOne(TRABillingHeader::class, 'DocumentHeaderId', 'Id');
    }
}
