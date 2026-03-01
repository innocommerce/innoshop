@php
  $name = $item['name'] ?? '';
  $label = $item['label'] ?? '';
  $value = request($name, $item['value'] ?? '');
  $format = $item['format'] ?? 'yyyy-MM-dd';
  $type = $item['date_type'] ?? 'date'; // date, datetime
  $uniqueId = 'criteria-date-' . uniqid();
@endphp

<div class="col-md-3 col-sm-6 mb-3">
  <label for="{{ $uniqueId }}" class="form-label form-label-sm">{{ $label }}</label>
  <div class="{{ $type }}">
    <input type="text"
           id="{{ $uniqueId }}"
           name="{{ $name }}"
           class="form-control form-control-sm"
           value="{{ $value }}"
           placeholder="{{ $label }}"
           autocomplete="off"
           readonly>
  </div>
</div>

@push('footer')
<script>
document.addEventListener('DOMContentLoaded', function() {
  var elem = document.getElementById('{{ $uniqueId }}');
  if (!elem || typeof laydate === 'undefined') return;

  var currentLang = document.documentElement.lang === 'zh-cn' ? 'cn' : 'en';

  laydate.render({
    elem: '#{{ $uniqueId }}',
    type: '{{ $type }}',
    format: '{{ $format }}',
    lang: currentLang,
    done: function(value) {
      elem.dispatchEvent(new Event('change', { bubbles: true }));
    }
  });
});
</script>
@endpush
