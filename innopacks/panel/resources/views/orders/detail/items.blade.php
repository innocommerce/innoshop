{{-- Order Items --}}
<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/order.order_items') }}</h5>
  </div>
  <div class="card-body">
    @hookupdate('panel.orders.detail.order_items')
      <table class="table products-table align-middle">
        <thead>
          <tr>
            <th>{{ __('panel/common.id') }}</th>
            <th>{{ __('panel/order.product') }}</th>
            <th>{{ __('panel/order.sku_code') }}</th>
            <th>{{ __('panel/order.quantity') }}</th>
            <th>{{ __('panel/order.unit_price') }}</th>
            <th>{{ __('panel/order.subtotal') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($order->items as $item)
            <tr>
              <td>{{ $item->id }}</td>
              <td>
                <div class="product-item d-flex align-items-center">
                  <div class="product-image wh-40 border"><img src="{{ $item->image }}" class="img-fluid">
                  </div>
                  <div class="product-info ms-2">
                    <div class="name">{{ $item->name }}</div>
                    @if ($item->productSku->variantLabel ?? '')
                      <span class="small fst-italic">{{ $item->productSku->variantLabel }}</span>
                    @endif
                    @if ($item->item_type_label)
                      <span class="badge bg-danger">{{ $item->item_type_label }}</span>
                    @endif
                    @include('panel::orders.bundle.details')
                  </div>
                </div>
              </td>
              <td>{{ $item->product_sku }}</td>
              <td>{{ $item->quantity }}</td>
              <td>{{ $item->price_format }}</td>
              <td>{{ $item->subtotal_format }}</td>
            </tr>
          @endforeach
          @foreach ($order->fees as $total)
            <tr>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td><strong>{{ $total->title }}</strong></td>
              <td>{{ $total->value_format }}</td>
            </tr>
          @endforeach
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><strong>{{ __('panel/order.total') }}</strong></td>
            <td>{{ $order->total_format }}</td>
          </tr>
        </tbody>
      </table>
    @endhookupdate
  </div>
</div>

@hookinsert('panel.orders.detail.order_items.after')
