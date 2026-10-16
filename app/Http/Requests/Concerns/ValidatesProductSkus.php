<?php

namespace App\Http\Requests\Concerns;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Validation\Validator;

trait ValidatesProductSkus
{
    /**
     * SKUs must be unique across products and product variations, but the
     * `unique` rule only inspects a single table, so the cross-table check is
     * done here. Soft-deleted variations are ignored by the query scope, which
     * keeps a removed variation's SKU reusable.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $productId = $this->currentProductId();
            $productSku = Product::normalizeSku($this->input('sku'));

            if ($productSku !== null && $this->skuTaken($productSku, $productId, null)) {
                $validator->errors()->add('sku', 'This SKU is already used by another product or variation.');
            }

            foreach ($this->input('variants', []) ?: [] as $index => $variant) {
                $sku = Product::normalizeSku($variant['sku'] ?? null);

                if ($sku === null) {
                    continue;
                }

                $variationId = !empty($variant['id']) ? (int) $variant['id'] : null;

                if ($this->skuTaken($sku, null, $variationId)) {
                    $validator->errors()->add("variants.$index.sku", 'This SKU is already used by another product or variation.');
                }
            }
        });
    }

    private function skuTaken(string $sku, ?int $ignoreProductId, ?int $ignoreVariationId): bool
    {
        $productSkuTaken = Product::query()
            ->where('sku', $sku)
            ->when($ignoreProductId, fn ($query) => $query->where('id', '!=', $ignoreProductId))
            ->exists();

        if ($productSkuTaken) {
            return true;
        }

        return ProductVariation::query()
            ->where('sku', $sku)
            ->when($ignoreVariationId, fn ($query) => $query->where('id', '!=', $ignoreVariationId))
            ->exists();
    }

    /**
     * Id of the product being updated, or null when creating one.
     */
    abstract protected function currentProductId(): ?int;
}
