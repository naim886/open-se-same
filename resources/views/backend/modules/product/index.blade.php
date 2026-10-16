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
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>SKU</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>
                                @if($product->primary_photo)
                                    <img src="{{asset('storage/' . ($product->primary_photo_thumb ?? $product->primary_photo))}}" alt="{{$product->name}}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-secondary d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; border-radius: 4px;">
                                        <i class="fa fa-image text-white"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <strong>{{$product->name}}</strong>
                                    <br>
                                    <small class="text-muted">{{Str::limit($product->short_description, 50)}}</small>
                                </div>
                            </td>
                            <td>
                                @if($product->category)
                                    <span class="badge bg-info">{{$product->category->name}}</span>
                                @else
                                    <span class="badge bg-secondary">No Category</span>
                                @endif
                            </td>
                            <td>
                                @if($product->brand)
                                    <span class="badge bg-primary">{{$product->brand->name}}</span>
                                @else
                                    <span class="badge bg-secondary">No Brand</span>
                                @endif
                            </td>
                            <td>
                                @if($product->sku)
                                    <code>{{ $product->sku }}</code>
                                @elseif($product->isVariantProduct())
                                    <span class="text-muted">Per variant</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($product->product_type === 'simple')
                                    <span class="badge bg-primary">Simple</span>
                                @else
                                    <span class="badge bg-warning">Variant</span>
                                @endif
                            </td>
                            <td>
                                @if($product->isSimpleProduct())
                                    @if($product->sale_price)
                                        <div>
                                            <span class="text-decoration-line-through text-muted">${{$product->price}}</span>
                                            <br>
                                            <span class="text-success fw-bold">${{$product->sale_price}}</span>
                                        </div>
                                    @else
                                        <span class="fw-bold">${{$product->price}}</span>
                                    @endif
                                @else
                                    <span class="text-muted">Variant Pricing</span>
                                @endif
                            </td>
                            <td>
                                @if($product->isSimpleProduct())
                                    @if($product->stock > 0)
                                        <span class="badge bg-success">{{$product->stock}} in stock</span>
                                    @else
                                        <span class="badge bg-danger">Out of stock</span>
                                    @endif
                                @else
                                    @if($product->activeVariations->isNotEmpty())
                                        <div class="d-flex flex-column gap-1">
                                            @foreach($product->activeVariations as $variation)
                                                <div class="d-flex align-items-center gap-1" style="font-size:12px;">
                                                    <span class="text-muted" style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $variation->label }}">{{ $variation->label }}</span>:
                                                    @if(($variation->stock ?? 0) > 0)
                                                        <span class="badge bg-success">{{ $variation->stock }}</span>
                                                    @else
                                                        <span class="badge bg-danger">0</span>
                                                    @endif
                                                    @if($variation->sku)
                                                        <code style="font-size:11px;">{{ $variation->sku }}</code>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">No variants</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($product->status == 1)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>{{$product->created_at->format('Y-m-d')}}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{route('product.show', $product->id)}}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{route('product.edit', $product->id)}}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{route('product.destroy', $product->id)}}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?')">
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
            <div class="d-flex justify-content-center">
                {{$products->links()}}
            </div>
        </div>
    </div>
@endsection

