@extends('frontend.layouts.app')

@section('title', $brand->name . ' Products - Open Se Same')
@section('meta_description', 'Shop ' . $brand->name . ' products at Open Se Same. Best quality products with fast delivery across Bangladesh.')
@section('og_title', $brand->name . ' Products - Open Se Same')
@section('og_description', 'Shop ' . $brand->name . ' products at Open Se Same. Best quality products with fast delivery across Bangladesh.')
@section('robots', 'index, follow')
@section('og_image', $brand->banner_url ?? $brand->logo_url ?? asset('images/og.webp'))

@section('schema')
@php
use Spatie\SchemaOrg\Schema;

$schemaTitle = $brand->name . ' Products - Open Se Same';
$schemaDesc  = $brand->short_description
    ?: 'Shop ' . $brand->name . ' products at Open Se Same with fast delivery across Bangladesh.';

echo Schema::collectionPage()
    ->name($schemaTitle)
    ->description($schemaDesc)
    ->url(route('shop.brand', $brand->slug))
    ->toScript();

$brandBreadItems = [
    Schema::listItem()->position(1)->name('Home')->item(route('shop.home')),
    Schema::listItem()->position(2)->name('Products')->item(route('shop.products')),
    Schema::listItem()->position(3)->name($brand->name)->item(route('shop.brand', $brand->slug)),
];
echo Schema::breadcrumbList()->itemListElement($brandBreadItems)->toScript();

if ($products->count() > 0) {
    $brandItems = $products->take(20)->values()->map(fn ($p, $i) =>
        Schema::listItem()
            ->position($i + 1)
            ->item(
                Schema::product()
                    ->name($p->name)
                    ->url(route('shop.product', $p->slug))
                    ->image($p->primary_photo ? asset('storage/' . $p->primary_photo) : null)
                    ->brand(Schema::brand()->name($brand->name))
                    ->offers(
                        Schema::offer()
                            ->price((float) ($p->sale_price ?? $p->price ?? 0))
                            ->priceCurrency('BDT')
                    )
            )
    )->toArray();

    echo Schema::itemList()->name($brand->name . ' Products')->itemListElement($brandItems)->toScript();
}
@endphp
@endsection

@section('content')
    @include('frontend.partials.product-listing')
@endsection
