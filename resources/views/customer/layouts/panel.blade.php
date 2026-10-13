@extends('frontend.layouts.app')

@section('extra_css')
<style>
    /* ── Customer Panel Styles ─────────────────────────────────────── */
    .customer-panel {
        min-height: 70vh;
        padding: 32px 0 60px;
        background: var(--bg-light);
    }

    /* Sidebar */
    .panel-sidebar {
        background: #fff;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        position: sticky;
        top: 80px;
    }

    .panel-user-card {
        background: var(--primary-gradient);
        color: #fff;
        padding: 24px 20px;
        text-align: center;
    }

    .panel-user-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        border: 3px solid rgba(255,255,255,.6);
        object-fit: cover;
        margin-bottom: 10px;
    }

    .panel-user-avatar-placeholder {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        border: 3px solid rgba(255,255,255,.6);
        background: rgba(255,255,255,.25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        margin: 0 auto 10px;
    }

    .panel-user-name {
        font-weight: 700;
        font-size: 15px;
        margin-bottom: 3px;
    }

    .panel-user-phone {
        font-size: 12px;
        opacity: .8;
    }

    .panel-nav {
        list-style: none;
        padding: 8px 0;
        margin: 0;
    }

    .panel-nav li a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 20px;
        font-size: 14px;
        font-weight: 500;
        color: var(--text-body);
        border-left: 3px solid transparent;
        transition: var(--transition);
    }

    .panel-nav li a:hover,
    .panel-nav li a.active {
        color: var(--primary);
        background: rgba(2,123,80, .06);
        border-left-color: var(--primary);
    }

    .panel-nav li a i {
        width: 18px;
        text-align: center;
        color: var(--primary);
    }

    .panel-nav .divider {
        border-top: 1px solid var(--border);
        margin: 6px 0;
    }

    .panel-nav-logout {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 20px;
        font-size: 14px;
        font-weight: 500;
        color: #dc3545;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        cursor: pointer;
        border-left: 3px solid transparent;
        transition: var(--transition);
    }

    .panel-nav-logout:hover {
        background: rgba(220, 53, 69, .06);
        border-left-color: #dc3545;
    }

    .panel-nav-logout i {
        width: 18px;
        text-align: center;
        color: #dc3545;
    }

    /* Mobile tabs */
    .panel-mobile-tabs {
        display: flex;
        overflow-x: auto;
        gap: 0;
        background: #fff;
        box-shadow: var(--shadow);
        border-radius: var(--radius);
        margin-bottom: 20px;
        scrollbar-width: none;
    }

    .panel-mobile-tabs::-webkit-scrollbar { display: none; }

    .panel-mobile-tab {
        flex: 0 0 auto;
        padding: 12px 18px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted);
        border-bottom: 3px solid transparent;
        white-space: nowrap;
        transition: var(--transition);
    }

    .panel-mobile-tab.active,
    .panel-mobile-tab:hover {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }

    /* Content area */
    .panel-content {
        background: #fff;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 28px;
    }

    .panel-section-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid var(--border);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .panel-section-title i {
        color: var(--primary);
    }

    /* Stat cards */
    .stat-card {
        background: var(--bg-light);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px;
        text-align: center;
    }

    .stat-card .stat-icon {
        font-size: 28px;
        color: var(--primary);
        margin-bottom: 8px;
    }

    .stat-card .stat-value {
        font-size: 22px;
        font-weight: 700;
        color: var(--text-dark);
    }

    .stat-card .stat-label {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 2px;
    }

    /* Order table */
    .order-table th {
        background: var(--bg-light);
        color: var(--text-body);
        font-weight: 600;
        font-size: 13px;
        border-color: var(--border);
    }

    .order-table td {
        font-size: 13.5px;
        vertical-align: middle;
        border-color: var(--border);
    }

    /* Status badge */
    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .3px;
    }

    .status-1  { background: #fff3cd; color: #856404; }
    .status-2  { background: #cfe2ff; color: #0a58ca; }
    .status-3  { background: #d1ecf1; color: #0c5460; }
    .status-4  { background: #d4edda; color: #155724; }
    .status-5  { background: #f0e6ff; color: #5a1aad; }
    .status-6  { background: #d4edda; color: #155724; }
    .status-7  { background: #f8d7da; color: #721c24; }
    .status-8  { background: #f8d7da; color: #721c24; }
    .status-9  { background: #fff3cd; color: #856404; }
    .status-10 { background: #d1ecf1; color: #0c5460; }

    /* Timeline */
    .tracking-timeline {
        position: relative;
        padding-left: 28px;
    }

    .tracking-timeline::before {
        content: '';
        position: absolute;
        left: 9px;
        top: 6px;
        bottom: 0;
        width: 2px;
        background: var(--border);
    }

    .timeline-step {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-step::before {
        content: '';
        position: absolute;
        left: -22px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--primary);
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px var(--primary);
    }

    .timeline-step.inactive::before {
        background: #e0e0e0;
        box-shadow: 0 0 0 2px #ccc;
    }

    .timeline-step .step-label {
        font-weight: 600;
        font-size: 14px;
        color: var(--text-dark);
    }

    .timeline-step .step-time {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 2px;
    }

    /* Profile form */
    .panel-form label {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-body);
        margin-bottom: 5px;
    }

    .panel-form .form-control {
        border: 1.5px solid var(--border);
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 14px;
        color: var(--text-dark);
        background: var(--bg-light);
        transition: var(--transition);
    }

    .panel-form .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(2,123,80,.12);
        background: #fff;
    }

    .btn-primary-custom {
        background: var(--primary-gradient);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 10px 28px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: var(--transition);
    }

    .btn-primary-custom:hover {
        opacity: .9;
        transform: translateY(-1px);
    }

    .btn-outline-custom {
        background: transparent;
        color: var(--primary);
        border: 2px solid var(--primary);
        border-radius: 8px;
        padding: 9px 20px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: var(--transition);
    }

    .btn-outline-custom:hover {
        background: var(--primary);
        color: #fff;
    }

    /* Avatar upload preview */
    .avatar-upload-preview {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--border);
    }

    @media (max-width: 767.98px) {
        .panel-content { padding: 18px; }
        .customer-panel { padding: 16px 0 40px; }
    }
</style>
@endsection

@section('content')
<div class="customer-panel">
    <div class="container">

        <!-- Mobile Tabs -->
        <div class="d-md-none panel-mobile-tabs mb-3">
            <a href="{{ route('customer.dashboard') }}"
               class="panel-mobile-tab {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home me-1"></i> Home
            </a>
            <a href="{{ route('customer.orders') }}"
               class="panel-mobile-tab {{ request()->routeIs('customer.orders', 'customer.order.detail') ? 'active' : '' }}">
                <i class="fas fa-box me-1"></i> Orders
            </a>
            <a href="{{ route('customer.profile') }}"
               class="panel-mobile-tab {{ request()->routeIs('customer.profile') ? 'active' : '' }}">
                <i class="fas fa-user me-1"></i> Profile
            </a>
            <a href="{{ route('customer.addresses') }}"
               class="panel-mobile-tab {{ request()->routeIs('customer.addresses') ? 'active' : '' }}">
                <i class="fas fa-map-marker-alt me-1"></i> Addresses
            </a>
            <a href="{{ route('shop.track-order') }}" class="panel-mobile-tab">
                <i class="fas fa-map-marker-alt me-1"></i> Track
            </a>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="panel-mobile-tab" style="border:none;background:none;color:#dc3545;cursor:pointer;">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </button>
            </form>
        </div>

        <div class="row g-4">
            <!-- Sidebar (desktop) -->
            <div class="col-md-3 d-none d-md-block">
                <div class="panel-sidebar">
                    <!-- User Card -->
                    <div class="panel-user-card">
                        @if(auth()->user()->photo)
                            <img src="{{ asset('storage' . User::IMAGE_PATH . auth()->user()->photo) }}"
                                 alt="{{ auth()->user()->name }}" class="panel-user-avatar">
                        @else
                            <div class="panel-user-avatar-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                        <div class="panel-user-name">{{ auth()->user()->name }}</div>
                        <div class="panel-user-phone">{{ auth()->user()->phone ?? auth()->user()->email }}</div>
                    </div>

                    <!-- Navigation -->
                    <ul class="panel-nav">
                        <li>
                            <a href="{{ route('customer.dashboard') }}"
                               class="{{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('customer.orders') }}"
                               class="{{ request()->routeIs('customer.orders', 'customer.order.detail') ? 'active' : '' }}">
                                <i class="fas fa-box"></i> My Orders
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('customer.profile') }}"
                               class="{{ request()->routeIs('customer.profile') ? 'active' : '' }}">
                                <i class="fas fa-user-edit"></i> Profile Settings
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('customer.addresses') }}"
                               class="{{ request()->routeIs('customer.addresses') ? 'active' : '' }}">
                                <i class="fas fa-address-book"></i> Address Book
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('shop.track-order') }}">
                                <i class="fas fa-map-marker-alt"></i> Track Order
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="{{ route('shop.home') }}">
                                <i class="fas fa-store"></i> Continue Shopping
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="panel-nav-logout">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="panel-content">
                    @yield('panel_content')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@php use App\Models\User; @endphp
