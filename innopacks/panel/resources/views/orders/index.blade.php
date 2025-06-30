@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.orders'))

@section('page-title-right')
  @hookinsert('panel.orders.index.title.right')
@endsection

@section('content')
  <div class="card h-min-600">
    <div class="card-body">

      <x-panel-data-criteria :criteria="$criteria ?? []" :action="panel_route('orders.index')" :export="true" />

      @if ($orders->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <td>{{ __('panel/common.id') }}</td>
                <td>{{ __('panel/order.number') }}</td>
                <td>{{ __('panel/order.order_items') }}</td>
                <td>{{ __('panel/order.customer_name') }}</td>
                <td>{{ __('panel/order.shipping_method_name') }}</td>
                <td>{{ __('panel/order.billing_method_name') }}</td>
                <td>{{ __('panel/order.total') }}</td>
                <td>{{ __('panel/order.status') }}</td>
                @hookinsert('panel.orders.index.header.extra')
                <td>{{ __('panel/order.created_at') }}</td>
                <td>{{ __('panel/common.actions') }}</td>
              </tr>
            </thead>
            <tbody>
              @foreach ($orders as $item)
                <tr>
                  <td>{{ $item->id }}</td>
                  <td>{{ $item->number }} {{ $item->id == $item->parent_id ? 'M' : '' }}</td>
                  <td>
                    <div class="d-flex">
                      @foreach ($item->items->take(5) as $product)
                        <div class="wh-30 overflow-hidden border border-1 me-1">
                          <img src="{{ image_resize($product->image) }}" alt="{{ $product->name }}" class="img-fluid">
                        </div>
                      @endforeach
                    </div>
                  </td>
                  <td><a href="{{ panel_route('customers.edit', $item->customer_id) }}" class="text-decoration-none"
                      target="_blank">
                      {{ $item->customer_name }}
                    </a></td>
                  <td>{{ $item->shipping_method_name }}</td>
                  <td>{{ $item->billing_method_name }}</td>
                  <td>{{ $item->total_format }}</td>
                  <td><span class="badge bg-{{ $item->status_color }}">{{ $item->status_format }}</span></td>
                  @hookinsert('panel.orders.index.row.extra', $item)
                  <td>{{ $item->created_at }}</td>
                  <td>
                    <a href="{{ panel_route('orders.show', [$item->id]) }}"
                      class="btn btn-sm btn-outline-primary">{{ __('panel/common.view') }}</a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        {{ $orders->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
      @else
        <x-common-no-data />
      @endif
    </div>
  </div>
@endsection

@push('footer')
  <script></script>
@endpush
