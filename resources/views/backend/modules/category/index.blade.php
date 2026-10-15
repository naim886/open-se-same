@extends('backend.layouts.app')
@section('content')
    <div class="row justify-content-center my-5">
        <div class="col-lg-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Categories</h5>
                <a href="{{ route('category.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Create Category
                </a>
            </div>
            <div class="table-responsive">
                <table class="table theme-table">
                    <thead>
                    <tr>
                        <th>SL</th>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Parent</th>
                        <th>Banner</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($categories as $category)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>
                                @if($category->icon_url)
                                    <img src="{{ $category->icon_url }}"
                                         alt="{{$category->name}}"
                                         class="img-thumbnail"
                                         style="width: 70px; height: 70px; object-fit: cover;">
                                @else
                                    <div class="bg-secondary d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; border-radius: 4px;">
                                        <i class="fa fa-image text-white"></i>
                                    </div>
                                @endif
                            </td>
                            <td>{{$category->name}}</td>
                            <td>
                                @if($category->parent)
                                    <span class="badge bg-secondary">{{ $category->parent->name }}</span>
                                @else
                                    <span class="text-muted">Top Level</span>
                                @endif
                            </td>
                            <td>
                                @if($category->banner_url)
                                    <img src="{{ $category->banner_url }}"
                                         alt="{{$category->name}}"
                                         class="img-thumbnail"
                                         style="width: 50px; height: 50px; object-fit: cover;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="bg-secondary align-items-center justify-content-center" style="width: 50px; height: 50px; border-radius: 4px; display: none;">
                                        <i class="fa fa-image text-white"></i>
                                    </div>
                                @else
                                    <div class="bg-secondary d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; border-radius: 4px;">
                                        <i class="fa fa-image text-white"></i>
                                    </div>
                                @endif
                            </td>
                            <td>{{$category->sort_order}}</td>
                            <td>
                                @if($category->status == 1)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{route('category.show', $category->id)}}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{route('category.edit', $category->id)}}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{route('category.destroy', $category->id)}}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if($categories->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
