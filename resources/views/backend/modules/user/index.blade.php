@extends('backend.layouts.app')

@section('title', 'Users')

@section('content')
<div class="container-fluid my-4">

    {{-- ── Header ── --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-user-shield me-2 text-primary"></i>Users</h4>
            <div class="small text-muted mt-1">
                {{ number_format($totalUsers) }} total &nbsp;·&nbsp;
                {{ number_format($activeUsers) }} active &nbsp;·&nbsp;
                {{ number_format($registeredToday) }} joined today
            </div>
        </div>
    </div>

    {{-- ── Alert ── --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── Summary Cards ── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-4">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-muted small mb-1">Total Users</div>
                <div class="fw-bold fs-2 text-primary">{{ number_format($totalUsers) }}</div>
            </div>
        </div>
        <div class="col-6 col-xl-4">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-muted small mb-1">Active</div>
                <div class="fw-bold fs-2 text-success">{{ number_format($activeUsers) }}</div>
            </div>
        </div>
        <div class="col-6 col-xl-4">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-muted small mb-1">Joined Today</div>
                <div class="fw-bold fs-2 text-warning">{{ number_format($registeredToday) }}</div>
            </div>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.users.index') }}"
                  class="d-flex flex-wrap align-items-end gap-2">

                <div style="min-width:220px; flex:1;">
                    <label class="form-label mb-1 small">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Name, email or phone…" value="{{ request('search') }}">
                </div>

                <div>
                    <label class="form-label mb-1 small">Role</label>
                    <select name="role" class="form-select form-select-sm" style="min-width:160px;">
                        <option value="">All Roles</option>
                        @foreach($allRoles as $r)
                            <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label mb-1 small">Status</label>
                    <select name="status" class="form-select form-select-sm" style="min-width:130px;">
                        <option value="">All</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                        <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Pending</option>
                        <option value="3" {{ request('status') === '3' ? 'selected' : '' }}>Banned</option>
                    </select>
                </div>

                <div>
                    <label class="form-label mb-1 small">Sort By</label>
                    <select name="sort" class="form-select form-select-sm" style="min-width:150px;">
                        <option value="newest"      {{ request('sort','newest') === 'newest'      ? 'selected' : '' }}>Newest First</option>
                        <option value="oldest"      {{ request('sort') === 'oldest'      ? 'selected' : '' }}>Oldest First</option>
                        <option value="most_orders" {{ request('sort') === 'most_orders' ? 'selected' : '' }}>Most Orders</option>
                        <option value="name"        {{ request('sort') === 'name'        ? 'selected' : '' }}>Name A–Z</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Table ── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:13.5px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>User</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Orders</th>
                            <th>Status</th>
                            <th>Last Activity</th>
                            <th>Joined</th>
                            <th style="width:100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            {{-- # --}}
                            <td class="text-muted" style="font-size:12px;">{{ $users->firstItem() + $loop->index }}</td>

                            {{-- User (avatar + name + email) --}}
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($user->photo)
                                        <img src="{{ asset('storage' . \App\Models\User::IMAGE_PATH . $user->photo) }}"
                                             alt="{{ $user->name }}"
                                             style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:1.5px solid #dee2e6;">
                                    @else
                                        <div style="width:34px;height:34px;border-radius:50%;background:var(--bs-primary);display:flex;align-items:center;justify-content:center;color:#fff;font-size:14px;font-weight:700;flex-shrink:0;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold" style="line-height:1.2;">{{ $user->name }}</div>
                                        <div class="text-muted" style="font-size:11.5px;">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Phone --}}
                            <td>
                                @if($user->phone)
                                    <a href="tel:{{ $user->phone }}" class="text-decoration-none text-dark">{{ $user->phone }}</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Role --}}
                            <td>
                                @php $roleName = $user->roles->first()?->name; @endphp
                                @if($roleName)
                                    @php
                                        $roleColor = match($roleName) {
                                            'Super Admin'         => 'danger',
                                            'Admin'               => 'warning',
                                            'Operation'           => 'info',
                                            'Sales Executive'     => 'primary',
                                            'Registered Customer' => 'success',
                                            default               => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $roleColor }}" style="font-size:11px;">{{ $roleName }}</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size:11px;">No Role</span>
                                @endif
                            </td>

                            {{-- Orders --}}
                            <td>
                                <span class="fw-semibold">{{ number_format($user->orders_count) }}</span>
                            </td>

                            {{-- Status --}}
                            <td>
                                @php
                                    $statusMap = [
                                        \App\Models\User::STATUS_ACTIVE   => ['Active',   'success'],
                                        \App\Models\User::STATUS_INACTIVE => ['Inactive', 'secondary'],
                                        \App\Models\User::STATUS_PENDING  => ['Pending',  'warning'],
                                        \App\Models\User::STATUS_BANNED   => ['Banned',   'danger'],
                                    ];
                                    [$statusLabel, $statusColor] = $statusMap[$user->status] ?? ['Unknown', 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $statusColor }}" style="font-size:11px;">{{ $statusLabel }}</span>
                            </td>

                            {{-- Last Activity (updated_at as proxy) --}}
                            <td>
                                <span title="{{ $user->updated_at?->format('d M Y H:i') }}">
                                    {{ $user->updated_at?->diffForHumans() ?? '—' }}
                                </span>
                            </td>

                            {{-- Joined --}}
                            <td style="white-space:nowrap;">
                                {{ $user->created_at?->format('d M Y') }}
                            </td>

                            {{-- Actions --}}
                            <td>
                                <div class="d-flex gap-1">
                                    {{-- Toggle status --}}
                                    @if(!$user->hasRole('Super Admin'))
                                    <form method="POST"
                                          action="{{ route('admin.users.toggleStatus', $user->id) }}"
                                          onsubmit="return confirm('Toggle status for {{ addslashes($user->name) }}?')">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="btn btn-sm {{ $user->status == \App\Models\User::STATUS_ACTIVE ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                title="{{ $user->status == \App\Models\User::STATUS_ACTIVE ? 'Deactivate' : 'Activate' }}"
                                                style="font-size:11px; padding:3px 8px;">
                                            <i class="fas {{ $user->status == \App\Models\User::STATUS_ACTIVE ? 'fa-ban' : 'fa-check' }}"></i>
                                        </button>
                                    </form>

                                    {{-- Change role --}}
                                    <button class="btn btn-sm btn-outline-primary"
                                            style="font-size:11px; padding:3px 8px;"
                                            title="Change Role"
                                            data-bs-toggle="modal"
                                            data-bs-target="#roleModal{{ $user->id }}">
                                        <i class="fas fa-user-tag"></i>
                                    </button>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        {{-- Role change modal --}}
                        @if(!$user->hasRole('Super Admin'))
                        <div class="modal fade" id="roleModal{{ $user->id }}" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header py-2">
                                        <h6 class="modal-title mb-0">Change Role — {{ $user->name }}</h6>
                                        <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="{{ route('admin.users.updateRole', $user->id) }}">
                                        @csrf @method('PATCH')
                                        <div class="modal-body py-2">
                                            <select name="role" class="form-select form-select-sm">
                                                @foreach($allRoles as $r)
                                                    @if($r !== 'Super Admin')
                                                    <option value="{{ $r }}"
                                                            {{ $user->roles->first()?->name === $r ? 'selected' : '' }}>
                                                        {{ $r }}
                                                    </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="modal-footer py-2">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif

                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-users fa-2x mb-2 d-block"></i>No users found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($users->hasPages())
        <div class="card-footer border-0 bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <div class="text-muted small">
                Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} users
            </div>
            {{ $users->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
