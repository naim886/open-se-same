<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ValentineController extends Controller
{
    /**
     * Display Valentine's Day landing page
     */
    public function landing(): View
    {
        $products = Product::where('status', Product::STATUS_ACTIVE)
            ->with(['category', 'photos'])
            ->orderBy('sort_order', 'asc')
            ->limit(12)
            ->get();

        return view('valentine.landing', compact('products'));
    }

    /**
     * Display Valentine's Day product detail page
     */
    public function productDetail(string $slug): View
    {
        $product = Product::with(['photos', 'category', 'specifications.specification'])
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedProducts = Product::where('status', Product::STATUS_ACTIVE)
            ->where('id', '!=', $product->id)
            ->with(['photos'])
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('valentine.product-detail', compact('product', 'relatedProducts'));
    }

    /**
     * Handle Valentine's Day order placement
     */
    public function placeOrder(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:10',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'email' => 'nullable|email|max:255',
            'message' => 'nullable|string|max:500', // Valentine special: gift message
        ]);

        try {
            $product = Product::findOrFail($request->product_id);
            $quantity = (int) $request->quantity;
            $price = $product->sale_price ?? $product->price;
            $subtotal = $price * $quantity;
            $deliveryCharge = ($subtotal > 1000) ? 0 : 70; // Free delivery logic based on landing page

            $payload = [
                'customer_name'    => $request->name,
                'customer_phone'   => $request->phone,
                'customer_address' => $request->address,
                'customer_email'   => $request->email,
                'note'             => $request->message,
                'product_ids'      => [$product->id],
                'quantities'       => [$quantity],
                'unit_prices'      => [$price],
                'product_price'    => $subtotal,
                'delivery_charge'  => $deliveryCharge,
                'order_total'      => $subtotal + $deliveryCharge,
                'status'           => Order::STATUS_PENDING,
            ];

            $order = new Order();
            $order->storeOrder($payload);

            return redirect()->route('valentine.order.success')
                ->with('success', '💝 Your Valentine order has been placed! We will contact you shortly.');
        } catch (\Exception $e) {
            \Log::error('Valentine Order Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to place order. Please try again.');
        }
    }

    /**
     * Display order success page
     */
    public function orderSuccess(): View
    {
        return view('valentine.order-success');
    }
}
