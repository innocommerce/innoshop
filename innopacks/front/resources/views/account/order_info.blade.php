@extends('layouts.app')
@section('body-class', 'page-order-info')

@section('content')
  <x-front-breadcrumb type="order" :value="$order"/>

  @hookinsert('account.order_info.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="account-card-box order-info-box">
          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/order.order_details') }}</span>
            <div class="d-flex align-items-center gap-2">
              <div>
                @if ($order->status == 'unpaid')
                  <a href="{{ front_route('orders.pay', ['number' => $order->number]) }}"
                     class="btn btn-primary btn-sm">{{ __('front/order.continue_pay') }}</a>
                  <button data-number="{{ $order->number }}"
                          class="btn btn-primary btn-sm btn-canceled">{{ __('front/account.cancel_order') }}</button>
                @elseif($order->status == 'completed')
                  <a href="{{ account_route('order_returns.create', ['order_number' => $order->number]) }}"
                     class="btn btn-primary btn-sm">{{ __('front/order.create_rma') }}</a>
                @elseif($order->status == 'shipped')
                  <button data-number="{{ $order->number }}"
                          class="btn btn-primary btn-sm btn-shipped">{{ __('front/account.signed') }}</button>
                @endif
              </div>
            </div>
          </div>
          <table class="table table-bordered table-striped mb-3 table-response">
            <thead>
            <tr>
              <th>{{ __('front/order.order_number') }}</th>
              <th>{{ __('front/order.order_date') }}</th>
              <th>{{ __('front/order.order_total') }}</th>
              <th>{{ __('front/order.order_status') }}</th>
            </tr>
            </thead>
            <tbody>
            <tr>
              <td data-title="Order ID">{{ $order->number }}</td>
              <td data-title="Order Date">{{ $order->created_at->format('Y-m-d') }}</td>
              <td data-title="Order Total">{{ $order->total_format }}</td>
              <td data-title="Order Status">{{ $order->status_format }}</td>
            </tr>
            </tbody>
          </table>

          <div class="products-table mb-4">
            <table class="table products-table align-middle">
              <thead>
              <tr>
                <th>{{ __('front/order.product') }}</th>
                <th>{{ __('front/order.operation') }}</th>
                <th>{{ __('front/order.price') }}</th>
                <th>{{ __('front/order.quantity') }}</th>
                <th>{{ __('front/order.subtotal') }}</th>
              </tr>
              </thead>
              <tbody>
              @foreach ($order_items as $product)
                <tr>
                  <td>
                    <div class="product-item">
                      <div class="product-image">
                        <img src="{{ $product['image'] }}" class="img-fluid">
                      </div>
                      <div class="product-info">
                        <div class="name" data-bs-toggle="tooltip" title="{{ $product['name'] }}">
                          {{ sub_string($product['name'], 64) }}
                        </div>
                        <div class="sku mt-2 text-secondary">{{ $product['product_sku'] }}
                          @if ($product['variant_label'])
                            - {{ $product['variant_label'] }}
                          @endif
                          @if ($product['item_type_label'])
                            <span class="badge bg-danger ms-2">{{ $product['item_type_label'] }}</span>
                          @endif
                        </div>
                        @if (!empty($product['options']))
                          <div class="product-options mt-2">
                            @foreach ($product['options'] as $option)
                              <div class="option-item text-muted small">
                                <strong>{{ $option['option_name'] }}:</strong> {{ $option['option_value_name'] }}
                                @if ($option['price_adjustment'] != 0)
                                  <span class="text-primary">({{ $option['price_adjustment'] > 0 ? '+' : '' }}{{ currency_format($option['price_adjustment']) }})</span>
                                @endif
                              </div>
                            @endforeach
                          </div>
                        @endif
                      </div>
                    </div>
                  </td>
                  <td>
                    @php($reviewed = \InnoShop\Common\Repositories\ReviewRepo::orderReviewed(current_customer_id(), $product['id']))
                    @if ($order->status == 'completed' && !$reviewed && $product['item_type'] === 'normal')
                      <button type="button" class="btn btn-sm btn-primary add_review" data-bs-toggle="modal"
                              data-bs-target="#addReview-Modal" data-name="{{ $product['name'] }}"
                              data-image="{{ $product['image'] }}" data-ordernumber="{{ $product['order_number'] }}"
                              data-label="{{ $product['variant_label'] }}" data-orderitemid="{{ $product['id'] }}"
                              data-productsku="{{ $product['product_sku'] }}">{{ __('front/order.add_review') }}
                      </button>
                    @endif
                  </td>
                  <td>{{ $product['price_format'] }}</td>
                  <td>{{ $product['quantity'] }}</td>
                  <td>{{ $product['subtotal_format'] }}</td>
                </tr>
              @endforeach

              @foreach ($order->fees as $total)
                <tr>
                  <td></td>
                  <td></td>
                  <td><strong>{{ $total['title'] }}</strong></td>
                  <td>{{ $total->value_format }}</td>
                </tr>
              @endforeach
              <tr>
                <td></td>
                <td></td>
                <td><strong>{{ __('front/order.order_total') }}</strong></td>
                <td>{{ $order->total_format }}</td>
              </tr>
              </tbody>
            </table>
          </div>

          <div class="row mb-4">
            <div class="col-12 col-md-6">
              <div class="address-card">
                <div class="address-card-header mb-3">
                  <h5 class="address-card-title border-bottom pb-3 fw-bold">{{ __('common/address.shipping_address') }}
                  </h5>
                </div>
                <div class="address-card-body">
                  <p>{{ __('common/address.name') }}: {{ $order->shipping_customer_name }}</p>
                  <p>{{ __('common/address.phone') }}: {{ $order->shipping_telephone }}</p>
                  <p>{{ __('common/address.zipcode') }}: {{ $order->shipping_zipcode }}</p>
                  <p>{{ __('common/address.address_1') }}: {{ $order->shipping_address_1 }}</p>
                  @if ($order->shipping_address_2)
                    <p>{{ __('common/address.address_2') }}: {{ $order->shipping_address_2 }}</p>
                  @endif
                  <p>{{ __('common/address.region') }}: {{ $order->shipping_city }}, {{ $order->shipping_state }}
                    , {{ $order->shipping_country }}</p>
                </div>
              </div>
            </div>
            <div class="col-12 col-md-6">
              <div class="address-card">
                <div class="address-card-header mb-3">
                  <h5 class="address-card-title border-bottom pb-3 fw-bold">{{ __('common/address.billing_address') }}
                  </h5>
                </div>
                <div class="address-card-body">
                  <p>{{ __('common/address.name') }}: {{ $order->billing_customer_name }}</p>
                  <p>{{ __('common/address.phone') }}: {{ $order->billing_telephone }}</p>
                  <p>{{ __('common/address.zipcode') }}: {{ $order->billing_zipcode }}</p>
                  <p>{{ __('common/address.address_1') }}: {{ $order->billing_address_1 }}</p>
                  @if ($order->billing_address_2)
                    <p>{{ __('common/address.address_2') }}: {{ $order->billing_address_2 }} </p>
                  @endif
                  <p>{{ __('common/address.region') }}: {{ $order->billing_city }}, {{ $order->billing_state }}
                    , {{ $order->billing_country }}</p>
                </div>
              </div>
            </div>
          </div>

          @if ($order->comment)
            <div class="account-card-sub-title d-flex justify-content-between align-items-center">
              <span class="fw-bold">{{ __('front/checkout.order_comment') }}</span>
            </div>
            <div class="mb-4">
              <span class="d-inline-block" tabindex="0">{{ $order->comment }}</span>
            </div>
          @endif
          <div class="account-card-sub-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/order.logistics_info') }}</span>
          </div>
          <div class="table-responsive">
            <table class="table table-response">
              <thead>
              <tr>
                <th>{{ __('front/order.express_code') }}</th>
                <th>{{ __('front/order.express_company') }}</th>
                <th>{{ __('front/order.express_number') }}</th>
                <th>{{ __('front/order.time') }}</th>
                <th>{{ __('front/order.shipment_info') }}</th>
              </tr>
              </thead>
              <tbody>
              @foreach ($order->shipments as $shipment)
                <tr class="align-middle">
                  <td data-title="express_code">{{ $shipment->express_code }}</td>
                  <td data-title="express_company">{{ $shipment->express_company }}</td>
                  <td data-title="express_number">{{ $shipment->express_number }}</td>
                  <td data-title="created_at">{{ $shipment->created_at }}</td>
                  <td class="align-middle">
                    <button data-id="{{ $shipment->id }}" type="button" class="btn btn-primary"
                            id="view-shipment-{{ $shipment->id }}">
                      {{ __('front/order.view') }}
                    </button>
                  </td>
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>
          <div class="account-card-sub-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/order.order_history') }}</span>
          </div>

          <div class="table-responsive">
            <table class="table table-response">
              <thead>
              <tr>
                <th>{{ __('front/order.order_status') }}</th>
                <th>{{ __('front/order.remark') }}</th>
                <th>{{ __('front/order.order_date') }}</th>
              </tr>
              </thead>
              <tbody>
              @foreach ($order->histories as $history)
                <tr>
                  <td data-title="State">{{ $history->status }}</td>
                  <td data-title="Remark">{{ $history->comment }}</td>
                  <td data-title="Update Time">{{ $history->created_at }}</td>
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade modal-lg" id="addReview-Modal" tabindex="-1" aria-labelledby="addReview-Modal-Label"
         aria-hidden="true">
      <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="addReview-Modal-Label">{{ __('front/order.add_review') }}</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          @include('shared.review')
        </div>
      </div>
    </div>
    <div class="modal fade" id="newShipmentModal" tabindex="-1" aria-labelledby="newShipmentModalLabel"
         aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="newShipmentModalLabel">{{ __('front/order.shipment_info') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <table class="table">
              <thead>
              <tr>
                <th class="col-3">{{ __('front/order.time') }}</th>
                <th class="col-9">{{ __('front/order.shipment_info') }}</th>
              </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary"
                    data-bs-dismiss="modal">{{ 'front/order.confirm' }}</button>
          </div>
        </div>
      </div>
    </div>
    @hookinsert('account.order_info.bottom')
    @endsection
    @push('footer')
      <script>
        $(document).ready(function () {
          const reviewModal = document.getElementById('addReview-Modal')
          reviewModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget
            const orderNumber = button.getAttribute('data-ordernumber')
            const productImage = button.getAttribute('data-image')
            const productName = button.getAttribute('data-name')
            const productLabel = button.getAttribute('data-label')
            const productItemId = button.getAttribute('data-orderitemid')
            const productSku = button.getAttribute('data-productsku')

            $('#order_number').text(orderNumber)
            $('#product-image').attr('src', productImage)
            $('#name').text(productName)
            $('#label').text(productLabel)
            $('input[name="order_number"]').val(orderNumber)
            $('input[name="order_item_id"]').val(productItemId)
            $('input[name="product_sku"]').val(productSku)
          })
          // View shipment details
          $(document).on('click', '[id^="view-shipment-"]', function () {
            const shipmentId = $(this).data('id')
            axios.get(`${urls.front_api}/panel/shipments/${shipmentId}/traces`)
              .then(response => {
                const traces = response.data.traces
                const tbody = $('#newShipmentModal .modal-body table tbody').last()
                tbody.empty()

                traces.forEach(trace => {
                  tbody.append(`
                <tr>
                  <td>${trace.time}</td>
                  <td>${trace.station}</td>
                </tr>
              `)
                })
                $('#newShipmentModal').modal('show')
              })
          })
          // Mark order as shipped
          $(document).on('click', '.btn-shipped', function () {
            const orderNumber = $(this).data('number')
            axios.post(`${urls.front_api}/orders/${orderNumber}/complete`, {
              number: orderNumber
            })
              .then(() => {
                inno.msg(__('front/account.signed_success'))
                window.location.reload()
              })
              .catch(() => inno.msg(__('front/account.signed_failed')))
          })
          // Cancel order
          $(document).on('click', '.btn-canceled', function () {
            const orderNumber = $(this).data('number')
            layer.confirm('{{ __('front/account.cancel_order_confirm') }}', {
              icon: 5,
              title: '{{ __('front/account.tip') }}',
              btn: [
                '{{ __('front/account.cancel_order_confirm_title') }}',
                '{{ __('front/account.cancel_order_confirm_btn_close') }}'
              ],
              offset: 'auto',
              area: ['400px', 'auto'],
              shade: [0.3, "#fff"]
            }, function (index) {
              layer.close(index)
              layer.load(2, {
                shade: [0.3, "#fff"]
              })

              axios.post(`${urls.front_api}/orders/${orderNumber}/cancel`, {
                number: orderNumber
              })
                .then(() => {
                  inno.msg("{{ __('front/account.cancel_order_success') }}")
                  window.location.reload()
                })
                .catch(() => inno.msg("{{ __('front/account.cancel_order_failed') }}"))
                .finally(() => layer.closeAll('loading'))
            })
          })
        })
      </script>
  @endpush
