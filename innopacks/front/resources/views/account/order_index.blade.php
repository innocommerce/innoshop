@extends('layouts.app')
@section('body-class', 'page-order')

@section('content')
  <x-front-breadcrumb type="route" value="account.orders.index" title="{{ __('front/account.orders') }}" />

  @hookinsert('account.order_index.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="account-card-box order-box">
          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/order.order') }}</span>
          </div>

            <ul class="nav nav-tabs tabs-plus">
              <li class="nav-item">
                <a class="nav-link {{ request('status') == '' ? 'active' : '' }}" href="{{ account_route('orders.index') }}">{{ __('front/order.all') }}</a>
              </li>
              @foreach($filter_statuses as $status)
              <li class="nav-item">
                <a class="nav-link {{ request('status') == $status ? 'active' : '' }}" href="{{ account_route('orders.index', ['status' => $status]) }}">{{ __('front/order.'.$status) }}</a>
              </li>
              @endforeach
            </ul>

          @if ($orders->count())
            <table class="table align-middle account-table-box table-response">
              <thead>
              <tr>
                <th>{{ __('front/order.order_number') }}</th>
                <th>{{ __('front/order.order_items') }}</th>
                <th>{{ __('front/order.order_date') }}</th>
                <th>{{ __('front/order.order_status') }}</th>
                <th>{{ __('front/order.order_total') }}</th>
                <th>{{ __('front/common.action') }}</th>
              </tr>
              </thead>
              <tbody>
              @foreach($orders as $order)
                <tr>
                  <td data-title="Order ID">{{ $order->number }}</td>
                  <td data-title="Order Items">
                    <div class="d-flex">
                      @foreach($order->items->take(5) as $product)
                        <div class="wh-30 overflow-hidden border border-1 me-1">
                          <img src="{{ $product->image }}" alt="{{ $product->name }}" class="img-fluid">
                        </div>
                      @endforeach
                    </div>
                  </td>
                  <td data-title="Date">{{ $order->created_at->format('Y-m-d') }}</td>
                  <td data-title="Status">
                    <span class="badge {{ $order->status == 'completed' || $order->status == 'paid' ? 'bg-success' : 'bg-warning' }} ">{{ $order->status_format }}</span>
                  </td>
                  <td data-title="Total">{{ $order->total_format }}</td>
                  <td data-title="Actions">
                    <a href="{{ account_route('orders.show', ['order' => $order] ) }}" class="btn btn-primary">{{ __('front/common.view') }}</a>
                  </td>
                </tr>
              @endforeach
              </tbody>
            </table>

            {{ $orders->links('panel::vendor/pagination/bootstrap-4') }}
          @else
            <x-common-no-data />
          @endif
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.order_index.bottom')

@endsection