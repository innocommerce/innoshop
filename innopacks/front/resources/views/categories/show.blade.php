@extends('layouts.app')
@section('body-class', 'page-categories')

@section('content')
  <x-front-breadcrumb type="category" :value="$category"/>

  @hookinsert('category.show.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-md-3">
        @include('shared.filter_sidebar')
      </div>
      <div class="col-12 col-md-9">
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
                  <option value="products.sales|asc" {{ request('sort') == 'products.sales' && request('order') == 'asc' ? 'selected' : '' }}>{{ __('/front/category.sales_volume') }} ({{ __('/front/category.low') . ' - ' . __('/front/category.high')}})</option>
                  <option value="products.sales|desc" {{ request('sort') == 'products.sales' && request('order') == 'desc' ? 'selected' : '' }}>{{ __('/front/category.sales_volume') }} ({{ __('/front/category.high') . ' - ' . __('/front/category.low')}})</option>
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

        <div class="row gx-3 gx-lg-4 {{ request('style_list') == 'list' ? 'product-list-wrap' : ''}}">
         @foreach ($products as $product)
            <div class="{{ !request('style_list') || request('style_list') == 'grid' ? 'col-6 col-md-4' : 'col-12'}}">
              @include('shared.product')
            </div>
          @endforeach
        </div>

        {{ $products->links('panel::vendor/pagination/bootstrap-4') }}
      </div>
    </div>
  </div>

  @hookinsert('category.show.bottom')

@endsection

@push('footer')
  <script>
    $('.form-select, input[name="style_list"]').change(function(event) {
      filterProductData();
    });

    function filterProductData() {
      let url = inno.removeURLParameters(window.location.href, 'price', 'sort', 'order');
      let order = $('.order-select').val();
      let perPage = $('.per-page-select').val();
      let styleList = $('input[name="style_list"]:checked').val();

      layer.load(2, {shade: [0.3,'#fff'] })

      if (order) {
        let orderKeys = order.split('|');
        url = inno.updateQueryStringParameter(url, 'sort', orderKeys[0]);
        url = inno.updateQueryStringParameter(url, 'order', orderKeys[1]);
      }

      if (perPage) {
        url = inno.updateQueryStringParameter(url, 'per_page', perPage);
      }

      if (styleList) {
        url = inno.updateQueryStringParameter(url, 'style_list', styleList);
      }

      location = url;
    }

    function filterAttrChecked(data) {
      let filterAtKey = [];
      data.forEach((item) => {
        let checkedAtValues = [];
        item.values.forEach((val) => val.selected ? checkedAtValues.push(val.id) : '')
        if (checkedAtValues.length) {
          filterAtKey.push(`${item.id}:${checkedAtValues.join(',')}`)
        }
      })

      return filterAtKey.join('|')
    }
  </script>
@endpush