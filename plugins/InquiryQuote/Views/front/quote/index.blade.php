@extends('layouts.app')
@section('body-class', 'page-checkout')

@push('header')
  <script src="{{ asset('vendor/vue/3.5/vue.global' . (!config('app.debug') ? '.prod' : '') . '.js') }}"></script>
  <link rel="stylesheet" href="{{ plugin_asset('InquiryQuote', 'css/quote.css') }}">
@endpush

@section('content')

  <x-front-breadcrumb type="route" value="quotes.current" title="Quote" />

  @hookinsert('cart.top')

  <div class="container">
    @if (session()->has('errors'))
      <x-common-alert type="danger" msg="{{ session('errors')->first() }}" class="mt-4" />
    @endif
    @if (session('success'))
      <x-common-alert type="success" msg="{{ session('success') }}" class="mt-4" />
    @endif
    @if (isset($inquiry_list) && count($inquiry_list))
      <div class="row mt-4">
        <div class="col-12 col-md-9">
          <table class="table products-table align-middle">
            <thead>
              <tr>
                <th scope="col">产品</th>
                <th scope="col"></th>
                <th scope="col">产品原价</th>
                <th scope="col">期望价格</th>
                <th scope="col">数量</th>
                <th scope="col">总计</th>
                <th scope="col"></th>
              </tr>
            </thead>
            <tbody>
              @foreach ($inquiry_list as $item)
                <tr>
                  <td colspan="5">{{ $item['seller']['name'] ?? '自营' }}</td>
                </tr>
                @foreach ($item['inquiries'] as $product)
                  <tr data-id="{{ $product['id'] }}" data-sku-code="{{ $product['sku_code'] }}"
                    data-inquiry-price="{{ $product['inquiry_price'] }}">
                    <td class="td-image">
                      <div class="product-image"><img src="{{ $product['image'] }}" class="img-fluid"></div>
                    </td>
                    <td class="td-product-info">
                      <div class="product-item">
                        <div class="product-info">
                          <div class="name">
                            {{ $product['product_name'] }}
                            <div class="text-secondary mt-1">
                              {{ $product['sku_code'] }}
                              @if ($product['variant_label'])
                                - {{ $product['variant_label'] }}
                              @endif
                            </div>
                          </div>
                          <div class="mb-price mt-1">{{ $product['inquiry_price_format'] }}</div>
                          <div class="quantity-wrap mt-1 d-lg-none">
                            <div class="minus"><i class="bi bi-dash-lg"></i></div>
                            <input type="number" class="form-control" value="{{ $product['quantity'] ?? 1 }}">
                            <div class="plus"><i class="bi bi-plus-lg"></i></div>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="td-price">{{ $product['origin_price'] }}</td>
                    <td class="td-quantity d-none d-lg-table-cell">
                      <div class="quantity-wrap price-view">
                        <input type="number" class="form-control price " value="{{ $product['inquiry_price'] ?? 0 }}">
                      </div>
                    </td>
                    <td class="td-quantity d-none d-lg-table-cell">
                      <div class="quantity-wrap number-view">
                        <div class="minus"><i class="bi bi-dash-lg"></i></div>
                        <input type="number" class="form-control number" value="{{ $product['quantity'] ?? 1 }}">
                        <div class="plus"><i class="bi bi-plus-lg"></i></div>
                      </div>
                    </td>
                    <td class="td-subtotal">{{ $product['inquiry_subtotal_format'] }}</td>
                    <td class="td-delete">
                      <div class="delete-cart text-danger fs-5 cursor-pointer"><i class="bi bi-x-circle-fill"></i>
                      </div>
                    </td>
                  </tr>
                @endforeach
              @endforeach
            </tbody>
          </table>
          <div class="container checkout-container h-min-600">
            <div class="row" id="app-checkout" v-cloak>
              <div class="col-12 col-md-7">
                <div class="checkout-info">
                  <div class="address-box">
                    <div class="checkout-item" v-if="!source.addressEdit">
                      <div class="addresses-wrap">
                        <div class="shipping-address">
                          <div class="title-wrap">
                            <div class="title">
                              {{ __('front/checkout.shipping_address') }}
                            </div>
                            <div>
                              <span class="cursor-pointer" v-if="!source.addressEdit" @click="addressEdit(true)"><i
                                  class="bi bi-plus-lg"></i>{{ __('front/checkout.create_address') }}</span>
                            </div>
                          </div>
                          <div class="checkout-select-wrap address-select"
                            v-if="source.addresses.length && !source.addressEdit">
                            <div :class="['select-item', current.shipping_address_id == address.id ? 'active' : '']"
                              v-for="address, index in source.addresses" :key="address.id"
                              @click="updateCheckout('shipping_address_id', address.id)">
                              <div class="left">
                                <i class="bi bi-circle"></i>
                                <div class="select-title">
                                  <div class="address-name mb-1">@{{ address.name }} @{{ address.phone }}
                                    @{{ address.zipcode }}
                                  </div>
                                  <div class="address-info">@{{ address.address_1 }} @{{ address.address_2 }}
                                    @{{ address.city }} @{{ address.state }} @{{ address.country_name }}
                                  </div>
                                </div>
                              </div>
                              <div class="edit-address text-decoration-underline text-secondary"
                                @click.stop="editAddress(index)"> {{ __('front/common.edit') }}
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div v-show="source.addressEdit">
                      <div class="checkout-item">
                        <div class="title-wrap">
                          <div class="title">{{ __('front/checkout.create_address') }}</div>
                          @if (!current_customer())
                            <span class="cursor-pointer btn btn-sm btn-outline-primary" @click="login"><i
                                class="bi bi-box-arrow-in-right"></i> {{ __('front/common.login') }}</span>
                          @endif
                          <span class="cursor-pointer" v-if="source.addresses.length" @click="addressEdit(false)"><i
                              class="bi bi-plus-lg"></i> {{ __('front/checkout.cancel_create') }}</span>
                        </div>
                        @include('shared.address-form')
                      </div>
                    </div>
                  </div>
                  <div class="checkout-item">
                    <div class="title-wrap">
                      <div class="title">{{ __('front/checkout.shipping_methods') }}</div>
                    </div>
                    <div class="checkout-select-wrap">
                      <div v-for="item in source.shippingMethods" :key="item.code">
                        <div v-for="quote in item.quotes" :key="quote.code"
                          @click="updateCheckout('shipping_method_code', quote.code, quote.cost_format)"
                          :class="['select-item', current.shipping_method_code == quote.code ? 'active' : '']">
                          <div class="left">
                            <i class="bi bi-circle"></i>
                            <div class="select-title">
                              <span class="name"> @{{ quote.name }}</span> &nbsp;&nbsp;
                              <span class="cost"> @{{ quote.cost_format }}</span>
                            </div>
                          </div>
                          <div class="icon"><img :src="quote.icon" class="img-fluid"></div>
                        </div>
                      </div>
                      <div v-if="!source.shippingMethods.length" class="alert alert-warning">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ __('front/checkout.no_shipping_methods') }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="checkout-item col-12 col-md-7">
              <div class="title-wrap">
                <div class="title">{{ __('front/checkout.order_comment') }}</div>
              </div>
              <div>
                <div class="checkout-select">
                  <textarea class="form-control" id="orderComment" rows="4"
                    placeholder="{{ __('front/checkout.order_comment') }}"></textarea>
                </div>
                <div class="mt-3 d-flex justify-content-end">
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-3">
          <div class="cart-data">
            <div class="title">Quote Totals 询盘总计</div>
            <ul class="cart-data-list">
              <li>
                <span>Subtotal </span><span class="total-amount" id="subtotal">{{ $subtotal_format }}</span>
              </li>
              <ul class="cart-data-list">
                @foreach ($quote_fees as $item)
                  <li class="d-flex justify-content-between align-items-center">
                    @if ($item['code'] !== 'subtotal' && $item['code'] !== 'shipping')
                      <span>{{ $item['label'] }}({{ $item['origin_amount_format'] }})</span>
                      <span class="total-amount d-flex justify-content-end align-items-center col-lg-6">
                        <input id="{{ $item['code'] }}" type="text" class="form-control fee-input"
                          value="{{ $item['inquiry_amount'] }}">
                      </span>
                    @endif
                    @if ($item['code'] === 'shipping')
                      <span>{{ $item['label'] }}($<span id="shipping-item-cost-span"></span>)</span>
                      <span class="total-amount d-flex justify-content-end align-items-center col-lg-6">
                        <input id="shipping-item-cost-input" class="form-control fee-input"
                          value="{{ $item['inquiry_amount'] }}">
                    @endif
                  </li>
                @endforeach
              </ul>
              <li class="d-flex justify-content-between">
                @if (plugin_setting('inquiry_quote', 'based_seller'))
                  <button type="button" data-customer_id="{{ $quote['customer_id'] }}"
                    data-quote-id="{{ $quote['id'] }}" data-quote-based="seller"
                    class="btn btn-primary send-quote">向商家询盘
                  </button>
                @endif
                @if (plugin_setting('inquiry_quote', 'based_salesman'))
                  <button type="button" data-customer_id="{{ $quote['customer_id'] }}"
                    data-quote-id="{{ $quote['id'] }}" data-quote-based="salesman"
                    class="btn btn-primary send-quote">向业务员询盘
                  </button>
                @endif
              </li>
            </ul>
          </div>
        </div>
      </div>
    @else
      <div class="text-center pm-5">
        <img src="{{ asset('icon/empty-cart.svg') }}" class="img-fluid w-max-300 mb-5">
        <h2>{{ __('front/cart.empty_cart') }}</h2>
        <a class="btn btn-primary btn-lg mt-3"
          href="{{ front_route('home.index') }}">{{ __('front/cart.continue') }}</a>
      </div>
    @endif
  </div>
  @hookinsert('cart.bottom')

@endsection

@push('footer')
  <script>
    $(document).ready(function() {
      $('#shipping-item-cost-span').text('0.00');
      $('.number-view input').on('change', function() {
        const newValue = $(this).val();
        if (!isNaN(newValue) && newValue > 0) {
          updateCarts($(this).closest('tr').data('id'), newValue);
        } else {
          inno.msg('Please enter a valid quantity greater than 0.');
        }
      });
    });

    $('.quantity-wrap .plus, .quantity-wrap .minus').on('click', function() {
      const quantity = parseInt($(this).siblings('input').val());
      if ($(this).hasClass('plus')) {
        $(this).siblings('input').val(quantity + 1);
      } else {
        if (quantity > 1) {
          $(this).siblings('input').val(quantity - 1);
        }
      }
    });

    $('.delete-cart').on('click', function() {
      var id = $(this).closest('tr').data('id');
      axios['delete'](`${urls.api_base}/inquiries/${id}`).then(function(res) {
        if (res.success) {
          inno.msg(res.message)
          $(`tr[data-id=${id}]`).remove();
          window.location.reload();
        }
      })
    });

    $('.btn.send-quote').on('click', function() {
      const quoteId = $(this).data('quote-id');
      const handling = $('#handling').val();
      const based = $(this).data('quote-based');
      const tax = $('#tax').val();
      const comment = $('#orderComment').val();
      const shipping = $('#shipping-item-cost-input').val();
      const fees = {
        handling,
        tax,
        shipping
      };
      const inquiries = [];
      $('.products-table tbody tr').each(function() {
        const row = $(this);
        const sku_code = row.data('sku-code');
        const quantity = row.find('.number-view .number').val();
        const inquiry_price = row.find('.price-view .price').val();
        if (!isNaN(quantity) && !isNaN(inquiry_price)) {
          inquiries.push({
            sku_code,
            quantity: parseFloat(quantity),
            inquiry_price: parseFloat(inquiry_price)
          });
        }
      });
      const shipping_address_id = addressApp.current.shipping_address_id;
      const shipping_method_code = addressApp.current.shipping_method_code;
      axios.put(`${urls.api_base}/quotes/${quoteId}`, {
        comment,
        inquiries,
        fees,
        shipping_address_id,
        shipping_method_code,
        based
      }).then(function(res) {
        inno.msg(res.message);
        window.location.reload();
      }).catch(function(err) {
        //layer.msg(err.response ? err.response.data.message : 'An error occurred.', {icon: 2});
      });
    });

    const {
      createApp,
      ref,
      reactive,
      onMounted,
      computed
    } = Vue
    const api = {
      address: @json(front_route('addresses.store')),
      checkout: @json(front_route('checkout.index')),
      checkoutConfirm: @json(front_route('checkout.confirm')),
    }

    function updateAddress(params) {
      addressApp.updateAddress(params)
    }

    const addressApp = createApp({
      setup() {
        const source = reactive({
          addresses: @json($address_list),
          shippingMethods: @json($shipping_methods),
          total: [],
          addressEdit: false,

        })

        const current = reactive({
          shipping_address_id: @json($checkout['shipping_address_id'] ?? 0),
          billing_address_id: @json($checkout['billing_address_id'] ?? 0),
          shipping_method_code: @json($checkout['shipping_method_code'] ?? ''),
          billing_method_code: @json($checkout['billing_method_code'] ?? ''),
          comment: '',
        })

        const isCheckout = computed(() => {
          return !current.shipping_address_id || !current.billing_address_id || !current.shipping_method_code ||
            !current.billing_method_code
        })

        editAddress = (index) => {
          source.addressEdit = true
          const address = source.addresses[index]

          getZones(address.country_code, function() {
            $('.address-form').find('input, select').each(function() {
              $(this).val(address[$(this).attr('name')])
            })
          })
        }

        const updateAddress = (params) => {
          const id = new URLSearchParams(params).get('id');
          const url = id ? api.address + '/' + id : api.address
          const method = id ? 'put' : 'post'
          axios[method](url, params).then(function(res) {
            if (res.success) {
              inno.msg(res.message)
              if (id) {
                const index = source.addresses.findIndex(address => address.id == id)
                source.addresses[index] = res.data
              } else {
                source.addresses.push(res.data)

                if (source.addresses.length == 1) {
                  current.shipping_address_id = res.data.id
                  current.billing_address_id = res.data.id
                  updateCheckout('shipping_address_id', res.data.id)
                }
              }

              source.addressEdit = false
              clearForm()
            }
          })
        }

        const addressEdit = (status) => {
          source.addressEdit = status
          clearForm()
        }

        const updateCheckout = (key, value, costFormat) => {
          current[key] = value;
          if (source.same_as_shipping_address && key == 'shipping_address_id') {
            current.billing_address_id = value;
          }
          if (key === 'shipping_method_code') {
            const formattedCost = costFormat.replace('$', '');
            document.getElementById('shipping-item-cost-span').textContent = formattedCost;
            document.getElementById('shipping-item-cost-input').value = formattedCost;
          }
        }

        const submitCheckout = () => {
          layer.load(2, {
            shade: [0.3, '#fff']
          })
          axios.post(api.checkoutConfirm, current).then(function(res) {
            if (res.success) {
              layer.msg(res.message, {
                time: 1000
              }, function() {
                location.href = inno.getBase() + '/orders/' + res.data.number + '/pay'
              })
            }
          }).finally(function() {
            layer.closeAll('loading')
          });
        }

        const login = () => {
          inno.openLogin()
        }

        return {
          source,
          login,
          current,
          editAddress,
          updateCheckout,
          addressEdit,
          isCheckout,
          updateAddress,
          submitCheckout,
        }
      }
    }).mount('#app-checkout')

    function updateAddress(params) {
      addressApp.updateAddress(params)
    }
  </script>
@endpush
