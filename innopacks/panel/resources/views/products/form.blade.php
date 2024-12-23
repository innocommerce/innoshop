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
 action="{{ $product->id ? panel_route('products.update', [$product->id]) : panel_route('products.store') }}"
 method="POST" id="product-form">
 @csrf
 @method($product->id ? 'PUT' : 'POST')
 <div class="row">
  <div class="col-12 col-md-12">
   <div class="card mb-3">
    <div class="card-body">
     <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item" role="presentation">
       <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic-tab-pane" type="button"
        role="tab" aria-controls="basic-tab-pane" aria-selected="true">{{ __('panel/product.basic_information') }}</button>
      </li>
      <li class="nav-item" role="presentation">
       <button class="nav-link" id="translation-tab" data-bs-toggle="tab" data-bs-target="#translation-tab-pane"
        type="button" role="tab" aria-controls="translation-tab-pane" aria-selected="false">{{__('panel/product.product_description')}}</button>
      </li>
      <li class="nav-item" role="presentation">
       <button class="nav-link" id="specification-tab" data-bs-toggle="tab" data-bs-target="#specification-tab-pane" type="button"
        role="tab" aria-controls="specification-tab-pane" aria-selected="false">{{__('panel/product.specification_attribute')}}</button>
      </li>
      <li class="nav-item" role="presentation">
       <button class="nav-link" id="addition-tab" data-bs-toggle="tab" data-bs-target="#addition-tab-pane"
        type="button" role="tab" aria-controls="addition-tab-pane" aria-selected="false">{{__('panel/product.extend_information')}}</button>
      </li>
      <li class="nav-item" role="presentation">
       <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo-tab-pane" type="button"
        role="tab" aria-controls="seo-tab-pane" aria-selected="false">{{__('panel/product.seo')}}</button>
      </li>
      <li class="nav-item" role="presentation">
       <button class="nav-link" id="relation-tab" data-bs-toggle="tab" data-bs-target="#relation-tab-pane" type="button"
        role="tab" aria-controls="relation-tab-pane" aria-selected="false">{{__('panel/product.related_products')}}</button>
      </li>
      @hookinsert('panel.product.edit.tab.nav.bottom')
     </ul>

     <div class="tab-content" id="myTabContent">
      <div class="tab-pane fade show active mt-3" id="basic-tab-pane" role="tabpanel" aria-labelledby="basic-tab" tabindex="0">
       <div class="mb-3">
        <div class="mb-1 fs-6">{{ __('panel/product.name') }}</div>
        @foreach (locales() as $locale)
        @php($localeCode = $locale->code)
        @php($localeName = $locale->name)

        <div class="input-group">
         <span class="input-group-text">
          <div class="d-flex align-items-center wh-20 me-2">
           <img src="{{ image_origin($locale->image) }}" class="img-fluid" alt="{{ $localeName }}">
          </div>
         </span>
         <div class="col-12 col-md-5">
          <input type="text" class="form-control" name="translations[{{$localeCode}}][name]"
           value="{{ old('translations.' . $localeCode . '.name', $product->translate($localeCode, 'name')) }}" required
           placeholder="{{ __('panel/product.name') }}" aria-label="{{ $localeName }}" aria-describedby="basic-addon1">
         </div>
        </div>
        @endforeach
       </div>
       <x-common-form-images title="{{ __('panel/common.image') }}" name="images"
        :values="old('images', $product->images->pluck('path')->toArray())" />
       <x-common-form-switch-radio :title="__('panel/common.status')" name="active"
        :value="old('active', $product->active ?? true)" />
        @hookinsert('panel.product.edit.basic.after')
      </div>
      <div class="tab-pane fade mt-4" id="translation-tab-pane" role="tabpanel" aria-labelledby="translation-tab"
       tabindex="1">
       <div class="mb-1 fs-6">{{ __('panel/product.content') }}</div>
       <ul class="nav nav-tabs" id="myTab" role="tablist">
        @foreach (locales() as $locale)
        @php($localeCode = $locale->code)
        @php($localeName = $locale->name)
        <li class="nav-item" role="presentation">
         <button class="nav-link d-flex {{ $loop->first ? 'active' : '' }}" id="{{ $localeCode }}-tab"
          data-bs-toggle="tab" data-bs-target="#{{ $localeCode }}-tab-pane" type="button" role="tab"
          aria-controls="{{ $localeCode }}-tab-pane" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
          <div class="wh-20 me-2">
           <img src="{{ image_origin($locale->image) }}" class="img-fluid">
          </div>
          {{ $localeName }}
         </button>
        </li>
        @endforeach
       </ul>

       <div class="tab-content pt-1" id="myTabContent">
        @foreach (locales() as $locale)
        @php($localeCode = $locale->code)
        @php($localeName = $locale->name)
        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $localeCode }}-tab-pane"
         role="tabpanel" aria-labelledby="{{ $localeCode }}-tab" tabindex="0">
         <div class="mb-3 ">
          <!-- Hidden Locale Field -->
          <input type="hidden" name="translations[{{$localeCode}}][locale]" value="{{ $localeCode }}">

          <!-- Content Field -->
          <x-common-form-rich-text
           name="translations[{{$localeCode}}][content]"
           value="{{ old('translations.' . $localeCode . '.content', $product->translate($localeCode, 'content')) }}"
           required placeholder="{{ __('panel/product.describe') }}" />
         </div>
        </div>
        @endforeach
       </div>
      </div>
      <div class="tab-pane fade mt-4" id="specification-tab-pane" role="tabpanel" aria-labelledby="specification-tab" tabindex="2">
       <div class="mt-2 skus-single-box {{ $product->isMultiple() ? 'd-none' : '' }}">
        <div class="alert alert-info mb-3">
         <i class="bi bi-info-circle me-2"></i>
         {{ __('panel/product.sku_notice') }}
        </div>
        <div class="row">
         <div class="col-12 col-md-4">
          <x-common-form-input :title="__('panel/product.price')" name="skus[0][price]"
           value="{{ old('skus.0.price', ($product->masterSku->price ?? '')) }}" />
         </div>
         <div class="col-12 col-md-4">
          <x-common-form-input :title="__('panel/product.quantity')" name="skus[0][quantity]"
           value="{{ old('skus.0.quantity', $product->masterSku->quantity ?? '') }}" />
         </div>
         <div class="col-12 col-md-4">
          <x-common-form-input title="SKU Code" name="skus[0][code]"
           value="{{ old('skus.0.code', $product->masterSku->code ?? '') }}" />
         </div>
        </div>

        <div class="row">
         <div class="col-12 col-md-4">
          <x-common-form-input :title="__('panel/product.model')" name="skus[0][model]"
           value="{{ old('skus.0.model', $product->masterSku->model ?? '') }}" />
         </div>
         <div class="col-12 col-md-4">
          <x-common-form-input :title="__('panel/product.origin_price')" name="skus[0][origin_price]"
           value="{{ old('skus.0.origin_price', $product->masterSku->origin_price ?? '') }}" />
         </div>
            @hookinsert('panel.product.edit.sku.single.input.item.price.after')
        </div>
       </div>
       @include('panel::products._form_variant')
        @hookinsert('panel.product.edit.form_variant.after')
       @include('panel::products._form_attribute')
      </div>
      <div class="tab-pane fade mt-3" id="addition-tab-pane" role="tabpanel" aria-labelledby="addition-tab" tabindex="3">
       <div class="d-flex flex-column flex-sm-row gap-3">
        <div class="col-12 col-sm-6">
         <x-common-form-select :title="__('panel/product.tax_class')" name="tax_class_id"
          :value="old('tax_class_id', $product->tax_class_id ?? 0)" :options="$tax_classes" key="id" label="name" />
         <x-common-form-input :title="__('panel/product.weight')" name="weight"
          :value="old('weight', $product->weight ?? '')" :placeholder="__('panel/product.weight')" />
         <div class="pt-2"></div>
         <x-common-form-select :title="__('panel/product.weight_class')" name="weight_class"
          :value="old('weight_class', $product->weight_class ?? 0)" :options="$weightClasses" key="id" label="name" />
         <x-common-form-select :title="__('panel/product.brand')" name="brand_id"
          :value="old('brand_id', $product->brand_id ?? 0)" :options="$brands" key="id" label="name" />
         <x-common-form-input :title="__('panel/product.position')" name="position"
          :value="old('position', $product->position ?? '')" :placeholder="__('panel/product.position')" />
        </div>
        <div class="col-12 col-sm-6">
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
              <input type="checkbox" name="categories[]" value="{{ $category->id }}" {{ in_array($category->id,
              old('categories', $product->categories->pluck('id')->toArray())) ? 'checked' : '' }}>
              <span class="name">{{ $category->translation->name }}</span>
             </label>
            </li>
            @endforeach
           </ul>
           @endif
          </div>
         </x-panel::form.row>
         <x-common-form-input :title="__('panel/product.sales')" name="sales"
          :value="old('sales', $product->sales ?? '')" :placeholder="__('panel/product.sales')" />
         <x-common-form-input :title="__('panel/product.viewed')" name="viewed"
          :value="old('viewed', $product->viewed ?? '')" :placeholder="__('panel/product.viewed')" />
        </div>
       </div>
       <x-common-form-switch-radio :title="__('panel/product.is_virtual')" name="is_virtual"
        :value="old('is_virtual', $product->is_virtual ?? false)" />
      </div>
      <div class="tab-pane fade mt-3" id="seo-tab-pane" role="tabpanel" aria-labelledby="seo-tab" tabindex="4">
       <x-common-form-input :title="__('panel/common.slug')" name="slug" :value="old('slug', $product->slug ?? '')"
        :placeholder="__('panel/common.slug')" column="product_slug" generate="true" />
       <ul class="nav nav-tabs mt-3 " id="myTab" role="tablist">
        @foreach (locales() as $locale)
        @php($localeCode = $locale->code)
        @php($localeName = $locale->name)
        <li class="nav-item" role="presentation">
         <button class="nav-link d-flex {{ $loop->first ? 'active' : '' }}" id="{{ $localeCode }}-tab"
          data-bs-toggle="tab" data-bs-target="#{{ $localeCode }}-tab-pane" type="button" role="tab"
          aria-controls="{{ $localeCode }}-tab-pane" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
          <div class="wh-20 me-2">
           <img src="{{ image_origin($locale->image) }}" class="img-fluid">
          </div>
          {{ $localeName }}
         </button>
        </li>
        @endforeach
       </ul>

       <div class="tab-content border p-2" id="myTabContent">
        @foreach (locales() as $locale)
        @php($localeCode = $locale->code)
        @php($localeName = $locale->name)
        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $localeCode }}-tab-pane"
         role="tabpanel" aria-labelledby="{{ $localeCode }}-tab" tabindex="0">
         <div class="mb-3">
          <!-- Hidden Locale Field -->
          <input type="hidden" name="translations[{{$localeCode}}][locale]" value="{{ $localeCode }}">

          <!-- Summary Field -->
          <x-common-form-textarea title="{{ __('panel/product.summary') }}"
           name="translations[{{$localeCode}}][summary]"
           value="{{ old('translations.' . $localeCode . '.summary', $product->translate($localeCode, 'summary')) }}"
           placeholder="{{ __('panel/product.summary') }}" column="product_summary" generate="true"
           description="{{ __('panel/product.keyword_ai') }}" />

          <!-- Selling Point Field -->
          <x-common-form-textarea title="{{ __('panel/product.selling_point') }}"
           name="translations[{{$localeCode}}][selling_point]"
           value="{{ old('translations.' . $localeCode . '.selling_point', $product->translate($localeCode, 'selling_point')) }}"
           placeholder="{{ __('panel/product.selling_point') }}" column="product_selling_point" generate="true"
           description="{{ __('panel/product.describe') }}" />

          <!-- Meta Title Field -->
          <x-common-form-input title="{{ __('panel/common.meta_title') }}"
           name="translations[{{$localeCode}}][meta_title]"
           value="{{ old('translations.' . $localeCode . '.meta_title', $product->translate($localeCode, 'meta_title')) }}"
           placeholder="{{ __('panel/common.meta_title') }}" column="product_title" generate="true" />

          <!-- Meta Description Field -->
          <x-common-form-textarea title="{{ __('panel/common.meta_description') }}"
           name="translations[{{$localeCode}}][meta_description]"
           value="{{ old('translations.' . $localeCode . '.meta_description', $product->translate($localeCode, 'meta_description')) }}"
           placeholder="{{ __('panel/common.meta_description') }}" column="product_description" generate="true" />

          <!-- Meta Keywords Field -->
          <x-common-form-input title="{{ __('panel/common.meta_keywords') }}"
           name="translations[{{$localeCode}}][meta_keywords]"
           value="{{ old('translations.' . $localeCode . '.meta_keywords', $product->translate($localeCode, 'meta_keywords')) }}"
           placeholder="{{ __('panel/common.meta_keywords') }}" column="product_keywords" generate="true" />
         </div>
        </div>
        @endforeach
       </div>
      </div>
      <div class="tab-pane fade mt-3 col-md-6" id="relation-tab-pane" role="tabpanel" aria-labelledby="relation-tab" tabindex="5">
       <x-panel-form-autocomplete-list name="related_ids[]" :value="old('related_ids', $product->relations->pluck('relation_id')->toArray() ?? [])" placeholder="{{ __('panel/product.searching_products') }}"
                                       title="{{__('panel/product.related_products')}}" api="/api/panel/products" />
      </div>
      @hookinsert('panel.product.edit.tab.pane.bottom')
     </div>
    </div>
   </div>
  </div>
 </div>
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
