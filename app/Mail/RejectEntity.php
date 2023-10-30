<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class RejectEntity extends Mailable
{
    use Queueable, SerializesModels;

    public $entity;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.reject-entity',[
            'url' => URL::signedRoute('client-party.show', [$this->entity->Id]),
            'entityCode' => $this->entity->Code,
            'reason' => $this->entity->RejectedReason,
        ]);
    }
}