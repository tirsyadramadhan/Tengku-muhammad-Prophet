@extends('layouts/contentNavbarLayout')

@section('title', 'Pelacakan Logistik & Pengiriman')

@section('content')
<div id="main-container-index" class="container-fluid px-3 px-md-4 py-4">

    {{-- ── Stat Cards ────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 p-2 bg-primary bg-opacity-10 flex-shrink-0">
                        <i class="ri-truck-line fs-4 text-primary"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h5 class="mb-0 fw-bold text-truncate">{{ $stats['total'] }}</h5>
                        <small class="text-muted">Total Pengiriman</small>
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
                    <div class="rounded-3 p-2 bg-warning bg-opacity-10 flex-shrink-0">
                        <i class="ri-ship-2-line fs-4 text-warning"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h5 class="mb-0 fw-bold text-truncate">{{ $stats['transit'] }}</h5>
                        <small class="text-muted">Dalam Perjalanan</small>
                    </div>
                </div>
                <div class="card-footer p-0 border-0">
                    <div class="bg-warning rounded-bottom" style="height:3px;"></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 p-2 bg-success bg-opacity-10 flex-shrink-0">
                        <i class="ri-map-pin-2-fill fs-4 text-success"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h5 class="mb-0 fw-bold text-truncate">{{ $stats['delivered'] }}</h5>
                        <small class="text-muted">Sudah Tiba</small>
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
                        <i class="ri-dropbox-line fs-4 text-info"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h5 class="mb-0 fw-bold text-truncate">
                            {{ number_format($stats['inventory']) }}
                            <small class="text-muted fw-normal" style="font-size:0.75rem;">Unit</small>
                        </h5>
                        <small class="text-muted">Total Barang Dikirim</small>
                    </div>
                </div>
                <div class="card-footer p-0 border-0">
                    <div class="bg-info rounded-bottom" style="height:3px;"></div>
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
                        <path fill="#c99d2c" d="M8.965 18a3.5 3.5 0 0 1-6.93 0H1V6a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2h3l3 4.056V18h-2.035a3.501 3.501 0 0 1-6.93 0zM15 7H3v8.05a3.5 3.5 0 0 1 5.663.95h5.674c.168-.353.393-.674.663-.95zm2 6h4v-.285L18.992 10H17zm.5 6a1.5 1.5 0 1 0 0-3.001a1.5 1.5 0 0 0 0 3.001M7 17.5a1.5 1.5 0 1 0-3 0a1.5 1.5 0 0 0 3 0" />
                    </svg>
                    <div>
                        <h5 class="fw-bold mb-0">Deliveries</h5>
                        <small class="text-muted">Kelola delivery</small>
                    </div>
                </div>

                {{-- Action Buttons --}}
                @if (Auth::user()->role_id != 2)
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('delivery.create') }}" class="btn btn-primary btn-sm px-3">
                        <i class="ri-add-line me-1"></i>
                        <span class="d-none d-md-inline">Buat Pengiriman Baru</span>
                        <span class="d-md-none">Buat</span>
                    </a>
                </div>
                @endif

            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table
                data-url="{{ route('delivery.index') }}"
                data-csrf="{{ csrf_token() }}"
                id="delivery-table"
                class="table table-hover align-middle mb-0 text-nowrap"
                style="width:100%;">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:50px;">No</th>
                        <th>No PO</th>
                        <th>Nama Barang</th>
                        <th>No. Delivery</th>
                        <th class="text-center">Qty Terkirim</th>
                        <th>Estimasi Pengiriman</th>
                        <th>Tanggal Dikirim</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width:60px;">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>

    </div>
</div>
@endsection