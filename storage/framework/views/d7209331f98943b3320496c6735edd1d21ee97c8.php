

<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content_header'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid p-2">
    <div class="card p-3">
        <form action="" class="form-horizontal row" id="reports-form" name="reports-form">
            
            <div class="form-group col-*-* mr-1">
                <label for="date" class="text-sm mb-0">Date</label> <br>
                <select name="date" id="date" class="selectpicker" data-width="">
                    <option value="Today">Today</option>
                    <option value="ThisMonth">This Month</option>
                    <option value="LastMonth">Last Month</option>
                    <option value="SpecificMonth">Specific Month</option>
                    <option value="Range">Range</option>
                </select><br>
                <div class="rangeDiv hide" id="rangeDiv">
                    <label for="fromDate" class="text-sm mb-0">From</label><br>
                    <input type="date" name="fromDate" id="fromDate"> <br>
                    <label for="toDate" class="text-sm mb-0">To</label><br>
                    <input type="date" name="toDate" id="toDate" class="">
                </div>
                <div id="monthDiv">
                    <label for="month" class="selectpicker text-sm mb-0" data-width="">Month</label><br>
                    <select name="month" id="month" class="selectpicker">
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>
                
                <label for="office" class="text-sm mb-0">Office</label> <br>
                <select name="office" id="office" class="selectpicker" data-width="">
                    <?php if(Auth::user()->can('raise-ticket-for-any-office')): ?>
                    <option value="" selected>Select Office</option>
                    <?php $__currentLoopData = \App\Models\Office::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $office): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($office->ID); ?>"><?php echo e($office->Name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                    <option value="<?php echo e(Auth::user()->OfficeID); ?>" selected><?php echo e(Auth::user()->office->Name); ?>

                    </option>
                    <?php endif; ?>
                </select>
                <br>
                
                <label for="priority" class="text-sm mb-0">Priority</label><br>
                <select name="priority" id="prioriry" class="selectpicker" data-width="">
                    <option value="" selected>Select Priority</option>
                    <?php $__empty_1 = true; $__currentLoopData = $priorities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <option value="<?php echo e($priority->ID); ?>" <?php if(old('priority')==$priority->ID): ?> selected <?php endif; ?>>
                        <?php echo e($priority->CategoryName); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <option value="" disabled>No Available Priorities</option>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="form-group col-*-* ml-1">
                <label for="solution" class="text-sm mb-0"> Solution</label><br>
                <select name="solution" id="solution" class="selectpicker ml-1" data-width="">
                    <option value="" selected>Select Solution</option>
                    <?php $__currentLoopData = \App\Models\Category::where('CategoryType', 'Solution')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $solution): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($solution->ID); ?>"><?php echo e($solution->CategoryName); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select><br>
                
                <label for="module" class="text-sm mb-0">Module</label><br>
                <select name="module" id="module" class=" ml-1" data-width="">
                    <option value="" selected>Select Module</option>
                </select><br>
                
                <label for="sub_module" class="text-sm mb-0 ">Sub-Module</label><br>
                <select name="sub_module" id="sub_module" class="ml-1" data-width="">
                    <option value="" selected>Select SubModule</option>
                </select><br>
            </div>

            <div class="form-group col-*-* ml-2 mr-1">
                
                <label for="assignedTo" class="text-sm mb-0">Assigned To</label> <br>
                <select name="assignedTo" id="assignedTo" class="selectpicker" data-width="">
                    <option value="" selected>Select User</option>
                    <?php $__currentLoopData = \App\Models\User::where('Role', 1)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($user->ID); ?>"><?php echo e($user->full_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select><br>
                
                <label for="solvedBy" class="text-sm mb-0">Solved By</label> <br>
                <select name="solvedBy" id="solvedBy" class="selectpicker" data-width="">
                    <option value="" selected>Select User</option>
                    <?php $__currentLoopData = \App\Models\User::where('Role', 1)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($user->ID); ?>"><?php echo e($user->full_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select> <br>
                <label for="status" class="text-sm mb-0">Status</label> <br>
                <select name="status" id="status" class="selectpicker" data-width="">
                    <option value="" selected>Select Status</option>
                    <option value="1">Open</option>
                    <option value="2">Solved</option>
                    <option value="3">Closed</option>
                    <option value="4">Re-Opened</option>
                    <option value="5">Settled</option>
                </select>
            </div>
            <div class="form-group col-*-* ml-0 ">
                <label for="tat_assign" class="text-sm mb-0">TAT to Assign</label><br>
                <select name="tat_assign" id="tat_assign" class="selectpicker" data-width="fit">
                    <option value="" disabled selected>Criteria</option>
                    <option value="<">Less than </option>
                    <option value=">">Greater than</option>
                    <option value="<=">Less than & Equal to</option>
                    <option value=">=">Greater then & Equal To</option>
                    <option value="tat_assign_range">Range</option>
                </select>
                <input type="text" name="tat_assign_v2" id="tat_assign_v2" style="width: 55px" placeholder="Value"><br>
                <label for="tat_close" class="text-sm mb-0">TAT to Close</label><br>
                <select name="tat_close" id="tat_close" class="selectpicker" data-width="fit">
                    <option value="" disabled selected>Criteria</option>
                    <option value="<">Less than </option>
                    <option value=">">Greater than</option>
                    <option value="<=">Less than & Equal to</option>
                    <option value=">=">Greater then & Equal To</option>
                    <option value="tat_close_range">Range</option>
                </select>
                <input type="text" name="tat_close_v2" id="tat_close_v2" style="width: 55px" placeholder="Value"><br>

                <label for="tat_solve" class="text-sm mb-0">TAT to Solve</label><br>
                <select name="tat_solve" id="tat_solve" class="selectpicker" data-width="fit">
                    <option value="" selected>Criteria</option>
                    <option value="<">Less than </option>
                    <option value=">">Greater than</option>
                    <option value="<=">Less than & Equal to</option>
                    <option value=">=">Greater then & Equal To</option>
                    <option value="tat_solve_range">Range</option>
                </select>
                <input type="text" name="tat_solve_v2" id="tat_solve_v2" style="width: 55px" placeholder="Value"><br>
                <div class="text-right">
                    <button class="btn btn-sm btn-outline-secondary mr-2" type="button" name="clear"
                        id="clear">Clear</button>
                    <button class="btn btn-sm btn-primary" type="button" name="generate" id="generate">Generate</button>
                </div>
                <div class="btns" id="btns"></div>
            </div>

        </form>
    </div>
    <div class="card card-body table-responsive">
        <table class="table table-xs display table-bordered text-sm responsive" id="reports-table">
            <thead>
                <th> TicketNo </th>
                <th> TicketDT </th>
                <th> Office</th>
                <th> RaisedBy</th>
                <th> AssignedTo </th>
                <th> AssignedDT </th>
                <th> SolutionName </th>
                <th> ModuleName </th>
                <th> SubModuleName </th>
                <th> Priority </th>
                <th> Status </th>
                <th> Title </th>
                <th> SolveBy </th>
                <th> SolveOn </th>
                <th> ClosedBy </th>
                <th> ClosedOn </th>
                <th> ReopenedBy </th>
                <th> ReopenedOn </th>
                <th> TATAssigned </th>
                <th> TATSolved </th>
                
                <th> TATClosed </th>
                
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<style>
    input[type=text],
    select,
    textarea,
    .selectpicker {
        /* width: 100%; */
        /* padding: 12px; */
        border: 1px solid rgb(111, 83, 214);
        border-radius: 4px;
        /*resize: vertical; */
    }

    select {
        width: 100%;
        /**/
        padding: 8px 8px;
        /**/
        border: none;
        /**/
        /* border-radius: 4px; */
        background-color: #f8f9fa;
        /**/
    }

    label {

        /* padding: 12px 12px 12px 0; */
        /* display: inline-block; */
    }

    .blur {
        /* font-size: 40px; */
        /* color: transparent;
        text-shadow: 0 0 8px #000; */
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css">
<!-- Datatable Bootstrap CDN -->
<link href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css" type="text/css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.dataTables.min.css">


<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">

<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>

<!-- (Optional) Latest compiled and minified JavaScript translation files -->



<script src="https://code.jquery.com/jquery-3.5.1.js"></script>



<script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js" type="text/javascript"></script>
<script src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js" type="text/javascript"></script>

<script src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>

<script src="<?php echo e(asset('js/tat.js')); ?>"></script>
<script type="text/javascript">
    $(document).ready(function() {
            /**reset form*/
            $('#clear').on('click', function(){
                $('#reports-form')[0].reset();
                reports_table.clear().draw();
            });
            var reports_table = $("#reports-table").DataTable({
                /**/
                dom: 'Bfrtip',
                buttons: [
                      /**{
                            extend: 'print',
                            orientation: 'landscape',
                            alignment: "center",
                            title: "ETICKET REPORT",
                            pageSize: 'A4',
                            customize: function ( win ) {
                               /* $(win.document.body)
                                    .css( 'font-size', '10pt' )
                                    .prepend(
                                        '<img src="https://freightintime.com/wp-content/uploads/2019/04/freight-in-time-logo-Edit.gif" style="position:absolute; top:10; left:0;" />'
                                    );

                                $(win.document.body).find( 'table' )
                                    .addClass( 'compact' )
                                    .css( 'font-size', 'inherit' );

                                    win.watermark = {text: 'watermark text', color: 'blue', opacity: 0.1};
                            }

                        },*/
                        'csv', 'excel',
                        {
                            extend: 'pdf',
                            orientation: 'landscape',
                            alignment: "center",
                            title: "ETICKET REPORT AS OF "+ new Date().toLocaleString(),
                            pageSize: 'A4',
                            customize: function ( win ) {
                                win.content[1].table.widths = Array(win.content[1].table.body[0].length + 1).join('*').split('');
                                win.watermark = {text: 'Freight In Time', color: 'blue', opacity: 0.1};

                                win.pageMargins = [7,7,7,7];
                                win.defaultStyle.fontSize = 5;
                                win.styles.tableHeader.fontSize = 5;
                                win.styles.title.fontSize = 9;
                                win.content[0].text = win.content[0].text.trim();

                                /* Create a footer */
                                win['footer']=(function(page, pages) {
                                    return {
                                        columns: [
                                            'Created By <?php echo e(Auth::user()->full_name); ?> ',
                                            {
                                                alignment: 'right',
                                                text: ['page ', { text: page.toString() },  ' of ', { text: pages.toString() }]
                                            }
                                        ],
                                        margin: [10, 0],
                                    }
                                });

                                /* Styling the table: create style object*/
                                var objLayout = {};
                                /* Horizontal line thickness*/
                                objLayout['hLineWidth'] = function(i) { return .5; };
                                /* Vertical line thickness*/
                                objLayout['vLineWidth'] = function(i) { return .5; };
                                /* Horizontal line color*/
                                objLayout['hLineColor'] = function(i) { return '#aaa'; };
                                /* Vertical line color*/
                                objLayout['vLineColor'] = function(i) { return '#aaa'; };
                                /* Left padding of the cell*/
                                objLayout['paddingLeft'] = function(i) { return 1; };
                                /* Right padding of the cell*/
                                objLayout['paddingRight'] = function(i) { return 1; };
                                /* Inject the object in the document*/
                                win.content[1].layout = objLayout;

                            }
                        }
                    ],
                columns: [{
                        "data": "TicketNo",
                        "name": "TicketNo"
                    },
                    {
                        "data": "TicketDT",
                        "name": "TicketDT"
                    },
                    {
                        "data": "Office",
                        "name": "Office"
                    },
                    {
                        "data": "RaisedBy",
                        "name": "RaisedBy"
                    },
                    {
                        "data": "AssignedTo",
                        "name": "AssignedTo"
                    },
                    {
                        "data": "AssignedDT",
                        "name": "AssignedDT"
                    },
                    {
                        "data": "SolutionName",
                        "name": "SolutionName"
                    },
                    {
                        "data": "ModuleName",
                        "name": "ModuleName"

                    },
                    {
                        "data": "SubModuleName",
                        "name": "SubModuleName"
                    },
                    {
                        "data": "Priority",
                        "name": "Priority"
                    },
                    {
                        "data": "Status",
                        "name": "Status"
                    },
                    {
                        "data": "Title",
                        "name": "Title"

                    },
                    {
                        "data": "SolveBy",
                        "name": "SolveBy"
                    },
                    {
                        "data": "SolveOn",
                        "name": "SolveOn"
                    },
                    {
                        "data": "ClosedBy",
                        "name": "ClosedBy"

                    },
                    {
                        "data": "ClosedOn",
                        "name": "ClosedOn"

                    },
                    {
                        "data": "ReopenedBy",
                        "name": "ReopenedBy"
                    },
                    {
                        "data": "ReopenedOn",
                        "name": "ReopenedOn"
                    },
                    {
                        "data": "TATAssigned",
                        "name": "TATAssigned"
                    },
                    {
                        "data": "TATSolved",
                        "name": "TATSolved"
                    },
                    /*{
                        "data": "TATSolvedHR",
                        "name": "TATSolvedHR"
                    },*/
                    {
                        "data": "TATClosed",
                        "name": "TATClosed"
                    },
                    /*{
                        "data": "TATReClosed",
                        "name": "TATReClosed"
                    },*/
                ],
                rowCallback: function(row, data) {},
                filter: false,
                info: false,
                ordering: false,
                processing: true,
                retrieve: true
            });

            /*reports_table.buttons().container().appendTo( $('.btns') );*/

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $("#generate").on("click", function(event) {
                $(this).text('Creating..');
                /* console.log('Generating ..');*/
                var searchData = $('form').serialize();
                /* console.log(searchData);*/
                $.ajax({
                    url: "/reports/filter",
                    type: "POST",
                    dataType: 'json',
                    data: searchData
                }).done(function(result) {
                    /* console.log(result);*/
                    if (result.length != 0) {
                        reports_table.clear().draw();
                        reports_table.rows.add(result).draw();
                    }else{
                        alert('No Results Found for Filter');
                    }


                }).fail(function(xhr, textStatus, errorThrown) {
                    console.log(xhr.status);
                    console.log(errorThrown);
                });
                $(this).text('Generate');
            });
            /*Show date range*/
            $('#rangeDiv').hide();
            $('#monthDiv').hide();
            $('#date').on('change', function() {
                if ($(this).val() == 'Range') {
                    $('#rangeDiv').show(1000);
                } else {
                    $('#rangeDiv').hide(1000);
                    /*Clear the inputs*/
                    $('#fromDate').val('');
                    $('#toDate').val('');
                }

                if ($(this).val() == 'SpecificMonth') {
                    $('#monthDiv').show(1000);
                }else{
                    $('#monthDiv').hide(1000);
                }
            });

            $('#module').attr('disabled', true);
            $('#sub_module').attr('disabled', true);
            $('#solution').on('change', function() {
                $('#module').attr('disabled', false);
                $('#sub_module').attr('disabled', true);
              var  sol = $('#solution option:selected').val();
                fetch_modules(sol);
            });

            $('#module').on('change', function() {
                $('#sub_module').attr('disabled', true);
                $.ajax({
                    url: '<?php echo e(route('modules.get_data')); ?>',
                    type: 'GET',
                    datatype: 'json',
                    data: {
                        ID: $('#module option:selected').val(),
                        check: 'module',
                    },
                    success: function(data) {

                        $('#sub_module').attr('disabled', false);
                        $('#sub_module').attr('required', true);

                        $('#sub_module').empty().append(
                            '<option value="" selected disabled>Select Category</option>'
                        );

                        $.each(data, function(index, value) {

                            $('#sub_module').append($('<option/>', {
                                value: value.ID,
                                text: value.CategoryName
                            }));
                        });
                    },
                    error: function(error) {}

                });

            });
            /*Fetch Modules*/
            function fetch_modules(solutionId){
                $.ajax({
                    url: "<?php echo e(route('modules.get_data')); ?>",
                    type: 'GET',
                    datatype: 'json',
                    data: {
                        ID: solutionId,
                        check: 'solution',
                    },
                    success: function(data) {

                        $('#module').attr('disabled', false);
                        $('#module').attr('required', true);
                        $('#module').empty().append(
                            '<option value="" selected disabled>Sub-Category Name</option>'
                        );

                        $.each(data, function(index, value) {

                            /**/$('#module').append($('<option/>', {
                                value: value.ID,
                                text: value.CategoryName
                            }));

                        });
                       /* $("#module").selectpicker('render');
                        $('#module').selectpicker('refresh');*/
                    },
                    error: function(error) {
                        console.log(error);
                    }

                });
            }

        });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\wwwroot\Projects\Production\erp_production\resources\views/archive/index.blade.php ENDPATH**/ ?>