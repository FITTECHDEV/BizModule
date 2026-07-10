<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContractRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ContractController extends Controller
{
    private function sessionKey(string $contractId): string
    {
        return "contract.draft.{$contractId}";
    }

    /**
     * Show the Create Contract screen. Generates a draft ID if none supplied,
     * so Add Service / Add Charges can be saved incrementally into the same
     * session slot before the contract is finalised.
     */
    public function create(Request $request)
    {
        $contractId = $request->query('draft', Str::uuid()->toString());
        $existing = Session::get($this->sessionKey($contractId));

        // Mock counts — replace with real Contract::query() aggregates once
        // contracts are persisted to a DB table instead of session drafts.
        $stats = [
            'active' => 12,
            'pending_approval' => 4,
            'approval' => 0,
            'total' => 16,
        ];

        return view('contracts.create', compact('contractId', 'existing', 'stats'));
    }

    /**
     * Persist the full Contract -> Services[] -> Charges[] tree for this draft.
     * Services/Charges arrive as JSON strings (client builds the nested state
     * in JS, same pattern as ContractTemplateController::store).
     */
    public function store(StoreContractRequest $request, string $contract): JsonResponse
    {
        $validated = $request->validated();

        $services = json_decode($request->input('services', '[]'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Services payload was not valid JSON. Nothing was saved.',
                ],
                422,
            );
        }

        if (!is_array($services)) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Services payload must be an array. Nothing was saved.',
                ],
                422,
            );
        }

        Session::put($this->sessionKey($contract), [
            'contract_no' => $validated['contract_no'],
            'contract_type' => $validated['contract_type'],
            'status' => $validated['status'],
            'effective_from' => $validated['effective_from'],
            'effective_to' => $validated['effective_to'],
            'description' => $validated['description'] ?? null,
            'remark' => $validated['remark'] ?? null,
            'services' => $services,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contract draft saved.',
            'data' => ['contract_id' => $contract],
        ]);
    }

    public function show(string $contract): JsonResponse
    {
        $draft = Session::get($this->sessionKey($contract));

        if (!$draft) {
            return response()->json(['success' => true, 'data' => null]);
        }

        return response()->json(['success' => true, 'data' => $draft]);
    }
}
