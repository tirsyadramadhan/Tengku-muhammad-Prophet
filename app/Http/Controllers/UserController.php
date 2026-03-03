<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = User::query()
                ->leftJoin('tbl_role', 'tbl_user.role_id', '=', 'tbl_role.role_id')
                ->select([
                    'tbl_user.*',
                    'tbl_role.role_name'
                ]);
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('actions', function ($row) {
                    $showUrl = Route::has('users.show') ? route('users.show', $row->user_id) : '#';
                    $editUrl = Route::has('users.edit') ? route('users.edit', $row->user_id) : '#';
                    $deleteUrl = Route::has('users.destroy') ? route('users.destroy', $row->user_id) : '#';

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
                ->rawColumns(['actions'])
                ->make(true);
        }
        return view('users');
    }

    public function create()
    {
        return view('users-create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'unique:tbl_user,user_name|required|regex:/^[a-zA-Z0-9_-]{3,16}$/',
            'email' => 'unique:tbl_user,email|required|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'password' => 'required|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
            'role_id' => 'required|in:1,2'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            User::create([
                'user_name' => $request->user_name,
                'email'     => $request->email,
                'role_id' => $request->role_id,
                'password'  => Hash::make($request->password),
            ]);
            return response()->json(['success' => true, 'message' => 'Created successfully', 'redirect_url' => route('users.index')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($user_id)
    {
        $user = User::findOrFail($user_id);

        return view('users-show', compact('user'));
    }

    public function edit($user_id)
    {
        $user = User::findOrFail($user_id);

        return view('users-edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'user_name' => 'required|regex:/^[a-zA-Z0-9_-]{3,16}$/',
            'email' => 'required|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'password' => 'nullable|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
            'role_id' => 'required|in:1,2'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update([
            'user_name' => $request->user_name,
            'email'     => $request->email,
            'role_id'     => $request->role_id,
            'password'     => Hash::make($request->password),
        ]);

        return response()->json(['success' => true, 'redirect_url' => route('users.index')]);
    }

    public function destroy(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'PO #' . $user->user_name . ' berhasil dihapus.'
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem.'
            ], 500);
        }
    }
}
