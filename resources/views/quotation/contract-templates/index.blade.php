@extends('adminlte::page')

@section('title', 'Contract')

@section('content_header')
    <h1 class="h3 mb-0">Contract</h1>
@stop

@section('content')
    <div class="container-fluid" id="contractTemplateContent">

        {{-- Added based on stakeholder meeting requirement --}}
        {{-- STEP 2: Contract selection by Name / Description (dropdown), not just Contract Number --}}
        <div class="cc-box cc-box-compact cc-box-tight">
            <div class="cc-info-row">
                <span class="cc-lbl cc-lbl-required" style="min-width:160px;">* Select Contract</span>
                <div class="cc-input-group" style="flex:1; gap:8px;">
                    <select class="cc-input cc-input-highlight" id="ctContractSelect" style="width:100%; height:28px;">
                        <option value="">-- Select Contract --</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- STEP 1 & 3: Combined Contract + Party & Route info card --}}
        <div class="cc-box cc-box-compact cc-box-tight" id="ctContractInfoBox" style="display:none;">
            <div class="cc-box-title cc-collapsible-title collapsed" id="ctContractInfoToggle">
                <span class="cc-caret">&#9660;</span> Contract Information
            </div>
            <div class="cc-info-grid cc-info-grid-compact collapsed" id="ctContractInfoBody">
                <div class="cc-info-col">
                    <div class="cc-info-row"><span class="cc-lbl">Contract Number</span><span class="cc-val"
                            id="ctfContractNumber">-</span></div>
                    <div class="cc-info-row"><span class="cc-lbl">Contract Name</span><span class="cc-val"
                            id="ctfContractName">-</span></div>
                    <div class="cc-info-row"><span class="cc-lbl">Valid From</span><span class="cc-val"
                            id="ctfValidFrom">-</span></div>
                    <div class="cc-info-row"><span class="cc-lbl">Valid To</span><span class="cc-val"
                            id="ctfValidTo">-</span></div>
                </div>
                <div class="cc-info-col">
                    <div class="cc-info-row"><span class="cc-lbl">Client</span><span class="cc-val" id="ctfClient">-</span>
                    </div>
                    <div class="cc-info-row"><span class="cc-lbl">Shipper</span><span class="cc-val"
                            id="ctfShipper">-</span></div>
                    <div class="cc-info-row"><span class="cc-lbl">Consignee</span><span class="cc-val"
                            id="ctfConsignee">-</span></div>
                </div>
                <div class="cc-info-col">
                    <div class="cc-info-row"><span class="cc-lbl">Port of Loading</span><span class="cc-val"
                            id="ctfPol">-</span></div>
                    <div class="cc-info-row"><span class="cc-lbl">Port of Discharge</span><span class="cc-val"
                            id="ctfPod">-</span></div>
                </div>
            </div>
        </div>

        {{-- STEP 4: Shipment information (drives quantity auto-derivation in STEP 10) --}}
        <div class="cc-box cc-box-compact cc-box-tight" id="ctShipmentBox" style="display:none;">
            <div class="cc-box-title cc-collapsible-title collapsed" id="ctShipmentInfoToggle">
                <span class="cc-caret">&#9660;</span> Shipment Information
            </div>
            <div class="cc-info-grid cc-info-grid-compact collapsed" id="ctShipmentInfoBody">
                <div class="cc-info-col">
                    <div class="cc-info-row"><span class="cc-lbl">Weight</span><span class="cc-val" id="ctfWeight">-</span>
                    </div>
                    <div class="cc-info-row"><span class="cc-lbl">Volume</span><span class="cc-val" id="ctfVolume">-</span>
                    </div>
                </div>
                <div class="cc-info-col">
                    <div class="cc-info-row"><span class="cc-lbl">Container</span><span class="cc-val"
                            id="ctfContainer">-</span></div>
                    <div class="cc-info-row"><span class="cc-lbl">Package Count</span><span class="cc-val"
                            id="ctfPackageCount">-</span></div>
                </div>
                <div class="cc-info-col">
                    <div class="cc-info-row"><span class="cc-lbl">Shipment Type</span><span class="cc-val"
                            id="ctfShipmentType">-</span></div>
                    <div class="cc-info-row"><span class="cc-lbl">Base Currency</span><span class="cc-val"
                            id="ctfBaseCurrency">-</span></div>
                </div>
            </div>
        </div>

        {{-- STEP 5 / 6 / 7: Revenue Charges — collapsible sections --}}
        <div id="ctChargesArea" style="display:none;">

            <div class="cc-section-block mb-3">
                <div class="cc-section-block-header cc-header-revenue">
                    <strong>Charges</strong>
                    <button type="button" class="btn btn-sm btn-outline-secondary ml-2"
                        id="ctReloadChargesBtn">Reload</button>
                    <span class="ml-auto cc-totals-inline">
                        Total: <span id="ctRevenueTotalDisplay">0.00</span> <span id="ctBaseCurrencyLabelRev"></span>
                    </span>
                </div>
                <div id="ctRevenueGroups"></div>
            </div>

            {{-- STEP 14: Margin summary --}}
            {{-- <div class="cc-box mb-3">
                <div class="cc-info-grid">
                    <div class="cc-info-col">
                        <div class="cc-info-row"><span class="cc-lbl">Revenue Total</span><span class="cc-val"
                                id="ctSummaryRevenue">0.00</span></div>
                    </div>
                    <div class="cc-info-col">
                        <div class="cc-info-row"><span class="cc-lbl">Cost Total</span><span class="cc-val"
                                id="ctSummaryCost">0.00</span></div>
                    </div>
                    <div class="cc-info-col">
                        <div class="cc-info-row"><span class="cc-lbl">Margin</span><span class="cc-val"
                                id="ctSummaryMargin">0.00</span></div>
                    </div>
                </div>
            </div> --}}

            {{-- STEP 16: Save Contract — only selected charges are persisted into the mock contract object --}}
            <div class="d-flex justify-content-end mb-4">
                <button type="button" class="btn btn-success" id="ctSaveContractBtn">Save Contract</button>
            </div>
        </div>
        {{-- End: Added based on stakeholder meeting requirement --}}

        <script>
            (function() {

                var shipmentContext = @json($shipment ?? []);
                var quotationId = @json($quotationId ?? '');

                // ============================================================
                // Added based on stakeholder meeting requirement
                // MOCK DATA LAYER — this whole block is the only thing that
                // needs to be replaced once backend endpoints exist.
                // Shape mirrors what the controller/service layer would return.
                // ============================================================
                var CHARGE_GROUP_ORDER = [
                    'General / Port Charges',
                    'Freight / Carrier Related Charges',
                    'Dangerous Goods Charges',
                    'Delivery / Pickup Related Charges',
                    'Documentation Charges',
                    'Customs Charges',
                    'Agency Charges',
                    'Product Charges'
                ];

                var state = {
                    contract: null
                };

                // Read-only mode: render values as plain text, hide the
                // checkbox column, and hide the Save / Reload controls.
                var READ_ONLY = true;

                if (READ_ONLY) {
                    // Scope read-only styling to just this injected content —
                    // doesn't leak out to the rest of the Quotation Edit page.
                    var __root = document.getElementById('contractTemplateContent');
                    if (__root) __root.classList.add('ct-readonly');
                }
                // Shipment context passed from the Quotation Edit page's "Load Contract Template" link
                var officeId = shipmentContext.officeId || '';
                var currencyOptions = [];
                // Template already linked to this quotation (set previously via store())
                var existingLink = @json($existing ?? null);

                // ------------------------------------------------------------
                // STEP 2: Contract Name / Description dropdown selector
                // Populated from sp_Contract_List (List mode) via ContractTemplateController::listContracts
                // ------------------------------------------------------------
                var contractSelect = document.getElementById('ctContractSelect');
                var contractsById = {};

                function appendContractOption(c) {
                    if (!contractSelect || !c || !c.id) {
                        return;
                    }

                    // Prevent duplicate options.
                    if (contractsById[c.id]) {
                        return;
                    }

                    contractsById[c.id] = c;

                    var opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.contractName + ' (' + c.contractNo + ')';

                    contractSelect.appendChild(opt);
                }

                function loadContractOptions() {
                    if (!contractSelect) {
                        console.error('[Contract] Contract select was not found.');
                        return;
                    }

                    var contracts = window.contractTemplateList || [];

                    if (contracts.length) {
                        contracts.forEach(appendContractOption);
                        tryAutoSelectExistingLink(); // ← NEW
                        return;
                    }

                    if (typeof window.loadContractTemplateList === 'function') {
                        window.loadContractTemplateList().then(function(result) {
                            if (result.success) {
                                result.data.forEach(appendContractOption);
                                tryAutoSelectExistingLink(); // ← NEW
                            } else {
                                showNoServiceMessage(result.message || 'Contract list unavailable.');
                            }
                        });
                        return;
                    }

                    console.error('[Contract] Contract template list is not available.');
                }

                function showNoServiceMessage(text) {
                    contractSelect.disabled = true;
                    if (contractSelect.nextElementSibling && contractSelect.nextElementSibling.classList.contains(
                            'ctc-no-service-msg')) {
                        return;
                    }
                    var msg = document.createElement('div');
                    msg.className = 'ctc-no-service-msg text-muted small mt-1';
                    msg.textContent = text;
                    contractSelect.insertAdjacentElement('afterend', msg);
                }

                // If the current quotation already has a contract linked, auto-select it
                // once the dropdown options are available. Keyed defensively because the
                // shape of $existing may vary (camelCase, PascalCase, or 'contract_id').
                function tryAutoSelectExistingLink() {
                    if (!existingLink) return;

                    var contractId =
                        existingLink.contractId ||
                        existingLink.ContractID ||
                        existingLink.contract_id ||
                        existingLink.id;

                    if (!contractId) return;

                    // The link references a contract that isn't in the current dropdown —
                    // likely deleted or outside the current office scope. Log and leave the
                    // dropdown on the placeholder so the user sees a clean slate.
                    if (!contractsById[contractId]) {
                        console.warn('[Contract] Linked contract not found in list:', contractId);
                        return;
                    }

                    // Route through the exact same path a manual selection takes, so
                    // info-box population, charge fetch, and totals all fire identically.
                    contractSelect.value = contractId;

                    // Bypass the debounce guard — this is a programmatic single-fire, not a
                    // user double-tap, and we want the fetch to run immediately.
                    __lastSelectedContractId = contractId;
                    __contractChangeGuard = Date.now();

                    selectContract(contractId);
                }

                loadContractOptions();

                // ------------------------------------------------------------
                // The Workbook's modal header (including its X) lives on the parent
                // page. Bind a delegated close handler from here so clicking X always
                // closes the whole modal, even if Bootstrap's data-dismiss wiring
                // didn't attach for some reason.
                // ------------------------------------------------------------
                document.addEventListener('click', function(e) {
                    var closeBtn = e.target.closest(
                        '#contractTemplateModal .modal-header .close, ' +
                        '#contractTemplateModal [data-dismiss="modal"]'
                    );
                    if (!closeBtn) return;

                    e.preventDefault();
                    var modalEl = document.getElementById('contractTemplateModal');
                    if (window.jQuery && modalEl) {
                        window.jQuery(modalEl).modal('hide');
                    }
                });

                if (officeId) {
                    fetch('{{ route('quotation.currencies') }}?office_id=' + encodeURIComponent(officeId))
                        .then(function(r) {
                            return r.json();
                        })
                        .then(function(res) {
                            if (!res.success) {
                                console.error('[Currency] load failed:', res.message);
                                return;
                            }

                            currencyOptions = res.currencies || [];
                        })
                        .catch(function(err) {
                            console.error('[Currency] fetch failed:', err);
                        });
                } else {
                    console.warn('[Currency] No office ID available for quotation:', '{{ $quotationId }}');
                }
                // ------------------------------------------------------------
                // Single source of truth for contract-change handling.
                //
                // A plain <select> fires 'change' once. select2 fires BOTH 'change'
                // AND 'select2:select' — so binding both directly caused two fetches
                // per switch. Dedupe by ignoring any repeat of the same id within
                // a short window (covers the select2 double-fire).
                // ------------------------------------------------------------
                var __lastSelectedContractId = null;
                var __contractChangeGuard = 0;

                function onContractChange(value) {
                    if (!value) return;

                    var now = Date.now();
                    if (
                        value === __lastSelectedContractId &&
                        now - __contractChangeGuard < 300
                    ) {
                        return;
                    }

                    __lastSelectedContractId = value;
                    __contractChangeGuard = now;
                    selectContract(value);
                }

                if (window.jQuery) {
                    window.jQuery('#ctContractSelect').on('change select2:select', function() {
                        onContractChange(this.value);
                    });
                } else {
                    // Fallback when jQuery isn't available for some reason.
                    contractSelect.addEventListener('change', function() {
                        onContractChange(contractSelect.value);
                    });
                }

                var contractInfoToggle = document.getElementById('ctContractInfoToggle');
                if (contractInfoToggle) {
                    contractInfoToggle.addEventListener('click', function() {
                        contractInfoToggle.classList.toggle('collapsed');
                        document.getElementById('ctContractInfoBody').classList.toggle('collapsed');
                    });
                }

                var shipmentInfoToggle = document.getElementById('ctShipmentInfoToggle');
                if (shipmentInfoToggle) {
                    shipmentInfoToggle.addEventListener('click', function() {
                        shipmentInfoToggle.classList.toggle('collapsed');
                        document.getElementById('ctShipmentInfoBody').classList.toggle('collapsed');
                    });
                }

                function selectContract(id) {
                    try {
                        doSelectContract(id);
                    } catch (err) {
                        console.error('[Contract] selectContract failed:', err);
                    }
                }

                // function doSelectContract(id) {
                //     var contract = contractsById[id];
                //     if (!contract) return;

                //     contractSelect.value = contract.id;

                //     document.getElementById('ctContractInfoBox').classList.add('is-visible');

                //     // Only fields sp_Contract_List (List mode) returns.
                //     // Shipment Info, party/port fields, and Charges need a details
                //     // endpoint that doesn't exist yet — left untouched for now.
                //     document.getElementById('ctfContractNumber').textContent = contract.contractNo;
                //     document.getElementById('ctfContractName').textContent = contract.contractName;
                //     document.getElementById('ctfValidFrom').textContent = contract.effectiveFrom;
                //     document.getElementById('ctfValidTo').textContent = contract.effectiveTo;
                // }

                function doSelectContract(id) {
                    var contract = contractsById[id];
                    if (!contract) return;

                    contractSelect.value = contract.id;

                    var infoBox = document.getElementById('ctContractInfoBox');
                    infoBox.classList.add('is-visible');

                    // Fill the header fields from the list row immediately — the user
                    // sees the switch is acknowledged even before the fetch lands.
                    document.getElementById('ctfContractNumber').textContent = contract.contractNo;
                    document.getElementById('ctfContractName').textContent = contract.contractName;
                    document.getElementById('ctfValidFrom').textContent = contract.effectiveFrom;
                    document.getElementById('ctfValidTo').textContent = contract.effectiveTo;

                    // Clear the previous contract's rows and show a spinner while the
                    // new contract's charges load. Without this, the old table stays
                    // visible during the fetch and the switch looks broken.
                    state.contract = null;
                    var groupsEl = document.getElementById('ctRevenueGroups');
                    if (groupsEl) {
                        groupsEl.innerHTML =
                            '<div class="text-center text-muted py-4">' +
                            '<i class="fas fa-spinner fa-spin mr-2"></i> Loading charges…' +
                            '</div>';
                    }
                    document.getElementById('ctRevenueTotalDisplay').textContent = '0.00';
                    document.getElementById('ctChargesArea').classList.add('is-visible');

                    // On failure, roll the UI back to the empty state so the user
                    // isn't left staring at a spinner forever.
                    function handleFetchFailure(message) {
                        state.contract = null;
                        groupsEl.innerHTML =
                            '<div class="text-center text-danger py-4">' +
                            (message || 'Failed to load charges for this contract.') +
                            '</div>';
                    }

                    fetch('/contracts/' + contract.id + '/charges/display?quotation_id=' + encodeURIComponent(quotationId))
                        .then(function(r) {
                            return r.json();
                        })
                        .then(function(res) {
                            if (!res.success || !res.data) {
                                console.error('[Contract] charge display failed:', res.message);
                                handleFetchFailure(res.message || 'No charges found for this contract.');
                                return;
                            }
                            var rows = res.data;

                            function deriveMarkup(raw, cost, sell, atActuals) {
                                if (atActuals) return null;

                                // Trust the backend when it sends a real, non-zero value.
                                if (raw !== null && raw !== undefined && raw !== '') {
                                    var n = parseFloat(raw);
                                    if (!isNaN(n) && n !== 0) return n;
                                }

                                // Fall back to deriving from Cost vs Sell — the case
                                // where the contract stores Sell as a raw number
                                // without ever persisting the markup value.
                                var c = parseFloat(cost);
                                var s = parseFloat(sell);
                                if (isNaN(c) || isNaN(s) || c <= 0) return null;
                                if (s <= c) return 0; // no markup, valid (Sell ≤ Cost)

                                return Math.round(((s - c) / c) * 100 * 100) / 100;
                            }

                            function toRow(r, revenueOrCost) {
                                var isRevenue = revenueOrCost === 'revenue';

                                var rate = r.CostRate;
                                var curr = isRevenue ? r.RevenueCurr : r.CostCurr;

                                var initialCostRate = r.CostRate;
                                var initialSellRate = r.RevenueRate;
                                var initialMarkup = r.IncreaseBy;
                                var initialMarkupType = r.IncreaseType;
                                var initialRateType = r.RateType;
                                var isAtActuals = String(initialRateType || '').trim().toLowerCase().replace(/[ _-]/g,
                                    '') === 'atactuals';

                                return {
                                    group: r.ChargeCatagory || 'Other',
                                    code: r.ChargeCode || '',
                                    chargeType: r.ChargeType || 'both',

                                    // Existing charge IDs
                                    chargeRowId: r.ChargeRowID || '',

                                    // New IDs required for edit
                                    costChargeRowId: r.CostChargeRowID || '',
                                    revenueChargeRowId: r.RevenueChargeRowID || '',

                                    partyCode: r.PartyCode || '',
                                    desc: r.ChargeDescription || '',
                                    basedOn: r.ChargeBasedon || '',
                                    basedOnUOM: r.ChargeBasedOnUOM || '',
                                    curr: curr || '',
                                    remark: r.ChargeRemark || '',

                                    cost: isAtActuals ? 0 : (
                                        rate !== null && rate !== '' && rate !== undefined ?
                                        parseFloat(rate) :
                                        null
                                    ),

                                    qty: r.Quantity !== null && r.Quantity !== undefined ?
                                        parseFloat(r.Quantity) : null,
                                    // Preserve the exact values returned by the contract/SP.
                                    // Do not calculate or convert missing values to zero here.
                                    initialCostRate: isAtActuals ? 0 : (initialCostRate !== null && initialCostRate !==
                                        '' &&
                                        initialCostRate !== undefined ?
                                        parseFloat(initialCostRate) : null),

                                    initialSellRate: isAtActuals ? 0 : (initialSellRate !== null && initialSellRate !==
                                        '' &&
                                        initialSellRate !== undefined ?
                                        parseFloat(initialSellRate) : null),

                                    initialCostAmount: isAtActuals ? 0 : (r.CostAmount !== null && r.CostAmount !==
                                        undefined && r
                                        .CostAmount !== '' ?
                                        parseFloat(r.CostAmount) :
                                        null),

                                    initialSellAmount: isAtActuals ? 0 : (r.RevenueAmount !== null && r
                                        .RevenueAmount !== undefined && r
                                        .RevenueAmount !== '' ?
                                        parseFloat(r.RevenueAmount) :
                                        null),

                                    sellRate: initialSellRate !== null && initialSellRate !== '' &&
                                        initialSellRate !== undefined ?
                                        parseFloat(initialSellRate) : null,

                                    // Markup: trust the backend value, but fall back to
                                    // deriving from Cost vs Sell when the contract stored a
                                    // Sell rate without ever persisting IncrementBy. This is
                                    // what makes rows authored via manual Cost/Sell entry
                                    // (or copied contracts) show their effective markup.
                                    markup: deriveMarkup(initialMarkup, initialCostRate, initialSellRate, isAtActuals),

                                    markupType: (initialMarkupType !== null && initialMarkupType !== '' &&
                                            initialMarkupType !== undefined) ?
                                        String(initialMarkupType).toLowerCase() : 'percentage',

                                    rateType: initialRateType !== null && initialRateType !== '' &&
                                        initialRateType !== undefined ?
                                        String(initialRateType) : '',
                                    atActuals: isAtActuals,

                                    // IMPORTANT:
                                    // false means this row has not yet been edited by the user.
                                    edited: false,

                                    selected: true
                                };
                            }

                            state.contract = {
                                id: contract.id,
                                contractName: contract.contractName,
                                baseCurrency: (shipmentContext && shipmentContext.baseCurrency) || 'USD',
                                shipment: (state.contract && state.contract.shipment) || {
                                    weight: 0,
                                    volume: 0,
                                    container: 1,
                                    packageCount: 1
                                },
                                revenue: rows.map(function(r) {
                                    return toRow(r, 'revenue');
                                }),
                                cost: rows.map(function(r) {
                                    return toRow(r, 'cost');
                                })
                            };

                            if (res.shipment) {
                                document.getElementById('ctfWeight').textContent = res.shipment.Weight || '-';
                                document.getElementById('ctfVolume').textContent = res.shipment.Volume || '-';
                                document.getElementById('ctfContainer').textContent = res.shipment.ContrainerList ||
                                    '-';
                                document.getElementById('ctShipmentBox').style.display = 'block';
                            }

                            document.getElementById('ctChargesArea').classList.add('is-visible');
                            renderSection('revenue');
                            recalcTotals();
                        })
                        .catch(function(err) {
                            console.error('[Contract] charge display fetch failed:', err);
                            handleFetchFailure('Failed to load contract charges. Please try again.');
                        });
                }

                function resetContractSelection() {
                    contractSelect.value = '';

                    var infoBox = document.getElementById('ctContractInfoBox');
                    infoBox.classList.remove('is-visible');
                    infoBox.style.display = 'none';
                    ['ctfContractNumber', 'ctfContractName', 'ctfValidFrom', 'ctfValidTo',
                        'ctfClient', 'ctfShipper', 'ctfConsignee', 'ctfPol', 'ctfPod'
                    ].forEach(function(id) {
                        var el = document.getElementById(id);
                        if (el) el.textContent = '-';
                    });

                    var shipBox = document.getElementById('ctShipmentBox');
                    shipBox.style.display = 'none';
                    ['ctfWeight', 'ctfVolume', 'ctfContainer', 'ctfPackageCount',
                        'ctfShipmentType', 'ctfBaseCurrency'
                    ].forEach(function(id) {
                        var el = document.getElementById(id);
                        if (el) el.textContent = '-';
                    });

                    document.getElementById('ctChargesArea').classList.remove('is-visible');
                    document.getElementById('ctRevenueGroups').innerHTML = '';
                    document.getElementById('ctRevenueTotalDisplay').textContent = '0.00';

                    state.contract = null;
                }

                window.__contractTemplateReset = resetContractSelection;

                // ------------------------------------------------------------
                // Reset on close so the next Workbook open starts clean:
                // dropdown back to "-- Select Contract --", info/charges hidden,
                // state cleared. The parent page caches the injected content, so
                // we can't rely on a fresh script run — this is what gives us
                // "reload the dropdown on reopen" behaviour.
                // ------------------------------------------------------------
                if (window.jQuery) {
                    window.jQuery('#contractTemplateModal').on('hidden.bs.modal', function() {
                        resetContractSelection();
                        __lastSelectedContractId = null;
                        __contractChangeGuard = 0;
                    });
                }
                // Manual reload: re-runs the same charges fetch so newly added
                // contract lines show up without leaving the page.
                var reloadChargesBtn = document.getElementById('ctReloadChargesBtn');
                if (reloadChargesBtn) {
                    reloadChargesBtn.addEventListener('click', function() {
                        // Prefer live state, fall back to the dropdown so Reload still
                        // works even if the state hasn't been re-hydrated yet.
                        var id = (state.contract && state.contract.id) || contractSelect.value;
                        if (id) {
                            __lastSelectedContractId = id; // don't let the guard swallow this click
                            __contractChangeGuard = 0;
                            doSelectContract(id);
                        }
                    });
                }

                // ------------------------------------------------------------
                // STEP 5/6/7/15: render collapsible, groupable charge sections
                // with checkboxes and an inline-editable Cost column
                // ------------------------------------------------------------
                function actualCost(row) {
                    return row.cost;
                }

                function hasValue(value) {
                    return value !== null && value !== undefined && value !== '';
                }

                // Display-only: treat 0 as a valid value so it renders as "0.00" instead of blank.
                function isNumber(value) {
                    return typeof value === 'number' && isFinite(value);
                }

                function computeSell(row) {

                    // ============================================================
                    // INITIALIZATION
                    //
                    // Never calculate Sell from Cost/Markup here.
                    // Always use exactly what the backend returned.
                    // ============================================================
                    if (!row.edited) {
                        return row.initialSellRate;
                    }

                    // ============================================================
                    // AFTER USER EDIT
                    //
                    // Calculation is forward-only and depends on the scenario.
                    // ============================================================

                    // Revenue-only:
                    // Sell Rate itself is the editable/source field.
                    if (
                        row.chargeType === 'revenue_only' ||
                        row.chargeType === 'revenue_accrual'
                    ) {
                        return row.sellRate;
                    }

                    // Cost + Markup:
                    // Sell is calculated forward from Cost + Markup.
                    if (row.chargeType === 'both') {

                        if (!isNumber(actualCost(row))) {
                            return null;
                        }

                        var markup = hasValue(row.markup) ?
                            row.markup :
                            0;

                        if (row.markupType === 'percentage') {
                            return round2(
                                actualCost(row) +
                                (actualCost(row) * (markup / 100))
                            );
                        }

                        return round2(
                            actualCost(row) + markup
                        );
                    }

                    // Cost-only / reimbursement:
                    // There is no Sell source to calculate from unless the backend
                    // already supplied one.
                    return row.initialSellRate;
                }

                function computeCostAmount(row) {
                    // Init: trust the backend completely.
                    if (!row.edited) {
                        return row.initialCostAmount == null ? 0 : row.initialCostAmount;
                    }

                    var cost = actualCost(row);
                    var qty = qtyFor(row);

                    // Null qty means "no matching container in cargo" — treat as 0
                    // so the save payload always carries a numeric amount.
                    if (!isNumber(cost)) return 0;
                    if (!isNumber(qty)) return 0;

                    return round2(cost * qty);
                }

                function computeSellAmount(row) {
                    // Init: trust the backend completely.
                    if (!row.edited) {
                        return row.initialSellAmount == null ? 0 : row.initialSellAmount;
                    }

                    var sell = computeSell(row);
                    var qty = qtyFor(row);

                    // Null qty means "no matching container in cargo" — treat as 0
                    // so the save payload always carries a numeric amount.
                    if (!isNumber(sell)) return 0;
                    if (!isNumber(qty)) return 0;

                    return round2(sell * qty);
                }

                function round2(n) {
                    return Math.round((n + Number.EPSILON) * 100) / 100;
                }

                function renderSection(kind) {
                    var rows = state.contract[kind];
                    var container = document.getElementById('ctRevenueGroups');
                    container.innerHTML = '';

                    var groups = {};
                    rows.forEach(function(r) {
                        if (!groups[r.group]) groups[r.group] = [];
                        groups[r.group].push(r);
                    });

                    var orderedGroupNames = CHARGE_GROUP_ORDER.filter(function(g) {
                            return groups[g];
                        })
                        .concat(Object.keys(groups).filter(function(g) {
                            return CHARGE_GROUP_ORDER.indexOf(g) === -1;
                        }));

                    orderedGroupNames.forEach(function(groupName) {
                        var groupRows = groups[groupName];
                        var groupTotal = groupRows.reduce(function(sum, r) {
                            if (!r.selected) return sum;
                            var amt = computeSellAmount(r);
                            return sum + (isNumber(amt) ? amt : 0);
                        }, 0);

                        var groupAllSelected = groupRows.length > 0 && groupRows.every(function(r) {
                            return r.selected;
                        });

                        var wrap = document.createElement('div');
                        wrap.className = 'cc-group';
                        wrap.innerHTML =
                            '<div class="cc-group-header" data-group="' + groupName + '" data-kind="' + kind +
                            '">' +
                            '<span class="cc-caret">&#9660;</span>' +
                            '<span>' + groupName + '</span>' +
                            '<span class="cc-group-total" id="grouptotal-' + kind + '-' + slug(groupName) +
                            '">' +
                            round2(groupTotal).toFixed(2) + ' ' + state.contract.baseCurrency + '</span>' +
                            '</div>' +
                            '<div class="cc-group-body" id="body-' + kind + '-' + slug(groupName) + '">' +
                            '<div class="cc-table-wrap">' +
                            '<table class="cc-table">' +
                            '<thead><tr>' +
                            (READ_ONLY ? '' :
                                '<th class="cc-col-chk"><input type="checkbox" class="cc-select-all-check" data-kind="' +
                                kind + '" data-group="' + groupName + '"' + (groupAllSelected ? ' checked' : '') +
                                '></th>'
                            ) +
                            '<th class="cc-col-description">Description</th>' +
                            '<th class="cc-col-based-on">Based On & UOM</th><th class="cc-col-tight text-right">Qty</th>' +
                            '<th class="cc-col-tight">Rate Type</th><th class="cc-col-tight">Cost Rate</th><th class="cc-col-tight">Cost Amount</th><th class="cc-toggle-group-col">Markup</th>' +
                            '<th class="cc-col-tight">Sell Rate</th><th class="cc-col-tight">Sell Amount</th><th class="cc-col-tight">Currency</th><th>Remark</th>' +
                            '</tr></thead>' +
                            '<tbody>' +
                            (groupRows.length ? groupRows.map(function(r) {
                                    var qty = qtyFor(r);
                                    var sell = computeSell(r);
                                    var amount = computeSellAmount(r);
                                    var costAmount = computeCostAmount(r);

                                    var sellDisplayWidth = isNumber(sell) ?
                                        Math.max(4, sell.toFixed(2).length + 2) :
                                        4;

                                    var currSuffix = r.curr ? ' ' + r.curr : '';
                                    var rowIndex = rows.indexOf(r);
                                    var rowKey = kind + '-' + rowIndex;

                                    // STEP: field editability driven by chargeType.
                                    // Qty is never editable in any bucket (see qtyFor()).
                                    //
                                    // Scenario 1 (both / Cost+Markup): the only editable field is
                                    // the markup (Increase By / Type). Cost Rate and Sell Rate are
                                    // both locked - Sell is derived from Cost + Markup.
                                    // Scenario 4 (revenue_only) and its locked-markup sibling
                                    // (revenue_accrual): Sell Rate is the only open field.
                                    // Scenario 5 (cost_only) and its locked-markup sibling
                                    // (cost_reimbursement): Cost Rate is the only open field.
                                    // revenue_accrual / cost_reimbursement are not returned by the
                                    // SP today (see classifyCharge() on the backend), so these two
                                    // branches are inert until that's fixed - kept here so the UI
                                    // is already correct once it is.
                                    // ============================================================
                                    // FIELD EDITABILITY BY CHARGE SCENARIO
                                    //
                                    // both:
                                    //   Cost Rate  = locked
                                    //   Markup     = editable
                                    //   Sell Rate  = locked
                                    //
                                    // revenue_only / revenue_accrual:
                                    //   Sell Rate  = editable
                                    //   Cost Rate  = locked
                                    //   Markup     = locked
                                    //
                                    // cost_only / cost_reimbursement:
                                    //   Cost Rate  = editable
                                    //   Sell Rate  = locked
                                    //   Markup     = locked
                                    //
                                    // IMPORTANT:
                                    // This only controls editability.
                                    // It does NOT calculate or modify backend values during
                                    // initialization.
                                    // ============================================================

                                    var hasCost = hasValue(r.initialCostRate);
                                    var hasSell = hasValue(r.initialSellRate);
                                    var hasMarkup = hasValue(r.markup);

                                    var costDisabled = true;
                                    var sellDisabled = true;
                                    var markupDisabled = true;

                                    if (r.atActuals) {
                                        // At Actuals is always 0 and never editable.
                                        costDisabled = true;
                                        sellDisabled = true;
                                        markupDisabled = true;
                                    } else {
                                        switch (r.chargeType) {
                                            case 'both':
                                                // Cost + Markup -> Sell. Existing values only.
                                                costDisabled = true;
                                                sellDisabled = true;
                                                markupDisabled = !hasMarkup;
                                                break;

                                            case 'revenue_only':
                                            case 'revenue_accrual':
                                                // Revenue is the source value.
                                                costDisabled = true;
                                                sellDisabled = !hasSell;
                                                markupDisabled = true;
                                                break;

                                            case 'cost_only':
                                            case 'cost_reimbursement':
                                                // Cost is the source value.
                                                costDisabled = !hasCost;
                                                sellDisabled = true;
                                                markupDisabled = true;
                                                break;

                                            default:
                                                costDisabled = !hasCost;
                                                sellDisabled = !hasSell;
                                                markupDisabled = !hasMarkup;
                                                break;
                                        }
                                    }

                                    if (READ_ONLY) {
                                        return '<tr data-key="' + rowKey + '">' +
                                            '<td class="cc-col-description">' + r.desc + '</td>' +
                                            '<td class="cc-col-based-on">' +
                                            r.basedOn +
                                            (r.basedOnUOM ? ' / ' + r.basedOnUOM : '') +
                                            '</td>' +
                                            '<td class="cc-col-tight text-right">' + qty + '</td>' +
                                            '<td class="cc-col-tight">' + (r.rateType || '') + '</td>' +
                                            '<td class="cc-col-tight text-right">' +
                                            (isNumber(r.cost) ? r.cost.toFixed(2) : '') +
                                            '</td>' +
                                            '<td class="cc-col-tight text-right">' +
                                            (isNumber(costAmount) ? costAmount.toFixed(2) : '') +
                                            '</td>' +
                                            '<td class="cc-toggle-group-col text-right">' +
                                            (hasValue(r.markup) ?
                                                r.markup + (r.markupType === 'percentage' ? '%' : '') :
                                                '') +
                                            '</td>' +
                                            '<td class="cc-col-tight text-right">' +
                                            (isNumber(r.initialSellRate) ? r.initialSellRate.toFixed(2) : '') +
                                            '</td>' +
                                            '<td class="cc-col-tight text-right">' +
                                            (isNumber(amount) ? amount.toFixed(2) : '') +
                                            '</td>' +
                                            '<td class="cc-col-tight">' + (r.curr || '') + '</td>' +
                                            '<td>' + (r.remark || '') + '</td>' +
                                            '</tr>';
                                    }

                                    return '<tr data-key="' + rowKey + '">' +
                                        '<td class="cc-col-chk"><input type="checkbox" class="cc-row-check" data-kind="' +
                                        kind + '" data-index="' + rowIndex + '"' + (r.selected ?
                                            ' checked' : '') + '></td>' +
                                        '<td class="cc-col-description">' + r.desc + '</td>' +
                                        '<td class="cc-col-based-on">' + r.basedOn + (r.basedOnUOM ? ' / ' +
                                            r
                                            .basedOnUOM : '') + '</td>' +
                                        '<td class="cc-col-tight">' + qty + '</td>' +
                                        '<td class="cc-col-tight">' +
                                        '<select class="cc-plain-input cc-rate-type-select" data-kind="' +
                                        kind + '" data-index="' + rowIndex + '"' + (r.atActuals ?
                                            ' disabled' :
                                            '') + '>' +
                                        '<option value="Fixed"' + (String(r.rateType || '')
                                            .toLowerCase() ===
                                            'fixed' ? ' selected' : '') + '>Fixed</option>' +
                                        '<option value="Percentage"' + (String(r.rateType || '')
                                            .toLowerCase() === 'percentage' ? ' selected' : '') +
                                        '>Percentage</option>' +
                                        '</select>' +
                                        '<td class="cc-col-tight">' +
                                        '<input type="number" step="0.01" class="cc-cost-input" data-kind="' +
                                        kind + '" data-index="' + rowIndex + '" value="' +
                                        (isNumber(r.cost) ? r.cost : '') +
                                        '"' + (costDisabled ? ' disabled' : '') + '>' +
                                        '</td>' +
                                        '<td class="cc-amount-val cc-cost-amount-val cc-col-tight">' +
                                        (isNumber(costAmount) ? costAmount.toFixed(2) : '') +
                                        '</td>' +
                                        '<td class="cc-toggle-group-col">' +
                                        '<div class="cc-cost-cell">' +
                                        '<input type="number" step="0.01" class="cc-cost-input cc-markup-input" data-kind="' +
                                        kind + '" data-index="' + rowIndex + '" value="' + (hasValue(r
                                                .markup) ?
                                            r.markup : '') + '"' +
                                        (markupDisabled ? ' disabled' : '') + '>' +
                                        '<div class="cc-toggle-group" data-kind="' + kind +
                                        '" data-index="' +
                                        rowIndex + '">' +
                                        '<button type="button" class="cc-toggle-btn' + (r.markupType !==
                                            'percentage' ? ' active' : '') +
                                        (markupDisabled ? ' disabled' : '') +
                                        '" data-type="fixed"' + (markupDisabled ? ' disabled' : '') +
                                        '>Fixed</button>' +
                                        '<button type="button" class="cc-toggle-btn' + (r.markupType ===
                                            'percentage' ? ' active' : '') +
                                        (markupDisabled ? ' disabled' : '') +
                                        '" data-type="percentage"' + (markupDisabled ? ' disabled' : '') +
                                        '>%</button>' +
                                        '</div>' +
                                        '</div>' +
                                        '</td>' +
                                        '<td class="cc-col-tight">' +
                                        '<input type="number" step="0.01" ' +
                                        'class="cc-plain-input cc-sell-input" ' +
                                        'data-kind="' + kind + '" ' +
                                        'data-index="' + rowIndex + '" ' +
                                        'value="' + (isNumber(r.initialSellRate) ? r.initialSellRate
                                            .toFixed(
                                                2) : '') + '" ' +
                                        'style="width:' + sellDisplayWidth + 'ch" ' +
                                        (sellDisabled ? 'disabled="disabled"' : '') +
                                        '>' +
                                        '</td>' +
                                        '<td class="cc-amount-val cc-sell-amount-val cc-col-tight">' +
                                        (isNumber(amount) ? amount.toFixed(2) : '') +
                                        '</td>' +
                                        '<td class="cc-col-tight"><select class="cc-plain-input cc-currency-select" data-kind="' +
                                        kind + '" data-index="' + rowIndex + '">' +
                                        currencyOptions.map(function(c) {
                                            return '<option value="' + c.CurrencyCode + '"' + (c
                                                    .CurrencyCode === r.curr ? ' selected' : '') + '>' +
                                                c
                                                .CurrencyCode + '</option>';
                                        }).join('') +
                                        '</select></td>' +
                                        '<td class="cc-col-narrow">' + (r.remark || '') + '</td>' +
                                        '</tr>';
                                }).join('') :
                                '<tr><td colspan="' + (READ_ONLY ? 11 : 12) +
                                '" class="cc-empty-msg">No charges yet</td></tr>'
                            ) +
                            '</tbody>' +
                            '</table>' +
                            '</div>' +
                            '</div>';
                        container.appendChild(wrap);
                    });
                }

                // STEP 10: quantity derivation.
                //
                // Priority order:
                //   1. row.qty from the SP — authoritative when present (it already
                //      accounts for the contract's own quantity rules).
                //   2. Derived from shipmentContext when the SP returned null.
                //      Keyed on row.basedOn / row.basedOnUOM.
                //   3. row.qty (null) — forces amount to 0 in the save payload.
                //
                // All lookups across shipmentContext are case-insensitive — the SP
                // returns varying casings for the shipment block.
                function qtyFor(row) {
                    if (row.qty !== null && row.qty !== undefined && !isNaN(row.qty)) {
                        return row.qty;
                    }
                    return deriveQtyFromShipment(row);
                }

                function deriveQtyFromShipment(row) {
                    var s = shipmentContext || {};
                    var basedOn = String(row.basedOn || '').trim().toLowerCase();
                    var uom = String(row.basedOnUOM || '').trim().toUpperCase();

                    // First non-empty value among the given candidate keys.
                    function pick() {
                        for (var i = 0; i < arguments.length; i++) {
                            var k = arguments[i];
                            if (s[k] !== undefined && s[k] !== null && s[k] !== '') return s[k];
                        }
                        return null;
                    }

                    // Shipment-level — always 1 per shipment.
                    if (basedOn.indexOf('shipment') !== -1) {
                        return 1;
                    }

                    // Weight-based (GrossWt, NetWt, Weight in KGS, etc.)
                    if (
                        basedOn.indexOf('grosswt') !== -1 ||
                        basedOn.indexOf('weight') !== -1 ||
                        basedOn === 'kg' ||
                        basedOn === 'kgs'
                    ) {
                        var w = pick('Weight', 'weight', 'GrossWeight', 'grossWeight', 'GrossWt');
                        return w === null ? null : parseFloat(w) || 0;
                    }

                    // Volume-based (CBM, Volume in CBM, etc.)
                    if (basedOn.indexOf('volume') !== -1 || basedOn.indexOf('cbm') !== -1) {
                        var v = pick('Volume', 'volume', 'CBM', 'cbm');
                        return v === null ? null : parseFloat(v) || 0;
                    }

                    // Package / box count.
                    if (
                        basedOn.indexOf('package') !== -1 ||
                        basedOn.indexOf('box') !== -1 ||
                        basedOn.indexOf('carton') !== -1
                    ) {
                        var p = pick('PackageCount', 'packageCount', 'Packages', 'packages', 'Package', 'package');
                        return p === null ? null : parseFloat(p) || 0;
                    }

                    // Container / Equipment — match the row's UOM against the container list.
                    // Container list format example: "20GPX2,40HCX1"
                    if (basedOn.indexOf('equipment') !== -1 || basedOn.indexOf('container') !== -1) {
                        var list = pick('ContrainerList', 'containerList', 'Containers', 'containers', 'Container',
                            'container');

                        if (!list || typeof list !== 'string') {
                            return null;
                        }

                        // Parse into { '20GP': 2, '40HC': 1 }.
                        var map = {};
                        list.split(/[,;]/).forEach(function(part) {
                            var m = part.trim().match(/^([A-Z0-9]+)\s*[xX*]\s*(\d+)$/);
                            if (m) map[m[1].toUpperCase()] = parseInt(m[2], 10);
                        });

                        if (!uom) {
                            // No UOM on the row — sum everything.
                            var total = 0;
                            Object.keys(map).forEach(function(k) {
                                total += map[k];
                            });
                            return total > 0 ? total : null;
                        }

                        var key = uom.toUpperCase();
                        if (map[key] !== undefined) return map[key];

                        // Partial match — e.g. row has "40GP", list has "40GPX1".
                        for (var k in map) {
                            if (k.indexOf(key) !== -1 || key.indexOf(k) !== -1) return map[k];
                        }

                        return null;
                    }

                    // Unknown BasedOn — fall back to whatever the SP returned (may be null).
                    return row.qty;
                }

                function slug(str) {
                    return str.toLowerCase().replace(/[^a-z0-9]+/g, '-');
                }

                // Grows the Sell input to fit its content instead of clipping digits
                function sizeSellInput(el) {
                    if (!el) return;
                    var len = (el.value || '').length;
                    el.style.width = Math.max(4, len + 2) + 'ch';
                }

                // Group collapse/expand
                document.addEventListener('click', function(e) {
                    var header = e.target.closest('.cc-group-header');
                    if (header) {
                        var body = document.getElementById('body-' + header.getAttribute('data-kind') + '-' +
                            slug(header.getAttribute('data-group')));
                        header.classList.toggle('collapsed');
                        body.classList.toggle('collapsed');
                    }
                });

                // Row selection ticks -> STEP 15
                document.addEventListener('change', function(e) {
                    if (e.target.classList.contains('cc-select-all-check')) {
                        var kind = e.target.getAttribute('data-kind');
                        var groupName = e.target.getAttribute('data-group');
                        var checked = e.target.checked;
                        state.contract[kind].forEach(function(r) {
                            if (r.group === groupName) r.selected = checked;
                        });
                        renderSection(kind);
                        recalcTotals();
                        return;
                    }

                    if (e.target.classList.contains('cc-row-check')) {
                        var kind = e.target.getAttribute('data-kind');
                        var idx = parseInt(e.target.getAttribute('data-index'), 10);
                        state.contract[kind][idx].selected = e.target.checked;
                        renderSection(kind);
                        recalcTotals();
                    }

                    if (e.target.classList.contains('cc-currency-select')) {
                        var kind = e.target.getAttribute('data-kind');
                        var idx = parseInt(e.target.getAttribute('data-index'), 10);

                        var row = state.contract[kind][idx];

                        row.curr = e.target.value;

                        updateRowDerived(kind, idx);

                        return;
                    }
                });
                // Rate Type change. At Actuals forces Rate to 0 and locks it.
                document.addEventListener('change', function(e) {
                    if (!e.target.classList.contains('cc-rate-type-select')) return;

                    var kind = e.target.getAttribute('data-kind');
                    var idx = parseInt(e.target.getAttribute('data-index'), 10);
                    if (!state.contract || !kind || isNaN(idx)) return;

                    var row = state.contract[kind][idx];
                    row.rateType = e.target.value;
                    row.atActuals = String(row.rateType).toLowerCase().replace(/[ _-]/g, '') === 'atactuals';

                    if (row.atActuals) {
                        row.cost = 0;
                        row.initialCostRate = 0;
                        row.initialCostAmount = 0;
                        row.sellRate = 0;
                        row.initialSellRate = 0;
                        row.initialSellAmount = 0;
                        row.markup = null;
                        row.markupType = null;
                    }

                    renderSection(kind);
                    recalcTotals();
                });

                // ------------------------------------------------------------
                // Inline Cost editing: value input + Fixed/% toggle per row.
                // Updates state directly and refreshes only the affected row's
                // Sell/Amount cells plus group + summary totals (no full re-render,
                // so the input never loses focus while typing).
                // ------------------------------------------------------------
                document.addEventListener('input', function(e) {
                    var target = e.target;
                    var kind = target.getAttribute('data-kind');
                    var idx = parseInt(target.getAttribute('data-index'), 10);

                    if (!state.contract || !kind || isNaN(idx)) {
                        return;
                    }

                    var row = state.contract[kind][idx];

                    // ------------------------------------------------------------
                    // COST RATE
                    // Editable only for cost_only / cost_reimbursement.
                    // ------------------------------------------------------------
                    if (
                        target.classList.contains('cc-cost-input') &&
                        !target.classList.contains('cc-markup-input')
                    ) {
                        row.cost = target.value === '' ?
                            null :
                            parseFloat(target.value);

                        row.edited = true;

                        updateRowDerived(kind, idx);

                        return;
                    }

                    // ------------------------------------------------------------
                    // MARKUP
                    // Editable only for both.
                    // ------------------------------------------------------------
                    if (target.classList.contains('cc-markup-input')) {
                        row.markup = target.value === '' ?
                            null :
                            parseFloat(target.value);

                        row.edited = true;

                        updateRowDerived(kind, idx, 'markup');

                        return;
                    }

                    // SELL RATE
                    // Editable only for revenue-only / revenue-accrual rows.
                    if (target.classList.contains('cc-sell-input') && !target.disabled) {
                        row.sellRate = target.value === '' ? null : parseFloat(target.value);
                        row.edited = true;
                        updateRowDerived(kind, idx, 'sell');
                    }
                });

                document.addEventListener('click', function(e) {
                    var btn = e.target.closest('.cc-toggle-btn');
                    if (!btn || btn.disabled) return;
                    var group = btn.closest('.cc-toggle-group');
                    var kind = group.getAttribute('data-kind');
                    var idx = parseInt(group.getAttribute('data-index'), 10);
                    state.contract[kind][idx].markupType = btn.getAttribute('data-type');
                    state.contract[kind][idx].edited = true;
                    group.querySelectorAll('.cc-toggle-btn').forEach(function(b) {
                        b.classList.toggle('active', b === btn);
                    });
                    updateRowDerived(kind, idx);
                });

                function updateRowDerived(kind, idx, changedField) {
                    var row = state.contract[kind][idx];
                    var qty = qtyFor(row);
                    var sell = computeSell(row);
                    var amount = computeSellAmount(row);

                    var tr = document.querySelector(
                        'tr[data-key="' + kind + '-' + idx + '"]'
                    );

                    if (tr) {
                        if (changedField !== 'sell') {
                            var sellInput = tr.querySelector('.cc-sell-input');

                            if (sellInput) {
                                sellInput.value = isNumber(sell) ?
                                    sell.toFixed(2) :
                                    '';

                                sizeSellInput(sellInput);
                            }
                        }

                        if (changedField !== 'markup') {
                            var markupInput = tr.querySelector('.cc-markup-input');

                            if (markupInput) {
                                markupInput.value = hasValue(row.markup) ?
                                    row.markup :
                                    '';
                            }
                        }

                        var costAmountCell = tr.querySelector('.cc-cost-amount-val');

                        if (costAmountCell) {
                            var costAmount = computeCostAmount(row);

                            costAmountCell.textContent =
                                isNumber(costAmount) ?
                                costAmount.toFixed(2) :
                                '';
                        }

                        var sellAmountCell = tr.querySelector('.cc-sell-amount-val');

                        sellAmountCell.textContent =
                            isNumber(amount) ?
                            amount.toFixed(2) :
                            '';
                    }

                    updateGroupTotal(kind, row.group);
                    recalcTotals();
                }

                function updateGroupTotal(kind, groupName) {
                    var rows = state.contract[kind].filter(function(r) {
                        return r.group === groupName;
                    });

                    var total = rows.reduce(function(sum, r) {
                        if (!r.selected) return sum;
                        var amt = computeSellAmount(r);
                        return sum + (isNumber(amt) ? amt : 0);
                    }, 0);

                    var el = document.getElementById(
                        'grouptotal-' + kind + '-' + slug(groupName)
                    );

                    if (el) {
                        el.textContent =
                            round2(total).toFixed(2) +
                            ' ' +
                            state.contract.baseCurrency;
                    }
                }

                function recalcTotals() {
                    var revenueTotal = state.contract.revenue.reduce(function(sum, r) {
                        if (!r.selected) return sum;
                        var amt = computeSellAmount(r);
                        return sum + (isNumber(amt) ? amt : 0);
                    }, 0);

                    document.getElementById('ctRevenueTotalDisplay').textContent =
                        round2(revenueTotal).toFixed(2);
                }

                // ------------------------------------------------------------
                // STEP 16/17: Save Contract — only selected charges go into the
                // payload. This is the object a real POST /contracts endpoint
                // would receive, and the same shape a Quotation Edit screen
                // would later read Revenue/Cost cards from (STEP 17).
                // ------------------------------------------------------------
                function refreshQuotationCards() {
                    fetch('{{ route('quotation.contract-template.charges', $quotationId) }}')
                        .then(function(r) {
                            return r.json();
                        })
                        .then(function(res) {
                            if (res.success && res.data) {
                                var revEl = document.getElementById('revenueTotalHeaderAmount');
                                var costEl = document.getElementById('costTotalHeaderAmount');
                                var quotedRevEl = document.getElementById('quotedRevenue');
                                var quotedMarginEl = document.getElementById('quotedMargin');
                                if (revEl) revEl.textContent = res.data.revenue_total.toFixed(2);
                                if (costEl) costEl.textContent = res.data.cost_total.toFixed(2);
                                if (quotedRevEl) quotedRevEl.value = res.data.revenue_total.toFixed(2);
                                if (quotedMarginEl) quotedMarginEl.value = res.data.margin.toFixed(2);
                            }
                        })
                        .catch(function(err) {
                            console.error('[ContractTemplate] charges refresh failed:', err);
                        })
                        .finally(function() {
                            if (typeof window.loadCharges === 'function') {
                                window.loadCharges();
                            } else {
                                console.warn('loadCharges not available; tables not refreshed.');
                            }
                        });
                }

                document.getElementById('ctSaveContractBtn').addEventListener('click', function() {
                    if (!state.contract) {
                        alert('Charges are not available for templates yet — nothing to save.');
                        return;
                    }
                    var saveBtn = this;
                    var revenueTotal = round2(state.contract.revenue.reduce(function(sum, r) {
                        return sum + (r.selected ? computeSellAmount(r) : 0);
                    }, 0));
                    var costTotal = round2(state.contract.cost.reduce(function(sum, r) {
                        return sum + (r.selected ? computeCostAmount(r) : 0);
                    }, 0));
                    var margin = round2(revenueTotal - costTotal);

                    function serializeSelected(kind) {
                        return state.contract[kind].filter(function(r) {
                            return r.selected;
                        }).map(function(r) {
                            var qty = qtyFor(r);
                            var sell = computeSell(r);
                            return Object.assign({}, r, {
                                qty: qty,
                                sell: sell,
                                amount: hasValue(sell) && hasValue(qty) ? round2(sell * qty) : null
                            });
                        });
                    }

                    function serializeCharges(kind) {
                        return state.contract[kind].filter(function(r) {
                            return r.selected;
                        }).map(function(r) {
                            return {
                                ChargeCatagory: r.group,
                                ChargeRowID: r.costChargeRowId || r.chargeRowId || '',

                                ChargeCode: r.code,
                                ChargeDescription: r.desc || '',
                                PartyCode: r.partyCode || '',
                                ChgCurr: r.curr || state.contract.baseCurrency,
                                BasedOnCode: r.basedOn,
                                BasedOnUOM: r.basedOnUOM || '',
                                Quantity: qtyFor(r) == null ? 0 : qtyFor(r),
                                CostRate: r.cost == null ? 0 : r.cost,
                                CostAmount: computeCostAmount(r) == null ? 0 : computeCostAmount(r),

                                IncreaseBy: r.markup == null ? 0 : r.markup,
                                IncreaseType: r.markupType === 'percentage' ? 'Percentage' : 'Fixed',
                                SellRate: computeSell(r) == null ? 0 : computeSell(r),
                                SellAmount: computeSellAmount(r) == null ? 0 : computeSellAmount(r),
                                ChargeRemark: r.remark || ''
                            };
                        });
                    }

                    var payload = {
                        OperationType: 'WorkbookCharges',
                        SubmittedDatetime: new Date().toISOString().replace(/[-:TZ.]/g, '').slice(0,
                            14),
                        SubmittedBy: '{{ auth()->user()->displayName ?? (auth()->user()->email ?? 'system') }}',
                        Action: 'Add',
                        QuotationNo: '{{ $shipment['quote_no'] ?? '' }}',
                        QuotationInfoID: '{{ $quotationId }}',
                        ContractID: state.contract.id,

                        Charges: serializeCharges('cost')
                    };

                    saveBtn.disabled = true;
                    saveBtn.textContent = 'Saving...';

                    console.log('[Contract Save] Payload:', JSON.stringify(payload, null, 2));

                    fetch('{{ route('quotation.contract-template.store', $quotationId) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(payload)
                        })
                        .then(function(r) {
                            return r.json();
                        })
                        .then(function(res) {
                            if (!res.success) throw new Error(res.message || 'Save failed');
                            refreshQuotationCards();
                            var modalEl = document.getElementById('contractTemplateModal');
                            if (window.jQuery && modalEl) window.jQuery(modalEl).modal('hide');
                            if (typeof window.showNotification === 'function') {
                                window.showNotification('Contract saved successfully', 'success');
                            }
                            // Reload so the Edit page reflects the saved contract/charges
                            // from a clean state. showNotification fires first so the
                            // confirmation is visible before the reload happens.
                            setTimeout(function() {
                                window.location.reload();
                            }, 700);
                        })
                        .catch(function(err) {
                            console.error('[Contract] save failed:', err);
                            alert('Failed to save contract. Please try again.');
                        })
                        .finally(function() {
                            saveBtn.disabled = false;
                            saveBtn.textContent = 'Save Contract';
                        });
                });
            })();
        </script>

    </div>
@stop

@section('css')
    <style>
        .cc-box {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 16px;
            background: #fff;
        }

        .cc-box.is-visible,
        #ctChargesArea.is-visible {
            display: block !important;
        }

        .cc-box-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #4B49AC;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        #contractTemplateModal .modal-dialog {
            max-width: 1400px;
            width: 95%;
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
            font-size: 0.82rem;
            color: #495057;
            min-width: 150px;
        }

        .cc-lbl-required {
            color: #495057;
        }

        .cc-val {
            font-size: 0.85rem;
            color: #212529;
            font-weight: 500;
        }

        .cc-input {
            border: 1px solid #ced4da;
            border-radius: 3px;
            font-size: 0.85rem;
            padding: 2px 8px;
        }

        .cc-input-group {
            display: flex;
            align-items: center;
        }

        .cc-section-block {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            overflow: hidden;
        }

        #contractTemplateContent {
            max-width: 100%;
        }

        #ctRevenueGroups {
            max-height: 500px;
            overflow-y: auto;
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

        .cc-header-cost {
            background: #6c757d;
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
            padding: 8px 12px;
        }

        .cc-group-body.collapsed {
            display: none;
        }

        .cc-table-wrap {
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .cc-table-wrap::-webkit-scrollbar {
            height: 8px;
        }

        .cc-table-wrap::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .cc-table-wrap::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .cc-table {
            width: 100%;
            min-width: 960px;
            margin-bottom: 0;
            font-size: 0.8rem;
            border-collapse: collapse;
        }

        .cc-table th,
        .cc-table td {
            border: 1px solid #dee2e6;
            padding: 5px 7px;
            vertical-align: middle;
            white-space: nowrap;
        }

        .cc-table thead th {
            background: #fff;
            font-weight: 600;
            color: #6c757d;
            font-size: 0.72rem;
            text-transform: uppercase;
        }

        .cc-col-chk {
            width: 30px;
            text-align: center;
        }

        .cc-col-narrow {
            max-width: 70px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .cc-col-description {
            min-width: 260px;
            width: 260px;
            max-width: 360px;
            white-space: normal !important;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .cc-col-based-on {
            min-width: 120px;
            max-width: 160px;
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: unset !important;
            word-break: normal;
        }

        .cc-col-wide {
            min-width: 70px;
        }

        .cc-col-tight {
            min-width: 55px;
        }

        .cc-toggle-group-col {
            min-width: 70px;
        }

        .cc-curr-tag {
            font-size: 0.7rem;
            color: #6c757d;
        }

        .cc-plain-input {
            width: 48px;
            min-width: 40px;
            border: 1px solid #ced4da;
            border-radius: 3px;
            font-size: 0.78rem;
            padding: 2px 5px;
            box-sizing: content-box;
        }

        .cc-empty-msg {
            text-align: center;
            color: #888;
            padding: 10px;
            font-size: 0.8rem;
        }

        .cc-json-preview {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
            font-size: 0.75rem;
            max-height: 320px;
            overflow: auto;
        }

        /* Inline editable Cost cell: value + fixed/% toggle */
        .cc-cost-cell {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 3px;
        }

        .cc-cost-input {
            width: 55px;
            border: 1px solid #ced4da;
            border-radius: 3px;
            font-size: 0.78rem;
            padding: 2px 5px;
        }

        .cc-toggle-group {
            display: flex;
            border: 1px solid #4B49AC;
            border-radius: 3px;
            flex-shrink: 0;
        }

        .cc-toggle-btn {
            border: none;
            background: #fff;
            color: #4B49AC;
            font-size: 0.72rem;
            padding: 3px 6px;
            cursor: pointer;
            line-height: 1;
        }

        .cc-toggle-btn+.cc-toggle-btn {
            border-left: 1px solid #4B49AC;
        }

        .cc-toggle-btn.active {
            background: #4B49AC;
            color: #fff;
        }

        .cc-box-compact {
            padding: 10px 16px;
        }

        .cc-box-compact .cc-info-row {
            margin-bottom: 3px;
        }

        .cc-box-compact .cc-lbl {
            min-width: 120px;
            font-size: 0.78rem;
        }

        .cc-box-compact .cc-val {
            font-size: 0.8rem;
        }

        .cc-collapsible-title {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            user-select: none;
        }

        .cc-collapsible-title .cc-caret {
            font-size: 0.7rem;
            transition: transform 0.15s ease;
        }

        .cc-collapsible-title.collapsed .cc-caret {
            transform: rotate(-90deg);
        }

        .cc-info-grid-compact.collapsed {
            display: none;
        }

        @media (max-width: 991px) {
            #contractTemplateModal .modal-dialog {
                max-width: 100%;
                width: 98%;
                margin: 8px auto;
            }

            .cc-info-grid {
                flex-direction: column;
                gap: 10px;
            }

            .cc-lbl {
                min-width: 130px;
            }

            .cc-table th,
            .cc-table td {
                padding: 4px 5px;
                font-size: 0.75rem;
            }

            .cc-cost-input,
            .cc-plain-input {
                width: 70px;
            }
        }

        @media (max-width: 576px) {
            .cc-box {
                padding: 10px 12px;
            }

            .cc-col-based-on {
                min-width: 120px;
                max-width: 140px;
                white-space: normal !important;
                overflow: visible !important;
                text-overflow: unset !important;
                line-height: 1.2;
            }

            .cc-box-compact {
                padding: 8px 12px;
            }

            .cc-box-tight {
                margin-bottom: 0;
                border-radius: 0;
                padding-top: 8px;
                padding-bottom: 8px;
            }

            .cc-box-tight+.cc-box-tight {
                border-top: none;
            }

            .cc-box-tight:first-of-type {
                border-radius: 4px 4px 0 0;
            }

            .cc-box-tight:last-of-type {
                border-radius: 0 0 4px 4px;
                margin-bottom: 1rem;
            }

            .cc-box-tight .cc-collapsible-title {
                margin-bottom: 0;
            }

            .cc-box-tight .cc-info-grid-compact {
                margin-top: 8px;
            }

            .cc-info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 2px;
            }

            .cc-lbl {
                min-width: 0;
            }

            .cc-section-block-header {
                flex-wrap: wrap;
                gap: 4px;
            }

            .cc-totals-inline {
                margin-left: 0 !important;
                width: 100%;
            }

            .cc-cost-cell {
                flex-wrap: wrap;
                gap: 4px;
            }

            .cc-toggle-group {
                flex-shrink: 1;
            }

            .cc-table-wrap {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .cc-table {
                min-width: 640px;
            }

            #ctRevenueGroups {
                max-height: 60vh;
            }

            .d-flex.justify-content-end.mb-4 {
                justify-content: stretch !important;
            }

            #ctSaveContractBtn {
                width: 100%;
            }
        }

        /* ═══════════════════════════════════════════════════════════════
                   READ-ONLY MODE — genuinely compact. Smaller type, tighter
                   rows, and denser info boxes than the editable version.
                   ═══════════════════════════════════════════════════════════════ */
        .ct-readonly #ctSaveContractBtn,
        .ct-readonly #ctReloadChargesBtn {
            display: none !important;
        }

        /* Table — one notch below the Charges card */
        .ct-readonly .cc-table {
            font-size: 0.72rem;
        }

        .ct-readonly .cc-table th,
        .ct-readonly .cc-table td {
            padding: 2px 5px;
            line-height: 1.25;
        }

        .ct-readonly .cc-table thead th {
            background: #e9ecef;
            color: #495057;
            font-size: 0.68rem;
            font-weight: 600;
            text-transform: none;
            letter-spacing: normal;
            padding: 3px 5px;
        }

        .ct-readonly .cc-table tbody tr:hover {
            background: #f5f5f5;
        }

        .ct-readonly .cc-table td.text-right {
            font-variant-numeric: tabular-nums;
        }

        /* Group bands — half-height */
        .ct-readonly .cc-group-header {
            padding: 3px 10px;
            font-size: 0.7rem;
            letter-spacing: 0.02em;
        }

        .ct-readonly .cc-group-body {
            padding: 2px 8px;
        }

        /* Info boxes — trim label/row spacing */
        .ct-readonly .cc-box-compact {
            padding: 6px 12px;
        }

        .ct-readonly .cc-info-row {
            margin-bottom: 1px;
            gap: 6px;
        }

        .ct-readonly .cc-lbl {
            font-size: 0.72rem;
            min-width: 110px;
        }

        .ct-readonly .cc-val {
            font-size: 0.74rem;
        }

        /* Contract dropdown at the top — shrinks with everything else */
        .ct-readonly .cc-input-highlight {
            height: 24px !important;
            font-size: 0.78rem;
        }

        /* Section block header (Charges / Reload strip) */
        .ct-readonly .cc-section-block-header {
            padding: 5px 12px;
            font-size: 0.82rem;
        }
    </style>
@stop

@section('js')

@stop
