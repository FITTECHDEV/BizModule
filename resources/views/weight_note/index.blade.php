@extends('adminlte::page')

@section('title', 'Weight Note')

@section('content_header')
    <div id="pageHeaderDashboard">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-weight mr-2"></i> Weight Note</h1>
                <p class="text-muted mb-0">Manage and track weight notes</p>
            </div>
        </div>
    </div>

    <div id="pageHeaderForm" style="display:none;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-file-alt mr-2"></i> New Weight Note — <span id="formJobTypeLabel" class="text-muted"
                        style="font-size:1rem; font-weight:400;"></span></h1>
                <p class="text-muted mb-0">Fill in the details below</p>
            </div>
            <div>
                <button class="btn btn-secondary btn-sm" onclick="backToDashboard()">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </button>
            </div>
        </div>
    </div>
@stop

@section('content')

    {{-- ================================================================ --}}
    {{-- DASHBOARD VIEW                                                    --}}
    {{-- ================================================================ --}}
    <div id="dashboardView">

        {{-- Status Summary Cards --}}
        <div class="row mb-3">
            <div class="col-6 col-sm-3">
                <div class="info-box shadow-sm" style="cursor:pointer;" onclick="filterTable('In Progress')">
                    <span class="info-box-icon bg-warning"><i class="fas fa-spinner"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">In Progress</span>
                        <span class="info-box-number" id="countInProgress">0</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="info-box shadow-sm" style="cursor:pointer;" onclick="filterTable('Submitted')">
                    <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Submitted</span>
                        <span class="info-box-number" id="countSubmitted">0</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="info-box shadow-sm" style="cursor:pointer;" onclick="filterTable('Failed')">
                    <span class="info-box-icon bg-danger"><i class="fas fa-times-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Failed</span>
                        <span class="info-box-number" id="countFailed">0</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="info-box shadow-sm" style="cursor:pointer;" onclick="filterTable('All')">
                    <span class="info-box-icon" style="background-color:#4B49AC;"><i class="fas fa-list"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total</span>
                        <span class="info-box-number" id="countTotal">0</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Weight Notes Table --}}
        <div class="card card-primary">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-list mr-1"></i>
                    Weight Notes
                    <span id="activeFilterBadge" class="badge badge-light ml-2" style="display:none;"></span>
                </h3>
                <div class="d-flex align-items-center ml-auto" style="gap:8px;">
                    <input type="text" id="tableSearch" class="form-control form-control-sm" placeholder="Search..."
                        style="width:200px;">
                    <button class="btn btn-sm btn-outline-light" onclick="filterTable('All')" title="Clear filter">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="openForm('Single Job')">
                        <i class="fas fa-plus mr-1"></i> New
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm mb-0" id="weightNoteTable">
                        <thead class="thead-light">
                            <tr>
                                <th>Job No</th>
                                <th>Job Type</th>
                                <th>Client</th>
                                <th>Shipper</th>
                                <th>POL</th>
                                <th>POD</th>
                                <th>Total Weight</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="weightNoteTableBody">
                            <tr id="emptyRow">
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    No weight notes found. Click <strong>New</strong> to create one.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- FORM VIEW                                                         --}}
    {{-- ================================================================ --}}
    <div id="formView" style="display:none;">

        <div class="row">

            {{-- LEFT COLUMN --}}
            <div class="col-md-4">

                {{-- Job Details Card --}}
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Job Details</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-chevron-up"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Job No</label>
                            <div class="col-sm-7">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm" id="fldJobNo"
                                        placeholder="Auto-generated">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary btn-sm" type="button" title="Search">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Client <span class="text-danger">*</span></label>
                            <div class="col-sm-7">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm" id="fldClient"
                                        placeholder="Search client..." oninput="onClientInput(this)">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary btn-sm" type="button">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="clientDropdown" class="list-group position-absolute shadow"
                                    style="display:none; z-index:9999; width:calc(100% - 30px); max-height:150px; overflow-y:auto;">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Shipper</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" id="fldShipper"
                                    placeholder="Shipper name">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Consignee</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" id="fldConsignee"
                                    placeholder="Consignee name">
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-sm-5 col-form-label">Forwarder</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" id="fldForwarder"
                                    placeholder="Forwarder name">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Weight Summary Card --}}
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Weight Summary</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-chevron-up"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Total Weight (kg)</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm bg-light"
                                    id="summaryTotalWeight" value="0.00" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Tare (kg)</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm bg-light" id="summaryTare"
                                    value="0.00" readonly>
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-sm-5 col-form-label">Net Weight (kg)</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm bg-light font-weight-bold"
                                    id="summaryNetWeight" value="0.00" readonly>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN --}}
            <div class="col-md-8">

                {{-- Product Entry Card — hidden by default, toggled by "Add" button --}}
                <div class="card card-primary" id="productEntryCard" style="display:none;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Product Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Product <span
                                            class="text-danger">*</span></label>
                                    <div class="col-sm-8 position-relative">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control form-control-sm" id="fldProduct"
                                                placeholder="Search product..." oninput="onProductInput(this)">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            </div>
                                        </div>
                                        <div id="productDropdown" class="list-group position-absolute shadow"
                                            style="display:none; z-index:9999; width:100%; max-height:150px; overflow-y:auto;">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Job No</label>
                                    <div class="col-sm-8">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control form-control-sm"
                                                id="fldProductJobNo" placeholder="Job no">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Description</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm" id="fldDescription"
                                            placeholder="Description">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Quantity</label>
                                    <div class="col-sm-8">
                                        <div class="input-group input-group-sm">
                                            <input type="number" class="form-control form-control-sm" id="fldProdQty"
                                                placeholder="0" min="0">
                                            <div class="input-group-append">
                                                <select class="form-control form-control-sm" id="fldProdUOM"
                                                    style="border-left:0;">
                                                    <option value="PCS">PCS</option>
                                                    <option value="KGS">KGS</option>
                                                    <option value="CBM">CBM</option>
                                                    <option value="CTN">CTN</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row mb-0">
                                    <label class="col-sm-4 col-form-label">Dimensions</label>
                                    <div class="col-sm-8">
                                        <div class="d-flex" style="gap:4px;">
                                            <input type="number" class="form-control form-control-sm" id="fldDimL"
                                                placeholder="L" min="0" style="width:60px;">
                                            <input type="number" class="form-control form-control-sm" id="fldDimW"
                                                placeholder="W" min="0" style="width:60px;">
                                            <input type="number" class="form-control form-control-sm" id="fldDimH"
                                                placeholder="H" min="0" style="width:60px;">
                                            <select class="form-control form-control-sm" id="fldDimUnit">
                                                <option value="cm">cm</option>
                                                <option value="m">m</option>
                                                <option value="in">in</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-5 col-form-label">Equipment Type</label>
                                    <div class="col-sm-7">
                                        <select class="form-control form-control-sm" id="fldEquipType">
                                            <option value="">-- Select --</option>
                                            <option value="20GP">20GP</option>
                                            <option value="40GP">40GP</option>
                                            <option value="40HC">40HC</option>
                                            <option value="45HC">45HC</option>
                                            <option value="20RF">20RF</option>
                                            <option value="40RF">40RF</option>
                                            <option value="20OT">20OT</option>
                                            <option value="40OT">40OT</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-5 col-form-label">Equip No</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control form-control-sm" id="fldEquipNo"
                                            placeholder="Equipment number">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-5 col-form-label">Tare Weight (kg)</label>
                                    <div class="col-sm-7">
                                        <input type="number" class="form-control form-control-sm" id="fldTareWeight"
                                            placeholder="0.00" min="0" step="0.01" oninput="recalcWeights()">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-5 col-form-label">Gross Weight (kg)</label>
                                    <div class="col-sm-7">
                                        <input type="number" class="form-control form-control-sm" id="fldGrossWeight"
                                            placeholder="0.00" min="0" step="0.01" oninput="recalcWeights()">
                                    </div>
                                </div>
                                <div class="form-group row mb-0">
                                    <label class="col-sm-5 col-form-label">Net Weight (kg)</label>
                                    <div class="col-sm-7">
                                        <input type="number" class="form-control form-control-sm bg-light"
                                            id="fldNetWeight" placeholder="0.00" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Save / Cancel buttons replace the old "Add to Grid" header button --}}
                        <div class="d-flex justify-content-end mt-3" style="gap:8px;">
                            <button class="btn btn-secondary btn-sm"
                                onclick="event.stopPropagation(); cancelProductEntry()">
                                <i class="fas fa-times mr-1"></i> Cancel
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); saveProductToGrid()">
                                <i class="fas fa-check mr-1"></i> Save Product
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Products Grid Card --}}
                <div class="card card-primary">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-table mr-1"></i> Products
                            <span class="badge badge-light ml-1" id="productCount">0</span>
                        </h3>
                        <button class="btn btn-light btn-sm ml-auto" id="btnShowProductEntry"
                            onclick="event.stopPropagation(); showProductEntry();" disabled
                            title="Select a client first to add products">
                            <i class="fas fa-plus mr-1"></i> Add
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th>Equipment Type</th>
                                        <th>Equip No</th>
                                        <th class="text-right">Tare (kg)</th>
                                        <th class="text-right">Gross (kg)</th>
                                        <th class="text-right">Net (kg)</th>
                                        <th class="text-center">Remove</th>
                                    </tr>
                                </thead>
                                <tbody id="productGridBody">
                                    <tr id="productGridEmpty">
                                        <td colspan="8" class="text-center text-muted py-3">
                                            <i class="fas fa-box-open mr-1"></i> No products added yet
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="card">
                    <div class="card-body py-2 px-3 d-flex justify-content-end" style="gap:8px;">
                        <button class="btn btn-secondary btn-sm" onclick="backToDashboard()">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                        <button class="btn btn-warning btn-sm" id="btnSave" onclick="saveWeightNote('save')">
                            <i class="fas fa-save mr-1"></i> Save
                        </button>
                        <button class="btn btn-success btn-sm" id="btnSaveSubmit" onclick="saveWeightNote('submit')">
                            <i class="fas fa-paper-plane mr-1"></i> Save & Submit
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

@stop

@section('css')
    <style>
        /* ---- Brand colour ---- */
        :root {
            --brand: #4B49AC;
        }

        .card-primary>.card-header,
        .card.card-primary>.card-header {
            background-color: var(--brand) !important;
            color: #fff !important;
        }

        /* Info-box accent */
        .info-box-icon {
            min-width: 70px;
        }

        /* Form spacing */
        .form-group.row {
            margin-bottom: 0.45rem;
        }

        .card-body {
            padding: 1rem;
        }

        .table-sm th,
        .table-sm td {
            padding: 0.3rem 0.5rem;
            font-size: 0.85rem;
        }

        .thead-light th {
            background-color: #e9ecef;
            font-weight: 600;
            font-size: 0.82rem;
        }

        /* Card header collapse cursor */
        .card-header {
            cursor: pointer;
            user-select: none;
        }

        .card-header:hover {
            opacity: 0.95;
        }

        /* Disabled field tint */
        input:disabled,
        select:disabled {
            background-color: #f4f4f4 !important;
        }

        /* Client / product autocomplete dropdown */
        #clientDropdown .list-group-item,
        #productDropdown .list-group-item {
            padding: 6px 10px;
            font-size: 0.85rem;
            cursor: pointer;
            border-radius: 0;
        }

        #clientDropdown .list-group-item:hover,
        #productDropdown .list-group-item:hover {
            background-color: #e8e8f5;
            color: var(--brand);
        }

        /* Status badge colours */
        .badge-in-progress {
            background-color: #ffc107;
            color: #000;
        }

        .badge-submitted {
            background-color: #28a745;
            color: #fff;
        }

        .badge-failed {
            background-color: #dc3545;
            color: #fff;
        }

        /* Active filter badge */
        #activeFilterBadge {
            background: #e9ecef;
            color: #555;
            font-size: 0.75rem;
        }

        /* Remove row btn */
        .btn-remove-row {
            padding: 1px 7px;
            font-size: 0.75rem;
        }

        /* Summary fields */
        #summaryTotalWeight,
        #summaryTare,
        #summaryNetWeight {
            font-weight: 600;
            color: var(--brand);
        }

        /* Product entry card slide-in feel */
        #productEntryCard {
            border-top: 3px solid var(--brand);
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {

            // ================================================================
            // STATE
            // ================================================================
            let currentJobType = '';
            let weightNotes = []; // in-memory list for dashboard table
            let productRows = []; // products in current form
            let clientSelected = false;
            let editingIndex = null; // null = new, number = editing

            // Dummy client list (replace with AJAX when backend is ready)
            const MOCK_CLIENTS = [
                'ABC Logistics Ltd', 'Beta Freight Solutions', 'Cargo King Africa',
                'Delta Shipping Co', 'Eagle Forwarding', 'Fast Track Movers',
                'Global Trade Partners', 'Horizon Freight', 'Inland Express Ltd'
            ];

            // Dummy product list
            const MOCK_PRODUCTS = [
                'Steel Coils', 'Cement Bags', 'Rice Sacks', 'Electronics',
                'Machinery Parts', 'Chemical Drums', 'Textile Rolls', 'Auto Parts'
            ];

            // ================================================================
            // NAVIGATION
            // ================================================================
            window.openForm = function(jobType) {
                currentJobType = jobType;
                editingIndex = null;
                resetForm();

                $('#formJobTypeLabel').text(jobType);
                $('#pageHeaderDashboard').hide();
                $('#pageHeaderForm').show();
                $('#dashboardView').hide();
                $('#formView').show();
            };

            window.backToDashboard = function() {
                $('#pageHeaderForm').hide();
                $('#pageHeaderDashboard').show();
                $('#formView').hide();
                $('#dashboardView').show();
                refreshDashboardTable();
            };

            // ================================================================
            // CLIENT INPUT — unlock "Add" button on products grid
            // ================================================================
            window.onClientInput = function(input) {
                const val = $(input).val().trim();

                if (val.length >= 1) {
                    const matches = MOCK_CLIENTS.filter(c => c.toLowerCase().includes(val.toLowerCase()));
                    const $dd = $('#clientDropdown');
                    $dd.empty();
                    if (matches.length) {
                        matches.forEach(function(c) {
                            $dd.append(
                                $('<a class="list-group-item list-group-item-action">').text(c).on(
                                    'click',
                                    function() {
                                        $('#fldClient').val(c);
                                        $dd.hide();
                                        onClientConfirmed();
                                    })
                            );
                        });
                        $dd.show();
                    } else {
                        $dd.hide();
                    }
                } else {
                    $('#clientDropdown').hide();
                }

                if (val === '') {
                    lockProductSection();
                }
            };

            function onClientConfirmed() {
                clientSelected = true;
                // Enable the Add button on the products grid
                $('#btnShowProductEntry')
                    .prop('disabled', false)
                    .attr('title', 'Add a product');
            }

            // Also unlock if user types and tabs out
            $('#fldClient').on('blur', function() {
                if ($(this).val().trim() !== '') {
                    onClientConfirmed();
                }
            });

            // Hide dropdowns on outside click
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#fldClient, #clientDropdown').length) {
                    $('#clientDropdown').hide();
                }
                if (!$(e.target).closest('#fldProduct, #productDropdown').length) {
                    $('#productDropdown').hide();
                }
            });

            // ================================================================
            // PRODUCT ENTRY PANEL — show / hide
            // ================================================================
            window.showProductEntry = function() {
                clearProductFields();
                $('#productEntryCard').slideDown(200);
                // Scroll to it so user sees the form
                $('html, body').animate({
                    scrollTop: $('#productEntryCard').offset().top - 80
                }, 300);
                $('#fldProduct').focus();
            };

            window.cancelProductEntry = function() {
                $('#productEntryCard').slideUp(200);
                clearProductFields();
            };

            // ================================================================
            // PRODUCT INPUT — autocomplete
            // ================================================================
            window.onProductInput = function(input) {
                const val = $(input).val().trim();
                if (val.length >= 1) {
                    const matches = MOCK_PRODUCTS.filter(p => p.toLowerCase().includes(val.toLowerCase()));
                    const $dd = $('#productDropdown');
                    $dd.empty();
                    if (matches.length) {
                        matches.forEach(function(p) {
                            $dd.append(
                                $('<a class="list-group-item list-group-item-action">').text(p).on(
                                    'click',
                                    function() {
                                        $('#fldProduct').val(p);
                                        $dd.hide();
                                    })
                            );
                        });
                        $dd.show();
                    } else {
                        $dd.hide();
                    }
                } else {
                    $('#productDropdown').hide();
                }
            };

            // ================================================================
            // WEIGHT RECALCULATION
            // ================================================================
            window.recalcWeights = function() {
                const tare = parseFloat($('#fldTareWeight').val()) || 0;
                const gross = parseFloat($('#fldGrossWeight').val()) || 0;
                const net = gross - tare;
                $('#fldNetWeight').val(net >= 0 ? net.toFixed(2) : '0.00');
            };

            // ================================================================
            // SAVE PRODUCT TO GRID (replaces addProductToGrid)
            // ================================================================
            window.saveProductToGrid = function() {
                const product = $('#fldProduct').val().trim();
                const equipType = $('#fldEquipType').val();
                const tare = parseFloat($('#fldTareWeight').val()) || 0;
                const gross = parseFloat($('#fldGrossWeight').val()) || 0;
                const net = parseFloat($('#fldNetWeight').val()) || 0;

                if (!product) {
                    toastr.error('Please enter a product name.');
                    $('#fldProduct').focus();
                    return;
                }
                if (!equipType) {
                    toastr.error('Please select an Equipment Type.');
                    $('#fldEquipType').focus();
                    return;
                }
                if (gross <= 0) {
                    toastr.error('Gross Weight must be greater than 0.');
                    $('#fldGrossWeight').focus();
                    return;
                }

                productRows.push({
                    product: product,
                    equipType: equipType,
                    equipNo: $('#fldEquipNo').val().trim(),
                    tare: tare,
                    gross: gross,
                    net: net,
                    description: $('#fldDescription').val().trim(),
                    qty: $('#fldProdQty').val() || 0,
                    uom: $('#fldProdUOM').val(),
                });

                renderProductGrid();
                recalcSummary();

                // Hide entry panel and clear fields
                $('#productEntryCard').slideUp(200);
                clearProductFields();

                toastr.success('Product added to grid.');
            };

            function renderProductGrid() {
                const $body = $('#productGridBody');
                $body.empty();

                if (!productRows.length) {
                    $body.append(`
                        <tr id="productGridEmpty">
                            <td colspan="8" class="text-center text-muted py-3">
                                <i class="fas fa-box-open mr-1"></i> No products added yet
                            </td>
                        </tr>`);
                    $('#productCount').text('0');
                    return;
                }

                productRows.forEach(function(r, i) {
                    $body.append(`
                        <tr>
                            <td>${i + 1}</td>
                            <td>${r.product}</td>
                            <td>${r.equipType}</td>
                            <td>${r.equipNo || '—'}</td>
                            <td class="text-right">${r.tare.toFixed(2)}</td>
                            <td class="text-right">${r.gross.toFixed(2)}</td>
                            <td class="text-right">${r.net.toFixed(2)}</td>
                            <td class="text-center">
                                <button class="btn btn-danger btn-remove-row" onclick="removeProductRow(${i})" title="Remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>`);
                });

                $('#productCount').text(productRows.length);
            }

            window.removeProductRow = function(index) {
                productRows.splice(index, 1);
                renderProductGrid();
                recalcSummary();
            };

            function recalcSummary() {
                const totalGross = productRows.reduce((s, r) => s + r.gross, 0);
                const totalTare = productRows.reduce((s, r) => s + r.tare, 0);
                const totalNet = productRows.reduce((s, r) => s + r.net, 0);
                $('#summaryTotalWeight').val(totalGross.toFixed(2));
                $('#summaryTare').val(totalTare.toFixed(2));
                $('#summaryNetWeight').val(totalNet.toFixed(2));
            }

            // ================================================================
            // SAVE / SAVE & SUBMIT
            // ================================================================
            window.saveWeightNote = function(action) {
                const client = $('#fldClient').val().trim();
                if (!client) {
                    toastr.error('Client is required.');
                    $('#fldClient').focus();
                    return;
                }

                const $btn = action === 'submit' ? $('#btnSaveSubmit') : $('#btnSave');
                $btn.prop('disabled', true).prepend('<i class="fas fa-spinner fa-spin mr-1"></i>');

                // Simulate async save
                setTimeout(function() {
                    const status = action === 'submit' ? 'Submitted' : 'In Progress';

                    const note = {
                        jobNo: $('#fldJobNo').val().trim() || generateJobNo(),
                        jobType: currentJobType,
                        client: client,
                        shipper: $('#fldShipper').val().trim(),
                        pol: $('#fldPOL').val().trim(),
                        pod: $('#fldPOD').val().trim(),
                        totalWeight: $('#summaryTotalWeight').val(),
                        status: status,
                        products: [...productRows],
                    };

                    if (editingIndex !== null) {
                        weightNotes[editingIndex] = note;
                    } else {
                        weightNotes.push(note);
                    }

                    $btn.prop('disabled', false).find('.fa-spinner').remove();

                    toastr.success(action === 'submit' ?
                        'Weight note submitted successfully.' : 'Weight note saved.');
                    backToDashboard();
                }, 800);
            };

            // ================================================================
            // DASHBOARD TABLE
            // ================================================================
            let activeFilter = 'All';

            window.filterTable = function(status) {
                activeFilter = status;
                refreshDashboardTable();

                if (status !== 'All') {
                    $('#activeFilterBadge').text(status).show();
                } else {
                    $('#activeFilterBadge').hide();
                }
            };

            function refreshDashboardTable() {
                const counts = {
                    'In Progress': 0,
                    'Submitted': 0,
                    'Failed': 0
                };
                weightNotes.forEach(n => {
                    if (counts[n.status] !== undefined) counts[n.status]++;
                });
                $('#countInProgress').text(counts['In Progress']);
                $('#countSubmitted').text(counts['Submitted']);
                $('#countFailed').text(counts['Failed']);
                $('#countTotal').text(weightNotes.length);

                const search = $('#tableSearch').val().toLowerCase().trim();
                const $body = $('#weightNoteTableBody');
                $body.empty();

                const filtered = weightNotes.filter(function(n) {
                    const matchStatus = (activeFilter === 'All') || (n.status === activeFilter);
                    const matchSearch = !search ||
                        n.jobNo.toLowerCase().includes(search) ||
                        n.client.toLowerCase().includes(search) ||
                        (n.pol || '').toLowerCase().includes(search) ||
                        (n.pod || '').toLowerCase().includes(search);
                    return matchStatus && matchSearch;
                });

                if (!filtered.length) {
                    $body.append(`
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                No weight notes found.
                            </td>
                        </tr>`);
                    return;
                }

                filtered.forEach(function(n) {
                    const realIndex = weightNotes.indexOf(n);
                    const badgeClass = n.status === 'Submitted' ? 'badge-submitted' :
                        n.status === 'Failed' ? 'badge-failed' : 'badge-in-progress';
                    $body.append(`
                        <tr>
                            <td>${n.jobNo}</td>
                            <td>${n.jobType}</td>
                            <td>${n.client}</td>
                            <td>${n.shipper || '—'}</td>
                            <td>${n.pol     || '—'}</td>
                            <td>${n.pod     || '—'}</td>
                            <td class="text-right">${n.totalWeight} kg</td>
                            <td><span class="badge ${badgeClass}">${n.status}</span></td>
                            <td class="text-center">
                                <button class="btn btn-xs btn-info mr-1" onclick="viewWeightNote(${realIndex})" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                ${n.status !== 'Submitted' ? `
                                                            <button class="btn btn-xs btn-warning" onclick="editWeightNote(${realIndex})" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </button>` : ''}
                            </td>
                        </tr>`);
                });
            }

            // Live search
            $('#tableSearch').on('input', function() {
                refreshDashboardTable();
            });

            window.viewWeightNote = function(index) {
                const n = weightNotes[index];
                toastr.info(`Viewing: ${n.jobNo} — ${n.client}`);
            };

            window.editWeightNote = function(index) {
                const n = weightNotes[index];
                editingIndex = index;
                currentJobType = n.jobType;
                productRows = [...n.products];

                openForm(n.jobType);

                $('#fldJobNo').val(n.jobNo);
                $('#fldClient').val(n.client);
                $('#fldShipper').val(n.shipper);
                $('#fldPOL').val(n.pol);
                $('#fldPOD').val(n.pod);

                onClientConfirmed();
                renderProductGrid();
                recalcSummary();
            };

            // ================================================================
            // HELPERS
            // ================================================================
            function lockProductSection() {
                clientSelected = false;
                $('#btnShowProductEntry')
                    .prop('disabled', true)
                    .attr('title', 'Select a client first to add products');
                // Hide the entry panel if it was open
                $('#productEntryCard').hide();
                clearProductFields();
            }

            function clearProductFields() {
                $('#fldProduct, #fldProductJobNo, #fldDescription, #fldEquipNo').val('');
                $('#fldProdQty, #fldDimL, #fldDimW, #fldDimH').val('');
                $('#fldTareWeight, #fldGrossWeight, #fldNetWeight').val('');
                $('#fldEquipType').val('');
                $('#fldProdUOM').val('PCS');
                $('#fldDimUnit').val('cm');
            }

            function resetForm() {
                clientSelected = false;
                productRows = [];
                $('#fldJobNo, #fldClient, #fldShipper, #fldConsignee, #fldForwarder').val('');
                $('#fldPlaceReceipt, #fldPOL, #fldPOD, #fldPlaceDelivery').val('');
                $('#fldQuantity, #fldEquipment').val('');
                $('#summaryTotalWeight, #summaryTare, #summaryNetWeight').val('0.00');
                lockProductSection();
                renderProductGrid();
            }

            function generateJobNo() {
                const ts = Date.now().toString().slice(-6);
                return 'WN-' + ts;
            }

            // ================================================================
            // CARD COLLAPSE
            // ================================================================
            $('[data-card-widget="collapse"]').on('click', function(e) {
                e.stopPropagation();
                $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            });

            $('.card-header').on('click', function(e) {
                if (!$(e.target).closest('[data-card-widget="collapse"]').length) {
                    $(this).find('[data-card-widget="collapse"]').click();
                }
            });

            // Initial dashboard render
            refreshDashboardTable();
        });
    </script>
@endsection
