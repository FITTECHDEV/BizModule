@extends('adminlte::page')

@section('title', 'AR/AP Smart Invoices')

@section('css')
<style>
    .border-green  { border-color: #198754 !important; }
    .bg-zra-green  { background-color: #198754 !important; }
    .stat-card     { transition: transform .15s ease, box-shadow .15s ease; cursor: pointer; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.2) !important; }
</style>
@stop

@section('content_header')
    @php
        $officeFlags = [
            'KE' => 'kenya_flag.png',
            'ZM' => 'zambia-flag.png',
            'TZ' => 'tanzania_flag.png',
            'AR' => 'tanzania_flag.png',
            'UG' => 'uganda_flag.png',
            'RW' => 'rwanda_flag.png',
        ];

        $officeLabels = [
            'KE' => 'KRA Smart Invoice Dashboard',
            'ZM' => 'ZRA Smart Invoice Dashboard',
            'TZ' => 'TRA Smart Invoice Dashboard',
            'AR' => 'TRA Smart Invoice Dashboard',
            'UG' => 'EFRIS Smart Invoice Dashboard',
            'RW' => 'RRA Smart Invoice Dashboard',
        ];

        $flagFile  = $officeFlags[$activeOffice]  ?? 'zambia-flag.png';
        $pageTitle = $officeLabels[$activeOffice] ?? 'Smart Invoice Dashboard';
    @endphp

    <div class="d-flex align-items-center">
        <img src="{{ asset('site_imgs/' . $flagFile) }}"
             alt="{{ $activeOffice }}"
             style="height: 28px; border-radius: 3px;">
        <h4 class="mb-0 ml-2 font-weight-bold">{{ $pageTitle }}</h4>
    </div>
    <hr class="mt-2 mb-0">
@stop

@section('content')

{{-- Stat Cards --}}
<div class="row pt-2">
    {{-- Failed --}}
    <div class="col-12 col-sm-6 col-md-3">
        <a href="#" id="failed" style="text-decoration:none;">
            <div class="info-box mb-2 shadow stat-card border-bottom border-danger">
                <span class="info-box-icon bg-danger elevation-1">
                    <i class="fas fa-times-circle"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text text-center font-weight-bold">Failed</span>
                    <span class="info-box-number text-center text-danger font-weight-bold" id="failedStats">—</span>
                </div>
            </div>
        </a>
    </div>

    {{-- Pending --}}
    <div class="col-12 col-sm-6 col-md-3">
        <a href="#" id="pending" style="text-decoration:none;">
            <div class="info-box mb-2 shadow stat-card border-bottom border-warning">
                <span class="info-box-icon bg-warning elevation-1">
                    <i class="fas fa-hourglass-half"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text text-center font-weight-bold">Pending</span>
                    <span class="info-box-number text-center text-warning font-weight-bold" id="pendingStats">—</span>
                </div>
            </div>
        </a>
    </div>

    {{-- Submitted --}}
    <div class="col-12 col-sm-6 col-md-3">
        <a href="#" id="submitted" style="text-decoration:none;">
            <div class="info-box mb-2 shadow stat-card border-bottom border-green">
                <span class="info-box-icon bg-zra-green elevation-1">
                    <i class="fas fa-check-circle"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text text-center font-weight-bold">Submitted</span>
                    <span class="info-box-number text-center font-weight-bold" style="color:#198754;" id="submittedStats">—</span>
                </div>
            </div>
        </a>
    </div>

    {{-- Total --}}
    <div class="col-12 col-sm-6 col-md-3">
        <a href="#" id="allInvoices" style="text-decoration:none;">
            <div class="info-box mb-2 shadow stat-card border-bottom border-info">
                <span class="info-box-icon bg-info elevation-1">
                    <i class="fas fa-layer-group"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text text-center font-weight-bold">Total</span>
                    <span class="info-box-number text-center text-info font-weight-bold" id="totalStats">—</span>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- DataTable --}}
<div class="row pt-0">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <h6 class="mb-0 font-weight-bold text-muted">
                    <i class="fas fa-file-invoice mr-1"></i> Invoice Records
                </h6>
                <small class="text-muted" id="lastRefreshed"></small>
            </div>
            <div class="card-body table-responsive p-2">
                <table class="table table-bordered table-hover table-sm text-sm" id="zra-table">
                    <thead class="thead-light">
                        <tr>
                            <th>Doc No</th>
                            <th>Doc Type</th>
                            <th>Debtor Name</th>
                            <th>Doc Date</th>
                            <th>Status</th>
                            <th style="width:35%;">Remarks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Edit / Update PIN Modal --}}
<div class="modal fade" id="editModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
     aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header" style="background:linear-gradient(135deg,#198754,#0a3d1f);">
                <h5 class="modal-title text-white" id="editModalLabel">
                    <i class="fas fa-user-edit mr-1"></i> Update Customer TPIN
                </h5>
            </div>
            <div class="modal-body">
                <div id="modalAlert" class="alert d-none"></div>
                <form id="editForm">
                    <input type="hidden" name="InvoiceId"    id="InvoiceId">
                    <input type="hidden" name="ChangeOption" id="ChangeOption" value="CustomerID">

                    <div class="form-group">
                        <label for="LevelOption" class="font-weight-bold">Level Option</label>
                        <select class="custom-select" id="LevelOption" name="LevelOption">
                            <option value="">Choose...</option>
                            <option value="Master">Master</option>
                            <option value="Invoice">Invoice</option>
                        </select>
                        <div class="invalid-feedback">Please select a level option.</div>
                    </div>

                    <div class="form-group">
                        <label for="OptionValue" class="font-weight-bold">Customer TPIN</label>
                        <input type="text" name="OptionValue" id="OptionValue" class="form-control"
                               placeholder="Enter 10-digit TPIN">
                        <div class="invalid-feedback">Please enter a valid TPIN.</div>
                    </div>

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" id="cancelModal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-sm text-white" id="saveChanges"
                        style="background:#198754;">
                    <i class="fas fa-save mr-1"></i> Update TPIN
                </button>
            </div>
        </div>
    </div>
</div>

@stop

@section('js')
<script>
const ACTIVE_OFFICE = "{{ $activeOffice }}";

const ZRA_URLS = {
    datatable : `/datatables/zra?office=${ACTIVE_OFFICE}`,
    stats     : `/zra/get-stats?office=${ACTIVE_OFFICE}`,
    update    : '/zra/update-invoice',
    retrigger : (docNo) => `/zra/re-trigger/${docNo}`,
};

// ── DataTable ────────────────────────────────────────────────
const zra_table = $('#zra-table').DataTable({
    order         : [[3, 'desc']],
    orderCellsTop : true,
    fixedHeader   : true,
    processing    : true,
    serverSide    : true,
    autoWidth     : false,
    ajax          : ZRA_URLS.datatable,
    columns: [
        { data: 'DocNo',       name: 'DocNo'             },
        { data: 'DocType',     name: 'DocumentType'      },
        { data: 'DebtorName',  name: 'DebtorName'        },
        { data: 'DocDate',     name: 'DocDate'           },
        { data: 'DocStatus',   name: 'DocStatus'         },
        { data: 'Remarks',     name: 'SubmissionStatus'  },
        { data: 'Action',      name: 'Action', orderable: false, searchable: false },
    ],
    columnDefs: [{ width: '35%', targets: 5 }],
    drawCallback: function () {
        updateRefreshTime();
    },
});

// ── Stat card clicks ─────────────────────────────────────────
$('#failed'     ).on('click', () => filterTable('Failed'));
$('#pending'    ).on('click', () => filterTable('Pending'));
$('#submitted'  ).on('click', () => filterTable('Submitted'));
$('#allInvoices').on('click', () => filterTable(' '));

function filterTable(status) {
    zra_table.column(4).search(status, false, false, false).draw();
}

// ── Stats fetch + auto-refresh ────────────────────────────────
let refreshTimer;

function getStats() {
    clearTimeout(refreshTimer);
    $.get(ZRA_URLS.stats, function (data) {
        data.forEach(el => {
            $('#failedStats'   ).text(el.Failed);
            $('#pendingStats'  ).text(el.Pending);
            $('#submittedStats').text(el.Submitted);
            $('#totalStats'    ).text(el.Total);
        });
    }).fail(err => console.error('Stats fetch failed', err));

    zra_table.ajax.reload(null, false);
    refreshTimer = setTimeout(getStats, 30000);
}

function updateRefreshTime() {
    const now = new Date();
    $('#lastRefreshed').text('Last refreshed: ' + now.toLocaleTimeString());
}

// ── Modal helpers ─────────────────────────────────────────────
function editInvoice(id, action, docNo = null) {
    $('#InvoiceId').val(id);

    if (action === 'edit') {
        $('#modalAlert').addClass('d-none').text('');
        $('#editModal').modal('show');
        return;
    }

    if (action === 're-trigger') {
        Swal.fire({ title: 'Re-triggering…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        $.get(ZRA_URLS.retrigger(docNo), function (response) {
            Swal.fire('Done', response[0].Status + ' : ' + response[0].Status_Remark, 'info');
            getStats();
        }).fail(() => Swal.fire('Error', 'Re-trigger request failed.', 'error'));
    }
}

$('#cancelModal').on('click', function () {
    $('#editForm')[0].reset();
    $('#editModal').modal('hide');
    $('#saveChanges').prop('disabled', false);
});

// ── Form submission ───────────────────────────────────────────
$('#saveChanges').on('click', function () {
    let valid = true;

    $('#editForm [required], #LevelOption, #OptionValue').each(function () {
        const el = $(this);
        if (!el.attr('type')?.includes('hidden') && el.val() === '') {
            el.addClass('is-invalid');
            valid = false;
        } else {
            el.removeClass('is-invalid');
        }
    });

    if (!valid) return;

    const formData = $('#editForm').serializeArray();
    $('#saveChanges').prop('disabled', true);
    $('#editForm').css('opacity', '.5');

    $.ajax({
        url     : ZRA_URLS.update,
        type    : 'POST',
        data    : formData,
        dataType: 'json',
        success(response) {
            $('#editForm').css('opacity', '');
            $('#saveChanges').prop('disabled', false);
            Swal.fire('Updated', response[0].Status + ' : ' + response[0].Status_Remark, 'success');
            $('#cancelModal').click();
            getStats();
        },
        error(err) {
            $('#editForm').css('opacity', '');
            $('#saveChanges').prop('disabled', false);
            const msg = err.responseJSON?.errors
                ? JSON.stringify(err.responseJSON.errors, null, 2)
                : 'An unexpected error occurred.';
            $('#modalAlert').removeClass('d-none').addClass('alert-danger').text(msg);
        },
    });
});

// ── Boot ──────────────────────────────────────────────────────
filterTable('Failed');
getStats();
</script>
@stop
