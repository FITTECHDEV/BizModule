<?php return array (
  'adminlte' => 
  array (
    'title' => 'FIT SERVICE DESK',
    'title_prefix' => '',
    'title_postfix' => '',
    'use_ico_only' => false,
    'use_full_favicon' => false,
    'google_fonts' => 
    array (
      'allowed' => true,
    ),
    'logo' => '<b> SERVICE </b> DESK',
    'logo_img' => 'site_imgs/banner.gif',
    'logo_img_class' => 'brand-image elevation-2',
    'logo_img_xl' => NULL,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'FIT',
    'auth_logo' => 
    array (
      'enabled' => false,
      'img' => 
      array (
        'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
        'alt' => 'Auth Logo',
        'class' => '',
        'width' => 50,
        'height' => 50,
      ),
    ),
    'preloader' => 
    array (
      'enabled' => true,
      'img' => 
      array (
        'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
        'alt' => 'AdminLTE Preloader Image',
        'effect' => 'animation__shake',
        'width' => 60,
        'height' => 60,
      ),
    ),
    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-purple',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => true,
    'layout_topnav' => NULL,
    'layout_boxed' => NULL,
    'layout_fixed_sidebar' => NULL,
    'layout_fixed_navbar' => NULL,
    'layout_fixed_footer' => true,
    'layout_dark_mode' => NULL,
    'classes_auth_card' => 'card-outline card-purple shadow',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat bg-purple shadow-lg',
    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-light-indigo elevation-4',
    'classes_sidebar_nav' => 'nav-child-indent',
    'classes_topnav' => 'navbar-white',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',
    'sidebar_mini' => 'xs',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => true,
    'sidebar_collapse_remember' => true,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-dark',
    'sidebar_scrollbar_auto_hide' => 'm',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,
    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'white',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',
    'use_route_url' => false,
    'dashboard_url' => 'home',
    'logout_url' => '/logout',
    'login_url' => '/azure/login',
    'register_url' => 'register',
    'password_reset_url' => 'forget-password',
    'password_email_url' => 'reset-password',
    'profile_url' => true,
    'enabled_laravel_mix' => true,
    'laravel_mix_css_path' => 'css/app.css',
    'laravel_mix_js_path' => 'js/app.js',
    'menu' => 
    array (
      0 => 
      array (
        'text' => 'Dashboard',
        'url' => 'home',
        'icon' => 'fas fa-tachometer-alt',
      ),
      1 => 
      array (
        'text' => ' Tickets',
        'url' => '/#',
        'icon' => 'fas fa-fw fa-ticket-alt  ',
        'active' => 
        array (
          0 => 'tickets/*',
        ),
        'submenu' => 
        array (
          0 => 
          array (
            'text' => 'Home',
            'url' => '/tickets',
            'icon' => 'fas fa-fw fa-home',
            'can' => 'admin-view',
          ),
          1 => 
          array (
            'text' => 'My Tickets',
            'route' => 'tickets.my_tickets',
            'icon' => 'fas fa-fw fa-ticket-alt',
            'can' => 'my-ticket-list',
            'active' => 
            array (
              0 => 'tickets/my-tickets',
            ),
          ),
          2 => 
          array (
            'text' => 'My Office Tickets',
            'route' => 'tickets.my_office_tickets',
            'icon' => 'fas fa-fw fa-building',
            'can' => 'view-my-office-tickets',
            'active' => 
            array (
              0 => 'tickets/my-office-tickets',
            ),
          ),
          3 => 
          array (
            'text' => 'Assigned',
            'route' => 'tickets.assigned',
            'icon' => 'fas fa-fw fa-ticket-alt',
            'active' => 
            array (
              0 => 'tickets/assigned',
            ),
            'can' => 'view-assigned-tickets',
          ),
          4 => 
          array (
            'text' => 'Dashboards',
            'route' => 'reports.index',
            'icon' => 'fas fa-fw fa-chart-bar',
            'active' => 
            array (
              0 => 'reports/*',
            ),
            'can' => 'view-ticket-reports-dashboard',
          ),
          5 => 
          array (
            'text' => ' Reports',
            'route' => 'archive',
            'icon' => 'fas fa-fw fa-file-alt',
            'active' => 
            array (
              0 => 'archive/*',
            ),
            'can' => 'view-ticket-reports',
          ),
        ),
      ),
      2 => 
      array (
        'key' => 'client_party',
        'text' => 'Client Party Request',
        'url' => '/#',
        'icon' => 'fas fa-fw fa-handshake ',
        'active' => 
        array (
          0 => 'client-party/*',
        ),
        'can' => 'view-client-party',
        'submenu' => 
        array (
          0 => 
          array (
            'text' => ' Requests',
            'url' => 'client-party?submitted_request=true',
            'icon' => 'fas fa-handshake',
            'active' => 
            array (
              0 => 'client-party?submitted_request=true',
            ),
          ),
          1 => 
          array (
            'text' => 'Request Client Party',
            'url' => 'client-party/create',
            'icon' => 'fas fa-fw fa-hand-paper ',
            'active' => 
            array (
              0 => 'client-party/create',
            ),
            'can' => 'request-client-party',
          ),
        ),
      ),
      3 => 
      array (
        'text' => 'TIMS',
        'url' => 'tims',
        'icon' => 'fa fa-fw fa-calculator',
        'can' => 'tims-access',
      ),
      4 => 
      array (
        'text' => 'EFRIS',
        'url' => 'efris',
        'icon' => 'fa fa-fw fa-dollar',
        'can' => 'efris-access',
      ),
      5 => 
      array (
        'text' => 'SETTINGS',
        'url' => '/#',
        'icon' => 'fas fa-fw fa-cogs ',
        'active' => 
        array (
          0 => 'settings/*',
        ),
        'submenu' => 
        array (
          0 => 
          array (
            'text' => 'change_password',
            'route' => 'user.get_change_password',
            'icon' => 'fas fa-fw fa-lock',
            'can' => 'change-user-password',
          ),
          1 => 
          array (
            'text' => 'Roles Management',
            'url' => 'roles',
            'icon' => 'fas fa-tasks',
            'active' => 
            array (
              0 => 'roles/*',
            ),
            'can' => 'roles-list',
          ),
          2 => 
          array (
            'text' => 'User Management',
            'url' => 'users',
            'icon' => 'fas fa-fw fa-users',
            'active' => 
            array (
              0 => 'users/*',
            ),
            'can' => 'users-list',
          ),
          3 => 
          array (
            'text' => 'Category Management',
            'url' => 'categories',
            'icon' => 'fas fa-fw fa-th',
            'active' => 
            array (
              0 => 'categories/*',
            ),
            'can' => 'manage-category',
          ),
        ),
      ),
    ),
    'filters' => 
    array (
      0 => 'JeroenNoten\\LaravelAdminLte\\Menu\\Filters\\GateFilter',
      1 => 'JeroenNoten\\LaravelAdminLte\\Menu\\Filters\\HrefFilter',
      2 => 'JeroenNoten\\LaravelAdminLte\\Menu\\Filters\\SearchFilter',
      3 => 'JeroenNoten\\LaravelAdminLte\\Menu\\Filters\\ActiveFilter',
      4 => 'JeroenNoten\\LaravelAdminLte\\Menu\\Filters\\ClassesFilter',
      5 => 'JeroenNoten\\LaravelAdminLte\\Menu\\Filters\\LangFilter',
      6 => 'JeroenNoten\\LaravelAdminLte\\Menu\\Filters\\DataFilter',
    ),
    'plugins' => 
    array (
      'Datatables' => 
      array (
        'active' => true,
        'files' => 
        array (
          0 => 
          array (
            'type' => 'js',
            'asset' => true,
            'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
          ),
          1 => 
          array (
            'type' => 'js',
            'asset' => true,
            'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
          ),
          2 => 
          array (
            'type' => 'css',
            'asset' => true,
            'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
          ),
        ),
      ),
      'DatatablesPlugins' => 
      array (
        'active' => true,
        'files' => 
        array (
          0 => 
          array (
            'type' => 'js',
            'asset' => true,
            'location' => 'vendor/datatables-plugins/buttons/js/dataTables.buttons.min.js',
          ),
          1 => 
          array (
            'type' => 'js',
            'asset' => true,
            'location' => 'vendor/datatables-plugins/buttons/js/buttons.bootstrap4.min.js',
          ),
          2 => 
          array (
            'type' => 'js',
            'asset' => true,
            'location' => 'vendor/datatables-plugins/buttons/js/buttons.html5.min.js',
          ),
          3 => 
          array (
            'type' => 'js',
            'asset' => true,
            'location' => 'vendor/datatables-plugins/buttons/js/buttons.print.min.js',
          ),
          4 => 
          array (
            'type' => 'js',
            'asset' => true,
            'location' => 'vendor/datatables-plugins/jszip/jszip.min.js',
          ),
          5 => 
          array (
            'type' => 'js',
            'asset' => true,
            'location' => 'vendor/datatables-plugins/pdfmake/pdfmake.min.js',
          ),
          6 => 
          array (
            'type' => 'js',
            'asset' => true,
            'location' => 'vendor/datatables-plugins/pdfmake/vfs_fonts.js',
          ),
          7 => 
          array (
            'type' => 'css',
            'asset' => true,
            'location' => 'vendor/datatables-plugins/buttons/css/buttons.bootstrap4.min.css',
          ),
        ),
      ),
      'Select2' => 
      array (
        'active' => false,
        'files' => 
        array (
          0 => 
          array (
            'type' => 'js',
            'asset' => false,
            'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
          ),
          1 => 
          array (
            'type' => 'css',
            'asset' => false,
            'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
          ),
        ),
      ),
      'Chartjs' => 
      array (
        'active' => false,
        'files' => 
        array (
          0 => 
          array (
            'type' => 'js',
            'asset' => false,
            'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
          ),
        ),
      ),
      'Sweetalert2' => 
      array (
        'active' => false,
        'files' => 
        array (
          0 => 
          array (
            'type' => 'js',
            'asset' => false,
            'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
          ),
        ),
      ),
      'Pace' => 
      array (
        'active' => false,
        'files' => 
        array (
          0 => 
          array (
            'type' => 'css',
            'asset' => false,
            'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
          ),
          1 => 
          array (
            'type' => 'js',
            'asset' => false,
            'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
          ),
        ),
      ),
    ),
    'iframe' => 
    array (
      'default_tab' => 
      array (
        'url' => NULL,
        'title' => NULL,
      ),
      'buttons' => 
      array (
        'close' => true,
        'close_all' => true,
        'close_all_other' => true,
        'scroll_left' => true,
        'scroll_right' => true,
        'fullscreen' => true,
      ),
      'options' => 
      array (
        'loading_screen' => 1000,
        'auto_show_new_tab' => true,
        'use_navbar_items' => true,
      ),
    ),
    'livewire' => false,
  ),
  'app' => 
  array (
    'name' => 'FIT Ticketing System',
    'version' => '',
    'env' => 'Production',
    'debug' => false,
    'url' => 'https://erp.freightintime.com',
    'asset_url' => NULL,
    'timezone' => 'Africa/Nairobi',
    'locale' => 'en',
    'fallback_locale' => 'en',
    'faker_locale' => 'en_US',
    'key' => 'base64:LgNxDsjeffar+Rq84nMUU4D7t8fJeCyMhvyMLTv7fvg=',
    'cipher' => 'AES-256-CBC',
    'providers' => 
    array (
      0 => 'Illuminate\\Auth\\AuthServiceProvider',
      1 => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
      2 => 'Illuminate\\Bus\\BusServiceProvider',
      3 => 'Illuminate\\Cache\\CacheServiceProvider',
      4 => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
      5 => 'Illuminate\\Cookie\\CookieServiceProvider',
      6 => 'Illuminate\\Database\\DatabaseServiceProvider',
      7 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
      8 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
      9 => 'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
      10 => 'Illuminate\\Hashing\\HashServiceProvider',
      11 => 'Illuminate\\Mail\\MailServiceProvider',
      12 => 'Illuminate\\Notifications\\NotificationServiceProvider',
      13 => 'Illuminate\\Pagination\\PaginationServiceProvider',
      14 => 'Illuminate\\Pipeline\\PipelineServiceProvider',
      15 => 'Illuminate\\Queue\\QueueServiceProvider',
      16 => 'Illuminate\\Redis\\RedisServiceProvider',
      17 => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
      18 => 'Illuminate\\Session\\SessionServiceProvider',
      19 => 'Illuminate\\Translation\\TranslationServiceProvider',
      20 => 'Illuminate\\Validation\\ValidationServiceProvider',
      21 => 'Illuminate\\View\\ViewServiceProvider',
      22 => 'Yajra\\Datatables\\DatatablesServiceProvider',
      23 => 'Sujip\\Guid\\GuidServiceProvider',
      24 => 'Clockwork\\Support\\Laravel\\ClockworkServiceProvider',
      25 => 'Spatie\\Permission\\PermissionServiceProvider',
      26 => 'Jimmyjs\\ReportGenerator\\ServiceProvider',
      27 => 'Rap2hpoutre\\LaravelLogViewer\\LaravelLogViewerServiceProvider',
      28 => 'Elibyy\\TCPDF\\ServiceProvider',
      29 => 'App\\Providers\\AppServiceProvider',
      30 => 'App\\Providers\\AuthServiceProvider',
      31 => 'App\\Providers\\BroadcastServiceProvider',
      32 => 'App\\Providers\\EventServiceProvider',
      33 => 'App\\Providers\\RouteServiceProvider',
    ),
    'aliases' => 
    array (
      'App' => 'Illuminate\\Support\\Facades\\App',
      'Arr' => 'Illuminate\\Support\\Arr',
      'Artisan' => 'Illuminate\\Support\\Facades\\Artisan',
      'Auth' => 'Illuminate\\Support\\Facades\\Auth',
      'Blade' => 'Illuminate\\Support\\Facades\\Blade',
      'Broadcast' => 'Illuminate\\Support\\Facades\\Broadcast',
      'Bus' => 'Illuminate\\Support\\Facades\\Bus',
      'Cache' => 'Illuminate\\Support\\Facades\\Cache',
      'Config' => 'Illuminate\\Support\\Facades\\Config',
      'Cookie' => 'Illuminate\\Support\\Facades\\Cookie',
      'Crypt' => 'Illuminate\\Support\\Facades\\Crypt',
      'Date' => 'Illuminate\\Support\\Facades\\Date',
      'DB' => 'Illuminate\\Support\\Facades\\DB',
      'Eloquent' => 'Illuminate\\Database\\Eloquent\\Model',
      'Event' => 'Illuminate\\Support\\Facades\\Event',
      'File' => 'Illuminate\\Support\\Facades\\File',
      'Gate' => 'Illuminate\\Support\\Facades\\Gate',
      'Hash' => 'Illuminate\\Support\\Facades\\Hash',
      'Http' => 'Illuminate\\Support\\Facades\\Http',
      'Lang' => 'Illuminate\\Support\\Facades\\Lang',
      'Log' => 'Illuminate\\Support\\Facades\\Log',
      'Mail' => 'Illuminate\\Support\\Facades\\Mail',
      'Notification' => 'Illuminate\\Support\\Facades\\Notification',
      'Password' => 'Illuminate\\Support\\Facades\\Password',
      'Queue' => 'Illuminate\\Support\\Facades\\Queue',
      'Redirect' => 'Illuminate\\Support\\Facades\\Redirect',
      'Request' => 'Illuminate\\Support\\Facades\\Request',
      'Response' => 'Illuminate\\Support\\Facades\\Response',
      'Route' => 'Illuminate\\Support\\Facades\\Route',
      'Schema' => 'Illuminate\\Support\\Facades\\Schema',
      'Session' => 'Illuminate\\Support\\Facades\\Session',
      'Storage' => 'Illuminate\\Support\\Facades\\Storage',
      'Str' => 'Illuminate\\Support\\Str',
      'URL' => 'Illuminate\\Support\\Facades\\URL',
      'Validator' => 'Illuminate\\Support\\Facades\\Validator',
      'View' => 'Illuminate\\Support\\Facades\\View',
      'Alert' => 'RealRashid\\SweetAlert\\Facades\\Alert',
      'Datatables' => 'Yajra\\Datatables\\Facades\\Datatables',
      'Guid' => 'Sujip\\Guid\\Guid',
      'PDF' => 'Elibyy\\TCPDF\\Facades\\TCPDF',
      'PdfReport' => 'Jimmyjs\\ReportGenerator\\Facades\\PdfReportFacade',
      'ExcelReport' => 'Jimmyjs\\ReportGenerator\\Facades\\ExcelReportFacade',
      'CSVReport' => 'Jimmyjs\\ReportGenerator\\Facades\\CSVReportFacade',
    ),
  ),
  'auth' => 
  array (
    'defaults' => 
    array (
      'guard' => 'web',
      'passwords' => 'users',
    ),
    'guards' => 
    array (
      'web' => 
      array (
        'driver' => 'session',
        'provider' => 'users',
      ),
      'api' => 
      array (
        'driver' => 'token',
        'provider' => 'users',
        'hash' => false,
      ),
    ),
    'providers' => 
    array (
      'users' => 
      array (
        'driver' => 'eloquent',
        'model' => 'App\\Models\\User',
      ),
    ),
    'passwords' => 
    array (
      'users' => 
      array (
        'provider' => 'users',
        'table' => 'Cust_PasswordResets',
        'expire' => 60,
        'throttle' => 60,
      ),
    ),
    'password_timeout' => 10800,
  ),
  'auth-timeout' => 
  array (
    'session' => 'last_activity_time',
    'timeout' => 240,
  ),
  'azure' => 
  array (
    'tenant_id' => 'common',
    'client' => 
    array (
      'id' => 'a480623a-450d-4ed8-b576-3e7a424bb52c',
      'secret' => 'QSy8Q~vYCg2fL-ORxUS2-Xk9UXwhVWJfXFEnhb1T',
    ),
    'resource' => '',
    'domain_hint' => '',
    'scope' => 'User.Read',
  ),
  'broadcasting' => 
  array (
    'default' => 'pusher',
    'connections' => 
    array (
      'pusher' => 
      array (
        'driver' => 'pusher',
        'key' => 'd4662ba538904d7c90aa',
        'secret' => '5c0c529e82a49781d91b',
        'app_id' => '1235543',
        'options' => 
        array (
          'cluster' => 'us2',
          'encrypted' => true,
        ),
      ),
      'ably' => 
      array (
        'driver' => 'ably',
        'key' => NULL,
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
      ),
      'log' => 
      array (
        'driver' => 'log',
      ),
      'null' => 
      array (
        'driver' => 'null',
      ),
    ),
  ),
  'cache' => 
  array (
    'default' => 'file',
    'stores' => 
    array (
      'apc' => 
      array (
        'driver' => 'apc',
      ),
      'array' => 
      array (
        'driver' => 'array',
        'serialize' => false,
      ),
      'database' => 
      array (
        'driver' => 'database',
        'table' => 'cache',
        'connection' => NULL,
        'lock_connection' => NULL,
      ),
      'file' => 
      array (
        'driver' => 'file',
        'path' => 'C:\\inetpub\\wwwroot\\fit_service_desk\\storage\\framework/cache/data',
      ),
      'memcached' => 
      array (
        'driver' => 'memcached',
        'persistent_id' => NULL,
        'sasl' => 
        array (
          0 => NULL,
          1 => NULL,
        ),
        'options' => 
        array (
        ),
        'servers' => 
        array (
          0 => 
          array (
            'host' => '127.0.0.1',
            'port' => 11211,
            'weight' => 100,
          ),
        ),
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
      ),
      'dynamodb' => 
      array (
        'driver' => 'dynamodb',
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
        'table' => 'cache',
        'endpoint' => NULL,
      ),
      'octane' => 
      array (
        'driver' => 'octane',
      ),
    ),
    'prefix' => 'fit_ticketing_system_cache',
  ),
  'charts' => 
  array (
    'default_library' => 'Chartjs',
    'global_route_prefix' => 'api/chart',
    'global_middlewares' => 
    array (
      0 => 'web',
    ),
    'global_route_name_prefix' => 'charts',
  ),
  'clockwork' => 
  array (
    'enable' => NULL,
    'features' => 
    array (
      'cache' => 
      array (
        'enabled' => true,
        'collect_queries' => true,
        'collect_values' => false,
      ),
      'database' => 
      array (
        'enabled' => true,
        'collect_queries' => true,
        'collect_models_actions' => true,
        'collect_models_retrieved' => false,
        'slow_threshold' => NULL,
        'slow_only' => false,
        'detect_duplicate_queries' => false,
      ),
      'events' => 
      array (
        'enabled' => true,
        'ignored_events' => 
        array (
        ),
      ),
      'log' => 
      array (
        'enabled' => true,
      ),
      'notifications' => 
      array (
        'enabled' => true,
      ),
      'performance' => 
      array (
        'client_metrics' => true,
      ),
      'queue' => 
      array (
        'enabled' => true,
      ),
      'redis' => 
      array (
        'enabled' => true,
      ),
      'routes' => 
      array (
        'enabled' => false,
        'only_namespaces' => 
        array (
          0 => 'App',
        ),
      ),
      'views' => 
      array (
        'enabled' => true,
        'collect_data' => false,
        'use_twig_profiler' => false,
      ),
    ),
    'web' => true,
    'toolbar' => true,
    'requests' => 
    array (
      'on_demand' => false,
      'errors_only' => false,
      'slow_threshold' => NULL,
      'slow_only' => false,
      'sample' => false,
      'except' => 
      array (
        0 => '/horizon/.*',
        1 => '/telescope/.*',
        2 => '/_debugbar/.*',
      ),
      'only' => 
      array (
      ),
      'except_preflight' => true,
    ),
    'artisan' => 
    array (
      'collect' => false,
      'except' => 
      array (
      ),
      'only' => 
      array (
      ),
      'collect_output' => false,
      'except_laravel_commands' => true,
    ),
    'queue' => 
    array (
      'collect' => false,
      'except' => 
      array (
      ),
      'only' => 
      array (
      ),
    ),
    'tests' => 
    array (
      'collect' => false,
      'except' => 
      array (
      ),
    ),
    'collect_data_always' => false,
    'storage' => 'files',
    'storage_files_path' => 'C:\\inetpub\\wwwroot\\fit_service_desk\\storage\\clockwork',
    'storage_files_compress' => false,
    'storage_sql_database' => 'C:\\inetpub\\wwwroot\\fit_service_desk\\storage\\clockwork.sqlite',
    'storage_sql_table' => 'clockwork',
    'storage_expiration' => 10080,
    'authentication' => false,
    'authentication_password' => 'VerySecretPassword',
    'stack_traces' => 
    array (
      'enabled' => true,
      'limit' => 10,
      'skip_vendors' => 
      array (
      ),
      'skip_namespaces' => 
      array (
      ),
      'skip_classes' => 
      array (
      ),
    ),
    'serialization_depth' => 10,
    'serialization_blackbox' => 
    array (
      0 => 'Illuminate\\Container\\Container',
      1 => 'Illuminate\\Foundation\\Application',
      2 => 'Laravel\\Lumen\\Application',
    ),
    'register_helpers' => true,
    'headers' => 
    array (
    ),
    'server_timing' => 10,
  ),
  'cors' => 
  array (
    'paths' => 
    array (
      0 => 'api/*',
      1 => 'sanctum/csrf-cookie',
    ),
    'allowed_methods' => 
    array (
      0 => '*',
    ),
    'allowed_origins' => 
    array (
      0 => '*',
    ),
    'allowed_origins_patterns' => 
    array (
    ),
    'allowed_headers' => 
    array (
      0 => '*',
    ),
    'exposed_headers' => 
    array (
    ),
    'max_age' => 0,
    'supports_credentials' => false,
  ),
  'database' => 
  array (
    'default' => 'sqlsrv',
    'connections' => 
    array (
      'sqlite' => 
      array (
        'driver' => 'sqlite',
        'url' => 'sqlsrv:serve=10.0.1.11,60780;Database=FITSYS;',
        'database' => 'C:\\inetpub\\wwwroot\\fit_service_desk\\database\\database.sqlite',
        'prefix' => '',
        'foreign_key_constraints' => true,
      ),
      'mysql' => 
      array (
        'driver' => 'mysql',
        'url' => 'sqlsrv:serve=10.0.1.11,60780;Database=FITSYS;',
        'host' => '10.0.1.11',
        'port' => '3306',
        'database' => 'forge',
        'username' => 'forge',
        'password' => '',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => NULL,
        'options' => 
        array (
        ),
      ),
      'pgsql' => 
      array (
        'driver' => 'pgsql',
        'url' => 'sqlsrv:serve=10.0.1.11,60780;Database=FITSYS;',
        'host' => '10.0.1.11',
        'port' => '5432',
        'database' => 'forge',
        'username' => 'forge',
        'password' => '',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
        'schema' => 'public',
        'sslmode' => 'prefer',
      ),
      'sqlsrv' => 
      array (
        'driver' => 'sqlsrv',
        'url' => 'sqlsrv:serve=10.0.1.11,60780;Database=FITSYS;',
        'host' => '10.0.1.11',
        'database' => 'FITSYS',
      ),
      'efris' => 
      array (
        'driver' => 'sqlsrv',
        'dsn' => NULL,
        'host' => NULL,
        'port' => '60734',
        'database' => 'EFRIS',
        'username' => 'CustomerService',
        'password' => 'Freight@2021',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
      ),
    ),
    'migrations' => 'migrations',
    'redis' => 
    array (
      'client' => 'phpredis',
      'options' => 
      array (
        'cluster' => 'redis',
        'prefix' => 'fit_ticketing_system_database_',
      ),
      'default' => 
      array (
        'url' => NULL,
        'host' => '127.0.0.1',
        'password' => NULL,
        'port' => '6379',
        'database' => '0',
      ),
      'cache' => 
      array (
        'url' => NULL,
        'host' => '127.0.0.1',
        'password' => NULL,
        'port' => '6379',
        'database' => '1',
      ),
    ),
  ),
  'datatables' => 
  array (
    'search' => 
    array (
      'smart' => true,
      'multi_term' => true,
      'case_insensitive' => true,
      'use_wildcards' => false,
      'starts_with' => false,
    ),
    'index_column' => 'DT_RowIndex',
    'engines' => 
    array (
      'eloquent' => 'Yajra\\DataTables\\EloquentDataTable',
      'query' => 'Yajra\\DataTables\\QueryDataTable',
      'collection' => 'Yajra\\DataTables\\CollectionDataTable',
      'resource' => 'Yajra\\DataTables\\ApiResourceDataTable',
    ),
    'builders' => 
    array (
    ),
    'nulls_last_sql' => ':column :direction NULLS LAST',
    'error' => NULL,
    'columns' => 
    array (
      'excess' => 
      array (
        0 => 'rn',
        1 => 'row_num',
      ),
      'escape' => '*',
      'raw' => 
      array (
        0 => 'action',
      ),
      'blacklist' => 
      array (
        0 => 'password',
        1 => 'remember_token',
      ),
      'whitelist' => '*',
    ),
    'json' => 
    array (
      'header' => 
      array (
      ),
      'options' => 0,
    ),
  ),
  'debugbar' => 
  array (
    'enabled' => NULL,
    'except' => 
    array (
      0 => 'telescope*',
      1 => 'horizon*',
    ),
    'storage' => 
    array (
      'enabled' => true,
      'driver' => 'file',
      'path' => 'C:\\inetpub\\wwwroot\\fit_service_desk\\storage\\debugbar',
      'connection' => NULL,
      'provider' => '',
      'hostname' => '127.0.0.1',
      'port' => 2304,
    ),
    'include_vendors' => true,
    'capture_ajax' => true,
    'add_ajax_timing' => false,
    'error_handler' => false,
    'clockwork' => false,
    'collectors' => 
    array (
      'phpinfo' => true,
      'messages' => true,
      'time' => true,
      'memory' => true,
      'exceptions' => true,
      'log' => true,
      'db' => true,
      'views' => true,
      'route' => true,
      'auth' => false,
      'gate' => true,
      'session' => true,
      'symfony_request' => true,
      'mail' => true,
      'laravel' => false,
      'events' => false,
      'default_request' => false,
      'logs' => false,
      'files' => false,
      'config' => false,
      'cache' => false,
      'models' => true,
      'livewire' => true,
    ),
    'options' => 
    array (
      'auth' => 
      array (
        'show_name' => true,
      ),
      'db' => 
      array (
        'with_params' => true,
        'backtrace' => true,
        'backtrace_exclude_paths' => 
        array (
        ),
        'timeline' => false,
        'duration_background' => true,
        'explain' => 
        array (
          'enabled' => false,
          'types' => 
          array (
            0 => 'SELECT',
          ),
        ),
        'hints' => false,
        'show_copy' => false,
      ),
      'mail' => 
      array (
        'full_log' => false,
      ),
      'views' => 
      array (
        'timeline' => false,
        'data' => false,
      ),
      'route' => 
      array (
        'label' => true,
      ),
      'logs' => 
      array (
        'file' => NULL,
      ),
      'cache' => 
      array (
        'values' => true,
      ),
    ),
    'inject' => true,
    'route_prefix' => '_debugbar',
    'route_domain' => NULL,
    'theme' => 'auto',
    'debug_backtrace_limit' => 50,
  ),
  'dompdf' => 
  array (
    'show_warnings' => false,
    'orientation' => 'portrait',
    'defines' => 
    array (
      'font_dir' => 'C:\\inetpub\\wwwroot\\fit_service_desk\\storage\\fonts/',
      'font_cache' => 'C:\\inetpub\\wwwroot\\fit_service_desk\\storage\\fonts/',
      'temp_dir' => 'C:\\Users\\FITERP~1\\AppData\\Local\\Temp\\3',
      'chroot' => 'C:\\inetpub\\wwwroot\\fit_service_desk',
      'enable_font_subsetting' => false,
      'pdf_backend' => 'CPDF',
      'default_media_type' => 'screen',
      'default_paper_size' => 'a4',
      'default_font' => 'serif',
      'dpi' => 96,
      'enable_php' => false,
      'enable_javascript' => true,
      'enable_remote' => true,
      'font_height_ratio' => 1.1,
      'enable_html5_parser' => false,
    ),
  ),
  'excel' => 
  array (
    'exports' => 
    array (
      'chunk_size' => 1000,
      'pre_calculate_formulas' => false,
      'strict_null_comparison' => false,
      'csv' => 
      array (
        'delimiter' => ',',
        'enclosure' => '"',
        'line_ending' => '
',
        'use_bom' => false,
        'include_separator_line' => false,
        'excel_compatibility' => false,
      ),
      'properties' => 
      array (
        'creator' => '',
        'lastModifiedBy' => '',
        'title' => '',
        'description' => '',
        'subject' => '',
        'keywords' => '',
        'category' => '',
        'manager' => '',
        'company' => '',
      ),
    ),
    'imports' => 
    array (
      'read_only' => true,
      'ignore_empty' => false,
      'heading_row' => 
      array (
        'formatter' => 'slug',
      ),
      'csv' => 
      array (
        'delimiter' => ',',
        'enclosure' => '"',
        'escape_character' => '\\',
        'contiguous' => false,
        'input_encoding' => 'UTF-8',
      ),
      'properties' => 
      array (
        'creator' => '',
        'lastModifiedBy' => '',
        'title' => '',
        'description' => '',
        'subject' => '',
        'keywords' => '',
        'category' => '',
        'manager' => '',
        'company' => '',
      ),
    ),
    'extension_detector' => 
    array (
      'xlsx' => 'Xlsx',
      'xlsm' => 'Xlsx',
      'xltx' => 'Xlsx',
      'xltm' => 'Xlsx',
      'xls' => 'Xls',
      'xlt' => 'Xls',
      'ods' => 'Ods',
      'ots' => 'Ods',
      'slk' => 'Slk',
      'xml' => 'Xml',
      'gnumeric' => 'Gnumeric',
      'htm' => 'Html',
      'html' => 'Html',
      'csv' => 'Csv',
      'tsv' => 'Csv',
      'pdf' => 'Dompdf',
    ),
    'value_binder' => 
    array (
      'default' => 'Maatwebsite\\Excel\\DefaultValueBinder',
    ),
    'cache' => 
    array (
      'driver' => 'memory',
      'batch' => 
      array (
        'memory_limit' => 60000,
      ),
      'illuminate' => 
      array (
        'store' => NULL,
      ),
    ),
    'transactions' => 
    array (
      'handler' => 'db',
    ),
    'temporary_files' => 
    array (
      'local_path' => 'C:\\inetpub\\wwwroot\\fit_service_desk\\storage\\framework/laravel-excel',
      'remote_disk' => NULL,
      'remote_prefix' => NULL,
      'force_resync_remote' => NULL,
    ),
  ),
  'filesystems' => 
  array (
    'default' => 'local',
    'disks' => 
    array (
      'local' => 
      array (
        'driver' => 'local',
        'root' => 'C:\\inetpub\\wwwroot\\fit_service_desk\\storage\\app',
      ),
      'public' => 
      array (
        'driver' => 'local',
        'root' => 'C:\\inetpub\\wwwroot\\fit_service_desk\\storage\\app/public',
        'url' => 'https://erp.freightintime.com/storage',
        'visibility' => 'public',
      ),
      's3' => 
      array (
        'driver' => 's3',
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
        'bucket' => '',
        'url' => NULL,
        'endpoint' => NULL,
        'use_path_style_endpoint' => false,
      ),
      'azure' => 
      array (
        'driver' => 'azure',
        'local_address' => 'https://scmprofitliverepo1.blob.core.windows.net',
        'name' => 'scmprofitliverepo1',
        'key' => 'XSgD/VulJRaCw+PlbC13Y2hUmidSfeuh2CNTAUtbGuC8MURwUlZD6r8b8ef44gcUnAsBAJl5Xd66+AStO19ypg==',
        'container' => 'fitrepository',
        'prefix' => NULL,
        'url' => NULL,
      ),
    ),
    'links' => 
    array (
      'C:\\inetpub\\wwwroot\\fit_service_desk\\public\\storage' => 'C:\\inetpub\\wwwroot\\fit_service_desk\\storage\\app/public',
    ),
  ),
  'flare' => 
  array (
    'key' => NULL,
    'reporting' => 
    array (
      'anonymize_ips' => true,
      'collect_git_information' => false,
      'report_queries' => true,
      'maximum_number_of_collected_queries' => 200,
      'report_query_bindings' => true,
      'report_view_data' => true,
      'grouping_type' => NULL,
      'report_logs' => true,
      'maximum_number_of_collected_logs' => 200,
      'censor_request_body_fields' => 
      array (
        0 => 'password',
      ),
    ),
    'send_logs_as_events' => true,
    'censor_request_body_fields' => 
    array (
      0 => 'password',
    ),
  ),
  'hashing' => 
  array (
    'driver' => 'bcrypt',
    'bcrypt' => 
    array (
      'rounds' => 10,
    ),
    'argon' => 
    array (
      'memory' => 1024,
      'threads' => 2,
      'time' => 2,
    ),
  ),
  'htmlmin' => 
  array (
    'blade' => true,
    'force' => false,
    'ignore' => 
    array (
      0 => 'resources/views/emails',
      1 => 'resources/views/html',
      2 => 'resources/views/notifications',
      3 => 'resources/views/markdown',
      4 => 'resources/views/vendor/emails',
      5 => 'resources/views/vendor/html',
      6 => 'resources/views/vendor/notifications',
      7 => 'resources/views/vendor/markdown',
    ),
  ),
  'ignition' => 
  array (
    'editor' => 'phpstorm',
    'theme' => 'light',
    'enable_share_button' => true,
    'register_commands' => false,
    'ignored_solution_providers' => 
    array (
      0 => 'Facade\\Ignition\\SolutionProviders\\MissingPackageSolutionProvider',
    ),
    'enable_runnable_solutions' => NULL,
    'remote_sites_path' => '',
    'local_sites_path' => '',
    'housekeeping_endpoint_prefix' => '_ignition',
  ),
  'laravel-model-caching' => 
  array (
    'cache-prefix' => '',
    'enabled' => true,
    'use-database-keying' => true,
    'store' => NULL,
  ),
  'laravel-page-speed' => 
  array (
    'enable' => true,
    'skip' => 
    array (
      0 => '*.xml',
      1 => '*.less',
      2 => '*.pdf',
      3 => '*.doc',
      4 => '*.txt',
      5 => '*.ico',
      6 => '*.rss',
      7 => '*.zip',
      8 => '*.mp3',
      9 => '*.rar',
      10 => '*.exe',
      11 => '*.wmv',
      12 => '*.doc',
      13 => '*.avi',
      14 => '*.ppt',
      15 => '*.mpg',
      16 => '*.mpeg',
      17 => '*.tif',
      18 => '*.wav',
      19 => '*.mov',
      20 => '*.psd',
      21 => '*.ai',
      22 => '*.xls',
      23 => '*.mp4',
      24 => '*.m4a',
      25 => '*.swf',
      26 => '*.dat',
      27 => '*.dmg',
      28 => '*.iso',
      29 => '*.flv',
      30 => '*.m4v',
      31 => '*.torrent',
    ),
    'php' => 
    array (
      'enable' => true,
      'skip' => 
      array (
        0 => '*.xml',
        1 => '*.less',
        2 => '*.pdf',
        3 => '*.doc',
        4 => '*.txt',
        5 => '*.ico',
        6 => '*.rss',
        7 => '*.zip',
        8 => '*.mp3',
        9 => '*.rar',
        10 => '*.exe',
        11 => '*.wmv',
        12 => '*.doc',
        13 => '*.avi',
        14 => '*.ppt',
        15 => '*.mpg',
        16 => '*.mpeg',
        17 => '*.tif',
        18 => '*.wav',
        19 => '*.mov',
        20 => '*.psd',
        21 => '*.ai',
        22 => '*.xls',
        23 => '*.mp4',
        24 => '*.m4a',
        25 => '*.swf',
        26 => '*.dat',
        27 => '*.dmg',
        28 => '*.iso',
        29 => '*.flv',
        30 => '*.m4v',
        31 => '*.torrent',
      ),
    ),
  ),
  'laravel-query-monitor' => 
  array (
    'enable' => true,
    'host' => '0.0.0.0',
    'port' => 8081,
  ),
  'ldap' => 
  array (
    'logging' => false,
    'connections' => 
    array (
      'default' => 
      array (
        'auto_connect' => false,
        'connection' => 'Adldap\\Connections\\Ldap',
        'settings' => 
        array (
          'schema' => 'Adldap\\Schemas\\ActiveDirectory',
          'account_prefix' => '',
          'account_suffix' => '',
          'hosts' => 
          array (
            0 => 'corp-dc1.corp.acme.org',
            1 => 'corp-dc2.corp.acme.org',
          ),
          'port' => 389,
          'timeout' => 5,
          'base_dn' => 'dc=corp,dc=acme,dc=org',
          'username' => 'username',
          'password' => 'secret',
          'follow_referrals' => false,
          'use_ssl' => false,
          'use_tls' => false,
        ),
      ),
    ),
  ),
  'ldap_auth' => 
  array (
    'connection' => 'default',
    'provider' => 'Adldap\\Laravel\\Auth\\DatabaseUserProvider',
    'model' => 'App\\Models\\User',
    'rules' => 
    array (
      0 => 'Adldap\\Laravel\\Validation\\Rules\\DenyTrashed',
    ),
    'scopes' => 
    array (
    ),
    'identifiers' => 
    array (
      'ldap' => 
      array (
        'locate_users_by' => 'samaccountname',
        'bind_users_by' => 'distinguishedname',
      ),
      'database' => 
      array (
        'guid_column' => 'objectguid',
        'username_column' => 'LoginID',
      ),
      'windows' => 
      array (
        'locate_users_by' => 'samaccountname',
        'server_key' => 'AUTH_USER',
      ),
    ),
    'passwords' => 
    array (
      'sync' => false,
      'column' => 'password',
    ),
    'login_fallback' => true,
    'sync_attributes' => 
    array (
      'email' => 'LoginID',
      'FirstName' => 'givenname',
      'LastName' => 'sn',
    ),
    'logging' => 
    array (
      'enabled' => true,
      'events' => 
      array (
        'Adldap\\Laravel\\Events\\Importing' => 'Adldap\\Laravel\\Listeners\\LogImport',
        'Adldap\\Laravel\\Events\\Synchronized' => 'Adldap\\Laravel\\Listeners\\LogSynchronized',
        'Adldap\\Laravel\\Events\\Synchronizing' => 'Adldap\\Laravel\\Listeners\\LogSynchronizing',
        'Adldap\\Laravel\\Events\\Authenticated' => 'Adldap\\Laravel\\Listeners\\LogAuthenticated',
        'Adldap\\Laravel\\Events\\Authenticating' => 'Adldap\\Laravel\\Listeners\\LogAuthentication',
        'Adldap\\Laravel\\Events\\AuthenticationFailed' => 'Adldap\\Laravel\\Listeners\\LogAuthenticationFailure',
        'Adldap\\Laravel\\Events\\AuthenticationRejected' => 'Adldap\\Laravel\\Listeners\\LogAuthenticationRejection',
        'Adldap\\Laravel\\Events\\AuthenticationSuccessful' => 'Adldap\\Laravel\\Listeners\\LogAuthenticationSuccess',
        'Adldap\\Laravel\\Events\\DiscoveredWithCredentials' => 'Adldap\\Laravel\\Listeners\\LogDiscovery',
        'Adldap\\Laravel\\Events\\AuthenticatedWithWindows' => 'Adldap\\Laravel\\Listeners\\LogWindowsAuth',
        'Adldap\\Laravel\\Events\\AuthenticatedModelTrashed' => 'Adldap\\Laravel\\Listeners\\LogTrashedModel',
      ),
    ),
  ),
  'logging' => 
  array (
    'default' => 'stack',
    'channels' => 
    array (
      'stack' => 
      array (
        'driver' => 'stack',
        'channels' => 
        array (
          0 => 'single',
        ),
        'ignore_exceptions' => false,
      ),
      'single' => 
      array (
        'driver' => 'single',
        'path' => 'C:\\inetpub\\wwwroot\\fit_service_desk\\storage\\logs/laravel.log',
        'level' => 'debug',
      ),
      'daily' => 
      array (
        'driver' => 'daily',
        'path' => 'C:\\inetpub\\wwwroot\\fit_service_desk\\storage\\logs/laravel.log',
        'level' => 'debug',
        'days' => 14,
      ),
      'slack' => 
      array (
        'driver' => 'slack',
        'url' => NULL,
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => 'debug',
      ),
      'papertrail' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\SyslogUdpHandler',
        'handler_with' => 
        array (
          'host' => NULL,
          'port' => NULL,
        ),
      ),
      'stderr' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\StreamHandler',
        'formatter' => NULL,
        'with' => 
        array (
          'stream' => 'php://stderr',
        ),
      ),
      'syslog' => 
      array (
        'driver' => 'syslog',
        'level' => 'debug',
      ),
      'errorlog' => 
      array (
        'driver' => 'errorlog',
        'level' => 'debug',
      ),
      'null' => 
      array (
        'driver' => 'monolog',
        'handler' => 'Monolog\\Handler\\NullHandler',
      ),
      'emergency' => 
      array (
        'path' => 'C:\\inetpub\\wwwroot\\fit_service_desk\\storage\\logs/laravel.log',
      ),
    ),
  ),
  'logviewer' => 
  array (
    'max_file_size' => 52428800,
    'pattern' => '*.log',
    'storage_path' => 'C:\\inetpub\\wwwroot\\fit_service_desk\\storage\\logs',
  ),
  'mail' => 
  array (
    'default' => 'smtp',
    'mailers' => 
    array (
      'smtp' => 
      array (
        'transport' => 'smtp',
        'host' => 'mail.freight-in-time.com',
        'port' => '465',
        'encryption' => 'ssl',
        'username' => 'fitexpress@freight-in-time.com',
        'password' => 'rtz=Vu$mW-m*',
        'timeout' => NULL,
        'auth_mode' => NULL,
        'from' => 
        array (
          'address' => 'fitexpress@freight-in-time.com',
          'name' => 'FIT Ticketing System',
        ),
      ),
      'stream' => 
      array (
        'ssl' => 
        array (
          'allow_self_signed' => true,
          'verify_peer' => false,
          'verify_peer_name' => false,
        ),
      ),
      'ses' => 
      array (
        'transport' => 'ses',
      ),
      'mailgun' => 
      array (
        'transport' => 'mailgun',
      ),
      'postmark' => 
      array (
        'transport' => 'postmark',
      ),
      'sendmail' => 
      array (
        'transport' => 'sendmail',
        'path' => '/usr/sbin/sendmail -bs',
      ),
      'log' => 
      array (
        'transport' => 'log',
        'channel' => NULL,
      ),
      'array' => 
      array (
        'transport' => 'array',
      ),
    ),
    'from' => 
    array (
      'address' => 'fitexpress@freight-in-time.com',
      'name' => 'FIT Ticketing System',
    ),
    'markdown' => 
    array (
      'theme' => 'default',
      'paths' => 
      array (
        0 => 'C:\\inetpub\\wwwroot\\fit_service_desk\\resources\\views/vendor/mail',
      ),
    ),
  ),
  'notify' => 
  array (
    'theme' => 'light',
    'demo' => true,
    'timeout' => 5000,
    'preset-messages' => 
    array (
      'user-updated' => 
      array (
        'message' => 'The user has been updated successfully.',
        'type' => 'success',
        'model' => 'connect',
        'title' => 'User Updated',
      ),
    ),
  ),
  'permission' => 
  array (
    'models' => 
    array (
      'permission' => 'Spatie\\Permission\\Models\\Permission',
      'role' => 'Spatie\\Permission\\Models\\Role',
    ),
    'table_names' => 
    array (
      'roles' => 'Cust_Roles',
      'permissions' => 'Cust_Permissions',
      'model_has_permissions' => 'Cust_Model_Has_Permissions',
      'model_has_roles' => 'Cust_Model_Has_Roles',
      'role_has_permissions' => 'Cust_Role_Has_permissions',
    ),
    'column_names' => 
    array (
      'model_morph_key' => 'model_uuid',
    ),
    'display_permission_in_exception' => false,
    'display_role_in_exception' => false,
    'enable_wildcard_permission' => false,
    'cache' => 
    array (
      'expiration_time' => 
      DateInterval::__set_state(array(
         'y' => 0,
         'm' => 0,
         'd' => 0,
         'h' => 24,
         'i' => 0,
         's' => 0,
         'f' => 0.0,
         'weekday' => 0,
         'weekday_behavior' => 0,
         'first_last_day_of' => 0,
         'invert' => 0,
         'days' => false,
         'special_type' => 0,
         'special_amount' => 0,
         'have_weekday_relative' => 0,
         'have_special_relative' => 0,
      )),
      'key' => 'spatie.permission.cache',
      'model_key' => 'name',
      'store' => 'default',
    ),
  ),
  'queue' => 
  array (
    'default' => 'sync',
    'connections' => 
    array (
      'sync' => 
      array (
        'driver' => 'sync',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'table' => 'Cust_Jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,
      ),
      'beanstalkd' => 
      array (
        'driver' => 'beanstalkd',
        'host' => 'localhost',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => 0,
        'after_commit' => false,
      ),
      'sqs' => 
      array (
        'driver' => 'sqs',
        'key' => '',
        'secret' => '',
        'prefix' => 'https://sqs.us-east-1.amazonaws.com/your-account-id',
        'queue' => 'default',
        'suffix' => NULL,
        'region' => 'us-east-1',
        'after_commit' => false,
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => NULL,
        'after_commit' => false,
      ),
    ),
    'failed' => 
    array (
      'driver' => 'database-uuids',
      'database' => 'sqlsrv',
      'table' => 'Cust_Failed_Jobs',
    ),
  ),
  'report-generator' => 
  array (
    'flush' => false,
    'pdfLibrary' => 'dompdf',
  ),
  'services' => 
  array (
    'mailgun' => 
    array (
      'domain' => NULL,
      'secret' => NULL,
      'endpoint' => 'api.mailgun.net',
    ),
    'postmark' => 
    array (
      'token' => NULL,
    ),
    'ses' => 
    array (
      'key' => '',
      'secret' => '',
      'region' => 'us-east-1',
    ),
  ),
  'session' => 
  array (
    'driver' => 'file',
    'lifetime' => '120',
    'expire_on_close' => true,
    'encrypt' => false,
    'files' => 'C:\\inetpub\\wwwroot\\fit_service_desk\\storage\\framework/sessions',
    'connection' => NULL,
    'table' => 'sessions',
    'store' => NULL,
    'lottery' => 
    array (
      0 => 2,
      1 => 100,
    ),
    'cookie' => 'fit_ticketing_system_session',
    'path' => '/',
    'domain' => NULL,
    'secure' => false,
    'http_only' => true,
    'same_site' => 'lax',
  ),
  'sweetalert' => 
  array (
    'cdn' => NULL,
    'alwaysLoadJS' => false,
    'neverLoadJS' => false,
    'timer' => 3000,
    'width' => '32rem',
    'height_auto' => true,
    'padding' => '1.25rem',
    'animation' => 
    array (
      'enable' => true,
    ),
    'animatecss' => 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css',
    'show_confirm_button' => true,
    'show_close_button' => false,
    'toast_position' => 'top-end',
    'timer_progress_bar' => false,
    'middleware' => 
    array (
      'autoClose' => false,
      'toast_position' => 'top-end',
      'toast_close_button' => false,
      'timer' => 6000,
      'auto_display_error_messages' => false,
    ),
    'customClass' => 
    array (
      'container' => NULL,
      'popup' => NULL,
      'header' => NULL,
      'title' => NULL,
      'closeButton' => NULL,
      'icon' => NULL,
      'image' => NULL,
      'content' => NULL,
      'input' => NULL,
      'actions' => NULL,
      'confirmButton' => NULL,
      'cancelButton' => NULL,
      'footer' => NULL,
    ),
  ),
  'tcpdf' => 
  array (
    'page_format' => 'A4',
    'page_orientation' => 'P',
    'page_units' => 'mm',
    'unicode' => true,
    'encoding' => 'UTF-8',
    'font_directory' => '',
    'image_directory' => '/public/img',
    'tcpdf_throw_exception' => true,
    'use_fpdi' => false,
    'use_original_header' => false,
    'use_original_footer' => false,
    'pdfa' => false,
    'creator' => 'Freight In Time',
    'author' => 'Freight In Time',
    'tcpdf_calls_in_html' => true,
    'timezone' => 'Africa/Nairobi',
  ),
  'tims' => 
  array (
    'local_invoice_path' => '',
    'production_invoice_path' => '',
  ),
  'tinker' => 
  array (
    'commands' => 
    array (
    ),
    'alias' => 
    array (
    ),
    'dont_alias' => 
    array (
      0 => 'App\\Nova',
    ),
  ),
  'trustedproxy' => 
  array (
    'proxies' => NULL,
    'headers' => 94,
  ),
  'view' => 
  array (
    'paths' => 
    array (
      0 => 'C:\\inetpub\\wwwroot\\fit_service_desk\\resources\\views',
    ),
    'compiled' => 'C:\\inetpub\\wwwroot\\fit_service_desk\\storage\\framework\\views',
  ),
  'webpush' => 
  array (
    'vapid' => 
    array (
      'subject' => NULL,
      'public_key' => NULL,
      'private_key' => NULL,
      'pem_file' => NULL,
    ),
    'model' => 'NotificationChannels\\WebPush\\PushSubscription',
    'table_name' => 'Cust_PushSubscriptions',
    'database_connection' => 'sqlsrv',
    'client_options' => 
    array (
    ),
    'gcm' => 
    array (
      'key' => NULL,
      'sender_id' => NULL,
    ),
  ),
);
