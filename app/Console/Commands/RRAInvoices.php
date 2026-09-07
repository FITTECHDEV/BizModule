<?php

namespace App\Console\Commands;

use App\Models\RRABillingHeader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use DateTime;
use Illuminate\Support\Facades\Http;

class RRAInvoices extends Command
{
    protected $signature = 'RRA:Invoice {invoiceId?}';
    protected $description = 'This Command helps in generating invoices and pushing them to RRA';

    public function __construct()
    {
        parent::__construct();
    }


public function handle()
{
    // Log::notice("RRA push running");

    $invoiceId = $this->argument('invoiceId');

    // If invoiceId is provided, just display the JSON and exit
    if ($invoiceId) {
        // Search by DocNo only (since ID is a GUID)
        $invoice = RRABillingHeader::where('DocNo', $invoiceId)->first();

        if (!$invoice) {
            $this->error("Invoice with DocNo: {$invoiceId} not found");
            Log::error("Invoice with DocNo: {$invoiceId} not found");
            return 1;
        }

        $this->info("=== JSON for Invoice ===");
        $this->info("ID: {$invoice->ID}");
        $this->info("DocNo: {$invoice->DocNo}");
        $this->line("");
        $this->line($invoice->RRAJsonFile);

        // Also log it
        Log::info("Invoice {$invoice->DocNo} JSON: " . $invoice->RRAJsonFile);

        return 0;
    }

    // Rest of your original code...
    $pendingInvoices = $this->fetchPendingInvoices($invoiceId);

    if ($pendingInvoices->isEmpty()) {
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
    protected function fetchPendingInvoices($invoiceId = null)
    {
        $query = RRABillingHeader::where('Docstatus', 'Pending')
            ->whereNull('RRA_CIS_Receipt_Number')
            ->whereNull('RRA_SDC_ID')
            ->whereNull('RRA_SDC_Data_QRCode');

        if ($invoiceId) {
            $query->where('ID', $invoiceId);
        }

        return $query->get();
    }

    /**
     * Submit invoice data to the API.
     *
     * @param \App\Models\RRABillingHeader $invoice
     * @param array $jsonData
     * @return void
     */
    private function submitToApi($invoice, $jsonData)
    {
        $baseUrl = rtrim(env('RRA_BASE_URL'), '/');
        $url = "{$baseUrl}/jsonrpc";

        $response = Http::post($url, $jsonData);

        if (!$response->successful()) {
            $this->handleFailedSubmission($invoice, null, $response->body());
            return;
        }

        $responseData = $response->json();
        Log::notice("Processing: " . $invoice->DocNo);

        if (!$response->ok() || $response->status() != 200) {
            $this->handleFailedSubmission($invoice, $responseData, $response->body());
        } else {
            $this->handleSuccessfulSubmission($invoice, $responseData);
        }
    }


    private function formatWithDashes($data)
    {
        if (!$data) {
            return null;
        }

        return implode('-', str_split($data, 4));
    }

    // protected function handleSuccessfulSubmission($invoice, $responseData)
    // {
    //     $result = $responseData['result'] ?? [];
    //     $errorMessage = trim($result['error'] ?? '');

    //     $sdcData = $result['sdc_data'] ?? null;
    //     $cisData = $result['cis_data'] ?? null;
    //     $journal = $result['journal'] ?? null;

    //     // Convert comma-separated sdcData to hash-separated format
    //     if (!empty($sdcData)) {
    //         $sdcData = str_replace(',', '#', $sdcData);
    //     }

    //     $receiptNumber = $sdcID = $sdcDate = $sdcSignature = $sdcInternalData = null;

    //     if (!empty($sdcData)) {
    //         $sdcParts = explode('#', $sdcData);
    //         if (count($sdcParts) >= 7) {
    //             $sdcID = $sdcParts[0];
    //             $receiptNumber = "{$sdcParts[1]}/{$sdcParts[2]} {$sdcParts[3]}";
    //             $sdcDate = $sdcParts[4];
    //             $sdcSignature = $this->formatWithDashes($sdcParts[5]);
    //             $sdcInternalData = $this->formatWithDashes($sdcParts[6]); // Use correct index
    //         }
    //     }

    //     $cisDate = $cisReceiptNumber = $cisMRC = null;
    //     if (!empty($cisData)) {
    //         $cisParts = explode(',', $cisData);
    //         if (count($cisParts) >= 3) {
    //             $cisDate = $cisParts[0];
    //             $cisReceiptNumber = $cisParts[1];
    //             $cisMRC = $cisParts[2];
    //         }
    //     }

    //     $formattedCisDate = null;
    //     if (!empty($cisDate)) {
    //         $dateObj = DateTime::createFromFormat('Y-m-d H:i:s.u', $cisDate);
    //         if ($dateObj) {
    //             $formattedCisDate = $dateObj->format('Y-m-d H:i:s');
    //         }
    //     }

    //     // Set SubmissionStatus based on errorMessage
    //     $submissionStatus = ($errorMessage === 'ERROR_EXIST')
    //         ? "Invoice with trader invoice No already exists"
    //         : ($errorMessage ?: 'Invoice generated successfully');

    //     $updateData = [
    //         'RRA_SDC_Receipt_Number' => $receiptNumber,
    //         'RRA_SDC_Date' => $sdcDate,
    //         'RRA_SDC_ID' => $sdcID,
    //         'RRA_SDC_Data_QRCode' => $sdcData, // Now hash-separated
    //         'RRA_SDC_InternalData' => $sdcInternalData, // Now formatted with dashes
    //         'RRA_SDC_Signature' => $sdcSignature, // Now formatted with dashes
    //         'RRA_CIS_Date' => $formattedCisDate,
    //         'RRA_CIS_Receipt_Number' => $cisReceiptNumber,
    //         'RRA_CIS_MRC' => $cisMRC,
    //         'RRA_Journal' => $journal,
    //         'SubmissionStatus' => $submissionStatus,
    //     ];

    //     // Update DocStatus logic (Checking if errorMessage exactly matches "ERROR_EXIST")
    //     if ($errorMessage === '' || $errorMessage === 'ERROR_EXIST') {
    //         $updateData['DocStatus'] = 'Submitted';
    //     } else {
    //         $updateData['DocStatus'] = 'Failed';
    //     }

    //     RRABillingHeader::where('ID', $invoice->ID)->update($updateData);

    //     if ($errorMessage === '' || $errorMessage === 'ERROR_EXIST') {
    //         Log::info("Invoice ID: {$invoice->ID} submitted successfully with Receipt No: {$receiptNumber}.");
    //     } else {
    //         Log::error("Invoice ID: {$invoice->ID} encountered an issue: {$errorMessage}. Response Data: " . json_encode($responseData));
    //     }
    // }
protected function handleSuccessfulSubmission($invoice, $responseData)
{
    $result = $responseData['result'] ?? [];
    $errorMessage = trim($result['error'] ?? '');

    $sdcData = $result['sdc_data'] ?? null;
    $cisData = $result['cis_data'] ?? null;
    $journal = $result['journal'] ?? null;

    // Convert comma-separated sdcData to hash-separated format
    if (!empty($sdcData)) {
        $sdcData = str_replace(',', '#', $sdcData);
    }

    $receiptNumber = $sdcID = $sdcDate = $sdcSignature = $sdcInternalData = null;

    if (!empty($sdcData)) {
        $sdcParts = explode('#', $sdcData);
        if (count($sdcParts) >= 7) {
            $sdcID = $sdcParts[0];
            $receiptNumber = "{$sdcParts[1]}/{$sdcParts[2]} {$sdcParts[3]}";
            $sdcDate = $sdcParts[4];
            $sdcSignature = $this->formatWithDashes($sdcParts[5]);
            $sdcInternalData = $this->formatWithDashes($sdcParts[6]);
        }
    }

    $cisDate = $cisReceiptNumber = $cisMRC = null;
    if (!empty($cisData)) {
        $cisParts = explode(',', $cisData);
        if (count($cisParts) >= 3) {
            $cisDate = $cisParts[0];
            $cisReceiptNumber = $cisParts[1];
            $cisMRC = $cisParts[2];
        }
    }

    $formattedCisDate = null;
    if (!empty($cisDate)) {
        $dateObj = DateTime::createFromFormat('Y-m-d H:i:s.u', $cisDate);
        if ($dateObj) {
            $formattedCisDate = $dateObj->format('Y-m-d H:i:s');
        }
    }

    // -------------------------------------------------
    // Decision logic
    // -------------------------------------------------
    $hasValidSdc = !empty($sdcID) && !empty($receiptNumber) && !empty($sdcData);
    $hasValidCis = !empty($cisReceiptNumber);

    if ($errorMessage === 'ERROR_EXIST') {
        $submissionStatus = "Invoice with trader invoice No already exists";
        $docStatus = 'Submitted';
    } elseif ($errorMessage !== '') {
        // Real error from RRA → mark Failed so it stops being retried
        $submissionStatus = $errorMessage;
        $docStatus = 'Failed';
    } elseif ($hasValidSdc && $hasValidCis) {
        // Clean success
        $submissionStatus = 'Invoice generated successfully';
        $docStatus = 'Submitted';
    } else {
        // No error message but missing critical data → keep Pending for retry
        $submissionStatus = 'Incomplete RRA response – missing SDC/CIS data';
        $docStatus = 'Pending';
    }

    $updateData = [
        'RRA_SDC_Receipt_Number' => $receiptNumber,
        'RRA_SDC_Date'           => $sdcDate,
        'RRA_SDC_ID'             => $sdcID,
        'RRA_SDC_Data_QRCode'    => $sdcData,
        'RRA_SDC_InternalData'   => $sdcInternalData,
        'RRA_SDC_Signature'      => $sdcSignature,
        'RRA_CIS_Date'           => $formattedCisDate,
        'RRA_CIS_Receipt_Number' => $cisReceiptNumber,
        'RRA_CIS_MRC'            => $cisMRC,
        'RRA_Journal'            => $journal,
        'SubmissionStatus'       => $submissionStatus,
        'DocStatus'              => $docStatus,
    ];

    RRABillingHeader::where('ID', $invoice->ID)->update($updateData);

    if ($docStatus === 'Submitted') {
        Log::info("Invoice ID: {$invoice->ID} submitted successfully with Receipt No: {$receiptNumber}.");
    } elseif ($docStatus === 'Failed') {
        Log::error("Invoice ID: {$invoice->ID} marked as Failed. Reason: {$submissionStatus}");
    } else {
        Log::warning("Invoice ID: {$invoice->ID} kept as Pending (incomplete data). Response: " . json_encode($responseData));
    }
}



    protected function handleFailedSubmission($invoice, $responseData, $responseBody)
    {
        $errorMessage = 'Unknown error';
        $sdcData = 'N/A';
        $cisData = 'N/A';

        if ($responseData && is_array($responseData)) {
            $result = $responseData['result'] ?? [];
            $errorMessage = trim($result['error'] ?? 'Unknown error');
            $sdcData = $result['sdc_data'] ?? 'N/A';
            $cisData = $result['cis_data'] ?? 'N/A';
        }

        if ($errorMessage !== 'Unknown error') {
            RRABillingHeader::where('ID', $invoice->ID)->update([
                'DocStatus' => 'Failed',
                'SubmissionStatus' => $errorMessage,
                'RRA_SDC_Data_QRCode' => $sdcData,
                'RRA_CIS_Data' => $cisData,
            ]);

            Log::error("Invoice ID: {$invoice->ID} failed to submit. Error: {$errorMessage}");
        } else {
            Log::warning("Invoice ID: {$invoice->ID} submission response was unsuccessful.");
        }
    }
}
