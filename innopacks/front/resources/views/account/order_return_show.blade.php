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
      <x-common-alert type="danger" msg="{{ session('errors')->first() }}" class="mt-4" />
    @endif
        @if (session('success'))
      <x-common-alert type="success" msg="{{ session('success') }}" class="mt-4" />
    @endif

        <div class="account-card-title d-flex justify-content-between align-items-center">
          <span class="fw-bold">{{ __('front/account.order_returns') }}</span>
          <span class="fs-6">{{ $order_return->number }}</span>
        </div>
        <div class="row">
          <div class="col-3 mb-3 d-flex align-items-center">
            <label class="col-form-label me-3">{{ __('front/return.number') }}:</label>
          </div>
          <div class="col-9 mb-3 d-flex align-items-center">
            <span class="form-control-plaintext">{{ $order_return->number }}</span>
          </div>
          <div class="col-3 mb-3 d-flex align-items-center">
            <label class="col-form-label me-3">{{ __('front/return.order_number') }}:</label>
          </div>
          <div class="col-9 mb-3 d-flex align-items-center">
            <span class="form-control-plaintext">{{ $order_return->order_number}}</span>
          </div>
          <div class="col-3 mb-3 d-flex align-items-center">
            <label class="col-form-label me-3">{{ __('front/return.product_id') }}:</label>
          </div>
          <div class="col-9 mb-3 d-flex align-items-center">
            <span class="form-control-plaintext">{{ $order_return->product_id }}</span>
          </div>
          <div class="col-3 mb-3 d-flex align-items-center">
            <label class="col-form-label me-3">{{ __('front/return.opened') }}:</label>
          </div>
          <div class="col-9 mb-3 d-flex align-items-center">
            <span class="form-control-plaintext">{{ $order_return->opened ? __('front/common.yes') : __('front/common.no')  }}</span>
          </div>
          <div class="col-3 mb-3 d-flex align-items-center">
            <label class="col-form-label me-3">{{ __('front/return.comment') }}:</label>
          </div>
          <div class="col-9 mb-3 d-flex align-items-center">
            <span class="form-control-plaintext">{{ $order_return->comment }}</span>
          </div>
          <div class="col-3 mb-3 d-flex align-items-center">
            <label class="col-form-label me-3">{{ __('front/return.status') }}:</label>
          </div>
          <div class="col-9 mb-3 d-flex align-items-center">
            <span class="form-control-plaintext">{{ $order_return->status_format }}</span>
          </div>
          <div class="col-3 mb-3 d-flex align-items-center">
            <label class="col-form-label me-3">{{ __('front/return.created_at') }}:</label>
          </div>
          <div class="col-9 mb-3 d-flex align-items-center">
            <span class="form-control-plaintext">{{ $order_return->updated_at }}</span>
          </div>
          <div class="col-3 mb-3 d-flex align-items-center">
            <label class="col-form-label me-3">{{ __('front/return.product_name') }}:</label>
          </div>
          <div class="col-9 mb-3 d-flex align-items-center">
            <span class="form-control-plaintext">{{ $order_return->product_name }}</span>
          </div>
          <div class="col-3 mb-3 d-flex align-items-center">
            <label class="col-form-label me-3">{{ __('front/return.quantity') }}:</label>
          </div>
          <div class="col-9 mb-3 d-flex align-items-center">
            <span class="form-control-plaintext">{{ $order_return->quantity }}</span>
          </div>
         
        </div>

      </div>
    </div>
  </div>

  @hookinsert('account.order_return_create.bottom')

  @endsection