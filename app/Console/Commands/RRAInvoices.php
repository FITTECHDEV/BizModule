<?php

namespace App\Console\Commands;

use App\Models\RRABillingHeader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use DateTime;
class RRAInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'RRA:Invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Commande help in generating invoice and pushing them to RRA';

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
        Log::notice("RRA push running");

        $invoiceId = $this->argument('invoiceId');
        $pendingInvoices = $this->fetchPendingInvoices($invoiceId);

        if ($pendingInvoices->isEmpty()) {
            Log::info("No pending invoices found.");
            return 0;
        }

        foreach ($pendingInvoices as $invoice) {
            $jsonData = json_decode($invoice->RRAJsonFile, true);

            if ($jsonData) {
                $this->submitToApi($invoice, $jsonData);
            } else {
                Log::error("Failed to decode JSON data for invoice ID: {$invoice->ID}");
            }
        }

        Log::notice("RRA push completed");
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
        $query = RRABillingHeader::nolock()
            ->where('Docstatus', 'Pending')
            ->whereNull('RRAInvoiceNo')
            ->whereNull('RRASerialNo')
            ->whereNull('RRAQRCode');

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
        $baseUrl = rtrim(env('RRA_BASE_URL'), '/');
        $url = "{$baseUrl}/invoices";

        $response = Http::withBasicAuth(env('RRA_USERNAME'), env('RRA_PASSWORD'))
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
     * @param \App\Models\RRABillingHeader $invoice
     * @param array $responseData
     * @return void
     */
    protected function handleSuccessfulSubmission($invoice, $responseData)
    {
        // Convert the scuReceiptDate
        $scuReceiptDate = $responseData['data']['scuReceiptDate'];
        $formattedDate = DateTime::createFromFormat('YmdHis', $scuReceiptDate)->format('Y-m-d H:i:s');

        // Update the database with the formatted date
        RRABillingHeader::where('ID', $invoice->ID)->update([
            'DocStatus' => 'Submitted',
            'RRAInvoiceNo' => $responseData['data']['invoiceNo'],
            'RRASerialNo' => $responseData['data']['scdcId'],
            'RRAQRCode' => $responseData['data']['invoiceVerificationUrl'],
            'RRAInternalData' => $responseData['data']['internalData'],
            'RRASignature' => $responseData['data']['signature'],
            'RRAScuReceiptDate' => $formattedDate,
            'RRAScuReceiptNo' => $responseData['data']['scuReceiptNo'],
            'SubmissionStatus' => $responseData['message'],
        ]);

        Log::info("Invoice ID: {$invoice->ID} submitted successfully.");
    }


    /**
     * Handle failed API submission.
     *
     * @param \App\Models\RRABillingHeader $invoice
     * @param array $responseData
     * @param string $responseBody
     * @return void
     */
    protected function handleFailedSubmission($invoice, $responseData, $responseBody)
    {
        RRABillingHeader::where('ID', $invoice->ID)->update([
            'DocStatus' => $responseData['statusCode'] ?? 'Error',
            'SubmissionStatus' => $responseData['message'] ?? 'Unknown error',
        ]);

        Log::error("Failed to submit invoice ID: {$invoice->ID}. Response: " . $responseBody);
    }
}
