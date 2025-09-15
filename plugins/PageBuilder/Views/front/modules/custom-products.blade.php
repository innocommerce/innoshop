@if (!empty($content['products']) && count($content['products']) > 0 || request('design'))
  <section class="module-line">
    <div class="module-product">
      <div class="{{ pb_get_width_class($content['width'] ?? 'wide') }}">
        @if (!empty($content['title']))
          <div class="module-title-wrap text-center">
            <div class="module-title">{{ $content['title'] ?? '' }}</div>
            @if (!empty($content['subtitle']))
              <div class="module-sub-title">{{ $content['subtitle'] ?? '' }}</div>
            @endif
          </div>
        @endif

        @if (!empty($content['products']) && $content['products']->count() > 0)
          <div class="row gx-3 gx-lg-4">
            @foreach ($content['products'] as $product)
              <div class="{{ pb_get_bootstrap_columns($content['columns'] ?? 4) }}">
                @include('shared.product', ['product' => $product])
              </div>
            @endforeach
          </div>
        @elseif (request('design'))
          <div class="module-product-empty">
            <div class="module-product-empty-text">
              <i class="bi bi-box"></i>
              <span>暂无商品,请配置商品</span>
            </div>
          </div>
        @endif
      </div>
    </div>
  </section>
@endif
