@extends('layouts.app')
@section('body-class', 'page-order')

@section('content')
  <x-front-breadcrumb type="route" value="account.orders.index" title="{{ __('front/account.orders') }}"/>

  @hookinsert('account.order_index.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="account-card-box order-box">
          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/order.order') }}</span>
          </div>

          <ul class="nav nav-tabs tabs-plus">
            <li class="nav-item">
              <a class="nav-link {{ request('status') == '' ? 'active' : '' }}"
                 href="{{ account_route('orders.index') }}">{{ __('front/order.all') }}</a>
            </li>
            @foreach ($filter_statuses as $status)
              <li class="nav-item">
                <a class="nav-link {{ request('status') == $status ? 'active' : '' }}"
                   href="{{ account_route('orders.index', ['status' => $status]) }}">
                  {{ __('front/order.' . $status) }}</a>
              </li>
            @endforeach
          </ul>

          @if ($orders->count())
            <table class="table align-middle account-table-box table-response">
              <thead>
              <tr>
                <th>{{ __('front/order.order_number') }}</th>
                <th>{{ __('front/order.order_items') }}</th>
                <th>{{ __('front/order.order_date') }}</th>
                <th>{{ __('front/order.order_status') }}</th>
                <th>{{ __('front/order.order_total') }}</th>
                <th>{{ __('front/common.action') }}</th>
              </tr>
              </thead>
              <tbody>
              @foreach ($orders as $order)
                <tr>
                  <td data-title="Order ID">
                    @if ($order->children->count())
                      <a class="btn btn-link btn-sm p-0 me-2" data-bs-toggle="collapse"
                         href="#collapse{{ $order->id }}" role="button">
                        <i class="bi bi-chevron-down"></i>
                      </a>
                    @endif
                    {{ $order->number }}
                  </td>
                  <td data-title="Order Items">
                    <div class="d-flex">
                      @foreach ($order->items->take(5) as $product)
                        <div class="wh-30 overflow-hidden border border-1 me-1">
                          <img src="{{ image_resize($product->image, 30, 30) }}" alt="{{ $product->name }}"
                               class="img-fluid">
                        </div>
                      @endforeach
                    </div>
                  </td>
                  <td data-title="Date">{{ $order->created_at->format('Y-m-d') }}</td>
                  <td data-title="Date"><span
                      class="badge bg-{{ $order->status_color }} ">{{ $order->status_format }}</span></td>
                  <td data-title="Total">{{ $order->total_format }}</td>
                  <td data-title="Actions">
                    <a href="{{ account_route('orders.number_show', $order->number) }}" class="btn btn-primary btn-sm"
                       role="button">{{ __('front/common.view') }}</a>
                    @if ($order->status == 'shipped')
                      <button data-number="{{ $order->number }}"
                              class="btn btn-primary btn-sm btn-shipped">{{ __('front/account.signed') }}</button>
                    @endif
                  </td>
                </tr>

                @if ($order->children->count())
                  <tr class="p-0">
                    <td colspan="6" class="p-0 border-bottom-0">
                      <div class="collapse" id="collapse{{ $order->id }}">
                        <div class="tab ps-5">
                          <table class="table table-sm mb-0">
                            <thead>
                            <tr>
                              <th>{{ __('front/order.order_number') }}</th>
                              <th>{{ __('front/order.order_items') }}</th>
                              <th>{{ __('front/order.order_date') }}</th>
                              <th>{{ __('front/order.order_status') }}</th>
                              <th>{{ __('front/order.order_total') }}</th>
                              <th>{{ __('front/common.action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($order->children as $child)
                              <tr>
                                <td>{{ $child->number }}</td>
                                <td>
                                  <div class="d-flex">
                                    @foreach ($child->items->take(5) as $product)
                                      <div class="wh-30 overflow-hidden border border-1 me-1">
                                        <img src="{{ image_resize($product->image, 30, 30) }}"
                                             alt="{{ $product->name }}"
                                             class="img-fluid">
                                      </div>
                                    @endforeach
                                  </div>
                                </td>
                                <td>{{ $child->created_at->format('Y-m-d') }}</td>
                                <td>
                                  <span class="badge bg-{{ $order->status_color }} ">{{ $order->status_format }}</span>
                                </td>
                                <td>{{ $child->total_format }}</td>
                                <td>
                                  <a href="{{ account_route('orders.number_show', $child->number) }}"
                                     class="btn btn-primary btn-sm" role="button">{{ __('front/common.view') }}</a>
                                  @if ($child->status == 'shipped')
                                    <button data-number="{{ $child->number }}"
                                            class="btn btn-primary btn-sm btn-shipped">{{ __('front/account.signed') }}</button>
                                  @endif
                                </td>
                              </tr>
                            @endforeach
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </td>
                  </tr>
                @endif
              @endforeach
              </tbody>
            </table>

            {{ $orders->links('panel::vendor/pagination/bootstrap-4') }}
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
    $(document).ready(function () {
      $('.btn-shipped').click(function () {
        var button = $(this);
        var number = $(this).data('number');

        axios.post(`${urls.api_base}/orders/${number}/complete`, {
          number: number
        }).then(function (response) {
          inno.msg(__('front/account.signed_success'));
          button.fadeOut(300, function () {
            $(this).remove();
          });
          window.location.reload();
        }).catch(function (error) {
          inno.msg(__('front/account.signed_failed'));
        });
      });
    });
  </script>
@endpush
