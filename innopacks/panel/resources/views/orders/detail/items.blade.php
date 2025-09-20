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
                    {{-- 显示订单项选项信息 --}}
                    @if ($item->options && $item->options->count() > 0)
                      <div class="item-options mt-1">
                        @foreach ($item->options as $option)
                          <div class="option-item small text-muted">
                            <strong>{{ $option->option_name_localized }}:</strong>
                            {{ $option->option_value_name_localized }}
                            @if ($option->price_adjustment != 0)
                              <span class="text-success">({{ $option->price_adjustment > 0 ? '+' : '' }}{{ $option->price_format }})</span>
                            @endif
                          </div>
                        @endforeach
                      </div>
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
