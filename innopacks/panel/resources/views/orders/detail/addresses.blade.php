{{-- Address Information --}}
<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/order.address') }}</h5>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-12 col-md-6">
        <div class="address-card">
          <div class="address-card-header mb-3">
            <h5 class="address-card-title">{{ __('panel/order.shipping_address') }}</h5>
          </div>
          <div class="address-card-body">
            <p>{{ __('common/address.name') }}: {{ $order->shipping_customer_name }}</p>
            <p>{{ __('common/address.phone') }}: {{ $order->shipping_telephone }}</p>
            <p>{{ __('common/address.zipcode') }}: {{ $order->shipping_zipcode }}</p>
            <p>{{ __('common/address.address_1') }}: {{ $order->shipping_address_1 }}</p>
            @if ($order->shipping_address_2)
              <p>{{ __('common/address.address_2') }}: {{ $order->shipping_address_2 }}</p>
            @endif
            <p>{{ __('common/address.region') }}: {{ $order->shipping_city }}, {{ $order->shipping_state }}
              , {{ $order->shipping_country }}</p>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6">
        <div class="address-card">
          <div class="address-card-header mb-3">
            <h5 class="address-card-title">{{ __('panel/order.billing_address') }}</h5>
          </div>
          <div class="address-card-body">
            <p>{{ __('common/address.name') }}: {{ $order->billing_customer_name }}</p>
            <p>{{ __('common/address.phone') }}: {{ $order->billing_telephone }}</p>
            <p>{{ __('common/address.zipcode') }}: {{ $order->billing_zipcode }}</p>
            <p>{{ __('common/address.address_1') }}: {{ $order->billing_address_1 }}</p>
            @if ($order->billing_address_2)
              <p>{{ __('common/address.address_2') }}: {{ $order->billing_address_2 }} </p>
            @endif
            <p>{{ __('common/address.region') }}: {{ $order->billing_city }}, {{ $order->billing_state }}
              , {{ $order->billing_country }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@hookinsert('panel.orders.detail.addresses.after')
