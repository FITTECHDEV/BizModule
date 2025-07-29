<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateJsonReport extends Command
{
    protected $signature = 'z_report_json_ar {date?}';
    protected $description = 'Generates AR JSON report from AR API and SQL Server data';

    public function handle()
    {
        $officeCode = 'TZ';
        $inputDate = $this->argument('date');

        try {
            if ($inputDate) {
                $zReportDate = $inputDate;
                $this->info("Processing ZReport for provided date: {$zReportDate}");
                Log::info('ZReport command manually triggered', [
                    'date' => $zReportDate,
                    'source' => 'manual'
                ]);
            } else {
                // Get today's or next unprocessed date from SQL
                $result = DB::select("
                    DECLARE @ZreportDate AS DATE;
                    EXEC dbo.TRA_ZReportDate :officeCode, @ZreportDate = @ZreportDate OUTPUT;
                    SELECT @ZreportDate as ZreportDate;
                ", ['officeCode' => $officeCode]);

                if (empty($result) || $result[0]->ZreportDate === null) {
                    $this->info("No ZReportDate to process today.");
                    Log::info("ZReportDate skipped: no date to process.");
                    return;
                }

                $zReportDate = $result[0]->ZreportDate;
                $this->info("Processing ZReport for: {$zReportDate}");
                Log::info('ZReport command auto-triggered', [
                    'date' => $zReportDate,
                    'source' => 'auto'
                ]);
            }

            // API call
            $apiUrl = "http://13.69.28.214:8888/api/v1/zreports?date=" . $zReportDate;

            $this->info("Calling API: $apiUrl");
            Log::info("Calling AR ZReport API", ['url' => $apiUrl]);

            $response = Http::withBasicAuth('admin', 'aDmiN@2024')
                ->timeout(120)
                ->get($apiUrl);

            $statusCode = $response->status();
            Log::info("API response received", ['status_code' => $statusCode]);

            if (!$response->ok()) {
                Log::error("Failed to fetch report from API", [
                    'status_code' => $statusCode,
                    'body' => $response->body()
                ]);
                $this->error("API request failed. Status: $statusCode");
                return;
            }

            $jsonData = $response->body();

            // Run the stored procedure
            DB::statement("
                EXEC dbo.TRA_JSON_Convertor :reportDate, :officeCode, :json
            ", [
                'reportDate' => $zReportDate,
                'officeCode' => $officeCode,
                'json'       => $jsonData
            ]);

            $this->info("Report for {$zReportDate} processed successfully.");
            Log::info("ZReport processed successfully", ['date' => $zReportDate]);

        } catch (\Exception $e) {
            Log::error('ZReport generation failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'stack' => $e->getTraceAsString()
            ]);

            $this->error("Failed to process ZReport: " . $e->getMessage());
        }
    }
}
