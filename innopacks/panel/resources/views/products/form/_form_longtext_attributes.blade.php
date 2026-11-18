{{-- Long-text attributes (textarea/richtext): per-locale editors instead of
     the specification table; each value can opt into storefront display. --}}
@pushonce('header')
  <script src="{{ asset('vendor/tinymce/5.9.1/tinymce.min.js') }}"></script>
@endpushonce

<div class="card mb-3">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/product.content_attributes') }}</h5>
  </div>
  <div class="card-body">
    @foreach($longtext_attributes as $longAttr)
      <div class="mb-4">
        <label class="form-label fw-bold">{{ $longAttr['name'] }}</label>

        @if(has_translator())
          <div
            class="col-md-5 d-flex align-items-center text-nowrap my-3 py-2 px-3 text-primary-emphasis bg-primary-subtle border border-primary-subtle rounded-3">
            <div class="me-3">{{ __('panel/product.auto_translate') }}</div>
            <select class="form-select form-select-sm long-attr-source-tab"
                    data-attr-id="{{ $longAttr['id'] }}">
              @foreach (locales() as $locale)
                <option value="{{ $locale->code }}">{{ $locale->name }}</option>
              @endforeach
            </select>
            <div class="px-1"><i class="bi bi-arrow-right"></i></div>
            <select class="form-select form-select-sm long-attr-target-tab"
                    data-attr-id="{{ $longAttr['id'] }}">
              <option value="all">{{ __('panel/product.other_all') }}</option>
              @foreach (locales() as $locale)
                <option value="{{ $locale->code }}">{{ $locale->name }}</option>
              @endforeach
            </select>
            <button type="button"
                    class="mx-2 btn btn-primary btn-custom-small btn-sm long-attr-translate-html"
                    data-attr-id="{{ $longAttr['id'] }}">
              {{ __('panel/product.translate') }}
            </button>
          </div>
        @endif

        <ul class="nav nav-tabs mb-2" role="tablist">
          @foreach (locales() as $locale)
            <li class="nav-item" role="presentation">
              <button class="nav-link {{ $loop->first ? 'active' : '' }}" type="button" data-bs-toggle="tab"
                data-bs-target="#long-attr-{{ $longAttr['id'] }}-{{ $locale->code }}-pane"
                id="long-attr-{{ $longAttr['id'] }}-{{ $locale->code }}-tab">
                <span class="wh-20 me-2 d-inline-block"><img src="{{ asset('images/flags/' . $locale->code . '.svg') }}" class="img-fluid"></span>
                {{ $locale->name }}
              </button>
            </li>
          @endforeach
        </ul>

        <div class="tab-content">
          @foreach (locales() as $locale)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
              id="long-attr-{{ $longAttr['id'] }}-{{ $locale->code }}-pane" role="tabpanel"
              aria-labelledby="long-attr-{{ $longAttr['id'] }}-{{ $locale->code }}-tab">
              @if($longAttr['type'] === \InnoShop\Common\Models\Attribute::TYPE_RICHTEXT)
                <textarea id="long-attr-{{ $longAttr['id'] }}-{{ $locale->code }}-editor"
                  rows="8" class="tinymce" data-is-rich-text="true"
                  name="long_attributes[{{ $longAttr['id'] }}][value_text][{{ $locale->code }}]">{{ $longAttr['values'][$locale->code] ?? '' }}</textarea>
              @else
                <textarea id="long-attr-{{ $longAttr['id'] }}-{{ $locale->code }}-editor"
                  rows="4" class="form-control w-max-1000"
                  name="long_attributes[{{ $longAttr['id'] }}][value_text][{{ $locale->code }}]">{{ $longAttr['values'][$locale->code] ?? '' }}</textarea>
              @endif
            </div>
          @endforeach
        </div>

        <input type="hidden" name="long_attributes[{{ $longAttr['id'] }}][is_visible]" value="0">
        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" value="1"
                 id="long-attr-visible-{{ $longAttr['id'] }}"
                 name="long_attributes[{{ $longAttr['id'] }}][is_visible]"
                 @if($longAttr['is_visible']) checked @endif>
          <label class="form-check-label" for="long-attr-visible-{{ $longAttr['id'] }}">
            {{ __('panel/attribute.show_on_detail') }}
          </label>
        </div>
      </div>
    @endforeach
  </div>
</div>
