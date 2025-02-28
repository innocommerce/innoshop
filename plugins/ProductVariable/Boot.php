<?php

namespace Plugin\ProductVariable;

use InnoShop\Common\Models\Order\Item;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Services\StateMachineService;
use Plugin\ProductVariable\Models\OrderItemCustomSkus;
use Plugin\ProductVariable\Models\ProductCustomSkus;
use Plugin\ProductVariable\Models\ProductCustomSkusTranslations;
use Plugin\ProductVariable\Models\SkuOtherData;

class Boot
{
    public function init(): void
    {

        listen_hook_filter('panel.product.form', function ($data) {
            $product = $data['product'];
            if (! empty($product->variables)) {
                $variables = $product->variables;
                foreach ($variables as $key => $variable) {
                    if (! isset($variable['show_type'])) {
                        $variables[$key]['show_type'] = 1;
                    }
                }
                $product->variables = $variables;
                $data['product']    = $product;
            }
            //print_r(json_encode($product->variables));
            //exit;

            // 获取产品的自定义SKU数据
            if ($product->id) {
                $custom_skus = ProductCustomSkus::query()->where('product_id', $product->id)->with('translations')->get();

                // 转换数据格式以适应前端
                $formatted_skus = [];
                foreach ($custom_skus as $sku) {
                    $sku_data = [
                        'name' => [],
                    ];
                    foreach ($sku->translations as $translation) {
                        $sku_data['name'][$translation->locale] = $translation->name;
                    }
                    $formatted_skus[] = $sku_data;
                }

                // 将数据添加到视图
                $data['custom_skus'] = $formatted_skus;
            }

            return $data;
        }, 2000199);

        //显示方式
        listen_blade_insert('panel.product.edit.variant_name.after', function ($data) {
            $view = view('ProductVariable::panel.sku_show_type', $data)->render();

            return $view;
        }, 2000199);

        listen_blade_insert('panel.product.edit.form_variant.after', function ($data) {
            $view = view('ProductVariable::panel.custom_skus', $data)->render();

            return $view;
        }, 2000199);

        listen_hook_filter('common.repo.product.create.after', function ($product) {
            $this->updateProductCustomSku($product);

            return $product;
        }, 2000199);

        listen_hook_filter('common.repo.product.update.after', function ($product) {
            $this->updateProductCustomSku($product);

            return $product;
        }, 2000199);

        //前端界面
        listen_blade_update('front.products.show.variants.value', function ($output, $data) {
            $product    = $data['product'];
            $customSkus = ProductCustomSkus::query()->with('translation')->where('product_id', $product->id)->get();

            $data['custom_skus'] = $customSkus;
            $view                = view('ProductVariable::front.custom_skus', $data)->render();

            return $view;
        });

        listen_hook_filter('model.sku.variant_label_attribute', function ($data) {
            $sessionId    = session()->getId();
            $customer     = current_customer();
            $skuId        = $data['sku']->id;
            $skuOtherData = null;
            if (empty($customer)) {
                $skuOtherData = SkuOtherData::query()->where('session_id', $sessionId)->where('sku_id', $skuId)->first();
            } else {
                $skuOtherData = SkuOtherData::query()->where('user_id', $customer->id)->where('sku_id', $skuId)->first();
            }
            if ($skuOtherData) {
                $vLabel    = $data['vLabel'];
                $customSku = $skuOtherData->custom_sku;
                $customSku = json_decode($customSku, true);
                foreach ($customSku as $name => $val) {
                    $vLabel = $vLabel.$name.':'.$val.';';
                }
                $data['vLabel'] = $vLabel;
            }

            return $data;
        }, 2000199);

        listen_hook_filter('panel.order.form', function ($data) {
            $order  = $data['order'];
            $items  = $order->items;
            $itemId = [];
            foreach ($items as $item) {
                $itemId[] = $item->id;
            }
            $customSkus    = OrderItemCustomSkus::query()->whereIn('order_item_id', $itemId)->get();
            $tmpCustomSkus = [];
            foreach ($customSkus as $customSku) {
                $vLabel     = '';
                $customSku2 = $customSku->custom_sku;
                $customSku2 = json_decode($customSku2, true);
                foreach ($customSku2 as $name => $val) {
                    $vLabel = $vLabel.$name.':'.$val.';';
                }
                $tmpCustomSkus[$customSku->order_item_id] = $vLabel;
            }

            foreach ($items as $item) {
                $item->custom_sku = $tmpCustomSkus[$item->id];
            }

            return $data;
        }, 2000199);

        listen_blade_insert('panel.orders.info.order_items', function ($output, $data) {
            $view = view('ProductVariable::panel.order_items', $data)->render();

            return $view;
        }, 2000199);

        listen_hook_filter('service.state_machine.machines', function ($data) {

            $data['machines'][StateMachineService::CREATED][StateMachineService::UNPAID][] = function () use ($data) {
                $order        = $data['order'];
                $items        = Item::query()->where('order_id', $order->id)->get();
                $product_skus = [];
                foreach ($items as $item) {
                    $product_skus[$item->product_sku] = $item->id;
                }
                $skus = Product\Sku::query()->whereIn('code', array_keys($product_skus))->get([
                    'id',
                    'code',
                ]);

                $tmpSku = [];
                foreach ($skus as $sku) {
                    $tmpSku[$sku->id] = $sku->code;
                }

                $sessionId    = session()->getId();
                $customer     = current_customer();
                $skuOtherData = null;
                if (empty($customer)) {
                    $skuOtherDatas = SkuOtherData::query()->where('session_id', $sessionId)->whereIn('sku_id', array_keys($tmpSku))->get();
                } else {
                    $skuOtherDatas = SkuOtherData::query()->where('user_id', $customer->id)->whereIn('sku_id', array_keys($tmpSku))->get();
                }
                if ($skuOtherDatas->count() > 0) {
                    foreach ($skuOtherDatas as $skuOtherData) {
                        $skuCode = $tmpSku[$skuOtherData->sku_id];
                        OrderItemCustomSkus::query()->insert([
                            'order_item_id' => $product_skus[$skuCode],
                            'custom_sku'    => $skuOtherData->custom_sku,
                        ]);
                    }
                }

            };

            return $data;
        });

    }

    private function updateProductCustomSku($product)
    {
        $custom_skus = request()->get('custom_skus');
        if (! $custom_skus) {
            return;
        }

        // 解码 JSON 字符串
        $skus_data = json_decode($custom_skus, true);

        // 删除该产品的所有现有自定义 SKU
        $cSkusId = ProductCustomSkus::query()->where('product_id', $product->id)->get(['id'])->pluck('id');
        if (! empty($cSkusId)) {
            ProductCustomSkus::query()->where('product_id', $product->id)->delete();
            ProductCustomSkusTranslations::query()->whereIn('product_custom_skus_id', $cSkusId)->delete();
        }
        if (! $skus_data || ! is_array($skus_data)) {
            return;
        }

        // 创建新的自定义 SKU
        foreach ($skus_data as $sku_data) {
            if (empty($sku_data['name'])) {
                continue;
            }

            // 创建自定义 SKU
            $custom_sku_id = ProductCustomSkus::query()->insertGetId([
                'product_id' => $product->id,
            ]);

            // 创建多语言翻译
            foreach ($sku_data['name'] as $locale => $name) {
                if (! $name) {
                    continue;
                }

                ProductCustomSkusTranslations::query()->insert([
                    'product_custom_skus_id' => $custom_sku_id,
                    'locale'                 => $locale,
                    'name'                   => $name,
                ]);
            }
        }
    }
}
