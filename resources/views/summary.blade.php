@extends('layouts/contentNavbarLayout')

@section('title', 'Purchase Orders')

@section('content')
<div id="main-container-index" class="container-fluid px-3 px-md-4 py-4">
    {{-- ── Main Table Card ────────────────────────────── --}}
    <div class="card border-0 shadow-sm">

        {{-- Card Header --}}
        <div class="card-header bg-transparent border-bottom py-3 px-3 px-md-4">
            <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">

                {{-- Title --}}
                <div class="d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="50px" height="50px" viewBox="0 0 24 24">
                        <path fill="#eca214" d="M9.335 11.502h2.17a4.5 4.5 0 0 1 4.5 4.5H9.004v1h8v-1a5.6 5.6 0 0 0-.885-3h2.886a5 5 0 0 1 4.516 2.852c-2.365 3.12-6.194 5.149-10.516 5.149c-2.761 0-5.1-.59-7-1.625v-9.304a6.97 6.97 0 0 1 3.33 1.428m-4.33 7.5a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-9a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1zm13-14a3 3 0 1 1 0 6a3 3 0 0 1 0-6m-7-3a3 3 0 1 1 0 6a3 3 0 0 1 0-6" />
                    </svg>
                    <div>
                        <h5 class="fw-bold mb-0">Transfer History</h5>
                        <small class="text-muted">Lihat Histori</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table
                data-url="{{ route('summary.index') }}"
                data-csrf="{{ csrf_token() }}"
                class="table table-hover align-middle mb-0 text-nowrap"
                id="summary-table"
                style="width:100%;">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:50px;">No</th>
                        <th>Tanggal Transfer</th>
                        <th>dana_tersedia</th>
                        <th>investasi_dikembalikan</th>
                        <th>investasi_tambahan</th>
                        <th>investasi_ditahan</th>
                        <th>total_investasi_transfer</th>
                        <th>total_transfer_investasi</th>
                        <th>margin_diterima</th>
                        <th>margin_tersedia</th>
                        <th>margin_ditahan</th>
                        <th>total_margin</th>
                        <th>sisa_margin</th>
                        <th class="text-center" style="width:60px;">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection