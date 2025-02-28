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
    @foreach ($order->items as $product)
        <tr>
            <td>{{ $product->id }}</td>
            <td>
                <div class="product-item d-flex align-items-center">
                    <div class="product-image wh-40 border"><img src="{{ $product->image }}" class="img-fluid"></div>
                    <div class="product-info ms-2">
                        <div class="name">{{ $product->name }}</div>
                    </div>
                </div>
            </td>
            <td>{{ $product->product_sku }} <br> 自定义：{{ $product->custom_sku }}</td>
            <td>{{ $product->quantity }}</td>
            <td>{{ $product->price_format }}</td>
            <td>{{ $product->subtotal_format }}</td>
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
