@extends('layouts/contentNavbarLayout')

@section('title', 'Riwayat Pembayaran')

@section('vendor-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" />
{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

<style>
    /* Professional Dashboard Styling */
    .card-header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
    }

    .table-container {
        padding: 0 1.5rem 1.5rem 1.5rem;
    }

    /* Column Sizing & Styling */
    .col-no {
        width: 50px;
        text-align: center;
        font-weight: bold;
        color: #696cff;
    }

    .col-ref {
        min-width: 180px;
        background-color: rgba(105, 108, 255, 0.02);
    }

    .col-money {
        text-align: right;
        font-family: 'Public Sans', sans-serif;
        font-weight: 700;
        min-width: 150px;
    }

    /* DataTables Overrides for cleaner look */
    table.dataTable tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.03) !important;
        transition: 0.2s;
    }

    .dataTables_wrapper .dataTables_length select {
        padding-right: 2rem !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #696cff !important;
        color: white !important;
        border: none;
        border-radius: 0.375rem;
    }

    /* Method Pills */
    .method-pill {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        letter-spacing: 0.5px;
    }

    /* Summary Stat Cards */
    .stat-card {
        border: none;
        border-bottom: 3px solid #696cff;
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stat-card shadow-sm">
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
            <div class="card stat-card shadow-sm" style="border-bottom-color: #71dd37;">
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
            <div class="card stat-card shadow-sm" style="border-bottom-color: #03c3ec;">
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
        <div class="card-header-actions border-bottom">
            <div class="d-flex align-items-center">
                <div class="avatar avatar-md bg-label-success me-3">
                    <i class="ri-exchange-funds-line fs-3"></i>
                </div>
                <div>
                    <h5 class="mb-0">Buku Besar Pembayaran</h5>
                    <small class="text-muted">Riwayat semua pembayaran masuk & penyelesaian</small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('payment.create') }}" class="btn btn-primary">
                    <i class="ri-add-fill me-1"></i> Rekam Pembayaran
                </a>
            </div>
        </div>

        <div class="table-responsive table-container pt-3">
            <table class="table table-hover align-middle mb-0" id="paymentTable">
                <thead class="table-light">
                    <tr>
                        <th class="col-no text-center">No</th>
                        <th class="col-ref">Detail Referensi</th>
                        <th>Pelanggan</th>
                        <th class="text-end">Jumlah Bayar</th>
                        <th class="ps-5">Tanggal & Metode</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Data processed via Yajra DataTables --}}
                </tbody>
            </table>
        </div>

        <div class="card-footer border-top bg-light-subtle d-flex justify-content-between align-items-center p-3">
            <span class="text-muted small">Catatan Keuangan Terverifikasi</span>
            <div class="d-flex align-items-center">
                <span class="me-3 text-muted">Total Volume Halaman Ini:</span>
                <span class="h5 mb-0 fw-bold text-primary" id="pageTotal">Calculating...</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('vendor-script')
{{-- JQuery & DataTables JS --}}
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
    $(document).ready(function() {
        $.noConflict();

        var table = $('#paymentTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('payment.index') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'col-no'
                },
                {
                    data: 'referensi',
                    name: 'referensi',
                    className: 'col-ref ps-4'
                },
                {
                    data: 'pelanggan',
                    name: 'pelanggan'
                },
                {
                    data: 'amount',
                    name: 'amount',
                    className: 'col-money'
                },
                {
                    data: 'tanggal_metode',
                    name: 'payment_date', // Sort based on date
                    className: 'ps-5'
                }
            ],
            order: [
                [4, 'desc']
            ], // Default sort by Payment Date (index 4)
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
            },
            drawCallback: function(settings) {
                var api = this.api();
                var pageTotal = api.column(3, {
                    page: 'current'
                }).data().reduce(function(a, b) {
                    // Convert to string to safely use .replace()
                    var strA = String(a);
                    var strB = String(b);
                    // Remove HTML tags and non‑numeric characters, then parse as float
                    var numA = parseFloat(strA.replace(/<[^>]*>/g, '').replace(/[^0-9.-]+/g, '')) || 0;
                    var numB = parseFloat(strB.replace(/<[^>]*>/g, '').replace(/[^0-9.-]+/g, '')) || 0;
                    return numA + numB;
                }, 0);

                // Format as IDR currency
                var formatted = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(pageTotal);

                $('#pageTotal').html(formatted);
            }
        });
    });
</script>
@endsection