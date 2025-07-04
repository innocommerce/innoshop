@extends('layouts.app')

@section('content')
  <x-front-breadcrumb type="route" value="account.wallet.withdrawals.index" title="{{ __('front/withdrawal.withdrawal_detail') }}"/>

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="withdrawal-detail-box bg-white border rounded p-4 mb-4">
          <div class="withdrawal-card-title d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
            <span class="fw-bold">{{ __('front/withdrawal.withdrawal_detail') }}</span>
            <a href="{{ account_route('wallet.withdrawals.index') }}" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-arrow-left"></i> {{ __('common/base.back') }}
            </a>
          </div>

          <div class="withdrawal-info mt-3">
            <div class="row mb-4">
              <div class="col-12 text-center">
                <div class="status-badge mb-4 mt-4">
                  @switch($withdrawal->status)
                    @case('pending')
                      <span class="badge bg-warning fs-6 px-3 py-2">
                        <i class="bi bi-clock"></i> {{ $withdrawal->status_format }}
                      </span>
                      @break
                    @case('approved')
                      <span class="badge bg-info fs-6 px-3 py-2">
                        <i class="bi bi-check-circle"></i> {{ $withdrawal->status_format }}
                      </span>
                      @break
                    @case('paid')
                      <span class="badge bg-success fs-6 px-3 py-2">
                        <i class="bi bi-check-circle-fill"></i> {{ $withdrawal->status_format }}
                      </span>
                      @break
                    @case('rejected')
                      <span class="badge bg-danger fs-6 px-3 py-2">
                        <i class="bi bi-x-circle"></i> {{ $withdrawal->status_format }}
                      </span>
                      @break
                    @default
                      <span class="badge bg-secondary fs-6 px-3 py-2">{{ $withdrawal->status_format }}</span>
                  @endswitch
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-12">
                <table class="table table-borderless withdrawal-detail-table mt-3">
                  <tbody>
                    <tr class="border-bottom">
                      <td class="label fw-semibold text-muted py-3" style="width: 200px;">{{ __('front/withdrawal.withdrawal_amount') }}</td>
                      <td class="value py-3">
                        <span class="fw-bold text-primary fs-5">{{ currency_format($withdrawal->amount) }}</span>
                      </td>
                    </tr>
                    <tr class="border-bottom">
                      <td class="label fw-semibold text-muted py-3">{{ __('front/withdrawal.account_type') }}</td>
                      <td class="value py-3">{{ $withdrawal->account_type_format }}</td>
                    </tr>
                    <tr class="border-bottom">
                      <td class="label fw-semibold text-muted py-3">{{ __('front/withdrawal.account_number') }}</td>
                      <td class="value py-3">
                        <code>{{ $withdrawal->account_number }}</code>
                      </td>
                    </tr>
                    @if($withdrawal->bank_name)
                    <tr class="border-bottom">
                      <td class="label fw-semibold text-muted py-3">{{ __('front/withdrawal.bank_name') }}</td>
                      <td class="value py-3">{{ $withdrawal->bank_name }}</td>
                    </tr>
                    @endif
                    @if($withdrawal->bank_account)
                    <tr class="border-bottom">
                      <td class="label fw-semibold text-muted py-3">{{ __('front/withdrawal.bank_account') }}</td>
                      <td class="value py-3">{{ $withdrawal->bank_account }}</td>
                    </tr>
                    @endif
                    <tr class="border-bottom">
                      <td class="label fw-semibold text-muted py-3">{{ __('front/withdrawal.created_at') }}</td>
                      <td class="value py-3">{{ $withdrawal->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    <tr class="border-bottom">
                      <td class="label fw-semibold text-muted py-3">{{ __('front/withdrawal.status') }}</td>
                      <td class="value py-3">{{ $withdrawal->status_format }}</td>
                    </tr>
                    @if($withdrawal->comment)
                    <tr class="border-bottom">
                      <td class="label fw-semibold text-muted py-3">{{ __('front/withdrawal.comment') }}</td>
                      <td class="value py-3">{{ $withdrawal->comment }}</td>
                    </tr>
                    @endif
                    @if($withdrawal->admin_comment)
                    <tr class="border-bottom">
                      <td class="label fw-semibold text-muted py-3">{{ __('front/withdrawal.admin_comment') }}</td>
                      <td class="value py-3">
                        <div class="alert alert-info mb-0">
                          <i class="bi bi-info-circle"></i>
                          {{ $withdrawal->admin_comment }}
                        </div>
                      </td>
                    </tr>
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection