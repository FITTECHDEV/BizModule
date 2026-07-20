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
                        <span
                            class="cc-val">{{ \Carbon\Carbon::parse($contractInfo->EffectiveFrom)->format('Y-m-d') }}</span>
                        <span class="cc-lbl" style="margin-left:24px;">Effective To</span>
                        <span class="cc-val">{{ \Carbon\Carbon::parse($contractInfo->EffectiveTo)->format('Y-m-d') }}</span>
                    </div>
                    <div class="cc-info-row">
                        <span class="cc-lbl">Remark</span>
                        <span class="cc-val">{{ $contractInfo->Remark }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Services ────────────────────────────────────────────────────── --}}
        @if ($services->count())
            <div class="cc-section-block mb-3">
                <div class="cc-section-block-header cc-header-revenue">
                    <strong>Services</strong>
                    <span class="ml-auto cc-totals-inline">{{ $services->count() }} total</span>
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
                                    <div class="ctr-field mb-2"><label class="ctr-lbl">Service</label>
                                        <select class="form-control form-control-sm ce-svc-field ce-svc-service" disabled>
                                            <option value="">-- Select --</option>
                                        </select>
                                    </div>
                                    <div class="ctr-field mb-2"><label class="ctr-lbl">Process</label>
                                        <select class="form-control form-control-sm ce-svc-field ce-svc-process" disabled>
                                            <option value="">-- Select --</option>
                                        </select>
                                    </div>
                                    <div class="ctr-field mb-2"><label class="ctr-lbl">IncoTerm</label>
                                        <select class="form-control form-control-sm ce-svc-field ce-svc-incoterm" disabled>
                                            <option value="">-- Select --</option>
                                        </select>
                                    </div>
                                    <div class="ctr-field mb-2"><label class="ctr-lbl">Service Class</label>
                                        <input type="text"
                                            class="form-control form-control-sm ce-svc-field ce-svc-serviceclass"
                                            value="{{ $service->ServiceClass }}" disabled>
                                    </div>
                                    <div class="ctr-field"><label class="ctr-lbl">Country of Origin</label>
                                        <select class="form-control form-control-sm ce-svc-field ce-svc-coo" disabled>
                                            <option value="">-- Select --</option>
                                        </select>
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
                                    <div class="ctr-field mb-2"><label class="ctr-lbl">Country of Destination</label>
                                        <select class="form-control form-control-sm ce-svc-field ce-svc-cod" disabled>
                                            <option value="">-- Select --</option>
                                        </select>
                                    </div>
                                    <div class="ctr-field"><label class="ctr-lbl">POL / POD</label>
                                        <div class="d-flex" style="gap:8px;">
                                            <input type="text"
                                                class="form-control form-control-sm ce-svc-field ce-svc-pol"
                                                value="{{ $service->POL }}" placeholder="POL code" disabled>
                                            <input type="text"
                                                class="form-control form-control-sm ce-svc-field ce-svc-pod"
                                                value="{{ $service->POD }}" placeholder="POD code" disabled>
                                        </div>
                                        <small class="text-muted">Shown as stored codes — retype to change.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mb-3">
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
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-pen ce-edit-charge"
                                                            style="cursor:pointer;color:#4B49AC;" title="Edit"
                                                            data-svc="{{ $service->Id }}"
                                                            data-charge='@json($chargeJs)'></i>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="ctr-subform mt-2" id="chargeform-{{ $service->Id }}" style="display:none;">
                                </div>
                            @else
                                <div class="cc-empty-msg">No charges for this service.</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="cc-box cc-box-compact">
                <div class="cc-empty-msg">No services added.</div>
            </div>
        @endif

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
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script>
        (function() {
            var CONTRACT_ID = @json($contractInfo->Id);
            var OFFICE_CODE = @json($officeCode ?? '');
            var MASTER_BASE = '/contracts/masters';

            var SERVICE_PROCESS_MAP = {
                'Air': ['Export', 'Import'],
                'Sea': ['Export', 'Import'],
                'Road': ['Local']
            };
            var ALL_PROCESSES = [];

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
                return $.ajax({
                    url: url + qs,
                    type: 'GET',
                    dataType: 'json'
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
                fetchMasterList('processes').done(function(res) {
                    if (res.success) {
                        ALL_PROCESSES = res.data;
                        populateSelect($group.find('.ce-svc-process'), res.data, 'Code', 'Name', initial
                            .process);
                    }
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
                fetchMasterList('countries').done(function(res) {
                    if (Array.isArray(res)) {
                        populateSelect($group.find('.ce-svc-coo'), res, 'CountCode', 'CountryName', initial
                            .coo);
                        populateSelect($group.find('.ce-svc-cod'), res, 'CountCode', 'CountryName', initial
                            .cod);
                    }
                });
            }

            $('.cc-group-body').each(function() {
                var $body = $(this);
                var svcId = $body.find('.ce-svc-id').val();
                var initial = SERVICE_INITIAL[svcId] || {};
                populateServiceGroupMasters($body, initial);

                $body.find('.ce-svc-service').on('change', function() {
                    var allowed = SERVICE_PROCESS_MAP[this.value];
                    var filtered = allowed ?
                        ALL_PROCESSES.filter(function(p) {
                            return allowed.indexOf(p.Code) !== -1;
                        }) :
                        ALL_PROCESSES;
                    populateSelect($body.find('.ce-svc-process'), filtered, 'Code', 'Name');
                });
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
                    entry.el.value = entry.val;
                });
                setServiceEditable($group, false);
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
                    placeOfReceipt: '',
                    placeOfDelivery: ''
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
                        _token: document.querySelector('meta[name="csrf-token"]').content
                    }, payload),
                    dataType: 'json'
                }).done(function(res) {
                    if (!res.success) {
                        alert('Failed to update service: ' + (res.message || 'Unknown error'));
                        return;
                    }
                    setServiceEditable($group, false);
                    alert(res.message || 'Service updated.');
                }).fail(function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Update failed.';
                    alert('Failed to update service: ' + msg);
                }).always(function() {
                    btn.disabled = false;
                    btn.textContent = originalText;
                });
            });

            // ── Charge edit form (same builder as create.blade.php) ─────────────
            var FORMULA_FIELDS = [];
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
                    '<div class="ctr-field mt-2" style="max-width:220px;"><label class="ctr-lbl">Rate Type</label>' +
                    '<select class="form-control form-control-sm ctr-fb-ratetype">' +
                    '<option value="Single">Single</option><option value="Slab Based">Slab Based</option></select></div>' +
                    '<div class="ctr-fb-ratearea mt-2"></div></div>';
            }

            function inlineRateHtml(rateType) {
                if (rateType === 'Single') {
                    return '<label class="ctr-lbl">Rate</label>' +
                        '<input type="number" step="0.00001" class="form-control form-control-sm ctr-cf-rate">';
                }
                return '';
            }

            function slabRowHtml(isAbove) {
                return '<tr' + (isAbove ? ' class="ctr-slab-above-row"' : '') + '>' +
                    '<td>' + (isAbove ? '<span class="ctr-slab-above-label">Above</span>' :
                        '<input type="number" class="form-control form-control-sm ctr-slab-upto">') + '</td>' +
                    '<td><input type="number" step="0.01" class="form-control form-control-sm ctr-slab-value"></td>' +
                    '<td>' + (isAbove ? '' : '<span class="ctr-slab-row-remove">&times;</span>') + '</td></tr>';
            }

            function rateAreaHtml(rateType) {
                if (rateType === 'Slab Based' || rateType === 'Step Up') {
                    return '<div class="ctr-slab-wrap"><table class="ctr-slab-table">' +
                        '<thead><tr><th>Up To</th><th>Value</th><th></th></tr></thead>' +
                        '<tbody class="ctr-slab-rows">' + slabRowHtml(true) + '</tbody></table>' +
                        '<button type="button" class="btn btn-xs btn-outline-primary ctr-slab-add-row mt-1">+ Add Slab</button></div>';
                }
                if (rateType === 'Formula') return formulaBuilderHtml();
                return '';
            }

            function fillSlabRows(tbody, slabs) {
                if (!tbody || !slabs || !slabs.length) return;
                tbody.innerHTML = '';
                slabs.forEach(function(s) {
                    var isAbove = s.upto === null || s.upto === undefined;
                    var row = document.createElement('tr');
                    if (isAbove) row.className = 'ctr-slab-above-row';
                    row.innerHTML =
                        '<td>' + (isAbove ? '<span class="ctr-slab-above-label">Above</span>' :
                            '<input type="number" class="form-control form-control-sm ctr-slab-upto" value="' +
                            s.upto + '">') + '</td>' +
                        '<td><input type="number" step="0.01" class="form-control form-control-sm ctr-slab-value" value="' +
                        s.value + '"></td>' +
                        '<td>' + (isAbove ? '' : '<span class="ctr-slab-row-remove">&times;</span>') + '</td>';
                    tbody.appendChild(row);
                });
            }

            function addSlabRow(wrap) {
                var tbody = wrap.querySelector('.ctr-slab-rows');
                var lastRow = tbody.querySelector('.ctr-slab-above-row');
                if (lastRow) {
                    var value = lastRow.querySelector('.ctr-slab-value').value;
                    var normalRow = document.createElement('tr');
                    normalRow.innerHTML =
                        '<td><input type="number" class="form-control form-control-sm ctr-slab-upto"></td>' +
                        '<td><input type="number" step="0.01" class="form-control form-control-sm ctr-slab-value" value="' +
                        value + '"></td>' +
                        '<td><span class="ctr-slab-row-remove">&times;</span></td>';
                    lastRow.replaceWith(normalRow);
                }
                var newRow = document.createElement('tr');
                newRow.className = 'ctr-slab-above-row';
                newRow.innerHTML = '<td><span class="ctr-slab-above-label">Above</span></td>' +
                    '<td><input type="number" step="0.01" class="form-control form-control-sm ctr-slab-value"></td><td></td>';
                tbody.appendChild(newRow);
            }

            function renderFormulaRateArea(wrap, rateType) {
                var area = wrap.querySelector('.ctr-fb-ratearea');
                if (rateType === 'Slab Based') {
                    area.innerHTML = '<div class="ctr-slab-wrap"><table class="ctr-slab-table">' +
                        '<thead><tr><th>Up To</th><th>Value</th><th></th></tr></thead>' +
                        '<tbody class="ctr-slab-rows">' + slabRowHtml(true) + '</tbody></table>' +
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
                return '<div class="ctr-grid-2" style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px 16px;">' +
                    '<div class="ctr-field"><label class="ctr-lbl">Charge Category</label>' +
                    '<select class="form-control form-control-sm ctr-cf-category"><option value="">-- Select --</option></select></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl">Charge Code</label>' +
                    '<select class="form-control form-control-sm ctr-cf-code"><option value="">-- Select --</option></select></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl">Charge Description</label>' +
                    '<input type="text" class="form-control form-control-sm ctr-cf-desc"></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl">Charge Based On</label>' +
                    '<select class="form-control form-control-sm ctr-cf-basedon"><option value="">-- Select --</option></select></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl">Based On UOM</label>' +
                    '<select class="form-control form-control-sm ctr-cf-uom"><option value="">Select Based On first</option></select></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl">Currency</label>' +
                    '<select class="form-control form-control-sm ctr-cf-currency"><option value="">-- Select --</option></select></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl">Min Amount</label>' +
                    '<input type="number" step="0.01" class="form-control form-control-sm ctr-cf-min"></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl">Max Amount</label>' +
                    '<input type="number" step="0.01" class="form-control form-control-sm ctr-cf-max"></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl">Rate Type</label>' +
                    '<select class="form-control form-control-sm ctr-cf-ratetype">' +
                    '<option value="">-- Select --</option><option value="Single">Single</option>' +
                    '<option value="Slab Based">Slab Based</option><option value="Step Up">Step Up</option>' +
                    '<option value="Formula">Formula</option></select></div>' +
                    '<div class="ctr-field ctr-cf-inline-rate"></div>' +
                    '</div><div class="ctr-cf-ratearea mt-2"></div>' +
                    '<div class="d-flex justify-content-end mt-2">' +
                    '<button type="button" class="btn btn-sm btn-secondary mr-2 ctr-cf-cancel">Cancel</button>' +
                    '<button type="button" class="btn btn-sm btn-success ctr-cf-save">Update Charge</button></div>';
            }

            function loadChargeFormMasters(wrap, chargeToEdit) {
                fetchMasterList('charge-categories').done(function(res) {
                    if (res.success) populateSelect($(wrap).find('.ctr-cf-category'), res.data, 'Code', 'Name');
                });
                fetchMasterList('charge-codes', {
                    office_code: OFFICE_CODE,
                    charge_type: 'Revenue'
                }).done(function(res) {
                    if (res.success) {
                        var options = res.charge_codes.map(function(c) {
                            return {
                                Code: c.Code,
                                Label: c.Code + ' - ' + (c.Description || c.Name || c.Code)
                            };
                        });
                        populateSelect($(wrap).find('.ctr-cf-code'), options, 'Code', 'Label', chargeToEdit
                            .chargeCode);
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
                        populateSelect($(wrap).find('.ctr-cf-basedon'), options, 'Code', 'Label', chargeToEdit
                            .chargeBasedOn);
                    }
                });
                if (chargeToEdit.chargeBasedOn) {
                    fetchMasterList('based-on-uom', {
                        based_on_code: chargeToEdit.chargeBasedOn
                    }).done(function(res) {
                        if (res.success) {
                            var options = res.uom_list.map(function(u) {
                                return {
                                    Code: u.Code,
                                    Label: u.Name
                                };
                            });
                            populateSelect($(wrap).find('.ctr-cf-uom'), options, 'Code', 'Label', chargeToEdit
                                .chargeBasedOnUOM);
                        }
                    });
                }
                fetchMasterList('currencies', {
                    office_id: OFFICE_CODE
                }).done(function(res) {
                    if (res.success) {
                        var $sel = $(wrap).find('.ctr-cf-currency');
                        $sel.empty().append('<option value="">-- Select --</option>');
                        res.currencies.forEach(function(c) {
                            $sel.append('<option value="' + c.CurrencyCode + '">' + c.CurrencyCode +
                                '</option>');
                        });
                        $sel.val(chargeToEdit.chargeCurr);
                    }
                });

                wrap.querySelector('.ctr-cf-desc').value = chargeToEdit.chargeDescription || '';
                wrap.querySelector('.ctr-cf-min').value = chargeToEdit.minAmount ?? '';
                wrap.querySelector('.ctr-cf-max').value = chargeToEdit.maxAmount ?? '';

                // NOTE: RateType values stored by the SP have been observed as both
                // 'Slab Based' (create.blade.php's dropdown value) and 'SlabBased'
                // (checked for in show.blade.php) — this select only recognises the
                // spaced form. If the stored value uses the no-space form, the
                // dropdown will show blank and the rate-type-specific area won't
                // pre-render; the charge can still be corrected and re-saved, but
                // flagging this data inconsistency rather than silently guessing.
                $(wrap).find('.ctr-cf-ratetype').val(chargeToEdit.rateType);
                $(wrap).find('.ctr-cf-inline-rate').html(inlineRateHtml(chargeToEdit.rateType));
                $(wrap).find('.ctr-cf-ratearea').html(rateAreaHtml(chargeToEdit.rateType));

                if (chargeToEdit.rateType === 'Single') {
                    wrap.querySelector('.ctr-cf-rate').value = chargeToEdit.rate ?? '';
                } else if (chargeToEdit.rateType === 'Slab Based' || chargeToEdit.rateType === 'Step Up') {
                    fillSlabRows(wrap.querySelector('.ctr-slab-rows'), chargeToEdit.rateSlab);
                } else if (chargeToEdit.rateType === 'Formula') {
                    bindFormulaBuilderEvents(wrap);
                    wrap.querySelector('.ctr-cf-formula').value = chargeToEdit.formula || '';
                }
            }

            function bindChargeFormEvents(svcId, chargeId, wrap, chargeToEdit) {
                $(wrap).find('.ctr-cf-ratetype').on('change', function() {
                    $(wrap).find('.ctr-cf-inline-rate').html(inlineRateHtml(this.value));
                    $(wrap).find('.ctr-cf-ratearea').html(rateAreaHtml(this.value));
                    if (this.value === 'Formula') bindFormulaBuilderEvents(wrap);
                });
                $(wrap).find('.ctr-cf-cancel').on('click', function() {
                    wrap.style.display = 'none';
                    wrap.innerHTML = '';
                });
                $(wrap).find('.ctr-cf-basedon').on('change', function() {
                    var basedOnCode = $(this).val();
                    var $uomSelect = $(wrap).find('.ctr-cf-uom');
                    if (!basedOnCode) {
                        $uomSelect.empty().append('<option value="">Select Based On first</option>');
                        return;
                    }
                    fetchMasterList('based-on-uom', {
                        based_on_code: basedOnCode
                    }).done(function(res) {
                        if (res.success) {
                            var options = res.uom_list.map(function(u) {
                                return {
                                    Code: u.Code,
                                    Label: u.Name
                                };
                            });
                            populateSelect($uomSelect, options, 'Code', 'Label');
                        }
                    });
                });
                $(wrap).find('.ctr-cf-save').on('click', function() {
                    saveChargeUpdate(svcId, chargeId, wrap);
                });
            }

            function saveChargeUpdate(svcId, chargeId, wrap) {
                var rateType = wrap.querySelector('.ctr-cf-ratetype').value;
                var required = {
                    category: wrap.querySelector('.ctr-cf-category').value,
                    code: wrap.querySelector('.ctr-cf-code').value.trim(),
                    desc: wrap.querySelector('.ctr-cf-desc').value.trim(),
                    basedOn: wrap.querySelector('.ctr-cf-basedon').value,
                    rateType: rateType
                };
                var missing = Object.keys(required).filter(function(k) {
                    return !required[k];
                });
                if (missing.length) {
                    alert('Please fill required Charge fields: ' + missing.join(', '));
                    return;
                }

                var charge = {
                    chargeCode: required.code,
                    chargeDescription: required.desc,
                    chargeBasedOn: required.basedOn,
                    chargeBasedOnUOM: wrap.querySelector('.ctr-cf-uom').value.trim(),
                    chargeCurr: wrap.querySelector('.ctr-cf-currency').value,
                    minAmount: wrap.querySelector('.ctr-cf-min').value !== '' ? parseFloat(parseFloat(wrap
                        .querySelector('.ctr-cf-min').value).toFixed(2)) : null,
                    maxAmount: wrap.querySelector('.ctr-cf-max').value !== '' ? parseFloat(parseFloat(wrap
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
                    var rows = wrap.querySelectorAll('.ctr-slab-rows tr');
                    var slabs = [];
                    var valid = true;
                    rows.forEach(function(row) {
                        var isAbove = row.classList.contains('ctr-slab-above-row');
                        var value = row.querySelector('.ctr-slab-value').value;
                        if (isAbove) {
                            if (value === '') {
                                valid = false;
                                return;
                            }
                            slabs.push({
                                upto: null,
                                value: parseFloat(value)
                            });
                        } else {
                            var upto = row.querySelector('.ctr-slab-upto').value;
                            if (upto === '' || value === '') {
                                valid = false;
                                return;
                            }
                            slabs.push({
                                upto: parseFloat(upto),
                                value: parseFloat(value)
                            });
                        }
                    });
                    if (!valid || !slabs.length) {
                        alert('Please fill all slab rows.');
                        return;
                    }
                    charge.rateSlab = slabs;
                } else if (rateType === 'Formula') {
                    var formulaVal = wrap.querySelector('.ctr-cf-formula').value.trim();
                    if (!formulaVal) {
                        alert('Formula is required for Formula rate type.');
                        return;
                    }
                    charge.formula = formulaVal;
                }

                var saveBtn = wrap.querySelector('.ctr-cf-save');
                var originalText = saveBtn.textContent;
                saveBtn.disabled = true;
                saveBtn.textContent = 'Updating...';

                $.ajax({
                    url: '/contracts/' + encodeURIComponent(CONTRACT_ID) + '/services/' + encodeURIComponent(
                            svcId) +
                        '/charges/' + encodeURIComponent(chargeId),
                    type: 'PUT',
                    data: $.extend({
                        _token: document.querySelector('meta[name="csrf-token"]').content
                    }, charge),
                    dataType: 'json'
                }).done(function(res) {
                    if (!res.success) {
                        alert('Failed to update charge: ' + (res.message || 'Unknown error'));
                        return;
                    }
                    alert('Charge updated. Reloading to reflect changes...');
                    window.location.reload();
                }).fail(function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Update failed.';
                    alert('Failed to update charge: ' + msg);
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
                wrap.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            });

        })();
    </script>
@stop
