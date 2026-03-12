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
                    $user = Auth::user()->role_id !== 2;
                    return $user ? '
                    <div class="dropdown">
                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                            <i class="ri-more-2-fill fs-5"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="' . route('customers.edit', $row->id_cust) . '">
                                <i class="ri-pencil-line me-2"></i> Edit
                            </a>
                            <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteCustomer(' . $row->id_cust . ')">
                                <i class="ri-delete-bin-line me-2"></i> Delete
                            </a>
                        </div>
                    </div>'
                        : '';
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
        $request->validate([
            'cust_name' => 'required|min:3|max:100',
        ]);

        Customer::create($request->only('cust_name'));

        return response()->json([
            'success' => true,
            'message' => 'Customer berhasil disimpan.',
            'redirect' => route('customers.index'),
        ]);
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
        try {
            $item = Customer::findOrFail($id);
            $oldCustomer = clone $item;

            $item->update([
                'cust_name' => $request->cust_name,
                'edit_by'   => Auth::id(),
                'edit_date' => now()
            ]);

            $this->logUpdate($item, $oldCustomer, 'Di ubah Customer ' . $oldCustomer->cust_name . ' menjadi ' . $item->cust_name);

            return response()->json([
                'success'      => true,
                'message'      => 'Customer berhasil diperbarui!',
                'redirect_url' => route('customers.index')
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Customer tidak ditemukan!'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui customer: ' . $e->getMessage()
            ], 500);
        }
    }

    // Delete customer
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $this->logDelete($customer, $customer, 'Di hapus Customer ' . $customer->cust_name);
        Customer::destroy($id);
        return response()->json(['success' => true, 'message' => 'Customer deleted!']);
    }
}
