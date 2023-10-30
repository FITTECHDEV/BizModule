<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * 
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'TicketID' => 'required',
            'message' => 'required',
        ]);

        // create a new message 
        $message = Message::create([
            'MessageID' => guid(),
            'TicketID' => $request->TicketID,
            'Role' => Auth::user()->Role,
            'Message' => $request->message,
            'CreatedBy' => Auth::user()->ID
        ]);

        toast('Message Sent !!', 'success');

        $mess_user = $message->with('user')->latest()->first();
        // return redirect()->back();
        return response()->json(['message' => $mess_user]);
    }
}
