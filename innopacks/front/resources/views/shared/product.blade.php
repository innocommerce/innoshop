@if($product->translation)
  <div class="product-grid-item">
    <div class="image">
      <a href="{{ $product->url }}">
        <img src="{{ $product->image_url }}" class="img-fluid">
      </a>
    </div>
    <div class="product-item-info">
      <div class="product-name"><a href="{{ $product->url }}">{{ $product->translation->name }}</a></div>
      <div class="product-bottom">
        <div class="product-bottom-btns">
          <div class="btn-add-cart cursor-pointer" data-id="{{ $product->id }}"
               data-sku-id="{{ $product->product_sku_id }}"> {{ __('front/cart.add_to_cart') }}
          </div>
        </div>
        <div class="product-price">
          @if ($product->masterSku->origin_price)
            <div class="price-old">{{ $product->masterSku->origin_price_format }}</div>
          @endif
          <div class="price-new">{{ $product->masterSku->price_format }}</div>
        </div>
      </div>
    </div>
  </div>
@endif