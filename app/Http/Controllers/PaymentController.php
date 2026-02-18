<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables; // Ensure you have yajra/laravel-datatables installed
use Illuminate\Support\Facades\Validator;

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
                    $time = \Carbon\Carbon::parse($row->input_date)->format('H:i');

                    $methodClass = match (strtolower($row->metode_bayar)) {
                        'transfer' => 'bg-label-info',
                        'cash'     => 'bg-label-success',
                        'credit'   => 'bg-label-warning',
                        default    => 'bg-label-secondary'
                    };

                    return '<div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex flex-column">
                                <span class="fw-medium text-dark">' . e($date) . '</span>
                                <small class="text-muted"><i class="ri-time-line me-1"></i>' . e($time) . '</small>
                            </div>
                            <span class="badge ' . $methodClass . ' method-pill ms-3">' . strtoupper(e($row->metode_bayar)) . '</span>
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

                ->rawColumns(['referensi', 'pelanggan', 'amount', 'tanggal_metode'])
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
}
