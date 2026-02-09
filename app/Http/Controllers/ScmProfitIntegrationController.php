<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ScmProfitIntegrationController extends Controller
{
    public function index()
    {
        $stats = $this->getStats();
        $offices = $this->getOffices();

        return view('scm-profit-integration.index', compact('stats', 'offices'));
    }

    public function getData(Request $request)
    {
        $query = DB::table('CRM_SCMprofit_integration as CI')
            ->selectRaw("
                CI.bookingno,
                CAST(CI.createdon as date) as report_date,
                CI.teamname as OfficeName,
                CI.companyname as ClientName,
                CI.clientid as ClientID,
                CI.username as SalesPerson,
                CI.origincountry as OriginCountry,
                CI.destinationcountry as DestinationCountry,
                ISNULL(CI.ClientIntegration,'') as ClientStatus,
                CASE 
                    WHEN LEN(ISNULL(CI.SCMINTStatus,'')) <= 0 THEN 'Pending'
                    WHEN CHARINDEX('success', LOWER(CI.SCMINTStatus), 1) > 0 THEN 'Success'
                    ELSE 'Failed' 
                END as SCMStatus,
                CASE 
                    WHEN LEN(ISNULL(CI.SCMINTStatus,'')) <= 0 THEN ''
                    WHEN CHARINDEX('success', LOWER(CI.SCMINTStatus), 1) > 0 THEN ''
                    ELSE CI.SCMINTStatus 
                END as StatusRemark,
                ISNULL(CI.SCMBookingNo,'') as BookingNo
            ")
            ->whereNotNull('CI.clientid');

        // Date filter
        if ($request->filled('days') && $request->days !== 'custom') {
            $days = (int) $request->days;
            $query->whereRaw("CAST(CI.createdon as date) >= DATEADD(day, -{$days}, CAST(GETDATE() as date))");
        }

        // Custom date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween(DB::raw('CAST(CI.createdon as date)'), [$request->start_date, $request->end_date]);
        }

        // Office filter
        if ($request->filled('office')) {
            $query->where('CI.teamname', $request->office);
        }

        // Card filter
        if ($request->filled('card_filter') && $request->card_filter !== 'all') {
            switch ($request->card_filter) {
                case 'pending':
                    $query->whereRaw("LEN(ISNULL(CI.SCMINTStatus,'')) <= 0");
                    break;
                case 'success':
                    $query->whereRaw("CHARINDEX('success', LOWER(CI.SCMINTStatus), 1) > 0");
                    break;
                case 'failed':
                    $query->whereRaw("LEN(ISNULL(CI.SCMINTStatus,'')) > 0 AND CHARINDEX('success', LOWER(CI.SCMINTStatus), 1) = 0");
                    break;
            }
        }

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                return '
                    <div style="display: flex; gap: 5px;">
                        <a href="' . route('scm-profit-integration.show', $row->bookingno) . '" 
                           class="btn btn-sm btn-info" 
                           title="View Details"
                           style="padding: 5px 10px;">
                            <i class="fa fa-eye"></i>
                        </a>
                        <a href="' . route('scm-profit-integration.edit', $row->bookingno) . '" 
                           class="btn btn-sm btn-warning" 
                           title="Edit Booking"
                           style="padding: 5px 10px;">
                            <i class="fa fa-edit"></i>
                        </a>
                        <button onclick="retriggerIntegration(' . $row->bookingno . ')" 
                                class="btn btn-sm btn-primary" 
                                title="Retrigger Integration"
                                style="padding: 5px 10px;">
                            <i class="fa fa-refresh"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['action'])
            ->orderColumn('report_date', 'CI.createdon $1')
            ->make(true);
    }

    public function getStatsData(Request $request)
    {
        return response()->json($this->getStats($request));
    }

    private function getStats(Request $request = null)
    {
        $query = DB::table('CRM_SCMprofit_integration as CI')
            ->whereNotNull('CI.clientid');

        // Apply filters if request provided
        if ($request) {
            if ($request->filled('days') && $request->days !== 'custom') {
                $days = (int) $request->days;
                $query->whereRaw("CAST(CI.createdon as date) >= DATEADD(day, -{$days}, CAST(GETDATE() as date))");
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween(DB::raw('CAST(CI.createdon as date)'), [$request->start_date, $request->end_date]);
            }

            if ($request->filled('office')) {
                $query->where('CI.teamname', $request->office);
            }
        } else {
            // Default: yesterday's data
            $query->whereRaw("CAST(CI.createdon as date) = CAST(DATEADD(day, -1, GETDATE()) as date)");
        }

        $stats = $query->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN LEN(ISNULL(CI.SCMINTStatus,'')) <= 0 THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN CHARINDEX('success', LOWER(CI.SCMINTStatus), 1) > 0 THEN 1 ELSE 0 END) as success,
            SUM(CASE WHEN LEN(ISNULL(CI.SCMINTStatus,'')) > 0 AND CHARINDEX('success', LOWER(CI.SCMINTStatus), 1) = 0 THEN 1 ELSE 0 END) as failed
        ")->first();

        return [
            'total' => $stats->total ?? 0,
            'pending' => $stats->pending ?? 0,
            'success' => $stats->success ?? 0,
            'failed' => $stats->failed ?? 0,
        ];
    }

    private function getOffices()
    {
        return DB::table('CRM_SCMprofit_integration')
            ->whereNotNull('clientid')
            ->whereNotNull('teamname')
            ->distinct()
            ->pluck('teamname')
            ->sort()
            ->values();
    }

    public function show($bookingno)
    {
        $booking = DB::table('CRM_SCMprofit_integration')
            ->where('bookingno', $bookingno)
            ->first();

        if (!$booking) {
            abort(404, 'Booking not found');
        }

        // ✅ HARD-CODE CHARGES HERE
        $charges = [
            'Freight Cost'   => 1500,
            'Handling Cost'  => 300,
            'Customs Cost'   => 450,
            'Insurance Cost' => 200,
        ];

        // ✅ TOTAL COST
        $totalCost = array_sum($charges);

        // ✅ OPTIONAL: Hard-coded revenue
        $quotedRevenue = 3500;

        return view('scm-profit-integration.show', [
            'booking'        => $booking,
            'charges'        => $charges,
            'totalCost'      => $totalCost,
            'quotedRevenue'  => $quotedRevenue,
        ]);
    }


    public function edit($bookingno)
    {
        $booking = DB::table('CRM_SCMprofit_integration')
            ->where('bookingno', $bookingno)
            ->first();

        if (!$booking) {
            abort(404, 'Booking not found');
        }

        return view('scm-profit-integration.edit', compact('booking'));
    }

    public function update(Request $request, $bookingno)
    {
        try {
            // Prepare update data
            $updateData = [
                'companyname' => $request->input('companyname'),
                'teamname' => $request->input('teamname'),
                'username' => $request->input('username'),
                'ServiceVertical' => $request->input('ServiceVertical'),
                'TransportMode' => $request->input('TransportMode'),
                'contact_name' => $request->input('contact_name'),
                'email_normalized' => $request->input('email_normalized'),
                'phone_sanitized' => $request->input('phone_sanitized'),
                'origincountry' => $request->input('origincountry'),
                'origin_country_code' => $request->input('origin_country_code'),
                'destinationcountry' => $request->input('destinationcountry'),
                'destination_country_code' => $request->input('destination_country_code'),
                'shipmentmode' => $request->input('shipmentmode'),
                'incoterm' => $request->input('incoterm'),
                'commodity' => $request->input('commodity'),
                'cargovolume' => $request->input('cargovolume'),
                'scopevolume' => $request->input('scopevolume'),
                'containerlist' => $request->input('containerlist'),
                'quotationo' => $request->input('quotationo'),
                'quoted_revenue' => $request->input('quoted_revenue'),
                'expected_revenue' => $request->input('expected_revenue'),
                'prorated_revenue' => $request->input('prorated_revenue'),
                'won_status' => $request->input('won_status'),
                'source' => $request->input('source'),
                'medium' => $request->input('medium'),
                'process' => $request->input('process'),
                'ServiceClass' => $request->input('ServiceClass'),
                'industryname' => $request->input('industryname'),
                'updatedon' => now()
            ];

            // Remove null values
            $updateData = array_filter($updateData, function ($value) {
                return $value !== null;
            });

            // Update the booking
            $affected = DB::table('CRM_SCMprofit_integration')
                ->where('bookingno', $bookingno)
                ->update($updateData);

            if ($affected) {
                return response()->json([
                    'success' => true,
                    'message' => 'Booking updated successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No changes were made'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating booking: ' . $e->getMessage()
            ], 500);
        }
    }

    public function retrigger(Request $request)
    {
        $request->validate([
            'booking_no' => 'required|string'
        ]);

        try {
            // Add your retrigger logic here
            // Example: Call stored procedure or API

            // Placeholder - replace with actual retrigger logic
            $result = DB::connection('sqlsrv')->statement(
                'EXEC sp_RetriggerSCMIntegration @BookingNo = ?',
                [$request->booking_no]
            );

            return response()->json([
                'success' => true,
                'message' => 'Successfully retriggered'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
