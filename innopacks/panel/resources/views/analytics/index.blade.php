@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel::menu.analytics'))

@push('header')
  <script src="{{ asset('vendor/chart/chart.min.js') }}"></script>
@endpush

@section('content')

  <div class="row">
    <div class="col-12">

      <x-panel-chart-line id="order" :labels="$order_latest_week['period']" :title="__('panel::dashboard.order_trends')"
                          :data="$order_latest_week['totals']"></x-panel-chart-line>

      <x-panel-chart-line id="product" :labels="$product_latest_week['period']" :title="__('panel::analytics.product_trends')"
                          :data="$product_latest_week['totals']"></x-panel-chart-line>

      <x-panel-chart-line id="customer" :labels="$customer_latest_week['period']" :title="__('panel::analytics.customer_trends')"
                          :data="$customer_latest_week['totals']"></x-panel-chart-line>
    </div>
  </div>
@endsection
