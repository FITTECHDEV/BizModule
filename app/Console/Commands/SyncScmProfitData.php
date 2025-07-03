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

            $currentDate = Carbon::now()->format('Y-m-d'); // Correct date format

            $startDate = '2025-02-03'; // Correctly formatted start date

            $scmData = DB::connection('pgsql')->select("
            SELECT * FROM vw_scmprofit_integration
            WHERE CAST(CreatedOn AS date) >= ?
            OR CAST(UpdatedOn AS date) >= ?",
                [$startDate, $currentDate]
            );
            if (empty($scmData)) {
                $this->info("No records found for: " . $currentDate);
                return;
            }

            foreach ($scmData as $record) {
                $bookingNo = $record->bookingno;

                //  Check if BookingNo exists in SQL Server
                $existingRecord = DB::connection('sqlsrv')
                    ->table('CRM_SCMprofit_integration')
                    ->where('bookingno', $bookingNo)
                    ->exists();

                //  Convert record to an array
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
                    'date_deadline' => $record->date_deadline,
                    'expected_revenue' => $record->expected_revenue,
                    'prorated_revenue' => $record->prorated_revenue,
                    'date_closed' => $record->date_closed,
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
                    'createdon' => Carbon::parse($record->createdon)->format('Y-m-d H:i:s'),
                    'updatedon' => Carbon::parse($record->updatedon)->format('Y-m-d H:i:s'),
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
                    // 'INTCreatedOn' => Carbon::now(),
                    // 'INTUpdatedOn' => Carbon::now(),
                    // 'ClientCreated' =>,
                    // 'ClientCodeINSCM' =>,
                    // 'ClientIDINSCM' =>,
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
                    //  Update existing record
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

            $this->info(" Data sync completed successfully!");
        } catch (\Exception $e) {
            $this->error(" Error: " . $e->getMessage());
        }
    }
}

