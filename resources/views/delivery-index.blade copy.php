@extends('layouts/contentNavbarLayout')

@section('title', 'Pelacakan Logistik & Pengiriman')

@section('vendor-style')
<style>
    /* ── Stat Cards ─────────────────────────────────── */
    .stat-card {
        border: none;
        border-radius: 12px;
        border-left: 4px solid transparent;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.10) !important;
    }

    .stat-card h5 {
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
        font-size: 1.15rem;
        letter-spacing: -0.5px;
    }

    /* ── Table Header ───────────────────────────────── */
    #delivery-table thead th {
        text-transform: uppercase;
        font-size: 0.72rem;
        letter-spacing: 1px;
        font-weight: 700;
        color: #566a7f;
        background-color: #f8f9fa;
        border-bottom: 2px solid #dbdade;
        padding: 0.85rem 0.75rem;
        white-space: nowrap;
    }

    /* ── Row Hover ──────────────────────────────────── */
    #delivery-table tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.03) !important;
        transition: background-color 0.15s ease;
    }

    /* ── No column ──────────────────────────────────── */
    #delivery-table td:first-child,
    #delivery-table th:first-child {
        width: 60px;
        text-align: center;
        font-weight: 500;
        color: #6c757d;
    }

    /* ── Export Buttons ─────────────────────────────── */
    .dt-buttons {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .dt-button {
        display: inline-flex !important;
        align-items: center;
        gap: 5px;
        padding: 0.40rem 0.85rem !important;
        border-radius: 6px !important;
        font-size: 0.80rem !important;
        font-weight: 600 !important;
        border: 1.5px solid !important;
        background: transparent !important;
        box-shadow: none !important;
        transition: all 0.2s ease !important;
        cursor: pointer;
        text-shadow: none !important;
    }

    .btn-export-copy {
        color: #566a7f !important;
        border-color: #dbdade !important;
    }

    .btn-export-csv {
        color: #03c3ec !important;
        border-color: #03c3ec !important;
    }

    .btn-export-excel {
        color: #71dd37 !important;
        border-color: #71dd37 !important;
    }

    .btn-export-print {
        color: #696cff !important;
        border-color: #696cff !important;
    }

    .btn-export-copy:hover {
        background: #566a7f !important;
        color: #fff !important;
    }

    .btn-export-csv:hover {
        background: #03c3ec !important;
        color: #fff !important;
    }

    .btn-export-excel:hover {
        background: #71dd37 !important;
        color: #fff !important;
    }

    .btn-export-print:hover {
        background: #696cff !important;
        color: #fff !important;
    }

    .dt-button:focus {
        outline: none !important;
        box-shadow: none !important;
    }

    /* ── Controls Bar ───────────────────────────────── */
    .dt-controls-bar {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f0f0f0;
        gap: 0.75rem;
    }

    .dt-controls-bar .dataTables_filter label,
    .dt-controls-bar .dataTables_length label {
        margin-bottom: 0;
        font-size: 0.85rem;
    }

    /* ── Custom Scrollbar ───────────────────────────── */
    .table-responsive::-webkit-scrollbar {
        height: 6px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #dbdade;
        border-radius: 10px;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    {{-- ── Page Title ────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Pengiriman</h4>
            <small class="text-muted">Kelola dan pantau semua pengiriman barang</small>
        </div>
    </div>

    {{-- ── Stat Cards ────────────────────────────────── --}}
    <div class="row mb-4 g-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #696cff;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $stats['total'] }}</h5>
                            <small class="text-muted">Total Pengiriman</small>
                        </div>
                        <div class="avatar bg-label-primary p-2 rounded">
                            <i class="ri-truck-line fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #ffab00;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $stats['transit'] }}</h5>
                            <small class="text-muted">Dalam Perjalanan</small>
                        </div>
                        <div class="avatar bg-label-warning p-2 rounded">
                            <i class="ri-ship-2-line fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $stats['delivered'] }}</h5>
                            <small class="text-muted">Sudah Tiba</small>
                        </div>
                        <div class="avatar bg-label-success p-2 rounded">
                            <i class="ri-map-pin-2-fill fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #03c3ec;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 fw-bold">{{ number_format($stats['inventory']) }} <small class="text-muted fs-6 fw-normal">Unit</small></h5>
                            <small class="text-muted">Total Barang Dikirim</small>
                        </div>
                        <div class="avatar bg-label-info p-2 rounded">
                            <i class="ri-dropbox-line fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Main Table Card ────────────────────────────── --}}
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom py-3 bg-white">
            <div class="d-flex align-items-center">
                <div class="avatar avatar-md bg-label-primary me-3 rounded p-2">
                    <i class="ri-route-line fs-3"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold">Data Pengiriman</h5>
                </div>
            </div>
            <a href="{{ route('delivery.create') }}" class="btn btn-primary btn-sm px-3">
                <i class="ri-add-line me-1"></i> Buat Pengiriman Baru
            </a>
        </div>

        <div class="table-responsive text-nowrap">
            <table id="delivery-table" class="table table-hover align-middle mb-0" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Delivery Number</th>
                        <th>PO &amp; Referensi</th>
                        <th class="text-center">Jumlah Terkirim</th>
                        <th class="text-center">Tiba Pada</th>
                        <th>Status Delivery</th>
                        <th>Status Invoice</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>
@endsection

@section('page-script')
<script>
    document.addEventListener("DOMContentLoaded", function() {

        // ── DataTable Init ────────────────────────────────────────────
        // Declare table here so delete handler (bound via delegation) can reference it
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
                    data: 'delivery_no',
                    name: 'delivery_no',
                    className: 'ps-3'
                },
                {
                    data: 'po_tracking',
                    name: 'po_tracking',
                    className: 'ps-3'
                },
                {
                    data: 'qty_delivered',
                    name: 'qty_delivered',
                    className: 'text-center'
                },
                {
                    data: 'delivered_at',
                    name: 'delivered_at',
                    className: 'ps-3'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'invoiced_status',
                    name: 'invoiced_status'
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
            ], // Fix: was [2,'desc'] which sorted by qty — sort by PO reference instead

            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],

            drawCallback: function() {
                var elements = document.getElementsByClassName("delivery-timer");
                for (let i = 0; i < elements.length; i++) {
                    var wrapper = elements[i].closest('.timer-wrapper');
                    var target = wrapper ? wrapper.getAttribute('data-target') : null;

                    if (!target) continue;

                    var tick = Tick.DOM.create(elements[i], {
                        value: target,
                        didInit: function(tick) {
                            console.log('Tick initialized!', tick);
                        }
                    });
                }
            }
        });

        // ── Delete Handler ────────────────────────────────────────────
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();

            const url = $(this).data('url');
            const deliveryNo = $(this).data('po');

            Swal.fire({
                title: 'Hapus Pengiriman?',
                text: `Hapus Pengiriman: ${deliveryNo}? Tindakan ini tidak dapat dibatalkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            dataType: 'json'
                        })
                        .done(response => response)
                        .fail(xhr => {
                            const errorMsg = xhr.responseJSON?.message ?? 'Terjadi kesalahan';
                            Swal.fire({
                                title: 'Gagal!',
                                text: errorMsg,
                                icon: 'error',
                                confirmButtonColor: '#3085d6'
                            });
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value?.success) {
                    Swal.fire({
                        title: 'Terhapus!',
                        text: result.value.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    table.ajax.reload(null, false); // Fix: uses stored variable instead of re-querying
                }
            });
        });

        // ── Countdown Timer Logic ─────────────────────────────────────
        function initTimers() {
            if (window.timerInterval) clearInterval(window.timerInterval);

            window.timerInterval = setInterval(function() {
                $('.timer-wrapper').each(function() {
                    const $this = $(this);
                    const dateStr = $this.data('target');
                    if (!dateStr) return;

                    const safeDateStr = dateStr.replace(' ', 'T');
                    let targetDate = new Date(safeDateStr).getTime();
                    if (isNaN(targetDate)) {
                        targetDate = new Date(dateStr.replace(/-/g, '/')).getTime();
                    }

                    const now = new Date().getTime();
                    const distance = targetDate - now;
                    const display = $this.find('.countdown-display');

                    if (distance < 0) {
                        display.html('TIBA...');
                        display.removeClass('text-warning').addClass('text-success');
                        if (!$this.data('processing')) {
                            $this.data('processing', true);
                            autoDeliver($this.data('id'));
                        }
                        return;
                    }

                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    const d = days > 0 ? days + 'h ' : '';
                    const h = String(hours).padStart(2, '0') + 'j ';
                    const m = String(minutes).padStart(2, '0') + 'm ';
                    const s = String(seconds).padStart(2, '0') + 'd';

                    display.text(d + h + m + s);
                });
            }, 1000);
        }

        function autoDeliver(id) {
            $.ajax({
                url: '/delivery/' + id + '/auto-deliver',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        table.ajax.reload(null, false);
                    }
                },
                error: function() {
                    console.error('Gagal mengubah status pengiriman otomatis untuk ID: ' + id);
                }
            });
        }
    });
</script>
@endsection