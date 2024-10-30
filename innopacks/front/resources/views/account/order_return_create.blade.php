@extends('layouts.app')
@section('body-class', 'page-order')

@section('content')
  <x-front-breadcrumb type="route" value="account.order_returns.index" title="{{ __('front/account.order_returns') }}"/>

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
            <span class="fs-6">Order Number: <a
                  href="{{ account_route('orders.number_show', $number) }}">{{ $number }}</a></span>
          </div>

          <table class="table table-bordered table-striped mb-3 table-response">
            <thead>
            <tr>
              <th>{{__('common/rma.purchase_commodity')}}</th>
              <th>{{__('common/rma.purchase_quantity')}}</th>
              <th>{{__('common/rma.returned_quantity')}}</th>
              <th>{{__('common/rma.returnable_quantity')}}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($order->items as $item)
              <tr>
                <td>{{ $item->name . ' - ' . $item->product_sku }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->returns->sum('quantity') }}</td>
                <td>{{ $item->quantity - ($item->returns->sum('quantity')) }}</td>
              </tr>
            @endforeach
            </tbody>
          </table>

          @if($item->returns->sum('quantity') < $item->quantity)
            <form class="needs-validation edit-form" action="{{ account_route('order_returns.store') }}" method="POST"
                  novalidate>
              @csrf

              <div class="row">
                <div class="col-12 col-lg-6">
                  <x-common-form-select title="{{__('front/cart.product')}}" name="order_item_id" :options="$options"
                                        key="key" label="label" :emptyOption="false"
                                        required placeholder="{{__('front/cart.product')}}"/>
                </div>
                <div class="col-12 col-lg-6">
                  <x-common-form-input name="quantity" title="{{__('front/return.quantity')}}"
                                       value="{{ old('quantity', 1) }}" required="required"
                                       placeholder="{{ __('front/return.return_number') }}"/>
                </div>
                <div class="col-12 col-lg-6">
                  <x-common-form-switch-radio name="opened" title="{{__('front/return.opened')}}"
                                              value="{{ old('opened', 1) }}" required="required"
                                              placeholder="{{ __('front/return.return_number') }}"/>
                </div>
                <div class="col-12">
                  <x-common-form-textarea name="comment" title="{{__('front/return.comment')}}"
                                          value="{{ old('comment', '') }}" required="required"/>
                </div>
              </div>

              <button type="submit" class="btn btn-primary btn-lg mt-4 w-50">{{ __('front/common.submit') }}</button>
            </form>
          @endif

        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.order_return_create.bottom')

@endsection