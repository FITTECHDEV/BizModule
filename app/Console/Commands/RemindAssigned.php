<?php

namespace App\Console\Commands;

use App\Jobs\RemindAssignedTickets;
use App\Mail\RemindAssigned as MailRemindAssigned;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RemindAssigned extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remind:assigned';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remind Assigned of the pending tickets.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {


        RemindAssignedTickets::dispatch();

        Log::info("Reminder Sent ! At " . date('Y-m-d H:i:s'));

        exit();
    }
}
