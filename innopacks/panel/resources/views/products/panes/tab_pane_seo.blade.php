<div class="tab-pane fade mt-3" id="seo-tab-pane" role="tabpanel" aria-labelledby="seo-tab"
     tabindex="4">
  <x-common-form-input :title="__('panel/common.slug')" name="slug" :value="old('slug', $product->slug ?? '')"
                       :placeholder="__('panel/common.slug')"
                       column="product_slug" :generate="true"/>

  <ul class="nav nav-tabs mt-3" id="seo-myTab" role="tablist">
    @foreach (locales() as $locale)
      @php($localeCode = $locale->code)
      @php($localeName = $locale->name)
      <li class="nav-item" role="presentation">
        <button class="nav-link d-flex {{ $loop->first ? 'active' : '' }}"
                id="seo-{{ $localeCode }}-tab" data-bs-toggle="tab"
                data-bs-target="#seo-{{ $localeCode }}-tab-pane" type="button" role="tab"
                aria-controls="seo-{{ $localeCode }}-tab-pane"
                aria-selected="{{ $loop->first ? 'true' : 'false' }}">
          <div class="wh-20 me-2">
            <img src="{{ image_origin($locale->image) }}" class="img-fluid {{ default_locale_class($locale->code) }}">
          </div>
          {{ $localeName }}
        </button>
      </li>
    @endforeach
  </ul>
  <div class="tab-content border p-2" id="seo-myTabContent">
    @foreach (locales() as $locale)
      @php($localeCode = $locale->code)
      @php($localeName = $locale->name)
      <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
           id="seo-{{ $localeCode }}-tab-pane" role="tabpanel"
           aria-labelledby="seo-{{ $localeCode }}-tab" tabindex="0">
        <div class="mb-3 locale-code" data-locale-code={{ $localeCode }}>
          <input type="hidden" name="translations[{{ $localeCode }}][locale]"
                 value="{{ $localeCode }}">

          <x-common-form-textarea title="{{ __('panel/product.summary') }}"
                                  name="translations[{{ $localeCode }}][summary]"
                                  value="{{ old('translations.' . $localeCode . '.summary', $product->translate($localeCode, 'summary')) }}"
                                  placeholder="{{ __('panel/product.summary') }}" column="product_summary"
                                  :generate="true"
                                  :translate="true" description="{{ __('panel/product.keyword_ai') }}"/>

          <x-common-form-textarea title="{{ __('panel/product.selling_point') }}"
                                  name="translations[{{ $localeCode }}][selling_point]"
                                  value="{{ old('translations.' . $localeCode . '.selling_point', $product->translate($localeCode, 'selling_point')) }}"
                                  placeholder="{{ __('panel/product.selling_point') }}" column="product_selling_point"
                                  :generate="true" :translate="true" description="{{ __('panel/product.describe') }}"/>

          <x-common-form-input title="{{ __('panel/common.meta_title') }}"
                               name="translations[{{ $localeCode }}][meta_title]"
                               value="{{ old('translations.' . $localeCode . '.meta_title', $product->translate($localeCode, 'meta_title')) }}"
                               :translate="true" placeholder="{{ __('panel/common.meta_title') }}"
                               column="product_title"
                               :generate="true"/>

          <x-common-form-textarea title="{{ __('panel/common.meta_description') }}"
                                  name="translations[{{ $localeCode }}][meta_description]"
                                  value="{{ old('translations.' . $localeCode . '.meta_description', $product->translate($localeCode, 'meta_description')) }}"
                                  placeholder="{{ __('panel/common.meta_description') }}" column="product_description"
                                  :translate="true" :generate="true"/>

          <x-common-form-input title="{{ __('panel/common.meta_keywords') }}"
                               name="translations[{{ $localeCode }}][meta_keywords]"
                               value="{{ old('translations.' . $localeCode . '.meta_keywords', $product->translate($localeCode, 'meta_keywords')) }}"
                               placeholder="{{ __('panel/common.meta_keywords') }}" column="product_keywords"
                               :translate="true" :generate="true"/>
        </div>
      </div>
    @endforeach
  </div>
</div>
