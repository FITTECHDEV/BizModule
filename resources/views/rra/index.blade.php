@extends('adminlte::page')

@section('title', 'RRA')

@section('content_header')

@stop

@section('css')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fa;
        }

        /* Info Box Styling */
        .info-box {
            border-radius: 12px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            border: none;
            overflow: hidden;
        }

        .info-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .info-box-icon {
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: background-color 0.3s ease;
        }

        .info-box-content {
            padding: 10px 15px;
        }

        .info-box-text {
            font-weight: 500;
            color: #333;
            font-size: 0.95rem;
        }

        .info-box-number {
            font-size: 1.6rem;
            font-weight: 600;
            color: #1a1a1a;
        }

        /* Status-Specific Colors */
        .bg-danger {
            background: linear-gradient(45deg, #ff4d4f, #ff7875) !important;
        }

        .bg-warning {
            background: linear-gradient(45deg, #ffa940, #ffc107) !important;
        }

        .bg-success {
            background: linear-gradient(45deg, #28a745, #34c759) !important;
        }

        .bg-purple {
            background: linear-gradient(45deg, #6f42c1, #a855f7) !important;
        }

        /* Table Styling */
        .table {
            border-radius: 8px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .table th {
            background: #f1f3f5;
            font-weight: 500;
            color: #333;
            padding: 12px;
            font-size: 0.9rem;
        }

        .table td {
            padding: 12px;
            font-size: 0.85rem;
            color: #444;
            vertical-align: middle;
        }

        .table tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s ease;
        }

        /* Modal Styling */
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.3s ease;
        }

        .modal-header {
            background: linear-gradient(45deg, #007bff, #00aaff);
            color: #fff;
            border-radius: 12px 12px 0 0;
            padding: 15px 20px;
        }

        .modal-title {
            font-weight: 500;
            font-size: 1.25rem;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group label {
            font-weight: 500;
            color: #333;
            font-size: 0.9rem;
        }

        .form-control,
        .custom-select {
            border-radius: 8px;
            border: 1px solid #dcdcdc;
            padding: 10px;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus,
        .custom-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

        .is-invalid {
            border-color: #ff4d4f !important;
        }

        .btn-primary {
            background: linear-gradient(45deg, #007bff, #00aaff);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: background 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #0056b3, #0088cc);
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: background 0.3s ease;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        /* Animation for Modal */
        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Responsive Adjustments */
        @media (max-width: 576px) {
            .info-box {
                margin-bottom: 15px;
            }

            .info-box-icon {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }

            .info-box-number {
                font-size: 1.4rem;
            }

            .table {
                font-size: 0.8rem;
            }
        }
    </style>
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
   <!-- Modal -->
<div class="modal fade" id="editModal" data-backdrop="static" data-keyboard="false" role="dialog"
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
                        <select class="custom-select" id="HSCodeOptions" name="HSCodeOptions" disabled>
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="cancelModal">Cancel</button>
                <input type="submit" class="btn btn-primary" id="saveChanges" value="Save changes">
            </div>
        </div>
    </div>
</div>
@stop


@section('js')
    <script>
        let url = 'datatables/rra';
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
            if (action === 'edit') {
                $('#editModal').modal('show');
                $('#hsCodeNote').remove();
            }
            if (action === 're-trigger') {
                $.ajax({
                    url: '/rra/re-trigger/' + docNo,
                    type: 'GET',
                    datatype: 'json',
                    success: function(response) {
                        alert(response[0].Status + " : " + response[0].Status_Remark);
                        refresh();
                    },
                    error: function(error) {
                        alert('Error re-triggering invoice. Please try again.');
                    }
                });
            }
        }

        function fetchHSCodes(id) {
            $('#editForm').css('filter', 'blur(4px)');
            $('#saveChanges').attr('disabled', 'true');
            $.ajax({
                url: '/rra/hs-codes/' + id,
                type: 'GET',
                datatype: 'json',
                success: function(response) {
                    $('#HSCodeOptions').find('option').not(':first').remove();
                    if (response.length > 0) {
                        response.forEach(element => {
                            $('#HSCodeOptions').append('<option value="' + element.ChargeCode + '">' +
                                element.Description + '</option>');
                        });
                    } else {
                        $('#ChangeOption option[value=HSCode]').prop('disabled', 'true').change();
                        $('#HSCodeOptionsDiv').after(
                            '<span class="text-danger" id="hsCodeNote">No HS Codes available</span>');
                    }
                    $('#editForm').css('filter', '');
                    $('#saveChanges').removeAttr('disabled');
                },
                error: function(error) {
                    $('#cancelModal').trigger('click');
                    $('#editForm').css('filter', '');
                    $('#saveChanges').removeAttr('disabled');
                    alert("Failed to fetch HS Codes. Please try again.");
                }
            });
        }

        $('#ChangeOption').change(function() {
            let option = $('#ChangeOption option:selected').val();
            if (option === "HSCode") {
                $('#HSCodeOptions').removeAttr('disabled').addClass('is-invalid');
            } else {
                $('#HSCodeOptions').attr('disabled', true).removeClass('is-invalid');
            }
        });

        $('#saveChanges').click(function() {
            let formData = $('#editForm').serializeArray();
            let submit = true;

            $.each(formData, function(k, v) {
                let el = $('#' + v.name);
                if (el.val() === "") {
                    el.addClass('is-invalid');
                    submit = false;
                } else {
                    el.removeClass('is-invalid');
                }
            });

            if (submit) {
                $('#editForm').css('filter', 'blur(4px)');
                $('#saveChanges').attr('disabled', 'true');
                $.ajax({
                    url: '/rra/update-invoice',
                    type: 'POST',
                    data: formData,
                    datatype: 'json',
                    success: function(response) {
                        $('#editForm').css('filter', '');
                        $('#saveChanges').removeAttr('disabled');
                        alert(response[0].Status + " : " + response[0].Status_Remark);
                        $('#cancelModal').trigger('click');
                        refresh();
                    },
                    error: function(error) {
                        $('#editForm').css('filter', '');
                        $('#saveChanges').removeAttr('disabled');
                        alert('Error: ' + (error.responseJSON?.errors ? JSON.stringify(error
                            .responseJSON.errors, null, 4) : 'Something went wrong.'));
                    }
                });
            }
        });

        function searchTable(param) {
            let column = 4;
            invcred_table.column(column).search(param, false, false, false).draw();
            getStats();
        }

        function refresh() {
            invcred_table.ajax.reload(null, false);
        }

        function getStats() {
            $.ajax({
                url: '/rra/get-stats',
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
                    console.error('Error fetching stats:', error);
                }
            });

            setTimeout(() => {
                refresh();
                getStats();
            }, 30000);
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

        // Initialize stats on page load
        getStats();
    </script>
@stop
