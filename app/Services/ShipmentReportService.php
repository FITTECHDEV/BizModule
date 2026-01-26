<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ShipmentReportService
{
    /**
     * Get client list from sp_ClientEmailDetails stored procedure
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getClientEmailList()
    {
        try {
            $results = DB::connection('sqlsrv')
                ->select('EXEC [dbo].[sp_ClientEmailDetails]');

            return collect($results);
        } catch (\Exception $e) {
            Log::error('Failed to execute sp_ClientEmailDetails', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Get milestone data for a specific client/service combination
     * 
     * @param int $clientId
     * @param int $serviceId
     * @param int $processId
     * @return \Illuminate\Support\Collection
     * 
     * TODO: Implement this method once milestone SP is provided
     */
    public function getMilestoneData($clientId, $serviceId, $processId)
    {
        // Placeholder - will be implemented when milestone SP is available
        // Expected to call something like:
        // EXEC [dbo].[sp_MilestoneData] @ClientId=?, @ServiceId=?, @ProcessId=?
        
        return collect([]);
    }

    /**
     * Process and send shipment reports to all clients
     * 
     * @return array Summary of sent/failed emails
     */
    public function processAndSendReports()
    {
        $summary = [
            'total_clients' => 0,
            'emails_sent' => 0,
            'emails_failed' => 0,
            'skipped_no_email' => 0,
            'errors' => []
        ];

        try {
            $clientList = $this->getClientEmailList();
            $summary['total_clients'] = $clientList->count();

            Log::info('Starting shipment report processing', [
                'total_clients' => $summary['total_clients'],
                'timestamp' => now()
            ]);

            foreach ($clientList as $client) {
                try {
                    // Skip clients without valid email
                    if ($client->EmailID === 'No Email ID Present') {
                        $summary['skipped_no_email']++;
                        Log::warning('Skipping client without email', [
                            'client_id' => $client->ClientId,
                            'client_name' => $client->ClientName
                        ]);
                        continue;
                    }

                    // Prepare email data
                    $emailData = [
                        'serviceType' => $client->ServiceName,
                        'reportDate' => Carbon::now()->format('d-m-Y'),
                        'clientName' => $client->ClientName
                    ];

                    // Send email
                    $this->sendReportEmail(
                        $client->EmailID,
                        $client->EmailSubject,
                        $emailData
                    );

                    $summary['emails_sent']++;

                    Log::info('Email sent successfully', [
                        'client_id' => $client->ClientId,
                        'client_name' => $client->ClientName,
                        'email' => $client->EmailID,
                        'service' => $client->ServiceName
                    ]);

                } catch (\Exception $e) {
                    $summary['emails_failed']++;
                    $summary['errors'][] = [
                        'client_id' => $client->ClientId,
                        'client_name' => $client->ClientName,
                        'error' => $e->getMessage()
                    ];

                    Log::error('Failed to send email to client', [
                        'client_id' => $client->ClientId,
                        'client_name' => $client->ClientName,
                        'email' => $client->EmailID,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Shipment report processing completed', $summary);

        } catch (\Exception $e) {
            Log::error('Fatal error in shipment report processing', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }

        return $summary;
    }

    /**
     * Send shipment report email to a client
     * 
     * @param string $emailAddress
     * @param string $subject
     * @param array $data
     * @return void
     */
    protected function sendReportEmail($emailAddress, $subject, $data)
    {
        Mail::send('emails.shipment-status-report', $data, function ($message) use ($emailAddress, $subject) {
            $message->to($emailAddress)
                    ->subject($subject)
                    ->from(config('mail.from.address'), config('mail.from.name'));
        });
    }

    /**
     * Validate email configuration
     * 
     * @return bool
     */
    public function validateEmailConfig()
    {
        $requiredConfigs = [
            'mail.mailers.smtp.host',
            'mail.mailers.smtp.port',
            'mail.from.address'
        ];

        foreach ($requiredConfigs as $config) {
            if (empty(config($config))) {
                Log::error("Missing email configuration: {$config}");
                return false;
            }
        }

        return true;
    }
}