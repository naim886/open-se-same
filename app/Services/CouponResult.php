<?php

namespace App\Services;

use App\Models\Coupon;

/**
 * Outcome of validating a coupon code.
 */
final class CouponResult
{
    private function __construct(
        public readonly bool $ok,
        public readonly float $discount = 0.0,
        public readonly ?Coupon $coupon = null,
        public readonly string $message = '',
    ) {
    }

    public static function applied(float $discount, Coupon $coupon): self
    {
        return new self(true, $discount, $coupon, '');
    }

    public static function rejected(string $message): self
    {
        return new self(false, 0.0, null, $message);
    }

    /**
     * No code was supplied — not an error, just nothing to apply.
     */
    public static function none(): self
    {
        return new self(false, 0.0, null, '');
    }

    public function hasCode(): bool
    {
        return $this->coupon !== null || $this->message !== '';
    }

    public function messageOr(string $default): string
    {
        return $this->message !== '' ? $this->message : $default;
    }
}
