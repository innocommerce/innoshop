<?php

namespace Plugin\TieredPricing\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use InnoShop\Common\Models\Product\Sku;
use Plugin\TieredPricing\Models\ProductSkuTieredPricing;

class TieredPricingService
{
    public static $key = 'all_tiered_';

    public static function getTiered($product_id, $sku_index, $quantity)
    {
        $key         = self::$key.$product_id.'-'.$sku_index; //
        $tieredsJson = Cache::get($key);
        if (empty($tieredsJson)) {
            $tiereds = ProductSkuTieredPricing::query()->where('product_id', $product_id)->where('sku_index', $sku_index)->orderByDesc('num')->get();
            if ($tiereds->count() > 0) {
                $tieredsJson = json_encode($tiereds, true);
                Cache::put($key, $tieredsJson, Carbon::now()->addSeconds(2));
                $tiereds = json_decode($tieredsJson, true);
            } else {
                $tieredsJson = json_encode($tiereds, true);
                $tiereds     = json_decode($tieredsJson, true);
            }
        } else {
            $tiereds = json_decode($tieredsJson, true);
        }
        $tieredRs = null;
        if (! empty($tiereds)) {
            foreach ($tiereds as $tiered) {
                if ($tiered['num'] <= $quantity) {
                    $tieredRs = $tiered;
                    break;
                }
            }
        }

        return $tieredRs;
    }

    public static function getTieredBySkuId($product_id, $sku_id, $quantity)
    {
        $key         = self::$key.$product_id.'-sku_id-'.$sku_id; //
        $tieredsJson = Cache::get($key);
        if (empty($tieredsJson)) {
            $tiereds = ProductSkuTieredPricing::query()->where('product_id', $product_id)->where('sku_id', $sku_id)->orderByDesc('num')->get();
            if ($tiereds->count() > 0) {
                $tieredsJson = json_encode($tiereds, true);
                Cache::put($key, $tieredsJson, Carbon::now()->addSeconds(2));
                $tiereds = json_decode($tieredsJson, true);
            } else {
                $tieredsJson = json_encode($tiereds, true);
                $tiereds     = json_decode($tieredsJson, true);
            }
        } else {
            $tiereds = json_decode($tieredsJson, true);
        }
        $tieredRs = null;
        if (! empty($tiereds)) {
            foreach ($tiereds as $tiered) {
                if ($tiered['num'] <= $quantity) {
                    $tieredRs = $tiered;
                    break;
                }
            }
        }

        return $tieredRs;
    }

    public static function getTieredBySkuCode($product_id, $sku_code, $quantity)
    {
        $key         = self::$key.$product_id.'-sku_code-'.$sku_code; //
        $tieredsJson = Cache::get($key);
        if (empty($tieredsJson)) {
            $tiereds = ProductSkuTieredPricing::query()->where('product_id', $product_id)->where('sku_code', $sku_code)->orderByDesc('num')->get();
            if ($tiereds->count() > 0) {
                $tieredsJson = json_encode($tiereds, true);
                Cache::put($key, $tieredsJson, Carbon::now()->addSeconds(2));
                $tiereds = json_decode($tieredsJson, true);
            } else {
                $tieredsJson = json_encode($tiereds, true);
                $tiereds     = json_decode($tieredsJson, true);
            }
        } else {
            $tiereds = json_decode($tieredsJson, true);
        }
        $tieredRs = null;
        if (! empty($tiereds)) {
            foreach ($tiereds as $tiered) {
                if ($tiered['num'] <= $quantity) {
                    $tieredRs = $tiered;
                    break;
                }
            }
        }

        return $tieredRs;
    }

    public static function getAllTieredPricing($product_id)
    {
        $key         = self::$key.$product_id; //
        $tieredsJson = Cache::get($key);
        if (empty($tieredsJson)) {
            $tiereds = ProductSkuTieredPricing::query()->where('product_id', $product_id)->orderBy('num')->get();
            if ($tiereds->count() > 0) {
                $tieredsJson = json_encode($tiereds, true);
                Cache::put($key, $tieredsJson, Carbon::now()->addSeconds(2));
                $tiereds = json_decode($tieredsJson, true);
            } else {
                $tieredsJson = json_encode($tiereds, true);
                $tiereds     = json_decode($tieredsJson, true);
            }
        } else {
            $tiereds = json_decode($tieredsJson, true);
        }

        return $tiereds;
    }

    public static function getProduct($product_sku_id)
    {
        $key            = self::$key.'-product_sku-'.$product_sku_id; //
        $productSkuJson = Cache::get($key);
        if (empty($productSkuJson)) {
            $productSku = Sku::query()->where('id', $product_sku_id)->first();
            if ($productSku) {
                $productSkuJson = json_encode($productSku, true);
                Cache::put($key, $productSkuJson, Carbon::now()->addSeconds(2)); //这个可能涉及到后台修改，只缓存5秒，以防一次请求太多查询
                $productSku = json_decode($productSkuJson, true);
            } else {
                $productSkuJson = json_encode($productSku, true);
                $productSku     = json_decode($productSkuJson, true);
            }
        } else {
            $productSku = json_decode($productSkuJson, true);
        }

        return $productSku;
    }
}
