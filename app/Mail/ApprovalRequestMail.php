<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class ApprovalRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $record;
    public $approveUrl;

    public function __construct($record)
    {
        $this->record = $record;

        $this->approveUrl = URL::temporarySignedRoute(
            'approval.approve',      // route name
            now()->addDays(3),       // expiry
            ['id' => $record->id]
        );
    }

    public function build()
    {
        return $this->subject('Approval Required: ' . $this->record->reference)
            ->view('emails.approval-request');
    }
}
