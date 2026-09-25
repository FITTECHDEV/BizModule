@extends('adminlte::page')

@section('title', 'Edit Contract')

@section('content')
    <div class="container-fluid" id="contractEditContent">

        {{-- ── Header (Contract Info) ─────────────────────────────────────── --}}
        <div class="cc-box cc-box-compact mb-3">
            <div class="cc-box-title d-flex align-items-center">
                <span id="ceContractNo">{{ $contractInfo->ContractNo }}</span>
                <button type="button" class="btn btn-outline-primary btn-sm ml-auto ce-copy-contract-btn">
                    <i class="fas fa-copy"></i> Copy Contract
                </button>
                <a href="{{ route('contracts.view', $contractInfo->Id) }}" class="btn btn-secondary btn-sm ml-2">
                    <i class="fas fa-arrow-left"></i> Back to View
                </a>
            </div>

            <div class="cc-info-grid">
                <div class="cc-info-col">
                    <div class="cc-info-row">
                        <span class="cc-lbl">Contract Name</span>
                        <span class="cc-val">{{ $contractInfo->ContractName }}</span>
                    </div>
                    <div class="cc-info-row">
                        <span class="cc-lbl">Type</span>
                        <span class="cc-val" title="Contract Type cannot be changed after creation">
                            {{ $contractInfo->ContractType ?? '—' }}
                        </span>
                    </div>
                    <div class="cc-info-row">
                        <span class="cc-lbl">Office</span>
                        <span class="cc-val">{{ $contractInfo->OfficeCode ?? '—' }}</span>
                    </div>
                    <div class="cc-info-row">
                        <span class="cc-lbl">Status</span>
                        <span class="cc-val">{{ $contractInfo->Status ?? '—' }}</span>
                    </div>
                </div>
                <div class="cc-info-col">
                    <div class="cc-info-row">
                        <span class="cc-lbl">Effective From</span>
                        <span class="cc-val ce-hdr-view"
                            data-field="effectiveFrom">{{ \Carbon\Carbon::parse($contractInfo->EffectiveFrom)->format('Y-m-d') }}</span>
                        <input type="date"
                            class="form-control form-control-sm cc-input ce-hdr-field ce-hdr-effectivefrom"
                            value="{{ \Carbon\Carbon::parse($contractInfo->EffectiveFrom)->format('Y-m-d') }}"
                            style="display:none;">
                    </div>
                    <div class="cc-info-row">
                        <span class="cc-lbl">Effective To</span>
                        <span class="cc-val ce-hdr-view"
                            data-field="effectiveTo">{{ \Carbon\Carbon::parse($contractInfo->EffectiveTo)->format('Y-m-d') }}</span>
                        <input type="date" class="form-control form-control-sm cc-input ce-hdr-field ce-hdr-effectiveto"
                            value="{{ \Carbon\Carbon::parse($contractInfo->EffectiveTo)->format('Y-m-d') }}"
                            style="display:none;">
                    </div>
                    <div class="cc-info-row">
                        <span class="cc-lbl">Remark</span>
                        <span class="cc-val ce-hdr-view" data-field="remark">{{ $contractInfo->Remark }}</span>
                        <input type="text" class="form-control form-control-sm cc-input ce-hdr-field ce-hdr-remark"
                            value="{{ $contractInfo->Remark }}" style="display:none;">
                    </div>
                    <div class="d-flex justify-content-end mt-2">
                        <button type="button" class="btn btn-sm btn-outline-primary ce-edit-header-btn">
                            <i class="fas fa-pen"></i> Edit
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary mr-2 ce-cancel-header-btn"
                            style="display:none;">Cancel</button>
                        <button type="button" class="btn btn-sm btn-success ce-save-header-btn"
                            style="display:none;">Save</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Services ────────────────────────────────────────────────────── --}}
        @if ($services->count())
            <div class="cc-section-block mb-3">
                <div class="cc-section-block-header cc-header-revenue">
                    <strong>Services</strong>
                    <span class="ml-auto mr-3 cc-totals-inline">{{ $services->count() }} total</span>

                    <button type="button" class="btn btn-sm btn-light ce-add-service-btn">
                        <i class="fas fa-plus"></i> Add Service
                    </button>
                </div>

                @foreach ($services as $service)
                    <div class="cc-group">
                        <div class="cc-group-header collapsed" data-target="serviceBody{{ $loop->index }}">
                            <span class="cc-caret">&#9660;</span>
                            <span>{{ $service->Service }} — {{ $service->Process }}</span>
                            <span class="cc-group-total">{{ $service->ShipmentMode ?? '—' }}</span>
                        </div>
                        <div class="cc-group-body collapsed" id="serviceBody{{ $loop->index }}">

                            <input type="hidden" class="ce-svc-id" value="{{ $service->Id }}">
                            <div class="cc-info-grid" style="margin-bottom:10px;">
                                <div class="cc-info-col">
                                    <div class="ctr-field mb-2 d-flex" style="gap:8px;">
                                        <div style="flex:1;"><label class="ctr-lbl">Service</label>
                                            <select class="form-control form-control-sm ce-svc-locked ce-svc-service"
                                                disabled>
                                                <option value="">-- Select --</option>
                                            </select>
                                        </div>
                                        <div style="flex:1;"><label class="ctr-lbl">Process</label>
                                            <select class="form-control form-control-sm ce-svc-locked ce-svc-process"
                                                disabled>
                                                <option value="">-- Select --</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="ctr-field mb-2"><label class="ctr-lbl">IncoTerm</label>
                                        <select class="form-control form-control-sm ce-svc-field ce-svc-incoterm" disabled>
                                            <option value="">-- Select --</option>
                                        </select>
                                    </div>
                                    <div class="ctr-field mb-2"><label class="ctr-lbl">Service Class</label>
                                        <select class="form-control form-control-sm ce-svc-field ce-svc-serviceclass"
                                            disabled>
                                            <option value="">-- Select --</option>
                                        </select>
                                    </div>

                                    {{-- Origin (left) / Destination (right) grouping --}}
                                    <div class="ctr-field d-flex" style="gap:8px;">
                                        <div style="flex:1;"><label class="ctr-lbl">Country of Origin</label>
                                            <select class="form-control form-control-sm ce-svc-field ce-svc-coo" disabled>
                                                <option value="">-- Select --</option>
                                            </select>
                                        </div>
                                        <div style="flex:1;"><label class="ctr-lbl">Country of Destination</label>
                                            <select class="form-control form-control-sm ce-svc-field ce-svc-cod" disabled>
                                                <option value="">-- Select --</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="ctr-field d-flex mt-2" style="gap:8px;">
                                        <div style="flex:1;"><label class="ctr-lbl">POL</label>
                                            <input type="text"
                                                class="form-control form-control-sm ce-svc-field ce-svc-pol"
                                                value="{{ $service->POL }}" placeholder="Type at least 2 characters..."
                                                disabled>
                                        </div>
                                        <div style="flex:1;"><label class="ctr-lbl">POD</label>
                                            <input type="text"
                                                class="form-control form-control-sm ce-svc-field ce-svc-pod"
                                                value="{{ $service->POD }}" placeholder="Type at least 2 characters..."
                                                disabled>
                                        </div>
                                    </div>
                                    <div class="ctr-field d-flex mt-2" style="gap:8px;">
                                        <div style="flex:1;"><label class="ctr-lbl">Place of Receipt</label>
                                            <input type="text"
                                                class="form-control form-control-sm ce-svc-field ce-svc-por"
                                                value="{{ $service->POR }}" placeholder="Type at least 2 characters..."
                                                disabled>
                                        </div>
                                        <div style="flex:1;"><label class="ctr-lbl">Place of Delivery</label>
                                            <input type="text"
                                                class="form-control form-control-sm ce-svc-field ce-svc-poe"
                                                value="{{ $service->POE }}" placeholder="Type at least 2 characters..."
                                                disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="cc-info-col">
                                    <div class="ctr-field mb-2"><label class="ctr-lbl">Shipment Type</label>
                                        <select class="form-control form-control-sm ce-svc-field ce-svc-shipmenttype"
                                            disabled>
                                            <option value="">-- Select --</option>
                                        </select>
                                    </div>
                                    <div class="ctr-field mb-2"><label class="ctr-lbl">Shipment Mode</label>
                                        <select class="form-control form-control-sm ce-svc-field ce-svc-shipmentmode"
                                            disabled>
                                            <option value="">-- Select --</option>
                                        </select>
                                    </div>
                                    <div class="ctr-field mb-2"><label class="ctr-lbl">Movement Type</label>
                                        <select class="form-control form-control-sm ce-svc-field ce-svc-movementtype"
                                            disabled>
                                            <option value="">-- Select --</option>
                                        </select>
                                    </div>
                                    <div class="ctr-field mb-2"><label class="ctr-lbl">Forwarder</label>
                                        <select class="form-control form-control-sm ce-svc-field ce-svc-forwarder"
                                            disabled>
                                            <option value="">-- Select --</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Service Action Buttons --}}
                            <div class="ce-svc-actions-row">
                                <button type="button" class="btn btn-sm btn-outline-danger ce-delete-service-btn"
                                    data-svc="{{ $service->Id }}">
                                    <i class="fas fa-trash"></i> Delete Service
                                </button>

                                <button type="button" class="btn btn-sm btn-outline-secondary ce-copy-service-btn"
                                    data-svc="{{ $service->Id }}">
                                    <i class="fas fa-copy"></i> Copy Service
                                </button>

                                <button type="button" class="btn btn-sm btn-outline-primary ce-edit-service-btn">
                                    <i class="fas fa-pen"></i> Edit
                                </button>

                                <button type="button" class="btn btn-sm btn-secondary ce-cancel-service-btn"
                                    style="display:none;">
                                    Cancel
                                </button>

                                <button type="button" class="btn btn-sm btn-success ce-save-service-btn"
                                    style="display:none;">
                                    Save
                                </button>
                            </div>

                            {{-- Charges Header --}}
                            <div class="ce-charges-header-row">
                                <strong class="ctr-lbl mb-0">Charges</strong>
                                <button type="button" class="btn btn-sm btn-outline-primary ce-add-charge-btn"
                                    data-svc="{{ $service->Id }}">
                                    <i class="fas fa-plus"></i> Add Charge
                                </button>
                            </div>

                            @php
                                $serviceCharges = $service->Charges ?? collect();

                                $revenueCharges = $serviceCharges
                                    ->filter(function ($c) {
                                        return trim((string) ($c->RevenueOrCost ?? 'Revenue')) !== 'Cost';
                                    })
                                    ->values();

                                $costCharges = $serviceCharges
                                    ->filter(function ($c) {
                                        return trim((string) ($c->RevenueOrCost ?? 'Revenue')) === 'Cost';
                                    })
                                    ->values();

                                $revenueTotal = $revenueCharges->sum(function ($c) {
                                    return (float) ($c->Rate ?? 0);
                                });
                                $costTotal = $costCharges->sum(function ($c) {
                                    return (float) ($c->Rate ?? 0);
                                });

                                $sections = [
                                    [
                                        'key' => 'rev',
                                        'title' => 'Revenue',
                                        'charges' => $revenueCharges,
                                        'total' => $revenueTotal,
                                        'emptyMsg' => 'No revenue charges.',
                                    ],
                                    [
                                        'key' => 'cost',
                                        'title' => 'Cost',
                                        'charges' => $costCharges,
                                        'total' => $costTotal,
                                        'emptyMsg' => 'No cost charges.',
                                    ],
                                ];
                            @endphp

                            <div class="ce-charges-sections">
                                @foreach ($sections as $section)
                                    <div class="ce-charge-section">
                                        <div class="ce-charge-section-header"
                                            data-target="ceChg{{ ucfirst($section['key']) }}-{{ $service->Id }}">
                                            <span class="ce-sec-title">{{ $section['title'] }}</span>
                                            <span class="ce-sec-caret">&#9660;</span>
                                            <span class="ce-sec-total">Total:
                                                {{ number_format($section['total'], 2) }}</span>
                                        </div>
                                        <div class="ce-charge-section-body"
                                            id="ceChg{{ ucfirst($section['key']) }}-{{ $service->Id }}">
                                            @if ($section['charges']->count())
                                                @php
                                                    $byCat = $section['charges']->groupBy(function ($c) {
                                                        $cat = trim((string) ($c->ChargeCatagory ?? ''));
                                                        return $cat !== '' ? $cat : 'Other';
                                                    });
                                                @endphp
                                                <div class="ce-charges-table-wrap">
                                                    <table class="cc-table ce-charges-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Nature</th>
                                                                <th>Code</th>
                                                                <th>Description</th>
                                                                <th>Rate Type</th>
                                                                <th>Rate</th>
                                                                <th>Curr</th>
                                                                <th style="width:90px;"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($byCat as $category => $categoryCharges)
                                                                <tr class="ce-charge-category-row">
                                                                    <td colspan="7">
                                                                        <i class="fas fa-folder-open mr-1"></i>
                                                                        {{ $category }}
                                                                        <span class="text-muted ml-1">
                                                                            ({{ $categoryCharges->count() }})
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                                @foreach ($categoryCharges as $charge)
                                                                    @php
                                                                        $revenueOrCost = trim(
                                                                            (string) ($charge->RevenueOrCost ??
                                                                                'Revenue'),
                                                                        );
                                                                        if ($revenueOrCost === '') {
                                                                            $revenueOrCost = 'Revenue';
                                                                        }
                                                                        $incrementBy = isset($charge->IncrementBy)
                                                                            ? (float) $charge->IncrementBy
                                                                            : 0;
                                                                        $hasMarkup = $incrementBy > 0;

                                                                        $chargeJson = [
                                                                            'chargeId' => $charge->Id ?? null,
                                                                            'chargeCategory' =>
                                                                                $charge->ChargeCatagory ?? '',
                                                                            'chargeCode' => $charge->ChargeCode ?? '',
                                                                            'chargeDescription' =>
                                                                                $charge->ChargeDescription ?? '',
                                                                            'chargeBasedOn' =>
                                                                                $charge->ChargeBasedOn ?? '',
                                                                            'chargeBasedOnUOM' =>
                                                                                $charge->ChargeBasedOnUOM ?? '',
                                                                            'chargeCurr' => $charge->ChargeCurr ?? '',
                                                                            'minAmount' => $charge->MinAmount ?? null,
                                                                            'maxAmount' => $charge->MaxAmount ?? null,
                                                                            'rateType' => $charge->RateType ?? '',
                                                                            'rate' => $charge->Rate ?? null,
                                                                            'formula' => $charge->Formula ?? null,
                                                                            'rateSlab' =>
                                                                                isset($charge->RateSlabJson) &&
                                                                                $charge->RateSlabJson
                                                                                    ? json_decode(
                                                                                        $charge->RateSlabJson,
                                                                                        true,
                                                                                    )
                                                                                    : null,
                                                                            'chargeAction' => $hasMarkup ? 'M' : 'R',
                                                                            'chargeNature' => $revenueOrCost,
                                                                            'markupType' =>
                                                                                $charge->IncrementType ?? null,
                                                                            'markupRateType' =>
                                                                                $charge->IncrementType ?? null,
                                                                            'markupRate' =>
                                                                                $charge->IncrementBy ?? null,
                                                                            'markupFormula' => null,
                                                                            'markupRateSlab' => null,
                                                                            'isAccruable' => '0',
                                                                            'isReimbursable' => '0',
                                                                        ];

                                                                        if ($hasMarkup) {
                                                                            $natureLabel = $revenueOrCost . ' + Markup';
                                                                            $natureClass = 'ce-nature-cost-markup';
                                                                        } elseif ($revenueOrCost === 'Cost') {
                                                                            $natureLabel = 'Cost';
                                                                            $natureClass = 'ce-nature-cost';
                                                                        } else {
                                                                            $natureLabel = 'Revenue';
                                                                            $natureClass = 'ce-nature-revenue';
                                                                        }
                                                                    @endphp
                                                                    <tr class="ce-charge-row">
                                                                        <td>
                                                                            <span
                                                                                class="ce-nature-badge {{ $natureClass }}">{{ $natureLabel }}</span>
                                                                        </td>
                                                                        <td>{{ $chargeJson['chargeCode'] }}</td>
                                                                        <td>{{ $chargeJson['chargeDescription'] }}</td>
                                                                        <td>{{ $chargeJson['rateType'] }}</td>
                                                                        <td>{{ $chargeJson['rate'] }}</td>
                                                                        <td>{{ $chargeJson['chargeCurr'] }}</td>
                                                                        <td class="text-right">
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-outline-primary ce-edit-charge"
                                                                                data-svc="{{ $service->Id }}"
                                                                                data-charge-id="{{ $chargeJson['chargeId'] }}"
                                                                                data-charge='@json($chargeJson)'>
                                                                                <i class="fas fa-pen"></i>
                                                                            </button>
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-outline-danger ce-delete-charge"
                                                                                data-svc="{{ $service->Id }}"
                                                                                data-charge-id="{{ $chargeJson['chargeId'] }}">
                                                                                <i class="fas fa-trash"></i>
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="cc-empty-msg">{{ $section['emptyMsg'] }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Inline edit form: populated by JS when .ce-edit-charge is clicked --}}
                            <div id="chargeform-{{ $service->Id }}" class="ctr-subform mt-3" style="display:none;">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="cc-section-block mb-3">
                <div class="cc-section-block-header cc-header-revenue">
                    <strong>Services</strong>
                    <button type="button" class="btn btn-sm btn-light ml-auto ce-add-service-btn">
                        <i class="fas fa-plus"></i> Add Service
                    </button>
                </div>
                <div class="cc-empty-msg">No services added.</div>
            </div>
        @endif
        <div class="modal fade" id="addChargeModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Charge</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="addChargeModalBody"></div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="addServiceModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Service</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="addServiceModalBody"></div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <style>
        .cc-box {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 16px;
            background: #fff;
        }

        .cc-box-compact {
            padding: 10px 16px;
        }

        .cc-box-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #4B49AC;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .cc-info-grid {
            display: flex;
            gap: 24px;
        }

        .cc-info-col {
            flex: 1;
        }

        .cc-info-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .cc-lbl {
            font-size: 0.78rem;
            color: #495057;
            min-width: 120px;
        }

        .cc-val {
            font-size: 0.8rem;
            color: #212529;
            font-weight: 500;
        }

        .cc-input {
            max-width: 260px;
        }

        .cc-section-block {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            overflow: hidden;
        }

        .cc-section-block-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            color: #fff;
            font-size: 0.9rem;
        }

        .cc-header-revenue {
            background: #4B49AC;
        }

        .cc-totals-inline {
            font-size: 0.8rem;
            opacity: 0.95;
        }

        .cc-group {
            border-top: 1px solid #eee;
        }

        .cc-group-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            background: #f4f4fb;
            cursor: pointer;
            font-size: 0.82rem;
            font-weight: 700;
            color: #4B49AC;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .cc-group-header .cc-caret {
            transition: transform 0.15s ease;
        }

        .cc-group-header.collapsed .cc-caret {
            transform: rotate(-90deg);
        }

        .cc-group-header .cc-group-total {
            margin-left: auto;
            font-weight: 500;
            text-transform: none;
            color: #495057;
        }

        .cc-group-body {
            padding: 10px 12px;
        }

        .cc-group-body.collapsed {
            display: none;
        }

        .cc-table-wrap {
            overflow-x: auto;
        }

        .cc-table {
            width: 100%;
            margin-bottom: 0;
            font-size: 0.8rem;
            border-collapse: collapse;
        }

        .cc-table th,
        .cc-table td {
            border: 1px solid #dee2e6;
            padding: 5px 7px;
            vertical-align: middle;
        }

        .cc-table thead th {
            background: #fff;
            font-weight: 600;
            color: #6c757d;
            font-size: 0.72rem;
            text-transform: uppercase;
        }

        .cc-empty-msg {
            text-align: center;
            color: #888;
            padding: 10px;
            font-size: 0.8rem;
        }

        .ctr-field {
            margin-bottom: 4px;
        }

        .ctr-lbl {
            font-size: .78rem;
            color: #495057;
            margin-bottom: 2px;
            display: block;
        }

        .ctr-subform {
            border: 1px dashed #4B49AC;
            border-radius: 4px;
            padding: 14px;
            background: #f8f8fd;
        }

        .ctr-slab-table {
            width: 100%;
            font-size: .78rem;
            margin-top: 6px;
        }

        .ctr-slab-table th,
        .ctr-slab-table td {
            padding: 3px 5px;
        }

        .ctr-slab-above-label {
            font-style: italic;
            color: #6c757d;
            padding-left: 4px;
        }

        .ctr-slab-row-remove {
            cursor: pointer;
            color: #dc3545;
            font-size: 1.4rem;
            line-height: 1;
        }

        .ctr-formula-cols {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .ctr-formula-col select[multiple] {
            height: auto;
        }

        @media (max-width: 991px) {
            .cc-info-grid {
                flex-direction: column;
                gap: 10px;
            }
        }

        /* Hide native number input spinners */
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield;
        }

        .ctr-field-readonly {
            background-color: #e9ecef;
            cursor: not-allowed;
        }

        .ctr-natureaction-lbl {
            font-weight: 400;
            font-size: 0.82rem;
        }

        .ctr-required::after {
            content: ' *';
            color: #dc3545;
        }

        .ctr-cf-uom:disabled {
            background-color: #e9ecef;
            cursor: not-allowed;
        }

        .ui-autocomplete {
            max-height: 220px;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 9999 !important;
            border: 1px solid #ced4da;
            border-radius: 4px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .15);
            padding: 4px 0;
            margin: 2px 0 0 0;
            list-style: none;
            font-size: 0.85rem;
        }

        .ui-autocomplete .ui-menu-item {
            padding: 0;
        }

        .ui-autocomplete .ui-menu-item-wrapper {
            display: block;
            padding: 6px 12px;
            cursor: pointer;
            color: #212529;
        }

        .ui-autocomplete .ui-menu-item-wrapper.ui-state-active {
            background: #4B49AC;
            color: #fff;
            border: none;
        }

        /* Service Action Row - keeps buttons in a horizontal line */
        .ce-svc-actions-row {
            display: flex;
            justify-content: flex-end;
            /* Pushes buttons to the right */
            align-items: center;
            gap: 8px;
            /* Spacing between buttons */
            margin-bottom: 15px;
            flex-wrap: wrap;
            /* Prevents breaking on small screens */
        }

        /* Pushes the Delete button to the far left */
        .ce-svc-actions-row .ce-delete-service-btn {
            margin-right: auto;
        }

        /* Charges Header Row */
        .ce-charges-header-row {
            display: flex;
            justify-content: space-between;
            /* Pushes "Charges" left, "Add Charge" right */
            align-items: center;
            margin-bottom: 10px;
        }

        .ce-charges-table-wrap {
            margin-top: 8px;
            overflow-x: auto;
        }

        .ce-charges-table {
            min-width: 640px;
        }

        .ce-charges-table td,
        .ce-charges-table th {
            padding: 4px 6px;
            font-size: 0.78rem;
        }

        .ce-charges-table .ce-charge-category-row td {
            background: #f4f4fb;
            color: #4B49AC;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            border-top: 1px solid #dddcf5;
        }

        .ce-charges-table .ce-charge-row td {
            background: #fff;
        }

        .ce-nature-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            white-space: nowrap;
        }

        .ce-nature-badge.ce-nature-revenue {
            background: #e6f4ea;
            color: #1e7e34;
            border: 1px solid #b7dfc3;
        }

        .ce-nature-badge.ce-nature-cost {
            background: #fdecea;
            color: #b21f2d;
            border: 1px solid #f5c2c7;
        }

        /* Revenue / Cost collapsible sections */
        .ce-charges-sections {
            margin-top: 8px;
        }

        .ce-charge-section {
            margin-bottom: 14px;
        }

        .ce-charge-section:last-child {
            margin-bottom: 0;
        }

        .ce-charge-section-header {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 10px 14px;
            background: #4B49AC;
            color: #fff;
            cursor: pointer;
            border-radius: 4px;
            font-size: 0.9rem;
            user-select: none;
        }

        .ce-charge-section-header:hover {
            background: #3f3e94;
        }

        .ce-charge-section-header .ce-sec-title {
            font-weight: 500;
        }

        .ce-charge-section-header .ce-sec-caret {
            font-size: 0.7rem;
            transition: transform 0.15s ease;
            color: rgba(255, 255, 255, 0.85);
        }

        .ce-charge-section-header.collapsed .ce-sec-caret {
            transform: rotate(-90deg);
        }

        .ce-charge-section-header .ce-sec-total {
            font-weight: 500;
        }

        .ce-charge-section-body {
            padding-top: 8px;
        }

        .ce-charge-section-body.collapsed {
            display: none;
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script>
        (function() {
            var CONTRACT_ID = @json($contractInfo->Id);
            var OFFICE_CODE = @json($officeCode ?? '');
            var MASTER_BASE = '{{ url('/contracts/masters') }}';

            // ── Collapsible groups (service blocks) ──────────────────────────
            document.addEventListener('click', function(e) {
                var header = e.target.closest('.cc-group-header');
                if (header) {
                    var body = document.getElementById(header.getAttribute('data-target'));
                    header.classList.toggle('collapsed');
                    if (body) body.classList.toggle('collapsed');
                }
            });

            // ── Collapsible Revenue/Cost charge sections ─────────────────────
            document.addEventListener('click', function(e) {
                var header = e.target.closest('.ce-charge-section-header');
                if (!header) return;
                var body = document.getElementById(header.getAttribute('data-target'));
                header.classList.toggle('collapsed');
                if (body) body.classList.toggle('collapsed');
            });

            // ── Master data helpers (same pattern as create.blade.php) ────────
            function fetchMasterList(endpoint, params) {
                var url = MASTER_BASE + '/' + endpoint;
                var qs = params ? '?' + $.param(params) : '';
                console.log('Fetching:', url + qs);
                return $.ajax({
                    url: url + qs,
                    type: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        console.log('✅ Response from', endpoint, res);
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ AJAX error for', endpoint, status, error, xhr.responseText);
                    }
                });
            }

            function populateSelect($select, data, valueField, labelField, selectedValue) {
                $select.empty().append('<option value="">-- Select --</option>');
                if (!data || !data.length) {
                    $select.append('<option value="" disabled>No options available</option>');
                    return;
                }
                data.forEach(function(item) {
                    var val = item[valueField] || '';
                    var label = item[labelField] || val;
                    $select.append('<option value="' + val + '">' + label + '</option>');
                });
                if (selectedValue) $select.val(selectedValue);
            }

            function initLocationAutocomplete(inputId, codeInputId, countrySelectId) {
                var $input = $('#' + inputId);
                var $codeInput = codeInputId ? $('#' + codeInputId) : null;
                var $country = countrySelectId ? $('#' + countrySelectId) : null;

                if ($input.hasClass('ui-autocomplete-input')) $input.autocomplete('destroy');

                $input.autocomplete({
                    minLength: 2,
                    delay: 300,
                    appendTo: $input.closest('.modal-body, .cc-group-body').length ?
                        $input.closest('.modal-body, .cc-group-body') : 'body',
                    source: function(request, response) {
                        $.ajax({
                            url: MASTER_BASE + '/locations',
                            data: {
                                filter: request.term,
                                include_country: $country && $country.val() ? 1 : 0,
                                country_code: $country ? $country.val() : ''
                            },
                            success: function(data) {
                                if (data.success && data.locations.length) {
                                    response(data.locations.map(function(l) {
                                        return {
                                            label: l.LocationCode + ' — ' + l
                                                .LocationName,
                                            value: l.LocationCode,
                                            code: l.LocationCode
                                        };
                                    }));
                                } else {
                                    response([]);
                                }
                            }
                        });
                    },
                    select: function(event, ui) {
                        $input.val(ui.item.value);
                        if ($codeInput) $codeInput.val(ui.item.code);
                        return false;
                    }
                });
            }

            // Normalizes RateType strings from the SP ('SlabBased', 'StepUp')
            // to the spaced form used by this page's <select> options
            // ('Slab Based', 'Step Up'). Passes through unrecognized/already
            // spaced values unchanged.
            function normalizeRateType(rt) {
                var map = {
                    'SlabBased': 'Slab Based',
                    'StepUp': 'Step Up',
                    'AtActual': 'At Actual'

                };
                return map[rt] || rt;
            }


            // ── Service sections: populate dropdowns + prefill existing values ─
            var CONTRACT_TEAM_ID = @json($teamCode ?? '');
            var CONTRACT_OFFICE_ID = @json($officeId ?? '');
            var SERVICE_INITIAL = {};
            @foreach ($services as $service)
                @php
                    $serviceInitial = [
                        'service' => $service->Service,
                        'process' => $service->Process,
                        'incoterm' => $service->IncoTerm,
                        'coo' => $service->COO,
                        'cod' => $service->COD,
                        'shipmentType' => $service->ShipmentType,
                        'shipmentMode' => $service->ShipmentMode,
                        'movementType' => $service->MovementType,
                        'serviceClass' => $service->ServiceClass,
                        'forwarderCode' => $service->ForwarderCode ?? '',
                        'forwarder' => $service->Forwarder ?? '',
                    ];
                @endphp
                SERVICE_INITIAL['{{ $service->Id }}'] = @json($serviceInitial);
            @endforeach

            function loadForwardersForSelect($select, savedCode = '', savedText = '') {
                var wasDisabled = $select.prop('disabled');
                $select.empty().append('<option value="">Loading...</option>').prop('disabled', true);

                if (!CONTRACT_TEAM_ID && !CONTRACT_OFFICE_ID) {
                    $select.empty().append('<option value="" disabled>No forwarders available</option>');
                    return;
                }

                $.ajax({
                    url: '{{ route('quotation.booking.forwarders') }}',
                    type: 'GET',
                    data: {
                        team_id: CONTRACT_TEAM_ID,
                        office_id: CONTRACT_OFFICE_ID
                    },
                    success: function(response) {
                        $select.empty().append('<option value=""></option>');

                        if (response.success && response.parties && response.parties.length) {
                            response.parties.forEach(function(p) {
                                $select.append(
                                    '<option value="' + (p.PartyCode || '') + '" data-id="' + (p
                                        .PartyID || '') + '">' +
                                    (p.PartyName || p.PartyCode || '') +
                                    '</option>'
                                );
                            });

                            if (savedCode) {
                                if (!$select.find('option[value="' + savedCode + '"]').length) {
                                    $select.append('<option value="' + savedCode + '">' + (savedText ||
                                        savedCode) + '</option>');
                                }
                                $select.val(savedCode);
                            }
                        } else {
                            $select.append('<option value="" disabled>No forwarders available</option>');
                        }
                    },
                    error: function() {
                        $select.empty().append('<option value="" disabled>Failed to load</option>');
                    },
                    complete: function() {
                        $select.prop('disabled', wasDisabled);
                    }
                });
            }

            function populateServiceGroupMasters($group, initial) {
                fetchMasterList('services').done(function(res) {
                    if (res.success) populateSelect($group.find('.ce-svc-service'), res.data, 'Code', 'Name',
                        initial
                        .service);
                });
                fetchMasterList('processes', {
                    service: initial.service
                }).done(function(res) {
                    if (res.success) populateSelect($group.find('.ce-svc-process'), res.data, 'Code', 'Name',
                        initial.process);
                });
                fetchMasterList('inco-terms').done(function(res) {
                    if (res.success) {
                        var withLabels = res.data.map(function(item) {
                            var name = item.IncoTermName || item.Name || item.Description || item
                                .IncoTermDescription || item.IncoTermCode;
                            return {
                                IncoTermCode: item.IncoTermCode,
                                IncoTermLabel: item.IncoTermCode + ' - ' + name
                            };
                        });
                        populateSelect($group.find('.ce-svc-incoterm'), withLabels, 'IncoTermCode',
                            'IncoTermLabel', initial.incoterm);
                    }
                });
                fetchMasterList('shipment-types').done(function(res) {
                    if (res.success) populateSelect($group.find('.ce-svc-shipmenttype'), res.data, 'Code',
                        'Name', initial.shipmentType);
                });
                fetchMasterList('shipment-modes').done(function(res) {
                    if (res.success) populateSelect($group.find('.ce-svc-shipmentmode'), res.data, 'Code',
                        'Code', initial.shipmentMode);
                });
                fetchMasterList('movement-types').done(function(res) {
                    if (res.success) populateSelect($group.find('.ce-svc-movementtype'), res.data, 'Code',
                        'Name', initial.movementType);
                });
                fetchMasterList('service-classes').done(function(res) {
                    if (res.success) populateSelect($group.find('.ce-svc-serviceclass'), res.data, 'Code',
                        'Name', initial.serviceClass);
                });
                fetchMasterList('countries').done(function(res) {
                    if (Array.isArray(res)) {
                        populateSelect($group.find('.ce-svc-coo'), res, 'CountCode', 'CountryName', initial
                            .coo);
                        populateSelect($group.find('.ce-svc-cod'), res, 'CountCode', 'CountryName', initial
                            .cod);
                    }
                });

                loadForwardersForSelect($group.find('.ce-svc-forwarder'), initial.forwarderCode || '', initial
                    .forwarder || '');
            }

            $(document).on('click', '.ce-add-service-btn', function() {
                var wrap = document.getElementById('addServiceModalBody');
                if (!wrap) return;
                wrap.innerHTML = serviceFormHtml();
                populateServiceGroupMasters($(wrap), {});
                bindAddServiceSave(wrap);
                $(wrap).find('.ce-svc-pol').attr('id', 'ce-svc-pol-new');
                $(wrap).find('.ce-svc-por').attr('id', 'ce-svc-por-new');
                $(wrap).find('.ce-svc-pod').attr('id', 'ce-svc-pod-new');
                $(wrap).find('.ce-svc-poe').attr('id', 'ce-svc-poe-new');
                $(wrap).find('.ce-svc-coo').attr('id', 'ce-svc-coo-new');
                $(wrap).find('.ce-svc-cod').attr('id', 'ce-svc-cod-new');
                initLocationAutocomplete('ce-svc-pol-new', null, null);
                initLocationAutocomplete('ce-svc-por-new', null, 'ce-svc-coo-new');
                initLocationAutocomplete('ce-svc-pod-new', null, null);
                initLocationAutocomplete('ce-svc-poe-new', null, 'ce-svc-cod-new');
                $('#addServiceModal').modal('show');
            });

            function serviceFormHtml() {
                return '<div class="cc-info-grid" style="margin-bottom:10px;">' +
                    '<div class="cc-info-col">' +
                    '<div class="ctr-field mb-2 d-flex" style="gap:8px;">' +
                    '<div style="flex:1;"><label class="ctr-lbl ctr-required">Service</label>' +
                    '<select class="form-control form-control-sm ce-svc-service">' +
                    '<option value="">-- Select --</option></select></div>' +
                    '<div style="flex:1;"><label class="ctr-lbl ctr-required">Process</label>' +
                    '<select class="form-control form-control-sm ce-svc-process">' +
                    '<option value="">-- Select --</option></select></div>' +
                    '</div>' +
                    '<div class="ctr-field mb-2"><label class="ctr-lbl">IncoTerm</label>' +
                    '<select class="form-control form-control-sm ce-svc-incoterm">' +
                    '<option value="">-- Select --</option></select></div>' +
                    '<div class="ctr-field mb-2"><label class="ctr-lbl">Service Class</label>' +
                    '<select class="form-control form-control-sm ce-svc-serviceclass">' +
                    '<option value="">-- Select --</option></select></div>' +
                    '<div class="ctr-field d-flex" style="gap:8px;">' +
                    '<div style="flex:1;"><label class="ctr-lbl">Country of Origin</label>' +
                    '<select class="form-control form-control-sm ce-svc-coo">' +
                    '<option value="">-- Select --</option></select></div>' +
                    '<div style="flex:1;"><label class="ctr-lbl">Country of Destination</label>' +
                    '<select class="form-control form-control-sm ce-svc-cod">' +
                    '<option value="">-- Select --</option></select></div>' +
                    '</div>' +
                    '<div class="ctr-field d-flex mt-2" style="gap:8px;">' +
                    '<div style="flex:1;"><label class="ctr-lbl">POL</label>' +
                    '<input type="text" class="form-control form-control-sm ce-svc-pol" placeholder="POL code"></div>' +
                    '<div style="flex:1;"><label class="ctr-lbl">POD</label>' +
                    '<input type="text" class="form-control form-control-sm ce-svc-pod" placeholder="POD code"></div>' +
                    '</div>' +
                    '<div class="ctr-field d-flex mt-2" style="gap:8px;">' +
                    '<div style="flex:1;"><label class="ctr-lbl">Place of Receipt</label>' +
                    '<input type="text" class="form-control form-control-sm ce-svc-por" placeholder="POR code"></div>' +
                    '<div style="flex:1;"><label class="ctr-lbl">Place of Delivery</label>' +
                    '<input type="text" class="form-control form-control-sm ce-svc-poe" placeholder="POE code"></div>' +
                    '</div>' +
                    '</div>' +
                    '<div class="cc-info-col">' +
                    '<div class="ctr-field mb-2"><label class="ctr-lbl">Shipment Type</label>' +
                    '<select class="form-control form-control-sm ce-svc-shipmenttype">' +
                    '<option value="">-- Select --</option></select></div>' +
                    '<div class="ctr-field mb-2"><label class="ctr-lbl">Shipment Mode</label>' +
                    '<select class="form-control form-control-sm ce-svc-shipmentmode">' +
                    '<option value="">-- Select --</option></select></div>' +
                    '<div class="ctr-field mb-2"><label class="ctr-lbl">Movement Type</label>' +
                    '<select class="form-control form-control-sm ce-svc-movementtype">' +
                    '<option value="">-- Select --</option></select></div>' +
                    '<div class="ctr-field mb-2"><label class="ctr-lbl">Forwarder</label>' +
                    '<select class="form-control form-control-sm ce-svc-forwarder"><option value="">-- Select --</option></select></div>' +
                    '</div>' +
                    '</div>' +
                    '<div class="d-flex justify-content-end mt-2">' +
                    '<button type="button" class="btn btn-sm btn-secondary mr-2 ce-cancel-new-service-btn">Cancel</button>' +
                    '<button type="button" class="btn btn-sm btn-success ce-save-new-service-btn">Save Service</button>' +
                    '</div>';
            }

            function bindAddServiceSave(wrap) {
                $(wrap).find('.ce-save-new-service-btn').on('click', function() {
                    var process = $(wrap).find('.ce-svc-process').val();
                    var coo = $(wrap).find('.ce-svc-coo').val().trim();
                    var cod = $(wrap).find('.ce-svc-cod').val().trim();

                    if (['Export', 'Local'].indexOf(process) !== -1 && !coo) {
                        alert('Country of Origin is required when Process is Export or Local.');
                        return;
                    }
                    if (process === 'Import' && !cod) {
                        alert('Country of Destination is required when Process is Import.');
                        return;
                    }

                    var payload = {
                        service: $(wrap).find('.ce-svc-service').val(),
                        process: process,
                        incoterm: $(wrap).find('.ce-svc-incoterm').val(),
                        serviceClass: $(wrap).find('.ce-svc-serviceclass').val().trim(),
                        shipmentType: $(wrap).find('.ce-svc-shipmenttype').val(),
                        movementType: $(wrap).find('.ce-svc-movementtype').val(),
                        shipmentMode: $(wrap).find('.ce-svc-shipmentmode').val(),
                        countryOrigin: coo,
                        countryDestination: cod,
                        pol: $(wrap).find('.ce-svc-pol').val().trim(),
                        pod: $(wrap).find('.ce-svc-pod').val().trim(),
                        placeOfReceipt: $(wrap).find('.ce-svc-por').val().trim(),
                        placeOfDelivery: $(wrap).find('.ce-svc-poe').val().trim(),
                        forwarder: $(wrap).find('.ce-svc-forwarder').val(),
                        forwarder_text: $(wrap).find('.ce-svc-forwarder option:selected').text() || $(wrap)
                            .find('.ce-svc-forwarder').val()
                    };

                    $.ajax({
                        url: '/contracts/' + encodeURIComponent(CONTRACT_ID) + '/services',
                        type: 'POST',
                        data: $.extend({
                            _token: document.querySelector('meta[name="csrf-token"]').content
                        }, payload),
                        dataType: 'json'
                    }).done(function(res) {
                        if (!res.success) {
                            alert('Failed to add service: ' + (res.message || 'Unknown error'));
                            return;
                        }
                        alert(res.message || 'Service added.');
                        window.location.reload(); // simplest: reload to show new service group
                    }).fail(function(xhr) {
                        alert('Failed to add service: ' + ((xhr.responseJSON && xhr.responseJSON
                            .message) || 'Add failed.'));
                    });
                });
            }

            $(document).on('click', '.ce-cancel-new-service-btn', function() {
                $('#addServiceModal').modal('hide');
            });

            $('.cc-group-body').each(function() {
                var $body = $(this);
                var svcId = $body.find('.ce-svc-id').val();
                var initial = SERVICE_INITIAL[svcId] || {};
                populateServiceGroupMasters($body, initial);

                // Assign unique IDs to each location select
                $body.find('.ce-svc-coo').attr('id', 'ce-svc-coo-' + svcId);
                $body.find('.ce-svc-cod').attr('id', 'ce-svc-cod-' + svcId);
                $body.find('.ce-svc-pol').attr('id', 'ce-svc-pol-' + svcId);
                $body.find('.ce-svc-pod').attr('id', 'ce-svc-pod-' + svcId);
                $body.find('.ce-svc-por').attr('id', 'ce-svc-por-' + svcId);
                $body.find('.ce-svc-poe').attr('id', 'ce-svc-poe-' + svcId);

                $body.data('ce-svc-id', svcId);

                $body.find('.ce-svc-service').on('change', function() {
                    var serviceCode = this.value;
                    if (!serviceCode) return;
                    fetchMasterList('processes', {
                        service: serviceCode
                    }).done(function(res) {
                        if (res.success) populateSelect($body.find('.ce-svc-process'), res.data,
                            'Code', 'Name');
                    });
                });
            });
            // Select2 must not initialize against a display:none container (produces the
            // broken/native-looking dropdown seen when a collapsed group loads on page
            // load). Instead, init lazily the first time each group is expanded.
            document.addEventListener('click', function(e) {
                var header = e.target.closest('.cc-group-header');
                if (!header) return;

                var body = document.getElementById(header.getAttribute('data-target'));
                if (!body || body.dataset.locAutocompleteInit) return;
                if (!body.querySelector('.ce-svc-id')) return; // only service groups carry this marker

                var $body = $(body);
                var svcId = $body.data('ce-svc-id');

                initLocationAutocomplete('ce-svc-pol-' + svcId, null, null);
                initLocationAutocomplete('ce-svc-por-' + svcId, null, 'ce-svc-coo-' + svcId);
                initLocationAutocomplete('ce-svc-pod-' + svcId, null, null);
                initLocationAutocomplete('ce-svc-poe-' + svcId, null, 'ce-svc-cod-' + svcId);

                body.dataset.locAutocompleteInit = '1';
            });

            // ── Edit / Cancel toggle for a service block ────────────────────────
            function setServiceEditable($group, editable) {
                $group.find('.ce-svc-field').prop('disabled', !editable);
                $group.find('.ce-svc-forwarder').prop('disabled', !editable);
                $group.find('.ce-edit-service-btn').toggle(!editable);
                $group.find('.ce-cancel-service-btn, .ce-save-service-btn').toggle(editable);
            }

            $(document).on('click', '.ce-edit-service-btn', function() {
                var $group = $(this).closest('.cc-group-body');
                var snapshot = $group.find('.ce-svc-field').map(function() {
                    return {
                        el: this,
                        val: this.value
                    };
                }).get();
                $group.data('ce-svc-snapshot', snapshot);
                setServiceEditable($group, true);
            });

            $(document).on('click', '.ce-cancel-service-btn', function() {
                var $group = $(this).closest('.cc-group-body');
                var snapshot = $group.data('ce-svc-snapshot') || [];
                snapshot.forEach(function(entry) {
                    $(entry.el).val(entry.val).trigger('change');
                });
                setServiceEditable($group, false);
            });

            $(document).on('click', '.ce-delete-service-btn', function() {
                var svcId = $(this).data('svc');
                if (!confirm('Delete this service and all its charges? This cannot be undone.')) return;

                var btn = this;
                btn.disabled = true;

                $.ajax({
                    url: '/contracts/' + encodeURIComponent(CONTRACT_ID) + '/services/' +
                        encodeURIComponent(svcId),
                    type: 'DELETE',
                    data: {
                        _token: document.querySelector('meta[name="csrf-token"]').content
                    },
                    dataType: 'json'
                }).done(function(res) {
                    if (!res.success) {
                        alert('Failed to delete service: ' + (res.message || 'Unknown error'));
                        btn.disabled = false;
                        return;
                    }
                    $(btn).closest('.cc-group').remove();
                }).fail(function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed.';
                    alert('Failed to delete service: ' + msg);
                    btn.disabled = false;
                });
            });

            // ── Copy Contract (whole contract → new contract) ───────────────────
            $(document).on('click', '.ce-copy-contract-btn', function() {
                if (!confirm('Copy this entire contract (header, services, and charges) into a new contract?'))
                    return;

                var btn = this;
                var originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Copying...';

                $.ajax({
                    url: '/contracts/' + encodeURIComponent(CONTRACT_ID) + '/duplicate',
                    type: 'POST',
                    data: {
                        _token: document.querySelector('meta[name="csrf-token"]').content,
                        CopyContractID: CONTRACT_ID,
                        Action: 'Copy'
                    },
                    dataType: 'json'
                }).done(function(res) {
                    if (!res.success) {
                        alert('Failed to copy contract: ' + (res.message || 'Unknown error'));
                        return;
                    }
                    alert(res.message || 'Contract copied.');
                    if (res.data && res.data.newContractId) {
                        window.location = '/contracts/' + encodeURIComponent(res.data.newContractId) +
                            '/edit';
                    } else {
                        window.location.reload();
                    }
                }).fail(function(xhr) {
                    alert('Failed to copy contract: ' + ((xhr.responseJSON && xhr.responseJSON
                        .message) || 'Copy failed.'));
                }).always(function() {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                });
            });

            // ── Copy Service (service + all its revenue/cost charges → new service) ──
            $(document).on('click', '.ce-copy-service-btn', function() {
                var svcId = $(this).data('svc');
                if (!confirm('Copy this service and all its charges as a new service?')) return;

                var btn = this;
                var originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Copying...';

                $.ajax({
                    url: '/contracts/' + encodeURIComponent(CONTRACT_ID) + '/services/' +
                        encodeURIComponent(svcId) + '/duplicate',
                    type: 'POST',
                    data: {
                        _token: document.querySelector('meta[name="csrf-token"]').content,
                        CopyServiceID: svcId,
                        Action: 'Copy'
                    },
                    dataType: 'json'
                }).done(function(res) {
                    if (!res.success) {
                        alert('Failed to copy service: ' + (res.message || 'Unknown error'));
                        return;
                    }
                    alert(res.message || 'Service copied.');
                    window.location.reload();
                }).fail(function(xhr) {
                    alert('Failed to copy service: ' + ((xhr.responseJSON && xhr.responseJSON
                        .message) || 'Copy failed.'));
                }).always(function() {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                });
            });

            // ── Save Service ───────────────────────────────────────────────────
            $(document).on('click', '.ce-save-service-btn', function() {
                var $group = $(this).closest('.cc-group-body');
                var svcId = $group.find('.ce-svc-id').val();

                var process = $group.find('.ce-svc-process').val();
                var coo = $group.find('.ce-svc-coo').val().trim();
                var cod = $group.find('.ce-svc-cod').val().trim();

                if (['Export', 'Local'].indexOf(process) !== -1 && !coo) {
                    alert('Country of Origin is required when Process is Export or Local.');
                    return;
                }
                if (process === 'Import' && !cod) {
                    alert('Country of Destination is required when Process is Import.');
                    return;
                }

                var payload = {
                    service: $group.find('.ce-svc-service').val(),
                    process: process,
                    incoterm: $group.find('.ce-svc-incoterm').val(),
                    serviceClass: $group.find('.ce-svc-serviceclass').val().trim(),
                    shipmentType: $group.find('.ce-svc-shipmenttype').val(),
                    movementType: $group.find('.ce-svc-movementtype').val(),
                    shipmentMode: $group.find('.ce-svc-shipmentmode').val(),
                    countryOrigin: coo,
                    countryDestination: cod,
                    pol: $group.find('.ce-svc-pol').val().trim(),
                    pod: $group.find('.ce-svc-pod').val().trim(),
                    placeOfReceipt: $group.find('.ce-svc-por').val().trim(),
                    placeOfDelivery: $group.find('.ce-svc-poe').val().trim(),
                    forwarder: $group.find('.ce-svc-forwarder').val(),
                    forwarder_text: $group.find('.ce-svc-forwarder option:selected').text() || $group.find(
                        '.ce-svc-forwarder').val()
                };

                var btn = this;
                var originalText = btn.textContent;
                btn.disabled = true;
                btn.textContent = 'Saving...';

                $.ajax({
                    url: '/contracts/' + encodeURIComponent(CONTRACT_ID) + '/services/' +
                        encodeURIComponent(
                            svcId),
                    type: 'PUT',
                    data: $.extend({
                        _token: document.querySelector('meta[name="csrf-token"]')
                            .content
                    }, payload),
                    dataType: 'json'
                }).done(function(res) {
                    if (!res.success) {
                        alert('Failed to update service: ' + (res.message ||
                            'Unknown error'));
                        return;
                    }
                    setServiceEditable($group, false);
                    alert(res.message || 'Service updated.');
                }).fail(function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ||
                        'Update failed.';
                    alert('Failed to update service: ' + msg);
                }).always(function() {
                    btn.disabled = false;
                    btn.textContent = originalText;
                });
            });

            function setHeaderEditable(editable) {
                $('.ce-hdr-view').toggle(!editable);
                $('.ce-hdr-field').toggle(editable);
                $('.ce-edit-header-btn').toggle(!editable);
                $('.ce-cancel-header-btn, .ce-save-header-btn').toggle(editable);
            }

            $(document).on('click', '.ce-edit-header-btn', function() {
                setHeaderEditable(true);
            });

            $(document).on('click', '.ce-cancel-header-btn', function() {
                $('.ce-hdr-effectivefrom').val($('.ce-hdr-view[data-field="effectiveFrom"]').text().trim());
                $('.ce-hdr-effectiveto').val($('.ce-hdr-view[data-field="effectiveTo"]').text().trim());
                $('.ce-hdr-remark').val($('.ce-hdr-view[data-field="remark"]').text().trim());
                setHeaderEditable(false);
            });

            $(document).on('click', '.modal .close[data-dismiss="modal"]', function() {
                $(this).closest('.modal').modal('hide');
            });

            $(document).on('click', '.ce-save-header-btn', function() {
                var effectiveFrom = $('.ce-hdr-effectivefrom').val();
                var effectiveTo = $('.ce-hdr-effectiveto').val();
                var remark = $('.ce-hdr-remark').val().trim();
                var btn = this;
                var originalText = btn.textContent;
                btn.disabled = true;
                btn.textContent = 'Saving...';

                $.ajax({
                    url: '/contracts/' + encodeURIComponent(CONTRACT_ID),
                    type: 'PUT',
                    data: {
                        _token: document.querySelector('meta[name="csrf-token"]').content,
                        contract_type: @json($contractInfo->ContractType),
                        contract_name: @json($contractInfo->ContractName),
                        effective_from: effectiveFrom,
                        effective_to: effectiveTo,
                        remark: remark
                    },
                    dataType: 'json'
                }).done(function(res) {
                    if (!res.success) {
                        alert('Failed to update contract: ' + (res.message || 'Unknown error'));
                        return;
                    }
                    $('.ce-hdr-view[data-field="effectiveFrom"]').text(effectiveFrom);
                    $('.ce-hdr-view[data-field="effectiveTo"]').text(effectiveTo);
                    $('.ce-hdr-view[data-field="remark"]').text(remark);
                    setHeaderEditable(false);
                }).fail(function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Update failed.';
                    alert('Failed to update contract: ' + msg);
                }).always(function() {
                    btn.disabled = false;
                    btn.textContent = originalText;
                });
            });

            // ── Charge edit form (same builder as create.blade.php) ─────────────
            // Formula fields are now loaded per selected Nature (Revenue/Cost)
            // via loadFormulaFields(svcId, isRevenue) — see call sites below and
            // the Nature-change handler in bindChargeFormEvents().
            var FORMULA_FIELDS = [];

            function loadFormulaFields(svcId, isRevenue) {
                return $.ajax({
                    url: '/contracts/' + encodeURIComponent(CONTRACT_ID) + '/services/' +
                        encodeURIComponent(
                            svcId) + '/formula-fields',
                    type: 'GET',
                    data: {
                        is_revenue: isRevenue
                    },
                    dataType: 'json'
                });
            }
            var FORMULA_OPERATORS = [{
                    label: 'Plus +',
                    token: '+'
                }, {
                    label: 'Minus -',
                    token: '-'
                },
                {
                    label: 'Multiply *',
                    token: '*'
                }, {
                    label: 'Divide /',
                    token: '/'
                },
                {
                    label: 'CurvedBraceStart (',
                    token: '('
                }, {
                    label: 'CurvedBraceEnd )',
                    token: ')'
                }
            ];
            var FORMULA_FUNCTIONS = [{
                label: 'Ceiling',
                token: 'CEILING('
            }, {
                label: 'Floor',
                token: 'FLOOR('
            }];

            function formulaOptionsHtml(list) {
                return list.map(function(o) {
                    return '<option value="' + o.token + '">' + o.label + '</option>';
                }).join('');
            }

            function formulaBuilderHtml() {
                return '<div class="ctr-formula-builder">' +
                    '<div class="ctr-formula-cols">' +
                    '<div class="ctr-formula-col"><label class="ctr-lbl">Select Formula</label>' +
                    '<select multiple size="6" class="form-control form-control-sm ctr-fb-fields">' +
                    formulaOptionsHtml(FORMULA_FIELDS) + '</select>' +
                    '<button type="button" class="btn btn-xs btn-outline-primary mt-1 ctr-fb-add-field">Add</button></div>' +
                    '<div class="ctr-formula-col"><label class="ctr-lbl">Select Operator</label>' +
                    '<select multiple size="6" class="form-control form-control-sm ctr-fb-operators">' +
                    formulaOptionsHtml(FORMULA_OPERATORS) + '</select>' +
                    '<button type="button" class="btn btn-xs btn-outline-primary mt-1 ctr-fb-add-operator">Add</button></div>' +
                    '<div class="ctr-formula-col"><label class="ctr-lbl">Select Function</label>' +
                    '<select multiple size="6" class="form-control form-control-sm ctr-fb-functions">' +
                    formulaOptionsHtml(FORMULA_FUNCTIONS) + '</select>' +
                    '<button type="button" class="btn btn-xs btn-outline-primary mt-1 ctr-fb-add-function">Add</button></div>' +
                    '</div>' +
                    '<div class="ctr-field mt-2"><div class="d-flex align-items-center">' +
                    '<label class="ctr-lbl mb-0">Formula</label><a href="#" class="ctr-fb-reset ml-auto">Reset</a></div>' +
                    '<textarea class="form-control form-control-sm ctr-cf-formula" rows="2"></textarea></div>' +
                    '<div class="d-flex" style="gap:16px; align-items:flex-start;">' +
                    '<div class="ctr-field mt-2" style="max-width:220px; flex:0 0 auto;"><label class="ctr-lbl">Rate Type</label>' +
                    '<select class="form-control form-control-sm ctr-fb-ratetype">' +
                    '<option value="Single">Single</option><option value="Slab Based">Slab Based</option></select></div>' +
                    '<div class="ctr-fb-ratearea mt-2" style="flex:1;"></div>' +
                    '</div></div>';
            }

            function inlineRateHtml(rateType) {
                if (rateType === 'Single') {
                    return '<label class="ctr-lbl">Rate</label>' +
                        '<input type="number" step="0.00001" class="form-control form-control-sm ctr-cf-rate">';
                }
                return ''; // At Actual falls through here — no rate input, correct
            }

            function slabRowHtml() {
                return '<tr>' +
                    '<td><input type="number" class="form-control form-control-sm ctr-slab-min" value="0" step="any"></td>' +
                    '<td><input type="number" class="form-control form-control-sm ctr-slab-upto" placeholder="e.g. 500"></td>' +
                    '<td><input type="number" step="0.01" class="form-control form-control-sm ctr-slab-value"></td>' +
                    '<td><input type="text" class="form-control form-control-sm ctr-slab-formula" placeholder="optional"></td>' +
                    '<td><span class="ctr-slab-row-remove">&times;</span></td></tr>';
            }

            function rateAreaHtml(rateType) {
                if (rateType === 'Slab Based' || rateType === 'Step Up') {
                    return '<div class="ctr-slab-wrap">' +
                        '<div class="ctr-field mb-1" style="max-width:200px;">' +
                        '<label class="ctr-lbl">Slab Type</label>' +
                        '<select class="form-control form-control-sm ctr-slab-type">' +
                        '<option value="LessThanOrEqual" selected>LessThanOrEqual</option>' +
                        '<option value="LessThan">LessThan</option>' +
                        '<option value="GreaterThan">GreaterThan</option>' +
                        '<option value="GreaterThanOrEqual">GreaterThanOrEqual</option>' +
                        '</select></div>' +
                        '<table class="ctr-slab-table">' +
                        '<thead><tr><th>Min</th><th>Slab (Up To)</th><th>Value</th><th>Formula (optional)</th><th></th></tr></thead>' +
                        '<tbody class="ctr-slab-rows">' + slabRowHtml() + '</tbody></table>' +
                        '<button type="button" class="btn btn-xs btn-outline-primary ctr-slab-add-row mt-1">+ Add Slab</button></div>';
                }
                if (rateType === 'Formula') return formulaBuilderHtml();
                return '';
            }

            function fillSlabRows(tbody, rateSlabData) {
                if (!tbody || !rateSlabData) return;
                var slabs = [];
                if (Array.isArray(rateSlabData)) {
                    // Old format: simple array of {upto, value} – convert
                    slabs = rateSlabData;
                } else if (rateSlabData.Slabs) {
                    slabs = rateSlabData.Slabs;
                }
                if (!slabs.length) return;
                tbody.innerHTML = '';
                slabs.forEach(function(s) {
                    var row = document.createElement('tr');
                    var min = s.Min ?? 0;
                    var upto = s.Slab ?? s.upto ?? '';
                    var value = s.value ?? '';
                    var formula = s.Formula ?? '';
                    row.innerHTML =
                        '<td><input type="number" class="form-control form-control-sm ctr-slab-min" value="' +
                        min + '"></td>' +
                        '<td><input type="number" class="form-control form-control-sm ctr-slab-upto" value="' +
                        upto + '"></td>' +
                        '<td><input type="number" step="0.01" class="form-control form-control-sm ctr-slab-value" value="' +
                        value + '"></td>' +
                        '<td><input type="text" class="form-control form-control-sm ctr-slab-formula" value="' +
                        formula + '"></td>' +
                        '<td><span class="ctr-slab-row-remove">&times;</span></td>';
                    tbody.appendChild(row);
                });
            }

            function addSlabRow(wrap) {
                var tbody = wrap.querySelector('.ctr-slab-rows');
                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td><input type="number" class="form-control form-control-sm ctr-slab-min" value="0"></td>' +
                    '<td><input type="number" class="form-control form-control-sm ctr-slab-upto" placeholder="e.g. 500"></td>' +
                    '<td><input type="number" step="0.01" class="form-control form-control-sm ctr-slab-value"></td>' +
                    '<td><input type="text" class="form-control form-control-sm ctr-slab-formula" placeholder="optional"></td>' +
                    '<td><span class="ctr-slab-row-remove">&times;</span></td>';
                tbody.appendChild(tr);
            }

            function renderFormulaRateArea(wrap, rateType) {
                var area = wrap.querySelector('.ctr-fb-ratearea');
                if (rateType === 'Slab Based') {
                    area.innerHTML = '<div class="ctr-slab-wrap"><table class="ctr-slab-table">' +
                        '<thead><tr><th>Min</th><th>Slab (Up To)</th><th>Value</th><th>Formula (optional)</th><th></th></tr></thead>' +
                        '<tbody class="ctr-slab-rows">' + slabRowHtml() + '</tbody></table>' +
                        '<button type="button" class="btn btn-xs btn-outline-primary ctr-slab-add-row mt-1">+ Add Slab</button></div>';
                } else {
                    area.innerHTML =
                        '<div class="ctr-field" style="max-width:200px;"><label class="ctr-lbl">Rate</label>' +
                        '<input type="number" step="0.00001" class="form-control form-control-sm ctr-formula-rate"></div>';
                }
            }

            function appendFormulaToken(wrap, token) {
                var ta = wrap.querySelector('.ctr-cf-formula');
                if (!ta || !token) return;
                ta.value += token;
                ta.focus();
            }

            function bindFormulaBuilderEvents(wrap) {
                $(wrap).find('.ctr-fb-add-field').on('click', function() {
                    var selected = $(wrap).find('.ctr-fb-fields').val() || [];
                    selected.forEach(function(token) {
                        appendFormulaToken(wrap, '{' + token + '}');
                    });
                });
                $(wrap).find('.ctr-fb-add-operator').on('click', function() {
                    appendFormulaToken(wrap, $(wrap).find('.ctr-fb-operators').val());
                });
                $(wrap).find('.ctr-fb-add-function').on('click', function() {
                    appendFormulaToken(wrap, $(wrap).find('.ctr-fb-functions').val());
                });
                $(wrap).find('.ctr-fb-fields, .ctr-fb-operators, .ctr-fb-functions').on('dblclick', 'option',
                    function() {
                        var token = $(this).val();
                        var isField = $(this).closest('select').hasClass('ctr-fb-fields');
                        appendFormulaToken(wrap, isField ? '{' + token + '}' : token);
                    });

                $(wrap).find('.ctr-fb-reset').on('click', function(e) {
                    e.preventDefault();
                    wrap.querySelector('.ctr-cf-formula').value = '';
                });
                var rtSelect = wrap.querySelector('.ctr-fb-ratetype');
                $(rtSelect).on('change', function() {
                    renderFormulaRateArea(wrap, this.value);
                });
                renderFormulaRateArea(wrap, rtSelect.value);
            }

            function chargeFormHtml() {
                return `
                    <!-- Nature + Accruals + Base Currency -->
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; gap:20px;">
            
                        <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
            
                            <!-- Nature -->
                            <div>
                                <label class="mr-3 ctr-natureaction-lbl">
                                    <input type="radio" name="ctr-cf-natureaction" value="Revenue" checked> Revenue
                                </label>
                                <label class="mr-3 ctr-natureaction-lbl">
                                    <input type="radio" name="ctr-cf-natureaction" value="Cost"> Cost
                                </label>
                                <label class="ctr-natureaction-lbl">
                                    <input type="radio" name="ctr-cf-natureaction" value="CostMarkup"> Cost with markup
                                </label>
                            </div>
            
                            <!-- Accruable / Reimbursable -->
                            <div class="ctr-cf-accruals-row" style="display:flex; gap:20px; align-items:center;">
                                <div class="ctr-cf-accruable-wrap">
                                    <label class="ctr-natureaction-lbl mb-0">
                                        <input type="checkbox" class="ctr-cf-accruable">
                                        Is Accruable
                                    </label>
                                </div>
            
                                <div class="ctr-cf-reimbursable-wrap">
                                    <label class="ctr-natureaction-lbl mb-0">
                                        <input type="checkbox" class="ctr-cf-reimbursable">
                                        Is Reimbursable (Adds to Revenue)
                                    </label>
                                </div>
                            </div>
            
                        </div>
            
                        <!-- Base Currency -->
                        <div class="ctr-cf-base-currency text-muted" style="font-size:.85rem;"></div>
                    </div>
            
                    <div class="ctr-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:12px 16px;">
            
                        <!-- Row 1 -->
                        <div class="ctr-field">
                            <label class="ctr-lbl ctr-required">Charge Code</label>
                            <input type="text" class="form-control form-control-sm ctr-cf-code" placeholder="Type to search..." autocomplete="off">
                            <input type="hidden" class="ctr-cf-code-hidden">
                        </div>
            
                        <div class="ctr-field">
                            <label class="ctr-lbl">Charge Description</label>
                            <input type="text" class="form-control form-control-sm ctr-cf-desc ctr-field-readonly" readonly>
                        </div>
            
                        <!-- Row 2 -->
                        <div class="ctr-field">
                            <label class="ctr-lbl ctr-required">Charge Based On</label>
                            <select class="form-control form-control-sm ctr-cf-basedon">
                                <option value="">-- Select --</option>
                            </select>
                        </div>
            
                        <div class="ctr-field">
                            <label class="ctr-lbl ctr-required">Based On UOM</label>
                            <select class="form-control form-control-sm ctr-cf-uom">
                                <option value="">Select Based On first</option>
                            </select>
                        </div>
    
                        
                        <div class="ctr-field">
                            <label class="ctr-lbl">Max Amount</label>
                            <input type="number" step="0.01" class="form-control form-control-sm ctr-cf-max">
                        </div>
                        
                            <!-- Row 4 -->
                            <div class="ctr-field">
                                <label class="ctr-lbl">Min Amount</label>
                                <input type="number" step="0.01" class="form-control form-control-sm ctr-cf-min">
                            </div>

                            <div class="ctr-field">
                                <label class="ctr-lbl ctr-required">Currency</label>
                                <select class="form-control form-control-sm ctr-cf-currency">
                                    <option value="">-- Select --</option>
                                </select>
                            </div>

                        
                            <div class="ctr-field">
                                <label class="ctr-lbl ctr-required">Charge Category</label>
                                <select class="form-control form-control-sm ctr-cf-category">
                                    <option value="">-- Select --</option>
                                </select>
                            </div>
                        
                        </div>
    
                        <div class="ctr-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:12px 16px; margin-top:12px;">
                            <!-- Row 5 -->
                            <div class="ctr-field">
                                <label class="ctr-lbl">Rate Type</label>
                                <select class="form-control form-control-sm ctr-cf-ratetype">
                                    <option value="">-- Select --</option>
                                    <option value="Single">Single</option>
                                    <option value="Slab Based">Slab Based</option>
                                    <option value="Step Up">Step Up</option>
                                    <option value="Formula">Formula</option>
                                    <option value="At Actual">At Actual</option>
                                </select>
                            </div>
                            
                            <div class="ctr-field ctr-cf-inline-rate"></div>
                        </div>

                        <!-- Markup section -->
                        <div class="ctr-cf-markup-wrap mt-2" style="display:none;">
                            <strong class="ctr-lbl">Markup</strong>
                        
                            <div class="ctr-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:12px 16px; margin-top:6px;">
                        
                                <div class="ctr-field d-flex" style="gap:8px;">
                                    <div style="flex:1;">
                                        <label class="ctr-lbl">Increase By</label>
                                        <input type="number" step="0.01" class="form-control form-control-sm ctr-mk-rate" placeholder="0.00">
                                    </div>
                                    <div style="flex:1;">
                                        <label class="ctr-lbl">&nbsp;</label>
                                        <select class="form-control form-control-sm ctr-mk-ratetype">
                                            <option value="Percentage">Percentage</option>
                                            <option value="Fixed">Fixed</option>
                                        </select>
                                    </div>
                                </div>
                        
                                <div class="ctr-field">
                                    <label class="ctr-lbl">Amount</label>
                                    <input type="number" step="0.01" class="form-control form-control-sm ctr-mk-amount" value="0.00" readonly>
                                </div>
                        
                            </div>
                        </div>
                
                        <!-- Slab / Formula area -->
                        <div class="ctr-cf-ratearea mt-2"></div>
                                    
                        <div class="d-flex justify-content-end mt-2">
                            <button type="button" class="btn btn-sm btn-secondary mr-2 ctr-cf-cancel">Cancel</button>
                            <button type="button" class="btn btn-sm btn-primary mr-2 ctr-cf-save-add-more">Save &amp; Add More</button>
                            <button type="button" class="btn btn-sm btn-success ctr-cf-save">Save</button>
                        </div>
                    `;
            }

            var equipmentTypeOptions = [];

            function loadEquipmentTypeOptions(callback) {
                if (equipmentTypeOptions.length > 0) {
                    if (typeof callback === 'function') callback();
                    return;
                }
                fetchMasterList('equipment-types', {}).done(function(res) {
                    if (res.success && res.data.length > 0) {
                        equipmentTypeOptions = res.data;
                    }
                    if (typeof callback === 'function') callback();
                }).fail(function() {
                    console.error('Failed to load equipment types');
                    if (typeof callback === 'function') callback();
                });
            }

            function initChargeCodeAutocomplete(wrap) {
                var $input = $(wrap).find('.ctr-cf-code');
                var $hidden = $(wrap).find('.ctr-cf-code-hidden');

                if ($input.hasClass('ui-autocomplete-input')) $input.autocomplete('destroy');

                $input.autocomplete({
                    minLength: 3,
                    delay: 300,
                    appendTo: $input.closest('.modal-body, .ctr-subform').length ?
                        $input.closest('.modal-body, .ctr-subform') : 'body',
                    source: function(request, response) {
                        fetchMasterList('charge-codes', {
                            office_code: OFFICE_CODE,
                            charge_type: 'Revenue',
                            filter: request.term
                        }).done(function(res) {
                            if (res.success && res.charge_codes.length) {
                                response(res.charge_codes.map(function(c) {
                                    return {
                                        label: (c.Code || '') + ' — ' + (c
                                            .Description || c.Name || c.Code || ''),
                                        value: c.Code || '',
                                        code: c.Code || '',
                                        description: c.Description || c.Name || c
                                            .Code || '',
                                        currency: c.Curr || ''
                                    };
                                }));
                            } else {
                                response([]);
                            }
                        }).fail(function() {
                            response([]);
                        });
                    },
                    select: function(event, ui) {
                        $input.val(ui.item.code);
                        $hidden.val(ui.item.code);
                        if (ui.item.description) $(wrap).find('.ctr-cf-desc').val(ui.item.description);
                        if (ui.item.currency) {
                            var $currencySelect = $(wrap).find('.ctr-cf-currency');
                            if ($currencySelect.find('option[value="' + ui.item.currency + '"]').length) {
                                $currencySelect.val(ui.item.currency);
                            }
                        }
                        return false;
                    }
                });
            }

            function loadChargeFormMasters(wrap, chargeToEdit) {
                fetchMasterList('charge-categories').done(function(res) {
                    if (res.success) populateSelect($(wrap).find('.ctr-cf-category'), res.data,
                        'Code', 'Name');
                });
                initChargeCodeAutocomplete(wrap);

                if (chargeToEdit.chargeCode) {
                    wrap.querySelector('.ctr-cf-code').value = chargeToEdit.chargeCode;
                    wrap.querySelector('.ctr-cf-code-hidden').value = chargeToEdit.chargeCode;
                    if (chargeToEdit.chargeDescription) {
                        wrap.querySelector('.ctr-cf-desc').value = chargeToEdit.chargeDescription;
                    }
                }
                fetchMasterList('based-on').done(function(res) {
                    if (res.success) {
                        var options = res.based_on_list.map(function(b) {
                            return {
                                Code: b.Code,
                                Label: b.Name
                            };
                        });
                        populateSelect($(wrap).find('.ctr-cf-basedon'), options, 'Code', 'Label',
                            chargeToEdit
                            .chargeBasedOn);
                    }
                });
                if (chargeToEdit.chargeBasedOn) {
                    var basedOnCode = chargeToEdit.chargeBasedOn;
                    var $uomSelect = $(wrap).find('.ctr-cf-uom');

                    if (basedOnCode === 'Equipment') {
                        // Load equipment types as UOM options
                        $uomSelect.empty().append('<option value="">-- Select Container Type --</option>').prop(
                            'disabled', true);
                        loadEquipmentTypeOptions(function() {
                            equipmentTypeOptions.forEach(function(item) {
                                var code = item.Code || item;
                                $uomSelect.append('<option value="' + code + '">' + code + '</option>');
                            });
                            $uomSelect.prop('disabled', false);
                            if (chargeToEdit.chargeBasedOnUOM) {
                                $uomSelect.val(chargeToEdit.chargeBasedOnUOM);
                            }
                        });
                    } else {
                        // Normal UOM fetch for other based‑on codes
                        fetchMasterList('based-on-uom', {
                            based_on_code: basedOnCode
                        }).done(function(res) {
                            if (res.success) {
                                if (!res.uom_list.length) {
                                    $uomSelect.empty().append('<option value="">No UOM required</option>').prop(
                                        'disabled', true);
                                    return;
                                }
                                $uomSelect.prop('disabled', false);
                                var options = res.uom_list.map(function(u) {
                                    return {
                                        Code: u.Code,
                                        Label: u.Name
                                    };
                                });
                                populateSelect($uomSelect, options, 'Code', 'Label', chargeToEdit
                                    .chargeBasedOnUOM);
                            }
                        });
                    }
                } else {
                    $(wrap).find('.ctr-cf-uom').empty().append('<option value="">Select Based On first</option>');
                }
                fetchMasterList('currencies', {
                    office_id: OFFICE_CODE
                }).done(function(res) {
                    if (res.success) {
                        var $sel = $(wrap).find('.ctr-cf-currency');
                        $sel.empty().append('<option value="">-- Select --</option>');
                        res.currencies.forEach(function(c) {
                            $sel.append('<option value="' + c.CurrencyCode + '">' + c
                                .CurrencyCode +
                                '</option>');
                        });
                        $sel.val(chargeToEdit.chargeCurr);
                    }
                });

                wrap.querySelector('.ctr-cf-desc').value = chargeToEdit.chargeDescription || '';
                wrap.querySelector('.ctr-cf-min').value = chargeToEdit.minAmount ?? '';
                wrap.querySelector('.ctr-cf-max').value = chargeToEdit.maxAmount ?? '';
                // Derive the single natureaction radio value from stored chargeNature + chargeAction
                var natureVal = chargeToEdit.chargeNature || '';
                var actionVal = chargeToEdit.chargeAction || '';
                var naVal = '';
                if (actionVal === 'M') {
                    naVal = 'CostMarkup';
                } else if (natureVal === 'Cost') {
                    naVal = 'Cost';
                } else if (natureVal === 'Revenue') {
                    naVal = 'Revenue';
                }
                if (naVal) {
                    $(wrap).find('input[name="ctr-cf-natureaction"][value="' + naVal + '"]').prop('checked', true);
                }
                $(wrap).find('.ctr-cf-markup-wrap').toggle(naVal === 'CostMarkup');
                $(wrap).find('.ctr-cf-accruals-row').toggle(naVal !== 'CostMarkup'); // <-- add this line

                if (actionVal === 'M') {
                    $(wrap).find('.ctr-mk-ratetype').val(chargeToEdit.markupType || 'Percentage');
                    wrap.querySelector('.ctr-mk-rate').value = chargeToEdit.markupRate ?? '';
                    recalcMarkupAmount(wrap);
                }

                var normalizedRateType = normalizeRateType(chargeToEdit.rateType);
                $(wrap).find('.ctr-cf-ratetype').val(normalizedRateType);
                $(wrap).find('.ctr-cf-inline-rate').html(inlineRateHtml(normalizedRateType));
                $(wrap).find('.ctr-cf-ratearea').html(rateAreaHtml(normalizedRateType));

                $(wrap).find('input[name="ctr-cf-natureaction"][value="CostMarkup"]').prop('disabled', false)
                    .closest('label').show();

                $(wrap).find('.ctr-cf-accruable').prop('checked', chargeToEdit.isAccruable == '1');
                $(wrap).find('.ctr-cf-reimbursable').prop('checked', chargeToEdit.isReimbursable == '1');
                $(wrap).find('.ctr-cf-accruable-wrap').toggle(naVal === 'Revenue');
                $(wrap).find('.ctr-cf-reimbursable-wrap').toggle(naVal === 'Cost');

                if (normalizedRateType === 'At Actual') {
                    // Rate is always 0 for At Actual — no input exists, so
                    // nothing to prefill. Downstream save will force 0.
                    $(wrap).find('input[name="ctr-cf-natureaction"][value="CostMarkup"]')
                        .prop('disabled', true)
                        .closest('label').css('opacity', '0.5');
                    $(wrap).find('.ctr-cf-markup-wrap').hide();
                } else if (normalizedRateType === 'Single') {
                    wrap.querySelector('.ctr-cf-rate').value = chargeToEdit.rate ?? '';
                } else if (normalizedRateType === 'Slab Based' || normalizedRateType === 'Step Up') {
                    fillSlabRows(wrap.querySelector('.ctr-slab-rows'), chargeToEdit.rateSlab);
                } else if (normalizedRateType === 'Formula') {
                    bindFormulaBuilderEvents(wrap);
                    wrap.querySelector('.ctr-cf-formula').value = chargeToEdit.formula || '';
                }
            }

            function recalcMarkupAmount(wrap) {
                var baseRate = parseFloat(wrap.querySelector('.ctr-cf-rate')?.value) || 0;
                var mkRate = parseFloat(wrap.querySelector('.ctr-mk-rate')?.value) || 0;
                var mkType = wrap.querySelector('.ctr-mk-ratetype')?.value;

                var markedUpRate = mkType === 'Fixed' ?
                    baseRate + mkRate :
                    baseRate + (baseRate * mkRate / 100);

                var amountField = wrap.querySelector('.ctr-mk-amount');
                if (amountField) amountField.value = markedUpRate.toFixed(5);
            }

            function bindChargeFormEvents(svcId, chargeId, wrap, chargeToEdit) {
                $(wrap).find('.ctr-cf-ratetype').on('change', function() {
                    $(wrap).find('.ctr-cf-inline-rate').html(inlineRateHtml(this.value));
                    $(wrap).find('.ctr-cf-ratearea').html(rateAreaHtml(this.value));
                    if (this.value === 'Formula') bindFormulaBuilderEvents(wrap);

                    // At Actual has no rate to mark up — force Revenue/Cost only
                    if (this.value === 'At Actual') {
                        var $mkRadio = $(wrap).find('input[name="ctr-cf-natureaction"][value="CostMarkup"]');
                        if ($mkRadio.is(':checked')) {
                            $(wrap).find('input[name="ctr-cf-natureaction"][value="Revenue"]')
                                .prop('checked', true)
                                .trigger('change');
                        }
                        $mkRadio.prop('disabled', true)
                            .closest('label').css('opacity', '0.5');
                        $(wrap).find('.ctr-cf-markup-wrap').hide();
                    } else {
                        $(wrap).find('input[name="ctr-cf-natureaction"][value="CostMarkup"]')
                            .prop('disabled', false)
                            .closest('label').css('opacity', '1');
                    }
                });
                $(wrap).find('.ctr-cf-cancel').on('click', function() {
                    var modal = $(wrap).closest('.modal');
                    if (modal.length) {
                        modal.modal('hide');
                    } else {
                        wrap.style.display = 'none';
                        wrap.innerHTML = '';
                    }
                });
                $(wrap).find('input[name="ctr-cf-natureaction"]').on('change', function() {
                    $(wrap).find('.ctr-cf-markup-wrap').toggle(this.value === 'CostMarkup');
                    $(wrap).find('.ctr-cf-accruals-row').toggle(this.value !== 'CostMarkup');

                    var isRevenue = (this.value === 'Revenue');
                    $(wrap).find('.ctr-cf-accruable-wrap').toggle(isRevenue);
                    $(wrap).find('.ctr-cf-reimbursable-wrap').toggle(!isRevenue && this.value === 'Cost');
                    if (!isRevenue) $(wrap).find('.ctr-cf-accruable').prop('checked', false);
                    if (isRevenue) $(wrap).find('.ctr-cf-reimbursable').prop('checked', false);

                    var isRevenueForFormula = (this.value === 'Revenue') ? 1 : 0;
                    loadFormulaFields(svcId, isRevenueForFormula).done(function(res) {
                        if (!res.success) return;
                        FORMULA_FIELDS = res.data.map(function(name) {
                            return {
                                label: name,
                                token: name
                            };
                        });
                        var $fieldsSelect = $(wrap).find('.ctr-fb-fields');
                        if ($fieldsSelect.length) {
                            $fieldsSelect.html(formulaOptionsHtml(FORMULA_FIELDS));
                        }
                    });
                });
                // Slab add/remove for main charge — delegated so it also catches
                // the Formula section's slab table, which is created dynamically
                // later via renderFormulaRateArea() and doesn't exist at bind time.
                $(wrap).on('click', '.ctr-slab-add-row', function() {
                    addSlabRow(this.closest('.ctr-slab-wrap').parentNode || wrap);
                });
                $(wrap).on('click', '.ctr-slab-row-remove', function() {
                    $(this).closest('tr').remove();
                });

                $(wrap).on('input', '.ctr-mk-rate, .ctr-cf-rate', function() {
                    recalcMarkupAmount(wrap);
                });
                $(wrap).on('change', '.ctr-mk-ratetype', function() {
                    recalcMarkupAmount(wrap);
                });

                $(wrap).find('.ctr-cf-basedon').on('change', function() {
                    var basedOnCode = $(this).val();
                    var $uomSelect = $(wrap).find('.ctr-cf-uom');
                    if (!basedOnCode) {
                        $uomSelect.empty().append('<option value="">Select Based On first</option>');
                        return;
                    }
                    // Reuse the same logic as in loadChargeFormMasters
                    if (basedOnCode === 'Equipment') {
                        $uomSelect.empty().append('<option value="">-- Select Container Type --</option>').prop(
                            'disabled', true);
                        loadEquipmentTypeOptions(function() {
                            equipmentTypeOptions.forEach(function(item) {
                                var code = item.Code || item;
                                $uomSelect.append('<option value="' + code + '">' + code +
                                    '</option>');
                            });
                            $uomSelect.prop('disabled', false);
                        });
                    } else {
                        fetchMasterList('based-on-uom', {
                            based_on_code: basedOnCode
                        }).done(function(res) {
                            if (res.success) {
                                if (!res.uom_list.length) {
                                    $uomSelect.empty().append(
                                        '<option value="">No UOM required</option>').prop(
                                        'disabled', true);
                                    return;
                                }
                                $uomSelect.prop('disabled', false);
                                var options = res.uom_list.map(function(u) {
                                    return {
                                        Code: u.Code,
                                        Label: u.Name
                                    };
                                });
                                populateSelect($uomSelect, options, 'Code', 'Label');
                            }
                        });
                    }
                });
                // ── Auto‑fill description and currency on charge code selection ──
                $(wrap).find('.ctr-cf-code').on('change', function() {
                    var $selected = $(this).find('option:selected');
                    var desc = $selected.data('description') || '';
                    var curr = $selected.data('currency') || '';
                    if (desc) $(wrap).find('.ctr-cf-desc').val(desc);
                    if (curr) $(wrap).find('.ctr-cf-currency').val(curr);
                    // Show code-only in the closed box after selection
                    if ($selected.val()) $selected.text($selected.val());
                });
                $(wrap).find('.ctr-cf-code').on('mousedown', function() {
                    // Restore full names right before the dropdown opens
                    $(this).find('option').each(function() {
                        var name = $(this).data('description');
                        if (name) $(this).text(name);
                    });
                });
                $(wrap).find('.ctr-cf-save').on('click', function() {
                    saveChargeUpdate(svcId, chargeId, wrap, false);
                });
                $(wrap).find('.ctr-cf-save-add-more').on('click', function() {
                    saveChargeUpdate(svcId, chargeId, wrap, true);
                });
            }

            function incrementChargeGroupTotal(newRowData, nature) {
                var groupKey = (nature === 'Cost') ? 'cost' : 'rev';
                var $header = $('.cc-group-header[data-target$="' + groupKey + '"]').filter(function() {
                    return $(this).data('target').indexOf('chargeBody') === 0;
                });
                if (!$header.length) return;
                var $total = $header.find('.cc-group-total');
                var current = parseInt($total.text(), 10) || 0;
                $total.text(current + 1);
            }

            function saveChargeUpdate(svcId, chargeId, wrap, keepOpen) {
                var rateType = wrap.querySelector('.ctr-cf-ratetype').value;
                var $uomField = wrap.querySelector('.ctr-cf-uom');
                var uomNotNeeded = $uomField.disabled;

                var required = {
                    category: wrap.querySelector('.ctr-cf-category').value,
                    code: wrap.querySelector('.ctr-cf-code-hidden').value.trim(),
                    desc: wrap.querySelector('.ctr-cf-desc').value.trim(),
                    basedOn: wrap.querySelector('.ctr-cf-basedon').value,
                    currency: wrap.querySelector('.ctr-cf-currency').value,
                    rateType: rateType
                };
                if (!uomNotNeeded) {
                    required.basedOnUOM = $uomField.value.trim();
                }
                var missing = Object.keys(required).filter(function(k) {
                    return !required[k];
                });
                if (missing.length) {
                    alert('Please fill required Charge fields: ' + missing.join(', '));
                    return;
                }

                var naRadio = wrap.querySelector('input[name="ctr-cf-natureaction"]:checked');
                if (!naRadio) {
                    alert('Please select Revenue, Cost, or Cost with markup.');
                    return;
                }
                var naVal = naRadio.value;
                // Revenue  → Revenue       / action R
                // Cost     → Cost          / action R
                // CostMarkup → Cost with Markup / action M
                var derivedNature =
                    naVal === 'Revenue' ? 'Revenue' :
                    naVal === 'CostMarkup' ? 'Cost with Markup' :
                    'Cost';
                var derivedAction = (naVal === 'CostMarkup') ? 'M' : 'R';

                var charge = {
                    chargeNature: derivedNature,
                    chargeCode: required.code,
                    chargeDescription: required.desc,
                    chargeBasedOn: required.basedOn,
                    chargeCategory: required.category,
                    chargeBasedOnUOM: wrap.querySelector('.ctr-cf-uom').value.trim(),
                    chargeCurr: wrap.querySelector('.ctr-cf-currency').value,
                    minAmount: wrap.querySelector('.ctr-cf-min').value !== '' ? parseFloat(parseFloat(
                        wrap
                        .querySelector('.ctr-cf-min').value).toFixed(2)) : null,
                    maxAmount: wrap.querySelector('.ctr-cf-max').value !== '' ? parseFloat(parseFloat(
                        wrap
                        .querySelector('.ctr-cf-max').value).toFixed(2)) : null,
                    rateType: rateType,
                    rate: null,
                    rateSlab: null,
                    formula: null
                };

                // sp_Quote_DetailsUpdate routing (dispatcher)
                charge.OperationType = 'WorkbookCharges';
                charge.Action = chargeId ? 'Edit' : 'Add';

                if (rateType === 'Single') {
                    var rateInput = wrap.querySelector('.ctr-cf-rate');
                    if (!rateInput) {
                        alert('Rate field is missing from the form. Please close and reopen Add Charge.');
                        return;
                    }
                    var rateVal = rateInput.value;
                    charge.rate = parseFloat(parseFloat(rateVal).toFixed(5));
                } else if (rateType === 'Slab Based' || rateType === 'Step Up') {
                    var slabType = wrap.querySelector('.ctr-slab-type')?.value || 'LessThanOrEqual';
                    var rows = wrap.querySelectorAll('.ctr-slab-rows tr');
                    var slabs = [];
                    var valid = true;
                    rows.forEach(function(row) {
                        var minVal = row.querySelector('.ctr-slab-min')?.value || '0';
                        var upto = row.querySelector('.ctr-slab-upto')?.value;
                        var value = row.querySelector('.ctr-slab-value')?.value;
                        var formula = row.querySelector('.ctr-slab-formula')?.value || '';
                        if (upto === '' || value === '') {
                            valid = false;
                            return;
                        }
                        slabs.push({
                            Min: parseFloat(minVal) || 0,
                            Slab: parseFloat(upto),
                            value: parseFloat(value),
                            Formula: formula
                        });
                    });
                    if (!valid || !slabs.length) {
                        alert('Please fill all slab rows (Min, Slab, Value are required).');
                        return;
                    }
                    charge.rateSlab = {
                        Type: slabType,
                        Slabs: slabs
                    };
                } else if (rateType === 'Formula') {
                    var formulaVal = wrap.querySelector('.ctr-cf-formula').value.trim();
                    if (!formulaVal) {
                        alert('Formula is required for Formula rate type.');
                        return;
                    }
                    charge.formula = formulaVal;
                } else if (rateType === 'At Actual') {
                    // At Actual has no rate — send 0 or null depending on SP expectation.
                    charge.rate = 0; // ← change to null if SP stores NULL
                }

                // --- Charge Action and Markup ---
                var actionVal = derivedAction;
                charge.chargeAction = actionVal;

                charge.isAccruable = wrap.querySelector('.ctr-cf-accruable').checked ? '1' : '0';
                charge.isReimbursable = wrap.querySelector('.ctr-cf-reimbursable').checked ? '1' : '0';

                // toBeAdded triggers the SP's auto-created counterpart charge — this
                // fires for Cost-with-Markup (actionVal === 'M') AND for plain
                // Accruable/Reimbursable checkboxes, since both create a second row.
                charge.toBeAdded = (actionVal === 'M' || charge.isAccruable === '1' || charge.isReimbursable === '1') ?
                    1 : 0;

                charge.markupRateType = null;
                charge.markupRate = null;
                charge.markupFormula = null;
                charge.markupRateSlab = null;

                if (actionVal === 'M') {
                    if (rateType === 'At Actual') {
                        alert('Cost with markup is not allowed when Rate Type is "At Actual". ' +
                            'Please choose Revenue or Cost, or select a different Rate Type.');
                        return;
                    }
                    var mkType = wrap.querySelector('.ctr-mk-ratetype').value;
                    var mkRateVal = wrap.querySelector('.ctr-mk-rate').value;
                    if (!mkType || mkRateVal === '') {
                        alert('Markup type and Increase By rate are required for Cost with Markup.');
                        return;
                    }
                    var baseRateVal = parseFloat(charge.rate) || 0;
                    var mkRateNum = parseFloat(mkRateVal);
                    var markedUpRate = mkType === 'Fixed' ?
                        baseRateVal + mkRateNum :
                        baseRateVal + (baseRateVal * mkRateNum / 100);

                    charge.markupRateType = mkType;
                    charge.markupType = mkType;
                    charge.markupRate = parseFloat(markedUpRate.toFixed(5));

                    charge.incrementBy = mkRateNum;
                    charge.incrementType = mkType;

                } else if (charge.toBeAdded === 1) {
                    charge.markupRateType = charge.rateType;
                    charge.markupRate = charge.rate;
                    charge.markupRateSlab = charge.rateSlab;
                    charge.markupFormula = charge.formula;
                }

                var saveBtn = wrap.querySelector('.ctr-cf-save');
                var originalText = saveBtn.textContent;
                saveBtn.disabled = true;
                saveBtn.textContent = 'Updating...';

                var isNew = !chargeId;
                var url = '/contracts/' + encodeURIComponent(CONTRACT_ID) + '/services/' +
                    encodeURIComponent(svcId) + '/charges';
                if (!isNew) url += '/' + encodeURIComponent(chargeId);

                $.ajax({
                    url: url,
                    type: isNew ? 'POST' : 'PUT',
                    data: $.extend({
                        _token: document.querySelector('meta[name="csrf-token"]').content
                    }, charge),
                    dataType: 'json'
                }).done(function(res) {
                    if (!res.success) {
                        alert('Failed to update charge: ' + (res.message || 'Unknown error'));
                        return;
                    }
                    if (keepOpen) {
                        alert('Charge saved. Add another below.');
                        var svcIdForNew = svcId;
                        var emptyCharge = {
                            chargeId: null,
                            chargeCode: '',
                            chargeDescription: '',
                            chargeBasedOn: '',
                            chargeBasedOnUOM: '',
                            chargeCurr: '',
                            minAmount: null,
                            maxAmount: null,
                            rateType: '',
                            rate: null,
                            formula: null,
                            rateSlab: null,
                            chargeAction: '',
                            markupRateType: null,
                            markupRate: null,
                            markupFormula: null,
                            markupRateSlab: null
                        };
                        wrap.innerHTML = chargeFormHtml();
                        bindChargeFormEvents(svcIdForNew, null, wrap, emptyCharge);
                        loadChargeFormMasters(wrap, emptyCharge);
                        incrementChargeGroupTotal(res.data, res.nature || charge.chargeNature);
                    } else {
                        alert('Charge updated. Reloading to reflect changes...');
                        window.location.reload();
                    }
                }).fail(function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Update failed.';
                    alert('Failed to update contract: ' + msg);
                }).always(function() {
                    saveBtn.disabled = false;
                    saveBtn.textContent = originalText;
                });
            }

            $(document).on('click', '.ce-edit-charge', function() {
                var svcId = $(this).data('svc');
                var chargeToEdit = $(this).data('charge');
                var wrap = document.getElementById('chargeform-' + svcId);
                if (!wrap || !chargeToEdit) return;

                wrap.style.display = 'block';
                wrap.innerHTML = chargeFormHtml();
                bindChargeFormEvents(svcId, chargeToEdit.chargeId, wrap, chargeToEdit);
                loadChargeFormMasters(wrap, chargeToEdit);

                var isRevenueForFormula = chargeToEdit.chargeNature === 'Cost' ? 0 : 1;
                loadFormulaFields(svcId, isRevenueForFormula).done(function(r) {
                    if (r.success) FORMULA_FIELDS = r.data.map(function(name) {
                        return {
                            label: name,
                            token: name
                        };
                    });
                });

                wrap.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            });

            $(document).on('click', '.ce-add-charge-btn', function() {
                var svcId = $(this).data('svc');
                var wrap = document.getElementById('addChargeModalBody');
                if (!wrap) return;

                var emptyCharge = {
                    chargeId: null,
                    chargeCode: '',
                    chargeDescription: '',
                    chargeBasedOn: '',
                    chargeBasedOnUOM: '',
                    chargeCurr: '',
                    minAmount: null,
                    maxAmount: null,
                    rateType: '',
                    rate: null,
                    formula: null,
                    rateSlab: null,
                    chargeAction: '',
                    markupRateType: null,
                    markupRate: null,
                    markupFormula: null,
                    markupRateSlab: null
                };

                wrap.innerHTML = chargeFormHtml();
                bindChargeFormEvents(svcId, null, wrap, emptyCharge);
                loadChargeFormMasters(wrap, emptyCharge);
                $(wrap).find('input[name="ctr-cf-natureaction"]:checked').trigger(
                    'change');
                loadFormulaFields(svcId, 1).done(function(res) {
                    if (res.success) FORMULA_FIELDS = res.data.map(function(name) {
                        return {
                            label: name,
                            token: name
                        };
                    });
                });
                $('#addChargeModal').modal('show');
            });

            $('#addChargeModal').on('hidden.bs.modal', function() {
                $('#addChargeModalBody').empty();
            });

            // Matches server-side number_format(x, 2) — adds thousand separators.
            function ceFormatTotal(num) {
                return Number(num || 0).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            $(document).on('click', '.ce-delete-charge', function() {
                var $btn = $(this);
                var svcId = $btn.data('svc');
                var chargeId = $btn.data('charge-id');
                if (!confirm('Delete this charge?')) return;

                // Resolve every element we'll touch BEFORE the AJAX call. If the DOM
                // mutates while the request is in flight, we still hold valid refs.
                var $row = $btn.closest('tr.ce-charge-row');
                var $section = $row.closest('.ce-charge-section');
                var $sectionBody = $section.find('.ce-charge-section-body');
                var $tableWrap = $sectionBody.find('.ce-charges-table-wrap');
                var $tableBody = $tableWrap.find('table.ce-charges-table tbody');

                // Bail with a clear console trace if the path is broken — never
                // silently manipulate an unexpected DOM shape.
                if (!$row.length || !$section.length || !$sectionBody.length || !$tableWrap.length) {
                    console.error('[Delete charge] DOM path incomplete:', {
                        row: $row.length,
                        section: $section.length,
                        sectionBody: $sectionBody.length,
                        tableWrap: $tableWrap.length,
                        tableBody: $tableBody.length
                    });
                    alert('Could not locate the charge row in the page. Please refresh and try again.');
                    return;
                }

                $.ajax({
                    url: '/contracts/' + encodeURIComponent(CONTRACT_ID) + '/services/' +
                        encodeURIComponent(svcId) + '/charges/' + encodeURIComponent(chargeId),
                    type: 'DELETE',
                    data: {
                        _token: document.querySelector('meta[name="csrf-token"]').content
                    },
                    dataType: 'json'
                }).done(function(res) {
                    if (!res.success) {
                        alert('Failed to delete charge: ' + (res.message || 'Unknown error'));
                        return;
                    }

                    // 1. Remove ONLY the deleted row.
                    $row.remove();

                    // 2. Remove category headers that no longer own any charge rows.
                    //    A category is "orphaned" if its next sibling is either another
                    //    category row or does not exist.
                    $tableBody.find('.ce-charge-category-row').each(function() {
                        var $cat = $(this);
                        var $next = $cat.next('tr');
                        if (!$next.length || $next.hasClass('ce-charge-category-row')) {
                            $cat.remove();
                        }
                    });

                    // 3. Recompute the section total from the rows that survived.
                    //    Column layout: 0=Nature 1=Code 2=Desc 3=RateType 4=Rate 5=Curr 6=Actions
                    var newTotal = 0;
                    $tableBody.find('tr.ce-charge-row').each(function() {
                        var rate = parseFloat($(this).find('td').eq(4).text().trim());
                        if (!isNaN(rate)) newTotal += rate;
                    });
                    $section.find('.ce-sec-total').text('Total: ' + ceFormatTotal(newTotal));

                    // 4. Show the empty state ONLY when every charge row is gone.
                    //    Keep the table-wrap element in the DOM (just hidden) so nothing
                    //    downstream is holding a stale reference to a removed node.
                    var remaining = $tableBody.find('tr.ce-charge-row').length;
                    if (remaining === 0) {
                        $tableWrap.hide();
                        if (!$sectionBody.find('.cc-empty-msg').length) {
                            var isCost = $section.find('.ce-sec-title').text().trim() === 'Cost';
                            $sectionBody.append(
                                '<div class="cc-empty-msg">' +
                                (isCost ? 'No cost charges.' : 'No revenue charges.') +
                                '</div>'
                            );
                        }
                    } else {
                        // Defensive: if a stale empty message somehow lingers, drop it.
                        $sectionBody.find('.cc-empty-msg').remove();
                        $tableWrap.show();
                    }
                }).fail(function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed.';
                    alert('Failed to delete charge: ' + msg);
                });
            });
        })();
    </script>
@stop
