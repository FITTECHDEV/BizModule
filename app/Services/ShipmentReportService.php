<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ShipmentReportService
{
    private $stats = [
        'total_clients' => 0,
        'emails_sent' => 0,
        'emails_failed' => 0,
        'skipped_no_email' => 0,
        'milestone_failures' => 0,
        'errors' => []
    ];

    /**
     * Get client list from stored procedure
     */
    public function getClientEmailList()
    {
        try {
            $pdo = DB::connection('sqlsrv_auth')->getPdo();
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC [dbo].[sp_EmailClientDetails]');
            $stmt->execute();
            
            return collect($stmt->fetchAll(\PDO::FETCH_OBJ));
        } catch (\Exception $e) {
            Log::error('Failed to execute sp_EmailClientDetails', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get milestone data for specific client/service
     */
    public function getMilestoneData($officeId, $clientId, $serviceId, $processId)
    {
        try {
            $pdo = DB::connection('sqlsrv_auth')->getPdo();
            $sql = "SET NOCOUNT ON; EXEC [dbo].[sp_MilestoneStatusAsofToday] @OfficeID=?, @ClientID=?, @ServiceID=?, @ProcessID=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$officeId, $clientId, $serviceId, $processId]);
            
            $results = $stmt->fetchAll(\PDO::FETCH_OBJ);

            if (!empty($results)) {
                $firstResult = (array) $results[0];
                $firstValue = reset($firstResult);

                if (is_string($firstValue) && strpos($firstValue, 'Failed') === 0) {
                    return ['success' => false, 'data' => null, 'error' => $firstValue];
                }
            }

            return ['success' => true, 'data' => $results, 'error' => null];
        } catch (\Exception $e) {
            return ['success' => false, 'data' => null, 'error' => 'Failed - ' . $e->getMessage()];
        }
    }

    /**
     * Main processing method
     */
    public function processAndSendReports($testEmail = null, $clientIds = null)
    {
        $this->stats = [
            'total_clients' => 0,
            'emails_sent' => 0,
            'emails_failed' => 0,
            'skipped_no_email' => 0,
            'milestone_failures' => 0,
            'errors' => []
        ];

        try {
            $clientList = $this->getClientEmailList();

            if ($clientIds !== null && !empty($clientIds)) {
                $clientList = $clientList->whereIn('ClientId', $clientIds);
            }

            $this->stats['total_clients'] = $clientList->count();

            Log::info('Starting shipment report processing', [
                'total_clients' => $this->stats['total_clients'],
                'test_email' => $testEmail,
                'timestamp' => now()
            ]);

            $processedCount = 0;

            foreach ($clientList as $client) {
                $processedCount++;
                
                // Progress logging
                if ($processedCount % config('shipment_reports.processing.progress_log_interval', 10) === 0) {
                    Log::info("Progress: {$processedCount}/{$this->stats['total_clients']}", [
                        'sent' => $this->stats['emails_sent'],
                        'failed' => $this->stats['emails_failed']
                    ]);
                }

                $this->processClient($client, $testEmail);
                
                // Delay between emails
                if ($this->stats['emails_sent'] > 0) {
                    sleep(config('shipment_reports.email.delay_between_sends', 2));
                }
            }

            Log::info('Processing completed', $this->stats);

        } catch (\Exception $e) {
            Log::error('Fatal error in processing', [
                'error' => $e->getMessage(),
                'stats' => $this->stats
            ]);
            throw $e;
        }

        return $this->stats;
    }

    /**
     * Process single client
     */
    private function processClient($client, $testEmail)
    {
        $status = 'Failed';
        $remark = '';

        try {
            // Check email
            if (!$testEmail && $client->EmailID === 'No Email ID Present') {
                $this->stats['skipped_no_email']++;
                $remark = 'No Email ID Present';
                
                Log::warning('No email for client', [
                    'client_id' => $client->ClientId,
                    'client_name' => $client->ClientName
                ]);
                
                $this->logTransaction($client, $status, $remark);
                return;
            }

            // Get milestone data
            $milestoneResult = $this->getMilestoneData(
                $client->OfficeID,
                $client->ClientId,
                $client->ServiceID,
                $client->ProcessId
            );

            if (!$milestoneResult['success']) {
                $this->stats['milestone_failures']++;
                $remark = $milestoneResult['error'];
                
                Log::warning('Milestone unavailable', [
                    'client_id' => $client->ClientId,
                    'client_name' => $client->ClientName,
                    'error' => $remark
                ]);
                
                $this->logTransaction($client, $status, $remark);
                return;
            }

            // Send email with retry
            $emailAddress = $testEmail ?? $client->EmailID;
            $maxRetries = config('shipment_reports.email.max_retries', 3);
            
            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    $this->sendReportEmail(
                        $emailAddress,
                        $client->EmailSubject,
                        $client->BodyDeails,
                        $milestoneResult['data'],
                        $client
                    );

                    $status = 'Success';
                    $remark = $testEmail ? "Sent to test: {$testEmail}" : 'Email sent successfully';
                    $this->stats['emails_sent']++;
                    
                    Log::info('Email sent', [
                        'client_id' => $client->ClientId,
                        'client_name' => $client->ClientName,
                        'email' => $emailAddress,
                        'test_mode' => !is_null($testEmail)
                    ]);
                    
                    break; // Success, exit retry loop

                } catch (\Exception $e) {
                    if ($attempt < $maxRetries) {
                        Log::warning("Email attempt {$attempt} failed, retrying", [
                            'client_id' => $client->ClientId,
                            'error' => $e->getMessage()
                        ]);
                        sleep(config('shipment_reports.email.retry_delay', 5));
                    } else {
                        throw $e; // Last attempt failed
                    }
                }
            }

        } catch (\Exception $e) {
            $this->stats['emails_failed']++;
            $remark = 'Failed - ' . $e->getMessage();
            
            $this->stats['errors'][] = [
                'client_id' => $client->ClientId,
                'client_name' => $client->ClientName,
                'error' => $e->getMessage()
            ];
            
            Log::error('Failed to process client', [
                'client_id' => $client->ClientId,
                'client_name' => $client->ClientName,
                'error' => $e->getMessage()
            ]);
        }

        $this->logTransaction($client, $status, $remark);
    }

    /**
     * Send email with Excel attachment
     */
    private function sendReportEmail($emailAddress, $subject, $htmlBody, $milestoneData, $client)
    {
        $excelFilePath = null;

        try {
            $excelFilePath = $this->generateExcel($milestoneData, $client->ClientName, $client->ServiceName);

            if (!$excelFilePath) {
                throw new \Exception('Failed to generate Excel');
            }

            $fileSize = filesize($excelFilePath);
            $maxSize = config('shipment_reports.email.max_attachment_size_mb', 10) * 1024 * 1024;
            
            if ($fileSize > $maxSize) {
                throw new \Exception('Excel too large: ' . round($fileSize / 1024 / 1024, 2) . 'MB');
            }

            // Parse email addresses
            $emails = array_filter(array_map('trim', preg_split('/[;,]/', $emailAddress)));
            
            foreach ($emails as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new \Exception("Invalid email: {$email}");
                }
            }

            // Send email
            Mail::send([], [], function ($message) use ($emails, $subject, $htmlBody, $excelFilePath) {
                $message->to($emails)
                    ->subject($subject)
                    ->html($htmlBody)
                    ->attach($excelFilePath, [
                        'as' => basename($excelFilePath),
                        'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    ]);
            });

            Log::info('Email sent with attachment', [
                'recipients' => implode(', ', $emails),
                'attachment' => basename($excelFilePath),
                'size' => $fileSize
            ]);

        } finally {
            if ($excelFilePath && file_exists($excelFilePath)) {
                unlink($excelFilePath);
                Log::debug('Temp file deleted', ['file' => basename($excelFilePath)]);
            }
        }
    }

    /**
     * Generate Excel file
     */
    private function generateExcel($milestoneData, $clientName, $serviceName)
    {
        try {
            if (empty($milestoneData)) {
                return false;
            }

            $tempDir = config('shipment_reports.temp_directory');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $sanitizedClient = preg_replace('/[^A-Za-z0-9_-]/', '_', $clientName);
            $sanitizedService = preg_replace('/[^A-Za-z0-9_-]/', '_', $serviceName);
            $filename = sprintf('%s_%s_%s.xlsx', $sanitizedClient, $sanitizedService, now()->format('Ymd_His'));
            $filepath = $tempDir . '/' . $filename;

            $data = array_map(fn($row) => (array) $row, $milestoneData);
            $headers = array_keys($data[0]);

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Shipment Status');

            // Headers
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $sheet->getStyle($col . '1')->getFont()->setBold(true);
                $sheet->getStyle($col . '1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF4472C4');
                $col++;
            }

            // Data
            $rowNum = 2;
            foreach ($data as $row) {
                $col = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue($col . $rowNum, $row[$header] ?? '');
                    $col++;
                }
                $rowNum++;
            }

            // Auto-size
            foreach (range('A', $sheet->getHighestColumn()) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($filepath);

            Log::info('Excel generated', [
                'file' => $filename,
                'rows' => count($data),
                'size' => filesize($filepath)
            ]);

            return $filepath;

        } catch (\Exception $e) {
            Log::error('Excel generation failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Validate email configuration
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

    /**
     * Log transaction to database
     */
    private function logTransaction($client, $status, $remark)
    {
        try {
            $json = json_encode([
                'transactionType' => 'EmailScheduler',
                'SchedulerType' => 'MilestoneUpdate',
                'SubmittedOn' => now()->format('Y-m-d H:i:s'),
                'ClientID' => $client->ClientId,
                'ClientName' => $client->ClientName,
                'OfficeID' => $client->OfficeID,
                'OfficeName' => $client->OfficeName,
                'ServiceID' => $client->ServiceID,
                'ProcessID' => $client->ProcessId,
                'ServiceName' => $client->ServiceName,
                'EmailFrom' => config('mail.from.address'),
                'EmailTo' => $client->EmailID,
                'Subject' => $client->EmailSubject,
                'BodyMessage' => $client->BodyDeails ?? '<body></body>',
                'Status' => $status,
                'SendRemark' => $remark
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $pdo = DB::connection('sqlsrv_auth')->getPdo();
            $sql = "DECLARE @OutStatus VARCHAR(20), @OutRemark VARCHAR(2000);
                    EXEC [dbo].[sp_EmailSchedulerDetailsUpdate] 
                        @JsonData = ?, 
                        @Status = @OutStatus OUTPUT, 
                        @Remark = @OutRemark OUTPUT;";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$json]);

            Log::debug('Transaction logged', ['client_id' => $client->ClientId, 'status' => $status]);

        } catch (\Exception $e) {
            Log::error('Failed to log transaction', [
                'client_id' => $client->ClientId,
                'error' => $e->getMessage()
            ]);
        }
    }
}