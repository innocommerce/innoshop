@php
  $hasProducts = !empty($content['products']) && ($content['products_count'] ?? 0) > 0;
@endphp

@if ($hasProducts || request('design'))
  <section class="module-line">
    <div class="module-brand-products">
      <div class="{{ $content['width_class'] ?? pb_get_width_class($content['width'] ?? 'wide') }}">
        @include('PageBuilder::front.partials.module-title', [
          'title' => $content['title'] ?? '',
          'subtitle' => $content['subtitle'] ?? '',
        ])

        @include('PageBuilder::front.partials.product-list', [
          'products' => $content['products'] ?? collect(),
          'productsCount' => $content['products_count'] ?? 0,
          'columns' => $content['columns'] ?? 4,
        ])

        @if (request('design') && !$hasProducts)
          @include('PageBuilder::front.partials.module-empty', [
            'moduleClass' => 'brand-products',
            'icon' => 'bi-award',
            'message' => __('PageBuilder::modules.no_brand_products'),
          ])
        @endif
      </div>
    </div>
  </section>
@endif 