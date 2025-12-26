{{-- 产品列表组件 --}}
@if (!empty($products) && ($productsCount ?? 0) > 0)
  <div class="row gx-3 gx-lg-4">
    @foreach ($products as $product)
      <div class="{{ pb_get_bootstrap_columns($columns ?? 4) }}">
        @include('shared.product', ['product' => $product])
      </div>
    @endforeach
  </div>
@endif

