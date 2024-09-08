@pushOnce('header')
<script src="{{ asset('vendor/laydate/laydate.js') }}"></script>
<style>
  .layui-laydate .layui-this {
    background-color: #1165ff !important;
  }
</style>
@endpushOnce

<x-panel::form.row :title="$title" :required="$required">
  <div class="{{ $type }}">
    <input name="{{ $name }}" class="form-control {{ $error ? 'is-invalid' : '' }}" value="{{ $value }}" placeholder="{{ $placeholder ?: $title }}" @if ($required) required @endif>
      @if ($description)
      <div class="help-text font-size-12 lh-base">{!! $description !!}</div>
      @endif

    <span class="invalid-feedback" role="alert">
      @if ($error)
        {{ $error }}
      @else
      {{ __('front/common.error_required', ['name' => $title]) }}
      @endif
    </span>
    {{ $slot }}
  </div>
</x-panel::form.row>
