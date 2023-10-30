<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KRABillingDetail extends Model
{
    use HasFactory;

    protected $table = 'KRA_BillingDetails';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'LastModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public function kraBillingHeader()
    {
        return $this->hasOne(KRABillingHeader::class, 'DocumentHeaderId', 'Id');
    }

}
