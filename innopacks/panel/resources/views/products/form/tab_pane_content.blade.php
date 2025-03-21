<div class="tab-pane fade mt-4" id="translation-tab-pane" role="tabpanel"
     aria-labelledby="translation-tab" tabindex="1">
  <div class="mb-1 fs-6">{{ __('panel/product.content') }}</div>
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
      <button type="button" class="mx-2 btn btn-primary btn-custom-small btn-sm" id="translate-tab">
        {{ __('panel/product.translate') }}
      </button>
    </div>
  @endif
  <div class="d-flex justify-content-between">
    <ul class="nav nav-tabs" id="myTab" role="tablist">
      @foreach (locales() as $locale)
        @php($localeCode = $locale->code)
        @php($localeName = $locale->name)
        <li class="nav-item" role="presentation">
          <button class="nav-link d-flex {{ $loop->first ? 'active' : '' }}"
                  id="{{ $localeCode }}-tab" data-bs-toggle="tab"
                  data-bs-target="#{{ $localeCode }}-tab-pane" type="button" role="tab"
                  aria-controls="{{ $localeCode }}-tab-pane"
                  aria-selected="{{ $loop->first ? 'true' : 'false' }}">
            <div class="wh-20 me-2">
              <img src="{{ image_origin($locale->image) }}" class="img-fluid {{ default_locale_class($locale->code)}}">
            </div>
            {{ $localeName }}
          </button>
        </li>
      @endforeach
    </ul>
  </div>

  <div class="tab-content pt-1" id="myTabContent">
    @foreach (locales() as $locale)
      @php($localeCode = $locale->code)
      @php($localeName = $locale->name)
      <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
           id="{{ $localeCode }}-tab-pane" role="tabpanel" aria-labelledby="{{ $localeCode }}-tab"
           tabindex="0">
        <div class="mb-3">
          <input type="hidden" name="translations[{{ $localeCode }}][locale]"
                 value="{{ $localeCode }}">
          <x-common-form-rich-text name="translations[{{ $localeCode }}][content]"
                                   elID="content-{{ $localeCode }}"
                                   value="{{ old('translations.' . $localeCode . '.content', $product->translate($localeCode, 'content')) }}"
                                   required placeholder="{{ __('panel/product.describe') }}"
                                   data-locale="{{ $localeCode }}"/>
        </div>
      </div>
    @endforeach
  </div>
</div>
