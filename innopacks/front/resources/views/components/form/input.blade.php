<x-panel::form.row :title="$title" :required="$required">
  <input type="{{ $type }}" name="{{ $name }}" class="form-control {{ $error ? 'is-invalid' : '' }}" value="{{ $value }}" placeholder="{{ $placeholder ?: $title }}" @if ($required) required @endif>
    @if ($description)
    <div class="help-text font-size-12 lh-base">{!! $description !!}</div>
    @endif

  <span class="invalid-feedback" role="alert">
    @if ($error)
      {{ $error }}
    @else
    请填写 {{ $title }}
    @endif
  </span>
  {{ $slot }}
</x-panel::form.row>
