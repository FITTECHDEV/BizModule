<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CloseTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ticket:close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close All Solved Tickets within 72 Hours.';

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
        $tickets = Ticket::where('Status', 5)->get();

        foreach ($tickets as $key => $ticket) {
            //Todo: add time filter for 72 hours
            // DB::table('TIC_TicketInfo')
            //     ->where('Id',  $ticket->Id)
            //     ->update([
            //         'Status' => 4
            //     ]);
        }
    }
}
