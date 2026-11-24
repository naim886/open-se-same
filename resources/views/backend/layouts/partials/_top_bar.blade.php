<a class="navbar-brand ps-3 text-start text-lg-center" href="{{route('dashboard.index')}}">
    <img src="{{asset('images/logo-white.png')}}" alt="logo" class="logo">
</a>
<button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>

{{-- Utility actions, left-aligned next to the sidebar toggle --}}
<div class="d-flex align-items-center gap-2 me-auto ms-2">
    @if(auth()->user()?->can('settings.update'))
        <form method="POST" action="{{ route('cache.clear') }}" class="m-0" id="cache-clear-form">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-light" title="Clear application cache">
                <i class="fas fa-broom"></i>
                <span class="d-none d-xl-inline ms-1">Clear Cache</span>
            </button>
        </form>
    @endif

    <a href="{{ route('shop.home') }}" target="_blank" rel="noopener"
       class="btn btn-sm btn-outline-light" title="Open the storefront in a new tab">
        <i class="fas fa-external-link-alt"></i>
        <span class="d-none d-xl-inline ms-1">Visit Website</span>
    </a>
</div>

<ul class="navbar-nav ms-auto me-3 me-lg-4 align-items-center">
    <li class="d-none d-lg-block me-2">{{\Illuminate\Support\Facades\Auth::user()?->name}}</li>

    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i
                class="fas fa-user fa-fw"></i></a>
        <ul class="dropdown-menu dropdown-menu-end text-center mt-2 shadow-bottom" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="{{ route('shop.home') }}" target="_blank" rel="noopener">View Store</a></li>
            @if(auth()->user()?->can('settings.view'))
                <li><a class="dropdown-item" href="{{ route('settings.index') }}">Settings</a></li>
            @endif
            <li>
                <hr class="dropdown-divider"/>
            </li>
            <li>
                {{html()->form('POST', route('logout'))->id('logout-form')->open()}}
                {{html()->form()->close()}}
                <button type="button" class="btn mx-auto btn-danger btn-sm" id="logout-button"><i class="fa-solid fa-power-off"></i> Logout
                </button>
            </li>
        </ul>
    </li>
</ul>

{{-- SweetAlert2 injects its own stylesheet after ours, so toasts need !important
     to stay on the dark theme (otherwise white text on a white toast). --}}
<style>
    .swal2-toast.swal2-popup {
        background: var(--bg-surface, #171717) !important;
        color: var(--theme-text-color, #E4F5EC) !important;
        border: 1px solid var(--border-subtle, rgba(255, 255, 255, .10)) !important;
        box-shadow: var(--shadow-md, 0 8px 24px rgba(0, 0, 0, .45)) !important;
    }
    .swal2-toast .swal2-title {
        color: var(--theme-text-color, #E4F5EC) !important;
    }
    .swal2-toast .swal2-html-container {
        color: var(--theme-text-color, #E4F5EC) !important;
    }
</style>
@push('scripts')
    <script>
        $('#logout-button').on('click', function (e) {
            e.preventDefault();
            Swal.fire({
                title: "Are you sure?",
                text: "You will be logged out form system!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, logout!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#logout-form').submit();
                }
            });
        })
    </script>

    <script>
        // Clear-cache button: confirm, POST without a page reload, toast the result.
        (function () {
            var form = document.getElementById('cache-clear-form');
            if (!form) return;

            form.addEventListener('submit', function (event) {
                event.preventDefault();

                var button = form.querySelector('button');
                var idleHtml = button.innerHTML;

                Swal.fire({
                    title: 'Clear all caches?',
                    text: 'Config, routes, views, events, data and the permission map will be rebuilt.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, clear it',
                    confirmButtonColor: '#027B50',
                    cancelButtonColor: '#6c757d'
                }).then(function (result) {
                    if (!result.isConfirmed) return;

                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new FormData(form)
                    })
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: data.ok ? 'success' : 'error',
                            title: data.message,
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                    })
                    .catch(function () {
                        // Network/JSON failure: fall back to a normal submit.
                        form.submit();
                    })
                    .finally(function () {
                        button.disabled = false;
                        button.innerHTML = idleHtml;
                    });
                });
            });
        })();
    </script>
@endpush
