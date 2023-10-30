<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class Notification extends Model
{use Cachable;
    use HasFactory, Notifiable;

    protected $table ='Cust_Notifications';

    public $timestamps = true;

    public $timezone = 'EAT';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s A',
    ];

    public function notifyUser($id, $greeting=null, $message, $thanks=null)
    {
    
        $user = User::where('ID', $id)->first() ? User::where('ID', $id)->first() : '';
        //check user availability
        if ($user) {
            
            $details = [
                'greeting' => $greeting,
                'body' => $message,
                'thanks' => $thanks,
            ];

            // if user role is 1 send one notification
            if (auth()->user()->Role == 1) {
                $user->notify(new \App\Notifications\TaskComplete($details));
            }else{
                // send to all admins
                $admins = User::where('Role','=', 1)->get();
                foreach ($admins as $key => $admin) {
                   $admin->notify(new \App\Notifications\TaskComplete($details));
                }
            }
        }
        
    }

    public function notifyTicket(Ticket $ticket, $greeting=null, $message, $thanks=null)
    {
        $ticket->notify(new \App\Notifications\TicketTrack($message));
    }

    public function markAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
    
        return redirect()->back();
    }
}
