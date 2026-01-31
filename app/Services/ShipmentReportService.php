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
    /**
     * Get client list from sp_ClientEmailDetails stored procedure
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getClientEmailList()
    {
        try {
            $pdo = DB::connection('sqlsrv_auth')->getPdo();
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC [dbo].[sp_EmailClientDetails]'); // Changed name
            $stmt->execute();

            $results = $stmt->fetchAll(\PDO::FETCH_OBJ);

            return collect($results);
        } catch (\Exception $e) {
            Log::error('Failed to execute sp_EmailClientDetails', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Get milestone data for a specific client/service combination
     * 
     * @param string $officeId
     * @param string $clientId
     * @param string $serviceId
     * @param string $processId
     * @return array ['success' => bool, 'data' => array|null, 'error' => string|null]
     */
    public function getMilestoneData($officeId, $clientId, $serviceId, $processId)
    {
        try {
            // Use PDO directly for proper GUID handling
            $pdo = DB::connection('sqlsrv_auth')->getPdo();

            $sql = "SET NOCOUNT ON; EXEC [dbo].[sp_Email_MilestoneStatusAsofToday] @OfficeID=?, @ClientID=?, @ServiceID=?, @ProcessID=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$officeId, $clientId, $serviceId, $processId]);

            $results = $stmt->fetchAll(\PDO::FETCH_OBJ);

            // Check if result is an error message
            if (!empty($results)) {
                $firstResult = (array) $results[0];
                $firstValue = reset($firstResult);

                if (is_string($firstValue) && strpos($firstValue, 'Failed') === 0) {
                    return [
                        'success' => false,
                        'data' => null,
                        'error' => $firstValue
                    ];
                }
            }

            return [
                'success' => true,
                'data' => $results,
                'error' => null
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'error' => 'Failed - ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process and send shipment reports to all clients
     * 
     * @param string|null $testEmail Optional test email to override all client emails
     * @param array|null $clientIds Optional array of specific client IDs to process
     * @return array Summary of sent/failed emails
     */
    public function processAndSendReports($testEmail = null, $clientIds = null)
    {
        $summary = [
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

                if ($clientList->isEmpty()) {
                    Log::warning('No clients found matching the provided IDs', [
                        'requested_ids' => $clientIds
                    ]);
                }
            }

            $summary['total_clients'] = $clientList->count();

            Log::info('Starting shipment report processing', [
                'total_clients' => $summary['total_clients'],
                'test_email' => $testEmail,
                'client_filter' => $clientIds,
                'timestamp' => now()
            ]);

            foreach ($clientList as $client) {
                $status = 'Failed';
                $remark = '';
                $shipmentCount = 0;  // ADDED - Initialize count

                try {
                    // Check for valid email first (unless using test email)
                    if (!$testEmail && $client->EmailID === 'No Email ID Present') {
                        $summary['skipped_no_email']++;
                        $remark = 'No Email ID Present';

                        Log::warning('Skipping client without email', [
                            'client_id' => $client->ClientId,
                            'client_name' => $client->ClientName
                        ]);

                        // Log to Phase 3 with 0 shipments
                        $this->logSchedulerTransaction($client, $status, $remark, 0);  // MODIFIED
                        usleep(100000);
                        continue;
                    }

                    // Phase 2: Get milestone data
                    $milestoneResult = $this->getMilestoneData(
                        $client->OfficeID,
                        $client->ClientId,
                        $client->ServiceID,
                        $client->ProcessId
                    );

                    if (!$milestoneResult['success']) {
                        // Milestone failed - don't send email
                        $summary['milestone_failures']++;
                        $remark = $milestoneResult['error'];

                        Log::warning('Milestone data unavailable for client', [
                            'client_id' => $client->ClientId,
                            'client_name' => $client->ClientName,
                            'error' => $remark
                        ]);

                        // Log to Phase 3 with 0 shipments
                        $shipmentCount = (int) ($client->NoofShipment ?? 0);
                        $this->logSchedulerTransaction($client, $status, $remark, $shipmentCount);
                        // MODIFIED

                        sleep(config('shipment.email.delay_between_sends', 2));
                        continue;
                    }

                    if (!property_exists($client, 'NoofShipment')) {
                        Log::error('NoofShipment missing from client object', [
                            'client_id' => $client->ClientId,
                            'available_fields' => array_keys((array) $client)
                        ]);
                    }


                    // ADDED - Calculate shipment count from milestone data
                    $shipmentCount = (int) ($client->NoofShipment ?? 0);

                    // Milestone success - send email with Excel attachment
                    $emailAddress = $testEmail ?? $client->EmailID;

                    // Retry logic for email sending
                    $maxRetries = config('shipment.email.max_retries', 3);
                    $retryDelay = config('shipment.email.retry_delay', 5);
                    $attempt = 0;
                    $emailSent = false;

                    while ($attempt < $maxRetries && !$emailSent) {
                        try {
                            $attempt++;

                            if ($attempt > 1) {
                                $this->resetMailConnection();
                            }

                            $this->sendReportEmail(
                                $emailAddress,
                                $client->EmailSubject,
                                $client->BodyDetails,
                                $milestoneResult['data'],
                                $client
                            );
                            $emailSent = true;
                        } catch (\Exception $e) {
                            if ($attempt >= $maxRetries) {
                                throw $e;
                            }
                            Log::warning('Email send failed, retrying', [
                                'attempt' => $attempt,
                                'max_retries' => $maxRetries,
                                'client_id' => $client->ClientId,
                                'error' => $e->getMessage()
                            ]);

                            $this->resetMailConnection();
                            sleep($retryDelay);
                        }
                    }

                    $status = 'Success';
                    $remark = $testEmail
                        ? "Email sent successfully to test address: {$testEmail}"
                        : 'Email sent successfully';
                    $summary['emails_sent']++;

                    Log::info('Email sent successfully', [
                        'client_id' => $client->ClientId,
                        'client_name' => $client->ClientName,
                        'email' => $emailAddress,
                        'service' => $client->ServiceName,
                        'shipment_count' => $shipmentCount,  // ADDED
                        'test_mode' => !is_null($testEmail)
                    ]);
                } catch (\Exception $e) {
                    $summary['emails_failed']++;
                    $status = 'Failed';
                    $remark = 'Failed - ' . $e->getMessage();

                    $summary['errors'][] = [
                        'client_id' => $client->ClientId,
                        'client_name' => $client->ClientName,
                        'error' => $e->getMessage()
                    ];

                    Log::error('Failed to process client', [
                        'client_id' => $client->ClientId,
                        'client_name' => $client->ClientName,
                        'email' => $testEmail ?? $client->EmailID,
                        'error' => $e->getMessage()
                    ]);
                }

                // Phase 3: Log transaction with shipment count
                $this->logSchedulerTransaction($client, $status, $remark, $shipmentCount);  // MODIFIED

                sleep(config('shipment.email.delay_between_sends', 2));
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
     * Send shipment report email to a client with Excel attachment
     * 
     * @param string $emailAddress
     * @param string $subject
     * @param string $htmlBody HTML content from SP
     * @param array $milestoneData Milestone data for Excel attachment
     * @param object $client Client object for filename generation
     * @return void
     * @throws \Exception
     */
    protected function sendReportEmail($emailAddress, $subject, $htmlBody, $milestoneData, $client)
    {
        try {
            $excelFilePath = $this->generateExcelFromMilestoneData(
                $milestoneData,
                $client->ClientName,
                $client->ServiceName
            );

            if (!$excelFilePath) {
                throw new \Exception('Failed to generate Excel attachment');
            }

            $fileSize = filesize($excelFilePath);
            $maxSizeMB = config('shipment.email.max_attachment_size_mb', 10);
            if ($fileSize > $maxSizeMB * 1024 * 1024) {
                throw new \Exception('Excel file too large: ' . round($fileSize / 1024 / 1024, 2) . 'MB (max: ' . $maxSizeMB . 'MB)');
            }

            // Parse TO email addresses (semicolon or comma separated)
            $toAddresses = array_map('trim', preg_split('/[;,]/', $emailAddress));
            $toAddresses = array_filter($toAddresses);

            // Parse CC email addresses (semicolon or comma separated)
            $ccAddresses = array_map('trim', preg_split('/[;,]/', $client->EmailIdCC ?? ''));
            $ccAddresses = array_filter($ccAddresses);

            // DEBUG: Check what we're getting
            Log::info('CC Email Debug', [
                'client_id' => $client->ClientId,
                'EmailIdCC_raw' => $client->EmailIdCC ?? 'NULL/EMPTY',  // Changed
                'cc_addresses_parsed' => $ccAddresses,
                'cc_count' => count($ccAddresses),
                'is_empty' => empty($ccAddresses)
            ]);

            // Ensure at least one recipient exists
            if (empty($toAddresses) && empty($ccAddresses)) {
                throw new \Exception('No email addresses found for client: ' . $client->ClientName);
            }

            // Create Swift attachment
            $attachment = \Swift_Attachment::fromPath($excelFilePath)
                ->setFilename(basename($excelFilePath))
                ->setContentType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

            // Send using SwiftMailer directly
            $swiftMessage = new \Swift_Message($subject);
            $swiftMessage->setFrom([config('mail.from.address') => config('mail.from.name')])
                ->setBcc([config('mail.from.address')]);

            // Set TO if exists
            if (!empty($toAddresses)) {
                $swiftMessage->setTo($toAddresses);
            }

            // Set CC if exists
            if (!empty($ccAddresses)) {
                $swiftMessage->setCc($ccAddresses);
            }

            $swiftMessage->setBody($htmlBody, 'text/html')
                ->attach($attachment);

            Log::info('About to send email', [
                'to' => $toAddresses,
                'cc' => $ccAddresses,
                'from' => config('mail.from.address'),
                'smtp_host' => config('mail.mailers.smtp.host'),
                'smtp_port' => config('mail.mailers.smtp.port'),
            ]);

            Mail::getSwiftMailer()->send($swiftMessage);

            // $this->saveToSentFolder($emailAddress, $subject, $htmlBody, $client);

            Log::info('Email sent with Excel attachment', [
                'to' => implode(', ', $toAddresses),
                'cc' => implode(', ', $ccAddresses),
                'attachment' => basename($excelFilePath),
                'size' => $fileSize
            ]);

            if (file_exists($excelFilePath)) {
                unlink($excelFilePath);
                Log::debug('Temporary Excel file deleted', ['file' => $excelFilePath]);
            }
        } catch (\Exception $e) {
            if (isset($excelFilePath) && file_exists($excelFilePath)) {
                unlink($excelFilePath);
            }

            Log::error('Failed to send email with attachment', [
                'email' => $emailAddress,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Generate Excel file from milestone data
     * 
     * @param array $milestoneData Database result set from SP
     * @param string $clientName Client name for filename
     * @param string $serviceName Service name for filename
     * @return string|false File path on success, false on failure
     */
    protected function generateExcelFromMilestoneData($milestoneData, $clientName, $serviceName)
    {
        try {
            // Validate milestone data
            if (empty($milestoneData)) {
                Log::warning('No milestone data to generate Excel');
                return false;
            }

            // Create temp directory if it doesn't exist
            $tempDir = storage_path('app/temp/shipment-reports');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Generate filename
            $sanitizedClient = preg_replace('/[^A-Za-z0-9_-]/', '_', $clientName);
            $sanitizedService = preg_replace('/[^A-Za-z0-9_-]/', '_', $serviceName);
            $filename = sprintf(
                '%s_%s_%s.xlsx',
                $sanitizedClient,
                $sanitizedService,
                now()->format('Ymd_His')
            );
            $filepath = $tempDir . '/' . $filename;

            // Convert stdClass objects to arrays
            $data = array_map(function ($row) {
                return (array) $row;
            }, $milestoneData);

            // Get headers from first row
            $headers = array_keys($data[0]);

            // Create Excel using PhpSpreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Shipment Status');

            // Write headers
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $sheet->getStyle($col . '1')->getFont()->setBold(true);
                $sheet->getStyle($col . '1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF4472C4');
                $col++;
            }

            // Write data
            $rowNum = 2;
            foreach ($data as $row) {
                $col = 'A';
                foreach ($headers as $header) {
                    $value = $row[$header] ?? '';
                    $sheet->setCellValue($col . $rowNum, $value);
                    $col++;
                }
                $rowNum++;
            }

            // Auto-size columns
            foreach (range('A', $sheet->getHighestColumn()) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Save Excel file
            $writer = new Xlsx($spreadsheet);
            $writer->save($filepath);

            Log::info('Excel file generated successfully', [
                'filepath' => $filepath,
                'rows' => count($data),
                'size' => filesize($filepath)
            ]);

            return $filepath;
        } catch (\Exception $e) {
            Log::error('Failed to generate Excel file', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
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

    /**
     * Log email scheduler transaction to database
     * 
     * @param object $client Client data from sp_ClientEmailDetails
     * @param string $status "Success" or "Failed"
     * @param string $remark Success/failure message
     * @param int $shipmentCount Number of shipments in the report (default 0)
     * @return array ['success' => bool, 'sp_status' => string|null, 'sp_remark' => string|null]
     */
    protected function logSchedulerTransaction($client, $status, $remark, $shipmentCount = 0)
    {
        try {
            $json = json_encode([
                'SchedulerType' => 'MilestoneUpdate',
                'SubmittedOn' => now()->format('Y-m-d H:i:s'),
                'ClientID' => $client->ClientId,
                'ClientName' => $client->ClientName,
                'OfficeID' => $client->OfficeID,
                'OfficeName' => $client->OfficeName,
                'ServiceID' => $client->ServiceID,
                'ProcessID' => $client->ProcessId,
                'ServiceName' => $client->ServiceName,
                'NoofShipment' => $shipmentCount,
                'EmailFrom' => config('mail.from.address'),
                'EmailTo' => $client->EmailID,
                'EmailIDCC' => $client->EmailIdCC ?? '',
                'Subject' => $client->EmailSubject,
                'BodyMessage' => $client->BodyDetails ?? '<body></body>',
                'Status' => $status,
                'SendRemark' => $remark
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            Log::info('Attempting to log transaction', [
                'client_id' => $client->ClientId,
                'shipment_count' => $shipmentCount
            ]);

            $pdo = DB::connection('sqlsrv_auth')->getPdo();

            // Declare output variables and execute the stored procedure
            $sql = "
            DECLARE @Status VARCHAR(20);
            DECLARE @Remark VARCHAR(2000);
            
            EXEC [dbo].[sp_EmailSchedulerDetailsUpdate] 
                @JsonData = :json_data,
                @Status = @Status OUTPUT,
                @Remark = @Remark OUTPUT;
            
            SELECT @Status AS Status, @Remark AS Remark;
        ";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':json_data', $json, \PDO::PARAM_STR);
            $stmt->execute();

            // SQL Server may return multiple result sets (from PRINT, INSERT, etc.)
            // We need to advance to the final SELECT statement
            $result = null;
            do {
                try {
                    $row = $stmt->fetch(\PDO::FETCH_OBJ);
                    if ($row !== false) {
                        $result = $row;
                    }
                } catch (\PDOException $e) {
                    // Empty result set - continue to next
                }
            } while ($stmt->nextRowset());

            // Check if we got the output parameters
            if (!$result) {
                throw new \Exception('Failed to retrieve output parameters from stored procedure');
            }

            $spStatus = $result->Status ?? null;
            $spRemark = $result->Remark ?? null;

            if ($spStatus === 'Success') {
                Log::info('Scheduler transaction logged successfully', [
                    'client_id' => $client->ClientId,
                    'status' => $status,
                    'shipment_count' => $shipmentCount,
                    'sp_status' => $spStatus,
                    'sp_remark' => $spRemark
                ]);

                return [
                    'success' => true,
                    'sp_status' => $spStatus,
                    'sp_remark' => $spRemark
                ];
            } else {
                Log::warning('Stored procedure reported failure', [
                    'client_id' => $client->ClientId,
                    'sp_status' => $spStatus,
                    'sp_remark' => $spRemark
                ]);

                return [
                    'success' => false,
                    'sp_status' => $spStatus,
                    'sp_remark' => $spRemark
                ];
            }
        } catch (\Exception $e) {
            Log::error('Failed to log scheduler transaction', [
                'client_id' => $client->ClientId,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'sp_status' => null,
                'sp_remark' => $e->getMessage()
            ];
        }
    }

    // // Add this method to ShipmentReportService.php
    // protected function saveToSentFolder($emailAddress, $subject, $htmlBody, $client)
    // {
    //     try {
    //         $sentFolder = config('mail.imap.sent_folder', 'Sent');

    //         $imap = imap_open(
    //             '{' . config('mail.imap.host') . ':' . config('mail.imap.port') . '/imap/ssl}' . $sentFolder,
    //             config('mail.from.address'),
    //             config('mail.imap.password')
    //         );

    //         if ($imap) {
    //             $message = "From: " . config('mail.from.address') . "\r\n";
    //             $message .= "To: {$emailAddress}\r\n";
    //             $message .= "Subject: {$subject}\r\n";
    //             $message .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
    //             $message .= $htmlBody;

    //             imap_append($imap, '{' . config('mail.imap.host') . ':' . config('mail.imap.port') . '/imap/ssl}' . $sentFolder, $message);
    //             imap_close($imap);
    //         }
    //     } catch (\Exception $e) {
    //         Log::warning('Failed to save to sent folder', ['error' => $e->getMessage()]);
    //     }
    // }


    /**
     * Reset SMTP connection to avoid "too many messages" errors
     * 
     * @return void
     */
    protected function resetMailConnection()
    {
        try {
            $transport = Mail::getSwiftMailer()->getTransport();
            if ($transport instanceof \Swift_SmtpTransport && $transport->isStarted()) {
                $transport->stop();
            }
        } catch (\Exception $e) {
            Log::warning('Failed to reset mail connection', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
