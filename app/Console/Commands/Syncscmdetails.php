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
        $today = Carbon::today()->toDateString();
        $this->info("Fetching data for {$today} from SQL Server...");
        Log::info("SyncScmDetails: Starting sync for {$today}");

        try {
            // Fetch today's rows from SQL Server
            $rows = DB::connection('sqlsrv')->select("
                SELECT [Id],
                       [BookingIn],
                       [CRMid],
                       [Action],
                       [Value],
                       [CreatedOn],
                       [UpdatedOn],
                       [Status]
                FROM [FITSYSUAT].[dbo].[CRM_UpdateSCMDetails]
                WHERE CAST([CreatedOn] AS DATE) = CAST(GETDATE() AS DATE)
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

            foreach ($rows as $row) {
                $data = [];

                foreach ((array) $row as $key => $value) {
                    $pgKey = strtolower($key); // normalize to lowercase
                    if (in_array($pgKey, $pgColumns)) {
                        $data[$pgKey] = $value;
                    }
                }

                if (!empty($data)) {
                    // Debug log what’s being inserted
                    $this->line("Preparing insert for row ID: {$row->Id}");
                    $this->line("Mapped Data: " . json_encode($data));
                    Log::debug("SyncScmDetails: Mapped data for row {$row->Id}", $data);

                    // Insert into Postgres
                    DB::connection('pgsql')->table('scmdetails')->insert($data);

                    $inserted++;
                    $this->info("✅ Inserted row ID: {$row->Id}");
                    Log::info("SyncScmDetails: Inserted row ID {$row->Id}");
                } else {
                    $this->warn("⚠️ Skipped row ID {$row->Id}, no matching columns.");
                    Log::warning("SyncScmDetails: Skipped row ID {$row->Id}, no matching columns.");
                }
            }

            $this->info("🎉 Sync complete: {$inserted} rows copied.");
            Log::info("SyncScmDetails: Completed successfully. {$inserted} rows copied.");

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            Log::error("SyncScmDetails: Failed with error - " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }

        return 0;
    }
}
