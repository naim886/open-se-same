<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FrontendController extends Controller
{
    final public function index(): View
    {
//        $banners = Banner::where('status', Banner::STATUS_ACTIVE)
//                        ->orderBy('sort_order', 'asc')
//                        ->orderBy('created_at', 'desc')
//                        ->get();
//
//        $categories = Category::where('status', Category::STATUS_ACTIVE)
//                            ->orderBy('sort_order', 'asc')
//                            ->limit(8)
//                            ->get();
//
//        $featuredProducts = Product::where('status', Product::STATUS_ACTIVE)
//                                  ->with(['category', 'photos'])
//                                  ->inRandomOrder()
//                                  ->limit(8)
//                                  ->get();
//
//        $categoryWiseProducts = [];
//        foreach ($categories->take(4) as $category) {
//            $categoryWiseProducts[$category->id] = [
//                'category' => $category,
//                'products' => Product::where('status', Product::STATUS_ACTIVE)
//                                   ->where('category_id', $category->id)
//                                   ->with(['photos'])
//                                   ->limit(4)
//                                   ->get()
//            ];
//        }
//
//        return view('frontend.index', compact('banners', 'categories', 'featuredProducts', 'categoryWiseProducts'));

        return view('frontend.pages.index');
    }
    final public function test(): View
    {
        return view('emails.newsletter.newsletter');
    }

    public function landing()
    {
        return view('frontend.pages.landing');
    }

    final public function productDetail($slug): View
    {
        $product = Product::with(['photos', 'category'])->where('slug', $slug)->firstOrFail();
        return view('frontend.pages.product-detail', compact('product'));
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'email' => 'nullable|email|max:255',
        ]);

        try {
            $order = new Order();
            $order->storeOrder($request);

            return redirect()->route('frontend.orderSuccess')
                           ->with('success', 'Order placed successfully! We will contact you shortly.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to place order. Please try again.');
        }
    }

    public function orderSuccess(): View
    {
        return view('frontend.pages.order-success');
    }
}
