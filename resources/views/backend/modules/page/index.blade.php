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
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse($pages as $page)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            @if($page->cover_photo)
                                <img src="{{ asset('storage/' . $page->cover_photo) }}" alt="{{ $page->title }}"
                                     class="img-thumbnail" style="width:50px; height:50px; object-fit:cover;">
                            @else
                                <div class="bg-secondary d-flex align-items-center justify-content-center"
                                     style="width:50px; height:50px; border-radius:4px;">
                                    <i class="fa fa-file text-white"></i>
                                </div>
                            @endif
                        </td>
                        <td><strong>{{ $page->title }}</strong></td>
                        <td><code>{{ $page->slug }}</code></td>
                        <td>
                            @if($page->isActive())
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('page.show', $page) }}" class="btn btn-sm btn-info me-1">
                                <i class="fa fa-eye"></i>
                            </a>
                            <a href="{{ route('page.edit', $page) }}" class="btn btn-sm btn-warning me-1">
                                <i class="fa fa-edit"></i>
                            </a>
                            <form action="{{ route('page.destroy', $page) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this page?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No pages found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $pages->links() }}</div>
    </div>
</div>
@endsection
