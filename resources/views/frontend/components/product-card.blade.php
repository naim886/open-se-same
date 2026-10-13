<div class="col">
    <div class="product-card">
        <!-- Image -->
        <a href="{{ route('shop.product', $product->slug) }}" class="product-card-img-wrap d-block">
            @if($product->primary_photo_thumb)
                <img src="{{ asset('storage/'.$product->primary_photo_thumb) }}" alt="{{ $product->name }}" class="product-card-img" loading="lazy">
            @elseif($product->photos && $product->photos->count() > 0)
                <img src="{{ asset('storage/'.$product->photos->first()->photo_thumb) }}" alt="{{ $product->name }}" class="product-card-img" loading="lazy">
            @else
                <div class="product-card-img d-flex align-items-center justify-content-center" style="background:#f5f5f5; height:100%;">
                    <i class="fas fa-image" style="font-size:40px; color:#ddd;"></i>
                </div>
            @endif

            {{-- Discount Badge --}}
            @if($product->sale_price && $product->price && $product->price > $product->sale_price)
                @php $disc = round((($product->price - $product->sale_price) / $product->price) * 100); @endphp
                <span class="product-discount-badge">-{{ $disc }}%</span>
            @endif

            {{-- Stock badge --}}
            @if($product->stock !== null)
                @if($product->stock <= 0)
                    <span class="product-stock-badge badge-outofstock">Out of Stock</span>
                @elseif($product->stock <= 5)
                    <span class="product-stock-badge badge-limitedstock">Limited</span>
                @endif
            @endif

            <div class="product-overlay">
                <button class="product-overlay-btn"><i class="fas fa-eye me-1"></i> Quick View</button>
            </div>
        </a>

        <!-- Body -->
        <div class="product-card-body">
            @if($product->category)
                <div class="product-category-tag">{{ $product->category->name }}</div>
            @endif
            <a href="{{ route('shop.product', $product->slug) }}" class="product-card-name d-block">{{ $product->name }}</a>
            <div class="price-row d-flex align-items-center gap-2">
                @if($product->isVariantProduct() && ($varRange = $product->priceRange))
                    @if($varRange['min'] == $varRange['max'])
                        <span class="product-price-sale">৳{{ number_format($varRange['min'], 0) }}</span>
                    @else
                        <span class="product-price-sale">৳{{ number_format($varRange['min'], 0) }}&ndash;{{ number_format($varRange['max'], 0) }}</span>
                    @endif
                @else
                    <span class="product-price-sale">
                        ৳{{ number_format($product->sale_price ?? $product->price, 0) }}
                    </span>
                    @if($product->sale_price && $product->price && $product->price > $product->sale_price)
                        <span class="product-price-original">৳{{ number_format($product->price, 0) }}</span>
                    @endif
                @endif
            </div>

            {{-- Express Delivery Info removed --}}
        </div>

        <!-- Footer - fixed at bottom -->
        <div class="product-card-footer">
            @if($product->stock !== null && $product->stock <= 0)
                <button class="btn-add-to-cart" disabled>
                    <i class="fas fa-times-circle"></i> Out of Stock
                </button>
            @else
                <div class="product-card-footer-btns">
                    @if($product->isVariantProduct())
                        <a href="{{ route('shop.product', $product->slug) }}" class="btn-add-to-cart">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </a>
                        <a href="{{ route('shop.product', $product->slug) }}" class="btn-card-buy-now">
                            <i class="fas fa-bolt"></i> Buy Now
                        </a>
                    @else
                        <button class="btn-add-to-cart" onclick="addToCart({{ $product->id }})">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <a href="{{ route('shop.quick-order', $product->slug) }}" class="btn-card-buy-now">
                            <i class="fas fa-bolt"></i> Buy Now
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
