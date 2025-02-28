<?php

namespace Plugin\OrderEmail\Controllers;

use Illuminate\Http\Request;
use InnoShop\Common\Models\Order;
use Plugin\OrderEmail\Services\OrderEmailService;

class EmailController
{
    public function send(Request $request, $order_id, $status_str)
    {
        $order = Order::query()->where('id', $order_id)->first();
        OrderEmailService::sendEmail($order, urldecode($status_str));
    }
}
