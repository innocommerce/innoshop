<div class="tab-pane fade mt-3" id="seo-tab-pane" role="tabpanel" aria-labelledby="seo-tab" tabindex="0">
  <div class="mb-3">
    <label class="form-label">{{ __('panel/common.slug') }}</label>
    <div class="input-group">
      <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
      <input type="text" name="slug" class="form-control"
             value="{{ old('slug', $article->slug ?? '') }}"
             placeholder="{{ __('panel/common.slug') }}"
             maxlength="60"
             data-column="article_slug">
      <button type="button" class="btn btn-outline-secondary ai-generate"
              data-column="article_slug"
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
      :translations="locale_field_data($article, 'summary')"
      :placeholder="__('panel/common.summary')"
      :description="__('panel/common.summary_description')"
      :generate="true"
      column="article_summary"
      entity-type="article"
      :entity-id="$article->id ?? 0"
    />
  </div>

  <div class="mb-3">
    <label class="form-label">{{ __('panel/common.meta_title') }}</label>
    <x-common-form-locale-input
      name="meta_title"
      :translations="locale_field_data($article, 'meta_title')"
      :placeholder="__('panel/common.meta_title')"
      :description="__('panel/common.meta_title_description')"
      :generate="true"
      column="article_title"
      entity-type="article"
      :entity-id="$article->id ?? 0"
    />
  </div>

  <div class="mb-3">
    <label class="form-label">{{ __('panel/common.meta_description') }}</label>
    <x-common-form-locale-input
      name="meta_description"
      type="textarea"
      :translations="locale_field_data($article, 'meta_description')"
      :placeholder="__('panel/common.meta_description')"
      :description="__('panel/common.meta_description_description')"
      :generate="true"
      column="article_description"
      entity-type="article"
      :entity-id="$article->id ?? 0"
    />
  </div>

  <div class="mb-3">
    <label class="form-label">{{ __('panel/common.meta_keywords') }}</label>
    <x-common-form-locale-input
      name="meta_keywords"
      type="textarea"
      :translations="locale_field_data($article, 'meta_keywords')"
      :placeholder="__('panel/common.meta_keywords')"
      :description="__('panel/common.meta_keywords_description')"
      :generate="true"
      column="article_keywords"
      entity-type="article"
      :entity-id="$article->id ?? 0"
    />
  </div>
</div>
