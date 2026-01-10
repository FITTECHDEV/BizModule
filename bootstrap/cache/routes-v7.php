<?php

/*
|--------------------------------------------------------------------------
| Load The Cached Routes
|--------------------------------------------------------------------------
|
| Here we will decode and unserialize the RouteCollection instance that
| holds all of the route information for an application. This allows
| us to instantaneously load the entire route map into the router.
|
*/

app('router')->setCompiledRoutes(
    array (
  'compiled' => 
  array (
    0 => false,
    1 => 
    array (
      '/_debugbar/open' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.openhandler',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_debugbar/assets/stylesheets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.assets.css',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_debugbar/assets/javascript' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.assets.js',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/adminlte/darkmode/toggle' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'adminlte.darkmode.toggle',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/broadcasting/auth' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Wj5jFk8FMnNMVplr',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/user' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::jTPecHyMsvhHvtXh',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/auth/login' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::0e3NSIbBJitZEGwf',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/test-pgsql' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::piQ4jGFVsM96Q2kq',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::klSytIydx8ybVmSR',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/login' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'login',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::yEJb9v5IJW6jHDq0',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/logout' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'logout',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::1kp7hsNKcWwBqrx0',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/password/reset' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'password.request',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'password.update',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/password/email' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'password.email',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/password/confirm' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'password.confirm',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::i4gX1ziruCB2FhDX',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/quote-logger' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quote-logger',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/login/azure' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.login',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/login/azurecallback' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.callback',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/logout/azure' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.logout',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/forget-password' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'forget.password.get',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'forget.password.post',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reset-password' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reset.password.post',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/logs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::RAUnUvDbe39sjFBq',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/home' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'home',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/archive' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'archive',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reports/filter' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reportsFilter',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/archive/data' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'archive.data',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/users/get-change-password' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'user.get_change_password',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/users/change-password' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'user.change_password',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'users.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'users.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/users/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'users.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/tickets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tickets.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'tickets.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/tickets/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tickets.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/ticktes/my-tickets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tickets.my_tickets',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/ticktes/my-office-tickets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tickets.my_office_tickets',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/ticktes/assigned' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tickets.assigned',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/ticktes/open-tickets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tickets.open_tickets',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/ticktes/closed-tickets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tickets.closed_tickets',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/ticktes/solved-tickets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tickets.solved_tickets',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/ticktes/re-opened-tickets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tickets.re_opened_tickets',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/tickets/update/ticket/status' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tickets.update_status',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/tickets/assign/ticket' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tickets.assign_ticket_user',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/datatables/all-tickets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'datatables.all_tickets',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/datatables/my-tickets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'datatables.my_tickets',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/datatables/my-office-tickets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'datatables.my_office_tickets',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/datatables/assigned' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'datatables.assigned',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/datatables/closed-tickets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'datatables.closed_tickets',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/datatables/open-tickets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'datatables.open_tickets',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/datatables/solved-tickets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'datatables.solved_tickets',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/datatables/re-opened-tickets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'datatables.re_opened_tickets',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/datatables/settled-tickets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'datatables.settled_tickets',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/datatables/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'datatables.users',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/datatables/tims' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'datatables.tims',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/datatables/efris' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'datatables.efris',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/datatables/tra' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'datatables.tra',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/datatables/tra_fit_tz' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'datatables.tra_tz',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/datatables/rra' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'datatables.rra',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/offices/get-name' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'office.get_name',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/modules/get-data' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'modules.get_data',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/roles' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'roles.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'roles.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/roles/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'roles.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/attachment/upload/file' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'attachment.upload',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/attachment/delete' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'attachment.destroy',
          ),
          1 => NULL,
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/messages' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::ysAhca2jKOyi1po9',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::WnxcDF5mlmKBKi8f',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reports' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reports.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'reports.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reports/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reports.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/filter/report' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::UfeMfaqEnMjdKLoi',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/generate-report' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generate_report',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/categories' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'categories.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/categories/save-category' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'saveCategory',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/categories/update/submodule' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'submodule.update_ajax',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/push' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Wwn5801JRiWujoBK',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'push',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/get-countries' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clientParty.getCountries',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/get-cities' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clientParty.getCities',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/autocomplete-client' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'autocomplete-client',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/get-tarrif-list' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clientParty.getTarrif',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/get-provinces' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clientParty.getProvinces',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/get-offices' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clientParty.getOffices',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/get-licenceType' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clientParty.getLicenceType',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/get-party-entity' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clientParty.getPartyEntity',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/get-inco-terms' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clientParty.getIncoTerms',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/get-payment-term-list' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clientParty.getPaymentTermList',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/get-currency-list' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clientParty.getCurrencyList',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/get-base-currency' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clientParty.getBaseCurrency',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/get-office-debtor-vendor-list' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clientParty.getDebtorVendorList',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/client-party' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'client-party.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'client-party.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/client-party/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'client-party.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/client-party/reject' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'client-party.reject',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/client-party/approve' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'client-party.approve',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'client-party.approvals',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/datatables/client-party' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'datatables.allEntityRequests',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/datatables/client-party-approval' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'datatables.approvalEntityRequests',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/tims' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tims.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/tims/get-stats' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tims.get_stats',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/tims/manual' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::b4WksEd4Zmq310jN',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/tims/update-invoice' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tims.updateInvoice',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/efris' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'efris.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/tra' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tra.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/tra/get-stats' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tra.get_stats',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/traADL' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'traADL.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/traADL/get-stats' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'traADL.get_stats',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/rra' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'rra.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/rra/get-stats' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'rra.get_stats',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/client-party/check-license' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'client-party.check-license',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/companies' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'companies',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/company-details' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'company.details',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/client-party/fetch-address-details' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'client-party.fetch-address-details',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/clients' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clients',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/create-client-party-scm' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'client.create-party-scm',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.home',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/dashboard' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.dashboard.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/dashboard/stats' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.dashboard.stats',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/dashboard/reports' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.dashboard.reports',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/dashboard/activity' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.dashboard.activity',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/users/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/users/active' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.active',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/users/pending' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.pending',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/users/inactive' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.inactive',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/users/stats' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.stats',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/modules' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.modules.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/modules/assign' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.modules.assign',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/modules/scm-functions' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.modules.scm-functions',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/modules/roles' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.modules.roles',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/reports' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.reports.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/reports/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.reports.users',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/reports/activity' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.reports.activity',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/reports/provisioning' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.reports.provisioning',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/auth' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.auth.redirect',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/auth/callback' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.auth.callback',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/auth/test-connection' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.auth.test',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/auth/disconnect' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.auth.disconnect',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/api/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.api.users.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'azure.api.users.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/api/users/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.api.users.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/api/users/stats' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.api.users.stats',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/api/dashboard/stats' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.api.dashboard.stats',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/api/dashboard/activity' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.api.dashboard.activity',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/profile' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.profile.edit',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'azure.profile.update',
          ),
          1 => NULL,
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'azure.profile.destroy',
          ),
          1 => NULL,
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/health' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.health',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/sync/odoo-users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.sync.odoo-users',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/sync/odoo/sync-users-ajax' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.sync.odoo.sync.ajax',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/sync/activate-users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.sync.activate-users',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/sync/full-sync' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.sync.full',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure/sync/status' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.sync.status',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/sync-odoo-users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::DR8Vly3K4j8bsyGt',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/test-odoo-columns' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::JD7IlYnshWAdospn',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/test-pgsql-connection' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::I1AuA0DEzj7he6gk',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/check-view-definition' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::2HKRxcMDBfD3OVSr',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/test-stored-procedures' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::t2Ist3GaOySsB3lD',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/test-azure-users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Ha2nKqqVF338yAMe',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reporting/users/active-this-month' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reporting.users.active-this-month',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reporting/users/active-last-month' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reporting.users.active-last-month',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reporting/users/inactive' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reporting.users.inactive',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reporting/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reporting.users.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'reporting.users.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reporting/users/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reporting.users.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'dashboard',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/debug-user-logins' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'dashboard.debug-user-logins',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/imports' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'imports.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/activity-report' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'activity.report',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/import/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'imports.users',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/import/sign-ins' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'imports.sign_ins',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/clear-cache' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'dashboard.clear-cache',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/imports/status' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'imports.status',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/debug-import-data' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::jEBJsy4NAiD0DoBh',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/tracker/dashboard' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.dashboard',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/tracker/vehicles/list' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.generated::IrzXhGhYvjZKJoVd',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/tracker/jobs/available' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.generated::83N44R7dvsHwg5Of',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/tracker/locations' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.generated::VgUK75URnhHIXzvo',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/jimi-test' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'jimi.test',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/jimi-test/connection' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'jimi.test.connection',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/jimi-test/vehicles' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'jimi.test.vehicles',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/jimi-test/mileage' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'jimi.test.mileage',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/jimi-test/track' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'jimi.test.track',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/azure-management/test' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.test',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/email-accounts' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::z5NMJgd45sLoQP3G',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::DzCGmYFvoeyBzK8G',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'generated::u4qmFegUDkIcorBg',
          ),
          1 => NULL,
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/test-simple' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::NacO3b701vRKESh0',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/sp-test' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sp-test.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
    ),
    2 => 
    array (
      0 => '{^(?|/_debugbar/c(?|lockwork/([^/]++)(*:39)|ache/([^/]++)(?:/([^/]++))?(*:73))|/password/reset/([^/]++)(*:105)|/r(?|e(?|set\\-password/([^/]++)(*:144)|port(?|s/([^/]++)(?|(*:172)|/edit(*:185)|(*:193))|ing/users/(?|([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12})(*:294)|([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12})/edit(*:386)|([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12})(?|(*:476)))))|oles/([^/]++)(?|(*:504)|/edit(*:517)|(*:525))|ra/(?|re\\-trigger/([^/]++)(*:560)|json/([^/]++)(*:581)|invoice/download/([^/]++)(*:614)))|/a(?|rchive/data/details/([^/]++)(*:657)|ttachment/([^/]++)/download(*:692)|zure/(?|users/([^/]++)(?|/(?|re(?|try\\-(?|provisioning(*:754)|modules(*:769)|d365(*:781)|scm(*:792)|biz(*:803)|fitexpress(*:821))|set\\-password(*:843))|disable(*:859)|e(?|nable(*:876)|dit(*:887))|sync\\-external(*:910)|configure\\-office(*:935)|offices/([^/]++)/assignments(*:971)|quick\\-assign\\-module(*:1000)|update\\-base\\-office(*:1029)|modules/([^/]++)/assignments(*:1066))|(*:1076))|reports/export/([^/]++)(*:1109)|api/(?|companies/([^/]++)(*:1143)|users/([^/]++)(?|/(?|disable(*:1180)|e(?|nable(*:1198)|dit(*:1210))|retry\\-modules(*:1234))|(*:1244))))|dmin/sp\\-test/(?|user/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12})(*:1357)|d365/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12})(*:1450)|scm/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12})(*:1542)|biz/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12})(*:1634)|check\\-sp\\-exists/([^/]++)(*:1669)))|/users/(?|profile/([^/]++)(*:1706)|([^/]++)(?|(*:1726)|/edit(*:1740)|(*:1749))|pass/([^/]++)(*:1772)|ajax\\-update(*:1793)|cleanup\\-empty(*:1816))|/t(?|i(?|ck(?|ets/([^/]++)(?|/(?|edit(*:1863)|([^/]++)(*:1880))|(*:1890))|tes/pick/([^/]++)(*:1917))|ms/(?|re\\-trigger/([^/]++)(*:1953)|json/([^/]++)(*:1975)|invoice/download/([^/]++)(*:2009)|hs\\-codes/([^/]++)(*:2036)))|ra(?|/re\\-trigger/([^/]++)(*:2073)|ADL/(?|re\\-trigger/([^/]++)(*:2109)|json/([^/]++)(*:2131)|invoice/download/([^/]++)(*:2165))|cker/trips/(?|get(?:/([^/]++))?(*:2206)|([^/]++)(*:2223)|create(*:2238)|([^/]++)/(?|start(*:2264)|end(*:2276)|jobs/(?|add(*:2296)|([^/]++)/(?|start(*:2322)|complete(*:2339))))))|est/(?|azure\\-sync\\-status/([^/]++)(*:2388)|check\\-biz\\-sync/([^/]++)(*:2422)))|/c(?|ategories/get\\-category(?:/([^/]++)(?:/([^/]++))?)?(*:2489)|lient\\-(?|details/([^/]++)(*:2524)|party/(?|show/([^/]++)(*:2555)|([^/]++)/update(*:2579)|view/([^/]++)(*:2601))))|/get\\-(?|clients/([^/]++)(*:2638)|debtor\\-vendor/([^/]++)(*:2670)|sales\\-account\\-pic/([^/]++)(*:2707)|notify\\-party\\-list/([^/]++)(*:2744)|plo\\-pld/([^/]++)(*:2770))|/delete\\-client\\-party\\-attachment/([^/]++)(*:2823)|/e(?|fris/re\\-trigger/([^/]++)(*:2862)|mail\\-accounts/(?|([^/]++)(*:2897)|password(*:2914)))|/jimi\\-test/location/([^/]++)(*:2954))/?$}sDu',
    ),
    3 => 
    array (
      39 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.clockwork',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      73 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.cache.delete',
            'tags' => NULL,
          ),
          1 => 
          array (
            0 => 'key',
            1 => 'tags',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      105 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'password.reset',
          ),
          1 => 
          array (
            0 => 'token',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      144 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reset.password.get',
          ),
          1 => 
          array (
            0 => 'token',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      172 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reports.show',
          ),
          1 => 
          array (
            0 => 'report',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      185 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reports.edit',
          ),
          1 => 
          array (
            0 => 'report',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      193 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reports.update',
          ),
          1 => 
          array (
            0 => 'report',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'reports.destroy',
          ),
          1 => 
          array (
            0 => 'report',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      294 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reporting.users.show',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      386 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reporting.users.edit',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      476 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reporting.users.update',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'reporting.users.destroy',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      504 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'roles.show',
          ),
          1 => 
          array (
            0 => 'role',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      517 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'roles.edit',
          ),
          1 => 
          array (
            0 => 'role',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      525 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'roles.update',
          ),
          1 => 
          array (
            0 => 'role',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'roles.destroy',
          ),
          1 => 
          array (
            0 => 'role',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      560 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'rra.reTrigger',
          ),
          1 => 
          array (
            0 => 'docNo',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      581 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'rra.json',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      614 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'rra.invoiceDownload',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      657 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'archive.data.details',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      692 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'attachment.download',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      754 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.retry-provisioning',
          ),
          1 => 
          array (
            0 => 'userId',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      769 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.retry-modules',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      781 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.retry-d365',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      792 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.retry-scm',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      803 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.retry-biz',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      821 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.retry-fitexpress',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      843 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.reset-password',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      859 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.disable',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      876 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.enable',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      887 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.edit',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      910 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.sync-external',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      935 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.configure-office',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      971 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.office-assignments',
          ),
          1 => 
          array (
            0 => 'user',
            1 => 'office',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1000 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.quick-assign-module',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1029 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.update-base-office',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1066 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.module-assignments',
          ),
          1 => 
          array (
            0 => 'user',
            1 => 'module',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1076 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.show',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.update',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.patch',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        3 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.destroy',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1109 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.reports.export',
          ),
          1 => 
          array (
            0 => 'type',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1143 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.api.companies.by-location',
          ),
          1 => 
          array (
            0 => 'location',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1180 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.api.users.disable',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1198 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.api.users.enable',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1210 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.api.users.edit',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1234 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.api.users.retry-modules',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1244 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.api.users.show',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'azure.api.users.update',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'azure.api.users.patch',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        3 => 
        array (
          0 => 
          array (
            '_route' => 'azure.api.users.destroy',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1357 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sp-test.user',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1450 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sp-test.d365',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1542 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sp-test.scm',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1634 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sp-test.biz',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1669 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sp-test.check-exists',
          ),
          1 => 
          array (
            0 => 'spName',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1706 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'user.update',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1726 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'users.show',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1740 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'users.edit',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1749 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'users.update',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'users.destroy',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1772 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'user.update.password',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1793 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'users.update_ajax',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1816 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'users.cleanup',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1863 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tickets.edit',
          ),
          1 => 
          array (
            0 => 'ticket',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1880 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tickets.filter_ticket',
          ),
          1 => 
          array (
            0 => 'filter',
            1 => 'filter_id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1890 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tickets.update',
          ),
          1 => 
          array (
            0 => 'ticket',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'tickets.destroy',
          ),
          1 => 
          array (
            0 => 'ticket',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'tickets.show',
          ),
          1 => 
          array (
            0 => 'ticket',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1917 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tickets.pick',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1953 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::ZhDdg5CfWXykzEEL',
          ),
          1 => 
          array (
            0 => 'docNo',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1975 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tims.json',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2009 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tims.invoiceDownload',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2036 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tims.hsCodes',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2073 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tra.reTrigger',
          ),
          1 => 
          array (
            0 => 'docNo',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2109 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'traADL.reTrigger',
          ),
          1 => 
          array (
            0 => 'docNo',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2131 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'traADL.json',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2165 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'traADL.invoiceDownload',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2206 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.',
            'filter' => NULL,
          ),
          1 => 
          array (
            0 => 'filter',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2223 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.generated::ToNYZk8VGx1I6ujE',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2238 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.generated::qxyRANfkMY0hv4Sn',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2264 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.generated::4fsbTP0ylJptWd60',
          ),
          1 => 
          array (
            0 => 'tripId',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2276 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.generated::sEAqv4zM4E3Gubo5',
          ),
          1 => 
          array (
            0 => 'tripId',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2296 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.generated::kIvp7q0v6VvvBdTj',
          ),
          1 => 
          array (
            0 => 'tripId',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2322 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.generated::v48OmteRWEo8YGV6',
          ),
          1 => 
          array (
            0 => 'tripId',
            1 => 'jobId',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2339 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.generated::wiIl6b7LcvWBbQiV',
          ),
          1 => 
          array (
            0 => 'tripId',
            1 => 'jobId',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2388 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::kjC9kwGAYaD5HB3K',
          ),
          1 => 
          array (
            0 => 'email',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2422 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::U7dPusbxVQLD9AfK',
          ),
          1 => 
          array (
            0 => 'email',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2489 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'getCategory',
            'id' => NULL,
            'type' => NULL,
          ),
          1 => 
          array (
            0 => 'id',
            1 => 'type',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2524 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'client.details',
          ),
          1 => 
          array (
            0 => 'companyName',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2555 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'client-party.show',
          ),
          1 => 
          array (
            0 => 'clientParty',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2579 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'client-party.update',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2601 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'client-party.preview',
          ),
          1 => 
          array (
            0 => 'path',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2638 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clientParty.getClients',
          ),
          1 => 
          array (
            0 => 'type',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2670 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clientParty.getDebtorVendor',
          ),
          1 => 
          array (
            0 => 'type',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2707 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clientParty.salesAccountPCI',
          ),
          1 => 
          array (
            0 => 'type',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2744 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clientParty.getNotifyPartyList',
          ),
          1 => 
          array (
            0 => 'type',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2770 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'clientParty.getPOLPOD',
          ),
          1 => 
          array (
            0 => 'type',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2823 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'client-party.deleteFile',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2862 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'efris.reTrigger',
          ),
          1 => 
          array (
            0 => 'docNo',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2897 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::dWfrFF1r1ocSYNFK',
          ),
          1 => 
          array (
            0 => 'email',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2914 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::UIYQ8ZCusiRYTFJp',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2954 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'jimi.test.location',
          ),
          1 => 
          array (
            0 => 'imei',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => NULL,
          1 => NULL,
          2 => NULL,
          3 => NULL,
          4 => false,
          5 => false,
          6 => 0,
        ),
      ),
    ),
    4 => NULL,
  ),
  'attributes' => 
  array (
    'debugbar.openhandler' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '_debugbar/open',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\OpenHandlerController@handle',
        'as' => 'debugbar.openhandler',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\OpenHandlerController@handle',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'debugbar.clockwork' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '_debugbar/clockwork/{id}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\OpenHandlerController@clockwork',
        'as' => 'debugbar.clockwork',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\OpenHandlerController@clockwork',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'debugbar.assets.css' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '_debugbar/assets/stylesheets',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\AssetController@css',
        'as' => 'debugbar.assets.css',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\AssetController@css',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'debugbar.assets.js' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '_debugbar/assets/javascript',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\AssetController@js',
        'as' => 'debugbar.assets.js',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\AssetController@js',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'debugbar.cache.delete' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => '_debugbar/cache/{key}/{tags?}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\CacheController@delete',
        'as' => 'debugbar.cache.delete',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\CacheController@delete',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'adminlte.darkmode.toggle' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'adminlte/darkmode/toggle',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'JeroenNoten\\LaravelAdminLte\\Http\\Controllers\\DarkModeController@toggle',
        'controller' => 'JeroenNoten\\LaravelAdminLte\\Http\\Controllers\\DarkModeController@toggle',
        'as' => 'adminlte.darkmode.toggle',
        'namespace' => NULL,
        'prefix' => 'adminlte',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Wj5jFk8FMnNMVplr' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'broadcasting/auth',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => '\\Illuminate\\Broadcasting\\BroadcastController@authenticate',
        'controller' => '\\Illuminate\\Broadcasting\\BroadcastController@authenticate',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'excluded_middleware' => 
        array (
          0 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
        ),
        'as' => 'generated::Wj5jFk8FMnNMVplr',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::jTPecHyMsvhHvtXh' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/user',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
        ),
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:295:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:77:"function (\\Illuminate\\Http\\Request $request) {
    return $request->user();
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000b8d0000000000000000";}";s:4:"hash";s:44:"eyoHz4OSUQuG8eaKXP5pq6RRsGF1nac0r890MxAg/iM=";}}',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::jTPecHyMsvhHvtXh',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::0e3NSIbBJitZEGwf' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/auth/login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\API\\v1\\Auth\\LoginController@login',
        'controller' => 'App\\Http\\Controllers\\API\\v1\\Auth\\LoginController@login',
        'namespace' => NULL,
        'prefix' => 'api/v1/auth',
        'where' => 
        array (
        ),
        'as' => 'generated::0e3NSIbBJitZEGwf',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::piQ4jGFVsM96Q2kq' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'test-pgsql',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:537:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:318:"function () {
    try {
        \\Illuminate\\Support\\Facades\\DB::connection(\'pgsql\')->getPdo();

        return \\response()->json([\'message\' => \'✅ PostgreSQL is connected!\']);
    } catch (\\Exception $e) {
        return \\response()->json([\'error\' => \'❌ PostgreSQL Connection Failed: \' . $e->getMessage()]);
    }
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000b900000000000000000";}";s:4:"hash";s:44:"tcm85axpIOp/rpD6XqcatjOq36ABp5YjiG/808WT34g=";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::piQ4jGFVsM96Q2kq',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::klSytIydx8ybVmSR' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '/',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\HomeController@index',
        'controller' => 'App\\Http\\Controllers\\HomeController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::klSytIydx8ybVmSR',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'login' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:397:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:178:"function () {
    if (\\config(\'app.env\') == \'Local\') {
        return \\view(\'auth.login\');
    }

    return \\view(\'auth.azure.login\');
    // return redirec t(\'/login/azure\');
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000b930000000000000000";}";s:4:"hash";s:44:"ekwXL1jHuMcT0jqCl+hURl7+ud2xloArz6cQrPPWyBw=";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'login',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::yEJb9v5IJW6jHDq0' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\LoginController@login',
        'controller' => 'App\\Http\\Controllers\\Auth\\LoginController@login',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::yEJb9v5IJW6jHDq0',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'logout' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\LoginController@logout',
        'controller' => 'App\\Http\\Controllers\\Auth\\LoginController@logout',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'logout',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.request' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'password/reset',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\ForgotPasswordController@showLinkRequestForm',
        'controller' => 'App\\Http\\Controllers\\Auth\\ForgotPasswordController@showLinkRequestForm',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.request',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.email' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'password/email',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\ForgotPasswordController@sendResetLinkEmail',
        'controller' => 'App\\Http\\Controllers\\Auth\\ForgotPasswordController@sendResetLinkEmail',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.email',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.reset' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'password/reset/{token}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\ResetPasswordController@showResetForm',
        'controller' => 'App\\Http\\Controllers\\Auth\\ResetPasswordController@showResetForm',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.reset',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'password/reset',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\ResetPasswordController@reset',
        'controller' => 'App\\Http\\Controllers\\Auth\\ResetPasswordController@reset',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.confirm' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'password/confirm',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\ConfirmPasswordController@showConfirmForm',
        'controller' => 'App\\Http\\Controllers\\Auth\\ConfirmPasswordController@showConfirmForm',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.confirm',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::i4gX1ziruCB2FhDX' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'password/confirm',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\ConfirmPasswordController@confirm',
        'controller' => 'App\\Http\\Controllers\\Auth\\ConfirmPasswordController@confirm',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::i4gX1ziruCB2FhDX',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::1kp7hsNKcWwBqrx0' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:265:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:47:"function () {
    return \\redirect(\'/login\');
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000b970000000000000000";}";s:4:"hash";s:44:"kCt8/oVDOjcoEfWz0P1HKEjT0FJmSbU8AuXQEHXMz44=";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::1kp7hsNKcWwBqrx0',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'quote-logger' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quote-logger',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'signed',
        ),
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:624:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:405:"function () {

    $auth_user = \\Illuminate\\Support\\Facades\\Auth::user();

    // todo: permissions
    return \\redirect()->away(\'https://quotesuat.freightintime.com/?username=\' . $auth_user->LoginID . \'&code=\' . $auth_user->password . \'&email=\' . $auth_user->Email, 302, [\'enc_data\' => \'\']);

    // return Http::post(\'https://quotes.freightintime.com/\', [
    //     \'enc_data\' => $enc_user
    // ]);
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000ba00000000000000000";}";s:4:"hash";s:44:"7DciNyx9Op9d0uWTF6Rlqwp6pXqlcuVQsaDIAANcAvU=";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quote-logger',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.login' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'login/azure',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => '\\App\\Http\\Middleware\\AppAzure@azure',
        'controller' => '\\App\\Http\\Middleware\\AppAzure@azure',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'azure.login',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.callback' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'login/azurecallback',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => '\\App\\Http\\Middleware\\AppAzure@azurecallback',
        'controller' => '\\App\\Http\\Middleware\\AppAzure@azurecallback',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'azure.callback',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.logout' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'logout/azure',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => '\\App\\Http\\Middleware\\AppAzure@azurelogout',
        'controller' => '\\App\\Http\\Middleware\\AppAzure@azurelogout',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'azure.logout',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'forget.password.get' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'forget-password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\ForgotPasswordController@showForgetPasswordForm',
        'controller' => 'App\\Http\\Controllers\\ForgotPasswordController@showForgetPasswordForm',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'forget.password.get',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'forget.password.post' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'forget-password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\ForgotPasswordController@submitForgetPasswordForm',
        'controller' => 'App\\Http\\Controllers\\ForgotPasswordController@submitForgetPasswordForm',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'forget.password.post',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reset.password.get' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reset-password/{token}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\ForgotPasswordController@showResetPasswordForm',
        'controller' => 'App\\Http\\Controllers\\ForgotPasswordController@showResetPasswordForm',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'reset.password.get',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reset.password.post' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'reset-password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\ForgotPasswordController@submitResetPasswordForm',
        'controller' => 'App\\Http\\Controllers\\ForgotPasswordController@submitResetPasswordForm',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'reset.password.post',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::RAUnUvDbe39sjFBq' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'logs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => '\\Rap2hpoutre\\LaravelLogViewer\\LogViewerController@index',
        'controller' => '\\Rap2hpoutre\\LaravelLogViewer\\LogViewerController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::RAUnUvDbe39sjFBq',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'home' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'home',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\HomeController@index',
        'controller' => 'App\\Http\\Controllers\\HomeController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'home',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'archive' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'archive',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\HomeController@archive',
        'controller' => 'App\\Http\\Controllers\\HomeController@archive',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'archive',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reportsFilter' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'reports/filter',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\HomeController@reportsFilter',
        'controller' => 'App\\Http\\Controllers\\HomeController@reportsFilter',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'reportsFilter',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'archive.data' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'archive/data',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\HomeController@getMasterData',
        'controller' => 'App\\Http\\Controllers\\HomeController@getMasterData',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'archive.data',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'archive.data.details' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'archive/data/details/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\HomeController@getDetailsData',
        'controller' => 'App\\Http\\Controllers\\HomeController@getDetailsData',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'archive.data.details',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'user.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'users/profile/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\UsersController@update',
        'controller' => 'App\\Http\\Controllers\\UsersController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'user.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'user.get_change_password' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'users/get-change-password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\UsersController@GetChangePassword',
        'controller' => 'App\\Http\\Controllers\\UsersController@GetChangePassword',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'user.get_change_password',
        'excluded_middleware' => 
        array (
          0 => 'change.password',
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'user.change_password' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'users/change-password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\UsersController@ChangePassword',
        'controller' => 'App\\Http\\Controllers\\UsersController@ChangePassword',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'user.change_password',
        'excluded_middleware' => 
        array (
          0 => 'change.password',
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'users.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'users.index',
        'uses' => 'App\\Http\\Controllers\\UsersController@index',
        'controller' => 'App\\Http\\Controllers\\UsersController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'users.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'users/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'users.create',
        'uses' => 'App\\Http\\Controllers\\UsersController@create',
        'controller' => 'App\\Http\\Controllers\\UsersController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'users.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'users.store',
        'uses' => 'App\\Http\\Controllers\\UsersController@store',
        'controller' => 'App\\Http\\Controllers\\UsersController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'users.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'users.show',
        'uses' => 'App\\Http\\Controllers\\UsersController@show',
        'controller' => 'App\\Http\\Controllers\\UsersController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'users.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'users/{user}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'users.edit',
        'uses' => 'App\\Http\\Controllers\\UsersController@edit',
        'controller' => 'App\\Http\\Controllers\\UsersController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'users.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'users.update',
        'uses' => 'App\\Http\\Controllers\\UsersController@update',
        'controller' => 'App\\Http\\Controllers\\UsersController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'users.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'users.destroy',
        'uses' => 'App\\Http\\Controllers\\UsersController@destroy',
        'controller' => 'App\\Http\\Controllers\\UsersController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'user.update.password' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'users/pass/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\UsersController@pass',
        'controller' => 'App\\Http\\Controllers\\UsersController@pass',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'user.update.password',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'users.update_ajax' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'users/ajax-update',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\UsersController@updateAjax',
        'controller' => 'App\\Http\\Controllers\\UsersController@updateAjax',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'users.update_ajax',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tickets.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tickets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'tickets.index',
        'uses' => 'App\\Http\\Controllers\\TicketController@index',
        'controller' => 'App\\Http\\Controllers\\TicketController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tickets.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tickets/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'tickets.create',
        'uses' => 'App\\Http\\Controllers\\TicketController@create',
        'controller' => 'App\\Http\\Controllers\\TicketController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tickets.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'tickets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'tickets.store',
        'uses' => 'App\\Http\\Controllers\\TicketController@store',
        'controller' => 'App\\Http\\Controllers\\TicketController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tickets.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tickets/{ticket}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'tickets.edit',
        'uses' => 'App\\Http\\Controllers\\TicketController@edit',
        'controller' => 'App\\Http\\Controllers\\TicketController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tickets.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'tickets/{ticket}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'tickets.update',
        'uses' => 'App\\Http\\Controllers\\TicketController@update',
        'controller' => 'App\\Http\\Controllers\\TicketController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tickets.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'tickets/{ticket}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'tickets.destroy',
        'uses' => 'App\\Http\\Controllers\\TicketController@destroy',
        'controller' => 'App\\Http\\Controllers\\TicketController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tickets.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tickets/{ticket}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\TicketController@show',
        'controller' => 'App\\Http\\Controllers\\TicketController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'tickets.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tickets.my_tickets' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'ticktes/my-tickets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\TicketController@myTickets',
        'controller' => 'App\\Http\\Controllers\\TicketController@myTickets',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'tickets.my_tickets',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tickets.my_office_tickets' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'ticktes/my-office-tickets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\TicketController@myOfficeTickets',
        'controller' => 'App\\Http\\Controllers\\TicketController@myOfficeTickets',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'tickets.my_office_tickets',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tickets.assigned' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'ticktes/assigned',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\TicketController@assigned',
        'controller' => 'App\\Http\\Controllers\\TicketController@assigned',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'tickets.assigned',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tickets.open_tickets' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'ticktes/open-tickets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\TicketController@openTickets',
        'controller' => 'App\\Http\\Controllers\\TicketController@openTickets',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'tickets.open_tickets',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tickets.closed_tickets' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'ticktes/closed-tickets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\TicketController@closedTickets',
        'controller' => 'App\\Http\\Controllers\\TicketController@closedTickets',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'tickets.closed_tickets',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tickets.solved_tickets' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'ticktes/solved-tickets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\TicketController@solvedTickets',
        'controller' => 'App\\Http\\Controllers\\TicketController@solvedTickets',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'tickets.solved_tickets',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tickets.re_opened_tickets' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'ticktes/re-opened-tickets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\TicketController@reOpenedTickets',
        'controller' => 'App\\Http\\Controllers\\TicketController@reOpenedTickets',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'tickets.re_opened_tickets',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tickets.pick' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'ticktes/pick/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\TicketController@pick',
        'controller' => 'App\\Http\\Controllers\\TicketController@pick',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'tickets.pick',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tickets.update_status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tickets/update/ticket/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\TicketController@updateStatus',
        'controller' => 'App\\Http\\Controllers\\TicketController@updateStatus',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'tickets.update_status',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tickets.assign_ticket_user' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'tickets/assign/ticket',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\TicketController@assignTicket',
        'controller' => 'App\\Http\\Controllers\\TicketController@assignTicket',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'tickets.assign_ticket_user',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tickets.filter_ticket' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tickets/{filter}/{filter_id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\TicketController@filterTicket',
        'controller' => 'App\\Http\\Controllers\\TicketController@filterTicket',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'tickets.filter_ticket',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'datatables.all_tickets' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'datatables/all-tickets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\DataTablesController@allTickets',
        'controller' => 'App\\Http\\Controllers\\DataTablesController@allTickets',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'datatables.all_tickets',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'datatables.my_tickets' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'datatables/my-tickets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\DataTablesController@myTickets',
        'controller' => 'App\\Http\\Controllers\\DataTablesController@myTickets',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'datatables.my_tickets',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'datatables.my_office_tickets' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'datatables/my-office-tickets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\DataTablesController@myOfficeTickets',
        'controller' => 'App\\Http\\Controllers\\DataTablesController@myOfficeTickets',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'datatables.my_office_tickets',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'datatables.assigned' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'datatables/assigned',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\DataTablesController@assigned',
        'controller' => 'App\\Http\\Controllers\\DataTablesController@assigned',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'datatables.assigned',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'datatables.closed_tickets' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'datatables/closed-tickets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\DataTablesController@closedTickets',
        'controller' => 'App\\Http\\Controllers\\DataTablesController@closedTickets',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'datatables.closed_tickets',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'datatables.open_tickets' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'datatables/open-tickets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\DataTablesController@openTickets',
        'controller' => 'App\\Http\\Controllers\\DataTablesController@openTickets',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'datatables.open_tickets',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'datatables.solved_tickets' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'datatables/solved-tickets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\DataTablesController@solvedTickets',
        'controller' => 'App\\Http\\Controllers\\DataTablesController@solvedTickets',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'datatables.solved_tickets',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'datatables.re_opened_tickets' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'datatables/re-opened-tickets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\DataTablesController@reOpenedTickets',
        'controller' => 'App\\Http\\Controllers\\DataTablesController@reOpenedTickets',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'datatables.re_opened_tickets',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'datatables.settled_tickets' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'datatables/settled-tickets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\DataTablesController@settledTickets',
        'controller' => 'App\\Http\\Controllers\\DataTablesController@settledTickets',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'datatables.settled_tickets',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'datatables.users' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'datatables/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\DataTablesController@users',
        'controller' => 'App\\Http\\Controllers\\DataTablesController@users',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'datatables.users',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'datatables.tims' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'datatables/tims',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\DataTablesController@tims',
        'controller' => 'App\\Http\\Controllers\\DataTablesController@tims',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'datatables.tims',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'datatables.efris' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'datatables/efris',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\DataTablesController@efris',
        'controller' => 'App\\Http\\Controllers\\DataTablesController@efris',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'datatables.efris',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'datatables.tra' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'datatables/tra',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\DataTablesController@tra',
        'controller' => 'App\\Http\\Controllers\\DataTablesController@tra',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'datatables.tra',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'datatables.tra_tz' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'datatables/tra_fit_tz',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\DataTablesController@traFITTZ',
        'controller' => 'App\\Http\\Controllers\\DataTablesController@traFITTZ',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'datatables.tra_tz',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'datatables.rra' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'datatables/rra',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\DataTablesController@rra',
        'controller' => 'App\\Http\\Controllers\\DataTablesController@rra',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'datatables.rra',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'office.get_name' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'offices/get-name',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\OfficeController@getOfficeName',
        'controller' => 'App\\Http\\Controllers\\OfficeController@getOfficeName',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'office.get_name',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'modules.get_data' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'modules/get-data',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ModuleController@getModuleData',
        'controller' => 'App\\Http\\Controllers\\ModuleController@getModuleData',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'modules.get_data',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'roles.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'roles',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'roles.index',
        'uses' => 'App\\Http\\Controllers\\RoleController@index',
        'controller' => 'App\\Http\\Controllers\\RoleController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'roles.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'roles/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'roles.create',
        'uses' => 'App\\Http\\Controllers\\RoleController@create',
        'controller' => 'App\\Http\\Controllers\\RoleController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'roles.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'roles',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'roles.store',
        'uses' => 'App\\Http\\Controllers\\RoleController@store',
        'controller' => 'App\\Http\\Controllers\\RoleController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'roles.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'roles/{role}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'roles.show',
        'uses' => 'App\\Http\\Controllers\\RoleController@show',
        'controller' => 'App\\Http\\Controllers\\RoleController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'roles.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'roles/{role}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'roles.edit',
        'uses' => 'App\\Http\\Controllers\\RoleController@edit',
        'controller' => 'App\\Http\\Controllers\\RoleController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'roles.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'roles/{role}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'roles.update',
        'uses' => 'App\\Http\\Controllers\\RoleController@update',
        'controller' => 'App\\Http\\Controllers\\RoleController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'roles.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'roles/{role}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'roles.destroy',
        'uses' => 'App\\Http\\Controllers\\RoleController@destroy',
        'controller' => 'App\\Http\\Controllers\\RoleController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'attachment.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'attachment/{id}/download',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\AttachmentController@download',
        'controller' => 'App\\Http\\Controllers\\AttachmentController@download',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'attachment.download',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'attachment.upload' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'attachment/upload/file',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\AttachmentController@upload',
        'controller' => 'App\\Http\\Controllers\\AttachmentController@upload',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'attachment.upload',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'attachment.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'attachment/delete',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\AttachmentController@destroy',
        'controller' => 'App\\Http\\Controllers\\AttachmentController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'attachment.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::ysAhca2jKOyi1po9' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'messages',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ChatsController@fetchMessages',
        'controller' => 'App\\Http\\Controllers\\ChatsController@fetchMessages',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::ysAhca2jKOyi1po9',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::WnxcDF5mlmKBKi8f' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'messages',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ChatsController@sendMessage',
        'controller' => 'App\\Http\\Controllers\\ChatsController@sendMessage',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::WnxcDF5mlmKBKi8f',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reports.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reports',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'reports.index',
        'uses' => 'App\\Http\\Controllers\\ReportController@index',
        'controller' => 'App\\Http\\Controllers\\ReportController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reports.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reports/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'reports.create',
        'uses' => 'App\\Http\\Controllers\\ReportController@create',
        'controller' => 'App\\Http\\Controllers\\ReportController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reports.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'reports',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'reports.store',
        'uses' => 'App\\Http\\Controllers\\ReportController@store',
        'controller' => 'App\\Http\\Controllers\\ReportController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reports.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reports/{report}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'reports.show',
        'uses' => 'App\\Http\\Controllers\\ReportController@show',
        'controller' => 'App\\Http\\Controllers\\ReportController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reports.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reports/{report}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'reports.edit',
        'uses' => 'App\\Http\\Controllers\\ReportController@edit',
        'controller' => 'App\\Http\\Controllers\\ReportController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reports.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'reports/{report}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'reports.update',
        'uses' => 'App\\Http\\Controllers\\ReportController@update',
        'controller' => 'App\\Http\\Controllers\\ReportController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reports.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'reports/{report}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'as' => 'reports.destroy',
        'uses' => 'App\\Http\\Controllers\\ReportController@destroy',
        'controller' => 'App\\Http\\Controllers\\ReportController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::UfeMfaqEnMjdKLoi' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'filter/report',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ReportController@filterReport',
        'controller' => 'App\\Http\\Controllers\\ReportController@filterReport',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::UfeMfaqEnMjdKLoi',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generate_report' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'generate-report',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ReportController@displayPdfReport',
        'controller' => 'App\\Http\\Controllers\\ReportController@displayPdfReport',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generate_report',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'categories.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'categories',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\CategoryController@index',
        'controller' => 'App\\Http\\Controllers\\CategoryController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'categories.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'getCategory' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'categories/get-category/{id?}/{type?}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\CategoryController@getCategory',
        'controller' => 'App\\Http\\Controllers\\CategoryController@getCategory',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'getCategory',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'saveCategory' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'categories/save-category',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\CategoryController@store',
        'controller' => 'App\\Http\\Controllers\\CategoryController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'saveCategory',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'submodule.update_ajax' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'categories/update/submodule',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\CategoryController@subModuleUpdateAjax',
        'controller' => 'App\\Http\\Controllers\\CategoryController@subModuleUpdateAjax',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'submodule.update_ajax',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Wwn5801JRiWujoBK' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'push',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\PushController@store',
        'controller' => 'App\\Http\\Controllers\\PushController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::Wwn5801JRiWujoBK',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'push' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'push',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\PushController@push',
        'controller' => 'App\\Http\\Controllers\\PushController@push',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'push',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientParty.getCountries' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'get-countries',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@getCountries',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@getCountries',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'clientParty.getCountries',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientParty.getCities' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'get-cities',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@getCities',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@getCities',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'clientParty.getCities',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientParty.getClients' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'get-clients/{type}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@getClients',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@getClients',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'clientParty.getClients',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'autocomplete-client' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'autocomplete-client',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@autocompleteClient',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@autocompleteClient',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'autocomplete-client',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'client.details' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'client-details/{companyName}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@fetchClientDetails',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@fetchClientDetails',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'client.details',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientParty.getTarrif' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'get-tarrif-list',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@getTarrif',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@getTarrif',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'clientParty.getTarrif',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientParty.getDebtorVendor' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'get-debtor-vendor/{type}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@getDebtorVendor',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@getDebtorVendor',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'clientParty.getDebtorVendor',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientParty.getProvinces' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'get-provinces',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@getProvinces',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@getProvinces',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'clientParty.getProvinces',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientParty.getOffices' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'get-offices',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@getOffices',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@getOffices',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'clientParty.getOffices',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientParty.getLicenceType' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'get-licenceType',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@getLicenceType',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@getLicenceType',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'clientParty.getLicenceType',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientParty.getPartyEntity' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'get-party-entity',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@getPartyEntity',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@getPartyEntity',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'clientParty.getPartyEntity',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientParty.getIncoTerms' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'get-inco-terms',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@getIncoTerms',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@getIncoTerms',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'clientParty.getIncoTerms',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientParty.getPaymentTermList' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'get-payment-term-list',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@getPaymentTermList',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@getPaymentTermList',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'clientParty.getPaymentTermList',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientParty.getCurrencyList' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'get-currency-list',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@getCurrencyList',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@getCurrencyList',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'clientParty.getCurrencyList',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientParty.getBaseCurrency' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'get-base-currency',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@getBaseCurrency',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@getBaseCurrency',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'clientParty.getBaseCurrency',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientParty.getDebtorVendorList' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'get-office-debtor-vendor-list',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@getDebtorVendorList',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@getDebtorVendorList',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'clientParty.getDebtorVendorList',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientParty.salesAccountPCI' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'get-sales-account-pic/{type}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@salesAccountPCI',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@salesAccountPCI',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'clientParty.salesAccountPCI',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientParty.getNotifyPartyList' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'get-notify-party-list/{type}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@getNotifyPartyList',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@getNotifyPartyList',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'clientParty.getNotifyPartyList',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clientParty.getPOLPOD' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'get-plo-pld/{type}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@getPOLPOD',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@getPOLPOD',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'clientParty.getPOLPOD',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'client-party.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'client-party',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@index',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'client-party.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'client-party.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'client-party/show/{clientParty}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@show',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'client-party.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'client-party.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'client-party/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@create',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'client-party.create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'client-party.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'client-party',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@store',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'client-party.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'client-party.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'client-party/{id}/update',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@update',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'client-party.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'client-party.preview' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'client-party/view/{path}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@preview',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@preview',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'client-party.preview',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'client-party.reject' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'client-party/reject',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@reject',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@reject',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'client-party.reject',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'client-party.approve' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'client-party/approve',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@approve',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@approve',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'client-party.approve',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'client-party.approvals' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'client-party/approve',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@approvals',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@approvals',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'client-party.approvals',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'client-party.deleteFile' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'delete-client-party-attachment/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@deleteFile',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@deleteFile',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'client-party.deleteFile',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'datatables.allEntityRequests' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'datatables/client-party',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\DataTablesController@allEntityRequests',
        'controller' => 'App\\Http\\Controllers\\DataTablesController@allEntityRequests',
        'namespace' => NULL,
        'prefix' => '/datatables',
        'where' => 
        array (
        ),
        'as' => 'datatables.allEntityRequests',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'datatables.approvalEntityRequests' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'datatables/client-party-approval',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\DataTablesController@approvalEntityRequests',
        'controller' => 'App\\Http\\Controllers\\DataTablesController@approvalEntityRequests',
        'namespace' => NULL,
        'prefix' => '/datatables',
        'where' => 
        array (
        ),
        'as' => 'datatables.approvalEntityRequests',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tims.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tims',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
          3 => 'auth',
          4 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\TIMSController@index',
        'controller' => 'App\\Http\\Controllers\\TIMSController@index',
        'namespace' => NULL,
        'prefix' => '/tims',
        'where' => 
        array (
        ),
        'as' => 'tims.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tims.get_stats' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tims/get-stats',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
          3 => 'auth',
          4 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\TIMSController@getStats',
        'controller' => 'App\\Http\\Controllers\\TIMSController@getStats',
        'namespace' => NULL,
        'prefix' => '/tims',
        'where' => 
        array (
        ),
        'as' => 'tims.get_stats',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::b4WksEd4Zmq310jN' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tims/manual',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
          3 => 'auth',
          4 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\TIMSController@manual',
        'controller' => 'App\\Http\\Controllers\\TIMSController@manual',
        'namespace' => NULL,
        'prefix' => '/tims',
        'where' => 
        array (
        ),
        'as' => 'generated::b4WksEd4Zmq310jN',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::ZhDdg5CfWXykzEEL' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tims/re-trigger/{docNo}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
          3 => 'auth',
          4 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\TIMSController@reTrigger',
        'controller' => 'App\\Http\\Controllers\\TIMSController@reTrigger',
        'namespace' => NULL,
        'prefix' => '/tims',
        'where' => 
        array (
        ),
        'as' => 'generated::ZhDdg5CfWXykzEEL',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tims.json' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tims/json/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
          3 => 'auth',
          4 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\TIMSController@jsonGenerate',
        'controller' => 'App\\Http\\Controllers\\TIMSController@jsonGenerate',
        'namespace' => NULL,
        'prefix' => '/tims',
        'where' => 
        array (
        ),
        'as' => 'tims.json',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tims.invoiceDownload' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tims/invoice/download/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
          3 => 'auth',
          4 => 'web',
          5 => 'signed',
        ),
        'uses' => 'App\\Http\\Controllers\\TIMSController@invoiceDownload',
        'controller' => 'App\\Http\\Controllers\\TIMSController@invoiceDownload',
        'namespace' => NULL,
        'prefix' => '/tims',
        'where' => 
        array (
        ),
        'as' => 'tims.invoiceDownload',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tims.hsCodes' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tims/hs-codes/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
          3 => 'auth',
          4 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\TIMSController@hsCodes',
        'controller' => 'App\\Http\\Controllers\\TIMSController@hsCodes',
        'namespace' => NULL,
        'prefix' => '/tims',
        'where' => 
        array (
        ),
        'as' => 'tims.hsCodes',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tims.updateInvoice' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'tims/update-invoice',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
          3 => 'auth',
          4 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\TIMSController@updateInvoice',
        'controller' => 'App\\Http\\Controllers\\TIMSController@updateInvoice',
        'namespace' => NULL,
        'prefix' => '/tims',
        'where' => 
        array (
        ),
        'as' => 'tims.updateInvoice',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'efris.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'efris',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\EfrisController@index',
        'controller' => 'App\\Http\\Controllers\\EfrisController@index',
        'namespace' => NULL,
        'prefix' => '/efris',
        'where' => 
        array (
        ),
        'as' => 'efris.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'efris.reTrigger' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'efris/re-trigger/{docNo}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\EfrisController@reTrigger',
        'controller' => 'App\\Http\\Controllers\\EfrisController@reTrigger',
        'namespace' => NULL,
        'prefix' => '/efris',
        'where' => 
        array (
        ),
        'as' => 'efris.reTrigger',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tra.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tra',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\TraController@index',
        'controller' => 'App\\Http\\Controllers\\TraController@index',
        'namespace' => NULL,
        'prefix' => '/tra',
        'where' => 
        array (
        ),
        'as' => 'tra.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tra.get_stats' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tra/get-stats',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\TraController@getStats',
        'controller' => 'App\\Http\\Controllers\\TraController@getStats',
        'namespace' => NULL,
        'prefix' => '/tra',
        'where' => 
        array (
        ),
        'as' => 'tra.get_stats',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tra.reTrigger' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tra/re-trigger/{docNo}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\TraController@reTrigger',
        'controller' => 'App\\Http\\Controllers\\TraController@reTrigger',
        'namespace' => NULL,
        'prefix' => '/tra',
        'where' => 
        array (
        ),
        'as' => 'tra.reTrigger',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'traADL.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'traADL',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\TraADLController@index',
        'controller' => 'App\\Http\\Controllers\\TraADLController@index',
        'namespace' => NULL,
        'prefix' => '/traADL',
        'where' => 
        array (
        ),
        'as' => 'traADL.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'traADL.get_stats' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'traADL/get-stats',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\TraADLController@getStats',
        'controller' => 'App\\Http\\Controllers\\TraADLController@getStats',
        'namespace' => NULL,
        'prefix' => '/traADL',
        'where' => 
        array (
        ),
        'as' => 'traADL.get_stats',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'traADL.reTrigger' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'traADL/re-trigger/{docNo}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\TraADLController@reTrigger',
        'controller' => 'App\\Http\\Controllers\\TraADLController@reTrigger',
        'namespace' => NULL,
        'prefix' => '/traADL',
        'where' => 
        array (
        ),
        'as' => 'traADL.reTrigger',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'traADL.json' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'traADL/json/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\TraADLController@jsonGenerate',
        'controller' => 'App\\Http\\Controllers\\TraADLController@jsonGenerate',
        'namespace' => NULL,
        'prefix' => '/traADL',
        'where' => 
        array (
        ),
        'as' => 'traADL.json',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'traADL.invoiceDownload' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'traADL/invoice/download/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
          3 => 'signed',
        ),
        'uses' => 'App\\Http\\Controllers\\TraADLController@invoiceDownload',
        'controller' => 'App\\Http\\Controllers\\TraADLController@invoiceDownload',
        'namespace' => NULL,
        'prefix' => '/traADL',
        'where' => 
        array (
        ),
        'as' => 'traADL.invoiceDownload',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'rra.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'rra',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\RRAController@index',
        'controller' => 'App\\Http\\Controllers\\RRAController@index',
        'namespace' => NULL,
        'prefix' => '/rra',
        'where' => 
        array (
        ),
        'as' => 'rra.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'rra.get_stats' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'rra/get-stats',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\RRAController@getStats',
        'controller' => 'App\\Http\\Controllers\\RRAController@getStats',
        'namespace' => NULL,
        'prefix' => '/rra',
        'where' => 
        array (
        ),
        'as' => 'rra.get_stats',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'rra.reTrigger' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'rra/re-trigger/{docNo}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\RRAController@reTrigger',
        'controller' => 'App\\Http\\Controllers\\RRAController@reTrigger',
        'namespace' => NULL,
        'prefix' => '/rra',
        'where' => 
        array (
        ),
        'as' => 'rra.reTrigger',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'rra.json' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'rra/json/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\RRAController@jsonGenerate',
        'controller' => 'App\\Http\\Controllers\\RRAController@jsonGenerate',
        'namespace' => NULL,
        'prefix' => '/rra',
        'where' => 
        array (
        ),
        'as' => 'rra.json',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'rra.invoiceDownload' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'rra/invoice/download/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
          3 => 'signed',
        ),
        'uses' => 'App\\Http\\Controllers\\RRAController@invoiceDownload',
        'controller' => 'App\\Http\\Controllers\\RRAController@invoiceDownload',
        'namespace' => NULL,
        'prefix' => '/rra',
        'where' => 
        array (
        ),
        'as' => 'rra.invoiceDownload',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'client-party.check-license' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'client-party/check-license',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@checkLicense',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@checkLicense',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'client-party.check-license',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'companies' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/companies',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@getCompanies',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@getCompanies',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'companies',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'company.details' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/company-details',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@getCompanyDetails',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@getCompanyDetails',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'company.details',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'client-party.fetch-address-details' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'client-party/fetch-address-details',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@fetchAddressDetails',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@fetchAddressDetails',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'client-party.fetch-address-details',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'clients' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/clients',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@getClients',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@getClients',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'clients',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'client.create-party-scm' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'create-client-party-scm',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:api',
        ),
        'uses' => 'App\\Http\\Controllers\\ClientPartyController@CreateClientPartySCM',
        'controller' => 'App\\Http\\Controllers\\ClientPartyController@CreateClientPartySCM',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'client.create-party-scm',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.home' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:297:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:79:"function () {
        return \\redirect()->route(\'azure.dashboard.index\');
    }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000c370000000000000000";}";s:4:"hash";s:44:"WBNHbcMk0Fin3ssQ42nudY0L0FwgLOBdNdaJGl5GIns=";}}',
        'as' => 'azure.home',
        'namespace' => NULL,
        'prefix' => '/azure',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.dashboard.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/dashboard',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureDashboardController@index',
        'controller' => 'App\\Http\\Controllers\\AzureDashboardController@index',
        'as' => 'azure.dashboard.index',
        'namespace' => NULL,
        'prefix' => 'azure/dashboard',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.dashboard.stats' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/dashboard/stats',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:dashboard',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureDashboardController@getStats',
        'controller' => 'App\\Http\\Controllers\\AzureDashboardController@getStats',
        'as' => 'azure.dashboard.stats',
        'namespace' => NULL,
        'prefix' => 'azure/dashboard',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.dashboard.reports' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/dashboard/reports',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureDashboardController@reports',
        'controller' => 'App\\Http\\Controllers\\AzureDashboardController@reports',
        'as' => 'azure.dashboard.reports',
        'namespace' => NULL,
        'prefix' => 'azure/dashboard',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.dashboard.activity' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/dashboard/activity',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:dashboard',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureDashboardController@getRecentActivity',
        'controller' => 'App\\Http\\Controllers\\AzureDashboardController@getRecentActivity',
        'as' => 'azure.dashboard.activity',
        'namespace' => NULL,
        'prefix' => 'azure/dashboard',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@index',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@index',
        'as' => 'azure.users.index',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/users/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@create',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@create',
        'as' => 'azure.users.create',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.active' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/users/active',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@active',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@active',
        'as' => 'azure.users.active',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.pending' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/users/pending',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@pending',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@pending',
        'as' => 'azure.users.pending',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.inactive' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/users/inactive',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@inactive',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@inactive',
        'as' => 'azure.users.inactive',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@store',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@store',
        'as' => 'azure.users.store',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.retry-provisioning' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/users/{userId}/retry-provisioning',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@retryProvisioning',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@retryProvisioning',
        'as' => 'azure.users.retry-provisioning',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.stats' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/users/stats',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@stats',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@stats',
        'as' => 'azure.users.stats',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.disable' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/users/{user}/disable',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@disable',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@disable',
        'as' => 'azure.users.disable',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.enable' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/users/{user}/enable',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@enable',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@enable',
        'as' => 'azure.users.enable',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.retry-modules' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/users/{user}/retry-modules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@retryModuleAssignments',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@retryModuleAssignments',
        'as' => 'azure.users.retry-modules',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.reset-password' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/users/{user}/reset-password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@resetPassword',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@resetPassword',
        'as' => 'azure.users.reset-password',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.retry-d365' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/users/{user}/retry-d365',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@retryD365Provisioning',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@retryD365Provisioning',
        'as' => 'azure.users.retry-d365',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.retry-scm' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/users/{user}/retry-scm',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@retrySCMProvisioning',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@retrySCMProvisioning',
        'as' => 'azure.users.retry-scm',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.retry-biz' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/users/{user}/retry-biz',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@retryBIZProvisioning',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@retryBIZProvisioning',
        'as' => 'azure.users.retry-biz',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.retry-fitexpress' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/users/{user}/retry-fitexpress',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@retryFITExpressProvisioning',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@retryFITExpressProvisioning',
        'as' => 'azure.users.retry-fitexpress',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.sync-external' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/users/{user}/sync-external',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@syncToExternalSystemsManual',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@syncToExternalSystemsManual',
        'as' => 'azure.users.sync-external',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.configure-office' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/users/{user}/configure-office',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@configureOfficeAssignments',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@configureOfficeAssignments',
        'as' => 'azure.users.configure-office',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.office-assignments' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/users/{user}/offices/{office}/assignments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:api',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@getOfficeAssignments',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@getOfficeAssignments',
        'as' => 'azure.users.office-assignments',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.quick-assign-module' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/users/{user}/quick-assign-module',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@quickAssignModule',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@quickAssignModule',
        'as' => 'azure.users.quick-assign-module',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.update-base-office' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'azure/users/{user}/update-base-office',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@updateBaseOffice',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@updateBaseOffice',
        'as' => 'azure.users.update-base-office',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.module-assignments' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/users/{user}/modules/{module}/assignments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:api',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@getUserModuleAssignments',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@getUserModuleAssignments',
        'as' => 'azure.users.module-assignments',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@show',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@show',
        'as' => 'azure.users.show',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/users/{user}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@edit',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@edit',
        'as' => 'azure.users.edit',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'azure/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@update',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@update',
        'as' => 'azure.users.update',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.patch' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'azure/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@update',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@update',
        'as' => 'azure.users.patch',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.users.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'azure/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@destroy',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@destroy',
        'as' => 'azure.users.destroy',
        'namespace' => NULL,
        'prefix' => 'azure/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.modules.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/modules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:301:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:83:"function () {
            return \\view(\'azure-management.modules.index\');
        }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000c5a0000000000000000";}";s:4:"hash";s:44:"5t2zzFNXy9TEggrXV2DRvKtqW26jG9SeP7lEgj4kdUU=";}}',
        'as' => 'azure.modules.index',
        'namespace' => NULL,
        'prefix' => 'azure/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.modules.assign' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/modules/assign',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:302:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:84:"function () {
            return \\view(\'azure-management.modules.assign\');
        }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000c5c0000000000000000";}";s:4:"hash";s:44:"1GPB1ziR2dLuXPAZ+DBEjeUmN8kJ+bThqXhdTLpzf74=";}}',
        'as' => 'azure.modules.assign',
        'namespace' => NULL,
        'prefix' => 'azure/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.modules.scm-functions' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/modules/scm-functions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:api',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@getSCMFunctions',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@getSCMFunctions',
        'as' => 'azure.modules.scm-functions',
        'namespace' => NULL,
        'prefix' => 'azure/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.modules.roles' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/modules/roles',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:api',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@getModuleRolesByCode',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@getModuleRolesByCode',
        'as' => 'azure.modules.roles',
        'namespace' => NULL,
        'prefix' => 'azure/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.reports.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/reports',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureReportController@index',
        'controller' => 'App\\Http\\Controllers\\AzureReportController@index',
        'as' => 'azure.reports.index',
        'namespace' => NULL,
        'prefix' => 'azure/reports',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.reports.users' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/reports/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureReportController@users',
        'controller' => 'App\\Http\\Controllers\\AzureReportController@users',
        'as' => 'azure.reports.users',
        'namespace' => NULL,
        'prefix' => 'azure/reports',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.reports.activity' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/reports/activity',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureReportController@activity',
        'controller' => 'App\\Http\\Controllers\\AzureReportController@activity',
        'as' => 'azure.reports.activity',
        'namespace' => NULL,
        'prefix' => 'azure/reports',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.reports.provisioning' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/reports/provisioning',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureReportController@provisioning',
        'controller' => 'App\\Http\\Controllers\\AzureReportController@provisioning',
        'as' => 'azure.reports.provisioning',
        'namespace' => NULL,
        'prefix' => 'azure/reports',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.reports.export' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/reports/export/{type}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:heavy_operations',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureReportController@export',
        'controller' => 'App\\Http\\Controllers\\AzureReportController@export',
        'as' => 'azure.reports.export',
        'namespace' => NULL,
        'prefix' => 'azure/reports',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.auth.redirect' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/auth',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureAuthController@redirectToProvider',
        'controller' => 'App\\Http\\Controllers\\AzureAuthController@redirectToProvider',
        'as' => 'azure.auth.redirect',
        'namespace' => NULL,
        'prefix' => 'azure/auth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.auth.callback' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/auth/callback',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureAuthController@handleProviderCallback',
        'controller' => 'App\\Http\\Controllers\\AzureAuthController@handleProviderCallback',
        'as' => 'azure.auth.callback',
        'namespace' => NULL,
        'prefix' => 'azure/auth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.auth.test' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/auth/test-connection',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureAuthController@testConnection',
        'controller' => 'App\\Http\\Controllers\\AzureAuthController@testConnection',
        'as' => 'azure.auth.test',
        'namespace' => NULL,
        'prefix' => 'azure/auth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.auth.disconnect' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/auth/disconnect',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureAuthController@disconnect',
        'controller' => 'App\\Http\\Controllers\\AzureAuthController@disconnect',
        'as' => 'azure.auth.disconnect',
        'namespace' => NULL,
        'prefix' => 'azure/auth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.api.companies.by-location' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/api/companies/{location}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AzureCompanyController@getByLocation',
        'controller' => 'App\\Http\\Controllers\\Api\\AzureCompanyController@getByLocation',
        'as' => 'azure.api.companies.by-location',
        'namespace' => NULL,
        'prefix' => 'azure/api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.api.users.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/api/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@index',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@index',
        'as' => 'azure.api.users.index',
        'namespace' => NULL,
        'prefix' => 'azure/api/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.api.users.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/api/users/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@create',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@create',
        'as' => 'azure.api.users.create',
        'namespace' => NULL,
        'prefix' => 'azure/api/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.api.users.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/api/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@store',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@store',
        'as' => 'azure.api.users.store',
        'namespace' => NULL,
        'prefix' => 'azure/api/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.api.users.stats' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/api/users/stats',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@stats',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@stats',
        'as' => 'azure.api.users.stats',
        'namespace' => NULL,
        'prefix' => 'azure/api/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.api.users.disable' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/api/users/{user}/disable',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@disable',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@disable',
        'as' => 'azure.api.users.disable',
        'namespace' => NULL,
        'prefix' => 'azure/api/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.api.users.enable' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/api/users/{user}/enable',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@enable',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@enable',
        'as' => 'azure.api.users.enable',
        'namespace' => NULL,
        'prefix' => 'azure/api/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.api.users.retry-modules' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/api/users/{user}/retry-modules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@retryModuleAssignments',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@retryModuleAssignments',
        'as' => 'azure.api.users.retry-modules',
        'namespace' => NULL,
        'prefix' => 'azure/api/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.api.users.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/api/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@show',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@show',
        'as' => 'azure.api.users.show',
        'namespace' => NULL,
        'prefix' => 'azure/api/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.api.users.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/api/users/{user}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@edit',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@edit',
        'as' => 'azure.api.users.edit',
        'namespace' => NULL,
        'prefix' => 'azure/api/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.api.users.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'azure/api/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@update',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@update',
        'as' => 'azure.api.users.update',
        'namespace' => NULL,
        'prefix' => 'azure/api/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.api.users.patch' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'azure/api/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@update',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@update',
        'as' => 'azure.api.users.patch',
        'namespace' => NULL,
        'prefix' => 'azure/api/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.api.users.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'azure/api/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@destroy',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@destroy',
        'as' => 'azure.api.users.destroy',
        'namespace' => NULL,
        'prefix' => 'azure/api/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.api.dashboard.stats' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/api/dashboard/stats',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:dashboard',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureDashboardController@getStats',
        'controller' => 'App\\Http\\Controllers\\AzureDashboardController@getStats',
        'as' => 'azure.api.dashboard.stats',
        'namespace' => NULL,
        'prefix' => 'azure/api/dashboard',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.api.dashboard.activity' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/api/dashboard/activity',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:dashboard',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureDashboardController@getRecentActivity',
        'controller' => 'App\\Http\\Controllers\\AzureDashboardController@getRecentActivity',
        'as' => 'azure.api.dashboard.activity',
        'namespace' => NULL,
        'prefix' => 'azure/api/dashboard',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.profile.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/profile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureProfileController@edit',
        'controller' => 'App\\Http\\Controllers\\AzureProfileController@edit',
        'as' => 'azure.profile.edit',
        'namespace' => NULL,
        'prefix' => 'azure/profile',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.profile.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'azure/profile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureProfileController@update',
        'controller' => 'App\\Http\\Controllers\\AzureProfileController@update',
        'as' => 'azure.profile.update',
        'namespace' => NULL,
        'prefix' => 'azure/profile',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.profile.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'azure/profile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureProfileController@destroy',
        'controller' => 'App\\Http\\Controllers\\AzureProfileController@destroy',
        'as' => 'azure.profile.destroy',
        'namespace' => NULL,
        'prefix' => 'azure/profile',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.health' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/health',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:465:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:246:"function () {
        return \\response()->json([
            \'status\' => \'ok\',
            \'system\' => \'Azure Management\',
            \'timestamp\' => \\now()->toIso8601String(),
            \'environment\' => \\app()->environment(),
        ]);
    }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000c390000000000000000";}";s:4:"hash";s:44:"EK9vUu81zJQlhB0FCnVsux/0iNqnlyKQSGZmqZZ+GwU=";}}',
        'as' => 'azure.health',
        'namespace' => NULL,
        'prefix' => '/azure',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.sync.odoo-users' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/sync/odoo-users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\OdooSyncController@syncUsersManual',
        'controller' => 'App\\Http\\Controllers\\OdooSyncController@syncUsersManual',
        'as' => 'azure.sync.odoo-users',
        'namespace' => NULL,
        'prefix' => '/azure/sync',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.sync.odoo.sync.ajax' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/sync/odoo/sync-users-ajax',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\OdooSyncController@syncUsersAjax',
        'controller' => 'App\\Http\\Controllers\\OdooSyncController@syncUsersAjax',
        'as' => 'azure.sync.odoo.sync.ajax',
        'namespace' => NULL,
        'prefix' => '/azure/sync',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.sync.activate-users' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/sync/activate-users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@activatePendingUsersManual',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@activatePendingUsersManual',
        'as' => 'azure.sync.activate-users',
        'namespace' => NULL,
        'prefix' => '/azure/sync',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.sync.full' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/sync/full-sync',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@fullSyncManual',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@fullSyncManual',
        'as' => 'azure.sync.full',
        'namespace' => NULL,
        'prefix' => '/azure/sync',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.sync.status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/sync/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:949:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:730:"function () {
        try {
            $stats = [
                \'pending_users\' => \\Illuminate\\Support\\Facades\\DB::connection(\'sqlsrv\')
                    ->table(\'Azure_Users\')
                    ->where(\'Status\', \'Pending\')
                    ->count(),
                \'last_sync\' => \\Cache::get(\'last_odoo_sync\'),
                \'last_activation\' => \\Cache::get(\'last_user_activation\'),
            ];

            return \\response()->json([
                \'success\' => true,
                \'data\' => $stats,
            ]);
        } catch (\\Exception $e) {
            return \\response()->json([
                \'success\' => false,
                \'message\' => $e->getMessage(),
            ], 500);
        }
    }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000c7f0000000000000000";}";s:4:"hash";s:44:"QaumuCm/vtcJo8cf5l1UCUExdkPfsjMv+GkTO4Ss2oI=";}}',
        'as' => 'azure.sync.status',
        'namespace' => NULL,
        'prefix' => '/azure/sync',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::DR8Vly3K4j8bsyGt' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'sync-odoo-users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\OdooSyncController@syncUsers',
        'controller' => 'App\\Http\\Controllers\\OdooSyncController@syncUsers',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::DR8Vly3K4j8bsyGt',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::JD7IlYnshWAdospn' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'test-odoo-columns',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\OdooSyncController@testColumns',
        'controller' => 'App\\Http\\Controllers\\OdooSyncController@testColumns',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::JD7IlYnshWAdospn',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::I1AuA0DEzj7he6gk' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'test-pgsql-connection',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\OdooSyncController@testConnection',
        'controller' => 'App\\Http\\Controllers\\OdooSyncController@testConnection',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::I1AuA0DEzj7he6gk',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::2HKRxcMDBfD3OVSr' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'check-view-definition',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\OdooSyncController@checkViewDefinition',
        'controller' => 'App\\Http\\Controllers\\OdooSyncController@checkViewDefinition',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::2HKRxcMDBfD3OVSr',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::t2Ist3GaOySsB3lD' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'test-stored-procedures',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:12057:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:11836:"function () {
    try {
        echo \'<html><head><title>Stored Procedures Test</title>\';
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
            .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
            h2 { color: #34495e; margin-top: 30px; }
            .success { color: #27ae60; font-weight: bold; }
            .error { color: #e74c3c; font-weight: bold; }
            .info { color: #3498db; }
            table { border-collapse: collapse; width: 100%; margin: 15px 0; }
            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
            th { background-color: #3498db; color: white; font-weight: bold; }
            tr:nth-child(even) { background-color: #f2f2f2; }
            .section { background: #ecf0f1; padding: 15px; border-radius: 5px; margin: 15px 0; }
            .code { background: #2c3e50; color: #ecf0f1; padding: 10px; border-radius: 4px; font-family: \'Courier New\', monospace; margin: 10px 0; }
        </style></head><body>";

        echo "<div class=\'container\'>";
        echo \'<h1>🔍 Stored Procedures Test</h1>\';
        echo "<p class=\'info\'>Testing: <strong>sp_UserCreation_Resourcefunction</strong> and <strong>sp_UserCreation_ResourceRole</strong></p>";
        echo \'<hr>\';

        // ================================================================
        // TEST 1: Check if sp_UserCreation_Resourcefunction exists
        // ================================================================
        echo "<div class=\'section\'>";
        echo \'<h2>Test 1: sp_UserCreation_Resourcefunction (SCM Functions)</h2>\';

        try {
            // Check if procedure exists
            $procedureExists = \\Illuminate\\Support\\Facades\\DB::connection(\'sqlsrv\')->select("
                SELECT COUNT(*) as count
                FROM sys.procedures
                WHERE name = \'sp_UserCreation_Resourcefunction\'
            ");

            if ($procedureExists[0]->count > 0) {
                echo "<p class=\'success\'>✅ Stored procedure EXISTS</p>";

                // Try to execute it
                echo "<p>Executing: <code class=\'code\'>EXEC sp_UserCreation_Resourcefunction</code></p>";

                $functions = \\Illuminate\\Support\\Facades\\DB::connection(\'sqlsrv\')->select(\'EXEC sp_UserCreation_Resourcefunction\');

                echo "<p class=\'success\'>✅ Execution successful! Found " . \\count($functions) . \' functions</p>\';

                if (\\count($functions) > 0) {
                    echo \'<table>\';
                    echo \'<thead><tr>\';

                    // Get column names from first row
                    $firstRow = $functions[0];
                    foreach ($firstRow as $key => $value) {
                        echo \'<th>\' . \\htmlspecialchars($key) . \'</th>\';
                    }
                    echo \'</tr></thead><tbody>\';

                    // Display all rows
                    foreach ($functions as $function) {
                        echo \'<tr>\';
                        foreach ($function as $value) {
                            echo \'<td>\' . \\htmlspecialchars($value ?? \'NULL\') . \'</td>\';
                        }
                        echo \'</tr>\';
                    }
                    echo \'</tbody></table>\';
                } else {
                    echo "<p class=\'info\'>⚠️ No functions returned (empty result set)</p>";
                }
            } else {
                echo "<p class=\'error\'>❌ Stored procedure DOES NOT EXIST</p>";
                echo \'<p>Please create the stored procedure first.</p>\';
            }
        } catch (\\Exception $e) {
            echo "<p class=\'error\'>❌ Error: " . \\htmlspecialchars($e->getMessage()) . \'</p>\';
            echo "<pre style=\'background: #fff3cd; padding: 10px; border-radius: 4px;\'>" . \\htmlspecialchars($e->getTraceAsString()) . \'</pre>\';
        }
        echo \'</div>\';

        // ================================================================
        // TEST 2: Check if sp_UserCreation_ResourceRole exists
        // ================================================================
        echo "<div class=\'section\'>";
        echo \'<h2>Test 2: sp_UserCreation_ResourceRole (Roles by Application Code)</h2>\';

        try {
            // Check if procedure exists
            $procedureExists = \\Illuminate\\Support\\Facades\\DB::connection(\'sqlsrv\')->select("
                SELECT COUNT(*) as count
                FROM sys.procedures
                WHERE name = \'sp_UserCreation_ResourceRole\'
            ");

            if ($procedureExists[0]->count > 0) {
                echo "<p class=\'success\'>✅ Stored procedure EXISTS</p>";

                // Test with different application codes
                $testCodes = [\'OPS\', \'OPSUAT\', \'ERP\', \'CRM\'];

                foreach ($testCodes as $appCode) {
                    echo "<h3>Testing with ApplicationCode: <strong>{$appCode}</strong></h3>";
                    echo "<p>Executing: <code class=\'code\'>EXEC sp_UserCreation_ResourceRole @ApplicationCode = \'{$appCode}\'</code></p>";

                    try {
                        $roles = \\Illuminate\\Support\\Facades\\DB::connection(\'sqlsrv\')->select(
                            \'EXEC sp_UserCreation_ResourceRole @ApplicationCode = ?\',
                            [$appCode]
                        );

                        echo "<p class=\'success\'>✅ Execution successful! Found " . \\count($roles) . \' roles</p>\';

                        if (\\count($roles) > 0) {
                            echo \'<table>\';
                            echo \'<thead><tr>\';

                            // Get column names from first row
                            $firstRow = $roles[0];
                            foreach ($firstRow as $key => $value) {
                                echo \'<th>\' . \\htmlspecialchars($key) . \'</th>\';
                            }
                            echo \'</tr></thead><tbody>\';

                            // Display all rows
                            foreach ($roles as $role) {
                                echo \'<tr>\';
                                foreach ($role as $value) {
                                    echo \'<td>\' . \\htmlspecialchars($value ?? \'NULL\') . \'</td>\';
                                }
                                echo \'</tr>\';
                            }
                            echo \'</tbody></table>\';
                        } else {
                            echo "<p class=\'info\'>⚠️ No roles returned for {$appCode}</p>";
                        }
                    } catch (\\Exception $e) {
                        echo "<p class=\'error\'>❌ Error for {$appCode}: " . \\htmlspecialchars($e->getMessage()) . \'</p>\';
                    }
                }
            } else {
                echo "<p class=\'error\'>❌ Stored procedure DOES NOT EXIST</p>";
                echo \'<p>Please create the stored procedure first.</p>\';
            }
        } catch (\\Exception $e) {
            echo "<p class=\'error\'>❌ Error: " . \\htmlspecialchars($e->getMessage()) . \'</p>\';
            echo "<pre style=\'background: #fff3cd; padding: 10px; border-radius: 4px;\'>" . \\htmlspecialchars($e->getTraceAsString()) . \'</pre>\';
        }
        echo \'</div>\';

        // ================================================================
        // TEST 3: Check available modules
        // ================================================================
        echo "<div class=\'section\'>";
        echo \'<h2>Test 3: Available Modules (Azure_SoftwareMaster)</h2>\';

        try {
            $modules = \\Illuminate\\Support\\Facades\\DB::connection(\'sqlsrv\')->select(\'
                SELECT Id, Name, ApplicationId
                FROM Azure_SoftwareMaster
                WHERE IsActive = 1 AND IsDeleted = 0
                ORDER BY Name
            \');

            echo "<p class=\'success\'>✅ Found " . \\count($modules) . \' active modules</p>\';

            if (\\count($modules) > 0) {
                echo \'<table>\';
                echo \'<thead><tr><th>ID</th><th>Name</th><th>Application ID</th><th>Is SCM?</th></tr></thead>\';
                echo \'<tbody>\';

                foreach ($modules as $module) {
                    $isSCM = \\in_array($module->ApplicationId, [\'OPS\', \'OPSUAT\']);
                    $scmBadge = $isSCM ? "<span style=\'background: #27ae60; color: white; padding: 2px 8px; border-radius: 3px; font-size: 0.85em;\'>SCM</span>" : \'\';

                    echo \'<tr>\';
                    echo \'<td>\' . \\htmlspecialchars($module->Id) . \'</td>\';
                    echo \'<td>\' . \\htmlspecialchars($module->Name) . \'</td>\';
                    echo \'<td><strong>\' . \\htmlspecialchars($module->ApplicationId) . "</strong> {$scmBadge}</td>";
                    echo \'<td>\' . ($isSCM ? \'Yes\' : \'No\') . \'</td>\';
                    echo \'</tr>\';
                }

                echo \'</tbody></table>\';
            }
        } catch (\\Exception $e) {
            echo "<p class=\'error\'>❌ Error: " . \\htmlspecialchars($e->getMessage()) . \'</p>\';
        }
        echo \'</div>\';

        // ================================================================
        // TEST 4: Check available locations
        // ================================================================
        echo "<div class=\'section\'>";
        echo \'<h2>Test 4: Available Locations (Cust_Office)</h2>\';

        try {
            $locations = \\Illuminate\\Support\\Facades\\DB::connection(\'sqlsrv\')->select(\'
                SELECT Id, Name, code
                FROM Cust_Office
                WHERE IsActive = 1 AND IsDeleted = 0
                ORDER BY Name
            \');

            echo "<p class=\'success\'>✅ Found " . \\count($locations) . \' active locations</p>\';

            if (\\count($locations) > 0) {
                echo \'<table>\';
                echo \'<thead><tr><th>ID</th><th>Name</th><th>Code</th></tr></thead>\';
                echo \'<tbody>\';

                foreach ($locations as $location) {
                    echo \'<tr>\';
                    echo \'<td>\' . \\htmlspecialchars($location->Id) . \'</td>\';
                    echo \'<td>\' . \\htmlspecialchars($location->Name) . \'</td>\';
                    echo \'<td><strong>\' . \\htmlspecialchars($location->code) . \'</strong></td>\';
                    echo \'</tr>\';
                }

                echo \'</tbody></table>\';
            }
        } catch (\\Exception $e) {
            echo "<p class=\'error\'>❌ Error: " . \\htmlspecialchars($e->getMessage()) . \'</p>\';
        }
        echo \'</div>\';

        // Summary
        echo \'<hr>\';
        echo \'<h2>Summary</h2>\';
        echo \'<p><strong>Next Steps:</strong></p>\';
        echo \'<ul>\';
        echo "<li>If stored procedures don\'t exist, create them in SQL Server</li>";
        echo \'<li>If stored procedures exist but return no data, populate the underlying tables</li>\';
        echo \'<li>If everything works, deploy the updated create user form</li>\';
        echo \'</ul>\';

        echo \'</div></body></html>\';
    } catch (\\Exception $e) {
        echo "<div style=\'padding: 20px; background: #fee; border: 2px solid #e74c3c; border-radius: 4px;\'>";
        echo "<h2 style=\'color: #e74c3c;\'>Critical Error</h2>";
        echo \'<p><strong>Error:</strong> \' . \\htmlspecialchars($e->getMessage()) . \'</p>\';
        echo \'<p><strong>File:</strong> \' . \\htmlspecialchars($e->getFile()) . \'</p>\';
        echo \'<p><strong>Line:</strong> \' . \\htmlspecialchars($e->getLine()) . \'</p>\';
        echo \'</div>\';
    }
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000c830000000000000000";}";s:4:"hash";s:44:"/KPjkLBjUW8UV1bhMZGM1KwG4jg2HSXa1d+ayNA/TjA=";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::t2Ist3GaOySsB3lD',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Ha2nKqqVF338yAMe' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'test-azure-users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:473:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:254:"function () {
    \\dd([
        \'raw_count\' => \\Illuminate\\Support\\Facades\\DB::connection(\'sqlsrv\')->table(\'Azure_Users\')->count(),
        \'model_count\' => \\App\\Models\\AzureUser::count(),
        \'first_user\' => \\App\\Models\\AzureUser::first(),
    ]);
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000c850000000000000000";}";s:4:"hash";s:44:"xRuKzWqR+1PcnnswsTU+pZSbWOQReRWnmqv4DlAnPlA=";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::Ha2nKqqVF338yAMe',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reporting.users.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reporting/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\UserController@show',
        'controller' => 'App\\Http\\Controllers\\UserController@show',
        'namespace' => NULL,
        'prefix' => '/reporting',
        'where' => 
        array (
        ),
        'as' => 'reporting.users.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reporting.users.active-this-month' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reporting/users/active-this-month',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\UserController@activeThisMonth',
        'controller' => 'App\\Http\\Controllers\\UserController@activeThisMonth',
        'namespace' => NULL,
        'prefix' => '/reporting',
        'where' => 
        array (
        ),
        'as' => 'reporting.users.active-this-month',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reporting.users.active-last-month' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reporting/users/active-last-month',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\UserController@activeLastMonth',
        'controller' => 'App\\Http\\Controllers\\UserController@activeLastMonth',
        'namespace' => NULL,
        'prefix' => '/reporting',
        'where' => 
        array (
        ),
        'as' => 'reporting.users.active-last-month',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reporting.users.inactive' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reporting/users/inactive',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\UserController@inactiveUsers',
        'controller' => 'App\\Http\\Controllers\\UserController@inactiveUsers',
        'namespace' => NULL,
        'prefix' => '/reporting',
        'where' => 
        array (
        ),
        'as' => 'reporting.users.inactive',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reporting.users.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reporting/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'reporting.users.index',
        'uses' => 'App\\Http\\Controllers\\UserController@index',
        'controller' => 'App\\Http\\Controllers\\UserController@index',
        'namespace' => NULL,
        'prefix' => 'reporting/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reporting.users.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reporting/users/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'reporting.users.create',
        'uses' => 'App\\Http\\Controllers\\UserController@create',
        'controller' => 'App\\Http\\Controllers\\UserController@create',
        'namespace' => NULL,
        'prefix' => 'reporting/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reporting.users.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'reporting/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'reporting.users.store',
        'uses' => 'App\\Http\\Controllers\\UserController@store',
        'controller' => 'App\\Http\\Controllers\\UserController@store',
        'namespace' => NULL,
        'prefix' => 'reporting/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reporting.users.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reporting/users/{user}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'reporting.users.edit',
        'uses' => 'App\\Http\\Controllers\\UserController@edit',
        'controller' => 'App\\Http\\Controllers\\UserController@edit',
        'namespace' => NULL,
        'prefix' => 'reporting/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reporting.users.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'reporting/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'reporting.users.update',
        'uses' => 'App\\Http\\Controllers\\UserController@update',
        'controller' => 'App\\Http\\Controllers\\UserController@update',
        'namespace' => NULL,
        'prefix' => 'reporting/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reporting.users.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'reporting/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'reporting.users.destroy',
        'uses' => 'App\\Http\\Controllers\\UserController@destroy',
        'controller' => 'App\\Http\\Controllers\\UserController@destroy',
        'namespace' => NULL,
        'prefix' => 'reporting/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\DashboardController@index',
        'controller' => 'App\\Http\\Controllers\\DashboardController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'dashboard',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'dashboard.debug-user-logins' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/debug-user-logins',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\DashboardController@debugUserLogins',
        'controller' => 'App\\Http\\Controllers\\DashboardController@debugUserLogins',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'dashboard.debug-user-logins',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'imports.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imports',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:281:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:63:"function () {
    return \\view(\'logintracker.imports.index\');
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000c8e0000000000000000";}";s:4:"hash";s:44:"L2PnvMYC55Nq8MhJcHFmEULKuGby/17DGdz96iwq0hg=";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'imports.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'activity.report' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'activity-report',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\ActivityReportController@userActivityReport',
        'controller' => 'App\\Http\\Controllers\\ActivityReportController@userActivityReport',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'activity.report',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'imports.users' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'import/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\ImportController@importUsers',
        'controller' => 'App\\Http\\Controllers\\ImportController@importUsers',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'imports.users',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'imports.sign_ins' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'import/sign-ins',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\ImportController@importSignIns',
        'controller' => 'App\\Http\\Controllers\\ImportController@importSignIns',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'imports.sign_ins',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'dashboard.clear-cache' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/clear-cache',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\DashboardController@clearDashboardCache',
        'controller' => 'App\\Http\\Controllers\\DashboardController@clearDashboardCache',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'dashboard.clear-cache',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'imports.status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imports/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\ImportController@getImportStatus',
        'controller' => 'App\\Http\\Controllers\\ImportController@getImportStatus',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'imports.status',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'users.cleanup' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'users/cleanup-empty',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\UserController@cleanupEmptyUsers',
        'controller' => 'App\\Http\\Controllers\\UserController@cleanupEmptyUsers',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'users.cleanup',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::jEBJsy4NAiD0DoBh' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'debug-import-data',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:2481:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:2261:"function () {
    $dates = [\'2025-10-10\', \'2025-10-23\'];

    $data = \\Illuminate\\Support\\Facades\\DB::table(\'Azure_LoginDetails\')
        ->selectRaw(\'CONVERT(DATE, date_utc) as login_date\')
        ->selectRaw(\'application\')
        ->selectRaw(\'username\')
        ->selectRaw(\'user_id\')
        ->selectRaw(\'COUNT(*) as count\')
        ->whereRaw(\'CONVERT(DATE, date_utc) IN (?, ?)\', $dates)
        ->groupBy(\\Illuminate\\Support\\Facades\\DB::raw(\'CONVERT(DATE, date_utc)\'), \'application\', \'username\', \'user_id\')
        ->orderBy(\'login_date\')
        ->orderBy(\'username\')
        ->get();

    $uniqueApps = $data->pluck(\'application\')->unique()->values();
    $systemMap = \\config(\'systemmap\', []);

    $appMappingStatus = [];
    foreach ($uniqueApps as $app) {
        $appLower = \\strtolower(\\trim($app));
        $isMapped = isset($systemMap[$appLower]) || isset($systemMap[$app]);
        $mappedTo = $systemMap[$appLower] ?? $systemMap[$app] ?? \'UNMAPPED\';

        $appMappingStatus[$app] = [
            \'is_mapped\' => $isMapped,
            \'maps_to\' => $mappedTo,
            \'in_display_systems\' => \\in_array($mappedTo, [
                \'BIZ PRO\',
                \'BIZ UAT\',
                \'D365 LIVE\',
                \'D365 UAT\',
                \'FIT Express\',
                \'FIT Express UAT\',
                \'Odoo\',
                \'OPS\',
                \'OPS UAT\',
                \'Windows Sign In\',
            ]),
        ];
    }

    $amayData = $data->filter(function ($record) {
        return \\stripos($record->username, \'amay\') !== false;
    })->values();

    return \\response()->json([
        \'dates_checked\' => $dates,
        \'total_records\' => $data->count(),
        \'unique_applications\' => $uniqueApps,
        \'application_mapping_status\' => $appMappingStatus,
        \'amay_records\' => [
            \'count\' => $amayData->count(),
            \'data\' => $amayData,

        ],
        \'all_records\' => $data->toArray(),
        \'display_systems\' => [
            \'BIZ PRO\',
            \'BIZ UAT\',
            \'D365 LIVE\',
            \'D365 UAT\',
            \'FIT Express\',
            \'FIT Express UAT\',
            \'Odoo\',
            \'OPS\',
            \'OPS UAT\',
            \'Windows Sign In\',
        ],
    ]);
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000c9b0000000000000000";}";s:4:"hash";s:44:"YNTlJ+vjzEYMG0tu4BsfXJ0AeIe9NUUHOTf3G8+edNk=";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::jEBJsy4NAiD0DoBh',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tracker.dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tracker/dashboard',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TrackerController@dashboard',
        'controller' => 'App\\Http\\Controllers\\TrackerController@dashboard',
        'as' => 'tracker.dashboard',
        'namespace' => NULL,
        'prefix' => '/tracker',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tracker.' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tracker/trips/get/{filter?}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TrackerController@getTrips',
        'controller' => 'App\\Http\\Controllers\\TrackerController@getTrips',
        'as' => 'tracker.',
        'namespace' => NULL,
        'prefix' => '/tracker',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tracker.generated::ToNYZk8VGx1I6ujE' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tracker/trips/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TrackerController@getTripDetails',
        'controller' => 'App\\Http\\Controllers\\TrackerController@getTripDetails',
        'as' => 'tracker.generated::ToNYZk8VGx1I6ujE',
        'namespace' => NULL,
        'prefix' => '/tracker',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tracker.generated::IrzXhGhYvjZKJoVd' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tracker/vehicles/list',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TrackerController@getVehicles',
        'controller' => 'App\\Http\\Controllers\\TrackerController@getVehicles',
        'as' => 'tracker.generated::IrzXhGhYvjZKJoVd',
        'namespace' => NULL,
        'prefix' => '/tracker',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tracker.generated::83N44R7dvsHwg5Of' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tracker/jobs/available',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TrackerController@getAvailableJobs',
        'controller' => 'App\\Http\\Controllers\\TrackerController@getAvailableJobs',
        'as' => 'tracker.generated::83N44R7dvsHwg5Of',
        'namespace' => NULL,
        'prefix' => '/tracker',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tracker.generated::VgUK75URnhHIXzvo' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tracker/locations',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TrackerController@getLocations',
        'controller' => 'App\\Http\\Controllers\\TrackerController@getLocations',
        'as' => 'tracker.generated::VgUK75URnhHIXzvo',
        'namespace' => NULL,
        'prefix' => '/tracker',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tracker.generated::qxyRANfkMY0hv4Sn' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'tracker/trips/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TrackerController@createTrip',
        'controller' => 'App\\Http\\Controllers\\TrackerController@createTrip',
        'as' => 'tracker.generated::qxyRANfkMY0hv4Sn',
        'namespace' => NULL,
        'prefix' => '/tracker',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tracker.generated::4fsbTP0ylJptWd60' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'tracker/trips/{tripId}/start',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TrackerController@startTrip',
        'controller' => 'App\\Http\\Controllers\\TrackerController@startTrip',
        'as' => 'tracker.generated::4fsbTP0ylJptWd60',
        'namespace' => NULL,
        'prefix' => '/tracker',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tracker.generated::sEAqv4zM4E3Gubo5' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'tracker/trips/{tripId}/end',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TrackerController@endTrip',
        'controller' => 'App\\Http\\Controllers\\TrackerController@endTrip',
        'as' => 'tracker.generated::sEAqv4zM4E3Gubo5',
        'namespace' => NULL,
        'prefix' => '/tracker',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tracker.generated::kIvp7q0v6VvvBdTj' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'tracker/trips/{tripId}/jobs/add',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TrackerController@addJobToTrip',
        'controller' => 'App\\Http\\Controllers\\TrackerController@addJobToTrip',
        'as' => 'tracker.generated::kIvp7q0v6VvvBdTj',
        'namespace' => NULL,
        'prefix' => '/tracker',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tracker.generated::v48OmteRWEo8YGV6' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'tracker/trips/{tripId}/jobs/{jobId}/start',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TrackerController@startJob',
        'controller' => 'App\\Http\\Controllers\\TrackerController@startJob',
        'as' => 'tracker.generated::v48OmteRWEo8YGV6',
        'namespace' => NULL,
        'prefix' => '/tracker',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'tracker.generated::wiIl6b7LcvWBbQiV' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'tracker/trips/{tripId}/jobs/{jobId}/complete',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TrackerController@completeJob',
        'controller' => 'App\\Http\\Controllers\\TrackerController@completeJob',
        'as' => 'tracker.generated::wiIl6b7LcvWBbQiV',
        'namespace' => NULL,
        'prefix' => '/tracker',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'jimi.test' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'jimi-test',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\JimiTestController@testPage',
        'controller' => 'App\\Http\\Controllers\\JimiTestController@testPage',
        'namespace' => NULL,
        'prefix' => '/jimi-test',
        'where' => 
        array (
        ),
        'as' => 'jimi.test',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'jimi.test.connection' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'jimi-test/connection',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\JimiTestController@testConnection',
        'controller' => 'App\\Http\\Controllers\\JimiTestController@testConnection',
        'namespace' => NULL,
        'prefix' => '/jimi-test',
        'where' => 
        array (
        ),
        'as' => 'jimi.test.connection',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'jimi.test.vehicles' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'jimi-test/vehicles',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\JimiTestController@getVehicles',
        'controller' => 'App\\Http\\Controllers\\JimiTestController@getVehicles',
        'namespace' => NULL,
        'prefix' => '/jimi-test',
        'where' => 
        array (
        ),
        'as' => 'jimi.test.vehicles',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'jimi.test.location' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'jimi-test/location/{imei}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\JimiTestController@getCurrentLocation',
        'controller' => 'App\\Http\\Controllers\\JimiTestController@getCurrentLocation',
        'namespace' => NULL,
        'prefix' => '/jimi-test',
        'where' => 
        array (
        ),
        'as' => 'jimi.test.location',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'jimi.test.mileage' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'jimi-test/mileage',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\JimiTestController@getMileage',
        'controller' => 'App\\Http\\Controllers\\JimiTestController@getMileage',
        'namespace' => NULL,
        'prefix' => '/jimi-test',
        'where' => 
        array (
        ),
        'as' => 'jimi.test.mileage',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'jimi.test.track' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'jimi-test/track',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\JimiTestController@getTrackData',
        'controller' => 'App\\Http\\Controllers\\JimiTestController@getTrackData',
        'namespace' => NULL,
        'prefix' => '/jimi-test',
        'where' => 
        array (
        ),
        'as' => 'jimi.test.track',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'azure.test' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure-management/test',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:4797:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:4577:"function () {
    $results = [
        \'config\' => [],
        \'token\' => [],
        \'create_user\' => [],
        \'find_user\' => [],
    ];

    // ============================================
    // TEST 1: Configuration Check
    // ============================================
    try {
        $results[\'config\'] = [
            \'status\' => \'success\',
            \'tenant_id\' => \\config(\'azure.tenant_id\') ? \'Set ✅\' : \'Missing ❌\',
            \'client_id\' => \\config(\'azure.client_id\') ? \'Set ✅\' : \'Missing ❌\',
            \'client_secret\' => \\config(\'azure.client_secret\') ? \'Set ✅\' : \'Missing ❌\',
            \'domain\' => \\config(\'azure.default_domain\'),
            \'graph_url\' => \\config(\'azure.graph_api_base_url\'),
        ];
    } catch (\\Exception $e) {
        $results[\'config\'] = [
            \'status\' => \'failed\',
            \'error\' => $e->getMessage(),
        ];
    }

    // ============================================
    // TEST 2: Get Access Token
    // ============================================
    try {
        $service = \\app(\\App\\Services\\AzureGraphService::class);
        $token = $service->getAccessToken();

        $results[\'token\'] = [
            \'status\' => \'success\',
            \'token_preview\' => \\substr($token, 0, 50) . \'...\',
            \'token_length\' => \\strlen($token),
            \'message\' => \'✅ Successfully obtained Azure AD token!\',
        ];
    } catch (\\Exception $e) {
        $results[\'token\'] = [
            \'status\' => \'failed\',
            \'error\' => $e->getMessage(),
            \'message\' => \'❌ Failed to get token\',
        ];

        // Stop here if token fails
        return \\view(\'azure-management.test\', \\compact(\'results\'));
    }

    // ============================================
    // TEST 3: Check if Test User Exists
    // ============================================
    if ($results[\'token\'][\'status\'] === \'success\') {
        try {
            $testUpn = \'azuretest@freight-in-time.com\';
            $existingUser = $service->findUserByUPN($testUpn);

            if ($existingUser) {
                $results[\'find_user\'] = [
                    \'status\' => \'success\',
                    \'found\' => true,
                    \'azure_id\' => $existingUser[\'id\'],
                    \'upn\' => $existingUser[\'userPrincipalName\'],
                    \'display_name\' => $existingUser[\'displayName\'],
                    \'message\' => \'✅ Test user already exists (will not create duplicate)\',
                ];
            } else {
                $results[\'find_user\'] = [
                    \'status\' => \'success\',
                    \'found\' => false,
                    \'message\' => \'Test user not found (ready to create)\',
                ];
            }
        } catch (\\Exception $e) {
            $results[\'find_user\'] = [
                \'status\' => \'failed\',
                \'error\' => $e->getMessage(),
            ];
        }
    }

    // ============================================
    // TEST 4: Create Test User (Only if not exists)
    // ============================================
    if (
        $results[\'token\'][\'status\'] === \'success\' &&
        isset($results[\'find_user\'][\'found\']) &&
        ! $results[\'find_user\'][\'found\']
    ) {
        try {
            $randomId = \\rand(1000, 9999);

            $userData = [
                \'name\' => "Azure Test User {$randomId}",
                \'email\' => "azuretest{$randomId}@freight-in-time.com",
                \'employee_id\' => "TEST{$randomId}",
                \'phone\' => \'+254712345678\',
                \'job_title\' => \'Test Engineer\',
                \'department\' => \'IT Testing\',
            ];

            $result = $service->createUser($userData);

            $results[\'create_user\'] = [
                \'status\' => \'success\',
                \'azure_id\' => $result[\'azure_id\'],
                \'azure_upn\' => $result[\'azure_upn\'],
                \'display_name\' => $result[\'azure_display_name\'],
                \'password_generated\' => ! empty($result[\'temporary_password\']),
                \'message\' => \'✅ Test user created successfully in Azure AD!\',
                \'note\' => \'This is a real user - you may want to delete it from Azure AD portal\',
            ];
        } catch (\\Exception $e) {
            $results[\'create_user\'] = [
                \'status\' => \'failed\',
                \'error\' => $e->getMessage(),
                \'message\' => \'❌ Failed to create test user\',
            ];
        }
    }

    return \\view(\'azure-management.test\', \\compact(\'results\'));
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000c9d0000000000000000";}";s:4:"hash";s:44:"zceAuEOJEofbdoG11ZewbAeYbItVaM24hbcskZxY0Jk=";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'azure.test',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::z5NMJgd45sLoQP3G' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'email-accounts',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\EmailAccountController@store',
        'controller' => 'App\\Http\\Controllers\\EmailAccountController@store',
        'namespace' => NULL,
        'prefix' => '/email-accounts',
        'where' => 
        array (
        ),
        'as' => 'generated::z5NMJgd45sLoQP3G',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::DzCGmYFvoeyBzK8G' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'email-accounts',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\EmailAccountController@index',
        'controller' => 'App\\Http\\Controllers\\EmailAccountController@index',
        'namespace' => NULL,
        'prefix' => '/email-accounts',
        'where' => 
        array (
        ),
        'as' => 'generated::DzCGmYFvoeyBzK8G',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::dWfrFF1r1ocSYNFK' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'email-accounts/{email}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\EmailAccountController@show',
        'controller' => 'App\\Http\\Controllers\\EmailAccountController@show',
        'namespace' => NULL,
        'prefix' => '/email-accounts',
        'where' => 
        array (
        ),
        'as' => 'generated::dWfrFF1r1ocSYNFK',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::UIYQ8ZCusiRYTFJp' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'email-accounts/password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\EmailAccountController@updatePassword',
        'controller' => 'App\\Http\\Controllers\\EmailAccountController@updatePassword',
        'namespace' => NULL,
        'prefix' => '/email-accounts',
        'where' => 
        array (
        ),
        'as' => 'generated::UIYQ8ZCusiRYTFJp',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::u4qmFegUDkIcorBg' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'email-accounts',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\EmailAccountController@destroy',
        'controller' => 'App\\Http\\Controllers\\EmailAccountController@destroy',
        'namespace' => NULL,
        'prefix' => '/email-accounts',
        'where' => 
        array (
        ),
        'as' => 'generated::u4qmFegUDkIcorBg',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::NacO3b701vRKESh0' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'test-simple',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:269:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:51:"function () {
    return \'Hello! Routing works!\';
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000cb10000000000000000";}";s:4:"hash";s:44:"2nB2S5VspQnVdUrAhKskd7qcnajNUagYLGRdgkEn3RY=";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::NacO3b701vRKESh0',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sp-test.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/sp-test',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\StoredProcedureTestController@index',
        'controller' => 'App\\Http\\Controllers\\StoredProcedureTestController@index',
        'as' => 'sp-test.index',
        'namespace' => NULL,
        'prefix' => '/admin/sp-test',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sp-test.user' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/sp-test/user/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\StoredProcedureTestController@testUser',
        'controller' => 'App\\Http\\Controllers\\StoredProcedureTestController@testUser',
        'as' => 'sp-test.user',
        'namespace' => NULL,
        'prefix' => '/admin/sp-test',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sp-test.d365' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/sp-test/d365/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\StoredProcedureTestController@testD365',
        'controller' => 'App\\Http\\Controllers\\StoredProcedureTestController@testD365',
        'as' => 'sp-test.d365',
        'namespace' => NULL,
        'prefix' => '/admin/sp-test',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sp-test.scm' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/sp-test/scm/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\StoredProcedureTestController@testSCM',
        'controller' => 'App\\Http\\Controllers\\StoredProcedureTestController@testSCM',
        'as' => 'sp-test.scm',
        'namespace' => NULL,
        'prefix' => '/admin/sp-test',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sp-test.biz' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/sp-test/biz/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\StoredProcedureTestController@testBIZ',
        'controller' => 'App\\Http\\Controllers\\StoredProcedureTestController@testBIZ',
        'as' => 'sp-test.biz',
        'namespace' => NULL,
        'prefix' => '/admin/sp-test',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sp-test.check-exists' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/sp-test/check-sp-exists/{spName}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\StoredProcedureTestController@checkSPExists',
        'controller' => 'App\\Http\\Controllers\\StoredProcedureTestController@checkSPExists',
        'as' => 'sp-test.check-exists',
        'namespace' => NULL,
        'prefix' => '/admin/sp-test',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::kjC9kwGAYaD5HB3K' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'test/azure-sync-status/{email}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:5659:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:5439:"function ($email) {
    try {
        // 1. Get user from local database
        $user = \\App\\Models\\AzureUser::where(\'userPrincipalName\', $email)
            ->orWhere(\'mail\', $email)
            ->firstOrFail();

        \\Log::info(\'=== AZURE SYNC DIAGNOSTIC ===\', [\'email\' => $email]);

        // ✅ FIX: Use email as user_id (not UUID)
        $userId = $user->mail ?? $user->userPrincipalName;

        // 2. Get all module assignments from database
        $roleAssignments = \\Illuminate\\Support\\Facades\\DB::table(\'Azure_Application_UserMapping\')
            ->join(\'Azure_SoftwareMaster\', \'Azure_Application_UserMapping.application_id\', \'=\', \'Azure_SoftwareMaster.ApplicationId\')
            ->where(\'Azure_Application_UserMapping.user_id\', $userId) // ✅ FIXED
            ->where(\'Azure_Application_UserMapping.IsActive\', 1)
            ->where(\'Azure_Application_UserMapping.IsDelete\', 0)
            ->select(
                \'Azure_SoftwareMaster.Name as module_name\',
                \'Azure_Application_UserMapping.OfficeCode\',
                \'Azure_Application_UserMapping.RoleCode\',
                \'Azure_Application_UserMapping.RoleID\'
            )
            ->get();

        $functionAssignments = \\Illuminate\\Support\\Facades\\DB::table(\'Azure_Application_UserFunction\')
            ->join(\'Azure_SoftwareMaster\', \'Azure_Application_UserFunction.application_id\', \'=\', \'Azure_SoftwareMaster.ApplicationId\')
            ->where(\'Azure_Application_UserFunction.user_id\', $userId) // ✅ FIXED
            ->where(\'Azure_Application_UserFunction.IsActive\', 1)
            ->where(\'Azure_Application_UserFunction.IsDelete\', 0)
            ->select(
                \'Azure_SoftwareMaster.Name as module_name\',
                \'Azure_Application_UserFunction.OfficeCode\',
                \'Azure_Application_UserFunction.FunctionCode\',
                \'Azure_Application_UserFunction.FunctionID\'
            )
            ->get();

        \\Log::info(\'Database Assignments\', [
            \'user_id_used\' => $userId,
            \'roles_count\' => $roleAssignments->count(),
            \'functions_count\' => $functionAssignments->count()
        ]);

        // 3. Check Azure provisioning status
        $azureStatus = [
            \'azure_id\' => $user->AzureId,
            \'is_provisioned\' => !empty($user->AzureId),
            \'last_updated\' => $user->updated_at ?? \'Never\'
        ];

        // 4. Group assignments by module for better readability
        $rolesByModule = $roleAssignments->groupBy(\'module_name\')->map(function ($roles) {
            return [
                \'count\' => $roles->count(),
                \'assignments\' => $roles->map(function ($role) {
                    return [
                        \'office\' => \\trim($role->OfficeCode),
                        \'role\' => $role->RoleCode
                    ];
                })->toArray()
            ];
        })->toArray();

        $functionsByModule = $functionAssignments->groupBy(\'module_name\')->map(function ($functions) {
            return [
                \'count\' => $functions->count(),
                \'assignments\' => $functions->map(function ($func) {
                    return [
                        \'office\' => \\trim($func->OfficeCode),
                        \'function\' => $func->FunctionCode
                    ];
                })->toArray()
            ];
        })->toArray();

        // 5. Summary
        $totalDbAssignments = $roleAssignments->count() + $functionAssignments->count();

        $summary = [
            \'total_roles\' => $roleAssignments->count(),
            \'total_functions\' => $functionAssignments->count(),
            \'total_assignments\' => $totalDbAssignments,
            \'unique_modules\' => \\count(\\array_unique(\\array_merge(
                $roleAssignments->pluck(\'module_name\')->toArray(),
                $functionAssignments->pluck(\'module_name\')->toArray()
            ))),
            \'modules_with_roles\' => \\array_keys($rolesByModule),
            \'modules_with_functions\' => \\array_keys($functionsByModule)
        ];

        // Build recommendation
        $recommendation = \'\';
        if (empty($user->AzureId)) {
            $recommendation = \'⚠️ User NOT provisioned to Azure - Run provision script\';
        } else {
            $recommendation = "✅ User is provisioned to Azure (Azure ID: {$user->AzureId}). Database has {$totalDbAssignments} assignments ready to sync.";
        }

        return \\response()->json([
            \'success\' => true,
            \'user\' => [
                \'id\' => $user->Id,
                \'name\' => $user->displayName,
                \'email\' => $userId,
                \'azure_id\' => $user->AzureId,
                \'status\' => $user->Status ?? \'unknown\',
                \'base_office\' => $user->BaseOfficeCode ?? \'Not set\'
            ],
            \'summary\' => $summary,
            \'roles_by_module\' => $rolesByModule,
            \'functions_by_module\' => $functionsByModule,
            \'azure_status\' => $azureStatus,
            \'recommendation\' => $recommendation
        ], 200);
    } catch (\\Exception $e) {
        \\Log::error(\'Azure sync diagnostic failed\', [
            \'email\' => $email,
            \'error\' => $e->getMessage(),
            \'line\' => $e->getLine()
        ]);

        return \\response()->json([
            \'success\' => false,
            \'error\' => $e->getMessage()
        ], 500);
    }
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000cb80000000000000000";}";s:4:"hash";s:44:"DTB2VpnCI5X2I5U0lbkquNdSl86FubAMN2sTt9L3WqM=";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::kjC9kwGAYaD5HB3K',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::U7dPusbxVQLD9AfK' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'test/check-biz-sync/{email}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:2920:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:2700:"function ($email) {
    try {
        $user = \\App\\Models\\AzureUser::where(\'userPrincipalName\', $email)
            ->orWhere(\'mail\', $email)
            ->firstOrFail();

        $userId = $user->mail ?? $user->userPrincipalName;

        \\Log::info(\'=== CHECKING BIZ PRO SYNC ===\', [\'user\' => $userId]);

        // 1. Get BIZ PRO assignments from database
        $bizRoles = \\Illuminate\\Support\\Facades\\DB::table(\'Azure_Application_UserMapping as aaum\')
            ->join(\'Azure_SoftwareMaster as asm\', \'aaum.application_id\', \'=\', \'asm.ApplicationId\')
            ->where(\'aaum.user_id\', $userId)
            ->where(\'asm.Name\', \'BIZ PRO\')
            ->where(\'aaum.IsActive\', 1)
            ->where(\'aaum.IsDelete\', 0)
            ->select(
                \'aaum.*\',
                \'asm.Name as ApplicationName\',
                \'asm.ApplicationId\'
            )
            ->get();

        // 2. Check if BIZ user ID exists
        $bizUserId = $user->BizUserID;
        $bizLoginId = $user->BizLoginId;
        $bizCreatedOn = $user->BizCreatedOn;

        // 3. Check for sync status or provisioning flags
        $provisioningStatus = \\Illuminate\\Support\\Facades\\DB::table(\'Azure_Application_UserProvisioningStatus\')
            ->where(\'user_id\', $user->Id)
            ->orWhere(\'user_id\', $userId)
            ->get();

        return \\response()->json([
            \'success\' => true,
            \'user\' => [
                \'email\' => $userId,
                \'azure_id\' => $user->AzureId,
                \'biz_user_id\' => $bizUserId,
                \'biz_login_id\' => $bizLoginId,
                \'biz_created_on\' => $bizCreatedOn
            ],
            \'biz_assignments_in_database\' => [
                \'count\' => $bizRoles->count(),
                \'roles\' => $bizRoles->map(function ($role) {
                    return [
                        \'office\' => \\trim($role->OfficeCode),
                        \'role\' => $role->RoleCode,
                        \'role_id\' => $role->RoleID,
                        \'created_at\' => $role->created_at ?? \'Unknown\',
                        \'updated_at\' => $role->updated_at ?? \'Unknown\'
                    ];
                })->toArray()
            ],
            \'provisioning_status\' => $provisioningStatus->toArray(),
            \'diagnosis\' => [
                \'has_biz_user_id\' => !empty($bizUserId),
                \'roles_in_database\' => $bizRoles->count(),
                \'needs_sync\' => empty($bizUserId) || $bizRoles->isEmpty()
            ]
        ]);
    } catch (\\Exception $e) {
        return \\response()->json([
            \'success\' => false,
            \'error\' => $e->getMessage()
        ], 500);
    }
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000cbf0000000000000000";}";s:4:"hash";s:44:"KTu7CG/dZayqQq+hAfotDwPo1xSkQWVf6VL9rx1cMXs=";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::U7dPusbxVQLD9AfK',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
  ),
)
);
