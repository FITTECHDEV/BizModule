<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    {{-- Navbar left links --}}
    <ul class="navbar-nav">
        {{-- Left sidebar toggler link --}}
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')

        {{-- Configured left links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

        {{-- Custom left links --}}
        @yield('content_top_nav_left')
    </ul>

    {{-- Navbar right links --}}
    <ul class="navbar-nav ml-auto">
        {{-- refresh --}}
        {{-- <li class="nav-item">
             <a class="nav-link" onclick="window.location.reload(true);"><i class="fas fa-spinner fa-success"></i> </a>
        </li> --}}
        <!-- Messages Dropdown Menu -->
        <li class="nav-item dropdown">
           {{--  <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                <span class="badge badge-danger navbar-badge">{{ auth()->user()->unreadNotifications->count() }}
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right overflow-auto" style="max-height: 300px;">
                @forelse (auth()->user()->unreadNotifications as $notification)
                    <span href="#" class="dropdown-item">
                        <!-- Message Start -->
                        <div class="media">
                            <img src="{{ asset('site_imgs/avatar5.png') }}" alt="User Avatar" class="img-size-50 mr-3 img-circle">
                            <div class="media-body">
                                <h3 class="dropdown-item-title">
                                    {{-- title --}
                                    <span class="float-right text-sm text-danger">
                                        {{-- <i class="fas fa-star"></i> --}
                                    </span>
                                </h3>
                                <p class="text-sm">
                                        {!! $notification->data['data'] !!} 
                                </p>
                                <p class="text-sm text-muted">
                                    <i class="far fa-clock mr-1"></i>
                                    {{ $notification->created_at->diffForHumans() }} <br>
                                    <a href="{{ route('mark',[ $notification->id]) }}" class="">Mark as Read</a>
                                </p>
                            </div>
                        </div>
                        <!-- Message End -->
                    </span>
                    <div class="dropdown-divider"></div>
                    @empty 
                    <a href="#" class="dropdown-item">
                        <!-- Message Start -->
                        <div class="media">
                            <div class="media-body">
                                <p class="text-sm">
                                    No Notifications
                                </p>
                            </div>
                        </div>
                    </a>
                @endforelse
                @if (auth()->user()->unreadNotifications->count() > 2)
                     <div class="dropdown-divider"></div>
                <a href="{{ route('mark') }}" class="dropdown-item dropdown-footer" onclick="return confirm('Are you sure ?')">Mark All As Read</a>
                @endif
            </div> --}}
        </li>
        {{-- Custom right links --}}
        @yield('content_top_nav_right')

        {{-- Configured right links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

        {{-- User menu link --}}
        @if (Auth::user())
            @if (config('adminlte.usermenu_enabled'))
                @include('adminlte::partials.navbar.menu-item-dropdown-user-menu')
            @else
                @include('adminlte::partials.navbar.menu-item-logout-link')
            @endif
        @endif

        {{-- Right sidebar toggler link --}}
        @if (config('adminlte.right_sidebar'))
            @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
        @endif
    </ul>

</nav>
