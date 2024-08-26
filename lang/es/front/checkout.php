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
    'shipping_address'      => 'Dirección de envío',
    'billing_address'       => 'Dirección de facturación',
    'same_shipping_address' => 'Igual que la dirección de envío',
    'create_address'        => 'Crear dirección',
    'shipping_methods'      => 'Métodos de envío',
    'billing_methods'       => 'Métodos de facturación',
    'order_comment'         => 'Comentario del pedido',
    'cancel_create'         => 'Cancelar',
    'my_order'              => 'Mi pedido',
    'place_order'           => 'Realizar pedido',
    'failed'                => 'Nombre de usuario o contraseña incorrectos.',
    'password'              => 'Contraseña incorrecta.',
    'throttle'              => 'Ha intentado iniciar sesión demasiadas veces. Por favor, inténtelo de nuevo en :seconds segundos.',
    'shipping_quote_error'  => 'Por favor, implemente el método public function getQuotes($checkoutService) en el plugin :classname.',

    'no_shipping_methods' => 'No hay métodos de envío, póngase en contacto con el administrador',
    'no_billing_methods'  => 'No hay métodos de facturación, póngase en contacto con el administrador',
];
