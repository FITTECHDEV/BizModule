<!DOCTYPE html>
<html lang="en">
<!-- <html lang="{{ str_replace('_', '-', app()->getLocale()) }}"> -->

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- <link rel="icon" href="https://freightintime.com/wp-content/uploads/2019/04/freight-in-time-logo-Edit.gif" 
        sizes="32x32" />-->
    <!-- <link rel="icon" href="https://freightintime.com/wp-content/uploads/2019/04/freight-in-time-logo-Edit.gif" 
        sizes="192x192" />-->
   <!-- <link rel="apple-touch-icon"
         href="https://freightintime.com/wp-content/uploads/2019/04/freight-in-time-logo-Edit.gif" /> -->

    <meta http-equiv="refresh" content="{{ config('session.lifetime') * 60 }}">

    @yield('meta_tags')

    <title>
        @yield('title_prefix', config('adminlte.title_prefix', ''))
        @yield('title', config('adminlte.title', 'FIT SERVICE DESK'))
        @yield('title_postfix', config('adminlte.title_postfix', ''))
    </title>

    @yield('adminlte_css_pre')

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @if (!config('adminlte.enabled_laravel_mix'))

    <link rel="stylesheet" href="{{ asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css') }}">


    @include('adminlte::plugins', ['type' => 'css'])

    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    @else
    <link rel="stylesheet" href="{{ asset(config('adminlte.laravel_mix_css_path', 'css/app.css')) }}">

    <link rel="stylesheet" href="{{ asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css') }}">

    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    @endif

    @if (config('adminlte.livewire'))
        @if (app()->version() >= 7)
            @livewireStyles
        @else
            <livewire:styles />
        @endif
    @endif

    <link rel="stylesheet" href="//cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">

    @yield('adminlte_css')

    <!-- Favicon -->
    @if (config('adminlte.use_ico_only'))
    {{--
    <!--<link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />--> --}}
    @elseif(config('adminlte.use_full_favicon'))

    {{--
    <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('favicons/apple-icon-57x57.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('favicons/apple-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('favicons/apple-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicons/apple-icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('favicons/apple-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('favicons/apple-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('favicons/apple-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('favicons/apple-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicons/apple-icon-180x180.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicons/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicons/android-icon-192x192.png') }}">
    <link rel="manifest" href="{{ asset('favicons/manifest.json') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ asset('favicon/ms-icon-144x144.png') }}"> --}}
    @endif

</head>

<body class="@yield('classes_body')" @yield('body_data')>
    <!-- sweet alert -->
    @include('sweetalert::alert')
    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="{{ asset('site_imgs/banner.gif') }}" alt="call_log" height="60" width="60">
    </div>
    <!-- alerts -->
    {{--
    <!-- @include('includes/flash-message') --> --}}

    <!-- Body Content -->
    @yield('body')

    <!-- Base Scripts -->
    @if (!config('adminlte.enabled_laravel_mix'))
        <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>

    <!-- Configured Scripts -->
    @include('adminlte::plugins', ['type' => 'js'])

    <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
    @else
    <script src="{{ asset(config('adminlte.laravel_mix_js_path', 'js/app.js')) }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous">
    </script>

    @endif


    <!-- Livewire Script -->
{{--@if (config('adminlte.livewire'))
        @if (app()->version() >= 7)
            @livewireScripts
        @else
            <livewire:scripts />
        @endif
    @endif --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

    @yield('adminlte_js')

    {{-- @auth --}}
    {{-- <script src="{{ asset('js/enable-push.js') }}" defer></script>--}}
    {{-- @endauth --}}
    <script type="text/javascript">
        $(function() {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: function(toast) {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
            $('[data-toggle="tooltip"]').tooltip();
        });

        $(document).on({
            ajaxStart: function() {
                $('#shipment_timeline').addClass('d-none');
                $('#timeline_loader').removeClass('d-none');
                $('#shipment-container').css('min-height', '0px');
            },
            ajaxStop: function() {
                $('#timeline_loader').addClass('d-none');
            }
        });
    </script>


</body>

</html>
