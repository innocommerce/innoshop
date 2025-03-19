<?php

namespace Plugin\TieredPricing;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use InnoShop\Common\Models\Product\Sku;
use InnoShop\Common\Services\CartService;
use Plugin\TieredPricing\Models\ProductSkuTieredPricing;
use Plugin\TieredPricing\Services\TieredPricingService;

class Boot
{
    private $key = 'all_tiered_';

    public function init(): void
    {

        //设定价格的弹框
        listen_blade_insert('panel.product.edit.variant.after', function ($data) {
            //获取阶梯计价的数据
            $tiereds             = TieredPricingService::getAllTieredPricing($data['product']['id']);
            $allTieredPricesData = [];
            if (! empty($tiereds)) {
                foreach ($tiereds as $tiered) {
                    $allTieredPricesData[$tiered['sku_index']][] = [
                        'sku_index' => $tiered['sku_index'],
                        'price'     => $tiered['price'],
                        'num'       => $tiered['num'],
                    ];
                }
            }
            $data['allTieredPricesData'] = $allTieredPricesData;
            $view                        = view('TieredPricing::panel.tiered_pricing_pop', $data)->render();

            return $view;
        });

        //设定价格的按钮(多规格规格)
        listen_blade_insert('panel.product.edit.sku.input.item.price.after', function ($data) {
            $view = view('TieredPricing::panel.index', $data)->render();

            return $view;
        });

        //设定价格的按钮(单规格)
        listen_blade_insert('panel.product.edit.sku.single.input.item.price.after', function ($data) {
            $view = view('TieredPricing::panel.single_index', $data)->render();

            return $view;
        });

        listen_hook_filter('common.repo.product.create.after', function ($product) {
            $this->updateTieredPricing($product);

            return $product;
        }, 20009);

        listen_hook_filter('common.repo.product.update.after', function ($product) {
            $this->updateTieredPricing($product);

            return $product;
        }, 20009);

        //增加批量设置
        listen_blade_insert('panel.product.edit.sku.batch.input.item.after', function ($data) {
            $view = view('TieredPricing::panel.batch_copy_btn', $data)->render();

            return $view;
        });

        //////////////////////以下为前台显示逻辑//////////////////////////

        //详情数据增加阶梯价格数据
        listen_hook_filter('front.product.rendershow', function ($data) {

            $tiereds    = TieredPricingService::getAllTieredPricing($data['product']['id']);
            $tmpTiereds = [];
            if (! empty($tiereds)) {
                foreach ($tiereds as $tiered) {
                    $tiered['price_format']             = currency_format($tiered['price']);
                    $tmpTiereds[$tiered['sku_index']][] = $tiered;
                }
            }

            $setting           = plugin_setting('tiered_pricing');
            $init_min_quantity = 1;
            if (isset($setting['init_min_quantity'])) {
                $init_min_quantity = $setting['init_min_quantity'];
            }

            $skus = $data['skus'];
            $sku1 = $data['sku'];
            foreach ($skus as $key => $sku) {
                $skus[$key]['tiereds']           = isset($tmpTiereds[$key]) ? $tmpTiereds[$key] : [];
                $skus[$key]['init_min_quantity'] = $init_min_quantity;
                if ($sku1['id'] == $sku['id']) {
                    $sku1['tiereds']           = isset($tmpTiereds[$key]) ? $tmpTiereds[$key] : [];
                    $sku1['init_min_quantity'] = $init_min_quantity;
                }
            }
            $data['skus'] = $skus;
            $data['sku']  = $sku1;

            return $data;
        }, 2000199);

        //显示阶梯价格

        listen_blade_update('front.product.show.price', function ($output, $data) {
            $tiereds = TieredPricingService::getAllTieredPricing($data['product']['id']);
            if (! empty($tiereds)) {
                $setting           = plugin_setting('tiered_pricing');
                $init_min_quantity = 1;
                if (isset($setting['init_min_quantity'])) {
                    $init_min_quantity = $setting['init_min_quantity'];
                }
                $data['init_min_quantity'] = $init_min_quantity;
                $view                      = view('TieredPricing::front.price', $data)->render();

                return $view;
            } else {
                return $output;
            }
        }, 200001);

        //加入购物车数量检测
        listen_hook_action('front.cart.store.before', function ($data) {
            $sku_id = request()->sku_id;
            $sku    = Sku::query()->where('id', $sku_id)->first();
            if ($sku != null) {
                $product_id = $sku->product_id;
                $sku_code   = $sku->code;
                $this->checkAddCard($product_id, $sku_code);
            }
        });
        //加入购物车数量检测
        listen_hook_action('front.cart.update.before', function ($data) {
            $cart       = request()->cart;
            $product_id = $cart->product_id;
            $sku_code   = $cart->sku_code;
            $this->checkAddCard($product_id, $sku_code);
        });

        listen_hook_filter('service.multi_cart.response', function ($data) {
            $cartList = $data['list'];
            $amount   = 0;
            foreach ($cartList as $key => $item) {
                foreach ($item['items'] as $cart) {
                    $amount = bcadd($amount, $cart['subtotal'], 2);
                }
            }

            $data['amount']        = $amount;
            $data['amount_format'] = currency_format($amount);
            $data['list']          = $cartList;

            return $data;
        });
        listen_hook_filter('service.cart.response', function ($data) {
            $cartList = $data['list'];
            $amount   = 0;
            foreach ($cartList as $key => $cart) {
                $amount = bcadd($amount, $cart['subtotal'], 2);
            }

            $data['amount']        = $amount;
            $data['amount_format'] = currency_format($amount);
            $data['list']          = $cartList;

            return $data;
        });

        listen_hook_filter('resource.cart_list_item', function ($data) {
            $cartData = $data['data'];
            $cart     = $data['cart'];
            $tiered   = TieredPricingService::getTieredBySkuId($cartData['product_id'], $cartData['sku_id'], $cartData['quantity']);
            if ($tiered) {//存在批发设置
                $cartData['price']           = $tiered['price'];
                $cartData['price_format']    = currency_format($tiered['price']);
                $cartData['subtotal']        = bcmul($tiered['price'], $cart['quantity'], 2);
                $cartData['subtotal_format'] = currency_format($cartData['subtotal']);
            }
            $data['data'] = $cartData;

            return $data;
        });
    }

    private function checkAddCard($product_id, $sku_code)
    {

        $setting           = plugin_setting('tiered_pricing');
        $init_min_quantity = 1;
        if (isset($setting['init_min_quantity'])) {
            $init_min_quantity = $setting['init_min_quantity'];
        }
        if ($init_min_quantity == 2) {//无起批要求
            return;
        }
        $quantity = request()->get('quantity');
        $tiered   = ProductSkuTieredPricing::query()->where('product_id', $product_id)->where('sku_code', $sku_code)->orderBy('num')->first();
        if ($tiered) {
            if ($tiered['num'] > $quantity) {//达不到起批量，禁止修改
                $cartData = CartService::getInstance()->handleResponse();
                $json     = [
                    'success' => false,
                    'message' => trans('TieredPricing::common.init_min_quantity_tips').$tiered['num'],
                    'data'    => $cartData,
                    'id'      => request()->cart,
                ];

                echo json_encode($json);
                exit;
            }
        }
    }

    private function updateTieredPricing($product)
    {
        $pid = $product->id;
        if ($pid > 0) {
            ProductSkuTieredPricing::query()->where('product_id', $pid)->delete(); //清除旧数据
            $tieredDatas = request()->get('allTieredPricesData');
            if (! empty($tieredDatas)) {
                $tieredDatas = json_decode($tieredDatas, true);
                if (! empty($tieredDatas)) {
                    $skus = Sku::query()->where('product_id', $pid)->get([
                        'id',
                        'code',
                    ]);
                    $temProductSku = [];
                    foreach ($skus as $key2 => $sku) {
                        $temProductSku[$key2] = $sku;
                    }

                    foreach ($tieredDatas as $tieredDatas1) {
                        if (empty($tieredDatas1)) {
                            continue;
                        }
                        foreach ($tieredDatas1 as $tieredData) {
                            $tieredSaveData = [
                                'product_id' => $pid,
                                'sku_index'  => $tieredData['sku_index'],
                                'sku_id'     => $temProductSku[$tieredData['sku_index']]->id,
                                'sku_code'   => $temProductSku[$tieredData['sku_index']]->code,
                                'price'      => $tieredData['price'],
                                'num'        => $tieredData['num'],
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ];
                            ProductSkuTieredPricing::query()->insert($tieredSaveData);
                        }
                    }
                }
            }
            $key = TieredPricingService::$key.$pid; //
            Cache::delete($key);
        }
    }
}
