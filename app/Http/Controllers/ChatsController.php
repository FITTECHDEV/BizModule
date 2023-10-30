<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatsController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Show chats
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    return view('chat');
  }

  /**
   * Fetch all messages
   *
   * @return Message
   */
  public function fetchMessages()
  {
    //get messages for this user
    $messages = Message::where('Type', 'Chat')->with('user')->oldest('CreatedOn')->get();
    return response()->json($messages);
  }

 
  /**
   * Persist message to database
   *
   * @param  Request $request
   * @return Response
   */
  public function sendMessage(Request $request)
  {
    $user = Auth::user();
    $ticket = Ticket::where('Id', $request->data['Id'])->first();
    Message::create([
      'ID' => guid(),
      'TicketID' => $request->data['Id'],
      'TicketNo' => $ticket->TicketNo,
      'Role' => $user->Role,
      'CreatedBy' => $user->ID,
      'Comment' => $request->input('Comment'),
      'LastModifiedBy' => $user->ID,
      'Type' => 'Chat',
      'PreviousValue' => Null,
      'NewValue' => Null
    ]);
    // // notify ticket
    // $notify = new Notification();
    // $notify->notifyTicket($ticket, '', 'Chat <b>'.$request->input('Message').'</b><br>By <small>' . auth()->user()->full_name . '</small>','');
    // broadcast(new MessageSent($user, $message, $request->data))->toOthers();

    return response()->json(200);
  }
}
