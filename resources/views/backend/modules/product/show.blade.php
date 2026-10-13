@extends('backend.layouts.app')

@section('title', 'Product Details')

@section('content')

    {{-- Action Bar --}}
    <div class="d-flex justify-content-end mb-3 gap-2">
        <a href="{{ route('product.edit', $product->id) }}" class="btn btn-sm btn-warning">
            <i class="fa fa-edit"></i> Edit Product
        </a>
        <a href="{{ route('product.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-list"></i> Product List
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Product Information</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-sm mb-0">
                        <tr>
                        <th width="30%">Product Name</th>
                        <td>{{$product->name}}</td>
                        </tr>
                        <tr>
                            <th>Category</th>
                            <td>
                                @if($product->category)
                                    <span class="badge bg-info">{{$product->category->name}}</span>
                                @else
                                    <span class="badge bg-secondary">No Category</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Product Type</th>
                            <td>
                                @if($product->product_type === 'simple')
                                    <span class="badge bg-primary">Simple Product</span>
                                @else
                                    <span class="badge bg-warning text-dark">Group with Variant</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Slug</th>
                            <td><code>{{$product->slug}}</code></td>
                        </tr>
                        <tr>
                            <th>Short Description</th>
                            <td>{{$product->short_description}}</td>
                        </tr>
                        @if($product->description)
                        <tr>
                            <th>Description</th>
                            <td>{!! $product->description !!}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($product->status == 1)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Sort Order</th>
                            <td>{{$product->sort_order}}</td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td>{{$product->created_at->format('Y-m-d H:i:s')}}</td>
                        </tr>
                        <tr>
                            <th>Updated At</th>
                            <td>{{$product->updated_at->format('Y-m-d H:i:s')}}</td>
                        </tr>
                    </table>
                </div>
            </div>{{-- /card --}}

        </div>{{-- /col-lg-8 --}}

        {{-- Right Column: Images --}}
        <div class="col-lg-4">

            @if($product->primary_photo)
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Primary Photo</h6>
                </div>
                <div class="card-body text-center">
                    <img src="{{ asset('storage/' . $product->primary_photo) }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-height: 280px; object-fit: contain;">
                </div>
            </div>
            @endif

            @if($product->og_image)
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">OG / Social Sharing Image</h6>
                </div>
                <div class="card-body text-center">
                    <img src="{{ asset('storage/' . $product->og_image) }}" alt="OG Image" class="img-fluid rounded" style="max-height: 180px; object-fit: contain;">
                </div>
            </div>
            @endif

            @if($product->photos->count() > 0)
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">Gallery</h6>
                    <span class="badge bg-secondary">{{ $product->photos->count() }}</span>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($product->photos as $photo)
                            <img src="{{ asset('storage/' . $photo->photo_path) }}"
                                 alt="{{ $photo->alt_text ?? 'Gallery' }}"
                                 class="img-thumbnail"
                                 style="width: 80px; height: 80px; object-fit: cover;">
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

        </div>{{-- /col-lg-4 --}}

    </div>{{-- /row --}}

    {{-- Pricing & Stock --}}
    @if($product->isSimpleProduct())
    <div class="row mb-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Pricing & Stock</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="text-center p-3 border rounded">
                                <small class="text-white-50 d-block mb-1">Cost</small>
                                <h5 class="text-white mb-0">৳{{ number_format($product->cost ?? 0, 2) }}</h5>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-center p-3 border rounded">
                                <small class="text-white-50 d-block mb-1">Price</small>
                                <h5 class="text-white mb-0">৳{{ number_format($product->price ?? 0, 2) }}</h5>
                            </div>
                        </div>
                        @if($product->sale_price)
                        <div class="col-6 col-md-3">
                            <div class="text-center p-3 border rounded">
                                <small class="text-white-50 d-block mb-1">Sale Price</small>
                                <h5 class="text-white mb-0">৳{{ number_format($product->sale_price, 2) }}</h5>
                            </div>
                        </div>
                        @endif
                        <div class="col-6 col-md-3">
                            <div class="text-center p-3 border rounded">
                                <small class="text-white-50 d-block mb-1">Stock</small>
                                <h5 class="text-white mb-0">{{ $product->stock ?? 0 }}</h5>
                            </div>
                        </div>
                        @if($product->stock_text)
                        <div class="col-12">
                            <small class="text-muted">Stock Label:</small>
                            <span class="ms-1">{{ $product->stock_text }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- SEO Information --}}
    @if($product->meta_title || $product->meta_description)
    <div class="row mb-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">SEO Information</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-sm mb-0">
                        @if($product->meta_title)
                        <tr>
                            <th width="25%" class="ps-3">Meta Title</th>
                            <td>{{ $product->meta_title }}</td>
                        </tr>
                        @endif
                        @if($product->meta_description)
                        <tr>
                            <th class="ps-3">Meta Description</th>
                            <td>{{ $product->meta_description }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

@endsection
