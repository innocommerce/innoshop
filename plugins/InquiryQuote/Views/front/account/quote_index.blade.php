@extends('layouts.app')
@section('body-class', 'page-order')

@section('content')
  <x-front-breadcrumb type="route" value="account.quotes.index" title="{{ __('InquiryQuote::quote.quotes') }}"/>

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="account-card-box order-box">
          @if (session('success'))
            <x-common-alert type="success" msg="{{ session('success') }}" class="mt-4"/>
          @endif
          @if (session('error'))
            <x-common-alert type="danger" msg="{{ session('error') }}" class="mt-4"/>
          @endif

          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('InquiryQuote::quote.quotes') }}</span>
          </div>

          <ul class="nav nav-tabs tabs-plus">
            <li class="nav-item">
              <a class="nav-link {{ request('status') == '' ? 'active' : '' }}"
                 href="{{ account_route('quotes.index') }}">{{ __('front/order.all') }}</a>
            </li>
            @foreach (\Plugin\InquiryQuote\Services\StateService::getAllStatuses() as $status)
              <li class="nav-item">
                <a class="nav-link {{ request('status') == $status['status'] ? 'active' : '' }}"
                   href="{{ account_route('quotes.index', ['status' => $status['status']]) }}">
                  {{ $status['name'] }}</a>
              </li>
            @endforeach
          </ul>

          @if ($quotes->count())
            <table class="table align-middle account-table-box table-response">
              <thead>
              <tr>
                <th>{{ __('InquiryQuote::quote.number') }}</th>
                <th>{{ __('front/order.order_items') }}</th>
                <th>{{ __('front/order.order_date') }}</th>
                <th>{{ __('front/order.order_status') }}</th>
                <th>{{ __('front/order.order_total') }}</th>
                <th>{{ __('front/common.action') }}</th>
              </tr>
              </thead>
              <tbody>
              @foreach ($quotes as $quote)
                <tr>
                  <td data-title="Order ID">{{ $quote->number }}</td>
                  <td data-title="Order Items">
                    <div class="d-flex">
                      @foreach ($quote->items->take(5) as $item)
                        <div class="wh-30 overflow-hidden border border-1 me-1">
                          <img src="{{ image_resize($item->product->image_url ?? '') }}"
                               alt="{{ $item->product->name }}" class="img-fluid">
                        </div>
                      @endforeach
                    </div>
                  </td>
                  <td data-title="Date">{{ $quote->created_at->format('Y-m-d') }}</td>
                  <td data-title="Status">
                    <span class="badge {{ $quote->status == 'completed' || $quote->status == 'paid' ? 'bg-success' : 'bg-warning' }} ">
                      {{$quote->status_format }}
                    </span>
                  </td>
                  <td data-title="Total">{{ $quote->total_format }}</td>
                  <td data-title="Actions">
                    <a href="{{ account_route('quotes.number_show', $quote->number) }}" class="btn btn-primary">{{ __('front/common.view') }}</a>
                    @if($quote->id != $quote->parent_id && $quote->status == \Plugin\InquiryQuote\Services\StateService::ADMIN_UPDATED)
                      <a href="{{ account_route('quotes.confirm', $quote->number) }}" class="btn btn-primary">下单</a>
                    @endif

                    @if ($quote->order_id)
                      <a href="{{ account_route('orders.number_show', $quote->order->number) }}" class="btn btn-primary">订单</a>
                    @endif
                  </td>
                </tr>
              @endforeach
              </tbody>
            </table>
            {{ $quotes->links('panel::vendor/pagination/bootstrap-4') }}
          @else
            <x-common-no-data/>
          @endif
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.order_index.bottom')

@endsection

@push('footer')
  <script>
  </script>
@endpush