<?php

namespace App\Http\Controllers;

use App\Models\Margin;
use App\Models\Po;
use Illuminate\Http\Request;

class MarginController extends Controller
{
    public function index()
    {
        $data = Margin::with('po.customer')->get();
        return view('margin-index', compact('data'));
    }

    public function create()
    {
        // Only show POs that DO NOT have a margin record yet
        $pos = Po::whereDoesntHave('margin')->get();
        return view('margin-create', compact('pos'));
    }
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'po_id' => 'required|exists:tbl_po,po_id|unique:tbl_margin,po_id',
    //         // Validates added_margin only if the toggle is NOT checked
    //         'added_margin' => 'required_without:hold_margin_toggle|nullable|numeric',
    //     ]);

    //     $po = Po::findOrFail($request->po_id);

    //     // 1. Margin Acquired: Only if PO is Closed
    //     $marginAcquired = ($po->status === 'Closed') ? $po->margin : 0;

    //     // 2. Held Margin: If toggled OR if PO is NOT closed
    //     $heldMargin = ($request->has('hold_margin_toggle') || $po->status !== 'Closed')
    //         ? $po->margin
    //         : 0;

    //     // 3. Held Modal: If toggled OR if PO is NOT closed
    //     $heldModal = ($request->has('hold_modal_toggle') || $po->status !== 'Closed')
    //         ? $po->modal_awal
    //         : 0;

    //     Margin::create([
    //         'po_id'           => $po->po_id,
    //         'margin_unit'     => $po->margin_unit, // From selected PO
    //         'added_margin'    => $request->input('added_margin', 0), // Use 0 if disabled in UI
    //         'margin_acquired' => $marginAcquired,
    //         'held_margin'     => $heldMargin,
    //         'held_modal'      => $heldModal,
    //         'total_margin'    => $po->margin,     // Value of margin from PO
    //         'modal'           => $po->modal_awal, // Value of modal_awal from PO
    //     ]);

    //     return redirect()->route('margin.index')->with('success', 'Margin record created successfully.');
    // }

    public function store(Request $request)
    {
        $request->validate([
            'po_id' => 'required|exists:tbl_po,po_id|unique:tbl_margin,po_id',
            // 'added_margin' => 'required_without:hold_margin_toggle|nullable|numeric',
        ]);

        $po = Po::findOrFail($request->po_id);
        $addedMarginValue = $request->input('added_margin', 0);

        // Update the PO's margin if an added_margin is inputted
        if ($addedMarginValue > 0) {
            // Updated formula: total_margin (stored as 'margin' in tbl_po) + added_margin
            $po->margin += $addedMarginValue;

            // Recalculate margin_unit for the PO to maintain consistency
            if ($po->qty > 0) {
                $po->margin_unit = $po->margin / $po->qty;
            }

            $po->save();
        }

        // 1. Margin Acquired: Only if PO is Closed
        // Note: This now uses the updated $po->margin if added_margin was provided
        $marginAcquired = ($po->status === 'Closed') ? $po->margin : 0;

        // 2. Held Margin: If toggled OR if PO is NOT closed
        $heldMargin = ($request->has('hold_margin_toggle') || $po->status !== 'Closed')
            ? $po->margin
            : 0;

        // 3. Held Modal: If toggled OR if PO is NOT closed
        $heldModal = ($request->has('hold_modal_toggle') || $po->status !== 'Closed')
            ? $po->modal_awal
            : 0;

        Margin::create([
            'po_id'           => $po->po_id,
            'margin_unit'     => $po->margin_unit,
            'added_margin'    => $addedMarginValue,
            'margin_acquired' => $marginAcquired,
            'held_margin'     => $heldMargin,
            'held_modal'      => $heldModal,
            'total_margin'    => $po->margin,     // Stores the newly updated total
            'modal'           => $po->modal_awal,
        ]);

        return redirect()->route('margin.index')->with('success', 'Margin record created successfully.');
    }
}
