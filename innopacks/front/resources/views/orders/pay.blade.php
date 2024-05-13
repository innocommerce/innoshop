@extends('layouts.app')
@section('body-class', 'page-checkout-success')

@section('content')

  <x-front-breadcrumb type="static" value="{{ front_route('orders.pay', ['number'=>$order->number]) }}" title="{{ $order->number }}"/>

  @hookinsert('order.pay.top')

  <div class="container">
    {!! $payment_view !!}
  </div>

  @hookinsert('order.pay.top')

@endsection

@push('footer')
  <script>

  </script>
@endpush