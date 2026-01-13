@extends('adminlte::page')

@section('title', 'KRA Invoices')

@section('content_header')

@stop

@section('css')

@stop

@section('content')
<div class="row pt-1">
    <div class="col-12 col-sm-6 col-md-3">
        <a href="#" id="failed">
            <div class="info-box mb-1 border-bottom border-danger shadow-lg">
                <span class="info-box-icon bg-danger elevation-1 shadow-lg"><i class="fas fa-times"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-center">Failed</span>
                    <span class="info-box-number text-center" id="failedStats">
                        0
                    </span>
                </div>
                <!-- /.info-box-content -->
            </div>
        </a>
    </div>
    <!-- /.col -->
    <div class="col-12 col-sm-6 col-md-3">
        <a href="#" id="pending">
            <div class="info-box mb-1 border-bottom border-warning shadow-lg">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-center">Pending</span>
                    <span class="info-box-number text-center" id="pendingStats">
                        0
                    </span>
                </div>
                <!-- /.info-box-content -->
            </div>
        </a>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
    <!-- fix for small devices only -->
    <div class="clearfix hidden-md-up"></div>
    <!-- /.col -->
    <div class="col-12 col-sm-6 col-md-3">
        <a href="#" id="submitted">
            <div class="info-box mb-1 border-bottom border-success shadow-lg">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-thumbs-up"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-center">Submitted</span>
                    <span class="info-box-number text-center" id="submittedStats">
                        0
                    </span>
                </div>
                <!-- /.info-box-content -->
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <a href="#" id="allInvoiceCreds">
            <div class="info-box mb-1 border-bottom border-purple shadow-lg">
                <span class="info-box-icon bg-purple elevation-1"><i class="fa fa-list" aria-hidden="true"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-center">Total</span>
                    <span class="info-box-number text-center" id="totalStats">
                       0
                    </span>
                </div>
                <!-- /.info-box-content -->
            </div>
        </a>
        <!-- /.info-box -->
    </div>
</div>
<div class="row justify-content-center pt-0">
    <div class="col-12 col-sm-12 col-md-12 ">
        <div class="card table-responsive">
            <div class="card-body">
                <table class="table table-bordered text-sm" id="invcred-table">
                    <thead>
                        <th>DocNo</th>
                        <th>DocType</th>
                        <th>DebtorName</th>
                        <th>DocDate</th>
                        <th>DocStatus</th>
                        <th>Remarks</th>
                        <th>Action</th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade hide" id="editModal" data-backdrop="static" data-keyboard="false" role="dialog"
    aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Update Customer PIN</h5>
            </div>
            <div class="modal-body">
                <form id="editForm" class="editForm">
                    <input type="hidden" name="InvoiceId" id="InvoiceId">
                    <input type="hidden" name="ChangeOption" id="ChangeOption" value="CustomerID">

                    <div class="form-group">
                        <label for="LevelOption">Level Option</label>
                        <select class="custom-select" id="LevelOption" name="LevelOption">
                            <option value="" selected>Choose...</option>
                            <option value="Master">Master</option>
                            <option value="Invoice">Invoice</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="OptionValue">Customer PIN</label>
                        <input type="text" name="OptionValue" id="OptionValue" class="form-control" placeholder="Enter Customer PIN">
                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="cancelModal">Cancel</button>
                <input type="submit" class="btn btn-primary" id="saveChanges" value="Update PIN">
            </div>
        </div>
    </div>
</div>


@stop


@section('js')
<script>
let url = 'datatables/tims';
var invcred_table = $('#invcred-table').DataTable({
    "order": [
        [3, "desc"]
    ],
    orderCellsTop: true,
    fixedHeader: true,
    processing: true,
    serverSide: true,
    autosave: true,
    "autoWidth": false,
    "columnDefs": [{
        "width": "40%",
        "targets": 5
    }],
    ajax: url,
    columns: [{
            data: 'DocNo',
            name: 'DocNo'
        },
        {
            data: 'DocType',
            name: 'DocumentType'
        },
        {
            data: 'DebtorName',
            name: 'DebtorName'
        },
        {
            data: 'DocDate',
            name: 'DocDate'
        },
        {
            data: 'DocStatus',
            name: 'DocStatus'
        },
        {
            data: 'Remarks',
            name: 'SubmissionStatus'
        },
        {
            data: 'Action',
            name: 'Action',
            orderable: false,
            searchable: false
        }
    ],
});

$('#cancelModal').click(function() {
    $('#editForm')[0].reset();
    $('#editModal').modal('hide');
    $('#saveChanges').removeAttr('disabled');
});

function editInvoice(id, action, docNo = null) {
    //append id to modal form
    $('#InvoiceId').val(id);
    if (action === 'edit') {
        // show modal
        $('#editModal').modal('show');
    }
    if (action === 're-trigger') {
        $.ajax({
            url: '/tims/re-trigger/' + docNo,
            type: 'GET',
            datatype: 'json',
            success: function(response) {
                alert(response[0].Status + " : " + response[0].Status_Remark);
                refresh();
            }
        });
    }
}

let submit = false;

// form submission
$('#saveChanges').click(function() {
    let formData = $('#editForm').serializeArray();

    console.log(formData);

    // form-validation
    $.each(formData, function(k, v) {
        let el = $('#' + v.name);
        if (el.val() == "" && el.attr('type') !== 'hidden') {
            el.addClass('is-invalid');
            submit = false;
            return false;
        } else {
            el.removeClass('is-invalid');
            submit = true;
        }
    });

    // submit form
    if (submit) {
        $('#editForm').css('filter', 'blur(4px)');
        $('#saveChanges').attr('disabled', 'true');
        $.ajax({
            url: '/tims/update-invoice',
            type: 'POST',
            data: formData,
            datatype: 'json',
            success: function(response) {
                $('#editForm').css('filter', '');
                $('#saveChanges').removeAttr('disabled');
                alert(response[0].Status + " : " + response[0].Status_Remark);
                $('#cancelModal').click();
                refresh();
            },
            error: function(error) {
                alert((error));
                alert(JSON.stringify(error.responseJSON.errors, null, 4));
                $('#editForm').css('filter', '');
                $('#saveChanges').removeAttr('disabled');
            }
        });
    }
});

searchTable("Failed");

function refresh() {
    invcred_table.ajax.reload(null, false);
}

$('#pending').on('click', function() {
    searchTable("Pending");
});

$('#failed').on('click', function() {
    searchTable("Failed");
});

$('#submitted').on('click', function() {
    searchTable("Submitted");
});

$('#allInvoiceCreds').on('click', function() {
    searchTable(" ");
});

function searchTable(param) {
    let column = 4;
    invcred_table.column(column).search(param, false, false, false).draw();
    getStats();
}

function getStats() {
    $.ajax({
        url: '/tims/get-stats',
        type:'GET',
        success: function(success){
            success.forEach(element =>{
                $('#failedStats').html(element.Failed);
                $('#pendingStats').html(element.Pending);
                $('#submittedStats').html(element.Submitted);
                $('#totalStats').html(element.Total);
            });
        },
        error: function(error){
            console.log(error);
        }
    });

    setTimeout(() => {
        refresh();
        getStats();
    }, 30000);
}
</script>
@stop
