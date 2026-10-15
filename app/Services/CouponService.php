<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;

/**
 * Single place where coupon validity and discount amounts are decided, so the
 * checkout, the API and the admin screens can never disagree.
 */
class CouponService
{
    /**
     * Validate a code against a cart subtotal.
     *
     * @param  string|null  $code      User-supplied coupon code
     * @param  float        $subtotal  Products subtotal (delivery excluded)
     * @param  string|null  $phone     Customer phone, for per-customer limits
     */
    public function validate(?string $code, float $subtotal, ?string $phone = null): CouponResult
    {
        $code = Coupon::normaliseCode($code);

        if ($code === '') {
            return CouponResult::none();
        }

        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            return CouponResult::rejected('Coupon code not found.');
        }

        if (! $coupon->is_active) {
            return CouponResult::rejected('This coupon is not active.');
        }

        $now = now();

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            return CouponResult::rejected('This coupon is not available yet.');
        }

        if ($coupon->ends_at && $coupon->ends_at->isPast()) {
            return CouponResult::rejected('This coupon has expired.');
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            return CouponResult::rejected('This coupon has reached its usage limit.');
        }

        if ($phone && (int) $coupon->per_customer_limit > 0) {
            $usedByCustomer = CouponUsage::where('coupon_id', $coupon->id)
                ->where('phone', $phone)
                ->count();

            if ($usedByCustomer >= (int) $coupon->per_customer_limit) {
                return CouponResult::rejected('You have already used this coupon.');
            }
        }

        $minOrder = (float) $coupon->min_order_amount;

        if ($minOrder > 0 && $subtotal < $minOrder) {
            return CouponResult::rejected(
                'This coupon requires a minimum order of ৳' . number_format($minOrder, 0) . '.'
            );
        }

        $discount = $coupon->discountFor($subtotal);

        if ($discount <= 0) {
            return CouponResult::rejected('This coupon gives no discount on this order.');
        }

        return CouponResult::applied($discount, $coupon);
    }

    /**
     * Record that a coupon was redeemed on an order and bump its counter.
     */
    public function recordUsage(Coupon $coupon, ?int $orderId, ?string $phone, float $discount): void
    {
        CouponUsage::create([
            'coupon_id'       => $coupon->id,
            'order_id'        => $orderId,
            'phone'           => $phone,
            'discount_amount' => $discount,
        ]);

        $coupon->increment('used_count');
    }
}
