@extends('backend.layouts.app')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table theme-table">
                <thead>
                <tr>
                    <th>SL</th>
                    <th>Cover</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>FAQs</th>
                    <th>Views</th>
                    <th>Read Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse($blogs as $blog)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            @if($blog->cover_photo)
                                <img src="{{ asset('storage/' . $blog->cover_photo) }}" alt="{{ $blog->title }}"
                                     class="img-thumbnail" style="width:50px; height:50px; object-fit:cover;">
                            @else
                                <div class="bg-secondary d-flex align-items-center justify-content-center"
                                     style="width:50px; height:50px; border-radius:4px;">
                                    <i class="fa fa-image text-white"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $blog->title }}</strong>
                            <div><small class="text-muted"><code>{{ $blog->slug }}</code></small></div>
                        </td>
                        <td>{{ $blog->author ?? '—' }}</td>
                        <td><span class="badge bg-secondary">{{ $blog->faqs_count ?? $blog->faqs()->count() }}</span></td>
                        <td><span class="badge bg-info text-dark"><i class="fas fa-eye me-1"></i>{{ number_format($blog->view_count) }}</span></td>
                        <td><span class="badge bg-secondary"><i class="fas fa-clock me-1"></i>{{ $blog->read_time }}m</span></td>
                        <td>
                            @if($blog->isActive())
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('blog.show', $blog) }}" class="btn btn-sm btn-info me-1">
                                <i class="fa fa-eye"></i>
                            </a>
                            <a href="{{ route('blog.edit', $blog) }}" class="btn btn-sm btn-warning me-1">
                                <i class="fa fa-edit"></i>
                            </a>
                            <form action="{{ route('blog.destroy', $blog) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this blog?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">No blogs found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $blogs->links() }}</div>
    </div>
</div>
@endsection
