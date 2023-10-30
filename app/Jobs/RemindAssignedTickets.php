<?php

namespace App\Jobs;

use App\Mail\RemindAssigned;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RemindAssignedTickets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // $reminder = DB::select('select ti.AssignedTo, count(ti.AssignedTo) as ToDo, cu.FirstName, cu.LastName, cu.Email
        // from TIC_TicketInfo ti
        // inner join Cust_User as cu on cu.ID = ti.AssignedTo
        // where ti.Status = 1 or ti.Status = 5
        // group by AssignedTo,  cu.FirstName, cu.Email, cu.LastName
        // order by cu.FirstName');

        $reminder = DB::select('SELECT
                COUNT(CASE WHEN Status = 1 THEN 1 END) AS "Open",
                COUNT(CASE WHEN Status = 4 THEN 1 END) AS "Re_Open",
                COUNT(CASE WHEN Status = 0 THEN 1 END) AS "Pending",
                cu.FirstName, cu.LastName, cu.Email
                from TIC_TicketInfo ti
                inner join Cust_User cu on cu.ID = ti.AssignedTo
                group by cu.FirstName, cu.Email, cu.LastName');

        // Log::info(Mail::failures());

        // Log::info($reminder);

        foreach ($reminder as $key => $admin_ticket) {
            $email = new RemindAssigned($admin_ticket);
            $total = $admin_ticket->Open + $admin_ticket->Re_Open + $admin_ticket->Pending;
            // send email if total is not 0
            if ($total > 0) {
                Mail::to([$admin_ticket->Email])->send($email);
            }
        }

        Log::info("Ticket Assigned Reminder Sent ! At " . date('Y-m-d H:i:s'));


    }
}
