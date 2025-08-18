{{-- 产品排序和筛选控制区域 --}}
<div class="category-wrap">
  <div class="top-order-wrap">
    <div class="d-none d-md-block">
      {{ __('front/common.page_total_show', ['first' => $products->firstItem(), 'last' => $products->lastItem(), 'total' => $products->total()]) }}
    </div>
    <div class="right">
      <div class="order-item">
        <span class="d-none d-md-block">{{ __('front/common.sort') }}:</span>
        <select class="form-select order-select">
          <option value="">{{ __('/front/category.default') }}</option>
          <option value="products.sales|asc" {{ request('sort') == 'products.sales' && request('order') == 'asc' ? 'selected' : '' }}>{{ __('/front/category.sales') }} ({{ __('/front/category.low') . ' - ' . __('/front/category.high')}})</option>
          <option value="products.sales|desc" {{ request('sort') == 'products.sales' && request('order') == 'desc' ? 'selected' : '' }}>{{ __('/front/category.sales') }} ({{ __('/front/category.high') . ' - ' . __('/front/category.low')}})</option>
          <option value="pt.name|asc" {{ request('sort') == 'pt.name' && request('order') == 'asc' ? 'selected' : '' }}>{{ __('/front/category.name') }} (A - Z)</option>
          <option value="pt.name|desc" {{ request('sort') == 'pt.name' && request('order') == 'desc' ? 'selected' : '' }}>{{ __('/front/category.name') }} (Z - A)</option>
          <option value="ps.price|asc" {{ request('sort') == 'ps.price' && request('order') == 'asc' ? 'selected' : '' }}>{{ __('/front/category.price') }} ({{ __('/front/category.low') . ' - ' . __('/front/category.high')}})</option>
          <option value="ps.price|desc" {{ request('sort') == 'ps.price' && request('order') == 'desc' ? 'selected' : '' }}>{{ __('/front/category.price') }} ({{ __('/front/category.high') . ' - ' . __('/front/category.low')}})</option>
        </select>
      </div>
      <div class="order-item">
        <span class="d-none d-md-block">{{ __('front/common.show') }}:</span>
        <select class="form-select per-page-select">
          @foreach ($per_page_items as $val)
            <option value="{{ $val }}" {{ request('per_page') == $val ? 'selected' : '' }}>{{ $val }}</option>
          @endforeach
        </select>
      </div>
      <div class="order-item">
        <label href="javascript:void(0)" class="order-icon {{ !request('style_list') || request('style_list') == 'grid' ? 'active' : ''}}">
          <i class="bi bi-grid"></i>
          <input class="d-none" value="grid" type="radio" name="style_list">
        </label>

        <label href="javascript:void(0)" class="order-icon {{ request('style_list') && request('style_list') == 'list' ? 'active' : ''}}">
          <i class="bi bi-list"></i>
          <input class="d-none" value="list" type="radio" name="style_list">
        </label>
      </div>
    </div>
  </div>
</div>