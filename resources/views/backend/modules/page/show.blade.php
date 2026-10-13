@extends('backend.layouts.app')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-lg-9">
        <div class="card shadow-sm">
            <div class="card-body">
                @if($page->cover_photo)
                    <img src="{{ asset('storage/' . $page->cover_photo) }}" alt="{{ $page->title }}"
                         class="img-fluid rounded mb-4" style="max-height:280px; object-fit:cover; width:100%;">
                @endif

                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                    <h4 class="fw-bold mb-0">{{ $page->title }}</h4>
                    @if($page->isActive())
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Inactive</span>
                    @endif
                </div>

                <table class="table table-bordered table-sm mb-4">
                    <tr><th style="width:160px;">Slug</th><td><code>{{ $page->slug }}</code></td></tr>
                    <tr><th>Meta Title</th><td>{{ $page->meta_title ?? '—' }}</td></tr>
                    <tr><th>Meta Description</th><td>{{ $page->meta_description ?? '—' }}</td></tr>
                    <tr><th>Created</th><td>{{ $page->created_at->toDayDateTimeString() }}</td></tr>
                    <tr><th>Updated</th><td>{{ $page->updated_at->toDayDateTimeString() }}</td></tr>
                </table>

                <h6 class="fw-bold mb-2">Description</h6>
                <div class="border rounded p-3">
                    {!! $page->description ?? '<em class="text-muted">No description.</em>' !!}
                </div>

                <div class="mt-4">
                    <a href="{{ route('page.edit', $page) }}" class="btn btn-warning me-2">
                        <i class="fa fa-edit me-1"></i> Edit
                    </a>
                    <a href="{{ route('page.index') }}" class="btn btn-secondary">
                        <i class="fa fa-list me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
