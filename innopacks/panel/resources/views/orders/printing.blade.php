<!DOCTYPE html>
<html dir="{{ panel_locale_direction() }}" lang="{{ panel_locale_code() }}">
<head>
  <meta charset="UTF-8"/>
  <title>{{ config('app.name') }} {{ __("panel/order.ship_list") }}</title>
  <link href="{{ mix('/build/panel/css/bootstrap.css') }}" rel="stylesheet">
  <style media="print">
    .printer {
      display: none;
    }

    .btn {
      display: none;
    }
  </style>
</head>

<body>
<div class="container">
  <div id="print-button">
    <p style="text-align: right;">
      <button class="btn btn-success right" type="button" onclick="window.print()"
              class="printer">{{ __("panel/order.print") }}</button>
    </p>
  </div>

  <div style="page-break-after: always;">
    <h1 style="text-align: center;">{{ config('app.name') }} {{ __("panel/order.ship_list") }}</h1>
    <table class="table">
      <tbody>
      <tr>
        <td>
          <b>{{ __("panel/order.shipping_customer_name") }}: </b> {{ $order['shipping_customer_name'] }}<br/>
          <b>{{ __("panel/order.telephone") }}: </b> {{ $order['shipping_telephone'] }}<br/>
          <b>{{ __("panel/order.email") }}: </b> {{ $order['email'] }}<br/>
          <b>{{ __("panel/order.shipping_address") }}:
          </b> {{ $order['shipping_customer_name'] . "(" . $order['shipping_telephone'] . ")". ' ' . $order['shipping_address_1'] . ' ' . $order['shipping_address_2'] . ' ' . $order['shipping_city'] . ' ' . $order['shipping_zone'] . ' ' . $order['shipping_country'] }}
          <br/>
        </td>
        <td style="width: 50%;">
          <b>{{ __("panel/order.number") }}: </b> {{ $order['number'] }}<br/>
          <b>{{ __("panel/order.created_at") }}: </b> {{ $order['created_at'] }}<br/>
        </td>
      </tr>
      </tbody>
    </table>
    <table class="table table-bordered">
      <thead>
      <tr>
        <td><b>{{ __("panel/order.index") }}</b></td>
        <td><b>{{ __("panel/order.image") }}</b></td>
        <td><b>{{ __("panel/order.product") }}</b></td>
        <td><b>{{ __("panel/order.sku_code") }}</b></td>
        <td class="text-right"><b>{{ __("panel/order.quantity") }}</b></td>
        <td class="text-right"><b>{{ __("panel/order.unit_price") }}</b></td>
        <td class="text-right"><b>{{ __("panel/order.total") }}</b></td>
      </tr>
      </thead>
      <tbody>
      @if ($order->items)
        @foreach ($order->items as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td><img class="img-thumbnail" src="{{ $item['image'] }}" style="width: 30px;"></td>
            <td>{{ $item['name'] }}
              @if($item->productSku->variantLabel)
                <br/><span class="small fst-italic">{{ $item->productSku->variantLabel }}</span>
              @endif
            </td>
            <td>{{ $item['product_sku'] }}</td>
            <td class="text-right">{{ $item['quantity'] }}</td>
            <td class="text-right">{{ currency_format($item['price']) }}</td>
            <td class="text-right">{{ currency_format($item['subtotal']) }}</td>
          </tr>
        @endforeach
      @endif
      </tbody>
    </table>
    <table class="table">
      <thead style="border-top: 1px solid #ddd;">
      @foreach ($order->fees as $total)
        <tr>
          <td colspan="6"></td>
          <td><b>{{ $total->title }}</b>: {{ $total->value_format }}</td>
        </tr>
      @endforeach
      <tr>
        <td colspan="6"></td>
        <td><b>{{ __("panel/order.total") }}</b>: {{ currency_format($order['total']) }}</td>
      </tr>
      </thead>
      <tbody>
      <tr>
        <td colspan="3">
          <b>{{ $order['store_name'] }}</b> <br/>
          <b>{{ __("panel/order.telephone") }}: </b> {{ $order['shipping_telephone'] }}<br/>
          <b>{{ __("panel/order.email") }}: </b> {{ $order['email'] }}<br/>
          <b>{{ __("panel/order.website") }}: </b> <a href="{{ config('app.url') }}">{{ config('app.url') }}</a></td>
        </td>
      </tr>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>
