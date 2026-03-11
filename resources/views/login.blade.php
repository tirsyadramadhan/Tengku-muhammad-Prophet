@extends('layouts/blankLayout')

@section('title', 'Login')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
<style>
    .divider:after,
    .divider:before {
        content: "";
        flex: 1;
        height: 1px;
        background: #eee;
    }

    .h-custom {
        height: calc(100% - 73px);
    }

    @media (max-width: 450px) {
        .h-custom {
            height: 100%;
        }
    }
</style>
@endsection

@section('content')
<section class="vh-100">
    <div class="container-fluid h-custom">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-md-9 col-lg-6 col-xl-5">
                <img src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-login-form/draw2.webp"
                    class="img-fluid" alt="Sample image">
            </div>
            <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1 shadow p-4">
                <form id="formAuthentication" novalidate autocomplete="off">
                    <div class="d-flex flex-row align-items-center justify-content-center justify-content-lg-start mb-4">
                        <p class="lead fw-normal mb-0 me-3">Login ke akun anda</p>
                    </div>

                    <!-- Email input -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <label class="form-label" for="form3Example3">Username</label>
                        <input
                            id="user_name"
                            name="user_name"
                            type="text"
                            class="form-control form-control-lg live-validate"
                            placeholder="Masukkan Username" />
                    </div>

                    <!-- Password input -->
                    <div data-mdb-input-init class="form-outline mb-3">
                        <label class="form-label" for="form3Example4">Password</label>
                        <input
                            id="password-input"
                            name="password"
                            type="password"
                            class="form-control form-control-lg live-validate"
                            placeholder="Masukkan Password" />
                        <div id="password-error"></div>
                    </div>

                    <div class="mb-3">
                        {{-- Hidden input JustValidate targets --}}
                        <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response" value="" />

                        <div class="g-recaptcha"
                            data-sitekey="{{ config('services.recaptcha.site_key') }}"
                            data-callback="onRecaptchaSuccess"
                            data-expired-callback="onRecaptchaExpired">
                        </div>
                        <div id="recaptcha-error"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <!-- Checkbox -->
                        <div class="form-check mb-0">
                            <input name="remember_user" class="form-check-input me-2" type="checkbox" value="0" id="form2Example3" />
                            <label class="form-check-label" for="form2Example3">
                                Ingat Saya
                            </label>
                        </div>
                        {{--
                        <a href="{{ route('forgot-password') }}" class="text-body">Lupa Password?</a>
                        --}}
                    </div>

                    <div class="text-center text-lg-start mt-4 pt-2">
                        <button type="submit" id="btnSave" class="btn btn-primary px-5">
                            <span id="btnSpinner" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                            Login
                        </button>
                        {{--
                        <p class="small fw-bold mt-2 pt-1 mb-0">Belum mempunyai akun? <a href="{{ route('register') }}"
                        class="link-danger">Daftar</a></p>
                        --}}
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div
        class="d-flex flex-column flex-md-row text-center text-md-start justify-content-center py-4 px-4 px-xl-5 bg-primary">
        <!-- Copyright -->
        <div class="text-white mb-3 mb-md-0">
            Copyright © {{ \Carbon\Carbon::now()->format('Y') }}. All rights reserved.
        </div>
        <!-- Copyright -->
    </div>
</section>
@endsection