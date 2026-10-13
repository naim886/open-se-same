@extends('backend.layouts.app')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-lg-9">
        <div class="card shadow-sm">
            <div class="card-body">
                @if($blog->cover_photo)
                    <img src="{{ asset('storage/' . $blog->cover_photo) }}" alt="{{ $blog->title }}"
                         class="img-fluid rounded mb-4" style="max-height:280px; object-fit:cover; width:100%;">
                @endif

                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                    <h4 class="fw-bold mb-0">{{ $blog->title }}</h4>
                    @if($blog->isActive())
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Inactive</span>
                    @endif
                </div>

                <table class="table table-bordered table-sm mb-4">
                    <tr><th style="width:160px;">Slug</th><td><code>{{ $blog->slug }}</code></td></tr>
                    <tr><th>Author</th><td>{{ $blog->author ?? '—' }}</td></tr>
                    <tr><th>Read Time</th><td><i class="fas fa-clock me-1"></i>{{ $blog->read_time }} min</td></tr>
                    <tr><th>Views</th><td><i class="fas fa-eye me-1"></i>{{ number_format($blog->view_count) }}</td></tr>
                    <tr><th>Meta Title</th><td>{{ $blog->meta_title ?? '—' }}</td></tr>
                    <tr><th>Meta Description</th><td>{{ $blog->meta_description ?? '—' }}</td></tr>
                    <tr><th>Created</th><td>{{ $blog->created_at->toDayDateTimeString() }}</td></tr>
                </table>

                <h6 class="fw-bold mb-2">Description</h6>
                <div class="border rounded p-3 mb-4">
                    {!! $blog->description ?? '<em class="text-muted">No description.</em>' !!}
                </div>

                @if($blog->faqs->isNotEmpty())
                    <h6 class="fw-bold mb-2"><i class="fa fa-question-circle me-1"></i>FAQs ({{ $blog->faqs->count() }})</h6>
                    <div class="accordion" id="faqAccordion">
                        @foreach($blog->faqs as $faq)
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#faq{{ $faq->id }}">
                                        {{ $faq->question }}
                                    </button>
                                </h2>
                                <div id="faq{{ $faq->id }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">{{ $faq->answer }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="mt-4">
                    <a href="{{ route('blog.edit', $blog) }}" class="btn btn-warning me-2">
                        <i class="fa fa-edit me-1"></i> Edit
                    </a>
                    <a href="{{ route('blog.index') }}" class="btn btn-secondary">
                        <i class="fa fa-list me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
