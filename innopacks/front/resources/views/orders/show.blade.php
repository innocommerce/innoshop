@extends('layouts.app')
@section('body-class', 'page-checkout-success')

@section('content')

  <x-front-breadcrumb type="static" value="{{ front_route('orders.pay', ['number'=>$order->number]) }}" title="{{ $order->number }}"/>

  @hookinsert('order.show.top')

  <div class="container">
    <div class="row">
      <div class="account-card-box order-info-box">
        <div class="account-card-title d-flex justify-content-between align-items-center">
          <span class="fw-bold">{{ __('front/order.order_details') }}</span>
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
                        @if ($product['variant_label'])
                          - {{ $product['variant_label'] }}
                        @endif
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
      </div>
    </div>

    @hookinsert('order.show.top')

    @endsection

    @push('footer')
      <script>

      </script>
  @endpush