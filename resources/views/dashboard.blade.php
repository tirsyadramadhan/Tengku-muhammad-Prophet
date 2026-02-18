@extends('layouts/contentNavbarLayout')

@section('title', 'Investment Dashboard')

@section('vendor-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" />
<style>
    /* Dashboard Aesthetics */
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

    /* Financial Column Typography */
    .col-money {
        text-align: right;
        font-family: 'Public Sans', sans-serif;
        font-weight: 600;
        white-space: nowrap;
        min-width: 140px;
    }

    /* Column Specifics */
    .col-id {
        width: 80px;
        text-align: center;
        color: #8898aa;
    }

    .col-po {
        min-width: 250px;
    }

    /* Background Tints */
    .bg-soft-primary {
        background-color: rgba(105, 108, 255, 0.04);
    }

    .bg-soft-success {
        background-color: rgba(113, 221, 55, 0.04);
    }

    .bg-soft-warning {
        background-color: rgba(255, 171, 0, 0.04);
    }

    /* Footer Sum Row */
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

    /* PO Badge Styling */
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

    /* Stat Cards */
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
    <div class="row mb-4 g-4">
        <h3>Rekening</h3>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Dana Tersedia</small>
                            <h4 class="mb-0 fw-bold mt-1" style="color: #000;">
                                Rp {{ number_format($stats['dana_tersedia']) }}
                            </h4>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102, 16, 242, 0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#893600" d="M22.005 6h-7a6 6 0 0 0 0 12h7v2a1 1 0 0 1-1 1h-18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h18a1 1 0 0 1 1 1zm-7 2h8v8h-8a4 4 0 1 1 0-8m0 3v2h3v-2z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #3E436F;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Total Dana Di transfer</small>
                            <h4 class="mb-0 fw-bold mt-1" style="color: #000;">
                                Rp {{ number_format($stats['total_dana_ditransfer']) }}
                            </h4>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102, 16, 242, 0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#3E436F" d="m11.005 2l7.298 2.28a1 1 0 0 1 .702.955V7h2a1 1 0 0 1 1 1v2h-13V8a1 1 0 0 1 1-1h7V5.97l-6-1.876l-6 1.876v7.404a4 4 0 0 0 1.558 3.169l.189.136l4.253 2.9L14.787 17h-4.782a1 1 0 0 1-1-1v-4h13v4a1 1 0 0 1-1 1l-3.22.001c-.387.51-.857.96-1.4 1.33L11.005 22l-5.38-3.668a6 6 0 0 1-2.62-4.958V5.235a1 1 0 0 1 .702-.954z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #3E436F;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Investasi Dikembalikan</small>
                            <h4 class="mb-0 fw-bold mt-1" style="color: #000;">
                                Rp {{ number_format($stats['investasi_dikembalikan']) }}
                            </h4>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102, 16, 242, 0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#3E436F" d="m11.005 2l7.298 2.28a1 1 0 0 1 .702.955V7h2a1 1 0 0 1 1 1v2h-13V8a1 1 0 0 1 1-1h7V5.97l-6-1.876l-6 1.876v7.404a4 4 0 0 0 1.558 3.169l.189.136l4.253 2.9L14.787 17h-4.782a1 1 0 0 1-1-1v-4h13v4a1 1 0 0 1-1 1l-3.22.001c-.387.51-.857.96-1.4 1.33L11.005 22l-5.38-3.668a6 6 0 0 1-2.62-4.958V5.235a1 1 0 0 1 .702-.954z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 g-4">
        <h3>Pemasukan</h3>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Total TF Investasi</small>
                            <h4 class="mb-0 fw-bold mt-1 text-success">Rp {{ number_format($stats['total_transfer_investasi']) }}</h4>
                        </div>
                        <div class="avatar bg-label-success p-2 rounded" style="background-color: rgba(102, 16, 242, 0.1); color: #154734;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#06ff00" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m0-2a8 8 0 1 0 0-16a8 8 0 0 0 0 16m-3.5-6h5.5a.5.5 0 1 0 0-1h-4a2.5 2.5 0 1 1 0-5h1v-2h2v2h2.5v2h-5.5a.5.5 0 0 0 0 1h4a2.5 2.5 0 0 1 0 5h-1v2h-2v-2h-2.5z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Margin Diterima</small>
                            <h4 class="mb-0 fw-bold mt-1 text-success">Rp {{ number_format( $stats['margin_diterima'] ) }}</h4>
                        </div>
                        <div class="avatar bg-label-success p-2 rounded" style="background-color: rgba(102, 16, 242, 0.1); color: #154734;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#06ff00" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m0-2a8 8 0 1 0 0-16a8 8 0 0 0 0 16m-3.5-6h5.5a.5.5 0 1 0 0-1h-4a2.5 2.5 0 1 1 0-5h1v-2h2v2h2.5v2h-5.5a.5.5 0 0 0 0 1h4a2.5 2.5 0 0 1 0 5h-1v2h-2v-2h-2.5z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Total Margin</small>
                            <h4 class="mb-0 fw-bold mt-1 text-success">Rp {{ number_format($stats['total_margin']) }}</h4>
                        </div>
                        <div class="avatar bg-label-success p-2 rounded" style="background-color: rgba(102, 16, 242, 0.1); color: #154734;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#06ff00" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m0-2a8 8 0 1 0 0-16a8 8 0 0 0 0 16m-3.5-6h5.5a.5.5 0 1 0 0-1h-4a2.5 2.5 0 1 1 0-5h1v-2h2v2h2.5v2h-5.5a.5.5 0 0 0 0 1h4a2.5 2.5 0 0 1 0 5h-1v2h-2v-2h-2.5z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Sisa Margin</small>
                            <h4 class="mb-0 fw-bold mt-1 text-success">Rp {{ number_format( $stats['sisa_margin'])}}</h4>
                        </div>
                        <div class="avatar bg-label-success p-2 rounded" style="background-color: rgba(102, 16, 242, 0.1); color: #154734;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#06ff00" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m0-2a8 8 0 1 0 0-16a8 8 0 0 0 0 16m-3.5-6h5.5a.5.5 0 1 0 0-1h-4a2.5 2.5 0 1 1 0-5h1v-2h2v2h2.5v2h-5.5a.5.5 0 0 0 0 1h4a2.5 2.5 0 0 1 0 5h-1v2h-2v-2h-2.5z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Margin Tersedia</small>
                            <h4 class="mb-0 fw-bold mt-1" style="color: #71dd37;">Rp {{ number_format( $stats['margin_tersedia']) }}</h4>
                        </div>
                        <div class="avatar bg-label-success p-2 rounded" style="background-color: rgba(102, 16, 242, 0.1); color: #154734;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#06ff00" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m0-2a8 8 0 1 0 0-16a8 8 0 0 0 0 16m-3.5-6h5.5a.5.5 0 1 0 0-1h-4a2.5 2.5 0 1 1 0-5h1v-2h2v2h2.5v2h-5.5a.5.5 0 0 0 0 1h4a2.5 2.5 0 0 1 0 5h-1v2h-2v-2h-2.5z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 g-4">
        <h3>Ditahan</h3>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #6610f2;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Investasi Yang Ditahan</small>
                            <h4 class="mb-0 fw-bold mt-1" style="color: #6610f2;">Rp {{ number_format($stats['investasi_ditahan']) }}</h4>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102, 16, 242, 0.1); color: #6610f2;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#6610f2" d="M6 4H4V2h16v2h-2v2c0 1.615-.816 2.915-1.844 3.977c-.703.726-1.558 1.395-2.425 2.023c.867.628 1.722 1.297 2.425 2.023C17.184 15.085 18 16.385 18 18v2h2v2H4v-2h2v-2c0-1.615.816-2.915 1.844-3.977c.703-.726 1.558-1.395 2.425-2.023c-.867-.628-1.722-1.297-2.425-2.023C6.816 8.915 6 7.615 6 6zm2 0v2c0 .685.26 1.335.771 2h6.458c.51-.665.771-1.315.771-2V4zm4 9.222c-1.045.738-1.992 1.441-2.719 2.192a7 7 0 0 0-.51.586h6.458a7 7 0 0 0-.51-.586c-.727-.751-1.674-1.454-2.719-2.192" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #6610f2;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Margin Ditahan</small>
                            <h4 class="mb-0 fw-bold mt-1" style="color: #6610f2;">Rp {{ number_format($stats['margin_ditahan']) }}</h4>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102, 16, 242, 0.1); color: #6610f2;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#6610f2" d="M6 4H4V2h16v2h-2v2c0 1.615-.816 2.915-1.844 3.977c-.703.726-1.558 1.395-2.425 2.023c.867.628 1.722 1.297 2.425 2.023C17.184 15.085 18 16.385 18 18v2h2v2H4v-2h2v-2c0-1.615.816-2.915 1.844-3.977c.703-.726 1.558-1.395 2.425-2.023c-.867-.628-1.722-1.297-2.425-2.023C6.816 8.915 6 7.615 6 6zm2 0v2c0 .685.26 1.335.771 2h6.458c.51-.665.771-1.315.771-2V4zm4 9.222c-1.045.738-1.992 1.441-2.719 2.192a7 7 0 0 0-.51.586h6.458a7 7 0 0 0-.51-.586c-.727-.751-1.674-1.454-2.719-2.192" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>
@endsection