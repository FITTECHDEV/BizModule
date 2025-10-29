<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KRABillingHeader;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use DateTime;

class EtimsInvoices extends Command
{
    protected $signature = 'generate:invoice {invoiceId?}';
    protected $description = 'This Command helps in generating Invoice using KRA API';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        //Log::notice("KRA push running");

        $invoiceId = $this->argument('invoiceId');
        $pendingInvoices = $this->fetchPendingInvoices($invoiceId);

        // if ($pendingInvoices->isEmpty()) {
        //     Log::info("No pending invoices found123.");
        //     return 0;
        // }

        foreach ($pendingInvoices as $invoice) {
            $jsonData = json_decode($invoice->KRAJsonFile, true);

            if ($jsonData) {
                $this->submitToApi($invoice, $jsonData);
            } else {
                Log::error("Failed to decode JSON data for invoice ID: {$invoice->ID}");
            }
        }

        Log::notice("KRA push completed");
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
        $query = KRABillingHeader::nolock()
            ->where('Docstatus', 'Pending')
            ->whereNull('KRAInvoiceNo')
            ->whereNull('KRASerialNo')
            ->whereNull('KRAQRCode');

        if ($invoiceId) {
            $query->where('ID', $invoiceId);
        }

        return $query->get();
    }

    /**
     * Submit invoice data to the KRA API.
     *
     * @param \App\Models\KRABillingHeader $invoice
     * @param array $jsonData
     * @return void
     */
    protected function submitToApi($invoice, $jsonData)
    {
        $baseUrl = rtrim(env('KRA_BASE_URL'), '/');
        $url = "{$baseUrl}/invoices";

        $response = Http::withBasicAuth(env('KRA_USERNAME'), env('KRA_PASSWORD'))
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
     * @param \App\Models\KRABillingHeader $invoice
     * @param array $responseData
     * @return void
     */
    protected function handleSuccessfulSubmission($invoice, $responseData)
    {
        // Convert the scuReceiptDate
        $scuReceiptDate = $responseData['data']['scuReceiptDate'];
        $formattedDate = DateTime::createFromFormat('YmdHis', $scuReceiptDate)->format('Y-m-d H:i:s');

        // Update the database with the formatted date
        KRABillingHeader::where('ID', $invoice->ID)->update([
            'DocStatus' => 'Submitted',
            'KRAInvoiceNo' => $responseData['data']['invoiceNo'],
            'KRASerialNo' => $responseData['data']['scdcId'],
            'KRAQRCode' => $responseData['data']['invoiceVerificationUrl'],
            'KRAInternalData' => $responseData['data']['internalData'],
            'KRASignature' => $responseData['data']['signature'],
            'KRAScuReceiptDate' => $formattedDate,
            'KRAScuReceiptNo' => $responseData['data']['scuReceiptNo'],
            'SubmissionStatus' => $responseData['message'],
        ]);

        Log::info("Invoice ID: {$invoice->ID} submitted successfully.");
    }


    /**
     * Handle failed API submission.
     *
     * @param \App\Models\KRABillingHeader $invoice
     * @param array $responseData
     * @param string $responseBody
     * @return void
     */
    protected function handleFailedSubmission($invoice, $responseData, $responseBody)
    {
        KRABillingHeader::where('ID', $invoice->ID)->update([
            'DocStatus' => $responseData['statusCode'] ?? 'Error',
            'SubmissionStatus' => $responseData['message'] ?? 'Unknown error',
        ]);

        Log::error("Failed to submit invoice ID: {$invoice->ID}. Response: " . $responseBody);
    }
}
