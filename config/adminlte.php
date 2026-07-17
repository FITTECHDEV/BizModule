<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'FIT SERVICE DESK',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => '<b> SERVICE </b> DESK',
    'logo_img' => 'site_imgs/banner.gif',
    // 'logo_img' => '',
    'logo_img_class' => 'brand-image elevation-2',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'FIT',

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-purple',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => true,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => true,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-purple shadow',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat bg-purple shadow-lg',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

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

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'xs',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => true,
    'sidebar_collapse_remember' => true,
    'sidebar_collapse_remember_no_transition' => true,
    // 'os-theme-light', 'os-theme-dark' or 'os-theme-none'
    'sidebar_scrollbar_theme' => 'os-theme-dark',
    'sidebar_scrollbar_auto_hide' => 'm',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,
    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'white',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => 'home',
    'logout_url' => '/logout',
    // 'logout_method' => 'GET',
    'login_url' => '/login',
    'register_url' => 'register',
    // 'password_reset_url' => 'password/reset',
    'password_reset_url' => 'forget-password',
    // 'password_email_url' => 'password/email',
    'password_email_url' => 'reset-password',
    'profile_url' => true,

    /*
    |--------------------------------------------------------------------------
    | Laravel Mix
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Mix option for the admin panel.
    |
    | For detailed instructions you can look the laravel mix section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'enabled_laravel_mix' => true,
    'laravel_mix_css_path' => 'css/app.css',
    'laravel_mix_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => [
        // Navbar items:
        [
            'text' => 'Dashboard',
            'url' => 'home',
            'icon' => 'fas fa-tachometer-alt',
            // 'can'  => 'admin-view'
        ],
        [
            'text' => ' Tickets',
            'url' => '/#',
            'icon' => 'fas fa-fw fa-ticket-alt  ',
            'active' => ['tickets/*'],
            // 'can' => 'tickets-view',
            'submenu' => [
                [
                    'text' => 'Home',
                    'url' => '/tickets',
                    'icon' => 'fas fa-fw fa-home',
                    'can' => 'admin-view',
                ],
                // [
                //     'text' => 'Raise Ticket',
                //     'route'  => 'tickets.create',
                //     'icon' => 'fas fa-fw fa-plus',
                //     'can'  => 'ticket-raise',
                //     'active' => ['tickets/create']
                // ],
                [
                    'text' => 'My Tickets',
                    'route' => 'tickets.my_tickets',
                    'icon' => 'fas fa-fw fa-ticket-alt',
                    'can' => 'my-ticket-list',
                    'active' => ['tickets/my-tickets'],
                ],
                [
                    'text' => 'My Office Tickets',
                    'route' => 'tickets.my_office_tickets',
                    'icon' => 'fas fa-fw fa-building',
                    'can' => 'view-my-office-tickets',
                    'active' => ['tickets/my-office-tickets'],
                ],
                [
                    'text' => 'Assigned',
                    'route' => 'tickets.assigned',
                    'icon' => 'fas fa-fw fa-ticket-alt',
                    'active' => ['tickets/assigned'],
                    'can' => 'view-assigned-tickets',
                ],
                [
                    'text' => 'Dashboards',
                    'route' => 'reports.index',
                    'icon' => 'fas fa-fw fa-chart-bar',
                    'active' => ['reports/*'],
                    'can' => 'view-ticket-reports-dashboard',
                ],
                [
                    'text' => ' Reports',
                    'route' => 'archive',
                    'icon' => 'fas fa-fw fa-file-alt',
                    'active' => ['archive/*'],
                    'can' => 'view-ticket-reports',
                ],
            ],
        ],
        [
            'key' => 'client_party',
            'text' => 'Client Party Request',
            'url' => '/#',
            'icon' => 'fas fa-fw fa-handshake ',
            'active' => ['client-party/*'],
            'can' => 'view-client-party',
            'submenu' => [
                [
                    'text' => ' Requests',
                    'url' => 'client-party?submitted_request=true',
                    'icon' => 'fas fa-handshake',
                    'active' => ['client-party?submitted_request=true'],
                    // 'can'  => 'create-client-party',
                ],
                [
                    'text' => 'Request Client Party',
                    'url' => 'client-party/create',
                    'icon' => 'fas fa-fw fa-hand-paper ',
                    'active' => ['client-party/create'],
                    'can' => 'request-client-party',
                ],
                // [
                //     'text' => 'My Requests',
                //     'url'  => 'client-party?my_request=true',
                //     'icon' => 'fas fa-fw fa-hand-paper ',
                //     'active' => ['client-party?my_request=true'],
                //     'can'  => 'request-client-party',
                // ],
            ],
        ],
        [
            'text' => 'TIMS',
            'url' => 'tims',
            'icon' => 'fa fa-fw fa-calculator',
            'can' => 'tims-access',
        ],
        [
            'text' => 'EFRIS',
            'url' => 'efris',
            'icon' => 'fa fa-fw fa-dollar',
            'can' => 'efris-access',
        ],

        [
            'text' => 'LOGIN TRACKER',
            'url' => 'dashboard',
            'icon' => 'fa fa-fw fa-user',
            'can' => 'efris-access',
        ],

        [
            'text' => 'AZURE AD MANAGEMENT',
            'url' => 'azure/dashboard',
            'icon' => 'fab fa-fw fa-microsoft',
            'active' => ['azure/*'],
        ],

        [
            'text' => 'SHIPMENT EMAIL REPORTS',
            'url' => 'shipment-reports',
            'icon' => 'fas fa-fw fa-envelope',
            'active' => ['shipment-reports/*'],
            'can' => 'approve-all-entity-request',
        ],

        [
            'text' => 'QUOTATION',
            'url' => '/#',
            'icon' => 'fas fa-fw fa-file-invoice',
            'active' => ['quotation', 'quotation/*'],
            'can' => 'view-quotation',
            'submenu' => [
                [
                    'text' => 'Quotations',
                    'url' => 'quotation',
                    'icon' => 'fas fa-fw fa-file-invoice',
                    'active' => ['quotation'],
                    'can' => 'view-quotation',
                ],
                [
                    'text' => 'Website Requests',
                    'url' => 'quotation/website-requests',
                    'icon' => 'fas fa-fw fa-globe',
                    'active' => ['quotation/website-requests'],
                    'can' => 'view-quotation',
                ],
                [
                    'text' => 'Approvals',
                    'url' => 'quotation/approvals',
                    'icon' => 'fas fa-fw fa-check-double',
                    'active' => ['quotation/approvals'],
                    'can' => 'view-quotation',
                ],
                [
                    'text' => 'Contract Management',
                    'url' => 'contracts/create',
                    'icon' => 'fas fa-fw fa-file-signature',
                    'active' => ['contracts', 'contracts/*'],
                    'can' => 'view-quotation',
                ],
            ],
        ],

        // ↓ NEW ENTRY ↓
        [
            'text' => 'Z-REPORT RUNNER',
            'url' => 'zreport',
            'icon' => 'fas fa-file-signature',
            'active' => ['zreport', 'zreport/*'],
            'can' => 'approve-all-entity-request',
        ],

        [
            'text' => 'WEIGHT NOTE',
            'url' => 'weight-note',
            'icon' => 'fas fa-weight-hanging',
            'can' => 'approve-all-entity-request',
        ],

        // ['header' => 'account_settings'],
        [
            'text' => 'SETTINGS',
            'url' => '/#',
            'icon' => 'fas fa-fw fa-cogs ',
            'active' => ['settings/*'],
            // 'can'  => 'view-access-settings',
            'submenu' => [
                [
                    'text' => 'change_password',
                    'route' => 'user.get_change_password',
                    'icon' => 'fas fa-fw fa-lock',
                    'can' => 'change-user-password',
                ],
                [
                    'text' => 'Roles Management',
                    'url' => 'roles',
                    'icon' => 'fas fa-tasks',
                    'active' => ['roles/*'],
                    'can' => 'roles-list',
                ],
                [
                    'text' => 'User Management',
                    'url' => 'users',
                    'icon' => 'fas fa-fw fa-users',
                    'active' => ['users/*'],
                    'can' => 'users-list',
                ],

                [
                    'text' => 'Category Management',
                    'url' => 'categories',
                    'icon' => 'fas fa-fw fa-th',
                    'active' => ['categories/*'],
                    'can' => 'manage-category',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */
    'plugins' => [
        'Datatables' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'DatatablesPlugins' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-plugins/buttons/js/dataTables.buttons.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-plugins/buttons/js/buttons.bootstrap4.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-plugins/buttons/js/buttons.html5.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-plugins/buttons/js/buttons.print.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-plugins/jszip/jszip.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-plugins/pdfmake/pdfmake.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-plugins/pdfmake/vfs_fonts.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/datatables-plugins/buttons/css/buttons.bootstrap4.min.css',
                ],
            ],
        ],
        'DatatablesResponsive' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false, // CDN, not local
                    'location' => '//cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false, // CDN, not local
                    'location' => '//cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css',
                ],
            ],
        ],

        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' =>
                        '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    */

    'livewire' => false,
];
