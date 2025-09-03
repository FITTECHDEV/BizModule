<?php

namespace App\Console\Commands;

use App\Models\TRABillingHeader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class TRATZInvoices extends Command
{
    protected $signature = 'tra_tz:invoices {invoiceId?}';
    protected $description = 'This Command Helps in generating Invoice Using TRA API for FIT- Tanzania';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $invoiceId = $this->argument('invoiceId');
        $pendingInvoices = $this->fetchPendingInvoices($invoiceId);

        if ($pendingInvoices->isEmpty()) {
            $msg = "No pending invoices found.";
            Log::info($msg);
            $this->info($msg);
            return 0;
        }

        foreach ($pendingInvoices as $invoice) {
            $jsonData = json_decode($invoice->TRAJsonFile, true);

            if ($jsonData) {
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

        return $query->get();
    }

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
