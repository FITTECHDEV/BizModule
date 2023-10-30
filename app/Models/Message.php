<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class Message extends Model
{
    // use Cachable;
    use HasFactory;

    protected $table = 'Tic_TicketCommentTrack';

    protected $primarykey = 'ID';

    CONST CREATED_AT = 'CreatedOn';
    CONST DELETED_AT = 'DeletedOn';
    CONST UPDATED_AT = 'LastModifiedOn';
// mutate dates
    protected $dates = [
        'CreatedOn',
        'DeletedOn',
        'LastModifiedOn'
    ];
    //casts


    protected $fillable = [
        'ID',
        'TicketID',
        'TicketNo',
        'Comment',
        'CreatedBy',
        'LastModifiedBy',
        'Type',
        'PreviousValue',
        'NewValue',
        'Role'
    ];

    // public $timestamps = false;
    public function user()
    {
       return $this->belongsTo(User::class, 'CreatedBy', 'ID');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class,'TicketID', 'Id');
    }

    public function messageNewUser()
    {
        return $this->belongsTo(User::class, 'NewValue', 'ID');

    }
}
