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
                    'tbl_role.role_name'
                ]);
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('user_details', function ($row) {

                    // ── [1] Avatar ────────────────────────────────────────────────────────────
                    $picturePath    = $row->profile_picture ? public_path($row->profile_picture) : public_path('defaults/default-avatar.jpg');
                    $profilePicture = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($picturePath));
                    // resolve it up top with your other variables
                    $defaultAvatar = 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('defaults/default-avatar.jpg')));

                    // ── [2] Sanitise output ───────────────────────────────────────────────────
                    $username = e($row->user_name ?? '—');
                    $email    = e($row->email    ?? '—');
                    $roleName = e($row->role?->role_name ?? 'No Role');

                    // ── [3] Dates ─────────────────────────────────────────────────────────────
                    $inputDateFixed = '—';
                    $lastLoginFixed = 'Belum pernah login';

                    if (!empty($row->input_date)) {
                        $inputDateFixed = Carbon::parse($row->input_date)->toIndonesianRelative();
                    }

                    if (!empty($row->last_login)) {
                        $lastLoginFixed = Carbon::parse($row->last_login)->toIndonesianRelative();
                    }

                    // ── [4] Status ────────────────────────────────────────────────────────────
                    $isActive      = ((int) $row->is_active) === 1;
                    $statusLabel   = $isActive ? 'Akun Aktif'              : 'Akun Nonaktif';
                    $badgeMod      = $isActive ? 'pc__badge--active'  : 'pc__badge--inactive';
                    $dotMod        = $isActive ? 'pc__dot--on'        : 'pc__dot--off';
                    $roleNoRole    = ($row->role?->role_name === null) ? 'pc__role--norole' : '';

                    // ── [5] Heredoc render ────────────────────────────────────────────────────
                    return <<<HTML
                    <div class="pc">

                    <!-- HEADER: avatar + name + status -->
                    <div class="pc__header">
                        <div class="pc__avatar-wrap">
                        <img
                            src="{$profilePicture}"
                            alt="{$username}"
                            class="pc__avatar"
                            loading="lazy"
                            onerror="this.src='{$defaultAvatar}'"
                        />
                        <span class="pc__dot {$dotMod}"></span>
                        </div>

                        <div class="pc__info">
                        <div class="pc__name" title="{$username}">{$username}</div>
                        <span class="pc__badge {$badgeMod}">
                            <span class="pc__badge__dot"></span>
                            {$statusLabel}
                        </span>
                        </div>
                    </div>

                    <!-- BODY: email + meta rows -->
                    <div class="pc__body">

                        <!-- Email row -->
                        <div class="pc__email" title="{$email}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                        </svg>
                        <span class="text-white">{$email}</span>
                        </div>

                        <!-- Meta grid -->
                        <div class="pc__meta">

                        <!-- Role -->
                        <div class="pc__row">
                            <div class="pc__row-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2a5 5 0 1 0 0 10A5 5 0 0 0 12 2z"/>
                                <path d="M2 22c0-5.523 4.477-10 10-10s10 4.477 10 10"/>
                            </svg>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="pc__row-label text-white">Role</span>
                                <span class="pc__role {$roleNoRole}">{$roleName}</span>
                            </div>
                        </div>

                        <div class="pc__divider"></div>

                        <!-- Created -->
                        <div class="pc__row">
                            <div class="pc__row-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8"  y1="2" x2="8"  y2="6"/>
                                <line x1="3"  y1="10" x2="21" y2="10"/>
                            </svg>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="pc__row-label text-white">Dibuat</span>
                                <span class="pc__row-label text-primary">{$row->input_date}</span>
                                <span class="pc__row-val">{$inputDateFixed}</span>
                            </div>
                        </div>

                        <!-- Last login -->
                        <div class="pc__row">
                            <div class="pc__row-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12,6 12,12 16,14"/>
                            </svg>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="pc__row-label text-white">Terakhir Login</span>
                                <span class="pc__row-label text-primary">{$row->last_login}</span>
                                <span class="pc__row-val">{$lastLoginFixed}</span>
                            </div>
                        </div>

                        </div><!-- /.pc__meta -->
                    </div><!-- /.pc__body -->
                    </div><!-- /.pc -->
                    HTML;
                })
                ->addColumn('actions', function ($row) {
                    $showUrl = Route::has('users.show') ? route('users.show', $row->user_id) : '#';
                    $editUrl = Route::has('users.edit') ? route('users.edit', $row->user_id) : '#';
                    $deleteUrl = Route::has('users.destroy') ? route('users.destroy', $row->user_id) : '#';

                    $user = Auth::user();
                    $canEditDelete = $user && $user->role_id !== 2;

                    $editBtn = $canEditDelete ? '
                    <a href="' . $editUrl . '" class="btn btn-sm btn-icon btn-label-warning" title="Edit">
                        <i class="ri-pencil-line"></i>
                    </a>' : '';

                    $deleteBtn = $canEditDelete ? '
                    <button type="button" class="btn btn-sm btn-icon btn-label-danger btn-delete-ajax" 
                        data-url="' . $deleteUrl . '" 
                        title="Delete">
                        <i class="ri-delete-bin-line"></i>
                    </button>' : '';

                    return '
                    <div class="d-flex align-items-center gap-2">
                        <a href="' . $showUrl . '" class="btn btn-sm btn-icon btn-label-info" title="Details">
                            <i class="ri-eye-line"></i>
                        </a>
                        ' . $editBtn . '
                        ' . $deleteBtn . '
                    </div>
                    ';
                })
                ->rawColumns(['actions', 'user_details'])
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
