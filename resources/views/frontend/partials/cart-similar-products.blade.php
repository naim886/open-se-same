@foreach($products as $product)
    @include('frontend.components.product-card', ['product' => $product])
@endforeach
