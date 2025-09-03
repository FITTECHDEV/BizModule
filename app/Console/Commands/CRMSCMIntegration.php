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
    protected $signature = 'int:Integration';
    protected $description = 'This Command is used to create a booking in SCM';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Start time tracking for the entire process
        $startTime = microtime(true);

        // Get count of pending records before processing
        $pendingCount = DB::table('CRM_SCMprofit_integration')
            ->whereNull('SCMBookingID')
            ->whereNotNull('SCMBookingXML')
             ->whereNull('SCMINTStatus')
            ->count();

        Log::info('Starting CRM-SCM Integration', [
            'pending_records' => $pendingCount,
            'start_time' => now()->toDateTimeString()
        ]);

        $records = DB::table('CRM_SCMprofit_integration')
            ->whereNull('SCMBookingID')
            ->whereNotNull('SCMBookingXML')
            ->limit(100)
            ->get();

        $processedCount = 0;
        $successCount = 0;
        $failedCount = 0;

        foreach ($records as $record) {
            $recordStartTime = microtime(true);
            $this->pushXMLToSCM($record);
            $recordExecutionTime = microtime(true) - $recordStartTime;

            $processedCount++;
            $status = DB::table('CRM_SCMprofit_integration')
                ->where('bookingno', $record->bookingno)
                ->first()->SCMINTStatus;

            if ($status === 'Success') {
                $successCount++;
            } else {
                $failedCount++;
            }

            Log::info('Processed record', [
                'bookingno' => $record->bookingno,
                'status' => $status,
                'execution_time_seconds' => round($recordExecutionTime, 3)
            ]);
        }

        $totalExecutionTime = microtime(true) - $startTime;
        $remainingPending = DB::table('CRM_SCMprofit_integration')
            ->whereNull('SCMBookingID')
            ->whereNotNull('SCMBookingXML')
            ->count();

        Log::info('Completed CRM-SCM Integration', [
            'total_processed' => $processedCount,
            'successful' => $successCount,
            'failed' => $failedCount,
            'remaining_pending' => $remainingPending,
            'total_execution_time_seconds' => round($totalExecutionTime, 3),
            'end_time' => now()->toDateTimeString()
        ]);
    }

    private function getAuthToken()
    {
        $tokenStartTime = microtime(true);
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

        $executionTime = microtime(true) - $tokenStartTime;

        if ($error) {
            Log::error('Failed to get auth token', [
                'error' => $error,
                'execution_time_seconds' => round($executionTime, 3)
            ]);
            return null;
        }

        $data = json_decode($response, true);
        $token = $data['access_token'] ?? null;

        Log::info('Retrieved auth token', [
            'success' => !is_null($token),
            'execution_time_seconds' => round($executionTime, 3)
        ]);

        return $token;
    }

    private function pushXMLToSCM($record)
    {
        $startTime = microtime(true);
        $token = $this->getAuthToken();

        if (!$token) {
            Log::error('Skipping record push due to auth token failure', [
                'bookingno' => $record->bookingno,
                'execution_time_seconds' => round(microtime(true) - $startTime, 3)
            ]);
            return;
        }

        $apiUrl = env('SCM_BOOKING_URL');
        $booking_xml = $record->SCMBookingXML;

        // Validate XML
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($booking_xml);
        if (!$doc) {
            $errors = libxml_get_errors();
            Log::error('Invalid XML format', [
                'bookingno' => $record->bookingno,
                'errors' => array_map(function($error) {
                    return $error->message;
                }, $errors),
                'execution_time_seconds' => round(microtime(true) - $startTime, 3)
            ]);
            DB::table('CRM_SCMprofit_integration')
                ->where('bookingno', $record->bookingno)
                ->update(['SCMINTStatus' => 'XML Error', 'UpdatedOninSCM' => now()]);
            libxml_clear_errors();
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
            Log::error('SCM API push failed', [
                'bookingno' => $record->bookingno,
                'error' => $error,
                'execution_time_seconds' => round(microtime(true) - $startTime, 3)
            ]);
            DB::table('CRM_SCMprofit_integration')
                ->where('bookingno', $record->bookingno)
                ->update(['SCMINTStatus' => 'Failed', 'UpdatedOninSCM' => now()]);
            return;
        }

        $xmlResponse = simplexml_load_string($response, null, LIBXML_NOCDATA);
        $jsonResponse = json_encode($xmlResponse);
        $xmlArrayResponse = json_decode($jsonResponse, true);

        Log::info('SCM API Response', [
            'bookingno' => $record->bookingno,
            'response' => $xmlArrayResponse,
            'execution_time_seconds' => round(microtime(true) - $startTime, 3)
        ]);

        if (isset($xmlArrayResponse['Transaction']['Status']) && $xmlArrayResponse['Transaction']['Status'] == 'Fail') {
            $errors = isset($xmlArrayResponse['Transaction']['Errors']['Error'])
                ? (is_array($xmlArrayResponse['Transaction']['Errors']['Error'])
                    ? implode(", ", array_column($xmlArrayResponse['Transaction']['Errors']['Error'], 'Description'))
                    : $xmlArrayResponse['Transaction']['Errors']['Error']['Description'])
                : 'Unknown error';

            Log::warning('SCM API push errors', [
                'bookingno' => $record->bookingno,
                'errors' => $errors,
                'execution_time_seconds' => round(microtime(true) - $startTime, 3)
            ]);
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

        Log::info('Successfully pushed XML to SCM', [
            'bookingno' => $record->bookingno,
            'execution_time_seconds' => round(microtime(true) - $startTime, 3)
        ]);
    }
}
