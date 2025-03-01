<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CRMSCMIntegration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'int:Integration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command is used to create a booking in SCM ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $records = DB::table('CRM_SCMprofit_integration')
            ->whereNull('SCMBookingID')
            ->whereNotNull('SCMBookingXML')
            ->limit(100)
            ->get();

        foreach ($records as $record) {
            $this->pushXMLToSCM($record);
        }
    }

    /**
     * Get authentication token using cURL.
     *
     * @return string|null
     */
    private function getAuthToken()
    {
        $tokenUrl = env('SCM_TOKEN_URL');
        $username = env('SCM_USERNAME');
        $password = env('SCM_PASSWORD');

        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $tokenUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'password',
                'username' => $username,
                'password' => $password,
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            Log::error("cURL Error: $error");
            return null;
        }

        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    }

    /**
     * Push XML data to SCM API.
     */
    private function pushXMLToSCM($record)
    {
        $token = $this->getAuthToken();
        if (!$token) {
            Log::error("Failed to retrieve authentication token");
            return;
        }
        
        $apiUrl = env('SCM_BOOKING_URL');
        $booking_xml = $record->SCMBookingXML;
        
        // Validate XML
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($booking_xml);
        if (!$doc) {
            Log::error("Invalid XML format for record ID: {$record->bookingno}");
            DB::table('CRM_SCMprofit_integration')
                ->where('bookingno', $record->bookingno)
                ->update(['SCMINTStatus' => 'XML Error', 'UpdatedOninSCM' => now()]);
            return;
        }
        libxml_clear_errors();

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $booking_xml,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/xml; charset=utf-8',
                'Authorization: Bearer ' . $token,
            ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            Log::error("cURL Error: $error");
            DB::table('CRM_SCMprofit_integration')
                ->where('bookingno', $record->bookingno)
                ->update(['SCMINTStatus' => 'Failed', 'UpdatedOninSCM' => now()]);
            return;
        }

        $xmlResponse = simplexml_load_string($response, null, LIBXML_NOCDATA);
        $jsonResponse = json_encode($xmlResponse);
        $xmlArrayResponse = json_decode($jsonResponse, true);

        Log::info("SCM Response for Record ID: {$record->bookingno} - " . json_encode($xmlArrayResponse));

        if (isset($xmlArrayResponse['Transaction']['Status']) && $xmlArrayResponse['Transaction']['Status'] == 'Fail') {
            $errors = isset($xmlArrayResponse['Transaction']['Errors']['Error'])
                ? (is_array($xmlArrayResponse['Transaction']['Errors']['Error'])
                    ? implode(", ", array_column($xmlArrayResponse['Transaction']['Errors']['Error'], 'Description'))
                    : $xmlArrayResponse['Transaction']['Errors']['Error']['Description'])
                : 'Unknown error';
            
            Log::warning("XML Push Errors for Record bookingno: {$record->bookingno} - $errors");
            DB::table('CRM_SCMprofit_integration')
                ->where('bookingno', $record->bookingno)
                ->update(['SCMINTStatus' => 'Failed', 'UpdatedOninSCM' => now()]);
            return;
        }
        
        DB::table('CRM_SCMprofit_integration')
            ->where('bookingno', $record->bookingno)
            ->update([
                'SCMBookingID' => $xmlArrayResponse['Transaction']['TransactionId'] ?? null,
                'SCMBookingNo' => $xmlArrayResponse['Transaction']['ID'] ?? null,
                'SCMINTStatus' => 'Success',
                'UpdatedOninSCM' => now(),
            ]);
        
        Log::info("Successfully pushed XML for Record ID: {$record->bookingno}");
    }
}
