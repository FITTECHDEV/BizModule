<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KRABillingHeader;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
        Log::notice("KRA push running");

        $invoiceId = $this->argument('invoiceId');
        $pendingInvoices = $this->fetchPendingInvoices($invoiceId);

        if ($pendingInvoices->isEmpty()) {
            Log::info("No pending invoices found.");
            return 0;
        }

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

    protected function fetchPendingInvoices($invoiceId = null)
    {
        $query = KRABillingHeader::pending();

        if ($invoiceId) {
            $query->where('ID', $invoiceId);
        }

        return $query->get();
    }

    protected function submitToApi($invoice, $jsonData)
    {
        $baseUrl = rtrim(env('KRA_BASE_URL'), '/');
        $url = "{$baseUrl}/invoices";

        $response = Http::withBasicAuth(env('KRA_USERNAME'), env('KRA_PASSWORD'))
            ->post($url, $jsonData);

        if ($response->successful()) {
            Log::info("Invoice ID: {$invoice->ID} submitted successfully.");

            // Extract data from the response
            $responseData = $response->json();

            if (
                isset($responseData['data']['invoiceNo']) &&
                isset($responseData['data']['scdcId']) &&
                isset($responseData['data']['invoiceVerificationUrl'])
            ) {
                // Begin transaction
                DB::beginTransaction();
                try {
                    $invoice->update([
                        'KRAInvoiceNo' => $responseData['data']['invoiceNo'],
                        'KRASerialNo' => $responseData['data']['scdcId'],
                        'KRAQRCode' => $responseData['data']['invoiceVerificationUrl'],
                        'DocStatus' => 'Submitted'
                    ]);

                    // Commit transaction
                    DB::commit();
                    Log::info("Invoice ID: {$invoice->ID} updated successfully with KRA details.");
                } catch (\Exception $e) {
                    // Rollback transaction
                    DB::rollBack();
                    Log::error("Failed to update KRA details for invoice ID: {$invoice->ID}. Error: {$e->getMessage()}");
                }
            } else {
                Log::error("Response data does not contain the expected keys for invoice ID: {$invoice->ID}");
            }
        } else {
            Log::error("Failed to submit invoice ID: {$invoice->ID}. Response: " . $response->body());
        }
    }
}
