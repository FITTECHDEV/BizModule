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
    //     // acCommands\CloseTickets::class,
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
        // Ensure logs directory exists
        $this->ensureLogDirectoryExists();

        // $schedule->command('inspire')->hourly();
        $schedule->command('TIMS:UploadExpInvAzure')
            ->everyMinute()
            ->withoutOverlapping();

        // $schedule->command('remind:assigned')->everyMinute()->between('8:00', '17:00');
        $schedule->job('App\Jobs\RemindAssignedTickets')
            ->between('8:30', '17:00')
            ->everyFourHours()
            ->withoutOverlapping();

        $schedule->command('emails:sendApprovalReminder')
            ->between('8:30', '17:00')
            ->withoutOverlapping();

        $schedule->command('generate:invoice')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('tra_adl:invoices')
            ->everyFiveMinutes()
            ->withoutOverlapping();

        $schedule->command('tra_tz:invoices')
            ->everyFiveMinutes()
            ->withoutOverlapping();

        // $schedule->command('sync:scmprofit')
        //     ->everyTenMinutes()
        //     ->withoutOverlapping()
        //     ->appendOutputTo(storage_path('logs/scm-sync.log'))
        //     ->emailOutputOnFailure('admin@yourcompany.com'); // Optional: add your email

        $schedule->command('crmscm:Booking')
            ->everyThirtyMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/crmscm.log'))
            ->onSuccess(function () {
                Log::info('CRMSCM: Scheduled task completed successfully');
            })
            ->onFailure(function () {
                Log::error('CRMSCM: Scheduled task failed');
            });



        // ->emailOutputOnFailure('admin@yourcompany.com'); // Optional: add your email

        $schedule->command('z_report_json_tz')
            ->dailyAt('10:00')
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('z_report_json_ar')
            ->dailyAt('10:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Sync Odoo users first (pulls user data from Odoo PostgreSQL)
        $schedule->command('sync:odoo-users --force')
            ->dailyAt('02:00')  // 2:00 AM
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/odoo-user-sync.log'))
            ->onSuccess(function () {
                Log::info('Odoo User Sync: Scheduled task completed successfully');
            })
            ->onFailure(function () {
                Log::error('Odoo User Sync: Scheduled task failed');
            });

        // Then activate pending users (30 minutes after Odoo sync)
        $schedule->command('azure:activate-pending-users --force')
            ->dailyAt('02:30')  // 2:30 AM
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/user-activation.log'))
            ->onSuccess(function () {
                Log::info('User Activation: Scheduled task completed successfully');
            })
            ->onFailure(function () {
                Log::error('User Activation: Scheduled task failed');
            });
    }

    /**
     * Ensure logs directory exists and is writable
     */
    private function ensureLogDirectoryExists()
    {
        $logPath = storage_path('logs');

        if (!file_exists($logPath)) {
            mkdir($logPath, 0755, true);
        }

        // Create the specific log files if they don't exist
        $logFiles = [
            storage_path('logs/crmscm.log'),
            storage_path('logs/scm-sync.log')
        ];

        foreach ($logFiles as $logFile) {
            if (!file_exists($logFile)) {
                touch($logFile);
                chmod($logFile, 0644);
            }
        }
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
