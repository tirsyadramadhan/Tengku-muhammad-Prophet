<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class InvoiceController extends Controller
{

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Invoice::query()
                ->leftJoin('tbl_delivery', 'tbl_invoice.delivery_id', '=', 'tbl_delivery.delivery_id')
                ->leftJoin('tbl_po', 'tbl_delivery.po_id', '=', 'tbl_po.po_id')
                ->leftJoin('tbl_payment', 'tbl_invoice.invoice_id', '=', 'tbl_payment.invoice_id')
                ->select([
                    'tbl_invoice.*',
                    'tbl_delivery.delivery_no',
                    'tbl_delivery.qty_delivered as qty_delivered',
                    'tbl_delivery.delivered_at',
                    'tbl_delivery.delivered_status',
                    'tbl_delivery.invoiced_status',
                    'tbl_po.no_po',
                    'tbl_po.nama_barang',
                    'tbl_po.harga',
                    'tbl_po.total as po_total',
                    'tbl_po.qty as qty',
                    DB::raw('tbl_delivery.qty_delivered * tbl_po.harga as invoice_amount'),
                    'tbl_payment.payment_date',
                    'tbl_payment.amount as paid_amount'
                ]);

            return DataTables::of($data)
                ->addIndexColumn()
                // ── No PO ──────────────────────────────────────────────────────────────────
                ->addColumn('no_po', function ($row) {
                    $label   = $row->no_po ?? 'Tanpa PO';
                    $isTanpa = $label === 'Tanpa PO';
                    $bg      = $isTanpa ? '#f1f5f9' : '#eff6ff';
                    $color   = $isTanpa ? '#94a3b8' : '#2563eb';
                    $icon    = $isTanpa ? 'ri-file-unknow-line' : 'ri-file-list-3-line';

                    return <<<HTML
                    <div style="display:inline-flex;align-items:center;gap:6px;
                                background:{$bg};color:{$color};
                                border:1px solid {$color}30;border-radius:8px;
                                padding:4px 10px;font-size:0.78rem;font-weight:700;">
                        <i class="{$icon}"></i>
                        {$label}
                    </div>
                    HTML;

                    // ── Nama Barang ────────────────────────────────────────────────────────────
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
                                    max-width:120px;white-space:normal;word-break:break-word;line-height:1.3;">
                            {$nama}
                        </span>
                    </div>
                    HTML;

                    // ── Delivery No ────────────────────────────────────────────────────────────
                })->addColumn('delivery_no', function ($row) {
                    $no = $row->delivery_no ?? '-';

                    return <<<HTML
                    <div style="display:inline-flex;align-items:center;gap:6px;
                                background:#f0f9ff;color:#0284c7;
                                border:1px solid #0284c730;border-radius:8px;
                                padding:4px 10px;font-size:0.78rem;font-weight:700;">
                        <i class="ri-truck-line"></i>
                        {$no}
                    </div>
                    HTML;

                    // ── Qty Delivered ──────────────────────────────────────────────────────────
                })->addColumn('qty_delivered', function ($row) {
                    $qty   = number_format((float) $row->qty_delivered, 0, ',', '.');
                    $qtyPo = number_format((float) ($row->qty ?? 0), 0, ',', '.');
                    $pct   = ($row->qty ?? 0) > 0
                        ? round(($row->qty_delivered / $row->qty) * 100)
                        : 0;

                    [$bg, $color, $barColor] = match (true) {
                        $pct <= 0  => ['#fef2f2', '#dc2626', '#fca5a5'],
                        $pct < 50  => ['#fff7ed', '#ea580c', '#fdba74'],
                        $pct < 100 => ['#eff6ff', '#2563eb', '#93c5fd'],
                        default    => ['#f0fdf4', '#16a34a', '#86efac'],
                    };

                    return <<<HTML
    <div style="display:flex;flex-direction:column;gap:4px;min-width:100px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:0.82rem;font-weight:700;color:#1e293b;">
                <i class="ri-stack-line me-1" style="color:{$color};"></i>{$qty}
            </span>
            <span style="font-size:0.7rem;font-weight:700;color:{$color};
                         background:{$bg};border:1px solid {$color}30;
                         border-radius:999px;padding:1px 7px;">
                {$pct}%
            </span>
        </div>
        <div style="height:5px;background:#e2e8f0;border-radius:999px;overflow:hidden;">
            <div style="height:100%;width:{$pct}%;background:{$barColor};
                        border-radius:999px;transition:width 0.4s ease;"></div>
        </div>
        <span style="font-size:0.68rem;color:#94a3b8;">dari {$qtyPo} Unit</span>
    </div>
    HTML;

                    // ── Delivered Status ───────────────────────────────────────────────────────
                })->addColumn('delivered_status', function ($row) {
                    $status = (int) $row->delivered_status;

                    if ($status === 1) {
                        return <<<HTML
                        <span style="display:inline-flex;align-items:center;gap:5px;
                                    background:#f0fdf4;color:#16a34a;
                                    border:1px solid #16a34a30;border-radius:999px;
                                    padding:4px 12px;font-size:0.75rem;font-weight:700;
                                    white-space:nowrap;">
                            <i class="ri-checkbox-circle-line" style="font-size:0.9rem;"></i>
                            Delivered
                        </span>
                        HTML;
                    }

                    return <<<HTML
                    <span style="display:inline-flex;align-items:center;gap:5px;
                                background:#f1f5f9;color:#64748b;
                                border:1px solid #64748b30;border-radius:999px;
                                padding:4px 12px;font-size:0.75rem;font-weight:700;
                                white-space:nowrap;">
                        <i class="ri-time-line" style="font-size:0.85rem;"></i>
                        Pending
                    </span>
                    HTML;

                    // ── Nomor Invoice ──────────────────────────────────────────────────────────
                })->addColumn('nomor_invoice', function ($row) {
                    $no = $row->nomor_invoice ?? '-';

                    return <<<HTML
                    <div style="display:inline-flex;align-items:center;gap:6px;
                                background:#fdf4ff;color:#a21caf;
                                border:1px solid #a21caf30;border-radius:8px;
                                padding:4px 10px;font-size:0.78rem;font-weight:700;">
                        <i class="ri-file-list-3-line"></i>
                        {$no}
                    </div>
                    HTML;

                    // ── Tanggal Invoice ────────────────────────────────────────────────────────
                })->addColumn('tgl_invoice', function ($row) {
                    if (!$row->tgl_invoice) {
                        return <<<HTML
                        <span style="font-size:0.75rem;color:#94a3b8;font-weight:500;">
                            <i class="ri-minus-line me-1"></i>Tidak ada
                        </span>
                        HTML;
                    }

                    $date     = \Carbon\Carbon::parse($row->tgl_invoice);
                    $dateStr  = $date->translatedFormat('d M Y');
                    $relative = $date->diffForHumans();

                    return <<<HTML
                    <div style="display:flex;flex-direction:column;gap:2px;">
                        <span style="font-size:0.82rem;font-weight:700;color:#1e293b;">
                            <i class="ri-calendar-line me-1" style="color:#7c3aed;"></i>
                            {$dateStr}
                        </span>
                        <span style="font-size:0.7rem;color:#94a3b8;font-weight:500;">
                            {$relative}
                        </span>
                    </div>
                    HTML;

                    // ── Due Date ───────────────────────────────────────────────────────────────
                })->addColumn('due_date', function ($row) {
                    if (!$row->due_date) {
                        return <<<HTML
                        <span style="font-size:0.75rem;color:#94a3b8;font-weight:500;">
                            <i class="ri-minus-line me-1"></i>Tidak ada
                        </span>
                        HTML;
                    }

                    $now    = \Carbon\Carbon::now();
                    $due    = \Carbon\Carbon::parse($row->due_date);
                    $isPast = $due->isPast();

                    $diff   = $now->diff($due);
                    $years  = $diff->y;
                    $months = $diff->m;
                    $days   = $diff->d;
                    $hours  = $diff->h;

                    $parts = [];
                    if ($years)  $parts[] = "{$years} tahun";
                    if ($months) $parts[] = "{$months} bulan";
                    if ($days)   $parts[] = "{$days} hari";
                    if ($hours)  $parts[] = "{$hours} jam";
                    if (empty($parts)) $parts[] = "Hari ini";

                    $duration = implode(' ', $parts);
                    $dateStr  = $due->translatedFormat('d M Y');

                    if ($isPast) {
                        $label  = "{$duration} yang lalu";
                        $icon   = 'ri-alarm-warning-line';
                        $color  = '#dc2626';
                        $bg     = '#fef2f2';
                        $subClr = '#f87171';
                    } elseif ($due->diffInDays($now) <= 7) {
                        $label  = "{$duration} lagi";
                        $icon   = 'ri-alarm-line';
                        $color  = '#d97706';
                        $bg     = '#fefce8';
                        $subClr = '#fbbf24';
                    } else {
                        $label  = "{$duration} lagi";
                        $icon   = 'ri-timer-line';
                        $color  = '#0284c7';
                        $bg     = '#e0f2fe';
                        $subClr = '#94a3b8';
                    }

                    return <<<HTML
                    <div style="display:flex;flex-direction:column;gap:3px;">
                        <div style="display:inline-flex;align-items:center;gap:5px;
                                    background:{$bg};color:{$color};
                                    border:1px solid {$color}30;border-radius:8px;
                                    padding:3px 9px;font-size:0.78rem;font-weight:700;
                                    width:fit-content;white-space:nowrap;">
                            <i class="{$icon}" style="font-size:0.85rem;"></i>
                            {$label}
                        </div>
                        <span style="font-size:0.68rem;color:{$subClr};padding-left:2px;">
                            <i class="ri-calendar-line me-1"></i>{$dateStr}
                        </span>
                    </div>
                    HTML;

                    // ── Status Invoice ─────────────────────────────────────────────────────────
                })->addColumn('status_invoice', function ($row) {
                    $map = [
                        0 => ['label' => 'Unpaid', 'icon' => 'ri-time-line',            'color' => '#64748b', 'bg' => '#f1f5f9'],
                        1 => ['label' => 'Paid',   'icon' => 'ri-checkbox-circle-line', 'color' => '#16a34a', 'bg' => '#f0fdf4'],
                    ];

                    $s          = $map[(int) $row->status_invoice] ?? $map[0];
                    $label      = $s['label'];
                    $icon       = $s['icon'];
                    $color      = $s['color'];
                    $bg         = $s['bg'];
                    $invoiceId  = $row->invoice_id;
                    $nomorInv   = $row->nomor_invoice ?? '-';
                    $csrfToken  = csrf_token();
                    $payUrl     = route('payInvoice', $invoiceId);

                    $chip = <<<HTML
                    <span style="display:inline-flex;align-items:center;gap:5px;
                                background:{$bg};color:{$color};
                                border:1px solid {$color}30;border-radius:999px;
                                padding:4px 12px;font-size:0.75rem;font-weight:700;
                                letter-spacing:0.03em;white-space:nowrap;">
                        <i class="{$icon}" style="font-size:0.85rem;"></i>
                        {$label}
                    </span>
                    HTML;

                    if ((int) $row->status_invoice === 1) {
                        return <<<HTML
                        <div style="display:flex;flex-direction:column;gap:5px;align-items:flex-start;">
                            {$chip}
                        </div>
                        HTML;
                    }

                    return <<<HTML
                    <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-start;">
                        {$chip}
                        <button class="btn-pay-now"
                            data-id="{$invoiceId}"
                            data-url="{$payUrl}"
                            data-token="{$csrfToken}"
                            data-nomor="{$nomorInv}"
                            style="display:inline-flex;align-items:center;gap:5px;
                                background:linear-gradient(135deg,#16a34a,#15803d);
                                color:#fff;border:none;border-radius:8px;
                                padding:5px 12px;font-size:0.76rem;font-weight:700;
                                cursor:pointer;white-space:nowrap;
                                box-shadow:0 2px 8px #16a34a40;
                                transition:opacity 0.2s ease;"
                            onmouseover="this.style.opacity='0.85'"
                            onmouseout="this.style.opacity='1'">
                            <i class="ri-secure-payment-line" style="font-size:0.85rem;"></i>
                            Bayar Sekarang
                        </button>
                    </div>
                    HTML;
                })
                ->addColumn('action', function ($row) {
                    $showUrl   = Route::has('invoice.show')    ? route('invoice.show',    $row->invoice_id) : '#';
                    $editUrl   = Route::has('invoice.edit')    ? route('invoice.edit',    $row->invoice_id) : '#';
                    $deleteUrl = Route::has('invoice.destroy') ? route('invoice.destroy', $row->invoice_id) : '#';

                    $user          = Auth::user();
                    $canEditDelete = $user && $user->role_id !== 2;

                    $deliveryNo  = $row->delivery->delivery_no;
                    $namaBarang  = $row->delivery->po->nama_barang;
                    $noPo        = $row->delivery->po->no_po;

                    $editItem = $canEditDelete ? <<<HTML
                        <li>
                            <a href="{$editUrl}" class="dropdown-item text-warning">
                                <i class="ri-pencil-line me-2"></i>Edit
                            </a>
                        </li>
                    HTML : '';

                    $deleteItem = $canEditDelete ? <<<HTML
                        <li>
                            <button type="button"
                                class="dropdown-item text-danger btn-delete-ajax"
                                data-url="{$deleteUrl}"
                                data-po="No delivery {$deliveryNo} yang terkait PO {$namaBarang} ({$noPo})">
                                <i class="ri-delete-bin-line me-2"></i>Delete
                            </button>
                        </li>
                    HTML : '';

                    return <<<HTML
                    <div class="dropdown">
                        <button type="button"
                            class="btn btn-sm btn-icon btn-label-secondary"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="ri-more-2-line"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li>
                                <a href="{$showUrl}" class="dropdown-item text-info">
                                    <i class="ri-eye-line me-2"></i>Details
                                </a>
                            </li>
                            {$editItem}
                            {$deleteItem}
                        </ul>
                    </div>
                    HTML;
                })
                ->orderColumn('no_po', 'tbl_po.no_po $1')
                ->filterColumn('no_po', function ($query, $keyword) {
                    $query->where('tbl_po.no_po', 'like', "%{$keyword}%");
                })
                ->orderColumn('nama_barang', 'tbl_po.nama_barang $1')
                ->filterColumn('nama_barang', function ($query, $keyword) {
                    $query->where('tbl_po.nama_barang', 'like', "%{$keyword}%");
                })
                ->orderColumn('delivery_no', 'tbl_delivery.delivery_no $1')
                ->filterColumn('delivery_no', function ($query, $keyword) {
                    $query->where('tbl_delivery.delivery_no', 'like', "%{$keyword}%");
                })
                ->orderColumn('qty_delivered', 'tbl_delivery.qty_delivered $1')
                ->filterColumn('qty_delivered', function ($query, $keyword) {
                    $kw = trim($keyword);

                    if (preg_match('/dari\s+(\d+(?:\.\d+)?)\s+unit/i', $kw, $m)) {
                        $query->where('qty', $m[1]);
                    } elseif (str_contains($keyword, '%') || str_contains($keyword, '-')) {
                        $clean = preg_replace('/[^0-9.]/', '', $keyword);
                        if ($clean !== '') {
                            $query->whereRaw(
                                'tbl_po.qty > 0 AND ROUND((tbl_delivery.qty_delivered / tbl_po.qty) * 100) LIKE ?',
                                ["%{$clean}%"]
                            );
                        }
                        return;
                    } elseif (is_numeric($kw)) {
                        $query->where('qty_delivered', (float) $kw);
                    } else {
                        $query->where('qty_delivered', 'like', "%{$kw}%");
                    }
                })
                ->orderColumn('delivered_status', 'tbl_delivery.delivered_status $1')
                ->filterColumn('delivered_status', function ($query, $keyword) {
                    $map = [
                        'pending'   => 0,
                        'delivered' => 1,
                    ];
                    $kw = strtolower(trim($keyword));

                    if (array_key_exists($kw, $map)) {
                        $query->where('tbl_delivery.delivered_status', $map[$kw]);
                    } elseif (is_numeric($kw)) {
                        $query->where('tbl_delivery.delivered_status', (int) $kw);
                    } else {
                        $query->where('tbl_delivery.delivered_status', 'like', "%{$keyword}%");
                    }
                })
                ->orderColumn('tgl_invoice', 'tbl_invoice.tgl_invoice $1')
                ->filterColumn('tgl_invoice', function ($query, $keyword) {
                    $lower = strtolower(trim($keyword));

                    if (str_contains($lower, 'belum')) {
                        $query->whereNull('tbl_invoice.tgl_invoice');
                        return;
                    }

                    if (
                        str_contains($lower, 'ago')   ||
                        str_contains($lower, 'month') ||
                        str_contains($lower, 'year')  ||
                        str_contains($lower, 'day')   ||
                        str_contains($lower, 'hour')  ||
                        str_contains($lower, 'week')
                    ) {
                        try {
                            $date = \Carbon\Carbon::parse($keyword);
                            $query->whereBetween('tbl_invoice.tgl_invoice', [
                                $date->copy()->subDay()->toDateString(),
                                $date->copy()->addDay()->toDateString(),
                            ]);
                        } catch (\Exception $e) {
                            // fallback — do nothing
                        }
                        return;
                    }

                    // "02 Feb 2026" or any parseable date
                    try {
                        $date = \Carbon\Carbon::createFromFormat('d M Y', trim($keyword));
                        $query->whereDate('tbl_invoice.tgl_invoice', $date->toDateString());
                    } catch (\Exception $e) {
                        try {
                            $date = \Carbon\Carbon::parse($keyword);
                            $query->whereDate('tbl_invoice.tgl_invoice', $date->toDateString());
                        } catch (\Exception $e2) {
                            $query->whereRaw(
                                "DATE_FORMAT(tbl_invoice.tgl_invoice, '%d %b %Y') LIKE ?",
                                ["%{$keyword}%"]
                            );
                        }
                    }
                })
                ->orderColumn('due_date', 'tbl_invoice.due_date $1')
                ->filterColumn('due_date', function ($query, $keyword) {
                    $lower = strtolower(trim($keyword));

                    // "11 hari 12 jam lagi" → future relative date
                    if (str_contains($lower, 'lagi')) {
                        $years  = 0;
                        $months = 0;
                        $days   = 0;
                        $hours  = 0;
                        if (preg_match('/(\d+)\s*tahun/', $lower, $m)) $years  = (int) $m[1];
                        if (preg_match('/(\d+)\s*bulan/', $lower, $m)) $months = (int) $m[1];
                        if (preg_match('/(\d+)\s*hari/',  $lower, $m)) $days   = (int) $m[1];
                        if (preg_match('/(\d+)\s*jam/',   $lower, $m)) $hours  = (int) $m[1];
                        $date = \Carbon\Carbon::now()
                            ->addYears($years)
                            ->addMonths($months)
                            ->addDays($days)
                            ->addHours($hours);
                        $query->whereBetween('tbl_invoice.due_date', [
                            $date->copy()->subDay()->toDateString(),
                            $date->copy()->addDay()->toDateString(),
                        ]);
                        return;
                    }

                    // "7 bulan 23 hari 8 jam yang lalu" → extract relative and compute date range
                    if (
                        str_contains($lower, 'yang lalu') ||
                        str_contains($lower, 'tahun')     ||
                        str_contains($lower, 'bulan')     ||
                        str_contains($lower, 'hari')      ||
                        str_contains($lower, 'jam')
                    ) {
                        // extract numbers with their unit and reconstruct a Carbon date
                        $years  = 0;
                        $months = 0;
                        $days = 0;
                        $hours = 0;
                        if (preg_match('/(\d+)\s*tahun/', $lower, $m))  $years  = (int) $m[1];
                        if (preg_match('/(\d+)\s*bulan/', $lower, $m))  $months = (int) $m[1];
                        if (preg_match('/(\d+)\s*hari/',  $lower, $m))  $days   = (int) $m[1];
                        if (preg_match('/(\d+)\s*jam/',   $lower, $m))  $hours  = (int) $m[1];

                        $date = \Carbon\Carbon::now()
                            ->subYears($years)
                            ->subMonths($months)
                            ->subDays($days)
                            ->subHours($hours);

                        // search within ±1 day tolerance
                        $query->whereBetween('tbl_invoice.due_date', [
                            $date->copy()->subDay()->toDateString(),
                            $date->copy()->addDay()->toDateString(),
                        ]);
                        return;
                    }

                    // "23 Jul 2025" or any parseable date
                    try {
                        $date = \Carbon\Carbon::createFromFormat('d M Y', trim($keyword));
                        $query->whereDate('tbl_invoice.due_date', $date->toDateString());
                    } catch (\Exception $e) {
                        try {
                            $date = \Carbon\Carbon::parse($keyword);
                            $query->whereDate('tbl_invoice.due_date', $date->toDateString());
                        } catch (\Exception $e2) {
                            $query->whereRaw(
                                "DATE_FORMAT(tbl_invoice.due_date, '%d %b %Y') LIKE ?",
                                ["%{$keyword}%"]
                            );
                        }
                    }
                })
                ->orderColumn('status_invoice', 'tbl_invoice.status_invoice $1')
                ->filterColumn('status_invoice', function ($query, $keyword) {
                    $map = [
                        'unpaid'    => 0,
                        'paid'      => 1,
                        'cancelled' => 2,
                    ];
                    $kw = strtolower(trim($keyword));

                    if (array_key_exists($kw, $map)) {
                        $query->where('tbl_invoice.status_invoice', $map[$kw]);
                    } elseif (is_numeric($kw)) {
                        $query->where('tbl_invoice.status_invoice', (int) $kw);
                    } else {
                        $query->where('tbl_invoice.status_invoice', 'like', "%{$keyword}%");
                    }
                })
                ->orderColumn('nomor_invoice', 'tbl_invoice.nomor_invoice $1')
                ->filterColumn('nomor_invoice', function ($query, $keyword) {
                    $query->where('tbl_invoice.nomor_invoice', 'like', "%{$keyword}%");
                })
                ->rawColumns(['no_po', 'nama_barang', 'delivery_no', 'action', 'qty_delivered', 'delivered_status', 'nomor_invoice', 'tgl_invoice', 'due_date', 'status_invoice',])
                ->make(true);
        }

        // Stats Calculation (unchanged)
        $stats = [
            'total'          => Invoice::count(),
            'unpaid_count'   => Invoice::where('status_invoice', 0)->count(),
            'unpaid_amount'  => Invoice::where('status_invoice', 0)
                ->join('tbl_delivery', 'tbl_invoice.delivery_id', '=', 'tbl_delivery.delivery_id')
                ->join('tbl_po', 'tbl_delivery.po_id', '=', 'tbl_po.po_id')
                ->sum('tbl_po.total'),
            'paid_amount'    => Invoice::where('status_invoice', 1)
                ->join('tbl_delivery', 'tbl_invoice.delivery_id', '=', 'tbl_delivery.delivery_id')
                ->join('tbl_po', 'tbl_delivery.po_id', '=', 'tbl_po.po_id')
                ->sum('tbl_po.total'),
            'paid_count'     => Invoice::where('status_invoice', 1)->count(),
            'overdue_count'  => Invoice::where('status_invoice', 0)
                ->where('due_date', '<', now())
                ->count(),
        ];

        return view('invoice-index', compact('stats'));
    }
    public function create()
    {
        // Only fetch deliveries that DO NOT have an invoice record yet
        $deliveries = Delivery::whereDoesntHave('invoice')
            ->with(['po.customer'])
            ->get();

        return view('invoice-create', compact('deliveries'));
    }

    // InvoiceController.php

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'delivery_id' => 'required|exists:tbl_delivery,delivery_id|unique:tbl_invoice,delivery_id',
            'tgl_invoice' => 'required|date',
            'due_date'    => 'required|date|after:tgl_invoice',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $delivery = Delivery::with('po')->findOrFail($request->delivery_id);

            $invoiceNumber = 'INV-' . now('Asia/Jakarta')->format('Ymd') . '-' . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'delivery_id'    => $delivery->delivery_id,
                'tgl_invoice'    => $request->tgl_invoice,
                'nomor_invoice'  => $invoiceNumber,
                'due_date'       => $request->due_date,
                'status_invoice' => $request->pay_now == 1 ? 1 : 0,
                'input_date'     => now('Asia/Jakarta'),
                'input_by'       => Auth::id() ?? 1,
            ]);

            $this->logCreate($invoice, 'Invoice dengan nomor invoice ' . $invoiceNumber . ' yang terhubung ke delivery ' . $invoice->delivery->delivery_no . ' dengan PO ' . $invoice->delivery->po->no_po . ' (' . $invoice->delivery->po->nama_barang . ') ' . ' Di tambahkan');

            // ← create payment if pay_now checkbox is checked
            if ($request->has('pay_now') && $request->pay_now == 1) {
                Payment::create([
                    'invoice_id'               => $invoice->invoice_id,
                    'payment_date'             => now('Asia/Jakarta'),
                    'payment_date_estimation'  => $request->due_date,
                    'payment_status'           => $request->pay_now == 1 ? 1 : 0,
                    'amount'                   => $delivery->po->harga * $delivery->qty_delivered ?? 0,
                    'metode_bayar'             => null,
                    'bukti_bayar'              => null,
                    'description'              => 'Pembayaran langsung saat pembuatan invoice ' . $invoiceNumber,
                    'input_by'                 => Auth::id() ?? 1,
                    'input_date'               => now('Asia/Jakarta'),
                ]);
            }

            $invoice->syncInvoiceStatus();
            $invoice->delivery->syncInvoicedStatus();

            return response()->json([
                'success'      => true,
                'message'      => 'Invoice created successfully',
                'redirect_url' => route('invoice.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating invoice: ' . $e->getMessage()
            ], 500);
        }
    }
    public function edit($id)
    {
        $item = Invoice::findOrFail($id);
        $currentDelivery = Delivery::findOrFail($item->delivery_id);
        $deliveries = Delivery::with('po')->get();

        return view('invoice-edit', compact('item', 'currentDelivery', 'deliveries'));
    }

    public function update(Request $request, $id)
    {
        $item = Invoice::findOrFail($id);

        $request->validate([
            'delivery_id' => 'sometimes|exists:tbl_delivery,delivery_id',
            'tgl_invoice' => 'required|date',
            'due_date'    => 'required|date|after:tgl_invoice',
        ]);

        $oldInvoice    = $item->toArray();
        $oldDeliveryModel = $item->delivery;

        $item->update([
            'delivery_id'    => $request->filled('delivery_id') ? $request->delivery_id : $item->delivery_id,
            'tgl_invoice'    => $request->tgl_invoice,
            'due_date'       => $request->due_date,
            'status_invoice' => $request->pay_now == 1 ? 1 : 0,
            'edit_by'        => Auth::id() ?? 1,
            'edit_date'      => now('Asia/Jakarta'),
        ]);

        $oldDeliveryModel->syncInvoicedStatus();

        if ($request->pay_now == 0) {
            $oldDeliveryModel->invoice->payment->delete();
        }

        if ($request->pay_now == 1) {
            if ($item->payment()->exists()) {
                $delivery = $item->fresh()->delivery;
                $delivery->syncInvoicedStatus();
                $payment = $item->fresh()->payment;

                $payment->update([
                    'payment_date'            => now('Asia/Jakarta'),
                    'payment_date_estimation' => $request->due_date,
                    'payment_status'          => $request->pay_now == 1 ? 1 : 0,
                    'amount'                  => $delivery->po->harga * $delivery->qty_delivered ?? 0,
                    'metode_bayar'            => null,
                    'bukti_bayar'             => null,
                    'description'             => 'Pembayaran langsung saat update invoice ' . $item->nomor_invoice,
                    'input_by'                => Auth::id() ?? 1,
                    'input_date'              => now('Asia/Jakarta'),
                ]);

                $item->fresh()->syncInvoiceStatus();
            }

            if (!$item->payment()->exists()) {
                $delivery = $item->fresh()->delivery;
                $delivery->syncInvoicedStatus();
                $invoiceNumber = 'INV-' . now('Asia/Jakarta')->format('Ymd') . '-' . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

                Payment::create([
                    'invoice_id'               => $item->fresh()->invoice_id,
                    'payment_date'             => now('Asia/Jakarta'),
                    'payment_date_estimation'  => $item->fresh()->due_date,
                    'payment_status'           => $item->fresh()->pay_now == 1 ? 1 : 0,
                    'amount'                   => $delivery->po->harga * $delivery->qty_delivered ?? 0,
                    'metode_bayar'             => null,
                    'bukti_bayar'              => null,
                    'description'              => 'Pembayaran langsung saat pembuatan invoice ' . $invoiceNumber,
                    'input_by'                 => Auth::id() ?? 1,
                    'input_date'               => now('Asia/Jakarta'),
                ]);

                $item->fresh()->syncInvoiceStatus();
            }
        }

        $newInvoice = $item->fresh();
        $this->logUpdate($newInvoice, $oldInvoice, 'Invoice dengan nomor invoice ' . $item->nomor_invoice . ' yang terhubung ke delivery ' . $item->delivery->delivery_no . ' dengan PO ' . $item->delivery->po->no_po . ' (' . $item->delivery->po->nama_barang . ') Di update');

        return response()->json([
            'success'      => true,
            'message'      => 'Invoice berhasil diperbarui.',
            'redirect_url' => route('invoice.index'),
        ]);
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $delivery = $invoice->delivery; // ← save before deletion

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $invoice->payment()->delete(); // ← payments() hasMany, safe even if none exist
        $invoice->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ← only sync if delivery still exists
        if ($delivery) {
            $delivery->syncInvoicedStatus();
        }

        return response()->json([
            'success' => true,
            'message' => 'Invoice berhasil dihapus.',
        ]);
    }
    public function show($invoice_id)
    {
        $invoice = Invoice::with([
            'delivery.po.customer',
            'payment' // Singular
        ])->findOrFail($invoice_id);

        return view('invoice-show', compact('invoice'));
    }
    public function payInvoice($id)
    {
        $invoice = Invoice::with('delivery.po')->findOrFail($id);

        $delivery = $invoice->delivery;
        $po = $delivery->po;

        $amount = $po->harga * $delivery->qty_delivered;
        $now = now()->toDateString();

        // Create new Payment
        Payment::create([
            'invoice_id'              => $invoice->invoice_id,
            'payment_date'            => $now,
            'amount'                  => $amount,
            'metode_bayar'            => 'Tidak Ada',
            'bukti_bayar'             => 'Tidak Ada',
            'description'             => Auth::user()->user_name . ' membayar Invoice dari dashboard',
            'payment_date_estimation' => $now,
            'payment_status'          => 1,
        ]);

        // Set invoice status to paid
        $invoice->status_invoice = 1;
        $invoice->save();

        return response()->json([
            'success' => true,
            'message' => 'Invoice berhasil ditandai sebagai lunas.',
        ]);
    }
}
