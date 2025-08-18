@if($product->type === 'bundle' && $bundle_items->count() > 0)
  <div class="bundle-items-display mb-3">
    <h6 class="bundle-title">{{ __('front/product.bundle_includes') }}:</h6>
    <div class="bundle-products d-flex align-items-center flex-wrap">
      @foreach($bundle_items as $index => $bundleItem)
        @if($index > 0)
          <span class="bundle-separator mx-2">+</span>
        @endif
        <div class="bundle-product-item d-flex align-items-center">
          <img src="{{ $bundleItem->sku->getImageUrl(40, 40) }}"
               alt="{{ $bundleItem->sku->full_name }}"
               class="bundle-product-image me-2"
               style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
          <div class="bundle-product-info">
            <div class="bundle-product-name" data-bs-toggle="tooltip" title="{{ $bundleItem->sku->full_name }}">
              {{ sub_string($bundleItem->sku->full_name, 68) }}
            </div>
            @if($bundleItem->quantity > 0)
              <small class="text-muted">× {{ $bundleItem->quantity }}</small>
            @endif
          </div>
        </div>
      @endforeach
    </div>
  </div>
@endif 