@if (!empty($variant_dimensions) && count($variant_dimensions))
  <div class="product-variant-box" data-variant-box>
    @hookupdate('front.products.show.variants.value')
    @foreach($variant_dimensions as $variant)
      <div class="product-variant" data-variant-id="{{ $variant['id'] }}">
        <div class="variant-title">
            {{ $variant['name'][front_locale_code()] ?? ($variant['name'][setting_locale_code()] ?? '-') }}</div>
          <div class="variant-values">
            @foreach ($variant['values'] as $value)
              <div class="variant-value-name"
                   data-variant-id="{{ $variant['id'] }}"
                   data-value-id="{{ $value['id'] }}">
                @if(isset($value['image']) && !empty($value['image']))
                  <div class="variant-image-container">
                    <img src="{{ image_resize($value['image'], 30, 30) }}" alt="{{ $value['name'][front_locale_code()] ?? ($value['name'][setting_locale_code()] ?? '-') }}" class="variant-value-image">
                  </div>
                @endif
                <span class="variant-text">{{ $value['name'][front_locale_code()] ?? ($value['name'][setting_locale_code()] ?? '-') }}</span>
              </div>
            @endforeach
        </div>
      </div>
    @endforeach
    @endhookupdate
  </div>
@endif
@push('footer')
  <script>
    let skus = @json($skus ?? []);

    if ($('.product-variant-box').length) {
      let masterSku = @json($sku);

      // selected is Map<variant_id, value_id>, sourced from the master SKU's
      // structured variant_values. ID-based, so order of variants in the
      // product doesn't matter (unlike the legacy positional index array).
      let selected = new Map();
      (masterSku.variant_values || []).forEach(v => {
        selected.set(String(v.variant_id), String(v.value_id));
      });

      // Reflect the current selection in the UI
      function syncActiveFromSelection() {
        $('.product-variant-box .variant-value-name').removeClass('active');
        selected.forEach((valueId, variantId) => {
          $(`.variant-value-name[data-variant-id="${variantId}"][data-value-id="${valueId}"]`).addClass('active');
        });
      }

      // Disable values that don't have a matching in-stock SKU when combined
      // with the rest of the current selection.
      function updateVariantStatus() {
        $('.product-variant-box .product-variant').each((_, variantEl) => {
          const variantId = $(variantEl).data('variant-id');
          $(variantEl).find('.variant-value-name').each((_, valueEl) => {
            const valueId = $(valueEl).data('value-id');
            const matched = findSkuFor(variantId, valueId);
            if (matched && matched.quantity > 0) {
              $(valueEl).removeClass('disabled');
            } else {
              $(valueEl).addClass('disabled');
            }
          });
        });

        if (masterSku.quantity * 1 <= 0) {
          $('.product-info-bottom .add-cart, .product-info-bottom .buy-now, .product-info-bottom.quantity-wrap').addClass('disabled');
          $('.stock-wrap .in-stock').addClass('d-none').siblings('.out-stock').removeClass('d-none');
        } else {
          $('.product-info-bottom .add-cart, .product-info-bottom .buy-now, .product-info-bottom.quantity-wrap').removeClass('disabled');
          $('.stock-wrap .in-stock').removeClass('d-none').siblings('.out-stock').addClass('d-none');
        }
      }

      // Find a SKU that matches: every (variant_id → value_id) pair in the
      // trial selection must be present in sku.variant_values.
      function findSkuFor(variantId, valueId) {
        const trial = new Map(selected);
        trial.set(String(variantId), String(valueId));
        return skus.find(sku => {
          const values = sku.variant_values || [];
          if (values.length !== trial.size) return false;
          return values.every(v => trial.get(String(v.variant_id)) === String(v.value_id));
        });
      }

      syncActiveFromSelection();
      updateVariantStatus();

      $('.product-variant-box .variant-value-name').click(function () {
        const variantId = String($(this).data('variant-id'));
        const valueId   = String($(this).data('value-id'));
        const matched = findSkuFor(variantId, valueId);
        if (!matched) return;  // no SKU matches this combination

        masterSku = matched;
        selected.set(variantId, valueId);

        $('.product-param .sku .value').text(masterSku.code);
        $('.product-param .model .value').text(masterSku.model);
        $('.product-price .price').text(masterSku.price_format);
        $('.product-price .old-price').text(masterSku.origin_price_format);
        $('.product-quantity').data('sku-id', masterSku.id)

        // Update option component base price and recalculate total price
        if (typeof window.updateBasePrice === 'function') {
          window.updateBasePrice(masterSku.price);
        }

        if (masterSku.origin_image_url) {
          $('.main-product-img img').attr('src', masterSku.origin_image_url);
        }
        history.pushState({}, '', inno.updateQueryStringParameter(window.location.href, 'sku_id', masterSku.id));

        if (masterSku.quantity * 1 <= 0) {
          $('.product-info-bottom .add-cart, .product-info-bottom .buy-now,.product-info-bottom.quantity-wrap').addClass('disabled');
          $('.stock-wrap .in-stock').addClass('d-none').siblings('.out-stock').removeClass('d-none');
        } else {
          $('.product-info-bottom .add-cart, .product-info-bottom .buy-now, .product-info-bottom.quantity-wrap').removeClass('disabled');
          $('.stock-wrap .in-stock').removeClass('d-none').siblings('.out-stock').addClass('d-none');
        }

        syncActiveFromSelection();
        updateVariantStatus();
      });
    }
  </script>
@endpush
