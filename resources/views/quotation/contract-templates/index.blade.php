@extends('adminlte::page')

@section('title', 'Contract')

@section('content_header')
    <h1 class="h3 mb-0">Contract</h1>
@stop

@section('content')
    <div class="container-fluid" id="contractTemplateContent">

        {{-- Added based on stakeholder meeting requirement --}}
        {{-- STEP 2: Contract selection by Name / Description (dropdown), not just Contract Number --}}
        <div class="cc-box mb-3">
            <div class="cc-info-row">
                <span class="cc-lbl cc-lbl-required" style="min-width:160px;">* Select Contract</span>
                <div class="cc-input-group" style="flex:1;">
                    <select class="cc-input cc-input-highlight" id="ctContractSelect" style="width:100%; height:28px;">
                        <option value="">-- Select Contract --</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- STEP 1: Contract master info card --}}
        <div class="cc-box mb-3" id="ctContractInfoBox" style="display:none;">
            <div class="cc-box-title">Contract Information</div>
            <div class="cc-info-grid">
                <div class="cc-info-col">
                    <div class="cc-info-row"><span class="cc-lbl">Contract Number</span><span class="cc-val"
                            id="ctfContractNumber">-</span></div>
                    <div class="cc-info-row"><span class="cc-lbl">Contract Name</span><span class="cc-val"
                            id="ctfContractName">-</span></div>
                </div>
                <div class="cc-info-col">
                    <div class="cc-info-row"><span class="cc-lbl">Valid From</span><span class="cc-val"
                            id="ctfValidFrom">-</span></div>
                    <div class="cc-info-row"><span class="cc-lbl">Valid To</span><span class="cc-val"
                            id="ctfValidTo">-</span></div>
                </div>
            </div>
        </div>

        {{-- STEP 3: Auto-populated party / route info --}}
        <div class="cc-box mb-3" id="ctPartyRouteBox" style="display:none;">
            <div class="cc-box-title">Party &amp; Route Information</div>
            <div class="cc-info-grid">
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
        <div class="cc-box mb-3" id="ctShipmentBox" style="display:none;">
            <div class="cc-box-title">Shipment Information</div>
            <div class="cc-info-grid">
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
                    <strong>Revenue Charges</strong>
                    <span class="ml-auto cc-totals-inline">
                        Total: <span id="ctRevenueTotalDisplay">0.00</span> <span id="ctBaseCurrencyLabelRev"></span>
                    </span>
                </div>
                <div id="ctRevenueGroups"></div>
            </div>

            {{-- STEP 14: Margin summary --}}
            <div class="cc-box mb-3">
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
            </div>

            {{-- STEP 16: Save Contract — only selected charges are persisted into the mock contract object --}}
            <div class="d-flex justify-content-end mb-4">
                <button type="button" class="btn btn-success" id="ctSaveContractBtn">Save Contract</button>
            </div>
        </div>
        {{-- End: Added based on stakeholder meeting requirement --}}

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

        .cc-col-chk {
            width: 30px;
            text-align: center;
        }

        .cc-table th:nth-child(5),
        .cc-table td:nth-child(5),
        .cc-table th:nth-child(8),
        .cc-table td:nth-child(8) {
            min-width: 75px;
        }

        .cc-table th:nth-child(6),
        .cc-table td:nth-child(6),
        .cc-table th:nth-child(7),
        .cc-table td:nth-child(7) {
            min-width: 165px;
        }

        .cc-plain-input {
            width: 60px;
            border: 1px solid #ced4da;
            border-radius: 3px;
            font-size: 0.78rem;
            padding: 2px 5px;
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
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }

        .cc-cost-input {
            width: 90px;
            border: 1px solid #ced4da;
            border-radius: 3px;
            font-size: 0.78rem;
            padding: 2px 5px;
        }

        .cc-type-select {
            width: 90px;
            flex-shrink: 0;
            border: 1px solid #4B49AC;
            border-radius: 3px;
            color: #4B49AC;
            background: #fff;
            font-size: 0.75rem;
            padding: 3px 4px;
            cursor: pointer;
        }
    </style>
@stop

@section('js')
    <script>
        (function() {

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

            var mockContracts = [{
                    id: 'CTR000123',
                    contractNumber: 'CTR000123',
                    contractName: 'Kenya Air Import Standard',
                    contractDescription: 'Standard air import contract - Dubai to Nairobi',
                    contractType: 'Standard',
                    validFrom: '2026-01-01',
                    validTo: '2026-12-31',
                    countryOrigin: 'UAE',
                    countryDestination: 'Kenya',
                    serviceType: 'Air',
                    processType: 'Import',
                    client: 'Kenya Traders Ltd',
                    shipper: 'Dubai Freight Co',
                    consignee: 'Kenya Traders Ltd',
                    notifyParty: 'Kenya Traders Ltd',
                    pol: 'Dubai (DXB)',
                    pod: 'Nairobi (NBO)',
                    finalDestination: 'Nairobi',
                    originDestination: 'UAE - Kenya',
                    baseCurrency: 'USD',
                    shipment: {
                        weight: 520,
                        volume: 3.2,
                        container: 0,
                        packageCount: 12,
                        shipmentType: 'General Cargo'
                    },
                    revenue: [{
                            group: 'General / Port Charges',
                            code: 'THC001',
                            desc: 'Terminal Handling',
                            basedOn: 'Shipment',
                            rate: 50,
                            currency: 'USD',
                            cost: 35,
                            costType: 'fixed',
                            markupType: 'fixed',
                            markup: 15,
                            sell: 50,
                            selected: true
                        },
                        {
                            group: 'General / Port Charges',
                            code: 'DOC001',
                            desc: 'Documentation Fee',
                            basedOn: 'Shipment',
                            rate: 25,
                            currency: 'USD',
                            cost: 0,
                            costType: 'fixed',
                            markupType: 'fixed',
                            markup: 25,
                            sell: 25,
                            selected: true
                        },
                        {
                            group: 'Freight / Carrier Related Charges',
                            code: 'AFR001',
                            desc: 'Air Freight',
                            basedOn: 'KG',
                            rate: 2.10,
                            currency: 'USD',
                            cost: 1.80,
                            costType: 'fixed',
                            markupType: 'fixed',
                            markup: 0.30,
                            sell: 2.10,
                            selected: true
                        },
                        {
                            group: 'Delivery / Pickup Related Charges',
                            code: 'LMD001',
                            desc: 'Last Mile Delivery',
                            basedOn: 'Shipment',
                            rate: 1225,
                            currency: 'USD',
                            cost: 965,
                            costType: 'fixed',
                            markupType: 'fixed',
                            markup: 260,
                            sell: 1225,
                            selected: false
                        },
                        {
                            group: 'Agency Charges',
                            code: 'AGN001',
                            desc: 'Agency Fee',
                            basedOn: 'Shipment',
                            rate: 120,
                            currency: 'USD',
                            cost: 0,
                            costType: 'fixed',
                            markupType: 'fixed',
                            markup: 120,
                            sell: 120,
                            selected: true
                        }
                    ],
                    cost: [{
                            group: 'General / Port Charges',
                            code: 'THC001',
                            desc: 'Terminal Handling',
                            basedOn: 'Shipment',
                            rate: 35,
                            currency: 'USD',
                            cost: 35,
                            costType: 'fixed',
                            markupType: 'fixed',
                            markup: 0,
                            sell: 35,
                            selected: true
                        },
                        {
                            group: 'Freight / Carrier Related Charges',
                            code: 'AFR001',
                            desc: 'Air Freight',
                            basedOn: 'KG',
                            rate: 1.80,
                            currency: 'USD',
                            cost: 1.80,
                            costType: 'fixed',
                            markupType: 'fixed',
                            markup: 0,
                            sell: 1.80,
                            selected: true
                        },
                        {
                            group: 'Delivery / Pickup Related Charges',
                            code: 'LMD001',
                            desc: 'Last Mile Delivery',
                            basedOn: 'Shipment',
                            rate: 965,
                            currency: 'USD',
                            cost: 965,
                            costType: 'fixed',
                            markupType: 'fixed',
                            markup: 0,
                            sell: 965,
                            selected: false
                        }
                    ]
                },
                {
                    id: 'CTR000124',
                    contractNumber: 'CTR000124',
                    contractName: 'Uganda Sea Freight Export',
                    contractDescription: 'FCL export contract - Mombasa to Kampala corridor',
                    contractType: 'Corridor',
                    validFrom: '2026-02-01',
                    validTo: '2027-01-31',
                    countryOrigin: 'Kenya',
                    countryDestination: 'Uganda',
                    serviceType: 'Sea',
                    processType: 'Export',
                    client: 'Uganda Imports Co',
                    shipper: 'Mombasa Shipping Ltd',
                    consignee: 'Uganda Imports Co',
                    notifyParty: 'Kampala Logistics',
                    pol: 'Mombasa (MBA)',
                    pod: 'Kampala (Dry Port)',
                    finalDestination: 'Kampala',
                    originDestination: 'Kenya - Uganda',
                    baseCurrency: 'USD',
                    shipment: {
                        weight: 18400,
                        volume: 33,
                        container: 2,
                        packageCount: 0,
                        shipmentType: 'FCL - 2x20GP'
                    },
                    revenue: [{
                            group: 'General / Port Charges',
                            code: 'THC002',
                            desc: 'Terminal Handling',
                            basedOn: 'Container',
                            rate: 120,
                            currency: 'USD',
                            cost: 95,
                            costType: 'fixed',
                            markupType: 'fixed',
                            markup: 25,
                            sell: 120,
                            selected: true
                        },
                        {
                            group: 'Freight / Carrier Related Charges',
                            code: 'OFR001',
                            desc: 'Ocean Freight',
                            basedOn: 'Container',
                            rate: 1800,
                            currency: 'USD',
                            cost: 1500,
                            costType: 'fixed',
                            markupType: 'fixed',
                            markup: 300,
                            sell: 1800,
                            selected: true
                        },
                        {
                            group: 'Documentation Charges',
                            code: 'BOL001',
                            desc: 'Bill of Lading Fee',
                            basedOn: 'Shipment',
                            rate: 80,
                            currency: 'USD',
                            cost: 0,
                            costType: 'fixed',
                            markupType: 'fixed',
                            markup: 80,
                            sell: 80,
                            selected: true
                        },
                        {
                            group: 'Delivery / Pickup Related Charges',
                            code: 'INL001',
                            desc: 'Inland Transport',
                            basedOn: 'Container',
                            rate: 2200,
                            currency: 'USD',
                            cost: 1805,
                            costType: 'fixed',
                            markupType: 'fixed',
                            markup: 395,
                            sell: 2200,
                            selected: false
                        }
                    ],
                    cost: [{
                            group: 'General / Port Charges',
                            code: 'THC002',
                            desc: 'Terminal Handling',
                            basedOn: 'Container',
                            rate: 95,
                            currency: 'USD',
                            cost: 95,
                            costType: 'fixed',
                            markupType: 'fixed',
                            markup: 0,
                            sell: 95,
                            selected: true
                        },
                        {
                            group: 'Freight / Carrier Related Charges',
                            code: 'OFR001',
                            desc: 'Ocean Freight',
                            basedOn: 'Container',
                            rate: 1500,
                            currency: 'USD',
                            cost: 1500,
                            costType: 'fixed',
                            markupType: 'fixed',
                            markup: 0,
                            sell: 1500,
                            selected: true
                        },
                        {
                            group: 'Delivery / Pickup Related Charges',
                            code: 'INL001',
                            desc: 'Inland Transport',
                            basedOn: 'Container',
                            rate: 1805,
                            currency: 'USD',
                            cost: 1805,
                            costType: 'fixed',
                            markupType: 'fixed',
                            markup: 0,
                            sell: 1805,
                            selected: false
                        }
                    ]
                },
                {
                    id: 'CTR000126',
                    contractNumber: 'CTR000126',
                    contractName: 'Dubai Export Contract - Perishables',
                    contractDescription: 'Air export contract with customs clearance included',
                    contractType: 'Special Handling',
                    validFrom: '2026-03-01',
                    validTo: '2026-12-31',
                    countryOrigin: 'Kenya',
                    countryDestination: 'UAE',
                    serviceType: 'Air',
                    processType: 'Export',
                    client: 'Dubai Export Partners',
                    shipper: 'Nairobi Cargo Ltd',
                    consignee: 'Dubai Export Partners',
                    notifyParty: 'Dubai Export Partners',
                    pol: 'Nairobi (NBO)',
                    pod: 'Dubai (DXB)',
                    finalDestination: 'Dubai',
                    originDestination: 'Kenya - UAE',
                    baseCurrency: 'USD',
                    shipment: {
                        weight: 800,
                        volume: 5.4,
                        container: 0,
                        packageCount: 40,
                        shipmentType: 'Perishables'
                    },
                    revenue: [{
                            group: 'General / Port Charges',
                            code: 'THC003',
                            desc: 'Terminal Handling',
                            basedOn: 'Shipment',
                            rate: 60,
                            currency: 'USD',
                            cost: 40,
                            costType: 'fixed',
                            markupType: 'fixed',
                            markup: 20,
                            sell: 60,
                            selected: true
                        },
                        {
                            group: 'Freight / Carrier Related Charges',
                            code: 'AFR002',
                            desc: 'Air Freight',
                            basedOn: 'KG',
                            rate: 2.50,
                            currency: 'USD',
                            cost: 2.05,
                            costType: 'fixed',
                            markupType: 'fixed',
                            markup: 0.45,
                            sell: 2.50,
                            selected: true
                        },
                        {
                            group: 'Customs Charges',
                            code: 'CUS001',
                            desc: 'Customs Clearance',
                            basedOn: 'Shipment',
                            rate: 1040,
                            currency: 'USD',
                            cost: 820,
                            costType: 'fixed',
                            markupType: 'fixed',
                            markup: 220,
                            sell: 1040,
                            selected: true
                        },
                        {
                            group: 'Dangerous Goods Charges',
                            code: 'DGR001',
                            desc: 'DG Handling Surcharge',
                            basedOn: 'Shipment',
                            rate: 150,
                            currency: 'USD',
                            cost: 100,
                            costType: 'fixed',
                            markupType: 'fixed',
                            markup: 50,
                            sell: 150,
                            selected: false
                        }
                    ],
                    cost: [{
                            group: 'General / Port Charges',
                            code: 'THC003',
                            desc: 'Terminal Handling',
                            basedOn: 'Shipment',
                            rate: 40,
                            currency: 'USD',
                            cost: 40,
                            costType: 'fixed',
                            markupType: 'fixed',
                            markup: 0,
                            sell: 40,
                            selected: true
                        },
                        {
                            group: 'Freight / Carrier Related Charges',
                            code: 'AFR002',
                            desc: 'Air Freight',
                            basedOn: 'KG',
                            rate: 2.05,
                            currency: 'USD',
                            cost: 2.05,
                            costType: 'fixed',
                            markupType: 'fixed',
                            markup: 0,
                            sell: 2.05,
                            selected: true
                        },
                        {
                            group: 'Customs Charges',
                            code: 'CUS001',
                            desc: 'Customs Clearance',
                            basedOn: 'Shipment',
                            rate: 820,
                            currency: 'USD',
                            cost: 820,
                            costType: 'fixed',
                            markupType: 'fixed',
                            markup: 0,
                            sell: 820,
                            selected: true
                        }
                    ]
                }
            ];

            var state = {
                contract: null
            };

            // Shipment context passed from the Quotation Edit page's "Load Contract Template" link
            var shipmentContext = @json($shipment ?? []);
            // Template already linked to this quotation (set previously via store())
            var existingLink = @json($existing ?? null);

            function contractMatchesShipment(c) {
                if (!shipmentContext) return true;
                var matches = true;
                if (shipmentContext.service) {
                    matches = matches && c.serviceType.toLowerCase() === shipmentContext.service.toLowerCase();
                }
                if (shipmentContext.origin) {
                    matches = matches && c.countryOrigin.toLowerCase() === shipmentContext.origin.toLowerCase();
                }
                if (shipmentContext.destination) {
                    matches = matches && c.countryDestination.toLowerCase() === shipmentContext.destination
                        .toLowerCase();
                }
                return matches;
            }

            var matchingContracts = mockContracts.filter(contractMatchesShipment);
            var contractsToShow = matchingContracts.length ? matchingContracts : mockContracts;

            // ------------------------------------------------------------
            // STEP 2: Contract Name / Description dropdown selector
            // ------------------------------------------------------------
            var contractSelect = document.getElementById('ctContractSelect');

            contractsToShow.forEach(function(c) {
                var opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.contractName + ' (' + c.contractNumber + ') — ' + c.contractDescription;
                contractSelect.appendChild(opt);
            });
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

            function selectContract(id) {
                try {
                    doSelectContract(id);
                } catch (err) {
                    console.error('[Contract] selectContract failed:', err);
                }
            }

            function doSelectContract(id) {
                var contract = mockContracts.filter(function(c) {
                    return c.id === id;
                })[0];
                if (!contract) return;
                state.contract = JSON.parse(JSON.stringify(
                    contract)); // clone so ticks/edits don't mutate the mock source
                contractSelect.value = contract.id;

                document.getElementById('ctContractInfoBox').classList.add('is-visible');
                document.getElementById('ctPartyRouteBox').classList.add('is-visible');
                document.getElementById('ctShipmentBox').classList.add('is-visible');
                document.getElementById('ctChargesArea').classList.add('is-visible');

                // STEP 1
                document.getElementById('ctfContractNumber').textContent = contract.contractNumber;
                document.getElementById('ctfContractName').textContent = contract.contractName;
                document.getElementById('ctfValidFrom').textContent = contract.validFrom;
                document.getElementById('ctfValidTo').textContent = contract.validTo;

                // STEP 3
                document.getElementById('ctfClient').textContent = contract.client;
                document.getElementById('ctfShipper').textContent = contract.shipper;
                document.getElementById('ctfConsignee').textContent = contract.consignee;
                document.getElementById('ctfPol').textContent = contract.pol;
                document.getElementById('ctfPod').textContent = contract.pod;

                // STEP 4
                document.getElementById('ctfWeight').textContent = contract.shipment.weight + ' KG';
                document.getElementById('ctfVolume').textContent = contract.shipment.volume + ' CBM';
                document.getElementById('ctfContainer').textContent = contract.shipment.container ? (contract
                    .shipment.container + ' unit(s)') : 'N/A';
                document.getElementById('ctfPackageCount').textContent = contract.shipment.packageCount || 'N/A';
                document.getElementById('ctfShipmentType').textContent = contract.shipment.shipmentType;
                // STEP 13: single base currency, no exchange rate shown anywhere
                document.getElementById('ctfBaseCurrency').textContent = contract.baseCurrency;
                document.getElementById('ctBaseCurrencyLabelRev').textContent = contract.baseCurrency;

                renderSection('revenue');
                recalcTotals();
            }

            // ------------------------------------------------------------
            // STEP 5/6/7/15: render collapsible, groupable charge sections
            // with checkboxes and an inline-editable Cost column
            // ------------------------------------------------------------
            function actualCost(row) {
                if (row.costType === 'percentage') {
                    return round2(row.rate * ((row.cost || 0) / 100));
                }
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
                        '<th class="cc-col-chk"><input type="checkbox" disabled></th>' +
                        '<th>Code</th><th>Description</th><th>Based On</th><th>Qty</th>' +
                        '<th>Cost</th><th>Markup</th><th>Sell</th><th>Amount</th>' +
                        '</tr></thead>' +
                        '<tbody>' +
                        (groupRows.length ? groupRows.map(function(r) {
                                var qty = qtyFor(r);
                                var sell = computeSell(r);
                                var amount = round2(sell * qty);
                                var rowIndex = rows.indexOf(r);
                                var rowKey = kind + '-' + rowIndex;
                                return '<tr data-key="' + rowKey + '">' +
                                    '<td class="cc-col-chk"><input type="checkbox" class="cc-row-check" data-kind="' +
                                    kind + '" data-index="' + rowIndex + '"' + (r.selected ?
                                        ' checked' : '') + '></td>' +
                                    '<td>' + r.code + '</td>' +
                                    '<td>' + r.desc + '</td>' +
                                    '<td>' + r.basedOn + '</td>' +
                                    '<td><input type="number" step="0.01" class="cc-plain-input cc-qty-input" data-kind="' +
                                    kind + '" data-index="' + rowIndex + '" value="' + qty + '"></td>' +
                                    '<td>' +
                                    '<div class="cc-cost-cell">' +
                                    '<input type="number" step="0.01" class="cc-cost-input" data-kind="' +
                                    kind + '" data-index="' + rowIndex + '" value="' + r.cost + '">' +
                                    '<select class="cc-type-select cc-cost-type-select" data-kind="' +
                                    kind +
                                    '" data-index="' + rowIndex + '">' +
                                    '<option value="fixed"' + (r.costType !== 'percentage' ? ' selected' :
                                        '') +
                                    '>Fixed</option>' +
                                    '<option value="percentage"' + (r.costType === 'percentage' ?
                                        ' selected' :
                                        '') +
                                    '>%</option>' +
                                    '</select>' +
                                    '</div>' +
                                    '</td>' +
                                    '<td>' +
                                    '<div class="cc-cost-cell">' +
                                    '<input type="number" step="0.01" class="cc-cost-input cc-markup-input" data-kind="' +
                                    kind + '" data-index="' + rowIndex + '" value="' + r.markup + '">' +
                                    '<select class="cc-type-select cc-markup-type-select" data-kind="' +
                                    kind +
                                    '" data-index="' + rowIndex + '">' +
                                    '<option value="fixed"' + (r.markupType !== 'percentage' ? ' selected' :
                                        '') +
                                    '>Fixed</option>' +
                                    '<option value="percentage"' + (r.markupType === 'percentage' ?
                                        ' selected' : '') +
                                    '>%</option>' +
                                    '</select>' +
                                    '</div>' +
                                    '</td>' +
                                    '<td><input type="number" step="0.01" class="cc-plain-input cc-sell-input" data-kind="' +
                                    kind + '" data-index="' + rowIndex + '" value="' + sell.toFixed(2) +
                                    '"></td>' +
                                    '<td class="cc-amount-val">' + amount.toFixed(2) + '</td>' +
                                    '</tr>';
                            }).join('') :
                            '<tr><td colspan="9" class="cc-empty-msg">No charges yet</td></tr>'
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
                var s = state.contract.shipment;
                switch (row.basedOn) {
                    case 'KG':
                        return s.weight;
                    case 'CBM':
                        return s.volume;
                    case 'Container':
                        return s.container || 1;
                    case 'Package':
                        return s.packageCount || 1;
                    case 'Shipment':
                    default:
                        return 1;
                }
            }

            function slug(str) {
                return str.toLowerCase().replace(/[^a-z0-9]+/g, '-');
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
                if (e.target.classList.contains('cc-row-check')) {
                    var kind = e.target.getAttribute('data-kind');
                    var idx = parseInt(e.target.getAttribute('data-index'), 10);
                    state.contract[kind][idx].selected = e.target.checked;
                    renderSection(kind);
                    recalcTotals();
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
                    state.contract[kind][idx].qtyOverride = parseFloat(target.value) || 0;
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
                    updateRowDerived(kind, idx, 'sell');
                    return;
                }
            });

            document.addEventListener('change', function(e) {
                var target = e.target;
                if (target.classList.contains('cc-cost-type-select')) {
                    var kind = target.getAttribute('data-kind');
                    var idx = parseInt(target.getAttribute('data-index'), 10);
                    state.contract[kind][idx].costType = target.value;
                    updateRowDerived(kind, idx);
                    return;
                }
                if (target.classList.contains('cc-markup-type-select')) {
                    var kind = target.getAttribute('data-kind');
                    var idx = parseInt(target.getAttribute('data-index'), 10);
                    state.contract[kind][idx].markupType = target.value;
                    updateRowDerived(kind, idx);
                    return;
                }
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
                        if (sellInput) sellInput.value = sell.toFixed(2);
                    }
                    if (changedField !== 'markup') {
                        var markupInput = tr.querySelector('.cc-markup-input');
                        if (markupInput) markupInput.value = row.markup;
                    }
                    var amountCell = tr.querySelector('.cc-amount-val');
                    if (amountCell) amountCell.textContent = amount.toFixed(2);
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

            // ------------------------------------------------------------
            // STEP 14: totals + margin, computed only from selected rows
            // ------------------------------------------------------------
            function recalcTotals() {
                var revenueTotal = state.contract.revenue.reduce(function(sum, r) {
                    return sum + (r.selected ? computeSell(r) * qtyFor(r) : 0);
                }, 0);
                var costTotal = state.contract.revenue.reduce(function(sum, r) {
                    return sum + (r.selected ? actualCost(r) * qtyFor(r) : 0);
                }, 0);
                var margin = revenueTotal - costTotal;

                document.getElementById('ctRevenueTotalDisplay').textContent = round2(revenueTotal).toFixed(2);
                document.getElementById('ctSummaryRevenue').textContent = round2(revenueTotal).toFixed(2) + ' ' +
                    state.contract.baseCurrency;
                document.getElementById('ctSummaryCost').textContent = round2(costTotal).toFixed(2) + ' ' + state
                    .contract.baseCurrency;
                document.getElementById('ctSummaryMargin').textContent = round2(margin).toFixed(2) + ' ' + state
                    .contract.baseCurrency;
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
                        if (!res.success || !res.data) return;
                        var revEl = document.getElementById('revenueTotalHeaderAmount');
                        var costEl = document.getElementById('costTotalHeaderAmount');
                        var quotedRevEl = document.getElementById('quotedRevenue');
                        var quotedMarginEl = document.getElementById('quotedMargin');
                        if (revEl) revEl.textContent = res.data.revenue_total.toFixed(2);
                        if (costEl) costEl.textContent = res.data.cost_total.toFixed(2);
                        if (quotedRevEl) quotedRevEl.value = res.data.revenue_total.toFixed(2);
                        if (quotedMarginEl) quotedMarginEl.value = res.data.margin.toFixed(2);
                    })
                    .catch(function(err) {
                        console.error('[ContractTemplate] charges refresh failed:', err);
                    });
            }

            document.getElementById('ctSaveContractBtn').addEventListener('click', function() {
                var saveBtn = this;
                var revenueTotal = parseFloat(document.getElementById('ctSummaryRevenue').textContent) || 0;
                var costTotal = parseFloat(document.getElementById('ctSummaryCost').textContent) || 0;
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

                var formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('contract_template_id', state.contract.id);
                formData.append('contract_template_name', state.contract.contractName);
                formData.append('markup_type', 'Fixed');
                formData.append('amount', margin);
                formData.append('percentage', '');
                formData.append('revenue_charges', JSON.stringify(serializeSelected('revenue')));
                formData.append('cost_charges', JSON.stringify(serializeSelected('cost')));
                formData.append('revenue_total', revenueTotal);
                formData.append('cost_total', costTotal);
                formData.append('margin', margin);

                saveBtn.disabled = true;
                saveBtn.textContent = 'Saving...';

                fetch('{{ route('quotation.contract-template.store', $quotationId) }}', {
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
                        refreshQuotationCards();
                        var modalEl = document.getElementById('contractTemplateModal');
                        if (window.jQuery && modalEl) {
                            window.jQuery(modalEl).modal('hide');
                        }
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
@stop
