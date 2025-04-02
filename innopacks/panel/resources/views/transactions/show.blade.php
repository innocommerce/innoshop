@extends('panel::layouts.app')
@section('body-class', 'page-transaction')
@section('title', __('panel/menu.transactions'))

<x-panel::form.right-btns/>

@section('content')
  <div class="card h-min-600">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('panel/menu.transactions') }}</h5>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-12 col-md-6 mb-3">
          <div class="col-sm-2 col-form-label text-start">
            <div class="fw-bold">{{ __('panel/transaction.customer') }}</div>
          </div>
          <div class="form-control-plaintext">{{ $transaction->customer->name ?? '' }}</div>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <div class="col-sm-2 col-form-label text-start">
            <div class="fw-bold">{{ __('panel/transaction.amount') }}</div>
          </div>
          <div class="form-control-plaintext">{{ $transaction->amount }}</div>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <div class="col-sm-2 col-form-label text-start">
            <div class="fw-bold">{{ __('panel/transaction.balance') }}</div>
          </div>
          <div class="form-control-plaintext">{{ $transaction->balance }}</div>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <div class="col-sm-2 col-form-label text-start">
            <div class="fw-bold">{{ __('panel/transaction.type') }}</div>
          </div>
          <div class="form-control-plaintext">{{ $transaction->type }}</div>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <div class="col-sm-2 col-form-label text-start">
            <div class="fw-bold">{{ __('panel/transaction.comment') }}</div>
          </div>
          <div class="form-control-plaintext">{{ $transaction->comment }}</div>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <div class="col-sm-2 col-form-label text-start">
            <div class="fw-bold">{{ __('panel/common.created_at') }}</div>
          </div>
          <div class="form-control-plaintext">{{ $transaction->created_at }}</div>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <div class="col-sm-2 col-form-label text-start">
            <div class="fw-bold">{{ __('panel/common.updated_at') }}</div>
          </div>
          <div class="form-control-plaintext">{{ $transaction->updated_at }}</div>
        </div>
      </div>
    </div>
  </div>
@endsection
