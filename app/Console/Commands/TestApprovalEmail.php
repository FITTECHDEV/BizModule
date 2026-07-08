<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\ApprovalRequestMail;

class TestApprovalEmail extends Command
{
    protected $signature = 'test:approval-email';
    protected $description = 'Send a test approval email';

    public function handle()
    {
        $record = (object) ['id' => 1, 'reference' => 'TEST-001'];
        Mail::to('brianmunene11@gmail.com')->send(new ApprovalRequestMail($record));
        $this->info('Sent.');
    }
}
