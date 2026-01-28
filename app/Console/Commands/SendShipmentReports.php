<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ShipmentReportService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SendShipmentReports extends Command
{
    protected $signature = 'shipments:send-reports
                        {--test : Run in test mode without sending emails}
                        {--test-email= : Send all emails to this address}
                        {--client= : Send for specific client ID(s) (comma-separated)}
                        {--limit= : Limit number of clients to process}
                        {--export : Export summary to JSON file}';

    protected $description = 'Send shipment status reports to clients';

    protected $reportService;
    protected $startTime;

    public function __construct(ShipmentReportService $reportService)
    {
        parent::__construct();
        $this->reportService = $reportService;
    }

    public function handle()
    {
        $this->startTime = microtime(true);
        
        $this->info('========================================');
        $this->info('Shipment Report Processing');
        $this->info('Started: ' . now()->toDateTimeString());
        $this->info('========================================');

        if (!$this->reportService->validateEmailConfig()) {
            $this->error('Email configuration incomplete. Check .env');
            return 1;
        }

        if ($this->option('test')) {
            return $this->runTestMode();
        }

        try {
            $testEmail = $this->option('test-email');
            $clientIds = $this->parseClientIds($this->option('client'));
            $limit = $this->option('limit');

            // Apply limit by modifying clientIds
            if ($limit && !$clientIds) {
                $allClients = $this->reportService->getClientEmailList();
                $clientIds = $allClients->take((int) $limit)->pluck('ClientId')->toArray();
            }

            if ($testEmail) {
                $this->warn("⚠ TEST MODE: All emails → {$testEmail}");
            }

            if ($clientIds) {
                $this->info("→ Processing " . count($clientIds) . " specific clients");
            }

            if ($limit) {
                $this->info("→ Limited to first {$limit} clients");
            }

            $this->newLine();
            $this->info('⏳ Processing... (check logs for progress)');
            $this->newLine();

            $summary = $this->reportService->processAndSendReports($testEmail, $clientIds);
            
            $this->displaySummary($summary);

            if ($this->option('export')) {
                $this->exportSummary($summary);
            }

            return $summary['emails_failed'] > 0 ? 1 : 0;

        } catch (\Exception $e) {
            $this->error('Fatal error: ' . $e->getMessage());
            Log::error('Command failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }

    /**
     * Enhanced test mode - tests 3 clients instead of 1
     */
    protected function runTestMode()
    {
        $this->warn('🧪 TEST MODE - No emails will be sent');
        $this->newLine();

        try {
            $clientList = $this->reportService->getClientEmailList();
            $clientIds = $this->parseClientIds($this->option('client'));
            
            if ($clientIds) {
                $clientList = $clientList->whereIn('ClientId', $clientIds);
            }

            $total = $clientList->count();
            $this->info("📊 Found {$total} clients to process");
            $this->newLine();

            // Show sample clients
            $this->table(
                ['Client ID', 'Name', 'Service', 'Email'],
                $clientList->take(10)->map(fn($c) => [
                    substr($c->ClientId, 0, 25),
                    substr($c->ClientName, 0, 35),
                    $c->ServiceName,
                    substr($c->EmailID, 0, 30)
                ])->toArray()
            );

            if ($total > 10) {
                $this->line("... and " . ($total - 10) . " more clients");
            }

            // Test milestone data for first 3 valid clients
            $this->newLine();
            $this->info('🔍 Testing milestone retrieval (first 3 clients with email):');
            
            $validClients = $clientList
                ->where('EmailID', '!=', 'No Email ID Present')
                ->take(3);
            
            $successCount = 0;
            foreach ($validClients as $client) {
                $result = $this->reportService->getMilestoneData(
                    $client->OfficeID,
                    $client->ClientId,
                    $client->ServiceID,
                    $client->ProcessId
                );

                if ($result['success']) {
                    $this->line("  ✓ {$client->ClientName}: " . count($result['data']) . " rows");
                    $successCount++;
                } else {
                    $this->line("  ✗ {$client->ClientName}: {$result['error']}");
                }
            }

            // Statistics
            $noEmail = $clientList->where('EmailID', 'No Email ID Present')->count();
            $this->newLine();
            $this->info('📈 Statistics:');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total clients', $total],
                    ['Without email', $noEmail],
                    ['Ready to process', $total - $noEmail],
                    ['Milestone test passed', $successCount . '/3']
                ]
            );

            return 0;

        } catch (\Exception $e) {
            $this->error('Test failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Enhanced summary with performance metrics
     */
    protected function displaySummary(array $summary)
    {
        $elapsed = microtime(true) - $this->startTime;
        
        $this->newLine();
        $this->info('========================================');
        $this->info('📊 Processing Summary');
        $this->info('========================================');
        
        // Main stats
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Clients', $summary['total_clients']],
                ['✓ Emails Sent', $summary['emails_sent']],
                ['✗ Failed', $summary['emails_failed']],
                ['⚠ Milestone Failures', $summary['milestone_failures']],
                ['⊘ Skipped (No Email)', $summary['skipped_no_email']]
            ]
        );

        // Performance metrics
        $this->newLine();
        $this->info('⚡ Performance:');
        $successRate = $summary['total_clients'] > 0 
            ? round(($summary['emails_sent'] / $summary['total_clients']) * 100, 1) 
            : 0;
        
        $emailsPerMin = $elapsed > 0 
            ? round(($summary['emails_sent'] / $elapsed) * 60, 1) 
            : 0;

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Time', round($elapsed, 1) . 's (' . gmdate('i:s', $elapsed) . ')'],
                ['Avg per Client', $summary['total_clients'] > 0 ? round($elapsed / $summary['total_clients'], 2) . 's' : 'N/A'],
                ['Success Rate', $successRate . '%'],
                ['Throughput', $emailsPerMin . ' emails/min']
            ]
        );

        // Show first 5 errors if any
        if (!empty($summary['errors'])) {
            $this->newLine();
            $this->error('❌ Errors (' . count($summary['errors']) . ' total):');
            foreach (array_slice($summary['errors'], 0, 5) as $error) {
                $this->line("  • {$error['client_name']}: " . substr($error['error'], 0, 80));
            }
            if (count($summary['errors']) > 5) {
                $this->line('  ... and ' . (count($summary['errors']) - 5) . ' more (see logs)');
            }
        }

        $this->newLine();
        $this->info('Completed: ' . now()->toDateTimeString());
        $this->info('========================================');
    }

    /**
     * Export summary to JSON
     */
    protected function exportSummary(array $summary)
    {
        try {
            $filename = 'shipment-reports/summary-' . now()->format('Ymd-His') . '.json';
            
            $export = array_merge($summary, [
                'exported_at' => now()->toDateTimeString(),
                'duration_seconds' => round(microtime(true) - $this->startTime, 2),
                'command_options' => [
                    'test_email' => $this->option('test-email'),
                    'client_filter' => $this->option('client'),
                    'limit' => $this->option('limit')
                ],
                'performance' => [
                    'success_rate_percent' => $summary['total_clients'] > 0 
                        ? round(($summary['emails_sent'] / $summary['total_clients']) * 100, 2)
                        : 0,
                    'avg_time_per_client' => $summary['total_clients'] > 0
                        ? round((microtime(true) - $this->startTime) / $summary['total_clients'], 2)
                        : 0
                ]
            ]);

            Storage::put($filename, json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            
            $this->info("✓ Summary exported: storage/app/{$filename}");
            
        } catch (\Exception $e) {
            $this->warn("Could not export summary: {$e->getMessage()}");
        }
    }

    protected function parseClientIds($clientOption)
    {
        if (empty($clientOption)) {
            return null;
        }

        $ids = array_filter(array_map('trim', explode(',', $clientOption)));
        return empty($ids) ? null : $ids;
    }
}