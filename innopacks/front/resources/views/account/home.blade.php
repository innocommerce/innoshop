@extends('layouts.app')
@section('body-class', 'page-account')

@section('content')
  <x-front-breadcrumb type="route" value="account.index" title="{{ __('front/account.account') }}"/>

  @hookinsert('account.home.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="account-card-box account-info">
          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/account.hello') }}, {{ $customer->name }}</span>
            <a href="{{ account_route('edit.index') }}" class="text-secondary">{{ __('front/account.edit') }} <i
                  class="bi bi-arrow-right"></i></a>
          </div>

          <div class="account-data">
            <div class="row">
              <div class="col-6 col-md-4">
                <div class="account-item-data">
                  <div class="value">{{ $order_total }}</div>
                  <div class="title text-secondary">{{ __('front/account.orders') }}</div>
                </div>
              </div>
              <div class="col-6 col-md-4">
                <div class="account-item-data">
                  <div class="value">{{ $fav_total }}</div>
                  <div class="title text-secondary">{{ __('front/account.favorites') }}</div>
                </div>
              </div>
              <div class="col-6 col-md-4">
                <div class="account-item-data">
                  <div class="value">{{ $address_total }}</div>
                  <div class="title text-secondary">{{ __('front/account.addresses') }}</div>
                </div>
              </div>
            </div>
          </div>

          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/account.orders') }}</span>
            <a href="{{ account_route('orders.index') }}" class="text-secondary">{{ __('front/account.view_all') }} <i
                  class="bi bi-arrow-right"></i></a>
          </div>

          @if($latest_orders->count())
            <table class="table align-middle account-table-box table-response">
              <thead>
              <tr>
                <th>{{ __('front/order.order_number') }}</th>
                <th>{{ __('front/order.order_date') }}</th>
                <th>{{ __('front/order.order_billing') }}</th>
                <th>{{ __('front/order.order_status') }}</th>
                <th>{{ __('front/order.order_total') }}</th>
                <th>{{ __('front/common.action') }}</th>
              </tr>
              </thead>
              <tbody>
              @foreach($latest_orders as $order)
                <tr>
                  <td data-title="Order ID">{{ $order->number }}</td>
                  <td data-title="Date">{{ $order->created_at->format('Y-m-d') }}</td>
                  <td data-title="Billing">{{ $order->billing_method_name }}</td>
                  <td data-title="Status">
                    <span class="badge {{ $order->status == 'completed' || $order->status == 'paid' ? 'bg-success' : 'bg-warning' }} ">{{ $order->status_format }}</span>
                  </td>
                  <td data-title="Total">{{ $order->total }}</td>
                  <td data-title="Actions">
                    <a href="{{ account_route('orders.show', ['order' => $order] ) }}" class="btn btn-primary">{{ __('front/common.view') }}</a>
                  </td>
                </tr>
              @endforeach
              </tbody>
            </table>
          @else
            <div class="no-order alert">
              <a href="{{ front_route('home.index') }}">
                <i class="bi bi-check-lg"></i>
                {!! __('front/account.no_order') !!}
              </a>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.home.bottom')

@endsection