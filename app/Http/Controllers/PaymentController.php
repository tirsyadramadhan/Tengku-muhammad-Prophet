<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables; // Ensure you have yajra/laravel-datatables installed
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Join through invoice and delivery to reach po and customer
            $query = Payment::query()
                ->leftJoin('tbl_invoice', 'tbl_payment.invoice_id', '=', 'tbl_invoice.invoice_id')
                ->leftJoin('tbl_delivery', 'tbl_invoice.delivery_id', '=', 'tbl_delivery.delivery_id')
                ->leftJoin('tbl_po', 'tbl_delivery.po_id', '=', 'tbl_po.po_id')
                ->select([
                    'tbl_payment.*',
                    'tbl_payment.invoice_id as invoice_id',
                    'tbl_invoice.nomor_invoice',
                    'tbl_invoice.tgl_invoice',
                    'tbl_invoice.due_date',
                    'tbl_invoice.status_invoice',
                    'tbl_delivery.delivery_no',
                    'tbl_delivery.qty_delivered',
                    'tbl_delivery.delivered_at',
                    'tbl_po.no_po',
                    'tbl_po.nama_barang',
                    'tbl_po.harga',
                    'tbl_po.qty',
                ]);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $showUrl   = Route::has('invoice.show')    ? route('invoice.show',    $row->invoice->invoice_id) : '#';
                    $editUrl   = Route::has('payment.edit')    ? route('payment.edit',    $row->payment_id) : '#';
                    $deleteUrl = Route::has('payment.destroy') ? route('payment.destroy', $row->payment_id) : '#';

                    $user          = Auth::user();
                    $canEditDelete = $user && $user->role_id !== 2;

                    $noPo       = $row->no_po;
                    $namaBarang = $row->nama_barang;

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
                                data-po="No po {$noPo} Nama Barang {$namaBarang}">
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
                ->addColumn('no_po', fn($row) => $row->invoice->delivery->po->no_po ?? '-')
                ->orderColumn('no_po', 'tbl_po.no_po $1')
                ->filterColumn('no_po', function ($query, $keyword) {
                    $query->where('tbl_po.no_po', 'like', "%{$keyword}%");
                })
                ->addColumn('nama_barang', fn($row) => $row->invoice->delivery->po->nama_barang ?? '-')
                ->orderColumn('nama_barang', 'tbl_po.nama_barang $1')
                ->filterColumn('nama_barang', function ($query, $keyword) {
                    $query->where('tbl_po.nama_barang', 'like', "%{$keyword}%");
                })
                ->addColumn('nomor_invoice', fn($row) => $row->invoice->nomor_invoice ?? '-')
                ->orderColumn('nomor_invoice', 'tbl_invoice.nomor_invoice $1')
                ->filterColumn('nomor_invoice', function ($query, $keyword) {
                    $query->where('tbl_invoice.nomor_invoice', 'like', "%{$keyword}%");
                })
                ->addColumn('status_invoice', function ($row) {
                    $map = [
                        0 => ['label' => 'Unpaid', 'icon' => 'ri-time-line',            'color' => '#64748b', 'bg' => '#f1f5f9'],
                        1 => ['label' => 'Paid',   'icon' => 'ri-checkbox-circle-line', 'color' => '#16a34a', 'bg' => '#f0fdf4'],
                    ];

                    $invoice   = $row->invoice;
                    $s         = $map[(int) $invoice->status_invoice] ?? $map[0];
                    $label     = $s['label'];
                    $icon      = $s['icon'];
                    $color     = $s['color'];
                    $bg        = $s['bg'];
                    $invoiceId = $invoice->invoice_id;
                    $nomorInv  = $invoice->nomor_invoice ?? '-';
                    $csrfToken = csrf_token();
                    $payUrl    = route('payments.payNow', $row->payment_id);

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

                    if ((int) $invoice->status_invoice === 1) {
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
                ->orderColumn('status_invoice', 'tbl_invoice.status_invoice $1')
                ->filterColumn('status_invoice', function ($query, $keyword) {
                    $map = [
                        'unpaid' => 0,
                        'paid'   => 1,
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
                ->addColumn('payment_date', function ($row) {
                    if (!$row->payment_date) {
                        return <<<HTML
        <span style="font-size:0.75rem;color:#94a3b8;font-weight:500;">
            <i class="ri-minus-line me-1"></i>Tidak ada
        </span>
        HTML;
                    }

                    $date    = \Carbon\Carbon::parse($row->payment_date);
                    $dateStr = $date->translatedFormat('d M Y');

                    return <<<HTML
    <span style="display:inline-flex;align-items:center;gap:5px;
                font-size:0.78rem;font-weight:600;color:#1e293b;
                white-space:nowrap;">
        <i class="ri-calendar-check-line" style="color:#0284c7;font-size:0.85rem;"></i>
        {$dateStr}
    </span>
    HTML;
                })
                ->orderColumn('payment_date', 'tbl_payment.payment_date $1')
                ->filterColumn('payment_date', function ($query, $keyword) {
                    try {
                        $date = \Carbon\Carbon::createFromFormat('d M Y', trim($keyword));
                        $query->whereDate('tbl_payment.payment_date', $date->toDateString());
                    } catch (\Exception $e) {
                        try {
                            $date = \Carbon\Carbon::parse($keyword);
                            $query->whereDate('tbl_payment.payment_date', $date->toDateString());
                        } catch (\Exception $e2) {
                            $query->whereRaw(
                                "DATE_FORMAT(tbl_payment.payment_date, '%d %b %Y') LIKE ?",
                                ["%{$keyword}%"]
                            );
                        }
                    }
                })
                ->addColumn('amount', function ($row) {
                    if (!$row->amount && $row->amount !== 0) {
                        return <<<HTML
        <span style="font-size:0.75rem;color:#94a3b8;font-weight:500;">
            <i class="ri-minus-line me-1"></i>Tidak ada
        </span>
        HTML;
                    }

                    $formatted = 'Rp ' . number_format($row->amount, 0, ',', '.');

                    return <<<HTML
    <span style="display:inline-flex;align-items:center;gap:5px;
                font-size:0.8rem;font-weight:700;color:#1e293b;
                white-space:nowrap;">
        <i class="ri-money-rupee-circle-line" style="color:#16a34a;font-size:0.9rem;"></i>
        {$formatted}
    </span>
    HTML;
                })
                ->orderColumn('amount', 'tbl_payment.amount $1')
                ->filterColumn('amount', function ($query, $keyword) {
                    $clean = preg_replace('/[Rp\s.]/', '', $keyword);
                    $clean = str_replace(',', '.', $clean);

                    if (is_numeric($clean)) {
                        $query->where('tbl_payment.amount', (float) $clean);
                    } else {
                        $query->where('tbl_payment.amount', 'like', "%{$keyword}%");
                    }
                })
                ->addColumn('metode_bayar', function ($row) {
                    if (!$row->metode_bayar) {
                        return <<<HTML
        <span style="font-size:0.75rem;color:#94a3b8;font-weight:500;">
            <i class="ri-minus-line me-1"></i>Tidak ada
        </span>
        HTML;
                    }

                    $val = e($row->metode_bayar);

                    return <<<HTML
    <span style="display:inline-flex;align-items:center;gap:5px;
                background:#f8fafc;color:#334155;
                border:1px solid #e2e8f0;border-radius:8px;
                padding:3px 10px;font-size:0.78rem;font-weight:600;
                white-space:nowrap;">
        <i class="ri-bank-card-line" style="font-size:0.85rem;color:#0284c7;"></i>
        {$val}
    </span>
    HTML;
                })
                ->orderColumn('metode_bayar', 'tbl_payment.metode_bayar $1')
                ->filterColumn('metode_bayar', function ($query, $keyword) {
                    $valid = [
                        'Tunai',
                        'Transfer Bank',
                        'Kartu Kredit',
                        'Kartu Debit',
                        'QRIS',
                        'OVO',
                        'GoPay',
                        'DANA',
                        'LinkAja',
                        'ShopeePay',
                    ];
                    // Case-insensitive exact match first
                    $match = collect($valid)->first(
                        fn($v) => strtolower($v) === strtolower(trim($keyword))
                    );

                    if ($match) {
                        $query->where('tbl_payment.metode_bayar', $match);
                    } else {
                        $query->where('tbl_payment.metode_bayar', 'like', "%{$keyword}%");
                    }
                })
                ->addColumn('bukti_bayar', function ($row) {
                    if (!$row->bukti_bayar) {
                        return <<<HTML
        <span style="font-size:0.75rem;color:#94a3b8;font-weight:500;">
            <i class="ri-minus-line me-1"></i>Tidak ada
        </span>
        HTML;
                    }

                    $val = e($row->bukti_bayar);

                    return <<<HTML
    <span style="font-size:0.78rem;color:#334155;font-weight:500;
                word-break:break-all;">
        <i class="ri-file-image-line me-1" style="color:#0284c7;"></i>
        {$val}
    </span>
    HTML;
                })
                ->orderColumn('bukti_bayar', 'tbl_payment.bukti_bayar $1')
                ->filterColumn('bukti_bayar', function ($query, $keyword) {
                    $query->where('tbl_payment.bukti_bayar', 'like', "%{$keyword}%");
                })
                ->addColumn('description', function ($row) {
                    if (!$row->description) {
                        return <<<HTML
        <span style="font-size:0.75rem;color:#94a3b8;font-weight:500;">
            <i class="ri-minus-line me-1"></i>Tidak ada
        </span>
        HTML;
                    }

                    $val = e($row->description);

                    return <<<HTML
    <span style="font-size:0.78rem;color:#475569;font-weight:400;
                line-height:1.5;">
        {$val}
    </span>
    HTML;
                })
                ->orderColumn('description', 'tbl_payment.description $1')
                ->filterColumn('description', function ($query, $keyword) {
                    $query->where('tbl_payment.description', 'like', "%{$keyword}%");
                })
                ->addColumn('payment_date_estimation', function ($row) {
                    if (!$row->payment_date_estimation) {
                        return <<<HTML
        <span style="font-size:0.75rem;color:#94a3b8;font-weight:500;">
            <i class="ri-minus-line me-1"></i>Tidak ada
        </span>
        HTML;
                    }

                    $now    = \Carbon\Carbon::now();
                    $due    = \Carbon\Carbon::parse($row->payment_date_estimation);
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
                })
                ->orderColumn('payment_date_estimation', 'tbl_payment.payment_date_estimation $1')
                ->filterColumn('payment_date_estimation', function ($query, $keyword) {
                    $lower = strtolower(trim($keyword));

                    // "X bulan Y hari Z jam lagi" → future relative
                    if (str_contains($lower, 'lagi')) {
                        $years  = 0;
                        $months = 0;
                        $days = 0;
                        $hours = 0;
                        if (preg_match('/(\d+)\s*tahun/', $lower, $m)) $years  = (int) $m[1];
                        if (preg_match('/(\d+)\s*bulan/', $lower, $m)) $months = (int) $m[1];
                        if (preg_match('/(\d+)\s*hari/',  $lower, $m)) $days   = (int) $m[1];
                        if (preg_match('/(\d+)\s*jam/',   $lower, $m)) $hours  = (int) $m[1];
                        $date = \Carbon\Carbon::now()
                            ->addYears($years)->addMonths($months)
                            ->addDays($days)->addHours($hours);
                        $query->whereBetween('tbl_payment.payment_date_estimation', [
                            $date->copy()->subDay()->toDateString(),
                            $date->copy()->addDay()->toDateString(),
                        ]);
                        return;
                    }

                    // "X bulan Y hari Z jam lalu" / "yang lalu" → past relative
                    if (str_contains($lower, 'lalu')) {
                        $years  = 0;
                        $months = 0;
                        $days = 0;
                        $hours = 0;
                        if (preg_match('/(\d+)\s*tahun/', $lower, $m)) $years  = (int) $m[1];
                        if (preg_match('/(\d+)\s*bulan/', $lower, $m)) $months = (int) $m[1];
                        if (preg_match('/(\d+)\s*hari/',  $lower, $m)) $days   = (int) $m[1];
                        if (preg_match('/(\d+)\s*jam/',   $lower, $m)) $hours  = (int) $m[1];
                        $date = \Carbon\Carbon::now()
                            ->subYears($years)->subMonths($months)
                            ->subDays($days)->subHours($hours);
                        $query->whereBetween('tbl_payment.payment_date_estimation', [
                            $date->copy()->subDay()->toDateString(),
                            $date->copy()->addDay()->toDateString(),
                        ]);
                        return;
                    }

                    // "15 May 2026" or any parseable date
                    try {
                        $date = \Carbon\Carbon::createFromFormat('d M Y', trim($keyword));
                        $query->whereDate('tbl_payment.payment_date_estimation', $date->toDateString());
                    } catch (\Exception $e) {
                        try {
                            $date = \Carbon\Carbon::parse($keyword);
                            $query->whereDate('tbl_payment.payment_date_estimation', $date->toDateString());
                        } catch (\Exception $e2) {
                            $query->whereRaw(
                                "DATE_FORMAT(tbl_payment.payment_date_estimation, '%d %b %Y') LIKE ?",
                                ["%{$keyword}%"]
                            );
                        }
                    }
                })
                ->addColumn('payment_status', function ($row) {
                    $map = [
                        0 => ['label' => 'Unpaid', 'icon' => 'ri-time-line',            'color' => '#64748b', 'bg' => '#f1f5f9'],
                        1 => ['label' => 'Paid',   'icon' => 'ri-checkbox-circle-line', 'color' => '#16a34a', 'bg' => '#f0fdf4'],
                    ];

                    $s     = $map[(int) $row->payment_status] ?? $map[0];
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
                ->orderColumn('payment_status', 'tbl_payment.payment_status $1')
                ->filterColumn('payment_status', function ($query, $keyword) {
                    $map = [
                        'unpaid' => 0,
                        'paid'   => 1,
                    ];
                    $kw = strtolower(trim($keyword));

                    if (array_key_exists($kw, $map)) {
                        $query->where('tbl_payment.payment_status', $map[$kw]);
                    } elseif (is_numeric($kw)) {
                        $query->where('tbl_payment.payment_status', (int) $kw);
                    } else {
                        $query->where('tbl_payment.payment_status', 'like', "%{$keyword}%");
                    }
                })
                ->rawColumns([
                    'status_invoice',
                    'payment_date',
                    'amount',
                    'metode_bayar',
                    'bukti_bayar',
                    'description',
                    'payment_date_estimation',
                    'payment_status',
                    'action',
                ])
                ->make(true);
        }

        // Statistics (unchanged)
        $totalVolume = Payment::sum('amount');
        $totalTransactions = Payment::count();
        $lastTransaction = Payment::latest('input_date')->first();

        return view('payment-index', compact('totalVolume', 'totalTransactions', 'lastTransaction'));
    }

    public function create()
    {
        // Fetch invoices that do NOT have a payment yet
        $invoices = \App\Models\Invoice::whereDoesntHave('payment')
            ->with(['po.customer', 'delivery'])
            ->get()
            ->map(function ($invoice) {
                // Calculate invoice amount: delivered quantity * unit price
                $amount = $invoice->delivery->qty_delivered * $invoice->delivery->po->harga;
                $invoice->total_display = $amount;
                $invoice->customer_name = $invoice->po->customer->cust_name ?? 'Unknown';
                return $invoice;
            });

        return view('payment-create', compact('invoices'));
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_id'   => 'required|exists:tbl_invoice,invoice_id|unique:tbl_payment,invoice_id',
            'payment_date' => 'required|date',
            'amount'       => 'required|numeric|min:0',
            'metode_bayar' => 'required|string',
            'pay_now'      => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $invoice  = \App\Models\Invoice::findOrFail($request->invoice_id);
            $payNow   = (int) $request->input('pay_now', 0) === 1;
            $status   = $payNow ? 1 : 0;
            $paidDate = $payNow ? now()->toDateString() : null;

            DB::transaction(function () use ($request, $invoice, $status, $paidDate, $payNow) {
                $payment = Payment::create([
                    'invoice_id'              => $invoice->invoice_id,
                    'amount'                  => $request->amount,
                    'metode_bayar'            => $request->metode_bayar,
                    'payment_date_estimation' => $request->payment_date, // form field → correct column
                    'payment_date'            => $paidDate,              // only set if paying now
                    'payment_status'          => $status,
                    'input_by'                => Auth::id(),
                    'input_date'              => now(),
                ]);

                // only mark invoice as paid if paying right now
                if ($payNow == 1) {
                    $invoice->update(['status_invoice' => 1]);
                }

                $this->logCreate(
                    $payment,
                    'Payment ditambahkan ke invoice ' . $invoice->nomor_invoice .
                        ' yang terhubung ke delivery ' . $invoice->delivery->delivery_no .
                        ' dengan PO ' . $invoice->delivery->po->no_po .
                        ' (' . $invoice->delivery->po->nama_barang . ')'
                );
            });

            if ($invoice->po) {
                $invoice->po->syncStatus();
            }

            return response()->json([
                'success'      => true,
                'message'      => 'Pembayaran berhasil direkam.',
                'redirect_url' => route('payment.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Payment $payment)
    {
        try {
            // 1. Grab references BEFORE deleting (they'll be gone after)
            $invoice  = $payment->invoice;
            $delivery = $invoice?->delivery;
            $po       = $delivery?->po;

            $this->logDelete($payment, $payment, 'Payment dengan invoice ' . $payment->invoice->nomor_invoice . ' yang terhubung ke delivery ' . $payment->invoice->delivery->delivery_no . ' dengan PO ' . $payment->invoice->delivery->po->no_po . ' (' . $payment->invoice->delivery->po->nama_barang . ') ' . ' Di hapus');

            // 2. Delete the payment
            $payment->delete();

            // 3. Reverse invoice status back to Unpaid (0) if no other payments exist
            if ($invoice) {
                $remainingPayments = $invoice->payment()->count(); // hasOne, so 0 or 1
                if ($remainingPayments === 0) {
                    $invoice->update(['status_invoice' => 0]);
                }
            }

            // 4. Re-sync PO status up the chain
            if ($po) {
                $po->syncStatus();
            }

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form to edit an existing payment.
     */

    public function edit(Payment $payment)
    {
        // Pull unpaid invoices + the one already on this payment
        // Uses Eloquent with() — reads column names from YOUR models
        $invoices = Invoice::with(['delivery.po.customer'])
            ->where(function ($q) use ($payment) {
                $q->where('status_invoice', 0)
                    ->orWhere('invoice_id', $payment->invoice_id);
            })
            ->get()
            ->map(function ($inv) {
                return (object) [
                    'invoice_id'    => $inv->invoice_id,
                    'nomor_invoice' => $inv->nomor_invoice,
                    'total_display' => optional($inv->delivery?->po)->total ?? 0,
                    'customer_name' => optional($inv->delivery?->po?->customer)->nama_cust
                        ?? optional($inv->delivery?->po?->customer)->name
                        ?? optional($inv->delivery?->po?->customer)->customer_name
                        ?? '(no customer)',
                    'delivery'      => (object) [
                        'delivery_no' => $inv->delivery?->delivery_no,
                        'po'          => (object) [
                            'no_po'       => $inv->delivery?->po?->no_po,
                            'nama_barang' => $inv->delivery?->po?->nama_barang,
                        ],
                    ],
                ];
            });

        return view('payment-edit', compact('payment', 'invoices'));
    }
    /**
     * Update an existing payment record.
     * Handles PO/Invoice status re-sync on change.
     */
    public function update(Request $request, Payment $payment)
    {
        $validator = Validator::make($request->all(), [
            'invoice_id'   => 'required|exists:tbl_invoice,invoice_id',
            'amount'       => 'required|numeric|min:0',
            'metode_bayar' => 'required|string|max:100',
            'payment_date' => 'required|date',
            'pay_now'      => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $oldInvoice = $payment->invoice;
            $oldPo      = $oldInvoice?->delivery?->po;
            $oldPayment = $payment->toArray();

            $payNow   = (int) $request->input('pay_now', 0) === 1;
            $status   = $payNow ? 1 : 0;
            $paidDate = $payNow ? now()->toDateString() : null;

            DB::transaction(function () use ($request, $payment, $oldInvoice, $oldPo, $oldPayment, $status, $paidDate, $payNow) {
                $payment->update([
                    'invoice_id'              => $request->invoice_id,
                    'amount'                  => $request->amount,
                    'metode_bayar'            => $request->metode_bayar,
                    'payment_date_estimation' => $request->payment_date,
                    'payment_date'            => $paidDate,
                    'payment_status'          => $status,
                    'edit_by'                 => Auth::id(),
                ]);

                $newPayment = $payment->fresh();

                $this->logUpdate(
                    $newPayment,
                    $oldPayment,
                    'Payment dengan invoice ' . $newPayment->invoice->nomor_invoice .
                        ' yang terhubung ke delivery ' . $newPayment->invoice->delivery->delivery_no .
                        ' dengan PO ' . $newPayment->invoice->delivery->po->no_po .
                        ' (' . $newPayment->invoice->delivery->po->nama_barang . ') Di update'
                );

                // if invoice changed, reverse old invoice back to Unpaid
                if ($oldInvoice && $oldInvoice->invoice_id != $request->invoice_id) {
                    $oldInvoice->update(['status_invoice' => 0]);
                    $oldPo?->syncStatus();
                }

                // mark new invoice as paid only if pay_now ticked
                $newInvoice = $newPayment->invoice;
                if ($newInvoice && $payNow) {
                    $newInvoice->update(['status_invoice' => 1]);
                } elseif ($newInvoice && !$payNow) {
                    $newInvoice->update(['status_invoice' => 0]);
                }
            });

            $payment->fresh()->invoice?->delivery?->po?->syncStatus();

            return response()->json([
                'success'      => true,
                'message'      => 'Pembayaran berhasil diperbarui.',
                'redirect_url' => route('payment.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function payNow($id)
    {
        $payment = Payment::findOrFail($id);

        if ((int) $payment->payment_status === 1) {
            return response()->json([
                'message' => 'Pembayaran ini sudah tercatat sebagai lunas.'
            ], 422);
        }

        DB::transaction(function () use ($payment) {
            $payment->update([
                'payment_status' => 1,
                'payment_date'   => now()->toDateString(),
                'edit_by'        => Auth::id(),
            ]);

            // mark the linked invoice as paid too
            if ($payment->invoice) {
                $payment->invoice->update(['status_invoice' => 1]);
            }
        });

        return response()->json([
            'message' => 'Pembayaran berhasil dikonfirmasi.'
        ]);
    }
}
