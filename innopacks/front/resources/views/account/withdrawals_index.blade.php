@extends('layouts.app')
@section('body-class', 'page-wallet')

@section('content')
  <x-front-breadcrumb type="route" value="account.wallet.withdrawals.index" title="{{ __('front/withdrawal.my_withdrawals') }}"/>

  @hookinsert('account.withdrawals_index.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="withdrawal-card-box">
          <div class="withdrawal-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/withdrawal.withdrawal_history') }}</span>
            <a href="{{ account_route('wallet.withdrawals.create') }}" class="btn btn-primary btn-sm">
              <i class="bi bi-plus-circle"></i> {{ __('front/withdrawal.apply_withdrawal') }}
            </a>
          </div>

          @if (session('success'))
            <x-common-alert type="success" msg="{{ session('success') }}" class="mt-3"/>
          @endif
          @if (session('error'))
            <x-common-alert type="danger" msg="{{ session('error') }}" class="mt-3"/>
          @endif

          @if ($withdrawals->count())
            <div class="table-responsive">
              <table class="table align-middle withdrawal-table-box table-response">
                <thead>
                <tr>
                  <th class="text-center">{{ __('front/withdrawal.amount') }}</th>
                  <th class="text-center">{{ __('front/withdrawal.account_type') }}</th>
                  <th class="text-center">{{ __('front/withdrawal.account_number') }}</th>
                  <th class="text-center">{{ __('front/withdrawal.status') }}</th>
                  <th class="text-center">{{ __('front/withdrawal.created_at') }}</th>
                  <th class="text-center">{{ __('front/common.action') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($withdrawals as $withdrawal)
                  <tr>
                    <td class="text-center">
                      <span class="fw-bold text-primary">{{ currency_format($withdrawal->amount) }}</span>
                    </td>
                    <td class="text-center">{{ $withdrawal->account_type_format }}</td>
                    <td class="text-center">
                      <span class="text-muted">{{ substr($withdrawal->account_number, 0, 6) }}****{{ substr($withdrawal->account_number, -4) }}</span>
                    </td>
                    <td class="text-center">
                      @switch($withdrawal->status)
                        @case('pending')
                          <span class="badge bg-warning">{{ $withdrawal->status_format }}</span>
                          @break
                        @case('approved')
                          <span class="badge bg-info">{{ $withdrawal->status_format }}</span>
                          @break
                        @case('paid')
                          <span class="badge bg-success">{{ $withdrawal->status_format }}</span>
                          @break
                        @case('rejected')
                          <span class="badge bg-danger">{{ $withdrawal->status_format }}</span>
                          @break
                        @default
                          <span class="badge bg-secondary">{{ $withdrawal->status_format }}</span>
                      @endswitch
                    </td>
                    <td class="text-center">{{ $withdrawal->created_at->format('Y-m-d H:i') }}</td>
                    <td class="text-center">
                      <a href="{{ account_route('wallet.withdrawals.show', $withdrawal->id) }}" 
                         class="btn btn-outline-primary btn-sm">
                        {{ __('front/common.view') }}
                      </a>
                    </td>
                  </tr>
                @endforeach
                </tbody>
              </table>
            </div>

            {{ $withdrawals->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
          @else
            <x-common-no-data text="{{ __('front/withdrawal.no_withdrawals') }}"/>
          @endif
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.withdrawals_index.bottom')

@endsection

 