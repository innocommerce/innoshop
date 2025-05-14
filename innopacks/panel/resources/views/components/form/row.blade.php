<div class="form-row mb-3">
  @if ($title ?? false)
    <div class="row">
      <div class="col-6">
        <div class="col-form-label {{ isset($required) && $required ? 'required' : '' }}">{{ $title }}</div>
      </div>
      <div class="col-6 d-flex justify-content-end   align-items-center">
        @if ($translate ?? false)
          <div>
            <button type="button" class="btn btn-primary btn-sm translate-submit">
              {{ __('panel/product.translate') }}
            </button>
          </div>
          <div>
            <select class="form-select form-select-sm">
              <option value="all">{{ __('panel/product.other_all') }}</option>
              @foreach (locales() as $locale)
                <option value="{{ $locale->code }}">{{ $locale->name }}</option>
              @endforeach
            </select>
          </div>
        @endif
        @if ($generate ?? false)
          <div class="ms-3">
            <a class="btn btn-success btn-sm ai-generate" data-column="{{ $column ?? '' }}" data-lang="{{ $localeCode ?? '' }}">
              {{ __('panel/common.ai_generate') }}
            </a>
          </div>
        @endif
      </div>
    </div>
  @endif

  <div class="flex-fill">
    {{ $slot }}
  </div>
</div>
