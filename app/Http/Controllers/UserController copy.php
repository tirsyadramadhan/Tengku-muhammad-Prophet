<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Traits\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    use ActivityLogger;
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = User::query()
                ->leftJoin('tbl_role', 'tbl_user.role_id', '=', 'tbl_role.role_id')
                ->select([
                    'tbl_user.*',
                    'tbl_role.role_name',
                    'tbl_role.role_id as tbl_role_id',
                ])
                ->orderBy('tbl_user.last_login', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('user_name', fn($row) => $row->user_name ?? '-')
                ->orderColumn('user_name', fn($q, $o) => $q->orderBy('tbl_user.user_name', $o))
                ->filterColumn('user_name', fn($q, $k) => $q->where('tbl_user.user_name', 'like', "%{$k}%"))
                ->addColumn('email', fn($row) => $row->email ?? '-')
                ->orderColumn('email', fn($q, $o) => $q->orderBy('tbl_user.email', $o))
                ->filterColumn('email', fn($q, $k) => $q->where('tbl_user.email', 'like', "%{$k}%"))
                ->addColumn('role_name', fn($row) => $row->role_name ?? '-')
                ->orderColumn('role_name', fn($q, $o) => $q->orderBy('tbl_role.role_name', $o))
                ->filterColumn('role_name', function ($q, $k) {
                    if ($k !== '') {
                        $q->where('tbl_user.role_id', $k); // match by role_id, not name
                    }
                })
                ->addColumn('is_active', function ($row) {
                    return $row->is_active == 1
                        ? '<span class="badge bg-success rounded-pill">Aktif</span>'
                        : '<span class="badge bg-danger rounded-pill">Nonaktif</span>';
                })
                ->orderColumn('is_active', fn($q, $o) => $q->orderBy('tbl_user.is_active', $o))
                ->filterColumn('is_active', function ($q, $k) {
                    $val = match (strtolower(trim($k))) {
                        'aktif'     => 1,
                        'nonaktif'  => 0,
                        default     => null,
                    };
                    if (!is_null($val)) {
                        $q->where('tbl_user.is_active', $val);
                    } else {
                        $q->whereRaw('CAST(tbl_user.is_active AS CHAR) LIKE ?', ["%{$k}%"]);
                    }
                })
                ->addColumn('last_login', function ($row) {
                    return $row->last_login
                        ? \Carbon\Carbon::parse($row->last_login)->translatedFormat('d M Y, H:i')
                        : '<span class="badge bg-label-secondary rounded-pill">Belum pernah</span>';
                })
                ->orderColumn('last_login', fn($q, $o) => $q->orderBy('tbl_user.last_login', $o))
                ->filterColumn('last_login', function ($q, $k) {
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($k))) {
                        $q->whereDate('tbl_user.last_login', trim($k));
                    } else {
                        $q->where('tbl_user.last_login', 'like', "%{$k}%");
                    }
                })
                ->addColumn('actions', function ($row) {
                    $showUrl   = Route::has('users.show')    ? route('users.show',    $row->user_id) : '#';
                    $editUrl   = Route::has('users.edit')    ? route('users.edit',    $row->user_id) : '#';
                    $deleteUrl = Route::has('users.destroy') ? route('users.destroy', $row->user_id) : '#';

                    $authUser      = Auth::user();
                    $canEditDelete = $authUser && $authUser->role_id !== 2;

                    $editItem = $canEditDelete ? <<<HTML
                        <li>
                            <a href="{$editUrl}" class="dropdown-item text-warning">
                                <i class="ri-pencil-line me-2"></i>Edit
                            </a>
                        </li>
                    HTML : '';

                    $deleteItem = $canEditDelete ? <<<HTML
                        <li>
                            <button type="button"
                                class="dropdown-item text-danger btn-delete-ajax"
                                data-url="{$deleteUrl}">
                                <i class="ri-delete-bin-line me-2"></i>Delete
                            </button>
                        </li>
                    HTML : '';

                    return <<<HTML
                    <div class="dropdown">
                        <button type="button"
                            class="btn btn-sm btn-icon btn-label-secondary"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="ri-more-2-line"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li>
                                <a href="{$showUrl}" class="dropdown-item text-info">
                                    <i class="ri-eye-line me-2"></i>Details
                                </a>
                            </li>
                            {$editItem}
                            {$deleteItem}
                        </ul>
                    </div>
                    HTML;
                })
                ->rawColumns(['is_active', 'last_login', 'actions'])
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
            'role_id' => 'required|in:1,2',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            $path = null;

            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $filename = time() . '_' . $file->getClientOriginalName();

                $file->move(public_path('profile_pictures'), $filename);

                $path = 'profile_pictures/' . $filename;
                // stores to: public/profile_pictures/filename.jpg
            }

            $user = User::create([
                'user_name' => $request->user_name,
                'email'     => $request->email,
                'role_id' => $request->role_id,
                'password'  => Hash::make($request->password),
                'profile_picture' => $path
            ]);
            $this->logCreate($user, 'Ditambahkan User ' . $user->user_name . ' dengan role ' . $user->role->role_name);
            return response()->json(['success' => true, 'message' => 'Created successfully', 'redirect_url' => route('users.index')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $user = User::with("activities")->find($id);

        $currencyFields = ['harga', 'total', 'modal_awal', 'margin', 'margin_unit', 'tambahan_margin'];

        return view("users-show", compact("user", 'currencyFields'));
    }

    public function edit($user_id)
    {
        $user = User::findOrFail($user_id);

        return view('users-edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $oldUserModel = clone $user;

        $validator = Validator::make($request->all(), [
            'user_name' => 'required|regex:/^[a-zA-Z0-9_-]{3,16}$/',
            'email' => 'required|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'password' => 'nullable|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
            'role_id' => 'required|in:1,2',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $oldUser = $user->toArray();

        $path = null;

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filename = time() . '_' . $file->getClientOriginalName();

            $file->move(public_path('profile_pictures'), $filename);

            $path = 'profile_pictures/' . $filename;
            // stores to: public/profile_pictures/filename.jpg
        }

        $updateData = [
            'user_name' => $request->user_name,
            'email'     => $request->email,
            'role_id'   => $request->role_id,
            'password'  => Hash::make($request->password),
        ];

        if ($path) {
            $updateData['profile_picture'] = $path;
        }

        $user->update($updateData);

        $newUser = $user->fresh();

        $this->logUpdate($newUser, $oldUser, 'User ' . $oldUserModel->user_name . ' dengan role ' . $oldUserModel->role->role_name . ' Di update menjadi ' . 'User ' . $user->user_name . ' dengan role ' . $user->role->role_name);

        return response()->json(['success' => true, 'redirect_url' => route('users.index')]);
    }

    public function destroy(Request $request, $id)
    {
        try {
            $user = User::with('role')->findOrFail($id);

            $userName  = $user->user_name ?? 'Unknown';
            $roleName  = $user->role->role_name ?? 'Unknown';

            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            $user->delete();

            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $this->logDelete($user, $user, "User {$userName} dengan role {$roleName} dihapus");

            return response()->json([
                'status'  => 'success',
                'message' => "User {$userName} berhasil dihapus."
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'User tidak ditemukan.'
            ], 404);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1'); // restore if query blows up mid-flight
            return response()->json([
                'status'  => 'error',
                'message' => 'Query gagal: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1'); // restore on any other failure too
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function profile($id)
    {
        $user = User::with("activities")->find($id);

        $currencyFields = ['harga', 'total', 'modal_awal', 'margin', 'margin_unit', 'tambahan_margin'];

        return view("users-profile", compact("user", 'currencyFields'));
    }

    public function editFromShowPage(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $oldUserModel = clone $user;

        $validator = Validator::make($request->all(), [
            'user_name' => 'required|regex:/^[a-zA-Z0-9_-]{3,16}$/',
            'email' => 'required|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $oldUser = $user->toArray();

        $user->update([
            'user_name' => $request->user_name,
            'email'     => $request->email,
            'is_active'     => $request->status
        ]);

        $newUser = $user->fresh();

        if ($request->status == 1) {
            $this->logUpdate($newUser, $oldUser, 'User ' . $oldUserModel->user_name . ' dengan role ' . $oldUserModel->role->role_name . ' Diaktifkan');
        } elseif ($request->status == 0) {
            $this->logUpdate($newUser, $oldUser, 'User ' . $oldUserModel->user_name . ' dengan role ' . $oldUserModel->role->role_name . ' Dinonaktifkan');
        }

        return response()->json(['success' => true, 'redirect_url' => route('users.index')]);
    }

    public function activate($id)
    {
        $user = User::findOrFail($id);

        // Already active
        if ((int) $user->is_active === 1) {
            return response()->json([
                'message' => 'Akun ini sudah aktif.'
            ], 422);
        }

        $user->update([
            'is_active' => 1,
            'edit_by'   => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Akun berhasil diaktifkan.'
        ]);
    }

    public function deactivate($id)
    {
        $user = User::findOrFail($id);

        // Prevent deactivating own account
        if ($user->user_id === Auth::id()) {
            return response()->json([
                'message' => 'Tidak dapat menonaktifkan akun sendiri.'
            ], 403);
        }

        // Already inactive
        if ((int) $user->is_active === 0) {
            return response()->json([
                'message' => 'Akun ini sudah nonaktif.'
            ], 422);
        }

        $user->update([
            'is_active' => 0,
            'edit_by'   => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Akun berhasil dinonaktifkan.'
        ]);
    }
}
