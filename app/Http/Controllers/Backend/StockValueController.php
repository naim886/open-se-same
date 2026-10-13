<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class StockValueController extends Controller
{
    public function index(): View
    {
        $products = Product::where('status', Product::STATUS_ACTIVE)
            ->with(['category', 'activeVariations'])
            ->orderBy('category_id')
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'sale_price', 'stock', 'cost', 'category_id', 'product_type']);

        $totalQty   = 0;
        $totalValue = 0.0;
        $totalCost  = 0.0;

        $rows = $products->map(function ($p) use (&$totalQty, &$totalValue, &$totalCost) {
            if ($p->isVariantProduct()) {
                $variations = $p->activeVariations->map(function ($v) {
                    $sellingPrice = (float) ($v->sale_price ?: ($v->price ?? 0));
                    $costPrice    = (float) ($v->cost ?? 0);
                    $qty          = (int)   ($v->stock ?? 0);
                    return [
                        'label'         => $v->label,
                        'selling_price' => $sellingPrice,
                        'cost_price'    => $costPrice,
                        'qty'           => $qty,
                        'value'         => $sellingPrice * $qty,
                        'cost_value'    => $costPrice * $qty,
                    ];
                });

                $variantQty   = $variations->sum('qty');
                $variantValue = $variations->sum('value');
                $variantCost  = $variations->sum('cost_value');

                $totalQty   += $variantQty;
                $totalValue += $variantValue;
                $totalCost  += $variantCost;

                return [
                    'product'       => $p,
                    'is_variant'    => true,
                    'selling_price' => null,
                    'cost_price'    => null,
                    'qty'           => $variantQty,
                    'value'         => $variantValue,
                    'cost_value'    => $variantCost,
                    'variations'    => $variations,
                ];
            }

            // Simple product
            $sellingPrice = (float) ($p->sale_price ?: ($p->price ?? 0));
            $costPrice    = (float) ($p->cost ?? 0);
            $qty          = (int)   ($p->stock ?? 0);
            $value        = $sellingPrice * $qty;
            $costValue    = $costPrice * $qty;

            $totalQty   += $qty;
            $totalValue += $value;
            $totalCost  += $costValue;

            return [
                'product'       => $p,
                'is_variant'    => false,
                'selling_price' => $sellingPrice,
                'cost_price'    => $costPrice,
                'qty'           => $qty,
                'value'         => $value,
                'cost_value'    => $costValue,
                'variations'    => collect(),
            ];
        });

        $cms_content = [
            'page_title' => 'Stock Value',
            'module'     => 'Report',
            'sub_module' => 'Stock Value',
        ];

        return view('backend.modules.report.stock-value', compact(
            'cms_content', 'rows', 'totalQty', 'totalValue', 'totalCost'
        ));
    }
}

