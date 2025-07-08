<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessCRMSCMBooking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crmscm:Booking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command creates bookings in SCM from CRM';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $records = DB::table('CRM_SCMprofit_integration')
            ->whereNull('SCMBookingID')
            ->whereNotNull('SCMBookingXML')
            ->limit(10) // Reduced limit to prevent hangs
            ->get();

        foreach ($records as $record) {
            $this->pushXMLToSCM($record);
            sleep(1); // Add delay to avoid overwhelming the API
        }
    }

    /**
     * Get authentication token using cURL.
     *
     * @param int $retries Number of retry attempts
     * @return string|null
     */
    private function getAuthToken(int $retries = 3): ?string
    {
        $tokenUrl = env('SCM_TOKEN_URL');
        $username = env('SCM_USERNAME');
        $password = env('SCM_PASSWORD');

        if (!$tokenUrl || !$username || !$password) {
            Log::error('Missing SCM_TOKEN_URL, SCM_USERNAME, or SCM_PASSWORD in .env');
            return null;
        }

        $attempt = 0;
        while ($attempt < $retries) {
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $tokenUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30, // Set timeout to prevent hangs
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
                Log::error("cURL Error (Attempt " . ($attempt + 1) . "): $error");
                $attempt++;
                sleep(2); // Wait before retrying
                continue;
            }

            $data = json_decode($response, true);
            if (isset($data['access_token'])) {
                return $data['access_token'];
            }

            Log::error("Failed to retrieve authentication token (Attempt " . ($attempt + 1) . ")");
            $attempt++;
            sleep(2);
        }

        Log::error("Failed to retrieve authentication token after $retries attempts");
        return null;
    }

    /**
     * Push XML data to SCM API.
     *
     * @param object $record
     * @return void
     */
    private function pushXMLToSCM(object $record): void
    {
        $token = $this->getAuthToken();
        if (!$token) {
            Log::error("Failed to retrieve authentication token for bookingno: {$record->bookingno}");
            return;
        }

        $apiUrl = env('SCM_BOOKING_URL');
        if (!$apiUrl) {
            Log::error('Missing SCM_BOOKING_URL in .env');
            return;
        }

        $booking_xml = $record->SCMBookingXML;

        // Validate XML
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($booking_xml);
        if (!$doc) {
            Log::error("Invalid XML format for record ID: {$record->bookingno}");
            DB::table('CRM_SCMprofit_integration')
                ->where('bookingno', $record->bookingno)
                ->update([
                    'SCMINTStatus' => 'XML Error',
                    'CreatedOnInSCM' => now(),
                    'UpdatedOninSCM' => now(),
                ]);
            return;
        }
        libxml_clear_errors();

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30, // Set timeout to prevent hangs
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
            Log::error("cURL Error for bookingno: {$record->bookingno} - $error");
            DB::table('CRM_SCMprofit_integration')
                ->where('bookingno', $record->bookingno)
                ->update([
                    'SCMINTStatus' => 'Failed',
                    'CreatedOnInSCM' => now(),
                    'UpdatedOninSCM' => now(),
                ]);
            return;
        }

        $xmlResponse = simplexml_load_string($response, null, LIBXML_NOCDATA);
        if (!$xmlResponse) {
            Log::error("Invalid XML response for bookingno: {$record->bookingno}");
            DB::table('CRM_SCMprofit_integration')
                ->where('bookingno', $record->bookingno)
                ->update([
                    'SCMINTStatus' => 'Invalid Response',
                    'CreatedOnInSCM' => now(),
                    'UpdatedOninSCM' => now(),
                ]);
            return;
        }

        $jsonResponse = json_encode($xmlResponse);
        $xmlArrayResponse = json_decode($jsonResponse, true);

        Log::info("SCM Response for Record ID: {$record->bookingno} - " . Str::limit(json_encode($xmlArrayResponse), 1000));

        if (isset($xmlArrayResponse['Transaction']['Status']) && $xmlArrayResponse['Transaction']['Status'] === 'Fail') {
            $errors = isset($xmlArrayResponse['Transaction']['Errors']['Error'])
                ? (is_array($xmlArrayResponse['Transaction']['Errors']['Error'])
                    ? implode(", ", array_column($xmlArrayResponse['Transaction']['Errors']['Error'], 'Description'))
                    : $xmlArrayResponse['Transaction']['Errors']['Error']['Description'])
                : 'Unknown error';

            Log::warning("XML Push Errors for Record bookingno: {$record->bookingno} - $errors");
            DB::table('CRM_SCMprofit_integration')
                ->where('bookingno', $record->bookingno)
                ->update([
                    'SCMINTStatus' => $errors,
                    'CreatedOnInSCM' => now(),
                    'UpdatedOninSCM' => now(),
                ]);
            return;
        }

        DB::transaction(function () use ($record, $xmlArrayResponse) {
            DB::table('CRM_SCMprofit_integration')
                ->where('bookingno', $record->bookingno)
                ->update([
                    'SCMBookingID' => $xmlArrayResponse['Transaction']['TransactionId'] ?? null,
                    'SCMBookingNo' => $xmlArrayResponse['Transaction']['ID'] ?? null,
                    'SCMINTStatus' => 'Success',
                    'CreatedOnInSCM' => now(),
                    'UpdatedOninSCM' => now(),
                ]);
        });

        Log::info("Successfully pushed XML for Record ID: {$record->bookingno}");
    }
}
