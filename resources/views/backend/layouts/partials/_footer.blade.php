<div class="container-fluid px-4">
    <div class="row align-items-center justify-content-between small">
        <div class="col-lg-auto">
            <p class="text-center text-lg-start">Copyright &copy; {{env('APP_NAME')}} {{date('Y')}}</p>
        </div>
        <div class="col-lg-auto d-none d-lg-block">
            <p>
                <small>
                    Last Activity: Few seconds ago |
                    Your Location: Near Dhaka, Bangladesh |
                    Version: {{env('APP_VERSION')}} |
                    Your IP: {{request()->ip()}} |
                    PHP Version: {{phpversion()}} |
                    Laravel Version: {{app()->version()}} |
                    Server Software: {{$_SERVER['SERVER_SOFTWARE']}} |
                    Server Name: {{$_SERVER['SERVER_NAME']}}
                </small>
            </p>
        </div>
    </div>
</div>
