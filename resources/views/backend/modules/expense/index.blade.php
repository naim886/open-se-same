@extends('backend.layouts.app')

@section('title', 'Expense List')

@section('content')
<div class="container-fluid my-4">

    {{-- Header --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h4 class="mb-0"><i class="fas fa-dollar-sign me-2"></i> Expense Management</h4>
        <a href="{{ route('expense.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Add Expense
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-md-3 col-6">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-title bg-light text-primary rounded-3" style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-list"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-0" style="font-size:12px;">Total Records</p>
                        <h5 class="mb-0">{{ $stats['count'] }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-title bg-light text-info rounded-3" style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-coins"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-0" style="font-size:12px;">Total Spent</p>
                        <h5 class="mb-0">৳ {{ number_format($stats['total'], 2) }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-title bg-light text-success rounded-3" style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-check-circle"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-0" style="font-size:12px;">Approved</p>
                        <h5 class="mb-0">৳ {{ number_format($stats['approved'], 2) }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-title bg-light text-warning rounded-3" style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-clock"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-0" style="font-size:12px;">Pending</p>
                        <h5 class="mb-0">৳ {{ number_format($stats['pending'], 2) }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center"
             style="cursor:pointer;"
             onclick="$(this).find('.filter-arrow').toggleClass('fa-chevron-up fa-chevron-down'); $('#exp-filter').toggleClass('d-none');">
            <span><i class="fas fa-bars me-2"></i> Filter</span>
            <i class="filter-arrow fas {{ request()->anyFilled(['status','category','date_from','date_to','search']) ? 'fa-chevron-up' : 'fa-chevron-down' }}"></i>
        </div>
        <div id="exp-filter" class="card-body {{ request()->anyFilled(['status','category','date_from','date_to','search']) ? '' : 'd-none' }}">
            <form method="GET" action="{{ route('expense.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1">Search Purpose</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Purpose..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach(\App\Models\Expense::STATUS_LIST as $k => $v)
                            <option value="{{ $k }}" {{ request('status') == $k ? 'selected':'' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Category</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach(\App\Models\Expense::CATEGORY_LIST as $k => $v)
                            <option value="{{ $k }}" {{ request('category') == $k ? 'selected':'' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Date From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Date To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                    <a href="{{ route('expense.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-sync"></i></a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table theme-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Purpose</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Added By</th>
                            <th>Attachment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td>{{ $expenses->firstItem() + $loop->index }}</td>
                            <td>
                                <strong>{{ $expense->purpose }}</strong>
                                @if($expense->note)
                                    <br><small class="text-muted">{{ Str::limit($expense->note, 60) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($expense->category)
                                    <span class="badge bg-secondary">{{ \App\Models\Expense::CATEGORY_LIST[$expense->category] ?? $expense->category }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $expense->date->format('M d, Y') }}</td>
                            <td><strong>৳ {{ number_format($expense->amount, 2) }}</strong></td>
                            <td>
                                <span class="badge bg-{{ $expense->getStatusColor() }}">
                                    {{ $expense->getStatusLabel() }}
                                </span>
                            </td>
                            <td>{{ $expense->user->name ?? 'System' }}</td>
                            <td>
                                @if($expense->attachment)
                                    @php $ext = pathinfo($expense->attachment, PATHINFO_EXTENSION); @endphp
                                    @if(in_array(strtolower($ext), ['jpg','jpeg','png','gif']))
                                        <a href="{{ Storage::url($expense->attachment) }}" target="_blank">
                                            <img src="{{ Storage::url($expense->attachment) }}"
                                                 style="width:40px;height:40px;object-fit:cover;border-radius:4px;" alt="attachment">
                                        </a>
                                    @else
                                        <a href="{{ Storage::url($expense->attachment) }}" target="_blank" class="btn btn-xs btn-outline-secondary">
                                            <i class="fas fa-file"></i> {{ $expense->attachment_title ?: 'File' }}
                                        </a>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('expense.show', $expense->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('expense.edit', $expense->id) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="confirmDelete({{ $expense->id }})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <form id="del-{{ $expense->id }}"
                                      action="{{ route('expense.destroy', $expense->id) }}"
                                      method="POST" class="d-none">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-inbox fa-2x d-block mb-2 text-muted"></i>
                                No expenses found. <a href="{{ route('expense.create') }}">Add one</a>.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($expenses->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $expenses->links() }}
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this expense? This cannot be undone.')) {
        document.getElementById('del-' + id).submit();
    }
}
</script>
@endpush

@push('styles')
<style>
    .card, .card-header, .card-body {
        background: transparent !important;
        color: inherit !important;
    }
    .card { border: 1px solid rgba(255,255,255,0.15) !important; }
    .card-header { border-bottom: 1px solid rgba(255,255,255,0.15) !important; }
    .table td, .table th {
        background-color: transparent !important;
        color: inherit !important;
        border-color: rgba(255,255,255,0.1) !important;
    }
    .table { --bs-table-bg: transparent; --bs-table-color: inherit; }
    .text-muted, small.text-muted { color: rgba(255,255,255,0.5) !important; }
    .form-control, .form-select {
        background: rgba(255,255,255,0.07) !important;
        color: inherit !important;
        border-color: rgba(255,255,255,0.2) !important;
    }
    .form-control::placeholder { color: rgba(255,255,255,0.35) !important; }
</style>
@endpush
