@if (!empty($content['products']))
  <section class="module-line">
    <div class="module-category">
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
          @php
            $columns = $content['columns'] ?? 4;
            $colClass = pb_get_bootstrap_columns($columns);
          @endphp
          <div class="row gx-3 gx-lg-4">
            @foreach ($content['products'] as $product)
              <div class="{{ $colClass }}">
                @include('shared.product', ['product' => $product])
              </div>
            @endforeach
          </div>
        @elseif (request('design'))
          <div class="module-category-empty">
            <div class="module-category-empty-text">
              <i class="bi bi-collection"></i>
              <span>暂无商品,请配置分类</span>
            </div>
          </div>
        @endif
      </div>
    </div>
  </section>
@endif 