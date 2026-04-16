<x-panel::form.row :title="$title" :required="$required" :locale-code="$localeCode ?? ''" :column="$column" :generate="$generate" :translate="$translate">
  @if (!$multiple)
  <div>
    <textarea rows="4" type="text" name="{{ $name }}" class="form-control" @if ($required) required @endif placeholder="{{ $title }}" data-column="{{ $column ?? '' }}" data-lang="{{ $localeCode ?? '' }}">{{ $value }}</textarea>
    <span class="invalid-feedback" role="alert">
      请填写 {{ $title }}
    </span>
    @if ($description ?? '')
    <div class="mt-2 text-muted small">
      <i class="bi bi-info-circle me-1"></i>{!! $description !!}
    </div>
    @endif
  </div>
  @else
    @php
      $localeValues = [];
      $useTranslationsFormat = false;
      if (is_string($value)) {
        $value = [$value];
      }
      foreach (locales() as $loc) {
        $code = $loc->code;
        $val = null;

        if (is_object($value)) {
          $item = $value->where('locale', $code)->first();
          $val = $item->description ?? '';
          $useTranslationsFormat = true;
        } elseif (is_array($value)) {
          if (isset($value[$code])) {
            $val = $value[$code];
          } elseif (isset($value[0])) {
            $val = $value[0];
          }
        }

        $localeValues[$code] = (string) ($val ?? '');
      }
    @endphp

    <x-common-form-locale-input
      name="{{ $name }}"
      type="textarea"
      :translations="$localeValues"
      :required="$required"
      :label="$title"
      :placeholder="$title"
      :name-format="$useTranslationsFormat ? 'translations' : 'direct'"
      :description="$description"
    />
  @endif
  {{ $slot }}
</x-panel::form.row>
