<?php

use App\Http\Controllers\Api\GoogleMerchantFeedController;
use App\Http\Controllers\Api\MobileApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API Routes — Open Se Same
|--------------------------------------------------------------------------
|
| All routes here are stateless JSON endpoints for the mobile app.
| No CSRF, no session-based cart. All responses are JSON.
|
| Base URL:  /api/v1/
|
*/

Route::prefix('v1')->group(function () {

    // ── Data / Read endpoints ─────────────────────────────────────

    /** Home page: banners, categories, featured/new/top-selling products, flash sale */
    Route::get('home', [MobileApiController::class, 'home']);

    /** All active categories */
    Route::get('categories', [MobileApiController::class, 'categories']);

    /** Category detail + paginated products
     *  Query: sort (newest|price_asc|price_desc|popular|discount), min_price, max_price, page */
    Route::get('category/{slug}', [MobileApiController::class, 'category']);

    /** All products (paginated, filterable)
     *  Query: category (id), sort, min_price, max_price, page */
    Route::get('products', [MobileApiController::class, 'products']);

    /** Sale / discounted products (paginated)
     *  Query: category (id), sort, min_price, max_price, page */
    Route::get('products/offers', [MobileApiController::class, 'offers']);

    /** Search products
     *  Query: q (search term), sort, page */
    Route::get('products/search', [MobileApiController::class, 'search']);

    /** Single product detail (with specifications, Q&A, related products, delivery/return info) */
    Route::get('product/{slug}', [MobileApiController::class, 'product']);

    /** Delivery options with costs */
    Route::get('delivery-options', [MobileApiController::class, 'deliveryOptions']);

    /** Blogs list
     *  Query: search, page */
    Route::get('blogs', [MobileApiController::class, 'blogs']);

    /** Blog detail */
    Route::get('blog/{slug}', [MobileApiController::class, 'blogDetail']);

    /** CMS page detail (e.g. about-us, privacy-policy) */
    Route::get('page/{slug}', [MobileApiController::class, 'pageDetail']);

    /** Order tracking
     *  Query: q (phone number or order number like M-1234 or 1234) */
    Route::get('track-order', [MobileApiController::class, 'trackOrder']);

    // ── Write / Action endpoints ──────────────────────────────────

    /** Place a quick single-product order
     *  Body (JSON): product_id, customer_name, customer_phone, customer_address,
     *               quantity, delivery_location (1–5), customer_email?, note? */
    Route::post('order/quick', [MobileApiController::class, 'placeQuickOrder']);

    /** Place a multi-product (cart) order
     *  Body (JSON): customer_name, customer_phone, customer_address,
     *               delivery_location (1–5), customer_email?, note?,
     *               items: [{ product_id, quantity }, ...] */
    Route::post('order', [MobileApiController::class, 'placeOrder']);

    /** Submit contact form
     *  Body (JSON): name, email, subject, message, phone? */
    Route::post('contact', [MobileApiController::class, 'submitContact']);

    /** Newsletter subscription
     *  Body (JSON): email */
    Route::post('newsletter', [MobileApiController::class, 'newsletter']);
});

// ─────────────────────────────────────────────────────────────────────────────
// Google Merchant Center Product Feed
// ─────────────────────────────────────────────────────────────────────────────
// All endpoints require ?key=<GOOGLE_MERCHANT_FEED_KEY> (configured in .env).
//
// TSV feed  → https://yourdomain.com/api/v1/merchant/feed.tsv?key=YOUR_KEY
// XML feed  → https://yourdomain.com/api/v1/merchant/feed.xml?key=YOUR_KEY
// JSON feed → https://yourdomain.com/api/v1/merchant/products?key=YOUR_KEY
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix('v1/merchant')->group(function () {
    Route::get('feed.tsv', [GoogleMerchantFeedController::class, 'tsv'])->name('merchant.feed.tsv');
    Route::get('feed.xml', [GoogleMerchantFeedController::class, 'xml'])->name('merchant.feed.xml');
    Route::get('products',  [GoogleMerchantFeedController::class, 'json'])->name('merchant.feed.json');
});
