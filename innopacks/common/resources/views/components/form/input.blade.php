<x-panel::form.row :title="$title" :required="$required" :disabled="$disabled" :readonly="$readonly" :locale-code="$localeCode ?? ''" :column="$column" :generate="$generate" :translate="$translate">
  @if (!$multiple)
    <input type="{{ $type }}" name="{{ $name }}" class="form-control {{ $error ? 'is-invalid' : '' }}"
           value="{{ $value }}" placeholder="{{ $placeholder ?: $title }}" @if ($required) required
           @endif @if($disabled) disabled @endif @if($readonly) readonly @endif 
           data-column="{{ $column ?? '' }}" data-lang="{{ $localeCode ?? '' }}" />

    @if ($description ?? '')
      <div class="text-secondary"><small>{!! $description !!}</small></div>
    @endif

    <span class="invalid-feedback" role="alert">
      @if ($error)
        {{ $error }}
      @else
        {{ __('front/common.error_required', ['name' => $title]) }}
      @endif
    </span>
  @else
    <ul class="nav nav-tabs mb-2" id="myTab" role="tablist">
      @foreach (locales() as $locale)
        <li class="nav-item" role="presentation">
          <button class="nav-link d-flex {{ $loop->first ? 'active' : ''}}" id="{{ $locale['code'] }}" data-bs-toggle="tab"
                  data-bs-target="#{{ $name }}-{{ $locale['code'] }}-pane" type="button">
            <div class="wh-20 me-2">
              <img src="{{ asset('images/flag/'. $locale['code'].'.png') }}" class="img-fluid {{ default_locale_class($locale->code) }}">
            </div>
            {{ $locale['name'] }}
          </button>
        </li>
      @endforeach
    </ul>

    <div class="tab-content" id="">
      @foreach (locales() as $locale)
        <div class="tab-pane fade {{ $loop->first ? 'show active' : ''}}" id="{{ $name }}-{{ $locale['code'] }}-pane"
             role="tabpanel" aria-labelledby="{{ $locale['code'] }}">
          @if(empty($value) || (is_array($value) && !isset($value[$locale['code']])))
            <input type="{{ $type }}" name="{{ $name }}[{{ $locale['code'] }}]"
                   class="form-control {{ $error ? 'is-invalid' : '' }}" value=""
                   placeholder="{{ $placeholder ?: $title }}" @if ($required) required @endif @if($disabled) disabled
                   @endif @if($readonly) readonly @endif />
          @elseif(is_array($value) && (is_string($value[$locale['code']]) || $value[$locale['code']] == null))
            <input type="{{ $type }}" name="{{ $name }}[{{ $locale['code'] }}]"
                   class="form-control {{ $error ? 'is-invalid' : '' }}" value="{{ $value[$locale['code']] ?? '' }}"
                   placeholder="{{ $placeholder ?: $title }}" @if ($required) required @endif @if($disabled) disabled
                   @endif @if($readonly) readonly @endif />
          @elseif(is_array($value) && is_array($value[$locale['code']]))
            <input type="hidden" name="translations[{{ $locale['code'] }}][locale]" value="{{ $locale['code'] }}">
            <input type="{{ $type }}" name="translations[{{ $locale['code'] }}][{{ $name }}]"
                   class="form-control {{ $error ? 'is-invalid' : '' }}"
                   value="{{ $value[$locale['code']]['name'] ?? '' }}" placeholder="{{ $placeholder ?: $title }}"
                   @if ($required) required @endif @if($disabled) disabled @endif @if($readonly) readonly @endif />
          @elseif(is_object($value))
            @php ($o_value = $value->where('locale', $locale['code'])->first())
            <input type="hidden" name="translations[{{ $locale['code'] }}][locale]" value="{{ $locale['code'] }}">
            <input type="{{ $type }}" name="translations[{{ $locale['code'] }}][{{ $name }}]"
                   class="form-control {{ $error ? 'is-invalid' : '' }}" value="{{ $o_value->name ?? '' }}"
                   placeholder="{{ $placeholder ?: $title }}" @if ($required) required @endif @if($disabled) disabled
                   @endif @if($readonly) readonly @endif />
          @endif
          @if ($description)
            <div class="mt-2 text-muted small">
              <i class="bi bi-info-circle me-1"></i>{!! $description !!}
            </div>
          @endif

          <span class="invalid-feedback" role="alert">
          @if ($error)
              {{ $error }}
            @else
              {{ __('front/common.error_required', ['name' => $title]) }}
            @endif
        </span>
        </div>
      @endforeach
    </div>
  @endif

  {{ $slot }}
</x-panel::form.row>
