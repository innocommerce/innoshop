@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.analytics_customer'))

@push('header')
  <script src="{{ asset('vendor/chart/chart.min.js') }}"></script>
@endpush

@section('content')

  <div class="row">
    <div class="col-12">
      <x-panel-chart-line id="customer" :labels="$customer_latest_week['period']" :title="__('panel/analytics.customer_trends')"
                          :data="$customer_latest_week['totals']"></x-panel-chart-line>
    </div>
  </div>

  <div class="row d-flex ">
    <div class="col-6">
      <x-panel-chart-pie id="customer_source" :labels="$customer_source['labels']" :title="__('panel/analytics.customer_source')"
                         :data="$customer_source['data']"></x-panel-chart-pie>
    </div>

   @hookinsert('panel.analytics.customer.bottom')

  </div>
@endsection

