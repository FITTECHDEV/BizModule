<?php

use App\Http\Controllers\ClientPartyController;
use App\Http\Controllers\EmailAccountController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\JimiTestController;
use App\Http\Controllers\JimiWebhookController;
use App\Http\Controllers\TrackerController;
use App\Http\Controllers\AzureUserManagementController;
use App\Http\Controllers\OdooSyncController;
use App\Http\Controllers\ShipmentReportController;
use App\Http\Controllers\ScmProfitIntegrationController;
use App\Http\Controllers\ApprovalController;

use App\Models\Category;
use App\Models\Office;
use App\Models\User;
use App\Services\AzureGraphService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/test-pgsql', function () {
    try {
        DB::connection('pgsql')->getPdo();

        return response()->json(['message' => '✅ PostgreSQL is connected!']);
    } catch (\Exception $e) {
        return response()->json(['error' => '❌ PostgreSQL Connection Failed: ' . $e->getMessage()]);
    }
});

Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);

/**
 * AUTH
 */
Auth::routes(['register' => false]);

Route::get('/login', function () {
    if (config('app.env') == 'Local') {
        return view('auth.login');
    }

    return view('auth.azure.login');
    // return redirect('/login/azure');
})->name('login');

Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::get('/logout', function () {
    return redirect('/login');
});

Route::get('quote-logger', function () {
    $auth_user = Auth::user();

    // todo: permissions
    return redirect()->away(
        'https://quotesuat.freightintime.com/?username=' .
            $auth_user->LoginID .
            '&code=' .
            $auth_user->password .
            '&email=' .
            $auth_user->Email,
        302,
        ['enc_data' => ''],
    );

    // return Http::post('https://quotes.freightintime.com/', [
    //     'enc_data' => $enc_user
    // ]);
})
    ->middleware(['auth', 'signed'])
    ->name('quote-logger');

/**
 * AZURE AUTH
 */
// Route::get('/login/azure', '\RootInc\LaravelAzureMiddleware\Azure@azure')
//     ->name('azure.login');
// Route::get('/login/azurecallback', '\RootInc\LaravelAzureMiddleware\Azure@azurecallback')
//     ->name('azure.callback');

Route::get('/login/azure', '\App\Http\Middleware\AppAzure@azure')->name('azure.login');
Route::get('/login/azurecallback', '\App\Http\Middleware\AppAzure@azurecallback')->name('azure.callback');
Route::get('/logout/azure', '\App\Http\Middleware\AppAzure@azurelogout')->name('azure.logout');

/**
 * Reset password routes
 */
Route::get('forget-password', [App\Http\Controllers\ForgotPasswordController::class, 'showForgetPasswordForm'])->name(
    'forget.password.get',
);
Route::post('forget-password', [
    App\Http\Controllers\ForgotPasswordController::class,
    'submitForgetPasswordForm',
])->name('forget.password.post');
Route::get('reset-password/{token}', [
    App\Http\Controllers\ForgotPasswordController::class,
    'showResetPasswordForm',
])->name('reset.password.get');
Route::post('reset-password', [App\Http\Controllers\ForgotPasswordController::class, 'submitResetPasswordForm'])->name(
    'reset.password.post',
);
// , 'change.password'

// logs
Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

Route::group(['middleware' => ['auth', 'HtmlMinifier']], function () {
    /**
     * Home
     */
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/archive', [App\Http\Controllers\HomeController::class, 'archive'])->name('archive');
    Route::post('/reports/filter', [App\Http\Controllers\HomeController::class, 'reportsFilter'])->name(
        'reportsFilter',
    );
    Route::get('/archive/data', [App\Http\Controllers\HomeController::class, 'getMasterData'])->name('archive.data');
    Route::get('/archive/data/details/{id}', [App\Http\Controllers\HomeController::class, 'getDetailsData'])->name(
        'archive.data.details',
    );
    /**
     * USERS
     */
    // Route::get('users/profile', [App\Http\Controllers\UsersController::class, 'show'])->name('user.profile');
    /**
     * USERS
     */
    // ✅ SPECIFIC ROUTES FIRST (before Route::resource)
    Route::get('users/get-change-password', [App\Http\Controllers\UsersController::class, 'GetChangePassword'])
        ->name('user.get_change_password')
        ->withoutMiddleware('change.password');

    Route::post('users/change-password', [App\Http\Controllers\UsersController::class, 'ChangePassword'])
        ->name('user.change_password')
        ->withoutMiddleware('change.password');

    // ✅ STATS ROUTE - Must come BEFORE Route::resource()
    Route::get('users/stats', [App\Http\Controllers\UsersController::class, 'stats'])
        ->name('bizpro.users.stats')
        ->middleware('auth');

    Route::post('users/ajax-update', [App\Http\Controllers\UsersController::class, 'updateAjax'])->name(
        'users.update_ajax',
    );

    // ✅ RESOURCE ROUTES - Come AFTER specific routes
    Route::resource('users', App\Http\Controllers\UsersController::class);

    // ✅ POST ROUTES WITH {user} PARAMETER - Can come after resource routes
    Route::post('users/profile/{user}', [App\Http\Controllers\UsersController::class, 'update'])->name('user.update');

    Route::post('users/pass/{user}', [App\Http\Controllers\UsersController::class, 'pass'])->name(
        'user.update.password',
    );

    /**
     * TICKETS
     */
    Route::resource('tickets', 'App\Http\Controllers\TicketController')->except('show');
    // Route::put('tickets/{ticket}', 'App\Http\Controllers\TicketController@update')->name('tickets.update');
    Route::get('tickets/{ticket}', 'App\Http\Controllers\TicketController@show')->name('tickets.show');
    Route::get('ticktes/my-tickets', [App\Http\Controllers\TicketController::class, 'myTickets'])->name(
        'tickets.my_tickets',
    );
    Route::get('ticktes/my-office-tickets', [App\Http\Controllers\TicketController::class, 'myOfficeTickets'])->name(
        'tickets.my_office_tickets',
    );
    Route::get('ticktes/assigned', [App\Http\Controllers\TicketController::class, 'assigned'])->name(
        'tickets.assigned',
    );

    // Route::post('ticktes/update/title_description', [App\Http\Controllers\TicketController::class, 'updateTicket'])->name('tickets.update_ticket');

    // Route::get('ticktes/open-tickets', [App\Http\Controllers\TicketController::class, 'openTickets'])->name('tickets.open_tickets');
    // Route::get('ticktes/closed-tickets', [App\Http\Controllers\TicketController::class, 'closedTickets'])->name('tickets.closed_tickets');
    // Route::get('ticktes/solved-tickets', [App\Http\Controllers\TicketController::class, 'solvedTickets'])->name('tickets.solved_tickets');
    // Route::get('ticktes/re-opened-tickets', [App\Http\Controllers\TicketController::class, 'reOpenedTickets'])->name('tickets.re_opened_tickets');
    Route::get('ticktes/open-tickets', [App\Http\Controllers\TicketController::class, 'openTickets'])->name(
        'tickets.open_tickets',
    );
    Route::get('ticktes/closed-tickets', [App\Http\Controllers\TicketController::class, 'closedTickets'])->name(
        'tickets.closed_tickets',
    );
    Route::get('ticktes/solved-tickets', [App\Http\Controllers\TicketController::class, 'solvedTickets'])->name(
        'tickets.solved_tickets',
    );
    Route::get('ticktes/re-opened-tickets', [App\Http\Controllers\TicketController::class, 'reOpenedTickets'])->name(
        'tickets.re_opened_tickets',
    );
    Route::get('ticktes/pick/{id}', [App\Http\Controllers\TicketController::class, 'pick'])->name('tickets.pick');
    Route::get('tickets/update/ticket/status', [App\Http\Controllers\TicketController::class, 'updateStatus'])->name(
        'tickets.update_status',
    );
    // Route::get('tickets/assign/ticket/{id?}', [App\Http\Controllers\TicketController::class, 'assignTicket'])->name('tickets.assign_ticket');
    Route::post('tickets/assign/ticket', [App\Http\Controllers\TicketController::class, 'assignTicket'])->name(
        'tickets.assign_ticket_user',
    );
    Route::get('tickets/{filter}/{filter_id}', [App\Http\Controllers\TicketController::class, 'filterTicket'])->name(
        'tickets.filter_ticket',
    );
    /**
     * DATATABLES
     */
    Route::get('datatables/all-tickets', [App\Http\Controllers\DataTablesController::class, 'allTickets'])->name(
        'datatables.all_tickets',
    );
    Route::get('datatables/my-tickets', [App\Http\Controllers\DataTablesController::class, 'myTickets'])->name(
        'datatables.my_tickets',
    );
    Route::get('datatables/my-office-tickets', [
        App\Http\Controllers\DataTablesController::class,
        'myOfficeTickets',
    ])->name('datatables.my_office_tickets');
    Route::get('datatables/assigned', [App\Http\Controllers\DataTablesController::class, 'assigned'])->name(
        'datatables.assigned',
    );
    // Route::get('datatables/closed-tickets', [App\Http\Controllers\DataTablesController::class, 'closedTickets'])->name('datatables.closed_tickets');
    // Route::get('datatables/open-tickets', [App\Http\Controllers\DataTablesController::class, 'openTickets'])->name('datatables.open_tickets');
    // Route::get('datatables/solved-tickets', [App\Http\Controllers\DataTablesController::class, 'solvedTickets'])->name('datatables.solved_tickets');
    // Route::get('datatables/re-opened-tickets', [App\Http\Controllers\DataTablesController::class, 'reOpenedTickets'])->name('datatables.re_opened_tickets');
    Route::get('datatables/closed-tickets', [App\Http\Controllers\DataTablesController::class, 'closedTickets'])->name(
        'datatables.closed_tickets',
    );
    Route::get('datatables/open-tickets', [App\Http\Controllers\DataTablesController::class, 'openTickets'])->name(
        'datatables.open_tickets',
    );
    Route::get('datatables/solved-tickets', [App\Http\Controllers\DataTablesController::class, 'solvedTickets'])->name(
        'datatables.solved_tickets',
    );
    Route::get('datatables/re-opened-tickets', [
        App\Http\Controllers\DataTablesController::class,
        'reOpenedTickets',
    ])->name('datatables.re_opened_tickets');
    Route::get('datatables/settled-tickets', [
        App\Http\Controllers\DataTablesController::class,
        'settledTickets',
    ])->name('datatables.settled_tickets');

    Route::get('datatables/users', [App\Http\Controllers\DataTablesController::class, 'users'])->name(
        'datatables.users',
    );

    Route::get('datatables/tims', [App\Http\Controllers\DataTablesController::class, 'tims'])->name('datatables.tims');

    Route::get('datatables/efris', [App\Http\Controllers\DataTablesController::class, 'efris'])->name(
        'datatables.efris',
    );

    Route::get('datatables/tra', [App\Http\Controllers\DataTablesController::class, 'tra'])->name('datatables.tra');

    Route::get('datatables/tra_fit_tz', [App\Http\Controllers\DataTablesController::class, 'traFITTZ'])->name(
        'datatables.tra_tz',
    );

    Route::get('datatables/rra', [App\Http\Controllers\DataTablesController::class, 'rra'])->name('datatables.rra');

    /**
     * OFFICE
     */
    Route::get('offices/get-name', [App\Http\Controllers\OfficeController::class, 'getOfficeName'])->name(
        'office.get_name',
    );

    Route::post('/switch-office', [App\Http\Controllers\OfficeController::class, 'switchOffice'])->name(
        'home.switch-office',
    );

    /**
     * MODULES
     */
    Route::get('modules/get-data', [App\Http\Controllers\ModuleController::class, 'getModuleData'])->name(
        'modules.get_data',
    );
    /**
     * ROLES AND PERMISSIONS
     */
    Route::resource('roles', App\Http\Controllers\RoleController::class);
    /**
     * FILES
     */
    Route::get('attachment/{id}/download', [App\Http\Controllers\AttachmentController::class, 'download'])->name(
        'attachment.download',
    );
    Route::post('attachment/upload/file', [App\Http\Controllers\AttachmentController::class, 'upload'])->name(
        'attachment.upload',
    );
    Route::delete('attachment/delete/', [App\Http\Controllers\AttachmentController::class, 'destroy'])->name(
        'attachment.destroy',
    );
    /**
     * MESSAGES
     */
    // Route::post('messages', [App\Http\Controllers\MessageController::class, 'sendMessage'])->name('messages.send');
    /**
     * CHATS
     */
    // Route::get('messages/{id}', 'App\Http\Controllers\ChatsController@fetchMessages');
    // Route::get('tickets/messages/ajax/{ticketid}', 'App\Http\Controllers\ChatsController@fetchAjaxMessages')->name('messages.ajax');

    Route::get('messages', 'App\Http\Controllers\ChatsController@fetchMessages');
    Route::post('messages', 'App\Http\Controllers\ChatsController@sendMessage');
    // Notify Route
    // Route::get('/notify', [App\Http\Controllers\NotificationController::class, 'notifyUser']);
    // Route::get('/markAsRead/{id?}', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark');
    /**
     * REPORTS
     */
    Route::resource('reports', App\Http\Controllers\ReportController::class);
    Route::post('filter/report', 'App\Http\Controllers\ReportController@filterReport');

    Route::post('/generate-report', 'App\Http\Controllers\ReportController@displayPdfReport')->name('generate_report');
    /**
     * CATEGORY
     */
    // Route::resource('categories', App\Http\Controllers\CategoryController::class);
    Route::get('categories', [App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index');
    Route::get('categories/get-category/{id?}/{type?}', [
        App\Http\Controllers\CategoryController::class,
        'getCategory',
    ])->name('getCategory');
    Route::post('categories/save-category', [App\Http\Controllers\CategoryController::class, 'store'])->name(
        'saveCategory',
    );
    Route::post('categories/update/submodule', [
        App\Http\Controllers\CategoryController::class,
        'subModuleUpdateAjax',
    ])->name('submodule.update_ajax');
    // * push notification
    Route::post('/push', [App\Http\Controllers\PushController::class, 'store']);
    Route::get('/push', [App\Http\Controllers\PushController::class, 'push'])->name('push');

    /**
     *! CLIENT PARTY ENTITY ROUTES
     */
    Route::get('/get-countries', [App\Http\Controllers\ClientPartyController::class, 'getCountries'])->name(
        'clientParty.getCountries',
    );
    Route::get('/get-cities', [App\Http\Controllers\ClientPartyController::class, 'getCities'])->name(
        'clientParty.getCities',
    );
    Route::get('/get-clients/{type}', [App\Http\Controllers\ClientPartyController::class, 'getClients'])->name(
        'clientParty.getClients',
    );
    Route::get('/autocomplete-client', [App\Http\Controllers\ClientPartyController::class, 'autocompleteClient'])->name(
        'autocomplete-client',
    );
    Route::get('/client-details/{companyName}', [
        App\Http\Controllers\ClientPartyController::class,
        'fetchClientDetails',
    ])->name('client.details');
    Route::get('/get-tarrif-list', [App\Http\Controllers\ClientPartyController::class, 'getTarrif'])->name(
        'clientParty.getTarrif',
    );
    Route::get('/get-debtor-vendor/{type}', [
        App\Http\Controllers\ClientPartyController::class,
        'getDebtorVendor',
    ])->name('clientParty.getDebtorVendor');
    Route::get('/get-provinces', [App\Http\Controllers\ClientPartyController::class, 'getProvinces'])->name(
        'clientParty.getProvinces',
    );
    Route::get('/get-offices', [App\Http\Controllers\ClientPartyController::class, 'getOffices'])->name(
        'clientParty.getOffices',
    );
    Route::get('/get-licenceType', [App\Http\Controllers\ClientPartyController::class, 'getLicenceType'])->name(
        'clientParty.getLicenceType',
    );
    Route::get('/get-party-entity', [App\Http\Controllers\ClientPartyController::class, 'getPartyEntity'])->name(
        'clientParty.getPartyEntity',
    );
    Route::get('/get-inco-terms', [App\Http\Controllers\ClientPartyController::class, 'getIncoTerms'])->name(
        'clientParty.getIncoTerms',
    );
    Route::get('/get-payment-term-list', [
        App\Http\Controllers\ClientPartyController::class,
        'getPaymentTermList',
    ])->name('clientParty.getPaymentTermList');
    Route::get('/get-currency-list', [App\Http\Controllers\ClientPartyController::class, 'getCurrencyList'])->name(
        'clientParty.getCurrencyList',
    );
    Route::get('/get-base-currency', [App\Http\Controllers\ClientPartyController::class, 'getBaseCurrency'])->name(
        'clientParty.getBaseCurrency',
    );
    Route::get('/get-office-debtor-vendor-list', [
        App\Http\Controllers\ClientPartyController::class,
        'getDebtorVendorList',
    ])->name('clientParty.getDebtorVendorList');

    Route::get('/get-sales-account-pic/{type}', [
        App\Http\Controllers\ClientPartyController::class,
        'salesAccountPCI',
    ])->name('clientParty.salesAccountPCI');

    Route::get('/get-notify-party-list/{type}', [
        App\Http\Controllers\ClientPartyController::class,
        'getNotifyPartyList',
    ])->name('clientParty.getNotifyPartyList');
    Route::get('/get-plo-pld/{type}', [App\Http\Controllers\ClientPartyController::class, 'getPOLPOD'])->name(
        'clientParty.getPOLPOD',
    );

    Route::get('client-party', [App\Http\Controllers\ClientPartyController::class, 'index'])->name(
        'client-party.index',
    );
    Route::get('client-party/show/{clientParty}', [App\Http\Controllers\ClientPartyController::class, 'show'])->name(
        'client-party.show',
    );
    Route::get('client-party/create', [App\Http\Controllers\ClientPartyController::class, 'create'])->name(
        'client-party.create',
    );
    Route::post('client-party', [App\Http\Controllers\ClientPartyController::class, 'store'])->name(
        'client-party.store',
    );
    Route::put('client-party/{id}/update', [App\Http\Controllers\ClientPartyController::class, 'update'])->name(
        'client-party.update',
    );
    Route::get('client-party/view/{path}', [App\Http\Controllers\ClientPartyController::class, 'preview'])->name(
        'client-party.preview',
    );
    Route::post('client-party/reject', [App\Http\Controllers\ClientPartyController::class, 'reject'])->name(
        'client-party.reject',
    );
    Route::post('client-party/approve', [App\Http\Controllers\ClientPartyController::class, 'approve'])->name(
        'client-party.approve',
    );
    Route::get('client-party/approve', [App\Http\Controllers\ClientPartyController::class, 'approvals'])->name(
        'client-party.approvals',
    );
    Route::post('/delete-client-party-attachment/{id}', [
        App\Http\Controllers\ClientPartyController::class,
        'deleteFile',
    ])->name('client-party.deleteFile');
    /**!client party datatables */
    Route::group(['prefix' => 'datatables'], function () {
        Route::get('client-party', [App\Http\Controllers\DataTablesController::class, 'allEntityRequests'])->name(
            'datatables.allEntityRequests',
        );
        Route::get('client-party-approval', [
            App\Http\Controllers\DataTablesController::class,
            'approvalEntityRequests',
        ])->name('datatables.approvalEntityRequests');
    });
    /**!SECTION TIMS */
    Route::group(['prefix' => 'tims', 'middleware' => ['auth', 'web']], function () {
        Route::get('/', [App\Http\Controllers\TIMSController::class, 'index'])->name('tims.index');
        Route::get('/get-stats', [App\Http\Controllers\TIMSController::class, 'getStats'])->name('tims.get_stats');
        Route::get('manual', [App\Http\Controllers\TIMSController::class, 'manual']);
        Route::get('re-trigger/{docNo}', [App\Http\Controllers\TIMSController::class, 'reTrigger']);
        Route::get('json/{id}', [App\Http\Controllers\TIMSController::class, 'jsonGenerate'])->name('tims.json');
        Route::get('invoice/download/{id}', [App\Http\Controllers\TIMSController::class, 'invoiceDownload'])
            ->name('tims.invoiceDownload')
            ->middleware(['signed']);
        Route::post('update-invoice', [App\Http\Controllers\TIMSController::class, 'updateInvoice'])->name(
            'tims.updateInvoice',
        );
    });

    /**!SECTION EFRIS */
    Route::group(['prefix' => 'efris'], function () {
        Route::get('/', [App\Http\Controllers\EfrisController::class, 'index'])->name('efris.index');
        Route::get('re-trigger/{docNo}', [App\Http\Controllers\EfrisController::class, 'reTrigger'])->name(
            'efris.reTrigger',
        );
        Route::post('update-invoice', [App\Http\Controllers\EfrisController::class, 'updateInvoice'])->name(
            'efris.updateInvoice',
        );
    });

    /**!SECTION TRA FIT*/
    Route::group(['prefix' => 'tra'], function () {
        Route::get('/', [App\Http\Controllers\TraController::class, 'index'])->name('tra.index');
        Route::get('/get-stats', [App\Http\Controllers\TraController::class, 'getStats'])->name('tra.get_stats');
        Route::get('re-trigger/{docNo}', [App\Http\Controllers\TraController::class, 'reTrigger'])->name(
            'tra.reTrigger',
        );
        Route::post('update-invoice', [App\Http\Controllers\TraController::class, 'updateInvoice'])->name(
            'tra.updateInvoice',
        );
    });

    /**!SECTION TRA ADL*/
    Route::group(['prefix' => 'traADL'], function () {
        Route::get('/', [App\Http\Controllers\TraADLController::class, 'index'])->name('traADL.index');
        Route::get('/get-stats', [App\Http\Controllers\TraADLController::class, 'getStats'])->name('traADL.get_stats');
        Route::get('re-trigger/{docNo}', [App\Http\Controllers\TraADLController::class, 'reTrigger'])->name(
            'traADL.reTrigger',
        );
        Route::get('json/{id}', [App\Http\Controllers\TraADLController::class, 'jsonGenerate'])->name('traADL.json');
        Route::get('invoice/download/{id}', [App\Http\Controllers\TraADLController::class, 'invoiceDownload'])
            ->name('traADL.invoiceDownload')
            ->middleware(['signed']);
        Route::post('update-invoice', [App\Http\Controllers\TraADLController::class, 'updateInvoice'])->name(
            'traADL.updateInvoice',
        );
    });

    /**!SECTION RRA */
    Route::group(['prefix' => 'rra'], function () {
        Route::get('/', [App\Http\Controllers\RRAController::class, 'index'])->name('rra.index');
        Route::get('/get-stats', [App\Http\Controllers\RRAController::class, 'getStats'])->name('rra.get_stats');
        Route::get('re-trigger/{docNo}', [App\Http\Controllers\RRAController::class, 'reTrigger'])->name(
            'rra.reTrigger',
        );
        Route::get('json/{id}', [App\Http\Controllers\RRAController::class, 'jsonGenerate'])->name('rra.json');
        Route::get('invoice/download/{id}', [App\Http\Controllers\RRAController::class, 'invoiceDownload'])
            ->name('rra.invoiceDownload')
            ->middleware(['signed']);
        Route::post('update-invoice', [App\Http\Controllers\RRAController::class, 'updateInvoice'])->name(
            'rra.updateInvoice',
        );
    });
});

// Route::get('/api/entities', function() {
//     $query = request('q');
//     return DB::table('Cust_Entity')
//         ->select('id', 'Name', 'Code')
//         ->where('Name', 'like', "%$query%")
//         ->take(10) // Limit to 10 results
//         ->get()
//         ->map(function($entity) {
//             return [
//                 'value' => $entity->id,
//                 'name' => $entity->Name,
//                 'code' => $entity->Code
//             ];
//         });
// })->middleware('auth'); // Ensure only authenticated users can access

Route::post('/client-party/check-license', [ClientPartyController::class, 'checkLicense'])->name(
    'client-party.check-license',
);
Route::get('/api/companies', [ClientPartyController::class, 'getCompanies'])->name('companies');
Route::get('/api/company-details', [ClientPartyController::class, 'getCompanyDetails'])->name('company.details');
Route::post('/client-party/fetch-address-details', [ClientPartyController::class, 'fetchAddressDetails'])->name(
    'client-party.fetch-address-details',
);
Route::get('/api/clients', [ClientPartyController::class, 'getClients'])->name('clients');
Route::post('/create-client-party-scm', [ClientPartyController::class, 'CreateClientPartySCM'])
    ->middleware('auth:api')
    ->name('client.create-party-scm');

// ============================================================================
// AZURE MANAGEMENT SYSTEM ROUTES
// ============================================================================

Route::prefix('azure')
    ->name('azure.')
    ->middleware(['auth'])
    ->group(function () {
        // Root redirect
        Route::get('/', function () {
            return redirect()->route('azure.dashboard.index');
        })->name('home');

        // ============================================================================
        // DASHBOARD ROUTES
        // ============================================================================

        Route::prefix('dashboard')
            ->name('dashboard.')
            ->group(function () {
                Route::get('/', [App\Http\Controllers\AzureDashboardController::class, 'index'])->name('index');
                Route::get('/stats', [App\Http\Controllers\AzureDashboardController::class, 'getStats'])
                    ->name('stats')
                    ->middleware('throttle:dashboard');
                Route::get('/reports', [App\Http\Controllers\AzureDashboardController::class, 'reports'])->name(
                    'reports',
                );
                Route::get('/activity', [App\Http\Controllers\AzureDashboardController::class, 'getRecentActivity'])
                    ->name('activity')
                    ->middleware('throttle:dashboard');

                // AJAX get users
                Route::get('/get-users', [App\Http\Controllers\AzureDashboardController::class, 'getUsers'])->name(
                    'get-users',
                );
            });

        // ============================================================================
        // USER MANAGEMENT ROUTES (with rate limiting)
        // ============================================================================

        Route::prefix('users')
            ->name('users.')
            ->group(function () {
                // ============================================================================
                // STATIC ROUTES - Must come first to prevent matching as {user} parameter
                // ============================================================================

                // List and create routes
                Route::get('/', [App\Http\Controllers\AzureUserManagementController::class, 'index'])->name('index');
                Route::get('/create', [App\Http\Controllers\AzureUserManagementController::class, 'create'])->name(
                    'create',
                );

                // Status filter routes - MUST come before /{user} routes
                Route::get('/active', [App\Http\Controllers\AzureUserManagementController::class, 'active'])->name(
                    'active',
                );
                Route::get('/pending', [App\Http\Controllers\AzureUserManagementController::class, 'pending'])->name(
                    'pending',
                );
                Route::get('/inactive', [App\Http\Controllers\AzureUserManagementController::class, 'inactive'])->name(
                    'inactive',
                );

                // Stats endpoint (must come before {user} parameter)
                Route::get('/stats', [App\Http\Controllers\AzureUserManagementController::class, 'stats'])->name(
                    'stats',
                );

                // ============================================================================
                // POST ROUTES - Order doesn't matter as much (different HTTP method)
                // ============================================================================

                // POST: Create user (rate limited - 10 per minute)
                Route::post('/', [App\Http\Controllers\AzureUserManagementController::class, 'store'])
                    ->name('store')
                    ->middleware('throttle:user_actions');

                // ============================================================================
                // DYNAMIC ROUTES - {user} parameter routes come last
                // ============================================================================

                // User-specific action routes (POST methods with {user} parameter)
                // These are rate limited to prevent abuse
                Route::post('/{user}/disable', [App\Http\Controllers\AzureUserManagementController::class, 'disable'])
                    ->name('disable')
                    ->middleware('throttle:user_actions');

                Route::post('/{user}/enable', [App\Http\Controllers\AzureUserManagementController::class, 'enable'])
                    ->name('enable')
                    ->middleware('throttle:user_actions');

                Route::post('/{user}/retry-modules', [
                    App\Http\Controllers\AzureUserManagementController::class,
                    'retryModuleAssignments',
                ])
                    ->name('retry-modules')
                    ->middleware('throttle:user_actions');

                Route::post('/{user}/reset-password', [
                    App\Http\Controllers\AzureUserManagementController::class,
                    'resetPassword',
                ])
                    ->name('reset-password')
                    ->middleware('throttle:user_actions');

                Route::post('/{user}/retry-d365', [
                    App\Http\Controllers\AzureUserManagementController::class,
                    'retryD365Provisioning',
                ])
                    ->name('retry-d365')
                    ->middleware('throttle:user_actions');

                Route::post('/{user}/retry-scm', [
                    App\Http\Controllers\AzureUserManagementController::class,
                    'retrySCMProvisioning',
                ])
                    ->name('retry-scm')
                    ->middleware('throttle:user_actions');

                Route::post('/{user}/sync-azure-id', [AzureUserManagementController::class, 'syncAzureId'])->name(
                    'sync-azure-id',
                );

                // BIZ/FITSYS Provisioning
                Route::post('/{user}/retry-biz', [
                    App\Http\Controllers\AzureUserManagementController::class,
                    'retryBIZProvisioning',
                ])
                    ->name('retry-biz')
                    ->middleware('throttle:user_actions');

                // FIT Express Provisioning
                Route::post('/{user}/retry-fitexpress', [
                    App\Http\Controllers\AzureUserManagementController::class,
                    'retryFITExpressProvisioning',
                ])
                    ->name('retry-fitexpress')
                    ->middleware('throttle:user_actions');

                // Office-specific assignment configuration
                Route::post('/{user}/configure-office', [
                    App\Http\Controllers\AzureUserManagementController::class,
                    'configureOfficeAssignments',
                ])
                    ->name('configure-office')
                    ->middleware('throttle:user_actions');

                Route::get('/{user}/offices/{office}/assignments', [
                    App\Http\Controllers\AzureUserManagementController::class,
                    'getOfficeAssignments',
                ])
                    ->name('office-assignments')
                    ->middleware('throttle:api');

                // Quick module assignment
                Route::post('/{user}/quick-assign-module', [
                    App\Http\Controllers\AzureUserManagementController::class,
                    'quickAssignModule',
                ])
                    ->name('quick-assign-module')
                    ->middleware('throttle:user_actions');

                Route::get('/{user}/modules/{module}/assignments', [
                    App\Http\Controllers\AzureUserManagementController::class,
                    'getUserModuleAssignments',
                ])
                    ->name('module-assignments')
                    ->middleware('throttle:api');

                // GET: Get user's current application access status
                Route::get('/{user}/application-status', [
                    App\Http\Controllers\AzureUserManagementController::class,
                    'getUserApplicationStatus',
                ])
                    ->name('application-status')
                    ->middleware('throttle:api');

                // POST: Manage user access across multiple applications
                Route::post('/{user}/manage-access', [
                    App\Http\Controllers\AzureUserManagementController::class,
                    'manageUserApplicationAccess',
                ])
                    ->name('manage-access')
                    ->middleware('throttle:user_actions');

                // ============================================================================
                // GENERIC CRUD ROUTES - Must come after all specific {user} routes
                // ============================================================================

                Route::get('/{user}', [App\Http\Controllers\AzureUserManagementController::class, 'show'])->name(
                    'show',
                );

                Route::get('/{user}/edit', [App\Http\Controllers\AzureUserManagementController::class, 'edit'])->name(
                    'edit',
                );

                // PUT/PATCH: Update user (rate limited - 10 per minute)
                Route::put('/{user}', [App\Http\Controllers\AzureUserManagementController::class, 'update'])
                    ->name('update')
                    ->middleware('throttle:user_actions');

                Route::patch('/{user}', [App\Http\Controllers\AzureUserManagementController::class, 'update'])
                    ->name('patch')
                    ->middleware('throttle:user_actions');

                // DELETE: Remove user (rate limited)
                Route::delete('/{user}', [App\Http\Controllers\AzureUserManagementController::class, 'destroy'])
                    ->name('destroy')
                    ->middleware('throttle:user_actions');
            });

        // ============================================================================
        // MODULE MANAGEMENT ROUTES
        // ============================================================================

        Route::prefix('modules')
            ->name('modules.')
            ->group(function () {
                Route::get('/', function () {
                    return view('azure-management.modules.index');
                })->name('index');

                Route::get('/assign', function () {
                    return view('azure-management.modules.assign');
                })->name('assign');

                // API endpoints for dynamic module assignment
                Route::get('/scm-functions', [
                    App\Http\Controllers\AzureUserManagementController::class,
                    'getSCMFunctions',
                ])
                    ->name('scm-functions')
                    ->middleware('throttle:api');

                Route::get('/roles', [
                    App\Http\Controllers\AzureUserManagementController::class,
                    'getModuleRolesByCode',
                ])
                    ->name('roles')
                    ->middleware('throttle:api');
            });

        // ============================================================================
        // REPORTS ROUTES (with rate limiting for exports)
        // ============================================================================

        Route::prefix('reports')
            ->name('reports.')
            ->group(function () {
                Route::get('/', [App\Http\Controllers\AzureReportController::class, 'index'])->name('index');
                Route::get('/users', [App\Http\Controllers\AzureReportController::class, 'users'])->name('users');
                Route::get('/activity', [App\Http\Controllers\AzureReportController::class, 'activity'])->name(
                    'activity',
                );
                Route::get('/provisioning', [App\Http\Controllers\AzureReportController::class, 'provisioning'])->name(
                    'provisioning',
                );

                // Export is a heavy operation - limit to 5 per minute
                Route::get('/export/{type}', [App\Http\Controllers\AzureReportController::class, 'export'])
                    ->name('export')
                    ->middleware('throttle:heavy_operations');
            });

        // ============================================================================
        // AZURE AUTHENTICATION ROUTES
        // ============================================================================

        Route::prefix('auth')
            ->name('auth.')
            ->group(function () {
                Route::get('/', [App\Http\Controllers\AzureAuthController::class, 'redirectToProvider'])->name(
                    'redirect',
                );
                Route::get('/callback', [
                    App\Http\Controllers\AzureAuthController::class,
                    'handleProviderCallback',
                ])->name('callback');
                Route::post('/test-connection', [
                    App\Http\Controllers\AzureAuthController::class,
                    'testConnection',
                ])->name('test');
                Route::post('/disconnect', [App\Http\Controllers\AzureAuthController::class, 'disconnect'])->name(
                    'disconnect',
                );
            });

        // ============================================================================
        // API ROUTES (AJAX/JSON endpoints with rate limiting)
        // ============================================================================

        Route::prefix('api')
            ->name('api.')
            ->group(function () {
                // Company API
                Route::get('/companies/{location}', [
                    App\Http\Controllers\Api\AzureCompanyController::class,
                    'getByLocation',
                ])
                    ->name('companies.by-location')
                    ->middleware('throttle:api');

                // User Management API (for AJAX operations)
                Route::prefix('users')
                    ->name('users.')
                    ->group(function () {
                        // List and create routes
                        Route::get('/', [App\Http\Controllers\AzureUserManagementController::class, 'index'])->name(
                            'index',
                        );
                        Route::get('/create', [
                            App\Http\Controllers\AzureUserManagementController::class,
                            'create',
                        ])->name('create');

                        // POST: Create user (rate limited - 10 per minute)
                        Route::post('/', [App\Http\Controllers\AzureUserManagementController::class, 'store'])
                            ->name('store')
                            ->middleware('throttle:user_actions');

                        // Stats endpoint (must come before {user} parameter)
                        Route::get('/stats', [
                            App\Http\Controllers\AzureUserManagementController::class,
                            'stats',
                        ])->name('stats');

                        // User-specific action routes (MUST come before generic {user} routes)
                        // These are rate limited to prevent abuse
                        Route::post('/{user}/disable', [
                            App\Http\Controllers\AzureUserManagementController::class,
                            'disable',
                        ])
                            ->name('disable')
                            ->middleware('throttle:user_actions');

                        Route::post('/{user}/enable', [
                            App\Http\Controllers\AzureUserManagementController::class,
                            'enable',
                        ])
                            ->name('enable')
                            ->middleware('throttle:user_actions');

                        Route::post('/{user}/retry-modules', [
                            App\Http\Controllers\AzureUserManagementController::class,
                            'retryModuleAssignments',
                        ])
                            ->name('retry-modules')
                            ->middleware('throttle:user_actions');

                        // Generic CRUD routes (come after specific actions)
                        Route::get('/{user}', [
                            App\Http\Controllers\AzureUserManagementController::class,
                            'show',
                        ])->name('show');

                        Route::get('/{user}/edit', [
                            App\Http\Controllers\AzureUserManagementController::class,
                            'edit',
                        ])->name('edit');

                        // PUT/PATCH: Update user (rate limited - 10 per minute)
                        Route::put('/{user}', [App\Http\Controllers\AzureUserManagementController::class, 'update'])
                            ->name('update')
                            ->middleware('throttle:user_actions');

                        Route::patch('/{user}', [App\Http\Controllers\AzureUserManagementController::class, 'update'])
                            ->name('patch')
                            ->middleware('throttle:user_actions');

                        // DELETE: Remove user (rate limited)
                        Route::delete('/{user}', [App\Http\Controllers\AzureUserManagementController::class, 'destroy'])
                            ->name('destroy')
                            ->middleware('throttle:user_actions');
                    });

                // Dashboard API (rate limited)
                Route::prefix('dashboard')
                    ->name('dashboard.')
                    ->group(function () {
                        Route::get('/stats', [App\Http\Controllers\AzureDashboardController::class, 'getStats'])
                            ->name('stats')
                            ->middleware('throttle:dashboard');
                        Route::get('/activity', [
                            App\Http\Controllers\AzureDashboardController::class,
                            'getRecentActivity',
                        ])
                            ->name('activity')
                            ->middleware('throttle:dashboard');
                    });
            });

        // ============================================================================
        // PROFILE ROUTES
        // ============================================================================

        Route::prefix('profile')
            ->name('profile.')
            ->group(function () {
                Route::get('/', [App\Http\Controllers\AzureProfileController::class, 'edit'])->name('edit');
                Route::patch('/', [App\Http\Controllers\AzureProfileController::class, 'update'])
                    ->name('update')
                    ->middleware('throttle:user_actions');
                Route::delete('/', [App\Http\Controllers\AzureProfileController::class, 'destroy'])
                    ->name('destroy')
                    ->middleware('throttle:user_actions');
            });

        // ============================================================================
        // HEALTH CHECK
        // ============================================================================

        Route::get('/health', function () {
            return response()->json([
                'status' => 'ok',
                'system' => 'Azure Management',
                'timestamp' => now()->toIso8601String(),
                'environment' => app()->environment(),
            ]);
        })->name('health');
    });

// ============================================================================
// AZURE USER SYNC ROUTES (Manual Triggers)
// ============================================================================

Route::middleware(['auth'])
    ->prefix('azure/sync')
    ->name('azure.sync.')
    ->group(function () {
        // Manual Odoo User Sync (web-based trigger)
        Route::post('/odoo-users', [OdooSyncController::class, 'syncUsersManual'])
            ->name('odoo-users')
            ->middleware('throttle:user_actions');

        // Odoo Sync Routes
        Route::post('/odoo/sync-users-ajax', [OdooSyncController::class, 'syncUsersAjax'])->name('odoo.sync.ajax');

        // Manual User Activation (web-based trigger)
        Route::post('/activate-users', [
            App\Http\Controllers\AzureUserManagementController::class,
            'activatePendingUsersManual',
        ])
            ->name('activate-users')
            ->middleware('throttle:user_actions');

        // Combined Full Sync (Odoo + Activation)
        Route::post('/full-sync', [App\Http\Controllers\AzureUserManagementController::class, 'fullSyncManual'])
            ->name('full')
            ->middleware('throttle:user_actions');

        // Sync status endpoint (AJAX)
        Route::get('/status', function () {
            try {
                $stats = [
                    'pending_users' => DB::connection('sqlsrv')
                        ->table('Azure_Users')
                        ->where('Status', 'Pending')
                        ->count(),
                    'last_sync' => Cache::get('last_odoo_sync'),
                    'last_activation' => Cache::get('last_user_activation'),
                ];

                return response()->json([
                    'success' => true,
                    'data' => $stats,
                ]);
            } catch (\Exception $e) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => $e->getMessage(),
                    ],
                    500,
                );
            }
        })->name('status');
    });

Route::get('/sync-odoo-users', [App\Http\Controllers\OdooSyncController::class, 'syncUsers']);
Route::get('/test-odoo-columns', [App\Http\Controllers\OdooSyncController::class, 'testColumns']);
Route::get('/test-pgsql-connection', [App\Http\Controllers\OdooSyncController::class, 'testConnection']);
Route::get('/check-view-definition', [App\Http\Controllers\OdooSyncController::class, 'checkViewDefinition']);

Route::get('/test-stored-procedures', function () {
    try {
        echo '<html><head><title>Stored Procedures Test</title>';
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
            .code { background: #2c3e50; color: #ecf0f1; padding: 10px; border-radius: 4px; font-family: 'Courier New', monospace; margin: 10px 0; }
        </style></head><body>";

        echo "<div class='container'>";
        echo '<h1>🔍 Stored Procedures Test</h1>';
        echo "<p class='info'>Testing: <strong>sp_UserCreation_Resourcefunction</strong> and <strong>sp_UserCreation_ResourceRole</strong></p>";
        echo '<hr>';

        // ================================================================
        // TEST 1: Check if sp_UserCreation_Resourcefunction exists
        // ================================================================
        echo "<div class='section'>";
        echo '<h2>Test 1: sp_UserCreation_Resourcefunction (SCM Functions)</h2>';

        try {
            // Check if procedure exists
            $procedureExists = DB::connection('sqlsrv')->select("
                SELECT COUNT(*) as count
                FROM sys.procedures
                WHERE name = 'sp_UserCreation_Resourcefunction'
            ");

            if ($procedureExists[0]->count > 0) {
                echo "<p class='success'>✅ Stored procedure EXISTS</p>";

                // Try to execute it
                echo "<p>Executing: <code class='code'>EXEC sp_UserCreation_Resourcefunction</code></p>";

                $functions = DB::connection('sqlsrv')->select('EXEC sp_UserCreation_Resourcefunction');

                echo "<p class='success'>✅ Execution successful! Found " . count($functions) . ' functions</p>';

                if (count($functions) > 0) {
                    echo '<table>';
                    echo '<thead><tr>';

                    // Get column names from first row
                    $firstRow = $functions[0];
                    foreach ($firstRow as $key => $value) {
                        echo '<th>' . htmlspecialchars($key) . '</th>';
                    }
                    echo '</tr></thead><tbody>';

                    // Display all rows
                    foreach ($functions as $function) {
                        echo '<tr>';
                        foreach ($function as $value) {
                            echo '<td>' . htmlspecialchars($value ?? 'NULL') . '</td>';
                        }
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                } else {
                    echo "<p class='info'>⚠️ No functions returned (empty result set)</p>";
                }
            } else {
                echo "<p class='error'>❌ Stored procedure DOES NOT EXIST</p>";
                echo '<p>Please create the stored procedure first.</p>';
            }
        } catch (\Exception $e) {
            echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . '</p>';
            echo "<pre style='background: #fff3cd; padding: 10px; border-radius: 4px;'>" .
                htmlspecialchars($e->getTraceAsString()) .
                '</pre>';
        }
        echo '</div>';

        // ================================================================
        // TEST 2: Check if sp_UserCreation_ResourceRole exists
        // ================================================================
        echo "<div class='section'>";
        echo '<h2>Test 2: sp_UserCreation_ResourceRole (Roles by Application Code)</h2>';

        try {
            // Check if procedure exists
            $procedureExists = DB::connection('sqlsrv')->select("
                SELECT COUNT(*) as count
                FROM sys.procedures
                WHERE name = 'sp_UserCreation_ResourceRole'
            ");

            if ($procedureExists[0]->count > 0) {
                echo "<p class='success'>✅ Stored procedure EXISTS</p>";

                // Test with different application codes
                $testCodes = ['OPS', 'OPSUAT', 'ERP', 'CRM'];

                foreach ($testCodes as $appCode) {
                    echo "<h3>Testing with ApplicationCode: <strong>{$appCode}</strong></h3>";
                    echo "<p>Executing: <code class='code'>EXEC sp_UserCreation_ResourceRole @ApplicationCode = '{$appCode}'</code></p>";

                    try {
                        $roles = DB::connection('sqlsrv')->select(
                            'EXEC sp_UserCreation_ResourceRole @ApplicationCode = ?',
                            [$appCode],
                        );

                        echo "<p class='success'>✅ Execution successful! Found " . count($roles) . ' roles</p>';

                        if (count($roles) > 0) {
                            echo '<table>';
                            echo '<thead><tr>';

                            // Get column names from first row
                            $firstRow = $roles[0];
                            foreach ($firstRow as $key => $value) {
                                echo '<th>' . htmlspecialchars($key) . '</th>';
                            }
                            echo '</tr></thead><tbody>';

                            // Display all rows
                            foreach ($roles as $role) {
                                echo '<tr>';
                                foreach ($role as $value) {
                                    echo '<td>' . htmlspecialchars($value ?? 'NULL') . '</td>';
                                }
                                echo '</tr>';
                            }
                            echo '</tbody></table>';
                        } else {
                            echo "<p class='info'>⚠️ No roles returned for {$appCode}</p>";
                        }
                    } catch (\Exception $e) {
                        echo "<p class='error'>❌ Error for {$appCode}: " . htmlspecialchars($e->getMessage()) . '</p>';
                    }
                }
            } else {
                echo "<p class='error'>❌ Stored procedure DOES NOT EXIST</p>";
                echo '<p>Please create the stored procedure first.</p>';
            }
        } catch (\Exception $e) {
            echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . '</p>';
            echo "<pre style='background: #fff3cd; padding: 10px; border-radius: 4px;'>" .
                htmlspecialchars($e->getTraceAsString()) .
                '</pre>';
        }
        echo '</div>';

        // ================================================================
        // TEST 3: Check available modules
        // ================================================================
        echo "<div class='section'>";
        echo '<h2>Test 3: Available Modules (Azure_SoftwareMaster)</h2>';

        try {
            $modules = DB::connection('sqlsrv')->select('
                SELECT Id, Name, ApplicationId
                FROM Azure_SoftwareMaster
                WHERE IsActive = 1 AND IsDeleted = 0
                ORDER BY Name
            ');

            echo "<p class='success'>✅ Found " . count($modules) . ' active modules</p>';

            if (count($modules) > 0) {
                echo '<table>';
                echo '<thead><tr><th>ID</th><th>Name</th><th>Application ID</th><th>Is SCM?</th></tr></thead>';
                echo '<tbody>';

                foreach ($modules as $module) {
                    $isSCM = in_array($module->ApplicationId, ['OPS', 'OPSUAT']);
                    $scmBadge = $isSCM
                        ? "<span style='background: #27ae60; color: white; padding: 2px 8px; border-radius: 3px; font-size: 0.85em;'>SCM</span>"
                        : '';

                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($module->Id) . '</td>';
                    echo '<td>' . htmlspecialchars($module->Name) . '</td>';
                    echo '<td><strong>' . htmlspecialchars($module->ApplicationId) . "</strong> {$scmBadge}</td>";
                    echo '<td>' . ($isSCM ? 'Yes' : 'No') . '</td>';
                    echo '</tr>';
                }

                echo '</tbody></table>';
            }
        } catch (\Exception $e) {
            echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . '</p>';
        }
        echo '</div>';

        // ================================================================
        // TEST 4: Check available locations
        // ================================================================
        echo "<div class='section'>";
        echo '<h2>Test 4: Available Locations (Cust_Office)</h2>';

        try {
            $locations = DB::connection('sqlsrv')->select('
                SELECT Id, Name, code
                FROM Cust_Office
                WHERE IsActive = 1 AND IsDeleted = 0
                ORDER BY Name
            ');

            echo "<p class='success'>✅ Found " . count($locations) . ' active locations</p>';

            if (count($locations) > 0) {
                echo '<table>';
                echo '<thead><tr><th>ID</th><th>Name</th><th>Code</th></tr></thead>';
                echo '<tbody>';

                foreach ($locations as $location) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($location->Id) . '</td>';
                    echo '<td>' . htmlspecialchars($location->Name) . '</td>';
                    echo '<td><strong>' . htmlspecialchars($location->code) . '</strong></td>';
                    echo '</tr>';
                }

                echo '</tbody></table>';
            }
        } catch (\Exception $e) {
            echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . '</p>';
        }
        echo '</div>';

        // Summary
        echo '<hr>';
        echo '<h2>Summary</h2>';
        echo '<p><strong>Next Steps:</strong></p>';
        echo '<ul>';
        echo "<li>If stored procedures don't exist, create them in SQL Server</li>";
        echo '<li>If stored procedures exist but return no data, populate the underlying tables</li>';
        echo '<li>If everything works, deploy the updated create user form</li>';
        echo '</ul>';

        echo '</div></body></html>';
    } catch (\Exception $e) {
        echo "<div style='padding: 20px; background: #fee; border: 2px solid #e74c3c; border-radius: 4px;'>";
        echo "<h2 style='color: #e74c3c;'>Critical Error</h2>";
        echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
        echo '<p><strong>Line:</strong> ' . htmlspecialchars($e->getLine()) . '</p>';
        echo '</div>';
    }
});

Route::get('/test-azure-users', function () {
    dd([
        'raw_count' => DB::connection('sqlsrv')->table('Azure_Users')->count(),
        'model_count' => App\Models\AzureUser::count(),
        'first_user' => App\Models\AzureUser::first(),
    ]);
});

// ============================================================================
// SHIPMENT EMAIL REPORTS ROUTES - Must come before wildcard routes
// ============================================================================
Route::middleware(['auth', 'can:approve-all-entity-request'])->group(function () {
    Route::get('/shipment-reports', [ShipmentReportController::class, 'index'])->name('shipment-reports.index');

    Route::get('/shipment-reports/data', [ShipmentReportController::class, 'getData'])->name('shipment-reports.data');

    Route::get('/shipment-reprts/stats', [ShipmentReportController::class, 'getstats'])->name('shipment-reports.stats');
});

// ============================================================================
// QUOTATION INTEGRATION ROUTES
// ============================================================================
Route::middleware(['auth', 'can:view-quotation'])->group(function () {
    Route::get('/quotation', [App\Http\Controllers\QuotationController::class, 'index'])->name('quotation.index');
    Route::get('/quotation/data', [App\Http\Controllers\QuotationController::class, 'data'])->name('quotation.data');

    // ============================================
    // STATIC LOOKUP ROUTES — all must be above any {id} wildcard
    // ============================================
    Route::get('/quotation/charges', [App\Http\Controllers\QuotationController::class, 'getCharges'])->name(
        'quotation.charges',
    );
    Route::get('/quotation/reasons', [App\Http\Controllers\QuotationController::class, 'getReasons'])->name(
        'quotation.reasons',
    );
    Route::get('/quotation/charge-codes', [App\Http\Controllers\QuotationController::class, 'getChargeCodes'])->name(
        'quotation.charge-codes',
    );
    Route::get('/quotation/legal-entities', [
        App\Http\Controllers\QuotationController::class,
        'getLegalEntities',
    ])->name('quotation.legal-entities');
    Route::get('/quotation/based-on-list', [App\Http\Controllers\QuotationController::class, 'getBasedOnList'])->name(
        'quotation.based-on-list',
    );
    Route::get('/quotation/based-on-uom', [App\Http\Controllers\QuotationController::class, 'getBasedOnUOM'])->name(
        'quotation.based-on-uom',
    );
    Route::get('/quotation/contract-charges', [
        App\Http\Controllers\QuotationController::class,
        'getContractCharges',
    ])->name('quotation.contract.charges');
    Route::get('/quotation/equipment-types', [
        App\Http\Controllers\QuotationController::class,
        'getEquipmentTypes',
    ])->name('quotation.equipment-types');
    Route::get('/quotation/charge-based-on-defaults', [
        App\Http\Controllers\QuotationController::class,
        'getChargeBasedOnDefaults',
    ])->name('quotation.charge-based-on-defaults');
    Route::get('/quotation/agent-types', [App\Http\Controllers\QuotationController::class, 'getAgentTypes'])->name(
        'quotation.agent-types',
    );
    Route::get('/quotation/customer-types', [
        App\Http\Controllers\QuotationController::class,
        'getCustomerTypes',
    ])->name('quotation.customer-types');
    Route::get('/quotation/client-list', [App\Http\Controllers\QuotationController::class, 'getClientList'])->name(
        'quotation.client-list',
    );
    Route::get('/quotation/client-details', [
        App\Http\Controllers\QuotationController::class,
        'getClientDetails',
    ])->name('quotation.client-details');
    Route::get('/quotation/charge-details', [
        App\Http\Controllers\QuotationController::class,
        'getChargeDetails',
    ])->name('quotation.charge-details');
    Route::get('/quotation/email-history', [App\Http\Controllers\QuotationController::class, 'getEmailHistory'])->name(
        'quotation.email.history',
    );
    Route::get('/quotation/shipment-modes', [
        App\Http\Controllers\QuotationController::class,
        'getShipmentModes',
    ])->name('quotation.shipment-modes');
    Route::get('/quotation/transport-modes', [
        App\Http\Controllers\QuotationController::class,
        'getTransportModes',
    ])->name('quotation.transport-modes');
    Route::get('/quotation/containers', [App\Http\Controllers\QuotationController::class, 'getContainers'])->name(
        'quotation.containers',
    );
    Route::get('/quotation/report-data', [App\Http\Controllers\QuotationController::class, 'reportData'])->name(
        'quotation.report-data',
    );
    Route::get('/quotation/debug-lookups', [App\Http\Controllers\QuotationController::class, 'debugLookups']);
    Route::get('/quotation/debug-countries', [App\Http\Controllers\QuotationController::class, 'debugLookupCountries']);
    Route::get('/quotation/terms-templates', [
        App\Http\Controllers\QuotationController::class,
        'getTermsTemplates',
    ])->name('quotation.terms-templates');

    // ✅ MOVED UP — were incorrectly placed after {id} wildcards
    Route::get('/quotation/inco-terms', [App\Http\Controllers\QuotationController::class, 'getIncoTerms'])->name(
        'quotation.inco-terms',
    );
    Route::get('/quotation/sales-persons', [App\Http\Controllers\QuotationController::class, 'getSalesPersons'])->name(
        'quotation.sales-persons',
    );

    // ============================================
    // OFFICE / CURRENCY
    // ============================================
    Route::get('/quotation/office-code', [App\Http\Controllers\QuotationController::class, 'getOfficeCode'])->name(
        'quotation.office-code',
    );
    Route::get('/quotation/currencies', [App\Http\Controllers\QuotationController::class, 'getCurrencies'])->name(
        'quotation.currencies',
    );
    Route::get('/quotation/exchange-rate', [App\Http\Controllers\QuotationController::class, 'getExchangeRate'])->name(
        'quotation.exchange-rate',
    );

    // ============================================
    // COMMUNICATION (T&C + EMAIL)
    // ============================================
    Route::get('/quotation/communication/templates', [
        App\Http\Controllers\QuotationController::class,
        'getCommunicationTemplates',
    ])->name('quotation.communication.templates');
    Route::get('/quotation/communication/description', [
        App\Http\Controllers\QuotationController::class,
        'getCommunicationDescription',
    ])->name('quotation.communication.description');
    Route::post('/quotation/communication/save-terms', [
        App\Http\Controllers\QuotationController::class,
        'saveTermsCondition',
    ])->name('quotation.communication.save-terms');
    Route::post('/quotation/email/send', [App\Http\Controllers\QuotationController::class, 'sendEmailQuote'])->name(
        'quotation.email.send',
    );

    // ============================================
    // CREATE BOOKING LOOKUPS
    // ============================================
    Route::get('/quotation/create-booking/service-templates', [
        App\Http\Controllers\QuotationController::class,
        'getServiceTemplates',
    ])->name('quotation.booking.service-templates');
    Route::get('/quotation/create-booking/bill-to-parties', [
        App\Http\Controllers\QuotationController::class,
        'getBillToParties',
    ])->name('quotation.booking.bill-to-parties');
    Route::get('/quotation/create-booking/forwarders', [
        App\Http\Controllers\QuotationController::class,
        'getForwarders',
    ])->name('quotation.booking.forwarders');
    Route::get('/quotation/create-booking/locations', [
        App\Http\Controllers\QuotationController::class,
        'getLocations',
    ])->name('quotation.booking.locations');

    // ============================================
    // LOOKUP ROUTES (duplicate modal)
    // ============================================
    Route::get('/quotation/lookup/countries', [
        App\Http\Controllers\QuotationController::class,
        'lookupCountries',
    ])->name('quotation.lookup.countries');
    Route::get('/quotation/lookup/incoterms', [
        App\Http\Controllers\QuotationController::class,
        'lookupIncoTerms',
    ])->name('quotation.lookup.incoterms');
    Route::get('/quotation/lookup/service-classes', [
        App\Http\Controllers\QuotationController::class,
        'lookupServiceClasses',
    ])->name('quotation.lookup.serviceClasses');
    Route::get('/quotation/lookup/industry-types', [
        App\Http\Controllers\QuotationController::class,
        'lookupIndustryTypes',
    ])->name('quotation.lookup.industryTypes');
    Route::get('/quotation/lookup/persons', [
        App\Http\Controllers\QuotationController::class,
        'lookupPersonsInCharge',
    ])->name('quotation.lookup.persons');
    Route::get('/quotation/lookup/shipment-modes', [
        App\Http\Controllers\QuotationController::class,
        'lookupShipmentModes',
    ])->name('quotation.lookup.shipmentModes');
    Route::get('/quotation/lookup/transport-modes', [
        App\Http\Controllers\QuotationController::class,
        'lookupTransportModes',
    ])->name('quotation.lookup.transportModes');
    Route::get('/quotation/lookup/equipment-types', [
        App\Http\Controllers\QuotationController::class,
        'lookupEquipmentTypes',
    ])->name('quotation.lookup.equipmentTypes');

    Route::get('/quotation/offices', [App\Http\Controllers\QuotationController::class, 'getOfficeList'])->name(
        'quotation.offices',
    );

    // ============================================
    // WRITE ACTIONS (POST)
    // POST routes are NOT affected by {id} GET wildcards,
    // but keeping them grouped here is cleaner.
    // ============================================
    Route::post('/quotation/process-booking', [
        App\Http\Controllers\QuotationController::class,
        'processBooking',
    ])->name('quotation.process-booking');

    // ── Individual booking step routes (sequential pipeline) ─────────────────
    Route::post('/quotation/booking/step/save-info', [
        App\Http\Controllers\QuotationController::class,
        'processBookingStep0',
    ])->name('quotation.booking.step.save-info');
    Route::post('/quotation/booking/step/xml', [
        App\Http\Controllers\QuotationController::class,
        'processBookingStep2',
    ])->name('quotation.booking.step.xml');
    Route::post('/quotation/booking/step/scm-push', [
        App\Http\Controllers\QuotationController::class,
        'processBookingStep2b',
    ])->name('quotation.booking.step.scm-push');
    Route::post('/quotation/booking/step/charges', [
        App\Http\Controllers\QuotationController::class,
        'processBookingStep3',
    ])->name('quotation.booking.step.charges');
    Route::post('/quotation/booking/step/documents', [
        App\Http\Controllers\QuotationController::class,
        'processBookingStep4',
    ])->name('quotation.booking.step.documents');

    Route::post('/quotation/booking/create', [App\Http\Controllers\QuotationController::class, 'createBooking'])->name(
        'quotation.booking.create',
    );
    Route::post('/quotation/charge/save', [App\Http\Controllers\QuotationController::class, 'saveCharge'])->name(
        'quotation.charge.save',
    );
    Route::post('/quotation/charge/delete', [App\Http\Controllers\QuotationController::class, 'deleteCharge'])->name(
        'quotation.charge.delete',
    );
    Route::post('/quotation/status/update', [App\Http\Controllers\QuotationController::class, 'updateStatus'])->name(
        'quotation.status.update',
    );
    Route::post('/quotation/retrigger', [App\Http\Controllers\QuotationController::class, 'retrigger'])->name(
        'quotation.retrigger',
    );
    Route::post('/quotation/customer/save', [
        App\Http\Controllers\QuotationController::class,
        'saveCustomerDetails',
    ])->name('quotation.customer.save');
    Route::post('/quotation/shipment/save', [
        App\Http\Controllers\QuotationController::class,
        'saveShipmentDetails',
    ])->name('quotation.shipment.save');
    Route::post('/quotation/cargo/save', [App\Http\Controllers\QuotationController::class, 'saveCargoDetails'])->name(
        'quotation.cargo.save',
    );
    // Route::post('/quotation/booking-integration-response', [App\Http\Controllers\QuotationController::class, 'handleBookingIntegrationResponse'])
    //     ->name('quotation.bookingIntegrationResponse');
    // ✅ MOVED UP — inquiry/save is a POST so technically safe, but grouped correctly herefv
    Route::post('/quotation/inquiry/save', [
        App\Http\Controllers\QuotationController::class,
        'saveInquiryDetails',
    ])->name('quotation.inquiry.save');

    Route::post('/quotation/push-to-scm', [App\Http\Controllers\QuotationController::class, 'pushBookingToSCM'])->name(
        'quotation.push-to-scm',
    );

    Route::get('/quotation/portal', [App\Http\Controllers\QuotationController::class, 'portal'])->name(
        'quotation.portal',
    );
    Route::get('/quotation/portal-debug', [App\Http\Controllers\QuotationController::class, 'portalDebug'])->name(
        'quotation.portal.debug',
    );
    Route::get('/quotation/copy-charges/search', [
        App\Http\Controllers\QuotationController::class,
        'getCopyChargesSearch',
    ])->name('quotation.copy-charges.search');
    Route::get('/quotation/copy-charges/charges', [
        App\Http\Controllers\QuotationController::class,
        'getCopyCharges',
    ])->name('quotation.copy-charges.charges');
    Route::post('/quotation/process-won', [App\Http\Controllers\QuotationController::class, 'processWon'])->name(
        'quotation.process-won',
    );
    Route::get('/quotation/create-booking/existing-data', [
        App\Http\Controllers\QuotationController::class,
        'getBookingFormData',
    ])->name('quotation.booking.existing-data');
    Route::post('/quotation/charges/bulk-delete', [
        App\Http\Controllers\QuotationController::class,
        'bulkDeleteCharges',
    ])->name('quotation.charges.bulk-delete');

    Route::post('/quotation/copy-charges/bulk', [
        App\Http\Controllers\QuotationController::class,
        'copyChargesBulk',
    ])->name('quotation.copy-charges.bulk');

    Route::post('/quotation/request-office', [
        App\Http\Controllers\QuotationController::class,
        'requestOfficeQuotation',
    ])->name('quotation.request-office');

    Route::get('/quotation/request-office-history', [
        App\Http\Controllers\QuotationController::class,
        'getRequestOfficeHistory',
    ])->name('quotation.request-office-history');

    Route::get('/quotation/sales-intelligence', [
        App\Http\Controllers\QuotationController::class,
        'salesIntelligence',
    ])->name('quotation.sales-intelligence');

    Route::get('/quotation/status-reset', [
        App\Http\Controllers\QuotationController::class,
        'showStatusResetForm',
    ])->name('quotation.status-reset.form');
    Route::post('/quotation/status-reset', [App\Http\Controllers\QuotationController::class, 'resetStatus'])->name(
        'quotation.status-reset',
    );

    Route::get('/quotation/{id}/approval-status', [
        App\Http\Controllers\QuotationController::class,
        'getApprovalStatus',
    ])->name('quotation.approval.status');

    Route::post('/quotation/{id}/send-for-approval', [
        App\Http\Controllers\QuotationController::class,
        'sendForApproval',
    ])->name('quotation.approval.send');

    // fix: add approvals dashboard route — separate from per-quote approval endpoints
    Route::get('/quotation/approvals', [App\Http\Controllers\QuotationController::class, 'approvalsDashboard'])->name(
        'quotation.approvals',
    );

    Route::post('/quotation/approvals/{requestId}/decision', [
        App\Http\Controllers\QuotationController::class,
        'submitApprovalDecision',
    ])->name('quotation.approvals.decision');

    // Approval page — reached via email link
    Route::get('/quotation/{id}/approve', [App\Http\Controllers\QuotationController::class, 'approvePage'])
        ->name('quotation.approve.page')
        ->middleware('auth');

    // Decision submission from approval page
    Route::post('/quotation/{id}/approval-decision', [
        App\Http\Controllers\QuotationController::class,
        'approvalDecision',
    ])
        ->name('quotation.approval.decision')
        ->middleware('auth');

    // Route::get('quotation/contract-templates', [App\Http\Controllers\ContractTemplateController::class, 'index'])->name(
    //     'quotation.contract-templates',
    // );

    Route::get('/quotation/{quotation}/contract-template', [
        App\Http\Controllers\ContractTemplateController::class,
        'edit',
    ])->name('quotation.contract-template.edit');
    Route::post('/quotation/{quotation}/contract-template', [
        App\Http\Controllers\ContractTemplateController::class,
        'store',
    ])->name('quotation.contract-template.store');

    Route::get('/quotation/{quotation}/contract-template/charges', [
        App\Http\Controllers\ContractTemplateController::class,
        'charges',
    ])->name('quotation.contract-template.charges');

    Route::get('/quotation/contract-template/list', [
        App\Http\Controllers\ContractTemplateController::class,
        'listContracts',
    ])->name('quotation.contract-template.list');

    Route::prefix('contracts')
        ->name('contracts.')
        ->group(function () {
            // ── 1. STATIC ROUTES (no dynamic parameters) ──
            Route::get('/create', [App\Http\Controllers\ContractController::class, 'create'])->name('create');
            Route::get('search-services', [App\Http\Controllers\ContractController::class, 'searchServices'])->name(
                'search-services',
            );
            Route::get('new-draft', [App\Http\Controllers\ContractController::class, 'newDraft'])->name('new-draft');
            Route::get('list', [App\Http\Controllers\ContractController::class, 'listContracts'])->name('list');

            // ── 2. MASTER ROUTES (also static) ──
            Route::prefix('masters')->group(function () {
                Route::get('inco-terms', [App\Http\Controllers\ContractController::class, 'getIncoTerms']);
                Route::get('countries', [App\Http\Controllers\ContractController::class, 'getCountries']);
                Route::get('shipment-modes', [App\Http\Controllers\ContractController::class, 'getShipmentModes']);
                Route::get('transport-modes', [App\Http\Controllers\ContractController::class, 'getTransportModes']);
                Route::get('equipment-types', [App\Http\Controllers\ContractController::class, 'getEquipmentTypes']);
                Route::get('charge-codes', [App\Http\Controllers\ContractController::class, 'getChargeCodes']);
                Route::get('based-on', [App\Http\Controllers\ContractController::class, 'getBasedOnList']);
                Route::get('based-on-uom', [App\Http\Controllers\ContractController::class, 'getBasedOnUOM']);
                Route::get('currencies', [App\Http\Controllers\ContractController::class, 'getCurrencies']);
                Route::get('exchange-rate', [App\Http\Controllers\ContractController::class, 'getExchangeRate']);
                Route::get('locations', [App\Http\Controllers\ContractController::class, 'getLocations']);
                Route::get('services', [App\Http\Controllers\ContractController::class, 'getServices']);
                Route::get('processes', [App\Http\Controllers\ContractController::class, 'getProcesses']);
                Route::get('shipment-types', [App\Http\Controllers\ContractController::class, 'getShipmentTypes']);
                Route::get('movement-types', [App\Http\Controllers\ContractController::class, 'getMovementTypes']);
                Route::get('charge-categories', [
                    App\Http\Controllers\ContractController::class,
                    'getChargeCategories',
                ]);
            });

            // ── 3. WILDCARD ROUTES (catch-all) ──
            Route::get('/{contract}/view', [App\Http\Controllers\ContractController::class, 'showDetails'])->name(
                'view',
            );
            Route::get('/{contract}/edit', [App\Http\Controllers\ContractController::class, 'edit'])->name('edit');
            Route::put('/{contract}', [App\Http\Controllers\ContractController::class, 'updateHeader'])->name('update');
            Route::post('/{contract}/services', [App\Http\Controllers\ContractController::class, 'storeService'])->name(
                'services.store',
            );
            Route::put('/{contract}/services/{serviceFilterId}', [
                App\Http\Controllers\ContractController::class,
                'updateService',
            ])->name('services.update');
            Route::post('/{contract}/services/{serviceFilterId}/charges', [
                App\Http\Controllers\ContractController::class,
                'storeCharge',
            ])->name('services.charges.store');
            Route::put('/{contract}/services/{serviceFilterId}/charges/{chargeId}', [
                App\Http\Controllers\ContractController::class,
                'updateCharge',
            ])->name('services.charges.update');
            Route::post('/{contract}', [App\Http\Controllers\ContractController::class, 'store'])->name('store');
            Route::get('/{contract}', [App\Http\Controllers\ContractController::class, 'show'])->name('show');
            Route::get('/{contract}/services/{serviceFilterId}/formula-fields', [
                App\Http\Controllers\ContractController::class,
                'getFormulaFields',
            ])->name('services.formula-fields');
        });

    Route::get('/quotation/website-requests', [App\Http\Controllers\WebsiteQuotationController::class, 'index'])->name(
        'quotation.website-requests',
    );

    // WEBSITE QUOTATION REQUESTS
    // ============================================
    Route::get('/quotation/website-requests', [App\Http\Controllers\WebsiteQuotationController::class, 'index'])->name(
        'quotation.website-requests',
    );

    Route::get('/quotation/website-requests/portal', [
        App\Http\Controllers\WebsiteQuotationController::class,
        'portal',
    ])->name('quotation.website-requests.portal');
});

// ============================================
// WILDCARD ROUTES — must be last
// {id} in a GET route matches ANY path segment.
// Nothing with a static segment may come after /quotation/{id}.
// ============================================
Route::post('/quotation/{id}/generate-pdf', [App\Http\Controllers\QuotationController::class, 'generatePdf'])->name(
    'quotation.generate-pdf',
);
Route::post('/quotation/{quoteno}/duplicate', [
    App\Http\Controllers\QuotationController::class,
    'processDuplicate',
])->name('quotation.processDuplicate');
Route::post('/quotation/{bookingno}/copy', [App\Http\Controllers\QuotationController::class, 'copy'])->name(
    'quotation.copy',
);

Route::get('/quotation/{id}/versions', [App\Http\Controllers\QuotationController::class, 'getQuotationVersions'])->name(
    'quotation.versions',
);
Route::get('/quotation/{id}/download-pdf/{version}', [
    App\Http\Controllers\QuotationController::class,
    'downloadPdf',
])->name('quotation.download-pdf');
Route::get('/quotation/{id}/edit', [App\Http\Controllers\QuotationController::class, 'edit'])->name('quotation.edit');
Route::post('/quotation/{id}/update', [App\Http\Controllers\QuotationController::class, 'update'])->name(
    'quotation.update',
);

// ⚠️ ABSOLUTE LAST — catches /quotation/<anything>
Route::get('/quotation/{id}', [App\Http\Controllers\QuotationController::class, 'view'])->name('quotation.view');

// ============================================
// CRM SYNC
// ============================================
Route::get('/crm/sync/history', [App\Http\Controllers\CrmSyncController::class, 'history'])->name('crm.sync.history');
Route::post('/crm/sync/run', [App\Http\Controllers\CrmSyncController::class, 'run'])->name('crm.sync.run');

Route::post('/quotation/booking-integration-response', [
    App\Http\Controllers\QuotationController::class,
    'handleBookingIntegrationResponse',
])->name('quotation.bookingIntegrationResponse');

Route::get('/weight-note', [App\Http\Controllers\WeightNoteController::class, 'index'])->name('weight-note.index');

// ============================================================================
// LOGIN TRACKER SYSTEM ROUTES (User Activity Monitoring)
// ============================================================================

// Restrict the {user} parameter (supports email or GUID)
Route::pattern('user', '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}|\w{8}-\w{4}-\w{4}-\w{4}-\w{12}');

// ----------------------
// Reporting routes (Login Tracker)
// ----------------------
Route::prefix('reporting')
    ->middleware('auth')
    ->group(function () {
        // Explicit show route (needed for dashboard links)
        Route::get('/users/{user}', [\App\Http\Controllers\UserController::class, 'show'])->name(
            'reporting.users.show',
        );

        Route::get('/users/active-this-month', [\App\Http\Controllers\UserController::class, 'activeThisMonth'])->name(
            'reporting.users.active-this-month',
        );

        Route::get('/users/active-last-month', [\App\Http\Controllers\UserController::class, 'activeLastMonth'])->name(
            'reporting.users.active-last-month',
        );

        Route::get('/users/inactive', [\App\Http\Controllers\UserController::class, 'inactiveUsers'])->name(
            'reporting.users.inactive',
        );

        // Resource routes except show
        Route::resource('/users', \App\Http\Controllers\UserController::class)
            ->parameters(['users' => 'user'])
            ->except(['show'])
            ->names([
                'index' => 'reporting.users.index',
                'create' => 'reporting.users.create',
                'store' => 'reporting.users.store',
                'edit' => 'reporting.users.edit',
                'update' => 'reporting.users.update',
                'destroy' => 'reporting.users.destroy',
            ]);
    });

// ----------------------
// Dashboard route (Login Tracker Dashboard)
// ----------------------
Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::get('/dashboard/debug-user-logins', [\App\Http\Controllers\DashboardController::class, 'debugUserLogins'])
    ->middleware('auth')
    ->name('dashboard.debug-user-logins');

// ----------------------
// Imports route (Login Tracker)
// ----------------------
Route::get('/imports', function () {
    return view('logintracker.imports.index');
})
    ->middleware('auth')
    ->name('imports.index');

// ----------------------
// Activity report (Login Tracker)
// ----------------------
Route::get('/activity-report', [\App\Http\Controllers\ActivityReportController::class, 'userActivityReport'])
    ->middleware('auth')
    ->name('activity.report');

// ----------------------
// CSV import endpoints (Login Tracker)
// ----------------------
Route::post('/import/users', [\App\Http\Controllers\ImportController::class, 'importUsers'])
    ->middleware('auth')
    ->name('imports.users');

Route::post('/import/sign-ins', [\App\Http\Controllers\ImportController::class, 'importSignIns'])
    ->middleware('auth')
    ->name('imports.sign_ins');

Route::post('/dashboard/clear-cache', [\App\Http\Controllers\DashboardController::class, 'clearDashboardCache'])
    ->middleware('auth')
    ->name('dashboard.clear-cache');

// ✅ NEW: Import status monitoring endpoint (optional but useful)
Route::get('/imports/status', [ImportController::class, 'getImportStatus'])
    ->name('imports.status')
    ->middleware('auth'); // Add your auth middleware

Route::get('/users/cleanup-empty', [\App\Http\Controllers\UserController::class, 'cleanupEmptyUsers'])
    ->middleware('auth')
    ->name('users.cleanup');

Route::get('/debug-import-data', function () {
    $dates = ['2025-10-10', '2025-10-23'];

    $data = DB::table('Azure_LoginDetails')
        ->selectRaw('CONVERT(DATE, date_utc) as login_date')
        ->selectRaw('application')
        ->selectRaw('username')
        ->selectRaw('user_id')
        ->selectRaw('COUNT(*) as count')
        ->whereRaw('CONVERT(DATE, date_utc) IN (?, ?)', $dates)
        ->groupBy(DB::raw('CONVERT(DATE, date_utc)'), 'application', 'username', 'user_id')
        ->orderBy('login_date')
        ->orderBy('username')
        ->get();

    $uniqueApps = $data->pluck('application')->unique()->values();
    $systemMap = config('systemmap', []);

    $appMappingStatus = [];
    foreach ($uniqueApps as $app) {
        $appLower = strtolower(trim($app));
        $isMapped = isset($systemMap[$appLower]) || isset($systemMap[$app]);
        $mappedTo = $systemMap[$appLower] ?? ($systemMap[$app] ?? 'UNMAPPED');

        $appMappingStatus[$app] = [
            'is_mapped' => $isMapped,
            'maps_to' => $mappedTo,
            'in_display_systems' => in_array($mappedTo, [
                'BIZ PRO',
                'BIZ UAT',
                'D365 LIVE',
                'D365 UAT',
                'FIT Express',
                'FIT Express UAT',
                'Odoo',
                'OPS',
                'OPS UAT',
                'Windows Sign In',
            ]),
        ];
    }

    $amayData = $data
        ->filter(function ($record) {
            return stripos($record->username, 'amay') !== false;
        })
        ->values();

    return response()->json([
        'dates_checked' => $dates,
        'total_records' => $data->count(),
        'unique_applications' => $uniqueApps,
        'application_mapping_status' => $appMappingStatus,
        'amay_records' => [
            'count' => $amayData->count(),
            'data' => $amayData,
        ],
        'all_records' => $data->toArray(),
        'display_systems' => [
            'BIZ PRO',
            'BIZ UAT',
            'D365 LIVE',
            'D365 UAT',
            'FIT Express',
            'FIT Express UAT',
            'Odoo',
            'OPS',
            'OPS UAT',
            'Windows Sign In',
        ],
    ]);
})->middleware('auth');

// ============================================================================
// VEHICLE TRACKER SYSTEM ROUTES (GPS/Trip Tracking)
// ============================================================================
// Webhook route (public - no auth)
Route::post('/webhooks/jimi/notifications', [JimiWebhookController::class, 'receiveNotification']);

// Tracker routes (authenticated)
Route::middleware(['auth'])
    ->prefix('tracker')
    ->name('tracker.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [TrackerController::class, 'dashboard'])->name('dashboard');

        // Trips
        Route::get('/trips/get/{filter?}', [TrackerController::class, 'getTrips']);
        Route::get('/trips/{id}', [TrackerController::class, 'getTripDetails']);
        Route::post('/trips/create', [TrackerController::class, 'createTrip']);
        Route::post('/trips/{tripId}/start', [TrackerController::class, 'startTrip']);
        Route::post('/trips/{tripId}/end', [TrackerController::class, 'endTrip']);

        // Jobs
        Route::post('/trips/{tripId}/jobs/add', [TrackerController::class, 'addJobToTrip']);
        Route::post('/trips/{tripId}/jobs/{jobId}/start', [TrackerController::class, 'startJob']);
        Route::post('/trips/{tripId}/jobs/{jobId}/complete', [TrackerController::class, 'completeJob']);
        Route::get('/jobs/available', [TrackerController::class, 'getAvailableJobs']);

        // Vehicles & Locations
        Route::get('/vehicles/list', [TrackerController::class, 'getVehicles']);
        Route::get('/locations', [TrackerController::class, 'getLocations']);

        // List pending geofence notifications for a vehicle (used in modal)
        Route::get('/notifications/vehicle', [TrackerController::class, 'getVehicleNotifications'])->name(
            'notifications.vehicle',
        );

        // List pending geofence notifications filtered by trip + vehicle
        // (you can use the same method and filter in PHP, or create a separate one later)
        Route::get('/notifications/trip', [TrackerController::class, 'getVehicleNotifications'])->name(
            'notifications.trip',
        );

        // Simple count – used for badge on trip modal / list
        Route::get('/notifications/count', [TrackerController::class, 'getNotificationCount'])->name(
            'notifications.count',
        );

        // Notifications
        // Route::get('/notifications/get', [TrackerController::class, 'getNotifications'])->name('notifications.get');
        Route::get('/notifications/alarm-types', [TrackerController::class, 'getAlarmTypes'])->name(
            'notifications.alarm-types',
        );
        Route::get('/notifications/stats', [TrackerController::class, 'getNotificationStats'])->name(
            'notifications.stats',
        );
        Route::post('/notifications/{id}/read', [TrackerController::class, 'markNotificationAsRead'])->name(
            'notifications.read',
        );
        Route::delete('/notifications/{id}', [TrackerController::class, 'deleteNotification'])->name(
            'notifications.delete',
        );
        Route::post('/notifications/map-to-trip', [TrackerController::class, 'mapNotificationsToTrip'])->name(
            'notifications.map-to-trip',
        );
    });

// // Temporary redirect - forwards notifications from production to UAT
// Route::post('/webhooks/jimi/notifications', function (Request $request) {
//     // Forward to UAT
//     Http::post('https://bizuat.freightintime.com/webhooks/jimi/notifications', [
//         'msgType' => $request->input('msgType'),
//         'data' => $request->input('data')
//     ]);

//     return response()->json(['success' => true, 'message' => 'Forwarded to UAT']);
// });

Route::middleware(['auth'])
    ->prefix('jimi-test')
    ->group(function () {
        // Test page
        Route::get('/', [JimiTestController::class, 'testPage'])->name('jimi.test');

        // Test connection
        Route::get('/connection', [JimiTestController::class, 'testConnection'])->name('jimi.test.connection');

        // Get all vehicles
        Route::get('/vehicles', [JimiTestController::class, 'getVehicles'])->name('jimi.test.vehicles');

        // Get current location
        Route::get('/location/{imei}', [JimiTestController::class, 'getCurrentLocation'])->name('jimi.test.location');

        // Get mileage
        Route::post('/mileage', [JimiTestController::class, 'getMileage'])->name('jimi.test.mileage');

        // Get track data
        Route::post('/track', [JimiTestController::class, 'getTrackData'])->name('jimi.test.track');
    });

Route::get('zreport', [App\Http\Controllers\ZReportRunnerController::class, 'index'])->name('zreport.index');
Route::post('zreport/run', [App\Http\Controllers\ZReportRunnerController::class, 'run'])->name('zreport.run');

Route::get('/azure-sync-user-apps/{email}', function ($email) {
    try {
        $user = \App\Models\AzureUser::where('userPrincipalName', $email)->first();

        if (!$user) {
            return "❌ User not found: {$email}";
        }

        if (empty($user->AzureId)) {
            return "❌ User has no Azure ID: {$user->displayName}";
        }

        $azureService = app(\App\Services\AzureGraphService::class);

        // Get all their modules from DB
        $modules = DB::connection('sqlsrv')
            ->table('Azure_Application_UserMapping')
            ->where('AzureUserID', $user->Id)
            ->where('IsActive', 1)
            ->where('IsDelete', 0)
            ->distinct()
            ->pluck('ApplicationName');

        $results = [];
        $spMap = config('azure.service_principal_map', []);

        foreach ($modules as $moduleName) {
            $spId = $spMap[$moduleName] ?? null;

            if ($spId) {
                $result = $azureService->assignUserToApplication($user->AzureId, $spId);
                $results[$moduleName] = $result;
            }
        }

        return view('azure-sync-result', [
            'user' => $user,
            'modules' => $modules,
            'results' => $results,
        ]);
    } catch (\Exception $e) {
        return '❌ Error: ' . $e->getMessage();
    }
})->middleware('auth');

Route::get('/debug-permissions', function () {
    // Find the Azure user first to get their email
    $azureUser = \App\Models\Logintracker\AzureUser::find('A71AFA52-654E-42D0-9BA1-C1818FE04ED0');

    if (!$azureUser) {
        return response()->json(['error' => 'Azure user not found']);
    }

    // Use their email to find the auth user
    $email = $azureUser->mail ?? $azureUser->userPrincipalName;
    $authUser = \App\Models\User::where('email', $email)->first();

    if (!$authUser) {
        return response()->json([
            'error' => 'Auth user not found',
            'azure_email' => $email,
        ]);
    }

    return response()->json([
        'auth_user_id' => $authUser->id,
        'email' => $authUser->email,
        'roles' => $authUser->getRoleNames(),
        'direct_permissions' => $authUser->getDirectPermissions()->pluck('name'),
        'all_permissions' => $authUser->getAllPermissions()->pluck('name'),
    ]);
});
// Add a temporary debug route in web.php or api.php
Route::get('/debug-azure-files/{prefix}', function ($prefix) {
    $files = Storage::disk('azure')->files('Kenya/ClientParty');
    $filtered = array_filter($files, fn($f) => str_contains($f, $prefix));
    return response()->json(array_values($filtered));
});
// ZRA Smart Invoice
Route::get('/zra', [App\Http\Controllers\ZraController::class, 'index'])
    ->name('zra.index')
    ->middleware('auth');
Route::get('/zra/get-stats', [App\Http\Controllers\ZraController::class, 'getStats'])->middleware('auth');
Route::post('/zra/update-invoice', [App\Http\Controllers\ZraController::class, 'updateInvoice'])->middleware('auth');
Route::get('/zra/re-trigger/{docNo}', [App\Http\Controllers\ZraController::class, 'reTrigger'])->middleware('auth');
Route::get('/datatables/zra', [App\Http\Controllers\ZraController::class, 'datatable'])->middleware('auth');
Route::get('/approval/{id}/approve', [ApprovalController::class, 'approve'])
    ->name('approval.approve')
    ->middleware('signed'); // rejects invalid/expired signatures automatically
