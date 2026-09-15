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
                <div class="cc-input-group" style="flex:1;">
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

                    // Parent page has already loaded the contracts.
                    if (contracts.length) {
                        contracts.forEach(appendContractOption);
                        return;
                    }

                    // Parent page is still loading them.
                    if (typeof window.loadContractTemplateList === 'function') {
                        window.loadContractTemplateList().then(function(data) {
                            (data || []).forEach(appendContractOption);
                        });

                        return;
                    }

                    console.error('[Contract] Contract template list is not available.');
                }

                loadContractOptions();

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
                contractSelect.addEventListener('change', function() {
                    if (contractSelect.value) selectContract(contractSelect.value);
                });
                // AdminLTE forms often auto-init select2 on <select> elements, which
                // does not reliably fire native 'change' events. Bind via jQuery too
                // so this works whether or not select2 is applied to this dropdown.
                if (window.jQuery) {
                    window.jQuery('#ctContractSelect').on('change select2:select', function() {
                        if (this.value) selectContract(this.value);
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

                    document.getElementById('ctContractInfoBox').classList.add('is-visible');

                    document.getElementById('ctfContractNumber').textContent = contract.contractNo;
                    document.getElementById('ctfContractName').textContent = contract.contractName;
                    document.getElementById('ctfValidFrom').textContent = contract.effectiveFrom;
                    document.getElementById('ctfValidTo').textContent = contract.effectiveTo;

                    fetch('/contracts/' + contract.id + '/charges/display?quotation_id=' + encodeURIComponent(quotationId))
                        .then(function(r) {
                            return r.json();
                        })
                        .then(function(res) {
                            if (!res.success || !res.data) {
                                console.error('[Contract] charge display failed:', res.message);
                                alert(res.message || 'No charges found for this contract.');
                                return;
                            }

                            var rows = res.data;

                            function toRow(r, revenueOrCost) {
                                var rate = revenueOrCost === 'revenue' ? r.RevenueRate : r.CostRate;
                                var curr = revenueOrCost === 'revenue' ? r.RevenueCurr : r.CostCurr;
                                return {
                                    group: r.ChargeCatagory || 'Other',
                                    code: r.ChargeCode || '',
                                    chargeRowId: r.ChargeRowID || '',
                                    partyCode: r.PartyCode || '',
                                    desc: r.ChargeDescription,
                                    basedOn: r.ChargeBasedon,
                                    basedOnUOM: r.ChargeBasedOnUOM || '',
                                    curr: curr || '',
                                    remark: r.ChargeRemark || '',
                                    cost: parseFloat(rate) || 0,
                                    qty: parseFloat(r.Quantity) ||
                                        0, // NEW — SP-computed quantity, single source of truth
                                    markup: 0,
                                    markupType: 'fixed',
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
                                document.getElementById('ctShipmentBox').style.display = '';
                            }

                            document.getElementById('ctChargesArea').classList.add('is-visible');
                            renderSection('revenue');
                            recalcTotals();
                        })
                        .catch(function(err) {
                            console.error('[Contract] charge display fetch failed:', err);
                            alert('Failed to load contract charges. Please try again.');
                        });
                }

                // ------------------------------------------------------------
                // STEP 5/6/7/15: render collapsible, groupable charge sections
                // with checkboxes and an inline-editable Cost column
                // ------------------------------------------------------------
                function actualCost(row) {
                    return row.cost || 0;
                }

                function computeSell(row) {
                    var costAmt = actualCost(row);
                    if (row.markupType === 'percentage') {
                        return round2(costAmt + (costAmt * (row.markup / 100)));
                    }
                    return round2(costAmt + row.markup);
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
                            return sum + (r.selected ? computeSell(r) * qtyFor(r) : 0);
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
                            '<th class="cc-col-chk"><input type="checkbox" class="cc-select-all-check" data-kind="' +
                            kind + '" data-group="' + groupName + '"' + (groupAllSelected ? ' checked' : '') +
                            '></th>' +
                            '<th class="cc-col-narrow">Description</th><th class="cc-col-based-on">Based On & UOM</th><th class="cc-col-tight">Qty</th>' +
                            '<th class="cc-col-tight">Cost Rate</th><th class="cc-col-tight">Cost Amount</th><th class="cc-toggle-group-col">Markup</th>' +
                            '<th class="cc-col-tight">Sell Rate</th><th class="cc-col-tight">Sell Amount</th><th class="cc-col-tight">Currency</th><th>Remark</th>' +
                            '</tr></thead>' +
                            '<tbody>' +
                            (groupRows.length ? groupRows.map(function(r) {
                                    var qty = qtyFor(r);
                                    var sell = computeSell(r);
                                    var amount = round2(sell * qty);
                                    var costAmount = round2(r.cost * qty);
                                    var currSuffix = r.curr ? ' ' + r.curr : '';
                                    var rowIndex = rows.indexOf(r);
                                    var rowKey = kind + '-' + rowIndex;
                                    return '<tr data-key="' + rowKey + '">' +
                                        '<td class="cc-col-chk"><input type="checkbox" class="cc-row-check" data-kind="' +
                                        kind + '" data-index="' + rowIndex + '"' + (r.selected ?
                                            ' checked' : '') + '></td>' +
                                        '<td>' + r.desc + '</td>' +
                                        '<td class="cc-col-based-on">' + r.basedOn + (r.basedOnUOM ? ' / ' + r
                                            .basedOnUOM : '') + '</td>' +
                                        '<td class="cc-col-tight"><input type="number" step="0.01" class="cc-plain-input cc-qty-input" data-kind="' +
                                        kind + '" data-index="' + rowIndex + '" value="' + qty + '"></td>' +
                                        '<td class="cc-col-tight">' +
                                        '<input type="number" step="0.01" class="cc-cost-input" data-kind="' +
                                        kind + '" data-index="' + rowIndex + '" value="' + r.cost +
                                        '">' +
                                        '</td>' +
                                        '<td class="cc-amount-val cc-cost-amount-val cc-col-tight">' +
                                        costAmount.toFixed(2) +
                                        currSuffix + '</td>' +
                                        '<td class="cc-toggle-group-col">' +
                                        '<div class="cc-cost-cell">' +
                                        '<input type="number" step="0.01" class="cc-cost-input cc-markup-input" data-kind="' +
                                        kind + '" data-index="' + rowIndex + '" value="' + r.markup + '">' +
                                        '<div class="cc-toggle-group" data-kind="' + kind + '" data-index="' +
                                        rowIndex + '">' +
                                        '<button type="button" class="cc-toggle-btn' + (r.markupType !==
                                            'percentage' ? ' active' : '') +
                                        '" data-type="fixed">Fixed</button>' +
                                        '<button type="button" class="cc-toggle-btn' + (r.markupType ===
                                            'percentage' ? ' active' : '') +
                                        '" data-type="percentage">%</button>' +
                                        '</div>' +
                                        '</div>' +
                                        '</td>' +
                                        '<td class="cc-col-tight"><input type="number" step="0.01" class="cc-plain-input cc-sell-input" data-kind="' +
                                        kind + '" data-index="' + rowIndex + '" value="' + sell.toFixed(2) +
                                        '" style="width:' + Math.max(4, sell.toFixed(2).length + 2) +
                                        'ch"></td>' +
                                        '<td class="cc-amount-val cc-sell-amount-val cc-col-tight">' + amount
                                        .toFixed(2) +
                                        currSuffix + '</td>' +
                                        '<td class="cc-col-tight"><select class="cc-plain-input cc-currency-select" data-kind="' +
                                        kind + '" data-index="' + rowIndex + '">' +
                                        currencyOptions.map(function(c) {
                                            return '<option value="' + c.CurrencyCode + '"' + (c
                                                    .CurrencyCode === r.curr ? ' selected' : '') + '>' + c
                                                .CurrencyCode + '</option>';
                                        }).join('') +
                                        '</select></td>' +
                                        '<td class="cc-col-narrow">' + (r.remark || '') + '</td>' +
                                        '</tr>';
                                }).join('') :
                                '<tr><td colspan="11" class="cc-empty-msg">No charges yet</td></tr>'
                            ) +
                            '</tbody>' +
                            '</table>' +
                            '</div>' +
                            '</div>';
                        container.appendChild(wrap);
                    });
                }

                // STEP 10: quantity is derived from shipment info based on "Based On", never typed
                function qtyFor(row) {
                    if (row.qtyOverride !== undefined && row.qtyOverride !== null && row.qtyOverride !== '') {
                        return row.qtyOverride;
                    }
                    return row.qty || 0;
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
                        state.contract[kind][idx].curr = e.target.value;
                        updateRowDerived(kind, idx);
                        return;
                    }
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

                    if (target.classList.contains('cc-cost-input') && !target.classList.contains(
                            'cc-markup-input')) {
                        state.contract[kind][idx].cost = parseFloat(target.value) || 0;
                        updateRowDerived(kind, idx);
                        return;
                    }

                    if (target.classList.contains('cc-qty-input')) {
                        var val = target.value;
                        state.contract[kind][idx].qtyOverride = (val === '') ? undefined : (parseFloat(val) || 0);
                        updateRowDerived(kind, idx);
                        return;
                    }

                    if (target.classList.contains('cc-markup-input')) {
                        state.contract[kind][idx].markup = parseFloat(target.value) || 0;
                        updateRowDerived(kind, idx, 'markup');
                        return;
                    }

                    if (target.classList.contains('cc-sell-input')) {
                        var row = state.contract[kind][idx];
                        var sellVal = parseFloat(target.value) || 0;
                        row.markupType = 'fixed';
                        row.markup = round2(sellVal - actualCost(row));
                        sizeSellInput(target);
                        updateRowDerived(kind, idx, 'sell');
                        return;
                    }
                });

                document.addEventListener('click', function(e) {
                    var btn = e.target.closest('.cc-toggle-btn');
                    if (!btn) return;
                    var group = btn.closest('.cc-toggle-group');
                    var kind = group.getAttribute('data-kind');
                    var idx = parseInt(group.getAttribute('data-index'), 10);
                    state.contract[kind][idx].markupType = btn.getAttribute('data-type');
                    group.querySelectorAll('.cc-toggle-btn').forEach(function(b) {
                        b.classList.toggle('active', b === btn);
                    });
                    updateRowDerived(kind, idx);
                });

                function updateRowDerived(kind, idx, changedField) {
                    var row = state.contract[kind][idx];
                    var qty = qtyFor(row);
                    var sell = changedField === 'sell' ? computeSell(row) : computeSell(row);
                    var amount = round2(sell * qty);
                    var tr = document.querySelector('tr[data-key="' + kind + '-' + idx + '"]');
                    if (tr) {
                        if (changedField !== 'sell') {
                            var sellInput = tr.querySelector('.cc-sell-input');
                            if (sellInput) {
                                sellInput.value = sell.toFixed(2);
                                sizeSellInput(sellInput);
                            }
                        }
                        if (changedField !== 'markup') {
                            var markupInput = tr.querySelector('.cc-markup-input');
                            if (markupInput) markupInput.value = row.markup;
                        }
                        var costAmountCell = tr.querySelector('.cc-cost-amount-val');
                        if (costAmountCell) costAmountCell.textContent = round2(actualCost(row) * qty).toFixed(2);

                        var sellAmountCell = tr.querySelector('.cc-sell-amount-val');
                        if (sellAmountCell) sellAmountCell.textContent = amount.toFixed(2);
                    }
                    updateGroupTotal(kind, row.group);
                    recalcTotals();
                }

                function updateGroupTotal(kind, groupName) {
                    var rows = state.contract[kind].filter(function(r) {
                        return r.group === groupName;
                    });
                    var total = rows.reduce(function(sum, r) {
                        return sum + (r.selected ? computeSell(r) * qtyFor(r) : 0);
                    }, 0);
                    var el = document.getElementById('grouptotal-' + kind + '-' + slug(groupName));
                    if (el) el.textContent = round2(total).toFixed(2) + ' ' + state.contract.baseCurrency;
                }

                function recalcTotals() {
                    var revenueTotal = state.contract.revenue.reduce(function(sum, r) {
                        return sum + (r.selected ? computeSell(r) * qtyFor(r) : 0);
                    }, 0);
                    document.getElementById('ctRevenueTotalDisplay').textContent = round2(revenueTotal).toFixed(2);
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
                        return sum + (r.selected ? computeSell(r) * qtyFor(r) : 0);
                    }, 0));
                    var costTotal = round2(state.contract.cost.reduce(function(sum, r) {
                        return sum + (r.selected ? actualCost(r) * qtyFor(r) : 0);
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
                                amount: round2(sell * qty)
                            });
                        });
                    }

                    function serializeCharges(kind) {
                        return state.contract[kind].filter(function(r) {
                            return r.selected;
                        }).map(function(r) {
                            return {
                                ChargeCatagory: r.group,
                                ChargeRowID: r.chargeRowId || '',

                                ChargeCode: r.code,
                                ChargeDescription: r.desc || '',
                                PartyCode: r.partyCode || '',
                                ChgCurr: r.curr || state.contract.baseCurrency,
                                BasedOnCode: r.basedOn,
                                BasedOnUOM: r.basedOnUOM || '',
                                Quantity: qtyFor(r),
                                CostRate: r.cost,
                                CostAmount: round2(actualCost(r) * qtyFor(r)),
                                IncreaseBy: 0,
                                IncreaseType: 'Percentage',
                                SellRate: computeSell(r),
                                SellAmount: round2(computeSell(r) * qtyFor(r)),
                                ChargeRemark: r.remark || ''
                            };
                        });
                    }

                    var revenueCharges = serializeCharges('revenue');
                    var costCharges = serializeCharges('cost');

                    var payload = {
                        OperationType: 'WorkbookCharges',
                        SubmittedDatetime: new Date().toISOString().replace(/[-:TZ.]/g, '').slice(0, 14),
                        SubmittedBy: '{{ auth()->user()->displayName ?? (auth()->user()->email ?? 'system') }}',
                        Action: 'Add',

                        QuotationNo: '{{ $shipment['quote_no'] ?? '' }}',
                        QuotationInfoID: '{{ $quotationId }}',
                        ContractID: state.contract.id,

                        Charges: revenueCharges
                    };

                    saveBtn.disabled = true;
                    saveBtn.textContent = 'Saving...';

                    console.log('[Contract Save] Payload:', JSON.stringify(payload, null, 2));

                    fetch('{{ route('quotation.contract-template.store', $quotationId) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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
    </style>
@stop

@section('js')

@stop
