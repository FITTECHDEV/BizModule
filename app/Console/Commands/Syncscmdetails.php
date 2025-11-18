<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncScmDetails extends Command
{
   protected $signature = 'sync:scmdetails {date? : Date in YYYY-MM-DD format} {--all : Sync all records}';

protected $description = 'Copy rows from SQL Server CRM_UpdateSCMDetails into Odoo scmdetails table';

    public function handle()
    {
        $isAll = $this->option('all');

        if ($isAll) {
            $this->info("Starting full sync (all records)...");
            Log::info("SyncScmDetails: Starting full sync (all records)");
            $query = "
                SELECT [Id],[Bookingno],[CRMid],[Action],[Value],[CreatedOn],[UpdatedOn],[Status]
                FROM [dbo].[CRM_UpdateSCMDetails]
                ORDER BY [CreatedOn] DESC
            ";
            $params = [];
        } else {
            // Only process the date if --all was NOT used
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

            $query = "
                SELECT [Id],[Bookingno],[CRMid],[Action],[Value],[CreatedOn],[UpdatedOn],[Status]
                FROM [dbo].[CRM_UpdateSCMDetails]
                WHERE CAST([CreatedOn] AS DATE) = ?
                ORDER BY [CreatedOn] DESC
            ";
            $params = [$syncDate];
        }

        try {
            $rows = DB::connection('sqlsrv')->select($query, $params);

            if (empty($rows)) {
                $msg = $isAll ? "No rows found in source table." : "No rows found for {$syncDate}.";
                $this->info($msg);
                Log::info("SyncScmDetails: {$msg}");
                return 0;
            }

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

                try {
                    DB::connection('pgsql')
                        ->table('scmdetails')
                        ->upsert(
                            $data,
                            ['id'],
                            array_keys($data)
                        );

                    $inserted++;
                    $this->info("✅ Upserted row ID: {$row->Id}");
                    Log::info("SyncScmDetails: Upserted row {$row->Id}", $data);
                } catch (\Exception $e) {
                    $this->warn("⚠️ Failed row ID {$row->Id}: " . $e->getMessage());
                    Log::warning("SyncScmDetails: Failed row ID {$row->Id}: " . $e->getMessage());
                    $skipped++;
                }
            }

            $summary = $isAll
                ? "🎉 Full sync complete: {$inserted} rows upserted, {$skipped} skipped."
                : "🎉 Sync complete for {$syncDate}: {$inserted} rows upserted, {$skipped} skipped.";

            $this->info($summary);
            Log::info("SyncScmDetails: {$summary}");
        } catch (\Exception $e) {
            $errorContext = isset($syncDate) ? "for {$syncDate}" : "for full sync";
            $this->error("❌ Error: " . $e->getMessage());
            Log::error("SyncScmDetails: Failed with error {$errorContext} - " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }
}
