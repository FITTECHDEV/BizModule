<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncScmDetails extends Command
{
    protected $signature = 'sync:scmdetails {date? : Date to sync in YYYY-MM-DD format (defaults to today)}';
    protected $description = 'Copy rows from SQL Server CRM_UpdateSCMDetails into Odoo scmdetails table';

    public function handle()
    {
        // Use provided date or default to today
        $dateInput = $this->argument('date');
        try {
            $syncDate = $dateInput
                ? Carbon::parse($dateInput)->toDateString()
                : Carbon::today()->toDateString();
        } catch (\Exception $e) {
            $this->error("❌ Invalid date format. Please use YYYY-MM-DD.");
            Log::error("SyncScmDetails: Invalid date format provided - {$dateInput}");
            return 1;
        }

        $this->info("Starting sync for {$syncDate}...");
        Log::info("SyncScmDetails: Starting sync for {$syncDate}");

        try {
            // Fetch rows from SQL Server for the specified date
            $rows = DB::connection('sqlsrv')->select("
                SELECT [Id],[Bookingno],[CRMid],[Action],[Value],[CreatedOn],[UpdatedOn],[Status]
                FROM [dbo].[CRM_UpdateSCMDetails]
                WHERE CAST([CreatedOn] AS DATE) = ?
                ORDER BY [CreatedOn] DESC
            ", [$syncDate]);

            if (empty($rows)) {
                $this->info("No rows found for {$syncDate}.");
                Log::info("SyncScmDetails: No rows found for {$syncDate}");
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

            $this->info("🎉 Sync complete for {$syncDate}: {$inserted} rows upserted, {$skipped} rows skipped.");
            Log::info("SyncScmDetails: Completed successfully for {$syncDate}. {$inserted} upserted, {$skipped} skipped.");

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            Log::error("SyncScmDetails: Failed with error for {$syncDate} - " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }
}
