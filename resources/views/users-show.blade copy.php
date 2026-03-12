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
                                <img class="img-fluid rounded mb-4" src="https://demos.themeselection.com/materio-bootstrap-html-laravel-admin-template/demo/assets/img/avatars/10.png" height="120" width="120" alt="User avatar">
                                <div class="user-info text-center" bis_skin_checked="1">
                                    <h5>{{$user->user_name}}</h5>
                                    <span class="badge bg-label-danger rounded-pill">{{ $user->role->role_name }}</span>
                                </div>
                            </div>
                        </div>
                        <h5 class="pb-4 border-bottom mb-4">Details</h5>
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
                                <a href="javascript:;" class="btn btn-primary me-4 waves-effect waves-light" data-bs-target="#editUser" data-bs-toggle="modal">Edit</a>
                                <a href="javascript:;" class="btn btn-outline-danger suspend-user waves-effect">Nonaktifkan</a>
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
                <div class="card">
                    @foreach ($user->activities as $activity)
                    <p>
                        @php
                        $json_object = $activity->new_data;
                        echo($json_object);
                        @endphp
                    </p>
                    @endforeach
                </div>
                <div class="card mb-6" bis_skin_checked="1">
                    <h5 class="card-header">Aktifitas Pengguna</h5>
                    <div class="card-body pt-0" bis_skin_checked="1">
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
                <!-- /Activity Timeline -->
            </div>
            <!--/ User Content -->
        </div>

        <!-- Modal -->
        <!-- Edit User Modal -->
        <div class="modal fade" id="editUser" tabindex="-1" aria-hidden="true" bis_skin_checked="1">
            <div class="modal-dialog modal-lg modal-simple modal-edit-user" bis_skin_checked="1">
                <div class="modal-content" bis_skin_checked="1">
                    <div class="modal-body p-0" bis_skin_checked="1">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="text-center mb-6" bis_skin_checked="1">
                            <h4 class="mb-2">Ubah Informasi Pengguna</h4>
                        </div>
                        <form id="editUserForm" class="row g-5" onsubmit="return false">
                            <div class="col-12" bis_skin_checked="1">
                                <div class="form-floating form-floating-outline" bis_skin_checked="1">
                                    <input type="text" id="modalEditUserName" name="modalEditUserName" class="form-control" value="{{$user->user_name}}" placeholder="{{$user->user_name}}">
                                    <label for="modalEditUserName">Username</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6" bis_skin_checked="1">
                                <div class="form-floating form-floating-outline" bis_skin_checked="1">
                                    <input type="text" id="modalEditUserEmail" name="modalEditUserEmail" class="form-control" value="{{$user->email}}" placeholder="{{$user->email}}">
                                    <label for="modalEditUserEmail">Email</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6" bis_skin_checked="1">
                                <div class="form-floating form-floating-outline" bis_skin_checked="1">
                                    <select id="modalEditUserStatus" name="modalEditUserStatus" class="form-select" aria-label="Default select example">
                                        <option value="1" selected="">Aktif</option>
                                        <option value="0">Nonaktif</option>
                                    </select>
                                    <label for="modalEditUserStatus">Status</label>
                                </div>
                            </div>
                            <div class="col-12 text-center" bis_skin_checked="1">
                                <button type="submit" class="btn btn-primary me-3 waves-effect waves-light">Submit</button>
                                <button type="reset" class="btn btn-outline-secondary waves-effect" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Edit User Modal --><!-- Add New Credit Card Modal -->
        <div class="modal fade" id="upgradePlanModal" tabindex="-1" aria-hidden="true" bis_skin_checked="1">
            <div class="modal-dialog modal-dialog-centered modal-simple modal-upgrade-plan" bis_skin_checked="1">
                <div class="modal-content" bis_skin_checked="1">
                    <div class="modal-body pt-md-0 px-0" bis_skin_checked="1">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="text-center mb-6" bis_skin_checked="1">
                            <h4 class="mb-2">Upgrade Plan</h4>
                            <p>Choose the best plan for user.</p>
                        </div>
                        <form id="upgradePlanForm" class="row g-5 d-flex align-items-center" onsubmit="return false">
                            <div class="col-sm-9" bis_skin_checked="1">
                                <select id="choosePlan" name="choosePlan" class="form-select form-select-sm" aria-label="Choose Plan">
                                    <option selected="">Choose Plan</option>
                                    <option value="standard">Standard - $99/month</option>
                                    <option value="exclusive">Exclusive - $249/month</option>
                                    <option value="Enterprise">Enterprise - $499/month</option>
                                </select>
                            </div>
                            <div class="col-sm-3 d-flex align-items-end" bis_skin_checked="1">
                                <button type="submit" class="btn btn-primary waves-effect waves-light">Upgrade</button>
                            </div>
                        </form>
                    </div>
                    <hr class="mx-md-n5 mx-n3">
                    <div class="modal-body pb-md-0 px-0" bis_skin_checked="1">
                        <p class="mb-0">User current plan is standard plan</p>
                        <div class="d-flex justify-content-between align-items-center flex-wrap" bis_skin_checked="1">
                            <div class="d-flex justify-content-center me-2 mt-3" bis_skin_checked="1">
                                <sup class="h5 pricing-currency pt-1 mt-2 mb-0 me-1 text-primary">$</sup>
                                <h1 class="display-3 mb-0 text-primary">99</h1>
                                <sub class="h6 pricing-duration mt-auto mb-2 pb-1 text-body">/month</sub>
                            </div>
                            <button class="btn btn-outline-danger cancel-subscription mt-4 waves-effect">Cancel Subscription</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Add New Credit Card Modal --><!-- /Modal -->

    </div>
    <!-- / Content -->
</div>
@endsection