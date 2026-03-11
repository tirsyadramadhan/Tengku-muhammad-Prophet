@extends('layouts/blankLayout')

@section('title', 'Login Basic - Pages')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
<div class="position-relative">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-6 mx-4">
            <div class="card p-sm-7 p-2">
                <div class="card-body mt-1">
                    <p class="mb-5">Please sign-in to your account</p>
                    <form id="formAuthentication" class="mb-5" novalidate autocomplete="off">
                        <div class="form-floating form-floating-outline mb-5">
                            <input type="text" class="form-control live-validate" id="user_name" name="user_name" placeholder="Enter your username" autofocus />
                            <label for="user_name">Username</label>
                        </div>
                        <div class="mb-5">
                            <div class="form-password-toggle">
                                <div class="input-group input-group-merge">
                                    <div class="form-floating form-floating-outline">
                                        <input type="password" id="password-input" class="form-control live-validate" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
                                        <label for="password">Password</label>
                                    </div>
                                    <span class="input-group-text cursor-pointer"><i class="icon-base ri ri-eye-off-line icon-20px"></i></span>
                                </div>
                                <div id="password-error"></div>
                            </div>
                        </div>
                        <div class="mb-5">
                            <button type="submit" id="btnSave" class="btn btn-primary px-5">
                                <span id="btnSpinner" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                                Login
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection