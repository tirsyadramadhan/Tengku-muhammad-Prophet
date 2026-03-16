<!DOCTYPE html>
<html lang="en"
    class="layout-menu-fixed layout-compact"
    data-assets-path="{{ asset('/assets') . '/' }}"
    dir="ltr"
    data-skin="default"
    data-base-url="{{ url('/') }}"
    data-framework="laravel"
    data-bs-theme="light"
    data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>@yield('title')</title>

    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="canonical" href="{{ config('variables.productPage') ?? '' }}" />
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    {{-- Styles only in head --}}
    @include('layouts/sections/styles')
</head>

<body>
    @yield('layoutContent')

    @vite([
    'resources/assets/vendor/js/helpers.js',
    'resources/assets/js/config.js',
    'resources/js/app.js',
    'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
    'resources/assets/vendor/js/menu.js',
    'resources/assets/js/main.js'
    ])

    {{-- All scripts at bottom of body --}}
    <script src="https://www.google.com/recaptcha/api.js?onload=onRecaptchaLoad&render=explicit" async defer></script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>

    @yield('vendor-script')
    @yield('page-script')
</body>

</html>