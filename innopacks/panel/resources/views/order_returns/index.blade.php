@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.order_returns'))

@section('content')
  <div class="card h-min-600">
    <div class="card-body">
      <x-panel-data-data-search
        :action="panel_route('order_returns.index')"
        :searchFields="$searchFields ?? []"
        :filters="$filterButtons ?? []"
        :enableDateRange="true"
      />
      @if ($order_returns->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
            <tr>
              <td>{{ __('common/base.id') }}</td>
              <td>{{ __('front/return.number') }}</td>
              <td>{{ __('panel/order_return.customer') }}</td>
              <td>{{ __('front/return.product_name') }}</td>
              <td>{{ __('front/return.quantity') }}</td>
              <td>{{ __('front/return.opened') }}</td>
              <td>{{ __('front/return.status') }}</td>
              <td>{{ __('panel/order.create_time') }}</td>
              @hookinsert('panel.order_returns.index.header.extra')
              <td>{{ __('panel/common.actions') }}</td>
            </tr>
            </thead>
            <tbody>

            @foreach($order_returns as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->number }}</td>
                <td>
                  @if($item->customer)
                    <a href="{{ panel_route('customers.edit', $item->customer_id) }}" target="_blank">{{ $item->customer->name }}</a>
                    <br><span class="text-muted small">{{ $item->customer->email }}</span>
                  @endif
                </td>
                <td>
                  <div class="d-flex align-items-center">
                    @if($item->product)
                      <div class="wh-30 border rounded me-2">
                        <img src="{{ $item->product->image_url }}" alt="{{ $item->product_name }}" class="img-fluid rounded">
                      </div>
                    @endif
                    <div>
                      <div>{{ sub_string($item->product_name, 50) }}</div>
                      <div class="text-muted small">
                        <a href="{{ panel_route('orders.edit', $item->order_id) }}" target="_blank">{{ $item->order_number }}</a>
                      </div>
                    </div>
                  </div>
                </td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->opened ? __('common/base.yes') : __('common/base.no') }}</td>
                <td><span class="badge bg-{{ $item->status_color }}">{{ $item->status_format }}</span></td>
                <td>{{ $item->created_at }}</td>

                @hookinsert('panel.order_returns.index.row.extra', $item)

                <td>
                  <a href="{{ panel_route('order_returns.edit', [$item->id]) }}"
                     class="btn btn-sm btn-outline-primary">{{ __('common/base.view')}}</a>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
        {{ $order_returns->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
      @else
        <x-common-no-data/>
      @endif
    </div>
  </div>
@endsection
@push('footer')
  <script>
  </script>
@endpush
