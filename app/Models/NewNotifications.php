<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class NewNotifications extends DatabaseNotification
{use Cachable;
    use HasFactory;

    protected $table = 'Cust_Notifications';

    public $timestamps = true;

    

    // protected $casts = [
    //     // 'created_at' => 'datetime:Y-m-d H:i:s A',
    // ];

}
