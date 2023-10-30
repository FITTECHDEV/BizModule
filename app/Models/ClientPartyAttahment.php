<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientPartyAttahment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $primaryKey = 'ID';

    protected $table = 'Cust_EntityAttachment';

    const DELETED_AT = 'DeletedOn';
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'LastModifiedOn';

}
