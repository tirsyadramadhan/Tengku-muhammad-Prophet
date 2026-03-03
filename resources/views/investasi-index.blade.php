@extends('layouts/contentNavbarLayout')

@section('title', 'Manajemen Investasi')

@section('vendor-style')
<style>
    :root {
        --accent: #696cff;
        --accent-soft: rgba(105, 108, 255, 0.10);
        --success: #71dd37;
        --success-soft: rgba(113, 221, 55, 0.10);
        --info: #03c3ec;
        --info-soft: rgba(3, 195, 236, 0.10);
        --warning: #ffab00;
        --warning-soft: rgba(255, 171, 0, 0.10);
        --danger: #ff3e1d;
        --danger-soft: rgba(255, 62, 29, 0.10);
        --surface: #ffffff;
        --border: #e8e8f0;
        --text-muted: #8b8fa8;
        --text-head: #3a3b4a;
        --radius: 14px;
        --shadow-sm: 0 2px 12px rgba(105, 108, 255, 0.06);
        --shadow-md: 0 6px 28px rgba(105, 108, 255, 0.12);
    }

    .inv-stat-card {
        background: var(--surface);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: transform .25s ease, box-shadow .25s ease;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
    }

    .inv-stat-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        border-radius: 4px 0 0 4px;
        background: var(--card-accent, var(--accent));
    }

    .inv-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
    }

    .inv-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
        background: var(--card-soft, var(--accent-soft));
        color: var(--card-accent, var(--accent));
    }

    .inv-stat-label {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        color: var(--text-muted);
        margin-bottom: 0.2rem;
    }

    .inv-stat-value {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--text-head);
        line-height: 1.2;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    /* Table card */
    .inv-table-card {
        background: var(--surface);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .inv-table-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        background: linear-gradient(135deg, #fafaff 0%, #ffffff 100%);
    }

    .inv-table-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-head);
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .inv-table-title-icon {
        width: 36px;
        height: 36px;
        background: var(--accent-soft);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--accent);
        font-size: 1.1rem;
    }

    .money-cell {
        font-variant-numeric: tabular-nums;
        font-weight: 600;
        font-size: 0.875rem;
        text-align: right;
    }

    .money-positive {
        color: #2e7d32;
    }

    .money-negative {
        color: var(--danger);
    }

    .po-badge {
        display: inline-block;
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.25rem 0.55rem;
        border-radius: 6px;
        background: var(--accent-soft);
        color: var(--accent);
        border: 1px solid rgba(105, 108, 255, 0.2);
        margin: 2px;
        line-height: 1;
    }

    .po-badge-empty {
        background: var(--danger-soft);
        color: var(--danger);
        border-color: rgba(255, 62, 29, 0.25);
    }

    .row-no {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        background: var(--accent-soft);
        color: var(--accent);
        border-radius: 50%;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .btn-action {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all .18s ease;
        text-decoration: none;
    }

    .btn-action-info {
        background: var(--info-soft);
        color: var(--info);
    }

    .btn-action-warning {
        background: var(--warning-soft);
        color: var(--warning);
    }

    .btn-action-danger {
        background: var(--danger-soft);
        color: var(--danger);
    }

    .btn-action-info:hover {
        background: var(--info);
        color: #fff;
        transform: scale(1.12);
        box-shadow: 0 3px 10px rgba(3, 195, 236, 0.35);
    }

    .btn-action-warning:hover {
        background: var(--warning);
        color: #fff;
        transform: scale(1.12);
        box-shadow: 0 3px 10px rgba(255, 171, 0, 0.35);
    }

    .btn-action-danger:hover {
        background: var(--danger);
        color: #fff;
        transform: scale(1.12);
        box-shadow: 0 3px 10px rgba(255, 62, 29, 0.35);
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0" style="color: var(--text-head);">Rekap Investasi</h4>
            <small class="text-muted">Manajemen &amp; rekap seluruh data investasi</small>
        </div>
    </div>

    <div class="row g-3 mb-4">

        <div class="col-sm-6 col-xl-4 col-xxl">
            <div class="inv-stat-card" style="--card-accent:#71dd37; --card-soft:rgba(113,221,55,0.10);">
                <div class="inv-stat-icon"><i class="ri-funds-line"></i></div>
                <div>
                    <div class="inv-stat-label">Total Margin</div>
                    <div class="inv-stat-value loading" id="inv-card-margin">0</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4 col-xxl">
            <div class="inv-stat-card" style="--card-accent:#03c3ec; --card-soft:rgba(3,195,236,0.10);">
                <div class="inv-stat-icon"><i class="ri-bank-card-line"></i></div>
                <div>
                    <div class="inv-stat-label">Modal Setor</div>
                    <div class="inv-stat-value loading" id="inv-card-modal-setor">0</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4 col-xxl">
            <div class="inv-stat-card" style="--card-accent:#ffab00; --card-soft:rgba(255,171,0,0.10);">
                <div class="inv-stat-icon"><i class="ri-shopping-bag-3-line"></i></div>
                <div>
                    <div class="inv-stat-label">Modal PO Baru</div>
                    <div class="inv-stat-value loading" id="inv-card-modal-po">0</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4 col-xxl">
            <div class="inv-stat-card" style="--card-accent:#ff3e1d; --card-soft:rgba(255,62,29,0.10);">
                <div class="inv-stat-icon"><i class="ri-hand-coin-line"></i></div>
                <div>
                    <div class="inv-stat-label">Total Pengembalian Dana</div>
                    <div class="inv-stat-value loading" id="inv-card-penarikan">0</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4 col-xxl">
            <div class="inv-stat-card" style="--card-accent:#696cff; --card-soft:rgba(105,108,255,0.10);">
                <div class="inv-stat-icon"><i class="ri-wallet-fill"></i></div>
                <div>
                    <div class="inv-stat-label">Dana Tersedia</div>
                    <div class="inv-stat-value loading" id="inv-card-dana">0</div>
                </div>
            </div>
        </div>

    </div>

    {{-- MAIN TABLE CARD --}}
    <div class="inv-table-card">
        <div class="inv-table-header">
            <div class="inv-table-title">
                <div class="inv-table-title-icon"><i class="ri-file-list-3-line"></i></div>
                <div>
                    <div style="font-size:1rem;font-weight:700;">Daftar Investasi</div>
                    <div style="font-size:0.75rem;font-weight:400;color:var(--text-muted);">Semua record investasi aktif &amp; historis</div>
                </div>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <a href="{{ route('investments.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                    <i class="ri-add-line"></i><span>Tambah Investasi</span>
                </a>
                <a href="{{ route('investments.importForm') }}" class="btn btn-success">
                    <i class="ri ri-file-excel-line"></i> Import Excel
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" id="investment-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width:50px;">No</th>
                        <th class="text-end">Tanggal Investasi</th>
                        <th class="text-end">Modal Setor Awal</th>
                        <th class="text-end">Modal PO Baru</th>
                        <th class="text-end">Margin</th>
                        <th class="text-end">Pencairan Modal</th>
                        <th class="text-end">Margin Cair</th>
                        <th class="text-end">Pengembalian Dana</th>
                        <th class="text-end">Dana Tersedia</th>
                    </tr>
                </thead>
                <tbody></tbody>
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
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        // ── HELPERS ───────────────────────────────────────────────
        function toNum(str) {
            return parseFloat(String(str).replace(/<[^>]*>/g, '').replace(/[^0-9.\-]/g, '')) || 0;
        }

        function formatRp(val) {
            return 'Rp ' + Math.round(val).toLocaleString('id-ID');
        }

        // ── COUNTUP STAT CARDS ────────────────────────────────────
        // Same pattern as po-index.blade.php — hits /api/investasi-stats
        function updateCardStats() {
            const moneyOpts = {
                startVal: 0,
                duration: 2.5,
                prefix: 'Rp ',
                separator: '.',
                decimal: ','
            };

            const statsMap = [{
                    id: 'inv-card-margin',
                    key: 'totalMargin'
                },
                {
                    id: 'inv-card-modal-setor',
                    key: 'totalModalSetor'
                },
                {
                    id: 'inv-card-modal-po',
                    key: 'totalModalPoBaru'
                },
                {
                    id: 'inv-card-penarikan',
                    key: 'totalPenarikan'
                },
                {
                    id: 'inv-card-dana',
                    key: 'danaTersedia'
                },
            ];

            // Remove skeleton shimmer before animating
            statsMap.forEach(function({
                id
            }) {
                document.getElementById(id)?.classList.remove('loading');
            });

            $.getJSON('/api/investasi-stats')
                .done(function(data) {
                    statsMap.forEach(function({
                        id,
                        key
                    }) {
                        var val = parseFloat(data[key] || 0);
                        var anim = new CountUp(id, val, moneyOpts);
                        if (!anim.error) anim.start();
                        else console.warn('CountUp error [' + id + ']:', anim.error);
                    });
                })
                .fail(function(err) {
                    console.error('Failed to fetch investasi stats:', err);
                    statsMap.forEach(function({
                        id
                    }) {
                        var el = document.getElementById(id);
                        if (el) el.textContent = '—';
                    });
                });
        }

        // Fire on page load
        updateCardStats();

        // ── DATATABLE INIT ────────────────────────────────────────
        var table = $('#investment-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('investments.index') }}",
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Memuat Data',
                        text: xhr.responseJSON?.message ?? 'Terjadi kesalahan saat mengambil data.',
                        confirmButtonColor: '#696cff'
                    });
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                },
                {
                    data: 'tgl_investasi',
                    name: 'tgl_investasi',
                    orderable: true,
                    searchable: true,
                    className: 'text-center',
                },
                {
                    data: 'modal_setor_awal',
                    name: 'modal_setor_awal',
                    className: 'money-cell money-positive',
                    orderable: true
                },
                {
                    data: 'modal_po_baru',
                    name: 'modal_po_baru',
                    className: 'money-cell',
                    orderable: true
                },
                {
                    data: 'margin',
                    name: 'margin',
                    className: 'money-cell money-positive',
                    orderable: true
                },
                {
                    data: 'pencairan_modal',
                    name: 'pencairan_modal',
                    className: 'money-cell',
                    orderable: true
                },
                {
                    data: 'margin_cair',
                    name: 'margin_cair',
                    className: 'money-cell money-negative',
                    orderable: true
                },
                {
                    data: 'pengembalian_dana',
                    name: 'pengembalian_dana',
                    className: 'money-cell fw-bold',
                    orderable: true
                },
                {
                    data: 'dana_tersedia',
                    name: 'dana_tersedia',
                    className: 'money-cell fw-bold',
                    orderable: true
                }
            ],
            order: [
                [1, 'desc']
            ],
        });
        $('#investment-table tbody').on('click', '.btn-delete-inv', function() {
            var url = $(this).data('url');
            var name = $(this).data('name') || 'record ini';
            var $btn = $(this);

            Swal.fire({
                title: 'Hapus Investasi?',
                html: 'Data <strong>' + name + '</strong> akan dihapus permanen.<br>Tindakan ini <u>tidak dapat dibatalkan</u>.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff3e1d',
                cancelButtonColor: '#8592a3',
                confirmButtonText: '<i class="ri-delete-bin-line me-1"></i>Ya, Hapus!',
                cancelButtonText: 'Batal',
                focusCancel: true,
                customClass: {
                    confirmButton: 'btn btn-danger px-4',
                    cancelButton: 'btn btn-secondary px-4 ms-2'
                },
                buttonsStyling: false
            }).then(function(result) {
                if (!result.isConfirmed) return;
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

                $.ajax({
                    url: url,
                    type: 'DELETE',
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: res.message ?? 'Data berhasil dihapus.',
                            timer: 1800,
                            showConfirmButton: false,
                            timerProgressBar: true
                        });
                        table.ajax.reload(null, false);
                        updateCardStats(); // Re-animate after delete
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menghapus!',
                            text: xhr.responseJSON?.message ?? 'Terjadi kesalahan.',
                            confirmButtonColor: '#696cff'
                        });
                        $btn.prop('disabled', false).html('<i class="ri-delete-bin-line"></i>');
                    }
                });
            });
        });

    });
</script>
@endsection