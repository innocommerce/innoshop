<?php

/*
|--------------------------------------------------------------------------
| Authentication Language Lines
|--------------------------------------------------------------------------
|
| The following language lines are used during authentication for various
| messages that we need to display to the user. You are free to modify
| these language lines according to your application's requirements.
|
*/

return [
    'checkout'         => '结账',
    'shipping_address' => '收货地址',
    'billing_address'  => '账单地址',

    'same_shipping_address' => '账单地址同收货地址',
    'create_address'        => '创建地址',
    'shipping_methods'      => '配送方式',
    'billing_methods'       => '支付方式',
    'order_comment'         => '订单备注',
    'cancel_create'         => '取消',
    'my_order'              => '我的订单',
    'place_order'           => '提交订单',
    'failed'                => '用户名或密码错误。',
    'password'              => '密码错误。',
    'throttle'              => '您尝试的登录次数过多，请 :seconds 秒后再试。',
    'shipping_quote_error'  => '请在插件 :classname 实现方法: public function getQuotes($checkoutService)"',

    'no_shipping_methods' => '没有配送方式，请联系管理员',
    'no_billing_methods'  => '没有支付方式，请联系管理员',
];
