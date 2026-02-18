<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class CustomerController extends Controller
{
    // List all customers (via DataTables jika AJAX)
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Customer::query(); // query builder

            return DataTables::of($data)
                ->addIndexColumn() // menambah kolom DT_RowIndex (NO)
                ->addColumn('action', function ($row) {
                    $btn = '<div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="ri-more-2-fill fs-5"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="' . route('customer.edit', $row->id_cust) . '">
                                        <i class="ri-pencil-line me-2"></i> Edit
                                    </a>
                                    <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteCustomer(' . $row->id_cust . ')">
                                        <i class="ri-delete-bin-line me-2"></i> Delete
                                    </a>
                                </div>
                            </div>';
                    return $btn;
                })
                ->editColumn('input_date', function ($row) {
                    return Carbon::parse($row->input_date)->format('d M Y H:i');
                })
                ->rawColumns(['action']) // agar HTML di action tidak di-escape
                ->make(true);
        }

        // Jika bukan AJAX, return view (untuk inisialisasi halaman)
        return view('customer-index');
    }

    // Show form to add new customer
    public function create()
    {
        return view('customer-create');
    }

    // Save new customer
    public function store(Request $request)
    {
        try {
            Customer::create([
                'cust_name'  => $request->cust_name,
                'input_by'   => Auth::id(),
                'input_date' => now()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Customer created!',
                    'redirect_url' => route('customer.index')
                ]);
            }

            return redirect()->route('customer.index')->with('success', 'Customer created!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create customer: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to create customer.');
        }
    }

    // Show form to edit existing customer
    public function edit($id)
    {
        $item = Customer::findOrFail($id);
        return view('customer-edit', compact('item'));
    }

    // Update existing customer
    public function update(Request $request, $id)
    {
        $item = Customer::findOrFail($id);
        $item->update([
            'cust_name' => $request->cust_name,
            'edit_by'   => Auth::id(),
            'edit_date' => now()
        ]);
        return redirect()->route('customer.index')->with('success', 'Customer updated!');
    }

    // Delete customer
    public function destroy($id)
    {
        Customer::destroy($id);
        return response()->json(['success' => true, 'message' => 'Customer deleted!']);
    }
}
