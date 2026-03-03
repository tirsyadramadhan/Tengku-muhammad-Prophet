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
use App\Models\Po;
use App\Models\Investasi;
use App\Models\Invoice;
use App\Models\Margin;
use Illuminate\Http\Request;

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

    // Refresh PO
    Route::get('/po/refresh', [PoController::class, 'refresh'])->name('po.refresh');
    // Export PO
    Route::get('/po/export', [PoController::class, 'export'])->name('po.export');
    // Import PO
    Route::get('/po/import', [PoController::class, 'importForm'])->name('po.importForm');
    Route::post('/po/import', [PoController::class, 'import'])->name('po.import');
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
    Route::get('investments/import', [InvestasiController::class, 'importForm'])->name('investments.importForm');
    Route::post('investments/import', [InvestasiController::class, 'import'])->name('investments.import');
    Route::resource('investments', InvestasiController::class)->names('investments');
    Route::resource('payments', PaymentController::class)->names('payment');
  });

  Route::prefix('master')->group(function () {
    Route::resource('customers', CustomerController::class)->names('customer');
    Route::resource('users', UserController::class)->names('users');
  });

  // Automations
  Route::post('/delivery/{id}/auto-deliver', [DeliveryController::class, 'autoDeliver'])->name('delivery.autoDeliver');
  // Automations

  Route::get('/api/investasi-stats', function () {
    $totalMargin      = Investasi::sum('margin');
    $totalModalSetor  = Investasi::sum('modal_setor_awal');
    $totalPenarikan   = Investasi::sum('pengembalian_dana');
    $totalModalPoBaru = Investasi::sum('modal_po_baru');
    $investasi = Investasi::orderBy('id_investasi', 'desc')->first();
    $danaTersedia = 0;
    if ($investasi) {
      $danaTersedia = $investasi->dana_tersedia;
    }
    return response()->json([
      'totalMargin'      => (float) $totalMargin,
      'totalModalSetor'  => (float) $totalModalSetor,
      'totalModalPoBaru' => (float) $totalModalPoBaru,
      'totalPenarikan'   => (float) $totalPenarikan,
      'danaTersedia'     => (float) $danaTersedia,
    ]);
  })->name('api.investasi.stats');
  // Card Datas
  // Dashboard
  Route::get('/api/dashboard-stats', function () {
    // 1. Pre-calculate Investment Sums
    $invSums = Investasi::query()
      ->selectRaw('SUM(modal_setor_awal) as total_awal, SUM(modal_po_baru) as total_po, SUM(margin_cair) as total_tarik, SUM(pengembalian_dana) as total_tf')
      ->first();

    // 2. Pre-calculate PO Margin Sums (Using constants for status is even better)
    $totalMargin = Po::where('status', '!=', 0)->sum('margin');
    $marginDitahan = Po::where('status', '!=', 0)->where('status', '!=', 8)->sum('margin');

    // 3. Pre-calculate Margin Table Sums
    $marginSums = Margin::query()
      ->selectRaw('SUM(investasi_dikembalikan) as dikembalikan, SUM(margin_diterima) as diterima, SUM(margin_tersedia) as tersedia')
      ->first();

    // 4. Compute Complex Formulas
    $sisaMargin   = $totalMargin - $marginSums->diterima;
    $marginTersedia = $sisaMargin - $marginDitahan;
    $investasiDitahan = Po::where('status', '!=', 0)->where('status', '!=', 8)->sum('modal_awal') - 77334100;
    $totalInvestasiTransfer = $invSums->total_awal + $marginSums->dikembalikan - $investasiDitahan;
    return response()->json([
      'danaTersedia'          => $totalInvestasiTransfer + $marginTersedia,
      'totalDanaDitf'         => $invSums->total_tf,
      'investasiDikembalikan' => $marginSums->dikembalikan,
      'totalTfInvestasi'      => $invSums->total_awal,
      'marginDiterima'        => $marginSums->diterima,
      'totalMargin'           => $totalMargin,
      'sisaMargin'            => $sisaMargin,
      'marginTersedia'        => $marginTersedia,
      'investasiDitahan'      => $investasiDitahan,
      'marginDitahan'         => $marginDitahan,
      'totalInvestasiTransfer' => $totalInvestasiTransfer
    ]);
  })->name('api.dashboard.stats');
  // Dashboard
  // Incoming POs
  Route::get('/api/incomingPo-stats', function () {
    // We run one query to get all aggregates at once
    $stats = Po::where('status', 0)
      ->selectRaw('
            COUNT(*) as incoming, 
            SUM(total) as price, 
            SUM(modal_awal) as capital, 
            SUM(margin) as margin
        ')
      ->first();

    return response()->json([
      'incoming' => (int) $stats->incoming,
      'price'    => (float) ($stats->price ?? 0),
      'capital'  => (float) ($stats->capital ?? 0),
      'margin'   => (float) ($stats->margin ?? 0),
    ]);
  })->name('api.incomingPo.stats');


  Route::get('/api/dashboard-filtered-stats', function (Request $request) {

    // ----------------------------------------------------------------
    // 1. VALIDATE INPUTS
    // ----------------------------------------------------------------
    $startDate = $request->query('startDate');   // e.g. "2025-01-01"
    $endDate   = $request->query('endDate');     // e.g. "2025-12-31"

    // Statuses in scope: 2,3,4,5,6,7  (excludes Incoming=0, Open=1, Closed=8)
    $activeStatuses = [
      Po::STATUS_PARTIALLY_DELIVERED,                         // 2
      Po::STATUS_FULLY_DELIVERED,                             // 3
      Po::STATUS_PARTIALLY_DELIVERED_PARTIALLY_INVOICED,     // 4
      Po::STATUS_FULLY_DELIVERED_PARTIALLY_INVOICED,         // 5
      Po::STATUS_PARTIALLY_DELIVERED_FULLY_INVOICED,         // 6
      Po::STATUS_FULLY_DELIVERED_FULLY_INVOICED,             // 7
    ];

    // ----------------------------------------------------------------
    // 2. BASE PO QUERY  (filtered by tgl_po date range + active statuses)
    // ----------------------------------------------------------------
    $poQuery = Po::whereIn('status', $activeStatuses);

    if ($startDate) {
      $poQuery->whereDate('tgl_po', '>=', $startDate);
    }
    if ($endDate) {
      $poQuery->whereDate('tgl_po', '<=', $endDate);
    }

    // ----------------------------------------------------------------
    // 3. MARGIN & CAPITAL AGGREGATES  (from tbl_po)
    // ----------------------------------------------------------------
    $poAggregates = (clone $poQuery)
      ->selectRaw('
            SUM(margin)      AS total_margin,
            SUM(modal_awal)  AS total_modal,
            SUM(total)       AS total_nilai_po,
            COUNT(*)         AS total_po
        ')
      ->first();

    // ----------------------------------------------------------------
    // 4. INVOICE STATUS BREAKDOWN
    //    Join through tbl_delivery → tbl_po so we respect the date filter
    //    Filter tgl_invoice by the same date range
    // ----------------------------------------------------------------
    $invoiceQuery = Invoice::query()
      ->join('tbl_delivery', 'tbl_invoice.delivery_id', '=', 'tbl_delivery.delivery_id')
      ->join('tbl_po',       'tbl_delivery.po_id',      '=', 'tbl_po.po_id')
      ->whereIn('tbl_po.status', $activeStatuses);

    // Date filter applies to tgl_invoice
    if ($startDate) {
      $invoiceQuery->whereDate('tbl_invoice.tgl_invoice', '>=', $startDate);
    }
    if ($endDate) {
      $invoiceQuery->whereDate('tbl_invoice.tgl_invoice', '<=', $endDate);
    }

    $invoiceCounts = (clone $invoiceQuery)
      ->selectRaw('
            COUNT(*)                                              AS total_invoice,
            SUM(CASE WHEN tbl_invoice.status_invoice = 0 THEN 1 ELSE 0 END) AS unpaid,
            SUM(CASE WHEN tbl_invoice.status_invoice = 1 THEN 1 ELSE 0 END) AS paid,
            SUM(CASE WHEN tbl_invoice.status_invoice = 2 THEN 1 ELSE 0 END) AS cancelled
        ')
      ->first();

    // ----------------------------------------------------------------
    // 5. PO STATUS BREAKDOWN  (count per status label)
    // ----------------------------------------------------------------
    $statusLabels = [
      2 => 'Partially Delivered',
      3 => 'Fully Delivered',
      4 => 'Partial Del. / Partial Inv.',
      5 => 'Full Del. / Partial Inv.',
      6 => 'Partial Del. / Full Inv.',
      7 => 'Full Del. / Full Inv. (Unpaid)',
    ];

    $poStatusCounts = (clone $poQuery)
      ->selectRaw('status, COUNT(*) as cnt')
      ->groupBy('status')
      ->pluck('cnt', 'status')
      ->toArray();

    $poStatusBreakdown = [];
    foreach ($statusLabels as $code => $label) {
      $poStatusBreakdown[] = [
        'status' => $code,
        'label'  => $label,
        'count'  => $poStatusCounts[$code] ?? 0,
      ];
    }

    // ----------------------------------------------------------------
    // 6. RETURN JSON
    // ----------------------------------------------------------------
    return response()->json([
      // --- PO Financials ---
      'totalPo'        => (int)   ($poAggregates->total_po       ?? 0),
      'totalNilaiPo'   => (float) ($poAggregates->total_nilai_po ?? 0),
      'totalMargin'    => (float) ($poAggregates->total_margin   ?? 0),
      'totalModal'     => (float) ($poAggregates->total_modal    ?? 0),

      // --- Invoice Counts ---
      'totalInvoice'   => (int)   ($invoiceCounts->total_invoice ?? 0),
      'invoiceUnpaid'  => (int)   ($invoiceCounts->unpaid        ?? 0),
      'invoicePaid'    => (int)   ($invoiceCounts->paid          ?? 0),
      'invoiceCancelled' => (int) ($invoiceCounts->cancelled     ?? 0),

      // --- Per-Status Breakdown ---
      'statusBreakdown' => $poStatusBreakdown,

      // --- Echo back the filter so the front-end can display it ---
      'filter' => [
        'startDate' => $startDate,
        'endDate'   => $endDate,
      ],
    ]);
  })->name('api.dashboard.filtered-stats');
  // Incoming POs
  // Purchase Orders
  Route::get('/api/po-stats', function () {
    // One query to rule them all
    $stats = Po::where('status', '!=', 0)
      ->selectRaw('
            COUNT(*) as incoming, 
            SUM(total) as price, 
            SUM(modal_awal) as capital, 
            SUM(margin) as margin
        ')
      ->first();

    return response()->json([
      'incoming' => (int) $stats->incoming,
      'price'    => (float) ($stats->price ?? 0),
      'capital'  => (float) ($stats->capital ?? 0),
      'margin'   => (float) ($stats->margin ?? 0),
    ]);
  })->name('api.po.stats');
  // Purchase Orders
  // Card Datas

});
