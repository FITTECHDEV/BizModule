@extends('adminlte::page')

@section('title', 'Home-Dashboard')

@section('content_header')
@stop

@section('plugins.Datatables', true)
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Existing CSS -->
    <style>
        /* General card styling */
        .service-card {
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 2rem;
            border-radius: 12px;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            background: #fff;
        }

        .service-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }

        .service-card:focus-within {
            outline: 2px solid #31D0D4;
            outline-offset: 2px;
        }

        /* Widget header */
        .widget-user-header {
            padding: 1.5rem;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .widget-user-header h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .widget-user-header h5 {
            font-size: 0.9rem;
            font-weight: 400;
            opacity: 0.9;
            margin: 0.5rem 0 0;
        }

        /* Widget image */
        .widget-user-image {
            margin-top: -40px;
            z-index: 1;
        }

        .widget-user-image img {
            width: 80px;
            height: 80px;
            border: 4px solid #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .service-card:hover .widget-user-image img {
            transform: scale(1.1);
        }

        /* Card footer */
        .card-footer {
            padding: 1rem;
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
        }

        .description-block {
            padding: 0.5rem;
        }

        .description-header .badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 12px;
        }

        .description-text {
            font-size: 0.85rem;
            color: #555;
            text-transform: capitalize;
        }

        /* Log In Tracker specific styling */
        .login-tracker-card .widget-user-header {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .login-tracker-card:hover .widget-user-header {
            background: linear-gradient(135deg, #218838, #1bae87);
        }

        .login-tracker-card .widget-user-image img {
            border-color: #20c997;
        }

        .login-tracker-card .card-footer {
            background: #e6f4ea;
        }

        .login-tracker-card .description-header .badge {
            background: #28a745;
            color: #fff;
        }

        /* Icon styling for widget-user-image */
        .widget-user-image i {
            color: #20c997;
            background: #fff;
            border: 4px solid #fff;
            border-radius: 50%;
            width: 80px;
            height: 80px;
            line-height: 72px;
            /* Center icon vertically */
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, color 0.3s ease, background 0.3s ease;
        }

        .service-card:hover .widget-user-image i {
            transform: scale(1.1);
            color: #fff;
            background: #28a745;
            /* Matches Log In Tracker header */
        }

        /* Ensure consistent positioning */
        .widget-user-image {
            margin-top: -40px;
            z-index: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .service-card {
                margin-bottom: 1.5rem;
            }

            .widget-user-header h3 {
                font-size: 1.2rem;
            }

            .widget-user-header h5 {
                font-size: 0.8rem;
            }

            .widget-user-image img {
                width: 60px;
                height: 60px;
            }
        }
    </style>
@stop

@section('content')
    <div class="p-3">
        <div class="row justify-content-center">
            <!-- Ticketing System -->
            <div class="col-md-3 col-sm-6 mb-3">
                <a href="/tickets">
                    <div class="d-flex h-100 flex-column card card-widget widget-user shadow service-card">
                        <div class="widget-user-header bg-info">
                            <h3 class="widget-user-username">TICKETING SYSTEM</h3>
                            <h5 class="widget-user-desc">Raise A Concern</h5>
                        </div>
                        <div class="widget-user-image">
                            <img class="img-circle elevation-2" src="{{ asset('site_imgs/images.png') }}"
                                alt="Phone Avatar">
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
            </div>

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
            @if (Auth::user()->can('quote-access'))
                <div class="col-md-3 col-sm-6 mb-3">
                    <a href="{{ URL::signedRoute('quote-logger') }}">
                        <div class="d-flex h-100 flex-column card card-widget widget-user shadow service-card">
                            <div class="widget-user-header bg-teal">
                                <h3 class="widget-user-username">QUOTE LOGGER</h3>
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
            @endif

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
            <div class="col-md-3 col-sm-6 mb-3">
                <a href="/dashboard">
                    <div
                        class="d-flex h-100 flex-column card card-widget widget-user shadow service-card login-tracker-card">
                        <div class="widget-user-header bg-blue">
                            <h3 class="widget-user-username">LOG IN TRACKER</h3>
                            <h5 class="widget-user-desc">Track User Logins</h5>
                        </div>
                        <div class="widget-user-image">
                            <i class="fas fa-user-clock fa-3x"></i>
                        </div>
                        {{-- <div class="card-footer">
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
                        </div> --}}
                    </div>
                </a>
            </div>

        </div>
    </div>
@stop

@section('js')
    <script>
        // Optional: Add JavaScript for additional interactivity if needed
    </script>
@stop
