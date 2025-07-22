<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GenerateJsonReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * This is the command you’ll run in terminal, e.g. php artisan report:json
     */
    protected $signature = 'z_report_json_ar';


    /**
     * The console command description.
     */
    protected $description = 'Generates AR JSON report from AR API and SQL Server date';

    /**
     * Execute the console command.
     */
public function handle()
{
    $officeCode = 'AR';

    // Get today's or next unprocessed date
    $result = DB::select("
        DECLARE @ZreportDate AS DATE;
        EXEC dbo.TRA_ZReportDate :officeCode, @ZreportDate = @ZreportDate OUTPUT;
        SELECT @ZreportDate as ZreportDate;
    ", ['officeCode' => $officeCode]);

    if (empty($result) || $result[0]->ZreportDate === null) {
        $this->info("No ZReportDate to process today.");
        return;
    }

    $zReportDate = $result[0]->ZreportDate;
    $this->info("Processing ZReport for: {$zReportDate}");

    $apiUrl = "http://40.119.131.119:8084/api/v1/zreports?date=" . $zReportDate;

    $response = Http::withBasicAuth('admin', 'admin')->timeout(120)->get($apiUrl);

    $statusCode = $response->status();
    $jsonData = $response->body();

    DB::statement("
        EXEC dbo.TRA_JSON_Convertor :reportDate, :officeCode, :json
    ", [
        'reportDate' => $zReportDate,
        'officeCode' => $officeCode,
        'json'       => $jsonData
    ]);

    $this->info("Report for {$zReportDate} processed successfully.");
}



}
