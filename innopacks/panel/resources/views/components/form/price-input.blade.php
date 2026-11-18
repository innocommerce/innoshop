@php
  $unit    = $addon !== '' ? $addon : default_currency_unit();
  $posLeft = default_currency_unit_position() === 'left';
@endphp
<x-panel::form.row :title="$title" :required="$required" :column="''" :generate="false" :translate="false">
  <div class="input-group">
    @if ($unit !== '' && $posLeft)
      <span class="input-group-text">{{ $unit }}</span>
    @endif
    <input type="text" name="{{ $name }}" class="form-control {{ $error ? 'is-invalid' : '' }}"
           value="{{ $value }}" placeholder="{{ $placeholder ?: $title }}" @if ($required) required @endif />
    @if ($unit !== '' && ! $posLeft)
      <span class="input-group-text">{{ $unit }}</span>
    @endif
  </div>

  <span class="invalid-feedback" role="alert">
    @if ($error)
      {{ $error }}
    @else
      {{ __('front/common.error_required', ['name' => $title]) }}
    @endif
  </span>
</x-panel::form.row>
