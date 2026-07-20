@extends('adminlte::page')

@section('title', 'Create Contract')

@section('content')
    <div class="container-fluid" id="contractCreateContent">

        <div id="ctrListSection">
            {{-- Dashboard stats --}}
            <div class="ctr-stat-grid mb-3">
                <div class="ctr-stat-card ctr-stat-pending" data-status="Pending">
                    <div class="ctr-stat-value">{{ $stats['pending'] }}</div>
                    <div class="ctr-stat-label">Pending</div>
                </div>
                <div class="ctr-stat-card ctr-stat-pending-approval" data-status="Pending Approval">
                    <div class="ctr-stat-value">{{ $stats['pending_approval'] }}</div>
                    <div class="ctr-stat-label">Pending Approval</div>
                </div>
                <div class="ctr-stat-card ctr-stat-authorized" data-status="Authorized">
                    <div class="ctr-stat-value">{{ $stats['authorized'] }}</div>
                    <div class="ctr-stat-label">Authorized</div>
                </div>
                <div class="ctr-stat-card ctr-stat-renewal" data-status="RenewalDue">
                    <div class="ctr-stat-value">{{ $stats['renewal_due'] }}</div>
                    <div class="ctr-stat-label">Renewal Due</div>
                </div>
                <div class="ctr-stat-card ctr-stat-total" data-status="">
                    <div class="ctr-stat-value">{{ $stats['total'] }}</div>
                    <div class="ctr-stat-label">Total</div>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-primary" id="ctrNewContractBtn">+ New</button>
            </div>
            <table class="table table-sm table-bordered" id="ctrContractsTable">
                <thead>
                    <tr>
                        <th>Contract No</th>
                        <th>Name</th>
                        <th>Effective From</th>
                        <th>Effective To</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="ctrContractsTableBody"></tbody>
            </table>
        </div>{{-- /ctrListSection --}}

        <div class="modal fade" id="ctrContractInfoModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Contract Information</h5>
                        <button type="button" class="close" data-dismiss="modal"
                            aria-label="Close"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="ctr-box-title d-flex align-items-center">
                            <span>Contract Information</span>
                            <button type="button" class="btn btn-sm btn-outline-primary ml-auto" id="ctrSaveHeaderBtn">Save
                                Contract
                                Info</button>
                        </div>
                        <div class="ctr-grid">
                            <input type="hidden" id="ctrContractNo" value="">

                            <div class="ctr-field">
                                <label class="ctr-lbl ctr-required">Contract Type</label>
                                <select class="form-control form-control-sm" id="ctrContractType">
                                    <option value="">-- Select --</option>
                                    <option value="Tariff">Tariff</option>
                                    <option value="Long Term">Long Term</option>
                                </select>
                            </div>

                            <div class="ctr-field ctr-field-span2">
                                <label class="ctr-lbl ctr-required">Contract Name</label>
                                <input type="text" class="form-control form-control-sm" id="ctrContractName">
                            </div>

                            <input type="hidden" id="ctrStatus" value="Draft">
                            <div class="ctr-field">
                                <label class="ctr-lbl ctr-required">Effective From</label>
                                <input type="date" class="form-control form-control-sm" id="ctrEffectiveFrom">
                            </div>
                            <div class="ctr-field">
                                <label class="ctr-lbl ctr-required">Effective To</label>
                                <input type="date" class="form-control form-control-sm" id="ctrEffectiveTo">
                            </div>
                            <div class="ctr-field ctr-field-wide">
                                <label class="ctr-lbl">Remark</label>
                                <input type="text" class="form-control form-control-sm" id="ctrRemark">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Services now lives inside a modal, opened after Contract Info is saved --}}
        <div class="modal fade" id="ctrServicesModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Services</h5>
                        <button type="button" class="close" data-dismiss="modal"
                            aria-label="Close"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="ctr-box-title d-flex align-items-center">
                            <span>Services</span>
                            <button type="button" class="btn btn-sm btn-primary ml-auto" id="ctrAddServiceBtn">+
                                Add
                                Service</button>
                        </div>

                        <div id="ctrServiceForm" class="ctr-subform mb-3" style="display:none;">

                            <div class="ctr-subsection-title">Service Details</div>
                            <div class="ctr-grid-2 mb-3">
                                <div class="ctr-field"><label class="ctr-lbl ctr-required">Service</label>
                                    <select class="form-control form-control-sm" id="ctrSvcService">
                                        <option value="">-- Select --</option>
                                    </select>
                                </div>
                                <div class="ctr-field"><label class="ctr-lbl ctr-required">Process</label>
                                    <select class="form-control form-control-sm" id="ctrSvcProcess">
                                        <option value="">-- Select --</option>
                                    </select>
                                </div>
                                <div class="ctr-field"><label class="ctr-lbl">IncoTerm</label>
                                    <select class="form-control form-control-sm" id="ctrSvcIncoTerm">
                                        <option value="">-- Select --</option>
                                    </select>
                                </div>
                                <div class="ctr-field"><label class="ctr-lbl">Service Class</label>
                                    <select class="form-control form-control-sm" id="ctrSvcServiceClass">
                                        <option value="">-- Select --</option>
                                    </select>
                                </div>
                                <div class="ctr-field"><label class="ctr-lbl">Shipment Type</label>
                                    <select class="form-control form-control-sm" id="ctrSvcShipmentType">
                                        <option value="">-- Select --</option>
                                    </select>
                                </div>
                                <div class="ctr-field"><label class="ctr-lbl">Shipment Mode</label>
                                    <select class="form-control form-control-sm" id="ctrSvcShipmentMode">
                                        <option value="">-- Select --</option>
                                    </select>
                                </div>
                                <div class="ctr-field"><label class="ctr-lbl">Movement Type</label>
                                    <select class="form-control form-control-sm" id="ctrSvcMovementType">
                                        <option value="">-- Select --</option>
                                    </select>
                                </div>
                                <div class="ctr-field"></div>
                            </div>

                            <div class="ctr-grid-2 mb-3">
                                <div>
                                    <div class="ctr-subsection-title">Origin Details</div>
                                    <div class="ctr-field mb-2"><label class="ctr-lbl">Country of Origin</label>
                                        <select class="form-control form-control-sm" id="ctrSvcCountryOrigin">
                                            <option value="">-- Select --</option>
                                        </select>
                                    </div>
                                    <div class="ctr-field mb-2"><label class="ctr-lbl">Port of Loading</label>
                                        <input type="text" class="form-control form-control-sm" id="ctrSvcPol"
                                            placeholder="Type to search..." autocomplete="off">
                                        <input type="hidden" id="ctrSvcPolCode">
                                    </div>
                                    <div class="ctr-field"><label class="ctr-lbl">Place of Receipt</label>
                                        <input type="text" class="form-control form-control-sm"
                                            id="ctrSvcPlaceOfReceipt" placeholder="Type to search..." autocomplete="off">
                                        <input type="hidden" id="ctrSvcPlaceOfReceiptCode">
                                    </div>
                                </div>
                                <div>
                                    <div class="ctr-subsection-title">Destination Details</div>
                                    <div class="ctr-field mb-2"><label class="ctr-lbl">Country of
                                            Destination</label>
                                        <select class="form-control form-control-sm" id="ctrSvcCountryDestination">
                                            <option value="">-- Select --</option>
                                        </select>
                                    </div>
                                    <div class="ctr-field mb-2"><label class="ctr-lbl">Port of Discharge</label>
                                        <input type="text" class="form-control form-control-sm" id="ctrSvcPod"
                                            placeholder="Type to search..." autocomplete="off">
                                        <input type="hidden" id="ctrSvcPodCode">
                                    </div>
                                    <div class="ctr-field"><label class="ctr-lbl">Place of Delivery</label>
                                        <input type="text" class="form-control form-control-sm"
                                            id="ctrSvcPlaceOfDelivery" placeholder="Type to search..."
                                            autocomplete="off">
                                        <input type="hidden" id="ctrSvcPlaceOfDeliveryCode">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-2">
                                <button type="button" class="btn btn-sm btn-secondary mr-2"
                                    id="ctrCancelServiceBtn">Cancel</button>
                                <button type="button" class="btn btn-sm btn-success" id="ctrSaveServiceBtn">Save
                                    Service</button>
                            </div>
                        </div>

                        <div id="ctrServicesList"></div>
                        <div class="ctr-empty-msg" id="ctrServicesEmpty">No services added yet.</div>
                    </div>{{-- /modal-body --}}
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="ctrDoneBtn">Done</button>
                    </div>
                </div>{{-- /modal-content --}}
            </div>{{-- /modal-dialog --}}
        </div>{{-- /ctrServicesModal --}}
    </div>{{-- /contractCreateContent --}}

    {{-- Charge row template --}}
    <template id="ctrChargeRowTemplate">
        <tr>
            <td class="ctr-nature-cell"></td>
            <td class="ctr-code-cell">
                <input type="text" class="form-control form-control-sm ctr-cf-code"
                    placeholder="Type at least 3 characters...">
                <input type="hidden" class="ctr-cf-code-hidden">
            </td>
            <td class="ctr-desc-cell"></td>
            <td class="ctr-category-cell"></td>
            <td class="ctr-ratetype-cell"></td>
            <td class="ctr-rate-cell"></td>
            <td class="ctr-actions-cell"></td>
        </tr>
    </template>
@stop

@section('css')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
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

        .ctr-field-span2 {
            grid-column: span 2;
        }

        .ctr-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px 16px;
        }

        .ctr-grid-2 .ctr-field-wide {
            grid-column: span 2;
        }

        .ctr-lbl {
            font-size: .78rem;
            color: #495057;
            margin-bottom: 2px;
            display: block;
        }

        .ctr-subsection-title {
            font-size: .8rem;
            font-weight: 700;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: .02em;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e2e2ec;
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

        .ctr-slab-above-label {
            font-style: italic;
            color: #6c757d;
            padding-left: 4px;
        }

        .ctr-slab-row-remove {
            cursor: pointer;
            color: #dc3545;
        }

        .ctr-fb-reset {
            font-size: .75rem;
        }

        .ctr-stat-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
        }

        .ctr-stat-card {
            border-radius: 6px;
            padding: 20px;
            border: none;
            color: #fff;
        }

        .ctr-stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: inherit;
        }

        .ctr-stat-label {
            font-size: .82rem;
            color: inherit;
            opacity: .9;
            text-transform: uppercase;
            letter-spacing: .03em;
            margin-top: 4px;
        }

        .ctr-stat-pending {
            background: #6c757d;
        }

        .ctr-stat-pending-approval {
            background: #ffc107;
            color: #212529;
            /* amber is too light for white text to stay legible */
        }

        .ctr-stat-authorized {
            background: #28a745;
        }

        .ctr-stat-renewal {
            background: #dc3545;
        }

        .ctr-stat-total {
            background: #4B49AC;
        }

        .ctr-stat-card {
            cursor: pointer;
            transition: box-shadow .15s ease;
        }

        .ctr-stat-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
        }

        .ctr-stat-card.ctr-stat-active {
            box-shadow: 0 0 0 3px rgba(0, 0, 0, .35) inset;
        }

        @media (max-width: 768px) {
            .ctr-stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .ctr-formula-cols {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .ctr-formula-col select[multiple] {
            height: auto;
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script>
        (function() {
            // ── PHP‑passed variables ──────────────────────────────────────────────
            var CONTRACT_ID = @json($contractId);
            var STORE_URL = @json(route('contracts.store', $contractId ?? ''));
            var SERVICES_STORE_URL = @json(route('contracts.services.store', $contractId ?? ''));
            var CONTRACTS_LIST_URL = @json(route('contracts.list'));
            var OFFICE_CODE = @json($officeCode ?? '');

            var CREATE_URL = @json(route('contracts.create'));
            var NEW_DRAFT_URL = @json(route('contracts.new-draft'));

            // ── Master data endpoints ─────────────────────────────────────────────
            var MASTER_BASE = '/contracts/masters';

            // ── State ──────────────────────────────────────────────────────────────
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
            state.contractSaved = !!existing;

            // ── Local draft autosave (prevents data loss on refresh) ─────────────
            var LOCAL_DRAFT_KEY = 'ctr_draft_' + CONTRACT_ID;

            function collectLocalDraftPayload() {
                return {
                    contractType: document.getElementById('ctrContractType').value,
                    contractName: document.getElementById('ctrContractName').value,
                    effectiveFrom: document.getElementById('ctrEffectiveFrom').value,
                    effectiveTo: document.getElementById('ctrEffectiveTo').value,
                    remark: document.getElementById('ctrRemark').value,
                    services: state.contract.services,
                    contractSaved: state.contractSaved
                };
            }

            function saveLocalDraft() {
                try {
                    localStorage.setItem(LOCAL_DRAFT_KEY, JSON.stringify(collectLocalDraftPayload()));
                } catch (e) {
                    console.error('[Contract] local draft save failed:', e);
                }
            }

            function clearLocalDraft() {
                try {
                    localStorage.removeItem(LOCAL_DRAFT_KEY);
                } catch (e) {
                    console.error('[Contract] local draft clear failed:', e);
                }
            }

            function loadLocalDraft() {
                try {
                    var raw = localStorage.getItem(LOCAL_DRAFT_KEY);
                    return raw ? JSON.parse(raw) : null;
                } catch (e) {
                    console.error('[Contract] local draft load failed:', e);
                    return null;
                }
            }

            var serviceSeq = 0;
            var chargeSeq = 0;

            // ── Helper: fetch master list ─────────────────────────────────────────
            function fetchMasterList(endpoint, params) {
                var url = MASTER_BASE + '/' + endpoint;
                var qs = params ? '?' + $.param(params) : '';
                return $.ajax({
                    url: url + qs,
                    type: 'GET',
                    dataType: 'json'
                });
            }

            function clampDecimals(input, places) {
                if (!input) return;
                $(input).on('blur', function() {
                    if (this.value === '') return;
                    var num = parseFloat(this.value);
                    if (isNaN(num)) return;
                    this.value = num.toFixed(places);
                });
            }

            // ── Helper: populate a select from API response ──────────────────────
            function populateSelect($select, data, valueField, labelField) {
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
            }

            function showSection(id) {
                document.getElementById('ctrListSection').style.display = (id === 'ctrListSection') ? 'block' : 'none';
            }

            // state.allContracts holds the unfiltered response; the table always
            // renders from a client-side filter of this, so status filtering never
            // needs a re-fetch.
            state.allContracts = [];
            state.activeStatusFilter = 'Pending'; // default view per dashboard spec

            function isRenewalDue(effectiveTo) {
                if (!effectiveTo) return false;
                var due = new Date(effectiveTo);
                if (isNaN(due.getTime())) return false;
                var today = new Date();
                today.setHours(0, 0, 0, 0);
                due.setHours(0, 0, 0, 0);
                var days = Math.round((due - today) / 86400000);
                return days >= 0 && days <= 10; // mirrors ContractController::computeDashboardStats
            }

            function filterContracts(list, status) {
                if (!status) return list; // Total card = no filter
                if (status === 'RenewalDue') {
                    return list.filter(function(c) {
                        return isRenewalDue(c.effectiveTo);
                    });
                }
                return list.filter(function(c) {
                    return c.status === status;
                });
            }

            function renderContractsTable(list) {
                var $tbody = $('#ctrContractsTableBody').empty();
                if (!list || !list.length) {
                    $tbody.append(
                        '<tr><td colspan="6" class="text-center text-muted">No contracts found.</td></tr>'
                    );
                    return;
                }
                list.forEach(function(c) {
                    var contractId = c.id || c.contractId || c.Id || '';
                    $tbody.append('<tr><td>' + c.contractNo + '</td><td>' + c.contractName +
                        '</td><td>' +
                        c.effectiveFrom + '</td><td>' + c.effectiveTo + '</td><td>' + c.status +
                        '</td><td>' +
                        '<button type="button" class="btn btn-xs btn-outline-primary ctr-view-contract mr-1" data-id="' +
                        contractId + '">View</button>' +
                        '<button type="button" class="btn btn-xs btn-outline-secondary ctr-edit-contract" data-id="' +
                        contractId + '">Edit</button>' +
                        '</td></tr>');
                });
            }

            function applyStatusFilterAndRender() {
                renderContractsTable(filterContracts(state.allContracts, state.activeStatusFilter));
                $('.ctr-stat-card').removeClass('ctr-stat-active');
                $('.ctr-stat-card[data-status="' + state.activeStatusFilter + '"]').addClass('ctr-stat-active');
            }

            function loadContractsList() {
                $.get(CONTRACTS_LIST_URL, function(res) {
                    if (!res.success) {
                        $('#ctrContractsTableBody').html(
                            '<tr><td colspan="6" class="text-center text-danger">Failed to load contracts.</td></tr>'
                        );
                        return;
                    }
                    state.allContracts = res.data || [];
                    applyStatusFilterAndRender();
                }).fail(function() {
                    $('#ctrContractsTableBody').html(
                        '<tr><td colspan="6" class="text-center text-danger">Failed to load contracts.</td></tr>'
                    );
                });
            }

            document.getElementById('ctrNewContractBtn').addEventListener('click', function() {
                var btn = this;
                btn.disabled = true;
                $.get(NEW_DRAFT_URL, function(res) {
                    if (!res.success) {
                        alert('Could not start a new contract. Please try again.');
                        return;
                    }
                    CONTRACT_ID = res.data.contract_id;
                    STORE_URL = res.data.store_url;
                    SERVICES_STORE_URL = res.data.services_store_url;
                    LOCAL_DRAFT_KEY = 'ctr_draft_' + CONTRACT_ID; // was: computed once, never updated

                    state.contract = {
                        contractNo: '',
                        contractType: '',
                        status: 'Draft',
                        effectiveFrom: '',
                        effectiveTo: '',
                        description: '',
                        remark: '',
                        services: []
                    };
                    state.contractSaved = false;

                    document.getElementById('ctrContractType').value = '';
                    document.getElementById('ctrContractName').value = '';
                    document.getElementById('ctrEffectiveFrom').value = '';
                    document.getElementById('ctrEffectiveTo').value = '';
                    document.getElementById('ctrRemark').value = '';

                    // NEW: clear any leftover service/charge UI from the previous contract
                    svcForm.style.display = 'none';
                    clearServiceForm();
                    renderServices();

                    $('#ctrContractInfoModal').modal('show');
                }).fail(function() {
                    alert('Could not start a new contract. Please try again.');
                }).always(function() {
                    btn.disabled = false;
                });
            });

            // ── Populate service form dropdowns ──────────────────────────────────
            function populateServiceFormMasters() {
                // Service
                fetchMasterList('services').done(function(res) {
                    if (res.success) populateSelect($('#ctrSvcService'), res.data, 'Code', 'Name');
                });
                // Process
                fetchMasterList('processes').done(function(res) {
                    if (res.success) populateSelect($('#ctrSvcProcess'), res.data, 'Code', 'Name');
                });
                // IncoTerm — updated to show both code and name
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
                        populateSelect($('#ctrSvcIncoTerm'), withLabels, 'IncoTermCode', 'IncoTermLabel');
                    }
                });
                // Shipment Type
                fetchMasterList('shipment-types').done(function(res) {
                    if (res.success) populateSelect($('#ctrSvcShipmentType'), res.data, 'Code', 'Name');
                });
                // Shipment Mode
                fetchMasterList('shipment-modes').done(function(res) {
                    if (res.success) populateSelect($('#ctrSvcShipmentMode'), res.data, 'Code', 'Code');
                });
                // Movement Type
                fetchMasterList('movement-types').done(function(res) {
                    if (res.success) populateSelect($('#ctrSvcMovementType'), res.data, 'Code', 'Name');
                });
                // Service Class
                fetchMasterList('service-classes').done(function(res) {
                    if (res.success) populateSelect($('#ctrSvcServiceClass'), res.data, 'Code', 'Name');
                });
                // Countries
                fetchMasterList('countries').done(function(res) {
                    if (Array.isArray(res)) {
                        populateSelect($('#ctrSvcCountryOrigin'), res, 'CountCode', 'CountryName');
                        populateSelect($('#ctrSvcCountryDestination'), res, 'CountCode', 'CountryName');
                    }
                });
            }

            // ── Location autocomplete (jQuery UI) ────────────────────────────────
            function initLocationAutocomplete(inputId, codeInputId) {
                var $input = $('#' + inputId);
                var $codeInput = $('#' + codeInputId);
                if ($input.hasClass('ui-autocomplete-input')) {
                    $input.autocomplete('destroy');
                }
                $input.autocomplete({
                    minLength: 2,
                    delay: 300,
                    source: function(request, response) {
                        fetchMasterList('locations', {
                                filter: request.term,
                                include_country: 0
                            })
                            .done(function(res) {
                                if (res.success && res.locations.length) {
                                    response(res.locations.map(function(l) {
                                        return {
                                            label: l.LocationCode + ' — ' + l.LocationName,
                                            value: l.LocationName,
                                            code: l.LocationCode
                                        };
                                    }));
                                } else {
                                    response([{
                                        label: 'No locations found',
                                        value: '',
                                        disabled: true
                                    }]);
                                }
                            })
                            .fail(function() {
                                response([{
                                    label: 'Error loading locations',
                                    value: '',
                                    disabled: true
                                }]);
                            });
                    },
                    select: function(event, ui) {
                        if (ui.item.disabled) {
                            event.preventDefault();
                            return false;
                        }
                        $input.val(ui.item.value);
                        $codeInput.val(ui.item.code);
                        return false;
                    }
                });
            }

            // ── Charge form: populate masters ────────────────────────────────────
            function loadChargeFormMasters(wrap, chargeToEdit) {
                // ── Charge Category ──────────────────────────────────
                fetchMasterList('charge-categories').done(function(res) {
                    if (res.success) {
                        populateSelect($(wrap).find('.ctr-cf-category'), res.data, 'Code', 'Name');
                        if (chargeToEdit) $(wrap).find('.ctr-cf-category').val(chargeToEdit.chargeCategory);
                    }
                });

                // ── Charge Based On ──────────────────────────────────
                fetchMasterList('based-on').done(function(res) {
                    if (res.success) {
                        var options = res.based_on_list.map(function(b) {
                            return {
                                Code: b.Code,
                                Label: b.Name
                            };
                        });
                        populateSelect($(wrap).find('.ctr-cf-basedon'), options, 'Code', 'Label');
                        if (chargeToEdit) $(wrap).find('.ctr-cf-basedon').val(chargeToEdit.chargeBasedOn);
                    }
                });

                // ── Based On UOM ─────────────────────────────────────
                // Load UOM only when editing an existing charge with a value
                if (chargeToEdit && chargeToEdit.chargeBasedOn) {
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
                            populateSelect($(wrap).find('.ctr-cf-uom'), options, 'Code', 'Label');
                            if (chargeToEdit) $(wrap).find('.ctr-cf-uom').val(chargeToEdit.chargeBasedOnUOM);
                        }
                    });
                } else {
                    // For new charges, leave the UOM select empty (will load on Based On change)
                    $(wrap).find('.ctr-cf-uom').empty().append('<option value="">Select Based On first</option>');
                }

                // ── Currency ──────────────────────────────────────────
                var officeId = OFFICE_CODE || '';
                fetchMasterList('currencies', {
                    office_id: officeId
                }).done(function(res) {
                    if (res.success) {
                        var $sel = $(wrap).find('.ctr-cf-currency');
                        $sel.empty().append('<option value="">-- Select --</option>');
                        res.currencies.forEach(function(c) {
                            $sel.append('<option value="' + c.CurrencyCode + '">' + c.CurrencyCode +
                                '</option>');
                        });
                        if (chargeToEdit) $sel.val(chargeToEdit.chargeCurr);
                    }
                });

                // ── Charge Code ──────────────────────────────────
                fetchMasterList('charge-codes', {
                    office_code: OFFICE_CODE,
                    charge_type: $(wrap).find('.ctr-cf-nature').val() || 'Revenue'
                }).done(function(res) {
                    if (res.success) {
                        var $select = $(wrap).find('.ctr-cf-code');
                        $select.empty().append('<option value="">-- Select --</option>');
                        res.charge_codes.forEach(function(c) {
                            var code = c.Code || '';
                            var name = c.Description || c.Name || c.Code || '';
                            var label = code + ' - ' + name;
                            var curr = c.Curr || '';
                            $select.append('<option value="' + code + '" data-description="' + name +
                                '" data-currency="' + curr + '">' + label + '</option>');
                        });
                        if (chargeToEdit) $select.val(chargeToEdit.chargeCode);
                        // Trigger change to populate description for edit
                        $select.trigger('change');
                    }
                });

                // ── If editing, fill remaining fields ────────────────
                if (!chargeToEdit) return;

                // ── Populate Reimbursable/Accrual on edit ────────────────────────
                if (chargeToEdit.toBeAdded) {
                    var nature = chargeToEdit.chargeNature || 'Revenue';
                    if (nature === 'Revenue') {
                        $(wrap).find('#ctrIsAccruable').prop('checked', true);
                    } else {
                        $(wrap).find('#ctrIsReimbursable').prop('checked', true);
                    }
                }

                wrap.querySelector('.ctr-cf-desc').value = chargeToEdit.chargeDescription || '';
                wrap.querySelector('.ctr-cf-min').value = chargeToEdit.minAmount ?? '';
                wrap.querySelector('.ctr-cf-max').value = chargeToEdit.maxAmount ?? '';

                $(wrap).find('.ctr-cf-ratetype').val(chargeToEdit.rateType);
                $(wrap).find('.ctr-cf-inline-rate').html(inlineRateHtml(chargeToEdit.rateType));
                $(wrap).find('.ctr-cf-ratearea').html(rateAreaHtml(chargeToEdit.rateType));

                if (chargeToEdit.rateType === 'Single') {
                    wrap.querySelector('.ctr-cf-rate').value = chargeToEdit.rate ?? '';
                    clampDecimals(wrap.querySelector('.ctr-cf-rate'), 5);
                } else if (chargeToEdit.rateType === 'Slab Based' || chargeToEdit.rateType === 'Step Up') {
                    fillSlabRows(wrap.querySelector('.ctr-slab-rows'), chargeToEdit.rateSlab);
                } else if (chargeToEdit.rateType === 'Formula') {
                    var svc2 = findService(svcId);
                    var isRevenue2 = (chargeToEdit.chargeNature === 'Cost') ? 0 : 1;
                    if (svc2 && svc2.serviceFilterId) {
                        loadFormulaFields(svc2.serviceFilterId, isRevenue2).done(function(res) {
                            if (res.success) FORMULA_FIELDS = res.data.map(function(name) {
                                return {
                                    label: name,
                                    token: name
                                };
                            });
                            bindFormulaBuilderEvents(wrap);
                        });
                    } else {
                        bindFormulaBuilderEvents(wrap);
                    }
                    wrap.querySelector('.ctr-cf-formula').value = chargeToEdit.formula || '';
                    $(wrap).find('.ctr-fb-ratetype').val(chargeToEdit.formulaRateType || 'Single');
                    renderFormulaRateArea(wrap, chargeToEdit.formulaRateType || 'Single');
                    if (chargeToEdit.formulaRateType === 'Slab Based') {
                        fillSlabRows(wrap.querySelector('.ctr-fb-ratearea .ctr-slab-rows'), chargeToEdit.formulaSlab);
                    } else {
                        wrap.querySelector('.ctr-formula-rate').value = chargeToEdit.formulaRate ?? '';
                    }
                }
            }

            function initChargeCodeAutocomplete(wrap) {
                var $input = $(wrap).find('.ctr-cf-code');
                var $hidden = $(wrap).find('.ctr-cf-code-hidden');
                var $nature = $(wrap).find('.ctr-cf-nature');
                var $desc = $(wrap).find('.ctr-cf-desc');
                var $currency = $(wrap).find('.ctr-cf-currency');

                if ($input.hasClass('ui-autocomplete-input')) {
                    $input.autocomplete('destroy');
                }

                $input.autocomplete({
                    source: function(request, response) {
                        if (request.term.length < 3) {
                            response([]);
                            return;
                        }

                        var chargeType = ($nature.val() === 'Cost') ? 'Cost' : 'Revenue';

                        fetchMasterList('charge-codes', {
                            office_code: OFFICE_CODE,
                            charge_type: chargeType,
                            filter: request.term
                        }).done(function(res) {
                            if (res.success && res.charge_codes && res.charge_codes.length) {
                                response(res.charge_codes.map(function(item) {
                                    return {
                                        label: item.Code + ' - ' + (item.Description ||
                                            item.Name || item.Code),
                                        value: item.Code,
                                        name: item.Description || item.Name || item
                                            .Code,
                                        currency: item.Curr,
                                        vat: item.VAT
                                    };
                                }));
                            } else {
                                response([{
                                    label: 'No charge codes found',
                                    value: '',
                                    disabled: true
                                }]);
                            }
                        }).fail(function() {
                            response([{
                                label: 'Error loading charge codes',
                                value: '',
                                disabled: true
                            }]);
                        });
                    },
                    minLength: 3,
                    delay: 300,
                    select: function(event, ui) {
                        if (ui.item.disabled) {
                            event.preventDefault();
                            return false;
                        }
                        $input.val(ui.item.value);
                        $hidden.val(ui.item.value);
                        if (ui.item.name) {
                            $desc.val(ui.item.name);
                        }
                        if (ui.item.currency) {
                            $currency.val(ui.item.currency);
                        }
                        // Optional: if you have a VAT field, set it here
                        return false;
                    }
                });

                // Re‑trigger search when Charge Nature changes
                $nature.off('change.autocomplete').on('change.autocomplete', function() {
                    var term = $input.val();
                    if (term.length >= 3) {
                        $input.autocomplete('search', term);
                    }
                });
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

            // ── Service form visibility and population ──────────────────────────
            var svcForm = document.getElementById('ctrServiceForm');

            function openServiceForm() {
                svcForm.style.display = 'block';
                if (!$('#ctrSvcService option').length) {
                    populateServiceFormMasters();
                }
                initLocationAutocomplete('ctrSvcPol', 'ctrSvcPolCode');
                initLocationAutocomplete('ctrSvcPod', 'ctrSvcPodCode');
                initLocationAutocomplete('ctrSvcPlaceOfReceipt', 'ctrSvcPlaceOfReceiptCode');
                initLocationAutocomplete('ctrSvcPlaceOfDelivery', 'ctrSvcPlaceOfDeliveryCode');
            }
            document.getElementById('ctrAddServiceBtn').addEventListener('click', openServiceForm);

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
                ['ctrSvcPolCode', 'ctrSvcPodCode', 'ctrSvcPlaceOfReceiptCode', 'ctrSvcPlaceOfDeliveryCode'].forEach(
                    function(id) {
                        document.getElementById(id).value = '';
                    });
            }

            // ── Save Service ──────────────────────────────────────────────────────
            document.getElementById('ctrSaveServiceBtn').addEventListener('click', function() {
                var required = {
                    service: document.getElementById('ctrSvcService').value,
                    process: document.getElementById('ctrSvcProcess').value
                };
                var missing = Object.keys(required).filter(function(k) {
                    return !required[k];
                });
                if (missing.length) {
                    alert('Please fill required Service fields: ' + missing.join(', '));
                    return;
                }

                var shipmentType = document.getElementById('ctrSvcShipmentType').value;
                var shipmentMode = document.getElementById('ctrSvcShipmentMode').value;
                var countryOrigin = document.getElementById('ctrSvcCountryOrigin').value.trim();
                var countryDestination = document.getElementById('ctrSvcCountryDestination').value.trim();

                if (['Export', 'Local'].indexOf(required.process) !== -1 && !countryOrigin) {
                    alert('Country of Origin is required when Process is Export or Local.');
                    return;
                }
                if (required.process === 'Import' && !countryDestination) {
                    alert('Country of Destination is required when Process is Import.');
                    return;
                }

                var payload = {
                    service: required.service,
                    process: required.process,
                    incoterm: document.getElementById('ctrSvcIncoTerm').value,
                    serviceClass: document.getElementById('ctrSvcServiceClass').value.trim(),
                    shipmentType: shipmentType,
                    movementType: document.getElementById('ctrSvcMovementType').value,
                    shipmentMode: shipmentMode,
                    countryOrigin: countryOrigin,
                    countryDestination: countryDestination,
                    pol: document.getElementById('ctrSvcPolCode').value.trim(),
                    pod: document.getElementById('ctrSvcPodCode').value.trim(),
                    placeOfReceipt: document.getElementById('ctrSvcPlaceOfReceiptCode').value.trim(),
                    placeOfDelivery: document.getElementById('ctrSvcPlaceOfDeliveryCode').value.trim()
                };

                var btn = this;
                var originalText = btn.textContent;
                btn.disabled = true;
                btn.textContent = 'Saving...';

                $.ajax({
                    url: SERVICES_STORE_URL,
                    type: 'POST',
                    data: $.extend({
                        _token: document.querySelector('meta[name="csrf-token"]').content
                    }, payload),
                    dataType: 'json'
                }).done(function(res) {
                    if (!res.success) {
                        alert('Failed to save service: ' + (res.message || 'Unknown error'));
                        return;
                    }
                    serviceSeq += 1;
                    payload.id = 'svc_' + serviceSeq;
                    payload.serviceFilterId = res.data.service_filter_id;
                    payload.charges = [];
                    state.contract.services.push(payload);

                    clearServiceForm();
                    svcForm.style.display = 'none';
                    renderServices();
                    saveLocalDraft();
                }).fail(function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Save failed.';
                    alert('Failed to save service: ' + msg);
                }).always(function() {
                    btn.disabled = false;
                    btn.textContent = originalText;
                });
            });

            // ── Render services & charges ────────────────────────────────────────
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
                        '<th>Nature</th><th>Code</th><th>Description</th><th>Category</th><th>Rate Type</th><th>Rate</th><th>Action</th>' +
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
                        '<td class="text-nowrap">' +
                        '<i class="fas fa-pen ctr-edit-charge mr-2" data-svc="' + svc.id + '" data-charge="' + c
                        .id +
                        '" style="cursor:pointer;color:#4B49AC;" title="Edit"></i>' +
                        '<span class="ctr-slab-row-remove ctr-remove-charge" data-svc="' + svc.id +
                        '" data-charge="' + c.id +
                        '" title="Delete">&times;</span></td>';
                    tbody.appendChild(tr);
                });
            }

            // ── Open Charge Form ──────────────────────────────────────────────────
            function openChargeForm(svcId, chargeToEdit) {
                var wrap = document.getElementById('chargeform-' + svcId);
                if (!wrap) return;
                wrap.style.display = 'block';
                wrap.innerHTML = chargeFormHtml(svcId, !!chargeToEdit);
                bindChargeFormEvents(svcId, wrap, chargeToEdit);
                loadChargeFormMasters(wrap, chargeToEdit);
            }

            function chargeFormHtml(svcId, isEdit) {
                var lock = isEdit ? ' disabled' : '';
                return '<div class="ctr-grid-2">' +
                    '<div class="ctr-field"><label class="ctr-lbl ctr-required">Charge Nature</label>' +
                    '<select class="form-control form-control-sm ctr-cf-nature"' + lock + '>' +
                    '<option value="Revenue">Revenue</option><option value="Cost">Cost</option>' +
                    '</select></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl ctr-required">Charge Category</label>' +
                    '<select class="form-control form-control-sm ctr-cf-category"' + lock + '>' +
                    '<option value="">-- Select --</option>' +
                    '</select></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl ctr-required">Charge Code</label>' +
                    '<select class="form-control form-control-sm ctr-cf-code"' + lock + '>' +
                    '<option value="">-- Select --</option>' +
                    '</select></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl ctr-required">Charge Description</label>' +
                    '<input type="text" class="form-control form-control-sm ctr-cf-desc" readonly></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl ctr-required">Charge Based On</label>' +
                    '<select class="form-control form-control-sm ctr-cf-basedon"' + lock + '>' +
                    '<option value="">-- Select --</option>' +
                    '</select></div>' +

                    '<div class="ctr-field"><label class="ctr-lbl">Based On UOM</label>' +
                    '<select class="form-control form-control-sm ctr-cf-uom"' + lock +
                    '><option value="">-- Select --</option></select></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl ctr-required">Currency</label>' +
                    '<select class="form-control form-control-sm ctr-cf-currency">' +
                    '<option value="">-- Select --</option>' +
                    '</select></div>' +
                    '<div class="ctr-field" id="ctr-is-accruable-container" style="display:none;">' +
                    '<div class="form-check mt-4">' +
                    '<input class="form-check-input" type="checkbox" id="ctrIsAccruable">' +
                    '<label class="form-check-label" for="ctrIsAccruable"><strong>Is Accruable</strong> <small class="text-muted">(Adds to Cost)</small></label>' +
                    '</div></div>' +
                    '<div class="ctr-field" id="ctr-is-reimbursable-container" style="display:none;">' +
                    '<div class="form-check mt-4">' +
                    '<input class="form-check-input" type="checkbox" id="ctrIsReimbursable">' +
                    '<label class="form-check-label" for="ctrIsReimbursable"><strong>Is Reimbursable</strong> <small class="text-muted">(Adds to Revenue)</small></label>' +
                    '</div></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl">Min Amount</label>' +
                    '<input type="number" step="0.01" class="form-control form-control-sm ctr-cf-min"></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl">Max Amount</label>' +
                    '<input type="number" step="0.01" class="form-control form-control-sm ctr-cf-max"></div>' +
                    '<div class="ctr-field"><label class="ctr-lbl ctr-required">Rate Type</label>' +
                    '<select class="form-control form-control-sm ctr-cf-ratetype">' +
                    '<option value="">-- Select --</option>' +
                    '<option value="Single">Single</option>' +
                    '<option value="Slab Based">Slab Based</option>' +
                    '<option value="Step Up">Step Up</option>' +
                    '<option value="Formula">Formula</option>' +
                    '</select></div>' +
                    '<div class="ctr-field ctr-cf-inline-rate"></div>' +
                    '</div>' +
                    '<div class="ctr-cf-ratearea mt-2"></div>' +
                    '<div class="d-flex justify-content-end mt-2">' +
                    '<button type="button" class="btn btn-sm btn-secondary mr-2 ctr-cf-cancel">Cancel</button>' +
                    '<button type="button" class="btn btn-sm btn-success ctr-cf-save" data-svc="' + svcId +
                    '">' + (isEdit ? 'Update Charge' : 'Save Charge') + '</button>' +
                    '</div>';
            }

            // ── Rate Type conditional UI (slabs, formula) ──────────────────────
            var FORMULA_FIELDS = [];

            function loadFormulaFields(svcId, isRevenue) {
                return $.ajax({
                    url: '/contracts/' + encodeURIComponent(CONTRACT_ID) + '/services/' + encodeURIComponent(
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
                },
                {
                    label: 'Minus -',
                    token: '-'
                },
                {
                    label: 'Multiply *',
                    token: '*'
                },
                {
                    label: 'Divide /',
                    token: '/'
                },
                {
                    label: 'CurvedBraceStart (',
                    token: '('
                },
                {
                    label: 'CurvedBraceEnd )',
                    token: ')'
                }
            ];
            var FORMULA_FUNCTIONS = [{
                    label: 'Ceiling',
                    token: 'CEILING('
                },
                {
                    label: 'Floor',
                    token: 'FLOOR('
                }
            ];

            function formulaOptionsHtml(list) {
                return list.map(function(o) {
                    return '<option value="' + o.token + '">' + o.label + '</option>';
                }).join('');
            }

            function formulaBuilderHtml() {
                return '<div class="ctr-formula-builder">' +
                    '<div class="ctr-formula-cols">' +
                    '<div class="ctr-formula-col">' +
                    '<label class="ctr-lbl">Select Formula</label>' +
                    '<select multiple size="6" class="form-control form-control-sm ctr-fb-fields">' +
                    formulaOptionsHtml(FORMULA_FIELDS) + '</select>' +
                    '<button type="button" class="btn btn-xs btn-outline-primary mt-1 ctr-fb-add-field">Add</button>' +
                    '</div>' +
                    '<div class="ctr-formula-col">' +
                    '<label class="ctr-lbl">Select Operator</label>' +
                    '<select multiple size="6" class="form-control form-control-sm ctr-fb-operators">' +
                    formulaOptionsHtml(FORMULA_OPERATORS) + '</select>' +
                    '<button type="button" class="btn btn-xs btn-outline-primary mt-1 ctr-fb-add-operator">Add</button>' +
                    '</div>' +
                    '<div class="ctr-formula-col">' +
                    '<label class="ctr-lbl">Select Function</label>' +
                    '<select multiple size="6" class="form-control form-control-sm ctr-fb-functions">' +
                    formulaOptionsHtml(FORMULA_FUNCTIONS) + '</select>' +
                    '<button type="button" class="btn btn-xs btn-outline-primary mt-1 ctr-fb-add-function">Add</button>' +
                    '</div>' +
                    '</div>' +
                    '<div class="ctr-field ctr-field-wide mt-2">' +
                    '<div class="d-flex align-items-center">' +
                    '<label class="ctr-lbl ctr-required mb-0">Formula</label>' +
                    '<a href="#" class="ctr-fb-reset ml-auto">Reset</a>' +
                    '</div>' +
                    '<textarea class="form-control form-control-sm ctr-cf-formula" rows="2" ' +
                    'placeholder="e.g. [ChargeableWeight]+([AssessableValue]*0.25)"></textarea>' +
                    '</div>' +
                    '<div class="ctr-field mt-2" style="max-width:220px;">' +
                    '<label class="ctr-lbl">Rate Type</label>' +
                    '<select class="form-control form-control-sm ctr-fb-ratetype">' +
                    '<option value="Single">Single</option><option value="Slab Based">Slab Based</option>' +
                    '</select></div>' +
                    '<div class="ctr-fb-ratearea mt-2"></div>' +
                    '</div>';
            }

            function inlineRateHtml(rateType) {
                if (rateType === 'Single') {
                    return '<label class="ctr-lbl ctr-required">Rate</label>' +
                        '<input type="number" step="0.00001" class="form-control form-control-sm ctr-cf-rate">';
                }
                return '';
            }

            function rateAreaHtml(rateType) {
                if (rateType === 'Slab Based' || rateType === 'Step Up') {
                    return '<div class="ctr-slab-wrap">' +
                        '<table class="ctr-slab-table"><thead><tr><th>Up To</th><th>Value</th><th></th></tr></thead>' +
                        '<tbody class="ctr-slab-rows">' +
                        slabRowHtml(true) +
                        '</tbody></table>' +
                        '<button type="button" class="btn btn-xs btn-outline-primary ctr-slab-add-row mt-1">+ Add Slab</button>' +
                        '</div>';
                }
                if (rateType === 'Formula') {
                    return formulaBuilderHtml();
                }
                return '';
            }

            function slabRowHtml(isAbove) {
                return '<tr' + (isAbove ? ' class="ctr-slab-above-row"' : '') + '>' +
                    '<td>' + (isAbove ?
                        '<span class="ctr-slab-above-label">Above</span>' :
                        '<input type="number" class="form-control form-control-sm ctr-slab-upto" placeholder="e.g. 200">'
                    ) + '</td>' +
                    '<td><input type="number" step="0.01" class="form-control form-control-sm ctr-slab-value"></td>' +
                    '<td>' + (isAbove ? '' : '<span class="ctr-slab-row-remove">&times;</span>') + '</td>' +
                    '</tr>';
            }

            function addSlabRow(wrap) {
                var tbody = wrap.querySelector('.ctr-slab-rows');
                var lastRow = tbody.querySelector('.ctr-slab-above-row');
                if (lastRow) {
                    var value = lastRow.querySelector('.ctr-slab-value').value;
                    var normalRow = document.createElement('tr');
                    normalRow.innerHTML =
                        '<td><input type="number" class="form-control form-control-sm ctr-slab-upto" placeholder="e.g. 500"></td>' +
                        '<td><input type="number" step="0.01" class="form-control form-control-sm ctr-slab-value" value="' +
                        value + '"></td>' +
                        '<td><span class="ctr-slab-row-remove">&times;</span></td>';
                    lastRow.replaceWith(normalRow);
                }
                var newRow = document.createElement('tr');
                newRow.className = 'ctr-slab-above-row';
                newRow.innerHTML =
                    '<td><span class="ctr-slab-above-label">Above</span></td>' +
                    '<td><input type="number" step="0.01" class="form-control form-control-sm ctr-slab-value"></td>' +
                    '<td></td>';
                tbody.appendChild(newRow);
            }

            function renderFormulaRateArea(wrap, rateType) {
                var area = wrap.querySelector('.ctr-fb-ratearea');
                if (rateType === 'Slab Based') {
                    area.innerHTML = '<div class="ctr-slab-wrap">' +
                        '<table class="ctr-slab-table"><thead><tr><th>Up To</th><th>Value</th><th></th></tr></thead>' +
                        '<tbody class="ctr-slab-rows">' + slabRowHtml(true) + '</tbody></table>' +
                        '<button type="button" class="btn btn-xs btn-outline-primary ctr-slab-add-row mt-1">+ Add Slab</button>' +
                        '</div>';
                } else {
                    area.innerHTML = '<div class="ctr-field" style="max-width:200px;">' +
                        '<label class="ctr-lbl ctr-required">Rate</label>' +
                        '<input type="number" step="0.00001" class="form-control form-control-sm ctr-formula-rate"></div>';
                    clampDecimals(area.querySelector('.ctr-formula-rate'), 5);
                }
            }

            function bindChargeFormEvents(svcId, wrap, chargeToEdit) {
                clampDecimals(wrap.querySelector('.ctr-cf-min'), 2);
                clampDecimals(wrap.querySelector('.ctr-cf-max'), 2);

                $(wrap).find('.ctr-cf-ratetype').on('change', function() {
                    $(wrap).find('.ctr-cf-inline-rate').html(inlineRateHtml(this.value));
                    $(wrap).find('.ctr-cf-ratearea').html(rateAreaHtml(this.value));
                    clampDecimals(wrap.querySelector('.ctr-cf-rate'), 5);
                    if (this.value !== 'Formula') return;
                    var svc = findService(svcId);
                    var isRevenue = ($(wrap).find('.ctr-cf-nature').val() === 'Cost') ? 0 : 1;
                    if (svc && svc.serviceFilterId) {
                        loadFormulaFields(svc.serviceFilterId, isRevenue).done(function(res) {
                            if (res.success) FORMULA_FIELDS = res.data.map(function(name) {
                                return {
                                    label: name,
                                    token: name
                                };
                            });
                            bindFormulaBuilderEvents(wrap);
                        });
                    } else {
                        bindFormulaBuilderEvents(wrap);
                    }
                });

                $(wrap).find('.ctr-cf-cancel').on('click', function() {
                    wrap.style.display = 'none';
                    wrap.innerHTML = '';
                });

                $(wrap).find('.ctr-cf-basedon').on('change', function() {
                    var basedOnCode = $(this).val();
                    var $uomSelect = $(wrap).find('.ctr-cf-uom');

                    if (!basedOnCode) {
                        $uomSelect.empty().append('<option value="">Select Based On first</option>').prop(
                            'disabled', true);
                        return;
                    }

                    // Fetch UOM options for the selected Based On
                    fetchMasterList('based-on-uom', {
                        based_on_code: basedOnCode
                    }).done(function(res) {
                        console.log('UOM response:', res);

                        if (res.success) {
                            var options = res.uom_list.map(function(u) {
                                return {
                                    Code: u.Code,
                                    Label: u.Name
                                };
                            });
                            populateSelect($uomSelect, options, 'Code', 'Label');
                            $uomSelect.prop('disabled', false);
                        } else {
                            $uomSelect.empty().append('<option value="">No UOM available</option>')
                                .prop('disabled', true);
                        }
                    });
                });

                // ── Toggle Accruable/Reimbursable based on Charge Nature ──────────
                function toggleAccruableReimbursable() {
                    var nature = $(wrap).find('.ctr-cf-nature').val();
                    if (nature === 'Revenue') {
                        $(wrap).find('#ctr-is-accruable-container').show();
                        $(wrap).find('#ctr-is-reimbursable-container').hide();
                        $(wrap).find('#ctrIsReimbursable').prop('checked', false);
                        // Restore previous accruable value if editing
                        if (chargeToEdit && chargeToEdit.toBeAdded) {
                            $(wrap).find('#ctrIsAccruable').prop('checked', true);
                        }
                    } else { // Cost
                        $(wrap).find('#ctr-is-accruable-container').hide();
                        $(wrap).find('#ctr-is-reimbursable-container').show();
                        $(wrap).find('#ctrIsAccruable').prop('checked', false);
                        // Restore previous reimbursable value if editing
                        if (chargeToEdit && chargeToEdit.toBeAdded) {
                            $(wrap).find('#ctrIsReimbursable').prop('checked', true);
                        }
                    }
                }

                // Initial toggle on form load
                toggleAccruableReimbursable();

                // ── Refresh charge codes when nature changes ──────────
                $(wrap).find('.ctr-cf-nature').on('change', function() {
                    var nature = $(this).val();
                    var chargeType = (nature === 'Cost') ? 'Cost' : 'Revenue';

                    fetchMasterList('charge-codes', {
                        office_code: OFFICE_CODE,
                        charge_type: chargeType,
                        filter: ''
                    }).done(function(res) {
                        if (res.success) {
                            var options = res.charge_codes.map(function(c) {
                                return {
                                    Code: c.Code,
                                    Label: c.Code + ' - ' + (c.Description || c.Name || c.Code)
                                };
                            });
                            var $select = $(wrap).find('.ctr-cf-code');
                            populateSelect($select, options, 'Code', 'Label');
                            // Preserve selected value if it exists in the new list
                            var currentVal = $select.val();
                            if (currentVal && !$select.find('option[value="' + currentVal + '"]')
                                .length) {
                                $select.val('');
                            }
                            // ✅ ADD THIS LINE
                            $select.trigger('change');
                        }
                    });
                });

                // Re-toggle when Charge Nature changes
                $(wrap).find('.ctr-cf-nature').on('change', function() {
                    toggleAccruableReimbursable();
                });

                // ── Auto‑fill description and currency on code selection ──
                $(wrap).find('.ctr-cf-code').on('change', function() {
                    var $selected = $(this).find('option:selected');
                    var desc = $selected.data('description') || '';
                    var curr = $selected.data('currency') || '';
                    if (desc) {
                        $(wrap).find('.ctr-cf-desc').val(desc);
                    }
                    if (curr) {
                        var $currencySelect = $(wrap).find('.ctr-cf-currency');
                        // Only set if the currency exists in the dropdown
                        if ($currencySelect.find('option[value="' + curr + '"]').length) {
                            $currencySelect.val(curr);
                        }
                    }
                });

                $(wrap).find('.ctr-cf-save').on('click', function() {
                    saveCharge(svcId, wrap, chargeToEdit);
                });
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

            function saveCharge(svcId, wrap, chargeToEdit) {
                var svc = findService(svcId);

                if (!svc.serviceFilterId) {
                    alert('This service has not finished saving yet. Please wait and try again.');
                    return;
                }

                var rateType = wrap.querySelector('.ctr-cf-ratetype').value;
                var required = {
                    category: wrap.querySelector('.ctr-cf-category').value,
                    code: wrap.querySelector('.ctr-cf-code').value.trim(),
                    desc: wrap.querySelector('.ctr-cf-desc').value.trim(),
                    basedOn: wrap.querySelector('.ctr-cf-basedon').value,
                    rateType: rateType
                };
                // ── Read Reimbursable/Accrual status ──────────────────────────────
                var isAccruable = wrap.querySelector('#ctrIsAccruable')?.checked || false;
                var isReimbursable = wrap.querySelector('#ctrIsReimbursable')?.checked || false;
                var toBeAdded = (isAccruable || isReimbursable) ? 1 : 0;
                var missing = Object.keys(required).filter(function(k) {
                    return !required[k];
                });
                if (missing.length) {
                    alert('Please fill required Charge fields: ' + missing.join(', '));
                    return;
                }

                var charge = {
                    chargeNature: wrap.querySelector('.ctr-cf-nature').value,
                    chargeCategory: required.category,
                    chargeCode: required.code,
                    chargeDescription: required.desc,
                    chargeBasedOn: required.basedOn,
                    chargeBasedOnUOM: wrap.querySelector('.ctr-cf-uom').value.trim(),
                    chargeCurr: wrap.querySelector('.ctr-cf-currency').value,
                    toBeAdded: toBeAdded,
                    minAmount: wrap.querySelector('.ctr-cf-min').value !== '' ? parseFloat(parseFloat(wrap
                        .querySelector('.ctr-cf-min').value).toFixed(2)) : null,
                    maxAmount: wrap.querySelector('.ctr-cf-max').value !== '' ? parseFloat(parseFloat(wrap
                        .querySelector('.ctr-cf-max').value).toFixed(2)) : null,
                    rateType: rateType,
                    rate: null,
                    rateSlab: null,
                    formula: null,
                    formulaRateType: null,
                    formulaRate: null,
                    formulaSlab: null
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
                    var formulaRateType = wrap.querySelector('.ctr-fb-ratetype').value;
                    charge.formulaRateType = formulaRateType;
                    if (formulaRateType === 'Slab Based') {
                        var fRows = wrap.querySelectorAll('.ctr-fb-ratearea .ctr-slab-rows tr');
                        var fSlabs = [];
                        var fValid = true;
                        fRows.forEach(function(row) {
                            var isAbove = row.classList.contains('ctr-slab-above-row');
                            var value = row.querySelector('.ctr-slab-value').value;
                            if (isAbove) {
                                if (value === '') {
                                    fValid = false;
                                    return;
                                }
                                fSlabs.push({
                                    upto: null,
                                    value: parseFloat(value)
                                });
                            } else {
                                var upto = row.querySelector('.ctr-slab-upto').value;
                                if (upto === '' || value === '') {
                                    fValid = false;
                                    return;
                                }
                                fSlabs.push({
                                    upto: parseFloat(upto),
                                    value: parseFloat(value)
                                });
                            }
                        });
                        if (!fValid || !fSlabs.length) {
                            alert('Please fill all Formula slab rows.');
                            return;
                        }
                        charge.formulaSlab = fSlabs;
                    } else {
                        var fRateVal = wrap.querySelector('.ctr-formula-rate').value;
                        if (fRateVal === '') {
                            alert('Rate is required for the Formula rate type.');
                            return;
                        }
                        charge.formulaRate = parseFloat(parseFloat(fRateVal).toFixed(5));
                    }
                }

                var isEdit = !!chargeToEdit;
                if (isEdit && !chargeToEdit.chargeId) {
                    alert('This charge cannot be edited yet — please refresh and try again.');
                    return;
                }

                var saveBtn = wrap.querySelector('.ctr-cf-save');
                var originalText = saveBtn.textContent;
                saveBtn.disabled = true;
                saveBtn.textContent = isEdit ? 'Updating...' : 'Saving...';

                var baseUrl = '/contracts/' + encodeURIComponent(CONTRACT_ID) +
                    '/services/' + encodeURIComponent(svc.serviceFilterId) + '/charges';
                var chargeUrl = isEdit ? baseUrl + '/' + encodeURIComponent(chargeToEdit.chargeId) : baseUrl;

                $.ajax({
                    url: chargeUrl,
                    type: isEdit ? 'PUT' : 'POST',
                    data: $.extend({
                        _token: document.querySelector('meta[name="csrf-token"]').content
                    }, charge),
                    dataType: 'json'
                }).done(function(res) {
                    if (!res.success) {
                        alert('Failed to save charge: ' + (res.message || 'Unknown error'));
                        return;
                    }
                    if (isEdit) {
                        charge.id = chargeToEdit.id;
                        charge.chargeId = chargeToEdit.chargeId;
                        var idx = svc.charges.findIndex(function(c) {
                            return c.id === chargeToEdit.id;
                        });
                        if (idx !== -1) svc.charges[idx] = charge;
                    } else {
                        chargeSeq += 1;
                        charge.id = 'chg_' + chargeSeq;
                        charge.chargeId = res.data && res.data.charge_id;
                        svc.charges.push(charge);
                    }
                    wrap.style.display = 'none';
                    wrap.innerHTML = '';
                    renderCharges(svc);
                    saveLocalDraft();
                }).fail(function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Save failed.';
                    alert('Failed to save charge: ' + msg);
                }).always(function() {
                    saveBtn.disabled = false;
                    saveBtn.textContent = originalText;
                });
            }

            // ── Header save ──────────────────────────────────────────────────────
            function collectHeaderPayload() {
                return {
                    contract_type: document.getElementById('ctrContractType').value,
                    contract_name: document.getElementById('ctrContractName').value.trim(),
                    status: document.getElementById('ctrStatus').value,
                    effective_from: document.getElementById('ctrEffectiveFrom').value,
                    effective_to: document.getElementById('ctrEffectiveTo').value,
                    remark: document.getElementById('ctrRemark').value.trim()
                };
            }

            function validateHeader(payload) {
                return ['contract_type', 'contract_name', 'effective_from', 'effective_to']
                    .filter(function(k) {
                        return !payload[k];
                    });
            }

            function validateServicesHaveCharges() {
                return state.contract.services.filter(function(svc) {
                    return !svc.charges.length;
                });
            }

            function saveDraft(btn, requireServices, onSuccess) {
                var payload = collectHeaderPayload();
                var missing = validateHeader(payload);
                if (missing.length) {
                    alert('Please fill required Contract fields: ' + missing.join(', '));
                    return;
                }
                if (requireServices && !state.contract.services.length) {
                    alert('Add at least one Service before saving.');
                    return;
                }

                var formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                Object.keys(payload).forEach(function(k) {
                    formData.append(k, payload[k]);
                });
                formData.append('services', JSON.stringify(state.contract.services));

                var originalText = btn.textContent;
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
                        return r.json().then(function(body) {
                            return {
                                ok: r.ok,
                                status: r.status,
                                body: body
                            };
                        });
                    })
                    .then(function(result) {
                        if (!result.ok || !result.body.success) {
                            var msg = result.body.message || 'Save failed';
                            if (result.body.errors) {
                                var details = Object.values(result.body.errors).flat().join('\n');
                                msg += '\n\n' + details;
                            }
                            throw new Error(msg);
                        }
                        alert(result.body.message || 'Saved.');
                        if (onSuccess) onSuccess();
                    })
                    .catch(function(err) {
                        console.error('[Contract] save failed:', err);
                        alert('Failed to save: ' + err.message);
                    })
                    .finally(function() {
                        btn.disabled = false;
                        btn.textContent = originalText;
                    });
            }

            document.getElementById('ctrSaveHeaderBtn').addEventListener('click', function() {
                var btn = this;
                saveDraft(btn, false, function() {
                    state.contractSaved = true;

                    // Update the URL so a page refresh reloads this same draft instead
                    // of generating a brand-new session slot.
                    var url = new URL(window.location.href);
                    url.searchParams.set('draft', CONTRACT_ID);
                    window.history.replaceState({}, '', url);
                    saveLocalDraft();

                    $('#ctrContractInfoModal').modal('hide');
                    $('#ctrServicesModal').modal('show');
                    openServiceForm();
                });
            });

            document.getElementById('ctrDoneBtn').addEventListener('click', function() {
                if (!state.contract.services.length) {
                    alert('Add at least one Service before finishing.');
                    return;
                }

                var servicesMissingCharges = validateServicesHaveCharges();
                if (servicesMissingCharges.length) {
                    var names = servicesMissingCharges.map(function(s) {
                        return s.service + ' — ' + s.process;
                    }).join(', ');
                    alert('Every service must have at least one Charge before finishing. Missing charges for: ' +
                        names);
                    return;
                }

                clearLocalDraft();
                $('#ctrServicesModal').modal('hide');
                showSection('ctrListSection');
                loadContractsList();
            });

            $('#ctrContractType, #ctrContractName, #ctrEffectiveFrom, #ctrEffectiveTo, #ctrRemark')
                .on('change input', saveLocalDraft);

            $('#ctrContractInfoModal .close, #ctrServicesModal .close').on('click', function() {
                $(this).closest('.modal').modal('hide');
            });

            // ── Click delegation for service/charge actions ──────────────────────
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
                    saveLocalDraft();
                    return;
                }

                var addChargeBtn = e.target.closest('.ctr-add-charge-btn');
                if (addChargeBtn) {
                    openChargeForm(addChargeBtn.getAttribute('data-svc'));
                    return;
                }

                var removeChargeBtn = e.target.closest('.ctr-remove-charge');
                var editChargeBtn = e.target.closest('.ctr-edit-charge');
                if (editChargeBtn) {
                    var svc2 = findService(editChargeBtn.getAttribute('data-svc'));
                    var chargeToEdit = svc2.charges.filter(function(c) {
                        return c.id === editChargeBtn.getAttribute('data-charge');
                    })[0];
                    if (!chargeToEdit || !chargeToEdit.chargeId) {
                        alert('This charge cannot be edited yet — please refresh and try again.');
                        return;
                    }
                    openChargeForm(svc2.id, chargeToEdit);
                    return;
                }
                if (removeChargeBtn) {
                    var svc = findService(removeChargeBtn.getAttribute('data-svc'));
                    var chargeId = removeChargeBtn.getAttribute('data-charge');
                    svc.charges = svc.charges.filter(function(c) {
                        return c.id !== chargeId;
                    });
                    renderCharges(svc);
                    saveLocalDraft();
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

            // ── View Contract Click Handler ────────────────────────────────
            $(document).on('click', '.ctr-view-contract', function() {
                var contractId = $(this).data('id');
                if (contractId) {
                    window.location.href = '/contracts/' + contractId + '/view';
                }
            });

            $(document).on('click', '.ctr-edit-contract', function() {
                var contractId = $(this).data('id');
                if (!contractId) return;
                window.location.href = '/contracts/' + encodeURIComponent(contractId) + '/edit';
            });

            $(document).on('click', '.ctr-stat-card', function() {
                state.activeStatusFilter = $(this).data('status') || '';
                applyStatusFilterAndRender();
            });

            // ── Hydrate from existing draft ──────────────────────────────────────
            var localDraft = loadLocalDraft();

            if (existing) {
                document.getElementById('ctrContractNo').value = existing.contract_no || '';
                document.getElementById('ctrContractType').value = existing.contract_type || '';
                document.getElementById('ctrContractName').value = existing.contract_name || '';
                document.getElementById('ctrStatus').value = existing.status || 'Draft';
                document.getElementById('ctrEffectiveFrom').value = existing.effective_from || '';
                document.getElementById('ctrEffectiveTo').value = existing.effective_to || '';
                document.getElementById('ctrRemark').value = existing.remark || '';
                state.contract.services = existing.services || [];
                serviceSeq = state.contract.services.length;
                document.getElementById('ctrAddServiceBtn').disabled = false;
            }

            if (localDraft) {
                document.getElementById('ctrContractType').value = localDraft.contractType || '';
                document.getElementById('ctrContractName').value = localDraft.contractName || '';
                document.getElementById('ctrEffectiveFrom').value = localDraft.effectiveFrom || '';
                document.getElementById('ctrEffectiveTo').value = localDraft.effectiveTo || '';
                document.getElementById('ctrRemark').value = localDraft.remark || '';
                state.contract.services = localDraft.services || [];
                state.contractSaved = !!localDraft.contractSaved;
                serviceSeq = state.contract.services.length;
                if (state.contractSaved) {
                    document.getElementById('ctrAddServiceBtn').disabled = false;
                    document.getElementById('ctrAddServiceBtn').removeAttribute('title');
                }
            }

            $('#ctrSvcService').on('change', function() {
                var serviceCode = this.value;
                if (!serviceCode) return;
                fetchMasterList('processes', {
                    service: serviceCode
                }).done(function(res) {
                    if (res.success) populateSelect($('#ctrSvcProcess'), res.data, 'Code', 'Name');
                });
            });
            // ── Initial render ─────────────────────────────────────────────────────
            renderServices();
            // Pre‑populate service form dropdowns in background (if not already)
            populateServiceFormMasters();

            // Always land on the list first. Any resumed draft/session data has
            // already hydrated the Contract Info fields above — it's just sitting
            // there ready the moment "+ New" is clicked, but the user never
            // auto-jumps past the landing page just because a stale draft exists.
            showSection('ctrListSection');
            loadContractsList();

        })();
    </script>
@stop
