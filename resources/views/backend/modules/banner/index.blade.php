@extends('backend.layouts.app')

@section('content')
    <div class="row justify-content-center my-5">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table class="table theme-table">
                    <thead>
                    <tr>
                        <th>SL</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Button Text</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($banners as $banner)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                @if($banner->image)
                                    <img src="{{ asset('storage/' . $banner->image) }}"
                                         alt="{{ $banner->title }}"
                                         class="img-thumbnail"
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-secondary d-flex align-items-center justify-content-center"
                                         style="width: 50px; height: 50px; border-radius: 4px;">
                                        <i class="fa fa-image text-white"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $banner->title }}</strong>
                                </div>
                            </td>
                            <td>{{ Str::limit($banner->description, 50) }}</td>
                            <td>{{ $banner->button_text ?? '-' }}</td>
                            <td>{{ $banner->sort_order }}</td>
                            <td>
                                @if($banner->isActive())
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{route('banner.show', $banner->id)}}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{route('banner.edit', $banner->id)}}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{route('banner.destroy', $banner->id)}}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this banner?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No banners found</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($banners->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $banners->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
