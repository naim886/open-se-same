@extends('backend.layouts.app')

@section('title', 'Roles')

@section('content')
<div class="container-fluid my-4">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-key me-2 text-primary"></i>Roles</h4>
            <div class="small text-muted mt-1">
                {{ $roles->count() }} role{{ $roles->count() === 1 ? '' : 's' }} &nbsp;·&nbsp;
                {{ $permissionCount }} permissions available
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('permissions.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-table me-1"></i>Permission Matrix
            </a>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                <i class="fas fa-plus me-1"></i>New Role
            </button>
        </div>
    </div>

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
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:13.5px;">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Role</th>
                        <th>Description</th>
                        <th class="text-center" style="width:90px;">Users</th>
                        <th class="text-center" style="width:120px;">Permissions</th>
                        <th style="width:190px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                        <tr>
                            <td class="text-muted" style="font-size:12px;">{{ $loop->iteration }}</td>

                            <td>
                                <div class="fw-semibold d-flex align-items-center gap-2">
                                    {{ $role->name }}
                                    @if($role->is_locked)
                                        <span class="badge bg-dark" style="font-size:10px;">
                                            <i class="fas fa-lock me-1"></i>Locked
                                        </span>
                                    @endif
                                </div>
                                <div class="text-muted" style="font-size:11.5px;">{{ $role->guard_name }}</div>
                            </td>

                            <td class="text-muted">{{ $role->description ?: '—' }}</td>

                            <td class="text-center">
                                <span class="badge bg-secondary" style="font-size:11px;">{{ $role->users_count }}</span>
                            </td>

                            <td class="text-center">
                                @if($role->is_locked)
                                    <span class="badge bg-success" style="font-size:11px;">All</span>
                                @else
                                    <span class="badge bg-info text-dark" style="font-size:11px;">
                                        {{ $role->permissions_count }} / {{ $permissionCount }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <a href="{{ route('permissions.index', ['role' => $role->id]) }}"
                                       class="btn btn-sm btn-outline-primary" style="font-size:11px; padding:3px 8px;"
                                       title="Edit permissions">
                                        <i class="fas fa-shield-alt"></i> Permissions
                                    </a>

                                    @if(! $role->is_locked)
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                style="font-size:11px; padding:3px 8px;" title="Rename"
                                                data-bs-toggle="modal" data-bs-target="#editRole{{ $role->id }}">
                                            <i class="fas fa-pen"></i>
                                        </button>

                                        <form method="POST" action="{{ route('roles.destroy', $role->id) }}"
                                              onsubmit="return confirm('Delete role {{ addslashes($role->name) }}? This cannot be undone.');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    style="font-size:11px; padding:3px 8px;" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

@foreach($roles as $role)
    @if(! $role->is_locked)
        <div class="modal fade" id="editRole{{ $role->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('roles.update', $role->id) }}">
                        @csrf @method('PUT')
                        <div class="modal-header py-2">
                            <h6 class="modal-title mb-0">Edit Role — {{ $role->name }}</h6>
                            <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-2">
                                <label class="form-label small mb-1">Name</label>
                                <input type="text" name="name" class="form-control form-control-sm"
                                       value="{{ $role->name }}" required maxlength="60">
                            </div>
                            <div>
                                <label class="form-label small mb-1">Description</label>
                                <input type="text" name="description" class="form-control form-control-sm"
                                       value="{{ $role->description }}" maxlength="255"
                                       placeholder="What this role is responsible for">
                            </div>
                        </div>
                        <div class="modal-footer py-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

<div class="modal fade" id="createRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('roles.store') }}">
                @csrf
                <div class="modal-header py-2">
                    <h6 class="modal-title mb-0">New Role</h6>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small mb-1">Name</label>
                        <input type="text" name="name" class="form-control form-control-sm" required maxlength="60"
                               placeholder="e.g. Warehouse Manager" value="{{ old('name') }}">
                    </div>
                    <div>
                        <label class="form-label small mb-1">Description</label>
                        <input type="text" name="description" class="form-control form-control-sm" maxlength="255"
                               placeholder="Optional" value="{{ old('description') }}">
                    </div>
                    <div class="form-text mt-2">
                        After creating the role you will be taken straight to its permission matrix.
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Create Role</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
