<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncScmDetails extends Command
{
    protected $signature = 'sync:scmdetails';
    protected $description = 'Copy today\'s rows from SQL Server CRM_UpdateSCMDetails into Odoo scmdetails table';

public function handle()
{
    set_time_limit(300); // 5 minutes
    ini_set('memory_limit', '512M'); // 512 MB

    $today = Carbon::today()->toDateString();
    $this->info("Fetching data for {$today} from SQL Server...");
    Log::info("SyncScmDetails: Starting sync for {$today}");

    try {
        // Fetch top 10 rows from SQL Server
        $rows = DB::connection('sqlsrv')->select("
            SELECT TOP 10
                   [Id],
                   [BookingIn],
                   [CRMid],
                   [Action],
                   [Value],
                   [CreatedOn],
                   [UpdatedOn],
                   [Status]
            FROM [FITSYSUAT].[dbo].[CRM_UpdateSCMDetails]
            ORDER BY [CreatedOn] DESC
        ");

        $count = count($rows);
        $this->info("Found {$count} rows in SQL Server.");
        Log::info("SyncScmDetails: Found {$count} rows in SQL Server.");

        if ($count === 0) {
            return 0;
        }

        // Get actual column names from Postgres table
        $columns = DB::connection('pgsql')->select("
            SELECT column_name
            FROM information_schema.columns
            WHERE table_name = 'scmdetails'
        ");
        $pgColumns = collect($columns)
            ->pluck('column_name')
            ->map(fn($c) => strtolower($c))
            ->toArray();

        $inserted = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $data = [];

            foreach ((array) $row as $key => $value) {
                $pgKey = strtolower($key); // normalize to lowercase
                if (in_array($pgKey, $pgColumns)) {
                    if ($pgKey === 'value') {
                        $data[$pgKey] = (float) $value; // Cast to float
                    } elseif ($pgKey === 'crmid') {
                        $data[$pgKey] = (int) $value; // Cast to integer
                    } else {
                        $data[$pgKey] = $value;
                    }
                }
            }

            if (!empty($data)) {
                // Debug log what’s being inserted
                $this->line("Preparing insert for row ID: {$row->Id}");
                $this->line("Mapped Data: " . json_encode($data));
                Log::debug("SyncScmDetails: Mapped data for row {$row->Id}", $data);

                // Log before checking existence
                Log::debug("SyncScmDetails: Checking if row ID {$row->Id} exists in scmdetails...");
                $startTime = microtime(true);

                // Check if the ID already exists in the Postgres table
                try {
                    if (DB::connection('pgsql')->table('scmdetails')->where('id', $data['id'])->exists()) {
                        $this->warn("⚠️ Skipped row ID {$row->Id}, already exists in scmdetails.");
                        Log::warning("SyncScmDetails: Skipped row ID {$row->Id}, already exists in scmdetails.");
                        $skipped++;
                        continue;
                    }
                } catch (\Exception $e) {
                    $this->warn("⚠️ Skipped row ID {$row->Id} due to exists check error: " . $e->getMessage());
                    Log::warning("SyncScmDetails: Skipped row ID {$row->Id} due to exists check error: " . $e->getMessage());
                    $skipped++;
                    continue;
                }

                // Log time taken for exists check
                $existsTime = microtime(true) - $startTime;
                Log::debug("SyncScmDetails: Exists check for row ID {$row->Id} took {$existsTime} seconds.");

                // Log before insert
                Log::debug("SyncScmDetails: Inserting row ID {$row->Id} into scmdetails...");
                $startTime = microtime(true);

                // Enable query logging
                DB::connection('pgsql')->enableQueryLog();

                // Use transaction with timeout
                try {
                    $result = DB::connection('pgsql')->transaction(function () use ($data) {
                        return DB::connection('pgsql')->table('scmdetails')->insertOrIgnore($data);
                    }, 5, 30); // 5 retries, 30-second timeout

                    // Log executed queries
                    Log::debug("SyncScmDetails: Executed queries for row ID {$row->Id}", DB::connection('pgsql')->getQueryLog());

                    // Log time taken for insert
                    $insertTime = microtime(true) - $startTime;
                    Log::debug("SyncScmDetails: InsertOrIgnore for row ID {$row->Id} took {$insertTime} seconds, affected rows: {$result}");

                    if ($result) {
                        $inserted++;
                        $this->info("✅ Inserted row ID: {$row->Id}");
                        Log::info("SyncScmDetails: Inserted row ID {$row->Id}");
                    } else {
                        $skipped++;
                        $this->warn("⚠️ Skipped row ID {$row->Id}, likely a constraint violation.");
                        Log::warning("SyncScmDetails: Skipped row ID {$row->Id}, likely a constraint violation.");
                    }
                } catch (\Exception $e) {
                    $this->warn("⚠️ Skipped row ID {$row->Id} due to insert error: " . $e->getMessage());
                    Log::warning("SyncScmDetails: Skipped row ID {$row->Id} due to insert error: " . $e->getMessage());
                    $skipped++;
                    continue;
                } finally {
                    DB::connection('pgsql')->disableQueryLog();
                }
            } else {
                $this->warn("⚠️ Skipped row ID {$row->Id}, no matching columns.");
                Log::warning("SyncScmDetails: Skipped row ID {$row->Id}, no matching columns.");
                $skipped++;
            }
        }

        $this->info("🎉 Sync complete: {$inserted} rows copied, {$skipped} rows skipped.");
        Log::info("SyncScmDetails: Completed successfully. {$inserted} rows copied, {$skipped} rows skipped.");

    } catch (\Exception $e) {
        $this->error("❌ Error: " . $e->getMessage());
        Log::error("SyncScmDetails: Failed with error - " . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
    }

    return 0;
}


}
