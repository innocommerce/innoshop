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
    'checkout'              => 'Checkout',
    'shipping_address'      => 'Shipping Address',
    'billing_address'       => 'Billing Address',
    'same_shipping_address' => 'Same as Shipping Address',
    'create_address'        => 'Create Address',
    'shipping_methods'      => 'Shipping Methods',
    'billing_methods'       => 'Billing Methods',
    'order_comment'         => 'Order Comment',
    'cancel_create'         => 'Cancel',
    'my_order'              => 'My Order',
    'place_order'           => 'Place Order',
    'failed'                => 'Username or password is incorrect.',
    'password'              => 'Password is incorrect.',
    'throttle'              => 'You have attempted to log in too many times. Please try again in :seconds seconds.',
    'shipping_quote_error'  => 'Please implement the method public function getQuotes($checkoutService) in the plugin :classname.',

    'no_shipping_methods' => 'No shipping methods, please contact the administrator',
    'no_billing_methods'  => 'No billing methods, please contact the administrator',
];
