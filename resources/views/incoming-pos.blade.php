@extends('layouts/contentNavbarLayout')

@section('title', 'Incoming Purchase Orders')

@section('content')
<div id="main-container-index" class="container-fluid px-3 px-md-4 py-4">

    {{-- ── Stat Cards ────────────────────────────── --}}
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

    {{-- ── Main Table Card ────────────────────────────── --}}
    <div class="card border-0 shadow-sm">

        {{-- Card Header --}}
        <div class="card-header bg-transparent border-bottom py-3 px-3 px-md-4">
            <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">

                {{-- Title --}}
                <div class="d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40px" height="40px" viewBox="0 0 24 24" class="flex-shrink-0">
                        <path fill="#9627dc" d="M4.005 16V4h-2V2h3a1 1 0 0 1 1 1v12h12.438l2-8H8.005V5h13.72a1 1 0 0 1 .97 1.243l-2.5 10a1 1 0 0 1-.97.757H5.004a1 1 0 0 1-1-1m2 7a2 2 0 1 1 0-4a2 2 0 0 1 0 4m12 0a2 2 0 1 1 0-4a2 2 0 0 1 0 4" />
                    </svg>
                    <div>
                        <h5 class="fw-bold mb-0">Incoming Purchase Orders</h5>
                        <small class="text-muted">Kelola PO yang akan datang</small>
                    </div>
                </div>

                {{-- Action Buttons --}}
                @if (Auth::user()->role_id != 2)
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('incoming-po.create') }}" class="btn btn-primary btn-sm px-3">
                        <i class="ri-add-line me-1"></i>
                        <span class="d-none d-md-inline">Buat Incoming PO</span>
                        <span class="d-md-none">Buat</span>
                    </a>
                </div>
                @endif

            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table
                data-url="{{ route('incomingPo') }}"
                data-csrf="{{ csrf_token() }}"
                class="table table-hover align-middle mb-0 text-nowrap"
                id="table-incoming"
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
                        <th>Tambahan Margin</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width:60px;">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>

    </div>
</div>
@endsection