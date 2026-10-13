@extends('customer.layouts.panel')

@section('title', 'Profile Settings — Open Se Same')

@section('panel_content')

@php use App\Models\User; @endphp

<div class="panel-section-title">
    <i class="fas fa-user-edit"></i> Profile Settings
</div>

<div class="row g-4">

    <!-- Profile Photo -->
    <div class="col-md-4">
        <h6 class="fw-bold mb-3" style="font-size:14px;color:var(--text-dark);">
            <i class="fas fa-camera me-2" style="color:var(--primary);"></i>Profile Photo
        </h6>

        <div class="text-center mb-3">
            @if($user->photo)
                <img src="{{ asset('storage' . User::IMAGE_PATH . $user->photo) }}"
                     alt="{{ $user->name }}"
                     class="avatar-upload-preview"
                     id="avatarPreview">
            @else
                <div id="avatarPlaceholder"
                     style="width:100px;height:100px;border-radius:50%;background:var(--bg-light);border:3px solid var(--border);display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:36px;color:var(--primary);">
                    <i class="fas fa-user"></i>
                </div>
                <img src="" alt="" class="avatar-upload-preview" id="avatarPreview" style="display:none;">
            @endif
        </div>

        <form action="{{ route('customer.profile.photo') }}" method="POST" enctype="multipart/form-data" class="panel-form">
            @csrf
            <div class="mb-3">
                <label for="photo">Upload New Photo</label>
                <input type="file" name="photo" id="photo" class="form-control @error('photo') is-invalid @enderror"
                       accept="image/jpeg,image/png,image/jpg,image/webp">
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">JPG/PNG/WEBP, max 2MB. Will be cropped to 300×300px.</div>
                @error('photo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn-primary-custom w-100">
                <i class="fas fa-upload me-1"></i> Update Photo
            </button>
        </form>
    </div>

    <!-- Profile Info -->
    <div class="col-md-8">
        <h6 class="fw-bold mb-3" style="font-size:14px;color:var(--text-dark);">
            <i class="fas fa-id-card me-2" style="color:var(--primary);"></i>Personal Information
        </h6>

        <form action="{{ route('customer.profile.update') }}" method="POST" class="panel-form">
            @csrf
            <div class="mb-3">
                <label for="name">Full Name <span style="color:#dc3545;">*</span></label>
                <input type="text" name="name" id="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $user->name) }}"
                       placeholder="Your full name" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="phone">Phone Number <span style="color:#dc3545;">*</span></label>
                <input type="text" name="phone" id="phone"
                       class="form-control @error('phone') is-invalid @enderror"
                       value="{{ old('phone', $user->phone) }}"
                       placeholder="01XXXXXXXXX" required>
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">
                    This is used to match your orders. Keep it updated.
                </div>
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $user->email) }}"
                       placeholder="your@email.com">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-primary-custom">
                <i class="fas fa-save me-1"></i> Save Changes
            </button>
        </form>
    </div>
</div>

<hr style="border-color:var(--border);margin:28px 0;">

<!-- Change Password -->
<h6 class="fw-bold mb-3" style="font-size:14px;color:var(--text-dark);">
    <i class="fas fa-lock me-2" style="color:var(--primary);"></i>Change Password
</h6>

<form action="{{ route('customer.profile.password') }}" method="POST" class="panel-form">
    @csrf
    <div class="row g-3">
        <div class="col-md-4">
            <label for="current_password">Current Password <span style="color:#dc3545;">*</span></label>
            <input type="password" name="current_password" id="current_password"
                   class="form-control @error('current_password') is-invalid @enderror"
                   placeholder="Current password" required>
            @error('current_password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="password">New Password <span style="color:#dc3545;">*</span></label>
            <input type="password" name="password" id="password"
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="New password" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="password_confirmation">Confirm New Password <span style="color:#dc3545;">*</span></label>
            <input type="password" name="password_confirmation" id="password_confirmation"
                   class="form-control"
                   placeholder="Confirm new password" required>
        </div>
    </div>
    <div class="mt-3">
        <button type="submit" class="btn-outline-custom">
            <i class="fas fa-key me-1"></i> Change Password
        </button>
    </div>
</form>

@endsection

@push('scripts')
<script>
document.getElementById('photo').addEventListener('change', function () {
    var file = this.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (e) {
        var preview = document.getElementById('avatarPreview');
        var placeholder = document.getElementById('avatarPlaceholder');
        preview.src = e.target.result;
        preview.style.display = 'block';
        if (placeholder) placeholder.style.display = 'none';
    };
    reader.readAsDataURL(file);
});
</script>
@endpush
