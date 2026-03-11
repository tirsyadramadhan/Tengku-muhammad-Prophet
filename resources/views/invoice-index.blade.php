@extends('layouts/contentNavbarLayout')

@section('title', 'Manajemen Invoice')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" id="invoice-main-container">
    {{-- STAT CARDS --}}
    <div class="row mb-4 g-3">
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm border-bottom border-primary border-3 h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="stat-icon bg-label-primary">
                        <i class="ri-file-list-3-line fs-4 text-primary"></i>
                    </div>
                    <div>
                        <div class="stat-label text-muted">Total Faktur</div>
                        <h4 class="stat-value text-dark">{{ $stats['total'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm border-bottom border-success border-3 h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="stat-icon bg-label-success">
                        <i class="ri-checkbox-circle-line fs-4 text-success"></i>
                    </div>
                    <div>
                        <div class="stat-label text-muted">Sudah Terbayar</div>
                        <h4 class="stat-value text-dark">{{ $stats['paid_count'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm border-bottom border-warning border-3 h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="stat-icon bg-label-warning">
                        <i class="ri-time-line fs-4 text-warning"></i>
                    </div>
                    <div>
                        <div class="stat-label text-muted">Menunggu Bayar</div>
                        <h4 class="stat-value text-dark">{{ $stats['unpaid_count'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm border-bottom border-danger border-3 h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="stat-icon bg-label-danger">
                        <i class="ri-error-warning-line fs-4 text-danger"></i>
                    </div>
                    <div>
                        <div class="stat-label text-muted">Terlambat</div>
                        <h4 class="stat-value text-dark">{{ $stats['overdue_count'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN TABLE CARD --}}
    <div class="card invoice-card">

        {{-- Card Header --}}
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="50px" height="50px" viewBox="0 0 24 24">
                    <path fill="#31c4da" d="M19 22H5a3 3 0 0 1-3-3V3a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v12h4v4a3 3 0 0 1-3 3m-1-5v2a1 1 0 1 0 2 0v-2zm-2 3V4H4v15a1 1 0 0 0 1 1zM6 7h8v2H6zm0 4h8v2H6zm0 4h5v2H6z" />
                </svg>
                <div class="d-flex flex-column ms-2">
                    <h4 class="fw-bold mb-0">Invoices</h4>
                    <small class="text-muted">Kelola invoice</small>
                </div>
            </div>
            <a href="{{ route('invoice.create') }}" class="btn btn-primary shadow-sm">
                <i class="ri-add-line me-1"></i> Tambah Faktur
            </a>
        </div>
        <div class="table-responsive" style="scroll-behavior: smooth;">
            <table
                data-url="{{ route('invoice.index') }}"
                data-csrf="{{ csrf_token() }}"
                id="invoice-table" class="table table-hover align-middle mb-0" style="width:100%">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Detail Faktur</th>
                        <th class="text-center">Detail Delivery</th>
                        <th class="text-center">Estimasi Jatuh Tempo</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection