@extends('layouts/contentNavbarLayout')

@section('title', 'Profil')

@section('content')
<div class="content-wrapper" bis_skin_checked="1">

    <!-- Content -->
    <div class="container-fluid flex-grow-1 container-p-y" bis_skin_checked="1">

        <!-- Header -->
        <div class="row" bis_skin_checked="1">
            <div class="col-12" bis_skin_checked="1">
                <div class="card mb-6" bis_skin_checked="1">
                    <div class="user-profile-header-banner" bis_skin_checked="1">
                        <img src="https://demos.themeselection.com/materio-bootstrap-html-laravel-admin-template/demo/assets/img/pages/profile-banner.png" alt="Banner image" class="rounded-top">
                    </div>
                    <div class="user-profile-header d-flex flex-column flex-lg-row text-sm-start text-center mb-4" bis_skin_checked="1">
                        <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto" bis_skin_checked="1">
                            <img src="https://demos.themeselection.com/materio-bootstrap-html-laravel-admin-template/demo/assets/img/avatars/1.png" alt="user image" class="d-block h-auto ms-0 ms-sm-5 rounded user-profile-img">
                        </div>
                        <div class="flex-grow-1 mt-3 mt-lg-5" bis_skin_checked="1">
                            <div class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-5 flex-md-row flex-column gap-4" bis_skin_checked="1">
                                <div class="user-profile-info" bis_skin_checked="1">
                                    <h4 class="mb-2 mt-lg-6">{{$user->user_name}}</h4>
                                    <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4">
                                        <li class="list-inline-item"><i class="icon-base ri ri-id-card-line me-2 icon-24px"></i><span class="fw-medium">{{$user->role->role_name}}</span></li>
                                        <li class="list-inline-item"><i class="icon-base ri ri-calendar-line me-2 icon-24px"></i><span class="fw-medium"> Joined {{\Carbon\Carbon::parse($user->input_date)->format('M Y')}}</span></li>
                                    </ul>
                                </div>
                                <a href="javascript:void(0)" class="btn btn-primary waves-effect waves-light"> <i class="icon-base ri ri-user-follow-line icon-16px me-1_5"></i>Connected </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Header -->

        <!-- Navbar pills -->
        <div class="row" bis_skin_checked="1">
            <div class="col-md-12" bis_skin_checked="1">
                <div class="nav-align-top" bis_skin_checked="1">
                    <ul class="nav nav-pills flex-column flex-sm-row mb-6 gap-2 gap-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active waves-effect waves-light" href="javascript:void(0);"><i class="icon-base ri ri-user-3-line icon-sm me-1_5"></i>Profile</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!--/ Navbar pills -->

        <!-- User Profile Content -->
        <div class="row" bis_skin_checked="1">
            <div class="col-xl-4 col-lg-5 col-md-5" bis_skin_checked="1">
                <!-- About User -->
                <div class="card mb-6" bis_skin_checked="1">
                    <div class="card-body" bis_skin_checked="1">
                        <small class="card-text text-uppercase text-body-secondary small">About</small>
                        <ul class="list-unstyled my-3 py-1">
                            <li class="d-flex align-items-center mb-4"><i class="icon-base ri ri-user-3-line icon-24px"></i><span class="fw-medium mx-2">Full Name:</span> <span>{{$user->user_name}}</span></li>
                            <li class="d-flex align-items-center mb-4"><i class="icon-base ri ri-check-line icon-24px"></i><span class="fw-medium mx-2">Status:</span>
                                @if ($user->is_active == 1)
                                <span class="badge bg-success">Aktif</span>
                                @elseif($user->is_active == 0)
                                <span class="badge bg-danger">Nonaktif</span>
                                @endif
                            </li>
                            <li class="d-flex align-items-center mb-4"><i class="icon-base ri ri-star-smile-line icon-24px"></i><span class="fw-medium mx-2">Role:</span> <span>{{$user->role->role_name}}</span></li>
                        </ul>
                    </div>
                </div>
                <!--/ About User -->

            </div>
            <div class="col-xl-8 col-lg-7 col-md-7" bis_skin_checked="1">
                <!-- Activity Timeline -->
                <div class="card card-action mb-6" bis_skin_checked="1">
                    <div class="card-header align-items-center" bis_skin_checked="1">
                        <h5 class="card-action-title mb-0"><i class="icon-base ri ri-bar-chart-2-line icon-24px text-body me-4"></i>Aktifitas</h5>
                    </div>
                    <div class="card-body pt-3" bis_skin_checked="1">
                        <ul class="timeline card-timeline mb-0 list-group">
                            @foreach ($user->activities as $activity)
                            <li class="timeline-item timeline-item-transparent list-group-item">
                                <div class="timeline-event">
                                    <div class="timeline-header mb-2">
                                        <h6 class="mb-1">{{ $activity->description }}</h6>

                                        @if($activity->new_data && $activity->model)
                                        @php $data = json_decode($activity->new_data, true); @endphp
                                        <div class="table-responsive my-2">
                                            <table class="table table-sm table-bordered mb-0">
                                                @foreach($data as $key => $value)
                                                <tr>
                                                    <th class="bg-light" width="30%">{{ ucfirst(str_replace('_', ' ', $key)) }}</th>
                                                    <td class="bg-dark text-white">
                                                        @if(is_array($value))
                                                        {{ json_encode($value) }}
                                                        @else
                                                        {{ $value }}
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </table>
                                        </div>
                                        @elseif($activity->new_data)
                                        @php $data = json_decode($activity->new_data, true); @endphp
                                        <div class="d-flex flex-wrap gap-1 my-2">
                                            @foreach($data as $key => $value)
                                            <span class="badge bg-secondary">{{ $key }}: {{ $value }}</span>
                                            @endforeach
                                        </div>
                                        @endif

                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">{{ $activity->created_at->format('d M Y H:i') }}</small>
                                            <small class="text-body-secondary">{{ $activity->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!--/ User Profile Content -->

    </div>
    <!-- / Content -->

</div>
@endsection