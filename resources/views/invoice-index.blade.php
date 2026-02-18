@extends('layouts/contentNavbarLayout')

@section('title', 'Manajemen Invoice')

@section('vendor-style')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" />
<style>
    /* --- Kartu Statistik (Dipertahankan) --- */
    .stat-card {
        border: none;
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08) !important;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }

    /* --- Penataan DataTable Sempurna (Gaya Materio) --- */
    .card-datatable {
        padding: 0;
    }

    #invoice-table thead th {
        background-color: #f8f9fa;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.8px;
        font-weight: 700;
        color: #566a7f;
        border-bottom: 2px solid #ebeef0 !important;
        padding: 1rem 1rem;
        vertical-align: middle;
    }

    #invoice-table tbody td {
        padding: 1rem 1rem;
        vertical-align: middle;
        color: #697a8d;
        font-size: 0.9rem;
        border-bottom: 1px solid #eff1f3;
    }

    /* ATURAN KOLOM "NO" */
    #invoice-table th:first-child,
    #invoice-table td:first-child {
        width: 60px;
        text-align: center;
        background-color: #fff;
        font-weight: 600;
        color: #a1acb8;
        cursor: default;
        pointer-events: none;
        user-select: none;
    }

    #invoice-table tbody tr:hover td {
        background-color: rgba(105, 108, 255, 0.04) !important;
        transition: background-color 0.2s ease;
    }

    #invoice-table tbody tr:hover td:first-child {
        background-color: #fff !important;
        box-shadow: inset -1px 0 0 #eff1f3;
    }

    .dataTables_filter input {
        border-radius: 20px;
        padding-left: 2.25rem !important;
        border: 1px solid #d9dee3;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23999'%3E%3Cpath d='M18.031 16.617l4.283 4.282-1.415 1.415-4.282-4.283A8.96 8.96 0 0 1 11 20c-4.968 0-9-4.032-9-9s4.032-9 9-9 9 4.032 9 9a8.96 8.96 0 0 1-1.969 5.617zm-2.006-.742A6.977 6.977 0 0 0 18 11c0-3.868-3.133-7-7-7-3.868 0-7 3.132-7 7 0 3.867 3.132 7 7 7a6.977 6.977 0 0 0 4.875-1.975l.15-.15z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: left 0.8rem center;
        background-size: 1.1rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .dataTables_filter input:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.1);
        outline: 0;
    }

    .page-item.active .page-link {
        background-color: #696cff;
        border-color: #696cff;
        box-shadow: 0 0.125rem 0.25rem rgba(105, 108, 255, 0.4);
    }

    .page-link {
        border-radius: 0.375rem;
        margin: 0 0.15rem;
        color: #697a8d;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Faktur</h4>
        </div>
    </div>

    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <div class="card stat-card shadow-sm border-bottom border-primary border-3">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-label-primary me-3"><i class="ri-file-list-3-line fs-4"></i></div>
                    <div>
                        <small class="text-muted d-block">Total Faktur</small>
                        <h5 class="mb-0 fw-bold">{{ $stats['total'] }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm border-bottom border-success border-3">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-label-success me-3"><i class="ri-checkbox-circle-line fs-4"></i></div>
                    <div>
                        <small class="text-muted d-block">Sudah Terbayar</small>
                        <h5 class="mb-0 fw-bold">{{ $stats['paid_count'] }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm border-bottom border-warning border-3">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-label-warning me-3"><i class="ri-time-line fs-4"></i></div>
                    <div>
                        <small class="text-muted d-block">Menunggu Pembayaran</small>
                        <h5 class="mb-0 fw-bold">{{ $stats['unpaid_count'] }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm border-bottom border-danger border-3">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-label-danger me-3"><i class="ri-error-warning-line fs-4"></i></div>
                    <div>
                        <small class="text-muted d-block">Sudah Telat</small>
                        <h5 class="mb-0 fw-bold">{{ $stats['overdue_count'] }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center bg-white py-4 px-4 border-bottom-0">
            <div class="d-flex align-items-center">
                <div class="avatar avatar-md bg-label-info me-3 rounded-3">
                    <i class="ri-file-paper-2-line fs-3"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">Data Faktur</h5>
                    <small class="text-muted">Kelola tagihan dan pembayaran Anda</small>
                </div>
            </div>
            <a href="{{ route('invoice.create') }}" class="btn btn-primary shadow-sm">
                <i class="ri-add-line me-1"></i> Tambah Faktur
            </a>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive text-nowrap">
                <table id="invoice-table" class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>Detail Faktur</th>
                            <th>Referensi (PO / Del)</th>
                            <th class="text-end">Jumlah & Status</th>
                            <th class="text-center">Jatuh Tempo</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('vendor-script')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
@endsection

@section('page-script')
<script>
    $(document).ready(function() {
        $.noConflict();
        var table = $('#invoice-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('invoice.index') }}",

            // PERBAIKAN: Set pengurutan default (misal, berdasarkan Tanggal Jatuh Tempo menaik, atau Nomor Faktur)
            // Kolom 4 adalah "Jatuh Tempo"
            order: [
                [4, 'asc']
            ],

            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'invoice_details',
                    name: 'invoice_details'
                },
                {
                    data: 'linked_references',
                    name: 'linked_references'
                },
                {
                    data: 'status_section',
                    name: 'status_section',
                    className: 'text-end'
                },
                {
                    data: 'due_date_timer',
                    name: 'due_date_timer',
                    className: 'text-center'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ],
            pageLength: 10,
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat...</span></div>',
                search: '',
                searchPlaceholder: 'Cari faktur, klien...',
                lengthMenu: 'Tampilkan _MENU_',
                paginate: {
                    next: '<i class="ri-arrow-right-s-line"></i>',
                    previous: '<i class="ri-arrow-left-s-line"></i>'
                },
                emptyTable: "Tidak ada data faktur",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                zeroRecords: "Tidak ditemukan data yang sesuai",
            },
            dom: '<"row mx-2 py-3"<"col-md-6"l><"col-md-6"f>>t<"row mx-2 py-3 border-top"<"col-md-6"i><"col-md-6"p>>',
            initComplete: function() {
                $('.dataTables_filter input').addClass('form-control');
                $('.dataTables_length select').addClass('form-select');
            },
            drawCallback: function(settings) {
                initTimers();
            }
        });

        function initTimers() {
            if (window.invTimer) clearInterval(window.invTimer);
            window.invTimer = setInterval(function() {
                $('.timer-wrapper').each(function() {
                    var $this = $(this);
                    var dateStr = $this.data('target');
                    if (!dateStr) return;

                    var safeDateStr = dateStr.replace(' ', 'T');
                    var targetDate = new Date(safeDateStr).getTime();
                    if (isNaN(targetDate)) {
                        targetDate = new Date(dateStr.replace(/-/g, '/')).getTime();
                    }

                    var now = new Date().getTime();
                    var distance = targetDate - now;
                    var display = $this.find('.countdown-display');

                    if (distance < 0) {
                        display.html("TERLAMBAT");
                        $this.removeClass('bg-label-info').addClass('bg-label-danger');
                        return;
                    }

                    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    display.text(days + "h " + hours + "j");
                });
            }, 1000);
        }
    });
</script>
@endsection