<?php

use App\Http\Controllers\AttributeNameController;
use App\Http\Controllers\AttributeValueController;
use App\Http\Controllers\Backend\OrderController;
use App\Http\Controllers\Backend\SearchKeywordController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\NewsLetterController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SpecificationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\SiteContentController;
use App\Http\Controllers\FlashSaleController;
use App\Http\Controllers\TopSellingProductController;
use App\Http\Controllers\PopupLeadController;
use Illuminate\Support\Facades\Route;


//Route::get('test', [FrontendController::class, 'test'])->name('frontend.test');

//Route::get('/', [FrontendController::class, 'index'])->name('frontend.index');
//Route::get('landing', [FrontendController::class, 'landing'])->name('frontend.landing');
//Route::get('product/{slug}', [FrontendController::class, 'productDetail'])->name('frontend.product.detail');
//Route::resource('/newsletter', NewsLetterController::class);
//Route::post('place-order', [FrontendController::class, 'placeOrder'])->name('frontend.placeOrder');
//Route::get('order-success', [FrontendController::class, 'orderSuccess'])->name('frontend.orderSuccess');


use App\Http\Controllers\CustomerPanelController;
use App\Http\Controllers\SteadfastWebhookController;

// ============================================================
// Customer Panel (authenticated registered customers)
// ============================================================
Route::prefix('account')->name('customer.')->middleware('auth')->group(function () {
    Route::get('/',                    [CustomerPanelController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders',              [CustomerPanelController::class, 'orders'])->name('orders');
    Route::get('/orders/{id}',         [CustomerPanelController::class, 'orderDetail'])->name('order.detail');
    Route::get('/profile',             [CustomerPanelController::class, 'profile'])->name('profile');
    Route::post('/profile',            [CustomerPanelController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/photo',      [CustomerPanelController::class, 'updatePhoto'])->name('profile.photo');
    Route::post('/profile/password',   [CustomerPanelController::class, 'updatePassword'])->name('profile.password');
    // Address Book
    Route::get('/addresses',           [CustomerPanelController::class, 'addressBook'])->name('addresses');
    Route::post('/addresses',          [CustomerPanelController::class, 'addressStore'])->name('addresses.store');
    Route::put('/addresses/{id}',      [CustomerPanelController::class, 'addressUpdate'])->name('addresses.update');
    Route::patch('/addresses/{id}/default', [CustomerPanelController::class, 'addressSetDefault'])->name('addresses.setDefault');
    Route::delete('/addresses/{id}',   [CustomerPanelController::class, 'addressDestroy'])->name('addresses.destroy');
});

// ============================================================
// Steadfast Webhook (no auth, no CSRF — verified by API key header)
// ============================================================
Route::post('/webhooks/steadfast', [SteadfastWebhookController::class, 'handle'])->name('webhooks.steadfast');

// ============================================================
// SHOP (Public Frontend) Routes
// ============================================================
Route::prefix('/')->name('shop.')->group(function () {
    // Pages
    Route::get('/',                [ShopController::class, 'home'])->name('home');
    Route::get('/products',        [ShopController::class, 'products'])->name('products');
    Route::get('/offer',           [ShopController::class, 'offer'])->name('offer');
    Route::get('/search',          [ShopController::class, 'search'])->name('search');
    Route::get('/category/{slug}', [ShopController::class, 'category'])->name('category');
    Route::get('/product/{slug}',  [ShopController::class, 'product'])->name('product');
    Route::get('/quick-order/{slug}', [ShopController::class, 'quickOrder'])->name('quick-order');
    Route::get('/cart',            [ShopController::class, 'cart'])->name('cart');
    Route::get('/checkout',        [ShopController::class, 'checkout'])->name('checkout');
    Route::get('/order-success',   [ShopController::class, 'orderSuccess'])->name('order.success');

    // Cart actions
    Route::post('/cart/add',                 [ShopController::class, 'cartAdd'])->name('cart.add');
    Route::patch('/cart/{productId}',        [ShopController::class, 'cartUpdate'])->name('cart.update');
    Route::delete('/cart/{productId}',       [ShopController::class, 'cartRemove'])->name('cart.remove');
    Route::delete('/cart',                   [ShopController::class, 'cartClear'])->name('cart.clear');

    // Order placement
    Route::post('/order/place',       [ShopController::class, 'placeOrder'])->name('order.place');
    Route::post('/order/quick',       [ShopController::class, 'placeQuickOrder'])->name('order.quick');
    Route::post('/coupon/validate',   [ShopController::class, 'validateCoupon'])->name('coupon.validate');

    // Blog & Pages (CMS)
    Route::get('/blogs',              [ShopController::class, 'blogs'])->name('blogs');
    Route::get('/blog/{slug}',        [ShopController::class, 'blogDetail'])->name('blog');
    Route::get('/page/{slug}',        [ShopController::class, 'pageDetail'])->name('page');

    // Contact
    Route::get('/contact',            [ShopController::class, 'contact'])->name('contact');
    Route::post('/contact',           [ShopController::class, 'submitContact'])->name('contact.submit');

    // Popup Lead capture
    Route::post('/popup-lead',        [PopupLeadController::class, 'store'])->name('popup-lead.store');

    // Order Tracking (public)
    Route::get('/track-order',        [\App\Http\Controllers\TrackingController::class, 'index'])->name('track-order');
});

Route::group(['middleware' => ['auth', 'role:Super Admin|Admin'], 'prefix' => 'admin'], static function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::resource('banner', BannerController::class);
    Route::resource('category', CategoryController::class);
    Route::resource('brand', BrandController::class);
    Route::resource('product', ProductController::class);
    Route::resource('expense', ExpenseController::class);
    Route::resource('attribute-name', AttributeNameController::class);
    Route::resource('attribute-value', AttributeValueController::class);
    Route::resource('specification', SpecificationController::class);
    // Order management routes
    Route::resource('orders', OrderController::class);
    Route::post('order-dependency', [OrderController::class, 'order_dependency'])->name('order_dependency');
    Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::post('orders/{order}/notes', [OrderController::class, 'addNote'])->name('orders.addNote');
    Route::post('orders/{order}/send-to-courier', [OrderController::class, 'sendToCourier'])->name('orders.sendToCourier');
    // Printable documents. The "Tk" currency label is used instead of ৳ because
    // the PDF fonts bundled with dompdf do not include the Bengali glyph.
    Route::get('orders/{order}/invoice', [\App\Http\Controllers\Backend\InvoiceController::class, 'invoice'])->name('orders.invoice');
    Route::get('orders/{order}/label', [\App\Http\Controllers\Backend\InvoiceController::class, 'label'])->name('orders.label');
    Route::get('orders-filter', [OrderController::class, 'filterByStatus'])->name('orders.filter');
    // Reports
    Route::get('reports/sales', [\App\Http\Controllers\Backend\SalesReportController::class, 'index'])->name('reports.sales');
    Route::get('reports/commission', [\App\Http\Controllers\Backend\CommissionReportController::class, 'index'])->name('reports.commission');
    Route::get('reports/product-performance', [\App\Http\Controllers\Backend\ProductPerformanceController::class, 'index'])->name('reports.product-performance');
    Route::get('reports/category-sales', [\App\Http\Controllers\Backend\CategorySalesReportController::class, 'index'])->name('reports.category-sales');
    Route::get('reports/margin', [\App\Http\Controllers\Backend\MarginReportController::class, 'index'])->name('reports.margin');
    Route::get('reports/sales-summary', [\App\Http\Controllers\Backend\SalesSummaryReportController::class, 'index'])->name('reports.sales-summary');
    Route::get('reports/stock-value', [\App\Http\Controllers\Backend\StockValueController::class, 'index'])->name('reports.stock-value');
    Route::get('reports/balance-sheet', [\App\Http\Controllers\Backend\BalanceSheetController::class, 'index'])->name('reports.balance-sheet');
    // Accounts
    Route::get('accounts', [\App\Http\Controllers\Backend\AccountController::class, 'index'])->name('accounts.index');
    Route::post('accounts', [\App\Http\Controllers\Backend\AccountController::class, 'store'])->name('accounts.store');
    Route::delete('accounts/{accountTransaction}', [\App\Http\Controllers\Backend\AccountController::class, 'destroy'])->name('accounts.destroy');
    // Customers
    Route::get('customers', [\App\Http\Controllers\Backend\CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/export', [\App\Http\Controllers\Backend\CustomerController::class, 'export'])->name('customers.export');

    // Customer tag management. Declared before customers/{phone} so the literal
    // "tags" segment is not captured as a phone number.
    Route::get('customers/tags', [\App\Http\Controllers\Backend\CustomerTagController::class, 'index'])->name('customers.tags.index');
    Route::post('customers/tags', [\App\Http\Controllers\Backend\CustomerTagController::class, 'store'])->name('customers.tags.store');
    Route::put('customers/tags/{tag}', [\App\Http\Controllers\Backend\CustomerTagController::class, 'update'])->name('customers.tags.update');
    Route::delete('customers/tags/{tag}', [\App\Http\Controllers\Backend\CustomerTagController::class, 'destroy'])->name('customers.tags.destroy');

    Route::get('customers/{phone}', [\App\Http\Controllers\Backend\CustomerController::class, 'show'])->name('customers.show');

    // Per-customer tags & internal notes
    Route::post('customers/{phone}/tags', [\App\Http\Controllers\Backend\CustomerController::class, 'attachTag'])->name('customers.tags.attach');
    Route::delete('customers/{phone}/tags/{tagId}', [\App\Http\Controllers\Backend\CustomerController::class, 'detachTag'])->name('customers.tags.detach');
    Route::post('customers/{phone}/notes', [\App\Http\Controllers\Backend\CustomerController::class, 'storeNote'])->name('customers.notes.store');
    Route::delete('customers/{phone}/notes/{noteId}', [\App\Http\Controllers\Backend\CustomerController::class, 'destroyNote'])->name('customers.notes.destroy');
    Route::patch('customers/order/{order}/source', [\App\Http\Controllers\Backend\CustomerController::class, 'updateOrderSource'])->name('customers.order-source');
    // CMS modules
    Route::resource('page', PageController::class);
    Route::resource('blog', BlogController::class);
    Route::resource('site-content', SiteContentController::class)->except(['show']);
    // Flash Sales
    Route::get('flash-sale/search-products', [FlashSaleController::class, 'searchProducts'])->name('flash-sale.search-products');
    Route::get('flash-sale/products-by-category', [FlashSaleController::class, 'productsByCategory'])->name('flash-sale.products-by-category');
    Route::resource('flash-sale', FlashSaleController::class)->except(['show']);

    // Top Selling Products
    Route::get('top-selling/search-products', [TopSellingProductController::class, 'searchProducts'])->name('top-selling.search-products');
    Route::get('top-selling/products-by-category', [TopSellingProductController::class, 'productsByCategory'])->name('top-selling.products-by-category');
    Route::post('top-selling/reorder', [TopSellingProductController::class, 'reorder'])->name('top-selling.reorder');
    Route::resource('top-selling', TopSellingProductController::class)->except(['show']);
    // Popup Leads
    Route::get('popup-leads', [PopupLeadController::class, 'index'])->name('popup-leads.index');
    Route::delete('popup-leads/{popupLead}', [PopupLeadController::class, 'destroy'])->name('popup-leads.destroy');

    // Coupons
    Route::get('coupon', [\App\Http\Controllers\Backend\CouponController::class, 'index'])->name('coupon.index');
    Route::get('coupon/create', [\App\Http\Controllers\Backend\CouponController::class, 'create'])->name('coupon.create');
    Route::post('coupon', [\App\Http\Controllers\Backend\CouponController::class, 'store'])->name('coupon.store');
    Route::get('coupon/{coupon}/edit', [\App\Http\Controllers\Backend\CouponController::class, 'edit'])->name('coupon.edit');
    Route::put('coupon/{coupon}', [\App\Http\Controllers\Backend\CouponController::class, 'update'])->name('coupon.update');
    Route::patch('coupon/{coupon}/toggle', [\App\Http\Controllers\Backend\CouponController::class, 'toggle'])->name('coupon.toggle');
    Route::delete('coupon/{coupon}', [\App\Http\Controllers\Backend\CouponController::class, 'destroy'])->name('coupon.destroy');
    // Contact Messages
    Route::resource('contact', ContactController::class)->only(['index', 'show', 'destroy']);
    Route::post('contact/{contact}/mark-read', [ContactController::class, 'markRead'])->name('contact.markRead');
    // Search Keywords
    Route::get('search-keywords', [SearchKeywordController::class, 'index'])->name('search-keywords.index');
    // TinyMCE image upload
    Route::post('tinymce/upload', [\App\Http\Controllers\TinymceController::class, 'uploadImage'])->name('tinymce.upload');
    // Settings
    Route::get('settings', [\App\Http\Controllers\Backend\SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [\App\Http\Controllers\Backend\SettingController::class, 'update'])->name('settings.update');

    // Users (Admin/Super Admin only)
    Route::get('users', [\App\Http\Controllers\Backend\UserController::class, 'index'])->name('admin.users.index');
    Route::patch('users/{user}/toggle-status', [\App\Http\Controllers\Backend\UserController::class, 'toggleStatus'])->name('admin.users.toggleStatus');
    Route::patch('users/{user}/role', [\App\Http\Controllers\Backend\UserController::class, 'updateRole'])->name('admin.users.updateRole');
});



Route::get('email', static function (){
    return view('emails.welcome.welcome');
});

// ============================================
// Valentine's Day Routes (Remove after event)
// ============================================
use App\Http\Controllers\ValentineController;

//Route::prefix('valentine')->name('valentine.')->group(function () {
//    Route::get('/', [ValentineController::class, 'landing'])->name('landing');
//    Route::get('/product/{slug}', [ValentineController::class, 'productDetail'])->name('product.detail');
//    Route::post('/place-order', [ValentineController::class, 'placeOrder'])->name('placeOrder');
//    Route::get('/order-success', [ValentineController::class, 'orderSuccess'])->name('order.success');
//});

require __DIR__ . '/auth.php';
