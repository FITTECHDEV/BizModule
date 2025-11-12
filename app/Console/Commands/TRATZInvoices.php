<?php

namespace App\Console\Commands;

use App\Models\TRABillingHeader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class TRATZInvoices extends Command
{
    protected $signature = 'tra_tz:invoices {invoiceId?} {--list : List pending invoices}';
    protected $description = 'This Command Helps in generating Invoice Using TRA API for FIT- Tanzania';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Check if list option is set
        if ($this->option('list')) {
            $this->listPendingInvoices();
            return 0;
        }

        $invoiceId = $this->argument('invoiceId');
        $pendingInvoices = $this->fetchPendingInvoices($invoiceId);

        if ($pendingInvoices->isEmpty()) {
            $msg = "No pending invoices found.";
            Log::info($msg);
            $this->info($msg);
            return 0;
        }

        foreach ($pendingInvoices as $invoice) {
            $jsonData = json_decode($invoice->TRAJsonFile, false);

            if ($jsonData) {
                // Log the JSON data to Laravel log file
                Log::info("Invoice {$invoice->DocNo} JSON Data:", [
                    'invoice_id' => $invoice->ID,
                    'doc_no' => $invoice->DocNo,
                    'json' => $jsonData
                ]);

                $this->info("Submitting Invoice DocNo: {$invoice->DocNo}");
                $this->submitToApi($invoice, $jsonData);
            } else {
                $msg = "Failed to decode JSON data for invoice ID: {$invoice->ID}";
                Log::error($msg);
                $this->error($msg);
            }
        }

        $this->info("TRA TZ push completed");
        Log::notice("TRA TZ push completed");
        return 0;
    }

    protected function listPendingInvoices()
    {
        $invoices = TRABillingHeader::nolock()
            ->where('Docstatus', 'Pending')
            ->whereNull('TRAInvoiceNo')
            ->whereNull('TRASerialNo')
            ->whereNull('TRAQRCode')
            ->where('OfficeCode', 'AR')
            ->select('ID', 'DocNo', 'DocStatus', 'OfficeCode')
            ->limit(10)
            ->get();

        if ($invoices->isEmpty()) {
            $this->info("No pending invoices found.");
            return;
        }

        $this->info("Pending Invoices (showing up to 10):");
        $this->table(
            ['ID', 'Doc No', 'Status', 'Office'],
            $invoices->map(fn($inv) => [$inv->ID, $inv->DocNo, $inv->DocStatus, $inv->OfficeCode])
        );
    }

   /**
 * Fetch pending invoices and generate JSON using stored procedure.
 *
 * @param int|null $invoiceId
 * @return \Illuminate\Database\Eloquent\Collection
 */
protected function fetchPendingInvoices($invoiceId = null)
{
    $query = TRABillingHeader::nolock()
        ->where('Docstatus', 'Pending')
        ->whereNull('TRAInvoiceNo')
        ->whereNull('TRASerialNo')
        ->whereNull('TRAQRCode')
        ->where('OfficeCode', 'AR');

    if ($invoiceId) {
        $query->where('ID', $invoiceId);
    }

    $invoices = $query->get();

    // Execute stored procedure for each invoice
    foreach ($invoices as $invoice) {
        try {
            // Execute with SET NOCOUNT ON
            $result = DB::select("
                SET NOCOUNT ON;
                DECLARE @JsonOutput VARCHAR(MAX);
                EXEC [dbo].[TRA_VFD] @InvoiceID = ?, @Json = @JsonOutput OUTPUT;
                SELECT @JsonOutput AS JsonResult;
            ", [$invoice->ID]);

            $json = $result[0]->JsonResult ?? null;

            if (!empty($json)) {
                $invoice->TRAJsonFile = $json;
                $invoice->save();
            }

            Log::info("Generated JSON for Invoice ID: {$invoice->ID}");

        } catch (\Exception $e) {
            Log::error("Failed to execute SP for Invoice ID: {$invoice->ID}. Error: " . $e->getMessage());
        }
    }

    return $invoices;
}

    // protected function submitToApi($invoice, $jsonData)
    // {
    //     $baseUrl = rtrim(env('TRA_FITTZ_BASE_URL'), '/');
    //     $url = "{$baseUrl}/invoices";

    //     $this->info("Posting invoice {$invoice->DocNo} to {$url}");



    //     try {
    //         $response = Http::withBasicAuth(env('TRA_FITTZ_USERNAME'), env('TRA_FITTZ_PASSWORD'))
    //             ->timeout((int) env('TRA_FITTZ_TIMEOUT', 20))
    //             ->retry((int) env('TRA_FITTZ_RETRIES', 3), (int) env('TRA_FITTZ_RETRY_DELAY', 500))
    //             ->post($url, $jsonData);

    //         $this->info("Received HTTP status: {$response->status()}");

    //         $responseData = $response->json();

    //         if (!$response->ok() || $response->status() != 200) {
    //             // Extract clean error message
    //             $errorMessage = $responseData['message'] ?? 'API error';
    //             $msg = "Invoice {$invoice->DocNo} submission failed — {$errorMessage}";
    //             Log::warning($msg);
    //             $this->warn($msg);

    //             $this->handleFailedSubmission($invoice, $responseData, $errorMessage);
    //         } else {
    //             $this->handleSuccessfulSubmission($invoice, $responseData);
    //         }
    //     } catch (\Throwable $e) {
    //         $errorMessage = $e->getMessage();

    //         // Try to extract JSON "message" if present in exception
    //         if (preg_match('/\{.*\}/s', $errorMessage, $matches)) {
    //             $json = json_decode($matches[0], true);
    //             if (json_last_error() === JSON_ERROR_NONE && isset($json['message'])) {
    //                 $errorMessage = $json['message'];
    //             }
    //         }

    //         $msg = "Invoice {$invoice->DocNo} API connection error: {$errorMessage}";
    //         Log::error($msg);
    //         $this->error($msg);
    //         $this->handleFailedSubmission($invoice, null, $errorMessage);
    //     }
    // }

    protected function submitToApi($invoice, $jsonData)
{
    $baseUrl = rtrim(env('TRA_FITTZ_BASE_URL'), '/');
    $url = "{$baseUrl}/invoices";

    $this->info("Posting invoice {$invoice->DocNo} to {$url}");

    try {
        $response = Http::withBasicAuth(env('TRA_FITTZ_USERNAME'), env('TRA_FITTZ_PASSWORD'))
            ->timeout((int) env('TRA_FITTZ_TIMEOUT', 20))
            ->retry((int) env('TRA_FITTZ_RETRIES', 3), (int) env('TRA_FITTZ_RETRY_DELAY', 500))
            ->post($url, $jsonData);

        $this->info("Received HTTP status: {$response->status()}");

        $responseData = $response->json();

        // Log the complete API response
        Log::info("TRA API Response for Invoice {$invoice->DocNo}:", [
            'invoice_id' => $invoice->ID,
            'doc_no' => $invoice->DocNo,
            'http_status' => $response->status(),
            'response_data' => $responseData,
            'raw_body' => $response->body()
        ]);

        if (!$response->ok() || $response->status() != 200) {
            // Extract clean error message
            $errorMessage = $responseData['message'] ?? 'API error';
            $msg = "Invoice {$invoice->DocNo} submission failed — {$errorMessage}";
            Log::warning($msg);
            $this->warn($msg);

            $this->handleFailedSubmission($invoice, $responseData, $errorMessage);
        } else {
            $this->handleSuccessfulSubmission($invoice, $responseData);
        }
    } catch (\Throwable $e) {
        $errorMessage = $e->getMessage();

        // Log the exception details
        Log::error("TRA API Exception for Invoice {$invoice->DocNo}:", [
            'invoice_id' => $invoice->ID,
            'doc_no' => $invoice->DocNo,
            'exception' => $errorMessage,
            'trace' => $e->getTraceAsString()
        ]);

        // Try to extract JSON "message" if present in exception
        if (preg_match('/\{.*\}/s', $errorMessage, $matches)) {
            $json = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE && isset($json['message'])) {
                $errorMessage = $json['message'];
            }
        }

        $msg = "Invoice {$invoice->DocNo} API connection error: {$errorMessage}";
        Log::error($msg);
        $this->error($msg);
        $this->handleFailedSubmission($invoice, null, $errorMessage);
    }
}


    protected function handleSuccessfulSubmission($invoice, $responseData)
    {
        TRABillingHeader::where('ID', $invoice->ID)->update([
            'DocStatus' => 'Submitted',
            'TRAInvoiceNo' => $responseData['data']['receipVerificationNo'] ?? null,
            'TRAQRCode' => $responseData['data']['invoiceVerificationUrl'] ?? null,
            'SubmissionStatus' => $responseData['message'] ?? 'Success',
        ]);

        Log::info("Invoice ID: {$invoice->ID} submitted successfully.");
    }

    protected function handleFailedSubmission($invoice, $responseData, $errorMessage)
    {
        // Use only the error message, avoid full HTTP response
        $submissionStatus = !empty($errorMessage) ? $errorMessage : 'Unknown error';

        TRABillingHeader::where('ID', $invoice->ID)->update([
            'DocStatus' => 'Failed', // Standardized to 'Failed'
            'SubmissionStatus' => $submissionStatus,
        ]);

        Log::error("Failed to submit invoice ID: {$invoice->ID}. Error: " . $submissionStatus);
    }
}
