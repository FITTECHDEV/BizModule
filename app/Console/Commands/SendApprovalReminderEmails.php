<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendApprovalReminderEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:sendApprovalReminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails to users with specific permissions based on count value';

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
     */
    public function handle()
    {
        Log::notice('Email Reminders Running');
        $finance_count = $this->getFinanceCountValue();
        $officeCounts = $this->getManagerCountValue();

        $manager_users = User::permission('approve-entity-request')->get();

        if ($finance_count > 5) {
            $finance_users = User::permission('create-client-party-SCM')->get();

            foreach ($finance_users as $finance_user) {
                Mail::send('emails.entity-reminder', ['username' => $finance_user->FirstName, 'count' => $finance_count], function ($message) use ($finance_user) {
                    $message->to($finance_user->Email)->subject('Reminder');
                });
            }
        }

        foreach ($officeCounts as $officeID => $count) {
            if ($count > 5) {
                foreach ($manager_users as $manager) {
                    if ($manager->OfficeID == $officeID) {
                        // Send email to the manager
                        Mail::send('emails.entity-reminder', ['username' => $manager->FirstName, 'count' => $count], function ($message) use ($manager) {
                            $message->to($manager->Email)->subject('Reminder');
                        });
                    }
                }
            }
        }

        Log::info('Scheduled test task ran successfully.');

        return 0;
    }

    private function getFinanceCountValue()
    {
        $submitted = DB::table('Cust_Entity')->where('Status', 'Submitted');
        $submitted_count = $submitted->count();

        return $submitted_count; // Submitted count value
    }


    public function getManagerCountValue()
    {
        $pending = DB::table('Cust_Entity')->where('Status', 'PendingManagerApproval')->get();
        $manager_users = User::permission('approve-entity-request')->get();

        $officeCounts = [];
        $officeIDs = [];

        foreach ($manager_users as $manager) {
            if (!in_array($manager->OfficeID, $officeIDs)) {
                $officeID = $manager->OfficeID;
                $officeIDs[] = $officeID;

                $count = $pending->where('OfficeID', $officeID)->count();
                $officeCounts[$officeID] = $count;
            }
        }

        return $officeCounts;
    }

}
