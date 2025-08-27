<div class="tab-pane fade show active mt-3" id="basic-tab-pane" role="tabpanel" aria-labelledby="basic-tab" tabindex="0">

  <div class="mb-3 col-12 col-md-8">
    <div class="mb-1 fs-6">{{ __('panel/article.title') }}</div>
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
      <div class="input-group mb-2">
        <div class="input-group-text">
          <div class="d-flex align-items-center wh-20">
            <img src="{{ image_origin($locale->image) }}"
                  class="img-fluid {{ default_locale_class($locale->code) }}"
                  alt="{{ $localeName }}">
          </div>
        </div>
        <input type="text" class="form-control" name="translations[{{ $localeCode }}][title]"
                value="{{ old('translations.' . $localeCode . '.title', $article->translate($localeCode, 'title')) }}"
                @if(is_setting_locale($localeCode)) required @endif placeholder="{{ __('panel/article.title') }}" aria-label="{{ $localeName }}"
                aria-describedby="basic-addon1" data-locale="{{ $localeCode }}">
        <input type="hidden" name="translations[{{ $localeCode }}][locale]" value="{{ $localeCode }}">
      </div>
    @endforeach
    <div class="mt-1 text-muted small">
      <i class="bi bi-info-circle me-1"></i>{{ __('panel/article.title_required_hint') }}
    </div>
  </div>

  <div class="mb-3 col-12 col-md-8">
    <x-common-form-image title="{{ __('panel/article.main_image') }}" name="image"
                        value="{{ old('image', $article->image ?? '') }}"/>
    <div class="form-text">{{ __('panel/article.main_image_description') }}</div>
  </div>

  <x-common-form-switch-radio :title="__('panel/common.whether_enable')" name="active"
                              :value="old('active', $article->active ?? true)" />

</div>

@hookinsert('panel.article.edit.basic.bottom')