<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
@extends('adminlte::page')

@section('title', 'Home-Dashboard')

@section('content_header')
@stop

@section('navbar')
    @if ($hasAzure && $baseOffice)
        @php
            $allOffices = array_merge(
                [['OfficeCode' => $baseOffice, 'OfficeName' => $baseOffice . ' (Base)']],
                array_map(fn($o) => is_array($o) ? $o : (array) $o, $otherOffices),
            );
            $currentLabel =
                collect($allOffices)->first(fn($o) => $o['OfficeCode'] === $activeOfficeCode)['OfficeName'] ??
                ($activeOfficeCode ?: $baseOffice . ' (Base)');
        @endphp

        <li class="nav-item dropdown mx-2">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="officeDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="gap: 6px;">
                <i class="fas fa-building"></i>
                <span class="d-none d-sm-inline">{{ $currentLabel }}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="officeDropdown">
                <h6 class="dropdown-header">
                    <i class="fas fa-exchange-alt mr-1"></i> Switch Office
                </h6>
                @foreach ($allOffices as $office)
                    @php
                        $code = $office['OfficeCode'] ?? '';
                        $name = $office['OfficeName'] ?? $code;
                        $isActive = $activeOfficeCode === $code || (!$activeOfficeCode && $code === $baseOffice);
                    @endphp
                    <form method="POST" action="{{ route('home.switch-office') }}" class="m-0">
                        @csrf
                        <input type="hidden" name="office_code" value="{{ $code }}">
                        <button type="submit"
                            class="dropdown-item d-flex align-items-center justify-content-between {{ $isActive ? 'active' : '' }}"
                            style="gap: 8px;">
                            <span>{{ $name }}</span>
                            @if ($isActive)
                                <i class="fas fa-check text-success" style="font-size:0.75rem;"></i>
                            @endif
                        </button>
                    </form>
                @endforeach
            </div>
        </li>
    @endif
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
            margin-bottom: 1rem;
            height: 100%;
        }

        .service-card:hover {
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

        /* Mobile optimizations */
        @media (max-width: 575.98px) {
            .widget-user-username {
                font-size: 1.1rem !important;
            }

            .widget-user-desc {
                font-size: 0.85rem !important;
            }

            .description-header {
                font-size: 0.95rem !important;
            }

            .description-text {
                font-size: 0.7rem !important;
            }

            .widget-user-image img {
                width: 65px !important;
                height: 65px !important;
            }
        }

        /* Tablet optimizations */
        @media (min-width: 576px) and (max-width: 991.98px) {
            .widget-user-username {
                font-size: 1.3rem !important;
            }

            .widget-user-desc {
                font-size: 0.95rem !important;
            }
        }
    </style>
@stop

@section('content')

    @php
        /**
         * Module visibility helper.
         * $canSeeAll bypasses all module checks (superuser / ALL module from SP).
         * Otherwise the card is shown only if its module code is in $activeModules.
         * Cards that are ALSO gated by Laravel Gate/can() keep their existing @if check.
         */
        $hasModule = fn(string $module): bool => $canSeeAll || in_array($module, $activeModules);

        // Count visible cards so we can show the empty state
        $visibleCards = 0;
    @endphp

    <div class="container-fluid p-2 p-md-3">
        <div class="row">

            {{-- ── Client Party System ─────────────────────────────────── --}}
            @if ($hasModule('CPS'))
                @php $visibleCards++ @endphp
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="/client-party?my_request=true" style="text-decoration:none;color:inherit;">
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
                                        <div class="col-4 border">
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

            {{-- ── TIMS Integration ─────────────────────────────────────── --}}
            @if (Auth::user()->can('tims-access') && $hasModule('TIMS'))
                @php $visibleCards++ @endphp
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="/tims" style="text-decoration:none;color:inherit;">
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
                                        <div class="col-4 border">
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

            {{-- ── EFRIS Integration ────────────────────────────────────── --}}
            @if (Auth::user()->can('efris-access') && $hasModule('EFRIS'))
                @php $visibleCards++ @endphp
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="/efris" style="text-decoration:none;color:inherit;">
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
                                        <div class="col-4 border">
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

            {{-- ── TRA FIT Tanzania ─────────────────────────────────────── --}}
            @if (Auth::user()->can('tra-tz-access') && $hasModule('TRAFIT'))
                @php $visibleCards++ @endphp
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="/tra" style="text-decoration:none;color:inherit;">
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
                                        <div class="col-4 border">
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

            {{-- ── TRA ADL ──────────────────────────────────────────────── --}}
            @if (Auth::user()->can('tra-adl-access') && $hasModule('TRAADL'))
                @php $visibleCards++ @endphp
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="/traADL" style="text-decoration:none;color:inherit;">
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
                                        <div class="col-4 border">
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

            {{-- ── RRA Integration ─────────────────────────────────────── --}}
            @if (Auth::user()->can('rra-access') && $hasModule('RRA'))
                @php $visibleCards++ @endphp
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="/rra" style="text-decoration:none;color:inherit;">
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
                                        <div class="col-4 border">
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

            {{-- ── Log In Tracker ───────────────────────────────────────── --}}
            @if (Auth::user()->can('approve-all-entity-request') && $hasModule('LOGINTRACKER'))
                @php $visibleCards++ @endphp
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="/dashboard" style="text-decoration:none;color:inherit;">
                        <div class="d-flex h-100 flex-column card card-widget widget-user shadow service-card"
                            style="cursor:pointer;">
                            <div class="widget-user-header bg-blue">
                                <h3 class="widget-user-username">LOG IN TRACKER</h3>
                                <h5 class="widget-user-desc">Track User Logins</h5>
                            </div>
                            <div class="widget-user-image">
                                <img src="{{ asset('img/calendar.gif') }}" alt="Login Tracker"
                                    class="img-circle elevation-2">
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-4 border-right">
                                        <div class="description-block">
                                            <h5 class="description-header text-success">{{ $active_this_month ?? 0 }}</h5>
                                            <span class="description-text">Active This Month</span>
                                        </div>
                                    </div>
                                    <div class="col-4 border-right">
                                        <div class="description-block">
                                            <h5 class="description-header text-primary">{{ $active_last_month ?? 0 }}</h5>
                                            <span class="description-text">Active Last Month</span>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="description-block">
                                            <h5 class="description-header text-danger">{{ $inactive_users ?? 0 }}</h5>
                                            <span class="description-text">Inactive Users</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endif

            {{-- ── GPS Tracker ──────────────────────────────────────────── --}}
            @if (Auth::user()->can('view-rate-calculator') && $hasModule('GPSTRACKER'))
                @php $visibleCards++ @endphp
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="{{ route('tracker.dashboard') }}" style="text-decoration:none;color:inherit;">
                        <div class="d-flex h-100 flex-column card card-widget widget-user shadow service-card">
                            <div class="widget-user-header bg-primary">
                                <h3 class="widget-user-username">GPS TRACKER</h3>
                                <h5 class="widget-user-desc">Track Vehicle Trips</h5>
                            </div>
                            <div class="widget-user-image">
                                <img class="img-circle elevation-2" src="{{ asset('/img/delivery.png') }}"
                                    alt="GPS Tracker Icon" style="background:white;padding:10px;">
                            </div>
                            <div class="card-footer">
                                <div class="row justify-content-center">
                                    <div class="col-4 border-right">
                                        <div class="description-block">
                                            <h5 class="description-header"><span
                                                    class="badge badge-info">{{ $tracker_stats->created ?? 0 }}</span>
                                            </h5>
                                            <span class="description-text small">Created</span>
                                        </div>
                                    </div>
                                    <div class="col-4 border-right">
                                        <div class="description-block">
                                            <h5 class="description-header"><span
                                                    class="badge badge-warning">{{ $tracker_stats->transit ?? 0 }}</span>
                                            </h5>
                                            <span class="description-text small">In Transit</span>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="description-block">
                                            <h5 class="description-header"><span
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
            @endif

            {{-- ── Azure Management ─────────────────────────────────────── --}}
            @if (Auth::user()->can('approve-all-entity-request') && $hasModule('AZURE'))
                @php $visibleCards++ @endphp
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="{{ route('azure.dashboard.index') }}" style="text-decoration:none;color:inherit;">
                        <div class="d-flex h-100 flex-column card card-widget widget-user shadow service-card"
                            style="cursor:pointer;">
                            <div class="widget-user-header bg-purple">
                                <h3 class="widget-user-username">AZURE MANAGEMENT</h3>
                                <h5 class="widget-user-desc">Azure Dashboard</h5>
                            </div>
                            <div class="widget-user-image">
                                <img class="img-circle elevation-2" src="{{ asset('/img/management.png') }}"
                                    alt="Azure Management Icon" style="background:white;padding:10px;">
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-4 border-right">
                                        <div class="description-block">
                                            <h5 class="description-header text-success">{{ $azureStats['active'] ?? 0 }}
                                            </h5>
                                            <span class="description-text">Active</span>
                                        </div>
                                    </div>
                                    <div class="col-4 border-right">
                                        <div class="description-block">
                                            <h5 class="description-header text-warning">{{ $azureStats['pending'] ?? 0 }}
                                            </h5>
                                            <span class="description-text">Pending</span>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="description-block">
                                            <h5 class="description-header text-danger">{{ $azureStats['inactive'] ?? 0 }}
                                            </h5>
                                            <span class="description-text">Inactive</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endif

            {{-- ── Shipment Reports ─────────────────────────────────────── --}}
            @if (Auth::user()->can('approve-all-entity-request') && $hasModule('SHIPMENTREP'))
                @php $visibleCards++ @endphp
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="{{ route('shipment-reports.index') }}" style="text-decoration:none;color:inherit;">
                        <div class="d-flex h-100 flex-column card card-widget widget-user shadow service-card"
                            style="cursor:pointer;">
                            <div class="widget-user-header bg-purple">
                                <h3 class="widget-user-username">SHIPMENT REPORTS</h3>
                                <h5 class="widget-user-desc">Email Report Status</h5>
                            </div>
                            <div class="widget-user-image">
                                <img src="{{ asset('img/postman.png') }}" alt="Shipment Reports"
                                    class="img-circle elevation-2">
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-4 border-right">
                                        <div class="description-block">
                                            <h5 class="description-header text-success">
                                                {{ $shipment_reports_stats['emails_sent'] ?? 0 }}</h5>
                                            <span class="description-text">Emails Sent</span>
                                        </div>
                                    </div>
                                    <div class="col-4 border-right">
                                        <div class="description-block">
                                            <h5 class="description-header text-primary">
                                                {{ $shipment_reports_stats['success_rate'] ?? 0 }}%</h5>
                                            <span class="description-text">Success Rate</span>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="description-block">
                                            <h5 class="description-header text-danger">
                                                {{ $shipment_reports_stats['failed'] ?? 0 }}</h5>
                                            <span class="description-text">Failed</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endif

            {{-- ── Quote Management System ──────────────────────────────── --}}
            @if ($hasModule('QMS'))
                @php $visibleCards++ @endphp
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="{{ route('quotation.index') }}" style="text-decoration:none;color:inherit;">
                        <div class="card card-widget widget-user shadow service-card">
                            <div class="widget-user-header bg-info">
                                <h3 class="widget-user-username">Quote Management System</h3>
                                <h5 class="widget-user-desc">Integration Status</h5>
                            </div>
                            <div class="widget-user-image">
                                <img class="img-circle elevation-2" src="{{ asset('img/supply-chain-management.png') }}"
                                    alt="SCM Icon" style="background:white;padding:5px;width:60px;height:60px;">
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-3 border-right">
                                        <h5 class="text-primary">{{ $quotation_stats['total'] ?? 0 }}</h5>
                                        <span>Total</span>
                                    </div>
                                    <div class="col-3 border-right">
                                        <h5 class="text-warning">{{ $quotation_stats['in_progress'] ?? 0 }}</h5>
                                        <span>In Progress</span>
                                    </div>
                                    <div class="col-3 border-right">
                                        <h5 class="text-danger">{{ $quotation_stats['failed_int'] ?? 0 }}</h5>
                                        <span>Failed</span>
                                    </div>
                                    <div class="col-3">
                                        <h5 class="text-secondary">{{ $quotation_stats['failed_progress'] ?? 0 }}</h5>
                                        <span>Failed In Progress</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endif

            {{-- ── RA Integration ───────────────────────────────────────── --}}
            @if (Auth::user()->can('approve-all-entity-request') && $hasModule('RAINT'))
                @php $visibleCards++ @endphp
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="{{ route('ra-integration.dashboard') }}" style="text-decoration:none;color:inherit;">
                        <div class="d-flex h-100 flex-column card card-widget widget-user shadow service-card"
                            style="cursor:pointer;">
                            <div class="widget-user-header bg-info">
                                <h3 class="widget-user-username">RA INTEGRATION</h3>
                                <h5 class="widget-user-desc">Revenue Authority Processing</h5>
                            </div>
                            <div class="widget-user-image">
                                <img src="{{ asset('img/sheet.png') }}" alt="RA Integration"
                                    class="img-circle elevation-2">
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-4 border-right">
                                        <div class="description-block">
                                            <h5 class="description-header text-warning">
                                                {{ $ra_metrics['ra_pending_d365'] ?? 0 }}</h5>
                                            <span class="description-text">Pending D365</span>
                                        </div>
                                    </div>
                                    <div class="col-4 border-right">
                                        <div class="description-block">
                                            <h5 class="description-header text-danger">
                                                {{ $ra_metrics['ra_total_failed'] ?? 0 }}</h5>
                                            <span class="description-text">Total Failed</span>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="description-block">
                                            <h5 class="description-header text-success">
                                                {{ $ra_metrics['ra_success_today'] ?? 0 }}</h5>
                                            <span class="description-text">Success Today</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endif

            {{-- ── Empty state ──────────────────────────────────────────── --}}
            @if ($visibleCards === 0)
                <div class="col-12">
                    <div class="card card-body text-center py-5 shadow-sm">
                        <i class="fas fa-building fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No modules assigned for this office</h4>
                        <p class="text-muted mb-0">
                            Switch to a different office using the dropdown in the top navigation bar,
                            or contact your administrator to assign modules.
                        </p>
                    </div>
                </div>
            @endif

        </div>
    </div>

@stop

@section('js')
    <script>
        // Optional: Add JavaScript for additional interactivity if needed
    </script>
@stop




Exact Local Storage Path — generatePdf()
php$folder = 'quotations/' . $quotation->bookingno;
$fileName = 'quote v' . $nextVersion . '.pdf';
$storagePath = $folder . '/' . $fileName;
$fullPath = \Storage::disk('public')->path($storagePath);
Which resolves to:
{Laravel_Root}/storage/app/public/quotations/{bookingno}/quote v1.pdf
{Laravel_Root}/storage/app/public/quotations/{bookingno}/quote v2.pdf

Exact Write to Disk — before Azure push
phpfile_put_contents($fullPath, $pdf->output());

if (!file_exists($fullPath)) {
    throw new \Exception('File was not found on disk after write: ' . $fullPath);
}
This must succeed before Azure upload is attempted.

Then Azure Push happens after
php$blobPath = 'Quotation/' . $officeShortCode . '/' . $fileName;
\Storage::disk('azure')->put($blobPath, file_get_contents($fullPath));
So the sequence is strictly:
1. PDF rendered in memory (Dompdf)
2. Written to local disk → storage/app/public/quotations/{bookingno}/
3. Read from local disk  → file_get_contents($fullPath)
4. Pushed to Azure blob  → Quotation/{officeCode}/{fileName}

For Terms & Conditions — saveTermsCondition()
php$folder    = 'quotations/' . $bookingNo . '/tc';
$fileName  = 'TC_' . $bookingNo . '_' . now()->format('Ymd_His') . '.html';
$filePath  = $folder . '/' . $fileName;

\Storage::disk('public')->put($filePath, $termsText ?? '');
Resolves to:
{Laravel_Root}/storage/app/public/quotations/{bookingno}/tc/TC_{bookingno}_{timestamp}.html

Full Local Directory Structure
{Laravel_Root}/
└── storage/
    └── app/
        └── public/
            └── quotations/
                └── {bookingno}/
                    ├── quote v1.pdf
                    ├── quote v2.pdf
                    └── tc/
                        └── TC_{bookingno}_{timestamp}.html

Publicly Accessible via Symlink
Laravel's storage:link command symlinks this to:
{Laravel_Root}/public/storage/quotations/{bookingno}/
Which is web-accessible at:
https://{your-domain}/storage/quotations/{bookingno}/quote v1.pdf


Based on the screenshot and the code:

Brief Description
These are locally stored PDF quotation documents generated by the system via Dompdf. Each folder is named after the bookingno integer. Files are created first locally, then pushed to Azure blob storage. They are also publicly accessible via browser.

File Path (Server)
/var/www/html/ERP_UAT/public/quotations/{bookingno}/
Example:
/var/www/html/ERP_UAT/public/quotations/1/quote v1.pdf
/var/www/html/ERP_UAT/public/quotations/25/quote v1.pdf

Browser URL
https://d009b.westeurope.cloudapp.azure.com:4505/quotations/{bookingno}/quote v1.pdf
Example:
https://d009b.westeurope.cloudapp.azure.com:4505/quotations/1/quote v1.pdf
https://d009b.westeurope.cloudapp.azure.com:4505/quotations/25/quote v1.pdf

Terms & Conditions (also stored locally)
# File Path
/var/www/html/ERP_UAT/public/quotations/{bookingno}/tc/TC_{bookingno}_{timestamp}.html

# Browser URL
https://d009b.westeurope.cloudapp.azure.com:4505/quotations/{bookingno}/tc/TC_{bookingno}_{timestamp}.html