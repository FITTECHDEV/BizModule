<?php
// UpdateUserLoginStatus.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Logintracker\AzureUser;
use App\Models\Logintracker\SigninLog;
use Illuminate\Support\Facades\DB;

class UpdateUserLoginStatus extends Command
{
    protected $signature = 'users:update-login-status {--days=30 : Number of days to check for recent logins}';
    protected $description = 'Check and report user login activity without database updates';

    public function handle()
    {
        $days = $this->option('days');
        $this->info("Checking user login status based on activity in the last {$days} days...");

        // Get the date threshold
        $dateThreshold = now()->subDays($days);

        // Get users with recent logins
        $usersWithRecentLogins = DB::table('Azure_Users')
            ->whereExists(function ($query) use ($dateThreshold) {
                $query->select(DB::raw(1))
                    ->from('Azure_LoginDetails')
                    ->whereColumn('Azure_LoginDetails.user_id', 'Azure_Users.id')
                    ->where('Azure_LoginDetails.status', 'Success')
                    ->where('Azure_LoginDetails.date_utc', '>=', $dateThreshold);
            })
            ->get(['id', 'userPrincipalName', 'displayName']);

        $this->info("Found {$usersWithRecentLogins->count()} users with recent successful logins.");

        // Display users with recent activity
        if ($usersWithRecentLogins->count() > 0) {
            $this->info("\n📊 Users with recent login activity:");
            foreach ($usersWithRecentLogins as $user) {
                $this->line("✅ {$user->displayName} ({$user->userPrincipalName})");
            }
        }

        // Get total user count
        $totalUsers = DB::table('Azure_Users')->count();
        $inactiveUsers = $totalUsers - $usersWithRecentLogins->count();

        $this->info("\n📈 Summary:");
        $this->info("Total users: {$totalUsers}");
        $this->info("Active users (last {$days} days): {$usersWithRecentLogins->count()}");
        $this->info("Inactive users: {$inactiveUsers}");

        // Optional: Show most recent login for each active user
        if ($this->option('verbose') || $this->confirm('Show detailed login information?', false)) {
            $this->showDetailedLoginInfo($usersWithRecentLogins, $dateThreshold);
        }

        $this->info('✅ User login status check completed!');

        return Command::SUCCESS;
    }

    private function showDetailedLoginInfo($activeUsers, $dateThreshold)
    {
        $this->info("\n📋 Detailed login information:");

        foreach ($activeUsers as $user) {
            $latestLogin = DB::table('Azure_LoginDetails')
                ->where('user_id', $user->id)
                ->where('status', 'Success')
                ->where('date_utc', '>=', $dateThreshold)
                ->orderBy('date_utc', 'desc')
                ->first();

            if ($latestLogin) {
                $this->line("👤 {$user->displayName}: Last login on {$latestLogin->date_utc}");
            }
        }
    }
}
