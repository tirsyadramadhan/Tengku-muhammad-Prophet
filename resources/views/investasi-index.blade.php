@extends('layouts/contentNavbarLayout')

@section('title', 'Investment Management')

@section('vendor-style')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" />
<style>
    /* --- Materio-style Card & Table Tweaks --- */
    .card-datatable {
        padding-bottom: 1rem;
    }

    .dataTables_wrapper .dataTables_head .col-md-6 {
        padding-right: 0;
    }

    /* Table Header Styling */
    table.dataTable thead th {
        text-transform: uppercase;
        font-size: 0.8125rem;
        letter-spacing: 1px;
        font-weight: 600;
        color: #5d596c;
        /* Materio header text color */
        padding-top: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #dbdade;
    }

    /* Row Hover Effect - Standard */
    table.dataTable tbody tr:hover {
        background-color: rgba(67, 89, 113, 0.04) !important;
    }

    /* --- "NO" Column Specifics (Not Hoverable) --- */
    /* Prevent the first column from changing background on row hover */
    table.dataTable tbody tr:hover>td:first-child {
        background-color: #fff !important;
        /* Or your table default bg */
        color: inherit;
    }

    /* Disable pointer events on the "NO" column cells to feel "static" */
    table.dataTable tbody td:first-child {
        cursor: default;
    }

    /* --- Stat Cards (Kept from your original) --- */
    .stat-card {
        border: none;
        border-left: 5px solid;
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        background: #fff;
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

    /* PO Badges */
    .po-badge {
        font-size: 0.72rem;
        padding: 0.35rem 0.6rem;
        margin: 2px;
        background-color: #e7e7ff;
        color: #696cff;
        border-radius: 6px;
        display: inline-block;
        border: 1px solid rgba(105, 108, 255, 0.2);
        font-weight: 500;
    }

    .po-badge-danger {
        background-color: #ff3e1d !important;
        color: #ffffff !important;
        border: none;
    }

    /* DataTable Control Elements (Search, Pagination) */
    div.dataTables_wrapper div.dataTables_filter input {
        border-radius: 0.375rem;
        padding: 0.375rem 0.75rem;
        border: 1px solid #d9dee3;
    }

    div.dataTables_wrapper div.dataTables_length select {
        border-radius: 0.375rem;
        padding: 0.375rem 2rem 0.375rem 0.75rem;
        border: 1px solid #d9dee3;
    }

    .page-item.active .page-link {
        background-color: #696cff;
        border-color: #696cff;
        box-shadow: 0 0.125rem 0.25rem rgba(105, 108, 255, 0.4);
    }
</style>
@endsection

@section('vendor-script')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <div class="row mb-4 g-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-label-success me-3">
                        <i class="ri-funds-line fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.65rem;">Total Margin</small>
                        <h5 class="mb-0 fw-bold">Rp {{ number_format($totalMargin) }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #03c3ec;">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-label-info me-3">
                        <i class="ri-bank-card-line fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.65rem;">Total Modal Setor</small>
                        <h5 class="mb-0 fw-bold">Rp {{ number_format($totalModalSetor) }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #ffc400;">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-label-warning me-3">
                        <i class="ri-shopping-bag-3-line fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.65rem;">Total Modal PO Baru</small>
                        <h5 class="mb-0 fw-bold">Rp {{ number_format($totalModalPoBaru) }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #ff3e1d;">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-label-danger me-3">
                        <i class="ri-hand-coin-line fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.65rem;">Total Penarikan</small>
                        <h5 class="mb-0 fw-bold">Rp {{ number_format($totalPenarikan) }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #A52A2A;">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-3" style="background-color: rgba(165, 42, 42, 0.1); color: #A52A2A;">
                        <i class="ri-wallet-fill fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.65rem;">Dana Tersedia</small>
                        <h5 class="mb-0 fw-bold">Rp {{ number_format($danaTersedia) }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center bg-white">
            <h5 class="card-title mb-0">Investment Ledger</h5>
            <div class="d-flex align-items-center">
                <a href="{{ route('investments.create') }}" class="btn btn-primary">
                    <i class="ri-add-line me-1"></i> Add Investment
                </a>
            </div>
        </div>

        <div class="card-datatable table-responsive">
            <table class="datatables-basic table border-top" id="investment-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>

                        <th>Reference POs</th>
                        <th class="text-end">Modal Setor</th>
                        <th class="text-end">Modal PO Baru</th>
                        <th class="text-end">Total Margin</th>
                        <th class="text-end">Pencairan</th>
                        <th class="text-end">Penarikan</th>
                        <th class="text-end">Dana Tersedia</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="2" class="text-end fw-bold">Total:</th>
                        <th class="text-end fw-bold" id="footer-modal-setor"></th>
                        <th class="text-end fw-bold" id="footer-modal-po-baru"></th>
                        <th class="text-end fw-bold" id="footer-total-margin"></th>
                        <th class="text-end fw-bold" id="footer-pencairan"></th>
                        <th class="text-end fw-bold" id="footer-penarikan"></th>
                        <th class="text-end fw-bold" id="footer-dana-tersedia"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $.noConflict();

        var table = $('#investment-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('investments.index') }}",
            columns: [{
                    // 1. NO Column: Not Clickable, Sortable, Searchable
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    // 2. Reference POs
                    data: 'pos_list',
                    name: 'pos_list',
                    orderable: false,
                    searchable: false
                },
                {
                    // 3. Modal Setor
                    data: 'modal_setor_awal',
                    name: 'modal_setor_awal',
                    className: 'text-end',
                    orderable: true,
                    searchable: true
                },
                {
                    // 4. Modal PO Baru
                    data: 'modal_po_baru',
                    name: 'modal_po_baru',
                    className: 'text-end',
                    orderable: true,
                    searchable: true
                },
                {
                    // 5. Total Margin
                    data: 'total_margin',
                    name: 'total_margin',
                    className: 'text-end',
                    orderable: true,
                    searchable: true
                },
                {
                    // 6. Pencairan
                    data: 'pencairan_modal',
                    name: 'pencairan_modal',
                    className: 'text-end',
                    orderable: true,
                    searchable: true
                },
                {
                    // 7. Penarikan
                    data: 'penarikan',
                    name: 'penarikan',
                    className: 'text-end',
                    orderable: true,
                    searchable: true
                },
                {
                    // 8. Dana Tersedia
                    data: 'dana_tersedia',
                    name: 'dana_tersedia',
                    className: 'text-end fw-bold',
                    orderable: true,
                    searchable: true
                }
            ],
            // Default Sort: Use ID (which is usually hidden or correlated to NO) 
            // Since NO is col 0 and not sortable, we order by the first meaningful data column or ID if available. 
            // Based on your controller, it orders by id_investasi DESC.
            order: [
                [2, 'desc']
            ],

            // DOM Layout customized for Materio look
            dom: '<"card-header d-flex border-bottom p-3"<"head-label"><"dt-action-buttons text-end pt-3 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t' +
                '<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',

            displayLength: 10,
            lengthMenu: [10, 25, 50, 75, 100],
            language: {
                search: '',
                searchPlaceholder: 'Search investments...',
                paginate: {
                    next: '<i class="ri-arrow-right-s-line"></i>',
                    previous: '<i class="ri-arrow-left-s-line"></i>'
                }
            },

            // Footer Totals Calculation
            footerCallback: function(row, data, start, end, display) {
                var api = this.api();
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[^0-9\-]/g, '') * 1 :
                        typeof i === 'number' ? i : 0;
                };

                // Helper to sum columns
                const sumCol = (index) => {
                    return api.column(index, {
                        page: 'current'
                    }).data().reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                };

                $('#footer-modal-setor').html('Rp ' + sumCol(2).toLocaleString('id-ID'));
                $('#footer-modal-po-baru').html('Rp ' + sumCol(3).toLocaleString('id-ID'));
                $('#footer-total-margin').html('Rp ' + sumCol(4).toLocaleString('id-ID'));
                $('#footer-pencairan').html('Rp ' + sumCol(5).toLocaleString('id-ID'));
                $('#footer-penarikan').html('Rp ' + sumCol(6).toLocaleString('id-ID'));
                $('#footer-dana-tersedia').html('Rp ' + sumCol(7).toLocaleString('id-ID'));
            }
        });
    });
</script>
@endsection