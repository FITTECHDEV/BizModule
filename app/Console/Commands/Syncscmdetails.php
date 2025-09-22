<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncScmDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:scmdetails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy today\'s rows from SQL Server CRM_UpdateSCMDetails into Odoo scmdetails table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString(); // YYYY-MM-DD
        $this->info("Fetching data for {$today} from SQL Server...");

        // Pull from SQL Server (rows where CreatedOn is today)
        $rows = DB::connection('sqlsrv')->select("
            SELECT [Id],
                   [BookingIn],
                   [CRMid],
                   [Action],
                   [Value],
                   [CreatedOn],
                   [UpdatedOn],
                   [Status]
            FROM [FITSYSUAT].[dbo].[CRM_UpdateSCMDetails]
            WHERE CAST([CreatedOn] AS DATE) = CAST(GETDATE() AS DATE)
        ");

        if (empty($rows)) {
            $this->warn('No rows found for today.');
            return 0;
        }

        $this->info('Inserting into Postgres (Odoo)...');

        foreach ($rows as $row) {
            DB::connection('pgsql')->table('scmdetails')->insert([
                'id'         => $row->Id,
                'booking_in' => $row->BookingIn,
                'crm_id'     => $row->CRMid,
                'action'     => $row->Action,
                'value'      => $row->Value,
                'created_on' => $row->CreatedOn,
                'updated_on' => $row->UpdatedOn,
                'status'     => $row->Status,
            ]);
        }

        $this->info('✅ Sync complete: ' . count($rows) . ' rows copied.');

        return 0;
    }
}
