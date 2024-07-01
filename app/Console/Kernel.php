<?php

namespace App\Console;

use App\Jobs\RemindAssignedTickets;
use App\Jobs\TicketSolvedJob;
use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    // protected $commands = [
    //     // Commands\CloseTickets::class,
    //     // Commands\RemindAssigned::class,
    // ];

    // protected $commands = [
    //     \App\Console\Commands\SendApprovalReminderEmails::class,
    // ];


    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('TIMS:UploadExpInvAzure')->everyMinute()->withoutOverlapping();
        // $schedule->command('remind:assigned')->everyMinute()->between('8:00', '17:00');
        $schedule->job('App\Jobs\RemindAssignedTickets')->between('8:30', '17:00')->everyFourHours()->withoutOverlapping();

        $schedule->command('emails:sendApprovalReminder')->between('8:30', '17:00')->withoutOverlapping();

        $schedule->command('generate:invoice')->everyFiveMinutes()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
