@extends('layouts/contentNavbarLayout')

@section('title', 'Purchase Orders')

@section('content')
<div id="main-container-index" class="container-fluid px-3 px-md-4 py-4">

    {{-- ── Stat Cards Row 1 ────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 p-2 bg-primary bg-opacity-10 flex-shrink-0">
                        <i class="ri-shopping-basket-line fs-4 text-primary"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h5 class="mb-0 fw-bold text-truncate" id="card-incoming">0</h5>
                        <small class="text-muted">Total PO</small>
                    </div>
                </div>
                <div class="card-footer p-0 border-0">
                    <div class="bg-primary rounded-bottom" style="height:3px;"></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 p-2 bg-success bg-opacity-10 flex-shrink-0">
                        <i class="ri-money-dollar-circle-line fs-4 text-success"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h5 class="mb-0 fw-bold text-truncate" id="card-price">0</h5>
                        <small class="text-muted">Total Harga</small>
                    </div>
                </div>
                <div class="card-footer p-0 border-0">
                    <div class="bg-success rounded-bottom" style="height:3px;"></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 p-2 bg-info bg-opacity-10 flex-shrink-0">
                        <i class="ri-bank-line fs-4 text-info"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h5 class="mb-0 fw-bold text-truncate" id="card-capital">0</h5>
                        <small class="text-muted">Total Modal</small>
                    </div>
                </div>
                <div class="card-footer p-0 border-0">
                    <div class="bg-info rounded-bottom" style="height:3px;"></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 p-2 bg-success bg-opacity-10 flex-shrink-0">
                        <i class="ri-percent-line fs-4 text-success"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h5 class="mb-0 fw-bold text-success text-truncate" id="card-margin">0</h5>
                        <small class="text-muted">Total Margin</small>
                    </div>
                </div>
                <div class="card-footer p-0 border-0">
                    <div class="bg-success rounded-bottom" style="height:3px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Date Filter Card ────────────────────────────── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3 p-md-4">
            <h6 class="fw-semibold text-muted mb-3">
                <i class="bx bx-briefcase me-1"></i>
                Filter PO delivered &amp; invoiced berdasarkan tanggal
            </h6>
            <div class="row g-2 align-items-end">
                <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                    <label for="filter-start-date" class="form-label fw-semibold mb-1 small">Tanggal Mulai</label>
                    <input type="date" id="filter-start-date" class="form-control form-control-sm" max="{{ date('Y-m-d') }}">
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                    <label for="filter-end-date" class="form-label fw-semibold mb-1 small">Tanggal Selesai</label>
                    <input type="date" id="filter-end-date" class="form-control form-control-sm" max="{{ date('Y-m-d') }}">
                </div>
                <div class="col-12 col-sm-12 col-md-4 col-xl-3 d-flex gap-2">
                    <button id="btn-filter-apply" class="btn btn-primary btn-sm fw-semibold px-3 flex-grow-1 flex-md-grow-0">
                        <i class="bx bx-search me-1"></i> Tampilkan
                    </button>
                    <button id="btn-filter-reset" class="btn btn-outline-secondary btn-sm fw-semibold px-3">
                        <i class="bx bx-reset me-1"></i> Reset
                    </button>
                </div>
                <div class="col-12" id="active-filter-badge-wrap" style="display:none;">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-semibold" id="active-filter-badge"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Filtered Stats Section ────────────────────────────── --}}
    <div class="row g-3 mb-4" id="filtered-stats-section" style="display:none;">
        <div class="col-12">
            <p class="fw-semibold text-muted small mb-0 text-uppercase letter-spacing-1">
                <i class="bx bx-bar-chart-alt-2 me-1"></i> Statistik Periode
            </p>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 p-2 flex-shrink-0" style="background:rgba(105,108,255,.12);">
                        <i class="bx bx-file fs-4" style="color:#696cff;"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h5 class="mb-0 fw-bold text-truncate" id="f-total-po">—</h5>
                        <small class="text-muted">Jumlah PO</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 p-2 flex-shrink-0" style="background:rgba(3,195,236,.12);">
                        <i class="bx bx-dollar-circle fs-4" style="color:#03c3ec;"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h5 class="mb-0 fw-bold text-truncate" id="f-total-nilai-po">—</h5>
                        <small class="text-muted">Total Nilai PO</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 p-2 flex-shrink-0" style="background:rgba(255,171,0,.12);">
                        <i class="bx bx-coin-stack fs-4" style="color:#ffab00;"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h5 class="mb-0 fw-bold text-truncate" id="f-total-modal">—</h5>
                        <small class="text-muted">Total Modal (50%)</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 p-2 flex-shrink-0" style="background:rgba(113,221,55,.12);">
                        <i class="bx bx-trending-up fs-4" style="color:#71dd37;"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h5 class="mb-0 fw-bold text-truncate" id="f-total-margin">—</h5>
                        <small class="text-muted">Total Margin</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Filtered Invoice Section ────────────────────────────── --}}
    <div class="row g-3 mb-4" id="filtered-invoice-section" style="display:none;">
        <div class="col-12">
            <p class="fw-semibold text-muted small mb-0 text-uppercase">
                <i class="bx bx-receipt me-1"></i> Invoice
            </p>
        </div>
        <div class="col-4 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-3">
                    <h5 class="mb-0 fw-bold" id="f-total-invoice">—</h5>
                    <small class="text-muted">Total Invoice</small>
                </div>
                <div class="card-footer p-0 border-0">
                    <div class="rounded-bottom" style="height:3px;background:#696cff;"></div>
                </div>
            </div>
        </div>
        <div class="col-4 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-3">
                    <h5 class="mb-0 fw-bold text-danger" id="f-invoice-unpaid">—</h5>
                    <small class="text-muted">Belum Dibayar</small>
                </div>
                <div class="card-footer p-0 border-0">
                    <div class="bg-danger rounded-bottom" style="height:3px;"></div>
                </div>
            </div>
        </div>
        <div class="col-4 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-3">
                    <h5 class="mb-0 fw-bold text-success" id="f-invoice-paid">—</h5>
                    <small class="text-muted">Lunas</small>
                </div>
                <div class="card-footer p-0 border-0">
                    <div class="bg-success rounded-bottom" style="height:3px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Status Breakdown Table ────────────────────────────── --}}
    <div class="row mb-4" id="filtered-breakdown-section" style="display:none;">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-semibold mb-0 text-muted">
                        <i class="bx bx-table me-1"></i> Breakdown Status
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-semibold">Deskripsi Status</th>
                                <th class="text-center fw-semibold" style="width:120px;">Jumlah PO</th>
                            </tr>
                        </thead>
                        <tbody id="status-breakdown-tbody">
                            <tr>
                                <td colspan="2" class="text-center text-muted py-4">
                                    <i class="bx bx-calendar-x fs-4 d-block mb-2 opacity-50"></i>
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
    <div class="card border-0 shadow-sm">

        {{-- Card Header --}}
        <div class="card-header bg-transparent border-bottom py-3 px-3 px-md-4">
            <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">

                {{-- Title --}}
                <div class="d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40px" height="40px" viewBox="0 0 24 24" class="flex-shrink-0">
                        <path fill="#2cc9c9" d="M12.005 2a6 6 0 0 1 6 6v1h4v2h-1.167l-.757 9.083a1 1 0 0 1-.996.917H4.925a1 1 0 0 1-.997-.917L3.171 11H2.005V9h4V8a6 6 0 0 1 6-6m6.826 9H5.178l.667 8h12.319zm-5.826 2v4h-2v-4zm-4 0v4h-2v-4zm8 0v4h-2v-4zm-5-9A4 4 0 0 0 8.01 7.8l-.005.2v1h8V8a4 4 0 0 0-3.8-3.995z" />
                    </svg>
                    <div>
                        <h5 class="fw-bold mb-0">Purchase Orders</h5>
                        <small class="text-muted">Kelola PO</small>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex flex-wrap gap-2">
                    @if (Auth::user()->role_id != 2)
                    <button class="btn btn-danger btn-sm px-3" id="truncate-po">
                        <i class="ri-delete-bin-5-line me-1"></i>
                        <span class="d-none d-md-inline">Hapus Seluruh PO</span>
                        <span class="d-md-none">Hapus</span>
                    </button>
                    <a href="{{ route('po.create') }}" class="btn btn-primary btn-sm px-3">
                        <i class="ri-add-line me-1"></i>
                        <span class="d-none d-md-inline">Buat PO Baru</span>
                        <span class="d-md-none">Buat</span>
                    </a>
                    <a href="{{ route('purchase-orders.importForm') }}" class="btn btn-success btn-sm px-3">
                        <i class="ri ri-file-excel-line me-1"></i>
                        <span class="d-none d-md-inline">Import Excel</span>
                        <span class="d-md-none">Import</span>
                    </a>
                    @endif
                    <a href="{{ route('po.export') }}" class="btn btn-outline-success btn-sm px-3">
                        <i class="ri ri-file-excel-line me-1"></i>
                        <span class="d-none d-md-inline">Export Excel</span>
                        <span class="d-md-none">Export</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table
                data-url="{{ route('po.index') }}"
                data-csrf="{{ csrf_token() }}"
                class="table table-hover align-middle mb-0 text-nowrap"
                id="table-po"
                style="width:100%;">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:50px;">No</th>
                        <th>No PO</th>
                        <th>Nama Barang</th>
                        <th>Tanggal PO</th>
                        <th class="text-center">Qty</th>
                        <th>Harga / Unit</th>
                        <th>Total Harga</th>
                        <th>Modal Awal</th>
                        <th>Margin</th>
                        <th>Tambahan</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width:60px;">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>
@include('truncate-alert')
@endsection