@extends('layouts/contentNavbarLayout')

@section('title', 'Manajemen Investasi')

@section('content')
<div id="main-container-index" class="container-fluid px-3 px-md-4 py-4">

    {{-- ── Stat Cards ────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 p-2 flex-shrink-0" style="background:rgba(113,221,55,0.12);">
                        <i class="ri-funds-line fs-4" style="color:#71dd37;"></i>
                    </div>
                    <div class="overflow-hidden">
                        <small class="text-muted">Total Margin</small>
                        <h5 class="mb-0 fw-bold text-truncate" id="inv-card-margin">0</h5>
                    </div>
                </div>
                <div class="card-footer p-0 border-0">
                    <div class="rounded-bottom" style="height:3px;background:#71dd37;"></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 p-2 flex-shrink-0" style="background:rgba(3,195,236,0.12);">
                        <i class="ri-bank-card-line fs-4" style="color:#03c3ec;"></i>
                    </div>
                    <div class="overflow-hidden">
                        <small class="text-muted">Modal Setor</small>
                        <h5 class="mb-0 fw-bold text-truncate" id="inv-card-modal-setor">0</h5>
                    </div>
                </div>
                <div class="card-footer p-0 border-0">
                    <div class="rounded-bottom" style="height:3px;background:#03c3ec;"></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 p-2 flex-shrink-0" style="background:rgba(255,171,0,0.12);">
                        <i class="ri-shopping-bag-3-line fs-4" style="color:#ffab00;"></i>
                    </div>
                    <div class="overflow-hidden">
                        <small class="text-muted">Modal PO Baru</small>
                        <h5 class="mb-0 fw-bold text-truncate" id="inv-card-modal-po">0</h5>
                    </div>
                </div>
                <div class="card-footer p-0 border-0">
                    <div class="rounded-bottom" style="height:3px;background:#ffab00;"></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 p-2 flex-shrink-0" style="background:rgba(255,62,29,0.12);">
                        <i class="ri-hand-coin-line fs-4" style="color:#ff3e1d;"></i>
                    </div>
                    <div class="overflow-hidden">
                        <small class="text-muted">Total Pengembalian Dana</small>
                        <h5 class="mb-0 fw-bold text-truncate" id="inv-card-penarikan">0</h5>
                    </div>
                </div>
                <div class="card-footer p-0 border-0">
                    <div class="rounded-bottom" style="height:3px;background:#ff3e1d;"></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 col-xl">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 p-2 flex-shrink-0" style="background:rgba(105,108,255,0.12);">
                        <i class="ri-wallet-fill fs-4" style="color:#696cff;"></i>
                    </div>
                    <div class="overflow-hidden">
                        <small class="text-muted">Dana Tersedia</small>
                        <h5 class="mb-0 fw-bold text-truncate" id="inv-card-dana">0</h5>
                    </div>
                </div>
                <div class="card-footer p-0 border-0">
                    <div class="rounded-bottom" style="height:3px;background:#696cff;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Main Table Card ────────────────────────────── --}}
    <div class="card border-0 shadow-sm">

        {{-- Card Header --}}
        <div class="card-header bg-transparent border-bottom py-3 px-3 px-md-4">
            <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">

                {{-- Title --}}
                <div class="d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" class="flex-shrink-0">
                        <path fill="#3ac7c6" d="M2 13h6v8H2zM9 3h6v18H9zm7 5h6v13h-6z" />
                    </svg>
                    <div>
                        <h5 class="fw-bold mb-0">Investasi</h5>
                        <small class="text-muted">Kelola investasi</small>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex flex-wrap gap-2">
                    @if (Auth::user()->role_id != 2)
                    <button class="btn btn-danger btn-sm px-3" id="truncate-investasi">
                        <i class="ri-delete-bin-5-line me-1"></i>
                        <span class="d-none d-md-inline">Hapus Seluruh Investasi</span>
                        <span class="d-md-none">Hapus</span>
                    </button>
                    <a href="{{ route('investments.create') }}" class="btn btn-primary btn-sm px-3">
                        <i class="ri-add-line me-1"></i>
                        <span class="d-none d-md-inline">Tambah Investasi</span>
                        <span class="d-md-none">Tambah</span>
                    </a>
                    <a href="{{ route('investments.importForm') }}" class="btn btn-success btn-sm px-3">
                        <i class="ri ri-file-excel-line me-1"></i>
                        <span class="d-none d-md-inline">Import Excel</span>
                        <span class="d-md-none">Import</span>
                    </a>
                    @endif
                    <a href="{{ route('investments.export') }}" class="btn btn-outline-success btn-sm px-3">
                        <i class="ri ri-file-excel-line me-1"></i>
                        <span class="d-none d-md-inline">Export Excel</span>
                        <span class="d-md-none">Export</span>
                    </a>
                </div>

            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table
                data-url="{{ route('investments.index') }}"
                data-role="{{ Auth::user()->role_id }}"
                class="table table-hover align-middle mb-0 text-nowrap"
                id="investment-table"
                style="width:100%;">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:50px;">No</th>
                        <th>Modal Setor Awal</th>
                        <th>Modal PO Baru</th>
                        <th>Margin</th>
                        <th>Pencairan Modal</th>
                        <th>Margin Cair</th>
                        <th>Pengembalian Dana</th>
                        <th>Dana Tersedia</th>
                        <th>Tanggal Investasi</th>
                        @if(Auth::user()->role_id !== 2)
                        <th class="text-center" style="width:60px;">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>
</div>
@include('truncate-alert-2')
@endsection