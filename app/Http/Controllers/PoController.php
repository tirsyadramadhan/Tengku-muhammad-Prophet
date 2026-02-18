<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Po;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\Customer;
use Yajra\DataTables\Facades\DataTables; // Don't forget to import this at the top!
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Route;

class PoController extends Controller
{

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

        // 2. PO Number Logic (Keep as is)
        $latestPo = Po::where('status', 0)->orderBy('no_po', 'desc')->first();
        $nextNum = ($latestPo && preg_match('/(\d+)$/', $latestPo->no_po, $m)) ? intval($m[1]) + 1 : 1;
        $generatedNoPo = "52010xxxx" . $nextNum;

        // Capture values and default to 0 if null/empty
        $qty = (float) $request->qty;
        $harga = (float) $request->harga;
        $percentage = (float) ($request->margin_percentage ?? 0);
        $tambahan = (float) ($request->tambahan_margin ?? 0);

        // 3. Updated Margin Calculation Logic
        $totalPrice = $qty * $harga;

        $percentage = $request->margin_percentage; // 20%

        // Calculation: (10,000,000 * 0.20) + 1,000,000 = 3,000,000
        $calculatedMargin = ($totalPrice * ($percentage / 100)) + $tambahan;

        // 4. Prepare Data
        $data = $validator->validated();
        $data['no_po'] = $generatedNoPo;
        $data['status'] = 0;
        $data['margin'] = $calculatedMargin;
        $data['tambahan_margin'] = $tambahan;

        // 5. Create
        try {
            Po::create($data);
            return response()->json(['success' => true, 'message' => 'Created successfully', 'redirect_url' => route('incomingPo')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function incomingPo(Request $request)
    {
        if ($request->ajax()) {
            // 1. Filter: Only retrieve status_po = 0
            $query = Po::with('customer')
                ->select('tbl_po.*')
                ->where('status', 0);

            return DataTables::of($query)
                // 2. No Column: Automatically generates sequence 1..Max
                ->addIndexColumn()
                ->editColumn('tgl_po', function ($row) {
                    return Carbon::parse($row->tgl_po)->format('d M Y');
                })
                ->editColumn('no_po', function ($row) {
                    return '<span class="fw-bold text-dark">#' . $row->no_po . '</span>';
                })
                ->addColumn('product_customer', function ($row) {
                    $cust = $row->customer->cust_name ?? 'Walk-in Customer';
                    return '<div class="d-flex flex-column">
                            <span class="fw-semibold text-truncate" style="max-width: 200px;">' . $row->nama_barang . '</span>
                            <small class="text-primary"><i class="ri-user-smile-line me-1"></i>' . $cust . '</small>
                        </div>';
                })
                // Currency Formatting
                ->editColumn('total', fn($row) => 'Rp ' . number_format($row->total))
                ->editColumn('modal_awal', fn($row) => 'Rp ' . number_format($row->modal_awal))
                ->editColumn('margin', fn($row) => '+' . number_format($row->margin))

                // Status Logic
                ->addColumn('status_badge', function ($row) {
                    $status = strtolower($row->status);
                    $class = match ($status) {
                        'lunas', 'paid' => 'bg-label-success',
                        'proses', 'pending' => 'bg-label-warning',
                        'batal', 'cancelled' => 'bg-label-danger',
                        default => 'bg-label-info'
                    };
                    $icon = match ($status) {
                        'lunas', 'paid' => 'ri-checkbox-circle-line',
                        'proses', 'pending' => 'ri-time-line',
                        'batal', 'cancelled' => 'ri-close-circle-line',
                        default => 'ri-information-line'
                    };
                    return '<span class="badge ' . $class . ' rounded-pill px-3">
                            <i class="' . $icon . ' me-1"></i>' . strtoupper($row->status) . '
                        </span>';
                })
                ->addColumn('action', function ($row) {
                    // Helper to prevent crash if route is missing (optional safety)
                    $showUrl = Route::has('incoming-po.show') ? route('incoming-po.show', $row->po_id) : '#';
                    $editUrl = Route::has('incoming-po.edit') ? route('incoming-po.edit', $row->po_id) : '#';
                    $deleteUrl = Route::has('incoming-po.destroy') ? route('incoming-po.destroy', $row->po_id) : '#';

                    $csrf = csrf_field();
                    $method = method_field('DELETE');

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
                // IMPORTANT: Add 'action' here so HTML renders correctly
                ->rawColumns(['no_po', 'product_customer', 'status_badge', 'action'])
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
        Po::syncAll();
        if ($request->ajax()) {
            // Updated filter: status != 0 (excludes Incoming)
            $query = Po::with('customer')
                ->select('tbl_po.*')
                ->selectSub(function ($query) {
                    $query->from('tbl_delivery')
                        ->whereColumn('po_id', 'tbl_po.po_id')
                        ->selectRaw('COALESCE(SUM(qty_delivered), 0)');
                }, 'total_delivered')
                ->where('status', '!=', 0);

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('tgl_po', function ($row) {
                    return Carbon::parse($row->tgl_po)->format('d M Y');
                })
                ->editColumn('no_po', function ($row) {
                    return '<span class="fw-bold text-heading">#' . $row->no_po . '</span>';
                })
                ->addColumn('product_customer', function ($row) {
                    $cust = $row->customer->cust_name ?? 'Walk-in';
                    return '<div class="d-flex flex-column">
                    <span class="text-truncate fw-medium" style="max-width:200px" title="' . $row->nama_barang . '">' . $row->nama_barang . '</span>
                    <small class="text-muted">' . $cust . '</small>
                </div>';
                })
                ->editColumn('total', fn($row) => 'Rp ' . number_format($row->total))
                ->editColumn('modal_awal', fn($row) => 'Rp ' . number_format($row->modal_awal))
                ->editColumn('margin', fn($row) => 'Rp ' . number_format($row->margin))
                ->editColumn('qty', function ($row) {
                    $remaining = $row->qty - ($row->total_delivered ?? 0);
                    return $remaining . '/' . $row->qty;
                })
                ->addColumn('status_badge', function ($row) {
                    $statusVal = $row->status;

                    // Define mapping for statuses 1-7 (0 is excluded)
                    $statusMap = [
                        0 => ['label' => 'INCOMING', 'class' => 'bg-label-secondary', 'icon' => 'ri-edit-box-line'],
                        1 => ['label' => 'OPEN', 'class' => 'bg-label-warning', 'icon' => 'ri-mail-send-line'],

                        // Physical/Logistics Phase (Blue tones)
                        2 => ['label' => 'PARTIAL DELIVERY', 'class' => 'bg-label-info', 'icon' => 'ri-truck-line'],
                        3 => ['label' => 'FULL DELIVERY', 'class' => 'bg-label-info', 'icon' => 'ri-checkbox-circle-line'],

                        // Mixed/Billing Phase (Purple/Indigo tones)
                        4 => ['label' => 'PARTIAL DELIV & BILL', 'class' => 'bg-label-primary', 'icon' => 'ri-exchange-box-line'],
                        5 => ['label' => 'DELIVERED, PARTIAL BILL', 'class' => 'bg-label-primary', 'icon' => 'ri-draft-line'],
                        6 => ['label' => 'PARTIAL DELIV, FULL BILL', 'class' => 'bg-label-primary', 'icon' => 'ri-file-warning-line'],

                        // Finalization (Success Green)
                        7 => ['label' => 'WAITING PAYMENT', 'class' => 'bg-label-success', 'icon' => 'ri-check-double-line'],
                        8 => ['label' => 'CLOSED', 'class' => 'bg-label-success', 'icon' => 'ri-verified-badge-fill'],
                    ];
                    $default = ['label' => 'UNKNOWN', 'class' => 'bg-label-secondary', 'icon' => 'ri-question-line'];
                    $map = $statusMap[$statusVal] ?? $default;

                    return '<span class="badge ' . $map['class'] . ' rounded-pill px-3">
                    <i class="' . $map['icon'] . ' me-1"></i>' . $map['label'] . '
                </span>';
                })
                ->filterColumn('status', function ($query, $keyword) {
                    $keyword = strtolower($keyword);

                    $statusMapping = [
                        'open' => 1,
                        'partially delivered' => 2,
                        'fully delivered' => 3,
                        'partially delivered & partially invoiced' => 4,
                        'fully delivered & partially invoiced' => 5,
                        'partially delivered & fully invoiced' => 6,
                        'closed' => 7,
                    ];

                    $matched = false;
                    foreach ($statusMapping as $text => $value) {
                        if (str_contains($keyword, $text)) {
                            $query->where('tbl_po.status', $value);
                            $matched = true;
                            break;
                        }
                    }

                    if (!$matched && is_numeric($keyword)) {
                        $query->where('tbl_po.status', (int)$keyword);
                    } elseif (!$matched) {
                        $query->whereRaw('1 = 0');
                    }
                })
                ->addColumn('action', function ($row) {
                    // Helper to prevent crash if route is missing (optional safety)
                    $showUrl = Route::has('po.show') ? route('po.show', $row->po_id) : '#';
                    $editUrl = Route::has('po.edit') ? route('po.edit', $row->po_id) : '#';
                    $deleteUrl = Route::has('po.destroy') ? route('po.destroy', $row->po_id) : '#';

                    return '
                <div class="d-flex align-items-center gap-2">
                    <a href="' . $showUrl . '" class="btn btn-sm btn-icon btn-label-info" title="Details">
                        <i class="ri-eye-line"></i>
                    </a>
                    <a href="' . $editUrl . '" class="btn btn-sm btn-icon btn-label-warning" title="Edit">
                        <i class="ri-pencil-line"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-icon btn-label-danger btn-delete" 
            data-url="' . $deleteUrl . '" 
            data-po="' . $row->no_po . '" 
            title="Delete">
            <i class="ri-delete-bin-line"></i>
        </button>
                </div>';
                })
                ->rawColumns(['no_po', 'product_customer', 'status_badge', 'action'])
                ->make(true);
        }

        // STATS: Also updated here to ensure totals match the table data
        $filteredStats = Po::where('status', '!=', 0);

        $totalPo = $filteredStats->count();
        $totalRevenue = $filteredStats->sum('total');
        $totalCapital = $filteredStats->sum('modal_awal');
        $totalMargin = $filteredStats->sum('margin');

        return view('po-index', compact('totalPo', 'totalRevenue', 'totalCapital', 'totalMargin'));
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
        // 1. Validation – now includes 'no_po'
        $validator = Validator::make($request->all(), [
            'incoming_po_id' => 'required|exists:tbl_po,po_id',
            'no_po'          => 'required|string|max:50|unique:tbl_po,no_po', // enforce uniqueness
            'customer_id'    => 'required|exists:tbl_customer,id_cust',
            'tgl_po'         => 'required|date',
            'qty'            => 'required|numeric|min:1',
            'harga'          => 'required|numeric|min:0',
            'margin'         => 'required|numeric|min:0',
            'nama_barang'    => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // 2. Find the existing Incoming PO
            $po = Po::findOrFail($request->incoming_po_id);

            // 3. Update all fields, including no_po (user-editable)
            $po->no_po       = $request->no_po;          // now directly from input
            $po->customer_id = $request->customer_id;
            $po->nama_barang = $request->nama_barang;
            $po->tgl_po      = $request->tgl_po;
            $po->qty         = $request->qty;
            $po->harga       = $request->harga;
            $po->margin      = $request->margin;

            // 4. Change status to 1 (Open)
            $po->status = 1;

            $po->save();

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
        // 1. Find the existing record
        $po = Po::findOrFail($id);

        // 2. Validation
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

        // 3. Capture and sanitize values
        $qty = (float) $request->qty;
        $harga = (float) $request->harga;
        $percentage = (float) ($request->margin_percentage ?? 0);
        $tambahan = (float) ($request->tambahan_margin ?? 0);

        // 4. Calculation Logic
        $totalPrice = $qty * $harga;
        // Calculation: (Total Price * %) + Fixed Addition
        $calculatedMargin = ($totalPrice * ($percentage / 100)) + $tambahan;

        // 5. Prepare Data
        $data = $validator->validated();

        // Ensure we save the numeric values correctly
        $data['margin'] = $calculatedMargin;
        $data['tambahan_margin'] = $tambahan;
        $data['harga'] = $harga;
        $data['qty'] = $qty;
        $data['total'] = $totalPrice;

        // Note: We typically don't change 'no_po' or 'status' during a standard edit 
        // unless specifically required by your business logic.

        try {
            // 6. Update the instance (NOT Po::update)
            $po->update($data);

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
        // 1. Find the existing record
        $po = Po::findOrFail($id);

        // 2. Validation
        $validator = Validator::make($request->all(), [
            'nama_barang'       => 'required|string',
            'customer_id'       => 'required|exists:tbl_customer,id_cust',
            'tgl_po'            => 'required|date',
            'harga'             => 'required|numeric|min:0',
            'margin_percentage' => 'nullable|numeric|min:0',
            'tambahan_margin'   => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 3. Capture and sanitize values
        $qty = (float) $request->qty;
        $harga = (float) $request->harga;
        $percentage = (float) ($request->margin_percentage ?? 0);
        $tambahan = (float) ($request->tambahan_margin ?? 0);

        // 4. Calculation Logic
        $totalPrice = $qty * $harga;
        // Calculation: (Total Price * %) + Fixed Addition
        $calculatedMargin = ($totalPrice * ($percentage / 100)) + $tambahan;

        // 5. Prepare Data
        $data = $validator->validated();

        // Ensure we save the numeric values correctly
        $data['margin'] = $calculatedMargin;
        $data['tambahan_margin'] = $tambahan;
        $data['harga'] = $harga;
        $data['qty'] = $qty;
        $data['total'] = $totalPrice;

        // Note: We typically don't change 'no_po' or 'status' during a standard edit 
        // unless specifically required by your business logic.

        try {
            // 6. Update the instance (NOT Po::update)
            $po->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Updated successfully',
                'redirect_url' => route('po.index')
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
        $totalPrice = $po->harga * $po->qty;
        $tambahanMargin = $po->tambahan_margin; // This must be known/stored

        // Reverse calculation
        $cleanMargin = $totalMargin - $tambahanMargin;
        $marginPercentage = ($cleanMargin / $totalPrice) * 100;

        return view('incoming-po-edit', compact('po', 'customers', 'currentCustomer', 'marginPercentage'));
    }
    public function editPo($po_id)
    {
        $po = Po::findOrFail($po_id);
        $currentCustomer = Customer::where('id_cust', $po->customer_id)->first();
        $customers = Customer::all(); // Needed for the dropdown in edit form

        // Assuming these are your variables
        $totalMargin = $po->margin;
        $totalPrice = $po->harga * $po->qty;
        $tambahanMargin = $po->tambahan_margin; // This must be known/stored

        // Reverse calculation
        $cleanMargin = $totalMargin - $tambahanMargin;
        $marginPercentage = ($cleanMargin / $totalPrice) * 100;

        return view('po-edit', compact('po', 'customers', 'currentCustomer', 'marginPercentage'));
    }

    /**
     * Remove the specified Purchase Order from storage.
     */
    public function destroy($po_id)
    {
        try {
            $po = Po::findOrFail($po_id);
            $po->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'PO #' . $po->no_po . ' berhasil dihapus.'
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == "23000") {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tidak bisa dihapus! PO ini masih terhubung dengan data Delivery atau Invoice.'
                ], 422); // Unprocessable Content
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem.'
            ], 500);
        }
    }

    public function destroyPo($po_id)
    {
        try {
            $po = Po::findOrFail($po_id);
            $po->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'PO #' . $po->no_po . ' berhasil dihapus.'
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == "23000") {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tidak bisa dihapus! PO ini masih terhubung dengan data Delivery atau Invoice.'
                ], 422); // Unprocessable Content
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem.'
            ], 500);
        }
    }
}
