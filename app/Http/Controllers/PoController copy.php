<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Po;
use App\Models\Invoice;
use App\Models\Customer;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Route;
use App\Exports\PoExport;
use App\Imports\PoImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PoController extends Controller
{
    use ActivityLogger;

    public function createIncoming()
    {
        // If you need to pass data like customers to the form:
        $customers = \App\Models\Customer::all();

        // Return the specific view for creating Incoming POs
        return view('incoming-po-create', compact('customers'));
    }
    // In App\Http\Controllers\PoController.php

    public function storeIncoming(Request $request)
    {
        // 1. Validation (Keep as is)
        $validator = Validator::make($request->all(), [
            'nama_barang'       => 'required|string',
            'customer_id'       => 'required|exists:tbl_customer,id_cust',
            'tgl_po'            => 'required|date',
            'qty'               => 'required|numeric|min:1',
            'harga'             => 'required|numeric|min:0',
            'margin_percentage' => 'nullable|numeric|min:0',
            'tambahan_margin'   => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get all PO numbers with status 0
        $allPos = Po::where('status', 0)->get();

        // Initialize array to store the extracted numbers
        $extractedNumbers = [];

        foreach ($allPos as $po) {
            // Remove the "52010xxxx" prefix to get just the sequential number
            $number = str_replace('52010xxxx', '', $po->no_po);

            // Convert to integer and add to array
            $extractedNumbers[] = (int) $number;
        }

        // Sort the numbers (ascending)
        sort($extractedNumbers);

        // Get the highest number (last element after sorting)
        $highestNumber = !empty($extractedNumbers) ? end($extractedNumbers) : 0;

        // Increment by 1
        $nextNumber = $highestNumber + 1;

        // Append back to the prefix
        $generatedNoPo = '52010xxxx' . $nextNumber;

        $qty = (float) $request->qty;
        $harga = (float) $request->harga;
        $tambahan = (float) ($request->tambahan_margin ?? 0);

        $total = $qty * $harga;
        $modal = $total / 2;
        $percentageMargin = $request->margin_percentage;
        $finalMargin = ($modal * ($percentageMargin / 100)) + $tambahan;
        $data = $validator->validated();
        $data['no_po'] = $generatedNoPo;
        $data['status'] = 0;
        $data['margin'] = $finalMargin;
        $data['tambahan_margin'] = $tambahan;

        // 5. Create
        try {
            $incomingPo = Po::create($data);
            $this->logCreate($incomingPo, 'Incoming PO dengan Nomor PO ' . $generatedNoPo . ' Dan Nama Barang ' . $request->nama_barang . ' Ditambahkan');
            return response()->json(['success' => true, 'message' => 'Created successfully', 'redirect_url' => route('incomingPo')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function incomingPo(Request $request)
    {
        if ($request->ajax()) {
            $query = Po::with('customer')
                ->select('tbl_po.*')
                ->where('status', 0);

            $totals = Po::where('status', 0)->selectRaw(
                '
                SUM(qty)       as qty,
                SUM(total)     as total,
                SUM(modal_awal) as modal_awal,
                SUM(margin)    as margin
            '
            )->first();
            return DataTables::of($query)
                // 2. No Column: Automatically generates sequence 1..Max
                ->addIndexColumn()
                ->editColumn('detail_po', function ($row) {
                    $statusVal = $row->status;
                    $statusMap = [
                        0 => ['label' => 'Incoming',                                'class' => 'status-incoming',   'icon' => 'ri-download-line',         'color' => '#f59e0b'],
                        1 => ['label' => 'Open',                                    'class' => 'status-open',       'icon' => 'ri-mail-send-line',        'color' => '#f59e0b'],
                        2 => ['label' => 'Partially Delivered',                     'class' => 'status-partial',    'icon' => 'ri-truck-line',            'color' => '#3b82f6'],
                        3 => ['label' => 'Fully Delivered',                         'class' => 'status-delivered',  'icon' => 'ri-checkbox-circle-line',  'color' => '#3b82f6'],
                        4 => ['label' => 'Partially Del. & Partially Inv.',         'class' => 'status-mixed',      'icon' => 'ri-exchange-box-line',     'color' => '#8b5cf6'],
                        5 => ['label' => 'Fully Del. & Partially Inv.',             'class' => 'status-mixed',      'icon' => 'ri-draft-line',            'color' => '#8b5cf6'],
                        6 => ['label' => 'Partially Del. & Fully Inv.',             'class' => 'status-mixed',      'icon' => 'ri-file-warning-line',     'color' => '#8b5cf6'],
                        7 => ['label' => 'Fully Delivered & Fully Invoiced',        'class' => 'status-complete',   'icon' => 'ri-check-double-line',     'color' => '#10b981'],
                        8 => ['label' => 'Closed',                                  'class' => 'status-closed',     'icon' => 'ri-verified-badge-fill',   'color' => '#10b981'],
                    ];
                    $default = ['label' => 'Unknown', 'class' => 'status-unknown', 'icon' => 'ri-question-line', 'color' => '#9ca3af'];
                    $map = $statusMap[$statusVal] ?? $default;

                    $tglPo    = Carbon::parse($row->tgl_po);
                    $relative = $tglPo->toIndonesianRelative();
                    $formated = $tglPo->format('d M Y');

                    return '
                        <style>
                            .po-card {
                                display: flex;
                                flex-direction: column;
                                gap: 0;
                                min-width: 220px;
                                max-width: 260px;
                                background: #ffffff;
                                border: 1px solid #e5e7eb;
                                border-radius: 12px;
                                overflow: hidden;
                                box-shadow: 0 1px 4px rgba(0,0,0,0.06);
                                font-family: inherit;
                            }
                            .po-card-header {
                                padding: 10px 14px 8px;
                                background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
                                border-bottom: 1px solid #e5e7eb;
                                display: flex;
                                align-items: center;
                                gap: 8px;
                            }
                            .po-card-header .po-number {
                                font-size: 0.8rem;
                                font-weight: 700;
                                color: #1e293b;
                                letter-spacing: 0.02em;
                            }
                            .po-card-header .po-icon {
                                width: 28px;
                                height: 28px;
                                background: #dbeafe;
                                border-radius: 8px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 0.85rem;
                                color: #3b82f6;
                                flex-shrink: 0;
                            }
                            .po-card-body {
                                padding: 10px 14px;
                                display: flex;
                                flex-direction: column;
                                gap: 8px;
                            }
                            .po-row {
                                display: flex;
                                align-items: flex-start;
                                gap: 8px;
                            }
                            .po-row-icon {
                                width: 22px;
                                height: 22px;
                                border-radius: 6px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 0.75rem;
                                flex-shrink: 0;
                                margin-top: 1px;
                            }
                            .po-row-content {
                                display: flex;
                                flex-direction: column;
                                gap: 1px;
                            }
                            .po-row-label {
                                font-size: 0.6rem;
                                font-weight: 600;
                                text-transform: uppercase;
                                letter-spacing: 0.06em;
                                color: #94a3b8;
                            }
                            .po-row-value {
                                font-size: 0.82rem;
                                font-weight: 600;
                                color: #1e293b;
                                line-height: 1.3;
                            }
                            .po-relative-badge {
                                display: inline-block;
                                font-size: 0.62rem;
                                font-weight: 600;
                                padding: 1px 7px;
                                border-radius: 20px;
                                background: #e0f2fe;
                                color: #0369a1;
                                margin-top: 2px;
                            }
                            .po-divider {
                                height: 1px;
                                background: #f1f5f9;
                                margin: 0 -14px;
                            }
                            .po-status-row {
                                padding: 8px 14px 10px;
                                background: #fafafa;
                                border-top: 1px solid #f1f5f9;
                            }
                            .po-status-chip {
                                display: inline-flex;
                                align-items: center;
                                gap: 5px;
                                padding: 4px 10px 4px 7px;
                                border-radius: 20px;
                                font-size: 0.72rem;
                                font-weight: 600;
                                letter-spacing: 0.01em;
                                border: 1.5px solid;
                            }
                        </style>
                        <div class="po-card shadow-lg">
                            <!-- Header: No PO -->
                            <div class="po-card-header">
                                <div class="po-icon"><i class="ri-file-list-3-line"></i></div>
                                <div>
                                    <div style="font-size:0.6rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;">No. PO</div>
                                    <div class="po-number">' . e($row->no_po) . '</div>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="po-card-body">

                                <!-- Tanggal PO -->
                                <div class="po-row">
                                    <div class="po-row-icon" style="background:#dcfce7;color:#16a34a;">
                                        <i class="ri-calendar-check-line"></i>
                                    </div>
                                    <div class="po-row-content">
                                        <span class="po-row-label">Tanggal PO</span>
                                        <span class="po-row-value">' . $formated . '</span>
                                        <span class="po-relative-badge">' . $relative . '</span>
                                    </div>
                                </div>

                                <div class="po-divider"></div>

                                <!-- Nama Barang -->
                                <div class="po-row">
                                    <div class="po-row-icon" style="background:#ede9fe;color:#7c3aed;">
                                        <i class="ri-box-3-line"></i>
                                    </div>
                                    <div class="po-row-content">
                                        <span class="po-row-label">Nama Barang</span>
                                        <span class="po-row-value">' . e($row->nama_barang) . '</span>
                                    </div>
                                </div>

                                <div class="po-divider"></div>

                                <!-- Kuantitas -->
                                <div class="po-row">
                                    <div class="po-row-icon" style="background:#e0f2fe;color:#0284c7;">
                                        <i class="ri-stack-line"></i>
                                    </div>
                                    <div class="po-row-content">
                                        <span class="po-row-label">Kuantitas</span>
                                        <span class="po-row-value">' . number_format((int) $row->qty, 0, ',', '.') . ' Unit</span>
                                    </div>
                                </div>

                            </div>

                            <!-- Status Footer -->
                            <div class="po-status-row">
                                <div style="font-size:0.6rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;margin-bottom:5px;">Status PO</div>
                                <span class="po-status-chip" style="background:' . $map['color'] . '18;color:' . $map['color'] . ';border-color:' . $map['color'] . '40;">
                                    <i class="' . $map['icon'] . '" style="font-size:0.8rem;"></i>
                                    ' . $map['label'] . '
                                </span>
                            </div>
                        </div>
                        ';
                })
                ->editColumn('price_references', function ($row) {
                    $total  = (float) ($row->total      ?? 0);
                    $harga  = (float) ($row->harga      ?? 0);
                    $modal  = (float) ($row->modal_awal ?? 0);
                    $qty    = (int)   ($row->qty        ?? 0);
                    $fmt = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');

                    return '
                        <style>
                            .prc-card {
                                display: flex;
                                flex-direction: column;
                                gap: 0;
                                min-width: 240px;
                                max-width: 280px;
                                background: #ffffff;
                                border: 1px solid #e5e7eb;
                                border-radius: 12px;
                                overflow: hidden;
                                box-shadow: 0 1px 4px rgba(0,0,0,0.06);
                                font-family: inherit;
                            }
                            .prc-hero {
                                padding: 12px 14px 10px;
                                background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                                display: flex;
                                align-items: center;
                                gap: 10px;
                            }
                            .prc-hero-icon {
                                width: 32px;
                                height: 32px;
                                background: rgba(255,255,255,0.1);
                                border-radius: 8px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 1rem;
                                color: #34d399;
                                flex-shrink: 0;
                            }
                            .prc-hero-label {
                                font-size: 0.58rem;
                                font-weight: 600;
                                text-transform: uppercase;
                                letter-spacing: 0.07em;
                                color: #64748b;
                            }
                            .prc-hero-value {
                                font-size: 1rem;
                                font-weight: 800;
                                color: #34d399;
                                letter-spacing: 0.01em;
                                line-height: 1.2;
                            }
                            .prc-card-body {
                                padding: 10px 14px;
                                display: flex;
                                flex-direction: column;
                                gap: 8px;
                            }
                            .prc-row {
                                display: flex;
                                align-items: flex-start;
                                gap: 8px;
                            }
                            .prc-row-icon {
                                width: 22px;
                                height: 22px;
                                border-radius: 6px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 0.75rem;
                                flex-shrink: 0;
                                margin-top: 1px;
                            }
                            .prc-row-content {
                                display: flex;
                                flex-direction: column;
                                gap: 1px;
                            }
                            .prc-row-label {
                                font-size: 0.6rem;
                                font-weight: 600;
                                text-transform: uppercase;
                                letter-spacing: 0.06em;
                                color: #94a3b8;
                            }
                            .prc-row-value {
                                font-size: 0.82rem;
                                font-weight: 700;
                                color: #1e293b;
                                line-height: 1.3;
                            }
                            .prc-row-sub {
                                font-size: 0.68rem;
                                font-weight: 500;
                                color: #64748b;
                                margin-top: 1px;
                            }
                            .prc-divider {
                                height: 1px;
                                background: #f1f5f9;
                                margin: 0 -14px;
                            }
                            .prc-metrics-grid {
                                display: grid;
                                grid-template-columns: 1fr 1fr;
                                gap: 6px;
                            }
                            .prc-metric-cell {
                                display: flex;
                                flex-direction: column;
                                gap: 2px;
                                padding: 6px 8px;
                                border-radius: 8px;
                                border: 1px solid;
                            }
                            .prc-metric-label {
                                font-size: 0.58rem;
                                font-weight: 600;
                                text-transform: uppercase;
                                letter-spacing: 0.06em;
                                color: #94a3b8;
                            }
                            .prc-metric-value {
                                font-size: 0.8rem;
                                font-weight: 800;
                                line-height: 1.2;
                            }
                            .prc-footer {
                                padding: 8px 14px 10px;
                                border-top: 1.5px solid;
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                            }
                            .prc-chip {
                                display: inline-flex;
                                align-items: center;
                                gap: 5px;
                                padding: 4px 10px 4px 7px;
                                border-radius: 20px;
                                font-size: 0.72rem;
                                font-weight: 700;
                                border: 1.5px solid;
                            }
                        </style>
                        <div class="prc-card shadow-lg">

                            <!-- Hero: Total Harga PO -->
                            <div class="prc-hero">
                                <div class="prc-hero-icon">
                                    <i class="ri-money-dollar-circle-line"></i>
                                </div>
                                <div class="d-flex flex-column gap-0">
                                    <span class="prc-hero-label">Total Harga PO</span>
                                    <span class="prc-hero-value">' . $fmt($total) . '</span>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="prc-card-body">

                                <!-- Harga Per Unit -->
                                <div class="prc-row">
                                    <div class="prc-row-icon" style="background:#ede9fe;color:#7c3aed;">
                                        <i class="ri-price-tag-3-line"></i>
                                    </div>
                                    <div class="prc-row-content">
                                        <span class="prc-row-label">Harga Per Unit</span>
                                        <span class="prc-row-value">' . $fmt($harga) . '</span>
                                        ' . ($qty > 0 ? '<span class="prc-row-sub"><i class="ri-stack-line me-1"></i>' . number_format($qty, 0, ',', '.') . ' Unit</span>' : '') . '
                                    </div>
                                </div>

                                <div class="prc-divider"></div>

                                <!-- Modal PO -->
                                <div class="prc-row">
                                    <div class="prc-row-icon" style="background:#e0f2fe;color:#0284c7;">
                                        <i class="ri-safe-line"></i>
                                    </div>
                                    <div class="prc-row-content">
                                        <span class="prc-row-label">Modal PO</span>
                                        <span class="prc-row-value">' . $fmt($modal) . '</span>
                                        ' . ($qty > 0 ? '<span class="prc-row-sub"><i class="ri-divide-line me-1"></i>' . $fmt($qty > 0 ? $modal / $qty : 0) . ' / unit</span>' : '') . '
                                    </div>
                                </div>
                            </div>
                        </div>
                        ';
                })
                ->editColumn('margin_references', function ($row) {
                    $margin         = (float) ($row->margin          ?? 0);
                    $marginUnit     = (float) ($row->margin_unit     ?? 0);
                    $tambahanMargin = (float) ($row->tambahan_margin ?? 0);
                    $modal_awal     = (float) ($row->modal_awal      ?? 0);
                    $qty            = (int)   ($row->qty             ?? 0);

                    // ── Margin Calculations ───────────────────────────────────
                    $marginPct     = $modal_awal > 0 ? round(($margin / $modal_awal) * 100, 1) : 0;
                    $marginUnitPct = $modal_awal > 0 ? round(($marginUnit / $modal_awal) * 100, 1) : 0;
                    $totalWithExtra = $modal_awal * ($marginPct / 100) + $tambahanMargin;
                    $extraPct      = $margin > 0 ? round(($tambahanMargin / $modal_awal) * 100, 1) : 0;

                    // ── Margin Health Thresholds ──────────────────────────────
                    $health = match (true) {
                        $marginPct >= 20 => ['color' => '#10b981', 'bg' => '#dcfce7', 'border' => '#bbf7d0', 'label' => 'Margin Normal',  'icon' => 'ri-arrow-up-circle-fill',  'bar' => '#10b981'],
                        $marginPct >= 10 => ['color' => '#f59e0b', 'bg' => '#fef3c7', 'border' => '#fde68a', 'label' => 'Margin Tipis',  'icon' => 'ri-error-warning-line',     'bar' => '#f59e0b'],
                        default          => ['color' => '#ef4444', 'bg' => '#fee2e2', 'border' => '#fecaca', 'label' => 'Margin Buruk',  'icon' => 'ri-arrow-down-circle-fill', 'bar' => '#ef4444'],
                    };

                    // ── Tambahan Margin State ─────────────────────────────────
                    $hasExtra     = $tambahanMargin > 0;
                    $extraColor   = $hasExtra ? '#10b981' : '#9ca3af';
                    $extraBg      = $hasExtra ? '#dcfce7' : '#f1f5f9';
                    $extraIcon    = $hasExtra ? 'ri-add-circle-line' : 'ri-minus-circle-line';
                    $extraPrefix  = $hasExtra ? '+' : '';

                    $fmt = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');
                    $barWidth = min($marginPct, 100);

                    return '
                        <style>
                            .mrg-card {
                                display: flex;
                                flex-direction: column;
                                gap: 0;
                                min-width: 240px;
                                max-width: 280px;
                                background: #ffffff;
                                border: 1px solid #e5e7eb;
                                border-radius: 12px;
                                overflow: hidden;
                                box-shadow: 0 1px 4px rgba(0,0,0,0.06);
                                font-family: inherit;
                            }
                            .mrg-hero {
                                padding: 12px 14px 10px;
                                background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                                display: flex;
                                align-items: center;
                                gap: 10px;
                            }
                            .mrg-hero-icon {
                                width: 32px;
                                height: 32px;
                                background: rgba(255,255,255,0.1);
                                border-radius: 8px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 1rem;
                                flex-shrink: 0;
                            }
                            .mrg-hero-label {
                                font-size: 0.58rem;
                                font-weight: 600;
                                text-transform: uppercase;
                                letter-spacing: 0.07em;
                                color: #64748b;
                            }
                            .mrg-hero-value {
                                font-size: 1rem;
                                font-weight: 800;
                                letter-spacing: 0.01em;
                                line-height: 1.2;
                            }
                            .mrg-pct-pill {
                                margin-left: auto;
                                padding: 3px 9px;
                                border-radius: 20px;
                                font-size: 0.75rem;
                                font-weight: 800;
                                border: 1.5px solid;
                                white-space: nowrap;
                            }
                            .mrg-progress-wrap {
                                padding: 0 14px 10px;
                                background: #0f172a;
                                display: flex;
                                flex-direction: column;
                                gap: 4px;
                            }
                            .mrg-progress-track {
                                width: 100%;
                                height: 6px;
                                background: rgba(255,255,255,0.1);
                                border-radius: 999px;
                                overflow: hidden;
                            }
                            .mrg-progress-fill {
                                height: 100%;
                                border-radius: 999px;
                                transition: width 0.4s ease;
                            }
                            .mrg-progress-labels {
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                            }
                            .mrg-card-body {
                                padding: 10px 14px;
                                display: flex;
                                flex-direction: column;
                                gap: 8px;
                            }
                            .mrg-row {
                                display: flex;
                                align-items: flex-start;
                                gap: 8px;
                            }
                            .mrg-row-icon {
                                width: 22px;
                                height: 22px;
                                border-radius: 6px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 0.75rem;
                                flex-shrink: 0;
                                margin-top: 1px;
                            }
                            .mrg-row-content {
                                display: flex;
                                flex-direction: column;
                                gap: 1px;
                                flex: 1;
                            }
                            .mrg-row-label {
                                font-size: 0.6rem;
                                font-weight: 600;
                                text-transform: uppercase;
                                letter-spacing: 0.06em;
                                color: #94a3b8;
                            }
                            .mrg-row-value {
                                font-size: 0.82rem;
                                font-weight: 700;
                                color: #1e293b;
                                line-height: 1.3;
                            }
                            .mrg-row-sub {
                                font-size: 0.65rem;
                                font-weight: 500;
                                color: #64748b;
                                margin-top: 1px;
                            }
                            .mrg-divider {
                                height: 1px;
                                background: #f1f5f9;
                                margin: 0 -14px;
                            }
                            .mrg-badge {
                                display: inline-flex;
                                align-items: center;
                                padding: 2px 8px;
                                border-radius: 20px;
                                font-size: 0.68rem;
                                font-weight: 700;
                                border: 1.5px solid;
                                white-space: nowrap;
                                margin-left: auto;
                                align-self: flex-start;
                                margin-top: 2px;
                            }
                            .mrg-extra-row {
                                display: flex;
                                align-items: flex-start;
                                gap: 8px;
                                padding: 7px 10px;
                                border-radius: 8px;
                                border: 1.5px dashed;
                            }
                            .mrg-footer {
                                padding: 8px 14px 10px;
                                border-top: 1.5px solid;
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                            }
                            .mrg-chip {
                                display: inline-flex;
                                align-items: center;
                                gap: 5px;
                                padding: 4px 10px 4px 7px;
                                border-radius: 20px;
                                font-size: 0.72rem;
                                font-weight: 700;
                                border: 1.5px solid;
                            }
                        </style>
                        <div class="mrg-card">

                            <!-- Hero: Total Margin PO -->
                            <div class="mrg-hero">
                                <div class="mrg-hero-icon" style="color:' . $health['color'] . ';">
                                    <i class="ri-funds-line"></i>
                                </div>
                                <div class="d-flex flex-column gap-0" style="flex:1;">
                                    <span class="mrg-hero-label">Total Margin PO</span>
                                    <span class="mrg-hero-value" style="color:' . $health['color'] . ';">' . $fmt($margin) . '</span>
                                </div>
                                <span class="mrg-pct-pill" style="background:' . $health['color'] . '22;color:' . $health['color'] . ';border-color:' . $health['color'] . '55;">
                                    ' . $marginPct . '%
                                </span>
                            </div>

                            <!-- Progress Bar (inside dark hero zone) -->
                            <div class="mrg-progress-wrap">
                                <div class="mrg-progress-track">
                                    <div class="mrg-progress-fill" style="width:' . $barWidth . '%;background:' . $health['bar'] . ';"></div>
                                </div>
                                <div class="mrg-progress-labels">
                                    <span style="font-size:0.58rem;font-weight:600;color:#475569;">0%</span>
                                    <span style="font-size:0.58rem;font-weight:700;color:' . $health['color'] . ';">' . $health['label'] . '</span>
                                    <span style="font-size:0.58rem;font-weight:600;color:#475569;">100%</span>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="mrg-card-body">

                                <!-- Margin Per Unit -->
                                <div class="mrg-row">
                                    <div class="mrg-row-icon" style="background:#ede9fe;color:#7c3aed;">
                                        <i class="ri-price-tag-3-line"></i>
                                    </div>
                                    <div class="mrg-row-content">
                                        <span class="mrg-row-label">Margin Per Unit</span>
                                        <span class="mrg-row-value">' . $fmt($marginUnit) . '</span>
                                        ' . ($qty > 0 ? '<span class="mrg-row-sub"><i class="ri-stack-line me-1"></i>' . number_format($qty, 0, ',', '.') . ' Unit</span>' : '') . '
                                    </div>
                                    <span class="mrg-badge" style="background:#7c3aed18;color:#7c3aed;border-color:#7c3aed40;">
                                        ' . $marginUnitPct . '%
                                    </span>
                                </div>

                                <div class="mrg-divider"></div>

                                <!-- Tambahan Margin -->
                                <div class="mrg-extra-row" style="background:' . $extraBg . ';border-color:' . $extraColor . '40;">
                                    <div class="mrg-row-icon" style="background:' . $extraBg . ';color:' . $extraColor . ';border:1px solid ' . $extraColor . '30;">
                                        <i class="' . $extraIcon . '"></i>
                                    </div>
                                    <div class="mrg-row-content">
                                        <span class="mrg-row-label" style="color:' . $extraColor . '88;">Tambahan Margin</span>
                                        <span style="font-size:0.82rem;font-weight:700;color:' . $extraColor . ';">
                                            ' . $extraPrefix . $fmt($tambahanMargin) . '
                                        </span>
                                        ' . ($hasExtra ? '<span class="mrg-row-sub" style="color:' . $extraColor . ';">+' . $extraPct . '% dari margin dasar</span>' : '<span class="mrg-row-sub">Tidak ada tambahan</span>') . '
                                    </div>
                                </div>

                                <div class="mrg-divider"></div>

                                <!-- Total Margin Gabungan -->
                                <div class="mrg-row">
                                    <div class="mrg-row-icon" style="background:#fef3c7;color:#d97706;">
                                        <i class="ri-calculator-line"></i>
                                    </div>
                                    <div class="mrg-row-content">
                                        <span class="mrg-row-label">Total Margin Gabungan</span>
                                        <span class="mrg-row-value" style="color:#d97706;">' . $fmt($totalWithExtra) . '</span>
                                        ' . ($hasExtra ? '<span class="mrg-row-sub">Margin + Tambahan</span>' : '<span class="mrg-row-sub" style="color:#94a3b8;">Sama dengan margin dasar</span>') . '
                                    </div>
                                </div>

                            </div>

                            <!-- Footer: Health Indicator -->
                            <div class="mrg-footer" style="background:' . $health['bg'] . ';border-color:' . $health['border'] . ';">
                                <span class="mrg-chip" style="background:' . $health['color'] . '18;color:' . $health['color'] . ';border-color:' . $health['color'] . '40;">
                                    <i class="' . $health['icon'] . '" style="font-size:0.85rem;"></i>
                                    ' . $health['label'] . '
                                </span>
                                <span style="font-size:0.68rem;font-weight:800;color:' . $health['color'] . ';">
                                    ' . $marginPct . '% dari modal
                                </span>
                            </div>
                        </div>
                        ';
                })
                ->addColumn('action', function ($row) {
                    $showUrl = Route::has('incoming-po.show') ? route('incoming-po.show', $row->po_id) : '#';
                    $editUrl = Route::has('incoming-po.edit') ? route('incoming-po.edit', $row->po_id) : '#';
                    $deleteUrl = Route::has('incoming-po.destroy') ? route('incoming-po.destroy', $row->po_id) : '#';

                    $user = Auth::user();
                    $canEditDelete = $user && $user->role_id !== 2;

                    $editBtn = $canEditDelete ? '
                    <a href="' . $editUrl . '" class="btn btn-sm btn-icon btn-label-warning" title="Edit">
                        <i class="ri-pencil-line"></i>
                    </a>' : '';

                    $deleteBtn = $canEditDelete ? '
                    <button type="button" class="btn btn-sm btn-icon btn-label-danger btn-delete-ajax" 
                        data-url="' . $deleteUrl . '" 
                        data-po="No po ' . $row->no_po . ' Nama Barang ' . $row->nama_barang . '" 
                        title="Delete">
                        <i class="ri-delete-bin-line"></i>
                    </button>' : '';

                    return '
                    <div class="d-flex align-items-center gap-2">
                        <a href="' . $showUrl . '" class="btn btn-sm btn-icon btn-label-info" title="Details">
                            <i class="ri-eye-line"></i>
                        </a>
                        ' . $editBtn . '
                        ' . $deleteBtn . '
                    </div>
                ';
                })
                ->orderColumn('margin_references', 'margin $1')
                ->filterColumn('margin_references', function ($qMargin, $keyword) {
                    $keyword_lower = strtolower(trim($keyword));

                    // Strip everything except digits and dots for numeric matching
                    $numericClean = preg_replace('/[^0-9]/', '', $keyword);

                    // ── Margin Health
                    if (str_contains('margin normal', $keyword_lower)) {
                        $qMargin->whereRaw("modal_awal > 0")
                            ->whereRaw("ROUND((margin / modal_awal) * 100, 1) >= 20");
                        return;
                    }

                    if (str_contains('margin tipis', $keyword_lower)) {
                        $qMargin->whereRaw("modal_awal > 0")
                            ->whereRaw("ROUND((margin / modal_awal) * 100, 1) < 20")
                            ->whereRaw("ROUND((margin / modal_awal) * 100, 1) > 10");
                        return;
                    }

                    if (str_contains('margin buruk', $keyword_lower)) {
                        $qMargin->whereRaw("modal_awal > 0")
                            ->whereRaw("ROUND((margin / modal_awal) * 100, 1) < 10");
                        return;
                    }

                    // ── Tidak ada tambahan / Sama dengan margin dasar
                    if (
                        str_contains('tidak ada tambahan', $keyword_lower) ||
                        str_contains('sama dengan margin dasar', $keyword_lower)
                    ) {
                        $qMargin->where('tambahan_margin', 0);
                        return;
                    }

                    // ── Percentage only e.g. "25%" or "25"
                    if (preg_match('/^(\d+(?:\.\d+)?)%?$/', trim($keyword), $matches)) {
                        $pct = $matches[1];
                        $qMargin->where(function ($q) use ($pct) {
                            $q->whereRaw("modal_awal > 0 AND ROUND((margin / modal_awal) * 100, 1) = ?", [$pct])
                                ->orWhereRaw("modal_awal > 0 AND ROUND((margin_unit / modal_awal) * 100, 1) = ?", [$pct]);
                        });
                        return;
                    }

                    // ── Numeric / Rp format — cast columns directly to CHAR
                    if (!empty($numericClean)) {
                        $qMargin->where(function ($q) use ($numericClean) {

                            // Total Margin PO
                            $q->orWhereRaw("CAST(margin AS CHAR) like ?", ["%{$numericClean}%"])

                                // Margin Per Unit
                                ->orWhereRaw("CAST(margin_unit AS CHAR) like ?", ["%{$numericClean}%"])

                                // Tambahan Margin
                                ->orWhereRaw("CAST(tambahan_margin AS CHAR) like ?", ["%{$numericClean}%"])

                                // Total Margin Gabungan
                                ->orWhereRaw("CAST((CASE WHEN modal_awal > 0 THEN ((margin / modal_awal) * modal_awal) + tambahan_margin ELSE 0 END) AS CHAR) like ?", ["%{$numericClean}%"])

                                // Total Margin PO percentage
                                ->orWhereRaw("modal_awal > 0 AND CAST(ROUND((margin / modal_awal) * 100, 1) AS CHAR) like ?", ["%{$numericClean}%"])

                                // Margin Per Unit percentage
                                ->orWhereRaw("modal_awal > 0 AND CAST(ROUND((margin_unit / modal_awal) * 100, 1) AS CHAR) like ?", ["%{$numericClean}%"]);
                        });
                        return;
                    }
                })
                ->orderColumn('price_references', 'total $1')
                ->filterColumn('price_references', function ($qPrice, $keyword) {
                    $numericClean = preg_replace('/[^0-9]/', '', $keyword);

                    if (empty($numericClean)) {
                        return; // no numeric content, skip silently
                    }

                    $qPrice->where(function ($q) use ($numericClean) {
                        $q->orWhereRaw("CAST(total AS CHAR) like ?", ["%{$numericClean}%"])
                            ->orWhereRaw("CAST(harga AS CHAR) like ?", ["%{$numericClean}%"])
                            ->orWhereRaw("CAST(qty AS CHAR) like ?", ["%{$numericClean}%"])
                            ->orWhereRaw("CAST(modal_awal AS CHAR) like ?", ["%{$numericClean}%"])
                            ->orWhereRaw("CAST(CASE WHEN qty > 0 THEN modal_awal / qty ELSE 0 END AS CHAR) like ?", ["%{$numericClean}%"]);
                    });
                })
                ->orderColumn('detail_po', 'tgl_po $1') // order by Tanggal PO
                ->filterColumn('detail_po', function ($query, $keyword) {
                    $statusSearch = [
                        'incoming'                                 => 0,
                        'open'                                     => 1,
                        'partially delivered'                      => 2,
                        'fully delivered'                          => 3,
                        'partially delivered & partially invoiced' => 4,
                        'fully delivered & partially invoiced'     => 5,
                        'partially delivered & fully invoiced'     => 6,
                        'fully delivered & fully invoiced'         => 7,
                        'closed'                                   => 8,
                    ];

                    $keyword_lower = strtolower(trim($keyword));
                    $matchedStatus = null;

                    foreach ($statusSearch as $label => $val) {
                        if (str_contains($label, $keyword_lower)) { // ← correct: does label contain keyword
                            $matchedStatus = $val;
                            break;
                        }
                    }

                    $query->where(function ($q) use ($keyword, $matchedStatus) {
                        $q->where('no_po', 'like', "%{$keyword}%")
                            ->orWhere('nama_barang', 'like', "%{$keyword}%")
                            ->orWhereRaw("CONCAT(qty, ' Unit') like ?", ["%{$keyword}%"])
                            ->orWhereRaw("DATE_FORMAT(tgl_po, '%d %b %Y') like ?", ["%{$keyword}%"])
                            ->orWhereRaw("DATE_FORMAT(tgl_po, '%d %M %Y') like ?", ["%{$keyword}%"]);

                        if (!is_null($matchedStatus)) {
                            $q->orWhere('status', $matchedStatus);
                        }
                    });
                })
                ->rawColumns(['detail_po', 'action', 'price_references', 'margin_references'])
                ->with('totals', [
                    'qty'       => $totals->qty       ?? 0,
                    'total'     => $totals->total     ?? 0,
                    'modal_awal' => $totals->modal_awal ?? 0,
                    'margin'    => $totals->margin     ?? 0,
                ])
                ->make(true);
        }

        // Totals for the top cards (Static check)
        $totalIncoming = Po::where('status', 0)->count();
        $totalPrice = Po::where('status', 0)->sum('total');
        $totalCapital = Po::where('status', 0)->sum('modal_awal');
        $totalMargin = Po::where('status', 0)->sum('margin');

        // Note: We remove $data here because DataTables handles the list via AJAX
        return view('incoming-pos', compact('totalIncoming', 'totalPrice', 'totalCapital', 'totalMargin'));
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $totals = Po::where('status', '!=', 0)->selectRaw(
                '
                SUM(qty)       as qty,
                SUM(total)     as total,
                SUM(modal_awal) as modal_awal,
                SUM(margin)    as margin
            '
            )->first();
            $query = Po::with('customer')
                ->from(DB::raw('(
                    SELECT tbl_po.*, 
                        COALESCE((
                            SELECT SUM(qty_delivered) 
                            FROM tbl_delivery 
                            WHERE tbl_delivery.po_id = tbl_po.po_id
                        ), 0) as total_delivered
                    FROM tbl_po
                    WHERE tbl_po.status != 0
                ) as tbl_po'));
            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('detail_po', function ($row) {
                    $statusVal = $row->status;
                    $statusMap = [
                        1 => ['label' => 'Open',                                    'class' => 'status-open',       'icon' => 'ri-mail-send-line',        'color' => '#f59e0b'],
                        2 => ['label' => 'Partially Delivered',                     'class' => 'status-partial',    'icon' => 'ri-truck-line',            'color' => '#3b82f6'],
                        3 => ['label' => 'Fully Delivered',                         'class' => 'status-delivered',  'icon' => 'ri-checkbox-circle-line',  'color' => '#3b82f6'],
                        4 => ['label' => 'Partially Del. & Partially Inv.',         'class' => 'status-mixed',      'icon' => 'ri-exchange-box-line',     'color' => '#8b5cf6'],
                        5 => ['label' => 'Fully Del. & Partially Inv.',             'class' => 'status-mixed',      'icon' => 'ri-draft-line',            'color' => '#8b5cf6'],
                        6 => ['label' => 'Partially Del. & Fully Inv.',             'class' => 'status-mixed',      'icon' => 'ri-file-warning-line',     'color' => '#8b5cf6'],
                        7 => ['label' => 'Fully Delivered & Fully Invoiced',        'class' => 'status-complete',   'icon' => 'ri-check-double-line',     'color' => '#10b981'],
                        8 => ['label' => 'Closed',                                  'class' => 'status-closed',     'icon' => 'ri-verified-badge-fill',   'color' => '#10b981'],
                    ];
                    $default = ['label' => 'Unknown', 'class' => 'status-unknown', 'icon' => 'ri-question-line', 'color' => '#9ca3af'];
                    $map = $statusMap[$statusVal] ?? $default;

                    $tglPo    = Carbon::parse($row->tgl_po);
                    $relative = $tglPo->toIndonesianRelative();
                    $formated = $tglPo->format('d M Y');

                    return '
                        <style>
                            .po-card {
                                display: flex;
                                flex-direction: column;
                                gap: 0;
                                min-width: 220px;
                                max-width: 260px;
                                background: #ffffff;
                                border: 1px solid #e5e7eb;
                                border-radius: 12px;
                                overflow: hidden;
                                box-shadow: 0 1px 4px rgba(0,0,0,0.06);
                                font-family: inherit;
                            }
                            .po-card-header {
                                padding: 10px 14px 8px;
                                background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
                                border-bottom: 1px solid #e5e7eb;
                                display: flex;
                                align-items: center;
                                gap: 8px;
                            }
                            .po-card-header .po-number {
                                font-size: 0.8rem;
                                font-weight: 700;
                                color: #1e293b;
                                letter-spacing: 0.02em;
                            }
                            .po-card-header .po-icon {
                                width: 28px;
                                height: 28px;
                                background: #dbeafe;
                                border-radius: 8px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 0.85rem;
                                color: #3b82f6;
                                flex-shrink: 0;
                            }
                            .po-card-body {
                                padding: 10px 14px;
                                display: flex;
                                flex-direction: column;
                                gap: 8px;
                            }
                            .po-row {
                                display: flex;
                                align-items: flex-start;
                                gap: 8px;
                            }
                            .po-row-icon {
                                width: 22px;
                                height: 22px;
                                border-radius: 6px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 0.75rem;
                                flex-shrink: 0;
                                margin-top: 1px;
                            }
                            .po-row-content {
                                display: flex;
                                flex-direction: column;
                                gap: 1px;
                            }
                            .po-row-label {
                                font-size: 0.6rem;
                                font-weight: 600;
                                text-transform: uppercase;
                                letter-spacing: 0.06em;
                                color: #94a3b8;
                            }
                            .po-row-value {
                                font-size: 0.82rem;
                                font-weight: 600;
                                color: #1e293b;
                                line-height: 1.3;
                            }
                            .po-relative-badge {
                                display: inline-block;
                                font-size: 0.62rem;
                                font-weight: 600;
                                padding: 1px 7px;
                                border-radius: 20px;
                                background: #e0f2fe;
                                color: #0369a1;
                                margin-top: 2px;
                            }
                            .po-divider {
                                height: 1px;
                                background: #f1f5f9;
                                margin: 0 -14px;
                            }
                            .po-status-row {
                                padding: 8px 14px 10px;
                                background: #fafafa;
                                border-top: 1px solid #f1f5f9;
                            }
                            .po-status-chip {
                                display: inline-flex;
                                align-items: center;
                                gap: 5px;
                                padding: 4px 10px 4px 7px;
                                border-radius: 20px;
                                font-size: 0.72rem;
                                font-weight: 600;
                                letter-spacing: 0.01em;
                                border: 1.5px solid;
                            }
                        </style>
                        <div class="po-card shadow-lg">
                            <!-- Header: No PO -->
                            <div class="po-card-header">
                                <div class="po-icon"><i class="ri-file-list-3-line"></i></div>
                                <div>
                                    <div style="font-size:0.6rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;">No. PO</div>
                                    <div class="po-number">' . e($row->no_po) . '</div>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="po-card-body">

                                <!-- Tanggal PO -->
                                <div class="po-row">
                                    <div class="po-row-icon" style="background:#dcfce7;color:#16a34a;">
                                        <i class="ri-calendar-check-line"></i>
                                    </div>
                                    <div class="po-row-content">
                                        <span class="po-row-label">Tanggal PO</span>
                                        <span class="po-row-value">' . $formated . '</span>
                                        <span class="po-relative-badge">' . $relative . '</span>
                                    </div>
                                </div>

                                <div class="po-divider"></div>

                                <!-- Nama Barang -->
                                <div class="po-row">
                                    <div class="po-row-icon" style="background:#ede9fe;color:#7c3aed;">
                                        <i class="ri-box-3-line"></i>
                                    </div>
                                    <div class="po-row-content">
                                        <span class="po-row-label">Nama Barang</span>
                                        <span class="po-row-value">' . e($row->nama_barang) . '</span>
                                    </div>
                                </div>

                                <div class="po-divider"></div>

                                <!-- Kuantitas -->
                                <div class="po-row">
                                    <div class="po-row-icon" style="background:#e0f2fe;color:#0284c7;">
                                        <i class="ri-stack-line"></i>
                                    </div>
                                    <div class="po-row-content">
                                        <span class="po-row-label">Kuantitas</span>
                                        <span class="po-row-value">' . number_format((int) $row->qty, 0, ',', '.') . ' Unit</span>
                                    </div>
                                </div>

                            </div>

                            <!-- Status Footer -->
                            <div class="po-status-row">
                                <div style="font-size:0.6rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;margin-bottom:5px;">Status PO</div>
                                <span class="po-status-chip" style="background:' . $map['color'] . '18;color:' . $map['color'] . ';border-color:' . $map['color'] . '40;">
                                    <i class="' . $map['icon'] . '" style="font-size:0.8rem;"></i>
                                    ' . $map['label'] . '
                                </span>
                            </div>
                        </div>
                        ';
                })
                ->editColumn('price_references', function ($row) {
                    $total  = (float) ($row->total      ?? 0);
                    $harga  = (float) ($row->harga      ?? 0);
                    $modal  = (float) ($row->modal_awal ?? 0);
                    $qty    = (int)   ($row->qty        ?? 0);
                    $fmt = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');

                    return '
                        <style>
                            .prc-card {
                                display: flex;
                                flex-direction: column;
                                gap: 0;
                                min-width: 240px;
                                max-width: 280px;
                                background: #ffffff;
                                border: 1px solid #e5e7eb;
                                border-radius: 12px;
                                overflow: hidden;
                                box-shadow: 0 1px 4px rgba(0,0,0,0.06);
                                font-family: inherit;
                            }
                            .prc-hero {
                                padding: 12px 14px 10px;
                                background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                                display: flex;
                                align-items: center;
                                gap: 10px;
                            }
                            .prc-hero-icon {
                                width: 32px;
                                height: 32px;
                                background: rgba(255,255,255,0.1);
                                border-radius: 8px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 1rem;
                                color: #34d399;
                                flex-shrink: 0;
                            }
                            .prc-hero-label {
                                font-size: 0.58rem;
                                font-weight: 600;
                                text-transform: uppercase;
                                letter-spacing: 0.07em;
                                color: #64748b;
                            }
                            .prc-hero-value {
                                font-size: 1rem;
                                font-weight: 800;
                                color: #34d399;
                                letter-spacing: 0.01em;
                                line-height: 1.2;
                            }
                            .prc-card-body {
                                padding: 10px 14px;
                                display: flex;
                                flex-direction: column;
                                gap: 8px;
                            }
                            .prc-row {
                                display: flex;
                                align-items: flex-start;
                                gap: 8px;
                            }
                            .prc-row-icon {
                                width: 22px;
                                height: 22px;
                                border-radius: 6px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 0.75rem;
                                flex-shrink: 0;
                                margin-top: 1px;
                            }
                            .prc-row-content {
                                display: flex;
                                flex-direction: column;
                                gap: 1px;
                            }
                            .prc-row-label {
                                font-size: 0.6rem;
                                font-weight: 600;
                                text-transform: uppercase;
                                letter-spacing: 0.06em;
                                color: #94a3b8;
                            }
                            .prc-row-value {
                                font-size: 0.82rem;
                                font-weight: 700;
                                color: #1e293b;
                                line-height: 1.3;
                            }
                            .prc-row-sub {
                                font-size: 0.68rem;
                                font-weight: 500;
                                color: #64748b;
                                margin-top: 1px;
                            }
                            .prc-divider {
                                height: 1px;
                                background: #f1f5f9;
                                margin: 0 -14px;
                            }
                            .prc-metrics-grid {
                                display: grid;
                                grid-template-columns: 1fr 1fr;
                                gap: 6px;
                            }
                            .prc-metric-cell {
                                display: flex;
                                flex-direction: column;
                                gap: 2px;
                                padding: 6px 8px;
                                border-radius: 8px;
                                border: 1px solid;
                            }
                            .prc-metric-label {
                                font-size: 0.58rem;
                                font-weight: 600;
                                text-transform: uppercase;
                                letter-spacing: 0.06em;
                                color: #94a3b8;
                            }
                            .prc-metric-value {
                                font-size: 0.8rem;
                                font-weight: 800;
                                line-height: 1.2;
                            }
                            .prc-footer {
                                padding: 8px 14px 10px;
                                border-top: 1.5px solid;
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                            }
                            .prc-chip {
                                display: inline-flex;
                                align-items: center;
                                gap: 5px;
                                padding: 4px 10px 4px 7px;
                                border-radius: 20px;
                                font-size: 0.72rem;
                                font-weight: 700;
                                border: 1.5px solid;
                            }
                        </style>
                        <div class="prc-card shadow-lg">

                            <!-- Hero: Total Harga PO -->
                            <div class="prc-hero">
                                <div class="prc-hero-icon">
                                    <i class="ri-money-dollar-circle-line"></i>
                                </div>
                                <div class="d-flex flex-column gap-0">
                                    <span class="prc-hero-label">Total Harga PO</span>
                                    <span class="prc-hero-value">' . $fmt($total) . '</span>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="prc-card-body">

                                <!-- Harga Per Unit -->
                                <div class="prc-row">
                                    <div class="prc-row-icon" style="background:#ede9fe;color:#7c3aed;">
                                        <i class="ri-price-tag-3-line"></i>
                                    </div>
                                    <div class="prc-row-content">
                                        <span class="prc-row-label">Harga Per Unit</span>
                                        <span class="prc-row-value">' . $fmt($harga) . '</span>
                                        ' . ($qty > 0 ? '<span class="prc-row-sub"><i class="ri-stack-line me-1"></i>' . number_format($qty, 0, ',', '.') . ' Unit</span>' : '') . '
                                    </div>
                                </div>

                                <div class="prc-divider"></div>

                                <!-- Modal PO -->
                                <div class="prc-row">
                                    <div class="prc-row-icon" style="background:#e0f2fe;color:#0284c7;">
                                        <i class="ri-safe-line"></i>
                                    </div>
                                    <div class="prc-row-content">
                                        <span class="prc-row-label">Modal PO</span>
                                        <span class="prc-row-value">' . $fmt($modal) . '</span>
                                        ' . ($qty > 0 ? '<span class="prc-row-sub"><i class="ri-divide-line me-1"></i>' . $fmt($qty > 0 ? $modal / $qty : 0) . ' / unit</span>' : '') . '
                                    </div>
                                </div>
                            </div>
                        </div>
                        ';
                })
                ->editColumn('margin_references', function ($row) {
                    $margin         = (float) ($row->margin          ?? 0);
                    $marginUnit     = (float) ($row->margin_unit     ?? 0);
                    $tambahanMargin = (float) ($row->tambahan_margin ?? 0);
                    $modal_awal     = (float) ($row->modal_awal      ?? 0);
                    $qty            = (int)   ($row->qty             ?? 0);

                    // ── Margin Calculations ───────────────────────────────────
                    $marginPct     = $modal_awal > 0 ? round(($margin / $modal_awal) * 100, 1) : 0;
                    $marginUnitPct = $modal_awal > 0 ? round(($marginUnit / $modal_awal) * 100, 1) : 0;
                    $totalWithExtra = $modal_awal * ($marginPct / 100) + $tambahanMargin;
                    $extraPct      = $margin > 0 ? round(($tambahanMargin / $modal_awal) * 100, 1) : 0;

                    // ── Margin Health Thresholds ──────────────────────────────
                    $health = match (true) {
                        $marginPct >= 20 => ['color' => '#10b981', 'bg' => '#dcfce7', 'border' => '#bbf7d0', 'label' => 'Margin Normal',  'icon' => 'ri-arrow-up-circle-fill',  'bar' => '#10b981'],
                        $marginPct >= 10 => ['color' => '#f59e0b', 'bg' => '#fef3c7', 'border' => '#fde68a', 'label' => 'Margin Tipis',  'icon' => 'ri-error-warning-line',     'bar' => '#f59e0b'],
                        default          => ['color' => '#ef4444', 'bg' => '#fee2e2', 'border' => '#fecaca', 'label' => 'Margin Buruk',  'icon' => 'ri-arrow-down-circle-fill', 'bar' => '#ef4444'],
                    };

                    // ── Tambahan Margin State ─────────────────────────────────
                    $hasExtra     = $tambahanMargin > 0;
                    $extraColor   = $hasExtra ? '#10b981' : '#9ca3af';
                    $extraBg      = $hasExtra ? '#dcfce7' : '#f1f5f9';
                    $extraIcon    = $hasExtra ? 'ri-add-circle-line' : 'ri-minus-circle-line';
                    $extraPrefix  = $hasExtra ? '+' : '';

                    $fmt = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');
                    $barWidth = min($marginPct, 100);

                    return '
                        <style>
                            .mrg-card {
                                display: flex;
                                flex-direction: column;
                                gap: 0;
                                min-width: 240px;
                                max-width: 280px;
                                background: #ffffff;
                                border: 1px solid #e5e7eb;
                                border-radius: 12px;
                                overflow: hidden;
                                box-shadow: 0 1px 4px rgba(0,0,0,0.06);
                                font-family: inherit;
                            }
                            .mrg-hero {
                                padding: 12px 14px 10px;
                                background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                                display: flex;
                                align-items: center;
                                gap: 10px;
                            }
                            .mrg-hero-icon {
                                width: 32px;
                                height: 32px;
                                background: rgba(255,255,255,0.1);
                                border-radius: 8px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 1rem;
                                flex-shrink: 0;
                            }
                            .mrg-hero-label {
                                font-size: 0.58rem;
                                font-weight: 600;
                                text-transform: uppercase;
                                letter-spacing: 0.07em;
                                color: #64748b;
                            }
                            .mrg-hero-value {
                                font-size: 1rem;
                                font-weight: 800;
                                letter-spacing: 0.01em;
                                line-height: 1.2;
                            }
                            .mrg-pct-pill {
                                margin-left: auto;
                                padding: 3px 9px;
                                border-radius: 20px;
                                font-size: 0.75rem;
                                font-weight: 800;
                                border: 1.5px solid;
                                white-space: nowrap;
                            }
                            .mrg-progress-wrap {
                                padding: 0 14px 10px;
                                background: #0f172a;
                                display: flex;
                                flex-direction: column;
                                gap: 4px;
                            }
                            .mrg-progress-track {
                                width: 100%;
                                height: 6px;
                                background: rgba(255,255,255,0.1);
                                border-radius: 999px;
                                overflow: hidden;
                            }
                            .mrg-progress-fill {
                                height: 100%;
                                border-radius: 999px;
                                transition: width 0.4s ease;
                            }
                            .mrg-progress-labels {
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                            }
                            .mrg-card-body {
                                padding: 10px 14px;
                                display: flex;
                                flex-direction: column;
                                gap: 8px;
                            }
                            .mrg-row {
                                display: flex;
                                align-items: flex-start;
                                gap: 8px;
                            }
                            .mrg-row-icon {
                                width: 22px;
                                height: 22px;
                                border-radius: 6px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 0.75rem;
                                flex-shrink: 0;
                                margin-top: 1px;
                            }
                            .mrg-row-content {
                                display: flex;
                                flex-direction: column;
                                gap: 1px;
                                flex: 1;
                            }
                            .mrg-row-label {
                                font-size: 0.6rem;
                                font-weight: 600;
                                text-transform: uppercase;
                                letter-spacing: 0.06em;
                                color: #94a3b8;
                            }
                            .mrg-row-value {
                                font-size: 0.82rem;
                                font-weight: 700;
                                color: #1e293b;
                                line-height: 1.3;
                            }
                            .mrg-row-sub {
                                font-size: 0.65rem;
                                font-weight: 500;
                                color: #64748b;
                                margin-top: 1px;
                            }
                            .mrg-divider {
                                height: 1px;
                                background: #f1f5f9;
                                margin: 0 -14px;
                            }
                            .mrg-badge {
                                display: inline-flex;
                                align-items: center;
                                padding: 2px 8px;
                                border-radius: 20px;
                                font-size: 0.68rem;
                                font-weight: 700;
                                border: 1.5px solid;
                                white-space: nowrap;
                                margin-left: auto;
                                align-self: flex-start;
                                margin-top: 2px;
                            }
                            .mrg-extra-row {
                                display: flex;
                                align-items: flex-start;
                                gap: 8px;
                                padding: 7px 10px;
                                border-radius: 8px;
                                border: 1.5px dashed;
                            }
                            .mrg-footer {
                                padding: 8px 14px 10px;
                                border-top: 1.5px solid;
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                            }
                            .mrg-chip {
                                display: inline-flex;
                                align-items: center;
                                gap: 5px;
                                padding: 4px 10px 4px 7px;
                                border-radius: 20px;
                                font-size: 0.72rem;
                                font-weight: 700;
                                border: 1.5px solid;
                            }
                        </style>
                        <div class="mrg-card">

                            <!-- Hero: Total Margin PO -->
                            <div class="mrg-hero">
                                <div class="mrg-hero-icon" style="color:' . $health['color'] . ';">
                                    <i class="ri-funds-line"></i>
                                </div>
                                <div class="d-flex flex-column gap-0" style="flex:1;">
                                    <span class="mrg-hero-label">Total Margin PO</span>
                                    <span class="mrg-hero-value" style="color:' . $health['color'] . ';">' . $fmt($margin) . '</span>
                                </div>
                                <span class="mrg-pct-pill" style="background:' . $health['color'] . '22;color:' . $health['color'] . ';border-color:' . $health['color'] . '55;">
                                    ' . $marginPct . '%
                                </span>
                            </div>

                            <!-- Progress Bar (inside dark hero zone) -->
                            <div class="mrg-progress-wrap">
                                <div class="mrg-progress-track">
                                    <div class="mrg-progress-fill" style="width:' . $barWidth . '%;background:' . $health['bar'] . ';"></div>
                                </div>
                                <div class="mrg-progress-labels">
                                    <span style="font-size:0.58rem;font-weight:600;color:#475569;">0%</span>
                                    <span style="font-size:0.58rem;font-weight:700;color:' . $health['color'] . ';">' . $health['label'] . '</span>
                                    <span style="font-size:0.58rem;font-weight:600;color:#475569;">100%</span>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="mrg-card-body">

                                <!-- Margin Per Unit -->
                                <div class="mrg-row">
                                    <div class="mrg-row-icon" style="background:#ede9fe;color:#7c3aed;">
                                        <i class="ri-price-tag-3-line"></i>
                                    </div>
                                    <div class="mrg-row-content">
                                        <span class="mrg-row-label">Margin Per Unit</span>
                                        <span class="mrg-row-value">' . $fmt($marginUnit) . '</span>
                                        ' . ($qty > 0 ? '<span class="mrg-row-sub"><i class="ri-stack-line me-1"></i>' . number_format($qty, 0, ',', '.') . ' Unit</span>' : '') . '
                                    </div>
                                    <span class="mrg-badge" style="background:#7c3aed18;color:#7c3aed;border-color:#7c3aed40;">
                                        ' . $marginUnitPct . '%
                                    </span>
                                </div>

                                <div class="mrg-divider"></div>

                                <!-- Tambahan Margin -->
                                <div class="mrg-extra-row" style="background:' . $extraBg . ';border-color:' . $extraColor . '40;">
                                    <div class="mrg-row-icon" style="background:' . $extraBg . ';color:' . $extraColor . ';border:1px solid ' . $extraColor . '30;">
                                        <i class="' . $extraIcon . '"></i>
                                    </div>
                                    <div class="mrg-row-content">
                                        <span class="mrg-row-label" style="color:' . $extraColor . '88;">Tambahan Margin</span>
                                        <span style="font-size:0.82rem;font-weight:700;color:' . $extraColor . ';">
                                            ' . $extraPrefix . $fmt($tambahanMargin) . '
                                        </span>
                                        ' . ($hasExtra ? '<span class="mrg-row-sub" style="color:' . $extraColor . ';">+' . $extraPct . '% dari margin dasar</span>' : '<span class="mrg-row-sub">Tidak ada tambahan</span>') . '
                                    </div>
                                </div>

                                <div class="mrg-divider"></div>

                                <!-- Total Margin Gabungan -->
                                <div class="mrg-row">
                                    <div class="mrg-row-icon" style="background:#fef3c7;color:#d97706;">
                                        <i class="ri-calculator-line"></i>
                                    </div>
                                    <div class="mrg-row-content">
                                        <span class="mrg-row-label">Total Margin Gabungan</span>
                                        <span class="mrg-row-value" style="color:#d97706;">' . $fmt($totalWithExtra) . '</span>
                                        ' . ($hasExtra ? '<span class="mrg-row-sub">Margin + Tambahan</span>' : '<span class="mrg-row-sub" style="color:#94a3b8;">Sama dengan margin dasar</span>') . '
                                    </div>
                                </div>

                            </div>

                            <!-- Footer: Health Indicator -->
                            <div class="mrg-footer" style="background:' . $health['bg'] . ';border-color:' . $health['border'] . ';">
                                <span class="mrg-chip" style="background:' . $health['color'] . '18;color:' . $health['color'] . ';border-color:' . $health['color'] . '40;">
                                    <i class="' . $health['icon'] . '" style="font-size:0.85rem;"></i>
                                    ' . $health['label'] . '
                                </span>
                                <span style="font-size:0.68rem;font-weight:800;color:' . $health['color'] . ';">
                                    ' . $marginPct . '% dari modal
                                </span>
                            </div>
                        </div>
                        ';
                })
                ->editColumn('qty', function ($row) {
                    $totalQty      = (int) $row->qty ?? 0;
                    $delivered     = (int) $row->total_delivered ?? 0;
                    $remaining     = (int) $totalQty - $delivered;
                    $percentage    = $totalQty > 0 ? round(($delivered / $totalQty) * 100) : 0;

                    if ($remaining <= 0) {
                        $statusLabel = '<span class="badge bg-success text-white fw-bold">Fully Delivered</span>';
                        $barClass    = 'bg-success';
                    } elseif ($remaining === $totalQty) {
                        $statusLabel = '<span class="badge bg-danger text-white fw-bold">Not Delivered</span>';
                        $barClass    = 'bg-dark';
                    } else {
                        $statusLabel = '<span class="badge bg-warning text-dark fw-bold">Partially Delivered</span>';
                        $barClass    = 'bg-warning';
                    }

                    return '
                        <div class="d-flex flex-column gap-1" style="min-width: 140px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted fw-semibold">' . $delivered . ' / ' . $totalQty . ' Unit</small>
                                <small class="fw-bold">' . $percentage . '%</small>
                            </div>

                            <div class="progress" style="height: 8px; border-radius: 999px;">
                                <div class="progress-bar ' . $barClass . '"
                                    role="progressbar"
                                    style="width: ' . $percentage . '%; border-radius: 999px; transition: width 0.4s ease;"
                                    aria-valuenow="' . $percentage . '"
                                    aria-valuemin="0"
                                    aria-valuemax="100">
                                </div>
                            </div>
                            ' . $statusLabel . '
                        </div>
                    ';
                })
                ->addColumn('invoice_details', function ($row) {
                    $invoices         = $row->invoices ?? collect();
                    $maxInvoice       = $row->deliveries->count() ?? 0; // or whatever your maximum is
                    $maxPaid          = $invoices->count(); // max paid = total invoices

                    $invoiceCount     = $invoices->count();
                    $paidCount        = $invoices->where('status_invoice', 1)->count(); // adjust value to match your DB

                    $invoicePct       = $maxInvoice > 0 ? round(($invoiceCount / $maxInvoice) * 100) : 0;
                    $paidPct          = $maxPaid    > 0 ? round(($paidCount    / $maxPaid)    * 100) : 0;

                    // ── Invoice Status ──────────────────────────────────────────
                    if ($invoiceCount <= 0) {
                        $invoiceLabel = '<span class="badge bg-danger text-white fw-bold">Not Invoiced</span>';
                        $invoiceBar   = 'bg-danger';
                    } elseif ($invoiceCount >= $maxInvoice) {
                        $invoiceLabel = '<span class="badge bg-success text-white fw-bold">Fully Invoiced</span>';
                        $invoiceBar   = 'bg-success';
                    } else {
                        $invoiceLabel = '<span class="badge bg-warning text-dark fw-bold">Partially Invoiced</span>';
                        $invoiceBar   = 'bg-warning';
                    }

                    // ── Paid Status ─────────────────────────────────────────────
                    if ($paidCount <= 0) {
                        $paidLabel = '<span class="badge bg-danger text-white fw-bold">Not Paid</span>';
                        $paidBar   = 'bg-danger';
                    } elseif ($paidCount >= $maxPaid) {
                        $paidLabel = '<span class="badge bg-success text-white fw-bold">Fully Paid</span>';
                        $paidBar   = 'bg-success';
                    } else {
                        $paidLabel = '<span class="badge bg-warning text-dark fw-bold">Partially Paid</span>';
                        $paidBar   = 'bg-warning';
                    }

                    return '
                        <div class="d-flex flex-column gap-3" style="min-width: 180px;">

                            <!-- Invoice Progress -->
                            <div class="d-flex flex-column gap-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted fw-semibold">
                                        <i class="ri-file-list-3-line me-1"></i>Invoice
                                    </small>
                                    <small class="fw-bold">' . $invoiceCount . ' / ' . $maxInvoice . ' &nbsp;(' . $invoicePct . '%)</small>
                                </div>
                                <div class="progress w-100" style="height: 8px; border-radius: 999px; background-color: #e9ecef;">
                                    <div class="progress-bar ' . $invoiceBar . '"
                                        role="progressbar"
                                        style="width: ' . $invoicePct . '%; border-radius: 999px; transition: width 0.4s ease;"
                                        aria-valuenow="' . $invoicePct . '"
                                        aria-valuemin="0"
                                        aria-valuemax="100">
                                    </div>
                                </div>
                                ' . $invoiceLabel . '
                            </div>

                            <div class="border-top my-1"></div>

                            <!-- Paid Progress -->
                            <div class="d-flex flex-column gap-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted fw-semibold">
                                        <i class="ri-money-dollar-circle-line me-1"></i>Payment
                                    </small>
                                    <small class="fw-bold">' . $paidCount . ' / ' . $maxPaid . ' &nbsp;(' . $paidPct . '%)</small>
                                </div>
                                <div class="progress w-100" style="height: 8px; border-radius: 999px; background-color: #e9ecef;">
                                    <div class="progress-bar ' . $paidBar . '"
                                        role="progressbar"
                                        style="width: ' . $paidPct . '%; border-radius: 999px; transition: width 0.4s ease;"
                                        aria-valuenow="' . $paidPct . '"
                                        aria-valuemin="0"
                                        aria-valuemax="100">
                                    </div>
                                </div>
                                ' . $paidLabel . '
                            </div>

                        </div>
                    ';
                })
                ->addColumn('action', function ($row) {
                    $showUrl   = Route::has('po.show')    ? route('po.show',    $row->po_id) : '#';
                    $editUrl   = Route::has('po.edit')    ? route('po.edit',    $row->po_id) : '#';
                    $deleteUrl = Route::has('po.destroy') ? route('po.destroy', $row->po_id) : '#';

                    $user = Auth::user();
                    $canEditDelete = $user && $user->role_id !== 2;

                    $editBtn = $canEditDelete ? '
                    <a href="' . $editUrl . '" class="btn btn-sm btn-icon btn-label-warning" title="Edit">
                        <i class="ri-pencil-line"></i>
                    </a>' : '';

                    $deleteBtn = $canEditDelete ? '
                    <button type="button" class="btn btn-sm btn-icon btn-label-danger btn-delete-ajax" 
                        data-url="' . $deleteUrl . '" 
                        data-po="No po ' . $row->no_po . ' Nama Barang ' . $row->nama_barang . '" 
                        title="Delete">
                        <i class="ri-delete-bin-line"></i>
                    </button>' : '';

                    return '
                    <div class="d-flex align-items-center gap-2">
                        <a href="' . $showUrl . '" class="btn btn-sm btn-icon btn-label-info" title="Details">
                            <i class="ri-eye-line"></i>
                        </a>
                        ' . $editBtn . '
                        ' . $deleteBtn . '
                    </div>
                    ';
                })
                ->orderColumn('detail_po', 'tgl_po $1') // order by Tanggal PO
                ->filterColumn('detail_po', function ($query, $keyword) {
                    $statusSearch = [
                        'incoming'                              => 0,
                        'open'                                  => 1,
                        'partially delivered'                   => 2,
                        'fully delivered'                       => 3,
                        'partially delivered & partially invoiced' => 4,
                        'fully delivered & partially invoiced'  => 5,
                        'partially delivered & fully invoiced'  => 6,
                        'fully delivered & fully invoiced'      => 7,
                        'closed'                                => 8,
                    ];

                    $keyword_lower = strtolower(trim($keyword));
                    $matchedStatus = null;

                    foreach ($statusSearch as $label => $val) {
                        if (str_contains($label, $keyword_lower)) {
                            $matchedStatus = $val;
                            break;
                        }
                    }

                    $query->where(function ($q) use ($keyword, $matchedStatus) {
                        $q->where('no_po', 'like', "%{$keyword}%")
                            ->orWhere('nama_barang', 'like', "%{$keyword}%")
                            ->orWhere('qty', 'like', "%{$keyword}%")
                            ->orWhereRaw("CONCAT(qty, ' Unit') like ?", ["%{$keyword}%"])
                            ->orWhereRaw("DATE_FORMAT(tgl_po, '%d %b %Y') like ?", ["%{$keyword}%"]);

                        if (!is_null($matchedStatus)) {
                            $q->orWhere('status', $matchedStatus);
                        }
                    });
                })
                ->orderColumn('qty', 'total_delivered $1')
                ->filterColumn('qty', function ($query, $keyword) {
                    $keyword = trim($keyword);

                    // Strip " Unit" suffix if present
                    $numericKeyword = preg_replace('/\s*unit\s*/i', '', $keyword);

                    // Check if searching "X / Y" format (delivered / qty)
                    if (str_contains($numericKeyword, '/')) {
                        [$searchDelivered, $searchQty] = array_map('trim', explode('/', $numericKeyword));
                        $query->where(function ($q) use ($searchDelivered, $searchQty) {
                            $q->where('total_delivered', 'like', "%{$searchDelivered}%")
                                ->where('qty', 'like', "%{$searchQty}%");
                        });
                        return;
                    }

                    $keyword_lower = strtolower(trim($keyword));

                    // ── Fully Delivered: total_delivered >= qty
                    if (str_contains('fully delivered', $keyword_lower)) {
                        $query->whereRaw('total_delivered >= qty');
                        return;
                    }

                    // ── Not Delivered: total_delivered = 0
                    if (str_contains('not delivered', $keyword_lower)) {
                        $query->where('total_delivered', 0);
                        return;
                    }

                    $query->where(function ($q) use ($numericKeyword, $keyword) {
                        // Search qty
                        $q->where('qty', 'like', "%{$numericKeyword}%")

                            // Search total_delivered
                            ->orWhere('total_delivered', 'like', "%{$numericKeyword}%")

                            // Search remaining (qty - total_delivered)
                            ->orWhereRaw("(qty - total_delivered) like ?", ["%{$numericKeyword}%"])

                            // Search percentage formula
                            ->orWhereRaw("
                                CASE WHEN qty > 0 
                                THEN ROUND((total_delivered / qty) * 100) 
                                ELSE 0 END 
                                like ?", ["%{$numericKeyword}%"])

                            // Search "X / Y Unit" format
                            ->orWhereRaw("CONCAT(total_delivered, ' / ', qty, ' Unit') like ?", ["%{$keyword}%"])

                            // Search remaining in "X / Y" format
                            ->orWhereRaw("CONCAT((qty - total_delivered), ' / ', qty, ' Unit') like ?", ["%{$keyword}%"]);
                    });
                })
                ->filterColumn('invoice_details', function ($qInvoice, $keyword) {
                    $keyword_lower = strtolower(trim($keyword));
                    $invoiceCount = "
                        (SELECT COUNT(*) FROM tbl_invoice 
                        WHERE tbl_invoice.delivery_id IN (
                            SELECT delivery_id FROM tbl_delivery WHERE tbl_delivery.po_id = tbl_po.po_id
                        ))
                    ";
                    $deliveryCount = "
                        (SELECT COUNT(*) FROM tbl_delivery WHERE tbl_delivery.po_id = tbl_po.po_id)
                    ";
                    $paidCount = "
                        (SELECT COUNT(*) FROM tbl_invoice 
                        WHERE tbl_invoice.delivery_id IN (
                            SELECT delivery_id FROM tbl_delivery WHERE tbl_delivery.po_id = tbl_po.po_id
                        ) AND tbl_invoice.status_invoice = 1)
                    ";

                    // ── Fully Invoiced
                    if (str_contains('fully invoiced', $keyword_lower)) {
                        $qInvoice->whereRaw("{$invoiceCount} >= {$deliveryCount}")
                            ->whereRaw("{$invoiceCount} > 0");
                        return;
                    }

                    // ── Not Invoiced
                    if (str_contains('not invoiced', $keyword_lower)) {
                        $qInvoice->whereRaw("{$invoiceCount} = 0");
                        return;
                    }

                    // ── Partially Invoiced
                    if (str_contains('partially invoiced', $keyword_lower)) {
                        $qInvoice->whereRaw("{$invoiceCount} > 0")
                            ->whereRaw("{$invoiceCount} < {$deliveryCount}");
                        return;
                    }

                    // ── Fully Paid
                    if (str_contains('fully paid', $keyword_lower)) {
                        $qInvoice->whereRaw("{$paidCount} >= {$invoiceCount}")
                            ->whereRaw("{$invoiceCount} > 0");
                        return;
                    }

                    // ── Not Paid
                    if (str_contains('not paid', $keyword_lower)) {
                        $qInvoice->whereRaw("{$paidCount} = 0");
                        return;
                    }

                    // ── Partially Paid
                    if (str_contains('partially paid', $keyword_lower)) {
                        $qInvoice->whereRaw("{$paidCount} > 0")
                            ->whereRaw("{$paidCount} < {$invoiceCount}");
                        return;
                    }

                    // ── Numeric / X/Y format
                    $numericKeyword = trim(preg_replace('/\s*/i', '', $keyword));

                    if (str_contains($numericKeyword, '/')) {
                        [$searchCount, $searchMax] = array_map('trim', explode('/', $numericKeyword));
                        $qInvoice->whereRaw("{$invoiceCount} like ?", ["%{$searchCount}%"])
                            ->whereRaw("{$deliveryCount} like ?", ["%{$searchMax}%"]);
                        return;
                    }

                    $qInvoice->where(function ($q) use ($numericKeyword, $invoiceCount, $deliveryCount, $paidCount) {
                        $q->whereRaw("{$invoiceCount} like ?", ["%{$numericKeyword}%"])
                            ->orWhereRaw("{$paidCount} like ?", ["%{$numericKeyword}%"])
                            ->orWhereRaw("
                                CASE WHEN {$deliveryCount} > 0
                                THEN ROUND({$invoiceCount} * 100.0 / {$deliveryCount})
                                ELSE 0 END like ?", ["%{$numericKeyword}%"])
                            ->orWhereRaw("
                                CASE WHEN {$invoiceCount} > 0
                                THEN ROUND({$paidCount} * 100.0 / {$invoiceCount})
                                ELSE 0 END like ?", ["%{$numericKeyword}%"]);
                    });
                })
                ->orderColumn('invoice_details', function ($qInvoice, $direction) {
                    $qInvoice->orderByRaw("
                        (SELECT COUNT(*) FROM tbl_invoice 
                        WHERE tbl_invoice.delivery_id IN (
                            SELECT delivery_id FROM tbl_delivery WHERE tbl_delivery.po_id = tbl_po.po_id
                        )) {$direction}
                    ");
                })
                ->orderColumn('price_references', 'total $1')
                ->filterColumn('price_references', function ($qPrice, $keyword) {
                    $numericClean = preg_replace('/[^0-9]/', '', $keyword);
                    $keyword_lower = strtolower(trim($keyword));

                    if (!empty($numericClean)) {
                        $qPrice->where(function ($q) use ($numericClean) {

                            // Total Harga PO
                            $q->orWhereRaw("CAST(total AS CHAR) like ?", ["%{$numericClean}%"])

                                // Harga Per Unit
                                ->orWhereRaw("CAST(harga AS CHAR) like ?", ["%{$numericClean}%"])

                                // Quantity
                                ->orWhereRaw("CAST(qty AS CHAR) like ?", ["%{$numericClean}%"])

                                // Modal PO
                                ->orWhereRaw("CAST(modal_awal AS CHAR) like ?", ["%{$numericClean}%"])

                                // Modal PO per unit
                                ->orWhereRaw("CAST(CASE WHEN qty > 0 THEN modal_awal / qty ELSE 0 END AS CHAR) like ?", ["%{$numericClean}%"]);
                        });
                    }
                })
                ->orderColumn('margin_references', 'margin $1')
                ->filterColumn('margin_references', function ($qMargin, $keyword) {
                    $keyword_lower = strtolower(trim($keyword));

                    // Strip everything except digits and dots for numeric matching
                    $numericClean = preg_replace('/[^0-9]/', '', $keyword);

                    // ── Margin Health
                    if (str_contains('margin normal', $keyword_lower)) {
                        $qMargin->whereRaw("modal_awal > 0")
                            ->whereRaw("ROUND((margin / modal_awal) * 100, 1) >= 20");
                        return;
                    }

                    if (str_contains('margin tipis', $keyword_lower)) {
                        $qMargin->whereRaw("modal_awal > 0")
                            ->whereRaw("ROUND((margin / modal_awal) * 100, 1) < 20")
                            ->whereRaw("ROUND((margin / modal_awal) * 100, 1) > 10");
                        return;
                    }

                    if (str_contains('margin buruk', $keyword_lower)) {
                        $qMargin->whereRaw("modal_awal > 0")
                            ->whereRaw("ROUND((margin / modal_awal) * 100, 1) < 10");
                        return;
                    }

                    // ── Tidak ada tambahan / Sama dengan margin dasar
                    if (
                        str_contains('tidak ada tambahan', $keyword_lower) ||
                        str_contains('sama dengan margin dasar', $keyword_lower)
                    ) {
                        $qMargin->where('tambahan_margin', 0);
                        return;
                    }

                    // ── Percentage only e.g. "25%" or "25"
                    if (preg_match('/^(\d+(?:\.\d+)?)%?$/', trim($keyword), $matches)) {
                        $pct = $matches[1];
                        $qMargin->where(function ($q) use ($pct) {
                            $q->whereRaw("modal_awal > 0 AND ROUND((margin / modal_awal) * 100, 1) = ?", [$pct])
                                ->orWhereRaw("modal_awal > 0 AND ROUND((margin_unit / modal_awal) * 100, 1) = ?", [$pct]);
                        });
                        return;
                    }

                    // ── Numeric / Rp format — cast columns directly to CHAR
                    if (!empty($numericClean)) {
                        $qMargin->where(function ($q) use ($numericClean) {

                            // Total Margin PO
                            $q->orWhereRaw("CAST(margin AS CHAR) like ?", ["%{$numericClean}%"])

                                // Margin Per Unit
                                ->orWhereRaw("CAST(margin_unit AS CHAR) like ?", ["%{$numericClean}%"])

                                // Tambahan Margin
                                ->orWhereRaw("CAST(tambahan_margin AS CHAR) like ?", ["%{$numericClean}%"])

                                // Total Margin Gabungan
                                ->orWhereRaw("CAST((CASE WHEN modal_awal > 0 THEN ((margin / modal_awal) * modal_awal) + tambahan_margin ELSE 0 END) AS CHAR) like ?", ["%{$numericClean}%"])

                                // Total Margin PO percentage
                                ->orWhereRaw("modal_awal > 0 AND CAST(ROUND((margin / modal_awal) * 100, 1) AS CHAR) like ?", ["%{$numericClean}%"])

                                // Margin Per Unit percentage
                                ->orWhereRaw("modal_awal > 0 AND CAST(ROUND((margin_unit / modal_awal) * 100, 1) AS CHAR) like ?", ["%{$numericClean}%"]);
                        });
                        return;
                    }
                })
                ->rawColumns(['detail_po', 'action', 'price_references', 'margin_references', 'qty', 'invoice_details'])
                ->with('totals', [
                    'qty'       => $totals->qty       ?? 0,
                    'total'     => $totals->total     ?? 0,
                    'modal_awal' => $totals->modal_awal ?? 0,
                    'margin'    => $totals->margin     ?? 0,
                ])
                ->make(true);
        }
        return view('po-index');
    }
    public function create()
    {
        // Get only Incoming POs (Status 0) to show in the dropdown
        $dataIncomingPo = Po::where('status', 0)->get();
        $customers = Customer::all();
        return view('po-create', compact('customers', 'dataIncomingPo'));
    }
    public function getIncomingDetails($id)
    {
        $po = Po::with('customer')->find($id);
        if ($po) {
            return response()->json([
                'success' => true,
                'data' => $po
            ]);
        }
        return response()->json(['success' => false], 404);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'incoming_po_id' => 'required|exists:tbl_po,po_id',
            'no_po'          => 'required|string|max:50|unique:tbl_po,no_po', // enforce uniqueness
            'nama_barang'       => 'required|string',
            'customer_id'       => 'required|exists:tbl_customer,id_cust',
            'tgl_po'            => 'required|date',
            'qty'               => 'required|numeric|min:1',
            'harga'             => 'required|numeric|min:0',
            'margin_percentage' => 'nullable|numeric|min:0',
            'tambahan_margin'   => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $po = Po::findOrFail($request->incoming_po_id);

            $po->no_po       = $request->no_po;
            $po->customer_id = $request->customer_id;
            $po->nama_barang = $request->nama_barang;
            $po->tgl_po      = $request->tgl_po;
            $po->qty         = $request->qty;
            $po->harga       = $request->harga;
            $po->margin      = $po->modal_awal * ($request->margin_percentage / 100) + $request->tambahan_margin;
            $po->tambahan_margin      = $request->tambahan_margin;

            $po->status = 1;

            $po->save();

            $this->logCreate($po, 'Incoming PO dengan Nomor PO ' . $request->no_po . ' Dan Nama Barang ' . $request->nama_barang . ' Menjadi PO');

            return response()->json([
                'success' => true,
                'message' => 'PO successfully opened (Status 1)',
                'redirect_url' => route('po.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing PO: ' . $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $po = Po::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_barang'       => 'required|string',
            'customer_id'       => 'required|exists:tbl_customer,id_cust',
            'tgl_po'            => 'required|date',
            'qty'               => 'required|numeric|min:1',
            'harga'             => 'required|numeric|min:0',
            'margin_percentage' => 'nullable|numeric|min:0',
            'tambahan_margin'   => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $qty = (float) $request->qty;
        $harga = (float) $request->harga;
        $tambahan = (float) ($request->tambahan_margin ?? 0);

        $total = $qty * $harga;
        $modal = $total / 2;
        $percentageMargin = $request->margin_percentage;
        $finalMargin = ($modal * ($percentageMargin / 100)) + $tambahan;
        $data = $validator->validated();
        $data['customer_id'] = $request->customer_id;
        $data['nama_barang'] = $request->nama_barang;
        $data['tgl_po'] = $request->tgl_po;
        $data['qty'] = $request->qty;
        $data['harga'] = $request->harga;
        $data['margin'] = $finalMargin;
        $data['tambahan_margin'] = $tambahan;
        $data['total'] = $total;
        $data['modal_awal'] = $modal;
        $data['margin_unit'] = ($modal / $qty * ($percentageMargin / 100));

        $oldPo = $po->toArray();
        $oldPoData = clone $po;

        try {
            $po->update($data);
            $newPo = $po->fresh();

            $this->logUpdate($newPo, $oldPo, 'Incoming PO dengan Nomor PO ' . $oldPoData->no_po . ' Dan Nama Barang ' . $oldPoData->nama_barang . ' Di update menjadi ' . 'Incoming PO dengan Nomor PO ' . $po->no_po . ' Dan Nama Barang ' . $po->nama_barang);

            return response()->json([
                'success' => true,
                'message' => 'Updated successfully',
                'redirect_url' => route('incomingPo')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatePo(Request $request, $id)
    {
        $po = Po::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_barang'       => 'required|string',
            'customer_id'       => 'required|exists:tbl_customer,id_cust',
            'tgl_po'            => 'required|date',
            'qty'               => 'required|numeric|min:1',
            'harga'             => 'required|numeric|min:0',
            'margin_percentage' => 'nullable|numeric|min:0',
            'tambahan_margin'   => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $qty = (float) $request->qty;
        $harga = (float) $request->harga;
        $tambahan = (float) ($request->tambahan_margin ?? 0);

        $total = $qty * $harga;
        $modal = $total / 2;
        $percentageMargin = $request->margin_percentage;
        $finalMargin = ($modal * ($percentageMargin / 100)) + $tambahan;
        $data = $validator->validated();
        $data['customer_id'] = $request->customer_id;
        $data['nama_barang'] = $request->nama_barang;
        $data['tgl_po'] = $request->tgl_po;
        $data['qty'] = $request->qty;
        $data['harga'] = $request->harga;
        $data['margin'] = $finalMargin;
        $data['tambahan_margin'] = $tambahan;
        $data['total'] = $total;
        $data['modal_awal'] = $modal;
        $data['margin_unit'] = ($modal / $qty * ($percentageMargin / 100));

        $oldPo = $po->toArray();
        $oldPoData = clone $po;

        try {
            $po->update($data);
            $newPo = $po->fresh();

            $this->logUpdate($newPo, $oldPo, 'PO dengan Nomor PO ' . $oldPoData->no_po . ' Dan Nama Barang ' . $oldPoData->nama_barang . ' Di update menjadi ' . 'PO dengan Nomor PO ' . $po->no_po . ' Dan Nama Barang ' . $po->nama_barang);

            return response()->json([
                'success' => true,
                'message' => 'Updated successfully',
                'redirect_url' => route('incomingPo')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($po_id)
    {
        // Fetch the PO with its customer relationship
        $po = Po::findOrFail($po_id);

        return view('incoming-po-show', compact('po'));
    }

    public function showPoDetails($po_id)
    {
        // Fetch the PO with its customer relationship
        $po = Po::findOrFail($po_id);

        return view('po-show', compact('po'));
    }

    /**
     * Show the form for editing the specified Purchase Order.
     */
    public function edit($po_id)
    {
        $po = Po::findOrFail($po_id);
        $currentCustomer = Customer::where('id_cust', $po->customer_id)->first();
        $customers = Customer::all(); // Needed for the dropdown in edit form

        // Assuming these are your variables
        $totalMargin = $po->margin;
        $modal = $po->modal_awal;
        $tambahanMargin = $po->tambahan_margin; // This must be known/stored

        // Reverse calculation
        $cleanMargin = $totalMargin - $tambahanMargin;
        $marginPercentage = ($cleanMargin / $modal) * 100;

        return view('incoming-po-edit', compact('po', 'customers', 'currentCustomer', 'marginPercentage'));
    }
    public function editPo($po_id)
    {
        $po = Po::findOrFail($po_id);
        $currentCustomer = Customer::where('id_cust', $po->customer_id)->first();
        $customers = Customer::all(); // Needed for the dropdown in edit form

        // Assuming these are your variables
        $totalMargin = $po->margin;
        $modal = $po->modal_awal;
        $tambahanMargin = $po->tambahan_margin; // This must be known/stored

        // Reverse calculation
        $cleanMargin = $totalMargin - $tambahanMargin;
        $marginPercentage = ($cleanMargin / $modal) * 100;

        return view('po-edit', compact('po', 'customers', 'currentCustomer', 'marginPercentage'));
    }

    /**
     * Remove the specified Purchase Order from storage.
     */
    public function destroy($po_id)
    {
        $po = Po::findOrFail($po_id);

        try {
            DB::transaction(function () use ($po) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');

                foreach ($po->deliveries as $delivery) {
                    $invoice = $delivery->invoice;

                    if ($invoice) {
                        $invoice->payment()->delete();
                        $invoice->delete();
                    }

                    $delivery->delete();
                }

                $po->delete();

                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            });

            return response()->json([
                'success' => true,
                'message' => 'PO dan semua data terkait berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function truncate(Request $request)
    {
        $request->validate([
            'confirm' => ['required', 'in:SAYA YAKIN ATAS TINDAKAN INI'],
        ]);

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Order matters — child tables first, parent last
            DB::table('payments')->truncate();
            DB::table('invoices')->truncate();
            DB::table('deliveries')->truncate();
            DB::table('pos')->truncate();

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return response()->json([
                'success' => true,
                'message' => 'Semua data PO beserta invoice, delivery, dan payment berhasil dikosongkan.',
            ]);
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengosongkan data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroyPo($po_id)
    {
        $po = Po::findOrFail($po_id);

        try {
            DB::transaction(function () use ($po) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');

                foreach ($po->deliveries as $delivery) {
                    $invoice = $delivery->invoice;

                    if ($invoice) {
                        $invoice->payment()->delete();
                        $invoice->delete();
                    }

                    $delivery->delete();
                }

                $po->delete();

                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            });

            return response()->json([
                'success' => true,
                'message' => 'PO dan semua data terkait berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function export()
    {
        $this->logExport();
        return Excel::download(new PoExport, 'purchase_orders.xlsx');
    }
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            Excel::import(new PoImport(1), $request->file('file'));
            $this->logImport();
            return response()->json(['success' => true, 'message' => 'Data berhasil diimport.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function importForm()
    {
        return view("po-import");
    }

    public function getStats()
    {
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
    }

    public function getStatsIncoming()
    {
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
    }

    public function filterPoDates(Request $request)
    {
        $startDate = $request->query('startDate');
        $endDate   = $request->query('endDate');

        $activeStatuses = [
            Po::STATUS_PARTIALLY_DELIVERED,
            Po::STATUS_FULLY_DELIVERED,
            Po::STATUS_PARTIALLY_DELIVERED_PARTIALLY_INVOICED,
            Po::STATUS_FULLY_DELIVERED_PARTIALLY_INVOICED,
            Po::STATUS_PARTIALLY_DELIVERED_FULLY_INVOICED,
            Po::STATUS_FULLY_DELIVERED_FULLY_INVOICED,
        ];

        $poQuery = Po::whereIn('status', $activeStatuses);

        if ($startDate) {
            $poQuery->whereDate('tgl_po', '>=', $startDate);
        }
        if ($endDate) {
            $poQuery->whereDate('tgl_po', '<=', $endDate);
        }

        $poAggregates = (clone $poQuery)
            ->selectRaw('
            SUM(margin)      AS total_margin,
            SUM(modal_awal)  AS total_modal,
            SUM(total)       AS total_nilai_po,
            COUNT(*)         AS total_po
        ')
            ->first();

        $invoiceQuery = Invoice::query()
            ->join('tbl_delivery', 'tbl_invoice.delivery_id', '=', 'tbl_delivery.delivery_id')
            ->join('tbl_po',       'tbl_delivery.po_id',      '=', 'tbl_po.po_id')
            ->whereIn('tbl_po.status', $activeStatuses);

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

        return response()->json([
            'totalPo'        => (int)   ($poAggregates->total_po       ?? 0),
            'totalNilaiPo'   => (float) ($poAggregates->total_nilai_po ?? 0),
            'totalMargin'    => (float) ($poAggregates->total_margin   ?? 0),
            'totalModal'     => (float) ($poAggregates->total_modal    ?? 0),

            'totalInvoice'   => (int)   ($invoiceCounts->total_invoice ?? 0),
            'invoiceUnpaid'  => (int)   ($invoiceCounts->unpaid        ?? 0),
            'invoicePaid'    => (int)   ($invoiceCounts->paid          ?? 0),
            'invoiceCancelled' => (int) ($invoiceCounts->cancelled     ?? 0),

            'statusBreakdown' => $poStatusBreakdown,

            'filter' => [
                'startDate' => $startDate,
                'endDate'   => $endDate,
            ],
        ]);
    }
}
