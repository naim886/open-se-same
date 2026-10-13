@extends('backend.layouts.app')
@section('title', 'Expense Details')
@section('content')
<div class="container-fluid my-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h4 class="mb-0"><i class="fas fa-dollar-sign me-2"></i> Expense Details</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('expense.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="ri-arrow-left-line"></i> Back to List
            </a>
            <a href="{{ route('expense.edit', $expense->id) }}" class="btn btn-sm btn-outline-primary">
                <i class="ri-edit-line"></i> Edit
            </a>
            <button type="button" class="btn btn-sm btn-outline-danger"
                    onclick="confirmDelete({{ $expense->id }})">
                <i class="ri-delete-bin-line"></i> Delete
            </button>
            <form id="del-{{ $expense->id }}"
                  action="{{ route('expense.destroy', $expense->id) }}"
                  method="POST" class="d-none">
                @csrf @method('DELETE')
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- Main Details --}}
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Expense Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th style="width:35%;" class="text-muted">Purpose</th>
                                <td><strong>{{ $expense->purpose }}</strong></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Date</th>
                                <td>{{ $expense->date->format('F d, Y') }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Amount</th>
                                <td><span class="badge bg-info fs-6">৳ {{ number_format($expense->amount, 2) }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Status</th>
                                <td>
                                    <span class="badge bg-{{ $expense->getStatusColor() }} fs-6">
                                        {{ $expense->getStatusLabel() }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Category</th>
                                <td>
                                    @if($expense->category)
                                        <span class="badge bg-secondary">
                                            {{ \App\Models\Expense::CATEGORY_LIST[$expense->category] ?? $expense->category }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Added By</th>
                                <td>{{ $expense->user->name ?? 'System' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Created At</th>
                                <td title="{{ $expense->created_at->format('M d, Y h:i A') }}">
                                    {{ $expense->created_at->diffForHumans() }}
                                    <small class="text-muted">({{ $expense->created_at->format('M d, Y h:i A') }})</small>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Updated At</th>
                                <td title="{{ $expense->updated_at->format('M d, Y h:i A') }}">
                                    {{ $expense->updated_at->diffForHumans() }}
                                    <small class="text-muted">({{ $expense->updated_at->format('M d, Y h:i A') }})</small>
                                </td>
                            </tr>
                            @if($expense->note)
                            <tr>
                                <th class="text-muted align-top">Note</th>
                                <td style="white-space:pre-line;">{{ $expense->note }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Attachment --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-paperclip me-2"></i> Attachment</h5>
                </div>
                <div class="card-body text-center">
                    @if($expense->attachment)
                        @php $ext = strtolower(pathinfo($expense->attachment, PATHINFO_EXTENSION)); @endphp
                        @if(in_array($ext, ['jpg','jpeg','png','gif']))
                            <a href="{{ Storage::url($expense->attachment) }}" target="_blank">
                                <img src="{{ Storage::url($expense->attachment) }}"
                                     class="img-fluid rounded" style="max-height:250px;" alt="attachment">
                            </a>
                        @else
                            <div style="font-size:64px;" class="mb-3">
                                @if($ext === 'pdf')
                                    <i class="fas fa-file-pdf text-danger"></i>
                                @elseif(in_array($ext, ['doc','docx']))
                                    <i class="fas fa-file-word text-primary"></i>
                                @elseif(in_array($ext, ['xls','xlsx']))
                                    <i class="fas fa-file-excel text-success"></i>
                                @else
                                    <i class="fas fa-file text-muted"></i>
                                @endif
                            </div>
                        @endif
                        @if($expense->attachment_title)
                            <p class="mb-2 text-muted">{{ $expense->attachment_title }}</p>
                        @endif
                        <a href="{{ Storage::url($expense->attachment) }}"
                           target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="fas fa-download"></i> Download
                        </a>
                    @else
                        <div class="py-4 text-muted">
                            <i class="fas fa-file fa-3x d-block mb-2"></i>
                            No attachment uploaded.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
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
    .text-muted, small.text-muted { color: rgba(255,255,255,0.5) !important; }
    .table { --bs-table-bg: transparent; --bs-table-color: inherit; }
</style>
@endpush
