@extends('layouts/contentNavbarLayout')

@section('title', 'Customer Management')

@section('content')
<div id="main-container-index" class="container-fluid px-3 px-md-4 py-4">

    {{-- ── Page Header ──────────────────────────────────────── --}}
    <div class="row align-items-center g-3 mb-4">
        <div class="col-12 col-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar avatar-md flex-shrink-0">
                    <span class="avatar-initial rounded bg-label-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24">
                            <path fill="#6f2aba" d="M12 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2m.16 14a6.98 6.98 0 0 0-5.147 2.256A7.97 7.97 0 0 0 12 20a7.97 7.97 0 0 0 5.167-1.892A6.98 6.98 0 0 0 12.16 16M12 4a8 8 0 0 0-6.384 12.821A8.98 8.98 0 0 1 12.16 14a8.97 8.97 0 0 1 6.362 2.634A8 8 0 0 0 12 4m0 1a4 4 0 1 1 0 8a4 4 0 0 1 0-8m0 2a2 2 0 1 0 0 4a2 2 0 0 0 0-4" />
                        </svg>
                    </span>
                </div>
                <div>
                    <h4 class="fw-bold mb-0">Customers</h4>
                    <small class="text-muted">Kelola Customer</small>
                </div>
            </div>
        </div>
        @if(Auth::user()->role_id != 2)
        <div class="col-12 col-sm-auto">
            <a href="{{ route('customers.create') }}" class="btn btn-primary w-100 w-sm-auto">
                <i class="ri-user-add-line me-1"></i>
                <span>Add New</span>
            </a>
        </div>
        @endif
    </div>

    {{-- ── Table Card ───────────────────────────────────────── --}}
    <div class="card">
        <div class="card-datatable table-responsive">
            <table
                data-url="{{ route('customers.index') }}"
                data-role="{{ Auth::user()->role_id }}"
                data-url-delete="{{ route('customers.destroy', ':id') }}"
                class="table table-hover align-middle"
                id="customerTable">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:50px;">No</th>
                        <th>Detail Customer</th>
                        <th class="text-center">Tanggal Daftar</th>
                        @if(Auth::user()->role_id != 2)
                        <th class="text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

</div>
@endsection