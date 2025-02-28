<?php

namespace Plugin\OrderEmail\Services;

use InnoShop\Common\Models\Order\Item;
use Plugin\OrderEmail\Models\OrderEmail;

class OrderEmailService
{
    public static function httpGet($url)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 不直接输出，返回到变量
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60); // 连接超时（秒）
            curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 执行超时（秒）
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            // curl_setopt($ch, CURLOPT_POSTFIELDS, $sendInfo);
            $curl_result = curl_exec($ch);
            curl_close($ch);

            return $curl_result;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function sendEmail($order, $statusStr)
    {
        $setting = plugin_setting('order_email');
        $content = $setting['content'];
        $content = str_replace('{order:number}', $order->number, $content);
        $content = str_replace('{order:total}', $order->total, $content);
        $content = str_replace('{order:created_at}', $order->created_at, $content);

        $orderProducts = Item::query()->where('order_id', $order->id)->get();
        $productNames  = '';
        foreach ($orderProducts as $orderProduct) {
            $productNames = $productNames.$orderProduct->name.' (数量:'.$orderProduct->quantity.')'.'###';
        }
        $content = str_replace('{order:products_names}', $productNames, $content);

        //缓存验证结果
        $to_email = $setting['to_email'];

        $orderEmail = OrderEmail::query()->where('id', $order->id)->first();

        $orderEmail->notifyAdmin($to_email, 'OrderEmail::front.notify_email', "订单通知[{$statusStr}]", $content);

    }
}
