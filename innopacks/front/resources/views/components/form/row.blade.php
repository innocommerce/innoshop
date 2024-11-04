<div class="form-row d-md-flex mb-3">
  <div class="col-form-label wp-200 text-end pe-3 {{ isset($required) && $required ? 'required' : '' }}">{{ $title ?? '' }}</div>
  <div class="flex-fill w-max-{{ $width ?? '500' }}">
    {{ $slot }}
  </div>
</div>