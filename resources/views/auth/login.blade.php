@extends('frontend.layouts.app')

@section('title', 'Login — Open Se Same')
@section('meta_description', 'Log in to your Open Se Same account to view your orders, track deliveries and manage your profile.')

@section('extra_css')
<style>
.auth-section {
    min-height: 70vh;
    display: flex;
    align-items: center;
    padding: 40px 0;
    background: var(--bg-light);
}
.auth-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 30px rgba(122,18,53,.10);
    padding: 40px 36px;
    max-width: 440px;
    margin: 0 auto;
}
.auth-logo {
    text-align: center;
    margin-bottom: 24px;
}
.auth-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--text-dark);
    text-align: center;
    margin-bottom: 6px;
}
.auth-subtitle {
    font-size: 13px;
    color: var(--text-muted);
    text-align: center;
    margin-bottom: 26px;
}
.auth-card label {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-body);
    margin-bottom: 5px;
    display: block;
}
.auth-card .form-control {
    border: 1.5px solid var(--border);
    border-radius: 8px;
    padding: 11px 14px;
    font-size: 14px;
    color: var(--text-dark);
    background: var(--bg-light);
    transition: var(--transition);
}
.auth-card .form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(194,67,107,.12);
    background: #fff;
    outline: none;
}
.auth-btn {
    width: 100%;
    background: var(--primary-gradient);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 12px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    margin-top: 6px;
}
.auth-btn:hover { opacity: .9; transform: translateY(-1px); }
.auth-link { color: var(--primary); font-weight: 600; }
.auth-link:hover { color: var(--primary-dark); text-decoration: underline; }
.input-group .form-control { border-right: none; border-radius: 8px 0 0 8px !important; }
.input-group-text {
    background: var(--bg-light);
    border: 1.5px solid var(--border);
    border-left: none;
    border-radius: 0 8px 8px 0;
    cursor: pointer;
    color: var(--text-muted);
}
.alert-auth {
    background: #fff3cd;
    border: 1px solid #ffeeba;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 13px;
    margin-bottom: 16px;
    color: #856404;
}
</style>
@endsection

@section('content')
<section class="auth-section">
    <div class="container">
        <div class="auth-card">
            <div class="auth-logo">
                <a href="{{ route('shop.home') }}">
                    <img src="{{ asset('images/logo-web.png') }}" alt="Open Se Same" height="52" style="max-width:160px;object-fit:contain;">
                </a>
            </div>

            <div class="auth-title">Welcome Back!</div>
            <div class="auth-subtitle">Sign in to view your orders &amp; manage your account</div>

            @if($errors->any())
            <div class="alert-auth">
                <i class="fas fa-exclamation-circle me-1"></i>
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email">Email or Phone <span style="color:#dc3545;">*</span></label>
                    <input type="text" name="email" id="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}"
                           placeholder="Enter your email or phone"
                           autocomplete="username" required autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <label for="password">Password <span style="color:#dc3545;">*</span></label>
                        <a href="{{ route('password.request') }}" class="auth-link" style="font-size:12px;">Forgot password?</a>
                    </div>
                    <div class="input-group">
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Enter your password"
                               autocomplete="current-password" required>
                        <span class="input-group-text" id="toggle-password">
                            <i class="fas fa-eye" id="pw-icon"></i>
                        </span>
                    </div>
                    @error('password')
                        <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 d-flex align-items-center gap-2">
                    <input type="checkbox" name="remember_me" id="remember_me" class="form-check-input" style="margin:0;">
                    <label for="remember_me" style="font-size:13px;font-weight:400;cursor:pointer;margin:0;">Remember me</label>
                </div>

                <button type="submit" class="auth-btn">
                    <i class="fas fa-sign-in-alt me-1"></i> Sign In
                </button>
            </form>

            <div class="text-center mt-4" style="font-size:14px;">
                Don't have an account? <a href="{{ route('register') }}" class="auth-link">Create one free</a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.getElementById('toggle-password').addEventListener('click', function () {
    var input = document.getElementById('password');
    var icon  = document.getElementById('pw-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
});
</script>
@endpush
