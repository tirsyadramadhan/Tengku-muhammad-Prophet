@extends('layouts/contentNavbarLayout')

@section('title', 'Purchase Orders')

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
            <h4 class="fw-bold mb-1">Purchase Order</h4>
            <small class="text-muted">Kelola dan pantau semua Purchase Order</small>
        </div>
    </div>

    {{-- ── Stat Cards ────────────────────────────────── --}}
    <div class="row mb-4 g-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #696cff;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="card-incoming">0</h4>
                            <small class="text-muted">Total PO</small>
                        </div>
                        <div class="avatar bg-label-primary p-2 rounded">
                            <i class="ri-shopping-basket-line fs-3"></i>
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
            <div class="card stat-card shadow-sm" style="border-left-color: #03c3ec;">
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
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
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

    {{-- ── Main Table Card ────────────────────────────── --}}
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom py-3 bg-white">
            <div class="d-flex align-items-center">
                <div class="avatar avatar-md bg-label-success me-3 rounded p-2">
                    <i class="ri-shopping-cart-line fs-3"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold">Data Purchase Order</h5>
                </div>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <a href="{{ route('po.create') }}" class="btn btn-primary btn-sm px-3">
                    <i class="ri-add-line me-1"></i> Buat PO Baru
                </a>
                <a href="{{ route('po.export') }}" class="btn btn-success">
                    <i class="ri ri-file-excel-line"></i> Export Excel
                </a>
                <a href="{{ route('po.importForm') }}" class="btn btn-success">
                    <i class="ri ri-file-excel-line"></i> Import Excel
                </a>
            </div>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0" id="table-po">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th class="text-center">No. PO</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Produk &amp; Pelanggan</th>
                        <th class="text-center">Jml</th>
                        <th class="text-end">Total Penjualan</th>
                        <th class="text-end">Modal</th>
                        <th class="text-end">Margin</th>
                        <th class="text-center">Status</th>
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

        function updateCardStats() {
            const moneyOpts = {
                startVal: 0,
                duration: 3,
                prefix: 'Rp ',
                separator: '.',
                decimal: ','
            };
            const numOpts = {
                startVal: 0,
                duration: 3
            };

            const statsMap = [{
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

            $.getJSON('/api/po-stats')
                .done(data => {
                    statsMap.forEach(({
                        id,
                        key,
                        opts
                    }) => {
                        const anim = new CountUp(id, data[key] || 0, opts);
                        if (!anim.error) anim.start();
                        else console.warn(`CountUp error for ${id}:`, anim.error);
                    });
                })
                .fail(err => console.error('Failed to fetch PO stats:', err));
        }

        updateCardStats();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function rupiah(val) {
            return 'Rp ' + parseFloat(val || 0).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        const exportColumns = [1, 2, 3, 4, 5, 6, 7, 8, 9];

        var dt_table = $('#table-po');

        if (dt_table.length) {
            var table = dt_table.DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('po.index') }}",
                    dataSrc: function(json) {
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
                        className: 'fw-medium'
                    },
                    {
                        data: 'no_po',
                        name: 'no_po',
                        className: 'fw-medium'
                    },
                    {
                        data: 'tgl_po',
                        name: 'tgl_po',
                        className: 'fw-medium'
                    },
                    {
                        data: 'product_customer',
                        name: 'nama_barang',
                        className: 'fw-medium'
                    },
                    {
                        data: 'qty',
                        name: 'qty',
                        className: 'fw-medium'
                    },
                    {
                        data: 'total',
                        name: 'total',
                        className: 'fw-medium'
                    },
                    {
                        data: 'modal_awal',
                        name: 'modal_awal',
                        className: 'fw-medium'
                    },
                    {
                        data: 'margin',
                        name: 'margin',
                        className: 'fw-medium'
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        className: 'fw-medium'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'fw-medium'
                    }
                ],
                order: [
                    [2, 'desc']
                ],
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
            });

            // ── Delete Handler ─────────────────────────────────────────
            $(document).on('click', '.btn-delete', function() {
                const deleteUrl = $(this).data('url');
                const poNo = $(this).data('po');

                Swal.fire({
                    title: 'Hapus PO?',
                    text: `Apakah Anda yakin ingin menghapus PO #${poNo}? Data yang sudah ada pengirimannya tidak bisa dihapus.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e6381a',
                    cancelButtonColor: '#6e7d88',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return $.ajax({
                            url: deleteUrl,
                            type: 'DELETE',
                        }).catch(error => {
                            Swal.showValidationMessage(
                                `Request failed: ${error.responseJSON?.message ?? error.statusText}`
                            );
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Dihapus!',
                            text: 'Data PO berhasil dihapus.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        table.ajax.reload(null, false);
                        updateCardStats();
                    }
                });
            });
        }
    });
</script>
@endsection