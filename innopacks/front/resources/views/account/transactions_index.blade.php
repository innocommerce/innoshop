@extends('layouts.app')
@section('body-class', 'page-transaction')

@section('content')
  <x-front-breadcrumb type="route" value="account.transactions.index" title="{{ __('front/account.transactions') }}"/>

  @hookinsert('account.transaction_index.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="transaction-card-box transaction-info">
         <div class="transaction-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/transaction.transaction') }}</span>
          </div>
          <div class="transaction-data">
            <div class="row">
              <div class="col-6 col-md-4">
                <div class="transaction-item-data">
                  <div class="value">{{ $balance }}</div>
                  <div class="title text-secondary">{{ __('front/transaction.total') }}</div>
                </div>
              </div>
              <div class="col-6 col-md-4">
                <div class="transaction-item-data">
                  <div class="value">{{ $frozen }}</div>
                  <div class="title text-secondary">{{ __('front/transaction.frozen') }}</div>
                </div>
              </div>
              <div class="col-6 col-md-4">
                <div class="transaction-item-data">
                  <div class="value">{{ $available }}</div>
                  <div class="title text-secondary">{{ __('front/transaction.available') }}</div>
                </div>
              </div>
            </div>
          </div>
          @if ($transactions->count())
              <table class="table align-middle transaction-table-box table-response table-bordered">
                <thead>
                <tr>
                  <th class="text-center">{{ __('front/transaction.type') }}</th>
                  <th class="text-center">{{ __('front/transaction.amount') }}</th>
                  <th class="text-center">{{ __('front/transaction.comment') }}</th>
                  <th class="text-center">{{ __('front/common.date') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($transactions as $transaction)
                  <tr>
                    <td class="text-center">{{ $transaction->type_format }}</td>
                    <td class="text-center">{{ $transaction->amount }}</td>
                    <td class="text-center">{{ $transaction->comment }}</td>
                    <td class="text-center">{{ $transaction->created_at }}</td>
                  </tr>
                @endforeach
                </tbody>
              </table>


            {{ $transactions->links('panel::vendor/pagination/bootstrap-4') }}
          @else
            <x-common-no-data/>
          @endif
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.transaction_index.bottom')

@endsection
