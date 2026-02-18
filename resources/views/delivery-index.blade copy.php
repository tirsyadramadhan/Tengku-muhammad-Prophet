@extends('layouts/contentNavbarLayout')

@section('title', 'Logistics & Delivery Tracking')

@section('vendor-style')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" />
<style>
    /* 1. Dashboard Stat Cards */
    .stat-card {
        border: none;
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }

    /* 2. DataTable Custom Styling */
    #delivery-table thead th {
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
        font-weight: 700;
        color: #4b4b4b;
        border-bottom-width: 1px;
    }

    #delivery-table tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.03) !important;
        transition: 0.2s;
    }

    /* Non‑interactive index column */
    #delivery-table td:first-child,
    #delivery-table th:first-child {
        width: 60px;
        text-align: center;
        font-weight: 500;
        color: #6c757d;
    }

    /* Search bar icon fix */
    .dataTables_filter input {
        padding-left: 2rem !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23999'%3E%3Cpath d='M18.031 16.617l4.283 4.282-1.415 1.415-4.282-4.283A8.96 8.96 0 0 1 11 20c-4.968 0-9-4.032-9-9s4.032-9 9-9 9 4.032 9 9a8.96 8.96 0 0 1-1.969 5.617zm-2.006-.742A6.977 6.977 0 0 0 18 11c0-3.868-3.133-7-7-7-3.868 0-7 3.132-7 7 0 3.867 3.132 7 7 7a6.977 6.977 0 0 0 4.875-1.975l.15-.15z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: left 0.75rem center;
        background-size: 1rem;
    }

    .dataTables_length select {
        width: 80px;
        padding-right: 2rem;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Deliveries</h4>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <div class="card stat-card shadow-sm border-bottom border-primary border-3">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-label-primary me-3 rounded p-2"><i class="ri-truck-line fs-4"></i></div>
                    <div>
                        <small class="text-muted d-block">Total Pengantaran</small>
                        <h5 class="mb-0 fw-bold">{{ $stats['total'] }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm border-bottom border-warning border-3">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-label-warning me-3 rounded p-2"><i class="ri-ship-2-line fs-4"></i></div>
                    <div>
                        <small class="text-muted d-block">Dalam Perjalanan</small>
                        <h5 class="mb-0 fw-bold">{{ $stats['transit'] }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm border-bottom border-success border-3">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-label-success me-3 rounded p-2"><i class="ri-map-pin-2-fill fs-4"></i></div>
                    <div>
                        <small class="text-muted d-block">Sudah Tiba</small>
                        <h5 class="mb-0 fw-bold">{{ $stats['delivered'] }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm border-bottom border-info border-3">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-label-info me-3 rounded p-2"><i class="ri-dropbox-line fs-4"></i></div>
                    <div>
                        <small class="text-muted d-block">Total Barang di Delivery</small>
                        <h5 class="mb-0 fw-bold">{{ number_format($stats['inventory']) }} Units</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom py-3">
            <div class="d-flex align-items-center">
                <div class="avatar avatar-md bg-label-primary me-3 rounded p-2">
                    <i class="ri-route-line fs-3"></i>
                </div>
                <div>
                    <h5 class="mb-0">Data Data Delivery</h5>
                </div>
            </div>
            <a href="{{ route('delivery.create') }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i> Buat Delivery Baru
            </a>
        </div>

        <div class="card-body pt-4">
            <div class="table-responsive">
                <table id="delivery-table" class="table table-hover align-middle mb-0" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">NO</th>
                            <th>PO & Tracking Ref</th>
                            <th class="text-center">Load (Qty)</th>
                            <th>ETA / Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
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

        var table = $('#delivery-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('delivery.index') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center fw-medium text-muted'
                },
                {
                    data: 'po_tracking',
                    name: 'po_tracking',
                    className: 'ps-4'
                },
                {
                    data: 'qty_delivered',
                    name: 'qty_delivered',
                    className: 'text-center'
                },
                {
                    data: 'status',
                    name: 'status'
                }
            ],
            order: [
                [2, 'desc']
            ], // order by qty_delivered or any column
            pageLength: 10,
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                search: '',
                searchPlaceholder: 'Search (Ref, Item, Date)...',
                lengthMenu: '_MENU_',
                paginate: {
                    next: '<i class="ri-arrow-right-s-line"></i>',
                    previous: '<i class="ri-arrow-left-s-line"></i>'
                }
            },
            dom: '<"card-body d-flex flex-wrap justify-content-between align-items-center gap-3"<"me-3"l><"flex-grow-1"f>>t<"card-body d-flex flex-wrap justify-content-between align-items-center"<"me-3"i><"p-0"p>>',
            initComplete: function() {
                $('.dataTables_filter input').addClass('form-control form-control-sm');
                $('.dataTables_length select').addClass('form-select form-select-sm');
            },
            drawCallback: function(settings) {
                initTimers(); // your existing timer function
            }
        });

        // Timer logic (unchanged)
        function initTimers() {
            if (window.timerInterval) clearInterval(window.timerInterval);
            window.timerInterval = setInterval(function() {
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
                        display.html("ARRIVING...");
                        display.removeClass('text-warning').addClass('text-success');
                        if (!$this.data('processing')) {
                            $this.data('processing', true);
                            autoDeliver($this.data('id'));
                        }
                        return;
                    }

                    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    var d = days > 0 ? days + "d " : "";
                    var h = (hours < 10 ? "0" + hours : hours) + "h ";
                    var m = (minutes < 10 ? "0" + minutes : minutes) + "m ";
                    var s = (seconds < 10 ? "0" + seconds : seconds) + "s";

                    display.text(d + h + m + s);
                });
            }, 1000);
        }

        function autoDeliver(id) {
            $.ajax({
                url: "/delivery/" + id + "/auto-deliver",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        table.ajax.reload(null, false);
                    }
                },
                error: function() {
                    console.error("Failed to auto-deliver item " + id);
                }
            });
        }
    });
</script>
@endsection