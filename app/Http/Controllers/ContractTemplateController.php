<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContractTemplateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ContractTemplateController extends Controller
{
    private function sessionKey($quotationId): string
    {
        return "contract_template.quotation.{$quotationId}";
    }

    public function edit(Request $request, $quotationId)
    {
        $existing = Session::get($this->sessionKey($quotationId));

        $shipment = [
            'quote_no' => $request->query('quote_no', ''),
            'service' => $request->query('service', ''),
            'origin' => $request->query('origin', ''),
            'destination' => $request->query('destination', ''),
        ];

        $view = $request->ajax() ? 'quotation.contract-templates._content' : 'quotation.contract-templates.index';

        return view($view, compact('quotationId', 'existing', 'shipment'));
    }

    public function store(StoreContractTemplateRequest $request, $quotationId)
    {
        $validated = $request->validated();

        $markupConfig = [
            'markupType' => $validated['markup_type'],
            'amount' => $validated['markup_type'] === 'Fixed' ? (float) $validated['amount'] : null,
            'percentage' => $validated['markup_type'] === 'Percentage' ? (float) $validated['percentage'] : null,
        ];

        Session::put($this->sessionKey($quotationId), [
            'contract_template_id' => $validated['contract_template_id'],
            'contract_template_name' => $validated['contract_template_name'],
            'markup_config' => $markupConfig,
            'revenue_charges' => json_decode($request->input('revenue_charges', '[]'), true),
            'cost_charges' => json_decode($request->input('cost_charges', '[]'), true),
            'revenue_total' => (float) $request->input('revenue_total', 0),
            'cost_total' => (float) $request->input('cost_total', 0),
            'margin' => (float) $request->input('margin', 0),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contract template applied. Revenue and Cost totals updated.',
        ]);
    }

    public function charges($quotationId): JsonResponse
    {
        $link = Session::get($this->sessionKey($quotationId));

        if (!$link) {
            return response()->json(['success' => true, 'data' => null]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'contract_template_name' => $link['contract_template_name'],
                'revenue_charges' => $link['revenue_charges'] ?? [],
                'cost_charges' => $link['cost_charges'] ?? [],
                'revenue_total' => $link['revenue_total'] ?? 0,
                'cost_total' => $link['cost_total'] ?? 0,
                'margin' => $link['margin'] ?? 0,
            ],
        ]);
    }
}
