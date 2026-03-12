@extends('layouts/contentNavbarLayout')

@section('title', 'Incoming Purchase Orders')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" id="po-main-container">
    {{-- ── Stat Cards ────────────────────────────────── --}}
    <div class="row mb-4 g-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #696cff;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="card-incoming">0</h4>
                            <small class="text-muted">Total PO</small>
                        </div>
                        <div class="avatar bg-label-primary p-2 rounded">
                            <i class="ri-shopping-basket-line fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="card-price">0</h4>
                            <small class="text-muted">Total Harga</small>
                        </div>
                        <div class="avatar bg-label-success p-2 rounded">
                            <i class="ri-money-dollar-circle-line fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #03c3ec;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="card-capital">0</h4>
                            <small class="text-muted">Total Modal</small>
                        </div>
                        <div class="avatar bg-label-info p-2 rounded">
                            <i class="ri-bank-line fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold text-success" id="card-margin">0</h4>
                            <small class="text-muted">Total Margin</small>
                        </div>
                        <div class="avatar bg-label-success p-2 rounded">
                            <i class="ri-percent-line fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="50px" height="50px" viewBox="0 0 24 24">
                    <path fill="#9627dc" d="M4.005 16V4h-2V2h3a1 1 0 0 1 1 1v12h12.438l2-8H8.005V5h13.72a1 1 0 0 1 .97 1.243l-2.5 10a1 1 0 0 1-.97.757H5.004a1 1 0 0 1-1-1m2 7a2 2 0 1 1 0-4a2 2 0 0 1 0 4m12 0a2 2 0 1 1 0-4a2 2 0 0 1 0 4" />
                </svg>
                <div class="d-flex flex-column ms-2">
                    <h4 class="fw-bold mb-0">Incoming Purchase Orders</h4>
                    <small class="text-muted">Kelola PO yang akan datang</small>
                </div>
            </div>
            @if (Auth::user()->role_id != 2)
            <a href="{{ route('incoming-po.create') }}" class="btn btn-primary btn-sm px-3">
                <i class="ri-add-line me-1"></i> Buat Incoming PO
            </a>
            @endif
        </div>

        <div class="table-responsive" style="scroll-behavior: smooth;">
            <table
                data-url="{{ route('incomingPo') }}"
                data-csrf="{{ csrf_token() }}"
                class="table table-hover align-middle mb-0 table-responsive text-nowrap" id="table-incoming">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th class="text-center">Detail PO</th>
                        <th class="text-center">Detail Harga PO</th>
                        <th class="text-center">Detail Margin PO</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection