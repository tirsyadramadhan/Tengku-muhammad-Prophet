@isset($pageConfigs)
{!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@extends('layouts/commonMaster')

@php
/* Display elements */
$contentNavbar = $contentNavbar ?? true;
$containerNav = $containerNav ?? 'container-xxl';
$isNavbar = $isNavbar ?? true;
$isMenu = $isMenu ?? true;
$isFlex = $isFlex ?? false;
$isFooter = $isFooter ?? true;
$customizerHidden = $customizerHidden ?? '';

/* HTML Classes */
$navbarDetached = 'navbar-detached';
$menuFixed = isset($configData['menuFixed']) ? $configData['menuFixed'] : '';
$menuCollapsed = isset($configData['menuCollapsed']) ? $configData['menuCollapsed'] : '';
$footerFixed = isset($configData['footerFixed']) ? $configData['footerFixed'] : '';

if (isset($navbarType)) {
$configData['navbarType'] = $navbarType;
}
$navbarType = isset($configData['navbarType']) ? $configData['navbarType'] : '';
@endphp

@section('layoutContent')
<div class="layout-wrapper layout-content-navbar {{ $isMenu ? '' : 'layout-without-menu' }}">
    <div class="layout-container">

        @if ($isMenu)
        @include('layouts/sections/menu/verticalMenu')
        @endif

        <!-- Layout page -->
        <div class="layout-page">

            <!-- BEGIN: Navbar -->
            @if ($isNavbar)
            @include('layouts/sections/navbar/navbar')
            @endif
            <!-- END: Navbar -->

            <!-- Content wrapper -->
            <div class="content-wrapper">

                <!-- Content -->
                @yield('content')
                <!-- / Content -->

                <!-- Footer -->
                @if ($isFooter)
                @include('layouts/sections/footer/footer')
                @endif
                <!-- / Footer -->

                <div class="content-backdrop fade"></div>
            </div>
            <!--/ Content wrapper -->

        </div>
        <!-- / Layout page -->

        @if ($isMenu)
        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
        @endif

        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>

    </div>
    <!-- / Layout wrapper -->
</div>
@endsection