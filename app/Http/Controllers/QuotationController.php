<?php

namespace App\Http\Controllers;

use App\Models\QuotationInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class QuotationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index()
    {
        // Get unique offices for filter dropdown
        $offices = DB::table('Quote_QuotationInfo')
            ->whereNotNull('teamname')
            ->distinct()
            ->pluck('teamname')
            ->sort()
            ->values();

        // Get initial stats for today
        $stats = $this->getStats(0, null, null, null);

        return view('quotation.index', compact('offices', 'stats'));
    }

    public function stats(Request $request)
    {
        $days = $request->input('days', 0);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $office = $request->input('office');

        $stats = $this->getStats($days, $startDate, $endDate, $office);

        return response()->json($stats);
    }

    private function getStats($days, $startDate, $endDate, $office)
    {
        $query = DB::table('Quote_QuotationInfo');

        // Date filtering
        if ($days == 'custom' && $startDate && $endDate) {
            $query->whereBetween('createdon', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } elseif ($days == 1) {
            $query->whereDate('createdon', now()->subDay());
        } elseif ($days == 15) {
            $query->where('createdon', '>=', now()->subDays(15));
        } elseif ($days == 30) {
            $query->where('createdon', '>=', now()->subDays(30));
        } else {
            $query->whereDate('createdon', today());
        }

        // Office filtering
        if ($office) {
            $query->where('teamname', $office);
        }

        $total = $query->count();

        $inProgress = (clone $query)->whereIn('status', ['Proposal', null])
            ->orWhereNull('status')
            ->count();

        $won = (clone $query)->where('status', 'Won')
            ->where(function ($q) {
                $q->whereNull('SCMINTStatus')
                    ->orWhere('SCMINTStatus', 'Success');
            })->count();

        $lost = (clone $query)->where('status', 'Lost')->count();

        $canceled = (clone $query)->where('status', 'Cancel')->count();

        $failedInt = (clone $query)->where('status', 'Won')
            ->whereNotNull('SCMINTStatus')
            ->where('SCMINTStatus', '!=', 'Success')
            ->count();

        return [
            'total' => $total,
            'in_progress' => $inProgress,
            'won' => $won,
            'lost' => $lost,
            'canceled' => $canceled,
            'failed_int' => $failedInt
        ];
    }

    public function data(Request $request)
    {
        $days = $request->input('days', 0);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $office = $request->input('office');
        $cardFilter = $request->input('card_filter', 'all');

        $query = DB::table('Quote_QuotationInfo')
            ->select([
                'ID',
                'bookingno',
                'companyname',
                'clientid',
                'username',
                'teamname',
                'origincountry',
                'destinationcountry',
                'status',
                'SCMINTStatus',
                'createdon',
                'updatedon'
            ]);

        // Date filtering
        if ($days == 'custom' && $startDate && $endDate) {
            $query->whereBetween('createdon', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } elseif ($days == 1) {
            $query->whereDate('createdon', now()->subDay());
        } elseif ($days == 15) {
            $query->where('createdon', '>=', now()->subDays(15));
        } elseif ($days == 30) {
            $query->where('createdon', '>=', now()->subDays(30));
        } else {
            $query->whereDate('createdon', today());
        }

        // Office filtering
        if ($office) {
            $query->where('teamname', $office);
        }

        // Card filter
        switch ($cardFilter) {
            case 'in_progress':
                $query->where(function ($q) {
                    $q->whereIn('status', ['Proposal', null])
                        ->orWhereNull('status');
                });
                break;
            case 'won':
                $query->where('status', 'Won')
                    ->where(function ($q) {
                        $q->whereNull('SCMINTStatus')
                            ->orWhere('SCMINTStatus', 'Success');
                    });
                break;
            case 'lost':
                $query->where('status', 'Lost');
                break;
            case 'canceled':
                $query->where('status', 'Cancel');
                break;
            case 'failed_int':
                $query->where('status', 'Won')
                    ->whereNotNull('SCMINTStatus')
                    ->where('SCMINTStatus', '!=', 'Success');
                break;
        }

        return DataTables::of($query)
            ->addColumn('report_date', function ($row) {
                return $row->createdon ? date('Y-m-d H:i', strtotime($row->createdon)) : '-';
            })
            ->addColumn('OfficeName', function ($row) {
                return $row->teamname ?? '-';
            })
            ->addColumn('ClientName', function ($row) {
                return $row->companyname ?? '-';
            })
            ->addColumn('ClientID', function ($row) {
                return $row->clientid ?? '-';
            })
            ->addColumn('SalesPerson', function ($row) {
                return $row->username ?? '-';
            })
            ->addColumn('OriginCountry', function ($row) {
                return $row->origincountry ?? '-';
            })
            ->addColumn('DestinationCountry', function ($row) {
                return $row->destinationcountry ?? '-';
            })
            ->addColumn('QuotationStatus', function ($row) {
                // Determine the display status
                $status = $row->status ?? 'In Progress';

                if ($status == 'Won' && $row->SCMINTStatus && $row->SCMINTStatus != 'Success') {
                    return 'Failed Int.';
                }

                if ($status == 'Proposal' || is_null($status)) {
                    return 'In Progress';
                }

                if ($status == 'Cancel') {
                    return 'Canceled';
                }

                return $status;
            })
            ->addColumn('StatusRemark', function ($row) {
                return $row->SCMINTStatus ?? '-';
            })
            ->addColumn('BookingNo', function ($row) {
                return $row->bookingno ?? '-';
            })
            ->addColumn('action', function ($row) {
                $status = $row->status ?? 'Proposal';
                $scmStatus = $row->SCMINTStatus;

                $viewBtn = '<a href="' . route('quotation.view', $row->ID) . '" class="btn btn-info btn-sm" title="View">
                    <i class="fas fa-eye"></i>
                </a>';

                $editBtn = '';
                $retriggerBtn = '';

                // In Progress - View & Edit
                if (in_array($status, ['Proposal', null])) {
                    $editBtn = ' <a href="' . route('quotation.edit', $row->ID) . '" class="btn btn-warning btn-sm" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>';
                }

                // Failed Int - View & Retrigger
                if ($status == 'Won' && $scmStatus && $scmStatus != 'Success') {
                    $retriggerBtn = ' <button class="btn btn-danger btn-sm btn-retrigger" data-booking="' . $row->bookingno . '" title="Retrigger">
                        <i class="fas fa-sync-alt"></i>
                    </button>';
                }

                return $viewBtn . $editBtn . $retriggerBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function view($id)
    {
        $quotation = QuotationInfo::where('ID', $id)->firstOrFail();

        return view('quotation.view', compact('quotation'));
    }


    public function edit($id)
    {
        try {
            // Debug: Log the ID being queried
            Log::info('Attempting to load quotation with ID: ' . $id);

            $quotation = QuotationInfo::where('ID', $id)->first();

            // Debug: Check if quotation was found
            if (!$quotation) {
                Log::error('Quotation not found with ID: ' . $id);
                return redirect()
                    ->route('quotation.index')
                    ->with('error', 'Quotation not found');
            }

            // Debug: Log what data we're sending to the view
            Log::info('Quotation data loaded', [
                'id' => $quotation->ID,
                'bookingno' => $quotation->bookingno,
                'companyname' => $quotation->companyname
            ]);

            return view('quotation.edit', compact('quotation'));
        } catch (\Exception $e) {
            Log::error('Error loading quotation for edit: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('quotation.index')
                ->with('error', 'Failed to load quotation: ' . $e->getMessage());
        }
    }


    public function getCharges(Request $request)
    {
        $bookingNo = $request->input('booking_no');

        if (!$bookingNo) {
            return response()->json([
                'success' => false,
                'message' => 'Booking number is required'
            ], 400);
        }

        try {
            $charges = DB::table('Quote_Quotation_ChargeSheet as QC')
                ->join('Quote_QuotationInfo as QI', 'QI.id', '=', 'QC.QuotationInfoID')
                ->leftJoin('[FITSCMUAT].dbo.Bill_ChargeCode as BC', 'BC.Id', '=', 'QC.ChargeCodeId')
                ->leftJoin('[FITSCMUAT].dbo.Cust_Currency as CC', 'CC.Id', '=', 'QC.EstChargeCurrencyId')
                ->leftJoin('[FITSCMUAT].dbo.Cust_ChargecodeBasedOn as CB', 'CB.ID', '=', 'QC.BasedOnId')
                ->leftJoin('[FITSCMUAT].dbo.Sys_UOM as SU', 'SU.Id', '=', 'QC.UOMId')
                ->leftJoin('[FITSCMUAT].dbo.Cust_Party as CP', 'CP.Id', '=', 'QC.PartyID')
                ->where('QI.bookingno', $bookingNo)
                ->where('QC.IsDeleted', 0)
                ->select([
                    'QC.Id as QuoteChargeID',
                    'BC.Code as ChargeCode',
                    'BC.Name as ChargeName',
                    'QC.Quantity',
                    'QC.EstChargeRate as Rate',
                    'QC.EstChargeAmount as AmtInChgCurr',
                    'QC.EstBaseAmount as AmtInBaseCurr',
                    'QC.BaseExchRate as ExChangeRate',
                    'CC.Code as ChgCurr',
                    'CB.Code as BasedOnCode',
                    'CB.BasedOn as BasedOn',
                    'SU.Code as BasedOnUOM',
                    'QC.Remarks as ChargeRemark',
                    'QC.IsRevenue',
                    'CP.Code as PartyCode',
                    'QC.ChargeType as SourceType'
                ])
                ->get();

            $revenue = [];
            $cost = [];

            foreach ($charges as $charge) {
                $chargeData = [
                    'id' => $charge->QuoteChargeID,
                    'chargeCode' => $charge->ChargeCode,
                    'chargeName' => $charge->ChargeName,
                    'quantity' => (float)$charge->Quantity,
                    'rate' => (float)$charge->Rate,
                    'amtInChgCurr' => (float)$charge->AmtInChgCurr,
                    'amtInBaseCurr' => (float)$charge->AmtInBaseCurr,
                    'exChangeRate' => (float)$charge->ExChangeRate,
                    'chgCurr' => $charge->ChgCurr,
                    'basedOnCode' => $charge->BasedOnCode,
                    'basedOn' => $charge->BasedOn,
                    'basedOnUOM' => $charge->BasedOnUOM,
                    'chargeRemark' => $charge->ChargeRemark,
                    'partyCode' => $charge->PartyCode,
                    'sourceType' => $charge->SourceType
                ];

                if ($charge->IsRevenue) {
                    $revenue[] = $chargeData;
                } else {
                    $cost[] = $chargeData;
                }
            }

            return response()->json([
                'success' => true,
                'revenue' => $revenue,
                'cost' => $cost
            ]);
        } catch (\Exception $e) {
            Log::error('Get charges failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load charges: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveCharge(Request $request)
    {
        try {
            $inputJson = json_encode([
                'OperationType' => 'QuotationAddorEditCharge',
                'SubmittedDatetime' => now()->format('YmdHis'),
                'SubmittedBy' => Auth::user()->username ?? Auth::user()->email,
                'Action' => $request->input('action', 'Add'),
                'BookingNo' => $request->input('booking_no'),
                'QuoteChargeID' => $request->input('quote_charge_id', ''),
                'PartyCode' => $request->input('party_code', ''),
                'RevenueOrCost' => $request->input('revenue_or_cost'),
                'ChargeCode' => $request->input('charge_code'),
                'Quantity' => (float)$request->input('quantity', 0),
                'Rate' => (float)$request->input('rate', 0),
                'AmtInChgCurr' => (float)$request->input('amt_in_chg_curr', 0),
                'AmtInBaseCurr' => (float)$request->input('amt_in_base_curr', 0),
                'ExChangeRate' => (float)$request->input('exchange_rate', 1),
                'ChgCurr' => $request->input('chg_curr', 'KES'),
                'BasedOnCode' => $request->input('based_on_code'),
                'BasedOnUOM' => $request->input('based_on_uom', ''),
                'ChargeRemark' => $request->input('charge_remark', ''),
                'ToBeAdded' => (int)$request->input('to_be_added', 0),
                'IncreaseBy' => (int)$request->input('increase_by', 0),
                'IncreaseType' => $request->input('increase_type', 'Percentage'),
                'SourceType' => $request->input('source_type', 'manual')
            ]);

            $outputJson = '';

            DB::statement('EXEC sp_Quote_Quotaiton_ChargesAddorEdit ?, ? OUTPUT', [
                $inputJson,
                &$outputJson
            ]);

            $result = DB::select('SELECT ? as OutputJson', [$outputJson]);
            $output = json_decode($result[0]->OutputJson ?? '{}', true);

            if (($output['Status'] ?? '') === 'Success') {
                return response()->json([
                    'success' => true,
                    'message' => $output['Remarks'][0]['Remark'] ?? 'Charge saved successfully',
                    'charge_id' => $output['ChargeID'] ?? null
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $output['Remarks'][0]['Remark'] ?? 'Failed to save charge'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Save charge failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save charge: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteCharge(Request $request)
    {
        try {
            $inputJson = json_encode([
                'OperationType' => 'QuotationAddorEditCharge',
                'SubmittedDatetime' => now()->format('YmdHis'),
                'SubmittedBy' => Auth::user()->username ?? Auth::user()->email,
                'Action' => 'Delete',
                'BookingNo' => $request->input('booking_no'),
                'QuoteChargeID' => $request->input('quote_charge_id'),
                'PartyCode' => '',
                'RevenueOrCost' => 'Revenue',
                'ChargeCode' => '',
                'Quantity' => 0,
                'Rate' => 0,
                'AmtInChgCurr' => 0,
                'AmtInBaseCurr' => 0,
                'ExChangeRate' => 1,
                'ChgCurr' => 'KES',
                'BasedOnCode' => '',
                'BasedOnUOM' => '',
                'ChargeRemark' => '',
                'ToBeAdded' => 0,
                'IncreaseBy' => 0,
                'IncreaseType' => 'Percentage',
                'SourceType' => 'manual'
            ]);

            $outputJson = '';

            DB::statement('EXEC sp_Quote_Quotaiton_ChargesAddorEdit ?, ? OUTPUT', [
                $inputJson,
                &$outputJson
            ]);

            $result = DB::select('SELECT ? as OutputJson', [$outputJson]);
            $output = json_decode($result[0]->OutputJson ?? '{}', true);

            if (($output['Status'] ?? '') === 'Success') {
                return response()->json([
                    'success' => true,
                    'message' => 'Charge deleted successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $output['Remarks'][0]['Remark'] ?? 'Failed to delete charge'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Delete charge failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete charge: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request)
    {
        try {
            $inputJson = json_encode([
                'OperationType' => 'QuotationStatusUpdate',
                'SubmittedDatetime' => now()->format('YmdHis'),
                'SubmittedBy' => Auth::user()->username ?? Auth::user()->email,
                'Action' => 'Edit',
                'BookingNo' => $request->input('booking_no'),
                'Status' => $request->input('status'),
                'ReasonCode' => $request->input('reason_code', ''),
                'ReasonRemark' => $request->input('reason_remark', '')
            ]);

            $outputJson = '';

            DB::statement('EXEC sp_Quote_Quotaiton_StatusUpdate ?, ? OUTPUT', [
                $inputJson,
                &$outputJson
            ]);

            $result = DB::select('SELECT ? as OutputJson', [$outputJson]);
            $output = json_decode($result[0]->OutputJson ?? '{}', true);

            if (($output['Status'] ?? '') === 'Success') {
                return response()->json([
                    'success' => true,
                    'message' => $output['Remarks'][0]['Remark'] ?? 'Status updated successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $output['Remarks'][0]['Remark'] ?? 'Failed to update status'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Update status failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getReasons(Request $request)
    {
        $module = $request->input('module', 'Quotation');
        $reasonType = $request->input('reason_type');
        $filter = $request->input('filter', '');

        if (!$reasonType) {
            return response()->json([
                'success' => false,
                'message' => 'Reason type is required'
            ], 400);
        }

        try {
            $reasons = DB::select('EXEC sp_ReasonMasterList ?, ?, ?', [
                $module,
                $reasonType,
                $filter
            ]);

            return response()->json([
                'success' => true,
                'reasons' => $reasons
            ]);
        } catch (\Exception $e) {
            Log::error('Get reasons failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load reasons: ' . $e->getMessage()
            ], 500);
        }
    }

    public function retrigger(Request $request)
    {
        $bookingNo = $request->input('booking_no');

        if (!$bookingNo) {
            return response()->json([
                'success' => false,
                'message' => 'Booking number is required'
            ], 400);
        }

        try {
            // TODO: Implement retrigger logic using stored procedure
            Log::info('Retrigger requested for booking: ' . $bookingNo);

            return response()->json([
                'success' => true,
                'message' => 'Retrigger initiated for booking: ' . $bookingNo
            ]);
        } catch (\Exception $e) {
            Log::error('Retrigger failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Retrigger failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
