@extends('frontend.layouts.app')

@section('title', ($category ? $category->name.' - ' : 'All Products - ').'Open Se Same')
@section('meta_description', $category ? 'Shop '.$category->name.' at Open Se Same. Best quality products with fast delivery across Bangladesh.' : 'Shop all products at Open Se Same. treasures s, miniature car & fashion with fast delivery across Bangladesh.')
@section('og_title', ($category ? $category->name.' - ' : 'All Products - ').'Open Se Same')
@section('og_description', $category ? 'Shop '.$category->name.' at Open Se Same. Best quality products with fast delivery across Bangladesh.' : 'Shop all products at Open Se Same. treasures s, miniature car & fashion with fast delivery across Bangladesh.')
@section('robots', 'index, follow')

@section('schema')
@php
use Spatie\SchemaOrg\Schema;
use Illuminate\Support\Str;
$schemaPageTitle = $category ? $category->name.' - Open Se Same' : 'All Products - Open Se Same';
$schemaPageDesc  = $category
    ? 'Shop '.$category->name.' at Open Se Same. Best quality products with fast delivery across Bangladesh.'
    : 'Shop all products at Open Se Same. treasures s, miniature car & fashion with fast delivery across Bangladesh.';
$schemaPageUrl   = $category ? route('shop.category', $category->slug) : route('shop.products');
echo Schema::collectionPage()
    ->name($schemaPageTitle)
    ->description($schemaPageDesc)
    ->url($schemaPageUrl)
    ->toScript();
$schemaBreadItems = [Schema::listItem()->position(1)->name('Home')->item(route('shop.home'))];
if ($category) {
    $schemaBreadItems[] = Schema::listItem()->position(2)->name($category->name)->item(route('shop.category', $category->slug));
} else {
    $schemaBreadItems[] = Schema::listItem()->position(2)->name('All Products')->item(route('shop.products'));
}
echo Schema::breadcrumbList()->itemListElement($schemaBreadItems)->toScript();
if ($products->count() > 0) {
    $schemaListItems = $products->take(20)->values()->map(fn($p, $i) =>
        Schema::listItem()
            ->position($i + 1)
            ->item(
                Schema::product()
                    ->name($p->name)
                    ->url(route('shop.product', $p->slug))
                    ->image($p->primary_photo ? asset('storage/'.$p->primary_photo) : null)
                    ->offers(
                        Schema::offer()
                            ->price((float)($p->sale_price ?? $p->price ?? 0))
                            ->priceCurrency('BDT')
                    )
            )
    )->toArray();
    echo Schema::itemList()->name($schemaPageTitle)->itemListElement($schemaListItems)->toScript();
}
@endphp
@endsection

@section('content')
    @include('frontend.partials.product-listing')
@endsection

@section('extra_js')
<script>
@if(request('q'))
// ── Meta Pixel: Search ───────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    if (typeof fbq === 'function') {
        fbq('track', 'Search', {
            search_string: {!! json_encode(request('q')) !!}
        });
    }
});
@endif
</script>
@endsection

