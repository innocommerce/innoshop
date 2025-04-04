<!-- Cart Offcanvas Component -->
<div class="offcanvas offcanvas-end cart-offcanvas" tabindex="-1" id="cartOffcanvas" aria-labelledby="cartOffcanvasLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="cartOffcanvasLabel">{{ __('front/cart.cart') }}</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    @if (isset($list) && count($list))
      <div class="cart-items">
        @foreach ($list as $product)
          <div class="cart-item" data-id="{{ $product['id'] }}">
            <div class="d-flex">
              <div class="cart-item-image">
                <img src="{{ $product['image'] }}" class="img-fluid">
              </div>
              <div class="cart-item-details ms-3">
                <div class="cart-item-name">
                  <a href="{{ $product['url'] }}">{{ $product['product_name'] }}</a>
                </div>
                <div class="text-secondary mt-1">
                  {{ $product['sku_code'] }}
                  @if ($product['variant_label'])
                    - {{ $product['variant_label'] }}
                  @endif
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div class="cart-item-price">{{ $product['price_format'] }}</div>
                  <div class="quantity-wrap small-quantity">
                    <div class="minus"><i class="bi bi-dash-lg"></i></div>
                    <input type="number" class="form-control" value="{{ $product['quantity'] ?? 1 }}">
                    <div class="plus"><i class="bi bi-plus-lg"></i></div>
                  </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div class="cart-item-subtotal">{{ $product['subtotal_format'] }}</div>
                  <div class="delete-cart text-danger cursor-pointer"><i class="bi bi-x-circle-fill"></i></div>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <div class="cart-summary mt-4">
        <div class="d-flex justify-content-between mb-2">
          <span>{{ __('front/cart.total') }}</span>
          <span class="total-amount">{{ $amount_format }}</span>
        </div>
        <a class="btn btn-primary btn-lg fw-bold w-100 to-checkout" href="{{ front_route('checkout.index') }}">{{ __('front/cart.go_checkout') }}</a>
        <a class="btn btn-outline-secondary btn-lg fw-bold w-100 mt-2" href="{{ front_route('carts.index') }}">{{ __('front/cart.cart') }}</a>
      </div>
    @else
      <div class="text-center py-5">
        <img src="{{ asset('images/icons/empty-cart.svg') }}" class="img-fluid w-max-200 mb-4">
        <h5>{{ __('front/cart.empty_cart') }}</h5>
        <a class="btn btn-primary mt-3" href="{{ front_route('home.index') }}">{{ __('front/cart.continue') }}</a>
      </div>
    @endif
  </div>
</div>

<style>
  .cart-offcanvas {
    width: 400px;
    max-width: 100%;
  }

  .cart-item {
    padding: 15px 0;
    border-bottom: 1px solid #eee;
  }

  .cart-item-image {
    width: 80px;
    height: 80px;
    overflow: hidden;
  }

  .cart-item-image img {
    object-fit: cover;
    width: 100%;
    height: 100%;
  }

  .small-quantity {
    max-width: 120px;
  }

  .w-max-200 {
    max-width: 200px;
  }

  /* Hide on mobile */
  @media (max-width: 767.98px) {
    .cart-offcanvas {
      display: none;
    }
  }
</style>
