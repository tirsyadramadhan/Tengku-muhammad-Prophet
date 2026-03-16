@php
use Illuminate\Support\Facades\Auth;
$defaultAvatar = public_path('defaults/default-avatar.jpg');
$picturePath = Auth::user()->profile_picture
? public_path(Auth::user()->profile_picture)
: $defaultAvatar;
$profilePic = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($picturePath));
$authUser = Auth::user();
@endphp

{{-- Brand --}}
@if(isset($navbarFull))
<div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-6">
    <a href="{{ url('/') }}" class="app-brand-link gap-2">
        <span class="app-brand-logo demo">@include('_partials.macros')</span>
        <span class="app-brand-text demo menu-text fw-bold">{{ config('variables.templateName') }}</span>
    </a>
</div>
@endif

{{-- Sidebar Toggle --}}
@if(!isset($navbarHideToggle))
<div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 ms-4">
    <a id="sidebar-toggle" class="nav-item nav-link px-0 me-xl-6 cursor-pointer">
        <i class="icon-base ri ri-menu-line icon-md"></i>
    </a>
</div>
@endif

<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    <ul class="navbar-nav flex-row align-items-center ms-auto">

        {{-- ── User Dropdown ──────────────────────────────── --}}
        <li class="nav-item navbar-dropdown dropdown-user dropdown">

            {{-- Trigger --}}
            <a class="nav-link dropdown-toggle hide-arrow p-0"
                href="javascript:void(0);"
                data-bs-toggle="dropdown"
                data-bs-offset="0,8"
                aria-expanded="false">
                <div class="avatar avatar-online">
                    <img src="{{ $profilePic }}"
                        alt="{{ $authUser->user_name }}"
                        class="rounded-circle object-fit-cover"
                        width="38" height="38">
                </div>
            </a>

            {{-- Dropdown Menu --}}
            <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width:240px;">

                {{-- User Info Header --}}
                <li>
                    <div class="dropdown-item pe-none py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-online flex-shrink-0">
                                <img src="{{ $profilePic }}"
                                    alt="{{ $authUser->user_name }}"
                                    class="rounded-circle object-fit-cover"
                                    width="42" height="42">
                            </div>
                            <div class="overflow-hidden">
                                <h6 class="mb-0 fw-bold text-truncate">
                                    {{ $authUser->user_name }}
                                </h6>
                                <small class="text-muted d-flex align-items-center gap-1">
                                    <i class="ri-shield-user-line"></i>
                                    {{ $authUser->role->role_name }}
                                </small>
                            </div>
                            @if($authUser->is_active == 1)
                            <span class="badge bg-success rounded-pill ms-auto flex-shrink-0">Aktif</span>
                            @else
                            <span class="badge bg-danger rounded-pill ms-auto flex-shrink-0">Nonaktif</span>
                            @endif
                        </div>
                    </div>
                </li>

                <li>
                    <hr class="dropdown-divider my-1">
                </li>

                {{-- Email Row --}}
                <li>
                    <div class="dropdown-item pe-none py-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-mail-line text-muted fs-6 flex-shrink-0"></i>
                            <small class="text-muted text-truncate">{{ $authUser->email }}</small>
                        </div>
                    </div>
                </li>

                {{-- Last Login Row --}}
                @if($authUser->last_login)
                <li>
                    <div class="dropdown-item pe-none py-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-time-line text-muted fs-6 flex-shrink-0"></i>
                            <small class="text-muted">
                                Login: {{ \Carbon\Carbon::parse($authUser->last_login)->format('d M Y, H:i') }}
                            </small>
                        </div>
                    </div>
                </li>
                @endif

                <li>
                    <hr class="dropdown-divider my-1">
                </li>

                {{-- My Profile --}}
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-3 py-2"
                        href="{{ route('users.profile', Auth::id()) }}">
                        <div class="avatar avatar-xs flex-shrink-0">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                <i class="ri-user-3-line small"></i>
                            </span>
                        </div>
                        <div>
                            <p class="mb-0 fw-medium small">My Profile</p>
                            <small class="text-muted">Lihat & edit profil</small>
                        </div>
                    </a>
                </li>

                <li>
                    <hr class="dropdown-divider my-1">
                </li>

                {{-- Logout --}}
                <li>
                    <div class="px-3 pt-1 pb-2">
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>

                        <a class="btn btn-danger d-flex" href="javascript:void(0);" id="logout-btn">
                            <small class="align-middle">Logout</small>
                            <i class="ri ri-logout-box-r-line ms-2 icon-xs"></i>
                        </a>
                    </div>
                </li>

            </ul>
        </li>
        {{-- ── /User Dropdown ─────────────────────────────── --}}

    </ul>
</div>