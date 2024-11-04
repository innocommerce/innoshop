@extends('layouts.app')
@section('body-class', 'page-cart')

@section('content')
<x-front-breadcrumb type="route" value="carts.index" title="{{ __('front/cart.cart') }}" />

@hookinsert('cart.top')

<div class="container">
  @if (count($list))
    <div class="row">
      <div class="col-12 col-md-9">
        <table class="table products-table align-middle">
          <thead>
            <tr>
              <th scope="col">
                @php
                  $selectedItems = array_filter($list, function($item) {
                    return $item['selected'] === true;
                  });
                @endphp
                <input class="form-check-input product-all-check" type="checkbox" @if (count($selectedItems) == count($list)) checked @endif>
              </th>
              <th scope="col">{{ __('front/cart.product') }}</th>
              <th scope="col"></th>
              <th scope="col">{{ __('front/cart.price') }}</th>
              <th scope="col">{{ __('front/cart.quantity') }}</th>
              <th scope="col">{{ __('front/cart.subtotal') }}</th>
              <th scope="col"></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($list as $product)
            <tr data-id="{{ $product['id'] }}">
              <td class="td-product-check">
                <input class="form-check-input product-item-check" value="{{ $product['id'] }}" type="checkbox" @if ($product['selected']) checked @endif>
              </td>
              <td class="td-image"><div class="product-image"><img src="{{ $product['image'] }}" class="img-fluid"></div></td>
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
                    <div class="mb-price mt-1">{{ $product['price_format'] }}</div>
                    <div class="quantity-wrap mt-1 d-lg-none">
                      <div class="minus"><i class="bi bi-dash-lg"></i></div>
                      <input type="number" class="form-control" value="{{ $product['quantity'] ?? 1 }}">
                      <div class="plus"><i class="bi bi-plus-lg"></i></div>
                    </div>
                  </div>
                </div>
              </td>
              <td class="td-price">{{ $product['price_format'] }}</td>
              <td class="td-quantity d-none d-lg-table-cell">
                <div class="quantity-wrap">
                  <div class="minus"><i class="bi bi-dash-lg"></i></div>
                  <input type="number" class="form-control" value="{{ $product['quantity'] ?? 1 }}">
                  <div class="plus"><i class="bi bi-plus-lg"></i></div>
                </div>
              </td>
              <td class="td-subtotal">{{ $product['subtotal_format'] }}</td>
              <td class="td-delete"><div class="delete-cart text-danger fs-5 cursor-pointer"><i class="bi bi-x-circle-fill"></i></div></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="col-12 col-md-3">
        <div class="cart-data">
          <div class="title">{{ __('front/cart.cart_total') }}</div>
          <ul class="cart-data-list">
            <li><span>{{ __('front/cart.selected') }}	</span><span class="total-total">{{ $total }}</span></li>
            <li><span>{{ __('front/cart.total') }}</span><span class="total-amount">{{ $amount_format }}</span></li>
          </ul>
          <a class="btn btn-primary btn-lg fw-bold w-100 to-checkout" href="{{ front_route('checkout.index') }}">{{ __('front/cart.go_checkout') }}</a>
        </div>
      </div>
    </div>
  @else
    <div class="text-center pm-5">
      <img src="{{ asset('icon/empty-cart.svg') }}" class="img-fluid w-max-300 mb-5">
      <h2>{{ __('front/cart.empty_cart') }}</h2>
      <a class="btn btn-primary btn-lg mt-3" href="{{ front_route('home.index') }}">{{ __('front/cart.continue') }}</a>
    </div>
  @endif
</div>

@hookinsert('cart.bottom')

@endsection

@push('footer')
<script>
  $('.product-all-check').on('change', function() {
    if ($(this).is(':checked')) {
      $('.products-table .form-check-input').prop('checked', true);
    } else {
      $('.products-table .form-check-input').prop('checked', false);
    }

    var ids = [];
    $('.product-item-check').each(function() {
      ids.push($(this).val());
    });

    const selected = $(this).is(':checked');
    updateSelectedCarts(selected, ids)
  });

  $('.product-item-check').on('change', function() {
    if ($('.product-item-check:checked').length == $('.product-item-check').length) {
      $('.product-all-check').prop('checked', true);
    } else {
      $('.product-all-check').prop('checked', false);
    }

    const selected = $(this).is(':checked');
    const id = $(this).val();

    updateSelectedCarts(selected, [id])
  });

  $('.quantity-wrap .plus, .quantity-wrap .minus').on('click', function() {
    var quantity = parseInt($(this).siblings('input').val());
    if ($(this).hasClass('plus')) {
      $(this).siblings('input').val(quantity + 1);
    } else {
      if (quantity > 1) {
        $(this).siblings('input').val(quantity - 1);
      }
    }

    updateCarts($(this).closest('tr').data('id'), $(this).siblings('input').val() * 1);
  });

  $('.quantity-wrap input').on('change', function() {
    updateCarts($(this).closest('tr').data('id'), $(this).val() * 1);
  });

  $('.delete-cart').on('click', function() {
    var id = $(this).closest('tr').data('id');
    updateCarts(id, 0, 'delete');
  });

  $('.to-checkout').on('click', function(e) {
    e.preventDefault();
    var ids = [];
    $('.product-item-check:checked').each(function() {
      ids.push($(this).val());
    });

    if (!ids.length) {
      inno.msg('Please select the product to checkout!');
      return false;
    }

    window.location.href = $(this).attr('href');
  });

  function updateCarts(id, quantity, method = 'put') {
    axios[method](`${urls.cart_add}/${id}`, {id, quantity}).then(function(res) {
      if (res.success) {
        inno.msg(res.message)
        $('.total-amount').text(res.data.amount_format);
        $('.total-total, .header-cart-icon .icon-quantity').text(res.data.total);
        if (method == 'delete') {
          $(`tr[data-id=${id}]`).remove();
          if (!$('.products-table tbody tr').length) {
            window.location.reload();
          }
        } else {
          $(`tr[data-id=${id}] .td-subtotal`).text(res.data.list.find(item => item.id == id).subtotal_format);
        }
      }
    })
  }

  // 更新选中的购物车商品
  function updateSelectedCarts(selected, ids) {
    axios.post(`${urls.cart_add}/${selected ? 'select' : 'unselect'}`, {cart_ids: ids}).then(function(res) {
      if (res.success) {
        inno.msg(res.message)
        $('.total-amount').text(res.data.amount_format);
        $('.total-total, .header-cart-icon .icon-quantity').text(res.data.total);
      }
    })
  }
</script>
@endpush