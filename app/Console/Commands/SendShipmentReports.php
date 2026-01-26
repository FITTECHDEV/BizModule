<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ShipmentReportService;
use Illuminate\Support\Facades\Log;

class SendShipmentReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipments:send-reports
                            {--test : Run in test mode without sending emails}
                            {--client= : Send report for specific client ID only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send shipment status reports to clients based on sp_ClientEmailDetails';

    /**
     * Shipment report service instance
     *
     * @var ShipmentReportService
     */
    protected $reportService;

    /**
     * Create a new command instance.
     *
     * @param ShipmentReportService $reportService
     * @return void
     */
    public function __construct(ShipmentReportService $reportService)
    {
        parent::__construct();
        $this->reportService = $reportService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('========================================');
        $this->info('Starting Shipment Report Processing');
        $this->info('Time: ' . now()->toDateTimeString());
        $this->info('========================================');

        // Validate email configuration
        if (!$this->reportService->validateEmailConfig()) {
            $this->error('Email configuration is incomplete. Please check your .env file.');
            Log::error('Shipment report command failed: Invalid email configuration');
            return 1;
        }

        // Test mode
        if ($this->option('test')) {
            $this->warn('Running in TEST MODE - No emails will be sent');
            return $this->runTestMode();
        }

        try {
            // Process and send reports
            $summary = $this->reportService->processAndSendReports();

            // Display summary
            $this->displaySummary($summary);

            // Return success if no failures, warning if partial success
            return $summary['emails_failed'] > 0 ? 1 : 0;

        } catch (\Exception $e) {
            $this->error('Fatal error occurred: ' . $e->getMessage());
            Log::error('Shipment report command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Run command in test mode (fetch data but don't send emails)
     *
     * @return int
     */
    protected function runTestMode()
    {
        try {
            $clientList = $this->reportService->getClientEmailList();

            $this->info("Found {$clientList->count()} clients to process");
            $this->newLine();

            // Display sample data
            $this->table(
                ['Client ID', 'Client Name', 'Office', 'Service', 'Email'],
                $clientList->take(10)->map(function ($client) {
                    return [
                        $client->ClientId,
                        $client->ClientName,
                        $client->OfficeName,
                        $client->ServiceName,
                        $client->EmailID
                    ];
                })->toArray()
            );

            if ($clientList->count() > 10) {
                $this->info('... and ' . ($clientList->count() - 10) . ' more clients');
            }

            $noEmailCount = $clientList->where('EmailID', 'No Email ID Present')->count();
            if ($noEmailCount > 0) {
                $this->warn("Warning: {$noEmailCount} clients have no valid email address");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('Test mode failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Display summary of email sending results
     *
     * @param array $summary
     * @return void
     */
    protected function displaySummary(array $summary)
    {
        $this->newLine();
        $this->info('========================================');
        $this->info('Report Processing Summary');
        $this->info('========================================');
        $this->info("Total Clients: {$summary['total_clients']}");
        $this->info("Emails Sent: {$summary['emails_sent']}");
        
        if ($summary['emails_failed'] > 0) {
            $this->error("Emails Failed: {$summary['emails_failed']}");
        } else {
            $this->info("Emails Failed: {$summary['emails_failed']}");
        }
        
        if ($summary['skipped_no_email'] > 0) {
            $this->warn("Skipped (No Email): {$summary['skipped_no_email']}");
        }

        // Display errors if any
        if (!empty($summary['errors'])) {
            $this->newLine();
            $this->error('Errors encountered:');
            foreach ($summary['errors'] as $error) {
                $this->error("  - Client {$error['client_name']} (ID: {$error['client_id']}): {$error['error']}");
            }
        }

        $this->newLine();
        $this->info('Processing completed at: ' . now()->toDateTimeString());
        $this->info('========================================');
    }
}