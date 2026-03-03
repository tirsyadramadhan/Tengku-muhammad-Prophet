<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
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
        Invoice::syncAllInvoiceStatus();
        if ($request->ajax()) {
            $data = Invoice::select([
                'tbl_invoice.*',
                'tbl_delivery.delivery_no'
            ])->join('tbl_delivery', 'tbl_delivery.delivery_id', '=', 'tbl_invoice.delivery_id')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status_invoice', function ($row) {
                    if ($row->status_invoice == 1) {
                        return
                            '

                        ';
                    }
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
                ->rawColumns(['action'])
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
            'tgl_invoice' => 'required',
            'due_date'    => 'required',
        ]);

        $data['tgl_invoice'] = $request->tgl_invoice;
        $data['due_date']    = $request->due_date;

        $item->update($data);

        return response()->json([
            'success'      => true,
            'message'      => 'Invoice berhasil diperbarui.',
            'redirect_url' => route('invoice.index'),
        ]);
    }

    public function destroy($id)
    {
        $item = Invoice::findOrFail($id);
        $item->delete();

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
}
