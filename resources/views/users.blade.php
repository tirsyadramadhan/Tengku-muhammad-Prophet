@extends('layouts/contentNavbarLayout')

@section('title', 'Users')

@section('content')
<div id="main-container-index" class="container-fluid px-3 px-md-4 py-4">

    {{-- ── Page Header ──────────────────────────────────────── --}}
    <div class="row align-items-center g-3 mb-4">
        <div class="col-12 col-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar avatar-md flex-shrink-0">
                    <span class="avatar-initial rounded bg-label-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                            <path fill="#E1B530" d="M3.783 2.826L12 1l8.217 1.826a1 1 0 0 1 .783.976v9.987a6 6 0 0 1-2.672 4.992L12 23l-6.328-4.219A6 6 0 0 1 3 13.79V3.802a1 1 0 0 1 .783-.976M5 4.604v9.185a4 4 0 0 0 1.781 3.328L12 20.597l5.219-3.48A4 4 0 0 0 19 13.79V4.604L12 3.05zM12 11a2.5 2.5 0 1 1 0-5a2.5 2.5 0 0 1 0 5m-4.473 5a4.5 4.5 0 0 1 8.946 0z" />
                        </svg>
                    </span>
                </div>
                <div>
                    <h4 class="fw-bold mb-0">Users</h4>
                    <small class="text-muted">Kelola Pengguna</small>
                </div>
            </div>
        </div>
        @if(Auth::user()->role_id != 2)
        <div class="col-12 col-sm-auto">
            <a href="{{ route('users.create') }}" class="btn btn-primary w-100 w-sm-auto">
                <i class="ri-add-line me-1"></i>
                <span>Buat User Baru</span>
            </a>
        </div>
        @endif
    </div>

    {{-- ── Table Card ───────────────────────────────────────── --}}
    <div class="card">
        <div class="card-datatable table-responsive">
            <table
                data-url="{{ route('users.index') }}"
                data-csrf="{{ csrf_token() }}"
                class="table table-hover align-middle"
                id="table-users">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:50px;">No</th>
                        <th class="text-center">Nama Pengguna</th>
                        <th class="text-center">Email</th>
                        <th class="text-center">Role</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Terakhir Login</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>
@endsection