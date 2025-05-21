<div class="tab-pane fade show active mt-3" id="basic-tab-pane" role="tabpanel" aria-labelledby="basic-tab"
     tabindex="0">
  <div class="mb-3 col-12 col-md-5">
    <div class="mb-1 fs-6">{{ __('panel/product.name') }}</div>
    @if(has_translator())
      <div
        class="d-flex align-items-center my-3 py-2 px-3 text-primary-emphasis bg-primary-subtle border border-primary-subtle rounded-3"
        style="white-space: nowrap;">
        <div class="d-flex align-items-center me-3">{{ __('panel/product.auto_translate') }}</div>
        <select id="source-locale" class="form-select form-select-sm">
          @foreach (locales() as $locale)
            <option value="{{ $locale->code }}">{{ $locale->name }}</option>
          @endforeach
        </select>
        <div class="px-1"><i class="bi bi-arrow-right"></i></div>
        <select id="target-locale" class="form-select form-select-sm">
          <option value="all">{{ __('panel/product.other_all') }}</option>
          @foreach (locales() as $locale)
            <option value="{{ $locale->code }}">{{ $locale->name }}</option>
          @endforeach
        </select>
        <button type="button" class="mx-2 btn btn-primary btn-custom-small btn-sm" id="translate-button">
          {{ __('panel/product.translate') }}
        </button>
      </div>
    @endif

    @foreach (locales() as $locale)
      @php($localeCode = $locale->code)
      @php($localeName = $locale->name)
      <div class="input-group">
        <div class="input-group-text">
          <div class="d-flex align-items-center wh-20">
            <img src="{{ image_origin($locale->image) }}"
                 class="img-fluid {{ default_locale_class($locale->code) }}"
                 alt="{{ $localeName }}">
          </div>
        </div>
        <input type="text" class="form-control" name="translations[{{ $localeCode }}][name]"
               value="{{ old('translations.' . $localeCode . '.name', $product->translate($localeCode, 'name')) }}"
               required placeholder="{{ __('panel/product.name') }}" aria-label="{{ $localeName }}"
               aria-describedby="basic-addon1" data-locale="{{ $localeCode }}">
      </div>
    @endforeach
    <div class="mt-1 text-muted small">
      <i class="bi bi-info-circle me-1"></i>{{ __('panel/product.name_required_hint') }}
    </div>
  </div>

  <x-common-form-images title="{{ __('panel/common.image') }}" name="images"
                        :values="old('images', $product->images ?? [])"/>

  <div class="row mt-5 mb-4">
    <div class="col-12">
      <div class="mb-3">
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="price_type" id="price_type_single" value="single"
                {{ (!$product->isMultiple()) ? 'checked' : '' }}>
          <label class="form-check-label" for="price_type_single">
            {{ __('panel/product.price_type_single') }}
          </label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="price_type" id="price_type_multiple" value="multiple"
                {{ ($product->isMultiple()) ? 'checked' : '' }}>
          <label class="form-check-label" for="price_type_multiple">
            {{ __('panel/product.price_type_multiple') }}
          </label>
        </div>
      </div>

      <div id="single_price_box" class="{{ $product->isMultiple() ? 'd-none' : '' }}">
        <div class="row">
          <div class="col-12 col-md-3">
            <x-common-form-input :title="__('panel/product.price')" name="skus[0][price]"
                               value="{{ old('skus.0.price', $product->masterSku->price ?? '') }}" required/>
              @hookinsert('panel.product.edit.sku.single.input.item.price.after')
          </div>
          <div class="col-12 col-md-3">
            <x-common-form-input :title="__('panel/product.quantity')" name="skus[0][quantity]"
                               value="{{ old('skus.0.quantity', $product->masterSku->quantity ?? '') }}" required/>
          </div>
          <div class="col-12 col-md-3">
            <x-common-form-input :title="__('panel/product.sku_code')" name="skus[0][code]"
                               value="{{ old('skus.0.code', $product->masterSku->code ?? '') }}" required/>
          </div>
          <div class="col-12 col-md-3">
            <x-common-form-input :title="__('panel/product.origin_price')" name="skus[0][origin_price]"
                               value="{{ old('skus.0.origin_price', $product->masterSku->origin_price ?? '') }}"/>
          </div>
          <input type="hidden" name="skus[0][model]" value="">
          <input type="hidden" name="skus[0][is_default]" value="1">
        </div>
      </div>
      
      <!-- 多规格部分 -->
      <div id="specifications_box" class="{{ !$product->isMultiple() ? 'd-none' : '' }}">
        <div class="alert alert-info mb-3" id="multi_spec_notice">
          <i class="bi bi-info-circle me-2"></i>
          {{ __('panel/product.multiple_spec_notice') }}
        </div>
        @include('panel::products.form._form_variant')
        @hookinsert('panel.product.edit.form_variant.after')
      </div>
    </div>
  </div>

  <x-common-form-switch-radio :title="__('panel/common.status')" name="active"
                              :value="old('active', $product->active ?? true)"/>
  @hookinsert('panel.product.edit.basic.after')
</div>
