@if(is_array($product) && $product)
  <a href="{{ panel_route('theme_market.show',$product['product_id']) }}">
    <div class="card product-item-card border-0 rounded-4 shadow-sm px-0  pb-4 product-grid-item h-100 position-relative">
      <div class="mx-0 px-0 position-absolute w-100 start-0 top-0 rounded-top-4"
           id="{{'productHeader'.$product['product_id']}}" style="background-color: rgb(255,255,255); height: 60px;">
      </div>

      <div class="image border-2 border-light">
        <a href="{{ panel_route('theme_market.show', $product['product_id']) }}" class="text-start">
          <img src="{{ $product['image_big'] ?? ''}}" class="rounded-top-4 img-fluid w-100 mx-0 px-0" style="height: 180px;object-fit: cover">
        </a>
      </div>

      <div class="product-item-info">
        <h5 class="h5 text-dark text-start my-2 text-truncate mx-3">
          <a href="{{ panel_route('theme_market.show',$product['product_id']) }}"
             class="text-dark text-decoration-none">{{ $product['name'] }}</a></h5>

        @if($product['seller_name'])
          <p class="card-text text-start mx-3 mb-1">作者：
            <span class="panel-text-primary">{{ $product['seller_name'] }}</span>
          </p>
        @endif

        <p class="card-text text-secondary text-start mx-3 mb-0 d-none">
          <i class="bi-star-fill text-warning pe-1"></i>
          <i class="bi-star-fill text-warning pe-1"></i>
          <i class="bi-star-fill text-warning pe-1"></i>
          <i class="bi-star-fill text-warning pe-1"></i>
          <i class="bi-star-half text-warning pe-1"></i> 4.5 (100)
        </p>

        <div class="row product-price-bottom position-absolute bottom-0 w-100 py-4">
          <div class="product-bottom text-start">
            <div class="product-bottom-btns text-start">
              <a class="h5 btn-add-cart buy-now-checkout panel-text-primary text-decoration-underline cursor-pointer cursor-pointer text-start mx-3"
                 href="" data-id="{{ $product['product_id'] }}"
                 data-sku-id="{{ $product['sku_id'] }}" data-is-buy-now="true"> {{ __('front/product.view_details') }}
              </a>
            </div>
            <div class="product-price text-start">
              @if ($product['origin_price_format'])
                <div class="price-old ms-3">{{ $product['origin_price_format'] }}</div>
              @endif
              <div class="price-new mx-3">{{ $product['price_format'] }}</div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </a>
@endif
@push('footer')
  <script>
  </script>
@endpush
