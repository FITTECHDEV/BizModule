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
            '_route' => 'generated::rd639xVSKWSq0xB9',
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
            '_route' => 'generated::4zOKsAyeqIhdSC7t',
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
            '_route' => 'generated::3XebnJOAaKJI2dLq',
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
            '_route' => 'generated::kw2SpQyCZ84BCHdT',
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
            '_route' => 'generated::4cukr4yCHCuUGzCw',
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
            '_route' => 'generated::UpzG311Lo9i8xYwi',
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
            '_route' => 'generated::m5SSJOQKMc2nOkzM',
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
            '_route' => 'generated::iN3cvLkOJRWLjwU6',
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
            '_route' => 'generated::tJtEmlYrWfi5gJ1g',
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
      '/users/stats' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'bizpro.users.stats',
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
      '/users/ajax-update' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'users.update_ajax',
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
      '/switch-office' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'home.switch-office',
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
            '_route' => 'generated::903XH3e2HJlfW8B8',
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
            '_route' => 'generated::WmUpkiOFFgSGFfdQ',
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
            '_route' => 'generated::sCfJVsfOBnucD3ni',
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
            '_route' => 'generated::yN70Von20ZbOqToH',
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
            '_route' => 'generated::wX20n1nocMBd78fd',
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
      '/efris/update-invoice' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'efris.updateInvoice',
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
      '/tra/update-invoice' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tra.updateInvoice',
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
      '/traADL/update-invoice' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'traADL.updateInvoice',
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
      '/rra/update-invoice' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'rra.updateInvoice',
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
      '/azure/dashboard/get-users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.dashboard.get-users',
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
            '_route' => 'generated::HrAh2fiHUffWvPzD',
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
            '_route' => 'generated::eeIYA8aAJyJziD21',
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
            '_route' => 'generated::4lykZVbdZEBwj9Eh',
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
            '_route' => 'generated::Bfz1ORP3aptTaJPe',
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
            '_route' => 'generated::YZYn9Pu1LqY4KtFP',
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
            '_route' => 'generated::S9z68zq9JBqwXnTP',
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
      '/shipment-reports' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'shipment-reports.index',
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
      '/shipment-reports/data' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'shipment-reports.data',
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
      '/shipment-reprts/stats' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'shipment-reports.stats',
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
      '/quotation' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.index',
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
      '/quotation/data' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.data',
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
      '/quotation/charges' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.charges',
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
      '/quotation/reasons' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.reasons',
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
      '/quotation/charge-codes' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.charge-codes',
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
      '/quotation/legal-entities' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.legal-entities',
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
      '/quotation/based-on-list' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.based-on-list',
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
      '/quotation/based-on-uom' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.based-on-uom',
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
      '/quotation/contract-charges' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.contract.charges',
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
      '/quotation/equipment-types' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.equipment-types',
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
      '/quotation/charge-based-on-defaults' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.charge-based-on-defaults',
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
      '/quotation/agent-types' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.agent-types',
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
      '/quotation/customer-types' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.customer-types',
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
      '/quotation/client-list' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.client-list',
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
      '/quotation/client-details' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.client-details',
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
      '/quotation/charge-details' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.charge-details',
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
      '/quotation/email-history' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.email.history',
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
      '/quotation/shipment-modes' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.shipment-modes',
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
      '/quotation/transport-modes' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.transport-modes',
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
      '/quotation/containers' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.containers',
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
      '/quotation/report-data' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.report-data',
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
      '/quotation/debug-lookups' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::9KZ0RWItu1RjWBo2',
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
      '/quotation/debug-countries' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Djb7E2EpdYbZOkVx',
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
      '/quotation/terms-templates' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.terms-templates',
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
      '/quotation/inco-terms' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.inco-terms',
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
      '/quotation/sales-persons' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.sales-persons',
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
      '/quotation/office-code' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.office-code',
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
      '/quotation/currencies' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.currencies',
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
      '/quotation/exchange-rate' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.exchange-rate',
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
      '/quotation/communication/templates' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.communication.templates',
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
      '/quotation/communication/description' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.communication.description',
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
      '/quotation/communication/save-terms' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.communication.save-terms',
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
      '/quotation/email/send' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.email.send',
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
      '/quotation/create-booking/service-templates' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.booking.service-templates',
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
      '/quotation/create-booking/bill-to-parties' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.booking.bill-to-parties',
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
      '/quotation/create-booking/forwarders' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.booking.forwarders',
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
      '/quotation/create-booking/locations' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.booking.locations',
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
      '/quotation/lookup/countries' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.lookup.countries',
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
      '/quotation/lookup/incoterms' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.lookup.incoterms',
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
      '/quotation/lookup/service-classes' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.lookup.serviceClasses',
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
      '/quotation/lookup/industry-types' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.lookup.industryTypes',
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
      '/quotation/lookup/persons' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.lookup.persons',
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
      '/quotation/lookup/shipment-modes' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.lookup.shipmentModes',
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
      '/quotation/lookup/transport-modes' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.lookup.transportModes',
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
      '/quotation/lookup/equipment-types' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.lookup.equipmentTypes',
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
      '/quotation/process-booking' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.process-booking',
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
      '/quotation/booking/step/save-info' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.booking.step.save-info',
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
      '/quotation/booking/step/xml' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.booking.step.xml',
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
      '/quotation/booking/step/scm-push' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.booking.step.scm-push',
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
      '/quotation/booking/step/charges' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.booking.step.charges',
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
      '/quotation/booking/step/documents' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.booking.step.documents',
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
      '/quotation/booking/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.booking.create',
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
      '/quotation/charge/save' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.charge.save',
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
      '/quotation/charge/delete' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.charge.delete',
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
      '/quotation/status/update' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.status.update',
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
      '/quotation/retrigger' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.retrigger',
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
      '/quotation/customer/save' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.customer.save',
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
      '/quotation/shipment/save' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.shipment.save',
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
      '/quotation/cargo/save' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.cargo.save',
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
      '/quotation/inquiry/save' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.inquiry.save',
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
      '/quotation/push-to-scm' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.push-to-scm',
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
      '/quotation/portal' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.portal',
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
      '/quotation/portal-debug' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.portal.debug',
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
      '/quotation/copy-charges/search' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.copy-charges.search',
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
      '/quotation/copy-charges/charges' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.copy-charges.charges',
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
      '/quotation/process-won' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.process-won',
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
      '/quotation/create-booking/existing-data' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.booking.existing-data',
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
      '/quotation/charges/bulk-delete' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.charges.bulk-delete',
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
      '/quotation/copy-charges/bulk' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.copy-charges.bulk',
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
      '/crm/sync/history' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'crm.sync.history',
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
      '/crm/sync/run' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'crm.sync.run',
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
      '/weight-note' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'weight-note.index',
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
            '_route' => 'generated::toD4klR6Lx59pq3i',
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
      '/webhooks/jimi/notifications' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::4QiAi4Vk0Ds5l4Ex',
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
      '/tracker/jobs/available' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.generated::FjnXnaN7b1cTfBYn',
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
            '_route' => 'tracker.generated::laB2Q7XzZKrtc3Fz',
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
            '_route' => 'tracker.generated::MOEnkdio9s2v1F0t',
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
      '/tracker/notifications/vehicle' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.notifications.vehicle',
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
      '/tracker/notifications/trip' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.notifications.trip',
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
      '/tracker/notifications/count' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.notifications.count',
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
      '/tracker/notifications/alarm-types' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.notifications.alarm-types',
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
      '/tracker/notifications/stats' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.notifications.stats',
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
      '/zreport' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'zreport.index',
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
      '/zreport/run' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'zreport.run',
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
      '/debug-permissions' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::nRVP2Sk4jZHee6Se',
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
      0 => '{^(?|/_debugbar/c(?|lockwork/([^/]++)(*:39)|ache/([^/]++)(?:/([^/]++))?(*:73))|/password/reset/([^/]++)(*:105)|/r(?|e(?|set\\-password/([^/]++)(*:144)|port(?|s/([^/]++)(?|(*:172)|/edit(*:185)|(*:193))|ing/users/(?|([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12})(*:294)|([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12})/edit(*:386)|([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}|\\w{8}-\\w{4}-\\w{4}-\\w{4}-\\w{12})(?|(*:476)))))|oles/([^/]++)(?|(*:504)|/edit(*:517)|(*:525))|ra/(?|re\\-trigger/([^/]++)(*:560)|json/([^/]++)(*:581)|invoice/download/([^/]++)(*:614)))|/a(?|rchive/data/details/([^/]++)(*:657)|ttachment/([^/]++)/download(*:692)|zure(?|/(?|users/([^/]++)(?|/(?|disable(*:739)|e(?|nable(*:756)|dit(*:767))|re(?|try\\-(?|modules(*:796)|d365(*:808)|scm(*:819)|biz(*:830)|fitexpress(*:848))|set\\-password(*:870))|sync\\-azure\\-id(*:894)|configure\\-office(*:919)|offices/([^/]++)/assignments(*:955)|quick\\-assign\\-module(*:984)|m(?|odules/([^/]++)/assignments(*:1023)|anage\\-access(*:1045))|application\\-status(*:1074))|(*:1084))|reports/export/([^/]++)(*:1117)|api/(?|companies/([^/]++)(*:1151)|users/([^/]++)(?|/(?|disable(*:1188)|e(?|nable(*:1206)|dit(*:1218))|retry\\-modules(*:1242))|(*:1252))))|\\-sync\\-user\\-apps/([^/]++)(*:1291)))|/users/(?|([^/]++)(?|(*:1323)|/edit(*:1337)|(*:1346))|p(?|rofile/([^/]++)(*:1375)|ass/([^/]++)(*:1396))|cleanup\\-empty(*:1420))|/t(?|i(?|ck(?|ets/([^/]++)(?|/(?|edit(*:1467)|([^/]++)(*:1484))|(*:1494))|tes/pick/([^/]++)(*:1521))|ms/(?|re\\-trigger/([^/]++)(*:1557)|json/([^/]++)(*:1579)|invoice/download/([^/]++)(*:1613)))|ra(?|/re\\-trigger/([^/]++)(*:1650)|ADL/(?|re\\-trigger/([^/]++)(*:1686)|json/([^/]++)(*:1708)|invoice/download/([^/]++)(*:1742))|cker/(?|trips/(?|get(?:/([^/]++))?(*:1786)|([^/]++)(*:1803)|create(*:1818)|([^/]++)/(?|start(*:1844)|end(*:1856)|jobs/(?|add(*:1876)|([^/]++)/(?|start(*:1902)|complete(*:1919)))))|notifications/(?|([^/]++)(?|/read(*:1965)|(*:1974))|map\\-to\\-trip(*:1997)))))|/c(?|ategories/get\\-category(?:/([^/]++)(?:/([^/]++))?)?(*:2066)|lient\\-(?|details/([^/]++)(*:2101)|party/(?|show/([^/]++)(*:2132)|([^/]++)/update(*:2156)|view/([^/]++)(*:2178))))|/get\\-(?|clients/([^/]++)(*:2215)|debtor\\-vendor/([^/]++)(*:2247)|sales\\-account\\-pic/([^/]++)(*:2284)|notify\\-party\\-list/([^/]++)(*:2321)|plo\\-pld/([^/]++)(*:2347))|/de(?|lete\\-client\\-party\\-attachment/([^/]++)(*:2403)|bug\\-azure\\-files/([^/]++)(*:2438))|/efris/re\\-trigger/([^/]++)(*:2475)|/quotation/(?|([^/]++)(?|/(?|generate\\-pdf(*:2526)|d(?|uplicate(*:2547)|ownload\\-pdf/([^/]++)(*:2577))|copy(*:2591)|versions(*:2608)|edit(*:2621)|update(*:2636))|(*:2646))|booking\\-integration\\-response(*:2686))|/jimi\\-test/location/([^/]++)(*:2725))/?$}sDu',
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
      739 => 
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
      756 => 
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
      767 => 
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
      796 => 
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
      808 => 
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
      819 => 
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
      830 => 
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
      848 => 
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
      870 => 
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
      894 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.sync-azure-id',
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
      919 => 
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
      955 => 
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
      984 => 
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
      1023 => 
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
      1045 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.manage-access',
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
      1074 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'azure.users.application-status',
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
      1084 => 
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
      1117 => 
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
      1151 => 
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
      1188 => 
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
      1206 => 
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
      1218 => 
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
      1242 => 
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
      1252 => 
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
      1291 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::i8o3JnzsWEzLhAQF',
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
      1323 => 
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
      1337 => 
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
      1346 => 
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
      1375 => 
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
      1396 => 
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
      1420 => 
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
      1467 => 
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
      1484 => 
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
      1494 => 
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
      1521 => 
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
      1557 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::82q6H4nSFKinMmpA',
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
      1579 => 
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
      1613 => 
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
      1650 => 
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
      1686 => 
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
      1708 => 
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
      1742 => 
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
      1786 => 
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
      1803 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.generated::tI456uzI9BTKxOPm',
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
      1818 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.generated::bZ5FqvhONGABu5jv',
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
      1844 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.generated::xv1jmyjo55ec7OxD',
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
      1856 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.generated::IiJ9RHGTUotBeRS7',
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
      1876 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.generated::OPKVnbfPW2UyGLkT',
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
      1902 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.generated::GZEoqOwEah8DJN9o',
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
      1919 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.generated::QiHueZXA1Vtaie5Z',
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
      1965 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.notifications.read',
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
          5 => false,
          6 => NULL,
        ),
      ),
      1974 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.notifications.delete',
          ),
          1 => 
          array (
            0 => 'id',
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
      1997 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'tracker.notifications.map-to-trip',
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
      2066 => 
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
      2101 => 
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
      2132 => 
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
      2156 => 
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
      2178 => 
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
      2215 => 
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
      2247 => 
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
      2284 => 
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
      2321 => 
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
      2347 => 
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
      2403 => 
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
      2438 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::yz3rccPDG8Yaby7W',
          ),
          1 => 
          array (
            0 => 'prefix',
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
      2475 => 
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
      2526 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.generate-pdf',
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
          5 => false,
          6 => NULL,
        ),
      ),
      2547 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.processDuplicate',
          ),
          1 => 
          array (
            0 => 'quoteno',
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
      2577 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.download-pdf',
          ),
          1 => 
          array (
            0 => 'id',
            1 => 'version',
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
      2591 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.copy',
          ),
          1 => 
          array (
            0 => 'bookingno',
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
      2608 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.versions',
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
      2621 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.edit',
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
      2636 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.update',
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
          5 => false,
          6 => NULL,
        ),
      ),
      2646 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.view',
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
      2686 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quotation.bookingIntegrationResponse',
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
      2725 => 
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
    'generated::rd639xVSKWSq0xB9' => 
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
        'as' => 'generated::rd639xVSKWSq0xB9',
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
    'generated::4zOKsAyeqIhdSC7t' => 
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
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000006920000000000000000";}";s:4:"hash";s:44:"SNETd66WYHLd+VsdZ+JwI0Kxh7TO9nluKEWowFXYuWE=";}}',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::4zOKsAyeqIhdSC7t',
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
    'generated::3XebnJOAaKJI2dLq' => 
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
        'as' => 'generated::3XebnJOAaKJI2dLq',
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
    'generated::kw2SpQyCZ84BCHdT' => 
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
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:545:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:326:"function () {
    try {
        \\Illuminate\\Support\\Facades\\DB::connection(\'pgsql\')->getPdo();

        return \\response()->json([\'message\' => \'✅ PostgreSQL is connected!\']);
    } catch (\\Exception $e) {
        return \\response()->json([\'error\' => \'❌ PostgreSQL Connection Failed: \' . $e->getMessage()]);
    }
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000006950000000000000000";}";s:4:"hash";s:44:"QVYwnyL2wt6/fBr3VLLIykA/nKhWzHZH+cdbGQa7Kyw=";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::kw2SpQyCZ84BCHdT',
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
    'generated::4cukr4yCHCuUGzCw' => 
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
        'as' => 'generated::4cukr4yCHCuUGzCw',
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
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:403:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:184:"function () {
    if (\\config(\'app.env\') == \'Local\') {
        return \\view(\'auth.login\');
    }

    return \\view(\'auth.azure.login\');
    // return redirect(\'/login/azure\');
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000006980000000000000000";}";s:4:"hash";s:44:"tcuVL+dVSr9lNVU4e4m08FTWdKFaeo4uatzazvDDa64=";}}',
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
    'generated::UpzG311Lo9i8xYwi' => 
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
        'as' => 'generated::UpzG311Lo9i8xYwi',
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
    'generated::iN3cvLkOJRWLjwU6' => 
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
        'as' => 'generated::iN3cvLkOJRWLjwU6',
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
    'generated::m5SSJOQKMc2nOkzM' => 
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
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:267:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:49:"function () {
    return \\redirect(\'/login\');
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"000000000000069c0000000000000000";}";s:4:"hash";s:44:"PAsr2awqeR5X3U0qm/9o4TvawQnuPH3mM1jdccgixLQ=";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::m5SSJOQKMc2nOkzM',
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
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:732:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:513:"function () {
    $auth_user = \\Illuminate\\Support\\Facades\\Auth::user();

    // todo: permissions
    return \\redirect()->away(
        \'https://quotesuat.freightintime.com/?username=\' .
            $auth_user->LoginID .
            \'&code=\' .
            $auth_user->password .
            \'&email=\' .
            $auth_user->Email,
        302,
        [\'enc_data\' => \'\'],
    );

    // return Http::post(\'https://quotes.freightintime.com/\', [
    //     \'enc_data\' => $enc_user
    // ]);
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000006a50000000000000000";}";s:4:"hash";s:44:"3ZGeMXoJzmV+MbDKssLyYKSs4KyuBnwED/A7SNlwwQc=";}}',
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
    'generated::tJtEmlYrWfi5gJ1g' => 
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
        'as' => 'generated::tJtEmlYrWfi5gJ1g',
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
    'bizpro.users.stats' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'users/stats',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
          3 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\UsersController@stats',
        'controller' => 'App\\Http\\Controllers\\UsersController@stats',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'bizpro.users.stats',
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
    'home.switch-office' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'switch-office',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\OfficeController@switchOffice',
        'controller' => 'App\\Http\\Controllers\\OfficeController@switchOffice',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'home.switch-office',
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
    'generated::903XH3e2HJlfW8B8' => 
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
        'as' => 'generated::903XH3e2HJlfW8B8',
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
    'generated::WmUpkiOFFgSGFfdQ' => 
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
        'as' => 'generated::WmUpkiOFFgSGFfdQ',
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
    'generated::sCfJVsfOBnucD3ni' => 
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
        'as' => 'generated::sCfJVsfOBnucD3ni',
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
    'generated::yN70Von20ZbOqToH' => 
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
        'as' => 'generated::yN70Von20ZbOqToH',
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
    'generated::wX20n1nocMBd78fd' => 
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
        'as' => 'generated::wX20n1nocMBd78fd',
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
    'generated::82q6H4nSFKinMmpA' => 
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
        'as' => 'generated::82q6H4nSFKinMmpA',
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
    'efris.updateInvoice' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'efris/update-invoice',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\EfrisController@updateInvoice',
        'controller' => 'App\\Http\\Controllers\\EfrisController@updateInvoice',
        'namespace' => NULL,
        'prefix' => '/efris',
        'where' => 
        array (
        ),
        'as' => 'efris.updateInvoice',
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
    'tra.updateInvoice' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'tra/update-invoice',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\TraController@updateInvoice',
        'controller' => 'App\\Http\\Controllers\\TraController@updateInvoice',
        'namespace' => NULL,
        'prefix' => '/tra',
        'where' => 
        array (
        ),
        'as' => 'tra.updateInvoice',
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
    'traADL.updateInvoice' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'traADL/update-invoice',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\TraADLController@updateInvoice',
        'controller' => 'App\\Http\\Controllers\\TraADLController@updateInvoice',
        'namespace' => NULL,
        'prefix' => '/traADL',
        'where' => 
        array (
        ),
        'as' => 'traADL.updateInvoice',
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
    'rra.updateInvoice' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'rra/update-invoice',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'HtmlMinifier',
        ),
        'uses' => 'App\\Http\\Controllers\\RRAController@updateInvoice',
        'controller' => 'App\\Http\\Controllers\\RRAController@updateInvoice',
        'namespace' => NULL,
        'prefix' => '/rra',
        'where' => 
        array (
        ),
        'as' => 'rra.updateInvoice',
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
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:307:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:89:"function () {
            return \\redirect()->route(\'azure.dashboard.index\');
        }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000af10000000000000000";}";s:4:"hash";s:44:"tBiQwQ+/gPHjeYVKZOHgT/HJe7wJT33Y2zNZZ+edlpQ=";}}',
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
    'azure.dashboard.get-users' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/dashboard/get-users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureDashboardController@getUsers',
        'controller' => 'App\\Http\\Controllers\\AzureDashboardController@getUsers',
        'as' => 'azure.dashboard.get-users',
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
    'azure.users.sync-azure-id' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/users/{user}/sync-azure-id',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@syncAzureId',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@syncAzureId',
        'as' => 'azure.users.sync-azure-id',
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
    'azure.users.application-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure/users/{user}/application-status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:api',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@getUserApplicationStatus',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@getUserApplicationStatus',
        'as' => 'azure.users.application-status',
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
    'azure.users.manage-access' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'azure/users/{user}/manage-access',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'throttle:user_actions',
        ),
        'uses' => 'App\\Http\\Controllers\\AzureUserManagementController@manageUserApplicationAccess',
        'controller' => 'App\\Http\\Controllers\\AzureUserManagementController@manageUserApplicationAccess',
        'as' => 'azure.users.manage-access',
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
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:320:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:101:"function () {
                    return \\view(\'azure-management.modules.index\');
                }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000b150000000000000000";}";s:4:"hash";s:44:"pPV1z7KOIqVrg12BJeVoKgDQIvYaJhDRitmO9TtM0xI=";}}',
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
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:321:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:102:"function () {
                    return \\view(\'azure-management.modules.assign\');
                }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000b170000000000000000";}";s:4:"hash";s:44:"Pv0DBOzTCeXJS3WE+OwbQazMgCLJQJ1+SnA9W/0DQK4=";}}',
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
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:500:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:281:"function () {
            return \\response()->json([
                \'status\' => \'ok\',
                \'system\' => \'Azure Management\',
                \'timestamp\' => \\now()->toIso8601String(),
                \'environment\' => \\app()->environment(),
            ]);
        }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000af30000000000000000";}";s:4:"hash";s:44:"wvtjvsnO+PImG/8nfHCFw6xd3R1ulIAIg3aCfV0p99c=";}}',
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
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:1124:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:905:"function () {
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
                return \\response()->json(
                    [
                        \'success\' => false,
                        \'message\' => $e->getMessage(),
                    ],
                    500,
                );
            }
        }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000b3a0000000000000000";}";s:4:"hash";s:44:"iCuUuQk9Y9TfZqOJo59BFaoN2AVxUYz37VK4XrBmJTg=";}}',
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
    'generated::HrAh2fiHUffWvPzD' => 
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
        'as' => 'generated::HrAh2fiHUffWvPzD',
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
    'generated::eeIYA8aAJyJziD21' => 
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
        'as' => 'generated::eeIYA8aAJyJziD21',
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
    'generated::4lykZVbdZEBwj9Eh' => 
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
        'as' => 'generated::4lykZVbdZEBwj9Eh',
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
    'generated::Bfz1ORP3aptTaJPe' => 
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
        'as' => 'generated::Bfz1ORP3aptTaJPe',
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
    'generated::YZYn9Pu1LqY4KtFP' => 
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
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:12421:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:12200:"function () {
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
            echo "<pre style=\'background: #fff3cd; padding: 10px; border-radius: 4px;\'>" .
                \\htmlspecialchars($e->getTraceAsString()) .
                \'</pre>\';
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
                            [$appCode],
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
            echo "<pre style=\'background: #fff3cd; padding: 10px; border-radius: 4px;\'>" .
                \\htmlspecialchars($e->getTraceAsString()) .
                \'</pre>\';
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
                    $scmBadge = $isSCM
                        ? "<span style=\'background: #27ae60; color: white; padding: 2px 8px; border-radius: 3px; font-size: 0.85em;\'>SCM</span>"
                        : \'\';

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
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000b3e0000000000000000";}";s:4:"hash";s:44:"xx5wSORx38swgm1raOxrZQAAnXQaDYSiL8Xr0X6q8XQ=";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::YZYn9Pu1LqY4KtFP',
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
    'generated::S9z68zq9JBqwXnTP' => 
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
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:479:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:260:"function () {
    \\dd([
        \'raw_count\' => \\Illuminate\\Support\\Facades\\DB::connection(\'sqlsrv\')->table(\'Azure_Users\')->count(),
        \'model_count\' => \\App\\Models\\AzureUser::count(),
        \'first_user\' => \\App\\Models\\AzureUser::first(),
    ]);
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000b400000000000000000";}";s:4:"hash";s:44:"ChQbOkPBhpA47BOrGI56+0oi/H/OZnCPjMv+B4FYRII=";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::S9z68zq9JBqwXnTP',
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
    'shipment-reports.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'shipment-reports',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:approve-all-entity-request',
        ),
        'uses' => 'App\\Http\\Controllers\\ShipmentReportController@index',
        'controller' => 'App\\Http\\Controllers\\ShipmentReportController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'shipment-reports.index',
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
    'shipment-reports.data' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'shipment-reports/data',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:approve-all-entity-request',
        ),
        'uses' => 'App\\Http\\Controllers\\ShipmentReportController@getData',
        'controller' => 'App\\Http\\Controllers\\ShipmentReportController@getData',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'shipment-reports.data',
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
    'shipment-reports.stats' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'shipment-reprts/stats',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:approve-all-entity-request',
        ),
        'uses' => 'App\\Http\\Controllers\\ShipmentReportController@getstats',
        'controller' => 'App\\Http\\Controllers\\ShipmentReportController@getstats',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'shipment-reports.stats',
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
    'quotation.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@index',
        'controller' => 'App\\Http\\Controllers\\QuotationController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.index',
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
    'quotation.data' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/data',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@data',
        'controller' => 'App\\Http\\Controllers\\QuotationController@data',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.data',
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
    'quotation.charges' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/charges',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getCharges',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getCharges',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.charges',
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
    'quotation.reasons' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/reasons',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getReasons',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getReasons',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.reasons',
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
    'quotation.charge-codes' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/charge-codes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getChargeCodes',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getChargeCodes',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.charge-codes',
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
    'quotation.legal-entities' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/legal-entities',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getLegalEntities',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getLegalEntities',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.legal-entities',
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
    'quotation.based-on-list' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/based-on-list',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getBasedOnList',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getBasedOnList',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.based-on-list',
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
    'quotation.based-on-uom' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/based-on-uom',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getBasedOnUOM',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getBasedOnUOM',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.based-on-uom',
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
    'quotation.contract.charges' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/contract-charges',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getContractCharges',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getContractCharges',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.contract.charges',
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
    'quotation.equipment-types' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/equipment-types',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getEquipmentTypes',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getEquipmentTypes',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.equipment-types',
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
    'quotation.charge-based-on-defaults' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/charge-based-on-defaults',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getChargeBasedOnDefaults',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getChargeBasedOnDefaults',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.charge-based-on-defaults',
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
    'quotation.agent-types' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/agent-types',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getAgentTypes',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getAgentTypes',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.agent-types',
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
    'quotation.customer-types' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/customer-types',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getCustomerTypes',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getCustomerTypes',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.customer-types',
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
    'quotation.client-list' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/client-list',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getClientList',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getClientList',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.client-list',
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
    'quotation.client-details' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/client-details',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getClientDetails',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getClientDetails',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.client-details',
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
    'quotation.charge-details' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/charge-details',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getChargeDetails',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getChargeDetails',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.charge-details',
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
    'quotation.email.history' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/email-history',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getEmailHistory',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getEmailHistory',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.email.history',
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
    'quotation.shipment-modes' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/shipment-modes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getShipmentModes',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getShipmentModes',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.shipment-modes',
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
    'quotation.transport-modes' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/transport-modes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getTransportModes',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getTransportModes',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.transport-modes',
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
    'quotation.containers' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/containers',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getContainers',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getContainers',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.containers',
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
    'quotation.report-data' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/report-data',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@reportData',
        'controller' => 'App\\Http\\Controllers\\QuotationController@reportData',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.report-data',
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
    'generated::9KZ0RWItu1RjWBo2' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/debug-lookups',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@debugLookups',
        'controller' => 'App\\Http\\Controllers\\QuotationController@debugLookups',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::9KZ0RWItu1RjWBo2',
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
    'generated::Djb7E2EpdYbZOkVx' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/debug-countries',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@debugLookupCountries',
        'controller' => 'App\\Http\\Controllers\\QuotationController@debugLookupCountries',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::Djb7E2EpdYbZOkVx',
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
    'quotation.terms-templates' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/terms-templates',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getTermsTemplates',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getTermsTemplates',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.terms-templates',
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
    'quotation.inco-terms' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/inco-terms',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getIncoTerms',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getIncoTerms',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.inco-terms',
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
    'quotation.sales-persons' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/sales-persons',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getSalesPersons',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getSalesPersons',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.sales-persons',
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
    'quotation.office-code' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/office-code',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getOfficeCode',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getOfficeCode',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.office-code',
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
    'quotation.currencies' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/currencies',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getCurrencies',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getCurrencies',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.currencies',
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
    'quotation.exchange-rate' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/exchange-rate',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getExchangeRate',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getExchangeRate',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.exchange-rate',
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
    'quotation.communication.templates' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/communication/templates',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getCommunicationTemplates',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getCommunicationTemplates',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.communication.templates',
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
    'quotation.communication.description' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/communication/description',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getCommunicationDescription',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getCommunicationDescription',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.communication.description',
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
    'quotation.communication.save-terms' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/communication/save-terms',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@saveTermsCondition',
        'controller' => 'App\\Http\\Controllers\\QuotationController@saveTermsCondition',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.communication.save-terms',
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
    'quotation.email.send' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/email/send',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@sendEmailQuote',
        'controller' => 'App\\Http\\Controllers\\QuotationController@sendEmailQuote',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.email.send',
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
    'quotation.booking.service-templates' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/create-booking/service-templates',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getServiceTemplates',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getServiceTemplates',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.booking.service-templates',
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
    'quotation.booking.bill-to-parties' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/create-booking/bill-to-parties',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getBillToParties',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getBillToParties',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.booking.bill-to-parties',
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
    'quotation.booking.forwarders' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/create-booking/forwarders',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getForwarders',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getForwarders',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.booking.forwarders',
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
    'quotation.booking.locations' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/create-booking/locations',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getLocations',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getLocations',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.booking.locations',
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
    'quotation.lookup.countries' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/lookup/countries',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@lookupCountries',
        'controller' => 'App\\Http\\Controllers\\QuotationController@lookupCountries',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.lookup.countries',
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
    'quotation.lookup.incoterms' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/lookup/incoterms',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@lookupIncoTerms',
        'controller' => 'App\\Http\\Controllers\\QuotationController@lookupIncoTerms',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.lookup.incoterms',
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
    'quotation.lookup.serviceClasses' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/lookup/service-classes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@lookupServiceClasses',
        'controller' => 'App\\Http\\Controllers\\QuotationController@lookupServiceClasses',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.lookup.serviceClasses',
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
    'quotation.lookup.industryTypes' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/lookup/industry-types',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@lookupIndustryTypes',
        'controller' => 'App\\Http\\Controllers\\QuotationController@lookupIndustryTypes',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.lookup.industryTypes',
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
    'quotation.lookup.persons' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/lookup/persons',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@lookupPersonsInCharge',
        'controller' => 'App\\Http\\Controllers\\QuotationController@lookupPersonsInCharge',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.lookup.persons',
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
    'quotation.lookup.shipmentModes' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/lookup/shipment-modes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@lookupShipmentModes',
        'controller' => 'App\\Http\\Controllers\\QuotationController@lookupShipmentModes',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.lookup.shipmentModes',
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
    'quotation.lookup.transportModes' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/lookup/transport-modes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@lookupTransportModes',
        'controller' => 'App\\Http\\Controllers\\QuotationController@lookupTransportModes',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.lookup.transportModes',
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
    'quotation.lookup.equipmentTypes' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/lookup/equipment-types',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@lookupEquipmentTypes',
        'controller' => 'App\\Http\\Controllers\\QuotationController@lookupEquipmentTypes',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.lookup.equipmentTypes',
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
    'quotation.process-booking' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/process-booking',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@processBooking',
        'controller' => 'App\\Http\\Controllers\\QuotationController@processBooking',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.process-booking',
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
    'quotation.booking.step.save-info' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/booking/step/save-info',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@processBookingStep0',
        'controller' => 'App\\Http\\Controllers\\QuotationController@processBookingStep0',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.booking.step.save-info',
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
    'quotation.booking.step.xml' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/booking/step/xml',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@processBookingStep2',
        'controller' => 'App\\Http\\Controllers\\QuotationController@processBookingStep2',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.booking.step.xml',
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
    'quotation.booking.step.scm-push' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/booking/step/scm-push',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@processBookingStep2b',
        'controller' => 'App\\Http\\Controllers\\QuotationController@processBookingStep2b',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.booking.step.scm-push',
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
    'quotation.booking.step.charges' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/booking/step/charges',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@processBookingStep3',
        'controller' => 'App\\Http\\Controllers\\QuotationController@processBookingStep3',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.booking.step.charges',
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
    'quotation.booking.step.documents' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/booking/step/documents',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@processBookingStep4',
        'controller' => 'App\\Http\\Controllers\\QuotationController@processBookingStep4',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.booking.step.documents',
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
    'quotation.booking.create' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/booking/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@createBooking',
        'controller' => 'App\\Http\\Controllers\\QuotationController@createBooking',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.booking.create',
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
    'quotation.charge.save' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/charge/save',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@saveCharge',
        'controller' => 'App\\Http\\Controllers\\QuotationController@saveCharge',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.charge.save',
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
    'quotation.charge.delete' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/charge/delete',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@deleteCharge',
        'controller' => 'App\\Http\\Controllers\\QuotationController@deleteCharge',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.charge.delete',
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
    'quotation.status.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/status/update',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@updateStatus',
        'controller' => 'App\\Http\\Controllers\\QuotationController@updateStatus',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.status.update',
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
    'quotation.retrigger' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/retrigger',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@retrigger',
        'controller' => 'App\\Http\\Controllers\\QuotationController@retrigger',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.retrigger',
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
    'quotation.customer.save' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/customer/save',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@saveCustomerDetails',
        'controller' => 'App\\Http\\Controllers\\QuotationController@saveCustomerDetails',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.customer.save',
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
    'quotation.shipment.save' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/shipment/save',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@saveShipmentDetails',
        'controller' => 'App\\Http\\Controllers\\QuotationController@saveShipmentDetails',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.shipment.save',
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
    'quotation.cargo.save' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/cargo/save',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@saveCargoDetails',
        'controller' => 'App\\Http\\Controllers\\QuotationController@saveCargoDetails',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.cargo.save',
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
    'quotation.inquiry.save' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/inquiry/save',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@saveInquiryDetails',
        'controller' => 'App\\Http\\Controllers\\QuotationController@saveInquiryDetails',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.inquiry.save',
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
    'quotation.push-to-scm' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/push-to-scm',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@pushBookingToSCM',
        'controller' => 'App\\Http\\Controllers\\QuotationController@pushBookingToSCM',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.push-to-scm',
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
    'quotation.portal' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/portal',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@portal',
        'controller' => 'App\\Http\\Controllers\\QuotationController@portal',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.portal',
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
    'quotation.portal.debug' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/portal-debug',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@portalDebug',
        'controller' => 'App\\Http\\Controllers\\QuotationController@portalDebug',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.portal.debug',
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
    'quotation.copy-charges.search' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/copy-charges/search',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getCopyChargesSearch',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getCopyChargesSearch',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.copy-charges.search',
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
    'quotation.copy-charges.charges' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/copy-charges/charges',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getCopyCharges',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getCopyCharges',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.copy-charges.charges',
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
    'quotation.process-won' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/process-won',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@processWon',
        'controller' => 'App\\Http\\Controllers\\QuotationController@processWon',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.process-won',
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
    'quotation.booking.existing-data' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/create-booking/existing-data',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getBookingFormData',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getBookingFormData',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.booking.existing-data',
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
    'quotation.charges.bulk-delete' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/charges/bulk-delete',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@bulkDeleteCharges',
        'controller' => 'App\\Http\\Controllers\\QuotationController@bulkDeleteCharges',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.charges.bulk-delete',
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
    'quotation.copy-charges.bulk' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/copy-charges/bulk',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@copyChargesBulk',
        'controller' => 'App\\Http\\Controllers\\QuotationController@copyChargesBulk',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.copy-charges.bulk',
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
    'quotation.generate-pdf' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/{id}/generate-pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@generatePdf',
        'controller' => 'App\\Http\\Controllers\\QuotationController@generatePdf',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.generate-pdf',
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
    'quotation.processDuplicate' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/{quoteno}/duplicate',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@processDuplicate',
        'controller' => 'App\\Http\\Controllers\\QuotationController@processDuplicate',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.processDuplicate',
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
    'quotation.copy' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/{bookingno}/copy',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@copy',
        'controller' => 'App\\Http\\Controllers\\QuotationController@copy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.copy',
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
    'quotation.versions' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/{id}/versions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@getQuotationVersions',
        'controller' => 'App\\Http\\Controllers\\QuotationController@getQuotationVersions',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.versions',
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
    'quotation.download-pdf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/{id}/download-pdf/{version}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@downloadPdf',
        'controller' => 'App\\Http\\Controllers\\QuotationController@downloadPdf',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.download-pdf',
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
    'quotation.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/{id}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@edit',
        'controller' => 'App\\Http\\Controllers\\QuotationController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.edit',
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
    'quotation.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/{id}/update',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@update',
        'controller' => 'App\\Http\\Controllers\\QuotationController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.update',
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
    'quotation.view' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quotation/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@view',
        'controller' => 'App\\Http\\Controllers\\QuotationController@view',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.view',
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
    'crm.sync.history' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'crm/sync/history',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\CrmSyncController@history',
        'controller' => 'App\\Http\\Controllers\\CrmSyncController@history',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'crm.sync.history',
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
    'crm.sync.run' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'crm/sync/run',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view-quotation',
        ),
        'uses' => 'App\\Http\\Controllers\\CrmSyncController@run',
        'controller' => 'App\\Http\\Controllers\\CrmSyncController@run',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'crm.sync.run',
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
    'quotation.bookingIntegrationResponse' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'quotation/booking-integration-response',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\QuotationController@handleBookingIntegrationResponse',
        'controller' => 'App\\Http\\Controllers\\QuotationController@handleBookingIntegrationResponse',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quotation.bookingIntegrationResponse',
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
    'weight-note.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'weight-note',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\WeightNoteController@index',
        'controller' => 'App\\Http\\Controllers\\WeightNoteController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'weight-note.index',
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
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:283:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:65:"function () {
    return \\view(\'logintracker.imports.index\');
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000b9d0000000000000000";}";s:4:"hash";s:44:"8EXEu+LUMwdWwg22aBFbblyGeOYvDAbj3FV6/kATNqo=";}}',
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
    'generated::toD4klR6Lx59pq3i' => 
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
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:2579:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:2359:"function () {
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
        $mappedTo = $systemMap[$appLower] ?? ($systemMap[$app] ?? \'UNMAPPED\');

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

    $amayData = $data
        ->filter(function ($record) {
            return \\stripos($record->username, \'amay\') !== false;
        })
        ->values();

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
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000baa0000000000000000";}";s:4:"hash";s:44:"7ekzD9wb/ATLlMh60ev2U5QjjCNxS9DysFxw/Yk8cWI=";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::toD4klR6Lx59pq3i',
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
    'generated::4QiAi4Vk0Ds5l4Ex' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'webhooks/jimi/notifications',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\JimiWebhookController@receiveNotification',
        'controller' => 'App\\Http\\Controllers\\JimiWebhookController@receiveNotification',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::4QiAi4Vk0Ds5l4Ex',
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
    'tracker.generated::tI456uzI9BTKxOPm' => 
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
        'as' => 'tracker.generated::tI456uzI9BTKxOPm',
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
    'tracker.generated::bZ5FqvhONGABu5jv' => 
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
        'as' => 'tracker.generated::bZ5FqvhONGABu5jv',
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
    'tracker.generated::xv1jmyjo55ec7OxD' => 
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
        'as' => 'tracker.generated::xv1jmyjo55ec7OxD',
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
    'tracker.generated::IiJ9RHGTUotBeRS7' => 
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
        'as' => 'tracker.generated::IiJ9RHGTUotBeRS7',
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
    'tracker.generated::OPKVnbfPW2UyGLkT' => 
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
        'as' => 'tracker.generated::OPKVnbfPW2UyGLkT',
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
    'tracker.generated::GZEoqOwEah8DJN9o' => 
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
        'as' => 'tracker.generated::GZEoqOwEah8DJN9o',
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
    'tracker.generated::QiHueZXA1Vtaie5Z' => 
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
        'as' => 'tracker.generated::QiHueZXA1Vtaie5Z',
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
    'tracker.generated::FjnXnaN7b1cTfBYn' => 
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
        'as' => 'tracker.generated::FjnXnaN7b1cTfBYn',
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
    'tracker.generated::laB2Q7XzZKrtc3Fz' => 
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
        'as' => 'tracker.generated::laB2Q7XzZKrtc3Fz',
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
    'tracker.generated::MOEnkdio9s2v1F0t' => 
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
        'as' => 'tracker.generated::MOEnkdio9s2v1F0t',
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
    'tracker.notifications.vehicle' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tracker/notifications/vehicle',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TrackerController@getVehicleNotifications',
        'controller' => 'App\\Http\\Controllers\\TrackerController@getVehicleNotifications',
        'as' => 'tracker.notifications.vehicle',
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
    'tracker.notifications.trip' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tracker/notifications/trip',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TrackerController@getVehicleNotifications',
        'controller' => 'App\\Http\\Controllers\\TrackerController@getVehicleNotifications',
        'as' => 'tracker.notifications.trip',
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
    'tracker.notifications.count' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tracker/notifications/count',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TrackerController@getNotificationCount',
        'controller' => 'App\\Http\\Controllers\\TrackerController@getNotificationCount',
        'as' => 'tracker.notifications.count',
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
    'tracker.notifications.alarm-types' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tracker/notifications/alarm-types',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TrackerController@getAlarmTypes',
        'controller' => 'App\\Http\\Controllers\\TrackerController@getAlarmTypes',
        'as' => 'tracker.notifications.alarm-types',
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
    'tracker.notifications.stats' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'tracker/notifications/stats',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TrackerController@getNotificationStats',
        'controller' => 'App\\Http\\Controllers\\TrackerController@getNotificationStats',
        'as' => 'tracker.notifications.stats',
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
    'tracker.notifications.read' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'tracker/notifications/{id}/read',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TrackerController@markNotificationAsRead',
        'controller' => 'App\\Http\\Controllers\\TrackerController@markNotificationAsRead',
        'as' => 'tracker.notifications.read',
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
    'tracker.notifications.delete' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'tracker/notifications/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TrackerController@deleteNotification',
        'controller' => 'App\\Http\\Controllers\\TrackerController@deleteNotification',
        'as' => 'tracker.notifications.delete',
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
    'tracker.notifications.map-to-trip' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'tracker/notifications/map-to-trip',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TrackerController@mapNotificationsToTrip',
        'controller' => 'App\\Http\\Controllers\\TrackerController@mapNotificationsToTrip',
        'as' => 'tracker.notifications.map-to-trip',
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
    'zreport.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'zreport',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\ZReportRunnerController@index',
        'controller' => 'App\\Http\\Controllers\\ZReportRunnerController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'zreport.index',
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
    'zreport.run' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'zreport/run',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\ZReportRunnerController@run',
        'controller' => 'App\\Http\\Controllers\\ZReportRunnerController@run',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'zreport.run',
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
    'generated::i8o3JnzsWEzLhAQF' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'azure-sync-user-apps/{email}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:1606:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:1386:"function ($email) {
    try {
        $user = \\App\\Models\\AzureUser::where(\'userPrincipalName\', $email)->first();

        if (!$user) {
            return "❌ User not found: {$email}";
        }

        if (empty($user->AzureId)) {
            return "❌ User has no Azure ID: {$user->displayName}";
        }

        $azureService = \\app(\\App\\Services\\AzureGraphService::class);

        // Get all their modules from DB
        $modules = \\Illuminate\\Support\\Facades\\DB::connection(\'sqlsrv\')
            ->table(\'Azure_Application_UserMapping\')
            ->where(\'AzureUserID\', $user->Id)
            ->where(\'IsActive\', 1)
            ->where(\'IsDelete\', 0)
            ->distinct()
            ->pluck(\'ApplicationName\');

        $results = [];
        $spMap = \\config(\'azure.service_principal_map\', []);

        foreach ($modules as $moduleName) {
            $spId = $spMap[$moduleName] ?? null;

            if ($spId) {
                $result = $azureService->assignUserToApplication($user->AzureId, $spId);
                $results[$moduleName] = $result;
            }
        }

        return \\view(\'azure-sync-result\', [
            \'user\' => $user,
            \'modules\' => $modules,
            \'results\' => $results,
        ]);
    } catch (\\Exception $e) {
        return \'❌ Error: \' . $e->getMessage();
    }
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000bc90000000000000000";}";s:4:"hash";s:44:"H5kRNg1kQAEUqtbJWY4XG+bUQK7syQc0jTpOEygeRL8=";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::i8o3JnzsWEzLhAQF',
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
    'generated::nRVP2Sk4jZHee6Se' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'debug-permissions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:1170:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:951:"function () {
    // Find the Azure user first to get their email
    $azureUser = \\App\\Models\\Logintracker\\AzureUser::find(\'A71AFA52-654E-42D0-9BA1-C1818FE04ED0\');

    if (!$azureUser) {
        return \\response()->json([\'error\' => \'Azure user not found\']);
    }

    // Use their email to find the auth user
    $email = $azureUser->mail ?? $azureUser->userPrincipalName;
    $authUser = \\App\\Models\\User::where(\'email\', $email)->first();

    if (!$authUser) {
        return \\response()->json([
            \'error\' => \'Auth user not found\',
            \'azure_email\' => $email,
        ]);
    }

    return \\response()->json([
        \'auth_user_id\' => $authUser->id,
        \'email\' => $authUser->email,
        \'roles\' => $authUser->getRoleNames(),
        \'direct_permissions\' => $authUser->getDirectPermissions()->pluck(\'name\'),
        \'all_permissions\' => $authUser->getAllPermissions()->pluck(\'name\'),
    ]);
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000bcb0000000000000000";}";s:4:"hash";s:44:"BHTtg74GSL3x3eLvOLEDu0AuzXg4zol6xlCfjTcE9rA=";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::nRVP2Sk4jZHee6Se',
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
    'generated::yz3rccPDG8Yaby7W' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'debug-azure-files/{prefix}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:444:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:225:"function ($prefix) {
    $files = \\Storage::disk(\'azure\')->files(\'Kenya/ClientParty\');
    $filtered = \\array_filter($files, fn($f) => \\str_contains($f, $prefix));
    return \\response()->json(\\array_values($filtered));
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000bcd0000000000000000";}";s:4:"hash";s:44:"w280xgcHgCX8mjQI0jfTZRxz9FzKTTWy5PaH59ydesE=";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::yz3rccPDG8Yaby7W',
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
