@extends('frontend.layouts.app')

@section('title', 'Create Account — Open Se Same')
@section('meta_description', 'Create your free Open Se Same account to track orders, save preferences and shop faster.')

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
    box-shadow: 0 4px 30px rgba(1,92,60,.10);
    padding: 40px 36px;
    max-width: 500px;
    margin: 0 auto;
}
.auth-logo { text-align: center; margin-bottom: 24px; }
.auth-title { font-size: 22px; font-weight: 700; color: var(--text-dark); text-align: center; margin-bottom: 6px; }
.auth-subtitle { font-size: 13px; color: var(--text-muted); text-align: center; margin-bottom: 26px; }
.auth-card label { font-size: 13px; font-weight: 600; color: var(--text-body); margin-bottom: 5px; display: block; }
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
    box-shadow: 0 0 0 3px rgba(2,123,80,.12);
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
.avatar-reg-preview {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border);
    display: none;
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

            <div class="auth-title">Create Your Account</div>
            <div class="auth-subtitle">Track orders, manage your profile and shop smarter</div>

            @if($errors->any())
            <div style="background:#fff3cd;border:1px solid #ffeeba;border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:16px;color:#856404;">
                <i class="fas fa-exclamation-circle me-1"></i>
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="name">Full Name <span style="color:#dc3545;">*</span></label>
                    <input type="text" name="name" id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           placeholder="Your full name" required autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="phone">Phone Number <span style="color:#dc3545;">*</span></label>
                    <input type="text" name="phone" id="phone"
                           class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone') }}"
                           placeholder="01XXXXXXXXX" required>
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email">Email Address <span style="color:#dc3545;">*</span></label>
                    <input type="email" name="email" id="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}"
                           placeholder="your@email.com" required autocomplete="email">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password">Password <span style="color:#dc3545;">*</span></label>
                    <div class="input-group">
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Choose a password" required autocomplete="new-password">
                        <span class="input-group-text" id="toggle-password">
                            <i class="fas fa-eye" id="pw-icon"></i>
                        </span>
                    </div>
                    @error('password')
                        <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="photo">Profile Photo <span style="font-size:11px;color:var(--text-muted);font-weight:400;">(optional, 300×300px)</span></label>
                    <div class="d-flex align-items-center gap-3">
                        <input type="file" name="photo" id="photo"
                               class="form-control @error('photo') is-invalid @enderror"
                               accept="image/jpeg,image/png,image/jpg,image/webp">
                        <img src="" alt="Preview" id="photoPreview" class="avatar-reg-preview">
                    </div>
                    @error('photo')
                        <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 d-flex align-items-center gap-2">
                    <input type="checkbox" name="remember_me" id="remember_me" class="form-check-input" style="margin:0;">
                    <label for="remember_me" style="font-size:13px;font-weight:400;cursor:pointer;margin:0;">Remember me</label>
                </div>

                <button type="submit" class="auth-btn">
                    <i class="fas fa-user-plus me-1"></i> Create Account
                </button>
            </form>

            <div class="text-center mt-4" style="font-size:14px;">
                Already have an account? <a href="{{ route('login') }}" class="auth-link">Sign in</a>
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
document.getElementById('photo').addEventListener('change', function () {
    var file = this.files && this.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (e) {
        var preview = document.getElementById('photoPreview');
        preview.src = e.target.result;
        preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
});
</script>
@endpush
