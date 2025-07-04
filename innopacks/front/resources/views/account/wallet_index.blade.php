@extends('layouts.app')
@section('body-class', 'page-wallet')

@section('content')
  <x-front-breadcrumb type="route" value="account.wallet.index" title="{{ __('front/account.wallet') }}"/>

  @hookinsert('account.wallet_index.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <!-- 余额概览 -->
        <div class="wallet-card-box wallet-balance">
          <div class="wallet-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/account.balance_overview') }}</span>
          </div>
          <div class="wallet-balance-data">
            <div class="row">
              <div class="col-6 col-md-4">
                <div class="wallet-balance-item">
                  <div class="value text-primary">{{ currency_format($balance) }}</div>
                  <div class="title text-secondary">{{ __('front/transaction.total') }}</div>
                </div>
              </div>
              <div class="col-6 col-md-4">
                <div class="wallet-balance-item">
                  <div class="value text-warning">{{ currency_format($freeze_balance) }}</div>
                  <div class="title text-secondary">{{ __('front/transaction.frozen') }}</div>
                </div>
              </div>
              <div class="col-6 col-md-4">
                <div class="wallet-balance-item">
                  <div class="value text-success">{{ currency_format($available_balance) }}</div>
                  <div class="title text-secondary">{{ __('front/transaction.available') }}</div>
                </div>
              </div>
            </div>
          </div>
          <div class="wallet-actions mt-3">
            <a href="{{ account_route('wallet.withdrawals.create') }}" 
               class="btn btn-primary {{ $has_pending_withdrawal ? 'disabled' : '' }}">
              <i class="bi bi-cash-coin"></i> {{ __('front/withdrawal.apply_withdrawal') }}
            </a>
            @if($has_pending_withdrawal)
              <small class="text-warning ms-2">{{ __('front/withdrawal.has_pending_withdrawal') }}</small>
            @endif
          </div>
        </div>

        <!-- 提现统计 -->
        <div class="wallet-card-box wallet-withdrawals mt-4">
          <div class="wallet-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/withdrawal.withdrawal_info') }}</span>
            <a href="{{ account_route('wallet.withdrawals.index') }}" class="text-secondary">
              {{ __('front/account.view_all') }} <i class="bi bi-arrow-right"></i>
            </a>
          </div>
          <div class="wallet-withdrawal-stats">
            <div class="row">
              <div class="col-6 col-md-3">
                <div class="wallet-stats-item">
                  <div class="value text-warning">{{ $withdrawal_stats['pending'] }}</div>
                  <div class="title text-secondary">{{ __('front/withdrawal.pending') }}</div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="wallet-stats-item">
                  <div class="value text-info">{{ $withdrawal_stats['approved'] }}</div>
                  <div class="title text-secondary">{{ __('front/withdrawal.approved') }}</div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="wallet-stats-item">
                  <div class="value text-success">{{ $withdrawal_stats['paid'] }}</div>
                  <div class="title text-secondary">{{ __('front/withdrawal.paid') }}</div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="wallet-stats-item">
                  <div class="value text-danger">{{ $withdrawal_stats['rejected'] }}</div>
                  <div class="title text-secondary">{{ __('front/withdrawal.rejected') }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 最近交易记录 -->
        <div class="wallet-card-box wallet-transactions mt-4">
          <div class="wallet-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/account.transactions') }}</span>
            <a href="{{ account_route('wallet.transactions.index') }}" class="text-secondary">
              {{ __('front/account.view_all') }} <i class="bi bi-arrow-right"></i>
            </a>
          </div>
          @if ($recent_transactions->count())
            <div class="table-responsive">
              <table class="table align-middle wallet-table-box table-response">
                <thead>
                <tr>
                  <th class="text-center">{{ __('front/transaction.type') }}</th>
                  <th class="text-center">{{ __('front/transaction.amount') }}</th>
                  <th class="text-center">{{ __('front/transaction.comment') }}</th>
                  <th class="text-center">{{ __('front/common.date') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($recent_transactions as $transaction)
                  <tr>
                    <td class="text-center">{{ $transaction->type_format }}</td>
                    <td class="text-center {{ $transaction->amount > 0 ? 'text-success' : 'text-danger' }}">
                      {{ $transaction->amount > 0 ? '+' : '' }}{{ currency_format($transaction->amount) }}
                    </td>
                    <td class="text-center">{{ $transaction->comment }}</td>
                    <td class="text-center">{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                  </tr>
                @endforeach
                </tbody>
              </table>
            </div>
          @else
            <x-common-no-data text="{{ __('front/transaction.no_transactions') }}"/>
          @endif
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.wallet_index.bottom')

@endsection

 