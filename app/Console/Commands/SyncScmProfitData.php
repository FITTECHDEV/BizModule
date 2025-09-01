<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SyncScmProfitData extends Command
{
    protected $signature = 'sync:scmprofit
                            {--date= : Specific date to sync (Y-m-d format)}
                            {--start-date= : Start date for sync (Y-m-d format)}
                            {--batch-size=100 : Number of records to process in each batch}
                            {--dry-run : Show what would be synced without actually syncing}
                            {--debug : Enable debug mode with detailed output}';

    protected $description = 'Sync data from PostgreSQL vw_scmprofit_integration to SQL Server CRM_SCMprofit_integration';

    private $batchSize;
    private $isDryRun;
    private $stats = [
        'processed' => 0,
        'inserted' => 0,
        'updated' => 0,
        'errors' => 0,
        'error_details' => [],
        'start_time' => null,
        'end_time' => null,
    ];

    public function handle()
    {
        $this->stats['start_time'] = Carbon::now();
        $this->batchSize = (int) $this->option('batch-size');
        $this->isDryRun = $this->option('dry-run');

        try {
            $this->info("Starting SCM Profit Data Sync...");

            if ($this->isDryRun) {
                $this->warn("DRY RUN MODE - No actual changes will be made");
            }

            $dates = $this->getDatesForSync();
            $this->info("Syncing data from {$dates['start']} to {$dates['current']}");

            $this->syncData($dates);
            $this->displaySummary();
        } catch (\Exception $e) {
            $this->error("Critical error: " . $e->getMessage());
            Log::error('SyncScmProfitData failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }

    private function getDatesForSync(): array
    {
        $currentDate = Carbon::now()->format('Y-m-d');

        if ($this->option('date')) {
            $specificDate = $this->option('date');
            $this->validateDate($specificDate);
            return ['start' => $specificDate, 'current' => $specificDate];
        }

        $startDate = $this->option('start-date') ?: '2025-02-03';
        $this->validateDate($startDate);

        return ['start' => $startDate, 'current' => $currentDate];
    }

    private function validateDate(string $date): void
    {
        if (!Carbon::createFromFormat('Y-m-d', $date)) {
            throw new \InvalidArgumentException("Invalid date format: {$date}. Expected Y-m-d format.");
        }
    }

    private function syncData(array $dates): void
    {
        $totalRecords = $this->getTotalRecords($dates);

        if ($totalRecords === 0) {
            $this->info("No records found for the specified date range.");
            return;
        }

        $this->info("Total records to process: {$totalRecords}");

        $progressBar = $this->output->createProgressBar($totalRecords);
        $progressBar->start();

        $offset = 0;

        while ($offset < $totalRecords) {
            $records = $this->getRecordsBatch($dates, $offset, $this->batchSize);

            if (empty($records)) {
                break;
            }

            $this->processBatch($records);
            $progressBar->advance(count($records));

            $offset += $this->batchSize;
        }

        $progressBar->finish();
        $this->newLine();
    }

    private function getTotalRecords(array $dates): int
{
    return DB::connection('pgsql')->selectOne("
        SELECT COUNT(*) as total
        FROM vw_scmprofit_integration
        WHERE CAST(CreatedOn AS date) = CURRENT_DATE
           OR CAST(UpdatedOn AS date) = CURRENT_DATE
    ")->total;
      Log::info("SCM Sync: Found {$total} records to process for today.");

    return $total;
}


  private function getRecordsBatch(array $dates, int $offset, int $limit): array
{
    return DB::connection('pgsql')->select(
        "
        SELECT *
        FROM vw_scmprofit_integration
        WHERE CAST(CreatedOn AS date) = CURRENT_DATE
           OR CAST(UpdatedOn AS date) = CURRENT_DATE
        ORDER BY bookingno
        LIMIT ? OFFSET ?",
        [$limit, $offset]
    );
    Log::info("SCM Sync: Fetched batch with offset {$offset}, limit {$limit}, count: " . count($records));

    return $records;
}


    private function processBatch(array $records): void
    {
        DB::connection('sqlsrv')->transaction(function () use ($records) {
            foreach ($records as $record) {
                try {
                    $this->processRecord($record);
                    $this->stats['processed']++;
                } catch (\Exception $e) {
                    $this->stats['errors']++;
                    $bookingNo = $record->bookingno ?? 'unknown';

                    // Always log the error
                    Log::error('Error processing record', [
                        'bookingno' => $bookingNo,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'record_data' => json_encode($record)
                    ]);

                    // Always show error in console (not just verbose mode)
                    $this->error("Error processing booking {$bookingNo}: " . $e->getMessage());

                    // Store error details for summary
                    $this->stats['error_details'][] = [
                        'bookingno' => $bookingNo,
                        'error' => $e->getMessage()
                    ];
                }
            }
        });
    }

    private function processRecord($record): void
    {
        $bookingNo = $record->bookingno;

        if ($this->option('debug')) {
            $this->info("Processing record: " . json_encode($record));
        }

        if (empty($bookingNo)) {
            throw new \Exception("BookingNo is required but missing");
        }

        $existingRecord = DB::connection('sqlsrv')
            ->table('CRM_SCMprofit_integration')
            ->where('bookingno', $bookingNo)
            ->exists();

        $data = $this->transformRecord($record);

        if ($this->isDryRun) {
            $action = $existingRecord ? 'UPDATE' : 'INSERT';
            $this->line("DRY RUN: Would {$action} BookingNo: {$bookingNo}");
            return;
        }

        if ($existingRecord) {
            DB::connection('sqlsrv')
                ->table('CRM_SCMprofit_integration')
                ->where('bookingno', $bookingNo)
                ->update($data);

            $this->stats['updated']++;

            if ($this->getOutput()->isVerbose()) {
                $this->info("Updated BookingNo: {$bookingNo}");
            }
        } else {
            DB::connection('sqlsrv')
                ->table('CRM_SCMprofit_integration')
                ->insert($data);

            $this->stats['inserted']++;

            if ($this->getOutput()->isVerbose()) {
                $this->info("Inserted BookingNo: {$bookingNo}");
            }
        }
    }

    private function transformRecord($record): array
    {
        return [
            'bookingno' => $record->bookingno,
            'companyname' => $record->companyname,
            'clientid' => $record->clientid,
            'source_id' => $record->source_id,
            'source' => $record->source,
            'medium_id' => $record->medium_id,
            'medium' => $record->medium,
            'stage_id' => $record->stage_id,
            'status' => $record->status,
            'userid' => $record->userid,
            'userpartnerid' => $record->userpartnerid,
            'username' => $record->username,
            'teamid' => $record->teamid,
            'teamname' => $record->teamname,
            'stateid' => $record->stateid,
            'statename' => $record->statename,
            'countryid' => $record->countryid,
            'countrycode' => $record->countrycode,
            'countryname' => $record->countryname,
            'industryid' => $record->industryid,
            'industryname' => $record->industryname,
            'serviceverticalid' => $record->serviceverticalid,
            'serviceverticalname' => $record->serviceverticalname,
            'origin_country_id' => $record->origin_country_id,
            'origin_country_code' => $record->origin_country_code,
            'origincountry' => $record->origincountry,
            'dest_country_id' => $record->dest_country_id,
            'destination_country_code' => $record->destination_country_code,
            'destinationcountry' => $record->destinationcountry,
            'source_name' => $record->source_name,
            'process' => $record->process,
            'storage' => $record->storage,
            'other' => $record->other,
            'state' => $record->state,
            'quotationo' => $record->quotationo,
            'scope_of_volume' => $record->scope_of_volume,
            'quoted_revenue' => $record->quoted_revenue,
            'won_status' => $record->won_status,
            'commodity' => $record->commodity,
            'cargo_volume' => $record->cargo_volume,
            'phone_sanitized' => $record->phone_sanitized,
            'email_normalized' => $record->email_normalized,
            'companyname_crm' => $record->companyname_crm,
            'type' => $record->type,
            'contact_name' => $record->contact_name,
            'partner_name' => $record->partner_name,
            'function' => $record->function,
            'email_from' => $record->email_from,
            'phone' => $record->phone,
            'mobile' => $record->mobile,
            'street' => $record->street,
            'street2' => $record->street2,
            'zip' => $record->zip,
            'city' => $record->city,
            'date_deadline' => $this->parseDate($record->date_deadline),
            'expected_revenue' => $record->expected_revenue,
            'prorated_revenue' => $record->prorated_revenue,
            'date_closed' => $this->parseDate($record->date_closed),
            'partnermaster_name' => $record->partnermaster_name,
            'partner_stateid' => $record->partner_stateid,
            'partner_statename' => $record->partner_statename,
            'partner_countryid' => $record->partner_countryid,
            'partner_country_code' => $record->partner_country_code,
            'partner_countryname' => $record->partner_countryname,
            'industry_id' => $record->industry_id,
            'partner_industrttypecode' => $record->partner_industrttypecode,
            'partner_industrttype' => $record->partner_industrttype,
            'partner_function' => $record->partner_function,
            'partner_steet' => $record->partner_steet,
            'partner_steet2' => $record->partner_steet2,
            'partner_zip' => $record->partner_zip,
            'partner_city' => $record->partner_city,
            'partner_email' => $record->partner_email,
            'partner_phone' => $record->partner_phone,
            'partner_mobile' => $record->partner_mobile,
            'createdon' => $this->parseDate($record->createdon),
            'updatedon' => $this->parseDate($record->updatedon),
            'pri_id' => $record->pri_id,
            'pri_partnermaster_name' => $record->pri_partnermaster_name,
            'pri_partner_stateid' => $record->pri_partner_stateid,
            'pri_partner_statename' => $record->pri_partner_statename,
            'pri_partner_countryid' => $record->pri_partner_countryid,
            'pri_partner_countrycode' => $record->pri_partner_countrycode,
            'pri_partner_countryname' => $record->pri_partner_countryname,
            'pri_rrp_industryid' => $record->pri_rrp_industryid,
            'pri_partner_industrttypecode' => $record->pri_partner_industrttypecode,
            'pri_partner_industrttype' => $record->pri_partner_industrttype,
            'pri_partner_function' => $record->pri_partner_function,
            'pri_partner_steet' => $record->pri_partner_steet,
            'pri_partner_steet2' => $record->pri_partner_steet2,
            'pri_partner_zip' => $record->pri_partner_zip,
            'pri_partner_city' => $record->pri_partner_city,
            'pri_partner_email' => $record->pri_partner_email,
            'pri_partner_phone' => $record->pri_partner_phone,
            'pri_partner_mobile' => $record->pri_partner_mobile,
            'ServiceVertical' => $record->servicevertical,
            'SVName' => $record->svname,
            'TransportMode' => $record->transportmode,
            'ServiceClass' => $record->serviceclass,
            'SalesPersonCode' => $record->salespersoncode,
                    // 🚀 Newly added fields
            'shipmentmode'   => $record->shipmentmode,
            'incoterm'       => $record->incoterm,
            'scopevolume'    => $record->scopevolume,
            'cargovolume'    => $record->cargovolume,
            'containerlist'  => $record->containerlist,
        ];
    }

    private function parseDate($date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            Log::warning('Invalid date format', ['date' => $date, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function displaySummary(): void
    {
        $this->stats['end_time'] = Carbon::now();
        $duration = $this->stats['end_time']->diffForHumans($this->stats['start_time'], true);

        $this->newLine();
        $this->info("=== Sync Summary ===");
        $this->info("Duration: {$duration}");
        $this->info("Processed: {$this->stats['processed']} records");
        $this->info("Inserted: {$this->stats['inserted']} records");
        $this->info("Updated: {$this->stats['updated']} records");

        if ($this->stats['errors'] > 0) {
            $this->error("Errors: {$this->stats['errors']} records");

            // Show error details in summary
            if (!empty($this->stats['error_details'])) {
                $this->newLine();
                $this->error("Error Details:");
                foreach ($this->stats['error_details'] as $errorDetail) {
                    $this->error("  - BookingNo: {$errorDetail['bookingno']} - {$errorDetail['error']}");
                }
            }

            $this->newLine();
            $this->warn("Check storage/logs/laravel.log for detailed error information");
        }

        $this->info("Sync completed successfully!");
    }
}
