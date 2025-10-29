<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Logintracker\AzureUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FixNullUserIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:fix-null-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix users with null IDs by assigning UUIDs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to fix users with null IDs...');

        // Find all users with null or empty IDs
        $usersWithNullIds = DB::table('Azure_Users')
            ->whereNull('id')
            ->orWhere('id', '')
            ->get();
        
        $this->info("Found {$usersWithNullIds->count()} users with null/empty IDs");

        if ($usersWithNullIds->isEmpty()) {
            $this->info('No users with null IDs found.');
            return;
        }

        $this->info('Assigning new UUIDs to users...');

        DB::transaction(function () use ($usersWithNullIds) {
            foreach ($usersWithNullIds as $user) {
                $newUuid = (string) Str::uuid();
                
                $this->line("Processing: {$user->userPrincipalName} -> {$newUuid}");

                // Update the user with a new UUID
                DB::table('Azure_Users')
                    ->where('userPrincipalName', $user->userPrincipalName)
                    ->whereNull('id')
                    ->update(['id' => $newUuid]);

                $this->line("✓ Fixed: {$user->userPrincipalName}");
            }
        });

        $this->info('✅ All users with null IDs have been fixed!');
        
        // Verify the fix
        $remainingNullIds = DB::table('Azure_Users')
            ->whereNull('id')
            ->orWhere('id', '')
            ->count();
            
        if ($remainingNullIds === 0) {
            $this->info('✅ Verification passed: No users with null IDs remain.');
        } else {
            $this->error("❌ {$remainingNullIds} users still have null IDs.");
        }
    }
}