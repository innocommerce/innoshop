<div class="tab-pane fade mt-3" id="content-tab-pane" role="tabpanel" aria-labelledby="content-tab" tabindex="0">

  <div class="mb-1 fs-6 fw-medium">{{ __('panel/page.content') }}</div>

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

  <ul class="nav nav-tabs mb-2" id="content-tabs" role="tablist">
    @foreach (locales() as $locale)
      @php($localeCode = $locale->code)
      <li class="nav-item" role="presentation">
        <button class="nav-link d-flex {{ $loop->first ? 'active' : '' }}"
                id="locale-{{ $localeCode }}-content-tab"
                data-bs-toggle="tab"
                data-bs-target="#tab-contentx-{{ $localeCode }}" type="button"
                role="tab" aria-controls="tab-contentx-{{ $localeCode }}"
                aria-selected="{{ $loop->first ? 'true' : 'false' }}">
          <div class="wh-20 me-2">
            <img src="{{ image_origin($locale->image) }}" class="img-fluid {{ default_locale_class($locale->code) }}">
          </div>
          {{ $locale->name }}
        </button>
      </li>
    @endforeach
  </ul>

  <div class="tab-content pt-1">
    @foreach (locales() as $locale)
      @php($localeCode = $locale->code)
      <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-contentx-{{ $localeCode }}"
           role="tabpanel" aria-labelledby="locale-{{ $localeCode }}-content-tab" tabindex="0">
        <input type="hidden" name="translations[{{ $localeCode }}][locale]" value="{{ $localeCode }}">
        <x-common-form-rich-text name="translations[{{ $localeCode }}][content]"
                                 elID="content-{{ $localeCode }}"
                                 :value="old('translations.' . $localeCode . '.content', $page->translate($localeCode, 'content'))"
                                 maxlength="100000"
                                 data-locale="{{ $localeCode }}"
                                 :generate="true"
                                 column="page_content"
                                 entity-type="page"
                                 :entity-id="$page->id ?? 0" />
      </div>
    @endforeach
  </div>

</div>
