<?php

namespace App\Console\Commands;

use App\Models\TRABillingHeader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


class TRATZInvoices extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tra_tz:invoices {invoiceId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command Helps in generating Invoice Using TRA API for FIT- Tanzania';

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
     * @return int
     */
    public function handle()
    {
        Log::notice("TRA TZ push running");

        $invoiceId = $this->argument('invoiceId');
        $pendingInvoices = $this->fetchPendingInvoices($invoiceId);

        if ($pendingInvoices->isEmpty()) {
            Log::info("No pending invoices found.");
            return 0;
        }

        foreach ($pendingInvoices as $invoice) {
            $jsonData = json_decode($invoice->TRAJsonFile, true);

            if ($jsonData) {
                $this->submitToApi($invoice, $jsonData);
            } else {
                Log::error("Failed to decode JSON data for invoice ID: {$invoice->ID}");
            }
        }

        Log::notice("TRA TZ push completed");
        return 0;
    }

    /**
     * Fetch pending invoices.
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

        return $query->get();
    }

    /**
     * Submit invoice data to the KRA API.
     *
     * @param \App\Models\TRABillingHeader $invoice
     * @param array $jsonData
     * @return void
     */
    protected function submitToApi($invoice, $jsonData)
    {
        $baseUrl = rtrim(env('TRA_FITTZ_BASE_URL'), '/');
        $url = "{$baseUrl}/invoices";

        $response = Http::withBasicAuth(env('TRA_USERNAME'), env('TRA_PASSWORD'))
            ->post($url, $jsonData);

        $responseData = $response->json();
        Log::notice("Processing: " . $invoice->DocNo);

        if (!$response->ok() || $response->status() != 200) {
            $this->handleFailedSubmission($invoice, $responseData, $response->body());
        } else {
            $this->handleSuccessfulSubmission($invoice, $responseData);
        }
    }

    /**
     * Handle successful API submission.
     *
     * @param \App\Models\TRABillingHeader $invoice
     * @param array $responseData
     * @return void
     */
    protected function handleSuccessfulSubmission($invoice, $responseData)
    {

        // Update the database with the formatted date
        TRABillingHeader::where('ID', $invoice->ID)->update([
            'DocStatus' => 'Submitted',
            'TRAInvoiceNo' => $responseData['data']['receipVerificationNo'],
            'TRAQRCode' => $responseData['data']['invoiceVerificationUrl'],
            'SubmissionStatus' => $responseData['message'],
        ]);

        Log::info("Invoice ID: {$invoice->ID} submitted successfully.");
    }


    /**
     * Handle failed API submission.
     *
     * @param \App\Models\TRABillingHeader $invoice
     * @param array $responseData
     * @param string $responseBody
     * @return void
     */
    protected function handleFailedSubmission($invoice, $responseData, $responseBody)
    {
        TRABillingHeader::where('ID', $invoice->ID)->update([
            'DocStatus' => $responseData['statusCode'] ?? 'Error',
            'SubmissionStatus' => $responseData['message'] ?? 'Unknown error',
        ]);

        Log::error("Failed to submit invoice ID: {$invoice->ID}. Response: " . $responseBody);
    }
}
