<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Delivery;
use App\Models\Po;
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
                    'tbl_delivery.qty_delivered',          // Added
                    'tbl_po.no_po',
                    'tbl_po.nama_barang',
                    'tbl_po.harga',                         // Added
                    'tbl_po.total as po_total',
                    DB::raw('tbl_delivery.qty_delivered * tbl_po.harga as invoice_amount'), // Computed
                    'tbl_payment.payment_date',
                    'tbl_payment.amount as paid_amount'
                ]);

            return DataTables::of($data)
                ->addIndexColumn()

                // --- Column Definitions ---
                ->addColumn('invoice_details', function ($row) {
                    $date = $row->tgl_invoice ? \Carbon\Carbon::parse($row->tgl_invoice)->format('d M Y') : '-';
                    return '<div class="d-flex flex-column">
                        <span class="fw-bold text-primary">' . e($row->nomor_invoice) . '</span>
                        <small class="text-muted"><i class="ri-calendar-line me-1"></i>' . $date . '</small>
                    </div>';
                })
                ->addColumn('linked_references', function ($row) {
                    $po = $row->no_po ?? 'N/A';
                    $del = $row->delivery_no ?? 'N/A';
                    return '<div class="d-flex flex-column">
                        <span class="fw-bold text-dark"><i class="ri-file-list-3-line me-1"></i>' . e($po) . '</span>
                        <small class="text-muted"><i class="ri-truck-line me-1"></i>' . e($del) . '</small>
                    </div>';
                })
                ->addColumn('status_section', function ($row) {
                    $isPaid = $row->status_invoice == 1;
                    $badge = $isPaid ? '<span class="badge bg-label-success rounded-pill">PAID</span>' : '<span class="badge bg-label-warning rounded-pill">UNPAID</span>';
                    $amount = number_format($row->invoice_amount ?? 0, 0, ',', ',');
                    return '<div class="d-flex flex-column align-items-end">
                        <span class="fw-bold">Rp ' . $amount . '</span>
                        <div class="mt-1">' . $badge . '</div>
                    </div>';
                })
                ->addColumn('due_date_timer', function ($row) {
                    if ($row->status_invoice == 1) {
                        return '<span class="badge bg-label-success rounded-pill px-3">
                        <i class="ri-check-double-line me-1"></i>PAID
                    </span>';
                    }
                    if (!$row->due_date) return '<span class="text-muted">-</span>';
                    return '<div class="timer-wrapper badge bg-label-info" data-target="' . $row->due_date . '">
                    <i class="ri-time-line me-1"></i>
                    <span class="countdown-display">Calculating...</span>
                </div>';
                })
                ->addColumn('action', function ($row) {
                    // Helper to prevent crash if route is missing (optional safety)
                    $showUrl = Route::has('invoice.show') ? route('invoice.show', $row->invoice_id) : '#';
                    $editUrl = Route::has('invoice.edit') ? route('invoice.edit', $row->invoice_id) : '#';
                    $deleteUrl = Route::has('invoice.destroy') ? route('invoice.destroy', $row->invoice_id) : '#';

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
            data-po="' . $row->no_po . '" 
            title="Delete">
            <i class="ri-delete-bin-line"></i>
        </button>
                </div>';
                })
                // --- Order Columns ---
                ->orderColumn('invoice_details', function ($query, $order) {
                    $query->orderBy('tbl_invoice.nomor_invoice', $order);
                })
                ->orderColumn('linked_references', function ($query, $order) {
                    $query->orderBy('tbl_po.no_po', $order);
                })
                ->orderColumn('status_section', function ($query, $order) {
                    $query->orderBy('invoice_amount', $order); // Now orders by the computed amount
                })
                ->orderColumn('due_date_timer', function ($query, $order) {
                    $query->orderBy('tbl_invoice.due_date', $order);
                })

                // --- Search Filters ---
                ->filterColumn('status_section', function ($query, $keyword) {
                    $keyword = strtolower($keyword);
                    $query->where(function ($q) use ($keyword) {
                        if ($keyword === 'paid') {
                            $q->orWhere('tbl_invoice.status_invoice', 1);
                        } elseif ($keyword === 'unpaid') {
                            $q->orWhere('tbl_invoice.status_invoice', 0);
                        }
                        $cleanKeyword = preg_replace('/[^0-9]/', '', $keyword);
                        if ($cleanKeyword != '') {
                            // Allow searching by the amount (numeric)
                            $q->orWhere(DB::raw('tbl_delivery.qty_delivered * tbl_po.harga'), 'like', "%{$cleanKeyword}%");
                        }
                    });
                })
                ->filterColumn('due_date_timer', function ($query, $keyword) {
                    $keyword = strtolower($keyword);
                    if ($keyword === 'overdue') {
                        $query->where('tbl_invoice.status_invoice', 0)
                            ->where('tbl_invoice.due_date', '<', now());
                    } elseif ($keyword === 'paid') {
                        $query->where('tbl_invoice.status_invoice', 1);
                    } else {
                        $query->where('tbl_invoice.due_date', 'like', "%{$keyword}%");
                    }
                })
                ->filterColumn('invoice_details', function ($query, $keyword) {
                    $query->where('tbl_invoice.nomor_invoice', 'like', "%{$keyword}%");
                })
                ->filterColumn('linked_references', function ($query, $keyword) {
                    $query->where('tbl_po.no_po', 'like', "%{$keyword}%")
                        ->orWhere('tbl_delivery.delivery_no', 'like', "%{$keyword}%");
                })

                ->rawColumns(['invoice_details', 'linked_references', 'status_section', 'due_date_timer', 'action'])
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
        $deliveries = \App\Models\Delivery::whereDoesntHave('invoice')
            ->with(['po.customer'])
            ->get();

        return view('invoice-create', compact('deliveries'));
    }

    // InvoiceController.php

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'delivery_id'   => 'required|exists:tbl_delivery,delivery_id|unique:tbl_invoice,delivery_id',
            'tgl_invoice'   => 'required|date',
            'due_date'      => 'required|date|after:tgl_invoice', // due_date must be after invoice date
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $delivery = \App\Models\Delivery::with('po')->findOrFail($request->delivery_id);

            // Generate a unique invoice number: INV-YYYYMMDD-XXXX (random 4 digits)
            $invoiceNumber = 'INV-' . now('Asia/Jakarta')->format('Ymd') . '-' . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

            Invoice::create([
                'delivery_id'   => $delivery->delivery_id,
                'tgl_invoice'   => $request->tgl_invoice,
                'nomor_invoice' => $invoiceNumber,
                'due_date'      => $request->due_date,
                'status_invoice' => 0,
                'input_date'    => now('Asia/Jakarta'),
                'input_by'      => Auth::id() ?? 1,
            ]);

            // Delivery's invoiced_status is updated by the Invoice model's boot method
            // PO status is synced via the same boot method

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
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
        return view('invoice-edit', compact('item', 'currentDelivery'));
    }

    public function update(Request $request, $id)
    {
        $item = Invoice::findOrFail($id);

        $request->validate([
            'delivery_id' => 'required|exists:tbl_delivery,delivery_id',
            'tgl_invoice' => 'required|datetime',
            'due_date' => 'required|datetime',
        ]);

        $data['tgl_invoice'] = $request->tgl_invoice;
        $data['due_date'] = $request->due_date;

        $item->update($data);
        return redirect()->route('invoice.index')->with('success', 'Invoice updated');
    }

    public function destroy($id)
    {
        Invoice::destroy($id);
        return back()->with('success', 'Invoice deleted');
    }

    public function show($invoice_id)
    {
        $invoice = Invoice::with([
            'delivery.po.customer',
            'payment' // Singular
        ])->findOrFail($invoice_id);

        return view('invoice-show', compact('invoice'));
    }
}
