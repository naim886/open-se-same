<!DOCTYPE html>
<html lang="en">
<head>
    @include('backend.layouts.partials._head')
    <title>{{$cms_content['page_title'] ?? 'Dashboard' }} | Open Se Same</title>
</head>
<body class="sb-nav-fixed">
<nav class="sb-topnav navbar navbar-expand theme-bg shadow-bottom">
    @include('backend.layouts.partials._top_bar')
</nav>
<div id="layoutSidenav">
    <div id="layoutSidenav_nav">
        @include('backend.layouts.partials._nav')
    </div>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                @include('backend.layouts.partials._bread_crumb')
                @yield('content')
            </div>
        </main>
        <footer class="py-1 theme-bg mt-auto shadow-top">
            @include('backend.layouts.partials._footer')
        </footer>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="{{ minified_asset('backend/js/scripts.js') }}"></script>
@stack('scripts')
</body>
</html>
