<?php

namespace App\Http\Controllers;

use App\Services\CrmSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CrmSyncController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * GET /crm/sync
     * Show the sync dashboard page.
     */
    public function index()
    {
        return view('crm.sync');
    }

    /**
     * POST /crm/sync/run
     * Trigger the sync via the web. Returns JSON.
     *
     * Body params (all optional):
     *   dry_run : 1|0    — preview only, no writes (default: 0)
     *   limit   : int    — max records to process
     *   booking : string — sync a single booking number
     */
    public function run(Request $request, CrmSyncService $sync)
    {
        $dryRun    = (bool) $request->input('dry_run', 0);
        $limit     = $request->input('limit') ? (int) $request->input('limit') : null;
        $bookingNo = $request->input('booking') ?: null;

        $triggeredBy = Auth::user()->displayName ?? Auth::user()->Email ?? 'system';

        \Illuminate\Support\Facades\Log::info('CRM Sync - Triggered via web', [
            'user'      => $triggeredBy,
            'dry_run'   => $dryRun,
            'limit'     => $limit,
            'booking'   => $bookingNo,
        ]);

        $result = $sync->run($dryRun, $limit, $bookingNo, $triggeredBy);

        $success = $result['failed'] === 0;

        return response()->json([
            'success' => $success,
            'dry_run' => $dryRun,
            'summary' => [
                'total'    => $result['total'],
                'inserted' => $result['inserted'],
                'updated'  => $result['updated'],
                'skipped'  => $result['skipped'],
                'failed'   => $result['failed'],
            ],
            'errors'       => $result['errors'],
            'verification' => $result['verification'],
        ]);
    }

    /**
     * GET /crm/sync/history
     * Returns the last N sync run summaries from the history file.
     */
    public function history(Request $request, CrmSyncService $sync)
    {
        $limit   = min((int) $request->input('limit', 20), 50);
        $history = $sync->getHistory($limit);

        return response()->json(['history' => $history]);
    }
}