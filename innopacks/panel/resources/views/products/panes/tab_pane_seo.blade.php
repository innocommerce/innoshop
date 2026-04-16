<div class="tab-pane fade mt-3" id="seo-tab-pane" role="tabpanel" aria-labelledby="seo-tab"
     tabindex="4">
  <x-common-form-input :title="__('panel/common.slug')" name="slug" :value="old('slug', $product->slug ?? '')"
                       :placeholder="__('panel/common.slug')"
                       column="product_slug" :generate="true"
                       description="{{ __('panel/common.slug_description') }}"/>

  <div class="mb-3">
    <label class="form-label">{{ __('panel/common.summary') }}</label>
    <x-common-form-locale-input
      name="summary"
      type="textarea"
      :translations="locale_field_data($product, 'summary')"
      :placeholder="__('panel/common.summary')"
      :description="__('panel/common.summary_description')"
    />
  </div>

  <div class="mb-3">
    <label class="form-label">{{ __('panel/product.selling_point') }}</label>
    <x-common-form-locale-input
      name="selling_point"
      type="textarea"
      :translations="locale_field_data($product, 'selling_point')"
      :placeholder="__('panel/product.selling_point')"
      :description="__('panel/product.selling_point_description')"
    />
  </div>

  <div class="mb-3">
    <label class="form-label">{{ __('panel/common.meta_title') }}</label>
    <x-common-form-locale-input
      name="meta_title"
      :translations="locale_field_data($product, 'meta_title')"
      :placeholder="__('panel/common.meta_title')"
      :description="__('panel/common.meta_title_description')"
    />
  </div>

  <div class="mb-3">
    <label class="form-label">{{ __('panel/common.meta_description') }}</label>
    <x-common-form-locale-input
      name="meta_description"
      type="textarea"
      :translations="locale_field_data($product, 'meta_description')"
      :placeholder="__('panel/common.meta_description')"
      :description="__('panel/common.meta_description_description')"
    />
  </div>

  <div class="mb-3">
    <label class="form-label">{{ __('panel/common.meta_keywords') }}</label>
    <x-common-form-locale-input
      name="meta_keywords"
      type="textarea"
      :translations="locale_field_data($product, 'meta_keywords')"
      :placeholder="__('panel/common.meta_keywords')"
      :description="__('panel/common.meta_keywords_description')"
    />
  </div>
</div>
