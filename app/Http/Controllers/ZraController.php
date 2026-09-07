<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ZraController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ── Page ─────────────────────────────────────────────────────
    // ── Page ─────────────────────────────────────────────────────
    public function index()
    {
        $activeOffice = session('active_office_code', 'ZM');

        Log::info('[ZRA] index() loaded', ['activeOffice' => $activeOffice]);

        return view('zambia.index', compact('activeOffice'));
    }

   public function datatable(Request $request)
{
    // $activeOffice = $request->query('office', session('active_office_code', 'ZM')); // TODO: re-enable for multi-country

    try {
        $query = DB::table('Bill_BillingDocumentHeader')
            // ->where('OfficeCode', $activeOffice) // TODO: re-enable for multi-country
            ->where(function ($q) {
                $q->whereNull('IsDeleted')->orWhere('IsDeleted', 0);
            })
            ->select([
                'DocNo',
                'DocumentType',
                DB::raw('PartyName as DebtorName'),
                'DocDate',
                'DocStatus',
                DB::raw('IntgrationRemark as Remarks'),
                DB::raw('Id as InvoiceId'),
                'BillerPinTINNo',
                'CustomerLicenseNo',
                'BillingCurrency',
                'TotalCharges',
                'TotalTaxes',
                'BillableTotalAmount',
            ]);

        return DataTables::of($query)
            ->addColumn('Action', function ($row) {
                $edit = '<button onclick="editInvoice(\'' . $row->InvoiceId . '\',\'edit\')"
                            class="btn btn-xs btn-warning mr-1">
                            <i class="fas fa-edit"></i> Edit TPIN
                         </button>';

                $retrigger = '<button onclick="editInvoice(\'' . $row->InvoiceId . '\',\'re-trigger\',\'' . $row->DocNo . '\')"
                                class="btn btn-xs btn-info">
                                <i class="fas fa-redo"></i> Re-trigger
                              </button>';

                return $edit . $retrigger;
            })
            ->editColumn('DocDate', function ($row) {
                return $row->DocDate ? \Carbon\Carbon::parse($row->DocDate)->format('d M Y') : '—';
            })
            ->editColumn('DocStatus', function ($row) {
                $color = match (true) {
                    str_contains(strtolower($row->DocStatus), 'failed')    => 'danger',
                    str_contains(strtolower($row->DocStatus), 'pending')   => 'warning',
                    str_contains(strtolower($row->DocStatus), 'submitted') => 'success',
                    str_contains(strtolower($row->DocStatus), 'accepted')  => 'info',
                    default => 'secondary',
                };
                return '<span class="badge badge-' . $color . '">' . $row->DocStatus . '</span>';
            })
            ->rawColumns(['Action', 'DocStatus'])
            ->make(true);

    } catch (\Exception $e) {
        Log::error('[ZRA] datatable() failed', [
            // 'activeOffice' => $activeOffice,
            'message' => $e->getMessage(),
            'trace'   => $e->getTraceAsString(),
        ]);
        return response()->json(['error' => 'Failed to load invoice data.'], 500);
    }
}

// ── Stats ─────────────────────────────────────────────────────
public function getStats(Request $request)
{
    // $activeOffice = $request->query('office', session('active_office_code', 'ZM')); // TODO: re-enable for multi-country

    try {
        $stats = DB::table('Bill_BillingDocumentHeader')
            // ->where('OfficeCode', $activeOffice) // TODO: re-enable for multi-country
            ->where(function ($q) {
                $q->whereNull('IsDeleted')->orWhere('IsDeleted', 0);
            })
            ->selectRaw("
                SUM(CASE WHEN DocStatus LIKE '%Failed%'    THEN 1 ELSE 0 END) as Failed,
                SUM(CASE WHEN DocStatus LIKE '%Pending%'   THEN 1 ELSE 0 END) as Pending,
                SUM(CASE WHEN DocStatus LIKE '%Submitted%' THEN 1 ELSE 0 END) as Submitted,
                COUNT(*) as Total
            ")
            ->first();

        Log::info('[ZRA] getStats() OK', [
            // 'activeOffice' => $activeOffice,
            'stats' => $stats,
        ]);

        return response()->json([$stats]);

    } catch (\Exception $e) {
        Log::error('[ZRA] getStats() failed', [
            // 'activeOffice' => $activeOffice,
            'message' => $e->getMessage(),
            'trace'   => $e->getTraceAsString(),
        ]);
        return response()->json(['error' => 'Failed to fetch stats.'], 500);
    }
}

    // ── Update Customer TPIN ──────────────────────────────────────
    public function updateInvoice(Request $request)
    {
        $request->validate([
            'InvoiceId'    => 'required',
            'ChangeOption' => 'required|string',
            'LevelOption'  => 'required|in:Master,Invoice',
            'OptionValue'  => 'required|string',
        ]);

        try {
            $result = DB::select('SET NOCOUNT ON; EXEC sp_ZRA_UpdateInvoice ?, ?, ?, ?', [
                $request->InvoiceId,
                $request->ChangeOption,
                $request->LevelOption,
                $request->OptionValue,
            ]);

            Log::info('[ZRA] updateInvoice() OK', [
                'InvoiceId'   => $request->InvoiceId,
                'LevelOption' => $request->LevelOption,
                'result'      => $result,
            ]);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('[ZRA] updateInvoice() failed', [
                'InvoiceId' => $request->InvoiceId,
                'message'   => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Failed to update TPIN.'], 500);
        }
    }

    // ── Re-trigger ────────────────────────────────────────────────
    public function reTrigger(string $docNo)
    {
        try {
            Log::info('[ZRA] reTrigger() initiated', ['DocNo' => $docNo]);

            $result = DB::select('SET NOCOUNT ON; EXEC sp_ZRA_ReTrigger ?', [$docNo]);

            Log::info('[ZRA] reTrigger() OK', [
                'DocNo'  => $docNo,
                'result' => $result,
            ]);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('[ZRA] reTrigger() failed', [
                'DocNo'   => $docNo,
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Re-trigger failed.'], 500);
        }
    }
}
