<div class="tab-pane fade mt-3" id="seo-tab-pane" role="tabpanel" aria-labelledby="seo-tab" tabindex="0">
        <x-common-form-input title="{{ __('panel/common.slug') }}" name="slug" :value="old('slug', $article->slug ?? '')"
          placeholder="{{ __('panel/common.slug') }}" column="article_slug" :generate="true"
          description="{{ __('panel/common.slug_description') }}" />

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

          <x-common-form-input title="{{ __('panel/common.meta_title') }}"
            name="translations[{{ $localeCode }}][meta_title]"
            value="{{ old('translations.' . $localeCode . '.meta_title', $article->translate($localeCode, 'meta_title')) }}"
            :translate="true" column="article_title" :generate="true"
            description="{{ __('panel/common.meta_title_description') }}"
            :locale-code="$localeCode" />

          <x-common-form-textarea title="{{ __('panel/common.meta_description') }}"
            name="translations[{{ $localeCode }}][meta_description]"
            value="{{ old('translations.' . $localeCode . '.meta_description', $article->translate($localeCode, 'meta_description')) }}"
            :translate="true" column="article_description" :generate="true"
            description="{{ __('panel/common.meta_description_description') }}"
            :locale-code="$localeCode" />

          <x-common-form-textarea title="{{ __('panel/common.meta_keywords') }}"
            name="translations[{{ $localeCode }}][meta_keywords]"
            value="{{ old('translations.' . $localeCode . '.meta_keywords', $article->translate($localeCode, 'meta_keywords')) }}"
            :translate="true" column="article_keywords" :generate="true"
            description="{{ __('panel/common.meta_keywords_description') }}"
            :locale-code="$localeCode" />
        </div>
      </div>
    @endforeach
        </div>
</div>