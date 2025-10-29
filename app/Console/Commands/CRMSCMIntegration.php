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
    protected $signature = 'int:Integration {--booking= : Process specific booking number}';
    protected $description = 'This Command is used to create a booking in SCM';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Start time tracking for the entire process
        $startTime = microtime(true);
        $specificBooking = $this->option('booking');

        // Validate booking number format
        if ($specificBooking && !preg_match('/^[A-Za-z0-9_-]+$/', $specificBooking)) {
            $this->error("Invalid booking number format: {$specificBooking}");
            Log::error('CRMSCM: Invalid booking number format', ['bookingno' => $specificBooking]);
            return 1;
        }

        // Get count of pending records before processing
        $query = DB::table('CRM_SCMprofit_integration')
            ->whereNull('SCMBookingID')
            ->whereNotNull('SCMBookingXML')
            ->whereRaw('TRIM(SCMBookingXML) != ?', [''])
            ->whereNull('SCMINTStatus');

        if ($specificBooking) {
            $query->whereRaw('LOWER(bookingno) = ?', [strtolower($specificBooking)]);
        }

        $pendingCount = $query->count();

        Log::info('Starting CRM-SCM Integration', [
            'pending_records' => $pendingCount,
            'specific_booking' => $specificBooking ?? 'N/A',
            'start_time' => now()->toDateTimeString()
        ]);

        // Diagnostic logging for specific booking
        if ($specificBooking) {
            $exists = DB::table('CRM_SCMprofit_integration')->where('bookingno', $specificBooking)->exists();
            Log::info('CRMSCM: Checking booking existence', [
                'bookingno' => $specificBooking,
                'exists' => $exists
            ]);

            if ($exists) {
                $record = DB::table('CRM_SCMprofit_integration')->where('bookingno', $specificBooking)->first();
                Log::info('CRMSCM: Booking details', [
                    'bookingno' => $specificBooking,
                    'SCMBookingID' => $record->SCMBookingID,
                    'SCMBookingXML' => $record->SCMBookingXML ? substr($record->SCMBookingXML, 0, 50) : null,
                    'SCMINTStatus' => $record->SCMINTStatus
                ]);
            } else {
                Log::warning('CRMSCM: Booking not found in database', ['bookingno' => $specificBooking]);
                $this->error("Booking {$specificBooking} not found in database");
                return 1;
            }
        }

        $records = $query->limit($specificBooking ? 1 : 100)->get();

        if ($records->isEmpty()) {
            $message = $specificBooking ? "No records found for booking {$specificBooking}" : "No unprocessed records found";
            $this->info($message);
            Log::info("CRMSCM: {$message}");

            // Diagnostic counts
            $totalRecords = DB::table('CRM_SCMprofit_integration')->count();
            $unprocessedRecords = DB::table('CRM_SCMprofit_integration')
                ->whereNull('SCMBookingID')
                ->whereNull('SCMINTStatus')
                ->count();
            Log::info('CRMSCM: Diagnostic counts', [
                'total_records_in_table' => $totalRecords,
                'unprocessed_records' => $unprocessedRecords
            ]);

            return 0;
        }

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
            ->whereRaw('TRIM(SCMBookingXML) != ?', [''])
            ->whereNull('SCMINTStatus')
            ->count();

        Log::info('Completed CRM-SCM Integration', [
            'total_processed' => $processedCount,
            'successful' => $successCount,
            'failed' => $failedCount,
            'remaining_pending' => $remainingPending,
            'specific_booking' => $specificBooking ?? 'N/A',
            'total_execution_time_seconds' => round($totalExecutionTime, 3),
            'end_time' => now()->toDateTimeString()
        ]);

        $this->info("Processed {$processedCount} records: {$successCount} successful, {$failedCount} failed");
    }

    private function getAuthToken()
    {
        $tokenStartTime = microtime(true);
        $tokenUrl = env('SCM_TOKEN_URL');
        $username = env('SCM_USERNAME');
        $password = env('SCM_PASSWORD');

        if (!$tokenUrl || !$username || !$password) {
            Log::error('CRMSCM: Missing authentication configuration');
            return null;
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $tokenUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
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
                'Accept: application/json',
                'User-Agent: Laravel-CRMSCM/1.0'
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        $executionTime = microtime(true) - $tokenStartTime;

        if ($error || $httpCode !== 200) {
            Log::error('Failed to get auth token', [
                'error' => $error ?: 'HTTP ' . $httpCode,
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
            DB::table('CRM_SCMprofit_integration')
                ->where('bookingno', $record->bookingno)
                ->update(['SCMINTStatus' => 'Auth Failure', 'UpdatedOninSCM' => now()]);
            return;
        }

        $apiUrl = env('SCM_BOOKING_URL');
        if (!$apiUrl) {
            Log::error('CRMSCM: Missing SCM_BOOKING_URL', ['bookingno' => $record->bookingno]);
            DB::table('CRM_SCMprofit_integration')
                ->where('bookingno', $record->bookingno)
                ->update(['SCMINTStatus' => 'Config Error', 'UpdatedOninSCM' => now()]);
            return;
        }

        $booking_xml = $record->SCMBookingXML;

        // Validate XML
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($booking_xml);
        if (!$doc) {
            $errors = libxml_get_errors();
            Log::error('Invalid XML format', [
                'bookingno' => $record->bookingno,
                'errors' => array_map(function($error) {
                    return "Line {$error->line}: {$error->message}";
                }, $errors),
                'xml_preview' => substr($booking_xml, 0, 50),
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
            CURLOPT_TIMEOUT => 300,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $booking_xml,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/xml; charset=utf-8',
                'Authorization: Bearer ' . $token,
                'Accept: application/xml',
                'User-Agent: Laravel-CRMSCM/1.0',
                'Content-Length: ' . strlen($booking_xml)
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error || $httpCode !== 200) {
            Log::error('SCM API push failed', [
                'bookingno' => $record->bookingno,
                'error' => $error ?: 'HTTP ' . $httpCode,
                'execution_time_seconds' => round(microtime(true) - $startTime, 3)
            ]);
            DB::table('CRM_SCMprofit_integration')
                ->where('bookingno', $record->bookingno)
                ->update(['SCMINTStatus' => 'API Error: ' . ($error ?: 'HTTP ' . $httpCode), 'UpdatedOninSCM' => now()]);
            return;
        }

        $xmlResponse = simplexml_load_string($response, null, LIBXML_NOCDATA);
        if (!$xmlResponse) {
            Log::error('Failed to parse SCM API response', [
                'bookingno' => $record->bookingno,
                'response_preview' => substr($response, 0, 50),
                'execution_time_seconds' => round(microtime(true) - $startTime, 3)
            ]);
            DB::table('CRM_SCMprofit_integration')
                ->where('bookingno', $record->bookingno)
                ->update(['SCMINTStatus' => 'Invalid Response', 'UpdatedOninSCM' => now()]);
            return;
        }

        $jsonResponse = json_encode($xmlResponse);
        $xmlArrayResponse = json_decode($jsonResponse, true);

        Log::info('SCM API Response', [
            'bookingno' => $record->bookingno,
            'response' => $xmlArrayResponse,
            'execution_time_seconds' => round(microtime(true) - $startTime, 3)
        ]);

        if (isset($xmlArrayResponse['Transaction']['Status']) && $xmlArrayResponse['Transaction']['Status'] === 'Fail') {
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
                ->update(['SCMINTStatus' => 'Failed: ' . $errors, 'UpdatedOninSCM' => now()]);
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
            'SCMBookingID' => $xmlArrayResponse['Transaction']['TransactionId'] ?? null,
            'SCMBookingNo' => $xmlArrayResponse['Transaction']['ID'] ?? null,
            'execution_time_seconds' => round(microtime(true) - $startTime, 3)
        ]);
    }
}
