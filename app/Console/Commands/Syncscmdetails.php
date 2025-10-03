<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncScmDetails extends Command
{
    protected $signature = 'sync:scmdetails';
    protected $description = 'Copy rows from SQL Server CRM_UpdateSCMDetails into Odoo scmdetails table';

    public function handle()
    {
        $today = Carbon::today()->toDateString();
        $this->info("Starting sync for {$today}...");
        Log::info("SyncScmDetails: Starting sync for {$today}");

        try {
            // Fetch rows from SQL Server (only today's records)
            $rows = DB::connection('sqlsrv')->select("
    SELECT [Id],[Bookingno],[CRMid],[Action],[Value],[CreatedOn],[UpdatedOn],[Status]
    FROM [dbo].[CRM_UpdateSCMDetails]
    WHERE CAST([CreatedOn] AS DATE) = CAST(GETDATE() AS DATE)
    ORDER BY [CreatedOn] DESC
");


            if (empty($rows)) {
                $this->info("No rows found for today.");
                return 0;
            }

            // Postgres column list
            $pgColumns = collect(
                DB::connection('pgsql')->select("
                    SELECT column_name FROM information_schema.columns
                    WHERE table_name = 'scmdetails'
                ")
            )->pluck('column_name')->map(fn($c) => strtolower($c))->toArray();

            $inserted = 0;
            $skipped = 0;

            foreach ($rows as $row) {
                $data = [];

                // Map SQL Server row to Postgres columns
                foreach ((array) $row as $key => $value) {
                    $col = strtolower($key);
                    if (!in_array($col, $pgColumns)) continue;

                    if ($col === 'value') {
                        $data[$col] = (float) $value;
                    } elseif ($col === 'crmid') {
                        $data[$col] = (int) $value;
                    } else {
                        $data[$col] = $value;
                    }
                }

                if (empty($data)) {
                    $skipped++;
                    continue;
                }

                // Upsert into Postgres
                try {
                    DB::connection('pgsql')
                        ->table('scmdetails')
                        ->upsert(
                            $data,
                            ['id'], // conflict column
                            array_keys($data) // update these columns on conflict
                        );

                    $inserted++;
                    $jsonData = json_encode($data, JSON_PRETTY_PRINT);
                    $this->info("✅ Upserted row ID: {$row->Id}");
                    $this->line("   ↳ Columns: " . implode(', ', array_keys($data)));
                    Log::info("SyncScmDetails: Upserted row {$row->Id} with data", $data);

                } catch (\Exception $e) {
                    $this->warn("⚠️ Failed row ID {$row->Id}: " . $e->getMessage());
                    Log::warning("SyncScmDetails: Failed row ID {$row->Id}: " . $e->getMessage());
                    $skipped++;
                }
            }

            $this->info("🎉 Sync complete: {$inserted} rows upserted, {$skipped} rows skipped.");
            Log::info("SyncScmDetails: Completed successfully. {$inserted} upserted, {$skipped} skipped.");

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            Log::error("SyncScmDetails: Failed with error - " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }

        return 0;
    }
}
