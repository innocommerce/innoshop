@extends('layouts.app')
@section('body-class', 'page-account')

@section('content')

<x-front-breadcrumb type="route" value="account.distributions.index" title="{{ __('Distribution::common.my_distribution') }}" />
  
  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="account-card-box account-info"> 
          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('Distribution::common.commission_amount') }}</span>
          </div>
          @if ($commissions->count())
          <div class="order-info-body">
            <table class="table">
              <thead>
                <tr>
                  <th>{{ __('Distribution::common.order_number') }}</th>
                  <th>{{ __('Distribution::common.commission_amount') }}</th>
                  <th>{{ __('Distribution::common.recommend_customer') }}</th>
                  <th>{{ __('Distribution::common.status') }}</th>
                  <th>{{ __('Distribution::common.created_at') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($commissions as $commission)
                <tr>
                  <td>{{ $commission->order->number }}</td>
                  <td>{{ currency_format($commission->commission_amount) }}</td>
                  <td>{{ $commission->customer->name}}</td>
                  <td>{{ $commission->status}}</td>
                  <td>{{ $commission->created_at }}</td>
                  <td></td>
                </tr>
                @endforeach
              </tbody>
            </table>
              <div class="mt-4">
                {{ $commissions->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
              </div>
            </div>
          </div>
          @else
          <x-common-no-data />
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection

@push('footer')
  <script>
  </script>
@endpush