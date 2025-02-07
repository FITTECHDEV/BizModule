<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncScmProfitData extends Command
{
    protected $signature = 'sync:scmprofit';
    protected $description = 'Sync data from PostgreSQL vw_scmprofit_integration to SQL Server CRM_SCMprofit_integration';

    public function handle()
    {
        try {
            $currentDate = Carbon::now()->format('Y-m-d'); // Correct date format for SQL

            // ✅ Fetch data from PostgreSQL
            $scmData = DB::connection('pgsql')->select("
                SELECT * FROM vw_scmprofit_integration
                WHERE CAST(CreatedOn AS date) = ?
                OR CAST(UpdatedOn AS date) = ?",
                [$currentDate, $currentDate]
            );

            if (empty($scmData)) {
                $this->info("No records found for: " . $currentDate);
                return;
            }

            foreach ($scmData as $record) {
                $bookingNo = $record->bookingno;

                // ✅ Check if BookingNo exists in SQL Server
                $existingRecord = DB::connection('sqlsrv')
                    ->table('CRM_SCMprofit_integration')
                    ->where('bookingno', $bookingNo)
                    ->exists();

                // ✅ Convert record to an array
                $data = [
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
                    'origincountry' => $record->origincountry,
                    'dest_country_id' => $record->dest_country_id,
                    'destinationcountry' => $record->destinationcountry,
                    'expected_revenue' => $record->expected_revenue,
                    'prorated_revenue' => $record->prorated_revenue,
                    'createdon' => Carbon::parse($record->createdon)->format('Y-m-d H:i:s'),
                    'updatedon' => Carbon::parse($record->updatedon)->format('Y-m-d H:i:s'),
                    // 'INTCreatedOn' => Carbon::now(),
                    // 'INTUpdatedOn' => Carbon::now(),
                    // 'SCMBookingID' => $record->SCMBookingID,
                    // 'SCMBookingNo' => $record->SCMBookingNo,
                    // 'SCMJobID' => $record->SCMJobID,
                    // 'SCMJobNo' => $record->SCMJobNo,
                    // 'SCMINTStatus' => $record->SCMINTStatus,
                    // 'SCMBookingXML' => $record->SCMBookingXML,
                    // 'CreatedOnInSCM' => Carbon::now(),
                    // 'UpdatedOninSCM' => Carbon::now(),
                ];

                if ($existingRecord) {
                    // ✅ Update existing record
                    DB::connection('sqlsrv')
                        ->table('CRM_SCMprofit_integration')
                        ->where('bookingno', $bookingNo)
                        ->update($data);

                    $this->info("Updated BookingNo: " . $bookingNo);
                } else {
                    // Insert new record
                    DB::connection('sqlsrv')
                        ->table('CRM_SCMprofit_integration')
                        ->insert($data);

                    $this->info("Inserted BookingNo: " . $bookingNo);
                }
            }

            $this->info("✅ Data sync completed successfully!");
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
        }
    }
}
