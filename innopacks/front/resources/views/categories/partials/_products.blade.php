{{-- 产品列表区域 --}}
<div class="row gx-3 gx-lg-4 {{ request('style_list') == 'list' ? 'product-list-wrap' : ''}}">
  @foreach ($products as $product)
    <div class="{{ !request('style_list') || request('style_list') == 'grid' ? 'col-6 col-md-4' : 'col-12'}}">
      @include('shared.product')
    </div>
  @endforeach
</div>

{{-- 分页导航 --}}
{{ $products->onEachSide(1)->links('panel::vendor/pagination/bootstrap-4') }}