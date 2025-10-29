<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
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
    protected $signature = 'crmscm:Booking
        {--debug : Enable debug mode}
        {--test-auth : Test authentication only}
        {--booking= : Process specific booking number}
        {--skip-delay : Run without 17-minute delay}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command Create Booking to SCM from CRM';

    private $startTime;
    private $debugMode = false;

    /**
     * Create a new command instance.
     *
     * @return void
     *
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // === INITIAL SETUP ===
        $this->startTime = now();
        $this->debugMode = $this->option('debug');

        Log::info("CRMSCM Command Started at: " . $this->startTime);
        if ($this->debugMode) {
            $this->info("Debug Mode is ENABLED");
        }

        // === CONDITIONAL DELAY ===
        if (!$this->option('skip-delay')) {
            if ($this->debugMode) {
                $this->info("Delaying execution for 17 minutes...");
            }
            Log::info("CRMSCM: Delaying execution for 17 minutes");
            sleep(1020); // 17 minutes
            Log::info("CRMSCM: Delay completed, proceeding with command execution");
        } else {
            Log::info("CRMSCM: Skip-delay option enabled, running immediately");
            if ($this->debugMode) {
                $this->info("⏩ Running without delay (skip-delay enabled)");
            }
        }

        // === TEST AUTH MODE SHORT CIRCUIT ===
        if ($this->option('test-auth')) {
            Log::info("CRMSCM: Running in TEST-AUTH mode");
            return $this->testAuthentication();
        }

        try {
            // === VALIDATE ENVIRONMENT ===
            $this->validateEnvironment();

            // === CONFIGURATION ===
            $batchSize = 10; // can be configured via config
            $specificBooking = $this->option('booking');

            Log::info("CRMSCM: Batch size set to {$batchSize}");
            if ($specificBooking) {
                Log::info("CRMSCM: Processing specific Booking ID: {$specificBooking}");
            }

            // === FETCH RECORDS ===
            $records = $this->getRecordsToProcess($batchSize, $specificBooking);
            $totalRecords = $records->count();
            Log::info("CRMSCM: Found {$totalRecords} record(s) to process");

            if ($totalRecords === 0) {
                $this->info("No records found to process");
                Log::info("CRMSCM: No records found - ending command");
                return;
            }

            // === PROCESS RECORDS ===
            $results = $this->processRecords($records);

            // === LOG SUMMARY & OUTPUT ===
            $this->logSummary($this->startTime, $totalRecords, $results);
            $this->displayResults($totalRecords, $results);
        } catch (\Exception $e) {
            $this->handleCommandException($e);
        }

        Log::info("CRMSCM Command Completed at: " . now());
    }


    /**
     * Validate environment configuration
     */
    private function validateEnvironment()
    {
        $required = ['SCM_TOKEN_URL', 'SCM_USERNAME', 'SCM_PASSWORD', 'SCM_BOOKING_URL'];
        $missing = [];

        foreach ($required as $key) {
            if (empty(env($key))) {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            throw new \Exception("Missing required environment variables: " . implode(', ', $missing));
        }

        if ($this->debugMode) {
            $this->info("Environment validation passed");
        }
    }

    /**
     * Test authentication only
     */
    private function testAuthentication()
    {
        $this->info("Testing authentication...");

        try {
            $token = $this->getAuthToken();
            if ($token) {
                $this->info("✅ Authentication successful");
                $this->info("Token preview: " . substr($token, 0, 50) . "...");
                return 0;
            } else {
                $this->error("❌ Authentication failed");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("❌ Authentication error: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Get records to process from database
     */
    private function getRecordsToProcess($batchSize, $specificBooking = null)
    {
        $query = DB::table('CRM_SCMprofit_integration')
            ->whereNull('SCMBookingID')
            ->whereNotNull('SCMBookingXML')
            ->where('SCMBookingXML', '!=', '')
            ->whereNull('SCMINTStatus');

        if ($specificBooking) {
            $query->where('bookingno', $specificBooking);
            $batchSize = 1;
        }

        return $query->limit($batchSize)->get();
    }

    /**
     * Process all records
     */
    private function processRecords($records)
    {
        $successCount = 0;
        $failureCount = 0;
        $processedRecords = [];

        $progressBar = $this->output->createProgressBar($records->count());
        $progressBar->start();

        foreach ($records as $record) {
            try {
                if ($this->debugMode) {
                    $this->newLine();
                    $this->info("Processing booking: {$record->bookingno}");
                }

                $result = $this->pushXMLToSCM($record);

                if ($result) {
                    $successCount++;
                    $processedRecords[] = [
                        'bookingno' => $record->bookingno,
                        'status' => 'success'
                    ];

                    if ($this->debugMode) {
                        $this->info("✅ Success: {$record->bookingno}");
                    }
                } else {
                    $failureCount++;
                    $processedRecords[] = [
                        'bookingno' => $record->bookingno,
                        'status' => 'failed'
                    ];

                    if ($this->debugMode) {
                        $this->error("❌ Failed: {$record->bookingno}");
                    }
                }
            } catch (\Exception $e) {
                $failureCount++;
                $processedRecords[] = [
                    'bookingno' => $record->bookingno,
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];

                Log::error("CRMSCM: Exception processing record", [
                    'bookingno' => $record->bookingno,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                $this->updateBookingStatus($record->bookingno, 'Exception: ' . $e->getMessage());

                if ($this->debugMode) {
                    $this->error("💥 Error: {$record->bookingno} - " . $e->getMessage());
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        return [
            'success' => $successCount,
            'failure' => $failureCount,
            'processed' => $processedRecords
        ];
    }

    /**
     * Log command summary
     */
    private function logSummary($startTime, $totalRecords, $results)
    {
        $endTime = now();
        $duration = $endTime->diffInSeconds($startTime);
        $successRate = $totalRecords > 0 ? round(($results['success'] / $totalRecords) * 100, 2) : 0;

        Log::info("CRMSCM Command Completed", [
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_seconds' => $duration,
            'total_records' => $totalRecords,
            'successful_records' => $results['success'],
            'failed_records' => $results['failure'],
            'success_rate' => $successRate . '%'
        ]);
    }

    /**
     * Display results in console
     */
    private function displayResults($totalRecords, $results)
    {
        $duration = now()->diffInSeconds($this->startTime ?? now());

        $this->info("\n" . str_repeat('=', 50));
        $this->info("CRMSCM Processing Complete!");
        $this->info("Total Records: {$totalRecords}");
        $this->info("Successful: {$results['success']}");
        $this->info("Failed: {$results['failure']}");
        $this->info("Duration: {$duration} seconds");
        $this->info(str_repeat('=', 50));
    }

    /**
     * Handle command exceptions
     */
    private function handleCommandException(\Exception $e)
    {
        Log::error("CRMSCM Command Failed with Exception", [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        $this->error("Command failed with error: " . $e->getMessage());
    }

    /**
     * Update booking status in database
     */
    private function updateBookingStatus($bookingno, $status, $additionalData = [])
    {
        try {
            $updateData = array_merge([
                'SCMINTStatus' => substr($status, 0, 500), // Limit status length
                'CreatedOnInSCM' => now(),
                'UpdatedOninSCM' => now()
            ], $additionalData);

            $affected = DB::table('CRM_SCMprofit_integration')
                ->where('bookingno', $bookingno)
                ->update($updateData);

            if ($this->debugMode && $affected > 0) {
                Log::info("CRMSCM: Updated booking status", [
                    'bookingno' => $bookingno,
                    'status' => $status
                ]);
            }
        } catch (\Exception $e) {
            Log::error("CRMSCM: Failed to update booking status", [
                'bookingno' => $bookingno,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get authentication token using cURL with enhanced error handling
     *
     * @return string|null
     */
    private function getAuthToken()
    {
        Log::info("CRMSCM: Authentication process started");

        $tokenUrl = env('SCM_TOKEN_URL');
        $username = env('SCM_USERNAME');
        $password = env('SCM_PASSWORD');

        if (!$tokenUrl || !$username || !$password) {
            Log::error("CRMSCM: Missing authentication configuration");
            throw new \Exception("Missing authentication configuration");
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $tokenUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60, // Increased timeout
            CURLOPT_CONNECTTIMEOUT => 30,
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
        $info = curl_getinfo($curl);
        curl_close($curl);

        if ($this->debugMode) {
            Log::info("CRMSCM: Auth request details", [
                'url' => $tokenUrl,
                'http_code' => $httpCode,
                'total_time' => $info['total_time'],
                'response_size' => strlen($response)
            ]);
        }

        if ($error) {
            Log::error("CRMSCM: Authentication cURL Error", [
                'error' => $error,
                'url' => $tokenUrl
            ]);
            throw new \Exception("Authentication cURL Error: $error");
        }

        if ($httpCode !== 200) {
            Log::error("CRMSCM: Authentication failed with HTTP code: $httpCode", [
                'response' => $response,
                'url' => $tokenUrl
            ]);
            throw new \Exception("Authentication failed with HTTP code: $httpCode");
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("CRMSCM: Invalid JSON in authentication response", [
                'response' => $response,
                'json_error' => json_last_error_msg()
            ]);
            throw new \Exception("Invalid JSON in authentication response");
        }

        if (!isset($data['access_token'])) {
            Log::error("CRMSCM: No access token in authentication response", [
                'response' => $response
            ]);
            throw new \Exception("No access token in authentication response");
        }

        Log::info("CRMSCM: Authentication successful");
        return $data['access_token'];
    }

    /**
     * Push XML data to SCM API with enhanced validation and error handling
     *
     * @param object $record
     * @return bool
     */
    private function pushXMLToSCM($record)
    {
        Log::info("CRMSCM: Processing booking record", [
            'bookingno' => $record->bookingno,
            'xml_length' => strlen($record->SCMBookingXML)
        ]);

        if ($this->debugMode) {
            Log::info("CRMSCM: XML Preview", [
                'bookingno' => $record->bookingno,
                'xml_preview' => substr($record->SCMBookingXML, 0, 500)
            ]);
        }

        $token = $this->getAuthToken();
        if (!$token) {
            Log::error("CRMSCM: Failed to retrieve authentication token for booking", [
                'bookingno' => $record->bookingno
            ]);
            $this->updateBookingStatus($record->bookingno, 'Authentication Failed');
            return false;
        }

        $apiUrl = env('SCM_BOOKING_URL');
        $booking_xml = $this->prepareXML($record->SCMBookingXML);

        // Validate XML
        if (!$this->validateXML($booking_xml, $record->bookingno)) {
            return false;
        }

        // Make API call
        $response = $this->makeAPICall($apiUrl, $booking_xml, $token, $record->bookingno);

        if (!$response) {
            $this->updateBookingStatus($record->bookingno, 'API Call Failed');
            return false;
        }

        // Process API response
        return $this->processAPIResponse($response, $record);
    }

    /**
     * Prepare XML with proper encoding and format
     */
    private function prepareXML($xml)
    {
        // Remove BOM if present
        $xml = preg_replace('/^\xEF\xBB\xBF/', '', $xml);

        // Ensure XML declaration is present
        if (strpos(trim($xml), '<?xml') !== 0) {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $xml;
        }

        // Clean up any extra whitespace
        $xml = trim($xml);

        return $xml;
    }

    /**
     * Validate XML format with enhanced error reporting
     */
    private function validateXML($xml, $bookingno)
    {
        libxml_use_internal_errors(true);
        libxml_clear_errors();

        $doc = simplexml_load_string($xml);

        if (!$doc) {
            $xmlErrors = libxml_get_errors();
            $errorMessages = [];

            foreach ($xmlErrors as $error) {
                $errorMessages[] = "Line {$error->line}: " . trim($error->message);
            }

            Log::error("CRMSCM: Invalid XML format", [
                'bookingno' => $bookingno,
                'xml_errors' => $errorMessages,
                'xml_preview' => substr($xml, 0, 200)
            ]);

            $this->updateBookingStatus($bookingno, 'XML Error: ' . implode('; ', $errorMessages));
            libxml_clear_errors();
            return false;
        }

        libxml_clear_errors();

        if ($this->debugMode) {
            Log::info("CRMSCM: XML validation passed", ['bookingno' => $bookingno]);
        }

        return true;
    }

    /**
     * Make API call to SCM with enhanced configuration
     */
    private function makeAPICall($apiUrl, $xml, $token, $bookingno)
    {
        if ($this->debugMode) {
            Log::info("CRMSCM: Making API call", [
                'bookingno' => $bookingno,
                'url' => $apiUrl,
                'xml_size' => strlen($xml),
                'token_length' => strlen($token)
            ]);
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300, // Increased to 5 minutes
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $xml,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/xml; charset=utf-8',
                'Authorization: Bearer ' . $token,
                'Accept: application/xml',
                'User-Agent: Laravel-CRMSCM/1.0',
                'Content-Length: ' . strlen($xml)
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_VERBOSE => $this->debugMode,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        if ($this->debugMode) {
            Log::info("CRMSCM: API call completed", [
                'bookingno' => $bookingno,
                'http_code' => $httpCode,
                'response_size' => strlen($response),
                'total_time' => $info['total_time'],
                'curl_error' => $error
            ]);
        }

        return [
            'response' => $response,
            'http_code' => $httpCode,
            'error' => $error,
            'info' => $info
        ];
    }

    /**
     * Process API response and update database with enhanced error handling
     */
    private function processAPIResponse($apiResponse, $record)
    {
        $response = $apiResponse['response'];
        $httpCode = $apiResponse['http_code'];
        $error = $apiResponse['error'];

        if ($error) {
            Log::error("CRMSCM: API cURL Error", [
                'bookingno' => $record->bookingno,
                'error' => $error,
                'total_time' => $apiResponse['info']['total_time'] ?? 'unknown'
            ]);
            $this->updateBookingStatus($record->bookingno, 'cURL Error: ' . $error);
            return false;
        }

        if ($httpCode !== 200) {
            Log::error("CRMSCM: API request failed", [
                'bookingno' => $record->bookingno,
                'http_code' => $httpCode,
                'response' => substr($response, 0, 1000),
                'full_response' => $response
            ]);
            $this->updateBookingStatus($record->bookingno, "HTTP Error {$httpCode}");
            return false;
        }

        // Parse XML response
        $xmlResponse = $this->parseXMLResponse($response);
        if (!$xmlResponse) {
            Log::error("CRMSCM: Failed to parse XML response", [
                'bookingno' => $record->bookingno,
                'response_preview' => substr($response, 0, 500),
                'full_response' => $response
            ]);
            $this->updateBookingStatus($record->bookingno, 'Invalid XML Response');
            return false;
        }

        if ($this->debugMode) {
            Log::info("CRMSCM: Parsed XML Response", [
                'bookingno' => $record->bookingno,
                'response_structure' => array_keys($xmlResponse)
            ]);
        }

        // Check for API errors with multiple possible response formats
        $isError = $this->checkForAPIErrors($xmlResponse);
        if ($isError) {
            $errors = $this->extractAPIErrors($xmlResponse);

            Log::error("CRMSCM: XML Push Failed123", [
                'bookingno' => $record->bookingno,
                'errors' => $errors,
                'raw_response' => $xmlResponse
            ]);

            $this->updateBookingStatus($record->bookingno, $errors);
            return false;
        }

        // Success - extract relevant IDs
        $transactionId = $this->extractTransactionId($xmlResponse);
        $bookingId = $this->extractBookingId($xmlResponse);

        $this->updateBookingStatus($record->bookingno, 'Success', [
            'SCMBookingID' => $transactionId,
            'SCMBookingNo' => $bookingId,
        ]);

        Log::info("CRMSCM: Successfully processed booking", [
            'bookingno' => $record->bookingno,
            'SCMBookingID' => $transactionId,
            'SCMBookingNo' => $bookingId
        ]);

        return true;
    }

    /**
     * Parse XML response with better error handling
     */
    private function parseXMLResponse($response)
    {
        try {
            libxml_use_internal_errors(true);
            libxml_clear_errors();

            $xmlResponse = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);

            if (!$xmlResponse) {
                $errors = libxml_get_errors();
                Log::error("CRMSCM: XML parsing failed", [
                    'libxml_errors' => $errors,
                    'response_preview' => substr($response, 0, 200)
                ]);
                libxml_clear_errors();
                return null;
            }

            $jsonResponse = json_encode($xmlResponse);
            $arrayResponse = json_decode($jsonResponse, true);

            libxml_clear_errors();
            return $arrayResponse;
        } catch (\Exception $e) {
            Log::error("CRMSCM: XML parsing exception", [
                'error' => $e->getMessage(),
                'response_preview' => substr($response, 0, 200)
            ]);
            return null;
        }
    }

    /**
     * Check for API errors in response
     */
    private function checkForAPIErrors($xmlResponse)
    {
        // Check multiple possible error indicators
        if (
            isset($xmlResponse['Transaction']['Status']) &&
            strtolower($xmlResponse['Transaction']['Status']) === 'fail'
        ) {
            return true;
        }

        if (
            isset($xmlResponse['Status']) &&
            strtolower($xmlResponse['Status']) === 'error'
        ) {
            return true;
        }

        if (isset($xmlResponse['Error']) || isset($xmlResponse['Errors'])) {
            return true;
        }

        if (isset($xmlResponse['Transaction']['Errors'])) {
            return true;
        }

        return false;
    }

    /**
     * Extract transaction ID from response
     */
    private function extractTransactionId($xmlResponse)
    {
        // Try multiple possible locations
        $possiblePaths = [
            'Transaction.TransactionId',
            'Transaction.ID',
            'TransactionId',
            'ID',
            'BookingId'
        ];

        foreach ($possiblePaths as $path) {
            $value = data_get($xmlResponse, $path);
            if ($value) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Extract booking ID from response
     */
    private function extractBookingId($xmlResponse)
    {
        // Try multiple possible locations
        $possiblePaths = [
            'Transaction.ID',
            'Transaction.BookingNo',
            'Transaction.Reference',
            'BookingNo',
            'Reference',
            'ID'
        ];

        foreach ($possiblePaths as $path) {
            $value = data_get($xmlResponse, $path);
            if ($value) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Extract API errors from response with comprehensive error handling
     */
    private function extractAPIErrors($xmlResponse)
    {
        $errors = [];

        // Multiple possible error locations
        $errorSources = [
            'Transaction.Errors.Error',
            'Transaction.Error',
            'Errors.Error',
            'Error',
            'ErrorMessage',
            'Message'
        ];

        foreach ($errorSources as $source) {
            $errorData = data_get($xmlResponse, $source);
            if ($errorData) {
                $extractedErrors = $this->processErrorData($errorData);
                $errors = array_merge($errors, $extractedErrors);
            }
        }

        if (empty($errors)) {
            // Check for status messages
            $status = data_get($xmlResponse, 'Transaction.Status') ??
                data_get($xmlResponse, 'Status') ??
                'Unknown error occurred';
            return "API returned failure status: $status";
        }

        return implode('; ', array_unique($errors));
    }

    /**
     * Process error data from various formats
     */
    private function processErrorData($errorData)
    {
        $errors = [];

        if (is_string($errorData)) {
            $cleaned = $this->cleanErrorDescription($errorData);
            if (!empty($cleaned)) {
                $errors[] = $cleaned;
            }
        } elseif (is_array($errorData)) {
            // Handle array of errors or single error as array
            if (isset($errorData['Code']) || isset($errorData['Description']) || isset($errorData['Message'])) {
                // Single error as associative array
                $description = $errorData['Description'] ?? $errorData['Message'] ?? 'Unknown error';
                $cleaned = $this->cleanErrorDescription($description);
                if (!empty($cleaned)) {
                    $code = isset($errorData['Code']) ? "[{$errorData['Code']}] " : '';
                    $errors[] = $code . $cleaned;
                }
            } else {
                // Multiple errors or indexed array
                foreach ($errorData as $error) {
                    if (is_string($error)) {
                        $cleaned = $this->cleanErrorDescription($error);
                        if (!empty($cleaned)) {
                            $errors[] = $cleaned;
                        }
                    } elseif (is_array($error)) {
                        $description = $error['Description'] ?? $error['Message'] ?? 'Unknown error';
                        $cleaned = $this->cleanErrorDescription($description);
                        if (!empty($cleaned)) {
                            $code = isset($error['Code']) ? "[{$error['Code']}] " : '';
                            $errors[] = $code . $cleaned;
                        }
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Clean error description with enhanced processing
     */
    private function cleanErrorDescription($description)
    {
        if (empty($description)) {
            return '';
        }

        // Handle various data types
        if (is_array($description)) {
            $description = implode(' ', array_filter($description));
        }

        // Convert to string if not already
        $description = (string) $description;

        // Clean up whitespace and newlines
        $description = trim($description);
        $description = preg_replace('/\s+/', ' ', $description);
        $description = str_replace(["\r\n", "\n", "\r"], ' ', $description);

        // Remove common XML artifacts
        $description = preg_replace('/<[^>]*>/', '', $description);
        $description = html_entity_decode($description);

        return trim($description);
    }


public function fixUserIds(Request $request)
{
    $usersWithoutId = DB::table('Azure_Users')
        ->whereNull('id')
        ->orWhere('id', '')
        ->count();
    
    if ($usersWithoutId === 0) {
        return response()->json([
            'status' => 'success',
            'message' => 'All users already have IDs',
            'users_checked' => DB::table('Azure_Users')->count()
        ]);
    }
    
    // Generate UUIDs for users without IDs
    $fixed = 0;
    $users = DB::table('Azure_Users')
        ->whereNull('id')
        ->orWhere('id', '')
        ->select('userPrincipalName')
        ->get();
    
    foreach ($users as $user) {
        $newId = (string) \Illuminate\Support\Str::uuid();
        DB::table('Azure_Users')
            ->where('userPrincipalName', $user->userPrincipalName)
            ->update(['id' => $newId]);
        $fixed++;
    }
    
    return response()->json([
        'status' => 'success',
        'message' => 'User IDs generated successfully',
        'users_fixed' => $fixed,
        'total_users' => DB::table('Azure_Users')->count()
    ]);
}




}
