<div class="fixed-bottom bg-light py-2">
  <div class="container">
    <div class="row align-items-center py-3">

      <div class="col-3 col-md-2 text-center text-md-start">
        <img src="{{ $product->image_url }}" alt="Dawn Stroll Lightweight Spring Trench Coat" class="img-fluid"
             style="max-width: 100px;">
      </div>

      <div class="col-5 col-md-4 text-center text-md-start">
        <h5 class="mb-1">{{ $product->translation->name }}</h5>
        <p class="mb-0">
          <span class="text-danger fs-4">{{ $sku['price_format'] }}</span>
          <span class="text-muted text-decoration-line-through">{{ $sku['origin_price_format'] }}</span>
        </p>
      </div>

      <div class="col-4 col-md-2 text-center">
        <div class="d-inline-flex align-items-center quantity-wrap my-3 bg-white">
          <div class="minus"><i class="bi bi-dash-lg"></i></div>
          <input type="number" class="form-control float-product-quantity" value="1">
          <div class="plus"><i class="bi bi-plus-lg"></i></div>
        </div>
      </div>

      <div class=" col-md-4 my-3 text-center text-md-end text-sm-end">
        <button class="btn btn-primary float-add-cart me-2">{{ __('FloatCart::common.add_to_cart') }}</button>
        <button class="btn btn-secondary float-buy-now">{{ __('FloatCart::common.buy_now') }}</button>
      </div>
    </div>
  </div>
</div>


@push('footer')
  <script>
    $('.float-add-cart, .float-buy-now').on('click', function () {
      const quantity = $('.float-product-quantity').val();
      const skuId = $('.product-quantity').data('sku-id');
      const isBuyNow = $(this).hasClass('float-buy-now');

      inno.addCart({skuId, quantity, isBuyNow}, this, function (res) {
        if (isBuyNow) {
          window.location.href = '{{ front_route('carts.index') }}';
        }
      })
    });
  </script>

@endpush