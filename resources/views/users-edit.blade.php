@extends('layouts/contentNavbarLayout')
@section('content')
<div class="row">
    <div class="col-xl-8 col-lg-10 mx-auto">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Ubah Pengguna</h5>
            </div>
            <div class="card-body">
                <form class="needs-validation" id="edit-user" action="{{ route('users.update', $user->user_id) }}" method="POST" novalidate autocomplete="off">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="form-label fw-bold">Nama Pengguna</label>
                        <input id="user_name" value="{{ $user->user_name }}" type="text" name="user_name" class="form-control live-validate" placeholder="Username">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Role Pengguna</label>
                        <select class="form-select" name="role_id" id="role-pengguna">
                            <option value="{{ $user->role_id }}">{{ $user->role->role_name }}</option>
                            @if ($user->role_id != 1)
                            <option value="1">Administrator</option>
                            @endif
                            @if ($user->role_id != 2)
                            <option value="2">Visitor</option>
                            @endif
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Email</label>
                        <input id="email" value="{{ $user->email }}" type="text" name="email" class="form-control live-validate" placeholder="Email">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Foto Profil (Opsional)</label>
                        <input id="profile_picture" type="file" name="profile_picture" class="form-control live-validate" placeholder="Foto Profil">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Password Baru (Opsional)</label>
                        <input type="password" name="password" id="password-input" class="form-control live-validate" placeholder="Password">
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" id="btnSave" class="btn btn-primary px-5">
                            <span id="btnSpinner" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                            Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection