@extends('layouts.app')
@section('body-class', 'page-order')

@section('content')
  <x-front-breadcrumb type="route" value="account.orders.index" title="{{ __('front/account.orders') }}" />

  @hookinsert('account.order_return_create.top')

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

          {{-- 为订单 {{ $number }} 创建售后 --}}
          <form class="needs-validation edit-form" action="{{ account_route('order_returns.store') }}" method="POST" novalidate>
            @csrf

            <div class="row">
              <div class="col-12 col-lg-6">
                <x-common-form-input name="name" title="{{ __('common/address.name') }}" value="{{ old('name') }}" required="required" placeholder="{{ __('common/address.name') }}" />
              </div>
              <div class="col-12 col-lg-6">
                <x-common-form-input name="number" title="{{ __('front/return.return_number') }}" value="{{ old('number') }}" required="required" placeholder="{{ __('front/return.return_number') }}" />
              </div>
              <div class="col-12 col-lg-6">
                <x-common-form-input name="email" title="{{ __('front/edit.email') }}" value="{{ old('email') }}" required="required" placeholder="{{ __('front/edit.email') }}" />
              </div>
              <div class="col-12 col-lg-6">
                <x-common-form-input name="telephone" title="{{ __('front/common.telephone') }}" value="{{ old('telephone') }}" required="required" placeholder="{{ __('front/common.telephone') }}" />
              </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg mt-4 w-50">{{ __('front/common.submit') }}</button>
          </form>

        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.order_return_create.bottom')

@endsection