@push('header')
<script src="{{ asset('vendor/tinymce/5.9.1/tinymce.min.js') }}"></script>
@endpush

<x-panel::form.row :title="$title" :required="$required" width="1000">
  <textarea rows="4" type="text" name="{{ $name }}" class="tinymce" placeholder="{{ $title }}">{{ $value }}</textarea>
  {{ $slot }}
</x-panel::form.row>

