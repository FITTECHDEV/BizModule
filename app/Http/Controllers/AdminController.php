<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ════════════════════════════════════════════════════════════════
    // SHARED FORM (single blade, two independent sections)
    // ════════════════════════════════════════════════════════════════

    public function entityToolsForm()
    {
        return view('admin.entity-tools');
    }

    // ════════════════════════════════════════════════════════════════
    // DELETE ENTITY FLOW
    // ════════════════════════════════════════════════════════════════

    public function entityDelete(Request $request)
    {
        $request->validate(['code' => 'required|string|max:50']);

        $code = strtoupper(trim($request->input('code')));

        try {
            // ── STEP 1: Preview record before delete ──────────────────
            $entity = DB::select("
                SELECT
                    Id, Code, Name, OperationalEntityType, LegalEntityType,
                    Status, OfficeCode, ClientCode, OtherPartyCode,
                    CreationRequestBy, CreationRequestOn, IsActive, IsDeleted
                FROM Cust_Entity WITH (NOLOCK)
                WHERE Code = ?
            ", [$code]);

            if (empty($entity)) {
                return back()->with('delete_error', "No entity found with code: {$code}");
            }

            // If user clicked Preview only
            if ($request->input('action') === 'preview') {
                return back()
                    ->with('delete_preview', $entity[0])
                    ->with('delete_code', $code);
            }

            // ── STEP 2: Delete with transaction ───────────────────────
            DB::transaction(function () use ($code) {

                $attachments = DB::delete("
                    DELETE FROM Cust_EntityAttachment WHERE RequestNo = ?
                ", [$code]);

                $history = DB::delete("
                    DELETE FROM Cust_EntityHistory WHERE RequestNo = ?
                ", [$code]);

                $main = DB::delete("
                    DELETE FROM Cust_Entity WHERE Code = ?
                ", [$code]);

                Log::warning('ADMIN ENTITY DELETE', [
                    'code'                => $code,
                    'deleted_by'          => auth()->user()->full_name ?? auth()->user()->email,
                    'attachments_deleted' => $attachments,
                    'history_deleted'     => $history,
                    'main_deleted'        => $main,
                ]);
            });

            // ── STEP 3: Verify ────────────────────────────────────────
            $verifyEntity      = DB::select("SELECT * FROM Cust_Entity WHERE Code = ?", [$code]);
            $verifyHistory     = DB::select("SELECT * FROM Cust_EntityHistory WHERE RequestNo = ?", [$code]);
            $verifyAttachment  = DB::select("SELECT * FROM Cust_EntityAttachment WHERE RequestNo = ?", [$code]);

            return back()->with('delete_success', [
                'code'                 => $code,
                'entity_remaining'     => count($verifyEntity),
                'history_remaining'    => count($verifyHistory),
                'attachment_remaining' => count($verifyAttachment),
            ]);

        } catch (\Exception $e) {
            Log::error('ADMIN ENTITY DELETE FAILED', [
                'code'  => $code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('delete_error', 'Delete failed: ' . $e->getMessage());
        }
    }

    // ════════════════════════════════════════════════════════════════
    // MARK AS CREATED (STATUS CHANGE) FLOW
    // ════════════════════════════════════════════════════════════════

    public function entityStatusChange(Request $request)
    {
        $request->validate(['code' => 'required|string|max:50']);

        $code = strtoupper(trim($request->input('code')));

        try {
            // ── STEP 1: Preview record before status change ───────────
            $entity = DB::select("
                SELECT
                    Id, Code, Name, OperationalEntityType, LegalEntityType,
                    Status, OfficeCode, ClientCode, OtherPartyCode,
                    CreationRequestBy, CreationRequestOn, IsActive, IsDeleted
                FROM Cust_Entity WITH (NOLOCK)
                WHERE Code = ?
            ", [$code]);

            if (empty($entity)) {
                return back()->with('status_error', "No entity found with code: {$code}");
            }

            // If user clicked Preview only
            if ($request->input('action') === 'preview') {
                return back()
                    ->with('status_preview', $entity[0])
                    ->with('status_code', $code);
            }

            $oldStatus = $entity[0]->Status;

            // ── STEP 2: Update status ──────────────────────────────────
            $updated = DB::update("
                UPDATE Cust_Entity SET Status = 'Created' WHERE Code = ?
            ", [$code]);

            Log::warning('ADMIN ENTITY STATUS CHANGE', [
                'code'         => $code,
                'old_status'   => $oldStatus,
                'new_status'   => 'Created',
                'changed_by'   => auth()->user()->full_name ?? auth()->user()->email,
                'rows_updated' => $updated,
            ]);

            return back()->with('status_success', [
                'code'       => $code,
                'old_status' => $oldStatus,
                'updated'    => $updated,
            ]);

        } catch (\Exception $e) {
            Log::error('ADMIN ENTITY STATUS CHANGE FAILED', [
                'code'  => $code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('status_error', 'Status change failed: ' . $e->getMessage());
        }
    }
}
