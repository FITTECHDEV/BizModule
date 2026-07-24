@extends('adminlte::page')

@section('title', 'Edit Contract')

@section('content')
    <div class="container-fluid" id="contractEditContent">

        {{-- ── Header (Contract Info) ─────────────────────────────────────── --}}
        <div class="cc-box cc-box-compact mb-3">
            <div class="cc-box-title d-flex align-items-center">
                <span id="ceContractNo">{{ $contractInfo->ContractNo }}</span>
                <a href="{{ route('contracts.view', $contractInfo->Id) }}" class="btn btn-secondary btn-sm ml-auto">
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
                                    <div class="ctr-field mb-2 d-flex" style="gap:8px;">
                                        <div style="flex:1;"><label class="ctr-lbl">POL</label>
                                            <input type="text"
                                                class="form-control form-control-sm ce-svc-field ce-svc-pol"
                                                value="{{ $service->POL }}" placeholder="Type at least 2 characters..."
                                                disabled>
                                        </div>
                                        <div style="flex:1;"><label class="ctr-lbl">Place of Receipt</label>
                                            <input type="text"
                                                class="form-control form-control-sm ce-svc-field ce-svc-por"
                                                value="{{ $service->POR }}" placeholder="Type at least 2 characters..."
                                                disabled>
                                        </div>
                                    </div>
                                    <div class="ctr-field d-flex" style="gap:8px;">
                                        <div style="flex:1;"><label class="ctr-lbl">POD</label>
                                            <input type="text"
                                                class="form-control form-control-sm ce-svc-field ce-svc-pod"
                                                value="{{ $service->POD }}" placeholder="Type at least 2 characters..."
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
                            </div>

                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" class="btn btn-sm btn-outline-danger mr-auto ce-delete-service-btn"
                                    data-svc="{{ $service->Id }}">
                                    <i class="fas fa-trash"></i> Delete Service
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary ce-edit-service-btn">
                                    <i class="fas fa-pen"></i> Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary mr-2 ce-cancel-service-btn"
                                    style="display:none;">
                                    Cancel
                                </button>
                                <button type="button" class="btn btn-sm btn-success ce-save-service-btn"
                                    style="display:none;">
                                    Save
                                </button>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong class="ctr-lbl mb-0">Charges</strong>
                                <button type="button" class="btn btn-sm btn-outline-primary ce-add-charge-btn"
                                    data-svc="{{ $service->Id }}">
                                    <i class="fas fa-plus"></i> Add Charge
                                </button>
                            </div>
                            @if (isset($charges[$service->Id]) && $charges[$service->Id]->count())
                                <div class="cc-table-wrap">
                                    <table class="cc-table">
                                        <thead>
                                            <tr>
                                                <th>Code</th>
                                                <th>Description</th>
                                                <th>Based On</th>
                                                <th>UOM</th>
                                                <th>Rate Type</th>
                                                <th>Rate</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($charges[$service->Id] as $charge)
                                                @php
                                                    $slabs = $charge->RateSlabJson
                                                        ? json_decode($charge->RateSlabJson, true)
                                                        : null;
                                                    $chargeJs = [
                                                        'id' => 'chg_' . $charge->Id,
                                                        'chargeId' => $charge->Id,
                                                        'chargeCode' => $charge->ChargeCode,
                                                        'chargeCategory' => $charge->ChargeCatagory,
                                                        'chargeDescription' => $charge->ChargeDescription,
                                                        'chargeBasedOn' => $charge->ChargeBasedon,
                                                        'chargeBasedOnUOM' => $charge->ChargeBasedOnUOM,
                                                        'chargeCurr' => $charge->ChargeCurr,
                                                        'minAmount' => $charge->MinAmount,
                                                        'maxAmount' => $charge->MaxAmount,
                                                        'rateType' => $charge->RateType,
                                                        'rate' => $charge->Rate,
                                                        'formula' => $charge->Formula,
                                                        'rateSlab' => $slabs,
                                                        'chargeAction' => $charge->ChargeAction ?? 'R',
                                                        'markupRateType' => $charge->MarkupRateType ?? null,
                                                        'markupRate' => $charge->MarkupRate ?? null,
                                                        'markupFormula' => $charge->MarkupFormula ?? null,
                                                        'markupRateSlab' =>
                                                            isset($charge->MarkupRateSlab) && $charge->MarkupRateSlab
                                                                ? json_decode($charge->MarkupRateSlab, true)
                                                                : null,
                                                        'isAccruable' => $charge->IsAccruable ?? '0',
                                                        'isReimbursable' => $charge->IsReimbursable ?? '0',
                                                    ];
                                                @endphp
                                                <tr>
                                                    <td>{{ $charge->ChargeCode }}</td>
                                                    <td>{{ $charge->ChargeDescription }}</td>
                                                    <td>{{ $charge->ChargeBasedon }}</td>
                                                    <td>{{ $charge->ChargeBasedOnUOM }}</td>
                                                    <td>{{ $charge->RateType }}</td>
                                                    <td>
                                                        @if ($charge->RateType === 'Single')
                                                            {{ $charge->Rate }}
                                                        @elseif(in_array($charge->RateType, ['SlabBased', 'Slab Based', 'StepUp', 'Step Up']))
                                                            {{ is_array($slabs) ? count($slabs) . ' slabs' : '—' }}
                                                        @elseif($charge->RateType === 'Formula')
                                                            {{ $charge->Formula }}
                                                        @else—
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-pen ce-edit-charge"
                                                            style="cursor:pointer;color:#4B49AC;" title="Edit"
                                                            data-svc="{{ $service->Id }}"
                                                            data-charge='@json($chargeJs)'></i>
                                                        <i class="fas fa-trash ce-delete-charge ml-2"
                                                            style="cursor:pointer;color:#dc3545;" title="Delete"
                                                            data-svc="{{ $service->Id }}"
                                                            data-charge-id="{{ $charge->Id }}"></i>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="cc-empty-msg">No charges for this service.</div>
                            @endif
                            <div class="ctr-subform mt-2" id="chargeform-{{ $service->Id }}" style="display:none;">
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
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script>
        (function() {
            var CONTRACT_ID = @json($contractInfo->Id);
            var OFFICE_CODE = @json($officeCode ?? '');
            var MASTER_BASE = '{{ url('/contracts/masters') }}';

            // ── Collapsible groups ────────────────────────────────────────────
            document.addEventListener('click', function(e) {
                var header = e.target.closest('.cc-group-header');
                if (header) {
                    var body = document.getElementById(header.getAttribute('data-target'));
                    header.classList.toggle('collapsed');
                    if (body) body.classList.toggle('collapsed');
                }
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
                    'StepUp': 'Step Up'
                };
                return map[rt] || rt;
            }


            // ── Service sections: populate dropdowns + prefill existing values ─
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
                    ];
                @endphp
                SERVICE_INITIAL['{{ $service->Id }}'] = @json($serviceInitial);
            @endforeach

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
                    '<div class="ctr-field mb-2 d-flex" style="gap:8px;">' +
                    '<div style="flex:1;"><label class="ctr-lbl">POL</label>' +
                    '<input type="text" class="form-control form-control-sm ce-svc-pol" placeholder="POL code"></div>' +
                    '<div style="flex:1;"><label class="ctr-lbl">Place of Receipt</label>' +
                    '<input type="text" class="form-control form-control-sm ce-svc-por" placeholder="POR code"></div>' +
                    '</div>' +
                    '<div class="ctr-field d-flex" style="gap:8px;">' +
                    '<div style="flex:1;"><label class="ctr-lbl">POD</label>' +
                    '<input type="text" class="form-control form-control-sm ce-svc-pod" placeholder="POD code"></div>' +
                    '<div style="flex:1;"><label class="ctr-lbl">Place of Delivery</label>' +
                    '<input type="text" class="form-control form-control-sm ce-svc-poe" placeholder="POE code"></div>' +
                    '</div>' +
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
                        placeOfDelivery: $(wrap).find('.ce-svc-poe').val().trim()
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

                var $body = $(body);
                var svcId = $body.data('ce-svc-id');
                console.log('svcId on expand:', svcId); // TEMP DEBUG

                initLocationAutocomplete('ce-svc-pol-' + svcId, null, null);
                initLocationAutocomplete('ce-svc-por-' + svcId, null, 'ce-svc-coo-' + svcId);
                initLocationAutocomplete('ce-svc-pod-' + svcId, null, null);
                initLocationAutocomplete('ce-svc-poe-' + svcId, null, 'ce-svc-cod-' + svcId);

                body.dataset.locAutocompleteInit = '1';
            });

            // ── Edit / Cancel toggle for a service block ────────────────────────
            function setServiceEditable($group, editable) {
                $group.find('.ce-svc-field').prop('disabled', !editable);
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
                    placeOfDelivery: $group.find('.ce-svc-poe').val().trim()
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
                return '';
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

            function markupInlineRateHtml(rateType) {
                if (rateType === 'Single') {
                    return '<label class="ctr-lbl">Markup Rate</label>' +
                        '<input type="number" step="0.00001" class="form-control form-control-sm ctr-mk-rate">';
                }
                return '';
            }


            function markupSlabRowHtml() {
                return '<tr>' +
                    '<td><input type="number" class="form-control form-control-sm ctr-mk-slab-min"></td>' +
                    '<td><input type="number" class="form-control form-control-sm ctr-mk-slab-upto"></td>' +
                    '<td><input type="number" step="0.01" class="form-control form-control-sm ctr-mk-slab-value"></td>' +
                    '<td><input type="text" class="form-control form-control-sm ctr-mk-slab-formula" placeholder="optional"></td>' +
                    '<td><span class="ctr-mk-slab-row-remove">&times;</span></td></tr>';
            }

            function markupRateAreaHtml(rateType) {
                // Markup is Single-only for now — slab/formula markup deferred.
                return '';
            }

            function fillMarkupSlabRows(tbody, rateSlabData) {
                if (!tbody || !rateSlabData) return;
                var slabs = Array.isArray(rateSlabData) ? rateSlabData : (rateSlabData.Slabs || []);
                if (!slabs.length) return;
                tbody.innerHTML = '';
                slabs.forEach(function(s) {
                    var row = document.createElement('tr');
                    row.innerHTML =
                        '<td><input type="number" class="form-control form-control-sm ctr-mk-slab-min" value="' +
                        (s.Min ?? '') + '"></td>' +
                        '<td><input type="number" class="form-control form-control-sm ctr-mk-slab-upto" value="' +
                        (s.Slab ?? s.upto ?? '') + '"></td>' +
                        '<td><input type="number" step="0.01" class="form-control form-control-sm ctr-mk-slab-value" value="' +
                        (s.value ?? '') + '"></td>' +
                        '<td><input type="text" class="form-control form-control-sm ctr-mk-slab-formula" value="' +
                        (s.Formula ?? '') + '"></td>' +
                        '<td><span class="ctr-mk-slab-row-remove">&times;</span></td>';
                    tbody.appendChild(row);
                });
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
                        <select class="form-control form-control-sm ctr-cf-code">
                            <option value="">-- Select --</option>
                        </select>
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
        
                    <!-- Row 3 -->
                    <div class="ctr-field">
                        <label class="ctr-lbl">Currency</label>
                        <select class="form-control form-control-sm ctr-cf-currency">
                            <option value="">-- Select --</option>
                        </select>
                    </div>

                    
                    <div class="ctr-field">
                        <label class="ctr-lbl">Charge Category</label>
                        <select class="form-control form-control-sm ctr-cf-category">
                            <option value="">-- Select --</option>
                        </select>
                    </div>
            
                        <!-- Row 4 -->
                        <div class="ctr-field">
                            <label class="ctr-lbl">Min Amount</label>
                            <input type="number" step="0.01" class="form-control form-control-sm ctr-cf-min">
                        </div>
            
                        <div class="ctr-field">
                            <label class="ctr-lbl">Max Amount</label>
                            <input type="number" step="0.01" class="form-control form-control-sm ctr-cf-max">
                        </div>
            
                        <!-- Row 5 -->
                        <div class="ctr-field">
                            <label class="ctr-lbl">Rate Type</label>
                            <select class="form-control form-control-sm ctr-cf-ratetype">
                                <option value="">-- Select --</option>
                                <option value="Single">Single</option>
                                <option value="Slab Based">Slab Based</option>
                                <option value="Step Up">Step Up</option>
                                <option value="Formula">Formula</option>
                            </select>
                        </div>
                        
                        <div class="ctr-field ctr-cf-inline-rate"></div>
                    </div>
            
                    <!-- Markup section -->
                    <div class="ctr-cf-markup-wrap mt-2" style="display:none;">
                        <strong class="ctr-lbl">Markup</strong>
            
                    <div class="ctr-field mt-1 d-flex" style="gap:8px; max-width:340px;">
                        <div style="flex:1;">
                            <label class="ctr-lbl">Increase By</label>
                            <input type="number" step="0.01" class="form-control form-control-sm ctr-mk-rate" value="0">
                        </div>
                        <div style="flex:1;">
                            <label class="ctr-lbl">&nbsp;</label>
                            <select class="form-control form-control-sm ctr-mk-ratetype">
                                <option value="Percentage">Percentage</option>
                                <option value="Fixed">Fixed</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="ctr-field" style="max-width:220px;">
                        <label class="ctr-lbl">Amount</label>
                        <input type="number" step="0.01" class="form-control form-control-sm ctr-mk-amount" readonly>
                    </div>
            
                        <div class="ctr-mk-ratearea mt-2"></div>
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

            function loadChargeFormMasters(wrap, chargeToEdit) {
                loadBaseCurrency(wrap);
                fetchMasterList('charge-categories').done(function(res) {
                    if (res.success) populateSelect($(wrap).find('.ctr-cf-category'), res.data,
                        'Code', 'Name');
                });
                fetchMasterList('charge-codes', {
                    office_code: OFFICE_CODE,
                    charge_type: 'Revenue'
                }).done(function(res) {
                    if (res.success) {
                        var $select = $(wrap).find('.ctr-cf-code');
                        $select.empty().append('<option value="">-- Select --</option>');
                        res.charge_codes.forEach(function(c) {
                            var code = c.Code || '';
                            var name = c.Description || c.Name || c.Code || '';
                            var curr = c.Curr || '';
                            $select.append('<option value="' + code + '" data-description="' + name +
                                '" data-currency="' + curr + '">' + name + '</option>');
                        });
                        if (chargeToEdit.chargeCode) $select.val(chargeToEdit.chargeCode);
                        // Trigger change to auto-fill description/currency when editing
                        $select.trigger('change');
                    }
                });
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
                    var normalizedMarkupRateType = normalizeRateType(chargeToEdit.markupRateType);
                    $(wrap).find('.ctr-mk-ratetype').val(normalizedMarkupRateType || '');
                    $(wrap).find('.ctr-mk-inline-rate').html(markupInlineRateHtml(normalizedMarkupRateType));
                    $(wrap).find('.ctr-mk-ratearea').html(markupRateAreaHtml(normalizedMarkupRateType));
                    if (normalizedMarkupRateType === 'Single') {
                        wrap.querySelector('.ctr-mk-rate').value = chargeToEdit.markupRate ?? '';
                    } else if (normalizedMarkupRateType === 'Slab Based' || normalizedMarkupRateType === 'Step Up') {

                        fillMarkupSlabRows(wrap.querySelector('.ctr-mk-slab-rows'), chargeToEdit.markupRateSlab);
                    } else if (normalizedMarkupRateType === 'Formula') {
                        wrap.querySelector('.ctr-mk-formula').value = chargeToEdit.markupFormula || '';
                    }
                }

                var normalizedRateType = normalizeRateType(chargeToEdit.rateType);
                $(wrap).find('.ctr-cf-ratetype').val(normalizedRateType);
                $(wrap).find('.ctr-cf-inline-rate').html(inlineRateHtml(normalizedRateType));
                $(wrap).find('.ctr-cf-ratearea').html(rateAreaHtml(normalizedRateType));

                $(wrap).find('.ctr-cf-accruable').prop('checked', chargeToEdit.isAccruable == '1');
                $(wrap).find('.ctr-cf-reimbursable').prop('checked', chargeToEdit.isReimbursable == '1');
                $(wrap).find('.ctr-cf-accruable-wrap').toggle(naVal === 'Revenue');
                $(wrap).find('.ctr-cf-reimbursable-wrap').toggle(naVal === 'Cost');

                // var $markupRadio = $(wrap).find('input[name="ctr-cf-action"][value="M"]');
                // $markupRadio.closest('label').toggle(normalizedRateType === 'Single');

                if (normalizedRateType === 'Single') {
                    wrap.querySelector('.ctr-cf-rate').value = chargeToEdit.rate ?? '';
                } else if (normalizedRateType === 'Slab Based' || normalizedRateType === 'Step Up') {
                    fillSlabRows(wrap.querySelector('.ctr-slab-rows'), chargeToEdit.rateSlab);
                } else if (normalizedRateType === 'Formula') {
                    bindFormulaBuilderEvents(wrap);
                    wrap.querySelector('.ctr-cf-formula').value = chargeToEdit.formula || '';
                }
            }

            function loadBaseCurrency(wrap) {
                fetchMasterList('currencies', {
                    office_id: OFFICE_CODE
                }).done(function(res) {
                    if (res.success && res.currencies && res.currencies.length) {
                        // Assumes first entry represents the office's base currency —
                        // adjust if your currencies endpoint flags a specific default.
                        var base = res.currencies[0].CurrencyCode;
                        $(wrap).find('.ctr-cf-base-currency').text('Base Currency: ' + base);
                    }
                });
            }

            function recalcMarkupAmount(wrap) {
                var baseRate = parseFloat(wrap.querySelector('.ctr-cf-rate')?.value) || 0;
                var qty = parseFloat(wrap.querySelector('.ctr-cf-quantity')?.value) ||
                    1; // adjust if field name differs
                var baseAmount = baseRate * qty;

                var mkRate = parseFloat(wrap.querySelector('.ctr-mk-rate').value) || 0;
                var mkType = wrap.querySelector('.ctr-mk-ratetype').value;

                var amount = mkType === 'Fixed' ? mkRate : baseAmount * (mkRate / 100);
                wrap.querySelector('.ctr-mk-amount').value = amount.toFixed(2);
            }

            function bindChargeFormEvents(svcId, chargeId, wrap, chargeToEdit) {
                $(wrap).find('.ctr-cf-ratetype').on('change', function() {
                    $(wrap).find('.ctr-cf-inline-rate').html(inlineRateHtml(this.value));
                    $(wrap).find('.ctr-cf-ratearea').html(rateAreaHtml(this.value));
                    if (this.value === 'Formula') bindFormulaBuilderEvents(wrap);

                    // var $markupRadio = $(wrap).find('input[name="ctr-cf-action"][value="M"]');
                    // var markupAllowed = (this.value === 'Single');
                    // $markupRadio.closest('label').toggle(markupAllowed);

                    // if (!markupAllowed && $markupRadio.is(':checked')) {
                    //     $(wrap).find('input[name="ctr-cf-action"]:visible').first().prop('checked', true)
                    //         .trigger('change');
                    // }
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

                // Markup Rate Type change
                $(wrap).on('change', '.ctr-mk-ratetype', function() {
                    $(wrap).find('.ctr-mk-inline-rate').html(markupInlineRateHtml(this.value));
                    $(wrap).find('.ctr-mk-ratearea').html(markupRateAreaHtml(this.value));
                });

                // Markup slab add/remove
                $(wrap).on('click', '.ctr-mk-slab-add-row', function() {
                    var tbody = wrap.querySelector('.ctr-mk-slab-rows');
                    var rows = tbody.querySelectorAll('tr');
                    var lastUpto = rows.length ? rows[rows.length - 1].querySelector('.ctr-mk-slab-upto')
                        .value : '';
                    var tr = document.createElement('tr');
                    tr.innerHTML =
                        '<td><input type="number" class="form-control form-control-sm ctr-mk-slab-min" value="' +
                        (lastUpto || '') + '"></td>' +
                        '<td><input type="number" class="form-control form-control-sm ctr-mk-slab-upto"></td>' +
                        '<td><input type="number" step="0.01" class="form-control form-control-sm ctr-mk-slab-value"></td>' +
                        '<td><input type="text" class="form-control form-control-sm ctr-mk-slab-formula" placeholder="optional"></td>' +
                        '<td><span class="ctr-mk-slab-row-remove">&times;</span></td>';
                    tbody.appendChild(tr);
                });

                $(wrap).on('click', '.ctr-mk-slab-row-remove', function() {
                    $(this).closest('tr').remove();
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

            function saveChargeUpdate(svcId, chargeId, wrap, keepOpen) {
                var rateType = wrap.querySelector('.ctr-cf-ratetype').value;
                var $uomField = wrap.querySelector('.ctr-cf-uom');
                var uomNotNeeded = $uomField.disabled;

                var required = {
                    category: wrap.querySelector('.ctr-cf-category').value,
                    code: wrap.querySelector('.ctr-cf-code').value.trim(),
                    desc: wrap.querySelector('.ctr-cf-desc').value.trim(),
                    basedOn: wrap.querySelector('.ctr-cf-basedon').value,
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
                var derivedNature = (naVal === 'Revenue') ? 'Revenue' : 'Cost';
                var derivedAction = (naVal === 'Revenue') ? 'A' : (naVal === 'CostMarkup') ? 'M' : 'R';

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

                if (rateType === 'Single') {
                    var rateVal = wrap.querySelector('.ctr-cf-rate').value;
                    if (rateVal === '') {
                        alert('Rate is required for Single rate type.');
                        return;
                    }
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
                }

                // --- Charge Action and Markup ---
                var actionVal = derivedAction;
                charge.chargeAction = actionVal;
                charge.toBeAdded = (actionVal === 'M') ? 1 : 0;

                charge.isAccruable = wrap.querySelector('.ctr-cf-accruable').checked ? '1' : '0';
                charge.isReimbursable = wrap.querySelector('.ctr-cf-reimbursable').checked ? '1' : '0';
                charge.markupRateType = null;
                charge.markupRate = null;
                charge.markupFormula = null;
                charge.markupRateSlab = null;

                if (actionVal === 'M') {
                    var mkRateType = wrap.querySelector('.ctr-mk-ratetype').value;
                    if (!mkRateType) {
                        alert('Markup Rate Type is required when Charge Action is Markup.');
                        return;
                    }
                    charge.markupRateType = mkRateType;
                    if (mkRateType === 'Single') {
                        var mkRateVal = wrap.querySelector('.ctr-mk-rate').value;
                        if (mkRateVal === '') {
                            alert('Markup Rate is required for Single markup rate type.');
                            return;
                        }
                        charge.markupRate = parseFloat(parseFloat(mkRateVal).toFixed(5));
                    }
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
                if (!wrap) return;
                wrap.style.display = 'block';
                wrap.innerHTML = chargeFormHtml();
                bindChargeFormEvents(svcId, chargeToEdit.chargeId, wrap, chargeToEdit);
                loadChargeFormMasters(wrap, chargeToEdit);
                var isRevenueForFormula = chargeToEdit.chargeNature === 'Cost' ? 0 : 1;
                loadFormulaFields(svcId, isRevenueForFormula).done(function(res) {
                    if (res.success) FORMULA_FIELDS = res.data.map(function(name) {
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

            $(document).on('click', '.ce-delete-charge', function() {
                var svcId = $(this).data('svc');
                var chargeId = $(this).data('charge-id');
                if (!confirm('Delete this charge?')) return;

                var icon = this;

                $.ajax({
                    url: '/contracts/' + encodeURIComponent(CONTRACT_ID) + '/services/' +
                        encodeURIComponent(svcId) +
                        '/charges/' + encodeURIComponent(chargeId),
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
                    $(icon).closest('tr').remove();
                }).fail(function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed.';
                    alert('Failed to delete charge: ' + msg);
                });
            });



        })();
    </script>
@stop
