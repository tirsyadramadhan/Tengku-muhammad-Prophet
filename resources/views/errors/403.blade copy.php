@extends('layouts/contentNavbarLayout')

@section('title', 'Unauthorized')
@section('vendor-style')
<style>
    .misc-wrapper {
        transform-origin: top center;
        overflow: hidden;
    }

    .misc-wrapper img {
        max-width: 100%;
        height: auto;
    }
</style>
@endsection
@section('content')
<div class="misc-wrapper" bis_skin_checked="1">
    <h1 class="mb-2 mx-2" style="font-size: 6rem;line-height: 6rem">401</h1>
    <h4 class="mb-2">Unauthorized! 🔐</h4>
    <p class="mb-10 mx-2">Kamu tidak bisa mengakses halaman ini!</p>
    <div class="d-flex justify-content-center mt-5" bis_skin_checked="1">
        <img src="https://demos.themeselection.com/materio-bootstrap-html-laravel-admin-template/demo/assets/img/illustrations/tree-3.png" alt="misc-tree" class="img-fluid misc-object d-none d-lg-inline-block">
        <img src="https://demos.themeselection.com/materio-bootstrap-html-laravel-admin-template/demo/assets/img/illustrations/tree.png" alt="misc-tree" class="img-fluid misc-object-right d-none d-lg-inline-block">
        <img src="https://demos.themeselection.com/materio-bootstrap-html-laravel-admin-template/demo/assets/img/illustrations/misc-mask-light.png" alt="misc-error" class="scaleX-n1-rtl misc-bg d-none d-lg-inline-block" height="172" data-app-light-img="illustrations/misc-mask-light.png" data-app-dark-img="illustrations/misc-mask-dark.png" style="visibility: visible;">
        <div class="d-flex flex-column align-items-center" bis_skin_checked="1">
            <img src="https://demos.themeselection.com/materio-bootstrap-html-laravel-admin-template/demo/assets/img/illustrations/401.png" alt="misc-error" class="misc-model img-fluid z-1" width="780">
            <div bis_skin_checked="1">
                <a href="https://demos.themeselection.com/materio-bootstrap-html-laravel-admin-template/demo-1" class="btn btn-primary text-center my-6 waves-effect waves-light">Back to home</a>
            </div>
        </div>
    </div>
</div>
@endsection