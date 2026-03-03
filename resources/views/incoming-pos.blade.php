@extends('layouts/contentNavbarLayout')

@section('title', 'Incoming Purchase Orders')

@section('vendor-style')
<style>
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

    .stat-card h4 {
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
        font-size: 1.15rem;
        letter-spacing: -0.5px;
    }

    @media (max-width: 576px) {
        .stat-card h4 {
            font-size: 1rem;
        }
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
            <h4 class="fw-bold mb-1">PO Yang Akan Datang</h4>
            <small class="text-muted">Kelola dan pantau semua Purchase Order masuk</small>
        </div>
    </div>

    {{-- ── Stat Cards ────────────────────────────────── --}}
    <div class="row mb-4 g-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #696cff">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="card-incoming">0</h4>
                            <small class="text-muted">Total PO Masuk</small>
                        </div>
                        <div class="avatar bg-label-primary p-2 rounded">
                            <i class="ri-shopping-basket-line fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="card-price">0</h4>
                            <small class="text-muted">Total Harga</small>
                        </div>
                        <div class="avatar bg-label-success p-2 rounded">
                            <i class="ri-money-dollar-circle-line fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #03c3ec">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="card-capital">0</h4>
                            <small class="text-muted">Total Modal</small>
                        </div>
                        <div class="avatar bg-label-info p-2 rounded">
                            <i class="ri-bank-line fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold text-success" id="card-margin">0</h4>
                            <small class="text-muted">Total Margin</small>
                        </div>
                        <div class="avatar bg-label-success p-2 rounded">
                            <i class="ri-percent-line fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center py-3 bg-white">
            <h5 class="mb-0 fw-bold">
                <i class="ri-table-line me-2 text-primary"></i>Data Incoming PO
            </h5>
            <a href="{{ route('incoming-po.create') }}" class="btn btn-primary btn-sm px-3">
                <i class="ri-add-line me-1"></i> Buat Incoming PO
            </a>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0" id="table-incoming">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th class="text-center">No. PO</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Produk &amp; Pelanggan</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Total Penjualan</th>
                        <th class="text-end">Modal</th>
                        <th class="text-end">Margin</th>
                        <th class="text-center no-export">Aksi</th>
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

        // ── CountUp Stats ─────────────────────────────────────────────
        function updateCardStats() {
            const moneyOpts = {
                startVal: 0,
                prefix: 'Rp ',
                separator: '.',
                decimal: ',',
                duration: 3
            };
            const numOpts = {
                startVal: 0,
                duration: 3
            };

            const statsConfig = [{
                    id: 'card-incoming',
                    key: 'incoming',
                    opts: numOpts
                },
                {
                    id: 'card-price',
                    key: 'price',
                    opts: moneyOpts
                },
                {
                    id: 'card-capital',
                    key: 'capital',
                    opts: moneyOpts
                },
                {
                    id: 'card-margin',
                    key: 'margin',
                    opts: moneyOpts
                },
            ];

            $.getJSON('/api/incomingPo-stats')
                .done(data => {
                    statsConfig.forEach(({
                        id,
                        key,
                        opts
                    }) => {
                        const anim = new CountUp(id, data[key] || 0, opts);
                        if (!anim.error) anim.start();
                        else console.error(`CountUp error for ${id}:`, anim.error);
                    });
                })
                .fail(err => console.error('Failed to fetch stats:', err));
        }

        updateCardStats();

        // ── Rupiah Formatter ──────────────────────────────────────────
        function rupiah(val) {
            return 'Rp ' + parseFloat(val || 0).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // ── Export: skip "No" (0) and "Aksi" (8) columns ─────────────
        const exportColumns = [1, 2, 3, 4, 5, 6, 7];

        // ── DataTable Init ────────────────────────────────────────────
        var dt_table = $('#table-incoming');

        if (dt_table.length) {
            var table = dt_table.DataTable({
                processing: true,
                serverSide: true,

                // dataSrc callback to capture totals from server response
                ajax: {
                    url: "{{ route('incomingPo') }}",
                    dataSrc: function(json) {
                        // Expects server to return: { data: [...], totals: { qty, total, modal_awal, margin } }
                        if (json.totals) {
                            $('#ft-qty').text(Number(json.totals.qty || 0).toLocaleString('id-ID'));
                            $('#ft-total').text(rupiah(json.totals.total));
                            $('#ft-modal').text(rupiah(json.totals.modal_awal));
                            $('#ft-margin').text(rupiah(json.totals.margin));
                        }
                        return json.data;
                    }
                },

                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center fw-medium'
                    },
                    {
                        data: 'no_po',
                        name: 'no_po',
                        className: 'text-center fw-medium'
                    },
                    {
                        data: 'tgl_po',
                        name: 'tgl_po',
                        className: 'text-center fw-medium'
                    },
                    {
                        data: 'product_customer',
                        name: 'nama_barang',
                        className: 'text-center fw-medium'
                    },
                    {
                        data: 'qty',
                        name: 'qty',
                        className: 'text-center fw-medium'
                    },
                    {
                        data: 'total',
                        name: 'total',
                        className: 'text-center fw-medium'
                    },
                    {
                        data: 'modal_awal',
                        name: 'modal_awal',
                        className: 'text-center fw-medium'
                    },
                    {
                        data: 'margin',
                        name: 'margin',
                        className: 'text-center fw-medium'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center fw-medium'
                    }
                ],

                order: [
                    [2, 'desc']
                ],
                pageLength: 10, // Fix: original used wrong key "displayLength"
            });

            // ── Delete Handler ─────────────────────────────────────────
            $(document).on('click', '.btn-delete-ajax', function() {
                const deleteUrl = $(this).data('url');
                const poNo = $(this).data('po');

                Swal.fire({
                    title: 'Hapus Data?',
                    text: `Apakah Anda yakin ingin menghapus PO #${poNo}?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return $.ajax({
                            url: deleteUrl,
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            error: function(xhr) {
                                const msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan.';
                                Swal.showValidationMessage(`Request failed: ${msg}`);
                            }
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire('Terhapus!', result.value.message, 'success');
                        table.ajax.reload(null, false);
                        updateCardStats();
                    }
                });
            });
        }
    });
</script>
@endsection