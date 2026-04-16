@props([
    'modalId' => 'localeModal',
    'inputPrefix' => 'locale-input',
    'title' => '',
])

@php
    $defaultLocale = panel_locale_code();
    $allLocales = locales();
    $defaultLocaleObj = $allLocales->first(fn ($l) => $l->code === $defaultLocale);
    $defaultLocaleName = $defaultLocaleObj->name ?? $defaultLocale;
    $otherLocalesList = $allLocales->filter(fn ($l) => $l->code !== $defaultLocale);
    $hasTranslatorFlag = has_translator();
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="{{ $modalId }}-title">{{ $title }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        {{-- Primary locale row --}}
        <div class="locale-modal-row mb-3 p-2 rounded border border-primary">
          <div class="d-flex align-items-center gap-2 mb-1">
            <div class="d-flex align-items-center wh-20">
              <img src="/images/flag/{{ $defaultLocale }}.png" class="img-fluid" alt="{{ $defaultLocaleName }}">
            </div>
            <span class="fw-medium">{{ $defaultLocaleName }}</span>
            <span class="badge bg-primary">{{ __('panel/common.panel_locale') }}</span>
          </div>
          <input type="text" class="form-control" id="{{ $inputPrefix }}-{{ $defaultLocale }}"
                 placeholder="{{ __('common/base.name') }}">
        </div>

        {{-- Other locale rows --}}
        @foreach($otherLocalesList as $locale)
        <div class="locale-modal-row mb-3 p-2 rounded border">
          <div class="d-flex align-items-center gap-2 mb-1">
            <div class="d-flex align-items-center wh-20">
              <img src="/images/flag/{{ $locale->code }}.png" class="img-fluid" alt="{{ $locale->name }}">
            </div>
            <span class="fw-medium">{{ $locale->name }}</span>
            @if($hasTranslatorFlag)
            <button type="button" class="btn btn-sm btn-outline-primary ms-auto"
                    data-lm-translate="{{ $locale->code }}"
                    title="{{ $defaultLocaleName }} → {{ $locale->name }}">
              <i class="bi bi-translate"></i>
            </button>
            @endif
          </div>
          <input type="text" class="form-control" id="{{ $inputPrefix }}-{{ $locale->code }}"
                 placeholder="{{ __('common/base.name') }}">
        </div>
        @endforeach

        {{-- Slot for additional fields --}}
        {{ $slot }}
      </div>
      <div class="modal-footer d-flex justify-content-between">
        @if($hasTranslatorFlag)
        <button type="button" class="btn btn-outline-secondary" data-lm-fill>
          <i class="bi bi-translate me-1"></i>{{ __('panel/common.translate_empty') }}
        </button>
        @else
        <button type="button" class="btn btn-outline-secondary" data-lm-fill>
          <i class="bi bi-arrow-right-circle me-1"></i>{{ __('panel/common.copy_empty') }}
        </button>
        @endif
        <div>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common/base.cancel') }}</button>
          <button type="button" class="btn btn-primary ms-2" id="{{ $modalId }}-confirm">{{ __('panel/common.confirm') }}</button>
        </div>
      </div>
    </div>
  </div>
</div>
