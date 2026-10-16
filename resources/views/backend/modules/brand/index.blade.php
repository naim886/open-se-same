@extends('backend.layouts.app')
@section('content')
    <div class="row justify-content-center my-5">
        <div class="col-lg-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Brands</h5>
                <a href="{{ route('brand.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Create Brand
                </a>
            </div>
            <div class="table-responsive">
                <table class="table theme-table">
                    <thead>
                    <tr>
                        <th>SL</th>
                        <th>Logo</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Banner</th>
                        <th>Products</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($brands as $brand)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>
                                @if($brand->logo_url)
                                    <img src="{{ $brand->logo_url }}"
                                         alt="{{$brand->name}}"
                                         class="img-thumbnail"
                                         style="width: 70px; height: 70px; object-fit: contain;">
                                @else
                                    <div class="bg-secondary d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; border-radius: 4px;">
                                        <i class="fa fa-image text-white"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{$brand->name}}</strong>
                                <br>
                                <small class="text-muted">{{Str::limit($brand->short_description, 50)}}</small>
                            </td>
                            <td><code>{{$brand->slug}}</code></td>
                            <td>
                                @if($brand->banner_url)
                                    <img src="{{ $brand->banner_url }}"
                                         alt="{{$brand->name}}"
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
                            <td>
                                <span class="badge bg-info">{{ $brand->products_count }} products</span>
                            </td>
                            <td>{{$brand->sort_order}}</td>
                            <td>
                                @if($brand->isActive())
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{route('brand.show', $brand->id)}}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{route('brand.edit', $brand->id)}}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{route('brand.destroy', $brand->id)}}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this brand?')">
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
            @if($brands->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $brands->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
