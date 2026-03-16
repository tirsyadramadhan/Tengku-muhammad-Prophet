@extends('layouts/contentNavbarLayout')

@section('title', 'Manajemen Invoice')

@section('content')
<div id="main-container-index" class="container-fluid px-3 px-md-4 py-4">

    {{-- ── Stat Cards ────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 p-2 bg-primary bg-opacity-10 flex-shrink-0">
                        <i class="ri-file-list-3-line fs-4 text-primary"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h5 class="mb-0 fw-bold text-truncate">{{ $stats['total'] }}</h5>
                        <small class="text-muted">Total Faktur</small>
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
                        <i class="ri-checkbox-circle-line fs-4 text-success"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h5 class="mb-0 fw-bold text-truncate">{{ $stats['paid_count'] }}</h5>
                        <small class="text-muted">Sudah Terbayar</small>
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
                    <div class="rounded-3 p-2 bg-warning bg-opacity-10 flex-shrink-0">
                        <i class="ri-time-line fs-4 text-warning"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h5 class="mb-0 fw-bold text-truncate">{{ $stats['unpaid_count'] }}</h5>
                        <small class="text-muted">Menunggu Bayar</small>
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
                    <div class="rounded-3 p-2 bg-danger bg-opacity-10 flex-shrink-0">
                        <i class="ri-error-warning-line fs-4 text-danger"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h5 class="mb-0 fw-bold text-truncate">{{ $stats['overdue_count'] }}</h5>
                        <small class="text-muted">Terlambat</small>
                    </div>
                </div>
                <div class="card-footer p-0 border-0">
                    <div class="bg-danger rounded-bottom" style="height:3px;"></div>
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
                        <path fill="#31c4da" d="M19 22H5a3 3 0 0 1-3-3V3a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v12h4v4a3 3 0 0 1-3 3m-1-5v2a1 1 0 1 0 2 0v-2zm-2 3V4H4v15a1 1 0 0 0 1 1zM6 7h8v2H6zm0 4h8v2H6zm0 4h5v2H6z" />
                    </svg>
                    <div>
                        <h5 class="fw-bold mb-0">Invoices</h5>
                        <small class="text-muted">Kelola invoice</small>
                    </div>
                </div>

                {{-- Action Buttons --}}
                @if (Auth::user()->role_id != 2)
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('invoice.create') }}" class="btn btn-primary btn-sm px-3">
                        <i class="ri-add-line me-1"></i>
                        <span class="d-none d-md-inline">Tambah Faktur</span>
                        <span class="d-md-none">Tambah</span>
                    </a>
                </div>
                @endif

            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table
                data-url="{{ route('invoice.index') }}"
                data-csrf="{{ csrf_token() }}"
                id="invoice-table"
                class="table table-hover align-middle mb-0 text-nowrap"
                style="width:100%;">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:50px;">No</th>
                        <th>No PO</th>
                        <th>Nama Barang</th>
                        <th>No. Delivery</th>
                        <th class="text-center">Qty Terkirim</th>
                        <th class="text-center">Status Delivery</th>
                        <th>No. Invoice</th>
                        <th>Tgl Invoice</th>
                        <th>Due Date</th>
                        <th class="text-center">Status Invoice</th>
                        <th class="text-center" style="width:60px;">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>

    </div>
</div>
@endsection