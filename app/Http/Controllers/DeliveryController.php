<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Po;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // 1. Join with tbl_po to access PO fields for sorting/searching
            $data = Delivery::query()
                ->leftJoin('tbl_po', 'tbl_delivery.po_id', '=', 'tbl_po.po_id')
                ->select(
                    'tbl_delivery.*',
                    'tbl_po.no_po',
                    'tbl_po.tgl_po',
                    'tbl_po.nama_barang',
                    'tbl_po.qty',
                    'tbl_po.status'
                );

            return DataTables::of($data)
                ->addIndexColumn()
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
                    $qty    = number_format((float) $row->qty_delivered, 0, ',', '.');
                    $qtyPo  = number_format((float) ($row->qty ?? 0), 0, ',', '.');
                    $pct    = ($row->qty ?? 0) > 0
                        ? round(($row->qty_delivered / $row->qty) * 100)
                        : 0;

                    [$bg, $color, $barColor] = match (true) {
                        $pct <= 0   => ['#fef2f2', '#dc2626', '#fca5a5'],
                        $pct < 50   => ['#fff7ed', '#ea580c', '#fdba74'],
                        $pct < 100  => ['#eff6ff', '#2563eb', '#93c5fd'],
                        default     => ['#f0fdf4', '#16a34a', '#86efac'],
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

                    // ── Delivery Time Estimation ───────────────────────────────────────────────
                })->addColumn('delivery_time_estimation', function ($row) {
                    if (!$row->delivery_time_estimation) {
                        return <<<HTML
            <span style="font-size:0.75rem;color:#94a3b8;font-weight:500;">
                <i class="ri-calendar-close-line me-1"></i>Tidak ada estimasi
            </span>
            HTML;
                    }

                    $now      = \Carbon\Carbon::now();
                    $est      = \Carbon\Carbon::parse($row->delivery_time_estimation);
                    $isPast   = $est->isPast();

                    // Build duration parts
                    $diff     = $now->diff($est);
                    $years    = $diff->y;
                    $months   = $diff->m;
                    $days     = $diff->d;
                    $hours    = $diff->h;

                    $parts = [];
                    if ($years)  $parts[] = "{$years} tahun";
                    if ($months) $parts[] = "{$months} bulan";
                    if ($days)   $parts[] = "{$days} hari";
                    if ($hours)  $parts[] = "{$hours} jam";
                    if (empty($parts)) $parts[] = "Hari ini";

                    $duration = implode(' ', $parts);
                    $dateStr  = $est->translatedFormat('d M Y');

                    if ($isPast) {
                        $label    = "{$duration} yang lalu";
                        $icon     = 'ri-alarm-warning-line';
                        $color    = '#dc2626';
                        $bg       = '#fef2f2';
                        $subColor = '#f87171';
                    } else {
                        $label    = "{$duration} lagi";
                        $icon     = 'ri-timer-line';
                        $color    = '#0284c7';
                        $bg       = '#e0f2fe';
                        $subColor = '#94a3b8';
                    }

                    // urgency override — within 7 days and not past
                    if (!$isPast && $est->diffInDays($now) <= 7) {
                        $icon     = 'ri-alarm-line';
                        $color    = '#d97706';
                        $bg       = '#fefce8';
                        $subColor = '#fbbf24';
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
            <span style="font-size:0.68rem;color:{$subColor};padding-left:2px;">
                <i class="ri-calendar-line me-1"></i>{$dateStr}
            </span>
        </div>
        HTML;

                    // ── Delivered At ───────────────────────────────────────────────────────────
                })->addColumn('delivered_at', function ($row) {
                    if (!$row->delivered_at) {
                        return <<<HTML
            <span style="font-size:0.75rem;color:#94a3b8;font-weight:500;white-space:nowrap;">
                <i class="ri-minus-line me-1"></i>Belum dikirim
            </span>
            HTML;
                    }

                    $date     = \Carbon\Carbon::parse($row->delivered_at);
                    $dateStr  = $date->translatedFormat('d M Y');
                    $relative = $date->diffForHumans();

                    return <<<HTML
        <div style="display:flex;flex-direction:column;gap:2px;">
            <span style="font-size:0.82rem;font-weight:700;color:#1e293b;">
                <i class="ri-calendar-check-line me-1" style="color:#16a34a;"></i>
                {$dateStr}
            </span>
            <span style="font-size:0.7rem;color:#94a3b8;font-weight:500;">
                {$relative}
            </span>
        </div>
        HTML;

                    // ── Delivered Status ───────────────────────────────────────────────────────
                })->addColumn('delivered_status', function ($row) {
                    $status     = (int) $row->delivered_status;
                    $deliveryId = $row->delivery_id;
                    $csrfToken  = csrf_token();
                    $deliverUrl = route('delivery.autoDeliver', $deliveryId);

                    if ($status === 1) {
                        return <<<HTML
                            <div style="display:flex;flex-direction:column;gap:4px;align-items:flex-start;">
                                <span style="display:inline-flex;align-items:center;gap:5px;
                                            background:#f0fdf4;color:#16a34a;
                                            border:1px solid #16a34a30;border-radius:999px;
                                            padding:4px 12px;font-size:0.75rem;font-weight:700;
                                            letter-spacing:0.03em;white-space:nowrap;">
                                    <i class="ri-checkbox-circle-line" style="font-size:0.9rem;"></i>
                                    Delivered
                                </span>
                            </div>
                            HTML;
                    }

                    return <<<HTML
                        <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-start;">

                            <!-- Status chip -->
                            <span style="display:inline-flex;align-items:center;gap:5px;
                                        background:#f1f5f9;color:#64748b;
                                        border:1px solid #64748b30;border-radius:999px;
                                        padding:4px 12px;font-size:0.75rem;font-weight:700;
                                        letter-spacing:0.03em;white-space:nowrap;">
                                <i class="ri-time-line" style="font-size:0.85rem;"></i>
                                Pending
                            </span>

                            <!-- Deliver Now button -->
                            <button class="btn-deliver-now"
                                data-id="{$deliveryId}"
                                data-url="{$deliverUrl}"
                                data-token="{$csrfToken}"
                                style="display:inline-flex;align-items:center;gap:5px;
                                    background:linear-gradient(135deg,#0284c7,#0369a1);
                                    color:#fff;border:none;border-radius:8px;
                                    padding:5px 12px;font-size:0.76rem;font-weight:700;
                                    cursor:pointer;white-space:nowrap;
                                    box-shadow:0 2px 8px #0284c740;
                                    transition:opacity 0.2s ease;"
                                onmouseover="this.style.opacity='0.85'"
                                onmouseout="this.style.opacity='1'">
                                <i class="ri-send-plane-fill" style="font-size:0.85rem;"></i>
                                Deliver Sekarang
                            </button>
                        </div>
                        HTML;
                })
                ->addColumn('action', function ($row) {
                    $showUrl   = Route::has('delivery.show')    ? route('delivery.show',    $row->delivery_id) : '#';
                    $editUrl   = Route::has('delivery.edit')    ? route('delivery.edit',    $row->delivery_id) : '#';
                    $deleteUrl = Route::has('delivery.destroy') ? route('delivery.destroy', $row->delivery_id) : '#';

                    $user         = Auth::user();
                    $canEditDelete = $user && $user->role_id !== 2;

                    $editItem = $canEditDelete ? <<<HTML
                        <a href="{$editUrl}" class="dropdown-item text-warning">
                            <i class="ri-pencil-line me-2"></i>Edit
                        </a>
                    HTML : '';

                    $deleteItem = $canEditDelete ? <<<HTML
                        <button type="button"
                            class="dropdown-item text-danger btn-delete-ajax"
                            data-url="{$deleteUrl}"
                            data-po="No po {$row->no_po} Nama Barang {$row->nama_barang}">
                            <i class="ri-delete-bin-line me-2"></i>Delete
                        </button>
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
                // ── no_po ──────────────────────────────────────────────────────────────────
                ->orderColumn('no_po', function ($query, $order) {
                    $query->orderBy('tbl_po.no_po', $order);
                })->filterColumn('no_po', function ($query, $keyword) {
                    $query->where('tbl_po.no_po', 'LIKE', "%{$keyword}%");

                    // ── nama_barang ────────────────────────────────────────────────────────────
                })->orderColumn('nama_barang', function ($query, $order) {
                    $query->orderBy('tbl_po.nama_barang', $order);
                })->filterColumn('nama_barang', function ($query, $keyword) {
                    $query->where('tbl_po.nama_barang', 'LIKE', "%{$keyword}%");

                    // ── delivery_no ────────────────────────────────────────────────────────────
                })->orderColumn('delivery_no', function ($query, $order) {
                    $query->orderBy('tbl_delivery.delivery_no', $order);
                })->filterColumn('delivery_no', function ($query, $keyword) {
                    $query->where('tbl_delivery.delivery_no', 'LIKE', "%{$keyword}%");

                    // ── qty_delivered ──────────────────────────────────────────────────────────
                })->orderColumn('qty_delivered', function ($query, $order) {
                    $query->orderBy('tbl_delivery.qty_delivered', $order);
                })->filterColumn('qty_delivered', function ($query, $keyword) {
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
                    // ── delivery_time_estimation ───────────────────────────────────────────────
                })->orderColumn('delivery_time_estimation', function ($query, $order) {
                    $query->orderBy('tbl_delivery.delivery_time_estimation', $order);
                })->filterColumn('delivery_time_estimation', function ($query, $keyword) {
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
                        $query->whereBetween('tbl_delivery.delivery_time_estimation', [
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
                        $query->whereBetween('tbl_delivery.delivery_time_estimation', [
                            $date->copy()->subDay()->toDateString(),
                            $date->copy()->addDay()->toDateString(),
                        ]);
                        return;
                    }

                    // "23 Jul 2025" or any parseable date
                    try {
                        $date = \Carbon\Carbon::createFromFormat('d M Y', trim($keyword));
                        $query->whereDate('tbl_delivery.delivery_time_estimation', $date->toDateString());
                    } catch (\Exception $e) {
                        try {
                            $date = \Carbon\Carbon::parse($keyword);
                            $query->whereDate('tbl_delivery.delivery_time_estimation', $date->toDateString());
                        } catch (\Exception $e2) {
                            $query->whereRaw(
                                "DATE_FORMAT(tbl_delivery.delivery_time_estimation, '%d %b %Y') LIKE ?",
                                ["%{$keyword}%"]
                            );
                        }
                    }

                    // ── delivered_at ───────────────────────────────────────────────────────────
                })->orderColumn('delivered_at', function ($query, $order) {
                    $query->orderByRaw("ISNULL(tbl_delivery.delivered_at) ASC, tbl_delivery.delivered_at {$order}");
                })->filterColumn('delivered_at', function ($query, $keyword) {
                    $lower = strtolower(trim($keyword));

                    // "belum dikirim" → null delivered_at
                    if (str_contains($lower, 'belum')) {
                        $query->whereNull('tbl_delivery.delivered_at');
                        return;
                    }

                    // relative formats: "1 month ago", "2 days ago", "3 hours ago"
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
                            $query->whereBetween('tbl_delivery.delivered_at', [
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
                        $query->whereDate('tbl_delivery.delivered_at', $date->toDateString());
                    } catch (\Exception $e) {
                        try {
                            $date = \Carbon\Carbon::parse($keyword);
                            $query->whereDate('tbl_delivery.delivered_at', $date->toDateString());
                        } catch (\Exception $e2) {
                            $query->whereRaw(
                                "DATE_FORMAT(tbl_delivery.delivered_at, '%d %b %Y') LIKE ?",
                                ["%{$keyword}%"]
                            );
                        }
                    }

                    // ── delivered_status ───────────────────────────────────────────────────────
                })->orderColumn('delivered_status', function ($query, $order) {
                    $query->orderBy('tbl_delivery.delivered_status', $order);
                })->filterColumn('delivered_status', function ($query, $keyword) {
                    // select sends "0" or "1" directly — exact match only
                    if ($keyword !== '' && is_numeric($keyword)) {
                        $query->where('tbl_delivery.delivered_status', (int) $keyword);
                    }
                })
                ->rawColumns(['no_po', 'nama_barang', 'action', 'delivery_no', 'qty_delivered', 'delivery_time_estimation', 'delivered_at', 'delivered_status',])
                ->make(true);
        }

        // 5. Stats for the view (unchanged)
        $stats = [
            'total'     => Delivery::count(),
            'transit'   => Delivery::where('delivered_status', 0)->count(),
            'delivered' => Delivery::where('delivered_status', 1)->count(),
            'inventory' => Delivery::sum('qty_delivered'),
        ];

        return view('delivery-index', compact('stats'));
    }

    public function autoDeliver($id)
    {
        $delivery = Delivery::find($id);
        if ($delivery) {
            $delivery->delivered_status = 1;
            $delivery->delivered_at = $delivery->delivery_time_estimation;
            $delivery->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }
    public function create()
    {
        // Get POs where status is NOT 0 and remaining quantity > 0
        $pos = Po::select('tbl_po.*')
            ->selectSub(function ($query) {
                $query->from('tbl_delivery')
                    ->whereColumn('po_id', 'tbl_po.po_id')
                    ->selectRaw('COALESCE(SUM(qty_delivered), 0)');
            }, 'total_delivered')
            ->where('tbl_po.status', '!=', 0) // Excludes "0: Open"
            ->havingRaw('tbl_po.qty - total_delivered > 0')
            ->get();

        // Add a display string for the dropdown
        foreach ($pos as $po) {
            $po->remaining = $po->qty - $po->total_delivered;
            $po->display_text = $po->no_po . ' (Available: ' . $po->remaining . '/' . $po->qty . ')';
        }

        return view('delivery-create', compact('pos'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'po_id'                    => 'required|exists:tbl_po,po_id',
            'delivery_time_estimation' => 'required|date|after_or_equal:today',
            'qty_delivered'            => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $po = Po::findOrFail($request->po_id);

            $totalDeliveredSoFar = $po->deliveries()->sum('qty_delivered');
            $newTotal = $totalDeliveredSoFar + $request->qty_delivered;
            if ($newTotal > $po->qty) {
                return response()->json([
                    'errors' => ['qty_delivered' => ['Total delivered would exceed PO quantity.']]
                ], 422);
            }

            $deliveryNo = 'DLV-' . date('Ymd') . '-' . strtoupper(uniqid());

            $deliveredStatus   = (int) $request->input('deliver_now', 0) === 1;

            $delivery = Delivery::create([
                'delivery_no'              => $deliveryNo,
                'po_id'                    => $request->po_id,
                'qty_delivered'            => $request->qty_delivered,
                'delivery_time_estimation' => $request->delivery_time_estimation,
                'invoiced_status'          => 0,
                'delivered_status'         => $deliveredStatus,
                'delivered_at'             => now(),
                'input_by'                 => Auth::id() ?? 1,
                'input_date'               => now(),
            ]);

            $this->logCreate($delivery, $delivery->qty_delivered . ' Delivery Dibuat dengan Nomor Delivery ' . $deliveryNo . ' Yang terhubung ke PO ' . $delivery->po->no_po . ' (' . $delivery->po->nama_barang . ')');

            $po->syncStatus();

            return response()->json([
                'success'      => true,
                'message'      => 'Delivery recorded. PO status updated.',
                'redirect_url' => route('delivery.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing delivery: ' . $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id)
    {
        $delivery = Delivery::findOrFail($id);
        $po = $delivery->po;

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // ← safely handle invoice and payment, both may not exist
        if ($delivery->invoice) {
            if ($delivery->invoice->payment) {
                $delivery->invoice->payment->delete();
            }
            $delivery->invoice->delete();
        }

        $delivery->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $po->syncStatus();

        return response()->json([
            'success' => true,
            'message' => 'Delivery berhasil dihapus.',
        ]);
    }
    public function edit($delivery_id)
    {
        $delivery = Delivery::findOrFail($delivery_id);

        // Fetch ONLY the current PO (no status filtering – we want it even if status is excluded)
        $purchaseOrders = Po::select('tbl_po.*')
            ->selectSub(function ($query) {
                $query->from('tbl_delivery')
                    ->whereColumn('po_id', 'tbl_po.po_id')
                    ->selectRaw('COALESCE(SUM(qty_delivered), 0)');
            }, 'total_delivered')
            ->where('po_id', $delivery->po_id)  // ← only the current PO
            ->get();

        foreach ($purchaseOrders as $po) {
            // Overall remaining quantity (after ALL deliveries)
            $po->overall_remaining = $po->qty - $po->total_delivered;

            // Quantity that can still be allocated to THIS delivery
            // (add back the current delivery's qty because we're editing it)
            $po->available_for_edit = $po->overall_remaining;

            // Display text uses overall_remaining
            $po->display_text = "{$po->no_po} (Available: " . number_format($po->overall_remaining) . " / " . number_format($po->qty) . ")";
        }

        // No need for $currentPo – $purchaseOrders already contains it
        return view('delivery-edit', compact('delivery', 'purchaseOrders'));
    }

    public function update(Request $request, $delivery_id)
    {
        // 1. Fetch the existing delivery
        $delivery = Delivery::findOrFail($delivery_id);

        // 2. Validation
        $validator = Validator::make($request->all(), [
            'po_id'                    => 'required|exists:tbl_po,po_id',
            'delivery_time_estimation' => 'required|date',
            'qty_delivered'            => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $po = Po::findOrFail($request->po_id);

            // 3. LOGIC FIX: Calculate total delivered by OTHERS (exclude this delivery's current qty)
            $totalDeliveredByOthers = $po->deliveries()
                ->where('delivery_id', '!=', $delivery_id) // Do not count the record we are currently editing
                ->sum('qty_delivered');

            $newTotal = $totalDeliveredByOthers + $request->qty_delivered;

            if ($newTotal > $po->qty) {
                $maxAllowed = $po->qty - $totalDeliveredByOthers;
                return response()->json([
                    'errors' => ['qty_delivered' => ["Total exceeds PO quantity. Max additional allowed: $maxAllowed"]]
                ], 422);
            }

            $oldDelivery = $delivery->toArray();

            $deliveredStatus   = (int) $request->input('deliver_now', 0) === 1;
            $deliveredAt     = $deliveredStatus ? now() : null;

            // 4. Update the existing delivery record
            $delivery->update([
                'po_id'                    => $request->po_id,
                'qty_delivered'            => $request->qty_delivered,
                'delivery_time_estimation' => $request->delivery_time_estimation,
                'delivered_at'             => $deliveredAt,
                'delivered_status'         => $deliveredStatus,
                'edit_by'                  => Auth::id() ?? 1,
                'edit_date'                => now(),
            ]);

            $newDelivery = $delivery->fresh();
            $this->logUpdate($newDelivery, $oldDelivery, 'Delivery dengan nomor ' . $delivery->delivery_no . ' Yang terhubung ke PO ' . $delivery->po->no_po . ' (' . $delivery->po->nama_barang . ') ' . ' Di update');

            // 5. Sync PO status
            if (method_exists($po, 'syncStatus')) {
                $po->syncStatus();
            }

            return response()->json([
                'success' => true,
                'message' => 'Pengiriman berhasil diperbarui.',
                'redirect_url' => route('delivery.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($delivery_id)
    {
        // Fetch the PO with its customer relationship
        $delivery = Delivery::findOrFail($delivery_id);

        return view('delivery-show', compact('delivery'));
    }
}
