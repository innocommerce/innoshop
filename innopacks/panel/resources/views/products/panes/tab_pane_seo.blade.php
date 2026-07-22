<div class="tab-pane fade mt-3" id="seo-tab-pane" role="tabpanel" aria-labelledby="seo-tab"
     tabindex="4">

  <div class="mb-3">
    <label class="form-label">{{ __('panel/common.slug') }}</label>
    <div class="input-group">
      <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
      <input type="text" name="slug" class="form-control"
             value="{{ old('slug', $product->slug ?? '') }}"
             placeholder="{{ __('panel/common.slug') }}"
             maxlength="60"
             data-column="product_slug">
      <button type="button" class="btn btn-outline-secondary ai-generate"
              data-column="product_slug"
              title="{{ __('panel/common.ai_generate') }}">
        <i class="bi bi-stars"></i>
      </button>
    </div>
    <div class="text-secondary mt-1"><small>{{ __('panel/common.slug_description') }}</small></div>
  </div>

  <div class="mb-3">
    <label class="form-label">{{ __('panel/common.summary') }}</label>
    <x-common-form-locale-input
      name="summary"
      type="textarea"
      :translations="locale_field_data($product, 'summary')"
      :placeholder="__('panel/common.summary')"
      :description="__('panel/common.summary_description')"
      :generate="true"
      column="product_summary"
      entity-type="product"
      :entity-id="$product->id ?? 0"
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
      :generate="true"
      column="product_selling_point"
      entity-type="product"
      :entity-id="$product->id ?? 0"
    />
  </div>

  <div class="mb-3">
    <label class="form-label">{{ __('panel/common.meta_title') }}</label>
    <x-common-form-locale-input
      name="meta_title"
      :translations="locale_field_data($product, 'meta_title')"
      :placeholder="__('panel/common.meta_title')"
      :description="__('panel/common.meta_title_description')"
      :generate="true"
      column="product_title"
      entity-type="product"
      :entity-id="$product->id ?? 0"
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
      :generate="true"
      column="product_description"
      entity-type="product"
      :entity-id="$product->id ?? 0"
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
      :generate="true"
      column="product_keywords"
      entity-type="product"
      :entity-id="$product->id ?? 0"
    />
  </div>
</div>
