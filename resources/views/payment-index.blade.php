@extends('layouts/contentNavbarLayout')

@section('title', 'Riwayat Pembayaran')

@section('vendor-style')
<style>
    .card-header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
    }

    .table-container {
        padding: 0 1.5rem 1.5rem 1.5rem;
    }

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

    .method-pill {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        letter-spacing: 0.5px;
    }

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
                    <small class="text-muted">Riwayat semua pembayaran masuk &amp; penyelesaian</small>
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
                        <th class="ps-5">Tanggal &amp; Metode</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Filled by Yajra DataTables via AJAX --}}
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
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
                    name: 'payment_date',
                    className: 'ps-5'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ],
            order: [
                [4, 'desc']
            ],
        });

        // ── 3. DELETE — Event delegation on tbody ─────────────────────────
        // MUST use event delegation because DataTables re-renders rows on
        // every page change, destroying any directly-bound listeners.
        $('#paymentTable tbody').on('click', '.btn-delete-ajax', function() {
            var deleteUrl = $(this).data('url'); // e.g. /payment/5
            var $btn = $(this);

            Swal.fire({
                title: 'Hapus Pembayaran?',
                text: 'Data pembayaran ini akan dihapus permanen. Tindakan ini tidak dapat dibatalkan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff3e1d',
                cancelButtonColor: '#8592a3',
                confirmButtonText: '<i class="ri-delete-bin-line me-1"></i> Ya, Hapus!',
                cancelButtonText: 'Batal',
                focusCancel: true,
            }).then(function(result) {
                if (!result.isConfirmed) return;

                // Show loading state on the button
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

                $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    success: function(response) {
                        Swal.fire({
                            title: 'Terhapus!',
                            text: response.message ?? 'Pembayaran berhasil dihapus.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false,
                        });

                        // Reload DataTable to reflect the deletion
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan. Coba lagi.';
                        Swal.fire({
                            title: 'Gagal!',
                            text: msg,
                            icon: 'error',
                            confirmButtonColor: '#696cff',
                        });

                        // Restore button on failure
                        $btn.prop('disabled', false).html('<i class="ri-delete-bin-line"></i>');
                    }
                });
            });
        });

    });
</script>
@endsection