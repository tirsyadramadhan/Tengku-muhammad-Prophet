<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\layouts\WithoutMenu;
use App\Http\Controllers\layouts\WithoutNavbar;
use App\Http\Controllers\layouts\Fluid;
use App\Http\Controllers\layouts\Container;
use App\Http\Controllers\layouts\Blank;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\pages\AccountSettingsNotifications;
use App\Http\Controllers\pages\AccountSettingsConnections;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\pages\MiscUnderMaintenance;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\cards\CardBasic;
use App\Http\Controllers\user_interface\Accordion;
use App\Http\Controllers\user_interface\Alerts;
use App\Http\Controllers\user_interface\Badges;
use App\Http\Controllers\user_interface\Buttons;
use App\Http\Controllers\user_interface\Carousel;
use App\Http\Controllers\user_interface\Collapse;
use App\Http\Controllers\user_interface\Dropdowns;
use App\Http\Controllers\user_interface\Footer;
use App\Http\Controllers\user_interface\ListGroups;
use App\Http\Controllers\user_interface\Modals;
use App\Http\Controllers\user_interface\Navbar;
use App\Http\Controllers\user_interface\Offcanvas;
use App\Http\Controllers\user_interface\PaginationBreadcrumbs;
use App\Http\Controllers\user_interface\Progress;
use App\Http\Controllers\user_interface\Spinners;
use App\Http\Controllers\user_interface\TabsPills;
use App\Http\Controllers\user_interface\Toasts;
use App\Http\Controllers\user_interface\TooltipsPopovers;
use App\Http\Controllers\user_interface\Typography;
use App\Http\Controllers\extended_ui\PerfectScrollbar;
use App\Http\Controllers\extended_ui\TextDivider;
use App\Http\Controllers\icons\RiIcons;
use App\Http\Controllers\form_elements\BasicInput;
use App\Http\Controllers\form_elements\InputGroups;
use App\Http\Controllers\form_layouts\VerticalForm;
use App\Http\Controllers\form_layouts\HorizontalForm;
use App\Http\Controllers\tables\Basic as TablesBasic;

// Main Page Route
Route::get('/', [Analytics::class, 'index'])->name('dashboard-analytics');

// layout
Route::get('/layouts/without-menu', [WithoutMenu::class, 'index'])->name('layouts-without-menu');
Route::get('/layouts/without-navbar', [WithoutNavbar::class, 'index'])->name('layouts-without-navbar');
Route::get('/layouts/fluid', [Fluid::class, 'index'])->name('layouts-fluid');
Route::get('/layouts/container', [Container::class, 'index'])->name('layouts-container');
Route::get('/layouts/blank', [Blank::class, 'index'])->name('layouts-blank');

// pages
Route::get('/pages/account-settings-account', [AccountSettingsAccount::class, 'index'])->name('pages-account-settings-account');
Route::get('/pages/account-settings-notifications', [AccountSettingsNotifications::class, 'index'])->name('pages-account-settings-notifications');
Route::get('/pages/account-settings-connections', [AccountSettingsConnections::class, 'index'])->name('pages-account-settings-connections');
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
Route::get('/pages/misc-under-maintenance', [MiscUnderMaintenance::class, 'index'])->name('pages-misc-under-maintenance');

// authentication
Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');
Route::get('/auth/forgot-password-basic', [ForgotPasswordBasic::class, 'index'])->name('auth-reset-password-basic');

// cards
Route::get('/cards/basic', [CardBasic::class, 'index'])->name('cards-basic');

// User Interface
Route::get('/ui/accordion', [Accordion::class, 'index'])->name('ui-accordion');
Route::get('/ui/alerts', [Alerts::class, 'index'])->name('ui-alerts');
Route::get('/ui/badges', [Badges::class, 'index'])->name('ui-badges');
Route::get('/ui/buttons', [Buttons::class, 'index'])->name('ui-buttons');
Route::get('/ui/carousel', [Carousel::class, 'index'])->name('ui-carousel');
Route::get('/ui/collapse', [Collapse::class, 'index'])->name('ui-collapse');
Route::get('/ui/dropdowns', [Dropdowns::class, 'index'])->name('ui-dropdowns');
Route::get('/ui/footer', [Footer::class, 'index'])->name('ui-footer');
Route::get('/ui/list-groups', [ListGroups::class, 'index'])->name('ui-list-groups');
Route::get('/ui/modals', [Modals::class, 'index'])->name('ui-modals');
Route::get('/ui/navbar', [Navbar::class, 'index'])->name('ui-navbar');
Route::get('/ui/offcanvas', [Offcanvas::class, 'index'])->name('ui-offcanvas');
Route::get('/ui/pagination-breadcrumbs', [PaginationBreadcrumbs::class, 'index'])->name('ui-pagination-breadcrumbs');
Route::get('/ui/progress', [Progress::class, 'index'])->name('ui-progress');
Route::get('/ui/spinners', [Spinners::class, 'index'])->name('ui-spinners');
Route::get('/ui/tabs-pills', [TabsPills::class, 'index'])->name('ui-tabs-pills');
Route::get('/ui/toasts', [Toasts::class, 'index'])->name('ui-toasts');
Route::get('/ui/tooltips-popovers', [TooltipsPopovers::class, 'index'])->name('ui-tooltips-popovers');
Route::get('/ui/typography', [Typography::class, 'index'])->name('ui-typography');

// extended ui
Route::get('/extended/ui-perfect-scrollbar', [PerfectScrollbar::class, 'index'])->name('extended-ui-perfect-scrollbar');
Route::get('/extended/ui-text-divider', [TextDivider::class, 'index'])->name('extended-ui-text-divider');

// icons
Route::get('/icons/icons-ri', [RiIcons::class, 'index'])->name('icons-ri');

// form elements
Route::get('/forms/basic-inputs', [BasicInput::class, 'index'])->name('forms-basic-inputs');
Route::get('/forms/input-groups', [InputGroups::class, 'index'])->name('forms-input-groups');

// form layouts
Route::get('/form/layouts-vertical', [VerticalForm::class, 'index'])->name('form-layouts-vertical');
Route::get('/form/layouts-horizontal', [HorizontalForm::class, 'index'])->name('form-layouts-horizontal');

// tables
Route::get('/tables/basic', [TablesBasic::class, 'index'])->name('tables-basic');

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PoController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvestasiController;
use App\Http\Controllers\UserController;
use App\Models\Po;

Route::middleware(['guest'])->group(function () {
  Route::get('/', [AuthController::class, 'showLogin'])->name('login'); // Name this 'login' for Laravel redirects
  Route::get('/login', [AuthController::class, 'showLogin']);
  Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['auth'])->group(function () {

  Route::get('/dashboard', [DashboardController::class, 'showDashboard'])->name('dashboard');
  Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

  Route::prefix('transactions')->group(function () {
    // Purchase Orders

    // Index Page
    Route::get('purchase-orders', [PoController::class, 'index'])->name('po.index');

    // Create purchase order
    Route::get('/purchase-order/create', [PoController::class, 'create'])->name('po.create');

    // Store purchase order
    Route::post('/purchase-order/store', [PoController::class, 'store'])
      ->name('po.store');

    // Make incoming purchase orders open
    Route::get('/purchase-order/details/{id}', [PoController::class, 'getIncomingDetails'])->name('po.incoming-details');

    // Get Details
    Route::get('/purchase-order/{po_id}', [PoController::class, 'showPoDetails'])->name('po.show');

    // Edit purchase order
    Route::get('/purchase-order/{po_id}/edit', [PoController::class, 'editPo'])->name('po.edit');

    // Update purchase order
    Route::put('/purchase-order/{id}', [PoController::class, 'updatePo'])->name('po.update');

    // Delete purchase order 
    Route::delete('/purchase-order/{po_id}', [PoController::class, 'destroyPo'])->name('po.destroy');

    // Purchase Orders End

    // Invoices
    Route::resource('invoices', InvoiceController::class)->names('invoice');
    // Invoices End

    // Deliveries
    Route::resource('deliveries', DeliveryController::class)->names('delivery');
    Route::put('transactions/deliveries/{delivery}', [DeliveryController::class, 'update'])->name('delivery.update');
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
  });

  Route::prefix('financials')->group(function () {
    Route::resource('investments', InvestasiController::class)->names('investments');
    Route::resource('payments', PaymentController::class)->names('payment');
  });

  Route::prefix('master')->group(function () {
    Route::resource('customers', CustomerController::class)->names('customer');
  });

  Route::prefix('settings')->group(function () {
    Route::resource('users', UserController::class)->names('users');
  });

  // Automations
  Route::post('/delivery/{id}/auto-deliver', [DeliveryController::class, 'autoDeliver'])->name('delivery.autoDeliver');
  // Automations

  // Card Datas
  // Incoming POs
  Route::get('/api/dashboard-stats', function () {
    return response()->json([
      // Count only POs where status is 0
      'incoming' => Po::where('status', 0)->count(),

      // Sum 'total' column for status 0
      'price'    => Po::where('status', 0)->sum('total'),

      // Sum 'modal_awal' column for status 0
      'capital'  => Po::where('status', 0)->sum('modal_awal'),

      // Sum 'margin' column for status 0
      'margin'   => Po::where('status', 0)->sum('margin'),
    ]);
  })->name('api.dashboard.stats');
  // Incoming POs
  // Purchase Orders
  Route::get('/api/po-stats', function () {
    return response()->json([
      // Count only POs where status is 0
      'incoming' => Po::where('status', '!=', 0)->count(),

      // Sum 'total' column for status 0
      'price'    => Po::where('status', '!=', 0)->sum('total'),

      // Sum 'modal_awal' column for status 0
      'capital'  => Po::where('status', '!=', 0)->sum('modal_awal'),

      // Sum 'margin' column for status 0
      'margin'   => Po::where('status', '!=', 0)->sum('margin'),
    ]);
  })->name('api.po.stats');
  // Purchase Orders
  // Card Datas

});
