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
    @php
      // Normalize value to [localeCode => value] format
      $localeValues = [];
      $useTranslationsFormat = false;
      foreach (locales() as $loc) {
        $code = $loc->code;
        $val = null;

        // Try old() first
        $oldVal = old($name . '.' . $code);
        if ($oldVal !== null) {
          $val = $oldVal;
        } elseif (is_object($value)) {
          // Eloquent Collection
          $item = $value->where('locale', $code)->first();
          $val = $item->name ?? ($item->value ?? null);
          $useTranslationsFormat = true;
        } elseif (is_array($value)) {
          if (isset($value[$code])) {
            if (is_array($value[$code])) {
              $val = $value[$code]['name'] ?? ($value[$code]['value'] ?? '');
              $useTranslationsFormat = true;
            } else {
              $val = $value[$code];
            }
          } elseif (isset($value[0]) && is_string($value[0])) {
            $val = $value[0];
          }
        } elseif (is_string($value) && $value !== '') {
          $val = $value;
        }

        $localeValues[$code] = (string) ($val ?? '');
      }
    @endphp

    <x-common-form-locale-input
      name="{{ $name }}"
      :translations="$localeValues"
      :required="$required"
      :label="$title"
      :placeholder="$placeholder ?: $title"
      :name-format="$useTranslationsFormat ? 'translations' : 'direct'"
      :description="$description"
    />
  @endif

  {{ $slot }}
</x-panel::form.row>
