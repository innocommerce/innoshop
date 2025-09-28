@extends('layouts.app')
@section('body-class', 'page-wallet')

@section('content')
  <x-front-breadcrumb type="route" value="account.wallet.withdrawals.create" title="{{ __('front/withdrawal.apply_withdrawal') }}"/>

  @hookinsert('account.withdrawals_create.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="withdrawal-create-box">
          <div class="withdrawal-card-title">
            <span class="fw-bold">{{ __('front/withdrawal.apply_withdrawal') }}</span>
          </div>

          @if (session('success'))
            <x-common-alert type="success" msg="{{ session('success') }}" class="mt-3"/>
          @endif
          @if (session('error'))
            <x-common-alert type="danger" msg="{{ session('error') }}" class="mt-3"/>
          @endif

          <!-- Balance information -->
          <div class="wallet-balance-overview">
            <div class="balance-header">
              <i class="bi bi-wallet2"></i>
              <span>{{ __('front/withdrawal.wallet_balance') }}</span>
            </div>
            <div class="balance-content">
              <div class="balance-main">
                <div class="available-balance">
                  <div class="amount">{{ currency_format($available_balance) }}</div>
                  <div class="label">{{ __('front/withdrawal.available_balance') }}</div>
                </div>
              </div>
              <div class="balance-note">
                <i class="bi bi-info-circle"></i>
                <span>{{ __('front/withdrawal.balance_note') }}</span>
              </div>
            </div>
          </div>

          @if($has_pending_withdrawal)
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle"></i>
              {{ __('front/withdrawal.has_pending_withdrawal') }}
            </div>
          @else
            <form action="{{ account_route('wallet.withdrawals.store') }}" method="POST" class="withdrawal-form">
              @csrf
              
              <div class="row">
                <div class="col-12 col-md-6">
                  <div class="mb-3">
                    <label for="amount" class="form-label required">{{ __('front/withdrawal.withdrawal_amount') }}</label>
                    <div class="input-group">
                      <input type="number" 
                             class="form-control @error('amount') is-invalid @enderror" 
                             id="amount" 
                             name="amount" 
                             step="0.01" 
                             min="0.01" 
                             max="{{ $available_balance }}"
                             value="{{ old('amount') }}" 
                             placeholder="{{ __('front/withdrawal.withdrawal_amount') }}" 
                             required>
                      <span class="input-group-text">{{ current_currency_code() }}</span>
                    </div>
                    @error('amount')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">{{ __('front/withdrawal.available_balance') }}: {{ currency_format($available_balance) }}</div>
                  </div>
                </div>

                <div class="col-12 col-md-6">
                  <div class="mb-3">
                    <label for="account_type" class="form-label required">{{ __('front/withdrawal.account_type') }}</label>
                    <select class="form-select @error('account_type') is-invalid @enderror" 
                            id="account_type" 
                            name="account_type" 
                            required>
                      <option value="">{{ __('front/common.please_choose') }}</option>
                      @foreach($account_types as $type)
                        <option value="{{ $type['value'] }}" {{ old('account_type') == $type['value'] ? 'selected' : '' }}>
                          {{ $type['label'] }}
                        </option>
                      @endforeach
                    </select>
                    @error('account_type')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-12 col-md-6">
                  <div class="mb-3">
                    <label for="account_number" class="form-label required">{{ __('front/withdrawal.account_number') }}</label>
                    <input type="text" 
                           class="form-control @error('account_number') is-invalid @enderror" 
                           id="account_number" 
                           name="account_number" 
                           value="{{ old('account_number') }}" 
                           placeholder="{{ __('front/withdrawal.account_number') }}" 
                           required>
                    @error('account_number')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>

                <div class="col-12 col-md-6">
                  <div class="mb-3 bank-info" style="display: none;">
                    <label for="bank_name" class="form-label">{{ __('front/withdrawal.bank_name') }}</label>
                    <input type="text" 
                           class="form-control @error('bank_name') is-invalid @enderror" 
                           id="bank_name" 
                           name="bank_name" 
                           value="{{ old('bank_name') }}" 
                           placeholder="{{ __('front/withdrawal.bank_name') }}">
                    @error('bank_name')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-12">
                  <div class="mb-3 bank-info" style="display: none;">
                    <label for="bank_account" class="form-label">{{ __('front/withdrawal.bank_account') }}</label>
                    <input type="text" 
                           class="form-control @error('bank_account') is-invalid @enderror" 
                           id="bank_account" 
                           name="bank_account" 
                           value="{{ old('bank_account') }}" 
                           placeholder="{{ __('front/withdrawal.bank_account') }}">
                    @error('bank_account')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-12">
                  <div class="mb-3">
                    <label for="comment" class="form-label">{{ __('front/withdrawal.comment') }}</label>
                    <textarea class="form-control @error('comment') is-invalid @enderror" 
                              id="comment" 
                              name="comment" 
                              rows="3" 
                              placeholder="{{ __('front/withdrawal.comment') }}">{{ old('comment') }}</textarea>
                    @error('comment')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>

              <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-check-circle"></i> {{ __('front/withdrawal.submit_application') }}
                </button>
                <a href="{{ account_route('wallet.withdrawals.index') }}" class="btn btn-secondary ms-2">
                  {{ __('front/common.cancel') }}
                </a>
              </div>
            </form>
          @endif
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.withdrawals_create.bottom')

@endsection



@push('footer')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const accountTypeSelect = document.getElementById('account_type');
  const bankInfoElements = document.querySelectorAll('.bank-info');
  
  function toggleBankInfo() {
    const isBank = accountTypeSelect.value === 'bank';
    bankInfoElements.forEach(element => {
      element.style.display = isBank ? 'block' : 'none';
    });
  }
  
  accountTypeSelect.addEventListener('change', toggleBankInfo);
  
  // Initialize display state
  toggleBankInfo();
});
</script>
@endpush