@extends('layouts/contentNavbarLayout')

@section('title', 'Riwayat Pembayaran')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" id="payment-main-container">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stat-card shadow-sm" style="border-left-color: #696cff;">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-label-primary me-3">
                        <i class="ri-bank-card-line fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total Penerimaan</small>
                        <h4 class="mb-0 fw-bold">Rp {{ number_format($totalVolume, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card shadow-sm" style="border-left-color: #696cff;">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-label-success me-3">
                        <i class="ri-checkbox-circle-line fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Jumlah Transaksi</small>
                        <h4 class="mb-0 fw-bold">{{ $totalTransactions }} Data</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card shadow-sm" style="border-left-color: #696cff;">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-label-info me-3">
                        <i class="ri-calendar-check-line fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Transaksi Terakhir</small>
                        <h4 class="mb-0 fw-bold">
                            {{ $lastTransaction ? \Carbon\Carbon::parse($lastTransaction->input_date)->format('d M Y') : '-' }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="50px" height="50px" viewBox="0 0 24 24">
                    <path fill="#3dab2d" d="M3.005 3.003h18a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-18a1 1 0 0 1-1-1v-16a1 1 0 0 1 1-1m1 2v14h16v-14zm4.5 9h5.5a.5.5 0 1 0 0-1h-4a2.5 2.5 0 1 1 0-5h1v-2h2v2h2.5v2h-5.5a.5.5 0 0 0 0 1h4a2.5 2.5 0 0 1 0 5h-1v2h-2v-2h-2.5z" />
                </svg>
                <div class="d-flex flex-column ms-2">
                    <h4 class="fw-bold mb-0">Payments</h4>
                    <small class="text-muted">Kelola payment</small>
                </div>
            </div>
            @if (Auth::user()->role_id != 2)
            <a href="{{ route('payment.create') }}" class="btn btn-primary">
                <i class="ri-add-fill me-1"></i> Rekam Pembayaran
            </a>
            @endif
        </div>

        <div class="table-responsive" style="scroll-behavior: smooth;">
            <table
                data-url="{{ route('payment.index') }}"
                class="table table-hover align-middle mb-0" id="paymentTable">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Detail Pembayaran</th>
                        <th class="text-center">Estimasi Pembayaran</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection