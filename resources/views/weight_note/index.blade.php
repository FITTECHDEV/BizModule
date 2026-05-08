@extends('adminlte::page')

@section('title', 'Weight Note')

@section('content_header')
    <div id="pageHeaderForm" style="display:none;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>
                    <i class="fas fa-file-alt mr-2"></i>
                    Weight Note &mdash;
                    <span id="formJobNoLabel" class="text-muted" style="font-size:1rem; font-weight:400;"></span>
                </h1>
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
            <div class="col-6 col-sm-3 col-lg">
                <div class="small-box bg-info" style="cursor:pointer;" onclick="filterTable('In Progress')">
                    <div class="inner">
                        <h3 id="countInProgress">0</h3>
                        <p>In Progress</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-3 col-lg">
                <div class="small-box bg-success" style="cursor:pointer;" onclick="filterTable('Submitted')">
                    <div class="inner">
                        <h3 id="countSubmitted">0</h3>
                        <p>Submitted</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-3 col-lg">
                <div class="small-box bg-danger" style="cursor:pointer;" onclick="filterTable('Failed')">
                    <div class="inner">
                        <h3 id="countFailed">0</h3>
                        <p>Failed</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-3 col-lg">
                <div class="small-box bg-primary" style="cursor:pointer;" onclick="filterTable('All')">
                    <div class="inner">
                        <h3 id="countTotal">0</h3>
                        <p>Total</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Weight Notes Table --}}
        <div class="card card-primary">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-list mr-1"></i> Weight Notes
                    <span id="activeFilterBadge" class="badge badge-light ml-2" style="display:none;"></span>
                </h3>
                <div class="d-flex align-items-center ml-auto" style="gap:8px;">
                    <input type="text" id="tableSearch" class="form-control form-control-sm" placeholder="Search..."
                        style="width:200px;">
                    <button class="btn btn-sm btn-outline-light" onclick="filterTable('All')" title="Clear filter">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="openForm()">
                        <i class="fas fa-plus mr-1"></i> New
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm mb-0" id="weightNoteTable">
                        <thead class="thead-light">
                            <tr>
                                <th>WN No</th>
                                <th>Equipment Type</th>
                                <th>Equipment No</th>
                                <th class="text-right">Tare (kg)</th>
                                <th class="text-right">Gross (kg)</th>
                                <th class="text-right">Net (kg)</th>
                                <th>Remark</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="weightNoteTableBody">
                            <tr>
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

        {{-- ── WEIGHT NOTE SUMMARY ── --}}
        <div class="card card-primary">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-clipboard-list mr-1"></i> Weight Note Summary
                </h3>
                <div class="card-tools ml-auto">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-chevron-up"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    {{-- Left column --}}
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Equipment Type</label>
                            <div class="col-sm-8">
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
                            <label class="col-sm-4 col-form-label">Equipment No</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="fldEquipNo"
                                    placeholder="Equipment number">
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-sm-4 col-form-label">Remark</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="fldRemark"
                                    placeholder="Remark">
                            </div>
                        </div>
                    </div>

                    {{-- Right column --}}
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Tare Weight</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm bg-light" id="summaryTare"
                                    value="0.00" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Gross Weight</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm bg-light" id="summaryGross"
                                    value="0.00" readonly>
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-sm-4 col-form-label">Net Weight</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm bg-light font-weight-bold"
                                    id="summaryNet" value="0.00" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── WEIGHT NOTE DETAILS (grid + inline form) ── --}}
        <div class="card card-primary">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-table mr-1"></i> Weight Note Details
                    <span class="badge badge-light ml-1" id="productCount">0</span>
                </h3>
                <div class="card-tools ml-auto d-flex align-items-center" style="gap:6px;">
                    <button class="btn btn-success btn-sm" id="btnAddDetail" onclick="showDetailForm()">
                        <i class="fas fa-plus mr-1"></i> Add
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-chevron-up"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">

                {{-- Grid --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Job No</th>
                                <th>Service</th>
                                <th>Client</th>
                                <th>Product</th>
                                <th class="text-right">Tare</th>
                                <th class="text-right">Gross Wt.</th>
                                <th class="text-right">Net Wt.</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="detailGridBody">
                            <tr id="detailGridEmpty">
                                <td colspan="9" class="text-center text-muted py-3">
                                    <i class="fas fa-box-open mr-1"></i> No details added yet
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Inline detail entry form — hidden by default --}}
                <div id="detailEntryPanel" style="display:none; border-top:2px solid #4B49AC;">
                    <div class="p-3">

                        {{-- Sr No + Client Name + Job No + Service --}}
                        <div class="row mb-2">
                            <div class="col-md-3">
                                <div class="form-group row mb-1">
                                    <label class="col-sm-5 col-form-label col-form-label-sm">Sr. No</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control form-control-sm bg-light"
                                            id="fldSrNo" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group row mb-1">
                                    <label class="col-sm-5 col-form-label col-form-label-sm">Client Name</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control form-control-sm bg-light"
                                            id="fldClientName" readonly placeholder="Auto-filled">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group row mb-1">
                                    <label class="col-sm-4 col-form-label col-form-label-sm">Job No</label>
                                    <div class="col-sm-8">
                                        <select class="form-control form-control-sm" id="fldJobNo"
                                            onchange="onJobNoChange(this)">
                                            <option value="">-- Select --</option>
                                            {{-- Populated via AJAX --}}
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group row mb-1">
                                    <label class="col-sm-4 col-form-label col-form-label-sm">Service</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm bg-light"
                                            id="fldService" readonly placeholder="Auto-filled">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-2">

                        {{-- Product + Description + Quantity + Dimension + Volume --}}
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <div class="form-group row mb-1">
                                    <label class="col-sm-4 col-form-label col-form-label-sm">
                                        Product <span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <select class="form-control form-control-sm" id="fldProduct">
                                            <option value="">-- Select --</option>
                                            {{-- Populated via AJAX --}}
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row mb-1">
                                    <label class="col-sm-4 col-form-label col-form-label-sm">Description</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm" id="fldDescription"
                                            placeholder="Description">
                                    </div>
                                </div>
                                <div class="form-group row mb-1">
                                    <label class="col-sm-4 col-form-label col-form-label-sm">Quantity</label>
                                    <div class="col-sm-8">
                                        <div class="input-group input-group-sm">
                                            <input type="number" class="form-control form-control-sm" id="fldQty"
                                                placeholder="0" min="0">
                                            <div class="input-group-append">
                                                <select class="form-control form-control-sm" id="fldQtyUOM"
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
                                <div class="form-group row mb-1">
                                    <label class="col-sm-4 col-form-label col-form-label-sm">Dimension</label>
                                    <div class="col-sm-8">
                                        <div class="d-flex align-items-center" style="gap:4px;">
                                            <input type="number" class="form-control form-control-sm" id="fldDimL"
                                                placeholder="L" min="0" style="width:55px;"
                                                oninput="recalcVolume()">
                                            <span>x</span>
                                            <input type="number" class="form-control form-control-sm" id="fldDimW"
                                                placeholder="W" min="0" style="width:55px;"
                                                oninput="recalcVolume()">
                                            <span>x</span>
                                            <input type="number" class="form-control form-control-sm" id="fldDimH"
                                                placeholder="H" min="0" style="width:55px;"
                                                oninput="recalcVolume()">
                                            <select class="form-control form-control-sm" id="fldDimUnit"
                                                style="width:60px;" onchange="recalcVolume()">
                                                <option value="cm">cm</option>
                                                <option value="m">m</option>
                                                <option value="in">in</option>
                                            </select>
                                            <button class="btn btn-sm btn-outline-success" title="Add dimension row"
                                                onclick="addDimensionRow()">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" title="Clear"
                                                onclick="clearDimensions()">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row mb-0">
                                    <label class="col-sm-4 col-form-label col-form-label-sm">Volume</label>
                                    <div class="col-sm-8">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control form-control-sm bg-light"
                                                id="fldVolume" value="0.000" readonly>
                                            <div class="input-group-append">
                                                <select class="form-control form-control-sm" id="fldVolumeUnit"
                                                    style="border-left:0;">
                                                    <option value="CBM">CBM</option>
                                                    <option value="CFT">CFT</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group row mb-1">
                                    <label class="col-sm-4 col-form-label col-form-label-sm">Tare Weight</label>
                                    <div class="col-sm-8">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control form-control-sm bg-light"
                                                id="fldTareWeight" value="0.00" readonly>
                                            <div class="input-group-append">
                                                <select class="form-control form-control-sm" id="fldTareUnit"
                                                    style="border-left:0;">
                                                    <option value="KG">KG</option>
                                                    <option value="LB">LB</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row mb-1">
                                    <label class="col-sm-4 col-form-label col-form-label-sm">Gross Weight</label>
                                    <div class="col-sm-8">
                                        <div class="input-group input-group-sm">
                                            <input type="number" class="form-control form-control-sm"
                                                id="fldGrossWeight" placeholder="0.00" min="0" step="0.01"
                                                oninput="recalcNetWeight()">
                                            <div class="input-group-append">
                                                <input type="text" class="form-control form-control-sm bg-light"
                                                    id="fldGrossWeightReadonly" readonly style="width:70px;">
                                            </div>
                                        </div>
                                        <small>
                                            <a href="#" class="text-primary"
                                                onclick="readTareFromEquipment(); return false;">
                                                Read
                                            </a>
                                        </small>
                                    </div>
                                </div>
                                <div class="form-group row mb-0">
                                    <label class="col-sm-4 col-form-label col-form-label-sm">Net Weight</label>
                                    <div class="col-sm-8">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control form-control-sm bg-light"
                                                id="fldNetWeight" value="0.00" readonly>
                                            <div class="input-group-append">
                                                <input type="text" class="form-control form-control-sm bg-light"
                                                    id="fldNetWeightReadonly" readonly style="width:70px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Detail form actions --}}
                        <div class="d-flex justify-content-end mt-2" style="gap:8px;">
                            <button class="btn btn-secondary btn-sm" onclick="cancelDetailEntry()">
                                <i class="fas fa-times mr-1"></i> Cancel
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="saveDetailRow()">
                                <i class="fas fa-save mr-1"></i> Save
                            </button>
                            <button class="btn btn-info btn-sm" onclick="saveAndAddMore()">
                                <i class="fas fa-plus mr-1"></i> Save & Add More
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- ── FORM ACTIONS ── --}}
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

@stop

@section('css')
    <style>
        :root {
            --brand: #4B49AC;
        }

        /* Card header brand colour */
        .card-primary>.card-header {
            background-color: var(--brand) !important;
            color: #fff !important;
        }

        .card-primary>.card-header .btn-tool {
            color: #fff !important;
        }

        /* Table */
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

        /* Form spacing */
        .form-group.row {
            margin-bottom: 0.4rem;
        }

        /* Summary readonly fields */
        #summaryTare,
        #summaryGross,
        #summaryNet {
            font-weight: 600;
            color: var(--brand);
        }

        /* Detail entry panel */
        #detailEntryPanel {
            background-color: #fafafa;
        }

        /* Status badges */
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

        /* Remove row btn */
        .btn-remove-row {
            padding: 1px 7px;
            font-size: 0.75rem;
        }

        /* Active filter badge */
        #activeFilterBadge {
            background: #e9ecef;
            color: #555;
            font-size: 0.75rem;
        }

        /* Card header cursor */
        .card-header {
            cursor: default;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {

            // ================================================================
            // STATE
            // ================================================================
            let weightNotes = []; // dashboard list
            let detailRows = []; // rows in current form
            let editingIndex = null; // null = new note, number = editing
            let currentWNNo = '';
            let activeFilter = 'All';

            // ================================================================
            // NAVIGATION
            // ================================================================
            window.openForm = function() {
                editingIndex = null;
                detailRows = [];
                currentWNNo = generateWNNo();

                resetSummaryForm();
                renderDetailGrid();
                recalcSummaryTotals();

                $('#formJobNoLabel').text(currentWNNo);
                $('#pageHeaderDashboard').hide();
                $('#pageHeaderForm').show();
                $('#dashboardView').hide();
                $('#formView').show();
                $('#detailEntryPanel').hide();
            };

            window.backToDashboard = function() {
                $('#pageHeaderForm').hide();
                $('#pageHeaderDashboard').show();
                $('#formView').hide();
                $('#dashboardView').show();
                refreshDashboardTable();
            };

            // ================================================================
            // DETAIL ENTRY PANEL — show / hide
            // ================================================================
            window.showDetailForm = function() {
                clearDetailFields();
                const srNo = currentWNNo + '-' + String(detailRows.length + 1).padStart(2, '0');
                $('#fldSrNo').val(srNo);
                $('#detailEntryPanel').slideDown(200);
                $('html, body').animate({
                    scrollTop: $('#detailEntryPanel').offset().top - 80
                }, 300);
            };

            window.cancelDetailEntry = function() {
                $('#detailEntryPanel').slideUp(200);
                clearDetailFields();
            };

            // ================================================================
            // JOB NO CHANGE — auto-fill Client & Service
            // ================================================================
            window.onJobNoChange = function(select) {
                const val = $(select).val();
                if (!val) {
                    $('#fldClientName').val('');
                    $('#fldService').val('');
                    return;
                }
                // TODO: Replace with AJAX call to fetch job details by job no
                // Example:
                // $.get('/api/jobs/' + val, function (res) {
                //     if (res.success) {
                //         $('#fldClientName').val(res.data.client_name);
                //         $('#fldService').val(res.data.service);
                //     }
                // });
            };

            // ================================================================
            // WEIGHT RECALCULATION
            // ================================================================
            window.recalcNetWeight = function() {
                const tare = parseFloat($('#fldTareWeight').val()) || 0;
                const gross = parseFloat($('#fldGrossWeight').val()) || 0;
                const net = gross >= tare ? (gross - tare) : 0;

                $('#fldNetWeight').val(net.toFixed(2));
                $('#fldNetWeightReadonly').val(net.toFixed(2));
                $('#fldGrossWeightReadonly').val(gross.toFixed(2));
            };

            // Read tare from the summary equipment fields
            window.readTareFromEquipment = function() {
                // TODO: AJAX call to read tare from equipment type/no
                // For now, reads from summary tare field
                const tare = parseFloat($('#summaryTare').val()) || 0;
                $('#fldTareWeight').val(tare.toFixed(2));
                recalcNetWeight();
            };

            // ================================================================
            // VOLUME RECALCULATION
            // ================================================================
            window.recalcVolume = function() {
                const l = parseFloat($('#fldDimL').val()) || 0;
                const w = parseFloat($('#fldDimW').val()) || 0;
                const h = parseFloat($('#fldDimH').val()) || 0;
                const unit = $('#fldDimUnit').val();

                let cbm = (l * w * h);

                // Convert to CBM based on unit
                if (unit === 'cm') cbm = cbm / 1000000;
                else if (unit === 'in') cbm = cbm * 0.0000163871;
                // 'm' is already CBM

                $('#fldVolume').val(cbm.toFixed(3));
            };

            window.addDimensionRow = function() {
                // Placeholder — extend for multi-dimension rows if needed
                toastr.info('Multi-dimension rows coming soon.');
            };

            window.clearDimensions = function() {
                $('#fldDimL, #fldDimW, #fldDimH').val('');
                $('#fldVolume').val('0.000');
            };

            // ================================================================
            // SAVE DETAIL ROW
            // ================================================================
            window.saveDetailRow = function() {
                if (!validateDetailRow()) return;
                pushDetailRow();
                renderDetailGrid();
                recalcSummaryTotals();
                $('#detailEntryPanel').slideUp(200);
                clearDetailFields();
                toastr.success('Detail row added.');
            };

            window.saveAndAddMore = function() {
                if (!validateDetailRow()) return;
                pushDetailRow();
                renderDetailGrid();
                recalcSummaryTotals();
                // Keep panel open, refresh Sr No
                const srNo = currentWNNo + '-' + String(detailRows.length + 1).padStart(2, '0');
                clearDetailFields();
                $('#fldSrNo').val(srNo);
                toastr.success('Row added. Fill next entry.');
            };

            function validateDetailRow() {
                const product = $('#fldProduct').val();
                const gross = parseFloat($('#fldGrossWeight').val()) || 0;

                if (!product) {
                    toastr.error('Please select a Product.');
                    $('#fldProduct').focus();
                    return false;
                }
                if (gross <= 0) {
                    toastr.error('Gross Weight must be greater than 0.');
                    $('#fldGrossWeight').focus();
                    return false;
                }
                return true;
            }

            function pushDetailRow() {
                detailRows.push({
                    srNo: $('#fldSrNo').val(),
                    jobNo: $('#fldJobNo').val(),
                    service: $('#fldService').val(),
                    clientName: $('#fldClientName').val(),
                    product: $('#fldProduct option:selected').text(),
                    productVal: $('#fldProduct').val(),
                    description: $('#fldDescription').val(),
                    qty: $('#fldQty').val() || 0,
                    uom: $('#fldQtyUOM').val(),
                    tare: parseFloat($('#fldTareWeight').val()) || 0,
                    gross: parseFloat($('#fldGrossWeight').val()) || 0,
                    net: parseFloat($('#fldNetWeight').val()) || 0,
                    volume: $('#fldVolume').val(),
                });
            }

            // ================================================================
            // RENDER DETAIL GRID
            // ================================================================
            function renderDetailGrid() {
                const $body = $('#detailGridBody');
                $body.empty();

                if (!detailRows.length) {
                    $body.append(`
                        <tr id="detailGridEmpty">
                            <td colspan="9" class="text-center text-muted py-3">
                                <i class="fas fa-box-open mr-1"></i> No details added yet
                            </td>
                        </tr>`);
                    $('#productCount').text('0');
                    return;
                }

                detailRows.forEach(function(r, i) {
                    $body.append(`
                        <tr>
                            <td>${i + 1}</td>
                            <td>${r.jobNo || '—'}</td>
                            <td>${r.service || '—'}</td>
                            <td>${r.clientName || '—'}</td>
                            <td>${r.product}</td>
                            <td class="text-right">${r.tare.toFixed(2)}</td>
                            <td class="text-right">${r.gross.toFixed(2)}</td>
                            <td class="text-right">${r.net.toFixed(2)}</td>
                            <td class="text-center">
                                <button class="btn btn-xs btn-warning mr-1" onclick="editDetailRow(${i})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-xs btn-danger btn-remove-row" onclick="removeDetailRow(${i})" title="Remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>`);
                });

                $('#productCount').text(detailRows.length);
            }

            window.removeDetailRow = function(index) {
                detailRows.splice(index, 1);
                renderDetailGrid();
                recalcSummaryTotals();
            };

            window.editDetailRow = function(index) {
                const r = detailRows[index];
                detailRows.splice(index, 1);
                renderDetailGrid();
                recalcSummaryTotals();

                $('#fldSrNo').val(r.srNo);
                $('#fldJobNo').val(r.jobNo);
                $('#fldService').val(r.service);
                $('#fldClientName').val(r.clientName);
                $('#fldProduct').val(r.productVal);
                $('#fldDescription').val(r.description);
                $('#fldQty').val(r.qty);
                $('#fldQtyUOM').val(r.uom);
                $('#fldTareWeight').val(r.tare.toFixed(2));
                $('#fldGrossWeight').val(r.gross.toFixed(2));
                $('#fldNetWeight').val(r.net.toFixed(2));
                $('#fldVolume').val(r.volume);

                $('#detailEntryPanel').slideDown(200);
                $('html, body').animate({
                    scrollTop: $('#detailEntryPanel').offset().top - 80
                }, 300);
            };

            // ================================================================
            // SUMMARY TOTALS
            // ================================================================
            function recalcSummaryTotals() {
                const totalTare = detailRows.reduce((s, r) => s + r.tare, 0);
                const totalGross = detailRows.reduce((s, r) => s + r.gross, 0);
                const totalNet = detailRows.reduce((s, r) => s + r.net, 0);
                $('#summaryTare').val(totalTare.toFixed(2));
                $('#summaryGross').val(totalGross.toFixed(2));
                $('#summaryNet').val(totalNet.toFixed(2));
            }

            // ================================================================
            // SAVE / SAVE & SUBMIT
            // ================================================================
            window.saveWeightNote = function(action) {
                if (!detailRows.length) {
                    toastr.error('Please add at least one detail row.');
                    return;
                }

                const $btn = action === 'submit' ? $('#btnSaveSubmit') : $('#btnSave');
                $btn.prop('disabled', true).prepend('<i class="fas fa-spinner fa-spin mr-1"></i>');

                // TODO: Replace setTimeout with AJAX POST to backend
                setTimeout(function() {
                    const status = action === 'submit' ? 'Submitted' : 'In Progress';

                    const note = {
                        wnNo: currentWNNo,
                        equipType: $('#fldEquipType').val(),
                        equipNo: $('#fldEquipNo').val().trim(),
                        remark: $('#fldRemark').val().trim(),
                        tare: $('#summaryTare').val(),
                        gross: $('#summaryGross').val(),
                        net: $('#summaryNet').val(),
                        status: status,
                        details: [...detailRows],
                    };

                    if (editingIndex !== null) {
                        weightNotes[editingIndex] = note;
                    } else {
                        weightNotes.push(note);
                    }

                    $btn.prop('disabled', false).find('.fa-spinner').remove();
                    toastr.success(action === 'submit' ? 'Weight note submitted.' :
                        'Weight note saved.');
                    backToDashboard();
                }, 800);
            };

            // ================================================================
            // DASHBOARD TABLE
            // ================================================================
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
                    const matchStatus = activeFilter === 'All' || n.status === activeFilter;
                    const matchSearch = !search ||
                        n.wnNo.toLowerCase().includes(search) ||
                        (n.equipNo || '').toLowerCase().includes(search);
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
                        n.status === 'Failed' ? 'badge-failed' :
                        'badge-in-progress';

                    $body.append(`
                        <tr>
                            <td>${n.wnNo}</td>
                            <td>${n.equipType || '—'}</td>
                            <td>${n.equipNo   || '—'}</td>
                            <td class="text-right">${n.tare} kg</td>
                            <td class="text-right">${n.gross} kg</td>
                            <td class="text-right">${n.net} kg</td>
                            <td>${n.remark || '—'}</td>
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

            $('#tableSearch').on('input', function() {
                refreshDashboardTable();
            });

            window.viewWeightNote = function(index) {
                const n = weightNotes[index];
                toastr.info(`Viewing: ${n.wnNo}`);
            };

            window.editWeightNote = function(index) {
                const n = weightNotes[index];
                editingIndex = index;
                currentWNNo = n.wnNo;
                detailRows = [...n.details];

                resetSummaryForm();
                $('#fldEquipType').val(n.equipType);
                $('#fldEquipNo').val(n.equipNo);
                $('#fldRemark').val(n.remark);

                renderDetailGrid();
                recalcSummaryTotals();

                $('#formJobNoLabel').text(currentWNNo);
                $('#pageHeaderDashboard').hide();
                $('#pageHeaderForm').show();
                $('#dashboardView').hide();
                $('#formView').show();
                $('#detailEntryPanel').hide();
            };

            // ================================================================
            // HELPERS
            // ================================================================
            function resetSummaryForm() {
                $('#fldEquipType').val('');
                $('#fldEquipNo, #fldRemark').val('');
                $('#summaryTare, #summaryGross, #summaryNet').val('0.00');
            }

            function clearDetailFields() {
                $('#fldSrNo, #fldClientName, #fldService').val('');
                $('#fldJobNo').val('');
                $('#fldProduct').val('');
                $('#fldDescription').val('');
                $('#fldQty').val('');
                $('#fldQtyUOM').val('PCS');
                $('#fldDimL, #fldDimW, #fldDimH').val('');
                $('#fldDimUnit').val('cm');
                $('#fldVolume').val('0.000');
                $('#fldTareWeight').val('0.00');
                $('#fldGrossWeight').val('');
                $('#fldGrossWeightReadonly').val('');
                $('#fldNetWeight, #fldNetWeightReadonly').val('0.00');
            }

            function generateWNNo() {
                const now = new Date();
                const yy = String(now.getFullYear()).slice(-2);
                const seq = String(weightNotes.length + 1).padStart(5, '0');
                return `KE-WN-${yy}-${seq}`;
            }

            // Card collapse
            $('[data-card-widget="collapse"]').on('click', function(e) {
                e.stopPropagation();
                $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            });

            // Initial render
            refreshDashboardTable();
        });
    </script>
@endsection
