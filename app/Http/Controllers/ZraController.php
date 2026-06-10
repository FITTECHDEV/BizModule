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

// ── DataTables server-side ────────────────────────────────────
public function datatable(Request $request)
{
    $activeOffice = session('active_office_code', 'ZM');

    try {
        $query = DB::table('vw_ZRA_Invoices')
            ->where('OfficeCode', $activeOffice)
            ->select([
                'DocNo',
                'DocumentType as DocType',
                'DebtorName',
                'DocDate',
                'DocStatus',
                'SubmissionStatus as Remarks',
                'InvoiceId',
            ]);

        return DataTables::of($query)
            ->addColumn('Action', function ($row) {
                $edit = '<button onclick="editInvoice(' . $row->InvoiceId . ',\'edit\')"
                            class="btn btn-xs btn-warning mr-1">
                            <i class="fas fa-edit"></i> Edit TPIN
                         </button>';

                $retrigger = '<button onclick="editInvoice(' . $row->InvoiceId . ',\'re-trigger\',\'' . $row->DocNo . '\')"
                                class="btn btn-xs btn-info">
                                <i class="fas fa-redo"></i> Re-trigger
                              </button>';

                return $edit . $retrigger;
            })
            ->rawColumns(['Action'])
            ->make(true);

    } catch (\Exception $e) {
        Log::error('[ZRA] datatable() failed', [
            'activeOffice' => $activeOffice,
            'message'      => $e->getMessage(),
            'trace'        => $e->getTraceAsString(),
        ]);
        return response()->json(['error' => 'Failed to load invoice data.'], 500);
    }
}

// ── Stats ─────────────────────────────────────────────────────
public function getStats()
{
    $activeOffice = session('active_office_code', 'ZM');

    try {
        $stats = DB::select('SET NOCOUNT ON; EXEC sp_ZRA_GetStats ?', [$activeOffice]);

        Log::info('[ZRA] getStats() OK', [
            'activeOffice' => $activeOffice,
            'stats'        => $stats,
        ]);

        return response()->json($stats);

    } catch (\Exception $e) {
        Log::error('[ZRA] getStats() failed', [
            'activeOffice' => $activeOffice,
            'message'      => $e->getMessage(),
            'trace'        => $e->getTraceAsString(),
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
