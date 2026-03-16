@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard')

@section('content')
<div id="main-container-index" class="container-fluid px-3 px-md-4 py-4">

    {{-- ── Rekening ──────────────────────────────────────────────────────── --}}
    <div class="mb-2">
        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="bg-success bg-opacity-10 rounded p-2 d-flex align-items-center justify-content-center">
                <i class="ri-bank-card-line text-success fs-5"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0">Rekening</h5>
                <small class="text-muted">Saldo & dana tersedia</small>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-sm-6 col-lg-4 col-xxl-3">
                <div class="card border-0 shadow-sm h-100 stats-card" id="dana_tersedia-card"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Klik untuk melihat detail"
                    style="border-left: 4px solid #71dd37 !important; cursor:pointer;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 rounded-3 p-2 d-flex align-items-center justify-content-center bg-success bg-opacity-10" style="width:48px;height:48px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24">
                                <path fill="#16a34a" d="M22.005 6h-7a6 6 0 0 0 0 12h7v2a1 1 0 0 1-1 1h-18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h18a1 1 0 0 1 1 1zm-7 2h8v8h-8a4 4 0 1 1 0-8m0 3v2h3v-2z" />
                            </svg>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="text-muted fw-semibold" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Dana Tersedia</div>
                            <h5 class="fw-bold mb-0 text-success" id="dana_tersedia">0</h5>
                        </div>
                        <i class="ri-arrow-right-s-line text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4 text-muted opacity-25">

    {{-- ── Pemasukan ─────────────────────────────────────────────────────── --}}
    <div class="mb-2">
        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="bg-warning bg-opacity-10 rounded p-2 d-flex align-items-center justify-content-center">
                <i class="ri-arrow-down-circle-line text-warning fs-5"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0">Pemasukan</h5>
                <small class="text-muted">Investasi & margin masuk</small>
            </div>
        </div>
        <div class="row g-3">

            {{-- Investasi Dikembalikan --}}
            <div class="col-sm-6 col-lg-4 col-xxl-3">
                <div class="card border-0 shadow-sm h-100 stats-card"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Klik untuk melihat detail"
                    style="border-left: 4px solid #71dd37 !important; cursor:pointer;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 rounded-3 p-2 d-flex align-items-center justify-content-center bg-warning bg-opacity-10" style="width:48px;height:48px;">
                            <i class="ri-arrow-go-back-line text-warning fs-5"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="text-muted fw-semibold" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Investasi Dikembalikan</div>
                            <h5 class="fw-bold mb-0" id="investasi_dikembalikan">0</h5>
                        </div>
                        <i class="ri-arrow-right-s-line text-muted"></i>
                    </div>
                </div>
            </div>

            {{-- Investasi Tambahan --}}
            <div class="col-sm-6 col-lg-4 col-xxl-3">
                <div class="card border-0 shadow-sm h-100 stats-card"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Klik untuk melihat detail"
                    style="border-left: 4px solid #71dd37 !important; cursor:pointer;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 rounded-3 p-2 d-flex align-items-center justify-content-center bg-warning bg-opacity-10" style="width:48px;height:48px;">
                            <i class="ri-add-circle-line text-warning fs-5"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="text-muted fw-semibold" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Investasi Tambahan</div>
                            <h5 class="fw-bold mb-0" id="investasi_tambahan">0</h5>
                        </div>
                        <i class="ri-arrow-right-s-line text-muted"></i>
                    </div>
                </div>
            </div>

            {{-- Investasi Ditahan --}}
            <div class="col-sm-6 col-lg-4 col-xxl-3">
                <div class="card border-0 shadow-sm h-100 stats-card"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Klik untuk melihat detail"
                    style="border-left: 4px solid #fd7e14 !important; cursor:pointer;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 rounded-3 p-2 d-flex align-items-center justify-content-center bg-warning bg-opacity-10" style="width:48px;height:48px;">
                            <i class="ri-pause-circle-line text-warning fs-5"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="text-muted fw-semibold" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Investasi Ditahan</div>
                            <h5 class="fw-bold mb-0 text-warning" id="investasi_ditahan">0</h5>
                        </div>
                        <i class="ri-arrow-right-s-line text-muted"></i>
                    </div>
                </div>
            </div>

            {{-- Total Investasi Transfer --}}
            <div class="col-sm-6 col-lg-4 col-xxl-3">
                <div class="card border-0 shadow-sm h-100 stats-card"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Klik untuk melihat detail"
                    style="border-left: 4px solid #71dd37 !important; cursor:pointer;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 rounded-3 p-2 d-flex align-items-center justify-content-center bg-success bg-opacity-10" style="width:48px;height:48px;">
                            <i class="ri-send-plane-line text-success fs-5"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="text-muted fw-semibold" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Total Investasi Transfer</div>
                            <h5 class="fw-bold mb-0" id="total_investasi_transfer">0</h5>
                        </div>
                        <i class="ri-arrow-right-s-line text-muted"></i>
                    </div>
                </div>
            </div>

            {{-- Total Transfer Investasi --}}
            <div class="col-sm-6 col-lg-4 col-xxl-3">
                <div class="card border-0 shadow-sm h-100 stats-card"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Klik untuk melihat detail"
                    style="border-left: 4px solid #71dd37 !important; cursor:pointer;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 rounded-3 p-2 d-flex align-items-center justify-content-center bg-success bg-opacity-10" style="width:48px;height:48px;">
                            <i class="ri-exchange-dollar-line text-success fs-5"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="text-muted fw-semibold" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Total Transfer Investasi</div>
                            <h5 class="fw-bold mb-0" id="total_transfer_investasi">0</h5>
                        </div>
                        <i class="ri-arrow-right-s-line text-muted"></i>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <hr class="my-4 text-muted opacity-25">

    {{-- ── Margin ────────────────────────────────────────────────────────── --}}
    <div class="mb-2">
        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="bg-primary bg-opacity-10 rounded p-2 d-flex align-items-center justify-content-center">
                <i class="ri-percent-line text-primary fs-5"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0">Margin</h5>
                <small class="text-muted">Distribusi margin PO</small>
            </div>
        </div>
        <div class="row g-3">

            {{-- Margin Diterima --}}
            <div class="col-sm-6 col-lg-4 col-xxl-3">
                <div class="card border-0 shadow-sm h-100 stats-card"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Klik untuk melihat detail"
                    style="border-left: 4px solid #696cff !important; cursor:pointer;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 rounded-3 p-2 d-flex align-items-center justify-content-center bg-primary bg-opacity-10" style="width:48px;height:48px;">
                            <i class="ri-hand-coin-line text-primary fs-5"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="text-muted fw-semibold" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Margin Diterima</div>
                            <h5 class="fw-bold mb-0 text-primary" id="margin_diterima">0</h5>
                        </div>
                        <i class="ri-arrow-right-s-line text-muted"></i>
                    </div>
                </div>
            </div>

            {{-- Margin Tersedia --}}
            <div class="col-sm-6 col-lg-4 col-xxl-3">
                <div class="card border-0 shadow-sm h-100 stats-card"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Klik untuk melihat detail"
                    style="border-left: 4px solid #71dd37 !important; cursor:pointer;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 rounded-3 p-2 d-flex align-items-center justify-content-center bg-success bg-opacity-10" style="width:48px;height:48px;">
                            <i class="ri-wallet-3-line text-success fs-5"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="text-muted fw-semibold" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Margin Tersedia</div>
                            <h5 class="fw-bold mb-0 text-success" id="margin_tersedia">0</h5>
                        </div>
                        <i class="ri-arrow-right-s-line text-muted"></i>
                    </div>
                </div>
            </div>

            {{-- Margin Ditahan --}}
            <div class="col-sm-6 col-lg-4 col-xxl-3">
                <div class="card border-0 shadow-sm h-100 stats-card"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Klik untuk melihat detail"
                    style="border-left: 4px solid #fd7e14 !important; cursor:pointer;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 rounded-3 p-2 d-flex align-items-center justify-content-center bg-warning bg-opacity-10" style="width:48px;height:48px;">
                            <i class="ri-lock-2-line text-warning fs-5"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="text-muted fw-semibold" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Margin Ditahan</div>
                            <h5 class="fw-bold mb-0 text-warning" id="margin_ditahan">0</h5>
                        </div>
                        <i class="ri-arrow-right-s-line text-muted"></i>
                    </div>
                </div>
            </div>

            {{-- Total Margin --}}
            <div class="col-sm-6 col-lg-4 col-xxl-3">
                <div class="card border-0 shadow-sm h-100 stats-card"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Klik untuk melihat detail"
                    style="border-left: 4px solid #71dd37 !important; cursor:pointer;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 rounded-3 p-2 d-flex align-items-center justify-content-center bg-success bg-opacity-10" style="width:48px;height:48px;">
                            <i class="ri-funds-line text-success fs-5"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="text-muted fw-semibold" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Total Margin</div>
                            <h5 class="fw-bold mb-0 text-success" id="total_margin">0</h5>
                        </div>
                        <i class="ri-arrow-right-s-line text-muted"></i>
                    </div>
                </div>
            </div>

            {{-- Sisa Margin --}}
            <div class="col-sm-6 col-lg-4 col-xxl-3">
                <div class="card border-0 shadow-sm h-100 stats-card"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Klik untuk melihat detail"
                    style="border-left: 4px solid #03c3ec !important; cursor:pointer;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 rounded-3 p-2 d-flex align-items-center justify-content-center bg-info bg-opacity-10" style="width:48px;height:48px;">
                            <i class="ri-bar-chart-grouped-line text-info fs-5"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="text-muted fw-semibold" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Sisa Margin</div>
                            <h5 class="fw-bold mb-0 text-info" id="sisa_margin">0</h5>
                        </div>
                        <i class="ri-arrow-right-s-line text-muted"></i>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection