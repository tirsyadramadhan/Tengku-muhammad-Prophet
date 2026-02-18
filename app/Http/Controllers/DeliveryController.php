<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Po;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Route;

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
                    'tbl_po.nama_barang',
                    'tbl_po.no_po'
                );

            return DataTables::of($data)
                ->addIndexColumn()

                // 2. Custom columns (unchanged, but now we have nama_barang & no_po in the row)
                ->addColumn('po_tracking', function ($row) {
                    $itemName = $row->nama_barang ?? 'Unassigned Item';
                    $poNo     = $row->no_po ?? 'N/A';
                    return '
                <div class="d-flex flex-column">
                    <span class="fw-bold text-primary">' . e($itemName) . '</span>
                    <small class="text-muted">
                        <i class="ri-file-list-3-line me-1"></i>PO: ' . e($poNo) . '
                    </small>
                </div>';
                })
                ->editColumn('qty_delivered', function ($row) {
                    return '<span class="badge bg-label-secondary fw-bold px-3">'
                        . number_format($row->qty_delivered) . ' Units</span>';
                })
                ->addColumn('status', function ($row) {
                    if ($row->delivered_status == 1) {
                        return '<span class="badge bg-label-success rounded-pill px-3 py-2">
                        <i class="ri-checkbox-circle-fill me-1"></i> SECURELY ARRIVED
                    </span>';
                    } else {
                        return '<div class="timer-wrapper" 
                         data-target="' . $row->delivery_time_estimation . '" 
                         data-id="' . $row->delivery_id . '">
                         <div class="timer-badge bg-label-warning mb-1 d-inline-block px-2 rounded">
                            <span class="pulse-dot"></span>
                            <span class="countdown-display fw-bold text-warning">CALCULATING...</span>
                         </div>
                         <div class="small text-muted mt-1">Moving in Transit</div>
                    </div>';
                    }
                })
                ->addColumn('action', function ($row) {
                    // Helper to prevent crash if route is missing (optional safety)
                    $showUrl = Route::has('delivery.show') ? route('delivery.show', $row->delivery_id) : '#';
                    $editUrl = Route::has('delivery.edit') ? route('delivery.edit', $row->delivery_id) : '#';
                    $deleteUrl = Route::has('delivery.destroy') ? route('delivery.destroy', $row->delivery_id) : '#';

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
            data-po="' . $row->delivery_no . '" 
            title="Delete">
            <i class="ri-delete-bin-line"></i>
        </button>
                </div>';
                })
                ->rawColumns(['po_tracking', 'qty_delivered', 'status', 'action']) // 'action' removed

                // 3. Sorting logic for custom columns
                ->orderColumn('po_tracking', 'nama_barang $1') // sort by PO item name
                ->orderColumn('status', 'delivered_status $1, delivery_time_estimation $1') // sort by arrived first, then ETA

                // 4. Searching logic for custom columns
                ->filterColumn('po_tracking', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('tbl_po.nama_barang', 'like', "%{$keyword}%")
                            ->orWhere('tbl_po.no_po', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('status', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        // 1. Search by status text keywords
                        $lowerKeyword = strtolower($keyword);
                        if (str_contains($lowerKeyword, 'arrived') || str_contains($lowerKeyword, 'secure')) {
                            $q->orWhere('delivered_status', 1);
                        }
                        if (str_contains($lowerKeyword, 'transit') || str_contains($lowerKeyword, 'moving')) {
                            $q->orWhere('delivered_status', 0);
                        }

                        // 2. FIX: Search by the actual ETA Date
                        // This enables searching for "2024", "10-25", etc.
                        $q->orWhere('delivery_time_estimation', 'like', "%{$keyword}%");
                    });
                })
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
            'po_id'                     => 'required|exists:tbl_po,po_id',
            'delivery_time_estimation' => 'required|date|after_or_equal:today',
            'qty_delivered'             => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $po = Po::findOrFail($request->po_id);

            // Check against original quantity (not remaining!)
            $totalDeliveredSoFar = $po->deliveries()->sum('qty_delivered');
            $newTotal = $totalDeliveredSoFar + $request->qty_delivered;
            if ($newTotal > $po->qty) {
                return response()->json([
                    'errors' => ['qty_delivered' => ['Total delivered would exceed PO quantity.']]
                ], 422);
            }

            // Generate unique delivery number
            $deliveryNo = 'DLV-' . date('Ymd') . '-' . strtoupper(uniqid());

            // Create delivery
            $delivery = Delivery::create([
                'delivery_no'               => $deliveryNo,
                'po_id'                      => $request->po_id,
                'qty_delivered'              => $request->qty_delivered,
                'delivery_time_estimation'   => $request->delivery_time_estimation,
                'invoiced_status'            => 0,
                'delivered_status'           => 0,
                'input_by'                   => Auth::id() ?? 1,
                'input_date'                 => now(),
            ]);

            // Sync PO status (this will use original qty and sum of deliveries)
            $po->syncStatus();

            return response()->json([
                'success' => true,
                'message' => 'Delivery recorded. PO status updated.',
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
        $item = Delivery::findOrFail($id);

        // CRITICAL CHECK: 0 = Uninvoiced, 1 = Invoiced
        if ($item->invoiced_status == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Restriction: This delivery has already been invoiced and cannot be deleted.'
            ], 422);
        }

        try {
            $item->delete();
            return response()->json([
                'success' => true,
                'message' => 'Delivery record deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server Error: Could not delete record.'
            ], 500);
        }
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

            // 4. Update the existing delivery record
            $delivery->update([
                'po_id'                    => $request->po_id,
                'qty_delivered'            => $request->qty_delivered,
                'delivery_time_estimation' => $request->delivery_time_estimation,
                // Use 'edit_by' instead of 'input_by' for updates if your table supports it
                'edit_by'                  => Auth::id() ?? 1,
                'edit_date'                => now(),
            ]);

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
