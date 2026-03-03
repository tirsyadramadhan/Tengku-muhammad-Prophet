<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables; // Ensure you have yajra/laravel-datatables installed
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Route;
use App\Models\Customer;  // ADD THIS import at top of controller

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Join through invoice and delivery to reach po and customer
            $data = Payment::query()
                ->leftJoin('tbl_invoice', 'tbl_payment.invoice_id', '=', 'tbl_invoice.invoice_id')
                ->leftJoin('tbl_delivery', 'tbl_invoice.delivery_id', '=', 'tbl_delivery.delivery_id')
                ->leftJoin('tbl_po', 'tbl_delivery.po_id', '=', 'tbl_po.po_id')
                ->leftJoin('tbl_customer', 'tbl_po.customer_id', '=', 'tbl_customer.id_cust')
                ->select([
                    'tbl_payment.*',
                    'tbl_invoice.nomor_invoice',
                    'tbl_po.no_po',
                    'tbl_po.customer_id',
                    'tbl_customer.cust_name'
                ]);

            return DataTables::of($data)
                ->addIndexColumn()

                // Column: Detail Referensi
                ->addColumn('referensi', function ($row) {
                    $invoiceNo = $row->nomor_invoice ?? 'NO-INV';
                    $poNo = $row->no_po ?? 'N/A';

                    return '<div class="d-flex flex-column">
                            <span class="fw-bold text-dark mb-0">' . e($invoiceNo) . '</span>
                            <small class="text-primary fw-medium">
                                <i class="ri-hashtag me-1"></i>PO: ' . e($poNo) . '
                            </small>
                        </div>';
                })

                // Column: Pelanggan
                ->addColumn('pelanggan', function ($row) {
                    $custName = $row->cust_name ?? 'Unknown';
                    $custId = $row->customer_id ?? '-';
                    $initial = strtoupper(substr($custName, 0, 1));

                    return '<div class="d-flex align-items-center">
                            <div class="avatar avatar-sm bg-label-secondary me-3">
                                <span class="avatar-initial rounded-circle">' . e($initial) . '</span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-semibold text-heading">' . e($custName) . '</span>
                                <small class="text-muted">ID: #' . e($custId) . '</small>
                            </div>
                        </div>';
                })

                // Column: Jumlah Bayar
                ->editColumn('amount', function ($row) {
                    return '<span class="text-success fs-5 fw-bold">Rp ' . number_format($row->amount, 0, ',', '.') . '</span>';
                })

                // Column: Tanggal & Metode
                ->addColumn('tanggal_metode', function ($row) {
                    $date = \Carbon\Carbon::parse($row->payment_date)->format('d M Y');

                    $methodClass = match (strtolower($row->metode_bayar)) {
                        'transfer' => 'bg-label-info',
                        'cash'     => 'bg-label-success',
                        'credit'   => 'bg-label-warning',
                        default    => 'bg-label-secondary'
                    };

                    return '<div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex flex-column">
                                <span class="fw-medium text-dark">' . e($date) . '</span>
                            </div>
                            <span class="badge ' . $methodClass . ' method-pill ms-3">' . strtoupper(e($row->metode_bayar)) . '</span>
                        </div>';
                })
                ->addColumn('action', function ($row) {
                    // Helper to prevent crash if route is missing (optional safety)
                    $showUrl = Route::has('payment.show') ? route('payment.show', $row->payment_id) : '#';
                    $editUrl = Route::has('payment.edit') ? route('payment.edit', $row->payment_id) : '#';
                    $deleteUrl = Route::has('payment.destroy') ? route('payment.destroy', $row->payment_id) : '#';

                    return '
                <div class="d-flex align-items-center gap-2">
                    <a href="' . $showUrl . '" class="btn btn-sm btn-icon btn-label-info" title="Details">
                        <i class="ri-eye-line"></i>
                    </a>
                    <a href="' . $editUrl . '" class="btn btn-sm btn-icon btn-label-warning" title="Edit">
                        <i class="ri-pencil-line"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-icon btn-label-danger btn-delete-ajax" 
            data-url="' . $deleteUrl . '" 
            title="Delete">
            <i class="ri-delete-bin-line"></i>
        </button>
                </div>';
                })
                // Search Filters (unchanged)
                ->filterColumn('referensi', function ($query, $keyword) {
                    $query->whereHas('invoice', function ($q) use ($keyword) {
                        $q->where('nomor_invoice', 'like', "%{$keyword}%");
                    })->orWhereHas('invoice.delivery.po', function ($q) use ($keyword) {
                        $q->where('no_po', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('pelanggan', function ($query, $keyword) {
                    $query->whereHas('invoice.delivery.po.customer', function ($q) use ($keyword) {
                        $q->where('cust_name', 'like', "%{$keyword}%");
                    });
                })

                // Order Columns (now using joined columns)
                ->orderColumn('referensi', function ($query, $order) {
                    $query->orderBy('nomor_invoice', $order);
                })
                ->orderColumn('pelanggan', function ($query, $order) {
                    $query->orderBy('cust_name', $order);
                })
                ->orderColumn('amount', function ($query, $order) {
                    $query->orderBy('amount', $order);
                })
                ->orderColumn('tanggal_metode', function ($query, $order) {
                    $query->orderBy('payment_date', $order);
                })

                ->rawColumns(['referensi', 'pelanggan', 'amount', 'tanggal_metode', 'action'])
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
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $invoice = \App\Models\Invoice::with('po')->findOrFail($request->invoice_id);

            DB::transaction(function () use ($request, $invoice) {
                Payment::create([
                    'invoice_id'     => $invoice->invoice_id,
                    'po_id'          => $invoice->po_id,
                    'payment_date'   => $request->payment_date,
                    'amount'         => $request->amount,
                    'metode_bayar'   => $request->metode_bayar,
                    'status_invoice' => 1,
                    'input_by'       => auth()->id() ?? 1,
                    'input_date'     => now(),
                ]);

                $invoice->update(['status_invoice' => 1]);
            });

            // Trigger PO status update
            if ($invoice->po) {
                $invoice->po->syncStatus();
            }

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil direkam.',
                'redirect_url' => route('payment.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($payment_id)
    {
        $payment = Payment::with([
            'invoice',
            'invoice.delivery',
            'invoice.delivery.po',
            'invoice.delivery.po.customer',
            'invoice.delivery.po.input_user',
            'invoice.payment',        // all payments for payment history table
            'input_user',              // recorded by (input_by foreign key)
        ])->findOrFail($payment_id);

        // Extract invoice so the blade can use $invoice as its root,
        // while $payment remains available if needed separately.
        $invoice = $payment->invoice;

        return view('payment-show', compact('payment', 'invoice'));
    }
    /**
     * Delete a payment record and reverse its side effects.
     * Called via AJAX DELETE from the index page.
     */
    public function destroy(Payment $payment)
    {
        try {
            // 1. Grab references BEFORE deleting (they'll be gone after)
            $invoice  = $payment->invoice;
            $delivery = $invoice?->delivery;
            $po       = $delivery?->po;

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
        $validated = $request->validate([
            'invoice_id'   => 'required|exists:tbl_invoice,invoice_id',
            'amount'       => 'required|numeric|min:1',
            'metode_bayar' => 'required|string|max:100',
            'payment_date' => 'required|date',
        ]);

        try {
            // 1. Grab the OLD invoice before we change anything
            $oldInvoice  = $payment->invoice;
            $oldDelivery = $oldInvoice?->delivery;
            $oldPo       = $oldDelivery?->po;

            // 2. Save updated payment
            $payment->update($validated);

            // 3. If the invoice_id CHANGED, reverse the old invoice back to Unpaid
            if ($oldInvoice && $oldInvoice->invoice_id != $validated['invoice_id']) {
                $oldInvoice->update(['status_invoice' => 0]);
                $oldPo?->syncStatus();
            }

            // 4. Mark the NEW (or same) invoice as Paid and re-sync its PO
            $newInvoice = $payment->fresh()->invoice;
            if ($newInvoice) {
                $newInvoice->update(['status_invoice' => 1]);
                $newInvoice->delivery?->po?->syncStatus();
            }

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
}
