@extends('adminlte::page')

@section('title', 'Create Contract')

@section('content_header')
    <h1 class="h3 mb-0">Create Contract</h1>
@stop

@section('content')
    <div class="container-fluid" id="contractCreateContent">

        {{-- STEP 1: Contract header --}}
        <div class="ctr-box mb-3">
            <div class="ctr-box-title">Contract Information</div>
            <div class="ctr-grid">
                <div class="ctr-field">
                    <label class="ctr-lbl ctr-required">Contract No</label>
                    <input type="text" class="form-control form-control-sm" id="ctrContractNo">
                </div>
                <div class="ctr-field">
                    <label class="ctr-lbl ctr-required">Contract Type</label>
                    <select class="form-control form-control-sm" id="ctrContractType">
                        <option value="">-- Select --</option>
                        <option value="Tariff">Tariff</option>
                        <option value="Long Term">Long Term</option>
                    </select>
                </div>
                <div class="ctr-field">
                    <label class="ctr-lbl ctr-required">Status</label>
                    <select class="form-control form-control-sm" id="ctrStatus">
                        <option value="Draft">Draft</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="ctr-field">
                    <label class="ctr-lbl ctr-required">Effective From</label>
                    <input type="date" class="form-control form-control-sm" id="ctrEffectiveFrom">
                </div>
                <div class="ctr-field">
                    <label class="ctr-lbl ctr-required">Effective To</label>
                    <input type="date" class="form-control form-control-sm" id="ctrEffectiveTo">
                </div>
                <div class="ctr-field ctr-field-wide">
                    <label class="ctr-lbl">Description</label>
                    <input type="text" class="form-control form-control-sm" id="ctrDescription">
                </div>
                <div class="ctr-field ctr-field-wide">
                    <label class="ctr-lbl">Remark</label>
                    <input type="text" class="form-control form-control-sm" id="ctrRemark">
                </div>
            </div>
        </div>

        {{-- STEP 2: Services list + inline Add Service form (embedded, not a new window) --}}
        <div class="ctr-box mb-3">
            <div class="ctr-box-title d-flex align-items-center">
                <span>Services</span>
                <button type="button" class="btn btn-sm btn-primary ml-auto" id="ctrAddServiceBtn">+ Add Service</button>
            </div>

            <div id="ctrServiceForm" class="ctr-subform mb-3" style="display:none;">
                <div class="ctr-grid">
                    <div class="ctr-field"><label class="ctr-lbl ctr-required">Service</label>
                        <select class="form-control form-control-sm" id="ctrSvcService">
                            <option value="">-- Select --</option>
                            <option>Freight Forwarding</option>
                            <option>Customs Clearance</option>
                            <option>Transportation</option>
                            <option>Warehousing</option>
                        </select>
                    </div>
                    <div class="ctr-field"><label class="ctr-lbl ctr-required">Process</label>
                        <select class="form-control form-control-sm" id="ctrSvcProcess">
                            <option value="">-- Select --</option>
                            <option>Import</option>
                            <option>Export</option>
                            <option>Cross Trade</option>
                        </select>
                    </div>
                    <div class="ctr-field"><label class="ctr-lbl">IncoTerm</label>
                        <select class="form-control form-control-sm" id="ctrSvcIncoTerm">
                            <option value="">-- Select --</option>
                            <option>EXW</option>
                            <option>FOB</option>
                            <option>CIF</option>
                            <option>DDP</option>
                            <option>FCA</option>
                        </select>
                    </div>
                    <div class="ctr-field"><label class="ctr-lbl">Service Class</label>
                        <input type="text" class="form-control form-control-sm" id="ctrSvcServiceClass">
                    </div>
                    <div class="ctr-field"><label class="ctr-lbl ctr-required">Shipment Type</label>
                        <select class="form-control form-control-sm" id="ctrSvcShipmentType">
                            <option value="">-- Select --</option>
                            <option>FCL</option>
                            <option>LCL</option>
                            <option>Air</option>
                            <option>Bulk</option>
                        </select>
                    </div>
                    <div class="ctr-field"><label class="ctr-lbl">Movement Type</label>
                        <select class="form-control form-control-sm" id="ctrSvcMovementType">
                            <option value="">-- Select --</option>
                            <option>Port to Port</option>
                            <option>Door to Door</option>
                            <option>Port to Door</option>
                        </select>
                    </div>
                    <div class="ctr-field"><label class="ctr-lbl ctr-required">Shipment Mode</label>
                        <select class="form-control form-control-sm" id="ctrSvcShipmentMode">
                            <option value="">-- Select --</option>
                            <option>Sea</option>
                            <option>Air</option>
                            <option>Road</option>
                        </select>
                    </div>
                    <div class="ctr-field"><label class="ctr-lbl ctr-required">Country of Origin</label>
                        <input type="text" class="form-control form-control-sm" id="ctrSvcCountryOrigin">
                    </div>
                    <div class="ctr-field"><label class="ctr-lbl ctr-required">Country of Destination</label>
                        <input type="text" class="form-control form-control-sm" id="ctrSvcCountryDestination">
                    </div>
                    <div class="ctr-field"><label class="ctr-lbl">Port of Loading</label>
                        <input type="text" class="form-control form-control-sm" id="ctrSvcPol">
                    </div>
                    <div class="ctr-field"><label class="ctr-lbl">Port of Discharge</label>
                        <input type="text" class="form-control form-control-sm" id="ctrSvcPod">
                    </div>
                    <div class="ctr-field"><label class="ctr-lbl">Place of Receipt</label>
                        <input type="text" class="form-control form-control-sm" id="ctrSvcPlaceOfReceipt">
                    </div>
                    <div class="ctr-field"><label class="ctr-lbl">Place of Delivery</label>
                        <input type="text" class="form-control form-control-sm" id="ctrSvcPlaceOfDelivery">
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-2">
                    <button type="button" class="btn btn-sm btn-secondary mr-2" id="ctrCancelServiceBtn">Cancel</button>
                    <button type="button" class="btn btn-sm btn-success" id="ctrSaveServiceBtn">Save Service</button>
                </div>
            </div>

            <div id="ctrServicesList"></div>
            <div class="ctr-empty-msg" id="ctrServicesEmpty">No services added yet.</div>
        </div>

        <div class="d-flex justify-content-end mb-4">
            <button type="button" class="btn btn-success" id="ctrSaveContractBtn">Save Contract</button>
        </div>
    </div>

    {{-- Charge row template (cloned via JS, never referenced directly) --}}
    <template id="ctrChargeRowTemplate">
        <tr>
            <td class="ctr-nature-cell"></td>
            <td class="ctr-code-cell"></td>
            <td class="ctr-desc-cell"></td>
            <td class="ctr-category-cell"></td>
            <td class="ctr-ratetype-cell"></td>
            <td class="ctr-rate-cell"></td>
            <td class="ctr-actions-cell"></td>
        </tr>
    </template>
@stop

@section('css')
    <style>
        .ctr-box {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 16px;
            background: #fff;
        }

        .ctr-box-title {
            font-size: .9rem;
            font-weight: 700;
            color: #4B49AC;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .ctr-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px 16px;
        }

        .ctr-field-wide {
            grid-column: span 3;
        }

        .ctr-lbl {
            font-size: .78rem;
            color: #495057;
            margin-bottom: 2px;
            display: block;
        }

        .ctr-required::after {
            content: ' *';
            color: #dc3545;
        }

        .ctr-subform {
            border: 1px dashed #4B49AC;
            border-radius: 4px;
            padding: 14px;
            background: #f8f8fd;
        }

        .ctr-empty-msg {
            text-align: center;
            color: #888;
            padding: 14px;
            font-size: .82rem;
        }

        .ctr-service-card {
            border: 1px solid #e2e2ec;
            border-radius: 4px;
            margin-bottom: 12px;
        }

        .ctr-service-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            background: #f4f4fb;
            cursor: pointer;
            font-size: .85rem;
            font-weight: 600;
            color: #4B49AC;
        }

        .ctr-service-header .ctr-caret {
            transition: transform .15s ease;
        }

        .ctr-service-header.collapsed .ctr-caret {
            transform: rotate(-90deg);
        }

        .ctr-service-body {
            padding: 12px 14px;
        }

        .ctr-service-body.collapsed {
            display: none;
        }

        .ctr-service-meta {
            font-size: .78rem;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .ctr-charges-table {
            width: 100%;
            font-size: .8rem;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .ctr-charges-table th,
        .ctr-charges-table td {
            border: 1px solid #dee2e6;
            padding: 5px 7px;
        }

        .ctr-charges-table thead th {
            background: #fff;
            font-weight: 600;
            color: #6c757d;
            font-size: .72rem;
            text-transform: uppercase;
        }

        .ctr-badge-revenue {
            background: #4B49AC;
            color: #fff;
            font-size: .68rem;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .ctr-badge-cost {
            background: #6c757d;
            color: #fff;
            font-size: .68rem;
            padding: 2px 6px;
            border-radius: 3px;
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

        .ctr-slab-row-remove {
            cursor: pointer;
            color: #dc3545;
        }
    </style>
@stop

@section('js')
    <script>
        (function() {
            var CONTRACT_ID = @json($contractId);
            var STORE_URL = @json(route('contracts.store', $contractId ?? ''));

            // ------------------------------------------------------------
            // Mock option lists (hardcoded per current phase — real lookups
            // will replace these once backend SPs exist)
            // ------------------------------------------------------------
            var CHARGE_CATEGORIES = [
                'General / Port Charges', 'Freight / Carrier Related Charges',
                'Dangerous Goods Charges', 'Delivery / Pickup Related Charges',
                'Documentation Charges', 'Customs Charges', 'Agency Charges', 'Product Charges'
            ];
            var CHARGE_BASED_ON = ['KG', 'CBM', 'Container', 'Shipment', 'Package'];
            var CURRENCIES = ['USD', 'KES', 'UGX', 'TZS'];
            var RATE_TYPES = ['Single', 'Slab Based', 'Step Up', 'Formula'];

            var existing = @json($existing ?? null);

            var state = {
                contract: existing || {
                    contractNo: '',
                    contractType: '',
                    status: 'Draft',
                    effectiveFrom: '',
                    effectiveTo: '',
                    description: '',
                    remark: '',
                    services: []
                }
            };

            var serviceSeq = 0;
            var chargeSeq = 0;

            // ------------------------------------------------------------
            // STEP 2: Add Service (embedded form, no new window)
            // ------------------------------------------------------------
            var svcForm = document.getElementById('ctrServiceForm');

            document.getElementById('ctrAddServiceBtn').addEventListener('click', function() {
                svcForm.style.display = 'block';
            });
            document.getElementById('ctrCancelServiceBtn').addEventListener('click', function() {
                svcForm.style.display = 'none';
                clearServiceForm();
            });

            function clearServiceForm() {
                ['ctrSvcService', 'ctrSvcProcess', 'ctrSvcIncoTerm', 'ctrSvcServiceClass', 'ctrSvcShipmentType',
                    'ctrSvcMovementType', 'ctrSvcShipmentMode', 'ctrSvcCountryOrigin', 'ctrSvcCountryDestination',
                    'ctrSvcPol', 'ctrSvcPod', 'ctrSvcPlaceOfReceipt', 'ctrSvcPlaceOfDelivery'
                ].forEach(function(id) {
                    document.getElementById(id).value = '';
                });
            }

            document.getElementById('ctrSaveServiceBtn').addEventListener('click', function() {
                var required = {
                    service: document.getElementById('ctrSvcService').value,
                    process: document.getElementById('ctrSvcProcess').value,
                    shipmentType: document.getElementById('ctrSvcShipmentType').value,
                    shipmentMode: document.getElementById('ctrSvcShipmentMode').value,
                    countryOrigin: document.getElementById('ctrSvcCountryOrigin').value.trim(),
                    countryDestination: document.getElementById('ctrSvcCountryDestination').value.trim()
                };
                var missing = Object.keys(required).filter(function(k) {
                    return !required[k];
                });
                if (missing.length) {
                    alert('Please fill required Service fields: ' + missing.join(', '));
                    return;
                }

                serviceSeq += 1;
                state.contract.services.push({
                    id: 'svc_' + serviceSeq,
                    service: required.service,
                    process: required.process,
                    incoterm: document.getElementById('ctrSvcIncoTerm').value,
                    serviceClass: document.getElementById('ctrSvcServiceClass').value.trim(),
                    shipmentType: required.shipmentType,
                    movementType: document.getElementById('ctrSvcMovementType').value,
                    shipmentMode: required.shipmentMode,
                    countryOrigin: required.countryOrigin,
                    countryDestination: required.countryDestination,
                    pol: document.getElementById('ctrSvcPol').value.trim(),
                    pod: document.getElementById('ctrSvcPod').value.trim(),
                    placeOfReceipt: document.getElementById('ctrSvcPlaceOfReceipt').value.trim(),
                    placeOfDelivery: document.getElementById('ctrSvcPlaceOfDelivery').value.trim(),
                    charges: []
                });

                clearServiceForm();
                svcForm.style.display = 'none';
                renderServices();
            });

            // ------------------------------------------------------------
            // Render Services list + per-service embedded Add Charge form
            // ------------------------------------------------------------
            function renderServices() {
                var list = document.getElementById('ctrServicesList');
                var empty = document.getElementById('ctrServicesEmpty');
                list.innerHTML = '';
                empty.style.display = state.contract.services.length ? 'none' : 'block';

                state.contract.services.forEach(function(svc) {
                    var card = document.createElement('div');
                    card.className = 'ctr-service-card';
                    card.innerHTML =
                        '<div class="ctr-service-header" data-svc="' + svc.id + '">' +
                        '<span class="ctr-caret">&#9660;</span>' +
                        '<span>' + svc.service + ' — ' + svc.process + ' (' + svc.shipmentMode + ')</span>' +
                        '<button type="button" class="btn btn-xs btn-outline-danger ml-auto ctr-remove-svc" data-svc="' +
                        svc.id + '">Remove</button>' +
                        '</div>' +
                        '<div class="ctr-service-body" id="body-' + svc.id + '">' +
                        '<div class="ctr-service-meta">' +
                        svc.countryOrigin + ' &rarr; ' + svc.countryDestination +
                        (svc.pol ? (' | POL: ' + svc.pol) : '') +
                        (svc.pod ? (' | POD: ' + svc.pod) : '') +
                        '</div>' +
                        '<table class="ctr-charges-table"><thead><tr>' +
                        '<th>Nature</th><th>Code</th><th>Description</th><th>Category</th><th>Rate Type</th><th>Rate</th><th></th>' +
                        '</tr></thead><tbody id="charges-' + svc.id + '"></tbody></table>' +
                        '<div class="ctr-empty-msg" id="charges-empty-' + svc.id +
                        '" style="display:none;">No charges added yet.</div>' +
                        '<button type="button" class="btn btn-sm btn-outline-primary ctr-add-charge-btn" data-svc="' +
                        svc.id + '">+ Add Charge</button>' +
                        '<div class="ctr-subform mt-2" id="chargeform-' + svc.id +
                        '" style="display:none;"></div>' +
                        '</div>';
                    list.appendChild(card);
                    renderCharges(svc);
                });
            }

            document.addEventListener('click', function(e) {
                var header = e.target.closest('.ctr-service-header');
                if (header && !e.target.closest('.ctr-remove-svc')) {
                    var body = document.getElementById('body-' + header.getAttribute('data-svc'));
                    header.classList.toggle('collapsed');
                    body.classList.toggle('collapsed');
                    return;
                }

                var removeBtn = e.target.closest('.ctr-remove-svc');
                if (removeBtn) {
                    var svcId = removeBtn.getAttribute('data-svc');
                    state.contract.services = state.contract.services.filter(function(s) {
                        return s.id !== svcId;
                    });
                    renderServices();
                    return;
                }

                var addChargeBtn = e.target.closest('.ctr-add-charge-btn');
                if (addChargeBtn) {
                    openChargeForm(addChargeBtn.getAttribute('data-svc'));
                    return;
                }

                var removeChargeBtn = e.target.closest('.ctr-remove-charge');
                if (removeChargeBtn) {
                    var svc = findService(removeChargeBtn.getAttribute('data-svc'));
                    var chargeId = removeChargeBtn.getAttribute('data-charge');
                    svc.charges = svc.charges.filter(function(c) {
                        return c.id !== chargeId;
                    });
                    renderCharges(svc);
                    return;
                }

                var slabAddBtn = e.target.closest('.ctr-slab-add-row');
                if (slabAddBtn) {
                    addSlabRow(slabAddBtn.closest('.ctr-slab-wrap'));
                    return;
                }

                var slabRemoveBtn = e.target.closest('.ctr-slab-row-remove');
                if (slabRemoveBtn) {
                    slabRemoveBtn.closest('tr').remove();
                    return;
                }
            });

            function findService(svcId) {
                return state.contract.services.filter(function(s) {
                    return s.id === svcId;
                })[0];
            }

            function renderCharges(svc) {
                var tbody = document.getElementById('charges-' + svc.id);
                var empty = document.getElementById('charges-empty-' + svc.id);
                if (!tbody) return;
                tbody.innerHTML = '';
                empty.style.display = svc.charges.length ? 'none' : 'block';

                svc.charges.forEach(function(c) {
                    var rateDisplay = c.rateType === 'Single' ? (c.rate + ' ' + c.chargeCurr) :
                        c.rateType === 'Formula' ? (c.formula || '—') :
                        (c.rateSlab || []).length + ' slab(s)';

                    var tr = document.createElement('tr');
                    tr.innerHTML =
                        '<td><span class="ctr-badge-' + c.chargeNature.toLowerCase() + '">' + c.chargeNature +
                        '</span></td>' +
                        '<td>' + c.chargeCode + '</td>' +
                        '<td>' + c.chargeDescription + '</td>' +
                        '<td>' + c.chargeCategory + '</td>' +
                        '<td>' + c.rateType + '</td>' +
                        '<td>' + rateDisplay + '</td>' +
                        '<td><span class="ctr-slab-row-remove ctr-remove-charge" data-svc="' + svc.id +
                        '" data-charge="' + c.id + '">&times;</span></td>';
                    tbody.appendChild(tr);
                });
            }

            // ------------------------------------------------------------
            // STEP 3: Add Charge — embedded inside the service card
            // (not a separate window), RateType-conditional inputs
            // ------------------------------------------------------------
            function openChargeForm(svcId) {
                var wrap = document.getElementById('chargeform-' + svcId);
                if (!wrap) return;
                wrap.style.display = 'block';
                wrap.innerHTML = chargeFormHtml(svcId);
                bindChargeFormEvents(svcId, wrap);
            }

            function chargeFormHtml(svcId) {
                return '<div class="ctr-grid">' +
                    '<div class="ctr-field"><label class="ctr-lbl ctr-required">Charge Nature</label>' +
                    '<select class="form-control form-control-sm ctr-cf-nature">' +
                    '<option value="Revenue">Revenue</option><option value="Cost">Cost</option>' +
                    '</select></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl ctr-required">Charge Category</label>' +
                    '<select class="form-control form-control-sm ctr-cf-category">' +
                    '<option value="">-- Select --</option>' +
                    CHARGE_CATEGORIES.map(function(c) {
                        return '<option>' + c + '</option>';
                    }).join('') +
                    '</select></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl ctr-required">Charge Code</label>' +
                    '<input type="text" class="form-control form-control-sm ctr-cf-code"></div>' +
                    '<div class="ctr-field ctr-field-wide"><label class="ctr-lbl ctr-required">Charge Description</label>' +
                    '<input type="text" class="form-control form-control-sm ctr-cf-desc"></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl ctr-required">Charge Based On</label>' +
                    '<select class="form-control form-control-sm ctr-cf-basedon">' +
                    '<option value="">-- Select --</option>' +
                    CHARGE_BASED_ON.map(function(b) {
                        return '<option>' + b + '</option>';
                    }).join('') +
                    '</select></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl">Based On UOM</label>' +
                    '<input type="text" class="form-control form-control-sm ctr-cf-uom"></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl ctr-required">Currency</label>' +
                    '<select class="form-control form-control-sm ctr-cf-currency">' +
                    CURRENCIES.map(function(c) {
                        return '<option>' + c + '</option>';
                    }).join('') +
                    '</select></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl">Min Amount</label>' +
                    '<input type="number" step="0.01" class="form-control form-control-sm ctr-cf-min"></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl">Max Amount</label>' +
                    '<input type="number" step="0.01" class="form-control form-control-sm ctr-cf-max"></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl ctr-required">Rate Type</label>' +
                    '<select class="form-control form-control-sm ctr-cf-ratetype">' +
                    '<option value="">-- Select --</option>' +
                    RATE_TYPES.map(function(r) {
                        return '<option>' + r + '</option>';
                    }).join('') +
                    '</select></div>' +
                    '</div>' +
                    '<div class="ctr-cf-ratearea mt-2"></div>' +
                    '<div class="d-flex justify-content-end mt-2">' +
                    '<button type="button" class="btn btn-sm btn-secondary mr-2 ctr-cf-cancel">Cancel</button>' +
                    '<button type="button" class="btn btn-sm btn-success ctr-cf-save" data-svc="' + svcId +
                    '">Save Charge</button>' +
                    '</div>';
            }

            function rateAreaHtml(rateType) {
                if (rateType === 'Single') {
                    return '<div class="ctr-field" style="max-width:200px;">' +
                        '<label class="ctr-lbl ctr-required">Rate</label>' +
                        '<input type="number" step="0.01" class="form-control form-control-sm ctr-cf-rate"></div>';
                }
                if (rateType === 'Slab Based' || rateType === 'Step Up') {
                    return '<div class="ctr-slab-wrap">' +
                        '<table class="ctr-slab-table"><thead><tr><th>Up To</th><th>Value</th><th></th></tr></thead>' +
                        '<tbody class="ctr-slab-rows">' +
                        '<tr><td><input type="number" class="form-control form-control-sm ctr-slab-upto" placeholder="e.g. 200"></td>' +
                        '<td><input type="number" step="0.01" class="form-control form-control-sm ctr-slab-value"></td>' +
                        '<td><span class="ctr-slab-row-remove">&times;</span></td></tr>' +
                        '</tbody></table>' +
                        '<button type="button" class="btn btn-xs btn-outline-primary ctr-slab-add-row mt-1">+ Add Slab</button>' +
                        '<div class="ctr-field mt-2" style="max-width:200px;">' +
                        '<label class="ctr-lbl ctr-required">Above (final tier value)</label>' +
                        '<input type="number" step="0.01" class="form-control form-control-sm ctr-slab-above"></div>' +
                        '</div>';
                }
                if (rateType === 'Formula') {
                    return '<div class="ctr-field ctr-field-wide">' +
                        '<label class="ctr-lbl ctr-required">Formula</label>' +
                        '<textarea class="form-control form-control-sm ctr-cf-formula" rows="2" placeholder="e.g. rate * weight + fuel_surcharge"></textarea></div>';
                }
                return '';
            }

            function addSlabRow(wrap) {
                var tbody = wrap.querySelector('.ctr-slab-rows');
                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td><input type="number" class="form-control form-control-sm ctr-slab-upto" placeholder="e.g. 500"></td>' +
                    '<td><input type="number" step="0.01" class="form-control form-control-sm ctr-slab-value"></td>' +
                    '<td><span class="ctr-slab-row-remove">&times;</span></td>';
                tbody.appendChild(tr);
            }

            function bindChargeFormEvents(svcId, wrap) {
                wrap.querySelector('.ctr-cf-ratetype').addEventListener('change', function() {
                    wrap.querySelector('.ctr-cf-ratearea').innerHTML = rateAreaHtml(this.value);
                });

                wrap.querySelector('.ctr-cf-cancel').addEventListener('click', function() {
                    wrap.style.display = 'none';
                    wrap.innerHTML = '';
                });

                wrap.querySelector('.ctr-cf-save').addEventListener('click', function() {
                    saveCharge(svcId, wrap);
                });
            }

            function saveCharge(svcId, wrap) {
                var svc = findService(svcId);
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
                    id: 'chg_' + (++chargeSeq),
                    chargeNature: wrap.querySelector('.ctr-cf-nature').value,
                    chargeCategory: required.category,
                    chargeCode: required.code,
                    chargeDescription: required.desc,
                    chargeBasedOn: required.basedOn,
                    chargeBasedOnUOM: wrap.querySelector('.ctr-cf-uom').value.trim(),
                    chargeCurr: wrap.querySelector('.ctr-cf-currency').value,
                    minAmount: parseFloat(wrap.querySelector('.ctr-cf-min').value) || null,
                    maxAmount: parseFloat(wrap.querySelector('.ctr-cf-max').value) || null,
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
                    charge.rate = parseFloat(rateVal);
                } else if (rateType === 'Slab Based' || rateType === 'Step Up') {
                    var rows = wrap.querySelectorAll('.ctr-slab-rows tr');
                    var slabs = [];
                    rows.forEach(function(row) {
                        var upto = row.querySelector('.ctr-slab-upto').value;
                        var value = row.querySelector('.ctr-slab-value').value;
                        if (upto !== '' && value !== '') {
                            slabs.push({
                                upto: parseFloat(upto),
                                value: parseFloat(value)
                            });
                        }
                    });
                    var aboveVal = wrap.querySelector('.ctr-slab-above').value;
                    if (!slabs.length || aboveVal === '') {
                        alert('Please add at least one slab row and the Above value.');
                        return;
                    }
                    slabs.push({
                        upto: null,
                        value: parseFloat(aboveVal)
                    }); // null upto = "Above"
                    charge.rateSlab = slabs;
                } else if (rateType === 'Formula') {
                    var formulaVal = wrap.querySelector('.ctr-cf-formula').value.trim();
                    if (!formulaVal) {
                        alert('Formula is required for Formula rate type.');
                        return;
                    }
                    charge.formula = formulaVal;
                }

                svc.charges.push(charge);
                wrap.style.display = 'none';
                wrap.innerHTML = '';
                renderCharges(svc);
            }

            // ------------------------------------------------------------
            // STEP 4: Save Contract — whole tree to session via ContractController::store
            // ------------------------------------------------------------
            document.getElementById('ctrSaveContractBtn').addEventListener('click', function() {
                var btn = this;
                var payload = {
                    contract_no: document.getElementById('ctrContractNo').value.trim(),
                    contract_type: document.getElementById('ctrContractType').value,
                    status: document.getElementById('ctrStatus').value,
                    effective_from: document.getElementById('ctrEffectiveFrom').value,
                    effective_to: document.getElementById('ctrEffectiveTo').value,
                    description: document.getElementById('ctrDescription').value.trim(),
                    remark: document.getElementById('ctrRemark').value.trim()
                };

                var missing = ['contract_no', 'contract_type', 'status', 'effective_from', 'effective_to']
                    .filter(function(k) {
                        return !payload[k];
                    });
                if (missing.length) {
                    alert('Please fill required Contract fields: ' + missing.join(', '));
                    return;
                }
                if (!state.contract.services.length) {
                    alert('Add at least one Service before saving.');
                    return;
                }

                var formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                Object.keys(payload).forEach(function(k) {
                    formData.append(k, payload[k]);
                });
                formData.append('services', JSON.stringify(state.contract.services));

                btn.disabled = true;
                btn.textContent = 'Saving...';

                fetch(STORE_URL, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(res) {
                        if (!res.success) throw new Error(res.message || 'Save failed');
                        alert(res.message || 'Contract saved.');
                    })
                    .catch(function(err) {
                        console.error('[Contract] save failed:', err);
                        alert('Failed to save contract: ' + err.message);
                    })
                    .finally(function() {
                        btn.disabled = false;
                        btn.textContent = 'Save Contract';
                    });
            });

            // ------------------------------------------------------------
            // Hydrate from an existing draft, if any
            // ------------------------------------------------------------
            if (existing) {
                document.getElementById('ctrContractNo').value = existing.contract_no || '';
                document.getElementById('ctrContractType').value = existing.contract_type || '';
                document.getElementById('ctrStatus').value = existing.status || 'Draft';
                document.getElementById('ctrEffectiveFrom').value = existing.effective_from || '';
                document.getElementById('ctrEffectiveTo').value = existing.effective_to || '';
                document.getElementById('ctrDescription').value = existing.description || '';
                document.getElementById('ctrRemark').value = existing.remark || '';
                state.contract.services = existing.services || [];
                serviceSeq = state.contract.services.length;
            }

            renderServices();
        })();
    </script>
@stop
