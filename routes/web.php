<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::get('/cron', function () {
    Artisan::call('sendErrors');
});

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/tarifs-public', [App\Http\Controllers\PublicController::class, 'tarifs'])->name('tarifs-public');

// Paiement public (sans authentification)
Route::get('/don', [App\Http\Controllers\PublicPaymentController::class, 'index'])->name('public.payment');
Route::post('/don', [App\Http\Controllers\PublicPaymentController::class, 'processPayment'])->name('public.payment.process');
Route::post('/don/callback', [App\Http\Controllers\PublicPaymentController::class, 'callback'])->name('public.payment.callback');
Route::get('/getPrice', [App\Http\Controllers\HomeController::class, 'getPrice']);
Route::post('/getAddFlightInfoTime', [App\Http\Controllers\HomeController::class, 'getAddFlightInfoTime']);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index']);
Route::post('/flightDay', [App\Http\Controllers\HomeController::class, 'addFlightDay'])->name('flightDay');
Route::post('/flightDay/delete', [App\Http\Controllers\HomeController::class, 'deleteFlightDay'])->name('deleteFlightDay');
Route::get('/flightDayBoard', [App\Http\Controllers\HomeController::class, 'getFlightDay'])->name('flightDayBoard');
Route::any('/saisie', [App\Http\Controllers\HomeController::class, 'saisie'])->name('saisie');
Route::get('/transactionsYear', [App\Http\Controllers\HomeController::class, 'getTransactionsYear'])->name('transactionsYear');
Route::get('/saisie/deleteLast', [App\Http\Controllers\HomeController::class, 'deleteLastTransaction'])->name('deleteLast');
Route::get('/saisiePeriodique/{year?}', [App\Http\Controllers\admin::class, 'saisiePeriodique'])->name('saisiePeriodique');
Route::post('/saisiePeriodique', [App\Http\Controllers\admin::class, 'saisiePeriodiqueEnregistrement'])->name('saisiePeriodique.store');
Route::any('/planches', [App\Http\Controllers\HomeController::class, 'planches'])->name('planches');
Route::any('/carnet', [App\Http\Controllers\HomeController::class, 'carnet'])->name('carnet');
Route::post('/pay/add', [App\Http\Controllers\HomeController::class, 'addPay'])->name('addPay');
Route::get('/addFlight', [App\Http\Controllers\HomeController::class, 'addFlight'])->name('addFlight');
Route::post('/alertRead', [App\Http\Controllers\HomeController::class, 'alertRead'])->name('alertRead');

// Transfert entre pilotes
Route::get('/transfer', [App\Http\Controllers\HomeController::class, 'transfer'])->name('transfer');
Route::post('/transfer', [App\Http\Controllers\HomeController::class, 'processTransfer'])->name('processTransfer');

// Todolist partagée
Route::get('/todolist', [App\Http\Controllers\TodolistController::class, 'index'])->name('todolist.index');
Route::post('/todolist', [App\Http\Controllers\TodolistController::class, 'store'])->name('todolist.store');
Route::get('/todolist/{id}/edit', [App\Http\Controllers\TodolistController::class, 'edit'])->name('todolist.edit');
Route::put('/todolist/{id}', [App\Http\Controllers\TodolistController::class, 'update'])->name('todolist.update');
Route::delete('/todolist/{id}', [App\Http\Controllers\TodolistController::class, 'destroy'])->name('todolist.destroy');
Route::post('/todolist/{id}/complete', [App\Http\Controllers\TodolistController::class, 'complete'])->name('todolist.complete');
Route::post('/todolist/{id}/in-progress', [App\Http\Controllers\TodolistController::class, 'inProgress'])->name('todolist.in-progress');
Route::post('/todolist/{id}/pending', [App\Http\Controllers\TodolistController::class, 'pending'])->name('todolist.pending');

Route::get('/validTransactions', [App\Http\Controllers\admin::class, 'getValidTransactions'])->name('validTransactions')->middleware('can:admin');
Route::post('/validTransactionPost', [App\Http\Controllers\admin::class, 'ValidTransactions'])->name('validTransactionPost')->middleware('can:admin');
Route::post('/deleteTransactionPost', [App\Http\Controllers\admin::class, 'DeleteTransactions'])->name('deleteTransactionPost')->middleware('can:admin');

Route::post('/validNewTrDate', [App\Http\Controllers\admin::class, 'validNewTrDate'])->name('validNewTrDate')->middleware('can:admin');
Route::get('/updateSolde', [App\Http\Controllers\admin::class, 'updateSolde'])->name('updateSolde')->middleware('can:admin');
Route::post('/validNewAdminFlight', [App\Http\Controllers\admin::class, 'validNewAdminFlight'])->name('validNewAdminFlight')->middleware('can:admin');
Route::get('/route', [App\Http\Controllers\admin::class, 'flightList'])->name('aircraftFlights')->middleware('can:admin');
Route::get('/vol', [App\Http\Controllers\admin::class, 'flightList'])->name('pilotFlights')->middleware('can:admin');
Route::get('/controlData', [App\Http\Controllers\admin::class, 'updateAndControlData'])->name('updateAndControlData')->middleware('can:admin');
Route::get('/accountExport', [App\Http\Controllers\admin::class, 'accountExport'])->name('accountExport')->middleware('can:admin');
Route::get('/invoiceExport', [App\Http\Controllers\admin::class, 'invoiceExport'])->name('invoiceExport')->middleware('can:admin');
Route::get('/sendAccountState', [App\Http\Controllers\admin::class, 'sendAccountState'])->name('sendAccountState')->middleware('can:admin');
Route::get('/instruction', [App\Http\Controllers\admin::class, 'instruction'])->name('instruction')->middleware('can:admin');
Route::get('/addInstructeur-{id}', [App\Http\Controllers\admin::class, 'addInstructeur'])->name('addInstructeur')->middleware('can:admin');

Route::get('/tarifs', [App\Http\Controllers\admin::class, 'tarifs'])->name('tarifs')->middleware('can:admin');
Route::put('/admin/aircraft/{id}/update-price', [App\Http\Controllers\admin::class, 'updateAircraftPrice'])->name('updateAircraftPrice')->middleware('can:admin');
Route::post('/admin/aircraft/create', [App\Http\Controllers\admin::class, 'createAircraft'])->name('createAircraft')->middleware('can:admin');
Route::put('/admin/start-price/{id}/update', [App\Http\Controllers\admin::class, 'updateStartPrice'])->name('updateStartPrice')->middleware('can:admin');
Route::post('/admin/start-price/create', [App\Http\Controllers\admin::class, 'createStartPrice'])->name('createStartPrice')->middleware('can:admin');

Route::post('/ajoutDepense', [App\Http\Controllers\RefundController::class, 'ajoutDepense'])->name('ajoutDepense');
Route::get('/facture/{id}', [App\Http\Controllers\RefundController::class, 'facture'])->name('facture');

Route::get('/manuel', [App\Http\Controllers\HomeController::class, 'wiki']);
Route::get('/towing', [App\Http\Controllers\admin::class, 'towing']);

//UTILISATEUR / PILOTE
Route::post('/admin/addUser', [App\Http\Controllers\admin::class, 'addUser'])->name('addUser')->middleware('can:admin');
Route::get('/usersList', [App\Http\Controllers\admin::class, 'usersList'])->name('usersList')->middleware('can:admin');
Route::get('/usersExportCsv', [App\Http\Controllers\admin::class, 'usersExportCsv'])->name('usersExportCsv')->middleware('can:admin');
Route::get('/user/state', [App\Http\Controllers\admin::class, 'userState'])->name('userState')->middleware('can:admin');
Route::get('/userMod/{id}', [App\Http\Controllers\admin::class, 'userMod'])->name('userMod')->middleware('can:admin');
Route::post('/userMod/{id}', [App\Http\Controllers\admin::class, 'saveUserMod'])->name('saveUserMod')->middleware('can:admin');
Route::get('/adminAccess/{id}', [App\Http\Controllers\admin::class, 'getAdminAccess'])->name('getAdminAccess')->middleware('can:admin');
Route::get('/aircraft/state', [App\Http\Controllers\admin::class, 'aircraftState'])->name('aircraftState')->middleware('can:admin');

Route::get('/usersSendAccountNotification/preview', [App\Http\Controllers\admin::class, 'usersSendAccountNotificationPreview'])->name('usersSendAccountNotification.preview')->middleware('can:admin');
Route::post('/usersSendAccountNotification', [App\Http\Controllers\admin::class, 'usersSendAccountNotification'])->name('usersSendAccountNotification')->middleware('can:admin');
Route::post('/usersSendAccountNotification/test', [App\Http\Controllers\admin::class, 'usersSendAccountNotificationTest'])->name('usersSendAccountNotification.test')->middleware('can:admin');
Route::get('/sendAccountState/preview/{year}', [App\Http\Controllers\admin::class, 'sendAccountStatePreview'])->name('sendAccountState.preview')->middleware('can:admin');
Route::post('/sendAccountState/{year}', [App\Http\Controllers\admin::class, 'sendAccountStateForYear'])->name('sendAccountState.send')->middleware('can:admin');
Route::post('/sendAccountState/{year}/test', [App\Http\Controllers\admin::class, 'sendAccountStateForYearTest'])->name('sendAccountState.test')->middleware('can:admin');
Route::post('/sendAccountState/user/{id}', [App\Http\Controllers\admin::class, 'sendAccountStateForUser'])->name('sendAccountState.user')->middleware('can:admin');

// WIKI SYSTEM
Route::get('/wiki/delete/{page}', [App\Http\Controllers\WikiController::class, 'deletePage']);
Route::get('/wiki/{page}/{name?}', [App\Http\Controllers\WikiController::class, 'readPage']);
Route::get('/wikiRestore/{page}', [App\Http\Controllers\WikiController::class, 'restore']);
Route::post('/wiki/update/{page}', [App\Http\Controllers\WikiController::class, 'updatePage']);
Route::post('/wiki/new', [App\Http\Controllers\WikiController::class, 'newPage']);
Route::post('/wikipassword', [App\Http\Controllers\WikiController::class, 'password']);

// PARAMÈTRES
Route::get('/admin/parametres', [App\Http\Controllers\ParametreController::class, 'index'])->name('admin.parametres')->middleware('can:admin:super');
Route::post('/admin/parametres', [App\Http\Controllers\ParametreController::class, 'update'])->name('admin.parametres.update')->middleware('can:admin:super');
Route::post('/admin/parametres/autres', [App\Http\Controllers\ParametreController::class, 'updateAutres'])->name('admin.parametres.autres')->middleware('can:admin:super');

// AUDIT
Route::get('/audit', [App\Http\Controllers\AuditController::class, 'index'])->name('audit.index')->middleware('can:admin:audit');

// SAUVEGARDES
Route::get('/backups', [App\Http\Controllers\BackupController::class, 'index'])->name('backups.index')->middleware('can:admin');
Route::post('/backups/create', [App\Http\Controllers\BackupController::class, 'create'])->name('backups.create')->middleware('can:admin');
Route::get('/backups/download/{filename}', [App\Http\Controllers\BackupController::class, 'download'])->name('backups.download')->middleware('can:admin');
Route::post('/backups/delete/{filename}', [App\Http\Controllers\BackupController::class, 'destroy'])->name('backups.destroy')->middleware('can:admin');

// AIDE CONTEXTUELLE
Route::get('/help/content/{key}', [App\Http\Controllers\HelpController::class, 'content'])->middleware('auth')->name('help.content');

// GitHub Webhook — mise à jour automatique
Route::post('/update', [App\Http\Controllers\GitWebhookController::class, 'update'])->name('git.webhook');

//STRIPE
Route::post('/stripe', [App\Http\Controllers\StripeController::class, 'completed']);

//OGN
Route::get('/ognImport/{DATE}', [App\Http\Controllers\OgnController::class, 'import'])->middleware('can:admin');
Route::get('/planchesOgn/{DATE?}', [App\Http\Controllers\OgnController::class, 'planches'])->middleware('can:admin');
Route::get('/planchesOgn/ignore/{ID}', [App\Http\Controllers\OgnController::class, 'ignore'])->middleware('can:admin');

//GESASSO

Route::get('/importGesasso', [App\Http\Controllers\admin::class, 'gesasso'])->middleware('can:admin');
Route::post('/importGesasso', [App\Http\Controllers\admin::class, 'gesassofile'])->middleware('can:admin');
Route::post('/saveDataGesasso', [App\Http\Controllers\admin::class, 'saveDataGesasso'])->middleware('can:admin');

//HELLO ASSO
Route::get('/helloasso', [App\Http\Controllers\HelloAssoController::class, 'page'])->name('helloasso.page');
Route::post('/helloasso/create-payment', [App\Http\Controllers\HelloAssoController::class, 'createPayment'])->name('helloasso.create-payment');

// Webhook HelloAsso (sans middleware CSRF pour les appels externes)
Route::post('/helloasso/webhook', [App\Http\Controllers\HelloAssoController::class, 'notification'])->name('helloasso.webhook');
Route::post('/helloasso/notification', [App\Http\Controllers\HelloAssoController::class, 'notification'])->name('helloasso.notification');

Route::get('/helloasso/return', [App\Http\Controllers\HelloAssoController::class, 'return'])->name('helloasso.return');
Route::get('/helloasso/error', [App\Http\Controllers\HelloAssoController::class, 'error'])->name('helloasso.error');
Route::get('/helloasso/back', [App\Http\Controllers\HelloAssoController::class, 'back'])->name('helloasso.back');

// Routes de test pour l'API HelloAsso
Route::get('/helloasso/test-access-token', [App\Http\Controllers\HelloAssoController::class, 'testAccessToken'])->name('helloasso.test-access-token');
Route::post('/helloasso/test-refresh-token', [App\Http\Controllers\HelloAssoController::class, 'testRefreshToken'])->name('helloasso.test-refresh-token');
Route::get('/helloasso/environment-info', [App\Http\Controllers\HelloAssoController::class, 'getEnvironmentInfo'])->name('helloasso.environment-info');

// Routes pour les pages de résultat de paiement
Route::get('/payment/success', function () {
    return redirect()->route('helloasso.page')->with('info', 'Votre paiement est en cours de traitement. Vous recevrez une confirmation par email une fois le traitement terminé.');
})->name('payment.success');

Route::get('/payment/error', function () {
    return redirect()->route('helloasso.page')->with('error', 'Une erreur est survenue lors du paiement. Veuillez réessayer.');
})->name('payment.error');

Route::get('/payment/cancelled', function () {
    return redirect()->route('helloasso.page')->with('info', 'Paiement annulé. Vous pouvez réessayer quand vous le souhaitez.');
})->name('payment.cancelled');

// Vols d'initiation — pages publiques (sans auth)
Route::get('/vi/{code}', [App\Http\Controllers\VolInitiationController::class, 'activationForm'])->name('vi.activation');
Route::post('/vi/{code}', [App\Http\Controllers\VolInitiationController::class, 'activationStore'])->name('vi.activation.store');

// Vols d'initiation — administration
Route::get('/admin/vi', [App\Http\Controllers\VolInitiationController::class, 'index'])->name('admin.vi.index')->middleware('can:admin:vi');
Route::get('/admin/vi/create', [App\Http\Controllers\VolInitiationController::class, 'create'])->name('admin.vi.create')->middleware('can:admin:vi');
Route::post('/admin/vi', [App\Http\Controllers\VolInitiationController::class, 'store'])->name('admin.vi.store')->middleware('can:admin:vi');
Route::get('/admin/vi/{id}', [App\Http\Controllers\VolInitiationController::class, 'show'])->name('admin.vi.show')->middleware('can:admin:vi');
Route::get('/admin/vi/{id}/edit', [App\Http\Controllers\VolInitiationController::class, 'edit'])->name('admin.vi.edit')->middleware('can:admin:vi');
Route::put('/admin/vi/{id}', [App\Http\Controllers\VolInitiationController::class, 'update'])->name('admin.vi.update')->middleware('can:admin:vi');
Route::post('/admin/vi/{id}/realise', [App\Http\Controllers\VolInitiationController::class, 'marquerRealise'])->name('admin.vi.realise')->middleware('can:admin:vi');
