@php
$defaultAvatar = public_path('defaults/default-avatar.jpg');
$picturePath = $user->profile_picture
? public_path($user->profile_picture)
: $defaultAvatar;
$profilePic = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($picturePath));

$currencyFields = ['harga', 'total', 'modal_awal', 'margin', 'margin_unit', 'tambahan_margin'];

$actionMap = [
'create' => ['label' => 'Dibuat', 'class' => 'bg-success'],
'update' => ['label' => 'Diperbarui','class' => 'bg-warning'],
'delete' => ['label' => 'Dihapus', 'class' => 'bg-danger'],
'login' => ['label' => 'Login', 'class' => 'bg-info'],
];

$modelMap = [
'App\Models\Investasi' => ['label' => 'Investasi', 'icon' => 'ri-funds-line', 'color' => 'text-success'],
'App\Models\Po' => ['label' => 'PO', 'icon' => 'ri-file-list-3-line', 'color' => 'text-primary'],
'App\Models\Delivery' => ['label' => 'Pengiriman', 'icon' => 'ri-truck-line', 'color' => 'text-info'],
'App\Models\Invoice' => ['label' => 'Invoice', 'icon' => 'ri-bill-line', 'color' => 'text-warning'],
'App\Models\Payment' => ['label' => 'Pembayaran', 'icon' => 'ri-money-dollar-circle-line', 'color' => 'text-success'],
];

$investasiLabels = [
'modal_setor_awal' => 'Modal Setor Awal',
'modal_po_baru' => 'Modal PO Baru',
'margin' => 'Margin',
'pencairan_modal' => 'Pencairan Modal',
'margin_cair' => 'Margin Cair',
'pengembalian_dana' => 'Pengembalian Dana',
'dana_tersedia' => 'Dana Tersedia',
];

$poLabels = [
'no_po' => 'Nomor PO',
'nama_barang' => 'Nama Barang',
'qty' => 'Jumlah',
'harga' => 'Harga Per Unit',
'total' => 'Total Harga',
'modal_awal' => 'Modal',
'margin' => 'Margin',
'margin_unit' => 'Margin Per Unit',
'tambahan_margin' => 'Tambahan Margin',
];

$investasiSkip = ['tgl_investasi', 'id_investasi'];
$poSkip = ['po_id', 'customer_id', 'tgl_po', 'status', 'input_by', 'input_date', 'edit_by', 'edit_date'];

$activities = $user->activities()->latest()->take(10)->get();
@endphp

@extends('layouts/contentNavbarLayout')

@section('title', 'User — ' . $user->user_name)

@section('content')
<div id="main-container-index" class="container-fluid px-3 px-md-4 py-4">

    {{-- ── Breadcrumb + Header ──────────────────────────────── --}}
    <div class="row align-items-center g-3 mb-4">
        <div class="col-12 col-sm">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item">
                        <a href="{{ route('users.index') }}">Users</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ $user->user_name }}
                    </li>
                </ol>
            </nav>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <h4 class="fw-bold mb-0">Detail Pengguna</h4>
                @if($user->is_active == 1)
                <span class="badge bg-success rounded-pill">Aktif</span>
                @else
                <span class="badge bg-danger rounded-pill">Nonaktif</span>
                @endif
            </div>
        </div>
        @if(Auth::user()->role_id != 2)
        <div class="col-12 col-sm-auto d-flex gap-2">
            @if($user->is_active == 0)
            <button class="btn btn-outline-success activate-user"
                data-id="{{ $user->user_id }}"
                data-url="{{ route('users.activate', $user->user_id) }}"
                data-token="{{ csrf_token() }}">
                <i class="ri-user-follow-line me-1"></i>
                <span class="d-none d-sm-inline">Aktifkan Akun</span>
            </button>
            @else
            <button
                id="suspend-user-2"
                class="btn btn-outline-danger">
                <i class="ri-user-forbid-line me-1"></i>
                <span class="d-none d-sm-inline">Nonaktifkan Akun</span>
            </button>
            @endif
        </div>
        @endif
    </div>

    {{-- ── Stat Pills ───────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-none bg-label-primary h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="avatar avatar-sm flex-shrink-0">
                        <span class="avatar-initial rounded bg-primary">
                            <i class="ri-user-line"></i>
                        </span>
                    </div>
                    <div class="overflow-hidden">
                        <p class="mb-0 text-muted small">Username</p>
                        <h6 class="mb-0 fw-bold text-truncate">{{ $user->user_name }}</h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-none bg-label-info h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="avatar avatar-sm flex-shrink-0">
                        <span class="avatar-initial rounded bg-info">
                            <i class="ri-shield-user-line"></i>
                        </span>
                    </div>
                    <div class="overflow-hidden">
                        <p class="mb-0 text-muted small">Role</p>
                        <h6 class="mb-0 fw-bold text-truncate">{{ $user->role->role_name }}</h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-none bg-label-warning h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="avatar avatar-sm flex-shrink-0">
                        <span class="avatar-initial rounded bg-warning">
                            <i class="ri-time-line"></i>
                        </span>
                    </div>
                    <div class="overflow-hidden">
                        <p class="mb-0 text-muted small">Login Terakhir</p>
                        <h6 class="mb-0 fw-bold small">
                            {{ $user->last_login
                                ? \Carbon\Carbon::parse($user->last_login)->format('d M Y')
                                : 'Belum pernah' }}
                        </h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-none bg-label-secondary h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="avatar avatar-sm flex-shrink-0">
                        <span class="avatar-initial rounded bg-secondary">
                            <i class="ri-history-line"></i>
                        </span>
                    </div>
                    <div class="overflow-hidden">
                        <p class="mb-0 text-muted small">Total Aktivitas</p>
                        <h6 class="mb-0 fw-bold">{{ $user->activities()->count() }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Main Layout ──────────────────────────────────────── --}}
    <div class="row g-4">

        {{-- ── Left Sidebar ────────────────────────────────── --}}
        <div class="col-12 col-lg-4 order-1 order-lg-0">

            {{-- Profile Card --}}
            <div class="card mb-4 overflow-hidden">
                <div class="bg-label-primary" style="height:80px;"></div>
                <div class="card-body pt-0 text-center" style="margin-top:-50px;">
                    <img src="{{ $profilePic }}"
                        alt="{{ $user->user_name }}"
                        width="100" height="100"
                        class="rounded-circle border border-4 border-white shadow-sm object-fit-cover mb-3">
                    <h5 class="fw-bold mb-1">{{ $user->user_name }}</h5>
                    <span class="badge bg-label-primary rounded-pill mb-3">
                        {{ $user->role->role_name }}
                    </span>
                    @if(Auth::user()->role_id != 2)
                    <div class="d-grid mt-3">
                        @if($user->is_active == 0)
                        <button class="btn btn-outline-success btn-sm activate-user"
                            data-id="{{ $user->user_id }}"
                            data-url="{{ route('users.activate', $user->user_id) }}"
                            data-token="{{ csrf_token() }}">
                            <i class="ri-user-follow-line me-1"></i> Aktifkan Akun
                        </button>
                        @else
                        <button
                            id="suspend-user"
                            class="btn btn-outline-danger btn-sm"
                            data-id="{{ $user->user_id }}"
                            data-url="{{ route('users.deactivate', $user->user_id) }}"
                            data-token="{{ csrf_token() }}">
                            <i class="ri-user-forbid-line me-1"></i> Nonaktifkan Akun
                        </button>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- Details --}}
            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="ri-file-user-line me-2 text-primary"></i>Detail Akun
                    </h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-4 py-3 d-flex align-items-center gap-3">
                            <i class="ri-user-3-line text-primary fs-5 flex-shrink-0"></i>
                            <div class="overflow-hidden">
                                <p class="mb-0 text-muted small">Username</p>
                                <p class="mb-0 fw-semibold text-truncate">{{ $user->user_name }}</p>
                            </div>
                        </li>
                        <li class="list-group-item px-4 py-3 d-flex align-items-center gap-3">
                            <i class="ri-mail-line text-info fs-5 flex-shrink-0"></i>
                            <div class="overflow-hidden">
                                <p class="mb-0 text-muted small">Email</p>
                                <p class="mb-0 fw-semibold text-truncate">{{ $user->email }}</p>
                            </div>
                        </li>
                        <li class="list-group-item px-4 py-3 d-flex align-items-center gap-3">
                            <i class="ri-shield-user-line text-warning fs-5 flex-shrink-0"></i>
                            <div>
                                <p class="mb-0 text-muted small">Role</p>
                                <p class="mb-0 fw-semibold">{{ $user->role->role_name }}</p>
                            </div>
                        </li>
                        <li class="list-group-item px-4 py-3 d-flex align-items-center gap-3">
                            <i class="ri-checkbox-circle-line text-success fs-5 flex-shrink-0"></i>
                            <div>
                                <p class="mb-0 text-muted small">Status</p>
                                @if($user->is_active == 1)
                                <span class="badge bg-success rounded-pill">Aktif</span>
                                @else
                                <span class="badge bg-danger rounded-pill">Nonaktif</span>
                                @endif
                            </div>
                        </li>
                        <li class="list-group-item px-4 py-3 d-flex align-items-center gap-3">
                            <i class="ri-calendar-check-line text-secondary fs-5 flex-shrink-0"></i>
                            <div>
                                <p class="mb-0 text-muted small">Bergabung</p>
                                <p class="mb-0 fw-semibold">
                                    {{ \Carbon\Carbon::parse($user->input_date)->format('d M Y') }}
                                </p>
                            </div>
                        </li>
                        @if($user->last_login)
                        <li class="list-group-item px-4 py-3 d-flex align-items-center gap-3">
                            <i class="ri-login-circle-line text-info fs-5 flex-shrink-0"></i>
                            <div>
                                <p class="mb-0 text-muted small">Login Terakhir</p>
                                <p class="mb-0 fw-semibold">
                                    {{ \Carbon\Carbon::parse($user->last_login)->format('d M Y, H:i') }}
                                </p>
                            </div>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            {{-- System Log --}}
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="ri-information-line me-2 text-secondary"></i>Log Sistem
                    </h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @if($user->input_by)
                        <li class="list-group-item px-4 py-3">
                            <p class="mb-0 text-muted small">Dibuat Oleh</p>
                            <p class="mb-0 fw-semibold small">User #{{ $user->input_by }}</p>
                        </li>
                        @endif
                        <li class="list-group-item px-4 py-3">
                            <p class="mb-0 text-muted small">Dibuat Pada</p>
                            <p class="mb-0 fw-semibold small">
                                {{ \Carbon\Carbon::parse($user->input_date)->format('d M Y, H:i') }}
                            </p>
                        </li>
                        @if($user->edit_by)
                        <li class="list-group-item px-4 py-3">
                            <p class="mb-0 text-muted small">Terakhir Diperbarui</p>
                            <p class="mb-0 fw-semibold small">
                                {{ \Carbon\Carbon::parse($user->edit_date)->format('d M Y, H:i') }}
                            </p>
                            <small class="text-muted">oleh User #{{ $user->edit_by }}</small>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

        </div>

        {{-- ── Right: Activity Feed ─────────────────────────── --}}
        <div class="col-12 col-lg-8 order-0 order-lg-1">
            <div class="card">
                <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="card-title mb-0">
                        <i class="ri-bar-chart-2-line me-2 text-primary"></i>Aktivitas Pengguna
                    </h5>
                    <span class="badge bg-label-secondary rounded-pill">10 Terbaru</span>
                </div>

                <div class="card-body p-0">
                    @if($activities->isEmpty())
                    <div class="text-center py-5">
                        <div class="avatar avatar-lg bg-label-secondary mx-auto mb-3">
                            <span class="avatar-initial rounded-circle">
                                <i class="ri-history-line fs-4"></i>
                            </span>
                        </div>
                        <p class="text-muted mb-0">Belum ada aktivitas tercatat.</p>
                    </div>
                    @else
                    <ul class="list-group list-group-flush">
                        @foreach($activities as $activity)
                        @php
                        $aMap = $actionMap[$activity->action] ?? ['label' => ucfirst($activity->action), 'class' => 'bg-secondary'];
                        $mMap = $modelMap[$activity->model] ?? ['label' => $activity->model ?? 'Sistem', 'icon' => 'ri-code-line', 'color' => 'text-secondary'];
                        $isInvestasi = $activity->action === 'create' && $activity->model === 'App\Models\Investasi';
                        $isPo = $activity->action === 'create' && $activity->model === 'App\Models\Po';
                        @endphp
                        <li class="list-group-item px-4 py-3">
                            <div class="d-flex gap-3">

                                {{-- Icon --}}
                                <div class="flex-shrink-0 mt-1">
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                            <i class="{{ $mMap['icon'] }} {{ $mMap['color'] }}"></i>
                                        </span>
                                    </div>
                                </div>

                                {{-- Content --}}
                                <div class="flex-grow-1 overflow-hidden">

                                    {{-- Badges + timestamp --}}
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <span class="badge {{ $aMap['class'] }} rounded-pill">
                                                {{ $aMap['label'] }}
                                            </span>
                                            <span class="badge bg-label-secondary rounded-pill">
                                                <i class="{{ $mMap['icon'] }} me-1"></i>{{ $mMap['label'] }}
                                            </span>
                                            @if($activity->model_id)
                                            <span class="badge bg-label-dark rounded-pill small">
                                                #{{ $activity->model_id }}
                                            </span>
                                            @endif
                                        </div>
                                        <small class="text-muted text-nowrap">
                                            {{ $activity->created_at->diffForHumans() }}
                                        </small>
                                    </div>

                                    {{-- Investasi table --}}
                                    @if($isInvestasi)
                                    @php $data = json_decode($activity->new_data, true); @endphp
                                    <p class="mb-2 fw-semibold small">Investasi ditambahkan dengan rincian:</p>
                                    <div class="table-responsive rounded border">
                                        <table class="table table-sm table-dark mb-0">
                                            <tbody>
                                                @foreach($data as $key => $value)
                                                @continue(in_array($key, $investasiSkip))
                                                <tr>
                                                    <td class="small text-nowrap py-1 px-3" style="width:1%">
                                                        {{ $investasiLabels[$key] ?? $key }}
                                                    </td>
                                                    <td class="small fw-semibold text-nowrap py-1 px-3">
                                                        <span class="{{ $value < 0 ? 'text-danger' : 'text-success' }}">
                                                            Rp {{ number_format($value, 0, ',', '.') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    {{-- PO table --}}
                                    @elseif($isPo)
                                    @php $data = json_decode($activity->new_data, true); @endphp
                                    <p class="mb-2 fw-semibold small">PO ditambahkan dengan rincian:</p>
                                    <div class="table-responsive rounded border">
                                        <table class="table table-sm table-dark mb-0">
                                            <tbody>
                                                @foreach($data as $key => $value)
                                                @continue(in_array($key, $poSkip))
                                                <tr>
                                                    <td class="small text-nowrap py-1 px-3" style="width:1%">
                                                        {{ $poLabels[$key] ?? $key }}
                                                    </td>
                                                    <td class="small fw-semibold text-nowrap py-1 px-3">
                                                        @if(in_array($key, $currencyFields))
                                                        <span class="{{ $value < 0 ? 'text-danger' : 'text-success' }}">
                                                            Rp {{ number_format($value, 0, ',', '.') }}
                                                        </span>
                                                        @else
                                                        <span>{{ $value ?? '-' }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    {{-- Generic --}}
                                    @else
                                    <p class="mb-2 small text-muted">{{ $activity->description }}</p>
                                    @if($activity->ip_address)
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge bg-label-dark rounded-pill small">
                                            <i class="ri-global-line me-1"></i>{{ $activity->ip_address }}
                                        </span>
                                        @if($activity->method)
                                        <span class="badge bg-label-info rounded-pill small">
                                            {{ $activity->method }}
                                        </span>
                                        @endif
                                    </div>
                                    @endif
                                    @endif

                                    {{-- Timestamp --}}
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="ri-calendar-line me-1"></i>
                                            {{ $activity->created_at->format('d M Y, H:i') }}
                                        </small>
                                    </div>

                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection