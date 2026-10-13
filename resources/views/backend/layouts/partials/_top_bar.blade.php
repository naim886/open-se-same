<a class="navbar-brand ps-3 text-start text-lg-center" href="{{route('dashboard.index')}}">
    <img src="{{asset('images/logo-white.png')}}" alt="logo" class="logo">
</a>
<button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>

<ul class="navbar-nav ms-auto me-3 me-lg-4 align-items-center">
    <li class="d-none d-lg-block">{{\Illuminate\Support\Facades\Auth::user()?->name}}</li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i
                class="fas fa-user fa-fw"></i></a>
        <ul class="dropdown-menu dropdown-menu-end text-center mt-2 shadow-bottom" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="#!">Settings</a></li>
            <li><a class="dropdown-item" href="#!">Activity Log</a></li>
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
@endpush
