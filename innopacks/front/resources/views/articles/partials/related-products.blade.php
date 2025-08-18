@if($relatedProducts && $relatedProducts->count() > 0)
<div class="related-products-section mt-5">
  <h4 class="section-title mb-4">
    {{ __('front/article.related_products') }}
  </h4>
  <div class="row gx-3 gx-lg-4">
    @foreach($relatedProducts as $product)
      <div class="col-6 col-lg-4 mb-4">
        @include('shared.product', ['product' => $product])
      </div>
    @endforeach
  </div>
  @if($relatedProducts->count() >= 6)
    <div class="text-center mt-4">
      <a href="{{ front_route('products.index') }}" class="btn btn-primary btn-sm">
        {{ __('front/product.view_more_products') }}
      </a>
    </div>
  @endif
</div>
@endif