@extends('layouts/contentNavbarLayout')

@section('title', 'Users')

@section('content')
<div class="card-body pt-12" bis_skin_checked="1">
    <div class="user-avatar-section" bis_skin_checked="1">
        <div class=" d-flex align-items-center flex-column" bis_skin_checked="1">
            <img class="img-fluid rounded mb-4" src="https://demos.themeselection.com/materio-bootstrap-html-laravel-admin-template/demo/assets/img/avatars/10.png" height="120" width="120" alt="User avatar">
            <div class="user-info text-center" bis_skin_checked="1">
                <h5>{{$user->user_name}}</h5>
                <span class="badge bg-label-danger rounded-pill">{{$user->role->role_name}}</span>
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
                <span class="badge bg-label-success rounded-pill">{{$user->is_active}}</span>
            </li>
            <li class="mb-2">
                <span class="h6">Role:</span>
                <span>{{$user->role->role_name}}</span>
            </li>
        </ul>
        <div class="d-flex justify-content-center" bis_skin_checked="1">
            <a href="javascript:;" class="btn btn-primary me-4 waves-effect waves-light" data-bs-target="#editUser" data-bs-toggle="modal">Edit</a>
            <a href="javascript:;" class="btn btn-outline-danger suspend-user waves-effect">Suspend</a>
        </div>
    </div>
</div>@endsection