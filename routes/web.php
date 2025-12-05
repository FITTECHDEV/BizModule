<?php

use Adldap\Laravel\Facades\Adldap;
use App\Http\Controllers\ClientPartyController;
use App\Models\Category;
use App\Models\ClientParty;
use App\Models\Office;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Crypt;

Route::get('/test-pgsql', function () {
    try {
        DB::connection('pgsql')->getPdo();
        return response()->json(["message" => "✅ PostgreSQL is connected!"]);
    } catch (\Exception $e) {
        return response()->json(["error" => "❌ PostgreSQL Connection Failed: " . $e->getMessage()]);
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
})->name('logout');

Route::get('quote-logger', function () {

    $auth_user = Auth::user();
    //todo: permissions
    return redirect()->away("https://quotesuat.freightintime.com/?username=" . $auth_user->LoginID . "&code=" . $auth_user->password . "&email=" . $auth_user->Email, 302, ['enc_data' => ""]);

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
//, 'change.password'

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
    Route::post('users/profile/{user}', [App\Http\Controllers\UsersController::class, 'update'])->name('user.update');
    Route::get('users/get-change-password', [App\Http\Controllers\UsersController::class, 'GetChangePassword'])->name('user.get_change_password')->withoutMiddleware('change.password');
    Route::post('users/change-password', [App\Http\Controllers\UsersController::class, 'ChangePassword'])->name('user.change_password')->withoutMiddleware('change.password');
    Route::resource('users', App\Http\Controllers\UsersController::class);
    Route::post('users/pass/{user}', [App\Http\Controllers\UsersController::class, 'pass'])->name('user.update.password');
    Route::post('users/ajax-update', [App\Http\Controllers\UsersController::class, 'updateAjax'])->name('users.update_ajax');
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
    //Notify Route
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
    //* push notification
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
        Route::get('hs-codes/{id}', [App\Http\Controllers\TIMSController::class, 'hsCodes'])->name('tims.hsCodes');
        Route::post('update-invoice', [App\Http\Controllers\TIMSController::class, 'updateInvoice'])->name('tims.updateInvoice');
    });

    /**!SECTION EFRIS */
    Route::group(['prefix' => 'efris'], function () {
        Route::get('/', [App\Http\Controllers\EfrisController::class, 'index'])->name('efris.index');
        Route::get('re-trigger/{docNo}', [App\Http\Controllers\EfrisController::class, 'reTrigger'])->name('efris.reTrigger');
    });

    /**!SECTION TRA FIT*/
    Route::group(['prefix' => 'tra'], function () {
        Route::get('/', [App\Http\Controllers\TraController::class, 'index'])->name('tra.index');
        Route::get('/get-stats', [App\Http\Controllers\TraController::class, 'getStats'])->name('tra.get_stats');
        Route::get('re-trigger/{docNo}', [App\Http\Controllers\TraController::class, 'reTrigger'])->name('tra.reTrigger');
    });

    /**!SECTION TRA ADL*/
    Route::group(['prefix' => 'traADL'], function () {
        Route::get('/', [App\Http\Controllers\TraADLController::class, 'index'])->name('traADL.index');
        Route::get('/get-stats', [App\Http\Controllers\TraADLController::class, 'getStats'])->name('traADL.get_stats');
        Route::get('re-trigger/{docNo}', [App\Http\Controllers\TraADLController::class, 'reTrigger'])->name('traADL.reTrigger');
        Route::get('json/{id}', [App\Http\Controllers\TraADLController::class, 'jsonGenerate'])->name('tims.json');
        Route::get('invoice/download/{id}', [App\Http\Controllers\TraADLController::class, 'invoiceDownload'])->name('tims.invoiceDownload')->middleware(['signed']);
    });

    /**!SECTION RRA */
    Route::group(['prefix' => 'rra'], function () {
        Route::get('/', [App\Http\Controllers\RRAController::class, 'index'])->name('rra.index');
        Route::get('/get-stats', [App\Http\Controllers\RRAController::class, 'getStats'])->name('rra.get_stats');
        Route::get('re-trigger/{docNo}', [App\Http\Controllers\RRAController::class, 'reTrigger'])->name('rra.reTrigger');
        Route::get('json/{id}', [App\Http\Controllers\RRAController::class, 'jsonGenerate'])->name('rra.json');
        Route::get('invoice/download/{id}', [App\Http\Controllers\RRAController::class, 'invoiceDownload'])->name('rra.invoiceDownload')->middleware(['signed']);
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
Route::post('/create-client-party-scm', [ClientPartyController::class, 'CreateClientPartySCM']) ->middleware('auth:api')->name('client.create-party-scm');




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
            ->whereNumber('user')
            ->middleware('throttle:user_actions');
        
        Route::post('/{user}/enable', [App\Http\Controllers\AzureUserManagementController::class, 'enable'])
            ->name('enable')
            ->whereNumber('user')
            ->middleware('throttle:user_actions');
        
        Route::post('/{user}/retry-modules', [App\Http\Controllers\AzureUserManagementController::class, 'retryModuleAssignments'])
            ->name('retry-modules')
            ->whereNumber('user')
            ->middleware('throttle:user_actions');
        
        // Generic CRUD routes (come after specific actions)
        Route::get('/{user}', [App\Http\Controllers\AzureUserManagementController::class, 'show'])
            ->name('show')
            ->whereNumber('user');
        
        Route::get('/{user}/edit', [App\Http\Controllers\AzureUserManagementController::class, 'edit'])
            ->name('edit')
            ->whereNumber('user');
        
        // PUT/PATCH: Update user (rate limited - 10 per minute)
        Route::put('/{user}', [App\Http\Controllers\AzureUserManagementController::class, 'update'])
            ->name('update')
            ->whereNumber('user')
            ->middleware('throttle:user_actions');
        
        Route::patch('/{user}', [App\Http\Controllers\AzureUserManagementController::class, 'update'])
            ->name('patch')
            ->whereNumber('user')
            ->middleware('throttle:user_actions');
        
        // DELETE: Remove user (rate limited)
        Route::delete('/{user}', [App\Http\Controllers\AzureUserManagementController::class, 'destroy'])
            ->name('destroy')
            ->whereNumber('user')
            ->middleware('throttle:user_actions');
    });

    // ============================================================================
    // MODULE MANAGEMENT ROUTES
    // ============================================================================

    Route::prefix('modules')->name('modules.')->group(function () {
    Route::get('/', function() { 
        return view('azure-management.modules.index'); 
    })->name('index');
    
    Route::get('/assign', function() { 
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
            Route::post('/{user}/disable', [App\Http\Controllers\AzureUserManagementController::class, 'disable'])
                ->name('disable')
                ->whereNumber('user')
                ->middleware('throttle:user_actions');
            
            Route::post('/{user}/enable', [App\Http\Controllers\AzureUserManagementController::class, 'enable'])
                ->name('enable')
                ->whereNumber('user')
                ->middleware('throttle:user_actions');
            
            Route::post('/{user}/retry-modules', [App\Http\Controllers\AzureUserManagementController::class, 'retryModuleAssignments'])
                ->name('retry-modules')
                ->whereNumber('user')
                ->middleware('throttle:user_actions');
            
            Route::get('/{user}/status', [App\Http\Controllers\AzureUserManagementController::class, 'getStatus'])
                ->name('status')
                ->whereNumber('user')
                ->middleware('throttle:api');
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


Route::get('/sync-odoo-users', [App\Http\Controllers\OdooSyncController::class, 'syncUsers']);
Route::get('/test-odoo-columns', [App\Http\Controllers\OdooSyncController::class, 'testColumns']);
Route::get('/test-pgsql-connection', [App\Http\Controllers\OdooSyncController::class, 'testConnection']);
Route::get('/check-view-definition', [App\Http\Controllers\OdooSyncController::class, 'checkViewDefinition']);






Route::get('/test-stored-procedures', function () {
    try {
        echo "<html><head><title>Stored Procedures Test</title>";
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
        echo "<h1>🔍 Stored Procedures Test</h1>";
        echo "<p class='info'>Testing: <strong>sp_UserCreation_Resourcefunction</strong> and <strong>sp_UserCreation_ResourceRole</strong></p>";
        echo "<hr>";

        // ================================================================
        // TEST 1: Check if sp_UserCreation_Resourcefunction exists
        // ================================================================
        echo "<div class='section'>";
        echo "<h2>Test 1: sp_UserCreation_Resourcefunction (SCM Functions)</h2>";
        
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
                
                echo "<p class='success'>✅ Execution successful! Found " . count($functions) . " functions</p>";
                
                if (count($functions) > 0) {
                    echo "<table>";
                    echo "<thead><tr>";
                    
                    // Get column names from first row
                    $firstRow = $functions[0];
                    foreach ($firstRow as $key => $value) {
                        echo "<th>" . htmlspecialchars($key) . "</th>";
                    }
                    echo "</tr></thead><tbody>";
                    
                    // Display all rows
                    foreach ($functions as $function) {
                        echo "<tr>";
                        foreach ($function as $value) {
                            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</tbody></table>";
                } else {
                    echo "<p class='info'>⚠️ No functions returned (empty result set)</p>";
                }
                
            } else {
                echo "<p class='error'>❌ Stored procedure DOES NOT EXIST</p>";
                echo "<p>Please create the stored procedure first.</p>";
            }
            
        } catch (\Exception $e) {
            echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre style='background: #fff3cd; padding: 10px; border-radius: 4px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        }
        echo "</div>";

        // ================================================================
        // TEST 2: Check if sp_UserCreation_ResourceRole exists
        // ================================================================
        echo "<div class='section'>";
        echo "<h2>Test 2: sp_UserCreation_ResourceRole (Roles by Application Code)</h2>";
        
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
                        
                        echo "<p class='success'>✅ Execution successful! Found " . count($roles) . " roles</p>";
                        
                        if (count($roles) > 0) {
                            echo "<table>";
                            echo "<thead><tr>";
                            
                            // Get column names from first row
                            $firstRow = $roles[0];
                            foreach ($firstRow as $key => $value) {
                                echo "<th>" . htmlspecialchars($key) . "</th>";
                            }
                            echo "</tr></thead><tbody>";
                            
                            // Display all rows
                            foreach ($roles as $role) {
                                echo "<tr>";
                                foreach ($role as $value) {
                                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                                }
                                echo "</tr>";
                            }
                            echo "</tbody></table>";
                        } else {
                            echo "<p class='info'>⚠️ No roles returned for {$appCode}</p>";
                        }
                        
                    } catch (\Exception $e) {
                        echo "<p class='error'>❌ Error for {$appCode}: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                
            } else {
                echo "<p class='error'>❌ Stored procedure DOES NOT EXIST</p>";
                echo "<p>Please create the stored procedure first.</p>";
            }
            
        } catch (\Exception $e) {
            echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre style='background: #fff3cd; padding: 10px; border-radius: 4px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        }
        echo "</div>";

        // ================================================================
        // TEST 3: Check available modules
        // ================================================================
        echo "<div class='section'>";
        echo "<h2>Test 3: Available Modules (Azure_SoftwareMaster)</h2>";
        
        try {
            $modules = DB::connection('sqlsrv')->select("
                SELECT Id, Name, ApplicationId 
                FROM Azure_SoftwareMaster 
                WHERE IsActive = 1 AND IsDeleted = 0 
                ORDER BY Name
            ");
            
            echo "<p class='success'>✅ Found " . count($modules) . " active modules</p>";
            
            if (count($modules) > 0) {
                echo "<table>";
                echo "<thead><tr><th>ID</th><th>Name</th><th>Application ID</th><th>Is SCM?</th></tr></thead>";
                echo "<tbody>";
                
                foreach ($modules as $module) {
                    $isSCM = in_array($module->ApplicationId, ['OPS', 'OPSUAT']);
                    $scmBadge = $isSCM ? "<span style='background: #27ae60; color: white; padding: 2px 8px; border-radius: 3px; font-size: 0.85em;'>SCM</span>" : "";
                    
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($module->Id) . "</td>";
                    echo "<td>" . htmlspecialchars($module->Name) . "</td>";
                    echo "<td><strong>" . htmlspecialchars($module->ApplicationId) . "</strong> {$scmBadge}</td>";
                    echo "<td>" . ($isSCM ? "Yes" : "No") . "</td>";
                    echo "</tr>";
                }
                
                echo "</tbody></table>";
            }
            
        } catch (\Exception $e) {
            echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        echo "</div>";

        // ================================================================
        // TEST 4: Check available locations
        // ================================================================
        echo "<div class='section'>";
        echo "<h2>Test 4: Available Locations (Cust_Office)</h2>";
        
        try {
            $locations = DB::connection('sqlsrv')->select("
                SELECT Id, Name, code 
                FROM Cust_Office 
                WHERE IsActive = 1 AND IsDeleted = 0 
                ORDER BY Name
            ");
            
            echo "<p class='success'>✅ Found " . count($locations) . " active locations</p>";
            
            if (count($locations) > 0) {
                echo "<table>";
                echo "<thead><tr><th>ID</th><th>Name</th><th>Code</th></tr></thead>";
                echo "<tbody>";
                
                foreach ($locations as $location) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($location->Id) . "</td>";
                    echo "<td>" . htmlspecialchars($location->Name) . "</td>";
                    echo "<td><strong>" . htmlspecialchars($location->code) . "</strong></td>";
                    echo "</tr>";
                }
                
                echo "</tbody></table>";
            }
            
        } catch (\Exception $e) {
            echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        echo "</div>";

        // Summary
        echo "<hr>";
        echo "<h2>Summary</h2>";
        echo "<p><strong>Next Steps:</strong></p>";
        echo "<ul>";
        echo "<li>If stored procedures don't exist, create them in SQL Server</li>";
        echo "<li>If stored procedures exist but return no data, populate the underlying tables</li>";
        echo "<li>If everything works, deploy the updated create user form</li>";
        echo "</ul>";

        echo "</div></body></html>";
        
    } catch (\Exception $e) {
        echo "<div style='padding: 20px; background: #fee; border: 2px solid #e74c3c; border-radius: 4px;'>";
        echo "<h2 style='color: #e74c3c;'>Critical Error</h2>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Line:</strong> " . htmlspecialchars($e->getLine()) . "</p>";
        echo "</div>";
    }
});




Route::get('/test-azure-users', function() {
    dd([
        'raw_count' => DB::connection('sqlsrv')->table('Azure_Users')->count(),
        'model_count' => App\Models\AzureUser::count(),
        'first_user' => App\Models\AzureUser::first(),
    ]);
});