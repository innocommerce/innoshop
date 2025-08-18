@php
  $paymentMode = plugin_setting('stripe', 'payment_mode', 'elements');
@endphp

@if($paymentMode === 'elements')
  @include('Stripe::partials.elements')
@elseif($paymentMode === 'checkout')
  @include('Stripe::partials.checkout')
@endif