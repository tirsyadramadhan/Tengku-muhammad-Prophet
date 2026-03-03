@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard')

@section('vendor-style')
<style>
    .card-header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        background-color: #fff;
    }

    .table-container {
        padding: 0;
    }

    .col-money {
        text-align: right;
        font-family: 'Public Sans', sans-serif;
        font-weight: 600;
        white-space: nowrap;
        min-width: 140px;
    }

    .col-id {
        width: 80px;
        text-align: center;
        color: #8898aa;
    }

    .col-po {
        min-width: 250px;
    }

    .bg-soft-primary {
        background-color: rgba(105, 108, 255, 0.04);
    }

    .bg-soft-success {
        background-color: rgba(113, 221, 55, 0.04);
    }

    .bg-soft-warning {
        background-color: rgba(255, 171, 0, 0.04);
    }

    .table tfoot tr {
        background-color: #f8f9fa;
        border-top: 2px solid #e1e4e8;
        font-weight: 700;
        color: #566a7f;
    }

    .table tfoot td {
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    .po-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        margin: 2px;
        border-radius: 4px;
        background-color: #e7e7ff;
        color: #696cff;
        border: 1px solid rgba(105, 108, 255, 0.2);
        display: inline-block;
    }

    .po-badge-empty {
        background-color: #ffe0db;
        color: #ff3e1d;
        border-color: #ff3e1d;
    }

    .stat-card {
        border: none;
        border-radius: 12px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 5px solid transparent;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    {{-- DATE FILTER CARD --}}
    <div class="row mb-4 g-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body py-3">
                    <div class="row g-3 align-items-end">
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
    </div>

    {{-- PO FINANCIALS (hidden until filter applied) --}}
    <div class="row mb-3 g-4" id="filtered-stats-section" style="display:none;">
        <div class="col-12">
            <h5 class="text-muted fw-semibold mb-0">
                <i class="bx bx-briefcase me-1"></i> Filter PO Khusus Delivered & Invoiced (Belum Dibayar)
            </h5>
        </div>
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

    {{-- INVOICE STATUS COUNTS (hidden until filter applied) --}}
    <div class="row mb-3 g-4" id="filtered-invoice-section" style="display:none;">
        <div class="col-12">
            <h5 class="text-muted fw-semibold mb-0">
                <i class="bx bx-receipt me-1"></i> Detail Invoice Setiap Delivery
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

    {{-- PO STATUS BREAKDOWN TABLE (hidden until filter applied) --}}
    <div class="row mb-5" id="filtered-breakdown-section" style="display:none;">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="bx bx-pie-chart-alt-2 me-1"></i> Ringkasan Tiap Status PO
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:80px;">Kode</th>
                                <th>Deskripsi Status</th>
                                <th class="text-center" style="width:120px;">Jumlah PO</th>
                                <th style="width:220px;">Persentase</th>
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

    {{-- ================================================================
         SECTION 2: REKENING
         ================================================================ --}}
    <div class="row mb-4 g-4">
        <h3>Rekening</h3>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xxl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="dana-tersedia">0</h4>
                            <small class="text-muted">Dana Tersedia</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102,16,242,0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#893600" d="M22.005 6h-7a6 6 0 0 0 0 12h7v2a1 1 0 0 1-1 1h-18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h18a1 1 0 0 1 1 1zm-7 2h8v8h-8a4 4 0 1 1 0-8m0 3v2h3v-2z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xxl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="investasi-dikembalikan">0</h4>
                            <small class="text-muted">Investasi Dikembalikan</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102,16,242,0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#E1B530" d="M9.335 11.502h2.17a4.5 4.5 0 0 1 4.5 4.5H9.004v1h8v-1a5.6 5.6 0 0 0-.885-3h2.886a5 5 0 0 1 4.516 2.852c-2.365 3.12-6.194 5.149-10.516 5.149c-2.761 0-5.1-.59-7-1.625v-9.304a6.97 6.97 0 0 1 3.33 1.428m-4.33 7.5a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-9a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1zm13-14a3 3 0 1 1 0 6a3 3 0 0 1 0-6m-7-3a3 3 0 1 1 0 6a3 3 0 0 1 0-6" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xxl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="totalInvestasiTransfer">0</h4>
                            <small class="text-muted">Total Investasi Transfer</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102,16,242,0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#E1B530" d="M14.005 2.003a8 8 0 0 1 3.292 15.293A8 8 0 1 1 6.711 6.71a8 8 0 0 1 7.294-4.707m-3 7h-2v1a2.5 2.5 0 0 0-.164 4.995l.164.005h2l.09.008a.5.5 0 0 1 0 .984l-.09.008h-4v2h2v1h2v-1a2.5 2.5 0 0 0 .164-4.995l-.164-.005h-2l-.09-.008a.5.5 0 0 1 0-.984l.09-.008h4v-2h-2zm3-5A6 6 0 0 0 9.52 6.016a8 8 0 0 1 8.47 8.471a6 6 0 0 0-3.986-10.484" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================
         SECTION 3: PEMASUKAN
         ================================================================ --}}
    <div class="row mb-4 g-4">
        <h3>Pemasukan</h3>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xxl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="total-tf-investasi">0</h4>
                            <small class="text-muted">Total TF Investasi</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102,16,242,0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#20d420" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m0-2a8 8 0 1 0 0-16a8 8 0 0 0 0 16m-3.5-6h5.5a.5.5 0 1 0 0-1h-4a2.5 2.5 0 1 1 0-5h1v-2h2v2h2.5v2h-5.5a.5.5 0 0 0 0 1h4a2.5 2.5 0 0 1 0 5h-1v2h-2v-2h-2.5z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xxl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="margin-diterima">0</h4>
                            <small class="text-muted">Margin Diterima</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102,16,242,0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#20d420" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m0-2a8 8 0 1 0 0-16a8 8 0 0 0 0 16m-3.5-6h5.5a.5.5 0 1 0 0-1h-4a2.5 2.5 0 1 1 0-5h1v-2h2v2h2.5v2h-5.5a.5.5 0 0 0 0 1h4a2.5 2.5 0 0 1 0 5h-1v2h-2v-2h-2.5z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xxl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="total-margin">0</h4>
                            <small class="text-muted">Total Margin</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102,16,242,0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#20d420" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m0-2a8 8 0 1 0 0-16a8 8 0 0 0 0 16m-3.5-6h5.5a.5.5 0 1 0 0-1h-4a2.5 2.5 0 1 1 0-5h1v-2h2v2h2.5v2h-5.5a.5.5 0 0 0 0 1h4a2.5 2.5 0 0 1 0 5h-1v2h-2v-2h-2.5z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xxl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="sisa-margin">0</h4>
                            <small class="text-muted">Sisa Margin</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102,16,242,0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#20d420" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m0-2a8 8 0 1 0 0-16a8 8 0 0 0 0 16m-3.5-6h5.5a.5.5 0 1 0 0-1h-4a2.5 2.5 0 1 1 0-5h1v-2h2v2h2.5v2h-5.5a.5.5 0 0 0 0 1h4a2.5 2.5 0 0 1 0 5h-1v2h-2v-2h-2.5z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xxl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="margin-tersedia">0</h4>
                            <small class="text-muted">Margin Tersedia</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102,16,242,0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#20d420" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m0-2a8 8 0 1 0 0-16a8 8 0 0 0 0 16m-3.5-6h5.5a.5.5 0 1 0 0-1h-4a2.5 2.5 0 1 1 0-5h1v-2h2v2h2.5v2h-5.5a.5.5 0 0 0 0 1h4a2.5 2.5 0 0 1 0 5h-1v2h-2v-2h-2.5z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================
         SECTION 4: DITAHAN
         ================================================================ --}}
    <div class="row mb-4 g-4">
        <h3>Ditahan</h3>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xxl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="investasi-ditahan">0</h4>
                            <small class="text-muted">Investasi Yang Ditahan</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102,16,242,0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#0c0cff" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m0-2a8 8 0 1 0 0-16a8 8 0 0 0 0 16m-3.5-6h5.5a.5.5 0 1 0 0-1h-4a2.5 2.5 0 1 1 0-5h1v-2h2v2h2.5v2h-5.5a.5.5 0 0 0 0 1h4a2.5 2.5 0 0 1 0 5h-1v2h-2v-2h-2.5z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xxl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="margin-ditahan">0</h4>
                            <small class="text-muted">Margin Ditahan</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102,16,242,0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#0c0cff" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m0-2a8 8 0 1 0 0-16a8 8 0 0 0 0 16m-3.5-6h5.5a.5.5 0 1 0 0-1h-4a2.5 2.5 0 1 1 0-5h1v-2h2v2h2.5v2h-5.5a.5.5 0 0 0 0 1h4a2.5 2.5 0 0 1 0 5h-1v2h-2v-2h-2.5z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>{{-- END container-xxl --}}
@endsection

@section('page-script')
<script>
    document.addEventListener("DOMContentLoaded", () => {

        // ================================================================
        // HELPERS — defined FIRST so everything below can use them
        // ================================================================
        const rupiah = (val) =>
            'Rp ' + Number(val).toLocaleString('id-ID', {
                maximumFractionDigits: 0
            });

        const setText = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val;
        };

        const showRow = (id) => {
            const el = document.getElementById(id);
            if (el) el.style.display = ''; // removes inline display:none, lets Bootstrap flex take over
        };

        const hideRow = (id) => {
            const el = document.getElementById(id);
            if (el) el.style.display = 'none';
        };

        // ================================================================
        // STATUS CONFIG
        // ================================================================
        const statusConfig = {
            2: {
                label: 'Sebagian di Deliver',
            },
            3: {
                label: 'Sepenuhnya di Deliver',
            },
            4: {
                label: 'Sebagian di Deliver dan sebagian di Invoice',
            },
            5: {
                label: 'Sepenuhnya di Deliver dan sebagian di Invoice',
            },
            6: {
                label: 'Sebagian di Deliver dan sepenuhnya di Invoice',
            },
            7: {
                label: 'Semuanya di deliver dan semuanya di Invoice (Menunggu Pembayaran)',
            },
        };

        // ================================================================
        // RENDER STATUS BREAKDOWN TABLE
        // ================================================================
        function renderBreakdown(breakdown) {
            const tbody = document.getElementById('status-breakdown-tbody');
            if (!tbody) return;

            const maxCount = Math.max(1, ...breakdown.map(r => r.count));
            tbody.innerHTML = '';

            breakdown.forEach(row => {
                const cfg = statusConfig[row.status] || {
                    label: row.label,
                    color: 'secondary'
                };
                const pct = Math.round((row.count / maxCount) * 100);
                const isEmpty = row.count === 0;

                tbody.insertAdjacentHTML('beforeend', `
                <tr class="${isEmpty ? 'opacity-50' : ''}">
                    <td class="text-center">
                        <span class="badge bg-label-${cfg.color} px-2">${row.status}</span>
                    </td>
                    <td class="fw-semibold">${cfg.label}</td>
                    <td class="text-center fw-bold ${isEmpty ? 'text-muted' : ''}">${row.count}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:8px;">
                                <div class="progress-bar bg-${cfg.color}" role="progressbar"
                                     style="width:${pct}%" aria-valuenow="${pct}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                            <small class="text-muted" style="min-width:36px;">${pct}%</small>
                        </div>
                    </td>
                </tr>
            `);
            });
        }

        // ================================================================
        // FILTERED STATS FETCH
        // ================================================================
        function fetchFilteredStats() {
            const startDate = document.getElementById('filter-start-date')?.value;
            const endDate = document.getElementById('filter-end-date')?.value;

            const params = new URLSearchParams();
            if (startDate) params.append('startDate', startDate);
            if (endDate) params.append('endDate', endDate);

            // Loading state
            ['f-total-po', 'f-total-nilai-po', 'f-total-modal', 'f-total-margin',
                'f-total-invoice', 'f-invoice-unpaid', 'f-invoice-paid'
            ]
            .forEach(id => setText(id, '…'));

            // Show sections immediately (with loading dots)
            ['filtered-stats-section', 'filtered-invoice-section', 'filtered-breakdown-section']
            .forEach(showRow);

            $.getJSON(`/api/dashboard-filtered-stats?${params.toString()}`)
                .done(data => {
                    setText('f-total-po', data.totalPo.toLocaleString('id-ID'));
                    setText('f-total-nilai-po', rupiah(data.totalNilaiPo));
                    setText('f-total-modal', rupiah(data.totalModal));
                    setText('f-total-margin', rupiah(data.totalMargin));

                    setText('f-total-invoice', data.totalInvoice);
                    setText('f-invoice-unpaid', data.invoiceUnpaid);
                    setText('f-invoice-paid', data.invoicePaid);

                    renderBreakdown(data.statusBreakdown);

                    // Active filter badge
                    const badge = document.getElementById('active-filter-badge');
                    const wrap = document.getElementById('active-filter-badge-wrap');
                    if (badge && wrap) {
                        const from = data.filter.startDate || 'Awal';
                        const to = data.filter.endDate || 'Hari ini';
                        badge.textContent = `Filter aktif: ${from} → ${to}`;
                        wrap.style.display = 'block';
                    }
                })
                .fail(err => {
                    console.error('Filtered stats fetch failed:', err);
                    alert('Gagal mengambil data filter. Cek console untuk detail.');
                });
        }

        // Button handlers
        document.getElementById('btn-filter-apply')
            ?.addEventListener('click', fetchFilteredStats);

        document.getElementById('btn-filter-reset')
            ?.addEventListener('click', () => {
                document.getElementById('filter-start-date').value = '';
                document.getElementById('filter-end-date').value = '';
                ['filtered-stats-section', 'filtered-invoice-section', 'filtered-breakdown-section']
                .forEach(hideRow);
                document.getElementById('active-filter-badge-wrap').style.display = 'none';
            });

        ['filter-start-date', 'filter-end-date'].forEach(id => {
            document.getElementById(id)
                ?.addEventListener('keydown', e => {
                    if (e.key === 'Enter') fetchFilteredStats();
                });
        });

        // ================================================================
        // GLOBAL STATS (Rekening / Pemasukan / Ditahan)
        // ================================================================
        const numberOptions = {
            startVal: 0,
            duration: 3
        };

        const statsMap = [
            'dana-tersedia',
            'total-dana-ditf',
            'investasi-dikembalikan',
            'total-tf-investasi',
            'margin-diterima',
            'total-margin',
            'sisa-margin',
            'margin-tersedia',
            'investasi-ditahan',
            'margin-ditahan',
            'totalInvestasiTransfer',
        ];

        function updateCardStats() {
            $.getJSON('/api/dashboard-stats')
                .done(data => {
                    statsMap.forEach(id => {
                        const dataKey = id.replace(/-([a-z])/g, g => g[1].toUpperCase());
                        if (data[dataKey] !== undefined) {
                            new CountUp(id, data[dataKey], numberOptions).start();
                        }
                    });
                })
                .fail(err => console.error('Failed to fetch dashboard stats:', err));
        }

        updateCardStats();

        if (typeof Echo !== 'undefined') {
            Echo.channel('global-updates')
                .listen('CrudActionOccurred', (e) => {
                    console.log('Real-time update triggered:', e.message);
                    updateCardStats();
                });
        }

    });
</script>
@endsection