<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PoController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvestasiController;
use App\Http\Controllers\UserController;

Route::middleware(['guest'])->group(function () {
  Route::get('/', [AuthController::class, 'showLogin'])->name('login'); // Name this 'login' for Laravel redirects
  Route::get('/login', [AuthController::class, 'showLogin']);
  Route::post('/login', [AuthController::class, 'login']);
  Route::get('forgot-password', function () {
    return view('forgot-password');
  })->name('forgot-password');

  Route::get('register', function () {
    return view('register');
  })->name('register');
});

Route::middleware(['auth'])->group(function () {

  Route::get('/dashboard', [DashboardController::class, 'showDashboard'])->name('dashboard');
  Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

  // Purchase Orders

  // Index Page
  Route::get('purchase-orders', [PoController::class, 'index'])->name('po.index');

  // Create purchase order
  Route::get('/purchase-order/create', [PoController::class, 'create'])->name('po.create');

  // Store purchase order
  Route::post('/purchase-order/store', [PoController::class, 'store'])
    ->name('po.store');

  // Get Incoming PO Details
  Route::get('/purchase-order/details/{id}', [PoController::class, 'getIncomingDetails'])->name('po.incoming-details');

  // Get Details
  Route::get('/purchase-order/{po_id}', [PoController::class, 'showPoDetails'])->name('po.show');

  // Edit purchase order
  Route::get('/purchase-order/{po_id}/edit', [PoController::class, 'editPo'])->name('po.edit');

  // Update purchase order
  Route::put('/purchase-order/{id}', [PoController::class, 'updatePo'])->name('po.update');

  // Delete purchase order 
  Route::delete('/purchase-order/{po_id}', [PoController::class, 'destroyPo'])->name('po.destroy');
  // Export PO
  Route::get('/po/export', [PoController::class, 'export'])->name('po.export');
  // Import PO
  Route::get('/purchase-orders/import', [PoController::class, 'importForm'])->name('purchase-orders.importForm');
  Route::post('/po/import', [PoController::class, 'import'])->name('po.import');
  // Purchase Orders End

  // Invoices
  Route::resource('invoices', InvoiceController::class)->names('invoice');
  Route::post('invoices/{id}/pay', [InvoiceController::class, 'payInvoice']);
  // Invoices End

  // Deliveries
  Route::resource('deliveries', DeliveryController::class)->names('delivery');
  Route::post('/delivery/{id}/auto-deliver', [DeliveryController::class, 'autoDeliver'])->name('delivery.autoDeliver');
  // Deliveries End

  // Incoming Purchase Orders Start

  // Index Page
  Route::get('incoming-purchase-orders', [PoController::class, 'incomingPo'])->name('incomingPo');

  // Create Form Page
  Route::get('/incoming-po/create', [PoController::class, 'createIncoming'])
    ->name('incoming-po.create');

  // Store incoming purchase order
  Route::post('/incoming-po/store', [PoController::class, 'storeIncoming'])
    ->name('incoming-po.store');

  // Get Details
  Route::get('/incoming-po/{po_id}', [PoController::class, 'show'])->name('incoming-po.show');

  // Edit incoming purchase order
  Route::get('/incoming-po/{po_id}/edit', [PoController::class, 'edit'])->name('incoming-po.edit');

  // Update incoming purchase order
  Route::put('/incoming-po/{id}', [PoController::class, 'update'])->name('incomingPo.update');

  // Delete incoming purchase order 
  Route::delete('/incoming-po/{po_id}', [PoController::class, 'destroy'])->name('incoming-po.destroy');

  // Incoming Purchase Orders End
  Route::get('investments/export', [InvestasiController::class, 'export'])->name('investments.export');
  Route::get('investments/import', [InvestasiController::class, 'importForm'])->name('investments.importForm');
  Route::post('investments/import', [InvestasiController::class, 'import'])->name('investments.import');
  Route::resource('investments', InvestasiController::class)->names('investments');
  Route::resource('payments', PaymentController::class)->names('payment');
  Route::post('payments/{id}/pay', [PaymentController::class, 'payNow'])->name('payments.payNow');
  Route::resource('customers', CustomerController::class);
  Route::resource('users', UserController::class)->names('users');
  Route::post('/users/{id}/activate', [UserController::class, 'activate'])->name('users.activate');
  Route::post('/users/{id}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
  Route::get('users/profile/{id}', [UserController::class, 'profile'])->name('users.profile');
  Route::get('/dashboard/dana-tersedia', [DashboardController::class, 'danaTersedia']);
  Route::get('/api/investasi-stats', [InvestasiController::class, 'getStats'])->name('api.investasi.stats');
  Route::get('/api/dashboard-stats', [DashboardController::class, 'getStats'])->name('api.dashboard.stats');
  Route::get('/api/incomingPo-stats', [PoController::class, 'getStatsIncoming'])->name('api.incomingPo.stats');
  Route::get('/api/po-filtered-stats', [PoController::class, 'filterPoDates'])->name('api.po-filtered-stats');
  Route::get('/api/po-stats', [PoController::class, 'getStats'])->name('api.po-stats');
});
