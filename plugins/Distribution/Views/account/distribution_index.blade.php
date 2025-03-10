@extends('layouts.app')
@section('body-class', 'page-account')

@section('content')
  <x-front-breadcrumb type="route" value="account.distributions.index" title="{{ __('Distribution::common.my_distribution') }}"/>
  
  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="account-card-box account-info">
          @if (session('success'))
            <x-common-alert type="success" msg="{{ session('success') }}" class="mt-4"/>
          @endif
          @if (session('error'))
            <x-common-alert type="danger" msg="{{ session('error') }}" class="mt-4"/>
          @endif
          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('Distribution::common.my_distribution') }}</span>
          </div>
          <div class="account-data">
            <div class="row">
              <div class="col-6 col-md-3">
                <div class="account-item-data">
                  <a href="{{ account_route('distributions.members') }}">
                  <div class="value">{{ $member_total }}</div>
                  <div class="title text-secondary">{{ __('Distribution::common.recommend_customer') }}</div>
                  </a>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="account-item-data">
                  <a href="{{ account_route('distributions.commissions') }}">
                  <div class="value">{{ $commission_amount }}</div>
                  <div class="title text-secondary">{{ __('Distribution::common.commission_amount') }}</div>
                  </a>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="account-item-data">
                  <a href="{{ account_route('distributions.orders') }}">
                  <div class="value">{{ $order_total }}</div>
                  <div class="title text-secondary">{{ __('Distribution::common.recommend_order_number') }}</div>
                  </a>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="account-item-data">
                  <a href="{{ account_route('distributions.orders') }}">
                  <div class="value">{{ $order_amount }}</div>
                  <div class="title text-secondary">{{ __('Distribution::common.commission_total') }}</div>
                  </a>
                </div>
              </div>
            </div>
          </div>
          <div class="share-link-box">
            <div class="fw-bold my-2 fs-5">
              {{ __('Distribution::common.share_link') }}
            </div>
            <div class="fs-6 my-2"> 
              {{ __('Distribution::common.share_link_description', ['rate' => plugin_setting('distribution.rate')]) }}
            </div>
            <div class="mb-3">
              <div class="input-group">
                <input type="text" class="form-control" id="shareLink"
                       value="{{ $referral_link }}"
                       readonly>
                <button class="btn btn-primary btn-sm copy-btn" type="button" onclick="copyToClipboard()">
                  <i class="bi bi-clipboard me-1"></i> {{ __('Distribution::common.copy_link') }}
                </button>
              </div>
            </div>
            <div class="mb-2 fs-6">{{ __('Distribution::common.share_to_friends') }}</div>
          </div>

          <div class="mt-4">
            <div class="fw-bold fs-5 my-2">{{ __('Distribution::common.share_way') }}</div>
            <div class="fs-6 mb-2">{{ __('Distribution::common.share_to_sns') }}</div>
          </div>

          <div class="mt-4">
            <div class="fw-bold fs-5 my-2">{{ __('Distribution::common.transparent_earnings') }}</div>
            <div class="fs-6">{{ __('Distribution::common.come_to_share') }}</div>
            <div class="fs-6">{{ __('Distribution::common.share_to_friends') }}</div>
          </div>
        </div>
      </div>
    </div>
    @endsection

    @push('footer')
      <script>
        function copyToClipboard() {
          const shareLink = document.getElementById('shareLink');
          shareLink.select();
          document.execCommand('copy');
          layer.msg('{{ __('Distribution::common.link_copied') }}');
        }
      </script>
  @endpush