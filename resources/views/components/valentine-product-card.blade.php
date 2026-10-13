<a href="{{ route('valentine.product.detail', $product->slug) }}" class="text-decoration-none w-100 d-block h-100">
    <div class="valentine-product-card h-100 d-flex flex-column">
                                <span class="valentine-badge">
                                    <i class="fas fa-heart me-1"></i> Valentine Special
                                </span>
        <div class="valentine-product-image">
            @if($product->sale_price && $product->sale_price < $product->price)
                @php
                    $discount = round((($product->price - $product->sale_price) / $product->price) * 100);
                @endphp
                <span class="valentine-discount-badge">{{ $discount }}% OFF</span>
            @endif
            @if($product->primary_photo_thumb)
                <img src="{{ asset('storage/' . $product->primary_photo_thumb) }}" alt="{{ $product->name }}">
            @elseif($product->photos->count() > 0)
                <img src="{{ asset('storage/' . $product->photos->first()->photo_thumb) }}" alt="{{ $product->name }}">
            @else
                <div class="valentine-no-image">
                    <i class="fas fa-heart"></i>
                </div>
            @endif
            <div class="valentine-product-overlay">
                <span class="overlay-text"><i class="fas fa-eye me-2"></i>View Details</span>
            </div>
        </div>
        <div class="valentine-product-body">
            <div class="product-tag {{$product->stock_text ? 'glow-animation': null}}"><i
                    class="fas fa-infinity me-1"></i> {{$product->stock_text ?? 'In Stock'}}</div>
            <h3 class="valentine-product-title">{{ $product->name }}</h3>
            @if($product->short_description)
                <p class="valentine-product-description">{{ $product->short_description }}</p>
            @else
                <p class="valentine-product-description">Perfect gift for your loved one</p>
            @endif
            <div class="valentine-product-price">
                @if($product->sale_price && $product->sale_price < $product->price)
                    <span class="original-price">৳{{ number_format($product->price) }}</span>
                    ৳{{ number_format($product->sale_price) }}
                @else
                    ৳{{ number_format($product->price) }}
                @endif
            </div>
            <div class="mt-auto pt-3">
                                        <span class="btn btn-order-valentine btn-sm w-100">
                                            <i class="fas fa-heart me-2"></i> Order with Love
                                        </span>
            </div>
        </div>
    </div>
</a>
