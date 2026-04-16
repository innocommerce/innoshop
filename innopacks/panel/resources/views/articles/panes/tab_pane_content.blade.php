<div class="tab-pane fade mt-3" id="content-tab-pane" role="tabpanel" aria-labelledby="content-tab" tabindex="0">

  @if(has_translator())
    <div
      class="col-md-5 d-flex align-items-center my-3 py-2 px-3 text-primary-emphasis bg-primary-subtle border border-primary-subtle rounded-3"
      style="white-space: nowrap;">
      <div class="me-3">{{ __('panel/product.auto_translate') }}</div>
      <select id="source-tab" class="form-select form-select-sm">
        @foreach (locales() as $locale)
          <option value="{{ $locale->code }}">{{ $locale->name }}</option>
        @endforeach
      </select>
      <div class="px-1"><i class="bi bi-arrow-right"></i></div>
      <select id="target-tab" class="form-select form-select-sm">
        <option value="all">{{ __('panel/product.other_all') }}</option>
        @foreach (locales() as $locale)
          <option value="{{ $locale->code }}">{{ $locale->name }}</option>
        @endforeach
      </select>
      <button type="button" class="mx-2 btn btn-primary btn-custom-small btn-sm" id="translate-html">
        {{ __('panel/product.translate') }}
      </button>
    </div>
  @endif

  <ul class="nav nav-tabs mb-4" id="locales-content-tab" role="tablist">
    @foreach (locales() as $locale)
      <li class="nav-item" role="presentation">
        <button class="nav-link d-flex {{ $loop->first ? 'active' : '' }}" id="locale-{{ $locale->code }}-content-tab"
          data-bs-toggle="tab" data-bs-target="#locale-{{ $locale->code }}-content-pane" type="button"
          role="tab" aria-controls="locale-{{ $locale->code }}-content-pane"
          aria-selected="{{ $loop->first ? 'true' : 'false' }}">
          <div class="wh-20 me-2">
            <img src="{{ image_origin($locale->image) }}" class="img-fluid {{ default_locale_class($locale->code) }}">
          </div>
          {{ $locale->name }}
        </button>
      </li>
    @endforeach
  </ul>

  <div class="tab-content" id="locales-content-tabContent">
    @foreach (locales() as $locale)
      <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
        id="locale-{{ $locale->code }}-content-pane" role="tabpanel"
        aria-labelledby="locale-{{ $locale->code }}-content-tab" tabindex="0">

        <div class="mb-3">
          <input type="hidden" name="translations[{{ $locale->code }}][locale]" value="{{ $locale->code }}">
          <label class="form-label">{{ __('panel/article.content') }}</label>
          <textarea rows="16" type="text" name="translations[{{ $locale->code }}][content]"
            class="tinymce" id="content-{{ $locale->code }}"
            @if(is_setting_locale($locale->code)) required @endif
            placeholder="{{ __('panel/article.content') }}">{{ old('translations.' . $locale->code . '.content', $article->translate($locale->code, 'content')) }}</textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">{{ __('panel/article.locale_image') }}</label>
          <x-common-form-image name="translations[{{ $locale->code }}][image]"
            value="{{ old('translations.' . $locale->code . '.image', $article->translate($locale->code, 'image')) }}" />
          <div class="form-text">{{ __('panel/article.locale_image_description') }}</div>
        </div>

      </div>
    @endforeach
  </div>
</div>

@hookinsert('panel.article.edit.content.bottom')