<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{

    public function markAsRead($id = null)
    {
        if (!$id) {
            auth()->user()->unreadNotifications->markAsRead();
        
        }else{
            auth()->user()->unreadNotifications->where('id', $id)->markAsRead();
        }

        toast('Marked as read', 'success');
        return redirect()->back();
    }
}
