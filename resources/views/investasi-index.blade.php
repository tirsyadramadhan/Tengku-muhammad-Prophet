@extends('layouts/contentNavbarLayout')

@section('title', 'Manajemen Investasi')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" id="investasi-main-container">
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4 col-xxl">
            <div class="inv-stat-card stat-card" style="--card-accent:#71dd37; --card-soft:rgba(113,221,55,0.10);">
                <div class="inv-stat-icon"><i class="ri-funds-line"></i></div>
                <div>
                    <div class="inv-stat-label">Total Margin</div>
                    <h4 class="inv-stat-value loading" id="inv-card-margin">0</h4>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4 col-xxl">
            <div class="inv-stat-card stat-card" style="--card-accent:#03c3ec; --card-soft:rgba(3,195,236,0.10);">
                <div class="inv-stat-icon"><i class="ri-bank-card-line"></i></div>
                <div>
                    <div class="inv-stat-label">Modal Setor</div>
                    <h4 class="inv-stat-value loading" id="inv-card-modal-setor">0</h4>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4 col-xxl">
            <div class="inv-stat-card stat-card" style="--card-accent:#ffab00; --card-soft:rgba(255,171,0,0.10);">
                <div class="inv-stat-icon"><i class="ri-shopping-bag-3-line"></i></div>
                <div>
                    <div class="inv-stat-label">Modal PO Baru</div>
                    <h4 class="inv-stat-value loading" id="inv-card-modal-po">0</h4>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4 col-xxl">
            <div class="inv-stat-card stat-card" style="--card-accent:#ff3e1d; --card-soft:rgba(255,62,29,0.10);">
                <div class="inv-stat-icon"><i class="ri-hand-coin-line"></i></div>
                <div>
                    <div class="inv-stat-label">Total Pengembalian Dana</div>
                    <h4 class="inv-stat-value loading" id="inv-card-penarikan">0</h4>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4 col-xxl inv-card-auto-width">
            <div class="inv-stat-card stat-card" style="--card-accent:#696cff; --card-soft:rgba(105,108,255,0.10);">
                <div class="inv-stat-icon"><i class="ri-wallet-fill"></i></div>
                <div>
                    <div class="inv-stat-label">Dana Tersedia</div>
                    <h4 class="inv-stat-value loading" id="inv-card-dana">0</h4>
                </div>
            </div>
        </div>

    </div>

    {{-- MAIN TABLE CARD --}}
    <div class="inv-table-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24">
                    <path fill="#3ac7c6" d="M2 13h6v8H2zM9 3h6v18H9zm7 5h6v13h-6z" />
                </svg>
                <div class="d-flex flex-column ms-2">
                    <h4 class="fw-bold mb-0">Investasi</h4>
                    <small class="text-muted">Kelola investasi</small>
                </div>
            </div>
            <div class="d-flex gap-2 align-items-center">
                @if (Auth::user()->role_id != 2)
                <button class="btn btn-danger btn-sm px-3" id="truncate-investasi">
                    <i class="ri-delete-bin-5-line me-1"></i> Hapus Seluruh investasi
                </button>
                <a href="{{ route('investments.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                    <i class="ri-add-line"></i><span>Tambah Investasi</span>
                </a>
                <a href="{{ route('investments.importForm') }}" class="btn btn-success">
                    <i class="ri ri-file-excel-line"></i> Import Excel
                </a>
                @endif
                <a href="{{ route('investments.export') }}" class="btn btn-success">
                    <i class="ri ri-file-excel-line"></i> Export Excel
                </a>
            </div>
        </div>

        <div class="table-responsive" style="scroll-behavior: smooth;">
            <table
                data-url="{{ route('investments.index') }}"
                data-role="{{ Auth::user()->role_id }}"
                class="table mb-0" id="investment-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width:50px;">No</th>
                        <th class="text-center">Detail Investasi</th>
                        @if(Auth::user()->role_id !== 2)
                        <th class="text-center">Aksi</th>
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