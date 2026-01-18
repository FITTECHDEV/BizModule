<?php

use App\Http\Controllers\ClientPartyController;
use App\Http\Controllers\EmailAccountController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\JimiTestController;
use App\Http\Controllers\JimiWebhookController;
use App\Http\Controllers\TrackerController;
use App\Http\Controllers\AzureUserManagementController;
use App\Http\Controllers\OdooSyncController;

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

    Route::post('users/ajax-update', [App\Http\Controllers\UsersController::class, 'updateAjax'])
        ->name('users.update_ajax');

    // ✅ RESOURCE ROUTES - Come AFTER specific routes
    Route::resource('users', App\Http\Controllers\UsersController::class);

    // ✅ POST ROUTES WITH {user} PARAMETER - Can come after resource routes
    Route::post('users/profile/{user}', [App\Http\Controllers\UsersController::class, 'update'])
        ->name('user.update');

    Route::post('users/pass/{user}', [App\Http\Controllers\UsersController::class, 'pass'])
        ->name('user.update.password');



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
    });

    // ============================================================================
    // USER MANAGEMENT ROUTES (with rate limiting)
    // ============================================================================

    Route::prefix('users')->name('users.')->group(function () {
        // ============================================================================
        // STATIC ROUTES - Must come first to prevent matching as {user} parameter
        // ============================================================================

        // List and create routes
        Route::get('/', [App\Http\Controllers\AzureUserManagementController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\AzureUserManagementController::class, 'create'])->name('create');

        // Status filter routes - MUST come before /{user} routes
        Route::get('/active', [App\Http\Controllers\AzureUserManagementController::class, 'active'])->name('active');
        Route::get('/pending', [App\Http\Controllers\AzureUserManagementController::class, 'pending'])->name('pending');
        Route::get('/inactive', [App\Http\Controllers\AzureUserManagementController::class, 'inactive'])->name('inactive');

        // Stats endpoint (must come before {user} parameter)
        Route::get('/stats', [App\Http\Controllers\AzureUserManagementController::class, 'stats'])->name('stats');

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

        Route::post('/{user}/sync-azure-id', [AzureUserManagementController::class, 'syncAzureId'])
            ->name('sync-azure-id');

        // BIZ/FITSYS Provisioning
        Route::post('/{user}/retry-biz', [App\Http\Controllers\AzureUserManagementController::class, 'retryBIZProvisioning'])
            ->name('retry-biz')
            ->middleware('throttle:user_actions');

        // FIT Express Provisioning
        Route::post('/{user}/retry-fitexpress', [App\Http\Controllers\AzureUserManagementController::class, 'retryFITExpressProvisioning'])
            ->name('retry-fitexpress')
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

        // ============================================================================
        // GENERIC CRUD ROUTES - Must come after all specific {user} routes
        // ============================================================================

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
    Route::post('/odoo-users', [OdooSyncController::class, 'syncUsersManual'])
        ->name('odoo-users')
        ->middleware('throttle:user_actions');

    // Odoo Sync Routes
    Route::post('/odoo/sync-users-ajax', [OdooSyncController::class, 'syncUsersAjax'])
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
            echo "<pre style='background: #fff3cd; padding: 10px; border-radius: 4px;'>" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
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
                            [$appCode]
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
            echo "<pre style='background: #fff3cd; padding: 10px; border-radius: 4px;'>" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
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
                    $scmBadge = $isSCM ? "<span style='background: #27ae60; color: white; padding: 2px 8px; border-radius: 3px; font-size: 0.85em;'>SCM</span>" : '';

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
        $mappedTo = $systemMap[$appLower] ?? $systemMap[$app] ?? 'UNMAPPED';

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

    $amayData = $data->filter(function ($record) {
        return stripos($record->username, 'amay') !== false;
    })->values();

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

    // Notifications
    Route::get('/notifications/get', [TrackerController::class, 'getNotifications'])->name('notifications.get');
    Route::get('/notifications/alarm-types', [TrackerController::class, 'getAlarmTypes'])->name('notifications.alarm-types');
    Route::get('/notifications/stats', [TrackerController::class, 'getNotificationStats'])->name('notifications.stats');
    Route::post('/notifications/{id}/read', [TrackerController::class, 'markNotificationAsRead'])->name('notifications.read');
    Route::delete('/notifications/{id}', [TrackerController::class, 'deleteNotification'])->name('notifications.delete');
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

Route::get('/azure-management/test', function () {
    $results = [
        'config' => [],
        'token' => [],
        'create_user' => [],
        'find_user' => [],
    ];

    // ============================================
    // TEST 1: Configuration Check
    // ============================================
    try {
        $results['config'] = [
            'status' => 'success',
            'tenant_id' => config('azure.tenant_id') ? 'Set ✅' : 'Missing ❌',
            'client_id' => config('azure.client_id') ? 'Set ✅' : 'Missing ❌',
            'client_secret' => config('azure.client_secret') ? 'Set ✅' : 'Missing ❌',
            'domain' => config('azure.default_domain'),
            'graph_url' => config('azure.graph_api_base_url'),
        ];
    } catch (\Exception $e) {
        $results['config'] = [
            'status' => 'failed',
            'error' => $e->getMessage(),
        ];
    }

    // ============================================
    // TEST 2: Get Access Token
    // ============================================
    try {
        $service = app(AzureGraphService::class);
        $token = $service->getAccessToken();

        $results['token'] = [
            'status' => 'success',
            'token_preview' => substr($token, 0, 50) . '...',
            'token_length' => strlen($token),
            'message' => '✅ Successfully obtained Azure AD token!',
        ];
    } catch (\Exception $e) {
        $results['token'] = [
            'status' => 'failed',
            'error' => $e->getMessage(),
            'message' => '❌ Failed to get token',
        ];

        // Stop here if token fails
        return view('azure-management.test', compact('results'));
    }

    // ============================================
    // TEST 3: Check if Test User Exists
    // ============================================
    if ($results['token']['status'] === 'success') {
        try {
            $testUpn = 'azuretest@freight-in-time.com';
            $existingUser = $service->findUserByUPN($testUpn);

            if ($existingUser) {
                $results['find_user'] = [
                    'status' => 'success',
                    'found' => true,
                    'azure_id' => $existingUser['id'],
                    'upn' => $existingUser['userPrincipalName'],
                    'display_name' => $existingUser['displayName'],
                    'message' => '✅ Test user already exists (will not create duplicate)',
                ];
            } else {
                $results['find_user'] = [
                    'status' => 'success',
                    'found' => false,
                    'message' => 'Test user not found (ready to create)',
                ];
            }
        } catch (\Exception $e) {
            $results['find_user'] = [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    // ============================================
    // TEST 4: Create Test User (Only if not exists)
    // ============================================
    if (
        $results['token']['status'] === 'success' &&
        isset($results['find_user']['found']) &&
        ! $results['find_user']['found']
    ) {
        try {
            $randomId = rand(1000, 9999);

            $userData = [
                'name' => "Azure Test User {$randomId}",
                'email' => "azuretest{$randomId}@freight-in-time.com",
                'employee_id' => "TEST{$randomId}",
                'phone' => '+254712345678',
                'job_title' => 'Test Engineer',
                'department' => 'IT Testing',
            ];

            $result = $service->createUser($userData);

            $results['create_user'] = [
                'status' => 'success',
                'azure_id' => $result['azure_id'],
                'azure_upn' => $result['azure_upn'],
                'display_name' => $result['azure_display_name'],
                'password_generated' => ! empty($result['temporary_password']),
                'message' => '✅ Test user created successfully in Azure AD!',
                'note' => 'This is a real user - you may want to delete it from Azure AD portal',
            ];
        } catch (\Exception $e) {
            $results['create_user'] = [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'message' => '❌ Failed to create test user',
            ];
        }
    }

    return view('azure-management.test', compact('results'));
})->name('azure.test');

// Route::post('/azure/users/{user}/sync', [AzureUserManagementController::class, 'sync'])
//     ->name('azure.users.sync');

// Route::get('/azure/users/{user}/sync-status', [AzureUserManagementController::class, 'syncStatus'])
//     ->name('azure.users.sync-status');

// Route::post('/azure/users', [AzureUserManagementController::class, 'store'])
//        ->middleware(['auth', 'permission:create-azure-users']);

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


Route::get('/test-sp-debug', function() {
    $tests = [];
    
    // Test different applications
    $apps = ['OPS', 'D365 LIVE', 'BIZ PRO', 'FIT Express'];
    
    foreach ($apps as $app) {
        try {
            // Test roles SP
            $roles = DB::connection('sqlsrv')
                ->select('EXEC sp_Azure_UserResourceRole @ApplicationCode = ?', [$app]);
            
            $tests[$app] = [
                'roles_count' => count($roles),
                'roles_sample' => array_slice($roles, 0, 2),
                'error' => null
            ];
        } catch (\Exception $e) {
            $tests[$app] = [
                'roles_count' => 0,
                'roles_sample' => [],
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Test functions SP
    try {
        $functions = DB::connection('sqlsrv')
            ->select('EXEC sp_Azure_UserResourcefunction');
        
        $tests['Functions'] = [
            'count' => count($functions),
            'sample' => array_slice($functions, 0, 2),
            'error' => null
        ];
    } catch (\Exception $e) {
        $tests['Functions'] = [
            'count' => 0,
            'sample' => [],
            'error' => $e->getMessage()
        ];
    }
    
    return response()->json($tests);
});

// Add this test route to your routes file (web.php or api.php)

Route::get('/test-sp-debug', function() {
    $tests = [];
    
    // Test different applications
    $apps = ['OPS', 'D365 LIVE', 'BIZ PRO', 'FIT Express'];
    
    foreach ($apps as $app) {
        try {
            // Test roles SP
            $roles = DB::connection('sqlsrv')
                ->select('EXEC sp_Azure_UserResourceRole @ApplicationCode = ?', [$app]);
            
            $tests[$app] = [
                'roles_count' => count($roles),
                'roles_sample' => array_slice($roles, 0, 2),
                'error' => null
            ];
        } catch (\Exception $e) {
            $tests[$app] = [
                'roles_count' => 0,
                'roles_sample' => [],
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Test functions SP
    try {
        $functions = DB::connection('sqlsrv')
            ->select('EXEC sp_Azure_UserResourcefunction');
        
        $tests['Functions'] = [
            'count' => count($functions),
            'sample' => array_slice($functions, 0, 2),
            'error' => null
        ];
    } catch (\Exception $e) {
        $tests['Functions'] = [
            'count' => 0,
            'sample' => [],
            'error' => $e->getMessage()
        ];
    }
    
    return response()->json($tests);
});

// sp_UserCreation_ResourceRole



Route::get('/azure/check-user', function (Illuminate\Http\Request $request) {
    try {
        // Get search parameter
        $azureId = $request->query('azure_id');
        $email = $request->query('email');
        $employeeId = $request->query('employee_id');

        if (!$azureId && !$email && !$employeeId) {
            return response()->json([
                'error' => 'Please provide at least one parameter: azure_id, email, or employee_id',
                'examples' => [
                    'By Azure ID' => url('/azure/check-user?azure_id=824F470D-21D3-4BD5-9AA3-044861F50BA9'),
                    'By Email' => url('/azure/check-user?email=c.c@gmail.com'),
                    'By Employee ID' => url('/azure/check-user?employee_id=EMP001'),
                ]
            ], 400);
        }

        // Build the query
        $query = DB::connection('sqlsrv')
            ->table('Azure_Users')
            ->select([
                'Id',
                'AzureId',
                'userPrincipalName',
                'displayName',
                'mail',
                'EmployeCode',
                'Status',
                'accountEnabled',
                'jobTitle',
                'department',
                'companyName',
                'officeLocation',
                'mobilePhone',
                'telephoneNumber',
                'createdDateTime',
                // External system IDs
                'OdooUserId',
                'OdooEmployeeId',
                'SCMUserID',
                'SCMResourceID',
                'BizUserID',
                'D365UserID',
            ]);

        // Apply filters based on provided parameters
        if ($azureId) {
            $query->where('AzureId', $azureId);
        }

        if ($email) {
            $query->where(function($q) use ($email) {
                $q->where('userPrincipalName', $email)
                  ->orWhere('mail', $email);
            });
        }

        if ($employeeId) {
            $query->where('EmployeCode', $employeeId);
        }

        $user = $query->first();

        if (!$user) {
            Log::info('User not found in Azure_Users table', [
                'search_params' => [
                    'azure_id' => $azureId,
                    'email' => $email,
                    'employee_id' => $employeeId,
                ]
            ]);

            return response()->json([
                'exists' => false,
                'message' => 'User not found in Azure_Users table',
                'search_params' => array_filter([
                    'azure_id' => $azureId,
                    'email' => $email,
                    'employee_id' => $employeeId,
                ])
            ], 404);
        }

        // Get module assignments
        $moduleAssignments = DB::connection('sqlsrv')
            ->table('Azure_Application_UserMapping')
            ->where('user_id', $user->userPrincipalName)
            ->where('IsActive', 1)
            ->where('IsDelete', 0)
            ->select([
                'ApplicationName',
                'RoleCode',
                'OfficeCode',
                'CreatedOn',
            ])
            ->get();

        // Get function assignments (for SCM modules)
        $functionAssignments = DB::connection('sqlsrv')
            ->table('Azure_Application_UserFunction')
            ->where('user_id', $user->userPrincipalName)
            ->where('IsActive', 1)
            ->where('IsDelete', 0)
            ->select([
                'ApplicationName',
                'FunctionCode',
                'OfficeCode',
                'CreatedOn',
            ])
            ->get();

        // Group modules
        $modules = $moduleAssignments->groupBy('ApplicationName')->map(function($assignments, $moduleName) {
            return [
                'name' => $moduleName,
                'roles' => $assignments->pluck('RoleCode')->unique()->values(),
                'locations' => $assignments->pluck('OfficeCode')->unique()->values(),
                'count' => $assignments->count(),
            ];
        });

        $functions = $functionAssignments->groupBy('ApplicationName')->map(function($assignments, $moduleName) {
            return [
                'name' => $moduleName,
                'functions' => $assignments->pluck('FunctionCode')->unique()->values(),
                'locations' => $assignments->pluck('OfficeCode')->unique()->values(),
                'count' => $assignments->count(),
            ];
        });

        Log::info('User found in Azure_Users table', [
            'user_id' => $user->Id,
            'azure_id' => $user->AzureId,
            'email' => $user->userPrincipalName,
            'status' => $user->Status,
            'modules_count' => $modules->count(),
        ]);

        return response()->json([
            'exists' => true,
            'message' => 'User found in database',
            'user' => [
                'id' => $user->Id,
                'azure_id' => $user->AzureId,
                'email' => $user->userPrincipalName,
                'display_name' => $user->displayName,
                'employee_code' => $user->EmployeCode,
                'status' => $user->Status,
                'account_enabled' => (bool)$user->accountEnabled,
                'job_title' => $user->jobTitle,
                'department' => $user->department,
                'company' => $user->companyName,
                'office_location' => $user->officeLocation,
                'mobile_phone' => $user->mobilePhone,
                'business_phone' => $user->telephoneNumber,
                'created_date' => $user->createdDateTime,
            ],
            'external_systems' => [
                'odoo' => [
                    'provisioned' => !empty($user->OdooUserId),
                    'user_id' => $user->OdooUserId,
                    'employee_id' => $user->OdooEmployeeId,
                ],
                'scm' => [
                    'provisioned' => !empty($user->SCMUserID),
                    'user_id' => $user->SCMUserID,
                    'resource_id' => $user->SCMResourceID,
                ],
                'biz' => [
                    'provisioned' => !empty($user->BizUserID),
                    'user_id' => $user->BizUserID,
                ],
                'd365' => [
                    'provisioned' => !empty($user->D365UserID),
                    'user_id' => $user->D365UserID,
                ],
            ],
            'modules' => $modules->values(),
            'functions' => $functions->values(),
            'summary' => [
                'total_modules' => $modules->count(),
                'total_module_assignments' => $moduleAssignments->count(),
                'total_function_assignments' => $functionAssignments->count(),
                'is_active' => $user->Status === 'Active',
                'is_provisioned_to_azure' => !empty($user->AzureId),
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Error checking user in database', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'error' => 'Database error occurred',
            'message' => $e->getMessage(),
        ], 500);
    }
})->name('azure.check-user');



// Route::get('/admin/repair-azure-ids', function() {
//     $azureService = app(\App\Services\AzureGraphService::class);
    
//     $user = DB::connection('sqlsrv')
//         ->table('Azure_Users')
//         ->where('userPrincipalName', 'c.c@gmail.com')
//         ->first();
    
//     if (!$user) {
//         return 'User not found';
//     }
    
//     if (!empty($user->AzureId)) {
//         return 'User already has Azure ID: ' . $user->AzureId;
//     }
    
//     try {
//         // Look up in Azure AD
//         $azureUser = $azureService->findUserByUPN('c.c@gmail.com');
        
//         if ($azureUser && isset($azureUser['id'])) {
//             // Update database
//             DB::connection('sqlsrv')
//                 ->table('Azure_Users')
//                 ->where('Id', $user->Id)
//                 ->update([
//                     'AzureId' => $azureUser['id'],
//                     'Status' => 'Active',
//                 ]);
            
//             return 'SUCCESS! Azure ID updated: ' . $azureUser['id'];
//         } else {
//             return 'User not found in Azure AD - needs provisioning';
//         }
//     } catch (\Exception $e) {
//         return 'Error: ' . $e->getMessage();
//     }
// })->middleware('auth');

// Azure ID Repair Route
Route::get('/azure/repair-azure-id', function (Illuminate\Http\Request $request) {
    try {
        // Get search parameters
        $azureId = $request->query('azure_id');
        $email = $request->query('email');
        $employeeId = $request->query('employee_id');
        $userId = $request->query('user_id');

        if (!$azureId && !$email && !$employeeId && !$userId) {
            return response()->json([
                'error' => 'Please provide at least one parameter',
                'examples' => [
                    'By Email' => url('/azure/repair-azure-id?email=cynthia.clarke@freight-in-time.com'),
                    'By Employee ID' => url('/azure/repair-azure-id?employee_id=EMP001'),
                ]
            ], 400);
        }

        // Find user
        $query = DB::connection('sqlsrv')->table('Azure_Users');

        if ($userId) {
            $query->where('Id', $userId);
        } elseif ($azureId) {
            $query->where('AzureId', $azureId);
        } elseif ($email) {
            $query->where(function($q) use ($email) {
                $q->where('userPrincipalName', $email)->orWhere('mail', $email);
            });
        } elseif ($employeeId) {
            $query->where('EmployeCode', $employeeId);
        }

        $user = $query->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found in database',
            ], 404);
        }

        // Check Azure AD
        $azureService = app(\App\Services\AzureGraphService::class);
        $azureUser = null;

        try {
            $azureUser = $azureService->findUserByUPN($user->userPrincipalName);
        } catch (\Exception $e) {
            // Not found in Azure AD
        }

        $azureIdFromAD = $azureUser['id'] ?? null;
        $needsRepair = (!empty($azureIdFromAD) && $user->AzureId !== $azureIdFromAD);

        // Auto repair if requested
        $autoRepair = filter_var($request->query('auto_repair', 'false'), FILTER_VALIDATE_BOOLEAN);
        $repaired = false;

        if ($needsRepair && $autoRepair && $azureIdFromAD) {
            DB::connection('sqlsrv')
                ->table('Azure_Users')
                ->where('Id', $user->Id)
                ->update(['AzureId' => $azureIdFromAD]);
            
            $repaired = true;
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->Id,
                'email' => $user->userPrincipalName,
                'display_name' => $user->displayName,
            ],
            'database_azure_id' => $user->AzureId,
            'azure_ad_id' => $azureIdFromAD,
            'needs_repair' => $needsRepair,
            'was_repaired' => $repaired,
            'hint' => $needsRepair && !$autoRepair ? 'Add &auto_repair=true to fix' : null,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
});



// ============================================================================
// ADD TO: routes/web.php
// ============================================================================

// View user data from Azure_Users table
Route::get('/azure/view-user', function (Illuminate\Http\Request $request) {
    try {
        $email = $request->query('email');
        $azureId = $request->query('azure_id');
        $userId = $request->query('user_id');
        $employeeId = $request->query('employee_id');
        $name = $request->query('name');

        if (!$email && !$azureId && !$userId && !$employeeId && !$name) {
            return response()->json([
                'error' => 'Please provide at least one search parameter',
                'examples' => [
                    'By Email' => url('/azure/view-user?email=cynthia.clarke@freight-in-time.com'),
                    'By Name' => url('/azure/view-user?name=Cynthia'),
                    'By Azure ID' => url('/azure/view-user?azure_id=7325baa6-dcea-47f3-9e96-f6cdbc690412'),
                    'By Employee ID' => url('/azure/view-user?employee_id=EMP001'),
                ]
            ], 400);
        }

        // Build query
        $query = DB::connection('sqlsrv')->table('Azure_Users');

        if ($userId) {
            $query->where('Id', $userId);
        } elseif ($azureId) {
            $query->where('AzureId', $azureId);
        } elseif ($email) {
            $query->where(function($q) use ($email) {
                $q->where('userPrincipalName', $email)->orWhere('mail', $email);
            });
        } elseif ($employeeId) {
            $query->where('EmployeCode', $employeeId);
        } elseif ($name) {
            $query->where('displayName', 'LIKE', "%{$name}%");
        }

        $user = $query->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found in Azure_Users table',
                'search_params' => array_filter([
                    'email' => $email,
                    'azure_id' => $azureId,
                    'user_id' => $userId,
                    'employee_id' => $employeeId,
                    'name' => $name,
                ])
            ], 404);
        }

        // Get module assignments
        $modules = DB::connection('sqlsrv')
            ->table('Azure_Application_UserMapping')
            ->where('user_id', $user->userPrincipalName)
            ->where('IsActive', 1)
            ->where('IsDelete', 0)
            ->select('ApplicationName', 'RoleCode', 'OfficeCode')
            ->get()
            ->groupBy('ApplicationName')
            ->map(function($items, $module) {
                return [
                    'module' => $module,
                    'roles' => $items->pluck('RoleCode')->unique()->values(),
                    'locations' => $items->pluck('OfficeCode')->unique()->values(),
                ];
            })->values();

        return response()->json([
            'success' => true,
            'user' => [
                // Basic Info
                'id' => $user->Id,
                'azure_id' => $user->AzureId,
                'status' => $user->Status,
                'account_enabled' => (bool)$user->accountEnabled,
                
                // Personal Info
                'display_name' => $user->displayName,
                'given_name' => $user->givenName,
                'surname' => $user->surname,
                'email' => $user->userPrincipalName,
                'mail' => $user->mail,
                
                // Contact
                'mobile_phone' => $user->mobilePhone,
                'telephone' => $user->telephoneNumber,
                
                // Work Info
                'employee_code' => $user->EmployeCode,
                'job_title' => $user->jobTitle,
                'department' => $user->department,
                'company' => $user->companyName,
                'office_location' => $user->officeLocation,
                'base_office_code' => $user->BaseOfficeCode,
                
                // Location
                'country' => $user->country,
                'usage_location' => $user->usageLocation,
                
                // External Systems
                'odoo_user_id' => $user->OdooUserId,
                'odoo_employee_id' => $user->OdooEmployeeId,
                'scm_user_id' => $user->SCMUserID,
                'scm_resource_id' => $user->SCMResourceID,
                'biz_user_id' => $user->BizUserID,
                'd365_user_id' => $user->D365UserID,
                
                // Timestamps
                'created_datetime' => $user->createdDateTime,
                'last_modified_on' => $user->LastModifiedOn ?? null,
            ],
            'modules' => $modules,
            'summary' => [
                'has_azure_id' => !empty($user->AzureId),
                'is_active' => $user->Status === 'Active',
                'modules_count' => $modules->count(),
                'provisioned_to' => array_filter([
                    'azure_ad' => !empty($user->AzureId),
                    'odoo' => !empty($user->OdooUserId),
                    'scm' => !empty($user->SCMUserID),
                    'biz' => !empty($user->BizUserID),
                    'd365' => !empty($user->D365UserID),
                ]),
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
});




// ============================================================================
// ADD TO: routes/web.php
// ============================================================================

// Fix Azure ID by manually providing both user_id and azure_id
Route::get('/azure/fix-azure-id', function (Illuminate\Http\Request $request) {
    try {
        $userId = $request->query('user_id');
        $azureId = $request->query('azure_id');
        $verify = filter_var($request->query('verify', 'false'), FILTER_VALIDATE_BOOLEAN);

        if (!$userId || !$azureId) {
            return response()->json([
                'error' => 'Both user_id and azure_id are required',
                'example' => url('/azure/fix-azure-id?user_id=824F47DD-21D3-4BD5-9AA3-044B61F50BA9&azure_id=7325baa6-dcea-47f3-9e96-f6cdbc690412&verify=true'),
            ], 400);
        }

        // Get user from database
        $user = DB::connection('sqlsrv')
            ->table('Azure_Users')
            ->where('Id', $userId)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found in database',
            ], 404);
        }

        // Validate Azure ID format
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $azureId)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Azure ID format',
            ], 400);
        }

        // Check if this Azure ID already exists for another user
        $existingUser = DB::connection('sqlsrv')
            ->table('Azure_Users')
            ->where('AzureId', $azureId)
            ->where('Id', '!=', $userId)
            ->first();

        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'This Azure ID is already assigned to another user',
                'existing_user' => [
                    'id' => $existingUser->Id,
                    'name' => $existingUser->displayName,
                    'email' => $existingUser->userPrincipalName,
                ]
            ], 400);
        }

        // Verify Azure AD (optional)
        $azureVerified = false;
        $azureUserData = null;

        try {
            $azureService = app(\App\Services\AzureGraphService::class);
            $azureUserData = $azureService->getUser($azureId);
            $azureVerified = true;
        } catch (\Exception $e) {
            $azureVerified = false;
        }

        // If not verifying, just show what would be updated
        if (!$verify) {
            return response()->json([
                'success' => false,
                'message' => 'Preview mode - no changes made',
                'current_data' => [
                    'user_id' => $user->Id,
                    'display_name' => $user->displayName,
                    'email' => $user->userPrincipalName,
                    'current_azure_id' => $user->AzureId,
                ],
                'proposed_changes' => [
                    'new_azure_id' => $azureId,
                ],
                'azure_verification' => [
                    'verified' => $azureVerified,
                    'azure_name' => $azureUserData['displayName'] ?? null,
                    'azure_email' => $azureUserData['userPrincipalName'] ?? null,
                ],
                'warning' => $azureVerified ? null : '⚠️ Could not verify this Azure ID exists in Azure AD',
                'next_step' => 'Add &verify=true to apply these changes'
            ]);
        }

        // Update the database (removed LastModifiedOn)
        DB::connection('sqlsrv')
            ->table('Azure_Users')
            ->where('Id', $userId)
            ->update([
                'AzureId' => $azureId,
            ]);

        Log::info('Azure ID manually updated', [
            'user_id' => $userId,
            'old_azure_id' => $user->AzureId,
            'new_azure_id' => $azureId,
            'user_name' => $user->displayName,
        ]);

        return response()->json([
            'success' => true,
            'message' => '✅ Azure ID updated successfully!',
            'user' => [
                'id' => $user->Id,
                'display_name' => $user->displayName,
                'email' => $user->userPrincipalName,
            ],
            'changes' => [
                'old_azure_id' => $user->AzureId,
                'new_azure_id' => $azureId,
            ],
            'azure_verification' => [
                'verified' => $azureVerified,
                'azure_name' => $azureUserData['displayName'] ?? null,
                'azure_email' => $azureUserData['userPrincipalName'] ?? null,
            ],
            'next_step' => 'Now try updating the user with roles/modules again'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
});



// 7325baa6-dcea-47f3-9e96-f6cdbc690412