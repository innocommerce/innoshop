@extends('panel::layouts.app')
@section('body-class', 'page-product-form')

@section('title', __('panel/menu.products'))
<x-panel::form.right-btns formid="product-form" />

@php
$weightClasses = [
  ['id' => 0, 'name' => 'g'],
  ['id' => 1, 'name' => 'kg'],
  ['id' => 2, 'name' => 'lb'],
  ['id' => 3, 'name' => 'oz'],
];
@endphp

@section('content')
<form class="needs-validation no-load" novalidate
  action="{{ $product->id ? panel_route('products.update', [$product->id]) : panel_route('products.store') }}" method="POST" id="product-form">
  @csrf
  @method($product->id ? 'PUT' : 'POST')
  <div class="row">
    <div class="col-12 col-md-8">
      <div class="card mb-3">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('panel/common.basic_info') }}</h5>
        </div>
        <div class="card-body">
          <div class="accordion accordion-flush locales-accordion mb-3" id="data-locales">
            @foreach (locales() as $locale)
            @php($localeCode = $locale->code)
            @php($localeName = $locale->name)
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button"
                  data-bs-toggle="collapse" data-bs-target="#data-locale-{{ $localeCode }}"
                  aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="data-locale-{{ $localeCode }}">
                  <div class="wh-20 me-2">
                    <img src="{{ image_origin($locale->image) }}" class="img-fluid">
                  </div>
                  {{ $localeName }}
                </button>
              </h2>
              <div id="data-locale-{{ $localeCode }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                data-bs-parent="#data-locales">
                <div class="accordion-body" data-locale-code="{{ $localeCode }}" data-locale-name="{{ $localeName }}">
                  <input type="hidden" name="translations[{{$localeCode}}][locale]" value="{{ $localeCode }}">
                  <x-common-form-input title="{{ __('panel/product.name') }}" name="translations[{{$localeCode}}][name]"
                    value="{{ old('translations.' . $localeCode . '.name', $product->translate($localeCode, 'name')) }}"
                    required placeholder="{{ __('panel/product.name') }}" />

                  <x-common-form-textarea title="{{ __('panel/product.summary') }}" name="translations[{{$localeCode}}][summary]"
                    value="{{ old('translations.' . $localeCode . '.summary', $product->translate($localeCode, 'summary')) }}"
                    placeholder="{{ __('panel/product.summary') }}" column="product_summary" generate="true"
                    description="填写关键字，点击'AI生成'可生成摘要" />

                  <x-common-form-textarea title="{{ __('panel/product.selling_point') }}" name="translations[{{$localeCode}}][selling_point]"
                    value="{{ old('translations.' . $localeCode . '.selling_point', $product->translate($localeCode, 'selling_point')) }}"
                    placeholder="{{ __('panel/product.selling_point') }}" column="product_selling_point" generate="true"
                    description="简单描述产品特点及优势，点击'AI生成'可生成卖点" />

                  <x-common-form-rich-text title="{{ __('panel/product.content') }}" name="translations[{{$localeCode}}][content]"
                    value="{{ old('translations.' . $localeCode . '.content', $product->translate($localeCode, 'content')) }}"
                    required placeholder="{{ __('panel/product.describe') }}" />

                  <x-common-form-input title="{{ __('panel/common.meta_title') }}" name="translations[{{$localeCode}}][meta_title]"
                    value="{{ old('translations.' . $localeCode . '.meta_title', $product->translate($localeCode, 'meta_title')) }}"
                    placeholder="{{ __('panel/common.meta_title') }}" column="product_title" generate="true" />

                  <x-common-form-textarea title="{{ __('panel/common.meta_description') }}" name="translations[{{$localeCode}}][meta_description]"
                    value="{{ old('translations.' . $localeCode . '.meta_description', $product->translate($localeCode, 'meta_description')) }}"
                    placeholder="{{ __('panel/common.meta_description') }}" column="product_description" generate="true" />

                  <x-common-form-input title="{{ __('panel/common.meta_keywords') }}" name="translations[{{$localeCode}}][meta_keywords]"
                    value="{{ old('translations.' . $localeCode . '.meta_keywords', $product->translate($localeCode, 'meta_keywords')) }}"
                    placeholder="{{ __('panel/common.meta_keywords') }}" column="product_keywords" generate="true" />
                </div>
              </div>
            </div>
            @endforeach
          </div>

          <x-common-form-images title="{{ __('panel/common.image') }}" name="images" :values="old('images', $product->images->pluck('path')->toArray())"/>

          <div class="skus-single-box {{ $product->is_multiple() ? 'd-none' : '' }}">
            <div class="row">
              <div class="col-12 col-md-4">
                <x-common-form-input :title="__('panel/product.price')" name="skus[0][price]" required value="{{ old('skus.0.price', ($product->masterSku->price ?? '')) }}" />
              </div>
              <div class="col-12 col-md-4">
                <x-common-form-input :title="__('panel/product.quantity')" name="skus[0][quantity]" value="{{ old('skus.0.quantity', $product->masterSku->quantity ?? '') }}" />
              </div>
              <div class="col-12 col-md-4">
                <x-common-form-input title="SKU Code" name="skus[0][code]" required value="{{ old('skus.0.code', $product->masterSku->code ?? '') }}" />
              </div>
            </div>

            <div class="row">
              <div class="col-12 col-md-4">
                <x-common-form-input :title="__('panel/product.model')" name="skus[0][model]" value="{{ old('skus.0.model', $product->masterSku->model ?? '') }}" />
              </div>
              <div class="col-12 col-md-4">
                <x-common-form-input :title="__('panel/product.origin_price')" name="skus[0][origin_price]" value="{{ old('skus.0.origin_price', $product->masterSku->origin_price ?? '') }}" />
              </div>
            </div>
          </div>
        </div>
      </div>

      @include('panel::products._form_variant')

      @include('panel::products._form_attribute')

    </div>
    <div class="col-12 col-md-4 ps-md-0">
      <div class="card">
        <div class="card-body">
          <x-common-form-switch-radio :title="__('panel/common.status')" name="active" :value="old('active', $product->active ?? true)" />
          <x-common-form-input :title="__('panel/common.slug')" name="slug" :value="old('slug', $product->slug ?? '')" :placeholder="__('panel/common.slug')" column="product_slug" generate="true" />
          <x-common-form-select :title="__('panel/product.brand')" name="brand_id" :value="old('brand_id', $product->brand_id ?? 0)" :options="$brands" key="id" label="name" />
          <x-panel::form.row title="{{ __('panel/product.category') }}">
            <div class="category-select">
              @if ($categories->count())
              <div class="category-search">
                <input type="text" class="form-control" placeholder="{{ __('panel/common.filter') }}">
              </div>
              <ul>
                @foreach ($categories as $category)
                <li>
                  <label>
                    <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                      {{ in_array($category->id, old('categories', $product->categories->pluck('id')->toArray())) ? 'checked' : '' }}>
                    <span class="name">{{ $category->translation->name }}</span>
                  </label>
                </li>
                @endforeach
              </ul>
              @endif
            </div>
          </x-panel::form.row>
          <x-common-form-select :title="__('panel/product.tax_class')" name="tax_class_id" :value="old('tax_class_id', $product->tax_class_id ?? 0)" :options="$tax_classes" key="id" label="name" />
          <div class="d-flex">
            <div class="w-25">
              <x-common-form-input :title="__('panel/product.weight')" name="weight" :value="old('weight', $product->weight ?? '')" :placeholder="__('panel/product.weight')" />
            </div>
            <div class="ms-1 w-75">
              <x-common-form-select :title="__('panel/product.weight_class')" name="weight_class" :value="old('weight_class', $product->weight_class ?? 0)" :options="$weightClasses" key="id" label="name" />
            </div>
          </div>
          <x-common-form-input :title="__('panel/product.position')" name="position" :value="old('position', $product->position ?? '')" :placeholder="__('panel/product.position')" />
          <x-common-form-input :title="__('panel/product.sales')" name="sales" :value="old('sales', $product->sales ?? '')" :placeholder="__('panel/product.sales')" />
          <x-common-form-input :title="__('panel/product.viewed')" name="viewed" :value="old('viewed', $product->viewed ?? '')" :placeholder="__('panel/product.viewed')" />
          <x-common-form-switch-radio :title="__('panel/product.is_virtual')" name="is_virtual" :value="old('is_virtual', $product->is_virtual ?? false)" />
        </div>
      </div>
    </div>
  </div>

  <button type="submit" class="d-none"></button>
</form>
@endsection

@push('footer')
<script>
  document.getElementById('product-form').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
    }
  });

  $('.category-search input').on('input', function() {
    const val = $(this).val().trim();
    var lists = $('.category-select li');
    lists.each(function() {
      var text = $(this).find('.name').text();
      if (text.indexOf(val) > -1) {
        $(this).show();
        $(this).find('.name').html(text.replace(val, '<span style="color: red;">' + val + '</span>'));
      } else {
        $(this).hide();
      }
    });
  });
</script>
@endpush