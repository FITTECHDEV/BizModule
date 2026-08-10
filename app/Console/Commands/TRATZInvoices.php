<?php

namespace App\Console\Commands;

use App\Models\TRABillingHeader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use DateTime;

class TRATZInvoices extends Command
{
    protected $signature = 'tra_tz:invoices {invoiceId?}
                            {--list : List pending invoices}';

    protected $description = 'Generate and submit TRA invoices for FIT Tanzania via TRA API';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if ($this->option('list')) {
            $this->listPendingInvoices();
            return 0;
        }

        // Log::info('─── TRA FIT Invoice Push Started ───');

        $bearerToken = env('FITTRA_TOKEN');
        if (!$bearerToken) {
            $this->logAndFail('FITTRA_TOKEN is not set in .env. Aborting.');
            return 1;
        }

        $activeBusinessId = env('FITTRA_BUSINESS_ID');
        if (!$activeBusinessId) {
            $this->logAndFail('FITTRA_BUSINESS_ID is not set in .env. Aborting.');
            return 1;
        }

        $invoiceId = $this->argument('invoiceId');
        $pendingInvoices = $this->fetchPendingInvoices($invoiceId);

        if ($pendingInvoices->isEmpty()) {
            // Log::info('TRA FIT: No pending invoices found. Nothing to process.');
            $this->info('No pending invoices found.');
            return 0;
        }

        Log::info("TRA FIT: Found {$pendingInvoices->count()} pending invoice(s) to process.");

        if (!$this->isApiAvailable($bearerToken, $activeBusinessId)) {
            $this->logAndFail('TRA FIT: API health check failed. Skipping all invoices.');
            return 1;
        }

        foreach ($pendingInvoices as $invoice) {
            $jsonData = json_decode($invoice->TRAJsonFile, true);

            if (!$jsonData) {
                Log::error("TRA FIT: Invoice {$invoice->DocNo} (ID: {$invoice->ID}) — failed to decode TRAJsonFile. Skipping.");
                $this->error("Failed to decode JSON for invoice ID: {$invoice->ID}");
                continue;
            }

            Log::info("TRA FIT: Submitting Invoice {$invoice->DocNo} (ID: {$invoice->ID})", [
                'payload' => $jsonData,
            ]);

            $this->info("Submitting Invoice DocNo: {$invoice->DocNo}");
            $this->submitToApi($invoice, $jsonData, $bearerToken, $activeBusinessId);
        }

        Log::info('─── TRA FIT Invoice Push Completed ───');
        $this->info('TRA FIT push completed.');
        return 0;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    protected function listPendingInvoices(): void
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
            $this->info('No pending invoices found.');
            return;
        }

        $this->info('Pending Invoices (showing up to 10):');
        $this->table(
            ['ID', 'Doc No', 'Status', 'Office'],
            $invoices->map(fn($inv) => [$inv->ID, $inv->DocNo, $inv->DocStatus, $inv->OfficeCode])
        );
    }

    protected function isApiAvailable(string $bearerToken, string $activeBusinessId): bool
    {
        $url = rtrim(env('TRA_BASE_URL'), '/') . '/health';

        Log::info("TRA FIT: API health check → {$url}");

        try {
            $response = Http::withHeaders([
                'Authorization'     => 'Bearer ' . $bearerToken,
                'x-active-business' => $activeBusinessId,
                'Accept'            => 'application/json',
            ])->timeout(5)->get($url);

            $status = $response->status();
            Log::info("TRA FIT: Health check response: HTTP {$status}");

            if (!$response->ok()) {
                Log::warning('TRA FIT: Health check returned non-OK status.', ['body' => $response->body()]);
            }

            return $response->ok();
        } catch (\Exception $e) {
            Log::error('TRA FIT: Health check exception: ' . $e->getMessage());
            return false;
        }
    }

    protected function fetchPendingInvoices(?int $invoiceId = null)
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

        foreach ($invoices as $invoice) {
            try {
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
                    Log::info("TRA FIT: SP TRA_VFD executed for Invoice ID: {$invoice->ID} — JSON generated.");
                } else {
                    Log::warning("TRA FIT: SP TRA_VFD returned empty JSON for Invoice ID: {$invoice->ID}");
                }
            } catch (\Exception $e) {
                Log::error("TRA FIT: SP execution failed for Invoice ID: {$invoice->ID}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $invoices;
    }

    protected function submitToApi(TRABillingHeader $invoice, array $jsonData, string $bearerToken, string $activeBusinessId): void
    {
        $url = rtrim(env('TRA_BASE_URL'), '/') . '/sales';

        Log::info("TRA FIT: POST → {$url} for Invoice {$invoice->DocNo}");

        try {
            $response = Http::withHeaders([
                'Authorization'     => 'Bearer ' . $bearerToken,
                'x-active-business' => $activeBusinessId,
                'Content-Type'      => 'application/json',
                'Accept'            => 'application/json',
            ])
                ->timeout((int) env('FITTRA_TIMEOUT', 20))
                ->retry((int) env('FITTRA_RETRIES', 3), (int) env('FITTRA_RETRY_DELAY', 500))
                ->post($url, $jsonData);

            $httpStatus   = $response->status();
            $rawBody      = $response->body();
            $responseData = $response->json();

            Log::info("TRA FIT: Response for Invoice {$invoice->DocNo}", [
                'http_status'   => $httpStatus,
                'raw_body'      => $rawBody,
                'response_body' => $responseData,
            ]);

            $this->info("HTTP {$httpStatus} received for Invoice {$invoice->DocNo}");

            if ($httpStatus === 401) {
                $this->warn("401 Unauthorized — Bearer token is invalid or expired.");
                $this->handleFailedSubmission($invoice, $responseData, 'Bearer token invalid or expired');
                return;
            }

            if ($httpStatus === 403) {
                $errorMessage = $responseData['message'] ?? 'Invalid or inactive business';
                $this->warn("403 Forbidden — {$errorMessage}");
                $this->handleFailedSubmission($invoice, $responseData, $errorMessage);
                return;
            }

            if ($httpStatus !== 201) {
                $errorMessage = $responseData['message'] ?? $responseData['error'] ?? 'API error';
                Log::warning("TRA FIT: Invoice {$invoice->DocNo} failed — {$errorMessage}");
                $this->warn("Invoice {$invoice->DocNo} failed — {$errorMessage}");
                $this->handleFailedSubmission($invoice, $responseData, $errorMessage);
                return;
            }

            if (!is_array($responseData)) {
                $msg = "HTTP 201 but response is not valid JSON. Raw body: {$rawBody}";
                Log::error("TRA FIT: Invoice {$invoice->DocNo} — {$msg}");
                $this->error("Invoice {$invoice->DocNo} — {$msg}");
                $this->handleFailedSubmission($invoice, null, $msg);
                return;
            }

            $this->handleSuccessfulSubmission($invoice, $responseData);
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();

            Log::error("TRA FIT: Exception while submitting Invoice {$invoice->DocNo}", [
                'error' => $errorMessage,
                'trace' => $e->getTraceAsString(),
            ]);

            $responseData = null;
            if (preg_match('/\{.*\}/s', $errorMessage, $matches)) {
                $parsed = json_decode($matches[0], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $responseData = $parsed;
                    $errorMessage = $parsed['message'] ?? $errorMessage;
                }
            }

            $this->error("Invoice {$invoice->DocNo} — connection error: {$errorMessage}");
            $this->handleFailedSubmission($invoice, $responseData, $errorMessage);
        }
    }

    protected function handleSuccessfulSubmission(TRABillingHeader $invoice, array $responseData): void
    {
        $traDateTime = null;
        $localDate   = $responseData['localDate'] ?? null;
        $localTime   = $responseData['localTime'] ?? null;

        if ($localDate && $localTime) {
            try {
                $traDateTime = new DateTime("{$localDate} {$localTime}");
            } catch (\Exception $e) {
                Log::warning("TRA FIT: Could not parse TRA dateTime for Invoice {$invoice->DocNo}: {$e->getMessage()}");
            }
        }

        $updateData = [
            'DocStatus'        => 'Submitted',
            'TRASubmissionOn'  => $traDateTime?->format('Y-m-d H:i:s'),
            'SubmissionStatus' => 'Success',
            'ResponseJson'     => json_encode($responseData),
            'TRAInvoiceNo'     => $responseData['rctnum']            ?? null,
            'TRASerialNo'      => $responseData['rctvnum']           ?? null,
            'TRAQRCode'        => $responseData['verificationLink']  ?? null,
        ];

        TRABillingHeader::where('ID', $invoice->ID)->update($updateData);

        Log::info("TRA FIT: Invoice {$invoice->DocNo} (ID: {$invoice->ID}) submitted successfully.", [
            'TRAInvoiceNo'    => $updateData['TRAInvoiceNo'],
            'TRASerialNo'     => $updateData['TRASerialNo'],
            'TRAQRCode'       => $updateData['TRAQRCode'],
            'TRASubmissionOn' => $updateData['TRASubmissionOn'],
        ]);

        $this->info("Invoice {$invoice->DocNo} submitted successfully.");
    }

    protected function handleFailedSubmission(TRABillingHeader $invoice, ?array $responseData, string $errorMessage): void
    {
        $submissionStatus = !empty($errorMessage) ? $errorMessage : 'Unknown error';

        TRABillingHeader::where('ID', $invoice->ID)->update([
            'DocStatus'        => 'Failed',
            'SubmissionStatus' => $submissionStatus,
            'ResponseJson'     => $responseData ? json_encode($responseData) : null,
        ]);

        Log::error("TRA FIT: Invoice {$invoice->DocNo} (ID: {$invoice->ID}) failed.", [
            'error'         => $submissionStatus,
            'response_data' => $responseData,
        ]);
    }

    protected function logAndFail(string $message): void
    {
        Log::critical($message);
        $this->error($message);
    }
}
