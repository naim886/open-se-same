@extends('backend.layouts.app')

@section('title', 'Customer Tags')

@section('content')
<div class="container-fluid my-4">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-tags me-2"></i>Customer Tags</h4>
            <div class="small text-muted mt-1">
                Labels you can attach to customers, e.g. VIP or frequent buyer.
            </div>
        </div>
        <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Customers
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Create ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent fw-bold">
            <i class="fas fa-plus me-1"></i>New Tag
        </div>
        <div class="card-body">
            <form action="{{ route('customers.tags.store') }}" method="POST" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-5">
                    <label class="form-label mb-1 small fw-600">Name</label>
                    <input type="text" name="name" class="form-control form-control-sm"
                           value="{{ old('name') }}" placeholder="e.g. VIP" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1 small fw-600">Colour</label>
                    <input type="color" name="color" class="form-control form-control-sm form-control-color"
                           value="{{ old('color', '#6c757d') }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-save me-1"></i>Create Tag
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── List ── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent fw-bold">
            <i class="fas fa-list me-1"></i>All Tags
            <span class="badge bg-secondary ms-1">{{ $tags->count() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0 align-middle">
                <thead class="thead-dm">
                    <tr>
                        <th class="ps-3" style="width:190px;">Preview</th>
                        <th style="min-width:320px;">Name &amp; Colour</th>
                        <th class="text-end">Customers</th>
                        <th class="text-end pe-3" style="width:200px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($tags as $tag)
                    <tr>
                        <td class="ps-3">
                            <span class="badge"
                                  style="background: {{ $tag->color }}; font-size:12px; padding:5px 10px;">
                                {{ $tag->name }}
                            </span>
                        </td>
                        <td>
                            <form action="{{ route('customers.tags.update', $tag->id) }}" method="POST"
                                  class="d-flex gap-2 align-items-center" id="tagForm{{ $tag->id }}">
                                @csrf
                                @method('PUT')
                                <input type="text" name="name" value="{{ $tag->name }}"
                                       class="form-control form-control-sm" style="max-width:200px;" required>
                                <input type="color" name="color" value="{{ $tag->color }}"
                                       class="form-control form-control-sm form-control-color" style="max-width:56px;">
                        </td>
                        <td class="text-end">{{ number_format($tag->assignments_count) }}</td>
                        <td class="text-end pe-3">
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-save"></i>
                                </button>
                            </form>
                            <form action="{{ route('customers.tags.destroy', $tag->id) }}" method="POST"
                                  class="d-inline" onsubmit="return confirm('Delete the “{{ $tag->name }}” tag? It will be removed from all customers.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No tags yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
