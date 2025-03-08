@extends('layouts.app')
@section('body-class', 'page-order')

@section('content')
  <x-front-breadcrumb type="order_return" :value="$order_return"/>

  @hookinsert('account.order_return_create.top')
  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>

      <div class="col-12 col-lg-9">
        <div class="account-card-box order-box">
          @if (session()->has('errors'))
            <x-common-alert type="danger" msg="{{ session('errors')->first() }}" class="mt-4"/>
          @endif
          @if (session('success'))
            <x-common-alert type="success" msg="{{ session('success') }}" class="mt-4"/>
          @endif

          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/account.order_returns') }}</span>
            <span class="fs-6">{{ $order_return->number }}</span>
          </div>

          <table class="table table-bordered">
            <tbody>
            <tr>
              <td class="order_return">{{ __('front/return.number') }}:</td>
              <td class="order_return">{{ $order_return->number }}</td>
            </tr>
            <tr>
              <td class="order_return">{{ __('front/return.order_number') }}:</td>
              <td class="order_return">{{ $order_return->order_number }}</td>
            </tr>
            <tr>
              <td class="order_return">{{ __('front/return.product_id') }}:</td>
              <td class="order_return">{{ $order_return->product_id }}</td>
            </tr>
            <tr>
              <td class="order_return">{{ __('front/return.opened') }}:</td>
              <td class="order_return">{{ $order_return->opened ? __('front/common.yes') : __('front/common.no') }}</td>
            </tr>
            <tr>
              <td class="order_return">{{ __('front/return.comment') }}:</td>
              <td class="order_return">{{ $order_return->comment }}</td>
            </tr>
            <tr>
              <td class="order_return">{{ __('front/return.status') }}:</td>
              <td class="order_return">{{ $order_return->status_format }}</td>
            </tr>
            <tr>
              <td class="order_return">{{ __('front/return.created_at') }}:</td>
              <td class="order_return">{{ $order_return->updated_at }}</td>
            </tr>
            <tr>
              <td class="order_return">{{ __('front/return.product_name') }}:</td>
              <td class="order_return">{{ $order_return->product_name }}</td>
            </tr>
            <tr>
              <td class="order_return">{{ __('front/return.quantity') }}:</td>
              <td class="order_return">{{ $order_return->quantity }}</td>
            </tr>
            </tbody>
          </table>

          <div class="table-responsive mt-5">
            <div class="account-card-title d-flex justify-content-between align-items-center">
              <span class="fw-bold">{{ __('panel/order.history') }}</span>
            </div>
            <table class="table table-bordered">
              <thead>
              <tr>
                <th class="order_return">{{ __('front/order.order_status') }}</th>
                <th class="order_return">{{ __('front/order.remark') }}</th>
                <th class="order_return">{{ __('front/order.order_date') }}</th>
              </tr>
              </thead>

              @if($histories->count())
                <tbody>
                @foreach($histories as $history)
                  <tr>
                    <td class="order_return">{{ $history->status_format }}</td>
                    <td class="order_return">{{ $history->comment }}</td>
                    <td class="order_return">{{ $history->created_at }}</td>
                  </tr>
                @endforeach
                </tbody>
              @endif
            </table>
          </div>
        </div>
      </div>
    </div>

    @hookinsert('account.order_return_create.bottom')
@endsection