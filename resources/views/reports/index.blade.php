@extends('adminlte::page')

@section('title', 'Reports-Dashboard')

@section('content_header')

@stop

@section('content')
    <div class="container">
        {{-- filter row --}}
        <div class="row jumbotron p-2 justify-content-center">
            <div class="col-md-7">
                <form action="">
                    @csrf
                    <table class="table table-sm text-center">
                        <tr>
                            <td>
                                <label for="filter_date">Date</label> &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;
                                <select name="filter_date" id="filter_date" >
                                    {{-- <option value="AllTime">All Time</option> --}}
                                    <option value="Today" selected>Today</option>
                                    <option value="Yesterday">Yesterday</option>
                                    <option value="ThisMonth">This Month</option>
                                    <option value="LastMonth">Last Month</option>
                                    <option value="PreviousMonth">Previous Month</option>
                                    <option value="ThisQuarter">This Quarter</option>
                                    <option value="Last3Months">Last 3 Months</option>
                                    <option value="Last6Months">Last 6 Months</option>
                                    <option value="Range">Range</option>
                                </select><br>
                                <div class="rangeDiv hide text-right" id="rangeDiv">
                                        <label for="fromDate" class="mr-2">From</label>
                                        <input type="date" name="fromDate" id="fromDate"> <br>
                                        <label for="toDate" class="mr-2">To</label>
                                        <input type="date" name="toDate" id="toDate"></div>
                                </div>

                            </td>
                            <td><label for="filter_solution">Solution</label>
                                <select name="filter_solution" id="filter_solution">
                                    <option value="">All Solutions</option>
                                    @forelse (\App\Models\Category::where('CategoryType', 'Solution')->orderBy('CategorySeq', 'ASC')->get() as $category)
                                        <option value="{{ $category->ID }}">{{ $category->CategoryName }}</option>
                                    @empty
                                        <option value="" disabled> No Solutions</option>
                                    @endforelse
                                </select>
                            </td>
                            <td>
                                <label for="filter_country">Country</label>
                                <select name="filter_country" id="filter_country" >
                                    <option value="">All Countries</option>
                                    @foreach (\App\Models\Office::get() as $office)
                                        <option value="{{ $office->ID }}">{{ $office->Name }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <button id="search_range"
                                type="button"
                                class="btn btn-outline-info btn-sm hide"
                                data-toggle="tooltip"
                                title="Click to Search"
                                data-placement="right"
                                ><i class="fa fa-search"></i> Search</button>
                                <!-- Default switch -->
                                {{-- <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="customSwitches">
                                    <label class="custom-control-label" for="customSwitches">Show Figures</label>
                                </div> --}}
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
        {{-- /end of filter row --}}
        {{-- badge raw --}}
        <div class="row justify-content-center">
            <div class="col-12 col-sm-6 col-md-2">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-envelope-open"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-center">Open </span>
                        <span class="info-box-number h4" id="open_tickets">
                            0
                        </span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-12 col-sm-6 col-md-2">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-center">Solved </span>
                        <span class="info-box-number h4" id="solved_tickets">
                            0
                        </span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <div class="info-box">
                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-dot-circle-slash"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-center">Closed </span>
                        <span class="info-box-number h4" id="closed_tickets">
                            0
                        </span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <div class="info-box">
                    <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-redo"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-center">Re-Opened</span>
                        <span class="info-box-number h4" id="re_opened_tickets">
                            0
                        </span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <div class="info-box">
                    <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-envelope"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-center">Settled </span>
                        <span class="info-box-number h4" id="settled_tickets">
                            0
                        </span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <div class="info-box">
                    <span class="info-box-icon bg-purple elevation-1"><i class="fa fa-table"
                            aria-hidden="true"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-center">Total </span>
                        <span class="info-box-number h4" id="total_tickets">
                            0
                        </span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
        </div>
        {{-- /end of badge row --}}
         <div class="row">
            <!-- Tickets Raised By Country -->
            <div class="col-md-12">
                <div class=" card">
                    <div class="card-header">Tickets Raised By Country
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Chart's container -->
                        <div id="chart" style="height: 300px;"></div>

                        <script>
                            const chart = new Chartisan({
                                el: '#chart',
                                url: "@chart('ticket_by_country_chart')",
                                hooks: new ChartisanHooks()
                                    .legend()
                                    .datasets(['line'])
                                    .colors()
                                    .tooltip(),
                            });
                        </script>
                    </div>
                </div>
            </div>
            <!-- / Tickets Raised By Country -->
        </div>
        <div class="row">
            <!-- verrall Ticket Status -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Overrall Ticket Status
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="overrallticketstatus" style="height: 400px;"></div>
                        <script>
                            const overrallticketstatus = new Chartisan({
                                el: '#overrallticketstatus',
                                url: "@chart('overall_ticket_status')",
                                hooks: new ChartisanHooks()
                                    // .title('Overrall Ticket Status')
                                    .legend()
                                    .datasets('pie')
                                    .colors()
                                    .tooltip()
                                    .axis(false),
                            });
                        </script>
                    </div>
                </div>
            </div>
            <!-- / verrall Ticket Status -->
        </div> {{----}}
        {{-- <div class="row">
            <div class="col-md-12">
                <div class=" card">
                    <div class="card-header">
                        Country Based - Solution wise Ticket Status
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="countrybasedsolutionreport" style="height: 400px"></div>
                        <script>
                            const countrybasedsolutionreport = new Chartisan({
                                el: '#countrybasedsolutionreport',
                                url:"@chart('country_based_solution_report')",
                                hooks: new ChartisanHooks()
                                .legend({ position: 'bottom' })
                                .title('This is a sample chart using chartisan!')
                                .datasets([{ type: 'line', fill: true }, 'bar']),
                            })
                        </script>
                    </div>
                </div>
            </div>
        </div> --}}
         <div class="row">
            <div class=" col-md-12">
                <div class=" card">
                    <div class="card-header">Ticket Status for Each Country
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="ticketstatusforeachcountry" style="height: 400px"></div>
                        <script>
                            const ticketstatusforeachcountry = new Chartisan({
                                el:'#ticketstatusforeachcountry',
                                url:"@chart('ticket_status_for_each_country')",
                                hooks: new ChartisanHooks()
                                .legend()
                                .colors()
                                .tooltip(),
                            })
                        </script>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class=" card">
                    <div class="card-header">PriorityWise Status Report
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="prioritywisereport" style="height: 400px"></div>
                        <script>
                            const prioritywisereport = new Chartisan({
                                el: '#prioritywisereport',
                                url: "@chart('priority_wise_report')",
                                hooks: new ChartisanHooks()
                                .legend()
                                .colors()
                                .tooltip()
                            })
                        </script>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class=" card">
                    <div class="card-header">
                        Date Wise Tickets Raised
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="daywiseticketsraised" style="height: 400px"></div>
                        <script>
                            const daywiseticketsraised = new Chartisan({
                                el: '#daywiseticketsraised',
                                url: "@chart('day_wise_tickets_raised')",
                                hooks: new ChartisanHooks()
                                .legend()
                                .colors()
                                .tooltip()
                                // .responsive({value: true})
                                ,
                            })
                        </script>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class=" card">
                    <div class="card-header">
                        Solution Wise Ticket Status
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="solutionwiseticketsraised" style="height: 400px"></div>
                        <script>
                            const solutionwiseticketsraised = new Chartisan({
                                el: '#solutionwiseticketsraised',
                                url: "@chart('solution_wise_tickets_raised')",
                                loader: {
                                    color: '#ff00ff',
                                    size: [30, 30],
                                    type: 'bar',
                                    textColor: '#ffff00',
                                    text: 'Loading some chart data...',
                                },
                                hooks: new ChartisanHooks()
                                .legend()
                                .colors()
                                .tooltip()
                                // .datasets([{ type: 'line', fill: true }, 'bar'])
                                ,
                            })
                        </script>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class=" card">
                    <div class="card-header">
                        Month Wise Tickets Raised
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        Year
                        <select name="year" id="year">
                            @foreach (\App\Models\Ticket::select(DB::raw('YEAR(CreatedOn) as year'))->distinct()->pluck('year')  as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                        <div id="monthwiseticketsraised" style="height: 400px;"></div>
                        <script>
                            const monthwiseticketsraised = new Chartisan({
                                el: '#monthwiseticketsraised',
                                url: "@chart('month_wise_tickets_raised')",
                                hooks: new ChartisanHooks()
                                    .legend({
                                        position: 'bottom'
                                    })
                                    .datasets('bar')
                                    .colors()
                                    .tooltip(),
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div> {{----}}
    </div>
@stop

@section('css')
    <!-- Charting library -->
    <script src="https://unpkg.com/echarts/dist/echarts.min.js"></script>
    <!-- Chartisan -->
    <script src="https://unpkg.com/@chartisan/echarts/dist/chartisan_echarts.js"></script>
    <!-- Your application script -->
@stop

@section('js')
    <script>
        $(function() {

            /*public filter variables*/
            var filter_date = $("#filter_date option:selected").val();
            var  filter_country = $("#filter_country option:selected").val();
            var  filter_solution = $("#filter_solution option:selected").val();
            /*Filter array*/
            var filter_search_var = [filter_date, filter_country, filter_solution];

            filter_search(filter_search_var);



            $('[data-toggle="tooltip"]').tooltip();
            /* hide date range inputs*/
            $('#rangeDiv').hide();
            /*hide the search  button*/
            $('#search_range').hide();

            /*Monthwise tickets raised year optioon*/
            $('#year').on('change', function() {
                monthwiseticketsraised.update({
                    url: 'api/chart/month_wise_tickets_raised?year=' + $(this).val()
                });
            });
            /** console.log(filter_date+'\n'+filter_country+'\n'+filter_solution );*/
            $("#filter_date").change(function(e) {
                /*Set the filter date to range*/
                filter_date = $(this).val();
                /* update filter date var*/
                filter_search_var.splice(0, 1, filter_date);
                if (filter_date == "Range") {
                    e.preventDefault();
                    /* show the daterange inputs*/
                    $('#rangeDiv').show(1000);
                    /*Show the search button*/
                    $('#search_range').show(1000);

                } else {
                    /* reload the search func*/
                    filter_search(filter_search_var);
                    update_charts(filter_search_var);
                    /*Hide te range input and the search button*/
                    $('#rangeDiv').hide(1000);
                    $('#search_range').hide(1000);
                }
            });
            /*Search button click*/
            $('#search_range').on('click', function(e){
                /*Set the varialbles*/
                var fromDate = $('#fromDate').val();
                var toDate = $('#toDate').val();
                var dateA = new Date(fromDate);
                var dateB = new Date(toDate);
                if (fromDate == '') {
                    alert('From Date is Required');
                }else if(toDate == '') {
                    alert('To Date is Required');
                }else if(((fromDate == '') || (toDate == '')) || (dateA > dateB)) {
                    alert('To Date Cannot be less than From date');
                }else{
                    var range = [fromDate, toDate];
                    filter_search(filter_search_var, range);
                    update_charts(filter_search_var+'&toDate='+toDate+'&fromDate='+fromDate);
                }
            });

            $("#filter_country").change(function() {
                filter_country = $(this).val();
                /* update filter_country var*/
                filter_search_var.splice(1, 1, filter_country);
                /* reload the search func if not range*/
                if (filter_date != 'Range') {
                    filter_search(filter_search_var);
                    update_charts(filter_search_var);
                }else{
                    search_button();
                }
            });
            /*Filter search function*/
            function update_charts(params){
                /*update raise ticket by country chart*/
                chart.update({
                        url: 'api/chart/ticket_by_country_chart?results=' + params,
                    });
                /*update overall ticket status chart*/
                overrallticketstatus.update({
                    url: 'api/chart/overall_ticket_status?results=' + params,
                });
                /*update overall ticket status chart*/
                ticketstatusforeachcountry.update({
                    url: 'api/chart/ticket_status_for_each_country?results=' + params,
                });
            }
            /*Submit search button*/
            function search_button(){
                $('#search_range').fadeOut(1000, function(){
                        $(this).addClass('shadow-lg');
                    }).fadeIn(2000);
                    $('[data-toggle="tooltip"]').tooltip("show");
            }

            $("#filter_solution").change(function() {
                filter_solution = $(this).val();
                /* update filer_solution*/
                filter_search_var.splice(2, 1, filter_solution);
                /* console.log(filter_solution);*/
                /**console.log(filter_search_var);*/
                /* reload the search func if not range*/
                if (filter_date != 'Range') {
                    filter_search(filter_search_var);
                    update_charts(filter_search_var);
                }else{
                    search_button();
                }
            });

            /* filter search fun*/
            function filter_search(filter_vals, filter_range = null) {
                 console.log('sending', filter_vals, filter_range);
               /* ajax setup */
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                /*loader funciton*/
                loading();
                /* search url */
                var url = '/filter/report';
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        filter_date: filter_vals[0],
                        filter_solution: filter_vals[2],
                        filter_country: filter_vals[1],
                        filter_range: filter_range,
                    },
                    success: function(response) {
                        /*apppend the response to the componets*/
                        $('#open_tickets').html(response.data.open);
                        $('#solved_tickets').html(response.data.solved);
                        $('#closed_tickets').html(response.data.closed);
                        $('#re_opened_tickets').html(response.data.re_opened);
                        $('#settled_tickets').html(response.data.settled);
                        $('#total_tickets').html(response.data.total);
                    },
                    error: function(erro) {
                        console.log(erro);
                    }
                })


            }
            /*Loading Function*/
            function loading() {

                $('#open_tickets').html(
                    '<i class="fa fa-spinner fa-spin" style="font-size:24px;color:yellow"></i>');
                $('#solved_tickets').html(
                    '<i class="fa fa-spinner fa-spin" style="font-size:24px;color:blue"></i>');
                $('#closed_tickets').html(
                    '<i class="fa fa-spinner fa-spin" style="font-size:24px;color:green"></i>');
                $('#re_opened_tickets').html(
                    '<i class="fa fa-spinner fa-spin" style="font-size:24px;color:red"></i>');
                $('#settled_tickets').html(
                    '<i class="fa fa-spinner fa-spin" style="font-size:24px;color:grey"></i>');
                $('#total_tickets').html(
                    '<i class="fa fa-spinner fa-spin" style="font-size:24px;color:grey"></i>');
            }
        })
    </script>

@stop
