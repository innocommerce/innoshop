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
    'checkout'         => 'チェックアウト',
    'shipping_address' => '配送先住所',
    'billing_address'  => '請求先住所',

    'same_shipping_address' => '請求先住所は配送先住所と同じ',
    'create_address'        => '住所を作成',
    'shipping_methods'      => '配送方法',
    'billing_methods'       => '支払い方法',
    'order_comment'         => '注文コメント',
    'cancel_create'         => 'キャンセル',
    'my_order'              => '私の注文',
    'place_order'           => '注文を確定する',
    'failed'                => 'ユーザー名またはパスワードが間違っています。',
    'password'              => 'パスワードが間違っています。',
    'throttle'              => '試行回数が多すぎます。 :seconds 秒後に再試行してください。',
    'shipping_quote_error'  => 'プラグイン :classname のメソッド: public function getQuotes($checkoutService)" を実装してください。',

    'no_shipping_methods' => '配送方法がありません。管理者に連絡してください。',
    'no_billing_methods'  => '支払い方法がありません。管理者に連絡してください。',
];
