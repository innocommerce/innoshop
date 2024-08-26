@extends('layouts.app')
@section('body-class', 'page-order-info')

@section('content')
  <x-front-breadcrumb type="order" :value="$order"/>

  @hookinsert('account.order_info.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="account-card-box order-info-box">
          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/order.order_details') }}</span>
            @if($order->status == 'unpaid')
              <a href="{{ front_route('orders.pay', ['number'=>$order->number]) }}"
                 class="btn btn-primary">{{ __('front/order.continue_pay') }}</a>
            @elseif($order->status == 'completed')
              <a href="{{ account_route('order_returns.create', ['order_number'=>$order->number]) }}"
                 class="btn btn-primary">{{ __('front/order.create_rma') }}</a>
            @endif
          </div>
          <table class="table table-bordered table-striped mb-3 table-response">
            <thead>
            <tr>
              <th>{{ __('front/order.order_number') }}</th>
              <th>{{ __('front/order.order_date') }}</th>
              <th>{{ __('front/order.order_total') }}</th>
              <th>{{ __('front/order.order_status') }}</th>
            </tr>
            </thead>
            <tbody>
            <tr>
              <td data-title="Order ID">{{ $order->number }}</td>
              <td data-title="Order Date">{{ $order->created_at->format('Y-m-d') }}</td>
              <td data-title="Order Total">{{ $order->total_format }}</td>
              <td data-title="Order Status">{{ $order->status_format }}</td>
            </tr>
            </tbody>
          </table>

          <div class="products-table mb-4">
            <table class="table products-table align-middle">
              <thead>
              <tr>
                <th>{{ __('front/order.product') }}</th>
                <th>{{ __('front/order.price') }}</th>
                <th>{{ __('front/order.quantity') }}</th>
                <th>{{ __('front/order.subtotal') }}</th>
              </tr>
              </thead>
              <tbody>
              @foreach ($order->items as $product)
                <tr>
                  <td>
                    <div class="product-item">
                      <div class="product-image">
                        <img src="{{ $product['image'] }}" class="img-fluid">
                      </div>
                      <div class="product-info">
                        <div class="name">{{ $product['name'] }}</div>
                        <div class="sku mt-2 text-secondary">{{ $product['product_sku'] }}
                          @if ($product['variant_label']) - {{ $product['variant_label'] }} @endif
                        </div>
                      </div>
                    </div>
                  </td>
                  <td>{{ $product['price_format'] }}</td>
                  <td>{{ $product['quantity'] }}</td>
                  <td>{{ $product['price_format'] }}</td>
                </tr>
              @endforeach

              @foreach ($order->fees as $total)
                <tr>
                  <td></td>
                  <td></td>
                  <td><strong>{{ $total['title'] }}</strong></td>
                  <td>{{ $total->value_format }}</td>
                </tr>
              @endforeach
              <tr>
                <td></td>
                <td></td>
                <td><strong>{{ __('front/order.order_total') }}</strong></td>
                <td>{{ $order->total_format }}</td>
              </tr>
              </tbody>
            </table>
          </div>

          <div class="account-card-sub-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('common/address.address') }}</span>
          </div>

          <div class="row mb-4">
            <div class="col-12 col-md-6">
              <div class="address-card">
                <div class="address-card-header mb-3">
                  <h5 class="address-card-title border-bottom pb-3">{{ __('common/address.shipping_address') }}</h5>
                </div>
                <div class="address-card-body">
                  <p>{{ $order->shipping_customer_name }}</p>
                  <p>{{ $order->shipping_address_1 }} {{ $order->shipping_address_2 }}</p>
                  <p>{{ $order->shipping_city }}</p>
                  <p>{{ $order->shipping_state }}, {{ $order->shipping_country }}</p>
                  <p>Phone: {{ $order->shipping_telephone }}</p>
                </div>
              </div>
            </div>
            <div class="col-12 col-md-6">
              <div class="address-card">
                <div class="address-card-header mb-3">
                  <h5 class="address-card-title border-bottom pb-3">{{ __('common/address.billing_address') }}</h5>
                </div>
                <div class="address-card-body">
                  <p>{{ $order->billing_customer_name }}</p>
                  <p>{{ $order->billing_address_1 }} {{ $order->billing_address_2 }}</p>
                  <p>{{ $order->billing_city }}</p>
                  <p>{{ $order->billing_state }}, {{ $order->billing_country }}</p>
                  <p>Phone: {{ $order->billing_telephone }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="account-card-sub-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/order.order_history') }}</span>
          </div>

          <div class="table-responsive">
            <table class="table table-response">
              <thead>
              <tr>
                <th>{{ __('front/order.order_status') }}</th>
                <th>{{ __('front/order.remark') }}</th>
                <th>{{ __('front/order.order_date') }}</th>
              </tr>
              </thead>
              <tbody>
              @foreach($order->histories as $history)
                <tr>
                  <td data-title="State">{{ $history->status }}</td>
                  <td data-title="Remark">{{ $history->comment }}</td>
                  <td data-title="Update Time">{{ $history->created_at }}</td>
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.order_info.bottom')

@endsection