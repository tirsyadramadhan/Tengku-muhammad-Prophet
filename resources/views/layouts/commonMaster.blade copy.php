<!DOCTYPE html>
<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="{{ asset('/assets') . '/' }}" dir="ltr" data-skin="default" data-base-url="{{ url('/') }}" data-framework="laravel" data-bs-theme="light" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>
        @yield('title')
    </title>
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <!-- laravel CRUD token -->
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <!-- Canonical SEO -->
    <link rel="canonical" href="{{ config('variables.productPage') ? config('variables.productPage') : '' }}" />
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @include('layouts/sections/styles')
    @vite(['resources/assets/vendor/js/helpers.js'])
    @vite(['resources/assets/js/config.js'])
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    @vite(['resources/js/app.js'])
    @vite(['resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js', 'resources/assets/vendor/js/menu.js'])
    @yield('vendor-script')
    @vite(['resources/assets/js/main.js'])
    @yield('page-script')
</head>

<body>
    <!-- Layout Content -->
    @yield('layoutContent')
    <!--/ Layout Content -->
</body>

</html>