<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RAIntegrationController extends Controller
{
    /**
     * Display the RA Integration Dashboard
     */
    public function dashboard(Request $request)
    {
        $view = $request->get('view', 'ar'); // Default to AR
        $office = $request->get('office', 'all');

        // Default to TODAY, not start of month
        $dateFrom = $request->get('date_from', now()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        return view('ra-integration.dashboard', [
            'view' => $view,
            'office' => $office,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'dashboardData' => [
                'cards' => $view === 'ar' ? [
                    'failed_verification' => 0,
                    'verified' => 0,
                    'failed_ra_integration' => 0,
                    'pending_d365_integration' => 0,
                    'failed_d365_integration' => 0,
                    'success' => 0,
                ] : [
                    'failed_verification' => 0,
                    'verified' => 0,
                    'failed_d365_integration' => 0,
                    'success' => 0,
                ]
            ],
            'offices' => $this->getOffices(),
        ]);
    }

    /**
     * Get dashboard data via AJAX
     */
    public function getDashboardData(Request $request)
    {
        $view = $request->get('view', 'ar');
        $office = $request->get('office', 'all');
        $dateFrom = $request->get('date_from', now()->format('Y-m-d')); // Changed
        $dateTo = $request->get('date_to', now()->format('Y-m-d')); // Changed

        try {
            $dashboardData = $this->fetchDashboardData($view, $office, $dateFrom, $dateTo);

            return response()->json([
                'success' => true,
                'data' => $dashboardData
            ]);
        } catch (\Exception $e) {
            Log::error('RA Integration Dashboard Data Error', [
                'message' => $e->getMessage(),
                'view' => $view,
                'office' => $office
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard data'
            ], 500);
        }
    }

    private function fetchDashboardData(string $view, string $office, string $dateFrom, string $dateTo): array
    {
        return [
            'cards' => $view === 'ar' ? [
                'failed_verification' => 0,
                'verified' => 0,
                'failed_ra_integration' => 0,
                'pending_d365_integration' => 0,
                'failed_d365_integration' => 0,
                'success' => 0,
            ] : [
                'failed_verification' => 0,
                'verified' => 0,
                'failed_d365_integration' => 0,
                'success' => 0,
            ],
            'invoices' => []
        ];
    }

    /**
     * Get mock dashboard data for UI development
     */
    private function getMockDashboardData(string $view): array
    {
        if ($view === 'ar') {
            return [
                'cards' => [
                    'failed_verification' => 12,
                    'verified' => 45,
                    'failed_ra_integration' => 8,
                    'pending_d365_integration' => 23,
                    'failed_d365_integration' => 5,
                    'success' => 156,
                ],
                'invoices' => []
            ];
        } else {
            return [
                'cards' => [
                    'failed_verification' => 7,
                    'verified' => 18,
                    'failed_d365_integration' => 3,
                    'success' => 42,
                ],
                'invoices' => []
            ];
        }
    }
    /**
     * Parse card counts from SP results
     */
    private function parseCardData($results, string $view): array
    {
        if ($view === 'ar') {
            return [
                'failed_verification' => $results[0]->failed_verification ?? 0,
                'verified' => $results[0]->verified ?? 0,
                'failed_ra_integration' => $results[0]->failed_ra_integration ?? 0,
                'pending_d365_integration' => $results[0]->pending_d365_integration ?? 0,
                'failed_d365_integration' => $results[0]->failed_d365_integration ?? 0,
                'success' => $results[0]->success ?? 0,
            ];
        } else {
            return [
                'failed_verification' => $results[0]->failed_verification ?? 0,
                'verified' => $results[0]->verified ?? 0,
                'failed_d365_integration' => $results[0]->failed_d365_integration ?? 0,
                'success' => $results[0]->success ?? 0,
            ];
        }
    }

    /**
     * Parse invoice details from SP results
     */
    private function parseInvoiceData($results): array
    {
        // Assuming SP returns invoice rows
        // Structure depends on your SP design
        return collect($results)->map(function ($invoice) {
            return [
                'ra_id' => $invoice->ra_id,
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $invoice->customer_name,
                'amount' => $invoice->amount,
                'status' => $invoice->status,
                'created_at' => $invoice->created_at,
                'office' => $invoice->office,
            ];
        })->toArray();
    }

    /**
     * Get list of offices
     */
    private function getOffices(): array
    {
        // Replace with your actual office retrieval logic
        return [
            ['id' => 'all', 'name' => 'All Offices'],
            ['id' => 'DAR', 'name' => 'Dar es Salaam'],
            ['id' => 'ARU', 'name' => 'Arusha'],
            ['id' => 'MWZ', 'name' => 'Mwanza'],
            // Add more offices
        ];
    }

    public static function getCardMetrics(): array
    {
        return [
            'ra_pending_d365' => 0,
            'ra_total_failed' => 0,
            'ra_success_today' => 0,
        ];
    }

    /**
     * Get invoices data for DataTable (AJAX)
     */
    public function getInvoicesData(Request $request)
    {
        $view = $request->get('view', 'ar');
        $office = $request->get('office', 'all');
        $dateFrom = $request->get('date_from', now()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $status = $request->get('status'); // Optional: for card filtering

        try {
            $invoices = $this->fetchInvoicesData($view, $office, $dateFrom, $dateTo, $status);

            return response()->json([
                'data' => $invoices
            ]);
        } catch (\Exception $e) {
            Log::error('RA Integration Invoices Data Error', [
                'message' => $e->getMessage(),
                'view' => $view,
                'office' => $office,
                'status' => $status
            ]);

            return response()->json([
                'data' => []
            ], 500);
        }
    }

    /**
     * Fetch invoices from database
     */
    private function fetchInvoicesData(string $view, string $office, string $dateFrom, string $dateTo, ?string $status = null): array
    {
        // TODO: Replace with actual stored procedure call
        // For now, return empty array to make the table load

        /*
    // Example of how you'll call your SP later:
    $results = DB::select('EXEC sp_GetRAInvoices ?, ?, ?, ?, ?', [
        $view,
        $office === 'all' ? null : $office,
        $dateFrom,
        $dateTo,
        $status
    ]);
    
    return $this->parseInvoiceData($results);
    */

        return []; // Empty for now - table will load with "No data available"
    }
}
