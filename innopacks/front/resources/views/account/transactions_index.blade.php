@extends('layouts.app')
@section('body-class', 'page-wallet')

@section('content')
  <x-front-breadcrumb type="route" value="account.wallet.transactions.index" title="{{ __('front/account.transactions') }}"/>

  @hookinsert('account.transaction_index.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="transaction-card-box">
          <div class="transaction-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/transaction.transaction') }}</span>
          </div>

          @if (session('success'))
            <x-common-alert type="success" msg="{{ session('success') }}" class="mt-3"/>
          @endif
          @if (session('error'))
            <x-common-alert type="danger" msg="{{ session('error') }}" class="mt-3"/>
          @endif

          @if ($transactions->count())
            <div class="table-responsive">
              <table class="table align-middle transaction-table-box table-response">
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
            </div>

            {{ $transactions->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
          @else
            <x-common-no-data text="{{ __('front/transaction.no_transactions') }}"/>
          @endif
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.transaction_index.bottom')

@endsection


