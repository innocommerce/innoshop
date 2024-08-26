@extends('layouts.app')
@section('body-class', 'page-order')

@section('content')
  <x-front-breadcrumb type="route" value="account.orders.index" title="{{ __('front/account.orders') }}" />

  @hookinsert('account.order_return_index.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="account-card-box order-box">
          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/account.order_returns') }}</span>
          </div>

          @if ($order_returns->count())
            <table class="table table-bordered table-striped mb-3 table-response">
              <thead>
              <tr>
                <th>{{ __('front/order.order_number') }}</th>
                <th>{{ __('front/return.return_number') }}</th>
                <th>{{ __('front/return.return_status') }}</th>
                <th>{{ __('front/common.created_at') }}</th>
                <th>{{ __('front/common.action') }}</th>
              </tr>
              </thead>
              <tbody>
              @foreach($order_returns as $item)
                <tr>
                  <td data-title="{{ __('front/return.return_number') }}"></td>
                  <td data-title="{{ __('front/return.return_date') }}"></td>
                  <td data-title="{{ __('front/return.return_date') }}">{{ $item->created_at }}</td>
                  <td data-title="{{ __('front/return.return_status') }}"></td>
                  <td data-title="{{ __('front/common.action') }}">
                    <a href="{{ account_route('order_returns.show', ['order_return'=>$item->id]) }}" class="btn btn-primary">{{ __('front/common.view') }}</a>
                  </td>
                </tr>
              @endforeach
              </tbody>
            </table>
          @else
            <x-common-no-data />
          @endif
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.order_return_index.bottom')

@endsection