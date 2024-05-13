@extends('layouts.app')
@section('body-class', 'page-checkout-success')

@section('content')

<x-front-breadcrumb type="order" :value="$order"/>

@hookinsert('checkout.success.top')

<div class="container">
  <div class="checkout-success-box">
    <div class="order-success-icon"><img src="{{ asset('/icon/order-success.svg') }}" class="img-fluid"></div>
    <div class="checkout-success-title"><span>Thank you. Your order has been received.</span></div>
    <table class="table w-max-700 mx-auto mb-3 mb-md-5 checkout-success-table">
      <thead>
        <tr>
          <th>Order Number</th>
          <th>Order Date</th>
          <th>Order Total</th>
          <th>Order Status</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>{{ $order->number }}</td>
          <td>{{ $order->created_at->format('Y-m-d') }}</td>
          <td>{{ currency_format($order->total) }}</td>
          <td>{{ $order->status }}</td>
        </tr>
      </tbody>
    </table>

    <div class="checkout-success-btns d-flex flex-column justify-content-center w-max-400 mx-auto">
      <a href="{{ front_route('account.orders.show', ['order'=>$order]) }}" class="btn btn-lg btn-primary mb-3">View Orders</a>
      <a href="{{ front_route('home.index') }}" class="btn btn-lg btn-outline-primary">Continue Shopping</a>
    </div>
  </div>
</div>

@hookinsert('checkout.success.bottom')

@endsection

@push('footer')
<script>

</script>
@endpush