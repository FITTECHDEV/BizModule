<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class TicketStatusHistory extends Model
{use Cachable;
    use HasFactory;

    protected $table = 'TIC_TicketStatusHistory';

    protected $guarded = [];

    const DELETED_AT = 'DeletedOn';
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'LastModifiedOn';

    // belongs to a ticket 
    public function ticket()
    {
        return $this->belongsTo(Ticket::class,'TicketID', 'Id')->latest();
    }
}
