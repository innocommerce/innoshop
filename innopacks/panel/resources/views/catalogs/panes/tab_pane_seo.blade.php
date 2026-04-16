<div class="tab-pane fade mt-3" id="seo-tab-pane" role="tabpanel" aria-labelledby="seo-tab" tabindex="0">

  <x-common-form-input title="{{ __('panel/common.slug') }}" name="slug"
    :value="old('slug', $catalog->slug ?? '')"
    placeholder="{{ __('panel/common.slug') }}" />

  {{-- 摘要 (多语言) --}}
  <div class="mb-3">
    <label class="form-label">{{ __('panel/common.summary') }}</label>
    <x-common-form-locale-input
      name="summary"
      type="textarea"
      :translations="locale_field_data($catalog, 'summary')"
      :label="__('panel/common.summary')"
      :placeholder="__('panel/common.summary')"
    />
  </div>

  <div class="mb-3">
    <label class="form-label">{{ __('panel/setting.meta_title') }}</label>
    <x-common-form-locale-input
      name="meta_title"
      :translations="locale_field_data($catalog, 'meta_title')"
      :label="__('panel/setting.meta_title')"
      :placeholder="__('panel/setting.meta_title')"
    />
  </div>

  <div class="mb-3">
    <label class="form-label">{{ __('panel/setting.meta_keywords') }}</label>
    <x-common-form-locale-input
      name="meta_keywords"
      :translations="locale_field_data($catalog, 'meta_keywords')"
      :label="__('panel/setting.meta_keywords')"
      :placeholder="__('panel/setting.meta_keywords')"
    />
  </div>

  <div class="mb-3">
    <label class="form-label">{{ __('panel/setting.meta_description') }}</label>
    <x-common-form-locale-input
      name="meta_description"
      type="textarea"
      :translations="locale_field_data($catalog, 'meta_description')"
      :label="__('panel/setting.meta_description')"
      :placeholder="__('panel/setting.meta_description')"
    />
  </div>

</div>
