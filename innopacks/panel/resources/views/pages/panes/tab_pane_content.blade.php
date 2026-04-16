<div class="tab-pane fade mt-3" id="content-tab-pane" role="tabpanel" aria-labelledby="content-tab" tabindex="0">

  <ul class="nav nav-tabs mb-3" id="content-tabs" role="tablist">
    @foreach (locales() as $locale)
      @php($localeCode = $locale->code)
      <li class="nav-item" role="presentation">
        <button class="nav-link d-flex {{ $loop->first ? 'active' : '' }}"
                data-bs-toggle="tab"
                data-bs-target="#tab-contentx-{{ $localeCode }}" type="button">
          <div class="wh-20 me-2">
            <img src="{{ image_origin($locale->image) }}" class="img-fluid {{ default_locale_class($locale->code) }}">
          </div>
          {{ $locale->name }}
        </button>
      </li>
    @endforeach
  </ul>

  <div class="tab-content">
    @foreach (locales() as $locale)
      @php($localeCode = $locale->code)
      <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-contentx-{{ $localeCode }}">
        <input type="hidden" name="translations[{{ $localeCode }}][locale]" value="{{ $localeCode }}">
        <textarea rows="4" type="text" name="translations[{{ $localeCode }}][content]" class="tinymce" id="content-{{ $localeCode }}"
          placeholder="{{ __('panel/article.content') }}">{{ old('translations.' . $localeCode . '.content', $page->translate($localeCode, 'content')) }}</textarea>
      </div>
    @endforeach
  </div>

</div>
