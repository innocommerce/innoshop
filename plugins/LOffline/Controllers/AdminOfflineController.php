<?php

namespace Plugin\LOffline\Controllers;

use Illuminate\Http\Request;
use InnoShop\Common\Models\Order;
use Plugin\LOffline\Models\OfflinePaymentConfigDescriptions;
use Plugin\LOffline\Models\OfflinePaymentOrder;

class AdminOfflineController
{
    public function index(Request $request)
    {
        $offlinePDs = OfflinePaymentConfigDescriptions::query()->get();
        if ($offlinePDs->count() > 0) {
            $data['offline_payment_descriptions'] = $offlinePDs;
        } else {
            $data['offline_payment_descriptions'] = [];
        }
        $data['languages2'] = locales();

        return view('LOffline::panel.config_form', $data)->render();
    }

    public function save_config(Request $request)
    {
        $descriptions = $request->descriptions;
        $saveData     = [];
        foreach ($descriptions as $locale => $description) {
            if (empty($description)) {
                return response()->json([
                    'code' => -1,
                    'msg'  => '内容不能为空',
                ]);
            }
            $saveData[] = [
                'content' => $description,
                'locale'  => $locale,
            ];
        }
        OfflinePaymentConfigDescriptions::query()->delete();
        OfflinePaymentConfigDescriptions::query()->insert($saveData);

        return response()->json([
            'code' => 0,
            'msg'  => '保存成功',
        ]);
    }

    public function pay_certificate(Request $request)
    {
        $order_id = $request->order_id;
        $order    = Order::query()->where('id', $order_id)->first();
        if ($order->billing_method_code != 'l_offline') {
            exit('无数据');
        }
        $offlineP = OfflinePaymentOrder::query()->where('order_id', $order->id)->first();
        if (empty($offlineP)) {
            exit('无数据');
        }

        $offline_imgs = json_decode($offlineP['imgs'], true);
        foreach ($offline_imgs as $key => $offline_img) {
            $offline_imgs[$key] = asset('/plugins/loffline/uploads/'.$offline_img);
        }

        $data['offline_imgs'] = $offline_imgs;

        return view('LOffline::panel.certificate', $data)->render();
    }
}
