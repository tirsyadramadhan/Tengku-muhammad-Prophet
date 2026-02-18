@extends('layouts/contentNavbarLayout')

@section('title', 'Purchase Orders Management')

@section('vendor-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" />
<style>
    /* Dashboard Aesthetics */
    .card-header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        background-color: #fff;
    }

    .table-container {
        padding: 0;
    }

    /* Financial & Grouped Columns */
    .col-po-info {
        min-width: 150px;
    }

    .col-product {
        min-width: 250px;
    }

    .col-qty {
        min-width: 100px;
    }

    .col-money {
        min-width: 140px;
        text-align: right;
        font-family: 'Public Sans', sans-serif;
        font-weight: 600;
        white-space: nowrap;
    }

    /* Subtle Column Highlighting */
    .bg-financial {
        background-color: #f8f9fa;
    }

    .bg-profit {
        background-color: rgba(113, 221, 55, 0.04);
    }

    /* Row Hover Effects */
    .table tbody tr:hover {
        background-color: rgba(67, 89, 113, 0.04) !important;
        transition: 0.2s;
    }

    /* Footer Totals Styling */
    .table tfoot tr {
        background-color: #f3f4f6;
        border-top: 2px solid #dbdade;
        font-weight: 800;
        font-size: 0.95rem;
    }

    .table tfoot td {
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    /* Stat Cards */
    .stat-card {
        border: none;
        border-radius: 12px;
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1) !important;
    }

    /* Custom Scrollbar for heavy tables */
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #dbdade;
        border-radius: 10px;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Purchase Orders</h4>
        </div>
    </div>

    <div class="row mb-4 g-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #696cff;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold">{{ $totalPo }}</h4>
                            <small class="text-muted">Total Purchase Orders</small>
                        </div>
                        <div class="avatar bg-label-primary p-2 rounded">
                            <i class="ri-shopping-basket-line fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold">Rp {{ number_format($totalRevenue) }}</h4>
                            <small class="text-muted">Total Harga</small>
                        </div>
                        <div class="avatar bg-label-success p-2 rounded">
                            <i class="ri-money-dollar-circle-line fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #03c3ec;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold">Rp {{ number_format($totalCapital) }}</h4>
                            <small class="text-muted">Total Modal</small>
                        </div>
                        <div class="avatar bg-label-info p-2 rounded">
                            <i class="ri-money-dollar-circle-line fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold text-success">Rp {{ number_format($totalMargin) }}</h4>
                            <small class="text-muted">Total Margin</small>
                        </div>
                        <div class="avatar bg-label-success p-2 rounded">
                            <i class="ri-money-dollar-circle-line fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom py-3">
            <div class="d-flex align-items-center">
                <div class="avatar avatar-md bg-label-success me-3 rounded p-2">
                    <i class="ri-shopping-cart-line fs-3"></i>
                </div>
                <div>
                    <h5 class="mb-0">Data Data PO</h5>
                </div>
            </div>

            <a href="{{ route('po.create') }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i> Buat PO Baru
            </a>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="datatables-po table table-hover align-middle mb-0" id="table-po">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th class="text-center" style="width: 120px;">PO Number</th>
                        <th class="text-center" style="width: 120px;">Date</th>
                        <th class="text-center">Product & Customer</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Total Sales</th>
                        <th class="text-center">Capital</th>
                        <th class="text-center">Margin</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
            </table>
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
    jQuery(document).ready(function($) {
        var dt_table = $('#table-po');

        if (dt_table.length) {
            dt_table.DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('po.index') }}",
                // Enabling State Save keeps the search/sort even after page refresh
                stateSave: true,
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center fw-semibold text-muted'
                    },
                    {
                        data: 'no_po',
                        name: 'no_po',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'tgl_po',
                        name: 'tgl_po',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'product_customer',
                        name: 'nama_barang', // This matches the Controller filterColumn
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'qty',
                        name: 'qty',
                        orderable: true,
                        searchable: true,
                        className: 'text-center'
                    },
                    {
                        data: 'total',
                        name: 'total',
                        orderable: true,
                        searchable: true,
                        className: 'text-end fw-bold bg-financial-group'
                    },
                    {
                        data: 'modal_awal',
                        name: 'modal_awal',
                        orderable: true,
                        searchable: true,
                        className: 'text-end text-muted bg-financial-group'
                    },
                    {
                        data: 'margin',
                        name: 'margin',
                        orderable: true,
                        searchable: true,
                        className: 'text-end text-success fw-bold bg-financial-group'
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: true,
                        searchable: true,
                        className: 'text-center'
                    }
                ],
                order: [
                    [2, 'desc']
                ],
                displayLength: 10,
                lengthMenu: [10, 25, 50, 100],
                // Dom configuration for a "Perfect" clean UI
                dom: '<"card-body d-flex flex-column flex-md-row justify-content-between align-items-center pt-0"<"me-md-2"l><"dt-action-buttons text-end"f>>t<"card-body d-flex flex-column flex-md-row justify-content-between"<"me-md-2"i><"p-0"p>>',
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                    search: "",
                    searchPlaceholder: "Search everything...",
                    sLengthMenu: "_MENU_",
                    paginate: {
                        next: '<i class="ri-arrow-right-s-line"></i>',
                        previous: '<i class="ri-arrow-left-s-line"></i>'
                    }
                },
            });

            // Optional: Styling the search input to look modern
            $('.dataTables_filter input').addClass('form-control form-control-sm ms-0');
            $('.dataTables_length select').addClass('form-select form-select-sm');
        }
    });
</script>
@endsection