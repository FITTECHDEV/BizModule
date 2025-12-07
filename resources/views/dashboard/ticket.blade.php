@extends('adminlte::page')

@section('title', 'Home-Dashboard')

@section('content_header')
@stop

@section('plugins.Datatables', true)
@section('css')
    <style>
        .service-card {
            text-align: center;
            box-shadow: 0 2px 2px rgba(0, 0, 0, 0.2);
            -moz-transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, .1);
            -o-transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, .1);
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, .1);
            margin-bottom: 10%;
        }

        .service-card:hover {
            box-shadow: 4px 4px 4px rgba(58, 105, 243, 0.3);
            -webkit-transform: translate(0, -8px);
            -moz-transform: translate(0, -8px);
            -ms-transform: translate(0, -8px);
            -o-transform: translate(0, -8px);
            transform: translate(0, -8px);
            box-shadow: 0 40px 40px rgba(0, 0, 0, 0.2);
        }

        .service-card i {
            font-size: 150px;
            padding: 10% 0;
            background-color: #f3f3f3;
            color: #31D0D4;
            box-shadow: 0 6px 6px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 1;
        }

        .service-card:hover i {
            color: #fff;
            transition: all ease-in-out 0.5s;
        }

        .service-card i:after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
            transform: scaleY(0);
            transform-origin: bottom center;
            background: linear-gradient(to left, #8487E2, #31D0D4, #31D0D4, #8487E2);
            z-index: -1;
            transition: transform 0.5s;
        }

        .service-card:hover i::after {
            transform: scaleY(1);
        }

        /* .service-card h3{
                                      margin-top: 2%;
                                      font-size: 44px;
                                      font-weight: bold;
                                      color: #464646;
                                    } */
    </style>
@stop

@section('content')
    <div class="p-3">
        <div class="row justify-content-center">
            {{-- <!-- Ticketing System -->
            <div class="col-md-3 col-sm-6 mb-3">
                <a href="/tickets">
                    <div class="d-flex h-100 flex-column card card-widget widget-user shadow service-card">
                        <div class="widget-user-header bg-info">
                            <h3 class="widget-user-username">TICKETING SYSTEM</h3>
                            <h5 class="widget-user-desc">Raise A Concern</h5>
                        </div>
                        <div class="widget-user-image">
                            <img class="img-circle elevation-2" src="{{ asset('site_imgs/images.png') }}" alt="Phone Avatar">
                        </div>
                        <div class="card-footer">
                            <div class="row justify-content-center">
                                @foreach ($ticketStatistics as $ticketStat)
                                    <div class="col-sm-4 border">
                                        <div class="description-block">
                                            <h5 class="description-header"><span
                                                    class="badge badge-info">{{ $ticketStat->StatusCount }}</span></h5>
                                            <span class="description-text small">{{ $ticketStat->StatusName }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </a>
            </div> --}}

            <!-- Client Party System -->
            <div class="col-md-3 col-sm-6 mb-3">
                <a href="/client-party?my_request=true">
                    <div class="d-flex h-100 flex-column card card-widget widget-user shadow service-card">
                        <div class="widget-user-header bg-primary">
                            <h3 class="widget-user-username">CLIENT PARTY SYSTEM</h3>
                            <h5 class="widget-user-desc">Entity Creation & Mapping</h5>
                        </div>
                        <div class="widget-user-image">
                            <img class="img-circle elevation-2" src="{{ asset('site_imgs/handshake.jpg') }}"
                                alt="Handshake Avatar">
                        </div>
                        <div class="card-footer">
                            <div class="row justify-content-center">
                                @foreach ($entityStatistics as $entityStats)
                                    <div class="col-sm-4 border">
                                        <div class="description-block">
                                            <h5 class="description-header"><span
                                                    class="badge badge-primary">{{ $entityStats->StatusCount }}</span></h5>
                                            <span class="description-text small">{{ $entityStats->StatusName }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- TIMS Integration -->
            @if (Auth::user()->can('tims-access'))
                <div class="col-md-3 col-sm-6 mb-3">
                    <a href="/tims">
                        <div class="d-flex h-100 flex-column card card-widget widget-user shadow service-card">
                            <div class="widget-user-header bg-maroon">
                                <h3 class="widget-user-username">TIMS INTEGRATION</h3>
                                <h5 class="widget-user-desc">KRA Dashboard</h5>
                            </div>
                            <div class="widget-user-image">
                                <img class="img-circle elevation-2" src="{{ asset('site_imgs/kenya_flag.png') }}"
                                    alt="Kenya Flag">
                            </div>
                            <div class="card-footer">
                                <div class="row justify-content-center">
                                    @foreach ($tims_stats as $entityStats)
                                        <div class="col-sm-4 border">
                                            <div class="description-block">
                                                <h5 class="description-header"><span
                                                        class="badge badge-primary">{{ $entityStats->StatusCount }}</span>
                                                </h5>
                                                <span class="description-text small">{{ $entityStats->StatusName }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endif

            <!-- EFRIS Integration -->
            @if (Auth::user()->can('efris-access'))
                <div class="col-md-3 col-sm-6 mb-3">
                    <a href="/efris">
                        <div class="d-flex h-100 flex-column card card-widget widget-user shadow service-card">
                            <div class="widget-user-header bg-maroon">
                                <h3 class="widget-user-username">EFRIS INTEGRATION</h3>
                                <h5 class="widget-user-desc">EFRIS Dashboard</h5>
                            </div>
                            <div class="widget-user-image">
                                <img class="img-circle elevation-2"
                                    src="{{ asset('site_imgs/Custom-Icon-Design-Round-World-Flags-Uganda.ico') }}"
                                    alt="Uganda Flag">
                            </div>
                            <div class="card-footer">
                                <div class="row justify-content-center">
                                    @foreach ($efris_stats as $entityStats)
                                        <div class="col-sm-4 border">
                                            <div class="description-block">
                                                <h5 class="description-header"><span
                                                        class="badge badge-primary">{{ $entityStats->EfrisStatus }}</span>
                                                </h5>
                                                <span class="description-text small">{{ $entityStats->NoOfInvoice }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endif

            <!-- Quote Logger -->
            {{-- @if (Auth::user()->can('quote-access'))
                <div class="col-md-3 col-sm-6 mb-3">
                    <a href="{{ URL::signedRoute('quote-logger') }}">
                        <div class="d-flex h-100 flex-column card card-widget widget-user shadow service-card">
                            <div class="widget-user-header bg-teal">
                                <h3 class="widget-user-username">QUOTE LOGGErrrrR</h3>
                                <h5 class="widget-user-desc">Quote-Log System</h5>
                            </div>
                            <div class="widget-user-image">
                                <img class="img-circle elevation-2" src="{{ asset('site_imgs/quotation2.png') }}"
                                    alt="Quote Avatar">
                            </div>
                            <div class="card-footer">
                                <div class="row justify-content-center">
                                    <!-- Placeholder for future stats -->
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endif --}}

            <!-- TRA Integration (Tanzania) -->
            @if (Auth::user()->can('tra-tz-access'))
                <div class="col-md-3 col-sm-6 mb-3">
                    <a href="/tra">
                        <div class="d-flex h-100 flex-column card card-widget widget-user shadow service-card">
                            <div class="widget-user-header bg-yellow">
                                <h3 class="widget-user-username">TRA INTEGRATION</h3>
                                <h5 class="widget-user-desc">FIT Tanzania Dashboard</h5>
                            </div>
                            <div class="widget-user-image">
                                <img class="img-circle elevation-2"
                                    src="{{ asset('site_imgs/transparent-flag-of-tanzania-flag.png') }}"
                                    alt="Tanzania Flag">
                            </div>
                            <div class="card-footer">
                                <div class="row justify-content-center">
                                    @foreach ($traStatsTZ as $entityStats)
                                        <div class="col-sm-4 border">
                                            <div class="description-block">
                                                <h5 class="description-header"><span
                                                        class="badge badge-primary">{{ $entityStats->StatusCount }}</span>
                                                </h5>
                                                <span class="description-text small">{{ $entityStats->StatusName }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endif

            <!-- TRA Integration (ADL) -->
            @if (Auth::user()->can('tra-adl-access'))
                <div class="col-md-3 col-sm-6 mb-3">
                    <a href="/traADL">
                        <div class="d-flex h-100 flex-column card card-widget widget-user shadow service-card">
                            <div class="widget-user-header bg-info">
                                <h3 class="widget-user-username">TRA INTEGRATION</h3>
                                <h5 class="widget-user-desc">ADL Dashboard</h5>
                            </div>
                            <div class="widget-user-image">
                                <img class="img-circle elevation-2"
                                    src="{{ asset('site_imgs/africa_direct_limited_logo.jpeg') }}" alt="ADL Logo">
                            </div>
                            <div class="card-footer">
                                <div class="row justify-content-center">
                                    @foreach ($traStatsADL as $entityStats)
                                        <div class="col-sm-4 border">
                                            <div class="description-block">
                                                <h5 class="description-header"><span
                                                        class="badge badge-primary">{{ $entityStats->StatusCount }}</span>
                                                </h5>
                                                <span class="description-text small">{{ $entityStats->StatusName }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endif

            <!-- RRA Integration -->
            @if (Auth::user()->can('rra-access'))
                <div class="col-md-3 col-sm-6 mb-3">
                    <a href="/rra">
                        <div class="d-flex h-100 flex-column card card-widget widget-user shadow service-card">
                            <div class="widget-user-header bg-green">
                                <h3 class="widget-user-username">RRA INTEGRATION</h3>
                                <h5 class="widget-user-desc">FIT Rwanda Dashboard</h5>
                            </div>
                            <div class="widget-user-image">
                                <img class="img-circle elevation-2" src="{{ asset('site_imgs/rw-circle-01.png') }}"
                                    alt="Rwanda Avatar">
                            </div>
                            <div class="card-footer">
                                <div class="row justify-content-center">
                                    @foreach ($rra_stats as $entityStats)
                                        <div class="col-sm-4 border">
                                            <div class="description-block">
                                                <h5 class="description-header"><span
                                                        class="badge badge-primary">{{ $entityStats->StatusCount }}</span>
                                                </h5>
                                                <span class="description-text small">{{ $entityStats->StatusName }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endif

            <!-- Log In Tracker -->
            @if (Auth::user()->can('approve-all-entity-request'))
                <div class="col-md-3 col-sm-6 mb-3">
                    <a href="/dashboard">
                        <div
                            class="d-flex h-100 flex-column card card-widget widget-user shadow service-card login-tracker-card">
                            <div class="widget-user-header bg-blue">
                                <h3 class="widget-user-username">LOG IN TRACKER</h3>
                                <h5 class="widget-user-desc">Track User Logins</h5>
                            </div>
                            {{-- <div class="widget-user-image">
                                <i class="fas fa-user-clock fa-sm"></i>

                            </div> --}}

                        </div>
                    </a>
                </div>
            @endif

            <!-- GPS Tracker Card -->
            @if (Auth::user()->can('view-rate-calculator'))
                <div class="col-md-3 col-sm-6 mb-3">
                    <a href="{{ route('tracker.dashboard') }}">
                        <div class="d-flex h-100 flex-column card card-widget widget-user shadow service-card">
                            <div class="widget-user-header bg-primary">
                                <h3 class="widget-user-username">GPS TRACKER</h3>
                                <h5 class="widget-user-desc">Track Vehicle Trips</h5>
                            </div>
                            <div class="widget-user-image">
                                <i class="fas fa-truck fa-3x text-white"></i>
                            </div>
                            <div class="card-footer">
                                <div class="row justify-content-center">
                                    <div class="col-sm-4 border-right">
                                        <div class="description-block">
                                            <h5 class="description-header">
                                                <span class="badge badge-info">{{ $tracker_stats->created ?? 0 }}</span>
                                            </h5>
                                            <span class="description-text small">Created</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 border-right">
                                        <div class="description-block">
                                            <h5 class="description-header">
                                                <span
                                                    class="badge badge-warning">{{ $tracker_stats->transit ?? 0 }}</span>
                                            </h5>
                                            <span class="description-text small">In Transit</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="description-block">
                                            <h5 class="description-header">
                                                <span
                                                    class="badge badge-success">{{ $tracker_stats->complete ?? 0 }}</span>
                                            </h5>
                                            <span class="description-text small">Complete</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                @endif <!-- @if (Auth::user()->can('azure-access'))
    -->

                <!-- Azure Management Card - Simple Click Solution -->
                <div class="col-md-3 mb-2">
                    <a href="{{ route('azure.dashboard.index') }}" style="text-decoration: none; color: inherit;">
                        <div class="d-flex h-100 flex-column card card-widget widget-user shadow service-card"
                            style="cursor: pointer;">
                            <div class="widget-user-header bg-purple">
                                <h3 class="widget-user-username">AZURE MANAGEMENT</h3>
                                <h5 class="widget-user-desc">Azure Dashboard</h5>
                            </div>
                            <div class="widget-user-image">
                                <img class="img-circle elevation-2" src="{{ asset('site_imgs/azure_logo.png') }}"
                                    alt="Azure Logo">
                            </div>
                            <div class="card-footer">
                                <div class="row justify-content-center">
                                    <div class="col-sm-12 text-center">
                                        <div class="description-block">
                                            <h5 class="description-header">
                                                <span class="badge badge-info">Active</span>
                                            </h5>
                                            <span class="description-text small">System Status</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <!--
    @endif -->



        </div>
    </div>
@stop

@section('js')
    <script>
        // Optional: Add JavaScript for additional interactivity if needed
    </script>
@stop
