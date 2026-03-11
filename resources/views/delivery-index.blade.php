@extends('layouts/contentNavbarLayout')

@section('title', 'Pelacakan Logistik & Pengiriman')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" id="delivery-main-container">
    {{-- ── Stat Cards ────────────────────────────────── --}}
    <div class="row mb-4 g-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #696cff;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $stats['total'] }}</h5>
                            <small class="text-muted">Total Pengiriman</small>
                        </div>
                        <div class="avatar bg-label-primary p-2 rounded">
                            <i class="ri-truck-line fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #ffab00;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $stats['transit'] }}</h5>
                            <small class="text-muted">Dalam Perjalanan</small>
                        </div>
                        <div class="avatar bg-label-warning p-2 rounded">
                            <i class="ri-ship-2-line fs-3"></i>
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
                            <h5 class="mb-0 fw-bold">{{ $stats['delivered'] }}</h5>
                            <small class="text-muted">Sudah Tiba</small>
                        </div>
                        <div class="avatar bg-label-success p-2 rounded">
                            <i class="ri-map-pin-2-fill fs-3"></i>
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
                            <h5 class="mb-0 fw-bold">{{ number_format($stats['inventory']) }} <small class="text-muted fs-6 fw-normal">Unit</small></h5>
                            <small class="text-muted">Total Barang Dikirim</small>
                        </div>
                        <div class="avatar bg-label-info p-2 rounded">
                            <i class="ri-dropbox-line fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Main Table Card ────────────────────────────── --}}
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom py-3 bg-white">
            <div class="d-flex align-items-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="50px" height="50px" viewBox="0 0 24 24">
                    <path fill="#c99d2c" d="M8.965 18a3.5 3.5 0 0 1-6.93 0H1V6a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2h3l3 4.056V18h-2.035a3.501 3.501 0 0 1-6.93 0zM15 7H3v8.05a3.5 3.5 0 0 1 5.663.95h5.674c.168-.353.393-.674.663-.95zm2 6h4v-.285L18.992 10H17zm.5 6a1.5 1.5 0 1 0 0-3.001a1.5 1.5 0 0 0 0 3.001M7 17.5a1.5 1.5 0 1 0-3 0a1.5 1.5 0 0 0 3 0" />
                </svg>
                <div class="d-flex flex-column ms-2">
                    <h4 class="fw-bold mb-0">Deliveries</h4>
                    <small class="text-muted">Kelola delivery</small>
                </div>
            </div>
            <a href="{{ route('delivery.create') }}" class="btn btn-primary btn-sm px-3">
                <i class="ri-add-line me-1"></i> Buat Pengiriman Baru
            </a>
        </div>

        <div class="table-responsive" style="scroll-behavior: smooth;">
            <table
                data-url="{{ route('delivery.index') }}"
                data-csrf="{{ csrf_token() }}"
                id="delivery-table" class="table table-hover align-middle mb-0" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Detail Delivery</th>
                        <th class="text-center">Detail PO</th>
                        <th class="text-center">Estimasi Tiba</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection