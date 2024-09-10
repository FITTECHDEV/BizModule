<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RRABillingDetail extends Model
{
    use HasFactory;

    protected $table = 'RRA_BillingDetails';

    const CREATED_AT = 'CreatedOn';

    const UPDATED_AT = 'LastModifiedOn';

    const DELETED_AT = 'DeletedOn';

    public function rraBillingHeader() {
        return $this->hasOne(RRABillingHeader::class, 'DocumentHeaderId', 'Id');
    }
}
