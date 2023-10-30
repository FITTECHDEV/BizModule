<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntityHistory extends Model
{
    use HasFactory;

    protected $table = 'Cust_EntityHistory';

    const DELETED_AT = 'DeletedOn';
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'LastModifiedOn';

    protected $fillable = [
        'ID',	
        'CustEntityID',	
        'RequestNo',	
        'Role',	
        'ReasonID',	
        'Comment',	
        'Type',	
        'PreviousValue',	
        'NewValue',	
        'CreatedBy',	
        'CreatedOn',	
        'LastModifiedBy',	
        'LastModifiedOn',	
        'IsActive',	
        'IsDeleted',
        'DeletedOn'
    ];
}
