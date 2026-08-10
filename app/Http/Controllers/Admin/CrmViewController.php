<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CrmViewController extends Controller
{
    /**
     * Fetch data from the PostgreSQL view vw_scmprofit_integration.
     *
     * @param  Request  $request
     * @param  int|null $bookingNo
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(Request $request, $bookingNo = null)
    {
        try {
            $connection = DB::connection('pgsql');
            $query = $connection->table('vw_scmprofit_integration');

            // ============================================================
            // DEBUG MODE 1: Show the raw SQL query (without executing it)
            // ============================================================
            if ($request->get('debug') === 'sql') {
                $sql = $query->toSql();
                $bindings = $query->getBindings();

                // Add the where clause for the debug view
                if ($bookingNo) {
                    $queryWithWhere = $connection->table('vw_scmprofit_integration')
                        ->where('bookingno', $bookingNo);
                    $sql = $queryWithWhere->toSql();
                    $bindings = $queryWithWhere->getBindings();
                }

                return response()->json([
                    'debug_mode' => 'sql',
                    'sql' => $sql,
                    'bindings' => $bindings,
                    'full_query' => vsprintf(str_replace('?', "'%s'", $sql), $bindings),
                ]);
            }

            // ============================================================
            // DEBUG MODE 2: Fetch ALL records, then search for 11232 manually
            // ============================================================
            if ($request->get('debug') === 'all') {
                // Get EVERY row from the view
                $allResults = $query->get();

                // Try to find the booking number in various ways
                $foundExact = null;
                $foundString = null;
                $foundTrimmed = null;
                $foundLike = collect();

                if ($bookingNo) {
                    // 1. Exact match (as passed)
                    $foundExact = $allResults->firstWhere('bookingno', $bookingNo);

                    // 2. Match as string (in case it's stored as text)
                    $foundString = $allResults->firstWhere('bookingno', (string) $bookingNo);

                    // 3. Match with trimmed spaces
                    $foundTrimmed = $allResults->first(function ($item) use ($bookingNo) {
                        return isset($item->bookingno) && trim($item->bookingno) == trim((string) $bookingNo);
                    });

                    // 4. Case-insensitive or partial match (if column is text)
                    $foundLike = $allResults->filter(function ($item) use ($bookingNo) {
                        return isset($item->bookingno) && stripos($item->bookingno, (string) $bookingNo) !== false;
                    });
                }

                return response()->json([
                    'debug_mode' => 'all_records',
                    'total_records_in_view' => $allResults->count(),
                    'sample_data_first_5' => $allResults->take(5)->toArray(),
                    'sample_keys' => $allResults->isEmpty() ? [] : array_keys((array) $allResults->first()),
                    'search_results' => [
                        'booking_number_searched' => $bookingNo,
                        'found_exact_match' => $foundExact ? true : false,
                        'found_as_string_match' => $foundString ? true : false,
                        'found_after_trimming' => $foundTrimmed ? true : false,
                        'found_as_partial_match' => $foundLike->count(),
                        'partial_matches' => $foundLike->take(5)->values()->toArray(),
                    ],
                    'raw_all_data' => $allResults->toArray(), // Full dump (might be huge, but useful)
                ]);
            }

            // ============================================================
            // NORMAL MODE (your original logic)
            // ============================================================
            if ($bookingNo) {
                $query->where('bookingno', $bookingNo);
            }

            $results = $query->get();

            if ($results->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No records found for the given booking number.',
                ]);
            }

            return response()->json([
                'success' => true,
                'count' => $results->count(),
                'data' => $results->toArray(),
            ]);
        } catch (\Exception $e) {
            Log::error('CrmViewController - view failed', [
                'booking_no' => $bookingNo,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch view data: ' . $e->getMessage(),
            ], 500);
        }
    }
}