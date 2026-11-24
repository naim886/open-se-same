@extends('backend.layouts.app')

@section('title', 'Permissions')

@section('content')
<style>
    /* Role pills */
    .perm-role-pills {
        display: flex;
        gap: 6px;
        overflow-x: auto;
        padding-bottom: 4px;
        scrollbar-width: thin;
    }
    .perm-role-pill {
        border: 1px solid var(--border-subtle, rgba(255,255,255,.12));
        background: var(--bg-surface, #171717);
        border-radius: 999px;
        padding: 6px 14px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--theme-text-color, #E4F5EC);
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all .15s ease;
    }
    .perm-role-pill:hover {
        border-color: var(--border-strong, rgba(255,255,255,.25));
        color: #fff;
    }
    .perm-role-pill.active {
        background: var(--brand-gradient, linear-gradient(135deg, #027B50 0%, #015C3C 62%, #014A30 100%));
        border-color: var(--border-brand, rgba(2,123,80,.55));
        color: #fff;
        box-shadow: var(--shadow-sm, 0 2px 8px rgba(0,0,0,.35));
    }
    .perm-role-pill .pill-count {
        font-size: 10.5px;
        background: rgba(255,255,255,.08);
        border-radius: 999px;
        padding: 1px 7px;
    }
    .perm-role-pill.active .pill-count { background: rgba(0,0,0,.22); }
    .perm-role-pill .dirty-dot {
        width: 7px; height: 7px; border-radius: 50%;
        background: var(--yellow, #FFCF2F); display: none;
    }
    .perm-role-pill.dirty .dirty-dot { display: inline-block; }

    /* Module cards */
    .perm-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        align-items: start;
        gap: 14px;
        margin-top: 14px;
    }
    .perm-module {
        border: 1px solid var(--border-subtle, rgba(255,255,255,.10));
        border-radius: var(--radius, 10px);
        background: var(--bg-surface, #171717);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        color: var(--theme-text-color, #E4F5EC);
    }
    .perm-module-head {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 9px 12px;
        background: var(--bg-surface-alt, #121212);
        border-bottom: 1px solid var(--border-subtle, rgba(255,255,255,.10));
    }
    .perm-module-head i { color: var(--accent, #04A871); width: 16px; text-align: center; }
    .perm-module-title {
        font-weight: 700; font-size: 12.5px; flex: 1;
        color: var(--theme-heading-color, #fff);
    }
    .perm-module-count { font-size: 10.5px; color: rgba(228,245,236,.6); }
    .perm-module-all {
        font-size: 11px; font-weight: 600; color: var(--accent, #04A871); cursor: pointer;
        display: inline-flex; align-items: center; gap: 4px; margin: 0;
    }
    .perm-module-body { padding: 8px 12px 10px; display: flex; flex-direction: column; gap: 2px; }
    .perm-item {
        display: flex; align-items: flex-start; gap: 8px; margin: 0;
        padding: 3px 0; font-size: 12.5px;
        color: var(--theme-text-color, #E4F5EC); cursor: pointer; line-height: 1.35;
    }
    .perm-item input { margin-top: 2px; flex-shrink: 0; }
    .perm-item:hover { color: #fff; }
    .perm-item span { color: inherit; }
    .perm-item.perm-hidden { display: none; }
    .perm-module.perm-hidden { display: none; }

    /* Sticky save bar */
    .perm-savebar {
        position: sticky; bottom: 0; z-index: 1030;
        margin: 18px -4px 0; padding: 10px 16px;
        background: var(--bg-surface, #171717);
        border: 1px solid var(--border-subtle, rgba(255,255,255,.10));
        border-bottom: none;
        box-shadow: 0 -8px 22px rgba(0,0,0,.45);
        display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
        border-radius: var(--radius, 10px) var(--radius, 10px) 0 0;
        color: var(--theme-text-color, #E4F5EC);
    }
    .perm-savebar .pending { font-size: 12.5px; font-weight: 600; color: var(--warning, #FFCF2F); }
    .perm-savebar .pending.zero { color: rgba(228,245,236,.6); font-weight: 500; }
    .perm-savebar .perm-hint { color: rgba(228,245,236,.65); }
    .perm-locked-note {
        font-size: 12px; color: rgba(228,245,236,.75);
        background: rgba(255,255,255,.04);
        border: 1px dashed var(--border-strong, rgba(255,255,255,.18));
        border-radius: 8px; padding: 8px 12px;
    }
    #permGrid .form-check-input { cursor: pointer; margin-top: .15rem; }
    #permGrid .form-check-input:disabled { cursor: not-allowed; opacity: .55; }
</style>

<div class="container-fluid my-4"
     id="permMatrix"
     data-sync-url="{{ route('permissions.sync') }}"
     data-token="{{ csrf_token() }}">

    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-shield-alt me-2 text-primary"></i>Role Permissions</h4>
            <div class="small text-muted mt-1">
                {{ $roles->count() }} role{{ $roles->count() === 1 ? '' : 's' }} &nbsp;·&nbsp;
                {{ $permissionCount }} permissions &nbsp;·&nbsp;
                only changed toggles are sent when you save
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-key me-1"></i>Manage Roles
            </a>
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

    <div class="card border-0 shadow-sm">
        <div class="card-body py-3">
            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-2">
                <div class="perm-role-pills" id="rolePills">
                    @foreach($roles as $role)
                        <button type="button"
                                class="perm-role-pill {{ (int) $role->id === $activeRoleId ? 'active' : '' }}"
                                data-role="{{ $role->id }}"
                                data-name="{{ $role->name }}"
                                data-locked="{{ $role->is_locked ? 1 : 0 }}">
                            <span class="dirty-dot"></span>
                            @if($role->is_locked)<i class="fas fa-lock" style="font-size:10px;"></i>@endif
                            {{ $role->name }}
                            <span class="pill-count" data-count-for="{{ $role->id }}">
                                {{ $role->is_locked ? $permissionCount : 0 }}
                            </span>
                        </button>
                    @endforeach
                </div>

                <div class="d-flex align-items-center gap-2">
                    <input type="search" id="permSearch" class="form-control form-control-sm"
                           placeholder="Filter permissions…" style="min-width:220px;">
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                <div id="permLockedNote" class="perm-locked-note d-none">
                    <i class="fas fa-lock me-1"></i>
                    This role is locked. It always has every permission and cannot be modified.
                </div>
                <div class="d-flex gap-2 ms-auto">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="permExpand">
                        <i class="fas fa-compress-alt me-1"></i><span>Collapse all</span>
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm" id="permGrantAll">
                        <i class="fas fa-check-double me-1"></i>Select all
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="permClearAll">
                        <i class="fas fa-eraser me-1"></i>Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="perm-grid" id="permGrid"></div>

    <noscript>
        <div class="alert alert-warning mt-3">
            <i class="fas fa-exclamation-triangle me-1"></i>
            The permission matrix needs JavaScript. The current permissions are listed below for reference.
        </div>
        @foreach($groups as $group)
            <div class="card border-0 shadow-sm mb-2">
                <div class="card-header py-2 fw-bold small">{{ $group['label'] }}</div>
                <div class="card-body py-2">
                    @foreach($group['permissions'] as $permission)
                        <span class="badge bg-light text-dark border me-1 mb-1">{{ $permission['label'] }}</span>
                    @endforeach
                </div>
            </div>
        @endforeach
    </noscript>

    <div class="perm-savebar">
        <span class="pending zero" id="permPending">No unsaved changes</span>
        <span class="small perm-hint" id="permHint">Editing <strong id="permActiveName">—</strong></span>
        <div class="ms-auto d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="permReset" disabled>
                <i class="fas fa-undo me-1"></i>Discard changes
            </button>
            <button type="button" class="btn btn-primary btn-sm" id="permSave" disabled>
                <i class="fas fa-save me-1"></i>Save changes
            </button>
        </div>
    </div>
</div>

<script type="application/json" id="permData">
    {!! json_encode([
        'roles'  => $roles->map(fn ($role) => [
            'id'          => (int) $role->id,
            'name'        => $role->name,
            'locked'      => (bool) $role->is_locked,
            'users'       => (int) $role->users_count,
        ])->values(),
        'groups' => $groups,
        'matrix' => (object) $matrix,
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
</script>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    var root = document.getElementById('permMatrix');
    var dataNode = document.getElementById('permData');
    if (!root || !dataNode) return;

    var payload = JSON.parse(dataNode.textContent || '{}');
    var roles = payload.roles || [];
    var groups = payload.groups || [];
    var matrix = payload.matrix || {};

    var syncUrl = root.dataset.syncUrl;
    var token = root.dataset.token;

    // ── working state ────────────────────────────────────────────────────
    // base   : what the server currently has
    // current: what the user has toggled
    var base = {}, current = {}, pending = {}, counts = {};

    roles.forEach(function (role) {
        var ids = matrix[role.id] || [];
        base[role.id] = new Set(ids.map(Number));
        current[role.id] = new Set(base[role.id]);
        pending[role.id] = 0;
        counts[role.id] = ids.length;
    });

    var activeRole = {{ (int) $activeRoleId }};
    if (!base.hasOwnProperty(activeRole) && roles.length) {
        activeRole = roles[0].id;
    }

    var permIndex = {};   // permId -> {name, label, group}
    groups.forEach(function (group) {
        group.permissions.forEach(function (permission) {
            permIndex[permission.id] = {
                name: permission.name,
                label: permission.label,
                group: group.key
            };
        });
    });

    // ── build the grid once, then only toggle checkbox states on switch ──
    var grid = document.getElementById('permGrid');
    var boxes = [];        // [{el, id, module}]
    var moduleEls = {};    // module -> {root, countEl, allEl, boxes:[]}

    var fragment = document.createDocumentFragment();

    groups.forEach(function (group) {
        var module = document.createElement('div');
        module.className = 'perm-module';
        module.dataset.module = group.key;

        var head = document.createElement('div');
        head.className = 'perm-module-head';

        var icon = document.createElement('i');
        icon.className = 'fas fa-' + group.icon;

        var title = document.createElement('span');
        title.className = 'perm-module-title';
        title.textContent = group.label;

        var count = document.createElement('span');
        count.className = 'perm-module-count';

        var allLabel = document.createElement('label');
        allLabel.className = 'perm-module-all';
        var allBox = document.createElement('input');
        allBox.type = 'checkbox';
        allBox.className = 'form-check-input';
        allLabel.appendChild(allBox);
        allLabel.appendChild(document.createTextNode('All'));

        head.appendChild(icon);
        head.appendChild(title);
        head.appendChild(count);
        head.appendChild(allLabel);

        var body = document.createElement('div');
        body.className = 'perm-module-body';

        var entry = { root: module, countEl: count, allEl: allBox, boxes: [] };

        group.permissions.forEach(function (permission) {
            var label = document.createElement('label');
            label.className = 'perm-item';
            label.dataset.search = (permission.label + ' ' + permission.name).toLowerCase();

            var box = document.createElement('input');
            box.type = 'checkbox';
            box.className = 'form-check-input';
            box.value = permission.id;
            box.dataset.perm = permission.id;
            box.dataset.module = group.key;

            var text = document.createElement('span');
            text.textContent = permission.label;

            label.appendChild(box);
            label.appendChild(text);
            body.appendChild(label);

            var item = { el: box, id: Number(permission.id), module: group.key, row: label };
            boxes.push(item);
            entry.boxes.push(item);
        });

        module.appendChild(head);
        module.appendChild(body);
        fragment.appendChild(module);

        moduleEls[group.key] = entry;
    });

    grid.appendChild(fragment);

    // ── helpers ──────────────────────────────────────────────────────────
    function activeLocked() {
        var role = roles.find(function (r) { return r.id === activeRole; });
        return !!(role && role.locked);
    }

    function paint() {
        var set = current[activeRole];
        var locked = activeLocked();

        boxes.forEach(function (item) {
            var checked = set.has(item.id) || locked;
            if (item.el.checked !== checked) item.el.checked = checked;
            item.el.disabled = locked;
        });

        Object.keys(moduleEls).forEach(function (key) {
            var entry = moduleEls[key];
            var granted = 0;
            entry.boxes.forEach(function (item) {
                if (set.has(item.id) || locked) granted++;
            });
            entry.countEl.textContent = granted + '/' + entry.boxes.length;
            entry.allEl.checked = granted === entry.boxes.length && entry.boxes.length > 0;
            entry.allEl.indeterminate = granted > 0 && granted < entry.boxes.length;
            entry.allEl.disabled = locked;
        });

        var role = roles.find(function (r) { return r.id === activeRole; });
        document.getElementById('permActiveName').textContent = role ? role.name : '—';
        document.getElementById('permLockedNote').classList.toggle('d-none', !locked);

        document.querySelectorAll('.perm-role-pill').forEach(function (pill) {
            var id = Number(pill.dataset.role);
            pill.classList.toggle('active', id === activeRole);
            pill.classList.toggle('dirty', pending[id] > 0);

            var badge = pill.querySelector('[data-count-for]');
            if (badge) badge.textContent = roleCount(id);
        });

        updateSaveBar();
    }

    function roleCount(id) {
        var role = roles.find(function (r) { return r.id === id; });
        return role && role.locked ? boxes.length : current[id].size;
    }

    function totalPending() {
        return Object.keys(pending).reduce(function (sum, id) { return sum + pending[id]; }, 0);
    }

    function updateSaveBar() {
        var total = totalPending();
        var label = document.getElementById('permPending');
        var save = document.getElementById('permSave');
        var reset = document.getElementById('permReset');

        if (total === 0) {
            label.textContent = 'No unsaved changes';
            label.classList.add('zero');
        } else {
            label.textContent = total + ' unsaved change' + (total === 1 ? '' : 's') +
                ' across ' + Object.keys(pending).filter(function (id) { return pending[id] > 0; }).length + ' role(s)';
            label.classList.remove('zero');
        }

        save.disabled = total === 0;
        reset.disabled = total === 0;
    }

    function toggle(checkbox) {
        if (activeLocked()) return;

        var id = Number(checkbox.value);
        var set = current[activeRole];
        var inBase = base[activeRole].has(id);

        if (checkbox.checked) {
            if (!set.has(id)) {
                set.add(id);
                pending[activeRole] += inBase ? -1 : 1;
            }
        } else if (set.has(id)) {
            set.delete(id);
            pending[activeRole] += inBase ? 1 : -1;
        }

        pending[activeRole] = Math.max(0, pending[activeRole]);

        paint();
    }

    // ── events ───────────────────────────────────────────────────────────
    grid.addEventListener('change', function (event) {
        var box = event.target;
        if (box.matches('input[type="checkbox"]') && box.dataset.perm) {
            toggle(box);
        }
    });

    grid.addEventListener('change', function (event) {
        var box = event.target;
        if (!box.dataset.module || box.dataset.perm) return;

        var entry = moduleEls[box.dataset.module];
        if (!entry) return;

        entry.boxes.forEach(function (item) {
            if (item.el.checked !== box.checked) {
                item.el.checked = box.checked;
                toggle(item.el);
            }
        });
    });

    // Module "All" boxes carry data-module but no data-perm.
    Object.keys(moduleEls).forEach(function (key) {
        moduleEls[key].allEl.dataset.module = key;
    });

    document.getElementById('rolePills').addEventListener('click', function (event) {
        var pill = event.target.closest('.perm-role-pill');
        if (!pill) return;

        var id = Number(pill.dataset.role);
        if (!base.hasOwnProperty(id) || id === activeRole) return;

        activeRole = id;
        paint();
    });

    var search = document.getElementById('permSearch');
    search.addEventListener('input', function () {
        var term = search.value.trim().toLowerCase();

        Object.keys(moduleEls).forEach(function (key) {
            var entry = moduleEls[key];
            var visible = 0;

            entry.boxes.forEach(function (item) {
                var match = term === '' || item.row.dataset.search.indexOf(term) !== -1;
                item.row.classList.toggle('perm-hidden', !match);
                if (match) visible++;
            });

            entry.root.classList.toggle('perm-hidden', visible === 0);
        });
    });

    var collapsed = false;
    document.getElementById('permExpand').addEventListener('click', function () {
        collapsed = !collapsed;
        grid.style.display = collapsed ? 'none' : '';
        this.querySelector('span').textContent = collapsed ? 'Expand all' : 'Collapse all';
    });

    document.getElementById('permGrantAll').addEventListener('click', function () {
        if (activeLocked()) return;
        boxes.forEach(function (item) {
            if (!item.el.checked) {
                item.el.checked = true;
                toggle(item.el);
            }
        });
    });

    document.getElementById('permClearAll').addEventListener('click', function () {
        if (activeLocked()) return;
        boxes.forEach(function (item) {
            if (item.el.checked) {
                item.el.checked = false;
                toggle(item.el);
            }
        });
    });

    document.getElementById('permReset').addEventListener('click', function () {
        Object.keys(current).forEach(function (id) {
            current[id] = new Set(base[id]);
            pending[id] = 0;
        });
        paint();
    });

    document.getElementById('permSave').addEventListener('click', function () {
        var changes = [];

        Object.keys(current).forEach(function (id) {
            if (!pending[id]) return;

            var roleId = Number(id);
            var grant = [];
            var revoke = [];

            current[id].forEach(function (perm) {
                if (!base[id].has(perm)) grant.push(perm);
            });
            base[id].forEach(function (perm) {
                if (!current[id].has(perm)) revoke.push(perm);
            });

            if (grant.length || revoke.length) {
                changes.push({ role_id: roleId, grant: grant, revoke: revoke });
            }
        });

        if (!changes.length) return;

        var button = this;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving…';

        fetch(syncUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ roles: changes })
        })
        .then(function (response) {
            return response.json().then(function (body) {
                return { ok: response.ok, status: response.status, body: body };
            });
        })
        .then(function (result) {
            if (!result.ok || !result.body.ok) {
                throw new Error(result.body.message || 'Could not save permissions.');
            }

            // Server accepted: the working state becomes the new baseline.
            changes.forEach(function (change) {
                base[change.role_id] = new Set(current[change.role_id]);
                pending[change.role_id] = 0;
            });

            if (result.body.counts) {
                Object.keys(result.body.counts).forEach(function (id) {
                    var role = roles.find(function (r) { return r.id === Number(id); });
                    if (role) role.count = result.body.counts[id];
                });
            }

            paint();
            toast(result.body.message || 'Saved.', 'success');
        })
        .catch(function (error) {
            toast(error.message || 'Could not save permissions.', 'danger');
            paint();
        })
        .finally(function () {
            button.innerHTML = '<i class="fas fa-save me-1"></i>Save changes';
            updateSaveBar();
        });
    });

    function toast(message, kind) {
        if (window.Swal) {
            window.Swal.fire({
                toast: true,
                position: 'top-end',
                icon: kind === 'success' ? 'success' : 'error',
                title: message,
                showConfirmButton: false,
                timer: 2600,
                timerProgressBar: true
            });
            return;
        }

        var box = document.createElement('div');
        box.className = 'alert alert-' + kind + ' position-fixed shadow';
        box.style.cssText = 'top:16px;right:16px;z-index:2000;font-size:13px;';
        box.textContent = message;
        document.body.appendChild(box);
        setTimeout(function () { box.remove(); }, 2600);
    }

    window.addEventListener('beforeunload', function (event) {
        if (totalPending() > 0) {
            event.preventDefault();
            event.returnValue = '';
        }
    });

    paint();
})();
</script>
@endpush
