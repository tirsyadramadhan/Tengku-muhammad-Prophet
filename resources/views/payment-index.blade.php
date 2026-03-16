@extends('layouts/contentNavbarLayout')

@section('title', 'Riwayat Pembayaran')

@section('content')
<div id="main-container-index" class="container-fluid px-3 px-md-4 py-4">

    {{-- ── Stat Cards ────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 p-2 bg-primary bg-opacity-10 flex-shrink-0">
                        <i class="ri-bank-card-line fs-4 text-primary"></i>
                    </div>
                    <div class="overflow-hidden">
                        <small class="text-muted">Total Penerimaan</small>
                        <h5 class="mb-0 fw-bold text-truncate">Rp {{ number_format($totalVolume, 0, ',', '.') }}</h5>
                    </div>
                </div>
                <div class="card-footer p-0 border-0">
                    <div class="bg-primary rounded-bottom" style="height:3px;"></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 p-2 bg-success bg-opacity-10 flex-shrink-0">
                        <i class="ri-checkbox-circle-line fs-4 text-success"></i>
                    </div>
                    <div class="overflow-hidden">
                        <small class="text-muted">Jumlah Transaksi</small>
                        <h5 class="mb-0 fw-bold text-truncate">{{ $totalTransactions }} Data</h5>
                    </div>
                </div>
                <div class="card-footer p-0 border-0">
                    <div class="bg-success rounded-bottom" style="height:3px;"></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 p-2 bg-info bg-opacity-10 flex-shrink-0">
                        <i class="ri-calendar-check-line fs-4 text-info"></i>
                    </div>
                    <div class="overflow-hidden">
                        <small class="text-muted">Transaksi Terakhir</small>
                        <h5 class="mb-0 fw-bold text-truncate">
                            {{ $lastTransaction ? \Carbon\Carbon::parse($lastTransaction->input_date)->format('d M Y') : '-' }}
                        </h5>
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
                        <path fill="#3dab2d" d="M3.005 3.003h18a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-18a1 1 0 0 1-1-1v-16a1 1 0 0 1 1-1m1 2v14h16v-14zm4.5 9h5.5a.5.5 0 1 0 0-1h-4a2.5 2.5 0 1 1 0-5h1v-2h2v2h2.5v2h-5.5a.5.5 0 0 0 0 1h4a2.5 2.5 0 0 1 0 5h-1v2h-2v-2h-2.5z" />
                    </svg>
                    <div>
                        <h5 class="fw-bold mb-0">Payments</h5>
                        <small class="text-muted">Kelola payment</small>
                    </div>
                </div>

                {{-- Action Button --}}
                @if (Auth::user()->role_id != 2)
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('payment.create') }}" class="btn btn-primary btn-sm px-3">
                        <i class="ri-add-fill me-1"></i>
                        <span class="d-none d-md-inline">Rekam Pembayaran</span>
                        <span class="d-md-none">Rekam</span>
                    </a>
                </div>
                @endif

            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table
                data-url="{{ route('payment.index') }}"
                class="table table-hover align-middle mb-0 text-nowrap"
                id="paymentTable"
                style="width:100%;">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">No</th>
                        <th>No PO</th>
                        <th>Nama Barang</th>
                        <th>Nomor Invoice</th>
                        <th class="text-center">Status Invoice</th>
                        <th class="text-center">Tgl Pembayaran</th>
                        <th>Nominal</th>
                        <th>Metode Bayar</th>
                        <th>Bukti Bayar</th>
                        <th>Keterangan</th>
                        <th>Estimasi Pembayaran</th>
                        <th class="text-center">Status Pembayaran</th>
                        <th class="text-center" style="width:60px;">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>
</div>
@endsection