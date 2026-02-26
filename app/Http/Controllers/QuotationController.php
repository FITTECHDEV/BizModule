<?php

namespace App\Http\Controllers;

use App\Models\QuotationInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }



    private function officeCodeMap(): array
    {
        return [
            'DJ' => 'Djibouti',
            'ET' => 'Ethiopia',
            'KE' => 'Kenya',
            'RW' => 'Rwanda',
            'SA' => 'Sales',
            'SS' => 'South Sudan',
            'TZ' => 'Tanzania',
            'UG' => 'Uganda',
            'ZM' => 'Zambia',
        ];
    }


    public function index()
    {
        // ============================================
        // RESOLVE USER'S MAPPED OFFICES FROM Azure_Users
        // Auth user model uses 'Email' (capital E)
        // matched against Azure_Users.userPrincipalName
        // ============================================
        $userPrincipalName = Auth::user()->Email ?? null;

        $normalizedEmail = strtolower(trim($userPrincipalName));

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

        $map = $this->officeCodeMap();

        $offices = $userOfficeCodes
            ->map(fn($code) => $map[$code] ?? null)
            ->filter()
            ->sort()
            ->values();

        $unmappedCodes = $userOfficeCodes->reject(fn($code) => isset($map[$code]));

        if ($unmappedCodes->isNotEmpty()) {
            Log::warning('Quotation index - Unmapped BaseOfficeCode(s) found', [
                'userPrincipalName' => $userPrincipalName,
                'unmapped_codes'    => $unmappedCodes->toArray(),
            ]);

            $fallbackOffices = DB::table('Quote_QuotationInfo')
                ->whereNotNull('teamname')
                ->whereIn('teamname', $unmappedCodes->toArray())
                ->distinct()
                ->pluck('teamname');

            $offices = $offices->merge($fallbackOffices)->unique()->sort()->values();
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

        $stats = $this->getStats('all', null, null, $defaultOffice);

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

        // Default to logged-in user's office if none provided
        $office = $request->input('office') ?: (Auth::user()->OfficeCode ?? null);

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
            $q->where(function ($sub) {
                $sub->whereIn('QuoteStatus', ['Proposal', 'Pending'])
                    ->orWhereIn('status', ['Proposal', 'Pending'])
                    ->orWhereNull('QuoteStatus')
                    ->orWhereNull('status');
            });
        })->count();

        $won = (clone $query)->where(function ($q) {
            $q->where('QuoteStatus', 'Won')
                ->orWhere('status', 'Won');
        })->where(function ($q) {
            $q->whereNull('SCMINTStatus')
                ->orWhere('SCMINTStatus', 'Success');
        })->count();

        $lost = (clone $query)->where(function ($q) {
            $q->where('QuoteStatus', 'Lost')
                ->orWhere('status', 'Lost');
        })->count();
        $canceled = (clone $query)->where(function ($q) {
            $q->where('QuoteStatus', 'Cancel')
                ->orWhere('status', 'Cancel');
        })->count();
        $failedInt = (clone $query)->where(function ($q) {
            $q->where('QuoteStatus', 'Won')
                ->orWhere('status', 'Won');
        })->whereNotNull('SCMINTStatus')
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
        $days       = $request->input('days', 0);
        $startDate  = $request->input('start_date');
        $endDate    = $request->input('end_date');
        $cardFilter = $request->input('card_filter', 'in_progress'); // ← default changed from 'all'

        // Default to logged-in user's office if no office param sent
        $office = $request->input('office') ?: (Auth::user()->OfficeCode ?? null);

        Log::info('Quotation data request', [
            'days'         => $days,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'office'       => $office,
            'card_filter'  => $cardFilter,
            'default_from' => $request->input('office') ? 'request' : 'user_profile',
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

        // Office filtering — user's default office if nothing explicitly passed
        if ($office) {
            $query->where('teamname', $office);
        }

        // Card filter — defaults to in_progress
        Log::info('Applying card filter', ['card_filter' => $cardFilter]);

        switch ($cardFilter) {
            case 'in_progress':
                $query->where(function ($q) {
                    $q->whereIn('QuoteStatus', ['Proposal', 'Pending'])
                        ->orWhereIn('status', ['Proposal', 'Pending'])
                        ->orWhereNull('QuoteStatus')
                        ->orWhereNull('status');
                });
                Log::info('Applied in_progress filter');
                break;

            case 'won':
                $query->where(function ($q) {
                    $q->where('QuoteStatus', 'Won')
                        ->orWhere('status', 'Won');
                })->where(function ($q) {
                    $q->whereNull('SCMINTStatus')
                        ->orWhere('SCMINTStatus', 'Success');
                });
                Log::info('Applied won filter');
                break;

            case 'lost':
                $query->where(function ($q) {
                    $q->where('QuoteStatus', 'Lost')
                        ->orWhere('status', 'Lost');
                });
                Log::info('Applied lost filter');
                break;

            case 'canceled':
                $query->where(function ($q) {
                    $q->where('QuoteStatus', 'Cancel')
                        ->orWhere('status', 'Cancel');
                });
                Log::info('Applied canceled filter');
                break;

            case 'failed_int':
                $query->where(function ($q) {
                    $q->where('QuoteStatus', 'Won')
                        ->orWhere('status', 'Won');
                })->whereNotNull('SCMINTStatus')
                    ->where('SCMINTStatus', '!=', 'Success');
                Log::info('Applied failed_int filter');
                break;

            case 'all':
            default:
                Log::info('No card filter applied - showing all records');
                break;
        }

        return DataTables::of($query)
            ->filterColumn('OfficeName', function ($query, $keyword) {
                $query->where('teamname', 'like', "%{$keyword}%");
            })
            ->filterColumn('ClientName', function ($query, $keyword) {
                $query->where('companyname', 'like', "%{$keyword}%");
            })
            ->filterColumn('ClientID', function ($query, $keyword) {
                $query->where('clientid', 'like', "%{$keyword}%");
            })
            ->filterColumn('SalesPerson', function ($query, $keyword) {
                $query->where('username', 'like', "%{$keyword}%");
            })
            ->filterColumn('OriginCountry', function ($query, $keyword) {
                $query->where('origincountry', 'like', "%{$keyword}%");
            })
            ->filterColumn('DestinationCountry', function ($query, $keyword) {
                $query->where('destinationcountry', 'like', "%{$keyword}%");
            })
            ->filterColumn('QuotationStatus', function ($query, $keyword) {
                $keyword = strtolower(trim($keyword));
                $query->where(function ($q) use ($keyword) {
                    if (str_contains($keyword, 'progress') || str_contains($keyword, 'pending') || str_contains($keyword, 'proposal')) {
                        $q->where(function ($sub) {
                            $sub->whereIn('QuoteStatus', ['Proposal', 'Pending'])
                                ->orWhereIn('status', ['Proposal', 'Pending'])
                                ->orWhereNull('QuoteStatus')
                                ->orWhereNull('status');
                        });
                    } elseif (str_contains($keyword, 'won')) {
                        $q->where(function ($sub) {
                            $sub->where('QuoteStatus', 'Won')->orWhere('status', 'Won');
                        })->where(function ($sub) {
                            $sub->whereNull('SCMINTStatus')->orWhere('SCMINTStatus', 'Success');
                        });
                    } elseif (str_contains($keyword, 'lost')) {
                        $q->where(function ($sub) {
                            $sub->where('QuoteStatus', 'Lost')->orWhere('status', 'Lost');
                        });
                    } elseif (str_contains($keyword, 'cancel')) {
                        $q->where(function ($sub) {
                            $sub->where('QuoteStatus', 'Cancel')->orWhere('status', 'Cancel');
                        });
                    } elseif (str_contains($keyword, 'failed') || str_contains($keyword, 'int')) {
                        $q->where(function ($sub) {
                            $sub->where('QuoteStatus', 'Won')->orWhere('status', 'Won');
                        })->whereNotNull('SCMINTStatus')->where('SCMINTStatus', '!=', 'Success');
                    } else {
                        $q->where('QuoteStatus', 'like', "%{$keyword}%")
                            ->orWhere('status', 'like', "%{$keyword}%");
                    }
                });
            })
            ->filterColumn('StatusRemark', function ($query, $keyword) {
                $query->where('SCMINTStatus', 'like', "%{$keyword}%");
            })
            ->filterColumn('BookingNo', function ($query, $keyword) {
                $query->where('bookingno', 'like', "%{$keyword}%");
            })
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
            ->addColumn('action', function ($row) use ($cardFilter) {
                $status    = $row->QuoteStatus ?? $row->status ?? 'Proposal';
                $scmStatus = $row->SCMINTStatus;

                $isInProgress = in_array($status, ['Pending', 'Proposal']) || is_null($status);
                $isFailedInt  = ($status == 'Won' && $scmStatus && $scmStatus != 'Success');

                $viewBtn = '<a href="' . route('quotation.view', $row->ID) . '" class="btn btn-info btn-sm" title="View">
                <i class="fas fa-eye"></i>
            </a>';

                $editBtn      = '';
                $retriggerBtn = '';

                if ($cardFilter === 'in_progress' && $isInProgress) {
                    $editBtn = ' <a href="' . route('quotation.edit', $row->ID) . '" class="btn btn-warning btn-sm" title="Edit">
                    <i class="fas fa-edit"></i>
                </a>';
                }

                if ($cardFilter === 'failed_int' && $isFailedInt) {
                    $retriggerBtn = ' <button class="btn btn-danger btn-sm btn-retrigger" data-booking="' . $row->bookingno . '" title="Retrigger">
                    <i class="fas fa-sync-alt"></i>
                </button>';
                }

                return $viewBtn . $editBtn . $retriggerBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
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
                ? (Auth::user()->username ?? Auth::user()->email ?? Auth::user()->name ?? 'system')
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
                ? (Auth::user()->username ?? Auth::user()->email ?? Auth::user()->name ?? 'system')
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
                'user' => Auth::user()->username ?? Auth::user()->email,
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
                    'user' => Auth::user()->username ?? Auth::user()->email
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
                    ? (Auth::user()->username ?? Auth::user()->email ?? Auth::user()->name ?? 'unknown')
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
                    'user' => Auth::user()->username ?? Auth::user()->email
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
                    'user' => Auth::user()->username ?? Auth::user()->email ?? 'unknown'
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
                'user' => Auth::user()->username ?? Auth::user()->email ?? 'unknown',
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
                    'user' => Auth::user()->username ?? Auth::user()->email ?? 'unknown'
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
                    'user' => Auth::user()->username ?? Auth::user()->email ?? 'unknown'
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
                ? (Auth::user()->username ?? Auth::user()->email ?? Auth::user()->name ?? 'system')
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
                    'user' => Auth::user()->username ?? Auth::user()->email ?? 'unknown'
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
                    'user' => Auth::user()->username ?? Auth::user()->email ?? 'unknown'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status. Must be Won, Lost, or Cancel'
                ], 400);
            }

            $submittedBy = Auth::check()
                ? (Auth::user()->username ?? Auth::user()->email ?? Auth::user()->name ?? 'system')
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

    public function saveTermsCondition(Request $request)
    {
        $bookingNo  = $request->input('booking_no');
        $termsText  = $request->input('terms_condition');
        $submittedBy = Auth::check()
            ? (Auth::user()->username ?? Auth::user()->email ?? Auth::user()->name ?? 'system')
            : 'system';

        if (!$bookingNo) {
            return response()->json([
                'success' => false,
                'message' => 'Booking number is required'
            ], 400);
        }

        try {
            $updated = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->update([
                    'terms_condition'  => $termsText,
                    'LastModifiedBy'   => $submittedBy,
                    'updatedon'        => now(),
                ]);

            if ($updated === 0) {
                Log::warning('saveTermsCondition - No rows updated', [
                    'booking_no'  => $bookingNo,
                    'user'        => $submittedBy,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No matching quotation found to update'
                ], 404);
            }

            Log::info('saveTermsCondition - Success', [
                'booking_no'  => $bookingNo,
                'user'        => $submittedBy,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Terms & Conditions saved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('saveTermsCondition - Failed', [
                'booking_no'  => $bookingNo,
                'error'       => $e->getMessage(),
                'file'        => $e->getFile(),
                'line'        => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save Terms & Conditions: ' . $e->getMessage()
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
        $module     = $request->input('module', 'Quotation');
        $subModule  = $request->input('sub_module');
        $officeCode = trim($request->input('office_code', ''));

        if (!$subModule || !$officeCode) {
            return response()->json([
                'success' => false,
                'message' => 'Sub module and office code are required'
            ], 400);
        }

        try {
            // ✅ First verify the office itself exists
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

            // ✅ TRIM on both sides to avoid trailing space mismatch
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
                'office_code'     => $officeCode,
                'module'          => $module,
                'sub_module'      => $subModule,
                'template_count'  => count($templates),
            ]);

            // ✅ Office exists but no templates configured for this module/submodule
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

    public function getCommunicationDescription(Request $request)
    {
        $module     = $request->input('module', 'Quotation');
        $subModule  = $request->input('sub_module');
        $officeCode = trim($request->input('office_code', ''));  // ✅ trim here too
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
        try {
            $bookingNo   = $request->input('booking_no');
            $toEmail     = $request->input('to_email');
            $fromEmail   = $request->input('from_email');
            $subject     = $request->input('subject');
            $body        = $request->input('body');
            $templateId  = $request->input('template_id');

            $submittedBy = Auth::check()
                ? (Auth::user()->username ?? Auth::user()->email ?? Auth::user()->name ?? 'system')
                : 'system';

            // ============================================
            // VALIDATE REQUIRED FIELDS
            // ============================================
            $validator = \Validator::make($request->all(), [
                'booking_no'  => 'required|string',
                'to_email'    => 'required|email',
                'from_email'  => 'required|email',
                'subject'     => 'required|string',
                'body'        => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors'  => $validator->errors()->toArray(),
                ], 422);
            }

            // ============================================
            // FETCH QUOTATION FOR CONTEXT COLUMNS
            // ============================================
            $quotation = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->first([
                    'ID',
                    'clientid',
                    'companyname',
                    'teamid',
                    'teamname',
                    'ServiceVerticalid',
                    'ServiceVerticalname',
                    'processid',
                ]);

            if (!$quotation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found for booking number: ' . $bookingNo,
                ], 404);
            }

            // ============================================
            // INSERT INTO email_emailscheduler
            // Nullable GUID columns left null until confirmed
            // ============================================
            DB::table('email_emailscheduler')->insert([
                'Id'            => DB::raw('NEWID()'),
                'SchedulerType' => 'QuotationEmail',
                'SubmittedOn'   => now(),
                'ClientID'      => null,  // GUID — confirm source column before mapping
                'ClientName'    => $quotation->companyname ?? null,
                'OfficeID'      => null,  // GUID — confirm source column before mapping
                'OfficeName'    => $quotation->teamname ?? null,
                'ServiceID'     => null,  // GUID — confirm source column before mapping
                'ProcessID'     => null,  // GUID — confirm source column before mapping
                'ServiceName'   => $quotation->ServiceVerticalname ?? null,
                'NoofShipment'  => null,
                'EmailFrom'     => $fromEmail,
                'EmailTo'       => $toEmail,
                'Subject'       => $subject,
                'BodyMessage'   => $body,
                'EmailIDCC'     => null,
                'Status'        => 'Pending',
                'SendRemark'    => null,
            ]);

            Log::info('sendEmailQuote - Scheduled successfully', [
                'booking_no'  => $bookingNo,
                'to_email'    => $toEmail,
                'from_email'  => $fromEmail,
                'subject'     => $subject,
                'user'        => $submittedBy,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email scheduled successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('sendEmailQuote - Failed', [
                'booking_no'    => $request->input('booking_no'),
                'error_message' => $e->getMessage(),
                'error_file'    => $e->getFile(),
                'error_line'    => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule email: ' . $e->getMessage(),
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
            $quotation = \App\Models\QuotationInfo::where('ID', $id)->first();

            if (!$quotation) {
                Log::warning('generatePdf - Quotation not found', ['id' => $id]);
                return response()->json(['success' => false, 'message' => 'Quotation not found'], 404);
            }

            // Resolve currency code
            $currencyCode = $quotation->currency ?? null;
            if (!empty($quotation->currency)) {
                $currency = DB::table('[FITSCMUAT].dbo.Cust_Currency')
                    ->where('Id', $quotation->currency)
                    ->first(['Code']);
                if ($currency) {
                    $currencyCode = $currency->Code;
                }
            }

            // Load charges
            $charges = DB::select("
            SELECT
                QC.Id                   AS QuoteChargeID,
                BC.Code                 AS ChargeCode,
                BC.Name                 AS ChargeName,
                QC.Quantity,
                QC.EstChargeRate        AS Rate,
                QC.EstChargeAmount      AS AmtInChgCurr,
                QC.EstBaseAmount        AS AmtInBaseCurr,
                QC.BaseExchRate         AS ExChangeRate,
                CC.Code                 AS ChgCurr,
                CB.Code                 AS BasedOnCode,
                CB.BasedOn              AS BasedOn,
                SU.Code                 AS BasedOnUOM,
                QC.Remarks              AS ChargeRemark,
                QC.IsRevenue,
                CP.Code                 AS PartyCode
            FROM Quote_Quotation_ChargeSheet QC WITH (NOLOCK)
            INNER JOIN Quote_QuotationInfo QI WITH (NOLOCK)
                ON QI.id = QC.QuotationInfoID
            LEFT JOIN [FITSCMUAT].dbo.Bill_ChargeCode BC WITH (NOLOCK)
                ON BC.Id = QC.ChargeCodeId
            LEFT JOIN [FITSCMUAT].dbo.Cust_Currency CC WITH (NOLOCK)
                ON CC.Id = QC.EstChargeCurrencyId
            LEFT JOIN Cust_ChargecodeBasedOn CB WITH (NOLOCK)
                ON CB.Id = QC.BasedOnId
            LEFT JOIN [FITSCMUAT].dbo.Sys_UOM SU WITH (NOLOCK)
                ON SU.Id = QC.UOMId
            LEFT JOIN [FITSCMUAT].dbo.Cust_Party CP WITH (NOLOCK)
                ON CP.Id = QC.PartyID
            WHERE QI.bookingno = ?
                AND QC.IsDeleted = 0
            ORDER BY QC.IsRevenue DESC, BC.Name ASC
        ", [$quotation->bookingno]);

            $revenueCharges = array_values(array_filter($charges, fn($c) =>  $c->IsRevenue));
            $costCharges    = array_values(array_filter($charges, fn($c) => !$c->IsRevenue));
            $totalRevenue   = array_sum(array_map(fn($c) => (float)$c->AmtInBaseCurr, $revenueCharges));
            $totalCost      = array_sum(array_map(fn($c) => (float)$c->AmtInBaseCurr, $costCharges));

            // Build PDF
            $pdf = \Barryvdh\DomPDF\Facade::loadView('quotation.pdf', [
                'quotation'      => $quotation,
                'currencyCode'   => $currencyCode,
                'revenueCharges' => $revenueCharges,
                'costCharges'    => $costCharges,
                'totalRevenue'   => $totalRevenue,
                'totalCost'      => $totalCost,
                'generatedDate'  => now()->format('d-m-Y'),
            ]);

            $pdf->setPaper('A4', 'portrait');
            $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
            $pdf->getDomPDF()->set_option('isRemoteEnabled', false);
            $pdf->getDomPDF()->set_option('defaultFont', 'arial');

            // Determine next version number from existing files
            $folder        = 'quotations/' . $quotation->bookingno;
            $existingFiles = \Storage::disk('public')->files($folder);
            $nextVersion   = count($existingFiles) + 1;

            $fileName    = 'Quotation_' . $quotation->bookingno . '_V' . $nextVersion . '_' . now()->format('Ymd_His') . '.pdf';
            $storagePath = $folder . '/' . $fileName;

            \Storage::disk('public')->put($storagePath, $pdf->output());

            Log::info('generatePdf - Saved', [
                'booking_no' => $quotation->bookingno,
                'version'    => $nextVersion,
                'path'       => $storagePath,
            ]);

            return response()->json([
                'success' => true,
                'version' => $nextVersion,
                'label'   => 'Version ' . $nextVersion,
                'url'     => \Storage::disk('public')->url($storagePath),
            ]);
        } catch (\Exception $e) {
            Log::error('generatePdf - Failed', [
                'id'    => $id,
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'PDF generation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getQuotationVersions($id)
    {
        try {
            $quotation = \App\Models\QuotationInfo::where('ID', $id)->first(['bookingno']);

            if (!$quotation) {
                return response()->json(['success' => false, 'message' => 'Quotation not found'], 404);
            }

            $folder = 'quotations/' . $quotation->bookingno;
            $files  = \Storage::disk('public')->files($folder);

            sort($files); // ascending so Version 1 is first

            $versions = array_values(array_map(function ($filePath, $index) {
                return [
                    'version' => $index + 1,
                    'label'   => 'Version ' . ($index + 1),
                    'url'     => \Storage::disk('public')->url($filePath),
                ];
            }, $files, array_keys($files)));

            return response()->json(['success' => true, 'versions' => $versions]);
        } catch (\Exception $e) {
            Log::error('getQuotationVersions - Failed', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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
                ->get(['SubmittedOn as sent_on', 'EmailTo as to_email', 'Status as status']);

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
            $result = DB::select("
                SELECT TOP 1 ER.ConvertToId as ExRate
                FROM [FITSCMUAT].dbo.Cust_ExchangeRate ER WITH (NOLOCK)
                INNER JOIN [FITSCMUAT].dbo.Cust_ExchangeRateOfficeMap OM WITH (NOLOCK)
                    ON OM.ExchangeRateId = ER.Id
                    AND OM.IsActive = 1
                INNER JOIN [FITSCMUAT].dbo.Cust_Office CO WITH (NOLOCK)
                    ON CO.Id = OM.OfficeId
                    AND CO.Code = ?
                    AND CO.IsActive = 1
                    AND CO.IsDeleted = 0
                INNER JOIN [FITSCMUAT].dbo.Cust_Currency CUR WITH (NOLOCK)
                    ON CUR.Id = ER.ConvertToId
                    AND CUR.Code = ?
                WHERE CAST(GETDATE() AS DATE) 
                    BETWEEN CAST(ER.EffectiveFromDate AS DATE) 
                    AND CAST(ER.EffectiveToDate AS DATE)
                AND ER.IsActive = 1
                AND ER.IsDeleted = 0
                ORDER BY ER.EffectiveFromDate DESC
            ", [$officeCode, $fromCurrency]);

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
        // Stub — wire up real logic when API details are confirmed
        return response()->json([
            'success' => false,
            'message' => 'Booking creation is not yet implemented.',
        ], 501);
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

        if (!$quotationId) {
            return response()->json(['success' => false, 'message' => 'quotation_id is required'], 422);
        }

        try {
            $pdo = DB::getPdo();
            $pdo->exec('SET NOCOUNT ON');

            $stmt = $pdo->prepare("EXEC dbo.sp_Quote_ServiceTemplateList ?, ?");
            $stmt->execute([$quotationId, $filter]);

            // ── FIX: loop through all result sets to get past PRINT @SQL ──
            $results = [];
            do {
                $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

                if (empty($rows)) {
                    continue;
                }

                // Check if this result set is a failure message
                $first = array_values((array) $rows[0])[0] ?? null;
                if (is_string($first) && str_starts_with($first, 'Failed')) {
                    return response()->json(['success' => false, 'message' => $first], 422);
                }

                // Check if rows have Id and Name — actual template data
                if (isset($rows[0]->Id) && isset($rows[0]->Name)) {
                    $results = $rows;
                    break;
                }
            } while ($stmt->nextRowset());

            return response()->json([
                'success'   => true,
                'templates' => $results,
            ]);
        } catch (\Exception $e) {
            Log::error('getServiceTemplates failed', [
                'quotation_id' => $quotationId,
                'error'        => $e->getMessage(),
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
        $includeCountry = $request->query('include_country', 0);
        $countryCode    = $request->query('country_code', '');

        try {
            $pdo = DB::getPdo();

            $pdo->exec('SET NOCOUNT ON');

            $stmt = $pdo->prepare("EXEC dbo.sp_LocationList 'Road', ?, ?, ?");
            $stmt->execute([(int) $includeCountry, $countryCode, $filter]);

            $results = $stmt->fetchAll(\PDO::FETCH_OBJ);

            return response()->json([
                'success'   => true,
                'locations' => $results,
            ]);
        } catch (\Exception $e) {
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
}
