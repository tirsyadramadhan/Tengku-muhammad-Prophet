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
            $query = Po::select('tbl_po.*')
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
                ->addColumn('no_po', function ($row) {
                    $label = $row->no_po ?? 'Tanpa PO';
                    $isTanpa = $label === 'Tanpa PO';

                    $bg    = $isTanpa ? '#f1f5f9' : '#eff6ff';
                    $color = $isTanpa ? '#94a3b8' : '#2563eb';
                    $icon  = $isTanpa ? 'ri-file-unknow-line' : 'ri-file-list-3-line';

                    return <<<HTML
        <div style="display:inline-flex;align-items:center;gap:6px;
                    background:{$bg};color:{$color};
                    border:1px solid {$color}30;border-radius:8px;
                    padding:4px 10px;font-size:0.78rem;font-weight:700;">
            <i class="{$icon}"></i>
            {$label}
        </div>
        HTML;
                })->addColumn('nama_barang', function ($row) {
                    $nama = $row->nama_barang ?? '-';

                    return <<<HTML
    <div style="display:flex;align-items:center;gap:8px;">
        <div style="width:32px;height:32px;border-radius:8px;
                    background:#ede9fe;color:#7c3aed;
                    display:flex;align-items:center;justify-content:center;
                    font-size:1rem;flex-shrink:0;">
            <i class="ri-box-3-line"></i>
        </div>
        <span style="font-size:0.82rem;font-weight:600;color:#1e293b;
                     max-width:120px;white-space:normal;word-break:break-word;
                     line-height:1.3;">
            {$nama}
        </span>
    </div>
    HTML;
                })->addColumn('tgl_po', function ($row) {
                    $tgl      = $row->tgl_po ? \Carbon\Carbon::parse($row->tgl_po) : null;
                    $formated = $tgl ? $tgl->translatedFormat('d M Y') : '-';
                    $relative = $tgl ? $tgl->diffForHumans() : '';

                    return <<<HTML
        <div style="display:flex;flex-direction:column;gap:2px;">
            <span style="font-size:0.82rem;font-weight:700;color:#1e293b;">
                <i class="ri-calendar-check-line me-1" style="color:#16a34a;"></i>
                {$formated}
            </span>
            <span style="font-size:0.7rem;color:#94a3b8;font-weight:500;">
                {$relative}
            </span>
        </div>
        HTML;
                })
                ->addColumn('qty', function ($row) {
                    $qty = number_format((int) $row->qty, 0, ',', '.');

                    return <<<HTML
        <div style="display:inline-flex;align-items:center;gap:6px;
                    background:#e0f2fe;color:#0284c7;
                    border:1px solid #0284c730;border-radius:8px;
                    padding:4px 10px;font-size:0.82rem;font-weight:700;">
            <i class="ri-stack-line"></i>
            {$qty} Unit
        </div>
        HTML;
                })->addColumn('harga', function ($row) {
                    $harga = 'Rp ' . number_format((float) $row->harga, 0, ',', '.');

                    return <<<HTML
        <div style="display:flex;flex-direction:column;gap:1px;">
            <span style="font-size:0.7rem;font-weight:600;text-transform:uppercase;
                         letter-spacing:0.05em;color:#94a3b8;">Per Unit</span>
            <span style="font-size:0.85rem;font-weight:700;color:#7c3aed;">
                {$harga}
            </span>
        </div>
        HTML;
                })->addColumn('total', function ($row) {
                    $total = 'Rp ' . number_format((float) $row->total, 0, ',', '.');

                    return <<<HTML
        <div style="display:flex;flex-direction:column;gap:1px;">
            <span style="font-size:0.7rem;font-weight:600;text-transform:uppercase;
                         letter-spacing:0.05em;color:#94a3b8;">Total PO</span>
            <span style="font-size:0.85rem;font-weight:700;color:#0284c7;">
                {$total}
            </span>
        </div>
        HTML;
                })->addColumn('modal_awal', function ($row) {
                    $modal = 'Rp ' . number_format((float) $row->modal_awal, 0, ',', '.');

                    return <<<HTML
        <div style="display:flex;flex-direction:column;gap:1px;">
            <span style="font-size:0.7rem;font-weight:600;text-transform:uppercase;
                         letter-spacing:0.05em;color:#94a3b8;">Modal</span>
            <span style="font-size:0.85rem;font-weight:700;color:#d97706;">
                {$modal}
            </span>
        </div>
        HTML;
                })->addColumn('margin', function ($row) {
                    $margin     = 'Rp ' . number_format((float) $row->margin, 0, ',', '.');
                    $marginUnit = 'Rp ' . number_format((float) $row->margin_unit, 0, ',', '.');
                    $pct        = $row->modal_awal > 0
                        ? number_format(($row->margin / $row->modal_awal) * 100, 1)
                        : '0.0';

                    [$bg, $color] = match (true) {
                        (float) $row->margin <= 0      => ['#fef2f2', '#dc2626'],
                        (float) $pct < 10              => ['#fff7ed', '#ea580c'],
                        (float) $pct < 25              => ['#fefce8', '#ca8a04'],
                        default                        => ['#f0fdf4', '#16a34a'],
                    };

                    return <<<HTML
        <div style="display:flex;flex-direction:column;gap:3px;">
            <div style="display:inline-flex;align-items:center;gap:5px;
                        background:{$bg};color:{$color};
                        border:1px solid {$color}30;border-radius:8px;
                        padding:3px 9px;font-size:0.82rem;font-weight:700;
                        width:fit-content;">
                <i class="ri-funds-line"></i>
                {$margin}
                <span style="font-size:0.68rem;font-weight:800;
                             background:{$color}18;border-radius:5px;
                             padding:1px 5px;">{$pct}%</span>
            </div>
            <span style="font-size:0.7rem;color:#94a3b8;padding-left:2px;">
                <i class="ri-price-tag-3-line me-1"></i>{$marginUnit} / unit
            </span>
        </div>
        HTML;
                })->addColumn('tambahan_margin', function ($row) {
                    $hasExtra = !is_null($row->tambahan_margin) && (float) $row->tambahan_margin > 0;

                    if (!$hasExtra) {
                        return <<<HTML
        <span style="font-size:0.72rem;color:#94a3b8;font-weight:500;white-space:nowrap;">
            <i class="ri-minus-line"></i> Tidak ada
        </span>
        HTML;
                    }

                    $extra = 'Rp ' . number_format((float) $row->tambahan_margin, 0, ',', '.');

                    return <<<HTML
    <span style="display:inline-flex;align-items:center;gap:4px;
                background:#f0fdf4;color:#16a34a;
                border:1px solid #16a34a30;border-radius:6px;
                padding:2px 6px;font-size:0.78rem;font-weight:700;
                white-space:nowrap;width:fit-content;">
        <i class="ri-add-circle-line" style="font-size:0.8rem;"></i>
        {$extra}
    </span>
    HTML;
                })->addColumn('status', function ($row) {
                    $map = [
                        0 => ['label' => 'Incoming',                            'icon' => 'ri-time-line',                    'color' => '#64748b', 'bg' => '#f1f5f9'],
                        1 => ['label' => 'Open',                                'icon' => 'ri-folder-open-line',             'color' => '#2563eb', 'bg' => '#eff6ff'],
                        2 => ['label' => 'Partially Delivered',                 'icon' => 'ri-truck-line',                   'color' => '#0284c7', 'bg' => '#e0f2fe'],
                        3 => ['label' => 'Fully Delivered',                     'icon' => 'ri-truck-check-line',             'color' => '#0369a1', 'bg' => '#e0f2fe'],
                        4 => ['label' => 'Partial Delivered & Partial Invoice', 'icon' => 'ri-file-edit-line',               'color' => '#7c3aed', 'bg' => '#ede9fe'],
                        5 => ['label' => 'Full Delivered & Partial Invoice',    'icon' => 'ri-file-edit-line',               'color' => '#6d28d9', 'bg' => '#ede9fe'],
                        6 => ['label' => 'Partial Delivered & Full Invoice',    'icon' => 'ri-file-list-3-line',             'color' => '#a21caf', 'bg' => '#fdf4ff'],
                        7 => ['label' => 'Fully Delivered & Fully Invoiced',    'icon' => 'ri-file-check-line',              'color' => '#d97706', 'bg' => '#fefce8'],
                        8 => ['label' => 'Closed',                              'icon' => 'ri-checkbox-circle-line',         'color' => '#16a34a', 'bg' => '#f0fdf4'],
                    ];

                    $s     = $map[(int) $row->status] ?? $map[0];
                    $label = $s['label'];
                    $icon  = $s['icon'];
                    $color = $s['color'];
                    $bg    = $s['bg'];

                    return <<<HTML
    <span style="display:inline-flex;align-items:center;gap:5px;
                 background:{$bg};color:{$color};
                 border:1px solid {$color}30;border-radius:999px;
                 padding:4px 12px;font-size:0.75rem;font-weight:700;
                 letter-spacing:0.03em;white-space:nowrap;">
        <i class="{$icon}" style="font-size:0.85rem;"></i>
        {$label}
    </span>
    HTML;
                })
                ->addColumn('action', function ($row) {
                    $showUrl   = Route::has('po.show')    ? route('po.show',    $row->po_id) : '#';
                    $editUrl   = Route::has('po.edit')    ? route('po.edit',    $row->po_id) : '#';
                    $deleteUrl = Route::has('po.destroy') ? route('po.destroy', $row->po_id) : '#';

                    $user          = Auth::user();
                    $canEditDelete = $user && $user->role_id !== 2;
                    $noPoData      = $row->no_po;
                    $namaBarang    = $row->nama_barang;

                    $editBtn = $canEditDelete ? <<<HTML
                        <li>
                            <a href="{$editUrl}" class="dropdown-item d-flex align-items-center gap-2" title="Edit">
                                <i class="ri-pencil-line text-warning"></i> Edit
                            </a>
                        </li>
                        HTML : '';

                    $deleteBtn = $canEditDelete ? <<<HTML
                        <li>
                            <button type="button"
                                class="dropdown-item d-flex align-items-center gap-2 btn-delete-ajax"
                                data-url="{$deleteUrl}"
                                data-po="No po {$noPoData} Nama Barang {$namaBarang}"
                                title="Delete">
                                <i class="ri-delete-bin-line text-danger"></i> Delete
                            </button>
                        </li>
                        HTML : '';

                    return <<<HTML
                    <div class="dropdown">
                        <button type="button"
                            class="btn btn-sm btn-icon btn-label-secondary"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="ri-more-2-fill"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a href="{$showUrl}" class="dropdown-item d-flex align-items-center gap-2" title="Details">
                                    <i class="ri-eye-line text-info"></i> Details
                                </a>
                            </li>
                            {$editBtn}
                            {$deleteBtn}
                        </ul>
                    </div>
                    HTML;
                })
                // ── no_po ──────────────────────────────────────────────────────────────────
                ->orderColumn('no_po', function ($query, $order) {
                    $query->orderBy('no_po', $order);
                })->filterColumn('no_po', function ($query, $keyword) {
                    $query->where('no_po', 'LIKE', "%{$keyword}%");

                    // ── nama_barang ────────────────────────────────────────────────────────────
                })->orderColumn('nama_barang', function ($query, $order) {
                    $query->orderBy('nama_barang', $order);
                })->filterColumn('nama_barang', function ($query, $keyword) {
                    $query->where('nama_barang', 'LIKE', "%{$keyword}%");

                    // ── tgl_po ─────────────────────────────────────────────────────────────────
                })->orderColumn('tgl_po', function ($query, $order) {
                    $query->orderBy('tgl_po', $order);
                })->filterColumn('tgl_po', function ($query, $keyword) {
                    try {
                        $date = \Carbon\Carbon::createFromFormat('d M Y', $keyword);
                        $query->whereDate('tgl_po', $date->toDateString());
                    } catch (\Exception $e) {
                        try {
                            $date = \Carbon\Carbon::parse($keyword);
                            $query->whereDate('tgl_po', $date->toDateString());
                        } catch (\Exception $e2) {
                            $query->whereRaw("DATE_FORMAT(tgl_po, '%d %b %Y') LIKE ?", ["%{$keyword}%"]);
                        }
                    }

                    // ── qty ────────────────────────────────────────────────────────────────────
                })->orderColumn('qty', function ($query, $order) {
                    $query->orderBy('qty', $order);
                })->filterColumn('qty', function ($query, $keyword) {
                    // supports: "2 Unit", "1.000 Unit", "2"
                    $clean = preg_replace('/[^0-9]/', '', $keyword);
                    if ($clean !== '') {
                        $query->where('qty', (int) $clean);
                    }

                    // ── harga ──────────────────────────────────────────────────────────────────
                })->orderColumn('harga', function ($query, $order) {
                    $query->orderBy('harga', $order);
                })->filterColumn('harga', function ($query, $keyword) {
                    // supports: "Rp 225.000.000", "225000000"
                    $clean = preg_replace('/[^0-9]/', '', $keyword);
                    if ($clean !== '') {
                        $query->whereRaw('CAST(harga AS CHAR) LIKE ?', ["%{$clean}%"]);
                    }

                    // ── total ──────────────────────────────────────────────────────────────────
                })->orderColumn('total', function ($query, $order) {
                    $query->orderBy('total', $order);
                })->filterColumn('total', function ($query, $keyword) {
                    $clean = preg_replace('/[^0-9]/', '', $keyword);
                    if ($clean !== '') {
                        $query->whereRaw('CAST(total AS CHAR) LIKE ?', ["%{$clean}%"]);
                    }
                })
                ->orderColumn('modal_awal', function ($query, $order) {
                    $query->orderBy('modal_awal', $order);
                })->filterColumn('modal_awal', function ($query, $keyword) {
                    // supports: "Rp 225.000.000", "225000000"
                    $clean = preg_replace('/[^0-9]/', '', $keyword);
                    if ($clean !== '') {
                        $query->whereRaw('CAST(modal_awal AS CHAR) LIKE ?', ["%{$clean}%"]);
                    }

                    // ── margin ─────────────────────────────────────────────────────────────────
                })->orderColumn('margin', function ($query, $order) {
                    $query->orderBy('margin', $order);
                })->filterColumn('margin', function ($query, $keyword) {
                    // supports:
                    // "Rp 45.000.000"       → total margin
                    // "20.0%" or "20%"      → percentage of modal
                    // "Rp 22.500 / unit"    → margin per unit
                    if (str_contains($keyword, '%')) {
                        $pct = (float) preg_replace('/[^0-9.]/', '', $keyword);
                        $query->whereRaw(
                            'modal_awal > 0 AND ROUND((margin / modal_awal) * 100, 1) LIKE ?',
                            ["%{$pct}%"]
                        );
                    } elseif (
                        str_contains(strtolower($keyword), '/ unit') ||
                        str_contains(strtolower($keyword), '/unit')
                    ) {
                        $clean = preg_replace('/[^0-9]/', '', $keyword);
                        if ($clean !== '') {
                            $query->whereRaw('CAST(margin_unit AS CHAR) LIKE ?', ["%{$clean}%"]);
                        }
                    } else {
                        $clean = preg_replace('/[^0-9]/', '', $keyword);
                        if ($clean !== '') {
                            $query->whereRaw('CAST(margin AS CHAR) LIKE ?', ["%{$clean}%"]);
                        }
                    }

                    // ── tambahan_margin ────────────────────────────────────────────────────────
                })->orderColumn('tambahan_margin', function ($query, $order) {
                    $query->orderByRaw("ISNULL(tambahan_margin) ASC, tambahan_margin {$order}");
                })->filterColumn('tambahan_margin', function ($query, $keyword) {
                    // supports: "Rp 225.000.000", "tidak ada"
                    $lower = strtolower(trim($keyword));
                    if (str_contains($lower, 'tidak') || str_contains($lower, 'ada')) {
                        $query->where(function ($q) {
                            $q->whereNull('tambahan_margin')->orWhere('tambahan_margin', 0);
                        });
                    } else {
                        $clean = preg_replace('/[^0-9]/', '', $keyword);
                        if ($clean !== '') {
                            $query->whereRaw('CAST(tambahan_margin AS CHAR) LIKE ?', ["%{$clean}%"]);
                        }
                    }

                    // ── status ─────────────────────────────────────────────────────────────────
                })->orderColumn('status', function ($query, $order) {
                    $query->orderBy('status', $order);
                })->filterColumn('status', function ($query, $keyword) {
                    if ($keyword !== '' && is_numeric($keyword)) {
                        $query->where('status', (int) $keyword);
                    }
                })
                ->rawColumns(['qty', 'action', 'status', 'no_po', 'nama_barang', 'harga', 'total', 'modal_awal', 'margin', 'margin_unit', 'tambahan_margin', 'tgl_po',])
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
            $query = Po::from(DB::raw('(
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
                ->addColumn('no_po', function ($row) {
                    $label = $row->no_po ?? 'Tanpa PO';
                    $isTanpa = $label === 'Tanpa PO';

                    $bg    = $isTanpa ? '#f1f5f9' : '#eff6ff';
                    $color = $isTanpa ? '#94a3b8' : '#2563eb';
                    $icon  = $isTanpa ? 'ri-file-unknow-line' : 'ri-file-list-3-line';

                    return <<<HTML
        <div style="display:inline-flex;align-items:center;gap:6px;
                    background:{$bg};color:{$color};
                    border:1px solid {$color}30;border-radius:8px;
                    padding:4px 10px;font-size:0.78rem;font-weight:700;">
            <i class="{$icon}"></i>
            {$label}
        </div>
        HTML;
                })->addColumn('nama_barang', function ($row) {
                    $nama = $row->nama_barang ?? '-';

                    return <<<HTML
    <div style="display:flex;align-items:center;gap:8px;">
        <div style="width:32px;height:32px;border-radius:8px;
                    background:#ede9fe;color:#7c3aed;
                    display:flex;align-items:center;justify-content:center;
                    font-size:1rem;flex-shrink:0;">
            <i class="ri-box-3-line"></i>
        </div>
        <span style="font-size:0.82rem;font-weight:600;color:#1e293b;
                     max-width:120px;white-space:normal;word-break:break-word;
                     line-height:1.3;">
            {$nama}
        </span>
    </div>
    HTML;
                })->addColumn('tgl_po', function ($row) {
                    $tgl      = $row->tgl_po ? \Carbon\Carbon::parse($row->tgl_po) : null;
                    $formated = $tgl ? $tgl->translatedFormat('d M Y') : '-';
                    $relative = $tgl ? $tgl->diffForHumans() : '';

                    return <<<HTML
        <div style="display:flex;flex-direction:column;gap:2px;">
            <span style="font-size:0.82rem;font-weight:700;color:#1e293b;">
                <i class="ri-calendar-check-line me-1" style="color:#16a34a;"></i>
                {$formated}
            </span>
            <span style="font-size:0.7rem;color:#94a3b8;font-weight:500;">
                {$relative}
            </span>
        </div>
        HTML;
                })
                ->addColumn('qty', function ($row) {
                    $qty = number_format((int) $row->qty, 0, ',', '.');

                    return <<<HTML
        <div style="display:inline-flex;align-items:center;gap:6px;
                    background:#e0f2fe;color:#0284c7;
                    border:1px solid #0284c730;border-radius:8px;
                    padding:4px 10px;font-size:0.82rem;font-weight:700;">
            <i class="ri-stack-line"></i>
            {$qty} Unit
        </div>
        HTML;
                })->addColumn('harga', function ($row) {
                    $harga = 'Rp ' . number_format((float) $row->harga, 0, ',', '.');

                    return <<<HTML
        <div style="display:flex;flex-direction:column;gap:1px;">
            <span style="font-size:0.7rem;font-weight:600;text-transform:uppercase;
                         letter-spacing:0.05em;color:#94a3b8;">Per Unit</span>
            <span style="font-size:0.85rem;font-weight:700;color:#7c3aed;">
                {$harga}
            </span>
        </div>
        HTML;
                })->addColumn('total', function ($row) {
                    $total = 'Rp ' . number_format((float) $row->total, 0, ',', '.');

                    return <<<HTML
        <div style="display:flex;flex-direction:column;gap:1px;">
            <span style="font-size:0.7rem;font-weight:600;text-transform:uppercase;
                         letter-spacing:0.05em;color:#94a3b8;">Total PO</span>
            <span style="font-size:0.85rem;font-weight:700;color:#0284c7;">
                {$total}
            </span>
        </div>
        HTML;
                })->addColumn('modal_awal', function ($row) {
                    $modal = 'Rp ' . number_format((float) $row->modal_awal, 0, ',', '.');

                    return <<<HTML
        <div style="display:flex;flex-direction:column;gap:1px;">
            <span style="font-size:0.7rem;font-weight:600;text-transform:uppercase;
                         letter-spacing:0.05em;color:#94a3b8;">Modal</span>
            <span style="font-size:0.85rem;font-weight:700;color:#d97706;">
                {$modal}
            </span>
        </div>
        HTML;
                })->addColumn('margin', function ($row) {
                    $margin     = 'Rp ' . number_format((float) $row->margin, 0, ',', '.');
                    $marginUnit = 'Rp ' . number_format((float) $row->margin_unit, 0, ',', '.');
                    $pct        = $row->modal_awal > 0
                        ? number_format(($row->margin / $row->modal_awal) * 100, 1)
                        : '0.0';

                    [$bg, $color] = match (true) {
                        (float) $row->margin <= 0      => ['#fef2f2', '#dc2626'],
                        (float) $pct < 10              => ['#fff7ed', '#ea580c'],
                        (float) $pct < 25              => ['#fefce8', '#ca8a04'],
                        default                        => ['#f0fdf4', '#16a34a'],
                    };

                    return <<<HTML
        <div style="display:flex;flex-direction:column;gap:3px;">
            <div style="display:inline-flex;align-items:center;gap:5px;
                        background:{$bg};color:{$color};
                        border:1px solid {$color}30;border-radius:8px;
                        padding:3px 9px;font-size:0.82rem;font-weight:700;
                        width:fit-content;">
                <i class="ri-funds-line"></i>
                {$margin}
                <span style="font-size:0.68rem;font-weight:800;
                             background:{$color}18;border-radius:5px;
                             padding:1px 5px;">{$pct}%</span>
            </div>
            <span style="font-size:0.7rem;color:#94a3b8;padding-left:2px;">
                <i class="ri-price-tag-3-line me-1"></i>{$marginUnit} / unit
            </span>
        </div>
        HTML;
                })->addColumn('tambahan_margin', function ($row) {
                    $hasExtra = !is_null($row->tambahan_margin) && (float) $row->tambahan_margin > 0;

                    if (!$hasExtra) {
                        return <<<HTML
        <span style="font-size:0.72rem;color:#94a3b8;font-weight:500;white-space:nowrap;">
            <i class="ri-minus-line"></i> Tidak ada
        </span>
        HTML;
                    }

                    $extra = 'Rp ' . number_format((float) $row->tambahan_margin, 0, ',', '.');

                    return <<<HTML
    <span style="display:inline-flex;align-items:center;gap:4px;
                background:#f0fdf4;color:#16a34a;
                border:1px solid #16a34a30;border-radius:6px;
                padding:2px 6px;font-size:0.78rem;font-weight:700;
                white-space:nowrap;width:fit-content;">
        <i class="ri-add-circle-line" style="font-size:0.8rem;"></i>
        {$extra}
    </span>
    HTML;
                })->addColumn('status', function ($row) {
                    $map = [
                        0 => ['label' => 'Incoming',                            'icon' => 'ri-time-line',                    'color' => '#64748b', 'bg' => '#f1f5f9'],
                        1 => ['label' => 'Open',                                'icon' => 'ri-folder-open-line',             'color' => '#2563eb', 'bg' => '#eff6ff'],
                        2 => ['label' => 'Partially Delivered',                 'icon' => 'ri-truck-line',                   'color' => '#0284c7', 'bg' => '#e0f2fe'],
                        3 => ['label' => 'Fully Delivered',                     'icon' => 'ri-truck-check-line',             'color' => '#0369a1', 'bg' => '#e0f2fe'],
                        4 => ['label' => 'Partial Delivered & Partial Invoice', 'icon' => 'ri-file-edit-line',               'color' => '#7c3aed', 'bg' => '#ede9fe'],
                        5 => ['label' => 'Full Delivered & Partial Invoice',    'icon' => 'ri-file-edit-line',               'color' => '#6d28d9', 'bg' => '#ede9fe'],
                        6 => ['label' => 'Partial Delivered & Full Invoice',    'icon' => 'ri-file-list-3-line',             'color' => '#a21caf', 'bg' => '#fdf4ff'],
                        7 => ['label' => 'Fully Delivered & Fully Invoiced',    'icon' => 'ri-file-check-line',              'color' => '#d97706', 'bg' => '#fefce8'],
                        8 => ['label' => 'Closed',                              'icon' => 'ri-checkbox-circle-line',         'color' => '#16a34a', 'bg' => '#f0fdf4'],
                    ];

                    $s     = $map[(int) $row->status] ?? $map[0];
                    $label = $s['label'];
                    $icon  = $s['icon'];
                    $color = $s['color'];
                    $bg    = $s['bg'];

                    return <<<HTML
    <span style="display:inline-flex;align-items:center;gap:5px;
                 background:{$bg};color:{$color};
                 border:1px solid {$color}30;border-radius:999px;
                 padding:4px 12px;font-size:0.75rem;font-weight:700;
                 letter-spacing:0.03em;white-space:nowrap;">
        <i class="{$icon}" style="font-size:0.85rem;"></i>
        {$label}
    </span>
    HTML;
                })
                ->addColumn('action', function ($row) {
                    $showUrl   = Route::has('po.show')    ? route('po.show',    $row->po_id) : '#';
                    $editUrl   = Route::has('po.edit')    ? route('po.edit',    $row->po_id) : '#';
                    $deleteUrl = Route::has('po.destroy') ? route('po.destroy', $row->po_id) : '#';

                    $user          = Auth::user();
                    $canEditDelete = $user && $user->role_id !== 2;
                    $noPoData      = $row->no_po;
                    $namaBarang    = $row->nama_barang;

                    $editBtn = $canEditDelete ? <<<HTML
                        <li>
                            <a href="{$editUrl}" class="dropdown-item d-flex align-items-center gap-2" title="Edit">
                                <i class="ri-pencil-line text-warning"></i> Edit
                            </a>
                        </li>
                        HTML : '';

                    $deleteBtn = $canEditDelete ? <<<HTML
                        <li>
                            <button type="button"
                                class="dropdown-item d-flex align-items-center gap-2 btn-delete-ajax"
                                data-url="{$deleteUrl}"
                                data-po="No po {$noPoData} Nama Barang {$namaBarang}"
                                title="Delete">
                                <i class="ri-delete-bin-line text-danger"></i> Delete
                            </button>
                        </li>
                        HTML : '';

                    return <<<HTML
                    <div class="dropdown">
                        <button type="button"
                            class="btn btn-sm btn-icon btn-label-secondary"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="ri-more-2-fill"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a href="{$showUrl}" class="dropdown-item d-flex align-items-center gap-2" title="Details">
                                    <i class="ri-eye-line text-info"></i> Details
                                </a>
                            </li>
                            {$editBtn}
                            {$deleteBtn}
                        </ul>
                    </div>
                    HTML;
                })
                // ── no_po ──────────────────────────────────────────────────────────────────
                ->orderColumn('no_po', function ($query, $order) {
                    $query->orderBy('no_po', $order);
                })->filterColumn('no_po', function ($query, $keyword) {
                    $query->where('no_po', 'LIKE', "%{$keyword}%");

                    // ── nama_barang ────────────────────────────────────────────────────────────
                })->orderColumn('nama_barang', function ($query, $order) {
                    $query->orderBy('nama_barang', $order);
                })->filterColumn('nama_barang', function ($query, $keyword) {
                    $query->where('nama_barang', 'LIKE', "%{$keyword}%");

                    // ── tgl_po ─────────────────────────────────────────────────────────────────
                })->orderColumn('tgl_po', function ($query, $order) {
                    $query->orderBy('tgl_po', $order);
                })->filterColumn('tgl_po', function ($query, $keyword) {
                    try {
                        $date = \Carbon\Carbon::createFromFormat('d M Y', $keyword);
                        $query->whereDate('tgl_po', $date->toDateString());
                    } catch (\Exception $e) {
                        try {
                            $date = \Carbon\Carbon::parse($keyword);
                            $query->whereDate('tgl_po', $date->toDateString());
                        } catch (\Exception $e2) {
                            $query->whereRaw("DATE_FORMAT(tgl_po, '%d %b %Y') LIKE ?", ["%{$keyword}%"]);
                        }
                    }

                    // ── qty ────────────────────────────────────────────────────────────────────
                })->orderColumn('qty', function ($query, $order) {
                    $query->orderBy('qty', $order);
                })->filterColumn('qty', function ($query, $keyword) {
                    // supports: "2 Unit", "1.000 Unit", "2"
                    $clean = preg_replace('/[^0-9]/', '', $keyword);
                    if ($clean !== '') {
                        $query->where('qty', (int) $clean);
                    }

                    // ── harga ──────────────────────────────────────────────────────────────────
                })->orderColumn('harga', function ($query, $order) {
                    $query->orderBy('harga', $order);
                })->filterColumn('harga', function ($query, $keyword) {
                    // supports: "Rp 225.000.000", "225000000"
                    $clean = preg_replace('/[^0-9]/', '', $keyword);
                    if ($clean !== '') {
                        $query->whereRaw('CAST(harga AS CHAR) LIKE ?', ["%{$clean}%"]);
                    }

                    // ── total ──────────────────────────────────────────────────────────────────
                })->orderColumn('total', function ($query, $order) {
                    $query->orderBy('total', $order);
                })->filterColumn('total', function ($query, $keyword) {
                    $clean = preg_replace('/[^0-9]/', '', $keyword);
                    if ($clean !== '') {
                        $query->whereRaw('CAST(total AS CHAR) LIKE ?', ["%{$clean}%"]);
                    }
                })
                ->orderColumn('modal_awal', function ($query, $order) {
                    $query->orderBy('modal_awal', $order);
                })->filterColumn('modal_awal', function ($query, $keyword) {
                    // supports: "Rp 225.000.000", "225000000"
                    $clean = preg_replace('/[^0-9]/', '', $keyword);
                    if ($clean !== '') {
                        $query->whereRaw('CAST(modal_awal AS CHAR) LIKE ?', ["%{$clean}%"]);
                    }

                    // ── margin ─────────────────────────────────────────────────────────────────
                })->orderColumn('margin', function ($query, $order) {
                    $query->orderBy('margin', $order);
                })->filterColumn('margin', function ($query, $keyword) {
                    // supports:
                    // "Rp 45.000.000"       → total margin
                    // "20.0%" or "20%"      → percentage of modal
                    // "Rp 22.500 / unit"    → margin per unit
                    if (str_contains($keyword, '%')) {
                        $pct = (float) preg_replace('/[^0-9.]/', '', $keyword);
                        $query->whereRaw(
                            'modal_awal > 0 AND ROUND((margin / modal_awal) * 100, 1) LIKE ?',
                            ["%{$pct}%"]
                        );
                    } elseif (
                        str_contains(strtolower($keyword), '/ unit') ||
                        str_contains(strtolower($keyword), '/unit')
                    ) {
                        $clean = preg_replace('/[^0-9]/', '', $keyword);
                        if ($clean !== '') {
                            $query->whereRaw('CAST(margin_unit AS CHAR) LIKE ?', ["%{$clean}%"]);
                        }
                    } else {
                        $clean = preg_replace('/[^0-9]/', '', $keyword);
                        if ($clean !== '') {
                            $query->whereRaw('CAST(margin AS CHAR) LIKE ?', ["%{$clean}%"]);
                        }
                    }

                    // ── tambahan_margin ────────────────────────────────────────────────────────
                })->orderColumn('tambahan_margin', function ($query, $order) {
                    $query->orderByRaw("ISNULL(tambahan_margin) ASC, tambahan_margin {$order}");
                })->filterColumn('tambahan_margin', function ($query, $keyword) {
                    // supports: "Rp 225.000.000", "tidak ada"
                    $lower = strtolower(trim($keyword));
                    if (str_contains($lower, 'tidak') || str_contains($lower, 'ada')) {
                        $query->where(function ($q) {
                            $q->whereNull('tambahan_margin')->orWhere('tambahan_margin', 0);
                        });
                    } else {
                        $clean = preg_replace('/[^0-9]/', '', $keyword);
                        if ($clean !== '') {
                            $query->whereRaw('CAST(tambahan_margin AS CHAR) LIKE ?', ["%{$clean}%"]);
                        }
                    }

                    // ── status ─────────────────────────────────────────────────────────────────
                })->orderColumn('status', function ($query, $order) {
                    $query->orderBy('status', $order);
                })->filterColumn('status', function ($query, $keyword) {
                    if ($keyword !== '' && is_numeric($keyword)) {
                        $query->where('status', (int) $keyword);
                    }
                })
                ->rawColumns(['qty', 'action', 'status', 'no_po', 'nama_barang', 'harga', 'total', 'modal_awal', 'margin', 'margin_unit', 'tambahan_margin', 'tgl_po',])
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
            'confirm' => ['required', 'string', function ($attribute, $value, $fail) {
                if ($value !== "SAYA YAKIN ATAS TINDAKAN INI") {
                    $fail('Konfirmasi tidak sesuai. Ketik tepat: SAYA YAKIN ATAS TINDAKAN INI');
                }
            }],
        ]);

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Order matters — child tables first, parent last
            DB::table('tbl_payment')->truncate();
            DB::table('tbl_invoice')->truncate();
            DB::table('tbl_delivery')->truncate();
            DB::table('tbl_po')->truncate();

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
