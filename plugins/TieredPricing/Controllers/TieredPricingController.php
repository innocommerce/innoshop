<?php

namespace Plugin\TieredPricing\Controllers;

use Illuminate\Http\Request;
use Plugin\TieredPricing\Models\ProductSkuTieredPricing;
use Plugin\TieredPricing\Services\TieredPricingService;

class TieredPricingController extends Controller
{
    public function getTotalPrice(Request $request)
    {
        $quantity       = $request->quantity;
        $sku_index      = $request->sku_index;
        $product_sku_id = $request->product_sku_id;

        $productSku = TieredPricingService::getProduct($product_sku_id);
        if (! $productSku) {
            return json_encode([
                'code' => -1,
                'msg'  => '未设置批发价格',
            ]);
        }
        $tiered = TieredPricingService::getTiered($productSku['product_id'], $sku_index, $quantity);
        if (! $tiered) {
            return json_encode([
                'code' => -2,
                'msg'  => '未设置批发价格',
            ]);
        }
        $old_price = $price = $productSku['price'];
        if (! empty($tiered)) {
            $price = $tiered['price'];
        }

        return json_encode([
            'code' => 0,
            'msg'  => '',
            'data' => [
                'format_total'           => currency_format(bcmul($price, $quantity, 2)),
                'price'                  => $price,
                'old_price'              => $old_price,
                'format_old_total_price' => currency_format(bcmul($old_price, $quantity, 2)),
            ],
        ]);
    }

    public function getMinQuantity(Request $request)
    {
        $skuID      = $request->sku_id;
        $productSku = ProductSku::query()->where('id', $skuID)->first();
        $tiereds    = ProductSkuTieredPricing::query()->where('product_id', $productSku->product_id)->where('sku_index', $productSku->position)->orderBy('num')->get(['num']);
        if ($tiereds->count() > 0) {
            return $tiereds[0]->num;
        } else {
            return 1;
        }
    }
}
