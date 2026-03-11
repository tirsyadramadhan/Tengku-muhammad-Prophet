@php
$defaultAvatar = public_path('defaults/default-avatar.jpg');
$picturePath = $user->profile_picture
? public_path($user->profile_picture)
: $defaultAvatar;

$profilePic = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($picturePath));
@endphp
@extends('layouts/contentNavbarLayout')

@section('title', 'Users')

@section('content')
<div class="content-wrapper" bis_skin_checked="1">

    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y" bis_skin_checked="1">

        <div class="row" bis_skin_checked="1">
            <!-- User Sidebar -->
            <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0" bis_skin_checked="1">
                <!-- User Card -->
                <div class="card mb-6" bis_skin_checked="1">
                    <div class="card-body pt-12" bis_skin_checked="1">
                        <div class="user-avatar-section" bis_skin_checked="1">
                            <div class=" d-flex align-items-center flex-column" bis_skin_checked="1">
                                <img class="img-fluid rounded mb-4"
                                    src="{{ $profilePic }}"
                                    height="120" width="120" alt="User avatar">
                                <div class="user-info text-center" bis_skin_checked="1">
                                    <h5>{{$user->user_name}}</h5>
                                    <span class="badge bg-label-danger rounded-pill">{{ $user->role->role_name }}</span>
                                </div>
                            </div>
                        </div>
                        <h5 class="pb-4 mb-4">Details</h5>
                        <div class="info-container" bis_skin_checked="1">
                            <ul class="list-unstyled mb-6">
                                <li class="mb-2">
                                    <span class="h6">Username:</span>
                                    <span>{{$user->user_name}}</span>
                                </li>
                                <li class="mb-2">
                                    <span class="h6">Email:</span>
                                    <span>{{$user->email}}</span>
                                </li>
                                <li class="mb-2">
                                    <span class="h6">Status:</span>
                                    @if ($user->is_active == 1)
                                    <span class="badge bg-label-success rounded-pill">Aktif</span>
                                    @elseif($user->is_active == 0)
                                    <span class="badge bg-label-danger rounded-pill">Nonaktif</span>
                                    @endif
                                </li>
                                <li class="mb-2">
                                    <span class="h6">Role:</span>
                                    <span>{{ $user->role->role_name }}</span>
                                </li>
                            </ul>
                            <div class="d-flex justify-content-center" bis_skin_checked="1">
                                @if ($user->is_active == 0)
                                <button
                                    class="btn btn-outline-success activate-user waves-effect"
                                    id="activate-user"
                                    data-id="{{ $user->user_id }}"
                                    data-url="{{ route('users.activate', $user->user_id) }}"
                                    data-token="{{ csrf_token() }}">
                                    Aktifkan Akun
                                </button>
                                @endif
                                @if ($user->is_active == 1)
                                <button
                                    class="btn btn-outline-danger suspend-user waves-effect"
                                    id="suspend-user"
                                    data-id="{{ $user->user_id }}"
                                    data-url="{{ route('users.deactivate', $user->user_id) }}"
                                    data-token="{{ csrf_token() }}">
                                    Nonaktifkan Akun
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /User Card -->
            </div>
            <!--/ User Sidebar -->

            <!-- User Content -->
            <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1" bis_skin_checked="1">
                <!-- User Tabs -->
                <div class="nav-align-top" bis_skin_checked="1">
                    <ul class="nav nav-pills flex-column flex-md-row flex-wrap mb-6 row-gap-2">
                        <li class="nav-item">
                            <a class="nav-link active waves-effect waves-light" href="javascript:void(0);"><i class="icon-base ri ri-group-line icon-sm me-1_5"></i>Account</a>
                        </li>
                    </ul>
                </div>
                <!--/ User Tabs -->
                <!-- Activity Timeline -->
                <div class="card mb-6" bis_skin_checked="1">
                    <h5 class="card-header">Aktifitas Pengguna</h5>
                    <div class="card-body pt-0" bis_skin_checked="1">
                        <ul class="timeline card-timeline mb-0 list-group">
                            @foreach ($user->activities()->latest()->take(10)->get() as $activity)
                            <li class="timeline-item timeline-item-transparent list-group-item">
                                <div class="timeline-event">
                                    <div class="timeline-header mb-2">
                                        @if ($activity->action == "create" && $activity->model == "App\Models\Investasi")
                                        @php
                                        $data = json_decode($activity->new_data, true);
                                        $skip = ['tgl_investasi', 'id_investasi'];
                                        $labels = [
                                        'modal_setor_awal' => 'Modal Setor Awal',
                                        'modal_po_baru' => 'Modal PO Baru',
                                        'margin' => 'Margin',
                                        'pencairan_modal' => 'Pencairan Modal',
                                        'margin_cair' => 'Margin Cair',
                                        'pengembalian_dana' => 'Pengembalian Dana',
                                        'dana_tersedia' => 'Dana Tersedia',
                                        ];
                                        @endphp
                                        <h6 class="mb-1">Investasi ditambahkan dengan rincian: </h6>
                                        <ul class="list-group">
                                            @foreach ($data as $key => $value)
                                            @continue(in_array($key, $skip))
                                            <li class="list-group-item">
                                                {{ $labels[$key] ?? $key }}: <span class="{{ $value < 0 ? 'text-danger' : 'text-success' }}">
                                                    Rp {{ number_format($value, 0, ',', '.') }}
                                                </span>
                                            </li>
                                            @endforeach
                                        </ul>
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <small class="text-muted">{{ $activity->created_at->format('d M Y H:i') }}</small>
                                            <small class="text-body-secondary">{{ $activity->created_at->diffForHumans() }}</small>
                                        </div>
                                        @elseif ($activity->action == "create" && $activity->model == "App\Models\Po")
                                        @php
                                        $data = json_decode($activity->new_data, true);
                                        $skip = ['po_id', 'customer_id', 'tgl_po', 'status', 'input_by', 'input_date', 'edit_by', 'edit_date'];
                                        $labels = [
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
                                        @endphp
                                        <h6 class="mb-1">PO ditambahkan dengan rincian: </h6>
                                        <ul class="list-group">
                                            @foreach ($data as $key => $value)
                                            @continue(in_array($key, $skip))
                                            <li class="list-group-item">
                                                {{ $labels[$key] ?? $key }}:
                                                @if (in_array($key, $currencyFields))
                                                <span class="{{ $value < 0 ? 'text-danger' : 'text-success' }}">
                                                    Rp {{ number_format($value, 0, ',', '.') }}
                                                </span>
                                                @else
                                                <span>{{ $value ?? '-' }}</span>
                                                @endif
                                            </li>
                                            @endforeach
                                        </ul>
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <small class="text-muted">{{ $activity->created_at->format('d M Y H:i') }}</small>
                                            <small class="text-body-secondary">{{ $activity->created_at->diffForHumans() }}</small>
                                        </div>
                                        @else
                                        <h6 class="mb-1">{{ $activity->description }}</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">{{ $activity->created_at->format('d M Y H:i') }}</small>
                                            <small class="text-body-secondary">{{ $activity->created_at->diffForHumans() }}</small>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <!-- /Activity Timeline -->
            </div>
            <!--/ User Content -->
        </div>

    </div>
    <!-- / Content -->
</div>
@endsection