<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class TicketAssign extends Model
{
    // use Cachable;
    use HasFactory;

    CONST CREATED_AT = 'AssignedOn';
    CONST UPDATED_AT = 'UpdatedOn';
    CONST DELETED_AT = 'DeletedOn';

    protected $table = 'TIC_TicketAssigningHistory';

    protected $fillable = [
        'ID',
        'TicketId',
        'AssignedBy',
        'AssignedTo',
        'Comment',
    ];

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'AssignedTo','ID');
    }
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'Assignedby','ID');
    }
}
