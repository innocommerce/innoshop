@extends('layouts.app')
@section('body-class', 'page-checkout-success')

@section('content')

  <x-front-breadcrumb type="static" value="{{ front_route('orders.pay', ['number'=>$order->number]) }}" title="{{ $order->number }}"/>

  @hookinsert('order.pay.top')

  <div class="container">
    @if(isset($error))
      {{ $error }}
    @endif

    <table class="table w-max-800 mx-auto mb-3 mb-md-5 checkout-success-table">
      <thead>
      <tr>
        <th>{{ __('front/order.order_number') }}</th>
        <th>{{ __('front/order.order_billing') }}</th>
        <th>{{ __('front/order.order_total') }}</th>
        <th>{{ __('front/order.order_status') }}</th>
      </tr>
      </thead>
      <tbody>
      <tr>
        <td>{{ $order->number }}</td>
        <td>{{ $order->billing_method_name }}</td>
        <td>{{ currency_format($order->total) }}</td>
        <td>{{ $order->status_format }}</td>
      </tr>
      </tbody>
    </table>

    <div class="d-flex flex-column justify-content-center w-max-800 mx-auto">
      @if(isset($payment_view))
        {!! $payment_view !!}
      @endif
    </div>
  </div>

  @hookinsert('order.pay.bottom')

@endsection

@push('footer')
  <script></script>
@endpush