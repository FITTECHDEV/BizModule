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
                <button class="btn btn-secondary btn-sm" id="btnBackToList">
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
                <div class="small-box bg-info" style="cursor:pointer;" id="cardInProgress">
                    <div class="inner">
                        <h3 id="countInProgress">0</h3>
                        <p>In Progress</p>
                    </div>
                    <div class="icon"><i class="fas fa-hourglass-half"></i></div>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="small-box bg-success" style="cursor:pointer;" id="cardSubmitted">
                    <div class="inner">
                        <h3 id="countSubmitted">0</h3>
                        <p>Submitted</p>
                    </div>
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="small-box bg-danger" style="cursor:pointer;" id="cardFailed">
                    <div class="inner">
                        <h3 id="countFailed">0</h3>
                        <p>Failed</p>
                    </div>
                    <div class="icon"><i class="fas fa-times-circle"></i></div>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="small-box bg-primary" style="cursor:pointer;" id="cardTotal">
                    <div class="inner">
                        <h3 id="countTotal">0</h3>
                        <p>Total</p>
                    </div>
                    <div class="icon"><i class="fas fa-list"></i></div>
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
                    <button class="btn btn-sm btn-outline-light" id="btnClearFilter" title="Clear filter">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button class="btn btn-primary btn-sm" id="btnNewNote">
                        <i class="fas fa-plus mr-1"></i> New
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm mb-0">
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
                <div class="card-tools ml-auto d-flex align-items-center" style="gap:6px;">
                    <button class="btn btn-light btn-sm" id="btnEditSummary" type="button" title="Edit equipment info">
                        <i class="fas fa-pencil-alt mr-1"></i> Edit
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-chevron-up"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Equipment Type</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm bg-light summary-field"
                                    id="displayEquipType" value="—" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Equipment No</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm bg-light summary-field"
                                    id="displayEquipNo" value="—" readonly>
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-sm-4 col-form-label">Remark</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm bg-light summary-field"
                                    id="displayRemark" value="—" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Tare Weight</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm bg-light summary-field"
                                    id="summaryTare" value="0.00" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Gross Weight</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm bg-light summary-field"
                                    id="summaryGross" value="0.00" readonly>
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-sm-4 col-form-label">Net Weight</label>
                            <div class="col-sm-8">
                                <input type="text"
                                    class="form-control form-control-sm bg-light summary-field font-weight-bold"
                                    id="summaryNet" value="0.00" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── WEIGHT NOTE DETAILS ── --}}
        <div class="card card-primary">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-table mr-1"></i> Weight Note Details
                    <span class="badge badge-light ml-1" id="productCount">0</span>
                </h3>
                <div class="card-tools ml-auto d-flex align-items-center" style="gap:6px;">
                    <button class="btn btn-success btn-sm" id="btnAddDetail" type="button">
                        <i class="fas fa-plus mr-1"></i> Add
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-chevron-up"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
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
            </div>
        </div>

        {{-- ── FORM ACTIONS ── --}}
        <div class="card">
            <div class="card-body py-2 px-3 d-flex justify-content-end" style="gap:8px;">
                <button class="btn btn-secondary btn-sm" id="btnCancelForm" type="button">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button class="btn btn-warning btn-sm" id="btnSave" type="button">
                    <i class="fas fa-save mr-1"></i> Save
                </button>
                <button class="btn btn-success btn-sm" id="btnSaveSubmit" type="button">
                    <i class="fas fa-paper-plane mr-1"></i> Save & Submit
                </button>
            </div>
        </div>

    </div>

    {{-- ================================================================ --}}
    {{-- DETAIL ENTRY CARD (replaces inline panel — floats as overlay card) --}}
    {{-- ================================================================ --}}
    <div id="detailCardOverlay" style="display:none;">
        <div id="detailCard" class="card card-primary detail-card-popup">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-plus-circle mr-1"></i>
                    <span id="detailCardTitle">Add Detail Row</span>
                </h3>
                <div class="card-tools ml-auto">
                    <button type="button" class="btn btn-sm text-white" id="btnCloseDetailCard" title="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">

                {{-- Row 1: Sr No, Job No, Client, Service --}}
                <div class="row mb-2">
                    <div class="col-md-6">
                        <div class="form-group row mb-2">
                            <label class="col-sm-4 col-form-label col-form-label-sm">Sr. No</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm bg-light" id="fldSrNo"
                                    readonly>
                            </div>
                        </div>
                        <div class="form-group row mb-2">
                            <label class="col-sm-4 col-form-label col-form-label-sm">Job No <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <select class="form-control form-control-sm" id="fldJobNo">
                                    <option value="">-- Select --</option>
                                    <option value="KE-2025-001">KE-2025-001</option>
                                    <option value="KE-2025-002">KE-2025-002</option>
                                    <option value="KE-2025-003">KE-2025-003</option>
                                    <option value="KE-2025-004">KE-2025-004</option>
                                    <option value="KE-2025-005">KE-2025-005</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row mb-2">
                            <label class="col-sm-4 col-form-label col-form-label-sm">Client Name</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm bg-light" id="fldClientName"
                                    readonly placeholder="Auto-filled">
                            </div>
                        </div>
                        <div class="form-group row mb-2">
                            <label class="col-sm-4 col-form-label col-form-label-sm">Service</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm bg-light" id="fldService"
                                    readonly placeholder="Auto-filled">
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-2">

                {{-- Row 2: Redesigned as single stacked layout matching the image --}}
                <div class="row mb-2">
                    <div class="col-12">

                        {{-- Product --}}
                        <div class="form-group row mb-2">
                            <label class="col-sm-2 col-form-label col-form-label-sm">
                                Product <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-10">
                                <select class="form-control form-control-sm" id="fldProduct">
                                    <option value="">-- Select --</option>
                                    <option value="electronics">Electronics</option>
                                    <option value="machinery_parts">Machinery Parts</option>
                                    <option value="textiles">Textiles</option>
                                    <option value="coffee_beans">Coffee Beans</option>
                                    <option value="tea_leaves">Tea Leaves</option>
                                    <option value="fresh_produce">Fresh Produce</option>
                                    <option value="pharmaceuticals">Pharmaceuticals</option>
                                    <option value="automotive_parts">Automotive Parts</option>
                                </select>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="form-group row mb-2">
                            <label class="col-sm-2 col-form-label col-form-label-sm">Description</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control form-control-sm" id="fldDescription"
                                    placeholder="Description">
                            </div>
                        </div>

                        {{-- Quantity + Dimension on same row --}}
                        <div class="form-group row mb-2 align-items-center">
                            <label class="col-sm-2 col-form-label col-form-label-sm">Quantity</label>
                            <div class="col-sm-2 pr-1">
                                <input type="number" class="form-control form-control-sm" id="fldQty"
                                    placeholder="0" min="0">
                            </div>
                            <div class="col-sm-1 pl-0">
                                <select class="form-control form-control-sm" id="fldQtyUOM">
                                    <option value="PCS">PCS</option>
                                    <option value="KGS">KGS</option>
                                    <option value="CBM">CBM</option>
                                    <option value="CTN">CTN</option>
                                </select>
                            </div>
                            <label class="col-sm-1 col-form-label col-form-label-sm text-center">Dimension</label>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center" style="gap:5px;">
                                    <input type="number" class="form-control form-control-sm dim-input" id="fldDimL"
                                        placeholder="L" min="0" style="width:70px;">
                                    <span class="text-muted">x</span>
                                    <input type="number" class="form-control form-control-sm dim-input" id="fldDimW"
                                        placeholder="W" min="0" style="width:70px;">
                                    <span class="text-muted">x</span>
                                    <input type="number" class="form-control form-control-sm dim-input" id="fldDimH"
                                        placeholder="H" min="0" style="width:70px;">
                                    <select class="form-control form-control-sm" id="fldDimUnit" style="width:68px;">
                                        <option value="cm">cm</option>
                                        <option value="m">m</option>
                                        <option value="in">in</option>
                                    </select>
                                    <button class="btn btn-sm btn-outline-success" id="btnClearDim" type="button"
                                        title="Clear dimensions">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" id="btnClearDimX" type="button"
                                        title="Clear dimensions">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Volume --}}
                        <div class="form-group row mb-2">
                            <label class="col-sm-2 col-form-label col-form-label-sm">Volume</label>
                            <div class="col-sm-2 pr-1">
                                <input type="text" class="form-control form-control-sm bg-light" id="fldVolume"
                                    value="0.000" readonly>
                            </div>
                            <div class="col-sm-1 pl-0">
                                <select class="form-control form-control-sm" id="fldVolumeUnit">
                                    <option value="CBM">CBM</option>
                                    <option value="CFT">CFT</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-2">

                        {{-- Tare Weight --}}
                        <div class="form-group row mb-2">
                            <label class="col-sm-2 col-form-label col-form-label-sm">Tare Weight</label>
                            <div class="col-sm-2 pr-1">
                                <input type="text" class="form-control form-control-sm bg-light" id="fldTareWeight"
                                    value="0.00" readonly>
                            </div>
                            <div class="col-sm-1 pl-0">
                                <select class="form-control form-control-sm" id="fldTareUnit">
                                    <option value="KG">KG</option>
                                    <option value="LB">LB</option>
                                </select>
                            </div>
                        </div>

                        {{-- Gross Weight + Read tare link inline --}}
                        <div class="form-group row mb-2">
                            <label class="col-sm-2 col-form-label col-form-label-sm">Gross Weight</label>
                            <div class="col-sm-2 pr-1">
                                <input type="number" class="form-control form-control-sm" id="fldGrossWeight"
                                    placeholder="0.00" min="0" step="0.01">
                            </div>
                            <div class="col-sm-1 pl-0">
                                <input type="text" class="form-control form-control-sm bg-light"
                                    id="fldGrossWeightUnit" value="KG" readonly>
                            </div>
                            <div class="col-sm-2 d-flex align-items-center pl-2">
                                <small>
                                    <a href="#" id="lnkReadTare" class="text-primary">
                                        <i class="fas fa-magic mr-1"></i>Read
                                    </a>
                                </small>
                            </div>
                        </div>

                        {{-- Net Weight --}}
                        <div class="form-group row mb-0">
                            <label class="col-sm-2 col-form-label col-form-label-sm">Net Weight</label>
                            <div class="col-sm-2 pr-1">
                                <input type="text" class="form-control form-control-sm bg-light font-weight-bold"
                                    id="fldNetWeight" value="0.00" readonly>
                            </div>
                            <div class="col-sm-1 pl-0">
                                <input type="text" class="form-control form-control-sm bg-light" id="fldNetWeightUnit"
                                    value="KG" readonly>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
            {{-- Card footer actions --}}
            <div class="card-footer d-flex justify-content-end" style="gap:8px; background:#f8f9fa;">
                <button class="btn btn-secondary btn-sm" id="btnCancelDetail" type="button">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button class="btn btn-primary btn-sm" id="btnSaveDetail" type="button">
                    <i class="fas fa-save mr-1"></i> Save
                </button>
                <button class="btn btn-info btn-sm" id="btnSaveAddMore" type="button">
                    <i class="fas fa-plus mr-1"></i> Save & Add More
                </button>
            </div>
        </div>
    </div>

    {{-- ── EQUIPMENT INFO MODAL ── --}}
    <div class="modal fade" id="equipInfoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color:#4B49AC; color:#fff; padding:0.6rem 1rem;">
                    <h6 class="modal-title mb-0">
                        <i class="fas fa-pencil-alt mr-1"></i> Equipment Info
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal" style="line-height:1;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-2">
                        <label class="col-form-label col-form-label-sm">Equipment Type</label>
                        <select class="form-control form-control-sm" id="modalEquipType">
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
                    <div class="form-group mb-2">
                        <label class="col-form-label col-form-label-sm">Equipment No</label>
                        <input type="text" class="form-control form-control-sm" id="modalEquipNo"
                            placeholder="Equipment number">
                    </div>
                    <div class="form-group mb-0">
                        <label class="col-form-label col-form-label-sm">Remark</label>
                        <input type="text" class="form-control form-control-sm" id="modalRemark"
                            placeholder="Remark">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" id="btnSaveEquipInfo">
                        <i class="fas fa-save mr-1"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── VIEW MODAL ── --}}
    <div class="modal fade" id="viewModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color:#4B49AC; color:#fff;">
                    <h5 class="modal-title">
                        <i class="fas fa-weight-hanging mr-2"></i>
                        Weight Note — <span id="modalWNNo"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Equipment Type</th>
                                <th>Equipment No</th>
                                <th>Tare (kg)</th>
                                <th>Gross (kg)</th>
                                <th>Net (kg)</th>
                                <th>Remark</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="modalSummaryBody"></tbody>
                    </table>
                    <hr class="my-0">
                    <div class="p-2">
                        <strong class="text-muted small">DETAILS</strong>
                    </div>
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Job No</th>
                                <th>Service</th>
                                <th>Client</th>
                                <th>Product</th>
                                <th class="text-right">Tare</th>
                                <th class="text-right">Gross</th>
                                <th class="text-right">Net</th>
                            </tr>
                        </thead>
                        <tbody id="modalDetailBody"></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@stop

@section('css')
    <style>
        :root {
            --brand: #4B49AC;
        }

        .card-primary>.card-header {
            background-color: var(--brand) !important;
            color: #fff !important;
        }

        .card-primary>.card-header .btn-tool {
            color: #fff !important;
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

        .form-group.row {
            margin-bottom: 0.4rem;
        }

        .summary-field {
            font-weight: 600;
            color: var(--brand);
        }

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

        #activeFilterBadge {
            background: #e9ecef;
            color: #555;
            font-size: 0.75rem;
        }

        .small-box {
            margin-bottom: 0;
        }

        .btn-remove-row {
            padding: 1px 7px;
            font-size: 0.75rem;
        }

        /* ── Detail Card Overlay ── */
        #detailCardOverlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 1040;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            /* animate */
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        #detailCardOverlay.overlay-visible {
            opacity: 1;
        }

        .detail-card-popup {
            width: 100%;
            max-width: 1050px;
            max-height: 92vh;
            overflow-y: auto;
            margin: 0;
            box-shadow: 0 8px 32px rgba(75, 73, 172, 0.25);
            /* animate card sliding up */
            transform: translateY(30px);
            transition: transform 0.25s ease;
        }

        .detail-card-popup .card-body {
            padding: 1.25rem 1.5rem;
        }

        .detail-card-popup .form-group.row {
            margin-bottom: 0.65rem;
        }

        .detail-card-popup .col-form-label-sm {
            font-size: 0.83rem;
            font-weight: 600;
            color: #444;
        }

        .detail-card-popup hr {
            margin: 0.85rem 0;
        }

        /* Give dimension inputs more room */
        .detail-card-popup .dim-input {
            width: 70px !important;
        }

        #detailCardOverlay.overlay-visible .detail-card-popup {
            transform: translateY(0);
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {

            // ================================================================
            // REFRESH GUARD
            // ================================================================
            $('#formView').hide();
            $('#dashboardView').show();
            $('#detailCardOverlay').hide();

            // ================================================================
            // CONSTANTS — hardcoded for presentation
            // ================================================================
            const JOB_MAP = {
                'KE-2025-001': {
                    client: 'Maersk Kenya',
                    service: 'FCL Export'
                },
                'KE-2025-002': {
                    client: 'DHL Logistics',
                    service: 'LCL Import'
                },
                'KE-2025-003': {
                    client: 'Bollore Transport',
                    service: 'FCL Import'
                },
                'KE-2025-004': {
                    client: 'Siginon Freight',
                    service: 'Air Freight'
                },
                'KE-2025-005': {
                    client: 'Kenya Airways Cargo',
                    service: 'Air Export'
                },
            };

            // Standard ISO tare weights in KG
            const TARE_WEIGHTS = {
                '20GP': 2200,
                '40GP': 3750,
                '40HC': 3900,
                '45HC': 4800,
                '20RF': 2800,
                '40RF': 4400,
                '20OT': 2100,
                '40OT': 3800,
            };

            // ================================================================
            // PERSISTENCE HELPERS
            // ================================================================
            const STORAGE_KEY = 'wn_weight_notes';

            function persistNotes() {
                try {
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(weightNotes));
                } catch (e) {
                    console.error('Failed to persist weight notes:', e);
                }
            }

            function loadPersistedNotes() {
                try {
                    const raw = localStorage.getItem(STORAGE_KEY);
                    return raw ? JSON.parse(raw) : [];
                } catch (e) {
                    console.error('Failed to load persisted weight notes:', e);
                    return [];
                }
            }

            // ================================================================
            // STATE
            // ================================================================
            let weightNotes = loadPersistedNotes();
            let detailRows = [];
            let editingIndex = null;
            let currentWNNo = '';
            let activeFilter = 'All';

            // ================================================================
            // NAVIGATION
            // ================================================================
            $('#btnNewNote').on('click', function() {
                openForm();
            });
            $('#btnBackToList, #btnCancelForm').on('click', function() {
                backToDashboard();
            });

            function openForm() {
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
                $('#equipInfoModal').modal('show');
            }

            function backToDashboard() {
                closeDetailCard();
                $('#pageHeaderForm').hide();
                $('#pageHeaderDashboard').show();
                $('#formView').hide();
                $('#dashboardView').show();
                refreshDashboardTable();
            }

            // ================================================================
            // DETAIL CARD — open / close with animation
            // ================================================================
            function openDetailCard() {
                $('#detailCardOverlay').css('display', 'flex');
                // Trigger reflow so transition fires
                $('#detailCardOverlay')[0].offsetHeight;
                $('#detailCardOverlay').addClass('overlay-visible');
            }

            function closeDetailCard() {
                $('#detailCardOverlay').removeClass('overlay-visible');
                setTimeout(function() {
                    $('#detailCardOverlay').hide();
                    clearDetailFields();
                }, 220);
            }

            // Close on overlay backdrop click (not on the card itself)
            $('#detailCardOverlay').on('click', function(e) {
                if ($(e.target).is('#detailCardOverlay')) {
                    closeDetailCard();
                }
            });

            $(document).on('click', '#btnCloseDetailCard, #btnCancelDetail', function() {
                closeDetailCard();
            });

            // ================================================================
            // ADD DETAIL BUTTON
            // ================================================================
            $('#btnAddDetail').on('click', function() {
                clearDetailFields();
                $('#fldSrNo').val(currentWNNo + '-' + String(detailRows.length + 1).padStart(2, '0'));
                $('#detailCardTitle').text('Add Detail Row');
                openDetailCard();
                $('#fldJobNo').focus();
            });

            // ================================================================
            // EQUIPMENT INFO MODAL
            // ================================================================
            $('#btnEditSummary').on('click', function() {
                const currentType = $('#displayEquipType').val();
                const currentNo = $('#displayEquipNo').val();
                const currentRmk = $('#displayRemark').val();
                $('#modalEquipType').val(currentType === '—' ? '' : currentType);
                $('#modalEquipNo').val(currentNo === '—' ? '' : currentNo);
                $('#modalRemark').val(currentRmk === '—' ? '' : currentRmk);
                $('#equipInfoModal').modal('show');
            });

            $('#btnSaveEquipInfo').on('click', function() {
                const equipType = $('#modalEquipType').val();
                const equipNo = $('#modalEquipNo').val().trim();
                const remark = $('#modalRemark').val().trim();

                if (!equipType) {
                    toastr.error('Please select an Equipment Type.');
                    $('#modalEquipType').focus();
                    return;
                }
                if (!equipNo) {
                    toastr.error('Please enter an Equipment No.');
                    $('#modalEquipNo').focus();
                    return;
                }

                $('#displayEquipType').val(equipType);
                $('#displayEquipNo').val(equipNo);
                $('#displayRemark').val(remark || '—');
                $('#equipInfoModal').modal('hide');
                toastr.success('Equipment info updated.');
            });

            // ================================================================
            // JOB NO — auto-fill client & service from hardcoded map
            // ================================================================
            $('#fldJobNo').on('change', function() {
                const job = JOB_MAP[$(this).val()];
                if (job) {
                    $('#fldClientName').val(job.client);
                    $('#fldService').val(job.service);
                } else {
                    $('#fldClientName, #fldService').val('');
                }
            });

            // ================================================================
            // TARE — read from equipment type via hardcoded ISO table
            // ================================================================
            $('#lnkReadTare').on('click', function(e) {
                e.preventDefault();
                const equipType = $('#displayEquipType').val().replace('—', '').trim();
                if (!equipType) {
                    toastr.warning('Please set an Equipment Type in the summary first.');
                    return;
                }
                const tare = TARE_WEIGHTS[equipType];
                if (tare === undefined) {
                    toastr.warning('No tare data available for: ' + equipType);
                    return;
                }
                $('#fldTareWeight').val(tare.toFixed(2));
                recalcNetWeight();
                toastr.info('Tare weight loaded: ' + tare.toFixed(2) + ' KG (' + equipType + ')');
            });

            // ================================================================
            // GROSS WEIGHT — triggers net recalc
            // ================================================================
            $('#fldGrossWeight').on('input', function() {
                recalcNetWeight();
            });

            $('#fldTareUnit').on('change', function() {
                const unit = $(this).val();
                $('#fldNetWeightUnit').val(unit);
                $('#fldGrossWeightUnit').val(unit);
            });

            function recalcNetWeight() {
                const tare = parseFloat($('#fldTareWeight').val()) || 0;
                const gross = parseFloat($('#fldGrossWeight').val()) || 0;
                const net = gross - tare;

                $('#fldNetWeight').val(net.toFixed(2));

                // Flag invalid state — gross must be >= tare in real weighing
                if (gross > 0 && net < 0) {
                    $('#fldNetWeight').removeClass('bg-light').addClass('border-danger text-danger');
                } else {
                    $('#fldNetWeight').addClass('bg-light').removeClass('border-danger text-danger');
                }
            }

            // ================================================================
            // DIMENSIONS — recalc volume on any change
            // ================================================================
            $(document).on('input', '.dim-input', function() {
                recalcVolume();
            });
            $('#fldDimUnit, #fldVolumeUnit').on('change', function() {
                recalcVolume();
            });

            // x button — clears all dimension fields
            $(document).on('click', '#btnClearDimX', function() {
                $('#fldDimL, #fldDimW, #fldDimH').val('');
                $('#fldVolume').val('0.000');
            });

            // + button — triggers volume recalculation from current dimension inputs
            $(document).on('click', '#btnClearDim', function() {
                recalcVolume();
            });

            function recalcVolume() {
                const l = parseFloat($('#fldDimL').val()) || 0;
                const w = parseFloat($('#fldDimW').val()) || 0;
                const h = parseFloat($('#fldDimH').val()) || 0;
                const dimUnit = $('#fldDimUnit').val();
                const volUnit = $('#fldVolumeUnit').val();

                let cbm = l * w * h;
                if (dimUnit === 'cm') cbm = cbm / 1_000_000;
                else if (dimUnit === 'in') cbm = cbm * 0.0000163871;
                // 'm' is already CBM

                $('#fldVolume').val((volUnit === 'CFT' ? cbm * 35.3147 : cbm).toFixed(3));
            }

            // ================================================================
            // SAVE DETAIL ROW
            // ================================================================
            $('#btnSaveDetail').on('click', function() {
                if (!validateDetailRow()) return;
                pushDetailRow();
                renderDetailGrid();
                recalcSummaryTotals();
                closeDetailCard();
                toastr.success('Detail row added.');
            });

            $('#btnSaveAddMore').on('click', function() {
                if (!validateDetailRow()) return;
                pushDetailRow();
                renderDetailGrid();
                recalcSummaryTotals();
                // Keep card open — bump Sr. No for next entry
                clearDetailFields();
                $('#fldSrNo').val(currentWNNo + '-' + String(detailRows.length + 1).padStart(2, '0'));
                $('#fldJobNo').focus();
                toastr.success('Row added. Fill next entry.');
            });

            function validateDetailRow() {
                if (!$('#fldJobNo').val()) {
                    toastr.error('Please select a Job No.');
                    $('#fldJobNo').focus();
                    return false;
                }
                if (!$('#fldProduct').val()) {
                    toastr.error('Please select a Product.');
                    $('#fldProduct').focus();
                    return false;
                }
                const gross = parseFloat($('#fldGrossWeight').val()) || 0;
                if (gross <= 0) {
                    toastr.error('Gross Weight must be greater than 0.');
                    $('#fldGrossWeight').focus();
                    return false;
                }

                // NEW — block save if gross < tare
                const tare = parseFloat($('#fldTareWeight').val()) || 0;
                if (gross < tare) {
                    toastr.error('Gross Weight cannot be less than Tare Weight. Please check your values.');
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
                    volumeUnit: $('#fldVolumeUnit').val(),
                });
            }

            // ================================================================
            // DETAIL GRID — render
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
                            <td>${r.jobNo      || '—'}</td>
                            <td>${r.service    || '—'}</td>
                            <td>${r.clientName || '—'}</td>
                            <td>${r.product}</td>
                            <td class="text-right">${r.tare.toFixed(2)}</td>
                            <td class="text-right">${r.gross.toFixed(2)}</td>
                            <td class="text-right">${r.net.toFixed(2)}</td>
                 <td class="text-center">
    <button class="btn btn-xs btn-info mr-1 btn-download-row"
        data-index="${i}" title="Download">
        <i class="fas fa-download"></i>
    </button>
    <button class="btn btn-xs btn-warning mr-1 btn-edit-row"
        data-index="${i}" title="Edit">
        <i class="fas fa-edit"></i>
    </button>
    <button class="btn btn-xs btn-danger btn-remove-row"
        data-index="${i}" title="Delete">
        <i class="fas fa-trash"></i>
    </button>
</td>
                        </tr>`);
                });

                $('#productCount').text(detailRows.length);
            }

            // Edit row — pull row back out, repopulate card
            $(document).on('click', '.btn-edit-row', function() {
                const i = parseInt($(this).data('index'));
                const r = detailRows[i];

                detailRows.splice(i, 1);
                renderDetailGrid();
                recalcSummaryTotals();

                $('#fldSrNo').val(r.srNo);
                $('#fldJobNo').val(r.jobNo).trigger('change');
                $('#fldProduct').val(r.productVal);
                $('#fldDescription').val(r.description);
                $('#fldQty').val(r.qty);
                $('#fldQtyUOM').val(r.uom);
                $('#fldTareWeight').val(r.tare.toFixed(2));
                $('#fldGrossWeight').val(r.gross.toFixed(2)).trigger('input');
                $('#fldVolume').val(r.volume);
                $('#fldVolumeUnit').val(r.volumeUnit);

                $('#detailCardTitle').text('Edit Detail Row');
                openDetailCard();
            });

            // Remove row
            $(document).on('click', '.btn-remove-row', function() {
                detailRows.splice(parseInt($(this).data('index')), 1);
                renderDetailGrid();
                recalcSummaryTotals();
                toastr.info('Row removed.');
            });

            // Download detail row as printable HTML
            $(document).on('click', '.btn-download-row', function() {
                const i = parseInt($(this).data('index'));
                const r = detailRows[i];
                const equipType = $('#displayEquipType').val().replace('—', '').trim() || '—';
                const equipNo = $('#displayEquipNo').val().replace('—', '').trim() || '—';

                const html = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Weight Note Detail — ${currentWNNo}</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 13px; padding: 24px; color: #333; }
                h2 { color: #4B49AC; margin-bottom: 4px; }
                .sub { color: #888; margin-bottom: 20px; font-size: 12px; }
                table { width: 100%; border-collapse: collapse; margin-top: 12px; }
                th { background: #4B49AC; color: #fff; padding: 7px 10px; text-align: left; font-size: 12px; }
                td { padding: 6px 10px; border-bottom: 1px solid #e0e0e0; font-size: 12px; }
                .label { font-weight: 600; width: 160px; }
                .section { margin-top: 20px; font-weight: 700; color: #4B49AC; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
            </style>
        </head>
        <body>
            <h2>Weight Note Detail</h2>
            <div class="sub">${currentWNNo} &nbsp;|&nbsp; Sr. No: ${r.srNo}</div>

            <div class="section">Equipment</div>
            <table>
                <tr><td class="label">Equipment Type</td><td>${equipType}</td></tr>
                <tr><td class="label">Equipment No</td><td>${equipNo}</td></tr>
            </table>

            <div class="section">Job Info</div>
            <table>
                <tr><td class="label">Job No</td><td>${r.jobNo || '—'}</td></tr>
                <tr><td class="label">Client</td><td>${r.clientName || '—'}</td></tr>
                <tr><td class="label">Service</td><td>${r.service || '—'}</td></tr>
            </table>

            <div class="section">Cargo</div>
            <table>
                <tr><td class="label">Product</td><td>${r.product}</td></tr>
                <tr><td class="label">Description</td><td>${r.description || '—'}</td></tr>
                <tr><td class="label">Quantity</td><td>${r.qty} ${r.uom}</td></tr>
                <tr><td class="label">Volume</td><td>${r.volume} ${r.volumeUnit}</td></tr>
            </table>

            <div class="section">Weights</div>
            <table>
                <tr><td class="label">Tare Weight</td><td>${r.tare.toFixed(2)} KG</td></tr>
                <tr><td class="label">Gross Weight</td><td>${r.gross.toFixed(2)} KG</td></tr>
                <tr><td class="label">Net Weight</td><td><strong>${r.net.toFixed(2)} KG</strong></td></tr>
            </table>
        </body>
        </html>`;

                // Open in new tab — user can Ctrl+P / Save as PDF
                const win = window.open('', '_blank');
                win.document.write(html);
                win.document.close();
                win.focus();
                // Auto-trigger print dialog
                setTimeout(() => win.print(), 400);
            });

            // ================================================================
            // SUMMARY TOTALS
            // ================================================================
            function recalcSummaryTotals() {
                $('#summaryTare').val(detailRows.reduce((s, r) => s + r.tare, 0).toFixed(2));
                $('#summaryGross').val(detailRows.reduce((s, r) => s + r.gross, 0).toFixed(2));
                $('#summaryNet').val(detailRows.reduce((s, r) => s + r.net, 0).toFixed(2));
            }

            // ================================================================
            // SAVE / SAVE & SUBMIT
            // ================================================================
            $('#btnSave').on('click', function() {
                saveWeightNote('save');
            });
            $('#btnSaveSubmit').on('click', function() {
                saveWeightNote('submit');
            });

            function saveWeightNote(action) {
                if (!detailRows.length) {
                    toastr.error('Please add at least one detail row before saving.');
                    return;
                }

                const $btn = action === 'submit' ? $('#btnSaveSubmit') : $('#btnSave');
                $btn.prop('disabled', true).prepend('<i class="fas fa-spinner fa-spin mr-1 spin-icon"></i>');

                setTimeout(function() {
                    const note = {
                        wnNo: currentWNNo,
                        equipType: $('#displayEquipType').val().replace('—', '').trim(),
                        equipNo: $('#displayEquipNo').val().replace('—', '').trim(),
                        remark: $('#displayRemark').val().replace('—', '').trim(),
                        tare: $('#summaryTare').val(),
                        gross: $('#summaryGross').val(),
                        net: $('#summaryNet').val(),
                        status: action === 'submit' ? 'Submitted' : 'In Progress',
                        details: [...detailRows],
                    };

                    if (editingIndex !== null) {
                        weightNotes[editingIndex] = note;
                    } else {
                        weightNotes.push(note);
                    }

                    persistNotes();
                    $btn.prop('disabled', false).find('.spin-icon').remove();
                    toastr.success(action === 'submit' ? 'Weight note submitted.' : 'Weight note saved.');
                    backToDashboard();
                }, 800);
            }

            // ================================================================
            // DASHBOARD TABLE — render
            // ================================================================
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

                const filtered = weightNotes.filter(n => {
                    const matchStatus = activeFilter === 'All' || n.status === activeFilter;
                    const matchSearch = !search ||
                        n.wnNo.toLowerCase().includes(search) ||
                        (n.equipType || '').toLowerCase().includes(search) ||
                        (n.equipNo || '').toLowerCase().includes(search) ||
                        (n.remark || '').toLowerCase().includes(search);
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
                                <button class="btn btn-xs btn-info mr-1 btn-view-note"
                                    data-index="${realIndex}" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                ${n.status !== 'Submitted' ? `
                                                                                <button class="btn btn-xs btn-warning btn-edit-note"
                                                                                    data-index="${realIndex}" title="Edit">
                                                                                    <i class="fas fa-edit"></i>
                                                                                </button>` : ''}
                            </td>
                        </tr>`);
                });
            }

            // Dashboard filter cards
            $('#cardInProgress').on('click', function() {
                applyFilter('In Progress');
            });
            $('#cardSubmitted').on('click', function() {
                applyFilter('Submitted');
            });
            $('#cardFailed').on('click', function() {
                applyFilter('Failed');
            });
            $('#cardTotal').on('click', function() {
                applyFilter('All');
            });
            $('#btnClearFilter').on('click', function() {
                $('#tableSearch').val('');
                applyFilter('All');
            });
            $('#tableSearch').on('input', function() {
                refreshDashboardTable();
            });

            function applyFilter(status) {
                activeFilter = status;
                $('#activeFilterBadge').toggle(status !== 'All').text(status);
                refreshDashboardTable();
            }

            // View note modal
            $(document).on('click', '.btn-view-note', function() {
                const n = weightNotes[parseInt($(this).data('index'))];
                const badgeClass = n.status === 'Submitted' ? 'badge-submitted' :
                    n.status === 'Failed' ? 'badge-failed' :
                    'badge-in-progress';
                $('#modalWNNo').text(n.wnNo);
                $('#modalSummaryBody').html(`
                    <tr>
                        <td>${n.equipType || '—'}</td>
                        <td>${n.equipNo   || '—'}</td>
                        <td class="text-right">${n.tare} kg</td>
                        <td class="text-right">${n.gross} kg</td>
                        <td class="text-right">${n.net} kg</td>
                        <td>${n.remark || '—'}</td>
                        <td><span class="badge ${badgeClass}">${n.status}</span></td>
                    </tr>`);

                const $det = $('#modalDetailBody').empty();
                if (!n.details.length) {
                    $det.append('<tr><td colspan="8" class="text-center text-muted">No details</td></tr>');
                } else {
                    n.details.forEach(function(r, i) {
                        $det.append(`
                            <tr>
                                <td>${i + 1}</td>
                                <td>${r.jobNo      || '—'}</td>
                                <td>${r.service    || '—'}</td>
                                <td>${r.clientName || '—'}</td>
                                <td>${r.product}</td>
                                <td class="text-right">${r.tare.toFixed(2)}</td>
                                <td class="text-right">${r.gross.toFixed(2)}</td>
                                <td class="text-right">${r.net.toFixed(2)}</td>
                            </tr>`);
                    });
                }
                $('#viewModal').modal('show');
            });

            // Edit note
            $(document).on('click', '.btn-edit-note', function() {
                const i = parseInt($(this).data('index'));
                const n = weightNotes[i];
                editingIndex = i;
                currentWNNo = n.wnNo;
                detailRows = [...n.details];

                resetSummaryForm();
                $('#displayEquipType').val(n.equipType || '—');
                $('#displayEquipNo').val(n.equipNo || '—');
                $('#displayRemark').val(n.remark || '—');
                renderDetailGrid();
                recalcSummaryTotals();

                $('#formJobNoLabel').text(currentWNNo);
                $('#pageHeaderDashboard').hide();
                $('#pageHeaderForm').show();
                $('#dashboardView').hide();
                $('#formView').show();
            });

            // ================================================================
            // CARD COLLAPSE
            // ================================================================
            $(document).on('click', '[data-card-widget="collapse"]', function(e) {
                e.stopPropagation();
                $(this).closest('.card').find('.card-body').first().slideToggle(200);
                $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            });

            // ================================================================
            // HELPERS
            // ================================================================
            function resetSummaryForm() {
                $('#displayEquipType, #displayEquipNo, #displayRemark').val('—');
                $('#modalEquipType').val('');
                $('#modalEquipNo, #modalRemark').val('');
                $('#summaryTare, #summaryGross, #summaryNet').val('0.00');
            }

            function clearDetailFields() {
                $('#fldSrNo, #fldClientName, #fldService').val('');
                $('#fldJobNo, #fldProduct').val('');
                $('#fldDescription').val('');
                $('#fldQty').val('');
                $('#fldQtyUOM').val('PCS');
                $('#fldDimL, #fldDimW, #fldDimH').val('');
                $('#fldDimUnit').val('cm');
                $('#fldVolume').val('0.000');
                $('#fldVolumeUnit').val('CBM');
                $('#fldTareWeight').val('0.00');
                $('#fldTareUnit').val('KG');
                $('#fldGrossWeight').val('');
                $('#fldNetWeight').val('0.00');
                $('#fldNetWeightUnit, #fldGrossWeightUnit').val('KG');
            }

            function generateWNNo() {
                const yy = String(new Date().getFullYear()).slice(-2);
                const seq = String(weightNotes.length + 1).padStart(5, '0');
                return `KE-WN-${yy}-${seq}`;
            }

            // ================================================================
            // INIT
            // ================================================================
            refreshDashboardTable();
        });
    </script>
@endsection
