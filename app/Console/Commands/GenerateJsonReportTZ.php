<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Storage;

class GenerateJsonReportTZ extends Command
{
    /**
     * The name and signature of the console command.
     *
     * This is the command you’ll run in terminal, e.g. php artisan report:json
     */
    protected $signature = 'z_report_json_tz';


    /**
     * The console command description.
     */
    protected $description = 'Generates TZ JSON report from TZ API and SQL Server date';

    /**
     * Execute the console command.
     */

     //   $apiUrl = "http://13.69.28.214:8888/api/v1/zreports?date=" . $zReportDate;

public function handle()
{
    $officeCode = 'TZ';

    Log::info("ZReport Command Started for {$officeCode}");

    $result = DB::select("
        DECLARE @ZreportDate AS DATE;
        EXEC dbo.TRA_ZReportDate :officeCode, @ZreportDate = @ZreportDate OUTPUT;
        SELECT @ZreportDate as ZreportDate;
    ", ['officeCode' => $officeCode]);

    if (empty($result) || $result[0]->ZreportDate === null) {
        $message = "No ZReportDate to process for {$officeCode}.";
        $this->info($message);
        Log::info($message);
        return;
    }

    $zReportDate = $result[0]->ZreportDate;
    $this->info("Processing ZReport for: {$zReportDate}");
    \Log::info("Processing ZReport for: {$zReportDate} ({$officeCode})");

    $apiUrl = "http://40.119.131.119:8084/api/v1/zreports?date=" . $zReportDate;

    $response = Http::withBasicAuth('admin', 'admin')->timeout(120)->get($apiUrl);

    $statusCode = $response->status();
    Log::info("API HTTP Status for {$zReportDate}: {$statusCode}");

    $jsonData = $response->body();

    DB::statement("
        EXEC dbo.TRA_JSON_Convertor :reportDate, :officeCode, :json
    ", [
        'reportDate' => $zReportDate,
        'officeCode' => $officeCode,
        'json'       => $jsonData
    ]);

    $this->info("Report for {$zReportDate} processed successfully.");
    Log::info("ZReport processed and sent to SP for {$zReportDate} ({$officeCode}).");

    Log::info("ZReport Command Finished for {$officeCode}");
}



}
