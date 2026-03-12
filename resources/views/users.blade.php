@extends('layouts/contentNavbarLayout')

@section('title', 'Users')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">


    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom py-3 bg-white">
            <div class="d-flex align-items-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24">
                    <path fill="#E1B530" d="M3.783 2.826L12 1l8.217 1.826a1 1 0 0 1 .783.976v9.987a6 6 0 0 1-2.672 4.992L12 23l-6.328-4.219A6 6 0 0 1 3 13.79V3.802a1 1 0 0 1 .783-.976M5 4.604v9.185a4 4 0 0 0 1.781 3.328L12 20.597l5.219-3.48A4 4 0 0 0 19 13.79V4.604L12 3.05zM12 11a2.5 2.5 0 1 1 0-5a2.5 2.5 0 0 1 0 5m-4.473 5a4.5 4.5 0 0 1 8.946 0z" />
                </svg>
                <div class="d-flex flex-column ms-2">
                    <h4 class="fw-bold mb-0">Users</h4>
                    <small class="text-muted">Kelola Pengguna</small>
                </div>
            </div>
            @if (Auth::user()->role_id != 2)
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm px-3">
                <i class="ri-add-line me-1"></i> Buat User Baru
            </a>
            @endif
        </div>

        <table
            data-url="{{ route('users.index') }}"
            data-csrf="{{ csrf_token() }}"
            class="table table-hover align-middle mb-0" id="table-users">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th class="text-center">Detail Akun</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection