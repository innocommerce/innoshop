<?php

namespace Plugin\ProductVariable\Controllers;

use Illuminate\Http\Request;
use Plugin\ProductVariable\Models\SkuOtherData;

class SkuController
{
    public function store(Request $request)
    {
        $sessionId    = session()->getId();
        $customer     = current_customer();
        $custom_sku   = $request->custom_sku;
        $skuId        = $request->sku_id;
        $skuOtherData = null;
        if (empty($customer)) {
            $skuOtherData = SkuOtherData::query()->where('session_id', $sessionId)->where('sku_id', $skuId)->first();
        } else {
            $skuOtherData = SkuOtherData::query()->where('user_id', $customer->id)->where('sku_id', $skuId)->first();
        }
        if (empty($custom_sku)) {
            if (! empty($skuOtherData)) {
                SkuOtherData::query()->where('id', $skuOtherData->id)->delete();
            }
        } else {
            if (empty($skuOtherData)) {
                $skuOtherData             = new SkuOtherData;
                $skuOtherData->session_id = $sessionId;
                $skuOtherData->user_id    = empty($customer) ? 0 : $customer->id;
                $skuOtherData->sku_id     = $skuId;
                $skuOtherData->custom_sku = json_encode($custom_sku, true);
                $skuOtherData->save();
            } else {
                $skuOtherData->custom_sku = json_encode($custom_sku, true);
                $skuOtherData->update();
            }
        }

        return response()->json();
    }
}
