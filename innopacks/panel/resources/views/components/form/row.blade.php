<div class="form-row mb-3">
  @if ($title ?? false)
  <div class="col-form-label pe-3 {{ isset($required) && $required ? 'required' : '' }}">{{ $title }}</div>
  @endif
  <div class="flex-fill">
    {{ $slot }}
  </div>
</div>