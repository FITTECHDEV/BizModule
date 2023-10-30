<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class Attachment extends Model
{use Cachable;
    use HasFactory, SoftDeletes;

    protected $table = 'TIC_TicketAttachment';

    protected $guarded= [];

    protected $primarykey ='ID';

    // public $timestamps = false;
    CONST UPDATED_AT = 'LastModifiedOn';
    CONST DELETED_AT = 'DeletedOn';
    CONST CREATED_AT = 'CreatedOn';

    protected $casts = [
        'DeletedOn' => 'datetime',
        'LastModifiedOn' => 'datetime',
        'CreatedOn' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'TicketID', 'Id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'CreatedBy', 'ID');
    }
}
