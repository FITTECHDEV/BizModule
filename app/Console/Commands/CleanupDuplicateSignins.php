<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupDuplicateSignins extends Command
{
    protected $signature = 'azure:cleanup-duplicates {--dry-run : Preview changes without executing}';
    protected $description = 'Clean up duplicate Azure users keeping the most recent record';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        $this->info('Starting duplicate cleanup process...');

        // Find duplicates
        $duplicates = DB::table('azure_users')
            ->select('userPrincipalName', DB::raw('COUNT(*) as count'), DB::raw('GROUP_CONCAT(id) as ids'))
            ->groupBy('userPrincipalName')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicates found!');
            return 0;
        }

        $this->warn("Found {$duplicates->count()} users with duplicates");

        $totalRemoved = 0;

        foreach ($duplicates as $duplicate) {
            $this->info("Processing: {$duplicate->userPrincipalName} ({$duplicate->count} duplicates)");
            
            $userIds = explode(',', $duplicate->ids);
            
            // Get all records for this user
            $users = DB::table('azure_users')
                ->whereIn('id', $userIds)
                ->orderBy('updated_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            // Keep the first (most recent) record
            $keepRecord = $users->first();
            $removeRecords = $users->skip(1);

            $this->line("  → Keeping record ID: {$keepRecord->id} (updated: {$keepRecord->updated_at})");

            foreach ($removeRecords as $record) {
                $this->line("  → Removing record ID: {$record->id} (updated: {$record->updated_at})");
                
                if (!$isDryRun) {
                    DB::table('azure_users')->where('id', $record->id)->delete();
                }
                $totalRemoved++;
            }
        }

        if ($isDryRun) {
            $this->info("DRY RUN: Would remove {$totalRemoved} duplicate records");
        } else {
            $this->info("Successfully removed {$totalRemoved} duplicate records");
            Log::info("Cleanup completed: removed {$totalRemoved} duplicate Azure user records");
        }

        // Verify cleanup
        $remainingDuplicates = DB::table('azure_users')
            ->select('userPrincipalName', DB::raw('COUNT(*) as count'))
            ->groupBy('userPrincipalName')
            ->having('count', '>', 1)
            ->count();

        if ($remainingDuplicates > 0 && !$isDryRun) {
            $this->error("Warning: {$remainingDuplicates} users still have duplicates");
        } else if (!$isDryRun) {
            $this->info('✓ All duplicates cleaned up successfully');
        }

        return 0;
    }
}