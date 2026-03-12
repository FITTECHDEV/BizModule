<?php

use App\Http\Controllers\AzureUserManagementController;
use App\Http\Controllers\ClientPartyController;
use App\Http\Controllers\EmailAccountController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\JimiTestController;
use App\Http\Controllers\JimiWebhookController;
use App\Http\Controllers\OdooSyncController;
use App\Http\Controllers\RAIntegrationController;
use App\Http\Controllers\ShipmentReportController;
use App\Http\Controllers\StoredProcedureTestController;
use App\Http\Controllers\TrackerController;
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
    // return redirec t('/login/azure');
})->name('login');

Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::get('/logout', function () {
    return redirect('/login');
});

Route::get('quote-logger', function () {

    $auth_user = Auth::user();

    // todo: permissions
    return redirect()->away('https://quotesuat.freightintime.com/?username=' . $auth_user->LoginID . '&code=' . $auth_user->password . '&email=' . $auth_user->Email, 302, ['enc_data' => '']);

    // return Http::post('https://quotes.freightintime.com/', [
    //     'enc_data' => $enc_user
    // ]);
})->middleware(['auth', 'signed'])->name('quote-logger');

/**
 * AZURE AUTH
 */
// Route::get('/login/azure', '\RootInc\LaravelAzureMiddleware\Azure@azure')
//     ->name('azure.login');
// Route::get('/login/azurecallback', '\RootInc\LaravelAzureMiddleware\Azure@azurecallback')
//     ->name('azure.callback');

Route::get('/login/azure', '\App\Http\Middleware\AppAzure@azure')
    ->name('azure.login');
Route::get('/login/azurecallback', '\App\Http\Middleware\AppAzure@azurecallback')
    ->name('azure.callback');
Route::get('/logout/azure', '\App\Http\Middleware\AppAzure@azurelogout')
    ->name('azure.logout');

/**
 * Reset password routes
 */
Route::get('forget-password', [App\Http\Controllers\ForgotPasswordController::class, 'showForgetPasswordForm'])->name('forget.password.get');
Route::post('forget-password', [App\Http\Controllers\ForgotPasswordController::class, 'submitForgetPasswordForm'])->name('forget.password.post');
Route::get('reset-password/{token}', [App\Http\Controllers\ForgotPasswordController::class, 'showResetPasswordForm'])->name('reset.password.get');
Route::post('reset-password', [App\Http\Controllers\ForgotPasswordController::class, 'submitResetPasswordForm'])->name('reset.password.post');
// , 'change.password'

// logs
Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

Route::group(['middleware' => ['auth', 'HtmlMinifier']], function () {
    /**
     * Home
     */
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/archive', [App\Http\Controllers\HomeController::class, 'archive'])->name('archive');
    Route::post('/reports/filter', [App\Http\Controllers\HomeController::class, 'reportsFilter'])->name('reportsFilter');
    Route::get('/archive/data', [App\Http\Controllers\HomeController::class, 'getMasterData'])->name('archive.data');
    Route::get('/archive/data/details/{id}', [App\Http\Controllers\HomeController::class, 'getDetailsData'])->name('archive.data.details');

    /**
     * USERS
     */
    // ✅ STATS ROUTE - Must come BEFORE Route::resource() and WITHOUT duplicate middleware
    Route::get('users/stats', [App\Http\Controllers\UsersController::class, 'stats'])
        ->name('bizpro.users.stats');

    // Other specific user routes that need to come before resource routes
    Route::post('users/profile/{user}', [App\Http\Controllers\UsersController::class, 'update'])->name('user.update');
    Route::get('users/get-change-password', [App\Http\Controllers\UsersController::class, 'GetChangePassword'])->name('user.get_change_password')->withoutMiddleware('change.password');
    Route::post('users/change-password', [App\Http\Controllers\UsersController::class, 'ChangePassword'])->name('user.change_password')->withoutMiddleware('change.password');
    Route::post('users/pass/{user}', [App\Http\Controllers\UsersController::class, 'pass'])->name('user.update.password');
    Route::post('users/ajax-update', [App\Http\Controllers\UsersController::class, 'updateAjax'])->name('users.update_ajax');

    // Resource routes come AFTER all specific routes
    Route::resource('users', App\Http\Controllers\UsersController::class);

    /**
     * TICKETS
     */
    Route::resource('tickets', 'App\Http\Controllers\TicketController')->except('show');
    // Route::put('tickets/{ticket}', 'App\Http\Controllers\TicketController@update')->name('tickets.update');
    Route::get('tickets/{ticket}', 'App\Http\Controllers\TicketController@show')->name('tickets.show');
    Route::get('ticktes/my-tickets', [App\Http\Controllers\TicketController::class, 'myTickets'])->name('tickets.my_tickets');
    Route::get('ticktes/my-office-tickets', [App\Http\Controllers\TicketController::class, 'myOfficeTickets'])->name('tickets.my_office_tickets');
    Route::get('ticktes/assigned', [App\Http\Controllers\TicketController::class, 'assigned'])->name('tickets.assigned');

    // Route::post('ticktes/update/title_description', [App\Http\Controllers\TicketController::class, 'updateTicket'])->name('tickets.update_ticket');

    // Route::get('ticktes/open-tickets', [App\Http\Controllers\TicketController::class, 'openTickets'])->name('tickets.open_tickets');
    // Route::get('ticktes/closed-tickets', [App\Http\Controllers\TicketController::class, 'closedTickets'])->name('tickets.closed_tickets');
    // Route::get('ticktes/solved-tickets', [App\Http\Controllers\TicketController::class, 'solvedTickets'])->name('tickets.solved_tickets');
    // Route::get('ticktes/re-opened-tickets', [App\Http\Controllers\TicketController::class, 'reOpenedTickets'])->name('tickets.re_opened_tickets');
    Route::get('ticktes/open-tickets', [App\Http\Controllers\TicketController::class, 'openTickets'])->name('tickets.open_tickets');
    Route::get('ticktes/closed-tickets', [App\Http\Controllers\TicketController::class, 'closedTickets'])->name('tickets.closed_tickets');
    Route::get('ticktes/solved-tickets', [App\Http\Controllers\TicketController::class, 'solvedTickets'])->name('tickets.solved_tickets');
    Route::get('ticktes/re-opened-tickets', [App\Http\Controllers\TicketController::class, 'reOpenedTickets'])->name('tickets.re_opened_tickets');
    Route::get('ticktes/pick/{id}', [App\Http\Controllers\TicketController::class, 'pick'])->name('tickets.pick');
    Route::get('tickets/update/ticket/status', [App\Http\Controllers\TicketController::class, 'updateStatus'])->name('tickets.update_status');
    // Route::get('tickets/assign/ticket/{id?}', [App\Http\Controllers\TicketController::class, 'assignTicket'])->name('tickets.assign_ticket');
    Route::post('tickets/assign/ticket', [App\Http\Controllers\TicketController::class, 'assignTicket'])->name('tickets.assign_ticket_user');
    Route::get('tickets/{filter}/{filter_id}', [App\Http\Controllers\TicketController::class, 'filterTicket'])->name('tickets.filter_ticket');

    /**
     * DATATABLES
     */
    Route::get('datatables/all-tickets', [App\Http\Controllers\DataTablesController::class, 'allTickets'])->name('datatables.all_tickets');
    Route::get('datatables/my-tickets', [App\Http\Controllers\DataTablesController::class, 'myTickets'])->name('datatables.my_tickets');
    Route::get('datatables/my-office-tickets', [App\Http\Controllers\DataTablesController::class, 'myOfficeTickets'])->name('datatables.my_office_tickets');
    Route::get('datatables/assigned', [App\Http\Controllers\DataTablesController::class, 'assigned'])->name('datatables.assigned');
    // Route::get('datatables/closed-tickets', [App\Http\Controllers\DataTablesController::class, 'closedTickets'])->name('datatables.closed_tickets');
    // Route::get('datatables/open-tickets', [App\Http\Controllers\DataTablesController::class, 'openTickets'])->name('datatables.open_tickets');
    // Route::get('datatables/solved-tickets', [App\Http\Controllers\DataTablesController::class, 'solvedTickets'])->name('datatables.solved_tickets');
    // Route::get('datatables/re-opened-tickets', [App\Http\Controllers\DataTablesController::class, 'reOpenedTickets'])->name('datatables.re_opened_tickets');
    Route::get('datatables/closed-tickets', [App\Http\Controllers\DataTablesController::class, 'closedTickets'])->name('datatables.closed_tickets');
    Route::get('datatables/open-tickets', [App\Http\Controllers\DataTablesController::class, 'openTickets'])->name('datatables.open_tickets');
    Route::get('datatables/solved-tickets', [App\Http\Controllers\DataTablesController::class, 'solvedTickets'])->name('datatables.solved_tickets');
    Route::get('datatables/re-opened-tickets', [App\Http\Controllers\DataTablesController::class, 'reOpenedTickets'])->name('datatables.re_opened_tickets');
    Route::get('datatables/settled-tickets', [App\Http\Controllers\DataTablesController::class, 'settledTickets'])->name('datatables.settled_tickets');

    Route::get('datatables/users', [App\Http\Controllers\DataTablesController::class, 'users'])->name('datatables.users');

    Route::get('datatables/tims', [App\Http\Controllers\DataTablesController::class, 'tims'])->name('datatables.tims');

    Route::get('datatables/efris', [App\Http\Controllers\DataTablesController::class, 'efris'])->name('datatables.efris');

    Route::get('datatables/tra', [App\Http\Controllers\DataTablesController::class, 'tra'])->name('datatables.tra');

    Route::get('datatables/tra_fit_tz', [App\Http\Controllers\DataTablesController::class, 'traFITTZ'])->name('datatables.tra_tz');

    Route::get('datatables/rra', [App\Http\Controllers\DataTablesController::class, 'rra'])->name('datatables.rra');

    /**
     * OFFICE
     */
    Route::get('offices/get-name', [App\Http\Controllers\OfficeController::class, 'getOfficeName'])->name('office.get_name');

    /**
     * MODULES
     */
    Route::get('modules/get-data', [App\Http\Controllers\ModuleController::class, 'getModuleData'])->name('modules.get_data');

    /**
     * ROLES AND PERMISSIONS
     */
    Route::resource('roles', App\Http\Controllers\RoleController::class);

    /**
     * FILES
     */
    Route::get('attachment/{id}/download', [App\Http\Controllers\AttachmentController::class, 'download'])->name('attachment.download');
    Route::post('attachment/upload/file', [App\Http\Controllers\AttachmentController::class, 'upload'])->name('attachment.upload');
    Route::delete('attachment/delete/', [App\Http\Controllers\AttachmentController::class, 'destroy'])->name('attachment.destroy');

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
    Route::get('categories/get-category/{id?}/{type?}', [App\Http\Controllers\CategoryController::class, 'getCategory'])->name('getCategory');
    Route::post('categories/save-category', [App\Http\Controllers\CategoryController::class, 'store'])->name('saveCategory');
    Route::post('categories/update/submodule', [App\Http\Controllers\CategoryController::class, 'subModuleUpdateAjax'])->name('submodule.update_ajax');
    // * push notification
    Route::post('/push', [App\Http\Controllers\PushController::class, 'store']);
    Route::get('/push', [App\Http\Controllers\PushController::class, 'push'])->name('push');

    /**
     *! CLIENT PARTY ENTITY ROUTES
     */
    Route::get('/get-countries', [App\Http\Controllers\ClientPartyController::class, 'getCountries'])->name('clientParty.getCountries');
    Route::get('/get-cities', [App\Http\Controllers\ClientPartyController::class, 'getCities'])->name('clientParty.getCities');
    Route::get('/get-clients/{type}', [App\Http\Controllers\ClientPartyController::class, 'getClients'])->name('clientParty.getClients');
    Route::get('/autocomplete-client', [App\Http\Controllers\ClientPartyController::class, 'autocompleteClient'])->name('autocomplete-client');
    Route::get('/client-details/{companyName}', [App\Http\Controllers\ClientPartyController::class, 'fetchClientDetails'])->name('client.details');
    Route::get('/get-tarrif-list', [App\Http\Controllers\ClientPartyController::class, 'getTarrif'])->name('clientParty.getTarrif');
    Route::get('/get-debtor-vendor/{type}', [App\Http\Controllers\ClientPartyController::class, 'getDebtorVendor'])->name('clientParty.getDebtorVendor');
    Route::get('/get-provinces', [App\Http\Controllers\ClientPartyController::class, 'getProvinces'])->name('clientParty.getProvinces');
    Route::get('/get-offices', [App\Http\Controllers\ClientPartyController::class, 'getOffices'])->name('clientParty.getOffices');
    Route::get('/get-licenceType', [App\Http\Controllers\ClientPartyController::class, 'getLicenceType'])->name('clientParty.getLicenceType');
    Route::get('/get-party-entity', [App\Http\Controllers\ClientPartyController::class, 'getPartyEntity'])->name('clientParty.getPartyEntity');
    Route::get('/get-inco-terms', [App\Http\Controllers\ClientPartyController::class, 'getIncoTerms'])->name('clientParty.getIncoTerms');
    Route::get('/get-payment-term-list', [App\Http\Controllers\ClientPartyController::class, 'getPaymentTermList'])->name('clientParty.getPaymentTermList');
    Route::get('/get-currency-list', [App\Http\Controllers\ClientPartyController::class, 'getCurrencyList'])->name('clientParty.getCurrencyList');
    Route::get('/get-base-currency', [App\Http\Controllers\ClientPartyController::class, 'getBaseCurrency'])->name('clientParty.getBaseCurrency');
    Route::get('/get-office-debtor-vendor-list', [App\Http\Controllers\ClientPartyController::class, 'getDebtorVendorList'])->name('clientParty.getDebtorVendorList');

    Route::get('/get-sales-account-pic/{type}', [App\Http\Controllers\ClientPartyController::class, 'salesAccountPCI'])->name('clientParty.salesAccountPCI');

    Route::get('/get-notify-party-list/{type}', [App\Http\Controllers\ClientPartyController::class, 'getNotifyPartyList'])->name('clientParty.getNotifyPartyList');
    Route::get('/get-plo-pld/{type}', [App\Http\Controllers\ClientPartyController::class, 'getPOLPOD'])->name('clientParty.getPOLPOD');

    Route::get('client-party', [App\Http\Controllers\ClientPartyController::class, 'index'])->name('client-party.index');
    Route::get('client-party/show/{clientParty}', [App\Http\Controllers\ClientPartyController::class, 'show'])->name('client-party.show');
    Route::get('client-party/create', [App\Http\Controllers\ClientPartyController::class, 'create'])->name('client-party.create');
    Route::post('client-party', [App\Http\Controllers\ClientPartyController::class, 'store'])->name('client-party.store');
    Route::put('client-party/{id}/update', [App\Http\Controllers\ClientPartyController::class, 'update'])->name('client-party.update');
    Route::get('client-party/view/{path}', [App\Http\Controllers\ClientPartyController::class, 'preview'])->name('client-party.preview');
    Route::post('client-party/reject', [App\Http\Controllers\ClientPartyController::class, 'reject'])->name('client-party.reject');
    Route::post('client-party/approve', [App\Http\Controllers\ClientPartyController::class, 'approve'])->name('client-party.approve');
    Route::get('client-party/approve', [App\Http\Controllers\ClientPartyController::class, 'approvals'])->name('client-party.approvals');
    Route::post('/delete-client-party-attachment/{id}', [App\Http\Controllers\ClientPartyController::class, 'deleteFile'])->name('client-party.deleteFile');

    /**!client party datatables */
    Route::group(['prefix' => 'datatables'], function () {
        Route::get('client-party', [App\Http\Controllers\DataTablesController::class, 'allEntityRequests'])->name('datatables.allEntityRequests');
        Route::get('client-party-approval', [App\Http\Controllers\DataTablesController::class, 'approvalEntityRequests'])->name('datatables.approvalEntityRequests');
    });

    /**!SECTION TIMS */
    Route::group(['prefix' => 'tims', 'middleware' => ['auth', 'web']], function () {
        Route::get('/', [App\Http\Controllers\TIMSController::class, 'index'])->name('tims.index');
        Route::get('/get-stats', [App\Http\Controllers\TIMSController::class, 'getStats'])->name('tims.get_stats');
        Route::get('manual', [App\Http\Controllers\TIMSController::class, 'manual']);
        Route::get('re-trigger/{docNo}', [App\Http\Controllers\TIMSController::class, 'reTrigger']);
        Route::get('json/{id}', [App\Http\Controllers\TIMSController::class, 'jsonGenerate'])->name('tims.json');
        Route::get('invoice/download/{id}', [App\Http\Controllers\TIMSController::class, 'invoiceDownload'])->name('tims.invoiceDownload')->middleware(['signed']);
        Route::post('update-invoice', [App\Http\Controllers\TIMSController::class, 'updateInvoice'])->name('tims.updateInvoice');
    });

    /**!SECTION EFRIS */
    Route::group(['prefix' => 'efris'], function () {
        Route::get('/', [App\Http\Controllers\EfrisController::class, 'index'])->name('efris.index');
        Route::get('re-trigger/{docNo}', [App\Http\Controllers\EfrisController::class, 'reTrigger'])->name('efris.reTrigger');
        Route::post('update-invoice', [App\Http\Controllers\EfrisController::class, 'updateInvoice'])->name('efris.updateInvoice');
    });

    /**!SECTION TRA FIT*/
    Route::group(['prefix' => 'tra'], function () {
        Route::get('/', [App\Http\Controllers\TraController::class, 'index'])->name('tra.index');
        Route::get('/get-stats', [App\Http\Controllers\TraController::class, 'getStats'])->name('tra.get_stats');
        Route::get('re-trigger/{docNo}', [App\Http\Controllers\TraController::class, 'reTrigger'])->name('tra.reTrigger');
        Route::post('update-invoice', [App\Http\Controllers\TraController::class, 'updateInvoice'])->name('tra.updateInvoice');
    });

    /**!SECTION TRA ADL*/
    Route::group(['prefix' => 'traADL'], function () {
        Route::get('/', [App\Http\Controllers\TraADLController::class, 'index'])->name('traADL.index');
        Route::get('/get-stats', [App\Http\Controllers\TraADLController::class, 'getStats'])->name('traADL.get_stats');
        Route::get('re-trigger/{docNo}', [App\Http\Controllers\TraADLController::class, 'reTrigger'])->name('traADL.reTrigger');
        Route::get('json/{id}', [App\Http\Controllers\TraADLController::class, 'jsonGenerate'])->name('traADL.json');
        Route::get('invoice/download/{id}', [App\Http\Controllers\TraADLController::class, 'invoiceDownload'])->name('traADL.invoiceDownload')->middleware(['signed']);
        Route::post('update-invoice', [App\Http\Controllers\TraADLController::class, 'updateInvoice'])->name('traADL.updateInvoice');
    });

    /**!SECTION RRA */
    Route::group(['prefix' => 'rra'], function () {
        Route::get('/', [App\Http\Controllers\RRAController::class, 'index'])->name('rra.index');
        Route::get('/get-stats', [App\Http\Controllers\RRAController::class, 'getStats'])->name('rra.get_stats');
        Route::get('re-trigger/{docNo}', [App\Http\Controllers\RRAController::class, 'reTrigger'])->name('rra.reTrigger');
        Route::get('json/{id}', [App\Http\Controllers\RRAController::class, 'jsonGenerate'])->name('rra.json');
        Route::get('invoice/download/{id}', [App\Http\Controllers\RRAController::class, 'invoiceDownload'])->name('rra.invoiceDownload')->middleware(['signed']);
        Route::post('update-invoice', [App\Http\Controllers\RRAController::class, 'updateInvoice'])->name('rra.updateInvoice');
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

Route::post('/client-party/check-license', [ClientPartyController::class, 'checkLicense'])->name('client-party.check-license');
Route::get('/api/companies', [ClientPartyController::class, 'getCompanies'])->name('companies');
Route::get('/api/company-details', [ClientPartyController::class, 'getCompanyDetails'])->name('company.details');
Route::post('/client-party/fetch-address-details', [ClientPartyController::class, 'fetchAddressDetails'])->name('client-party.fetch-address-details');
Route::get('/api/clients', [ClientPartyController::class, 'getClients'])->name('clients');
Route::post('/create-client-party-scm', [ClientPartyController::class, 'CreateClientPartySCM'])->middleware('auth:api')->name('client.create-party-scm');

// ============================================================================
// AZURE MANAGEMENT SYSTEM ROUTES
// ============================================================================

Route::prefix('azure')->name('azure.')->middleware(['auth'])->group(function () {
    // Root redirect
    Route::get('/', function () {
        return redirect()->route('azure.dashboard.index');
    })->name('home');

    // ============================================================================
    // DASHBOARD ROUTES
    // ============================================================================

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/', [App\Http\Controllers\AzureDashboardController::class, 'index'])->name('index');
        Route::get('/stats', [App\Http\Controllers\AzureDashboardController::class, 'getStats'])
            ->name('stats')
            ->middleware('throttle:dashboard');
        Route::get('/reports', [App\Http\Controllers\AzureDashboardController::class, 'reports'])->name('reports');
        Route::get('/activity', [App\Http\Controllers\AzureDashboardController::class, 'getRecentActivity'])
            ->name('activity')
            ->middleware('throttle:dashboard');

        // AJAX get users
        Route::get('/get-users', [App\Http\Controllers\AzureDashboardController::class, 'getUsers'])->name('get-users');
    });

    // ============================================================================
    // USER MANAGEMENT ROUTES (with rate limiting)
    // ============================================================================

    Route::prefix('users')->name('users.')->group(function () {
        // List and create routes
        Route::get('/', [App\Http\Controllers\AzureUserManagementController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\AzureUserManagementController::class, 'create'])->name('create');

        // ✅ ADD THESE THREE ROUTES HERE (after 'create', before 'store'):
        Route::get('/active', [App\Http\Controllers\AzureUserManagementController::class, 'active'])->name('active');
        Route::get('/pending', [App\Http\Controllers\AzureUserManagementController::class, 'pending'])->name('pending');
        Route::get('/inactive', [App\Http\Controllers\AzureUserManagementController::class, 'inactive'])->name('inactive');

        // POST: Create user (rate limited - 10 per minute)
        Route::post('/', [App\Http\Controllers\AzureUserManagementController::class, 'store'])
            ->name('store')
            ->middleware('throttle:user_actions');

        Route::post('/{userId}/retry-provisioning', [
            App\Http\Controllers\AzureUserManagementController::class,
            'retryProvisioning'
        ])->name('retry-provisioning')
            ->middleware('throttle:user_actions');

        // Stats endpoint (must come before {user} parameter)
        Route::get('/stats', [App\Http\Controllers\AzureUserManagementController::class, 'stats'])->name('stats');

        // User-specific action routes (MUST come before generic {user} routes)
        // These are rate limited to prevent abuse
        Route::post('/{user}/disable', [App\Http\Controllers\AzureUserManagementController::class, 'disable'])
            ->name('disable')
            ->middleware('throttle:user_actions');

        Route::post('/{user}/enable', [App\Http\Controllers\AzureUserManagementController::class, 'enable'])
            ->name('enable')
            ->middleware('throttle:user_actions');

        Route::post('/{user}/retry-modules', [App\Http\Controllers\AzureUserManagementController::class, 'retryModuleAssignments'])
            ->name('retry-modules')
            ->middleware('throttle:user_actions');

        Route::post('/{user}/reset-password', [App\Http\Controllers\AzureUserManagementController::class, 'resetPassword'])
            ->name('reset-password')
            ->middleware('throttle:user_actions');

        Route::post('/{user}/retry-d365', [App\Http\Controllers\AzureUserManagementController::class, 'retryD365Provisioning'])
            ->name('retry-d365')
            ->middleware('throttle:user_actions');

        Route::post('/{user}/retry-scm', [App\Http\Controllers\AzureUserManagementController::class, 'retrySCMProvisioning'])
            ->name('retry-scm')
            ->middleware('throttle:user_actions');

        // BIZ/FITSYS Provisioning
        Route::post('/{user}/retry-biz', [App\Http\Controllers\AzureUserManagementController::class, 'retryBIZProvisioning'])
            ->name('retry-biz')
            ->middleware('throttle:user_actions');

        // FIT Express Provisioning
        Route::post('/{user}/retry-fitexpress', [App\Http\Controllers\AzureUserManagementController::class, 'retryFITExpressProvisioning'])
            ->name('retry-fitexpress')
            ->middleware('throttle:user_actions');

        Route::post('/{user}/sync-external', [App\Http\Controllers\AzureUserManagementController::class, 'syncToExternalSystemsManual'])
            ->name('sync-external')
            ->middleware('throttle:user_actions');

        // Office-specific assignment configuration
        Route::post('/{user}/configure-office', [App\Http\Controllers\AzureUserManagementController::class, 'configureOfficeAssignments'])
            ->name('configure-office')
            ->middleware('throttle:user_actions');

        Route::get('/{user}/offices/{office}/assignments', [App\Http\Controllers\AzureUserManagementController::class, 'getOfficeAssignments'])
            ->name('office-assignments')
            ->middleware('throttle:api');

        // Quick module assignment
        Route::post('/{user}/quick-assign-module', [App\Http\Controllers\AzureUserManagementController::class, 'quickAssignModule'])
            ->name('quick-assign-module')
            ->middleware('throttle:user_actions');

        // Remove duplicate POST route, keep only this:
        Route::put('/{user}/update-base-office', [App\Http\Controllers\AzureUserManagementController::class, 'updateBaseOffice'])
            ->name('update-base-office')
            ->middleware('throttle:user_actions');

        Route::get('/{user}/modules/{module}/assignments', [App\Http\Controllers\AzureUserManagementController::class, 'getUserModuleAssignments'])
            ->name('module-assignments')
            ->middleware('throttle:api');

        // GET: Get user's current application access status
        Route::get('/{user}/application-status', [App\Http\Controllers\AzureUserManagementController::class, 'getUserApplicationStatus'])
            ->name('application-status')
            ->middleware('throttle:api');

        // POST: Manage user access across multiple applications
        Route::post('/{user}/manage-access', [App\Http\Controllers\AzureUserManagementController::class, 'manageUserApplicationAccess'])
            ->name('manage-access')
            ->middleware('throttle:user_actions');


        // Generic CRUD routes (come after specific actions)
        Route::get('/{user}', [App\Http\Controllers\AzureUserManagementController::class, 'show'])
            ->name('show');

        Route::get('/{user}/edit', [App\Http\Controllers\AzureUserManagementController::class, 'edit'])
            ->name('edit');

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

    Route::prefix('modules')->name('modules.')->group(function () {
        Route::get('/', function () {
            return view('azure-management.modules.index');
        })->name('index');

        Route::get('/assign', function () {
            return view('azure-management.modules.assign');
        })->name('assign');

        // API endpoints for dynamic module assignment
        Route::get('/scm-functions', [App\Http\Controllers\AzureUserManagementController::class, 'getSCMFunctions'])
            ->name('scm-functions')
            ->middleware('throttle:api');

        Route::get('/roles', [App\Http\Controllers\AzureUserManagementController::class, 'getModuleRolesByCode'])
            ->name('roles')
            ->middleware('throttle:api');
    });

    // ============================================================================
    // REPORTS ROUTES (with rate limiting for exports)
    // ============================================================================

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [App\Http\Controllers\AzureReportController::class, 'index'])->name('index');
        Route::get('/users', [App\Http\Controllers\AzureReportController::class, 'users'])->name('users');
        Route::get('/activity', [App\Http\Controllers\AzureReportController::class, 'activity'])->name('activity');
        Route::get('/provisioning', [App\Http\Controllers\AzureReportController::class, 'provisioning'])->name('provisioning');

        // Export is a heavy operation - limit to 5 per minute
        Route::get('/export/{type}', [App\Http\Controllers\AzureReportController::class, 'export'])
            ->name('export')
            ->middleware('throttle:heavy_operations');
    });

    // ============================================================================
    // AZURE AUTHENTICATION ROUTES
    // ============================================================================

    Route::prefix('auth')->name('auth.')->group(function () {
        Route::get('/', [App\Http\Controllers\AzureAuthController::class, 'redirectToProvider'])->name('redirect');
        Route::get('/callback', [App\Http\Controllers\AzureAuthController::class, 'handleProviderCallback'])->name('callback');
        Route::post('/test-connection', [App\Http\Controllers\AzureAuthController::class, 'testConnection'])->name('test');
        Route::post('/disconnect', [App\Http\Controllers\AzureAuthController::class, 'disconnect'])->name('disconnect');
    });

    // ============================================================================
    // API ROUTES (AJAX/JSON endpoints with rate limiting)
    // ============================================================================

    Route::prefix('api')->name('api.')->group(function () {

        // Company API
        Route::get('/companies/{location}', [App\Http\Controllers\Api\AzureCompanyController::class, 'getByLocation'])
            ->name('companies.by-location')
            ->middleware('throttle:api');

        // User Management API (for AJAX operations)
        Route::prefix('users')->name('users.')->group(function () {
            // List and create routes
            Route::get('/', [App\Http\Controllers\AzureUserManagementController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\AzureUserManagementController::class, 'create'])->name('create');

            // POST: Create user (rate limited - 10 per minute)
            Route::post('/', [App\Http\Controllers\AzureUserManagementController::class, 'store'])
                ->name('store')
                ->middleware('throttle:user_actions');

            // Stats endpoint (must come before {user} parameter)
            Route::get('/stats', [App\Http\Controllers\AzureUserManagementController::class, 'stats'])->name('stats');

            // User-specific action routes (MUST come before generic {user} routes)
            // These are rate limited to prevent abuse
            Route::post('/{user}/disable', [App\Http\Controllers\AzureUserManagementController::class, 'disable'])
                ->name('disable')
                ->middleware('throttle:user_actions');

            Route::post('/{user}/enable', [App\Http\Controllers\AzureUserManagementController::class, 'enable'])
                ->name('enable')
                ->middleware('throttle:user_actions');

            Route::post('/{user}/retry-modules', [App\Http\Controllers\AzureUserManagementController::class, 'retryModuleAssignments'])
                ->name('retry-modules')
                ->middleware('throttle:user_actions');

            // Generic CRUD routes (come after specific actions)
            Route::get('/{user}', [App\Http\Controllers\AzureUserManagementController::class, 'show'])
                ->name('show');

            Route::get('/{user}/edit', [App\Http\Controllers\AzureUserManagementController::class, 'edit'])
                ->name('edit');

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
        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('/stats', [App\Http\Controllers\AzureDashboardController::class, 'getStats'])
                ->name('stats')
                ->middleware('throttle:dashboard');
            Route::get('/activity', [App\Http\Controllers\AzureDashboardController::class, 'getRecentActivity'])
                ->name('activity')
                ->middleware('throttle:dashboard');
        });
    });

    // ============================================================================
    // PROFILE ROUTES
    // ============================================================================

    Route::prefix('profile')->name('profile.')->group(function () {
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

Route::middleware(['auth'])->prefix('azure/sync')->name('azure.sync.')->group(function () {

    // Manual Odoo User Sync (web-based trigger)
    Route::post('/odoo-users', [App\Http\Controllers\OdooSyncController::class, 'syncUsersManual'])
        ->name('odoo-users')
        ->middleware('throttle:user_actions');

    // ============================================================================
    // ODOO SYNC AJAX ROUTE (For Dashboard Sync Button)
    // ============================================================================
    Route::post('/odoo/sync-users-ajax', [OdooSyncController::class, 'syncUsersAjax'])
        ->middleware(['auth'])
        ->name('odoo.sync.ajax');

    // Manual User Activation (web-based trigger)
    Route::post('/activate-users', [App\Http\Controllers\AzureUserManagementController::class, 'activatePendingUsersManual'])
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
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    })->name('status');
});


// ============================================================================
// SHIPMENT EMAIL REPORTS ROUTES - Must come before wildcard routes
// ============================================================================
Route::middleware(['auth', 'can:approve-all-entity-request'])->group(function () {
    Route::get('/shipment-reports', [ShipmentReportController::class, 'index'])
        ->name('shipment-reports.index');

    Route::get('/shipment-reports/data', [ShipmentReportController::class, 'getData'])
        ->name('shipment-reports.data');

    Route::get('/shipment-reprts/stats', [ShipmentReportController::class, 'getstats'])
        ->name('shipment-reports.stats');
});


// ============================================================================
// QUOTATION INTEGRATION ROUTES
// ============================================================================
Route::middleware(['auth', 'can:approve-all-entity-request'])->group(function () {

    // ============================================
    // QUOTATION — INDEX & DATA
    // ============================================
    Route::get('/quotation', [App\Http\Controllers\QuotationController::class, 'index'])
        ->name('quotation.index');

    Route::get('/quotation/data', [App\Http\Controllers\QuotationController::class, 'data'])
        ->name('quotation.data');

    Route::get('/quotation/stats', [App\Http\Controllers\QuotationController::class, 'stats'])
        ->name('quotation.stats');



    // ============================================
    // QUOTATION — STATIC LOOKUP ROUTES
    // NOTE: All static /quotation/* routes MUST be declared before the
    // wildcard routes (/quotation/{id}) at the bottom of this group.
    // Laravel matches routes top-to-bottom; the wildcard will swallow
    // anything that comes after it.
    // ============================================
    Route::get('/quotation/charges', [App\Http\Controllers\QuotationController::class, 'getCharges'])
        ->name('quotation.charges');

    Route::get('/quotation/reasons', [App\Http\Controllers\QuotationController::class, 'getReasons'])
        ->name('quotation.reasons');

    Route::get('/quotation/charge-codes', [App\Http\Controllers\QuotationController::class, 'getChargeCodes'])
        ->name('quotation.charge-codes');

    Route::get('/quotation/legal-entities', [App\Http\Controllers\QuotationController::class, 'getLegalEntities'])
        ->name('quotation.legal-entities');

    Route::get('/quotation/based-on-list', [App\Http\Controllers\QuotationController::class, 'getBasedOnList'])
        ->name('quotation.based-on-list');

    Route::get('/quotation/based-on-uom', [App\Http\Controllers\QuotationController::class, 'getBasedOnUOM'])
        ->name('quotation.based-on-uom');

    Route::get('/quotation/contract-charges', [App\Http\Controllers\QuotationController::class, 'getContractCharges'])
        ->name('quotation.contract.charges');

    Route::get('/quotation/equipment-types', [App\Http\Controllers\QuotationController::class, 'getEquipmentTypes'])
        ->name('quotation.equipment-types');

    Route::get('/quotation/charge-based-on-defaults', [App\Http\Controllers\QuotationController::class, 'getChargeBasedOnDefaults'])
        ->name('quotation.charge-based-on-defaults');

    Route::get('/quotation/agent-types', [App\Http\Controllers\QuotationController::class, 'getAgentTypes'])
        ->name('quotation.agent-types');

    Route::get('/quotation/customer-types', [App\Http\Controllers\QuotationController::class, 'getCustomerTypes'])
        ->name('quotation.customer-types');

    Route::get('/quotation/email-history', [App\Http\Controllers\QuotationController::class, 'getEmailHistory'])
        ->name('quotation.email.history');

    Route::get('/quotation/shipment-modes',  [App\Http\Controllers\QuotationController::class, 'getShipmentModes'])
        ->name('quotation.shipment-modes');

    Route::get('/quotation/transport-modes', [App\Http\Controllers\QuotationController::class, 'getTransportModes'])
        ->name('quotation.transport-modes');


    // ============================================
    // QUOTATION — OFFICE / CURRENCY
    // ============================================
    Route::get('/quotation/office-code', [App\Http\Controllers\QuotationController::class, 'getOfficeCode'])
        ->name('quotation.office-code');

    Route::get('/quotation/currencies', [App\Http\Controllers\QuotationController::class, 'getCurrencies'])
        ->name('quotation.currencies');

    Route::get('/quotation/exchange-rate', [App\Http\Controllers\QuotationController::class, 'getExchangeRate'])
        ->name('quotation.exchange-rate');

    // ============================================
    // QUOTATION — COMMUNICATION (T&C + EMAIL)
    // ============================================
    Route::get('/quotation/communication/templates', [App\Http\Controllers\QuotationController::class, 'getCommunicationTemplates'])
        ->name('quotation.communication.templates');

    Route::get('/quotation/communication/description', [App\Http\Controllers\QuotationController::class, 'getCommunicationDescription'])
        ->name('quotation.communication.description');

    Route::post('/quotation/communication/save-terms', [App\Http\Controllers\QuotationController::class, 'saveTermsCondition'])
        ->name('quotation.communication.save-terms');

    Route::post('/quotation/email/send', [App\Http\Controllers\QuotationController::class, 'sendEmailQuote'])
        ->name('quotation.email.send');

    // ============================================
    // QUOTATION — CREATE BOOKING LOOKUPS
    // ============================================
    Route::get('/quotation/create-booking/service-templates', [App\Http\Controllers\QuotationController::class, 'getServiceTemplates'])
        ->name('quotation.booking.service-templates');

    Route::get('/quotation/create-booking/bill-to-parties', [App\Http\Controllers\QuotationController::class, 'getBillToParties'])
        ->name('quotation.booking.bill-to-parties');

    Route::get('/quotation/create-booking/forwarders', [App\Http\Controllers\QuotationController::class, 'getForwarders'])
        ->name('quotation.booking.forwarders');

    Route::get('/quotation/create-booking/locations', [App\Http\Controllers\QuotationController::class, 'getLocations'])
        ->name('quotation.booking.locations');

    // ============================================
    // QUOTATION — WRITE ACTIONS (POST)
    // ============================================
    Route::post('/quotation/process-booking', [App\Http\Controllers\QuotationController::class, 'processBooking'])
        ->name('quotation.process-booking');

    Route::post('/quotation/booking/create', [App\Http\Controllers\QuotationController::class, 'createBooking'])
        ->name('quotation.booking.create');

    Route::post('/quotation/charge/save', [App\Http\Controllers\QuotationController::class, 'saveCharge'])
        ->name('quotation.charge.save');

    Route::post('/quotation/charge/delete', [App\Http\Controllers\QuotationController::class, 'deleteCharge'])
        ->name('quotation.charge.delete');

    Route::post('/quotation/status/update', [App\Http\Controllers\QuotationController::class, 'updateStatus'])
        ->name('quotation.status.update');

    Route::post('/quotation/retrigger', [App\Http\Controllers\QuotationController::class, 'retrigger'])
        ->name('quotation.retrigger');

    Route::post('/quotation/customer/save', [App\Http\Controllers\QuotationController::class, 'saveCustomerDetails'])
        ->name('quotation.customer.save');

    Route::post('/quotation/shipment/save', [App\Http\Controllers\QuotationController::class, 'saveShipmentDetails'])
        ->name('quotation.shipment.save');

    Route::get('/quotation/containers', [App\Http\Controllers\QuotationController::class, 'getContainers'])
        ->name('quotation.containers');

    Route::post('/quotation/cargo/save', [App\Http\Controllers\QuotationController::class, 'saveCargoDetails'])
        ->name('quotation.cargo.save');

    Route::post('/quotation/booking-integration-response', [App\Http\Controllers\QuotationController::class, 'handleBookingIntegrationResponse'])
        ->name('quotation.bookingIntegrationResponse');


    // Duplicate
    Route::post('/quotation/{bookingno}/duplicate', [App\Http\Controllers\QuotationController::class, 'processDuplicate'])->name('quotation.processDuplicate');

    // Lookup endpoints for duplicate modal
    Route::get('/quotation/lookup/countries', [App\Http\Controllers\QuotationController::class, 'lookupCountries'])
        ->name('quotation.lookup.countries');

    Route::get('/quotation/lookup/incoterms',  [App\Http\Controllers\QuotationController::class, 'lookupIncoTerms'])
        ->name('quotation.lookup.incoterms');

    Route::get('/quotation/lookup/service-classes', [App\Http\Controllers\QuotationController::class, 'lookupServiceClasses'])
        ->name('quotation.lookup.serviceClasses');

    Route::get('/quotation/lookup/industry-types',  [App\Http\Controllers\QuotationController::class, 'lookupIndustryTypes'])
        ->name('quotation.lookup.industryTypes');

    Route::get('/quotation/lookup/persons', [App\Http\Controllers\QuotationController::class, 'lookupPersonsInCharge'])
        ->name('quotation.lookup.persons');

    Route::get('/quotation/lookup/shipment-modes', [App\Http\Controllers\QuotationController::class, 'lookupShipmentModes'])
        ->name('quotation.lookup.shipmentModes');

    Route::get('/quotation/lookup/transport-modes', [App\Http\Controllers\QuotationController::class, 'lookupTransportModes'])
        ->name('quotation.lookup.transportModes');

    Route::get('/quotation/lookup/equipment-types', [App\Http\Controllers\QuotationController::class, 'lookupEquipmentTypes'])
        ->name('quotation.lookup.equipmentTypes');

    // ============================================
    // QUOTATION — WILDCARD ROUTES (must be last)
    // These use {id} and will match ANY segment — keep them after all
    // static /quotation/* routes to prevent swallowing legitimate URLs.
    // ============================================
    Route::post('/quotation/{id}/generate-pdf', [App\Http\Controllers\QuotationController::class, 'generatePdf'])
        ->name('quotation.generate-pdf');

    Route::get('/quotation/{id}/versions', [App\Http\Controllers\QuotationController::class, 'getQuotationVersions'])
        ->name('quotation.versions');

    Route::get('/quotation/{id}/download-pdf/{version}', [App\Http\Controllers\QuotationController::class, 'downloadPdf'])
        ->name('quotation.download-pdf');

    Route::post('/quotation/{bookingno}/copy', [App\Http\Controllers\QuotationController::class, 'copy'])
        ->name('quotation.copy');

    Route::get('/quotation/inco-terms', [App\Http\Controllers\QuotationController::class, 'getIncoTerms'])
        ->name('quotation.inco-terms');

    Route::post('/quotation/inquiry/save', [App\Http\Controllers\QuotationController::class, 'saveInquiryDetails'])
        ->name('quotation.inquiry.save');

    Route::get('/quotation/inco-terms', [App\Http\Controllers\QuotationController::class, 'getIncoTerms'])
        ->name('quotation.inco-terms');

    Route::get('/quotation/{id}/edit', [App\Http\Controllers\QuotationController::class, 'edit'])
        ->name('quotation.edit');

    Route::post('/quotation/{id}/update', [App\Http\Controllers\QuotationController::class, 'update'])
        ->name('quotation.update');

    // NOTE: /quotation/{id} must be the very last GET wildcard — it matches everything.
    Route::get('/quotation/{id}', [App\Http\Controllers\QuotationController::class, 'view'])
        ->name('quotation.view');

    Route::get('/quotation/sales-persons', [App\Http\Controllers\QuotationController::class, 'getSalesPersons'])
        ->name('quotation.sales-persons');

    // ============================================
    // CRM SYNC
    // ============================================
    Route::get('/crm/sync/history', [App\Http\Controllers\CrmSyncController::class, 'history'])
        ->name('crm.sync.history');

    Route::post('/crm/sync/run', [App\Http\Controllers\CrmSyncController::class, 'run'])
        ->name('crm.sync.run');
});

// ============================================================================
// RA INTEGRATION ROUTES (Revenue Authority Integration)
// ============================================================================
Route::middleware(['auth', 'can:approve-all-entity-request'])->group(function () {
    // Dashboard
    Route::get('/ra-integration-dashboard', [App\Http\Controllers\RAIntegrationController::class, 'dashboard'])
        ->name('ra-integration.dashboard');

    // Dashboard data endpoint (for card refresh)
    Route::get('/ra-integration-dashboard/data', [App\Http\Controllers\RAIntegrationController::class, 'getDashboardData'])
        ->name('ra-integration.dashboard.data');

    // Invoice table data endpoint (for DataTable)
    Route::get('/ra-integration/invoices/data', [App\Http\Controllers\RAIntegrationController::class, 'getInvoicesData'])
        ->name('ra-integration.invoices.data');
});


Route::get('/sync-odoo-users', [App\Http\Controllers\OdooSyncController::class, 'syncUsers']);
Route::get('/test-odoo-columns', [App\Http\Controllers\OdooSyncController::class, 'testColumns']);
Route::get('/test-pgsql-connection', [App\Http\Controllers\OdooSyncController::class, 'testConnection']);
Route::get('/check-view-definition', [App\Http\Controllers\OdooSyncController::class, 'checkViewDefinition']);

// ============================================================================
// LOGIN TRACKER SYSTEM ROUTES (User Activity Monitoring)
// ============================================================================

// Restrict the {user} parameter (supports email or GUID)
Route::pattern('user', '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}|\w{8}-\w{4}-\w{4}-\w{4}-\w{12}');

// ----------------------
// Reporting routes (Login Tracker)
// ----------------------
Route::prefix('reporting')->middleware('auth')->group(function () {
    // Explicit show route (needed for dashboard links)
    Route::get('/users/{user}', [\App\Http\Controllers\UserController::class, 'show'])
        ->name('reporting.users.show');

    Route::get('/users/active-this-month', [\App\Http\Controllers\UserController::class, 'activeThisMonth'])
        ->name('reporting.users.active-this-month');

    Route::get('/users/active-last-month', [\App\Http\Controllers\UserController::class, 'activeLastMonth'])
        ->name('reporting.users.active-last-month');

    Route::get('/users/inactive', [\App\Http\Controllers\UserController::class, 'inactiveUsers'])
        ->name('reporting.users.inactive');

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
})->middleware('auth')->name('imports.index');

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

// ============================================================================
// VEHICLE TRACKER SYSTEM ROUTES (GPS/Trip Tracking)
// ============================================================================
// Webhook route (public - no auth)
Route::post('/webhooks/jimi/notifications', [JimiWebhookController::class, 'receiveNotification']);

// Tracker routes (authenticated)
Route::middleware(['auth'])->prefix('tracker')->name('tracker.')->group(function () {

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
    Route::get('/notifications/vehicle', [TrackerController::class, 'getVehicleNotifications'])
        ->name('notifications.vehicle');

    // List pending geofence notifications filtered by trip + vehicle
    // (you can use the same method and filter in PHP, or create a separate one later)
    Route::get('/notifications/trip', [TrackerController::class, 'getVehicleNotifications'])
        ->name('notifications.trip');

    // Simple count – used for badge on trip modal / list
    Route::get('/notifications/count', [TrackerController::class, 'getNotificationCount'])
        ->name('notifications.count');

    // Notifications
    Route::get('/notifications/get', [TrackerController::class, 'getNotifications'])->name('notifications.get');
    Route::get('/notifications/alarm-types', [TrackerController::class, 'getAlarmTypes'])->name('notifications.alarm-types');
    Route::get('/notifications/stats', [TrackerController::class, 'getNotificationStats'])->name('notifications.stats');
    Route::post('/notifications/{id}/read', [TrackerController::class, 'markNotificationAsRead'])->name('notifications.read');
    Route::delete('/notifications/{id}', [TrackerController::class, 'deleteNotification'])->name('notifications.delete');
    Route::post('/notifications/map-to-trip', [TrackerController::class, 'mapNotificationsToTrip'])
        ->name('notifications.map-to-trip');
});


Route::middleware(['auth'])->prefix('jimi-test')->group(function () {

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

/*
|--------------------------------------------------------------------------
| Email Account API Routes
|--------------------------------------------------------------------------
|
| These routes handle all email account management operations.
| All routes return JSON responses.
|
*/

Route::prefix('email-accounts')->group(function () {

    // Create new email account
    // POST /api/email-accounts
    Route::post('/', [EmailAccountController::class, 'store']);

    // List all email accounts (with optional domain filter)
    // GET /api/email-accounts?domain=example.com
    Route::get('/', [EmailAccountController::class, 'index']);

    // Get specific email account details
    // GET /api/email-accounts/{email}
    Route::get('/{email}', [EmailAccountController::class, 'show']);

    // Update email password
    // PATCH /api/email-accounts/password
    Route::patch('/password', [EmailAccountController::class, 'updatePassword']);

    // Delete email account
    // DELETE /api/email-accounts
    Route::delete('/', [EmailAccountController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Alternative RESTful Route (if preferred)
|--------------------------------------------------------------------------
|
| You can also use Laravel's resource routing:
| Route::apiResource('email-accounts', EmailAccountController::class);
|
| But the custom routes above give you more control over the endpoints.
|
*/

Route::get('/test-simple', function () {
    return 'Hello! Routing works!';
});


Route::prefix('admin/sp-test')->middleware(['auth'])->group(function () {
    Route::get('/', [StoredProcedureTestController::class, 'index'])->name('sp-test.index');
    Route::get('/user/{user}', [StoredProcedureTestController::class, 'testUser'])->name('sp-test.user');
    Route::post('/d365/{user}', [StoredProcedureTestController::class, 'testD365'])->name('sp-test.d365');
    Route::post('/scm/{user}', [StoredProcedureTestController::class, 'testSCM'])->name('sp-test.scm');
    Route::post('/biz/{user}', [StoredProcedureTestController::class, 'testBIZ'])->name('sp-test.biz');
    Route::get('/check-sp-exists/{spName}', [StoredProcedureTestController::class, 'checkSPExists'])->name('sp-test.check-exists');
});



// Stored Procedure Testing Routes (Admin only)
Route::prefix('admin/sp-test')

    ->middleware(['auth']) // Add more middleware if needed
    ->name('sp-test.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\StoredProcedureTestController::class, 'index'])
            ->name('index');

        Route::get('/user/{user}', [\App\Http\Controllers\StoredProcedureTestController::class, 'testUser'])
            ->name('user');

        Route::post('/d365/{user}', [\App\Http\Controllers\StoredProcedureTestController::class, 'testD365'])
            ->name('d365');

        Route::post('/scm/{user}', [\App\Http\Controllers\StoredProcedureTestController::class, 'testSCM'])
            ->name('scm');

        Route::post('/biz/{user}', [\App\Http\Controllers\StoredProcedureTestController::class, 'testBIZ'])
            ->name('biz');

        Route::get('/check-sp-exists/{spName}', [\App\Http\Controllers\StoredProcedureTestController::class, 'checkSPExists'])
            ->name('check-exists');
    });


Route::post('/webhooks/jimi/notifications', [App\Http\Controllers\JimiWebhookController::class, 'receiveNotification']);

Route::get('zreport',        [App\Http\Controllers\ZReportRunnerController::class, 'index'])->name('zreport.index');
Route::post('zreport/run',   [App\Http\Controllers\ZReportRunnerController::class, 'run'])->name('zreport.run');



// TEMPORARY — remove before deploying to production
Route::get('/quotation/{id}/pdf-preview', function ($id) {
    $quotation = \App\Models\QuotationInfo::where('ID', $id)->firstOrFail();

    $currencyCode = $quotation->currency ?? null;
    if (!empty($quotation->currency)) {
        $currency = \Illuminate\Support\Facades\DB::table('[FITSCMUAT].dbo.Cust_Currency')
            ->where('Id', $quotation->currency)
            ->first(['Code']);
        if ($currency) $currencyCode = $currency->Code;
    }

    $charges = \Illuminate\Support\Facades\DB::select("
        SELECT
            QC.Id AS QuoteChargeID, BC.Code AS ChargeCode, BC.Name AS ChargeName,
            QC.Quantity, QC.EstChargeRate AS Rate, QC.EstChargeAmount AS AmtInChgCurr,
            QC.EstBaseAmount AS AmtInBaseCurr, QC.BaseExchRate AS ExChangeRate,
            CC.Code AS ChgCurr, CB.Code AS BasedOnCode, CB.BasedOn AS BasedOn,
            SU.Code AS BasedOnUOM, QC.Remarks AS ChargeRemark, QC.IsRevenue, CP.Code AS PartyCode
        FROM Quote_Quotation_ChargeSheet QC WITH (NOLOCK)
        INNER JOIN Quote_QuotationInfo QI WITH (NOLOCK) ON QI.id = QC.QuotationInfoID
        LEFT JOIN [FITSCMUAT].dbo.Bill_ChargeCode BC WITH (NOLOCK) ON BC.Id = QC.ChargeCodeId
        LEFT JOIN [FITSCMUAT].dbo.Cust_Currency CC WITH (NOLOCK) ON CC.Id = QC.EstChargeCurrencyId
        LEFT JOIN Cust_ChargecodeBasedOn CB WITH (NOLOCK) ON CB.Id = QC.BasedOnId
        LEFT JOIN [FITSCMUAT].dbo.Sys_UOM SU WITH (NOLOCK) ON SU.Id = QC.UOMId
        LEFT JOIN [FITSCMUAT].dbo.Cust_Party CP WITH (NOLOCK) ON CP.Id = QC.PartyID
        WHERE QI.bookingno = ? AND QC.IsDeleted = 0
        ORDER BY QC.IsRevenue DESC, BC.Name ASC
    ", [$quotation->bookingno]);

    $revenueCharges = array_values(array_filter($charges, fn($c) =>  $c->IsRevenue));
    $costCharges    = array_values(array_filter($charges, fn($c) => !$c->IsRevenue));
    $totalRevenue   = array_sum(array_map(fn($c) => (float)$c->AmtInBaseCurr, $revenueCharges));
    $totalCost      = array_sum(array_map(fn($c) => (float)$c->AmtInBaseCurr, $costCharges));

    return view('quotation.pdf', [
        'quotation'      => $quotation,
        'currencyCode'   => $currencyCode,
        'revenueCharges' => $revenueCharges,
        'costCharges'    => $costCharges,
        'totalRevenue'   => $totalRevenue,
        'totalCost'      => $totalCost,
        'generatedDate'  => now()->format('d-m-Y'),
    ]);
})->middleware('auth')->name('quotation.pdf-preview');
