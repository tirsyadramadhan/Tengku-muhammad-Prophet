@extends('layouts/contentNavbarLayout')

@section('title', 'Purchase Orders')

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
    <div class="row mb-4 g-3">
        <div class="card shadow-sm" style="max-width: fit-content;">
            <div class="card-body py-3">
                <div class="row g-3 align-items-end">
                    <h5 class="text-muted fw-semibold mb-0">
                        <i class="bx bx-briefcase me-1"></i> Filter PO delivered & invoiced berdasarkan tanggal
                    </h5>
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <label for="filter-start-date" class="form-label fw-semibold mb-1">Tanggal Mulai</label>
                        <input type="date" id="filter-start-date" class="form-control" max="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <label for="filter-end-date" class="form-label fw-semibold mb-1">Tanggal Selesai</label>
                        <input type="date" id="filter-end-date" class="form-control" max="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-sm-12 col-md-4 col-lg-3 d-flex gap-2">
                        <button id="btn-filter-apply" class="btn btn-primary fw-semibold px-4">
                            <i class="bx bx-search me-1"></i> Tampilkan
                        </button>
                        <button id="btn-filter-reset" class="btn btn-outline-secondary fw-semibold px-3">
                            Reset
                        </button>
                    </div>
                    <div class="col-12" id="active-filter-badge-wrap" style="display:none;">
                        <span class="badge bg-label-primary" id="active-filter-badge"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3 g-4" id="filtered-stats-section" style="display:none;">
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #696cff;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="f-total-po">—</h4>
                            <small class="text-muted">Jumlah PO</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(105,108,255,0.12);">
                            <i class="bx bx-file fs-4" style="color:#696cff;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #03c3ec;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="f-total-nilai-po">—</h4>
                            <small class="text-muted">Total Nilai PO</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(3,195,236,0.12);">
                            <i class="bx bx-dollar-circle fs-4" style="color:#03c3ec;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #ffab00;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="f-total-modal">—</h4>
                            <small class="text-muted">Total Modal (50%)</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(255,171,0,0.12);">
                            <i class="bx bx-coin-stack fs-4" style="color:#ffab00;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="f-total-margin">—</h4>
                            <small class="text-muted">Total Margin</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(113,221,55,0.12);">
                            <i class="bx bx-trending-up fs-4" style="color:#71dd37;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3 g-4 mt-4" id="filtered-invoice-section" style="display:none;">
        <div class="col-12">
            <h5 class="text-muted fw-semibold mb-0">
                <i class="bx bx-receipt me-1"></i> Invoice
            </h5>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #696cff;">
                <div class="card-body text-center">
                    <h4 class="mb-0 fw-bold" id="f-total-invoice">—</h4>
                    <small class="text-muted">Total Invoice</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #ff3e1d;">
                <div class="card-body text-center">
                    <h4 class="mb-0 fw-bold text-danger" id="f-invoice-unpaid">—</h4>
                    <small class="text-muted">Belum Dibayar</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body text-center">
                    <h4 class="mb-0 fw-bold text-success" id="f-invoice-paid">—</h4>
                    <small class="text-muted">Lunas</small>
                </div>
            </div>
        </div>

    </div>

    <div class="row mb-5 mt-4" id="filtered-breakdown-section" style="display:none;">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Deskripsi Status</th>
                                <th class="text-center" style="width:120px;">Jumlah PO</th>
                            </tr>
                        </thead>
                        <tbody id="status-breakdown-tbody">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    Pilih rentang tanggal lalu klik <strong>Tampilkan</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    {{-- ── Main Table Card ────────────────────────────── --}}
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="50px" height="50px" viewBox="0 0 24 24">
                    <path fill="#2cc9c9" d="M12.005 2a6 6 0 0 1 6 6v1h4v2h-1.167l-.757 9.083a1 1 0 0 1-.996.917H4.925a1 1 0 0 1-.997-.917L3.171 11H2.005V9h4V8a6 6 0 0 1 6-6m6.826 9H5.178l.667 8h12.319zm-5.826 2v4h-2v-4zm-4 0v4h-2v-4zm8 0v4h-2v-4zm-5-9A4 4 0 0 0 8.01 7.8l-.005.2v1h8V8a4 4 0 0 0-3.8-3.995z" />
                </svg>
                <div class="d-flex flex-column ms-2">
                    <h4 class="fw-bold mb-0">Purchase Orders</h4>
                    <small class="text-muted">Kelola PO</small>
                </div>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <button class="btn btn-danger btn-sm px-3" id="truncate-po">
                    <i class="ri-delete-bin-5-line me-1"></i> Hapus Seluruh PO
                </button>
                <a href="{{ route('po.create') }}" class="btn btn-primary btn-sm px-3">
                    <i class="ri-add-line me-1"></i> Buat PO Baru
                </a>
                <a href="{{ route('po.export') }}" class="btn btn-success">
                    <i class="ri ri-file-excel-line"></i> Export Excel
                </a>
                <a href="{{ route('purchase-orders.importForm') }}" class="btn btn-success">
                    <i class="ri ri-file-excel-line"></i> Import Excel
                </a>
            </div>
        </div>

        <table
            data-url="{{ route('po.index') }}"
            data-csrf="{{ csrf_token() }}"
            class="table table-hover align-middle mb-0 text-nowrap" id="table-po">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th class="text-center">Detail PO</th>
                    <th class="text-center">Delivery / Invoice / Payment</th>
                    <th class="text-center">Detail Harga PO</th>
                    <th class="text-center">Detail Margin PO</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@include('truncate-alert')
@endsection