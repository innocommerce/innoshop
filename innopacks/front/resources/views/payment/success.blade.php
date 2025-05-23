@extends('layouts.app')
@section('body-class', 'page-checkout-success')

@section('content')

  @if($order)
    <x-front-breadcrumb type="order" :value="$order"/>
  @endif

  @hookinsert('checkout.success.top')

  <div class="container">
    <div class="checkout-success-box">
      @if($order)
        <div class="order-success-icon"><img src="{{ asset('/images/icons/payment-success.svg') }}" class="img-fluid"></div>
        @if($order->payment_method_code == 'bank_transfer')
          <div class="checkout-success-title"><span>{{ trans('front/payment.bank_transfer_success_title') }}</span></div>
        @else
          <div class="checkout-success-title"><span>{{ trans('front/payment.success_title') }}</span></div>
        @endif
        <table class="table w-max-700 mx-auto mb-3 mb-md-5 checkout-success-table">
          <thead>
          <tr>
            <th>{{ trans('front/order.order_number') }}</th>
            <th>{{ trans('front/order.order_date') }}</th>
            <th>{{ trans('front/order.order_total') }}</th>
            <th>{{ trans('front/order.order_status') }}</th>
          </tr>
          </thead>
          <tbody>
          <tr>
            <td>{{ $order->number }}</td>
            <td>{{ $order->created_at->format('Y-m-d') }}</td>
            <td>{{ currency_format($order->total) }}</td>
            <td>{{ $order->status_format }}</td>
          </tr>
          </tbody>
        </table>

        <div class="checkout-success-btns d-flex flex-column justify-content-center w-max-400 mx-auto">
          @if(current_customer())
            <a href="{{ account_route('orders.number_show', ['number'=>$order->number]) }}"
               class="btn btn-lg btn-primary mb-3">{{ trans('front/payment.view_order') }}</a>
          @else
            <a href="{{ front_route('orders.number_show', ['number'=>$order->number]) }}"
               class="btn btn-lg btn-primary mb-3">{{ trans('front/payment.view_order') }}</a>
          @endif
          <a href="{{ front_route('home.index') }}" class="btn btn-lg btn-outline-primary">{{ trans('front/payment.continue_shopping') }}</a>
        </div>
      @else
        No order.
      @endif
    </div>
  </div>
  @hookinsert('checkout.success.bottom')
@endsection
