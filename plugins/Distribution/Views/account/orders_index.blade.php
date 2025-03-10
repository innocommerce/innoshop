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
        <span class="fw-bold">{{ __('Distribution::common.recommend_order_number') }}</span>
      </div>
      @if ($orders->count())
      <div class="order-info-body">
        <table class="table">
          <thead>
            <tr>
              <th>{{ __('Distribution::common.order_number') }}</th>
              <th>{{ __('Distribution::common.email') }}</th>
              <th>{{ __('Distribution::common.total_amount') }}</th>
              <th>{{ __('Distribution::common.status') }}</th>
              <th>{{ __('Distribution::common.created_at') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($orders as $order)
            <tr>
              <td>{{ $order->number }}</td>
              <td>{{ $order->email }}</td>
              <td>{{ $order->total_format }}</td>
              <td>{{ $order->status_format }}</td>
              <td>{{ $order->created_at }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>

        <div class="mt-4">
          {{ $orders->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
        </div>
      </div>
      @else
      <x-common-no-data />
      @endif
      </div>
      </div>
    </div>
  </div>
  </div>
  </div>
  @endsection
  @push('footer')
  <script>
  </script>
  @endpush