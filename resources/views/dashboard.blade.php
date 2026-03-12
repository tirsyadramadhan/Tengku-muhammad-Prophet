@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" id="dashboard-main-container">
    <div class="row mb-4 g-4">
        <h3>Rekening</h3>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xxl-3">
            <div class="card stat-card shadow-sm stats-card" style="border-left-color: #71dd37;" id="dana-tersedia-card"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Klik untuk melihat detail">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="dana-tersedia">0</h4>
                            <small class="text-muted">Dana Tersedia</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102,16,242,0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#893600" d="M22.005 6h-7a6 6 0 0 0 0 12h7v2a1 1 0 0 1-1 1h-18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h18a1 1 0 0 1 1 1zm-7 2h8v8h-8a4 4 0 1 1 0-8m0 3v2h3v-2z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xxl-3">
            <div class="card stat-card shadow-sm stats-card" style="border-left-color: #71dd37;"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Klik untuk melihat detail">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="investasi-dikembalikan">0</h4>
                            <small class="text-muted">Investasi Dikembalikan</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102,16,242,0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#E1B530" d="M9.335 11.502h2.17a4.5 4.5 0 0 1 4.5 4.5H9.004v1h8v-1a5.6 5.6 0 0 0-.885-3h2.886a5 5 0 0 1 4.516 2.852c-2.365 3.12-6.194 5.149-10.516 5.149c-2.761 0-5.1-.59-7-1.625v-9.304a6.97 6.97 0 0 1 3.33 1.428m-4.33 7.5a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-9a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1zm13-14a3 3 0 1 1 0 6a3 3 0 0 1 0-6m-7-3a3 3 0 1 1 0 6a3 3 0 0 1 0-6" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xxl-3">
            <div class="card stat-card shadow-sm stats-card" style="border-left-color: #71dd37;"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Klik untuk melihat detail">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="totalInvestasiTransfer">0</h4>
                            <small class="text-muted">Total Investasi Transfer</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102,16,242,0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#E1B530" d="M14.005 2.003a8 8 0 0 1 3.292 15.293A8 8 0 1 1 6.711 6.71a8 8 0 0 1 7.294-4.707m-3 7h-2v1a2.5 2.5 0 0 0-.164 4.995l.164.005h2l.09.008a.5.5 0 0 1 0 .984l-.09.008h-4v2h2v1h2v-1a2.5 2.5 0 0 0 .164-4.995l-.164-.005h-2l-.09-.008a.5.5 0 0 1 0-.984l.09-.008h4v-2h-2zm3-5A6 6 0 0 0 9.52 6.016a8 8 0 0 1 8.47 8.471a6 6 0 0 0-3.986-10.484" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 g-4">
        <h3>Pemasukan</h3>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xxl-3">
            <div class="card stat-card shadow-sm stats-card" style="border-left-color: #71dd37;"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Klik untuk melihat detail">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="total-tf-investasi">0</h4>
                            <small class="text-muted">Total TF Investasi</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102,16,242,0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#20d420" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m0-2a8 8 0 1 0 0-16a8 8 0 0 0 0 16m-3.5-6h5.5a.5.5 0 1 0 0-1h-4a2.5 2.5 0 1 1 0-5h1v-2h2v2h2.5v2h-5.5a.5.5 0 0 0 0 1h4a2.5 2.5 0 0 1 0 5h-1v2h-2v-2h-2.5z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xxl-3">
            <div class="card stat-card shadow-sm stats-card" style="border-left-color: #71dd37;"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Klik untuk melihat detail">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="margin-diterima">0</h4>
                            <small class="text-muted">Margin Diterima</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102,16,242,0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#20d420" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m0-2a8 8 0 1 0 0-16a8 8 0 0 0 0 16m-3.5-6h5.5a.5.5 0 1 0 0-1h-4a2.5 2.5 0 1 1 0-5h1v-2h2v2h2.5v2h-5.5a.5.5 0 0 0 0 1h4a2.5 2.5 0 0 1 0 5h-1v2h-2v-2h-2.5z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xxl-3">
            <div class="card stat-card shadow-sm stats-card" style="border-left-color: #71dd37;"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Klik untuk melihat detail">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="total-margin">0</h4>
                            <small class="text-muted">Total Margin</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102,16,242,0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#20d420" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m0-2a8 8 0 1 0 0-16a8 8 0 0 0 0 16m-3.5-6h5.5a.5.5 0 1 0 0-1h-4a2.5 2.5 0 1 1 0-5h1v-2h2v2h2.5v2h-5.5a.5.5 0 0 0 0 1h4a2.5 2.5 0 0 1 0 5h-1v2h-2v-2h-2.5z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xxl-3">
            <div class="card stat-card shadow-sm stats-card" style="border-left-color: #71dd37;"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Klik untuk melihat detail">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="sisa-margin">0</h4>
                            <small class="text-muted">Sisa Margin</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102,16,242,0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#20d420" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m0-2a8 8 0 1 0 0-16a8 8 0 0 0 0 16m-3.5-6h5.5a.5.5 0 1 0 0-1h-4a2.5 2.5 0 1 1 0-5h1v-2h2v2h2.5v2h-5.5a.5.5 0 0 0 0 1h4a2.5 2.5 0 0 1 0 5h-1v2h-2v-2h-2.5z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xxl-3">
            <div class="card stat-card shadow-sm stats-card" style="border-left-color: #71dd37;"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Klik untuk melihat detail">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="margin-tersedia">0</h4>
                            <small class="text-muted">Margin Tersedia</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102,16,242,0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#20d420" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m0-2a8 8 0 1 0 0-16a8 8 0 0 0 0 16m-3.5-6h5.5a.5.5 0 1 0 0-1h-4a2.5 2.5 0 1 1 0-5h1v-2h2v2h2.5v2h-5.5a.5.5 0 0 0 0 1h4a2.5 2.5 0 0 1 0 5h-1v2h-2v-2h-2.5z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 g-4">
        <h3>Ditahan</h3>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xxl-3">
            <div class="card stat-card shadow-sm stats-card" style="border-left-color: #71dd37;"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Klik untuk melihat detail">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="investasi-ditahan">0</h4>
                            <small class="text-muted">Investasi Yang Ditahan</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102,16,242,0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#0c0cff" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m0-2a8 8 0 1 0 0-16a8 8 0 0 0 0 16m-3.5-6h5.5a.5.5 0 1 0 0-1h-4a2.5 2.5 0 1 1 0-5h1v-2h2v2h2.5v2h-5.5a.5.5 0 0 0 0 1h4a2.5 2.5 0 0 1 0 5h-1v2h-2v-2h-2.5z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 col-lg-4 col-xxl-3">
            <div class="card stat-card shadow-sm stats-card" style="border-left-color: #71dd37;"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Klik untuk melihat detail">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold" id="margin-ditahan">0</h4>
                            <small class="text-muted">Margin Ditahan</small>
                        </div>
                        <div class="avatar p-2 rounded" style="background-color: rgba(102,16,242,0.1);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#0c0cff" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m0-2a8 8 0 1 0 0-16a8 8 0 0 0 0 16m-3.5-6h5.5a.5.5 0 1 0 0-1h-4a2.5 2.5 0 1 1 0-5h1v-2h2v2h2.5v2h-5.5a.5.5 0 0 0 0 1h4a2.5 2.5 0 0 1 0 5h-1v2h-2v-2h-2.5z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection