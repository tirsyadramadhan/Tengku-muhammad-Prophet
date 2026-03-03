@extends('layouts/contentNavbarLayout')

@section('title', 'Manajemen Invoice')

@section('vendor-style')
<style>
    /* ============================================================
       STAT CARDS
    ============================================================ */
    .stat-card {
        border: none;
        border-radius: 16px;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        overflow: hidden;
        position: relative;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: -20px;
        right: -20px;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        opacity: 0.07;
    }

    .stat-card.border-primary::before {
        background: #696cff;
    }

    .stat-card.border-success::before {
        background: #71dd37;
    }

    .stat-card.border-warning::before {
        background: #ffab00;
    }

    .stat-card.border-danger::before {
        background: #ff3e1d;
    }

    .stat-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 0.75rem 2rem rgba(0, 0, 0, 0.1) !important;
    }

    .stat-icon {
        width: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        flex-shrink: 0;
    }

    .stat-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        font-weight: 600;
    }

    .stat-value {
        font-size: 1.6rem;
        font-weight: 800;
        line-height: 1;
    }

    /* ============================================================
       MAIN TABLE CARD
    ============================================================ */
    .invoice-card {
        border-radius: 16px;
        border: none;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    }

    .invoice-card .card-header {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
        border-bottom: 1px solid #ebeef0;
        padding: 1.25rem 1.5rem;
    }

    .card-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #696cff22, #696cff11);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #696cff;
    }

    /* ============================================================
       DATATABLE CORE STYLES
    ============================================================ */
    #invoice-table thead th {
        background-color: #f8f9fa;
        text-transform: uppercase;
        font-size: 0.70rem;
        letter-spacing: 1px;
        font-weight: 700;
        color: #566a7f;
        border-bottom: 2px solid #ebeef0 !important;
        padding: 0.9rem 1rem;
        vertical-align: middle;
        white-space: nowrap;
    }

    #invoice-table thead th.sorting::after,
    #invoice-table thead th.sorting_asc::after,
    #invoice-table thead th.sorting_desc::after {
        opacity: 0.5;
    }

    #invoice-table tbody td {
        padding: 0.9rem 1rem;
        vertical-align: middle;
        color: #697a8d;
        font-size: 0.875rem;
        border-bottom: 1px solid #f0f2f5;
    }

    /* NO column */
    #invoice-table th:first-child,
    #invoice-table td:first-child {
        width: 52px;
        text-align: center;
        background-color: #fafafa;
        font-weight: 700;
        color: #b0bac5;
        cursor: default;
        pointer-events: none;
        user-select: none;
        font-size: 0.78rem;
    }

    #invoice-table tbody tr {
        transition: background-color 0.15s ease;
    }

    #invoice-table tbody tr:hover td {
        background-color: rgba(105, 108, 255, 0.035) !important;
    }

    #invoice-table tbody tr:hover td:first-child {
        background-color: #fafafa !important;
    }

    #invoice-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* ============================================================
       DATATABLE TOOLBAR (search, length, buttons)
    ============================================================ */
    .dt-toolbar-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f0f2f5;
        background: #fff;
    }

    .dt-toolbar-left {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .dt-toolbar-right {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    /* Length select */
    .dataTables_length select {
        border-radius: 8px;
        border: 1px solid #d9dee3;
        padding: 0.35rem 1.8rem 0.35rem 0.75rem;
        font-size: 0.82rem;
        color: #566a7f;
        background-color: #fff;
        appearance: auto;
        cursor: pointer;
        transition: border-color 0.15s;
    }

    .dataTables_length select:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.15);
        outline: none;
    }

    .dataTables_length label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.82rem;
        color: #697a8d;
        margin: 0;
    }

    /* Search input */
    .dataTables_filter {
        display: flex;
        align-items: center;
    }

    .dataTables_filter label {
        display: flex;
        align-items: center;
        margin: 0;
        position: relative;
    }

    .dataTables_filter .filter-icon {
        position: absolute;
        left: 0.75rem;
        color: #a1acb8;
        pointer-events: none;
        font-size: 0.9rem;
        z-index: 1;
    }

    .dataTables_filter input {
        border-radius: 10px;
        border: 1px solid #d9dee3;
        padding: 0.45rem 1rem 0.45rem 2.2rem;
        font-size: 0.82rem;
        color: #566a7f;
        min-width: 220px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        background: #fafbfc;
    }

    .dataTables_filter input:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.15);
        outline: 0;
        background: #fff;
    }

    .dataTables_filter input::placeholder {
        color: #b0bac5;
    }

    /* ============================================================
       EXPORT BUTTONS — custom styled
    ============================================================ */
    .dt-export-group {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        border: 1px solid #ebeef0;
        border-radius: 10px;
        padding: 0.25rem;
        background: #fafbfc;
    }

    .dt-export-group .btn-export {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.35rem 0.65rem;
        border-radius: 7px;
        font-size: 0.75rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        color: #566a7f;
        background: transparent;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }

    .dt-export-group .btn-export:hover {
        background: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        color: #696cff;
    }

    .btn-export.copy-btn:hover {
        color: #566a7f;
    }

    .btn-export.csv-btn:hover {
        color: #1a7a4a;
    }

    .btn-export.excel-btn:hover {
        color: #1f6e34;
    }

    .btn-export.print-btn:hover {
        color: #d63b2a;
    }

    .btn-export .export-icon {
        font-size: 0.95rem;
    }

    /* Override DT default buttons (hidden, we use custom) */
    div.dt-buttons {
        display: none !important;
    }

    /* ============================================================
       FOOTER (info + pagination)
    ============================================================ */
    .dt-footer-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 0.85rem 1.5rem;
        border-top: 1px solid #f0f2f5;
        background: #fafbfc;
    }

    .dataTables_info {
        font-size: 0.78rem;
        color: #a1acb8;
        font-weight: 500;
    }

    /* Pagination */
    .dataTables_paginate .paginate_button {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px !important;
        margin: 0 2px;
        font-size: 0.8rem;
        font-weight: 600;
        color: #697a8d !important;
        border: 1px solid transparent !important;
        background: transparent !important;
        cursor: pointer;
        transition: all 0.15s ease;
        box-shadow: none !important;
    }

    .dataTables_paginate .paginate_button:hover:not(.disabled):not(.current) {
        background: #f0f1ff !important;
        color: #696cff !important;
        border-color: #d5d6ff !important;
    }

    .dataTables_paginate .paginate_button.current,
    .dataTables_paginate .paginate_button.current:hover {
        background: linear-gradient(135deg, #696cff, #848eff) !important;
        color: #fff !important;
        border-color: transparent !important;
        box-shadow: 0 4px 12px rgba(105, 108, 255, 0.35) !important;
    }

    .dataTables_paginate .paginate_button.disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }

    /* ============================================================
       PROCESSING OVERLAY
    ============================================================ */
    .dataTables_processing {
        background: rgba(255, 255, 255, 0.9) !important;
        border: none !important;
        border-radius: 12px !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12) !important;
        padding: 1.5rem 2rem !important;
    }

    /* ============================================================
       BADGE / STATUS styles (for data rendered in cells)
    ============================================================ */
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.3rem 0.65rem;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.4px;
        text-transform: uppercase;
    }

    /* ============================================================
       PAGE HEADER
    ============================================================ */
    .page-header-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: linear-gradient(135deg, #696cff15, #696cff08);
        color: #696cff;
        border-radius: 20px;
        padding: 0.3rem 0.9rem;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        border: 1px solid #696cff25;
        margin-bottom: 0.5rem;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <div class="page-header-badge">
                <i class="ri-file-list-3-line"></i> Invoice Management
            </div>
            <h4 class="fw-bold mb-0 text-dark">Manajemen Faktur</h4>
            <small class="text-muted">Kelola tagihan, pembayaran, dan jatuh tempo</small>
        </div>
    </div>

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
                        <div class="stat-value text-dark">{{ $stats['total'] }}</div>
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
                        <div class="stat-value text-dark">{{ $stats['paid_count'] }}</div>
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
                        <div class="stat-value text-dark">{{ $stats['unpaid_count'] }}</div>
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
                        <div class="stat-value text-dark">{{ $stats['overdue_count'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN TABLE CARD --}}
    <div class="card invoice-card">

        {{-- Card Header --}}
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div class="card-icon-wrap">
                    <i class="ri-file-paper-2-line fs-4"></i>
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
                <table id="invoice-table" class="table align-middle mb-0" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Detail Faktur</th>
                            <th>Referensi (PO / Del)</th>
                            <th class="text-end">Jumlah &amp; Status</th>
                            <th class="text-center">Jatuh Tempo</th>
                            <th class="text-center no-export">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@section('page-script')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        $('#invoice-table').on('click', '.btn-delete-ajax', function() {
            const url = $(this).data('url');
            const noPo = $(this).data('po');

            Swal.fire({
                title: 'Hapus Invoice?',
                html: `Invoice terkait <strong>${noPo}</strong> akan dihapus secara permanen.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: $('meta[name="csrf-token"]').attr('content'),
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false,
                            }).then(() => table.ajax.reload(null, false));
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message || 'Terjadi kesalahan.'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Tidak Dapat Dihapus',
                            text: 'Invoice ini sudah memiliki payment dan tidak bisa dihapus!',
                        });
                    }
                });
            });
        });

        /* ----------------------------------------------------------------
           DATATABLE INIT
        ---------------------------------------------------------------- */
        var table = $('#invoice-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('invoice.index') }}",

            order: [
                [4, 'asc']
            ],

            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
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
        });

        function initCountdownTimers() {
            if (window.invTimer) clearInterval(window.invTimer);

            window.invTimer = setInterval(function() {
                $('.timer-wrapper').each(function() {
                    var $this = $(this);
                    var dateStr = $this.data('target');
                    if (!dateStr) return;

                    /* Parse date safely */
                    var targetDate = new Date(dateStr.replace(' ', 'T')).getTime();
                    if (isNaN(targetDate)) {
                        targetDate = new Date(dateStr.replace(/-/g, '/')).getTime();
                    }

                    var distance = targetDate - Date.now();
                    var $display = $this.find('.countdown-display');

                    if (distance < 0) {
                        $display.html('<span class="text-danger fw-bold">TERLAMBAT</span>');
                        $this.removeClass('bg-label-info bg-label-warning').addClass('bg-label-danger');
                        return;
                    }

                    var days = Math.floor(distance / 86400000);
                    var hours = Math.floor((distance % 86400000) / 3600000);
                    var mins = Math.floor((distance % 3600000) / 60000);

                    var color = days <= 3 ? 'text-warning' : 'text-info';
                    $display.html(
                        '<span class="' + color + ' fw-semibold">' +
                        days + 'h ' + hours + 'j ' + mins + 'm' +
                        '</span>'
                    );
                });
            }, 1000);
        }

    });
</script>
@endsection