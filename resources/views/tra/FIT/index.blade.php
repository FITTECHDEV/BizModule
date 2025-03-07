@extends('adminlte::page')

@section('title', 'Tickets')

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
                    <h5 class="modal-title" id="editModalLabel">Edit Invoice</h5>
                </div>
                <div class="modal-body">
                    <form id="editForm" class="editForm">
                        <input type="hidden" name="InvoiceId" id="InvoiceId">
                        <div class="form-group">
                            <label for="ChangeOption">Change Option</label>
                            <select class="custom-select" name="ChangeOption" id="ChangeOption">
                                <option value="" selected>Choose...</option>
                                <option value="CustomerID">CustomerID</option>
                                <option value="HSCode">HS Code</option>
                            </select>
                        </div>
                        <div class="form-group" id="HSCodeOptionsDiv">
                            <label for="HSCodeOptions">HS Codes Option</label>
                            <select class="custom-select " id="HSCodeOptions" name="HSCodeOptions" disabled>
                                <option value="" selected>Choose...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="LevelOption">Level Option</label>
                            <select class="custom-select" id="LevelOption" name="LevelOption">
                                <option value="" selected>Choose...</option>
                                <option value="Master">Master</option>
                                <option value="Invoice">Invoice</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="OptionValue">Value</label>
                            <input type="text" name="OptionValue" id="OptionValue" class="form-control">
                        </div>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                        id="cancelModal">Cancel</button>
                    <input type="submit" class="btn btn-primary" id="saveChanges" value="Save changes">
                </div>
            </div>
        </div>
    </div>
@stop


@section('js')
    <script>
        let url = 'datatables/tra_fit_tz';
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
            $('#hsCodeNote').remove();
            $('#HSCodeOptions').removeClass('is-invalid').attr('disabled', true);
            $('#saveChanges').removeAttr('disabled');
        });

        function editInvoice(id, action, docNo = null) {
            fetchHSCodes(id);
            //append id to modal form
            $('#InvoiceId').val(id);
            if (action === 'edit') {
                // show modal
                $('#editModal').modal('show');
                $('#hsCodeNote').remove();
            }
            if (action === 're-trigger') {
                $.ajax({
                    url: '/tra/re-trigger/' + docNo,
                    type: 'GET',
                    datatype: 'json',
                    success: function(response) {
                        alert(response[0].Status + " : " + response[0].Status_Remark);
                        refresh();
                    }
                });
            }
        }

        function fetchHSCodes(id) {
            let idx = 'D195296F-018F-4411-B3EC-AF660098F08A';
            $('#editForm').css('filter', 'blur(4px)');
            $('#saveChanges').attr('disabled', 'true');
            $.ajax({
                url: '/tra/hs-codes/' + id,
                type: 'GET',
                datatype: 'json',
                success: function(response) {
                    // remove options exept the first one
                    $('#HSCodeOptions').find('option').not(':first').remove();
                    if (response.length > 0) {
                        response.forEach(element => {
                            $('#HSCodeOptions').append('<option value=' + element.ChargeCode + '>' +
                                element.Description + '</option>');
                        });
                    } else {
                        // disable hs code option
                        $('#ChangeOption option[value=HSCode]').prop('disabled', 'true').change();

                        $('#HSCodeOptionsDiv').after(
                            '<span class="text-danger" id="hsCodeNote">No HS Codes</span>');
                    }
                    $('#editForm').css('filter', '');
                    $('#saveChanges').removeAttr('disabled');
                },
                error: function(error) {
                    $('#cancelModal').toggle('click');
                    $('#editForm').css('filter', '');
                    $('#saveChanges').removeAttr('disabled');
                    alert("Something went wrong. Try again !!");

                }
            });
        }

        // on select hs codes show codes
        $('#ChangeOption').change(function() {
            let option = $('#ChangeOption option:selected').val();
            if (option == "HSCode") {
                $('#HSCodeOptions').removeAttr('disabled');
                $('#HSCodeOptions').addClass('is-invalid');
            } else {
                $('#HSCodeOptions').attr('disabled', true);
                $('#HSCodeOptions').removeClass('is-invalid');
            }
        });


        let submit = false;
        let formData = $('#editForm').serializeArray();

        // form submission
        $('#saveChanges').click(function() {
            let formData = $('#editForm').serializeArray();

            console.log(formData);

            // form-validation
            $.each(formData, function(k, v) {
                let el = $('#' + v.name);
                if (el.val() == "") {
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
                    url: '/tra/update-invoice',
                    type: 'POST',
                    data: formData,
                    datatype: 'json',
                    success: function(response) {
                        $('#editForm').css('filter', '');
                        $('#saveChanges').removeAttr('disabled');
                        alert(response[0].Status + " : " + response[0].Status_Remark);
                        $('#cancelModal').click();
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
                url: '/tra/get-stats',
                type: 'GET',
                success: function(success) {
                    success.forEach(element => {
                        $('#failedStats').html(element.Failed);
                        $('#pendingStats').html(element.Pending);
                        $('#submittedStats').html(element.Submitted);
                        $('#totalStats').html(element.Total);
                    });
                },
                error: function(error) {
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
