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

    // ============================================
    // OFFICE BASE CURRENCY MAP
    // Source: Cust_Office + Bill_ChargeCodeOfficeMap (dominant currency per office)
    // SA has no operational currency — defaults to KES
    // ============================================
    const OFFICE_BASE_CURRENCY = [
        'DJ' => 'DJF',
        'ET' => 'ETB',
        'KE' => 'KES',
        'RW' => 'RWF',
        'SA' => 'KES', // Sales office — no dedicated currency, falls back to KES
        'SS' => 'SSP',
        'TZ' => 'TZS',
        'UG' => 'UGX',
        'ZM' => 'ZMW',
    ];

    public function index()
    {
        // ============================================
        // RESOLVE USER'S MAPPED OFFICES FROM Azure_Users
        // Auth user model uses 'Email' (capital E)
        // matched against Azure_Users.userPrincipalName
        // ============================================

        $userPrincipalName = Auth::user()->Email ?? null;
        $normalizedEmail   = strtolower(trim($userPrincipalName));

        $userOfficeCodes = DB::table('Azure_Users')
            ->where(function ($q) use ($normalizedEmail) {
                $q->whereRaw('LOWER(LTRIM(RTRIM(userPrincipalName))) = ?', [$normalizedEmail])
                    ->orWhereRaw('LOWER(LTRIM(RTRIM(alternateEmailAddress))) = ?', [$normalizedEmail]);
            })
            ->whereNotNull('BaseOfficeCode')
            ->pluck('BaseOfficeCode')
            ->flatMap(fn($codes) => explode(',', $codes))
            ->map(fn($code) => trim($code))
            ->filter()
            ->unique()
            ->values();

        if ($userOfficeCodes->isEmpty()) {
            Log::warning('Quotation index - No BaseOfficeCode found for user', [
                'userPrincipalName' => $userPrincipalName,
            ]);

            return view('quotation.index', [
                'offices'       => collect(),
                'stats'         => $this->getStats('all', null, null, null),
                'defaultOffice' => null,
                'officeError'   => 'Your account is not mapped to any office. Please contact your administrator.',
            ]);
        }

        // ============================================
        // RESOLVE OFFICE CODES → TEAMNAMES
        // Look up the actual teamname values used in Quote_QuotationInfo
        // by joining through Cust_Office on TeamCode.
        // Falls back to treating the BaseOfficeCode itself as a teamname
        // for any code that has no matching Cust_Office row.
        // ============================================

        $resolvedOffices = DB::table('Cust_Office')
            ->whereIn('Code', $userOfficeCodes->toArray())
            ->where('IsActive', 1)
            ->where('IsDeleted', 0)
            ->pluck('Code', 'Code') // we need the Code to match against teamname
            ->values();

        // For codes that matched Cust_Office, get the distinct teamnames
        // actually present in quotation data
        $offices = DB::table('Quote_QuotationInfo')
            ->whereNotNull('teamname')
            ->whereIn('teamname', $userOfficeCodes->toArray())
            ->distinct()
            ->orderBy('teamname')
            ->pluck('teamname');

        // Also check if any BaseOfficeCode maps through Cust_Office.Code → teamname
        // (handles cases where teamname stores the office Code directly)
        if ($offices->isEmpty()) {
            Log::warning('Quotation index - No teamname match on BaseOfficeCode directly, trying via Cust_Office', [
                'userPrincipalName' => $userPrincipalName,
                'base_office_codes' => $userOfficeCodes->toArray(),
            ]);

            $teamCodes = DB::table('Cust_Office')
                ->whereIn('Code', $userOfficeCodes->toArray())
                ->where('IsActive', 1)
                ->where('IsDeleted', 0)
                ->pluck('TeamCode');

            if ($teamCodes->isNotEmpty()) {
                $offices = DB::table('Quote_QuotationInfo')
                    ->whereNotNull('teamname')
                    ->whereIn('teamid', $teamCodes->toArray())
                    ->distinct()
                    ->orderBy('teamname')
                    ->pluck('teamname');
            }
        }

        if ($offices->isEmpty()) {
            Log::warning('Quotation index - BaseOfficeCode(s) could not be resolved to any office', [
                'userPrincipalName' => $userPrincipalName,
                'base_office_codes' => $userOfficeCodes->toArray(),
            ]);

            return view('quotation.index', [
                'offices'       => collect(),
                'stats'         => $this->getStats('all', null, null, null),
                'defaultOffice' => null,
                'officeError'   => 'Your office mapping could not be resolved. Please contact your administrator.',
            ]);
        }

        $defaultOffice = $offices->first();
        $stats         = $this->getStats('all', null, null, $defaultOffice);

        Log::info('Quotation index - Resolved user offices', [
            'userPrincipalName' => $userPrincipalName,
            'base_office_codes' => $userOfficeCodes->toArray(),
            'resolved_offices'  => $offices->toArray(),
            'default_office'    => $defaultOffice,
        ]);

        return view('quotation.index', compact('offices', 'stats', 'defaultOffice'));
    }

    public function stats(Request $request)
    {
        $days      = $request->input('days', 0);
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $office    = $request->input('office') ?: (Auth::user()->OfficeCode ?? null);
        $stats     = $this->getStats($days, $startDate, $endDate, $office);
        return response()->json($stats);
    }

    private function getStats($days, $startDate, $endDate, $office)
    {
        $query = DB::table('Quote_QuotationInfo');

        // ── Date filter ──────────────────────────────────────────────────────────
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
        // 'all' → no date filter

        // ── Office filter ─────────────────────────────────────────────────────────
        if ($office) {
            $query->where('teamname', $office);
        }

        $total = $query->count();

        // In Progress: not Won / Lost / Cancel / Failed Progress
        $inProgress = (clone $query)->where(function ($q) {
            $q->whereNotIn('QuoteStatus', ['Won', 'Lost', 'Cancel', 'Failed Progress'])
                ->orWhereNull('QuoteStatus');
        })->where(function ($q) {
            $q->whereNotIn('status', ['Won', 'Lost', 'Cancel', 'Failed Progress'])
                ->orWhereNull('status');
        })->count();

        // Completed: Won + (SCMINTStatus IS NULL OR 'Success')
        $completed = (clone $query)->where(function ($q) {
            $q->where('QuoteStatus', 'Won')->orWhere('status', 'Won');
        })->where('SCMINTStatus', 'Success')->count();

        // Lost / Canceled: Lost OR Cancel
        $lostCanceled = (clone $query)->where(function ($q) {
            $q->whereIn('QuoteStatus', ['Lost', 'Cancel'])
                ->orWhereIn('status', ['Lost', 'Cancel']);
        })->count();

        // Failed Int.: Won + SCMINTStatus set and not Success
        $failedInt = (clone $query)->where(function ($q) {
            $q->where('QuoteStatus', 'Won')->orWhere('status', 'Won');
        })->whereNotNull('SCMINTStatus')
            ->where('SCMINTStatus', '!=', 'Success')
            ->count();

        // Failed Progress
        $failedProgress = (clone $query)->where(function ($q) {
            $q->where('QuoteStatus', 'Failed Progress')
                ->orWhere('status', 'Failed Progress');
        })->count();

        // Won = Won but SCMINTStatus not yet set (pending integration)
        $won = (clone $query)->where(function ($q) {
            $q->where('QuoteStatus', 'Won')->orWhere('status', 'Won');
        })->whereNull('SCMINTStatus')->count();

        return [
            'total'           => $total,
            'in_progress'     => $inProgress,
            'won'             => $won,             // ← add this back
            'completed'       => $completed,
            'lost_canceled'   => $lostCanceled,
            'failed_int'      => $failedInt,
            'failed_progress' => $failedProgress,
        ];
    }

    public function data(Request $request)
    {
        try {
            $days       = $request->input('days', '0');
            $startDate  = $request->input('start_date');
            $endDate    = $request->input('end_date');
            $cardFilter = $request->input('card_filter', 'in_progress');
            $office     = $request->input('office') ?: (Auth::user()->OfficeCode ?? null);

            Log::info('Quotation data request', [
                'days'        => $days,
                'start_date'  => $startDate,
                'end_date'    => $endDate,
                'office'      => $office,
                'card_filter' => $cardFilter,
            ]);

            $query = DB::table('Quote_QuotationInfo')
                ->select([
                    'ID',
                    'QuoteNo',
                    'bookingno',
                    'companyname',
                    'clientid',
                    'username',
                    'teamname',
                    'teamid',
                    'origincountry',
                    'destinationcountry',
                    'status',
                    'QuoteStatus',
                    'SCMINTStatus',
                    'QuoteStatusDescription',
                    'CreatedOn',
                    'Updatedon',
                ]);

            // ── Date filter ──────────────────────────────────────────────────────
            if ($days === 'custom' && $startDate && $endDate) {
                $query->whereBetween('createdon', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            } elseif ($days === '1') {
                $query->whereDate('createdon', now()->subDay());
            } elseif ($days === '15') {
                $query->where('createdon', '>=', now()->subDays(15));
            } elseif ($days === '30') {
                $query->where('createdon', '>=', now()->subDays(30));
            } elseif ($days === '0') {
                $query->whereDate('createdon', today());
            }

            // ── Office filter ────────────────────────────────────────────────────
            if ($office) {
                $query->where('teamname', $office);
            }

            // ── Card filter ──────────────────────────────────────────────────────
            switch ($cardFilter) {

                case 'in_progress':
                    $query->where(function ($q) {
                        $q->whereNotIn('QuoteStatus', ['Won', 'Lost', 'Cancel', 'Failed Progress'])
                            ->orWhereNull('QuoteStatus');
                    })->where(function ($q) {
                        $q->whereNotIn('status', ['Won', 'Lost', 'Cancel', 'Failed Progress'])
                            ->orWhereNull('status');
                    });
                    break;

                // NEW: replaces 'won'
                case 'completed':
                    $query->where(function ($q) {
                        $q->where('QuoteStatus', 'Won')->orWhere('status', 'Won');
                    })->where('SCMINTStatus', 'Success');
                    break;

                // NEW: replaces separate 'lost' and 'canceled'
                case 'lost_canceled':
                    $query->where(function ($q) {
                        $q->whereIn('QuoteStatus', ['Lost', 'Cancel'])
                            ->orWhereIn('status', ['Lost', 'Cancel']);
                    });
                    break;

                case 'failed_int':
                    $query->where(function ($q) {
                        $q->where('QuoteStatus', 'Won')->orWhere('status', 'Won');
                    })->whereNotNull('SCMINTStatus')
                        ->where('SCMINTStatus', '!=', 'Success');
                    break;

                case 'Failed Progress':
                    $query->where(function ($q) {
                        $q->where('QuoteStatus', 'Failed Progress')
                            ->orWhere('status', 'Failed Progress');
                    });
                    break;

                case 'all':
                default:
                    // No filter
                    break;
            }

            return DataTables::of($query)

                // ── Search column filters (unchanged) ────────────────────────────
                ->filterColumn('OfficeName',        fn($q, $k) => $q->where('teamname',           'like', "%$k%"))
                ->filterColumn('ClientName',         fn($q, $k) => $q->where('companyname',        'like', "%$k%"))
                ->filterColumn('ClientID',           fn($q, $k) => $q->where('clientid',           'like', "%$k%"))
                ->filterColumn('SalesPerson',        fn($q, $k) => $q->where('username',           'like', "%$k%"))
                ->filterColumn('OriginCountry',      fn($q, $k) => $q->where('origincountry',      'like', "%$k%"))
                ->filterColumn('DestinationCountry', fn($q, $k) => $q->where('destinationcountry', 'like', "%$k%"))
                ->filterColumn('BookingNo',          fn($q, $k) => $q->where('bookingno',          'like', "%$k%"))
                ->filterColumn('QuotationStatus', function ($query, $keyword) {
                    $kw = strtolower(trim($keyword));
                    $query->where(function ($q) use ($kw) {
                        if (str_contains($kw, 'progress') || str_contains($kw, 'pending') || str_contains($kw, 'proposal')) {
                            $q->where(function ($sub) {
                                $sub->whereIn('QuoteStatus', ['Proposal', 'Pending', 'In Progress'])
                                    ->orWhereIn('status', ['Proposal', 'Pending'])
                                    ->orWhereNull('QuoteStatus')->orWhereNull('status');
                            });
                        } elseif (str_contains($kw, 'complete') || str_contains($kw, 'won')) {
                            $q->where(function ($sub) {
                                $sub->where('QuoteStatus', 'Won')->orWhere('status', 'Won');
                            })->where(function ($sub) {
                                $sub->whereNull('SCMINTStatus')->orWhere('SCMINTStatus', 'Success');
                            });
                        } elseif (str_contains($kw, 'lost')) {
                            $q->whereIn('QuoteStatus', ['Lost', 'Cancel'])
                                ->orWhereIn('status', ['Lost', 'Cancel']);
                        } elseif (str_contains($kw, 'cancel')) {
                            $q->where('QuoteStatus', 'Cancel')->orWhere('status', 'Cancel');
                        } elseif (str_contains($kw, 'failed') && str_contains($kw, 'progress')) {
                            $q->where('QuoteStatus', 'Failed Progress')
                                ->orWhere('status', 'Failed Progress');
                        } elseif (str_contains($kw, 'failed') || str_contains($kw, 'int')) {
                            $q->where(function ($sub) {
                                $sub->where('QuoteStatus', 'Won')->orWhere('status', 'Won');
                            })->whereNotNull('SCMINTStatus')->where('SCMINTStatus', '!=', 'Success');
                        } else {
                            $q->where('QuoteStatus', 'like', "%$kw%")
                                ->orWhere('status', 'like', "%$kw%");
                        }
                    });
                })

                // ── Added columns (unchanged) ─────────────────────────────────────
                ->addColumn('report_date',        fn($r) => $r->CreatedOn       ? date('Y-m-d H:i', strtotime($r->CreatedOn)) : '-')
                ->addColumn('OfficeName',         fn($r) => $r->teamname         ?? '-')
                ->addColumn('ClientName',         fn($r) => $r->companyname      ?? '-')
                ->addColumn('ClientID',           fn($r) => $r->clientid         ?? '-')
                ->addColumn('SalesPerson',        fn($r) => $r->username         ?? '-')
                ->addColumn('OriginCountry',      fn($r) => $r->origincountry    ?? '-')
                ->addColumn('DestinationCountry', fn($r) => $r->destinationcountry ?? '-')
                ->addColumn('BookingNo',          fn($r) => $r->bookingno        ?? '-')

                ->addColumn('QuotationStatus', function ($row) {
                    $status    = $row->QuoteStatus ?? $row->status ?? 'Proposal';
                    $scmStatus = $row->SCMINTStatus;

                    if ($status === 'Won' && $scmStatus && $scmStatus !== 'Success') return 'Failed Int.';
                    if ($status === 'Won')                                             return 'Completed';
                    if ($status === 'Failed Progress')                                 return 'Failed Progress';
                    if (in_array($status, ['Proposal', 'Pending', 'In Progress']) || is_null($status)) return 'In Progress';
                    if (in_array($status, ['Lost', 'Cancel']))                         return 'Lost / Canceled';
                    return $status;
                })

                ->addColumn('remarks_error', function ($row) {
                    $raw = $row->QuoteStatusDescription ?? null;
                    if (!$raw) return '-';

                    $decoded = json_decode('[' . $raw . ']', true);
                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) return $raw;

                    return implode(' | ', array_column($decoded, 'Remark'));
                })

                // ── Action column — updated for new card logic ────────────────────
                ->addColumn('action', function ($row) use ($cardFilter) {
                    $status    = $row->QuoteStatus ?? $row->status ?? 'Proposal';
                    $scmStatus = $row->SCMINTStatus;

                    $isInProgress = in_array($status, ['Pending', 'Proposal', 'In Progress', 'Failed Progress'])
                        || is_null($status);
                    $isFailedInt  = ($status === 'Won' && $scmStatus && $scmStatus !== 'Success');
                    $isCompleted  = ($status === 'Won' && (is_null($scmStatus) || $scmStatus === 'Success'));

                    // View button — always shown
                    $viewBtn = '<a href="' . route('quotation.view', $row->ID) . '"
                    class="btn btn-info btn-sm" title="View">
                    <i class="fas fa-eye"></i>
                </a>';

                    // Edit quotation (In Progress only)
                    $editBtn = '';
                    if ($cardFilter === 'in_progress' && $isInProgress) {
                        $editBtn = ' <a href="' . route('quotation.edit', $row->ID) . '"
                        class="btn btn-warning btn-sm" title="Edit Quotation">
                        <i class="fas fa-edit"></i>
                    </a>';
                    }

                    // Retrigger (Failed Int. only)
                    $retriggerBtn = '';
                    if ($cardFilter === 'failed_int' && $isFailedInt) {
                        $retriggerBtn = ' <button class="btn btn-danger btn-sm btn-retrigger"
                        data-booking="' . $row->bookingno . '" title="Retrigger">
                        <i class="fas fa-sync-alt"></i>
                    </button>';
                    }

                    // Create Booking — shown on Completed and Failed Int. cards
                    // (Completed = edit existing booking info; Failed Int. = retry pipeline)
                    $createBookingBtn = '';
                    if (($cardFilter === 'completed' && $isCompleted) ||
                        ($cardFilter === 'failed_int' && $isFailedInt)
                    ) {
                        $createBookingBtn = ' <button class="btn btn-success btn-sm btn-create-booking"
                        data-id="'      . $row->ID       . '"
                        data-booking="' . $row->bookingno . '"
                        data-team="'    . $row->teamid    . '"
                        data-quoteno="' . $row->QuoteNo   . '"
                        title="Create / Edit Booking">
                        <i class="fas fa-plus-circle"></i>
                    </button>';
                    }

                    return $viewBtn . $editBtn . $retriggerBtn . $createBookingBtn;
                })

                ->rawColumns(['action', 'remarks_error'])
                ->make(true);
        } catch (\Throwable $e) {
            Log::error('QuotationData 500', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            return response()->json([
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $quotation = DB::table('Quote_QuotationInfo')
                ->where('ID', $id)
                ->first();

            if (!$quotation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found'
                ], 404);
            }

            $submittedBy = Auth::check()
                ? (Auth::user()->displayName ?? Auth::user()->Email ?? 'system')
                : 'system';

            Log::info('Update Quotation - Starting', [
                'id'         => $id,
                'booking_no' => $quotation->bookingno,
                'updated_by' => $submittedBy,
            ]);

            DB::table('Quote_QuotationInfo')
                ->where('ID', $id)
                ->update([

                    // Shipment
                    'origincountry'       => $request->input('origincountry'),
                    'destinationcountry'  => $request->input('destinationcountry'),
                    'incoterm'            => $request->input('incoterm'),
                    'shipmentmode'        => $request->input('shipmentmode'),
                    'TransportMode'       => $request->input('TransportMode'),
                    'ServiceClass'        => $request->input('ServiceClass'),
                    'serviceverticalname' => $request->input('serviceverticalname'),
                    'process'             => $request->input('process'),

                    // Cargo
                    'commodity'     => $request->input('commodity'),
                    'scopevolume'   => $request->input('scopevolume'),
                    'cargovolume'   => $request->input('cargovolume'),
                    'containerlist' => $request->input('containerlist'),

                    // Customer
                    'companyname'             => $request->input('companyname'),
                    'pri_partner_steet'       => $request->input('pri_partner_steet'),
                    'pri_partner_steet2'      => $request->input('pri_partner_steet2'),
                    'pri_partner_city'        => $request->input('pri_partner_city'),
                    'pri_partner_zip'         => $request->input('pri_partner_zip'),
                    'pri_partner_countryname' => $request->input('pri_partner_countryname'),
                    'pri_partner_email'       => $request->input('pri_partner_email'),
                    'pri_partner_phone'       => $request->input('pri_partner_phone'),

                    // Audit
                    'LastModifiedBy' => $submittedBy,
                    'updatedon'      => now(),
                ]);

            Log::info('Update Quotation - Success', [
                'id'         => $id,
                'booking_no' => $quotation->bookingno,
                'updated_by' => $submittedBy,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Update Quotation - Failed', [
                'id'    => $id,
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
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

    public function copy($bookingno)
    {
        try {
            // ============================================
            // FIND ORIGINAL QUOTATION UUID BY BOOKING NUMBER
            // ============================================
            $original = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingno)
                ->select('ID', 'bookingno')
                ->first();

            if (!$original) {
                Log::error('Copy Quotation - Original not found', [
                    'bookingno' => $bookingno
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found for booking number: ' . $bookingno
                ], 404);
            }

            $submittedBy = Auth::check()
                ? (Auth::user()->displayName ?? Auth::user()->Email ?? 'system')
                : 'system';

            Log::info('Copy Quotation - Starting', [
                'original_booking_no' => $original->bookingno,
                'original_id'         => $original->ID,
                'copied_by'           => $submittedBy,
            ]);

            // ============================================
            // CALL COPY SP
            // Handles header + charges internally
            // ============================================

            $pdo = DB::getPdo();
            $sql = "
            DECLARE @Status  VARCHAR(25)
            DECLARE @Remark  VARCHAR(200)
            DECLARE @QuoteNo VARCHAR(50)
            EXEC dbo.sp_Quote_QuotationCopy ?,
                @Status  = @Status  OUTPUT,
                @Remark  = @Remark  OUTPUT,
                @QuoteNo = @QuoteNo OUTPUT
            SELECT @Status AS Status, @Remark AS Remark, @QuoteNo AS QuoteNo  
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$original->ID]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$result || strtolower($result['Status']) !== 'success') {
                $remark = $result['Remark'] ?? 'Unknown error from copy SP';

                Log::error('Copy Quotation - SP Failed', [
                    'original_booking_no' => $original->bookingno,
                    'sp_status'           => $result['Status'] ?? null,
                    'sp_remark'           => $remark,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $remark
                ], 500);
            }

            $newBookingNo = $result['QuoteNo'];

            // ============================================
            // FETCH NEW QUOTATION ID FOR REDIRECT
            // ============================================

            $newQuotation = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $newBookingNo)
                ->select('ID')
                ->first();

            if (!$newQuotation) {
                Log::error('Copy Quotation - New record not found after SP', [
                    'new_booking_no' => $newBookingNo,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Copy succeeded but new quotation record could not be located.'
                ], 500);
            }

            Log::info('Copy Quotation - Success', [
                'original_booking_no' => $original->bookingno,
                'new_booking_no'      => $newBookingNo,
                'new_id'              => $newQuotation->ID,
                'copied_by'           => $submittedBy,
            ]);

            return response()->json([
                'success'        => true,
                'new_booking_no' => $newBookingNo,
                'new_id'         => $newQuotation->ID,
                'redirect_url'   => route('quotation.edit', $newQuotation->ID) . '?copied=1',
            ]);
        } catch (\Exception $e) {

            Log::error('Copy Quotation - Failed', [
                'original_bookingno' => $bookingno,
                'error'              => $e->getMessage(),
                'file'               => $e->getFile(),
                'line'               => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
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
            // Get quotation context for better error logging
            $quotation = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->first(['ID', 'teamid', 'ServiceVerticalname', 'origincountry', 'destinationcountry']);

            if (!$quotation) {
                Log::warning('Quotation not found for charges', ['booking_no' => $bookingNo]);
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found'
                ], 404);
            }

            // Get office code for context

            $office = DB::table('Cust_Office')
                ->where('TeamCode', $quotation->teamid)
                ->where('IsActive', 1)
                ->where('IsDeleted', 0)
                ->first(['Code']);

            $officeCode = $office ? trim($office->Code) : 'Unknown';

            Log::info('Loading charges with context', [
                'booking_no' => $bookingNo,
                'quotation_id' => $quotation->ID,
                'office_code' => $officeCode,
                'service_type' => $quotation->ServiceVerticalname ?? 'N/A',
                'origin' => $quotation->origincountry ?? 'N/A',
                'destination' => $quotation->destinationcountry ?? 'N/A',
            ]);

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

            Log::info('Charges query executed', [
                'booking_no' => $bookingNo,
                'result_count' => count($charges),
                'first_result_keys' => !empty($charges) ? array_keys((array)$charges[0]) : []
            ]);

            // ============================================
            // ENHANCED ERROR DETECTION (FROM SCM SYSTEM)
            // ============================================

            if (!empty($charges) && is_object($charges[0])) {
                $properties = get_object_vars($charges[0]);

                // Check for error indicators in property names or values
                foreach ($properties as $key => $value) {

                    // Error conditions:
                    // 1. Empty/blank property name (unnamed column - SQL error indicator)
                    // 2. Property value contains "Error Message"

                    // 3. Property value contains "Failed"
                    if (
                        empty($key) ||
                        $key === '' ||
                        (is_string($value) && (
                            strpos($value, 'Error Message') !== false ||
                            strpos($value, 'Failed -') !== false
                        ))
                    ) {

                        Log::error('Charges query returned error object:', [
                            'booking_no' => $bookingNo,
                            'quotation_id' => $quotation->ID,
                            'office_code' => $officeCode,
                            'error_property_name' => $key,
                            'error_message' => $value,
                            'all_properties' => $properties,
                            'context' => [
                                'service_type' => $quotation->ServiceVerticalname ?? 'N/A',
                                'origin' => $quotation->origincountry ?? 'N/A',
                                'destination' => $quotation->destinationcountry ?? 'N/A',
                            ],

                            'probable_cause' => 'Database schema mismatch, missing joins, or stored procedure error'
                        ]);

                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to load charges: Database query error detected',
                            'error_details' => config('app.debug') ? $value : null
                        ], 500);
                    }
                }
            }

            $revenue = [];

            $cost = [];

            foreach ($charges as $charge) {

                // Skip if charge lacks required properties

                if (!property_exists($charge, 'QuoteChargeID') || !property_exists($charge, 'ChargeName')) {

                    Log::warning('Skipping invalid charge entry:', [

                        'booking_no' => $bookingNo,

                        'charge_properties' => array_keys((array)$charge)

                    ]);

                    continue;
                }

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

            Log::info('Charges loaded successfully', [

                'booking_no' => $bookingNo,

                'revenue_count' => count($revenue),

                'cost_count' => count($cost),

                'total_revenue' => array_sum(array_column($revenue, 'amtInBaseCurr')),

                'total_cost' => array_sum(array_column($cost, 'amtInBaseCurr'))

            ]);

            return response()->json([

                'success' => true,

                'revenue' => $revenue,

                'cost' => $cost

            ]);
        } catch (\Illuminate\Database\QueryException $e) {

            Log::error('Charges query exception:', [

                'booking_no' => $bookingNo,

                'error_message' => $e->getMessage(),

                'error_code' => $e->getCode(),

                'sql' => $e->getSql() ?? 'N/A',

                'bindings' => $e->getBindings() ?? [],

                'file' => $e->getFile(),

                'line' => $e->getLine(),

            ]);

            return response()->json([

                'success' => false,

                'message' => 'Database error while loading charges',

                'error_details' => config('app.debug') ? $e->getMessage() : null

            ], 500);
        } catch (\Exception $e) {

            Log::error('Get charges failed with general exception:', [

                'booking_no' => $bookingNo,

                'error_message' => $e->getMessage(),

                'error_file' => $e->getFile(),

                'error_line' => $e->getLine(),

                'trace' => $e->getTraceAsString()

            ]);

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
            // GET QUOTATION CONTEXT FOR DETAILED LOGGING
            // ============================================

            $bookingNo = $request->input('booking_no');
            $quotation = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->first(['ID', 'teamid', 'ServiceVerticalname', 'origincountry', 'destinationcountry', 'QuoteStatus', 'status']);

            $officeCode = 'Unknown';
            if ($quotation) {
                $office = DB::table('Cust_Office')
                    ->where('TeamCode', $quotation->teamid)
                    ->where('IsActive', 1)
                    ->where('IsDeleted', 0)
                    ->first(['Code']);
                $officeCode = $office ? trim($office->Code) : 'Unknown';
            }

            // ============================================
            // LOG INCOMING REQUEST WITH CONTEXT
            // ============================================
            Log::info('Save Charge Request - Start', [
                'booking_no' => $bookingNo,
                'charge_code' => $request->input('charge_code'),
                'based_on_code' => $request->input('based_on_code'),
                'based_on_uom' => $request->input('based_on_uom'),
                'action' => $request->input('action'),
                'revenue_or_cost' => $request->input('revenue_or_cost'),
                'user' => Auth::user()->displayName ?? Auth::user()->Email ?? 'system',
                'context' => [
                    'quotation_id' => $quotation->ID ?? 'N/A',
                    'office_code' => $officeCode,
                    'service_type' => $quotation->ServiceVerticalname ?? 'N/A',
                    'status' => $quotation->QuoteStatus ?? $quotation->status ?? 'Unknown',
                ]
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
                    'booking_no' => $bookingNo,
                    'errors' => $validator->errors()->toArray(),
                    'request_data' => $request->all()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()->toArray()
                ], 422);
            }

            // ============================================
            // VERIFY QUOTATION EXISTS
            // ============================================

            if (!$quotation) {
                Log::error('Save Charge - Quotation not found', [
                    'booking_no' => $bookingNo,
                    'action' => $request->input('action'),
                    'user' => Auth::user()->displayName ?? Auth::user()->Email ?? 'system'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found for booking number: ' . $bookingNo
                ], 404);
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
                    ? (Auth::user()->displayName ?? Auth::user()->Email ?? 'system')
                    : 'system',

                'Action' => $request->input('action', 'Add'),
                'BookingNo' => $bookingNo,
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
                'booking_no' => $bookingNo,
                'action' => $request->input('action'),
                'json' => $inputJson,
                'json_decoded' => json_decode($inputJson, true)
            ]);

            // ============================================

            // EXECUTE STORED PROCEDURE WITH PDO

            // ============================================
            Log::info('Executing Stored Procedure: sp_Quote_Quotaiton_ChargesAddorEdit', [
                'booking_no' => $bookingNo,
                'office_code' => $officeCode
            ]);

            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("
            SET NOCOUNT ON;
            DECLARE @OutputJson VARCHAR(2000);
            EXEC sp_Quote_Quotaiton_ChargesAddorEdit ?, @OutputJson OUTPUT;
            SELECT @OutputJson AS OutputJson

            ");

            $executeSuccess = $stmt->execute([$inputJson]);
            if (!$executeSuccess) {
                $errorInfo = $stmt->errorInfo();
                Log::error('Stored procedure execution failed', [
                    'booking_no' => $bookingNo,
                    'error_code' => $errorInfo[0] ?? 'Unknown',
                    'sql_state' => $errorInfo[1] ?? 'Unknown',
                    'error_message' => $errorInfo[2] ?? 'Unknown',
                    'input_json' => $inputJson
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to execute stored procedure',
                    'error_details' => config('app.debug') ? $errorInfo[2] : null
                ], 500);
            }

            // ============================================
            // ENHANCED RESULT SET PROCESSING
            // ============================================

            $result = null;
            $resultSetCount = 0;

            do {
                $resultSetCount++;
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    Log::debug("Result set #$resultSetCount", [
                        'booking_no' => $bookingNo,
                        'count' => count($rows),
                        'first_row' => !empty($rows) ? (array)$rows[0] : null
                    ]);

                    // ============================================
                    // ENHANCED ERROR DETECTION IN RESULT SET
                    // ============================================

                    if (!empty($rows) && is_object($rows[0])) {
                        $properties = get_object_vars($rows[0]);

                        // Check for error indicators
                        $hasError = false;
                        foreach ($properties as $key => $value) {
                            if (
                                empty($key) ||
                                $key === '' ||
                                (is_string($value) && (
                                    strpos($value, 'Error Message') !== false ||
                                    strpos($value, 'Failed -') !== false
                                ))
                            ) {
                                Log::error('Stored procedure returned error object:', [
                                    'booking_no' => $bookingNo,
                                    'result_set' => $resultSetCount,
                                    'error_property_name' => $key,
                                    'error_message' => $value,
                                    'all_properties' => $properties,
                                    'context' => [
                                        'office_code' => $officeCode,
                                        'service_type' => $quotation->ServiceVerticalname ?? 'N/A',
                                        'action' => $request->input('action'),
                                    ]
                                ]);
                                $hasError = true;
                                break;
                            }
                        }

                        if ($hasError) {
                            continue; // Skip this result set
                        }

                        // Valid result set with OutputJson
                        if (isset($rows[0]->OutputJson)) {
                            $result = $rows;
                            Log::info('✓ Found OutputJson in result set #' . $resultSetCount, [
                                'booking_no' => $bookingNo
                            ]);
                            break;
                        }
                    }
                } catch (\PDOException $e) {

                    // Skip empty result sets
                    Log::debug("Result set #$resultSetCount error (skipping)", [
                        'booking_no' => $bookingNo,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            } while ($stmt->nextRowset());

            if (!$result) {
                Log::error('Stored procedure did not return OutputJson', [
                    'booking_no' => $bookingNo,
                    'total_result_sets' => $resultSetCount,
                    'action' => $request->input('action'),
                    'office_code' => $officeCode,
                    'probable_cause' => 'Stored procedure may have failed silently or returned unexpected format'
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
                'booking_no' => $bookingNo,
                'result' => $result,
                'output_json' => $result[0]->OutputJson ?? null
            ]);

            // ============================================
            // PARSE OUTPUT WITH ENHANCED ERROR HANDLING
            // ============================================

            $outputJson = $result[0]->OutputJson ?? '{}';
            $output = json_decode($outputJson, true);

            // Check if JSON decode failed

            if (json_last_error() !== JSON_ERROR_NONE) {

                Log::error('Failed to decode stored procedure output', [
                    'booking_no' => $bookingNo,
                    'json_error' => json_last_error_msg(),
                    'json_error_code' => json_last_error(),
                    'raw_output' => $outputJson,
                    'context' => [
                        'office_code' => $officeCode,
                        'action' => $request->input('action'),
                    ]
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to parse stored procedure response: ' . json_last_error_msg(),
                    'error_details' => config('app.debug') ? $outputJson : null
                ], 500);
            }

            Log::info('Stored Procedure Parsed Output', [
                'booking_no' => $bookingNo,
                'output' => $output,
                'status' => $output['Status'] ?? 'Unknown',
                'remarks' => $output['Remarks'] ?? []
            ]);

            // ============================================
            // HANDLE SUCCESS RESPONSE
            // ============================================

            if (($output['Status'] ?? '') === 'Success') {
                Log::info('Charge saved successfully', [
                    'booking_no' => $bookingNo,
                    'charge_id' => $output['ChargeID'] ?? null,
                    'action' => $request->input('action'),
                    'revenue_or_cost' => $request->input('revenue_or_cost'),
                    'charge_code' => $request->input('charge_code'),
                    'amount' => $request->input('amt_in_base_curr'),
                    'user' => Auth::user()->displayName ?? Auth::user()->Email ?? 'system'
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
                'booking_no' => $bookingNo,
                'status' => $output['Status'] ?? 'Unknown',
                'message' => $errorMessage,
                'full_output' => $output,
                'context' => [
                    'office_code' => $officeCode,
                    'action' => $request->input('action'),
                    'charge_code' => $request->input('charge_code'),
                    'revenue_or_cost' => $request->input('revenue_or_cost'),
                ]
            ]);

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'details' => $output['Remarks'] ?? []
            ], 422);
        } catch (\PDOException $e) {

            // ============================================
            // PDO-SPECIFIC ERRORS
            // ============================================

            Log::error('Save charge failed with PDO exception', [
                'booking_no' => $request->input('booking_no'),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'sql_state' => $e->errorInfo[0] ?? 'Unknown',
                'driver_error_code' => $e->errorInfo[1] ?? 'Unknown',
                'driver_error_message' => $e->errorInfo[2] ?? 'Unknown',
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Database connection error while saving charge',
                'error_code' => 'PDO_ERROR',
                'details' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'sql_state' => $e->errorInfo[0] ?? null,
                ] : null

            ], 500);
        } catch (\Illuminate\Database\QueryException $e) {

            // ============================================
            // LARAVEL QUERY ERRORS
            // ============================================

            Log::error('Save charge failed with query exception', [
                'booking_no' => $request->input('booking_no'),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'sql' => $e->getSql() ?? 'N/A',
                'bindings' => $e->getBindings() ?? [],
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Database query error while saving charge',
                'error_code' => 'QUERY_ERROR',
                'details' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null

            ], 500);
        } catch (\Exception $e) {

            // ============================================
            // GENERAL ERRORS
            // ============================================

            Log::error('Save charge failed with general exception', [
                'booking_no' => $request->input('booking_no'),
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

            // ============================================
            // VALIDATE REQUIRED FIELDS
            // ============================================

            if (!$bookingNo || !$chargeId) {
                Log::warning('Delete Charge - Missing required fields', [
                    'booking_no' => $bookingNo,
                    'charge_id' => $chargeId,
                    'user' => Auth::user()->displayName ?? Auth::user()->Email ?? 'system'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => !$bookingNo ? 'Booking number is required' : 'Charge ID is required'
                ], 400);
            }

            // ============================================
            // GET QUOTATION CONTEXT FOR DETAILED LOGGING
            // ============================================

            $quotation = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->first(['ID', 'teamid', 'ServiceVerticalname', 'origincountry', 'destinationcountry', 'QuoteStatus', 'status']);

            $officeCode = 'Unknown';

            if ($quotation) {
                $office = DB::table('Cust_Office')
                    ->where('TeamCode', $quotation->teamid)
                    ->where('IsActive', 1)
                    ->where('IsDeleted', 0)
                    ->first(['Code']);
                $officeCode = $office ? trim($office->Code) : 'Unknown';
            }

            Log::info('Delete Charge - Starting', [
                'booking_no' => $bookingNo,
                'charge_id' => $chargeId,
                'user' => Auth::user()->displayName ?? Auth::user()->Email ?? 'system',
                'context' => [
                    'quotation_id' => $quotation->ID ?? 'N/A',
                    'office_code' => $officeCode,
                    'service_type' => $quotation->ServiceVerticalname ?? 'N/A',
                    'origin' => $quotation->origincountry ?? 'N/A',
                    'destination' => $quotation->destinationcountry ?? 'N/A',
                    'current_status' => $quotation->QuoteStatus ?? $quotation->status ?? 'Unknown',
                ]
            ]);

            // ============================================
            // VERIFY QUOTATION EXISTS
            // ============================================
            if (!$quotation) {
                Log::error('Delete Charge - Quotation not found', [
                    'booking_no' => $bookingNo,
                    'charge_id' => $chargeId,
                    'user' => Auth::user()->displayName ?? Auth::user()->Email ?? 'system'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found for booking number: ' . $bookingNo
                ], 404);
            }

            // ============================================
            // FETCH CHARGE DETAILS WITH ENHANCED VALIDATION
            // ============================================

            $charge = DB::selectOne("
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

            Log::info('Delete Charge - Charge lookup result', [
                'booking_no' => $bookingNo,
                'charge_id' => $chargeId,
                'charge_found' => $charge ? 'yes' : 'no',
                'charge_code' => $charge->ChargeCode ?? 'N/A',
                'charge_name' => $charge->ChargeName ?? 'N/A',
                'is_revenue' => $charge->IsRevenue ?? 'N/A',
            ]);

            // ============================================
            // ENHANCED ERROR DETECTION ON CHARGE LOOKUP
            // ============================================

            if (!empty($charge) && is_object($charge)) {
                $properties = get_object_vars($charge);

                foreach ($properties as $key => $value) {
                    if (
                        empty($key) ||
                        $key === '' ||

                        (is_string($value) && (
                            strpos($value, 'Error Message') !== false ||
                            strpos($value, 'Failed -') !== false
                        ))
                    ) {
                        Log::error('Delete Charge - Charge lookup returned error object:', [
                            'booking_no' => $bookingNo,
                            'charge_id' => $chargeId,
                            'error_property_name' => $key,
                            'error_message' => $value,
                            'all_properties' => $properties,
                            'context' => [
                                'office_code' => $officeCode,
                                'service_type' => $quotation->ServiceVerticalname ?? 'N/A',
                            ],
                            'probable_cause' => 'Database schema mismatch or invalid charge ID'
                        ]);

                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to retrieve charge details for deletion',
                            'error_details' => config('app.debug') ? $value : null
                        ], 500);
                    }
                }
            }

            if (!$charge) {
                Log::warning('Delete Charge - Charge not found or already deleted', [
                    'charge_id' => $chargeId,
                    'booking_no' => $bookingNo,
                    'office_code' => $officeCode,
                    'user' => Auth::user()->displayName ?? Auth::user()->Email ?? 'system'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Charge not found or already deleted'
                ], 404);
            }

            // ============================================
            // PREPARE DELETE REQUEST
            // ============================================

            $submittedBy = Auth::check()
                ? (Auth::user()->displayName ?? Auth::user()->Email ?? 'system')
                : 'system';

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

            Log::info('Delete Charge - Stored Procedure Input', [
                'booking_no' => $bookingNo,
                'charge_id' => $chargeId,
                'charge_code' => $charge->ChargeCode,
                'is_revenue' => $charge->IsRevenue,
                'user' => $submittedBy,
                'office_code' => $officeCode,
                'json' => $inputJson
            ]);

            // ============================================
            // EXECUTE STORED PROCEDURE WITH PDO
            // ============================================

            Log::info('Delete Charge - Executing Stored Procedure: sp_Quote_Quotaiton_ChargesAddorEdit', [
                'booking_no' => $bookingNo,
                'charge_id' => $chargeId,
                'office_code' => $officeCode
            ]);

            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("
            SET NOCOUNT ON;
            DECLARE @OutputJson VARCHAR(2000);
            EXEC sp_Quote_Quotaiton_ChargesAddorEdit ?, @OutputJson OUTPUT;
            SELECT @OutputJson AS OutputJson    
                ");

            $executeSuccess = $stmt->execute([$inputJson]);

            if (!$executeSuccess) {
                $errorInfo = $stmt->errorInfo();

                Log::error('Delete Charge - Stored procedure execution failed', [
                    'booking_no' => $bookingNo,
                    'charge_id' => $chargeId,
                    'error_code' => $errorInfo[0] ?? 'Unknown',
                    'sql_state' => $errorInfo[1] ?? 'Unknown',
                    'error_message' => $errorInfo[2] ?? 'Unknown',
                    'input_json' => $inputJson,
                    'context' => [
                        'office_code' => $officeCode,
                        'charge_code' => $charge->ChargeCode,
                    ]
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to execute delete stored procedure',
                    'error_details' => config('app.debug') ? $errorInfo[2] : null
                ], 500);
            }

            // ============================================
            // ENHANCED RESULT SET PROCESSING
            // ============================================

            $result = null;

            $resultSetCount = 0;

            do {
                $resultSetCount++;
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    Log::debug("Delete Charge - Result set #$resultSetCount", [
                        'booking_no' => $bookingNo,
                        'charge_id' => $chargeId,
                        'count' => count($rows),
                        'first_row' => !empty($rows) ? (array)$rows[0] : null
                    ]);

                    // ============================================
                    // ENHANCED ERROR DETECTION IN RESULT SET
                    // ============================================

                    if (!empty($rows) && is_object($rows[0])) {
                        $properties = get_object_vars($rows[0]);
                        $hasError = false;

                        foreach ($properties as $key => $value) {
                            if (
                                empty($key) ||
                                $key === '' ||
                                (is_string($value) && (
                                    strpos($value, 'Error Message') !== false ||
                                    strpos($value, 'Failed -') !== false
                                ))

                            ) {

                                Log::error('Delete Charge - SP returned error object in result set:', [
                                    'booking_no' => $bookingNo,
                                    'charge_id' => $chargeId,
                                    'result_set' => $resultSetCount,
                                    'error_property_name' => $key,
                                    'error_message' => $value,
                                    'all_properties' => $properties,
                                    'context' => [
                                        'office_code' => $officeCode,
                                        'charge_code' => $charge->ChargeCode,
                                        'is_revenue' => $charge->IsRevenue,
                                    ]
                                ]);
                                $hasError = true;
                                break;
                            }
                        }

                        if ($hasError) {
                            continue;
                        }

                        if (isset($rows[0]->OutputJson)) {
                            $result = $rows;

                            Log::info('Delete Charge - ✓ Found OutputJson in result set #' . $resultSetCount, [
                                'booking_no' => $bookingNo,
                                'charge_id' => $chargeId
                            ]);
                            break;
                        }
                    }
                } catch (\PDOException $e) {

                    Log::debug("Delete Charge - Result set #$resultSetCount error (skipping)", [
                        'booking_no' => $bookingNo,
                        'charge_id' => $chargeId,
                        'error' => $e->getMessage()
                    ]);

                    continue;
                }
            } while ($stmt->nextRowset());

            if (!$result) {

                Log::error('Delete Charge - SP did not return OutputJson', [
                    'booking_no' => $bookingNo,
                    'charge_id' => $chargeId,
                    'total_result_sets' => $resultSetCount,
                    'office_code' => $officeCode,
                    'charge_code' => $charge->ChargeCode,
                    'probable_cause' => 'Stored procedure may have failed silently or returned unexpected format'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Stored procedure did not return expected output'
                ], 500);
            }

            // ============================================
            // PARSE OUTPUT WITH ENHANCED ERROR HANDLING
            // ============================================

            $outputJson = $result[0]->OutputJson ?? '{}';
            $output = json_decode($outputJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Delete Charge - Failed to decode SP output', [
                    'booking_no' => $bookingNo,
                    'charge_id' => $chargeId,
                    'json_error' => json_last_error_msg(),
                    'json_error_code' => json_last_error(),
                    'raw_output' => $outputJson,
                    'context' => [
                        'office_code' => $officeCode,
                        'charge_code' => $charge->ChargeCode,
                    ]
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to parse stored procedure response: ' . json_last_error_msg(),
                    'error_details' => config('app.debug') ? $outputJson : null
                ], 500);
            }

            // ============================================
            // HANDLE SUCCESS
            // ============================================

            if (($output['Status'] ?? '') === 'Success') {
                Log::info('Delete Charge - Charge deleted successfully', [
                    'booking_no' => $bookingNo,
                    'charge_id' => $chargeId,
                    'charge_code' => $charge->ChargeCode,
                    'charge_name' => $charge->ChargeName,
                    'was_revenue' => (bool)$charge->IsRevenue,
                    'base_amount' => $charge->AmtInBaseCurr,
                    'user' => $submittedBy,
                    'office_code' => $officeCode
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Charge deleted successfully'
                ]);
            }

            // ============================================
            // HANDLE FAILURE
            // ============================================

            $errorMessage = $output['Remarks'][0]['Remark'] ?? 'Failed to delete charge';

            Log::warning('Delete Charge - SP returned failure', [
                'booking_no' => $bookingNo,
                'charge_id' => $chargeId,
                'charge_code' => $charge->ChargeCode,
                'status' => $output['Status'] ?? 'Unknown',
                'message' => $errorMessage,
                'full_output' => $output,
                'context' => [
                    'office_code' => $officeCode,
                    'is_revenue' => $charge->IsRevenue,
                    'user' => $submittedBy
                ]
            ]);

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'details' => $output['Remarks'] ?? []
            ], 422);
        } catch (\PDOException $e) {

            Log::error('Delete Charge - PDO exception', [
                'booking_no' => $request->input('booking_no'),
                'charge_id' => $request->input('quote_charge_id'),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'sql_state' => $e->errorInfo[0] ?? 'Unknown',
                'driver_error_code' => $e->errorInfo[1] ?? 'Unknown',
                'driver_error_message' => $e->errorInfo[2] ?? 'Unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Database connection error while deleting charge',
                'error_code' => 'PDO_ERROR',
                'details' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'sql_state' => $e->errorInfo[0] ?? null,
                ] : null

            ], 500);
        } catch (\Illuminate\Database\QueryException $e) {

            Log::error('Delete Charge - Query exception', [
                'booking_no' => $request->input('booking_no'),
                'charge_id' => $request->input('quote_charge_id'),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'sql' => $e->getSql() ?? 'N/A',
                'bindings' => $e->getBindings() ?? [],
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Database query error while deleting charge',
                'error_code' => 'QUERY_ERROR',
                'details' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null

            ], 500);
        } catch (\Exception $e) {

            Log::error('Delete Charge - General exception', [
                'booking_no' => $request->input('booking_no'),
                'charge_id' => $request->input('quote_charge_id'),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
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

            // ============================================
            // VALIDATE REQUIRED FIELDS
            // ============================================

            if (!$bookingNo || !$targetStatus) {
                Log::warning('Update Status - Missing required fields', [
                    'booking_no' => $bookingNo,
                    'target_status' => $targetStatus,
                    'user' => Auth::user()->displayName ?? Auth::user()->Email ?? 'system'

                ]);

                return response()->json([
                    'success' => false,
                    'message' => !$bookingNo ? 'Booking number is required' : 'Status is required'
                ], 400);
            }

            // Validate status value
            if (!in_array($targetStatus, ['Won', 'Lost', 'Cancel'])) {
                Log::warning('Update Status - Invalid status value', [
                    'booking_no' => $bookingNo,
                    'target_status' => $targetStatus,
                    'valid_statuses' => ['Won', 'Lost', 'Cancel'],
                    'user' => Auth::user()->displayName ?? Auth::user()->Email ?? 'system'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status. Must be Won, Lost, or Cancel'
                ], 400);
            }

            $submittedBy = Auth::check()
                ? (Auth::user()->displayName ?? Auth::user()->Email ?? 'system')
                : 'system';

            // ============================================
            // GET QUOTATION WITH FULL CONTEXT
            // ============================================

            $quotation = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->first([
                    'ID',
                    'teamid',
                    'QuoteStatus',
                    'status',
                    'ServiceVerticalname',
                    'origincountry',
                    'destinationcountry',
                    'companyname'
                ]);

            if (!$quotation) {
                Log::error('Update Status - Quotation not found', [
                    'booking_no' => $bookingNo,
                    'target_status' => $targetStatus,
                    'user' => $submittedBy

                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found'
                ], 404);
            }

            // Get office code for context
            $officeCode = 'Unknown';
            $office = DB::table('Cust_Office')
                ->where('TeamCode', $quotation->teamid)
                ->where('IsActive', 1)
                ->where('IsDeleted', 0)
                ->first(['Code']);

            if ($office) {
                $officeCode = trim($office->Code);
            }

            $currentStatus = $quotation->QuoteStatus ?? $quotation->status ?? 'Proposal';

            Log::info('Update Status - Starting', [
                'booking_no' => $bookingNo,
                'current_status' => $currentStatus,
                'target_status' => $targetStatus,
                'user' => $submittedBy,
                'context' => [
                    'quotation_id' => $quotation->ID,
                    'office_code' => $officeCode,
                    'service_type' => $quotation->ServiceVerticalname ?? 'N/A',
                    'company' => $quotation->companyname ?? 'N/A',
                    'origin' => $quotation->origincountry ?? 'N/A',
                    'destination' => $quotation->destinationcountry ?? 'N/A',
                    'reason_code' => $reasonCode ?: 'N/A',
                    'reason_remark' => $reasonRemark ?: 'N/A',

                ]
            ]);

            // ============================================
            // HANDLE "WON" STATUS SEPARATELY (NOT IN SP)
            // ============================================

            if ($targetStatus === 'Won') {
                Log::info('Update Status - Handling Won status directly', [
                    'booking_no' => $bookingNo,
                    'current_status' => $currentStatus,
                    'user' => $submittedBy,
                    'office_code' => $officeCode
                ]);

                DB::beginTransaction();

                try {
                    // Update quotation status
                    $updatedRows = DB::table('Quote_QuotationInfo')
                        ->where('bookingno', $bookingNo)
                        ->update([
                            'QuoteStatus' => 'Won',
                            'status' => 'Won',
                            'LastModifiedBy' => $submittedBy,
                            'updatedon' => now()
                        ]);

                    if ($updatedRows === 0) {
                        DB::rollBack();
                        Log::error('Update Status - Won update affected no rows', [
                            'booking_no' => $bookingNo,
                            'quotation_id' => $quotation->ID,
                            'user' => $submittedBy
                        ]);

                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to update status: No records were updated'
                        ], 500);
                    }

                    // Insert history record

                    DB::table('Cust_EntityHistory')->insert([
                        'ID' => DB::raw('NEWID()'),
                        'CustEntityID' => $quotation->ID,
                        'RequestNo' => (string)$bookingNo,
                        'Comment' => $reasonRemark ?: 'Status updated to Won',
                        'Type' => 'Quotation Status Update',
                        'PreviousValue' => $currentStatus,
                        'NewValue' => 'Won',
                        'CreatedBy' => $submittedBy,
                        'CreatedOn' => now(),
                        'LastModifiedBy' => $submittedBy,
                        'LastModifiedOn' => now(),
                        'IsActive' => 1,
                        'IsDeleted' => 0
                    ]);

                    DB::commit();

                    Log::info('Update Status - Won status set successfully', [
                        'booking_no' => $bookingNo,
                        'quotation_id' => $quotation->ID,
                        'previous_status' => $currentStatus,
                        'new_status' => 'Won',
                        'rows_updated' => $updatedRows,
                        'user' => $submittedBy,
                        'office_code' => $officeCode,
                        'company' => $quotation->companyname ?? 'N/A'
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Quotation marked as WON successfully'
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {

                    DB::rollBack();

                    Log::error('Update Status - Won query exception', [
                        'booking_no' => $bookingNo,
                        'quotation_id' => $quotation->ID,
                        'error_message' => $e->getMessage(),
                        'error_code' => $e->getCode(),
                        'sql' => $e->getSql() ?? 'N/A',
                        'bindings' => $e->getBindings() ?? [],
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'office_code' => $officeCode
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Database error while updating status to Won',
                        'error_details' => config('app.debug') ? $e->getMessage() : null
                    ], 500);
                } catch (\Exception $e) {

                    DB::rollBack();

                    Log::error('Update Status - Won general exception', [
                        'booking_no' => $bookingNo,
                        'quotation_id' => $quotation->ID,
                        'error_message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'office_code' => $officeCode
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to update status to Won: ' . $e->getMessage()
                    ], 500);
                }
            }

            // ============================================
            // HANDLE "LOST" AND "CANCEL" VIA STORED PROCEDURE
            // ============================================

            // Validate reason code is provided for Lost/Cancel
            if (empty($reasonCode)) {
                Log::warning('Update Status - Reason code missing for Lost/Cancel', [
                    'booking_no' => $bookingNo,
                    'target_status' => $targetStatus,
                    'user' => $submittedBy
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Reason code is required for ' . $targetStatus . ' status'
                ], 400);
            }

            // Handle status transition requirements
            $needsPendingFirst = false;

            if ($targetStatus === 'Lost' && $currentStatus !== 'Pending') {
                $needsPendingFirst = true;
                Log::info('Update Status - Lost requires Pending first', [
                    'booking_no' => $bookingNo,
                    'current_status' => $currentStatus,
                    'office_code' => $officeCode
                ]);
            } elseif ($targetStatus === 'Cancel' && !in_array($currentStatus, ['Pending', 'Won'])) {

                $needsPendingFirst = true;

                Log::info('Update Status - Cancel requires Pending/Won first', [
                    'booking_no' => $bookingNo,
                    'current_status' => $currentStatus,
                    'office_code' => $officeCode
                ]);
            }

            // Update to Pending first if needed

            if ($needsPendingFirst) {
                try {
                    $pendingRows = DB::table('Quote_QuotationInfo')
                        ->where('bookingno', $bookingNo)
                        ->update([
                            'QuoteStatus' => 'Pending',
                            'status' => 'Pending',
                            'LastModifiedBy' => $submittedBy,
                            'updatedon' => now()
                        ]);

                    Log::info('Update Status - Intermediate Pending update', [
                        'booking_no' => $bookingNo,
                        'previous_status' => $currentStatus,
                        'rows_updated' => $pendingRows,
                        'target_status' => $targetStatus,
                        'office_code' => $officeCode

                    ]);
                } catch (\Exception $e) {

                    Log::error('Update Status - Failed to set intermediate Pending status', [
                        'booking_no' => $bookingNo,
                        'error_message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'office_code' => $officeCode
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to prepare status transition: ' . $e->getMessage()
                    ], 500);
                }
            }

            // ============================================
            // EXECUTE STATUS UPDATE STORED PROCEDURE
            // ============================================

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

            Log::info('Update Status - Executing SP', [
                'booking_no' => $bookingNo,
                'target_status' => $targetStatus,
                'office_code' => $officeCode,
                'json' => $inputJson
            ]);

            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("
            SET NOCOUNT ON;
            DECLARE @OutputJson VARCHAR(2000);
            EXEC sp_Quote_Quotaiton_StatusUpdate ?, @OutputJson OUTPUT;
                SELECT @OutputJson AS OutputJson
            ");

            $executeSuccess = $stmt->execute([$inputJson]);
            if (!$executeSuccess) {
                $errorInfo = $stmt->errorInfo();

                // Rollback pending status if we changed it
                if ($needsPendingFirst) {
                    DB::table('Quote_QuotationInfo')
                        ->where('bookingno', $bookingNo)
                        ->update([

                            'QuoteStatus' => $currentStatus,
                            'status' => $currentStatus,
                            'LastModifiedBy' => $submittedBy,
                            'updatedon' => now()
                        ]);

                    Log::info('Update Status - Rolled back intermediate Pending status', [
                        'booking_no' => $bookingNo,
                        'restored_status' => $currentStatus
                    ]);
                }

                Log::error('Update Status - SP execution failed', [
                    'booking_no' => $bookingNo,
                    'target_status' => $targetStatus,
                    'error_code' => $errorInfo[0] ?? 'Unknown',
                    'sql_state' => $errorInfo[1] ?? 'Unknown',
                    'error_message' => $errorInfo[2] ?? 'Unknown',
                    'office_code' => $officeCode
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to execute status update procedure',
                    'error_details' => config('app.debug') ? $errorInfo[2] : null
                ], 500);
            }

            // ============================================
            // ENHANCED RESULT SET PROCESSING
            // ============================================

            $result = null;
            $resultSetCount = 0;

            do {
                $resultSetCount++;
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    Log::debug("Update Status - Result set #$resultSetCount", [
                        'booking_no' => $bookingNo,
                        'count' => count($rows),
                        'first_row' => !empty($rows) ? (array)$rows[0] : null
                    ]);

                    // ============================================
                    // ENHANCED ERROR DETECTION IN RESULT SET
                    // ============================================

                    if (!empty($rows) && is_object($rows[0])) {
                        $properties = get_object_vars($rows[0]);
                        $hasError = false;

                        foreach ($properties as $key => $value) {
                            if (
                                empty($key) ||
                                $key === '' ||
                                (is_string($value) && (
                                    strpos($value, 'Error Message') !== false ||
                                    strpos($value, 'Failed -') !== false
                                ))
                            ) {

                                Log::error('Update Status - SP returned error object in result set:', [
                                    'booking_no' => $bookingNo,
                                    'target_status' => $targetStatus,
                                    'result_set' => $resultSetCount,
                                    'error_property_name' => $key,
                                    'error_message' => $value,
                                    'all_properties' => $properties,
                                    'context' => [
                                        'office_code' => $officeCode,
                                        'current_status' => $currentStatus,
                                        'reason_code' => $reasonCode,
                                    ]
                                ]);
                                $hasError = true;
                                break;
                            }
                        }

                        if ($hasError) {
                            continue;
                        }

                        if (isset($rows[0]->OutputJson)) {
                            $result = $rows;
                            Log::info('Update Status - ✓ Found OutputJson in result set #' . $resultSetCount, [
                                'booking_no' => $bookingNo
                            ]);
                            break;
                        }
                    }
                } catch (\PDOException $e) {

                    Log::debug("Update Status - Result set #$resultSetCount error (skipping)", [
                        'booking_no' => $bookingNo,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            } while ($stmt->nextRowset());

            if (!$result) {
                Log::error('Update Status - SP did not return OutputJson', [
                    'booking_no' => $bookingNo,
                    'target_status' => $targetStatus,
                    'total_result_sets' => $resultSetCount,
                    'office_code' => $officeCode,
                    'probable_cause' => 'Stored procedure may have failed silently or returned unexpected format'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Stored procedure did not return expected output'
                ], 500);
            }

            // ============================================
            // PARSE OUTPUT WITH ENHANCED ERROR HANDLING
            // ============================================

            $outputJson = $result[0]->OutputJson ?? '{}';
            $output = json_decode($outputJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Update Status - Failed to decode SP output', [
                    'booking_no' => $bookingNo,
                    'target_status' => $targetStatus,
                    'json_error' => json_last_error_msg(),
                    'json_error_code' => json_last_error(),
                    'raw_output' => $outputJson,
                    'context' => [
                        'office_code' => $officeCode,
                        'current_status' => $currentStatus,
                    ]
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to parse stored procedure response: ' . json_last_error_msg(),
                    'error_details' => config('app.debug') ? $outputJson : null
                ], 500);
            }

            // ============================================
            // HANDLE SUCCESS
            // ============================================

            if (($output['Status'] ?? '') === 'Success') {
                Log::info('Update Status - Status updated successfully', [
                    'booking_no' => $bookingNo,
                    'quotation_id' => $quotation->ID,
                    'previous_status' => $currentStatus,
                    'new_status' => $targetStatus,
                    'reason_code' => $reasonCode,
                    'user' => $submittedBy,
                    'office_code' => $officeCode,
                    'company' => $quotation->companyname ?? 'N/A'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $output['Remarks'][0]['Remark'] ?? 'Status updated successfully'
                ]);
            }

            // ============================================
            // HANDLE FAILURE - ROLLBACK PENDING IF NEEDED
            // ============================================

            $errorMessage = $output['Remarks'][0]['Remark'] ?? 'Failed to update status';

            // If SP failed and we had changed to Pending, restore original status
            if ($needsPendingFirst) {
                try {
                    DB::table('Quote_QuotationInfo')
                        ->where('bookingno', $bookingNo)
                        ->update([
                            'QuoteStatus' => $currentStatus,
                            'status' => $currentStatus,
                            'LastModifiedBy' => $submittedBy,
                            'updatedon' => now()
                        ]);

                    Log::info('Update Status - Rolled back intermediate Pending after SP failure', [
                        'booking_no' => $bookingNo,
                        'restored_status' => $currentStatus,
                        'sp_error' => $errorMessage
                    ]);
                } catch (\Exception $e) {
                    Log::error('Update Status - Failed to rollback intermediate Pending status', [
                        'booking_no' => $bookingNo,
                        'error' => $e->getMessage(),
                        'original_status' => $currentStatus
                    ]);
                }
            }

            Log::warning('Update Status - SP returned failure', [
                'booking_no' => $bookingNo,
                'target_status' => $targetStatus,
                'current_status' => $currentStatus,
                'status' => $output['Status'] ?? 'Unknown',
                'message' => $errorMessage,
                'full_output' => $output,
                'context' => [
                    'office_code' => $officeCode,
                    'reason_code' => $reasonCode,
                    'user' => $submittedBy,
                    'company' => $quotation->companyname ?? 'N/A'
                ]
            ]);

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'details' => $output['Remarks'] ?? []
            ], 422);
        } catch (\PDOException $e) {

            Log::error('Update Status - PDO exception', [
                'booking_no' => $request->input('booking_no'),
                'target_status' => $request->input('status'),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'sql_state' => $e->errorInfo[0] ?? 'Unknown',
                'driver_error_code' => $e->errorInfo[1] ?? 'Unknown',
                'driver_error_message' => $e->errorInfo[2] ?? 'Unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Database connection error while updating status',
                'error_code' => 'PDO_ERROR',
                'details' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'sql_state' => $e->errorInfo[0] ?? null,
                ] : null

            ], 500);
        } catch (\Illuminate\Database\QueryException $e) {

            Log::error('Update Status - Query exception', [
                'booking_no' => $request->input('booking_no'),
                'target_status' => $request->input('status'),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'sql' => $e->getSql() ?? 'N/A',
                'bindings' => $e->getBindings() ?? [],
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Database query error while updating status',
                'error_code' => 'QUERY_ERROR',
                'details' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null

            ], 500);
        } catch (\Exception $e) {

            Log::error('Update Status - General exception', [
                'booking_no' => $request->input('booking_no'),
                'target_status' => $request->input('status'),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
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
        $module     = $request->input('module', 'Quotation');
        $reasonType = $request->input('reason_type');
        $filter     = $request->input('filter', '');

        if (!$reasonType) {
            return response()->json(['success' => false, 'message' => 'Reason type is required'], 400);
        }

        try {
            $pdo  = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC sp_ReasonMasterList ?, ?, ?");
            $stmt->execute([$module, $reasonType, $filter]);

            $reasons = [];
            do {
                try {
                    $results = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($results)) continue;
                    $firstRow   = $results[0];
                    $properties = array_keys((array) $firstRow);

                    // Skip SP status/error result sets — they return a single
                    // column whose value starts with 'Failed' or 'Error'

                    $firstVal = array_values((array) $firstRow)[0] ?? '';
                    if (is_string($firstVal) && (
                        str_starts_with($firstVal, 'Failed') ||
                        str_starts_with($firstVal, 'Error')
                    )) {

                        Log::warning('getReasons - SP returned failure', [
                            'reason_type' => $reasonType,
                            'message'     => $firstVal,
                        ]);
                        continue;
                    }

                    // Accept any result set that has at least a Code column
                    if (in_array('Code', $properties)) {
                        $reasons = $results;
                        Log::info('getReasons - Found reasons', [
                            'reason_type' => $reasonType,
                            'count'       => count($results),
                            'columns'     => $properties,
                        ]);

                        break;
                    }
                } catch (\Exception $e) {

                    continue;
                }
            } while ($stmt->nextRowset());

            return response()->json(['success' => true, 'reasons' => $reasons]);
        } catch (\Exception $e) {

            Log::error('getReasons failed', [

                'reason_type' => $reasonType,

                'error'       => $e->getMessage(),

            ]);

            return response()->json([

                'success' => false,

                'message' => 'Failed to load reasons: ' . $e->getMessage()

            ], 500);
        }
    }

    public function saveTermsCondition(Request $request)
    {
        $bookingNo    = $request->input('booking_no');
        $termsText    = $request->input('terms_condition');
        $templateName = $request->input('template_name');
        $submittedBy = Auth::check()
            ? (Auth::user()->displayName ?? Auth::user()->Email ?? 'system')
            : 'system';

        if (!$bookingNo) {
            return response()->json(['success' => false, 'message' => 'Booking number is required'], 400);
        }

        try {
            // ── Update quotation header ──
            $updated = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->update([
                    'QuotationTCTemplate' => $templateName,
                    'LastModifiedBy'      => $submittedBy,
                    'updatedon'           => now(),
                ]);

            if ($updated === 0) {
                return response()->json(['success' => false, 'message' => 'No matching quotation found'], 404);
            }

            // ── Get quotation UUID ──
            $quotation = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->first(['ID']);

            // ── Save T&C content as a file ──
            $folder   = 'quotations/' . $bookingNo . '/tc';
            $fileName = 'TC_' . $bookingNo . '_' . now()->format('Ymd_His') . '.html';
            $filePath = $folder . '/' . $fileName;
            \Storage::disk('public')->put($filePath, $termsText ?? '');

            // $fullPath = \Storage::disk('public')->path($filePath);
            $docName = $templateName ?? $fileName;
            // ── Upsert into Quote_Quotation_Document (PK is QuotationInfoID + DocumentType + Name) ──

            DB::statement("
            MERGE dbo.Quote_Quotation_Document AS target
            USING (
                SELECT :quotationId AS QuotationInfoID,
                    'TermsCondition' AS DocumentType,
                    :name AS Name
            ) AS src
            ON  target.QuotationInfoID = src.QuotationInfoID
            AND target.DocumentType    = src.DocumentType
            AND target.Name            = src.Name
            WHEN MATCHED THEN UPDATE SET
                FilePath        = :filePath,
                LastModifiedBy  = :modifiedBy,
                LastModifiedOn  = :modifiedOn,
                IsDeleted       = 0
            WHEN NOT MATCHED THEN INSERT
                (Id, QuotationInfoID, DocumentType, Name, IsVisible, IsPrinted, FilePath,
                CreatedBy, CreatedOn, LastModifiedBy, LastModifiedOn, IsDeleted)
            VALUES
                (NEWID(), :quotationId2, 'TermsCondition', :name2, 1, 0, :filePath2,
                :createdBy, :createdOn, :modifiedBy2, :modifiedOn2, 0);
            ", [
                'quotationId'  => $quotation->ID,
                'name'         => $docName,
                'filePath'     => $filePath,
                'modifiedBy'   => $submittedBy,
                'modifiedOn'   => now(),
                'quotationId2' => $quotation->ID,
                'name2'        => $docName,
                'filePath2'    => $filePath,
                'createdBy'    => $submittedBy,
                'createdOn'    => now(),
                'modifiedBy2'  => $submittedBy,
                'modifiedOn2'  => now(),
            ]);

            $nextVersion = DB::table('Cust_DocumentDeails')
                ->where('ReferenceID', $quotation->ID)
                ->where('DocumentType', 'TermsCondition')
                ->max('VersionNo') ?? 0;

            DB::table('Cust_DocumentDeails')->insert([
                'Id'            => DB::raw('NEWID()'),
                'DocumentType'  => 'TermsCondition',
                'ReferenceID'   => $quotation->ID,
                'ReferenceNo'   => (string) $bookingNo,
                'SubmittedDate' => now(),
                'VersionNo'     => $nextVersion + 1,
                'FileName'      => $fileName,
                'Path'          => $filePath,   // relative path, not $fullPath
                'CreatedOn'     => now(),
            ]);

            Log::info('saveTermsCondition - Success', [
                'booking_no' => $bookingNo,
                'template'   => $templateName,
                'file'       => $filePath,
                'user'       => $submittedBy,
            ]);

            return response()->json(['success' => true, 'message' => 'Terms & Conditions saved successfully']);
        } catch (\Exception $e) {

            Log::error('saveTermsCondition - Failed', [
                'booking_no' => $bookingNo,
                'error'      => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to save: ' . $e->getMessage()], 500);
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
        $module     = $request->input('module', 'Quotation');
        $subModule  = $request->input('sub_module');
        $officeCode = trim($request->input('office_code', ''));
        $bookingNo  = $request->input('booking_no');

        if (!$subModule) {
            return response()->json([
                'success' => false,
                'message' => 'Sub module and office code are required'
            ], 400);
        }

        // ── T&C: route through sp_Quote_CommunicationDetail_List ─────────────────
        // The SP resolves Service, Process, and OfficeCode internally from QuoteNo,
        // so it returns templates correctly filtered for this specific quotation.
        // Email still uses the direct query below (office_code path).
        if ($subModule === 'T&C' && $bookingNo) {
            return $this->getCommunicationTemplatesViaSP($module, $subModule, $bookingNo);
        }

        // ── Email (and any other sub_module): original direct query ──────────────
        if (!$officeCode) {
            return response()->json([
                'success' => false,
                'message' => 'Sub module and office code are required'
            ], 400);
        }

        try {
            $officeExists = DB::table('Cust_Office')
                ->whereRaw("TRIM(Code) = ?", [$officeCode])
                ->where('IsActive', 1)
                ->where('IsDeleted', 0)
                ->exists();

            if (!$officeExists) {
                Log::warning('getCommunicationTemplates - Office not found', [
                    'office_code' => $officeCode,
                    'module'      => $module,
                    'sub_module'  => $subModule,
                ]);

                return response()->json([
                    'success' => false,
                    'code'    => 'OFFICE_NOT_FOUND',
                    'message' => "Office '{$officeCode}' is not configured in the system."
                ], 404);
            }

            $templates = DB::select(
                "SELECT Name, Description
             FROM Cust_CommunicationDetails WITH (NOLOCK)
             WHERE Module     = ?
               AND SubModule  = ?
               AND TRIM(OfficeCode) = ?
               AND IsActive   = 1
             ORDER BY Name",
                [$module, $subModule, $officeCode]
            );

            Log::info('getCommunicationTemplates', [
                'office_code'    => $officeCode,
                'module'         => $module,
                'sub_module'     => $subModule,
                'template_count' => count($templates),
            ]);

            if (empty($templates)) {
                return response()->json([
                    'success' => false,
                    'code'    => 'NOT_CONFIGURED',
                    'message' => "No '{$subModule}' templates are configured for office '{$officeCode}'. Please contact your administrator."
                ], 404);
            }

            return response()->json([
                'success'   => true,
                'templates' => $templates
            ]);
        } catch (\Exception $e) {
            Log::error('getCommunicationTemplates failed', [
                'office_code' => $officeCode,
                'error'       => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load templates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Load T&C templates via sp_Quote_CommunicationDetail_List.
     * The SP filters by Service+Process+OfficeCode resolved from the QuoteNo internally,
     * so it returns templates appropriate for this specific quotation type and office.
     */
    private function getCommunicationTemplatesViaSP(string $module, string $subModule, string $bookingNo): \Illuminate\Http\JsonResponse
    {
        try {
            $pdo  = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_Quote_CommunicationDetail_List ?, ?, ?");
            $stmt->execute([$bookingNo, $module, $subModule]);

            $templates      = [];
            $resultSetIndex = 0;

            do {
                $resultSetIndex++;
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    if (empty($rows)) {
                        continue;
                    }

                    $firstVal = array_values((array) $rows[0])[0] ?? '';

                    // SP returns 'Failed - ...' on guard failures
                    if (is_string($firstVal) && str_starts_with($firstVal, 'Failed')) {
                        Log::warning('getCommunicationTemplatesViaSP - SP guard failed', [
                            'booking_no' => $bookingNo,
                            'sub_module' => $subModule,
                            'message'    => $firstVal,
                        ]);

                        $code = str_contains($firstVal, 'Office') ? 'OFFICE_NOT_FOUND' : 'NOT_CONFIGURED';

                        return response()->json([
                            'success' => false,
                            'code'    => $code,
                            'message' => $firstVal,
                        ], 404);
                    }

                    // Valid result set — SP returns TemplateID and TemplateName
                    if (isset($rows[0]->TemplateID) && isset($rows[0]->TemplateName)) {
                        // Normalize to Name/Description shape the frontend expects
                        $templates = array_map(fn($row) => (object)[
                            'Name'        => $row->TemplateName,
                            'Description' => '',
                        ], $rows);

                        Log::info('getCommunicationTemplatesViaSP - Templates found', [
                            'booking_no'     => $bookingNo,
                            'sub_module'     => $subModule,
                            'template_count' => count($templates),
                        ]);
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            if (empty($templates)) {
                Log::warning('getCommunicationTemplatesViaSP - No templates returned', [
                    'booking_no' => $bookingNo,
                    'sub_module' => $subModule,
                ]);

                return response()->json([
                    'success' => false,
                    'code'    => 'NOT_CONFIGURED',
                    'message' => "No T&C templates configured for this quotation's office and service type. Please contact your administrator.",
                ], 404);
            }

            return response()->json([
                'success'   => true,
                'templates' => $templates,
            ]);
        } catch (\Exception $e) {
            Log::error('getCommunicationTemplatesViaSP failed', [
                'booking_no' => $bookingNo,
                'sub_module' => $subModule,
                'error'      => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load T&C templates: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getCommunicationDescription(Request $request)
    {
        $module     = $request->input('module', 'Quotation');
        $subModule  = $request->input('sub_module');
        $officeCode = trim($request->input('office_code', ''));
        $name       = $request->input('name');

        if (!$subModule || !$officeCode || !$name) {
            return response()->json([
                'success' => false,
                'message' => 'All parameters are required'
            ], 400);
        }

        try {
            $result = DB::select('
            DECLARE @Status      VARCHAR(15);
            DECLARE @Remark      VARCHAR(1000);
            DECLARE @Description NVARCHAR(MAX);
            EXEC sp_CommunicationDetails_GetDescription ?, ?, ?, ?,
                @Status      = @Status      OUTPUT,
                @Remark      = @Remark      OUTPUT,
                @Description = @Description OUTPUT;
            SELECT @Status AS Status, @Remark AS Remark, @Description AS Description

        ', [$module, $subModule, $officeCode, $name]);
            if (empty($result)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No response from stored procedure'
                ], 500);
            }

            if ($result[0]->Status === 'Success') {
                return response()->json([
                    'success'     => true,
                    'description' => $result[0]->Description
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result[0]->Remark ?? 'Failed to load description'
            ], 422);
        } catch (\Exception $e) {

            Log::error('getCommunicationDescription failed', [
                'office_code' => $officeCode,
                'name'        => $name,
                'error'       => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load description: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendEmailQuote(Request $request)
    {
        $request->validate([
            'booking_no'  => 'required|string',
            'quote_id'    => 'required|string',
            'template_id' => 'required|string',
            'to_email'    => 'required|email',
            'from_email'  => 'required|email',
            'subject'     => 'required|string',
            'body'        => 'required|string',
        ]);

        $bookingNo  = $request->booking_no;
        $toEmail    = $request->to_email;
        $fromEmail  = $request->from_email;
        $subject    = $request->subject;
        $body       = $request->body;
        $templateId = $request->template_id;

        // Fetch quotation so $quotation->ID is available
        $quotation = DB::table('Quote_QuotationInfo')
            ->where('ID', $request->quote_id)
            ->first(['ID', 'bookingno']);

        if (!$quotation) {
            return response()->json([
                'success' => false,
                'message' => 'Quotation not found',
            ], 404);
        }

        try {
            // ============================================
            // SEND EMAIL
            // ============================================
            \Illuminate\Support\Facades\Mail::send(
                [],
                [],
                function (\Illuminate\Mail\Message $message) use ($toEmail, $fromEmail, $subject, $body, $quotation) {
                    $message
                        ->to([$toEmail, 'amay@freight-in-time.com'])
                        ->cc('nashonmwendwa04@gmail.com')
                        ->replyTo($fromEmail)
                        ->subject($subject)
                        ->setBody($body, 'text/html');

                    // ============================================
                    // ATTACH PDF — prefer saved file, fall back to
                    // in-memory generation from SP if none exists.
                    // ============================================
                    $attached = false;

                    // Path 1: latest saved PDF on disk (existing behaviour)
                    $latest = DB::table('Quote_Quotation_Document')
                        ->where('QuotationInfoID', $quotation->ID)
                        ->where('DocumentType', 'QuotationPDF')
                        ->where('IsDeleted', 0)
                        ->orderByDesc('CreatedOn')
                        ->first(['Name', 'FilePath']);

                    if ($latest && $latest->FilePath) {
                        $relativePath = $this->normalizeFilePath($latest->FilePath);
                        $fullPath     = \Storage::disk('public')->path($relativePath);

                        if (file_exists($fullPath)) {
                            $attachName = $latest->Name ?: "Quotation_{$quotation->bookingno}.pdf";
                            $message->attach($fullPath, ['as' => $attachName, 'mime' => 'application/pdf']);
                            $attached = true;

                            Log::info('sendEmailQuote - Attached saved PDF', [
                                'booking_no' => $quotation->bookingno,
                                'file'       => $attachName,
                            ]);
                        }
                    }

                    // Path 2: no saved PDF found — generate in-memory from SP and attach directly.
                    // This avoids writing a new version to disk just to send an email.
                    if (!$attached) {
                        Log::warning('sendEmailQuote - No saved PDF found, generating in-memory from SP', [
                            'booking_no' => $quotation->bookingno,
                            'quote_no'   => $quotation->QuoteNo,
                        ]);

                        $html = $this->getQuotationHtml($quotation->QuoteNo ?? '');

                        if (!empty($html)) {
                            try {
                                $options = new \Dompdf\Options();
                                $options->set('isHtml5ParserEnabled', true);
                                $options->set('isPhpEnabled', false);
                                $options->set('isRemoteEnabled', true);
                                $options->set('defaultFont', 'Arial');

                                $pdf = new \Dompdf\Dompdf($options);
                                $pdf->loadHtml($html, 'UTF-8');
                                $pdf->setPaper('A4', 'portrait');
                                $pdf->render();

                                // Attach from memory — no temp file needed
                                $attachName = "Quotation_{$quotation->bookingno}.pdf";
                                $message->attachData($pdf->output(), $attachName, ['mime' => 'application/pdf']);

                                Log::info('sendEmailQuote - Attached in-memory PDF from SP', [
                                    'booking_no' => $quotation->bookingno,
                                ]);
                            } catch (\Exception $e) {
                                Log::error('sendEmailQuote - In-memory PDF generation failed', [
                                    'booking_no' => $quotation->bookingno,
                                    'error'      => $e->getMessage(),
                                ]);
                                // Email still sends — just without attachment
                            }
                        } else {
                            Log::error('sendEmailQuote - SP returned no HTML, email sent without PDF', [
                                'booking_no' => $quotation->bookingno,
                                'quote_no'   => $quotation->QuoteNo ?? 'N/A',
                            ]);
                        }
                    }
                }
            );

            // ============================================
            // LOG VIA SP
            // ============================================
            $clientName = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->value('companyname') ?? '';

            $jsonData = json_encode([
                'SchedulerType' => 'QuotationEmail',
                'SubmittedOn'   => now()->format('Y-m-d H:i:s'),
                'ClientName'    => $clientName,
                'EmailFrom'     => $fromEmail,
                'EmailTo'       => $toEmail,
                'Subject'       => $subject,
                'BodyMessage'   => $body,
                'Status'        => 'Sent',
                'SendRemark'    => 'Sent via Quotation Module' . ($request->input('quote_version') ? ' | ' . $request->input('quote_version') : ''),
            ]);

            $pdo      = DB::getPdo();
            $stmt     = $pdo->prepare('EXEC sp_EmailSchedulerDetailsUpdate ?, ?, ?');
            $spStatus = null;
            $spRemark = null;
            $stmt->bindParam(1, $jsonData);
            $stmt->bindParam(2, $spStatus, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 20);
            $stmt->bindParam(3, $spRemark, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 2000);
            $stmt->execute();

            if ($spStatus === 'Failed') {
                Log::error('sendEmailQuote - SP log failed', [
                    'booking_no' => $bookingNo,
                    'remark'     => $spRemark,
                ]);
            }

            // ============================================
            // SAVE TEMPLATE USED AGAINST QUOTATION
            // ============================================
            DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->update(['QuotationEmailTemplate' => $templateId]);

            Log::info('sendEmailQuote - Success', [
                'booking_no' => $bookingNo,
                'to'         => $toEmail,
                'subject'    => $subject,
                'template'   => $templateId,
                'sp_status'  => $spStatus,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully',
            ]);
        } catch (\Symfony\Component\Mailer\Exception\TransportException $e) {
            Log::error('sendEmailQuote - SMTP error', [
                'booking_no' => $bookingNo,
                'error'      => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Mail server error: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::error('sendEmailQuote - Failed', [
                'booking_no' => $bookingNo,
                'error'      => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate and stream a PDF quotation document.
     * Route: GET /quotation/{id}/generate-pdf
     */
    public function generatePdf($id)
    {
        try {
            ini_set('memory_limit', '512M');
            ini_set('max_execution_time', '300');

            Log::info('PDF Generation - Starting', ['quotation_id' => $id]);

            // ============================================
            // LOAD QUOTATION
            // ============================================
            $quotation = \App\Models\QuotationInfo::where('ID', $id)->first();

            if (!$quotation) {
                Log::error('PDF Generation - Quotation not found', ['id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found',
                ], 404);
            }

            $submittedBy = auth()->user()->displayName ?? auth()->user()->Email ?? 'system';

            // ============================================
            // GUARD: QuoteNo must be set
            // sp_Quote_QuotationFormat uses an INNER JOIN on QuotationTCTemplate,
            // so it will silently return empty HTML if T&C has not been applied.
            // The UI already enforces this — this guard is a backend safety net.
            // ============================================
            $quoteNo = trim((string) ($quotation->QuoteNo ?? ''));

            if (empty($quoteNo)) {
                Log::error('PDF Generation - QuoteNo is empty', ['id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'QuoteNo is not set on this quotation. Cannot generate PDF.',
                ], 422);
            }

            // ============================================
            // CALL sp_Quote_QuotationFormat
            //
            // SP signature:
            //   @QuotationNo VARCHAR(25) INPUT
            //   @QuoteFormat VARCHAR(MAX) OUTPUT
            //
            // On internal SQL error the SP CATCH block sets:
            //   @QuoteFormat = 'Failed (Reason) - <message>'
            // instead of raising — we detect and surface this explicitly.
            //
            // DECLARE uses VARCHAR(MAX) to match the SP OUTPUT param type.
            // Using VARCHAR(N) would silently truncate large HTML documents.
            // ============================================
            $html    = null;
            $spError = null;

            try {
                $pdo  = DB::getPdo();
                $stmt = $pdo->prepare("
                DECLARE @QuoteFormat VARCHAR(MAX);
                EXEC dbo.sp_Quote_QuotationFormat ?,
                    @QuoteFormat = @QuoteFormat OUTPUT;
                SELECT @QuoteFormat AS QuoteFormat;
            ");

                $stmt->execute([$quoteNo]);

                // Walk all result sets — the SP creates/drops #VATDetails
                // which can produce empty intermediate result sets before the SELECT.
                $setIndex = 0;
                do {
                    $setIndex++;
                    try {
                        $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

                        if (empty($rows)) {
                            continue;
                        }

                        if (isset($rows[0]->QuoteFormat)) {
                            $raw = (string) $rows[0]->QuoteFormat;

                            // SP CATCH block returns error as a string — detect it
                            if (str_starts_with(trim($raw), 'Failed (Reason)')) {
                                $spError = $raw;
                            } else {
                                $html = $raw;
                            }
                            break;
                        }
                    } catch (\PDOException $e) {
                        // Normal for SPs that use temp tables — skip and continue
                        Log::debug('PDF Generation - Skipping intermediate result set #' . $setIndex, [
                            'quote_no' => $quoteNo,
                            'error'    => $e->getMessage(),
                        ]);
                        continue;
                    }
                } while ($stmt->nextRowset());
            } catch (\Exception $e) {
                Log::error('PDF Generation - SP execution threw exception', [
                    'quote_no' => $quoteNo,
                    'error'    => $e->getMessage(),
                    'file'     => $e->getFile(),
                    'line'     => $e->getLine(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to execute quotation format SP: ' . $e->getMessage(),
                ], 500);
            }

            // Surface the SP's own diagnostic message — far more useful than a generic error
            if (!empty($spError)) {
                Log::error('PDF Generation - SP returned failure string', [
                    'quote_no' => $quoteNo,
                    'sp_error' => $spError,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation format SP failed: ' . $spError,
                ], 500);
            }

            // Empty HTML most commonly means T&C template is not applied (INNER JOIN returns no rows)
            if (empty($html) || trim($html) === '') {
                Log::error('PDF Generation - SP returned empty HTML', [
                    'quote_no'               => $quoteNo,
                    'tc_template_set'        => !empty($quotation->QuotationTCTemplate),
                    'tc_template_value'      => $quotation->QuotationTCTemplate ?? 'NULL',
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No quotation HTML returned from SP. '
                        . 'Ensure a Terms & Conditions template is applied before generating.',
                ], 422);
            }

            Log::info('PDF Generation - HTML retrieved from SP', [
                'quote_no'    => $quoteNo,
                'html_length' => strlen($html),
                'tc_template' => $quotation->QuotationTCTemplate ?? 'N/A',
            ]);

            // ============================================
            // RENDER PDF WITH DOMPDF
            // ============================================
            try {
                $options = new \Dompdf\Options();
                $options->set('isHtml5ParserEnabled', true);
                $options->set('isPhpEnabled', false);
                $options->set('isRemoteEnabled', true);
                $options->set('defaultFont', 'Arial');
                $options->set('chroot', public_path());

                $pdf = new \Dompdf\Dompdf($options);
                $pdf->loadHtml($html, 'UTF-8');
                $pdf->setPaper('A4', 'portrait');
                $pdf->render();

                Log::info('PDF Generation - DomPDF rendered successfully', [
                    'quote_no' => $quoteNo,
                ]);
            } catch (\Exception $e) {
                Log::error('PDF Generation - DomPDF render failed', [
                    'quote_no' => $quoteNo,
                    'error'    => $e->getMessage(),
                    'file'     => $e->getFile(),
                    'line'     => $e->getLine(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'PDF render failed: ' . $e->getMessage(),
                ], 500);
            }

            // ============================================
            // DETERMINE VERSION NUMBER
            // Count only QuotationPDF documents — avoids T&C
            // subfolder files inflating the version number.
            // ============================================
            $folder = 'quotations/' . $quotation->bookingno;

            try {
                \Storage::disk('public')->makeDirectory($folder);
            } catch (\Exception $e) {
                Log::warning('PDF Generation - Could not ensure directory exists', [
                    'folder' => $folder,
                    'error'  => $e->getMessage(),
                ]);
            }

            $existingVersions = DB::table('Quote_Quotation_Document')
                ->where('QuotationInfoID', $quotation->ID)
                ->where('DocumentType', 'QuotationPDF')
                ->where('IsDeleted', 0)
                ->count();

            $nextVersion = $existingVersions + 1;
            $fileName    = 'Quotation_' . $quotation->bookingno
                . '_V' . $nextVersion
                . '_' . now()->format('Ymd_His')
                . '.pdf';
            $storagePath = $folder . '/' . $fileName;
            $fullPath    = \Storage::disk('public')->path($storagePath);

            Log::info('PDF Generation - Paths resolved', [
                'storage_path'      => $storagePath,
                'version'           => $nextVersion,
                'existing_versions' => $existingVersions,
            ]);

            // ============================================
            // WRITE PDF TO DISK
            // ============================================
            try {
                $dir = dirname($fullPath);
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                    Log::info('PDF Generation - Created directory', ['dir' => $dir]);
                }

                file_put_contents($fullPath, $pdf->output());

                if (!file_exists($fullPath)) {
                    throw new \Exception('File was not found on disk after write: ' . $fullPath);
                }

                Log::info('PDF Generation - File written to disk', [
                    'path' => $fullPath,
                    'size' => filesize($fullPath),
                ]);
            } catch (\Exception $e) {
                Log::error('PDF Generation - File write failed', [
                    'path'  => $fullPath,
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save PDF to disk: ' . $e->getMessage(),
                ], 500);
            }

            // ============================================
            // RECORD IN DATABASE
            // Non-fatal — PDF is already on disk; a failed
            // insert should not block the user response.
            // ============================================
            try {
                DB::table('Quote_Quotation_Document')->insert([
                    'Id'              => DB::raw('NEWID()'),
                    'QuotationInfoID' => $quotation->ID,
                    'DocumentType'    => 'QuotationPDF',
                    'Name'            => $fileName,
                    'IsVisible'       => 1,
                    'IsPrinted'       => 1,
                    'PrintedOn'       => now(),
                    'FilePath'        => $storagePath,
                    'CreatedBy'       => $submittedBy,
                    'CreatedOn'       => now(),
                    'LastModifiedBy'  => $submittedBy,
                    'LastModifiedOn'  => now(),
                    'IsDeleted'       => 0,
                ]);

                Log::info('PDF Generation - Document record inserted', [
                    'quote_no' => $quoteNo,
                    'version'  => $nextVersion,
                    'file'     => $fileName,
                ]);
            } catch (\Exception $e) {
                // Non-fatal: log but do not return an error response.
                // The file is on disk and can be served; the DB record can be recreated.
                Log::error('PDF Generation - DB insert failed (non-fatal, PDF is on disk)', [
                    'quote_no' => $quoteNo,
                    'error'    => $e->getMessage(),
                    'file'     => $e->getFile(),
                    'line'     => $e->getLine(),
                ]);
            }

            Log::info('PDF Generation - Completed successfully', [
                'booking_no'  => $quotation->bookingno,
                'quote_no'    => $quoteNo,
                'version'     => $nextVersion,
                'path'        => $storagePath,
                'tc_template' => $quotation->QuotationTCTemplate ?? 'N/A',
            ]);

            return response()->json([
                'success' => true,
                'version' => $nextVersion,
                'label'   => 'Version ' . $nextVersion,
                'url'     => route('quotation.download-pdf', [$id, $nextVersion]),
            ]);
        } catch (\Exception $e) {
            Log::error('PDF Generation - Unexpected top-level exception', [
                'id'    => $id,
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'PDF generation failed unexpectedly: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Execute sp_Quote_QuotationFormat and return the HTML string.
     *
     * The SP signature:
     *   @QuotationNo VARCHAR(25) INPUT
     *   @QuoteFormat VARCHAR(MAX) OUTPUT
     *
     * On internal error the SP sets @QuoteFormat = 'Failed (Reason) - <message>'
     * instead of raising an exception, so we must detect that string explicitly.
     *
     * @param  string $quoteNo  The QuoteNo value (e.g. '8270'), NOT the UUID
     * @return string|null      Complete HTML string, or null on failure
     */
    private function getQuotationHtml(string $quoteNo): ?string
    {
        if (empty(trim($quoteNo))) {
            Log::error('getQuotationHtml - quoteNo is empty');
            return null;
        }

        try {
            $pdo = DB::getPdo();

            // IMPORTANT: DECLARE uses VARCHAR(MAX) to match the SP OUTPUT param type.
            // Using VARCHAR(N) here would silently truncate large HTML documents.
            $sql = "
            DECLARE @QuoteFormat VARCHAR(MAX);
            EXEC dbo.sp_Quote_QuotationFormat ?,
                @QuoteFormat = @QuoteFormat OUTPUT;
            SELECT @QuoteFormat AS QuoteFormat;
        ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([trim($quoteNo)]);

            // Walk result sets — the SP creates/drops a temp table (#VATDetails)
            // which can produce intermediate empty result sets before the SELECT.
            $html        = null;
            $setIndex    = 0;

            do {
                $setIndex++;
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    if (empty($rows)) {
                        continue;
                    }

                    if (isset($rows[0]->QuoteFormat)) {
                        $html = $rows[0]->QuoteFormat;
                        Log::info('getQuotationHtml - Found QuoteFormat in result set #' . $setIndex, [
                            'quote_no' => $quoteNo,
                            'length'   => strlen($html ?? ''),
                        ]);
                        break;
                    }
                } catch (\PDOException $e) {
                    // Skip unfetchable intermediate result sets (normal for temp table SPs)
                    Log::debug('getQuotationHtml - Skipping result set #' . $setIndex, [
                        'quote_no' => $quoteNo,
                        'error'    => $e->getMessage(),
                    ]);
                    continue;
                }
            } while ($stmt->nextRowset());

            // SP CATCH block sets @QuoteFormat = 'Failed (Reason) - <message>'
            // instead of raising — detect this and treat as a failure.
            if (!empty($html) && str_starts_with(trim($html), 'Failed (Reason)')) {
                Log::error('getQuotationHtml - SP returned failure message', [
                    'quote_no' => $quoteNo,
                    'message'  => $html,
                ]);
                return null;
            }

            if (empty($html) || trim($html) === '') {
                Log::warning('getQuotationHtml - SP returned empty HTML', [
                    'quote_no'         => $quoteNo,
                    'result_sets_seen' => $setIndex,
                ]);
                return null;
            }

            return $html;
        } catch (\Exception $e) {
            Log::error('getQuotationHtml - Exception', [
                'quote_no' => $quoteNo,
                'error'    => $e->getMessage(),
                'file'     => $e->getFile(),
                'line'     => $e->getLine(),
            ]);
            return null;
        }
    }

    public function getQuotationVersions($id)
    {
        try {
            $quotation = \App\Models\QuotationInfo::where('ID', $id)->first();

            if (!$quotation) {
                return response()->json(['success' => false, 'message' => 'Quotation not found'], 404);
            }

            $documents = DB::table('Quote_Quotation_Document')
                ->where('QuotationInfoID', $quotation->ID)
                ->where('DocumentType', 'QuotationPDF')
                ->where('IsDeleted', 0)
                ->orderBy('CreatedOn', 'asc')
                ->get(['Id', 'Name', 'FilePath', 'CreatedOn']);

            $versions = [];

            foreach ($documents as $index => $doc) {
                // Version number = position in DB (1-based), not parsed from filename.
                // This is the authoritative source used for email attachment selection.
                $versionNo = $index + 1;

                $relativePath = $this->normalizeFilePath($doc->FilePath);
                $fileExists   = \Storage::disk('public')->exists($relativePath);

                Log::info('getQuotationVersions - Version entry', [
                    'quotation_id' => $id,
                    'version_no'   => $versionNo,
                    'file_name'    => $doc->Name,
                    'file_path'    => $relativePath,
                    'file_exists'  => $fileExists,
                ]);

                // Skip versions whose file no longer exists on disk
                if (!$fileExists) {
                    Log::warning('getQuotationVersions - File missing on disk, skipping', [
                        'quotation_id' => $id,
                        'version_no'   => $versionNo,
                        'file_name'    => $doc->Name,
                        'raw_path'     => $doc->FilePath,
                        'relative'     => $relativePath,
                    ]);
                    continue;
                }

                $versions[] = [
                    'version'    => $versionNo,
                    'label'      => 'Version ' . $versionNo,
                    'url'        => route('quotation.download-pdf', [$id, $versionNo]),
                    'created_on' => $doc->CreatedOn,
                    'file_name'  => $doc->Name,
                    'doc_id'     => $doc->Id,
                ];
            }

            Log::info('getQuotationVersions - Completed', [
                'quotation_id'   => $id,
                'total_db_docs'  => $documents->count(),
                'versions_found' => count($versions),
            ]);

            return response()->json([
                'success'  => true,
                'versions' => $versions,
            ]);
        } catch (\Exception $e) {
            Log::error('getQuotationVersions - Failed', [
                'id'    => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'message' => 'Failed to load versions'], 500);
        }
    }

    public function getEmailHistory(Request $request)
    {
        $bookingNo = $request->input('booking_no');

        if (!$bookingNo) {
            return response()->json(['success' => false, 'message' => 'Booking number is required'], 400);
        }

        try {
            // Fetch quotation to get companyname for matching
            $quotation = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->first(['companyname']);
            if (!$quotation) {
                return response()->json(['success' => true, 'emails' => []]);
            }

            $emails = DB::table('email_emailscheduler')
                ->where('ClientName', $quotation->companyname)
                ->where('SchedulerType', 'QuotationEmail')
                ->orderBy('SubmittedOn', 'desc')
                ->get(['SubmittedOn as sent_on', 'EmailTo as to_email', 'Status as status', 'SendRemark as send_remark']);

            // Extract version from SendRemark — stored as "Sent via Quotation Module | Version N"
            $emails = $emails->map(function ($email) {
                $version = null;
                if (!empty($email->send_remark) && str_contains($email->send_remark, '|')) {
                    $version = trim(explode('|', $email->send_remark, 2)[1]);
                }
                $email->quote_version = $version ?: 'N/A';
                unset($email->send_remark);
                return $email;
            });

            return response()->json(['success' => true, 'emails' => $emails]);
        } catch (\Exception $e) {

            Log::error('getEmailHistory - Failed', [
                'booking_no' => $bookingNo,
                'error'      => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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

    public function getExchangeRate(Request $request)
    {
        $officeCode   = trim($request->office_code);
        $fromCurrency = trim($request->from_currency);
        $toCurrency   = trim($request->to_currency ?? 'KES');

        // Accept any date format from frontend, normalize to '05/Mar/2026'
        // AFTER
        try {
            $dateFor = $request->date_for
                ? \Carbon\Carbon::parse($request->date_for)->format('d/M/Y')
                : now()->format('d/M/Y');
        } catch (\Exception $e) {
            Log::warning('getExchangeRate - Invalid date_for value, falling back to today', [
                'date_for_raw' => $request->date_for,
                'error'        => $e->getMessage(),
            ]);
            $dateFor = now()->format('d/M/Y');
        }

        if (!$officeCode || !$fromCurrency) {
            return response()->json([
                'success' => false,
                'message' => 'Office code and from currency are required'
            ], 400);
        }

        if ($fromCurrency === $toCurrency) {
            return response()->json([
                'success'       => true,
                'exchange_rate' => 1.000000
            ]);
        }

        try {
            $result = DB::selectOne("
            SELECT dbo.fn_GetCurrentExRateUsingCodeandPeriod(?, ?, ?, ?) AS ExchangeRate
        ", [$officeCode, $fromCurrency, $toCurrency, $dateFor]);

            $rate = $result ? (float) $result->ExchangeRate : null;

            Log::info('getExchangeRate - Result', [
                'office_code'   => $officeCode,
                'from_currency' => $fromCurrency,
                'to_currency'   => $toCurrency,
                'date_for'      => $dateFor,
                'rate'          => $rate,
            ]);

            if (!$rate || $rate <= 0) {
                Log::warning('getExchangeRate - No rate found', [
                    'office_code'   => $officeCode,
                    'from_currency' => $fromCurrency,
                    'to_currency'   => $toCurrency,
                    'date_for'      => $dateFor,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => "No exchange rate found for {$fromCurrency} → {$toCurrency} in office {$officeCode} on {$dateFor}",
                ], 404);
            }

            return response()->json([
                'success'       => true,
                'exchange_rate' => $rate,
            ]);
        } catch (\Exception $e) {
            Log::error('getExchangeRate - Failed', [
                'office_code'   => $officeCode,
                'from_currency' => $fromCurrency,
                'to_currency'   => $toCurrency,
                'date_for'      => $dateFor,
                'error'         => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exchange rate: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getContractCharges(Request $request)
    {
        $bookingNo = $request->input('booking_no');

        if (!$bookingNo) {
            return response()->json(['success' => false, 'message' => 'Booking number is required'], 400);
        }

        try {
            $quotation = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->first([
                    'clientid',
                    'ServiceVerticalname',
                    'process',
                    'origincountry',
                    'destinationcountry',
                    'incoterm',
                    'shipmentmode',
                    'TransportMode',
                    'ServiceClass',
                    'commodity',
                    'containerlist',
                    'scopevolume',
                    'cargovolume',
                ]);

            if (!$quotation) {
                return response()->json(['success' => false, 'message' => 'Quotation not found'], 404);
            }

            // Parse containerlist — try JSON first, otherwise pass empty array
            $equipment = [];

            if (!empty($quotation->containerlist)) {
                $decoded = json_decode($quotation->containerlist, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $equipment = $decoded;
                }
            }

            $inputJson = json_encode([
                'Service'   => $quotation->ServiceVerticalname ?? '',
                'Process'   => $quotation->process ?? '',
                'Client'    => $quotation->clientid ?? '',
                'Weight'    => (float)($quotation->scopevolume ?? 0),
                'Volume'    => (float)($quotation->cargovolume ?? 0),
                'Filter'    => [
                    ['Code' => 'Inco Terms',             'Value' => $quotation->incoterm ?? ''],
                    ['Code' => 'Country Of Origin',      'Value' => $quotation->origincountry ?? ''],
                    ['Code' => 'Country Of Destination', 'Value' => $quotation->destinationcountry ?? ''],
                    ['Code' => 'Place Of Reciept',       'Value' => ''],
                    ['Code' => 'Place Of Delivery',      'Value' => ''],
                    ['Code' => 'Port Of Loading',        'Value' => ''],
                    ['Code' => 'Port Of Discharge',      'Value' => ''],
                    ['Code' => 'Service Class',          'Value' => $quotation->ServiceClass ?? ''],
                    ['Code' => 'Commodity',              'Value' => $quotation->commodity ?? ''],
                    ['Code' => 'Shipment Mode',          'Value' => $quotation->shipmentmode ?? ''],
                    ['Code' => 'Shipment Type',          'Value' => ''],
                    ['Code' => 'Movement Type',          'Value' => $quotation->TransportMode ?? ''],
                    ['Code' => 'Forwarder',              'Value' => ''],
                    ['Code' => 'Shipping Line',          'Value' => ''],
                    ['Code' => 'Air Line',               'Value' => ''],
                    ['Code' => 'Trucker',                'Value' => ''],
                ],

                'Equipment' => $equipment,
            ]);

            Log::info('getContractCharges - Input JSON', [
                'booking_no' => $bookingNo,
                'input_json' => $inputJson,
            ]);

            $pdo  = DB::connection()->getPdo();
            $stmt = $pdo->prepare("

            DECLARE @Output VARCHAR(MAX);
            EXEC dbo.sp_Quote_QuotationCharges ?, @Output = @Output OUTPUT;
            SELECT @Output AS OutputJson
        ");

            $stmt->execute([$inputJson]);

            // Walk result sets to find the OutputJson row
            $outputJson = null;
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (!empty($rows) && isset($rows[0]->OutputJson)) {
                        $outputJson = $rows[0]->OutputJson;
                        break;
                    }
                } catch (\PDOException $e) {

                    continue;
                }
            } while ($stmt->nextRowset());

            if ($outputJson === null) {
                Log::warning('getContractCharges - SP returned no output', ['booking_no' => $bookingNo]);
                return response()->json(['success' => true, 'charges' => []]);
            }

            $output = json_decode($outputJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('getContractCharges - Failed to decode SP output', [
                    'booking_no' => $bookingNo,
                    'raw'        => $outputJson,
                ]);

                return response()->json(['success' => false, 'message' => 'Failed to parse contract charges'], 500);
            }

            // SP may return {"Charges": [...]} or a raw array

            $charges = $output['Charges'] ?? (isset($output[0]) ? $output : []);

            Log::info('getContractCharges - Success', [
                'booking_no'   => $bookingNo,
                'charge_count' => count($charges),
            ]);

            return response()->json(['success' => true, 'charges' => $charges]);
        } catch (\Exception $e) {

            Log::error('getContractCharges - Failed', [
                'booking_no' => $bookingNo,
                'error'      => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getLegalEntities(Request $request)
    {
        $officeCode = trim($request->input('office_code', ''));
        $chargeType = $request->input('charge_type', 'Cost');
        $filter     = $request->input('filter', '');

        if (!$officeCode) {
            return response()->json(['success' => false, 'message' => 'Office code is required'], 400);
        }

        if (!in_array($chargeType, ['Revenue', 'Cost'])) {
            $chargeType = 'Cost';
        }

        try {
            $pdo  = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC sp_LegalEntityList ?, ?, ?");
            $stmt->execute([$officeCode, $chargeType, $filter]);

            $parties = [];
            do {
                try {
                    $results = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($results)) continue;
                    $firstRow = (array) $results[0];
                    $firstVal = array_values($firstRow)[0] ?? '';

                    // Skip SP error/status result sets
                    if (is_string($firstVal) && str_starts_with((string)$firstVal, 'Failed')) {
                        Log::warning('sp_LegalEntityList returned failure', [
                            'office_code' => $officeCode,
                            'charge_type' => $chargeType,
                            'message'     => $firstVal,
                        ]);
                        continue;
                    }

                    // Valid data result set — must have Id and Code

                    if (isset($results[0]->Id) && isset($results[0]->Code)) {
                        $parties = $results;
                        break;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            return response()->json(['success' => true, 'parties' => $parties]);
        } catch (\Exception $e) {

            Log::error('getLegalEntities failed', [
                'office_code' => $officeCode,
                'error'       => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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

    /**
     * Create a booking from a won quotation.
     * TODO: Implement full booking creation logic.
     */
    public function createBooking(Request $request)
    {
        $request->validate([
            'booking_no'        => 'required|string',
            'service_template'  => 'required|string',
            'bill_to_party'     => 'required|string',
            'forwarder'         => 'required|string',
            'place_of_receipt'  => 'required|string',
            'place_of_delivery' => 'required|string',
        ]);

        $submittedBy = auth()->user()->displayName ?? auth()->user()->Email ?? 'system';
        $input = json_encode([
            'OperationType'     => 'BookingInfoUpdate',
            'SubmittedDatetime' => now()->format('YmdHis'),
            'SubmittedBy'       => $submittedBy,
            'Action'            => 'Update',
            'QuotationNo'       => $request->booking_no,
            'ServiceTemplate'   => $request->service_template,
            'BillToParty'       => $request->bill_to_party,
            'Forwarder'         => $request->forwarder,
            'CountryIncluded'   => $request->include_country ? 1 : 0,
            'PlaceOfReceipt'    => $request->place_of_receipt,
            'PlaceOfDelivery'   => $request->place_of_delivery,
        ]);

        if (strlen($input) > 2000) {
            return response()->json([
                'success' => false,
                'message' => 'Request payload too large for SP. Max 2000 characters.'
            ], 400);
        }

        try {
            $output = $this->executeDetailsUpdateSP($input);
            if (($output['Status'] ?? '') === 'Failed') {
                $remarks = collect($output['Remarks'] ?? [])->pluck('Remark')->implode(', ');
                return response()->json([
                    'success' => false,
                    'message' => $remarks ?: 'Booking update failed'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully'
            ]);
        } catch (\Exception $e) {

            Log::error('createBooking error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resolve Cust_Office.Id (UUID) from Quote_QuotationInfo.teamid (bigint via TeamCode)
     * Shared helper used by Bill To Party and Forwarder lookups.
     */
    private function resolveOfficeId(string $teamId): ?string
    {
        $office = DB::selectOne("
        SELECT SCMOfficeID
        FROM Cust_Office WITH (NOLOCK)
        WHERE TeamCode = ?
            AND IsActive = 1
            AND IsDeleted = 0
        ", [$teamId]);

        return $office ? $office->SCMOfficeID : null;
    }

    /**
     * GET /quotation/create-booking/service-templates
     * Calls sp_Quote_ServiceTemplateList with the QuotationID.
     * Returns: [{ Id, Name }]
     */

    public function getServiceTemplates(Request $request)
    {
        $quotationId = $request->query('quotation_id');
        $filter      = $request->query('filter', '');

        Log::info('getServiceTemplates - params', [
            'quotation_id' => $quotationId,
            'filter'       => $filter,
        ]);

        if (!$quotationId) {
            return response()->json(['success' => false, 'message' => 'quotation_id is required'], 422);
        }

        try {
            $pdo  = DB::getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_Quote_ServiceTemplateList ?, ?");
            $stmt->execute([$quotationId, $filter]);

            $results        = [];
            $resultSetIndex = 0;

            do {
                $resultSetIndex++;
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    Log::debug('getServiceTemplates - Result set #' . $resultSetIndex, [
                        'quotation_id' => $quotationId,
                        'row_count'    => count($rows),
                        'first_row'    => !empty($rows) ? (array) $rows[0] : null,
                    ]);

                    if (empty($rows)) {
                        continue;
                    }

                    $firstVal = array_values((array) $rows[0])[0] ?? null;

                    // Skip diagnostic UUID result sets
                    if (count($rows) === 1 && count((array) $rows[0]) === 1 && is_string($firstVal)) {
                        if (preg_match('/^[0-9a-f\-]{36}$/i', trim($firstVal))) {
                            Log::debug('getServiceTemplates - Skipping diagnostic UUID result set');
                            continue;
                        }
                    }

                    // SP failure message
                    if (is_string($firstVal) && str_starts_with($firstVal, 'Failed')) {
                        Log::warning('getServiceTemplates - SP returned failure', [
                            'quotation_id' => $quotationId,
                            'message'      => $firstVal,
                        ]);
                        return response()->json(['success' => false, 'message' => $firstVal], 422);
                    }

                    // Actual template data — SP returns lowercase 'id' and 'Name'
                    if (isset($rows[0]->id) && isset($rows[0]->Name)) {
                        $results = $rows;
                        Log::info('getServiceTemplates - Found templates in result set #' . $resultSetIndex, [
                            'quotation_id'   => $quotationId,
                            'template_count' => count($results),
                        ]);
                        break;
                    }
                } catch (\PDOException $e) {
                    Log::debug('getServiceTemplates - Skipping unfetchable result set #' . $resultSetIndex, [
                        'error' => $e->getMessage(),
                    ]);
                    continue;
                }
            } while ($stmt->nextRowset());

            Log::info('getServiceTemplates - Completed', [
                'quotation_id'     => $quotationId,
                'template_count'   => count($results),
                'result_sets_seen' => $resultSetIndex,
            ]);

            return response()->json([
                'success'   => true,
                'templates' => $results,
            ]);
        } catch (\Exception $e) {
            Log::error('getServiceTemplates failed', [
                'quotation_id' => $quotationId,
                'error'        => $e->getMessage(),
                'file'         => $e->getFile(),
                'line'         => $e->getLine(),
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /quotation/create-booking/bill-to-parties
     * Calls sp_PartyList with PartyType = 'Debtor'
     * Returns: [{ PartyID, PartyCode, PartyName }]
     */

    public function getBillToParties(Request $request)
    {
        $teamId = $request->query('team_id');
        $filter = $request->query('filter', '');

        if (!$teamId) {
            return response()->json(['success' => false, 'message' => 'team_id is required'], 422);
        }

        $officeId = $this->resolveOfficeId($teamId);

        if (!$officeId) {
            return response()->json(['success' => false, 'message' => 'Office not found for team_id: ' . $teamId], 404);
        }

        try {
            $results = DB::select("EXEC dbo.sp_PartyList ?, 'Debtor', ?", [
                $officeId,
                $filter,
            ]);

            return response()->json([
                'success' => true,
                'parties' => $results, // each row has PartyID, PartyCode, PartyName
            ]);
        } catch (\Exception $e) {

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /quotation/create-booking/forwarders
     * Calls sp_PartyList with PartyType = 'Agent / Forwarder'
     * Returns: [{ PartyID, PartyCode, PartyName }]
     */

    public function getForwarders(Request $request)
    {
        $teamId = $request->query('team_id');
        $filter = $request->query('filter', '');

        if (!$teamId) {
            return response()->json(['success' => false, 'message' => 'team_id is required'], 422);
        }

        $officeId = $this->resolveOfficeId($teamId);

        if (!$officeId) {
            return response()->json(['success' => false, 'message' => 'Office not found for team_id: ' . $teamId], 404);
        }

        try {
            $results = DB::select("EXEC dbo.sp_PartyList ?, 'Agent / Forwarder', ?", [
                $officeId,
                $filter,
            ]);

            return response()->json([
                'success' => true,
                'parties' => $results, // each row has PartyID, PartyCode, PartyName
            ]);
        } catch (\Exception $e) {

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /quotation/create-booking/locations
     * Calls sp_LocationList with LocationType = 'Road' (always static per spec)
     * @IncludeCountry is driven by the checkbox value passed from the frontend
     * Returns: [{ LocationID, LocationCode, LocationName }]
     */
    public function getLocations(Request $request)
    {
        $filter         = $request->query('filter', '');
        $includeCountry = (int) $request->query('include_country', 0);
        $countryCode    = $request->query('country_code', '');

        try {
            $db = DB::getDatabaseName() === 'FITSYSUAT'
                ? '[FITSCMUAT].dbo.'
                : '[FITSCM].dbo.';

            $locationFilter = "('Station','Location','Airport','Seaport','InlandPort')";
            $query = "
            SELECT
                CL.Id   AS LocationID,
                CL.Code AS LocationCode,
                CL.Name AS LocationName,
                SL.Code AS LocationType
            FROM {$db}Cust_Location CL WITH (NOLOCK)
            INNER JOIN {$db}sys_locationType SL WITH (NOLOCK)
                ON SL.Id = CL.LocationTypeId
                AND SL.Code IN {$locationFilter}
        ";

            $bindings = [];
            if ($includeCountry === 1 && !empty($countryCode)) {
                $query .= " INNER JOIN Cust_Country CC WITH (NOLOCK)
                            ON CC.ID = CL.CountryId
                            AND CC.Code = ? ";
                $bindings[] = $countryCode;
            }

            $query .= " WHERE CL.IsActive = 1 AND CL.IsDeleted = 0 ";
            if (!empty($filter)) {
                $query .= " AND (CL.Name LIKE ? OR CL.Code LIKE ? OR CL.UNCode LIKE ?) ";
                $bindings[] = $filter . '%';
                $bindings[] = $filter . '%';
                $bindings[] = $filter . '%';
            }

            $query .= " ORDER BY CL.Name ";
            $results = DB::select($query, $bindings);

            return response()->json([
                'success'   => true,
                'locations' => $results,
            ]);
        } catch (\Exception $e) {

            Log::error('getLocations failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getAgentTypes(Request $request)
    {
        try {
            $filter = $request->input('filter', '');
            $agentTypes = DB::connection('sqlsrv')
                ->select('EXEC dbo.sp_ClientAgentList @Filter = ?', [$filter]);

            return response()->json([
                'success' => true,
                'data'    => $agentTypes,

            ]);
        } catch (\Exception $e) {
            Log::error('getAgentTypes - Failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load agent types: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getCustomerTypes()
    {
        try {
            $results = DB::select('EXEC sp_Quote_GetCustomerType');
            return response()->json(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'data' => [], 'message' => $e->getMessage()]);
        }
    }

    public function saveCustomerDetails(Request $request)
    {
        $request->validate([
            'booking_no'    => 'required|string',
            'customer_type' => 'required|string',
        ]);

        $submittedBy = auth()->user()->displayName ?? auth()->user()->Email ?? 'system';
        $input = json_encode([
            'OperationType'     => 'CustomerUpdate',
            'SubmittedDatetime' => now()->format('YmdHis'),
            'SubmittedBy'       => $submittedBy,
            'Action'            => 'Update',
            'QuotationNo'       => $request->booking_no,
            'ClientID'          => '',
            'Company'           => '',
            'Address1'          => '',
            'Address2'          => '',
            'City'              => '',
            'ZipCode'           => '',
            'State'             => '',
            'CountryCode'       => '',
            'ContactPerson'     => '',
            'EmailID'           => '',
            'PhoneNo'           => '',
            'IndustryCode'      => '',
            'Industry'          => '',
            'CustomerType'      => $request->customer_type,
            'AgentType'         => $request->agent_type ?? '',
        ]);

        if (strlen($input) > 2000) {
            return response()->json([
                'success' => false,
                'message' => 'Request payload too large for SP. Max 2000 characters.'
            ], 400);
        }

        try {
            $output = $this->executeDetailsUpdateSP($input);

            if (($output['Status'] ?? '') === 'Failed') {
                $remarks = collect($output['Remarks'] ?? [])->pluck('Remark')->implode(', ');

                return response()->json([
                    'success' => false,
                    'message' => $remarks ?: 'Customer update failed'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Customer details saved successfully'
            ]);
        } catch (\Exception $e) {

            Log::error('saveCustomerDetails error: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * Shared PDO helper for sp_Quote_DetailsUpdate.
     * Uses nextRowset() to skip intermediate result sets produced by
     * DROP/CREATE TABLE inside the SP — same pattern as saveCharge().
     */

    private function executeDetailsUpdateSP(string $inputJson): array
    {
        $pdo  = DB::connection()->getPdo();
        $stmt = $pdo->prepare("
            SET NOCOUNT ON;
            DECLARE @OutputJson VARCHAR(2000);
            EXEC dbo.sp_Quote_DetailsUpdate ?, @OutputJson = @OutputJson OUTPUT;
            SELECT @OutputJson AS output
        ");

        $stmt->execute([$inputJson]);
        do {
            try {
                $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                if (!empty($rows) && isset($rows[0]->output)) {
                    $decoded = json_decode($rows[0]->output, true);
                    return json_last_error() === JSON_ERROR_NONE ? $decoded : [];
                }
            } catch (\PDOException $e) {

                // skip empty/intermediate result sets
            }
        } while ($stmt->nextRowset());

        Log::error('executeDetailsUpdateSP - no output returned', ['input' => $inputJson]);
        return [
            'Status'  => 'Failed',
            'Remarks' => [['Remark' => 'No response from stored procedure']]
        ];
    }

    public function downloadPdf($id, $version)
    {
        try {
            $quotation = QuotationInfo::where('ID', $id)->first(['ID', 'bookingno']);

            if (!$quotation) {

                abort(404, 'Quotation not found');
            }

            $document = DB::table('Quote_Quotation_Document')
                ->where('QuotationInfoID', $quotation->ID)
                ->where('DocumentType', 'QuotationPDF') // FIX: was 'Quotation'
                ->where('IsDeleted', 0)
                ->where('IsVisible', 1)
                ->orderBy('CreatedOn', 'asc')
                ->skip($version - 1)
                ->take(1)
                ->first(['Name', 'FilePath']);
            if (!$document) {
                abort(404, 'Version ' . $version . ' not found');
            }

            $relativePath = $this->normalizeFilePath($document->FilePath);
            Log::info('downloadPdf - Path diagnostic', [
                'raw_filepath'      => $document->FilePath,
                'relative_path'     => $relativePath,
                'storage_root'      => \Storage::disk('public')->path(''),
                'full_resolved'     => \Storage::disk('public')->path($relativePath),
                'exists'            => \Storage::disk('public')->exists($relativePath),
                'file_exists_raw'   => file_exists($document->FilePath),
                'file_exists_full'  => file_exists(\Storage::disk('public')->path($relativePath)),

            ]);

            if (!\Storage::disk('public')->exists($relativePath)) {
                Log::error('downloadPdf - File not found on disk', [
                    'id'            => $id,
                    'version'       => $version,
                    'raw_filepath'  => $document->FilePath,
                    'relative_path' => $relativePath,
                ]);
                abort(404, 'File not found on disk');
            }

            Log::info('downloadPdf - Downloaded', [
                'id'         => $id,
                'booking_no' => $quotation->bookingno,
                'version'    => $version,
                'file'       => $document->Name,
                'user'       => Auth::user()->username ?? Auth::user()->email ?? 'system'

            ]);

            return \Storage::disk('public')->download($relativePath, $document->Name);
        } catch (\Exception $e) {
            Log::error('downloadPdf - Failed', [
                'id'      => $id,
                'version' => $version,
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            abort(500, 'Download failed: ' . $e->getMessage());
        }
    }

    /**
     * Normalize a FilePath that may be an absolute Windows path or already relative.
     * generatePdf saves the full path (e.g. C:\inetpub\...\storage\app/public\quotations/...)
     * Storage::disk('public') expects just the relative path (e.g. quotations/5238/...)
     */

    private function normalizeFilePath(string $filePath): string
    {
        // Normalize all slashes to forward
        $normalized = str_replace('\\', '/', $filePath);

        // Try to extract relative path after 'storage/app/public/'
        $marker = 'storage/app/public/';
        $pos    = strpos($normalized, $marker);

        if ($pos !== false) {
            return substr($normalized, $pos + strlen($marker));
        }

        // Fallback: try 'public/' in case path was already partially normalized
        $marker2 = '/public/';
        $pos2    = strrpos($normalized, $marker2); // strrpos = last occurrence

        if ($pos2 !== false) {
            return substr($normalized, $pos2 + strlen($marker2));
        }
        return ltrim($normalized, '/');
    }

    /**
     * Process a won quotation through the full booking pipeline.
     * Step 0: Save booking info (ServiceTemplate, BillToParty, Forwarder, PoR, PoD)
     * Step 1: Client Creation   → sp_Quote_BookingProcessMain 'Client'
     * Step 2: Booking XML       → sp_Quote_BookingProcessMain 'XML'
     *         ↳ After success: verify SCMBookingID was populated (required by Steps 3 & 4)
     * Step 3: Charge Transfer   → sp_Quote_BookingProcessMain 'Charge'
     * Step 4: Document Transfer → sp_Quote_BookingProcessMain 'Document'
     *
     * Route: POST /quotation/process-booking
     */
    public function processBooking(Request $request)
    {
        $request->validate([
            'id'                => 'required|string',
            'booking_no'        => 'required|string',
            'service_template'  => 'required|string',
            'bill_to_party'     => 'required|string',
            'forwarder'         => 'required|string',
            'place_of_receipt'  => 'required|string',
            'place_of_delivery' => 'required|string',
        ]);

        $submittedBy = Auth::user()->displayName ?? Auth::user()->Email ?? 'system';

        // ── Resolve QuoteNo ───────────────────────────────────────────────────────
        $quotation = DB::table('Quote_QuotationInfo')
            ->where('ID', $request->id)
            ->first(['ID', 'QuoteNo', 'bookingno', 'QuoteStatus', 'teamid', 'OfficeCode']);

        if (!$quotation) {
            Log::error('processBooking - Quotation not found', [
                'id'   => $request->id,
                'user' => $submittedBy,
            ]);
            return response()->json(['success' => false, 'message' => 'Quotation not found'], 404);
        }

        $quoteNo = $quotation->QuoteNo;

        if (empty($quoteNo)) {
            return response()->json([
                'success' => false,
                'message' => 'QuoteNo is not set on this quotation. Cannot proceed with booking process.',
            ], 422);
        }

        Log::info('processBooking - Starting', [
            'id'         => $request->id,
            'booking_no' => $request->booking_no,
            'quote_no'   => $quoteNo,
            'user'       => $submittedBy,
        ]);

        $steps = [];

        // ── Step 0: Save Booking Info ─────────────────────────────────────────────
        $step0Result = $this->runBookingInfoUpdate(
            $quoteNo,
            $request->service_template,
            $request->bill_to_party,
            $request->forwarder,
            (int) $request->input('include_country', 0),
            $request->place_of_receipt,
            $request->place_of_delivery,
            $submittedBy
        );

        $steps[] = [
            'step'   => 0,
            'label'  => 'Save Booking Info',
            'status' => $step0Result['status'],
            'remark' => $step0Result['remark'],
        ];

        if ($step0Result['status'] !== 'Success') {
            $this->markAsFailed($request->id, 'Save Booking Info', $step0Result['remark'], $submittedBy);

            return response()->json([
                'success'      => false,
                'failed_step'  => 0,
                'failed_label' => 'Save Booking Info',
                'steps'        => $steps,
            ]);
        }

        // ── Steps 1–4: sp_Quote_BookingProcessMain ────────────────────────────────
        $processSteps = [
            1 => ['type' => 'Client',   'label' => 'Client Creation'],
            2 => ['type' => 'XML',      'label' => 'Booking XML'],
            3 => ['type' => 'Charge',   'label' => 'Charge Transfer'],
            4 => ['type' => 'Document', 'label' => 'Document Transfer'],
        ];

        foreach ($processSteps as $stepNo => $stepDef) {
            $result = $this->runBookingProcessMain($quoteNo, $stepDef['type']);

            $steps[] = [
                'step'   => $stepNo,
                'label'  => $stepDef['label'],
                'status' => $result['status'],
                'remark' => $result['remark'],
            ];

            Log::info('processBooking - Step result', [
                'quote_no'     => $quoteNo,
                'step'         => $stepNo,
                'type'         => $stepDef['type'],
                'status'       => $result['status'],
                'remark'       => $result['remark'],
            ]);

            if ($result['status'] !== 'Success') {
                $this->markAsFailed(
                    $request->id,
                    $stepDef['label'],
                    $result['remark'],
                    $submittedBy
                );

                // Mark remaining steps as skipped
                foreach ($processSteps as $remainingNo => $remainingDef) {
                    if ($remainingNo > $stepNo) {
                        $steps[] = [
                            'step'   => $remainingNo,
                            'label'  => $remainingDef['label'],
                            'status' => 'Skipped',
                            'remark' => 'Skipped due to previous step failure',
                        ];
                    }
                }

                return response()->json([
                    'success'      => false,
                    'failed_step'  => $stepNo,
                    'failed_label' => $stepDef['label'],
                    'steps'        => $steps,
                ]);
            }

            // ── After XML step: verify SCMBookingID was written ───────────────────
            // sp_Quote_QuoteBookingCreateInSCM saves SCMBookingXML to the row but
            // SCMBookingID must be populated by the XML generation sub-SP
            // (sp_Quote_XMLGeneration / sp_Quote_XMLGenerationforIndividual).
            // Steps 3 (Charge) and 4 (Document) both read SCMBookingID and will
            // immediately fail with "Booking Is Still No Created" if it is NULL.
            // We guard here so the error is clear rather than cryptic.
            if ($stepNo === 2) {
                $bookingCheck = DB::table('Quote_QuotationInfo')
                    ->where('QuoteNo', $quoteNo)
                    ->first(['SCMBookingID', 'SCMBookingNo']);

                if (empty($bookingCheck->SCMBookingID)) {
                    $guardRemark = 'SCMBookingID was not populated after the XML step. '
                        . 'Steps 3 & 4 require it — contact the DB team to confirm '
                        . 'sp_Quote_XMLGeneration writes SCMBookingID back to Quote_QuotationInfo.';

                    Log::error('processBooking - SCMBookingID missing after XML step', [
                        'quote_no'        => $quoteNo,
                        'scm_booking_id'  => $bookingCheck->SCMBookingID  ?? 'NULL',
                        'scm_booking_no'  => $bookingCheck->SCMBookingNo  ?? 'NULL',
                    ]);

                    $this->markAsFailed($request->id, 'Post-XML Check', $guardRemark, $submittedBy);

                    // Mark Steps 3 & 4 as skipped
                    foreach ($processSteps as $remainingNo => $remainingDef) {
                        if ($remainingNo > $stepNo) {
                            $steps[] = [
                                'step'   => $remainingNo,
                                'label'  => $remainingDef['label'],
                                'status' => 'Skipped',
                                'remark' => 'Skipped — SCMBookingID not set after XML step',
                            ];
                        }
                    }

                    return response()->json([
                        'success'      => false,
                        'failed_step'  => 2,
                        'failed_label' => 'Post-XML Check',
                        'steps'        => $steps,
                    ]);
                }

                Log::info('processBooking - SCMBookingID confirmed after XML step', [
                    'quote_no'       => $quoteNo,
                    'scm_booking_id' => $bookingCheck->SCMBookingID,
                    'scm_booking_no' => $bookingCheck->SCMBookingNo ?? 'NULL',
                ]);
            }
        }

        // ── All steps passed: mark SCMINTStatus = 'Success' ──────────────────────
        DB::table('Quote_QuotationInfo')
            ->where('ID', $request->id)
            ->update([
                'SCMINTStatus'   => 'Success',
                'LastModifiedBy' => $submittedBy,
                'updatedon'      => now(),
            ]);

        Log::info('processBooking - All steps completed successfully', [
            'quote_no' => $quoteNo,
            'user'     => $submittedBy,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking process completed successfully',
            'steps'   => $steps,
        ]);
    }


    /**
     * Writes SCMINTStatus = 'Failed' and appends the step + error message to
     * QuoteStatusDescription so the row appears in the Failed Int. card and the
     * Remarks column shows the specific failure reason.
     *
     * QuoteStatusDescription stores concatenated JSON objects:
     *   {"Remark":"msg1"},{"Remark":"msg2"}
     * so we append a new object in the same format.
     */
    private function markAsFailed(string $quotationId, string $stepLabel, string $remark, string $submittedBy): void
    {
        try {
            $existing = DB::table('Quote_QuotationInfo')
                ->where('ID', $quotationId)
                ->value('QuoteStatusDescription');

            $newEntry     = json_encode(['Remark' => $stepLabel . ': ' . $remark]);
            $description  = $existing ? $existing . ',' . $newEntry : $newEntry;

            DB::table('Quote_QuotationInfo')
                ->where('ID', $quotationId)
                ->update([
                    'SCMINTStatus'           => 'Failed',
                    'QuoteStatusDescription' => $description,
                    'LastModifiedBy'         => $submittedBy,
                    'updatedon'              => now(),
                ]);

            Log::info('markAsFailed - Updated record', [
                'quotation_id' => $quotationId,
                'step_label'   => $stepLabel,
                'remark'       => $remark,
            ]);
        } catch (\Exception $e) {
            Log::error('markAsFailed - Could not update record', [
                'quotation_id' => $quotationId,
                'error'        => $e->getMessage(),
            ]);
        }
    }


    /**
     * Execute sp_Quote_BookingInfoUpdate (Step 0).
     * Saves ServiceTemplate, BillToParty, Forwarder, PlaceOfReceipt, PlaceOfDelivery
     * onto the quotation record before the SCM process steps run.
     */
    private function runBookingInfoUpdate(
        string $quoteNo,
        string $serviceTemplate,
        string $billToParty,
        string $forwarder,
        int    $includeCountry,
        string $placeOfReceipt,
        string $placeOfDelivery,
        string $submittedBy
    ): array {
        try {
            // Truncate to 25 chars to match SP varchar(25) declaration
            $submittedBy = substr($submittedBy, 0, 25);

            $inputJson = json_encode([
                'OperationType'     => 'BookingInfoUpdate',
                'SubmittedDatetime' => now()->format('YmdHis'),
                'SubmittedBy'       => $submittedBy,
                'Action'            => 'Update',
                'QuotationNo'       => $quoteNo,
                'ServiceTemplate'   => $serviceTemplate,
                'BillToParty'       => $billToParty,
                'Forwarder'         => $forwarder,
                'CountryIncluded'   => $includeCountry,
                'PlaceOfReceipt'    => $placeOfReceipt,
                'PlaceOfDelivery'   => $placeOfDelivery,
            ]);

            Log::info('runBookingInfoUpdate - Input', [
                'quote_no'   => $quoteNo,
                'input_json' => $inputJson,
            ]);

            $pdo  = DB::connection()->getPdo();
            $stmt = $pdo->prepare("
                SET NOCOUNT ON;
                DECLARE @OutputJson VARCHAR(2000);
                EXEC dbo.sp_Quote_DetailsUpdate ?, @OutputJson = @OutputJson OUTPUT;
                SELECT @OutputJson AS OutputJson
            ");
            $stmt->execute([$inputJson]);

            // Walk result sets — SP may emit intermediate sets
            $outputJson = null;
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (!empty($rows) && isset($rows[0]->OutputJson)) {
                        $outputJson = $rows[0]->OutputJson;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            if ($outputJson === null) {
                Log::error('runBookingInfoUpdate - No output returned', ['quote_no' => $quoteNo]);
                return ['status' => 'Failed', 'remark' => 'No response from booking info SP'];
            }

            $output = json_decode($outputJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('runBookingInfoUpdate - JSON decode failed', [
                    'quote_no'   => $quoteNo,
                    'raw_output' => $outputJson,
                ]);
                return ['status' => 'Failed', 'remark' => 'Failed to parse booking info SP response'];
            }

            // Fetch the raw OfficeCode for diagnostic purposes
            $rawOfficeCode = DB::selectOne(
                "SELECT OfficeCode, LEN(OfficeCode) as OfficeCodeLen, QuoteNo 
     FROM Quote_QuotationInfo WHERE QuoteNo = ?",
                [$quoteNo]
            );

            Log::info('runBookingInfoUpdate - Output', [
                'quote_no'          => $quoteNo,
                'output'            => $output,
                'sp_called'         => 'sp_Quote_DetailsUpdate',
                'input_json'        => $inputJson,
                'diagnostic' => [
                    'raw_office_code'     => $rawOfficeCode->OfficeCode ?? 'N/A',
                    'office_code_length'  => $rawOfficeCode->OfficeCodeLen ?? 'N/A',
                    'trimmed_office_code' => trim($rawOfficeCode->OfficeCode ?? ''),
                    'has_trailing_space'  => isset($rawOfficeCode->OfficeCode)
                        && $rawOfficeCode->OfficeCode !== trim($rawOfficeCode->OfficeCode),
                ]
            ]);

            return [
                'status' => $output['Status'] ?? 'Failed',
                'remark' => $output['Remark'] ?? '',
                'diagnostic' => [
                    'sp_called'           => 'sp_Quote_DetailsUpdate',
                    'quote_no'            => $quoteNo,
                    'raw_office_code'     => $rawOfficeCode->OfficeCode ?? 'N/A',
                    'office_code_length'  => $rawOfficeCode->OfficeCodeLen ?? 'N/A',
                    'has_trailing_space'  => isset($rawOfficeCode->OfficeCode)
                        && $rawOfficeCode->OfficeCode !== trim($rawOfficeCode->OfficeCode),
                ]
            ];
        } catch (\Exception $e) {
            Log::error('runBookingInfoUpdate - Exception', [
                'quote_no' => $quoteNo,
                'error'    => $e->getMessage(),
                'file'     => $e->getFile(),
                'line'     => $e->getLine(),
            ]);
            return ['status' => 'Failed', 'remark' => $e->getMessage()];
        }
    }

    /**
     * Execute sp_Quote_BookingProcessMain for a single process type.
     * Returns ['status' => 'Success'|'Failed', 'remark' => '...']
     */
    private function runBookingProcessMain(string $quoteNo, string $processType): array
    {
        try {
            Log::info('runBookingProcessMain - Executing', [
                'quote_no'     => $quoteNo,
                'process_type' => $processType,
            ]);

            $pdo = DB::connection()->getPdo();

            $sql = "
                DECLARE @Status VARCHAR(20), @Remark VARCHAR(200);
                EXEC dbo.sp_Quote_BookingProcessMain ?, ?,
                    @Status = @Status OUTPUT,
                    @Remark = @Remark OUTPUT;
                SELECT @Status AS Status, @Remark AS Remark
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$quoteNo, $processType]);

            // Walk result sets to find Status/Remark row
            $result = null;
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (!empty($rows) && isset($rows[0]->Status)) {
                        $result = $rows[0];
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            if (!$result) {
                Log::error('runBookingProcessMain - No output returned', [
                    'quote_no'     => $quoteNo,
                    'process_type' => $processType,
                ]);
                return ['status' => 'Failed', 'remark' => 'No response from booking process SP'];
            }

            Log::info('runBookingProcessMain - Result', [
                'quote_no'     => $quoteNo,
                'process_type' => $processType,
                'status'       => $result->Status,
                'remark'       => $result->Remark,
            ]);

            return [
                'status' => $result->Status ?? 'Failed',
                'remark' => $result->Remark ?? '',
            ];
        } catch (\Exception $e) {
            Log::error('runBookingProcessMain - Exception', [
                'quote_no'     => $quoteNo,
                'process_type' => $processType,
                'error'        => $e->getMessage(),
                'file'         => $e->getFile(),
                'line'         => $e->getLine(),
            ]);
            return ['status' => 'Failed', 'remark' => $e->getMessage()];
        }
    }

    // ============================================
    // SHIPMENT MODE DROPDOWN
    // sp_ShipmentMode returns: Id, Code
    // ============================================
    public function getShipmentModes(Request $request)
    {
        $filter = $request->input('filter', '');
        try {
            $pdo  = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_ShipmentMode ?");
            $stmt->execute([$filter]);

            $results = [];
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($rows)) continue;
                    $firstVal = array_values((array) $rows[0])[0] ?? '';
                    if (is_string($firstVal) && str_starts_with($firstVal, 'Failed')) continue;
                    if (isset($rows[0]->Id) && isset($rows[0]->Code)) {
                        $results = $rows;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            return response()->json(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            Log::error('getShipmentModes - Failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ============================================
    // TRANSPORT MODE DROPDOWN
    // sp_TransportMode returns: Id, Code, Name
    // ============================================
    public function getTransportModes(Request $request)
    {
        $filter = $request->input('filter', '');
        try {
            $pdo  = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_TransportMode ?");
            $stmt->execute([$filter]);

            $results = [];
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($rows)) continue;
                    $firstVal = array_values((array) $rows[0])[0] ?? '';
                    if (is_string($firstVal) && str_starts_with($firstVal, 'Failed')) continue;
                    if (isset($rows[0]->Id) && isset($rows[0]->Code)) {
                        $results = $rows;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            return response()->json(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            Log::error('getTransportModes - Failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function saveShipmentDetails(Request $request)
    {
        $request->validate([
            'booking_no'     => 'required|string',
            'shipment_mode'  => 'required|string',
            'transport_mode' => 'required|string',
        ]);

        $submittedBy = auth()->user()->displayName ?? auth()->user()->Email ?? 'system';

        // SP expects QuoteNo (e.g. 'QT-5298'), not the numeric bookingno
        $quotation = DB::table('Quote_QuotationInfo')
            ->where('bookingno', $request->booking_no)
            ->value('QuoteNo');

        if (!$quotation) {
            return response()->json([
                'success' => false,
                'message' => 'Quotation not found for booking number: ' . $request->booking_no
            ], 404);
        }

        $input = json_encode([
            'OperationType'     => 'ShipmentDetails',       // was 'ShipmentUpdate'
            'SubmittedDatetime' => now()->format('YmdHis'),
            'SubmittedBy'       => $submittedBy,
            'Action'            => 'Update',
            'QuotationNo'       => $quotation,              // was $request->booking_no
            'ShipmentMode'      => $request->shipment_mode,
            'TransportMode'     => $request->transport_mode,
        ]);

        if (strlen($input) > 2000) {
            return response()->json([
                'success' => false,
                'message' => 'Request payload too large for SP. Max 2000 characters.'
            ], 400);
        }

        try {
            $output = $this->executeDetailsUpdateSP($input);

            if (($output['Status'] ?? '') === 'Failed') {
                $remarks = collect($output['Remarks'] ?? [])->pluck('Remark')->implode(', ');
                return response()->json([
                    'success' => false,
                    'message' => $remarks ?: 'Shipment update failed'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Shipment details saved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('saveShipmentDetails error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getEquipmentTypes(Request $request)
    {
        $filter = $request->input('filter', '');
        try {
            $pdo  = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_EquipmentType ?");
            $stmt->execute([$filter]);

            $results = [];
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($rows)) continue;
                    $firstVal = array_values((array) $rows[0])[0] ?? '';
                    if (is_string($firstVal) && str_starts_with($firstVal, 'Failed')) continue;
                    if (isset($rows[0]->Id) && isset($rows[0]->Code)) {
                        $results = $rows;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            return response()->json(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            Log::error('getEquipmentTypes - Failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getChargeBasedOnDefaults(Request $request)
    {
        $bookingNo  = $request->input('booking_no');
        $basedOn    = $request->input('based_on');
        $equipment  = $request->input('equipment', '');

        if (!$bookingNo || !$basedOn) {
            return response()->json(['success' => false, 'message' => 'booking_no and based_on are required'], 400);
        }

        // ── Resolve QuoteNo from bookingno ──────────────────────────────────────
        // sp_ChargeBasedOnDefaultDetails uses WHERE QI.QuoteNo = @QuotationNo
        // QuoteNo is the formatted reference (e.g. 'QT-5298'), NOT the numeric bookingno.
        $quotation = DB::table('Quote_QuotationInfo')
            ->where('bookingno', $bookingNo)
            ->first(['QuoteNo']);

        if (!$quotation || empty($quotation->QuoteNo)) {
            Log::warning('getChargeBasedOnDefaults - QuoteNo not found', ['booking_no' => $bookingNo]);
            return response()->json(['success' => false, 'message' => 'QuoteNo not found for this booking'], 404);
        }

        $quoteNo = trim($quotation->QuoteNo);

        Log::info('getChargeBasedOnDefaults - Calling SP', [
            'booking_no' => $bookingNo,
            'quote_no'   => $quoteNo,
            'based_on'   => $basedOn,
            'equipment'  => $equipment ?: 'N/A',
        ]);

        try {
            $pdo  = DB::getPdo();
            $stmt = $pdo->prepare("
            DECLARE @BasedOnUOMCode VARCHAR(50),
                    @BasedOnUOM     VARCHAR(100),
                    @Quantity       DECIMAL(18,3);

            EXEC dbo.sp_ChargeBasedOnDefaultDetails ?, ?, ?,
                @BasedOnUOMCode = @BasedOnUOMCode OUTPUT,
                @BasedOnUOM     = @BasedOnUOM     OUTPUT,
                @Quantity       = @Quantity       OUTPUT;

            SELECT @BasedOnUOMCode AS BasedOnUOMCode,
                   @BasedOnUOM     AS BasedOnUOM,
                   @Quantity       AS Quantity;
        ");

            $stmt->execute([$quoteNo, $basedOn, $equipment]);

            $result = null;
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($rows)) continue;

                    $firstVal = array_values((array) $rows[0])[0] ?? '';

                    // SP CATCH block returns 'Failed - ...' as a result row
                    if (is_string($firstVal) && str_starts_with(trim($firstVal), 'Failed')) {
                        Log::error('getChargeBasedOnDefaults - SP returned failure', [
                            'booking_no' => $bookingNo,
                            'quote_no'   => $quoteNo,
                            'message'    => $firstVal,
                        ]);
                        return response()->json(['success' => false, 'message' => $firstVal], 422);
                    }

                    if (isset($rows[0]->BasedOnUOMCode)) {
                        $result = $rows[0];
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            if (!$result) {
                Log::warning('getChargeBasedOnDefaults - SP returned no output', [
                    'booking_no' => $bookingNo,
                    'quote_no'   => $quoteNo,
                    'based_on'   => $basedOn,
                ]);
                return response()->json(['success' => false, 'message' => 'No defaults returned from SP'], 500);
            }

            Log::info('getChargeBasedOnDefaults - Success', [
                'booking_no'      => $bookingNo,
                'quote_no'        => $quoteNo,
                'based_on'        => $basedOn,
                'based_on_uom'    => $result->BasedOnUOM,
                'quantity'        => $result->Quantity,
            ]);

            return response()->json([
                'success'          => true,
                'based_on_uom_code' => trim($result->BasedOnUOMCode ?? ''),
                'based_on_uom'     => trim($result->BasedOnUOM     ?? ''),
                'quantity'         => (float) ($result->Quantity   ?? 0),
            ]);
        } catch (\Exception $e) {
            Log::error('getChargeBasedOnDefaults - Exception', [
                'booking_no' => $bookingNo,
                'error'      => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ============================================
    // GET CONTAINERS
    // Loads from Quote_Quotation_EquipmentDetails for a given QuoteNo.
    // Used to populate the container list table on page load.
    // ============================================
    public function getContainers(Request $request)
    {
        $quoteNo = $request->input('quote_no', '');

        if (!$quoteNo) {
            return response()->json(['success' => false, 'message' => 'Quote number required']);
        }

        try {
            $containers = DB::connection('sqlsrv')
                ->table('Quote_Quotation_EquipmentDetails as QE')
                ->join('Quote_QuotationInfo as QI', 'QI.ID', '=', 'QE.QuotationInfoID')
                ->where('QI.QuoteNo', $quoteNo)
                ->where('QE.IsActive', 1)
                ->where('QE.IsDeleted', 0)
                ->select('QE.EquipmentType', 'QE.Quantity')
                ->get();

            return response()->json(['success' => true, 'containers' => $containers]);
        } catch (\Exception $e) {
            Log::error('getContainers - Failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ============================================
    // SAVE CARGO DETAILS
    // Calls sp_Quote_CargoDetailsUpdate via OUTPUT parameter pattern.
    // Containers array uses "Qunatity" (SP typo — must match exactly).
    // ============================================
    public function saveCargoDetails(Request $request)
    {
        $request->validate([
            'quote_no'     => 'required|string',
            'cargo_volume' => 'required|numeric',
            'containers'   => 'nullable|string',
        ]);

        $submittedBy = auth()->user()->displayName ?? auth()->user()->Email ?? 'system';

        // Build containers array — note SP typo "Qunatity" must be preserved
        $containers = [];
        if ($request->containers) {
            foreach (explode(',', $request->containers) as $segment) {
                $segment = trim($segment);
                $xPos = strrpos($segment, 'X');
                if ($xPos === false) continue;
                $type = trim(substr($segment, 0, $xPos));
                $qty  = (int) trim(substr($segment, $xPos + 1));
                if ($type && $qty > 0) {
                    $containers[] = [
                        'ContainerType' => $type,
                        'Qunatity'      => $qty,   // ← SP typo — intentional
                    ];
                }
            }
        }

        $input = json_encode([
            'OperationType'     => 'CargoDetails',
            'SubmittedDatetime' => now()->format('YmdHis'),
            'SubmittedBy'       => $submittedBy,
            'Action'            => 'Update',
            'QuotationNo'       => $request->quote_no,
            'CargoVolume'       => (float) $request->cargo_volume,
            'Containers'        => $containers,
        ]);

        if (strlen($input) > 2000) {
            return response()->json([
                'success' => false,
                'message' => 'Payload too large for SP. Reduce number of containers.'
            ], 400);
        }

        try {
            $pdo  = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_Quote_CargoDetailsUpdate ?, ?");

            $outputJson = '';
            $stmt->bindParam(1, $input,      \PDO::PARAM_STR);
            $stmt->bindParam(2, $outputJson, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 2000);
            $stmt->execute();

            $output = json_decode($outputJson, true);

            // SP returns array of {Status, Remark} — find the last Status row
            $status  = '';
            $remarks = [];
            if (is_array($output)) {
                foreach ($output as $row) {
                    if (isset($row['Status'])) $status = $row['Status'];
                    if (isset($row['Remark'])) $remarks[] = $row['Remark'];
                }
            }

            if ($status === 'Failed') {
                return response()->json([
                    'success' => false,
                    'message' => implode(', ', array_filter($remarks)) ?: 'Cargo update failed'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cargo details saved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('saveCargoDetails error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
