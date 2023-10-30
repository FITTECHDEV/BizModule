<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class TicketCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $admin;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Ticket $ticket, $admin)
    {
        $this->ticket = $ticket;
        $this->admin = $admin;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.ticket.created', ['admin'=> '', 'ticket' => $this->ticket, 'url' => URL::signedRoute('tickets.show', $this->ticket->Id)]);
    }
}
