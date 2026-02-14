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
        } elseif ($days == 0) {
            $query->whereDate('createdon', today());
        }
        // else: No date filter - show all records

        // Office filtering
        if ($office) {
            $query->where('teamname', $office);
        }

        $total = $query->count();

        $inProgress = (clone $query)->where(function ($q) {
            $q->whereIn('status', ['Proposal', 'Pending'])
                ->orWhereNull('status');
        })->count();

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

        Log::info('Quotation data request', [
            'days' => $days,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'office' => $office,
            'card_filter' => $cardFilter
        ]);

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
                'QuoteStatus',
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
        } elseif ($days == 0) {
            $query->whereDate('createdon', today());
        }
        // else: No date filter - show all records

        // Office filtering
        if ($office) {
            $query->where('teamname', $office);
        }

        // Card filter
        switch ($cardFilter) {
            case 'in_progress':
                $query->where(function ($q) {
                    $q->whereIn('status', ['Proposal', 'Pending'])
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
                // Use QuoteStatus first, fallback to status
                $status = $row->QuoteStatus ?? $row->status ?? 'Proposal';

                if ($status == 'Won' && $row->SCMINTStatus && $row->SCMINTStatus != 'Success') {
                    return 'Failed Int.';
                }

                if (in_array($status, ['Proposal', 'Pending']) || is_null($status)) {
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
                // Use QuoteStatus first, fallback to status
                $status = $row->QuoteStatus ?? $row->status ?? 'Proposal';
                $scmStatus = $row->SCMINTStatus;

                $viewBtn = '<a href="' . route('quotation.view', $row->ID) . '" class="btn btn-info btn-sm" title="View">
                <i class="fas fa-eye"></i>
            </a>';

                $editBtn = '';
                $retriggerBtn = '';

                // In Progress - View & Edit
                if (in_array($status, ['Proposal', 'Pending', null])) {
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
            $charges = DB::select("
            SELECT 
                QC.Id as QuoteChargeID,
                BC.Code as ChargeCode,
                BC.Name as ChargeName,
                QC.Quantity,
                QC.EstChargeRate as Rate,
                QC.EstChargeAmount as AmtInChgCurr,
                QC.EstBaseAmount as AmtInBaseCurr,
                QC.BaseExchRate as ExChangeRate,
                CC.Code as ChgCurr,
                CB.Code as BasedOnCode,
                CB.BasedOn as BasedOn,
                SU.Code as BasedOnUOM,
                QC.Remarks as ChargeRemark,
                QC.IsRevenue,
                CP.Code as PartyCode,
                QC.ChargeType as SourceType
            FROM Quote_Quotation_ChargeSheet QC WITH (NOLOCK)
            INNER JOIN Quote_QuotationInfo QI WITH (NOLOCK) ON QI.id = QC.QuotationInfoID
            LEFT JOIN [FITSCMUAT].dbo.Bill_ChargeCode BC WITH (NOLOCK) ON BC.Id = QC.ChargeCodeId
            LEFT JOIN [FITSCMUAT].dbo.Cust_Currency CC WITH (NOLOCK) ON CC.Id = QC.EstChargeCurrencyId
            LEFT JOIN Cust_ChargecodeBasedOn CB WITH (NOLOCK) ON CB.Id = QC.BasedOnId
            LEFT JOIN [FITSCMUAT].dbo.Sys_UOM SU WITH (NOLOCK) ON SU.Id = QC.UOMId
            LEFT JOIN [FITSCMUAT].dbo.Cust_Party CP WITH (NOLOCK) ON CP.Id = QC.PartyID
            WHERE QI.bookingno = ?
                AND QC.IsDeleted = 0
        ", [$bookingNo]);

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
            // ============================================
            // LOG INCOMING REQUEST
            // ============================================
            Log::info('Save Charge Request - Start', [
                'booking_no' => $request->input('booking_no'),
                'charge_code' => $request->input('charge_code'),
                'based_on_code' => $request->input('based_on_code'),
                'based_on_uom' => $request->input('based_on_uom'),
                'action' => $request->input('action'),
                'revenue_or_cost' => $request->input('revenue_or_cost'),
                'user' => Auth::user()->username ?? Auth::user()->email,
            ]);

            // ============================================
            // VALIDATE REQUIRED FIELDS
            // ============================================
            $validator = \Validator::make($request->all(), [
                'booking_no' => 'required|string',
                'charge_code' => 'required|string',
                'based_on_code' => 'required|string',
                'revenue_or_cost' => 'required|string',
                'action' => 'required|in:Add,Edit,Delete',
            ]);

            if ($validator->fails()) {
                Log::warning('Save Charge Validation Failed', [
                    'errors' => $validator->errors()->toArray()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first()
                ], 422);
            }

            // ============================================
            // DEBUG AUTH USER
            // ============================================
            $user = Auth::user();
            Log::info('Current authenticated user', [
                'user' => $user ? [
                    'id' => $user->id ?? null,
                    'username' => $user->username ?? null,
                    'email' => $user->email ?? null,
                    'name' => $user->name ?? null,
                ] : 'No user authenticated'
            ]);


            // ============================================
            // PREPARE STORED PROCEDURE INPUT
            // ============================================
            $inputJson = json_encode([
                'OperationType' => 'QuotationAddorEditCharge',
                'SubmittedDatetime' => now()->format('YmdHis'),
                'SubmittedBy' => Auth::check()
                    ? (Auth::user()->username ?? Auth::user()->email ?? Auth::user()->name ?? 'unknown')
                    : 'system',
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
                'IncreaseBy' => (float)$request->input('increase_by', 0),
                'IncreaseType' => $request->input('increase_type', 'Percentage'),
                'SourceType' => $request->input('source_type', 'manual')
            ]);

            Log::info('Stored Procedure Input JSON', [
                'json' => $inputJson,
                'json_decoded' => json_decode($inputJson, true)
            ]);

            // ============================================
            // EXECUTE STORED PROCEDURE WITH PDO
            // ============================================
            Log::info('Executing Stored Procedure: sp_Quote_Quotaiton_ChargesAddorEdit');

            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("
            SET NOCOUNT ON;
            DECLARE @OutputJson VARCHAR(2000);
            EXEC sp_Quote_Quotaiton_ChargesAddorEdit ?, @OutputJson OUTPUT;
            SELECT @OutputJson AS OutputJson
        ");
            $stmt->execute([$inputJson]);

            // Skip empty result sets and get the final output
            $result = null;
            $resultSetCount = 0;

            do {
                $resultSetCount++;
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    Log::debug("Result set #$resultSetCount", [
                        'count' => count($rows),
                        'first_row' => !empty($rows) ? (array)$rows[0] : null
                    ]);

                    if (!empty($rows) && isset($rows[0]->OutputJson)) {
                        $result = $rows;
                        Log::info('✓ Found OutputJson in result set #' . $resultSetCount);
                        break;
                    }
                } catch (\PDOException $e) {
                    // Skip empty result sets
                    Log::debug("Result set #$resultSetCount error (skipping)", [
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            } while ($stmt->nextRowset());

            if (!$result) {
                Log::error('Stored procedure did not return OutputJson', [
                    'total_result_sets' => $resultSetCount
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Stored procedure did not return expected output'
                ], 500);
            }

            // ============================================
            // LOG RAW STORED PROCEDURE OUTPUT
            // ============================================
            Log::info('Stored Procedure Raw Output', [
                'result' => $result,
                'output_json' => $result[0]->OutputJson ?? null
            ]);

            // ============================================
            // PARSE OUTPUT
            // ============================================
            $outputJson = $result[0]->OutputJson ?? '{}';
            $output = json_decode($outputJson, true);

            // Check if JSON decode failed
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to decode stored procedure output', [
                    'json_error' => json_last_error_msg(),
                    'raw_output' => $outputJson
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to parse stored procedure response: ' . json_last_error_msg()
                ], 500);
            }

            Log::info('Stored Procedure Parsed Output', [
                'output' => $output,
                'status' => $output['Status'] ?? 'Unknown',
                'remarks' => $output['Remarks'] ?? []
            ]);

            // ============================================
            // HANDLE SUCCESS RESPONSE
            // ============================================
            if (($output['Status'] ?? '') === 'Success') {
                Log::info('Charge saved successfully', [
                    'booking_no' => $request->input('booking_no'),
                    'charge_id' => $output['ChargeID'] ?? null,
                    'action' => $request->input('action')
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $output['Remarks'][0]['Remark'] ?? 'Charge saved successfully',
                    'charge_id' => $output['ChargeID'] ?? null,
                    'data' => $output
                ]);
            }

            // ============================================
            // HANDLE FAILURE RESPONSE
            // ============================================
            $errorMessage = $output['Remarks'][0]['Remark'] ?? 'Failed to save charge';

            Log::warning('Stored Procedure returned failure', [
                'status' => $output['Status'] ?? 'Unknown',
                'message' => $errorMessage,
                'full_output' => $output
            ]);

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'details' => $output['Remarks'] ?? []
            ], 422);
        } catch (\Exception $e) {
            // ============================================
            // GENERAL ERRORS
            // ============================================
            Log::error('Save charge failed with exception', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save charge: ' . $e->getMessage(),
                'error_code' => 'GENERAL_ERROR',
                'details' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null
            ], 500);
        }
    }

    public function deleteCharge(Request $request)
    {
        try {
            $bookingNo = $request->input('booking_no');
            $chargeId = $request->input('quote_charge_id');

            // FIX: Fetch the charge details first (SP validates all fields even for DELETE)
            $charge = DB::selectOne("
            SELECT 
                QC.Id as QuoteChargeID,
                BC.Code as ChargeCode,
                QC.Quantity,
                QC.EstChargeRate as Rate,
                QC.EstChargeAmount as AmtInChgCurr,
                QC.EstBaseAmount as AmtInBaseCurr,
                QC.BaseExchRate as ExChangeRate,
                CC.Code as ChgCurr,
                CB.Code as BasedOnCode,
                ISNULL(SU.Code, '') as BasedOnUOM,
                ISNULL(QC.Remarks, '') as ChargeRemark,
                QC.IsRevenue,
                ISNULL(CP.Code, '') as PartyCode,
                ISNULL(QC.ChargeType, 'manual') as SourceType
            FROM Quote_Quotation_ChargeSheet QC WITH (NOLOCK)
            INNER JOIN Quote_QuotationInfo QI WITH (NOLOCK) ON QI.id = QC.QuotationInfoID
            LEFT JOIN [FITSCMUAT].dbo.Bill_ChargeCode BC WITH (NOLOCK) ON BC.Id = QC.ChargeCodeId
            LEFT JOIN [FITSCMUAT].dbo.Cust_Currency CC WITH (NOLOCK) ON CC.Id = QC.EstChargeCurrencyId
            LEFT JOIN Cust_ChargecodeBasedOn CB WITH (NOLOCK) ON CB.Id = QC.BasedOnId
            LEFT JOIN [FITSCMUAT].dbo.Sys_UOM SU WITH (NOLOCK) ON SU.Id = QC.UOMId
            LEFT JOIN [FITSCMUAT].dbo.Cust_Party CP WITH (NOLOCK) ON CP.Id = QC.PartyID
            WHERE QC.Id = ?
                AND QC.IsDeleted = 0
        ", [$chargeId]);

            if (!$charge) {
                Log::warning('Charge not found for deletion', [
                    'charge_id' => $chargeId,
                    'booking_no' => $bookingNo
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Charge not found or already deleted'
                ], 404);
            }

            $submittedBy = Auth::check()
                ? (Auth::user()->username ?? Auth::user()->email ?? Auth::user()->name ?? 'system')
                : 'system';

            // FIX: Use real charge data (SP validates all fields even for DELETE)
            $inputJson = json_encode([
                'OperationType' => 'QuotationAddorEditCharge',
                'SubmittedDatetime' => now()->format('YmdHis'),
                'SubmittedBy' => $submittedBy,
                'Action' => 'Delete',
                'BookingNo' => $bookingNo,
                'QuoteChargeID' => $chargeId,
                'PartyCode' => $charge->PartyCode ?? '',
                'RevenueOrCost' => $charge->IsRevenue ? 'Revenue' : 'Cost',
                'ChargeCode' => $charge->ChargeCode,
                'Quantity' => (float)$charge->Quantity,
                'Rate' => (float)$charge->Rate,
                'AmtInChgCurr' => (float)$charge->AmtInChgCurr,
                'AmtInBaseCurr' => (float)$charge->AmtInBaseCurr,
                'ExChangeRate' => (float)$charge->ExChangeRate,
                'ChgCurr' => $charge->ChgCurr ?? 'KES',
                'BasedOnCode' => $charge->BasedOnCode,
                'BasedOnUOM' => $charge->BasedOnUOM ?? '',
                'ChargeRemark' => $charge->ChargeRemark ?? '',
                'ToBeAdded' => 0,
                'IncreaseBy' => 0,
                'IncreaseType' => 'Percentage',
                'SourceType' => $charge->SourceType ?? 'manual'
            ]);

            Log::info('Delete Charge Request', [
                'booking_no' => $bookingNo,
                'charge_id' => $chargeId,
                'charge_code' => $charge->ChargeCode,
                'user' => $submittedBy
            ]);

            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("
            SET NOCOUNT ON;
            DECLARE @OutputJson VARCHAR(2000);
            EXEC sp_Quote_Quotaiton_ChargesAddorEdit ?, @OutputJson OUTPUT;
            SELECT @OutputJson AS OutputJson
        ");
            $stmt->execute([$inputJson]);

            // Skip empty result sets and get the final output
            $result = null;
            $resultSetCount = 0;

            do {
                $resultSetCount++;
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    if (!empty($rows) && isset($rows[0]->OutputJson)) {
                        $result = $rows;
                        Log::info('✓ Found OutputJson in result set #' . $resultSetCount);
                        break;
                    }
                } catch (\PDOException $e) {
                    Log::debug("Delete - Result set #$resultSetCount error (skipping)", [
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            } while ($stmt->nextRowset());

            if (!$result) {
                Log::error('Delete - Stored procedure did not return OutputJson', [
                    'total_result_sets' => $resultSetCount
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Stored procedure did not return expected output'
                ], 500);
            }

            $outputJson = $result[0]->OutputJson ?? '{}';
            $output = json_decode($outputJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Delete - Failed to decode stored procedure output', [
                    'json_error' => json_last_error_msg(),
                    'raw_output' => $outputJson
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to parse stored procedure response: ' . json_last_error_msg()
                ], 500);
            }

            if (($output['Status'] ?? '') === 'Success') {
                Log::info('Charge deleted successfully', [
                    'booking_no' => $bookingNo,
                    'charge_id' => $chargeId,
                    'charge_code' => $charge->ChargeCode
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Charge deleted successfully'
                ]);
            }

            Log::warning('Delete charge failed - SP returned failure', [
                'status' => $output['Status'] ?? 'Unknown',
                'remarks' => $output['Remarks'] ?? []
            ]);

            return response()->json([
                'success' => false,
                'message' => $output['Remarks'][0]['Remark'] ?? 'Failed to delete charge',
                'details' => $output['Remarks'] ?? []
            ], 422);
        } catch (\Exception $e) {
            Log::error('Delete charge failed with exception', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'booking_no' => $request->input('booking_no'),
                'charge_id' => $request->input('quote_charge_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete charge: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request)
    {
        try {
            $bookingNo = $request->input('booking_no');
            $targetStatus = $request->input('status');
            $reasonCode = $request->input('reason_code', '');
            $reasonRemark = $request->input('reason_remark', '');

            $submittedBy = Auth::check()
                ? (Auth::user()->username ?? Auth::user()->email ?? Auth::user()->name ?? 'system')
                : 'system';

            Log::info('Update Status Request', [
                'booking_no' => $bookingNo,
                'target_status' => $targetStatus,
                'user' => $submittedBy
            ]);

            // Get current status
            $quotation = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->first(['QuoteStatus', 'status']);

            if (!$quotation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found'
                ], 404);
            }

            $currentStatus = $quotation->QuoteStatus ?? $quotation->status ?? 'Proposal';

            Log::info('Current quotation status', [
                'booking_no' => $bookingNo,
                'current_status' => $currentStatus,
                'target_status' => $targetStatus
            ]);

            // Handle status transitions based on SP business rules
            $needsPendingFirst = false;

            if ($targetStatus === 'Lost') {
                // Lost requires current status to be Pending
                if ($currentStatus !== 'Pending') {
                    $needsPendingFirst = true;
                    Log::info('Lost requires Pending status, updating first', [
                        'current' => $currentStatus
                    ]);
                }
            } elseif ($targetStatus === 'Cancel') {
                // Cancel requires current status to be Pending or Won
                if (!in_array($currentStatus, ['Pending', 'Won'])) {
                    $needsPendingFirst = true;
                    Log::info('Cancel requires Pending/Won status, updating to Pending first', [
                        'current' => $currentStatus
                    ]);
                }
            }
            // Won has no restrictions, can be set from any status

            // Update to Pending first if needed
            if ($needsPendingFirst) {
                DB::table('Quote_QuotationInfo')
                    ->where('bookingno', $bookingNo)
                    ->update([
                        'QuoteStatus' => 'Pending',
                        'status' => 'Pending',
                        'LastModifiedBy' => $submittedBy,
                        'updatedon' => now()
                    ]);

                Log::info('Updated to Pending, now proceeding with final status');
            }

            // Now execute the SP with the target status
            $inputJson = json_encode([
                'OperationType' => 'QuotationStatusUpdate',
                'SubmittedDatetime' => now()->format('YmdHis'),
                'SubmittedBy' => $submittedBy,
                'Action' => 'Edit',
                'BookingNo' => $bookingNo,
                'Status' => $targetStatus,
                'ReasonCode' => $reasonCode,
                'ReasonRemark' => $reasonRemark
            ]);

            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("
            SET NOCOUNT ON;
            DECLARE @OutputJson VARCHAR(2000);
            EXEC sp_Quote_Quotaiton_StatusUpdate ?, @OutputJson OUTPUT;
            SELECT @OutputJson AS OutputJson
        ");
            $stmt->execute([$inputJson]);

            // Skip empty result sets
            $result = null;
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (!empty($rows) && isset($rows[0]->OutputJson)) {
                        $result = $rows;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            if (!$result) {
                Log::error('Status update SP did not return OutputJson');
                return response()->json([
                    'success' => false,
                    'message' => 'Stored procedure did not return expected output'
                ], 500);
            }

            $output = json_decode($result[0]->OutputJson ?? '{}', true);

            if (($output['Status'] ?? '') === 'Success') {
                Log::info('Status updated successfully', [
                    'booking_no' => $bookingNo,
                    'final_status' => $targetStatus
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $output['Remarks'][0]['Remark'] ?? 'Status updated successfully'
                ]);
            }

            Log::warning('Status update failed', [
                'output' => $output
            ]);

            return response()->json([
                'success' => false,
                'message' => $output['Remarks'][0]['Remark'] ?? 'Failed to update status'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Update status exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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
            // Use raw PDO to handle multiple result sets from SP
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC sp_ReasonMasterList ?, ?, ?");
            $stmt->execute([$module, $reasonType, $filter]);

            $reasons = [];

            // Loop through result sets until we find the data
            do {
                try {
                    $results = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (!empty($results)) {
                        $firstRow = $results[0];
                        // Check if this is the data result set (has ID, Code, Description)
                        if (isset($firstRow->ID) && isset($firstRow->Code) && isset($firstRow->Description)) {
                            $reasons = $results;
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    continue; // Skip empty result sets
                }
            } while ($stmt->nextRowset());

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

    public function getCommunicationTemplates(Request $request)
    {
        $module = $request->input('module', 'Quotation');
        $subModule = $request->input('sub_module');
        $officeCode = $request->input('office_code');

        if (!$subModule || !$officeCode) {
            return response()->json([
                'success' => false,
                'message' => 'Sub module and office code are required'
            ], 400);
        }

        try {
            $templates = DB::select(
                "SELECT Name, Description 
                FROM Cust_CommunicationDetails WITH (NOLOCK)
                WHERE Module = ? AND SubModule = ? AND OfficeCode = ? AND IsActive = 1",
                [$module, $subModule, $officeCode]
            );

            return response()->json([
                'success' => true,
                'templates' => $templates
            ]);
        } catch (\Exception $e) {
            Log::error('Get communication templates failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load templates: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCommunicationDescription(Request $request)
    {
        $module = $request->input('module', 'Quotation');
        $subModule = $request->input('sub_module');
        $officeCode = $request->input('office_code');
        $name = $request->input('name');

        if (!$subModule || !$officeCode || !$name) {
            return response()->json([
                'success' => false,
                'message' => 'All parameters are required'
            ], 400);
        }

        try {
            $result = DB::select('
            DECLARE @Status VARCHAR(15), @Remark VARCHAR(1000), @Description NVARCHAR(MAX);
            EXEC sp_CommunicationDetails_GetDescription ?, ?, ?, ?, 
                @Status OUTPUT, @Remark OUTPUT, @Description OUTPUT;
            SELECT @Status AS Status, @Remark AS Remark, @Description AS Description
        ', [$module, $subModule, $officeCode, $name]);

            if ($result[0]->Status === 'Success') {
                return response()->json([
                    'success' => true,
                    'description' => $result[0]->Description
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result[0]->Remark
            ], 422);
        } catch (\Exception $e) {
            Log::error('Get communication description failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load description: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getChargeCodes(Request $request)
    {
        $bookingNo = $request->input('booking_no');
        $chargeType = $request->input('charge_type', 'Revenue');
        $filter = $request->input('filter', '');

        Log::info('=== GET CHARGE CODES CALLED ===', [
            'booking_no' => $bookingNo,
            'charge_type' => $chargeType,
            'filter' => $filter,
            'filter_length' => strlen($filter)
        ]);

        if (!$bookingNo) {
            return response()->json(['success' => false, 'message' => 'Booking number required'], 400);
        }

        try {
            $quotation = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->first(['teamid']);

            if (!$quotation) {
                Log::warning('Quotation not found', ['booking_no' => $bookingNo]);
                return response()->json(['success' => false, 'message' => 'Quotation not found'], 404);
            }

            Log::info('Quotation found', ['team_id' => $quotation->teamid]);

            $office = DB::table('Cust_Office')
                ->where('TeamCode', $quotation->teamid)
                ->where('IsActive', 1)
                ->where('IsDeleted', 0)
                ->first(['Code']);

            if (!$office) {
                Log::warning('Office not found', ['team_id' => $quotation->teamid]);
                return response()->json(['success' => false, 'message' => 'Office not found'], 404);
            }

            // FIX: Trim the office code to remove trailing spaces
            $officeCode = trim($office->Code);

            Log::info('=== EXECUTING SP_CHARGECODE ===', [
                'office_code' => $officeCode,
                'office_code_raw' => $office->Code, // Log both to compare
                'charge_type' => $chargeType,
                'filter' => $filter
            ]);

            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC sp_ChargeCode ?, ?, ?");
            $stmt->execute([$officeCode, $chargeType, $filter]); // Use trimmed code

            $chargeCodes = [];
            $resultSetCount = 0;

            do {
                $resultSetCount++;
                try {
                    $results = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    Log::info("Result set #$resultSetCount", [
                        'count' => count($results),
                        'first_row' => !empty($results) ? (array)$results[0] : null
                    ]);

                    if (!empty($results)) {
                        $firstRow = $results[0];
                        // Check if this is the data result set
                        if (isset($firstRow->Id) && isset($firstRow->Code)) {
                            $chargeCodes = $results;
                            Log::info('✓ Found charge codes result set', ['count' => count($chargeCodes)]);
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    Log::debug("Result set #$resultSetCount error", ['error' => $e->getMessage()]);
                    continue;
                }
            } while ($stmt->nextRowset());

            Log::info('=== FINAL RESULT ===', [
                'charge_codes_found' => count($chargeCodes),
                'total_result_sets' => $resultSetCount
            ]);

            return response()->json([
                'success' => true,
                'charge_codes' => $chargeCodes
            ]);
        } catch (\Exception $e) {
            Log::error('Charge codes error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load charge codes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getOfficeCode(Request $request)
    {
        try {
            $teamId = $request->team_id;

            Log::info('getOfficeCode called', ['team_id' => $teamId]);

            if (!$teamId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Team ID is required'
                ], 400);
            }

            // ✅ FIX: Use TeamCode (int column), not TeamId
            $office = DB::connection('sqlsrv')
                ->table('Cust_Office')
                ->where('TeamCode', $teamId)           // ✅ Changed from TeamId
                ->where('IsActive', 1)                 // ✅ Added active filter
                ->where('IsDeleted', 0)                // ✅ Added deleted filter
                ->select('Code', 'TeamCode')           // ✅ Changed from TeamId
                ->first();

            Log::info('Office lookup result', [
                'team_id' => $teamId,
                'office_found' => $office ? 'yes' : 'no',
                'office_code' => $office ? $office->Code : null
            ]);

            if (!$office) {
                // Fallback: Try to get from quotation
                $quotation = DB::connection('sqlsrv')
                    ->table('Quote_QuotationInfo')
                    ->where('teamid', $teamId)
                    ->select('teamid')
                    ->first();

                if ($quotation) {
                    $office = DB::connection('sqlsrv')
                        ->table('Cust_Office')
                        ->where('TeamCode', $quotation->teamid)  // ✅ Changed from TeamId
                        ->where('IsActive', 1)
                        ->where('IsDeleted', 0)
                        ->select('Code')
                        ->first();
                }
            }

            if (!$office) {
                return response()->json([
                    'success' => false,
                    'message' => 'Office not found for team ID: ' . $teamId
                ], 404);
            }

            return response()->json([
                'success' => true,
                'office_code' => trim($office->Code)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get office code', [
                'team_id' => $request->team_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve office code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get currencies for office
     */
    public function getCurrencies(Request $request)
    {
        try {
            $officeCode = $request->office_code;

            Log::info('getCurrencies called', ['office_code' => $officeCode]);

            if (!$officeCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Office code is required'
                ], 400);
            }

            // Query FITSCMUAT database (where currency data lives)
            $currencies = DB::connection('sqlsrv')
                ->select("
                SELECT DISTINCT CC.Id as CurrencyID, CC.Code as CurrencyCode, CC.Name as CurrencyName
                FROM [FITSCMUAT].dbo.Cust_Office CO WITH (NOLOCK)
                INNER JOIN [FITSCMUAT].dbo.Bill_ChargeCodeOfficeMap BCO WITH (NOLOCK) 
                    ON BCO.OfficeId = CO.Id AND BCO.IsActive = 1
                INNER JOIN [FITSCMUAT].dbo.Cust_Currency CC WITH (NOLOCK) 
                    ON CC.Id = BCO.DefaultCurrencyId
                WHERE CO.Code = ?
                    AND CO.IsActive = 1 
                    AND CO.IsDeleted = 0
                    AND CC.IsActive = 1 
                    AND CC.IsDeleted = 0
                ORDER BY CC.Code
            ", [trim($officeCode)]);

            Log::info('Currencies fetched', [
                'office_code' => $officeCode,
                'count' => count($currencies)
            ]);

            return response()->json([
                'success' => true,
                'currencies' => $currencies
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get currencies', [
                'office_code' => $request->office_code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve currencies: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exchange rate for currency
     */
    /**
     * Get exchange rate for currency
     */
    public function getExchangeRate(Request $request)
    {
        try {
            $officeCode = $request->office_code;
            $fromCurrency = $request->from_currency;

            Log::info('getExchangeRate called', [
                'office_code' => $officeCode,
                'from_currency' => $fromCurrency
            ]);

            if (!$officeCode || !$fromCurrency) {
                return response()->json([
                    'success' => false,
                    'message' => 'Office code and currency are required'
                ], 400);
            }

            // Query FITSCMUAT database (where exchange rate data lives)
            $result = DB::connection('sqlsrv')
                ->select("
                SELECT TOP 1 ER.ExchangeRate as ExRate
                FROM [FITSCMUAT].dbo.Cust_Office CO WITH (NOLOCK)
                INNER JOIN [FITSCMUAT].dbo.Cust_ExchangeRate ER WITH (NOLOCK) 
                    ON ER.OfficeId = CO.Id
                    AND ER.FromCurrencyCode = ?
                    AND CAST(GETDATE() AS DATE) BETWEEN ER.FromDate AND ER.ToDate
                    AND ER.IsActive = 1 
                    AND ER.IsDeleted = 0
                WHERE CO.Code = ?
                    AND CO.IsActive = 1 
                    AND CO.IsDeleted = 0
                ORDER BY ER.FromDate DESC
            ", [$fromCurrency, trim($officeCode)]);

            if (empty($result) || !isset($result[0]->ExRate)) {
                Log::warning('Exchange rate not found', [
                    'office_code' => $officeCode,
                    'from_currency' => $fromCurrency
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Exchange rate not found, using default',
                    'exchange_rate' => 1.000000
                ]);
            }

            Log::info('Exchange rate fetched', [
                'office_code' => $officeCode,
                'from_currency' => $fromCurrency,
                'rate' => $result[0]->ExRate
            ]);

            return response()->json([
                'success' => true,
                'exchange_rate' => floatval($result[0]->ExRate)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get exchange rate', [
                'office_code' => $request->office_code,
                'from_currency' => $request->from_currency,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Failed to retrieve exchange rate, using default',
                'exchange_rate' => 1.000000
            ]);
        }
    }

    public function getLegalEntities(Request $request)
    {
        $officeCode = $request->input('office_code');
        $chargeType = $request->input('charge_type', 'Cost');
        $filter = $request->input('filter', '');

        if (!$officeCode) {
            return response()->json([
                'success' => false,
                'message' => 'Office code is required'
            ], 400);
        }

        try {
            $parties = DB::select('EXEC sp_LegalEntityList ?, ?, ?', [
                $officeCode,
                $chargeType,
                $filter
            ]);

            return response()->json([
                'success' => true,
                'parties' => $parties
            ]);
        } catch (\Exception $e) {
            Log::error('Get legal entities failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load legal entities: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBasedOnList(Request $request)
    {
        try {
            $basedOnList = DB::select(
                "SELECT Id, Code, Name, BasedOn 
                    FROM Cust_ChargecodeBasedOn WITH (NOLOCK)
                    WHERE IsActive = 1
                ORDER BY Name"
            );

            return response()->json([
                'success' => true,
                'based_on_list' => $basedOnList
            ]);
        } catch (\Exception $e) {
            Log::error('Get based on list failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load based on list: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get UOM list based on Based On code
     */
    public function getBasedOnUOM(Request $request)
    {
        try {
            $basedOnCode = $request->based_on_code;

            Log::info('Get based on UOM called', ['based_on_code' => $basedOnCode]);

            if (!$basedOnCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Based On code is required'
                ], 400);
            }

            // Execute stored procedure
            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare("EXEC sp_ChargeBasedOnUOM ?");
            $stmt->execute([$basedOnCode]);

            $uomList = [];
            $hasResults = false;

            // Handle multiple result sets
            do {
                try {
                    $results = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    if (!empty($results)) {
                        // Check if it's an error message from the SP
                        $firstRow = $results[0];
                        $firstColumn = current((array)$firstRow);

                        // If the SP returns "Failed - ..." message
                        if (is_string($firstColumn) && strpos($firstColumn, 'Failed') === 0) {
                            Log::warning('SP returned failure message', [
                                'based_on_code' => $basedOnCode,
                                'message' => $firstColumn
                            ]);
                            continue; // Skip this result set
                        }

                        // Valid UOM data
                        $uomList = array_merge($uomList, $results);
                        $hasResults = true;
                        Log::info('UOM result set fetched', ['count' => count($results)]);
                    }
                } catch (\PDOException $e) {
                    // Skip empty result sets (this is normal for this SP)
                    if (strpos($e->getMessage(), 'no fields') === false) {
                        throw $e; // Re-throw if it's a real error
                    }
                    Log::debug('Empty result set skipped', ['error' => $e->getMessage()]);
                }
            } while ($stmt->nextRowset());

            // If no UOM found, it means this Based On doesn't have UOM requirements
            // This is valid for things like AssessableValue, CommercialInvoiceVal, etc.
            if (empty($uomList)) {
                Log::info('No UOM required for this Based On', ['based_on_code' => $basedOnCode]);

                return response()->json([
                    'success' => true,
                    'uom_list' => [],
                    'message' => 'No UOM selection required for this Based On type'
                ]);
            }

            Log::info('UOM list fetched successfully', [
                'based_on_code' => $basedOnCode,
                'count' => count($uomList)
            ]);

            return response()->json([
                'success' => true,
                'uom_list' => $uomList
            ]);
        } catch (\Exception $e) {
            Log::error('Get based on UOM failed', [
                'based_on_code' => $request->based_on_code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get UOM list: ' . $e->getMessage()
            ], 500);
        }
    }
}
