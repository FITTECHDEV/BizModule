<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GenerateJsonReport extends Command
{
    protected $signature = 'z_report_json_tz {date?} {--backfill=}';
    protected $description = 'Generates TZ JSON report from AR API and SQL Server data (single date, auto, or backfill)';

    public function handle()
    {
        $officeCode = 'TZ';
        $inputDate  = $this->argument('date');
        $backfillStart = $this->option('backfill');

        try {
            $dates = [];

            if ($backfillStart !== null) {
                // Backfill mode
                $startDate = $backfillStart ? Carbon::parse($backfillStart) : Carbon::parse('2025-03-02');
                $endDate   = Carbon::yesterday();

                while ($startDate->lte($endDate)) {
                    $dates[] = $startDate->toDateString();
                    $startDate->addDay();
                }

                $this->info("Backfill mode: processing from {$dates[0]} to {$endDate->toDateString()}");
                Log::info("TZ ZReport backfill started", [
                    'range_start' => $dates[0],
                    'range_end'   => $endDate->toDateString()
                ]);
            } elseif ($inputDate) {
                // Manual single date
                $dates[] = $inputDate;
                $this->info("Manual mode: processing {$inputDate}");
                Log::info("TZ ZReport manual mode", ['date' => $inputDate]);
            } else {
                // Automatic mode: use stored procedure
                $result = DB::select("
                    SET NOCOUNT ON;
                    DECLARE @ZreportDate AS DATE;
                    EXEC dbo.TRA_ZReportDate :officeCode, @ZreportDate = @ZreportDate OUTPUT;
                    SELECT @ZreportDate as ZreportDate;
                ", ['officeCode' => $officeCode]);

                if (empty($result) || $result[0]->ZreportDate === null) {
                    $this->info("No ZReportDate to process today.");
                    Log::info("TZ ZReport skipped: no date from SP.");
                    return;
                }

                $dates[] = $result[0]->ZreportDate;
                $this->info("Auto mode: processing {$result[0]->ZreportDate}");
                Log::info("TZ ZReport auto mode", ['date' => $result[0]->ZreportDate]);
            }

            // Process each date
            foreach ($dates as $zReportDate) {
                $this->processDate($zReportDate, $officeCode);
            }

            $this->info("All reports processed.");
            Log::info("TZ ZReport command finished", [
                'mode' => $backfillStart !== null ? 'backfill' : 'normal'
            ]);
        } catch (\Exception $e) {
            Log::error('TZ ZReport generation failed', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
                'stack'   => $e->getTraceAsString()
            ]);

            $this->error("Failed to process ZReport: " . $e->getMessage());
        }
    }

    private function processDate($zReportDate, $officeCode)
    {
        $apiUrl = "http://13.69.28.214:8888/api/v1/zreports?date={$zReportDate}";

        $this->info("Calling API for {$zReportDate}");
        Log::info("Calling TZ ZReport API", ['date' => $zReportDate]);

        try {
            $response = Http::withBasicAuth('admin', 'aDmiN@2024')
                ->timeout(120)
                ->get($apiUrl);

            $statusCode = $response->status();
            Log::info("API response received", [
                'date' => $zReportDate,
                'status_code' => $statusCode
            ]);

            if (!$response->ok()) {
                Log::error("Failed to fetch report from API", [
                    'date' => $zReportDate,
                    'status_code' => $statusCode,
                    'body' => $response->body()
                ]);
                $this->error("API request failed for {$zReportDate} (Status: $statusCode)");
                return;
            }

            $jsonData = $response->body();

            // Run stored procedure
            DB::statement("
            EXEC dbo.TRA_JSON_Convertor :reportDate, :officeCode, :json
        ", [
                'reportDate' => $zReportDate,
                'officeCode' => $officeCode,
                'json'       => $jsonData
            ]);

            $this->info("Report for {$zReportDate} processed successfully.");
            Log::info("TZ ZReport processed successfully", ['date' => $zReportDate]);
        } catch (\Throwable $e) {
            Log::error("API call failed", [
                'date' => $zReportDate,
                'message' => $e->getMessage()
            ]);
            $this->error("Failed API call for {$zReportDate}: " . $e->getMessage());
        }
    }
}
