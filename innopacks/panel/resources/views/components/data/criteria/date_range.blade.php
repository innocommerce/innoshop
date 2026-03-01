@php
  $name = $item['name'] ?? 'date_range';
  $label = $item['label'] ?? '';
  $startName = $item['start']['name'] ?? $name . '_start';
  $endName = $item['end']['name'] ?? $name . '_end';
  $startValue = request($startName, $item['start']['value'] ?? '');
  $endValue = request($endName, $item['end']['value'] ?? '');
  $format = $item['format'] ?? 'yyyy-MM-dd';
  $type = $item['date_type'] ?? 'date'; // date, datetime
  $separator = $item['separator'] ?? '-';
  $uniqueId = 'criteria-date-range-' . uniqid();
@endphp

<div class="col-md-3 col-sm-6 mb-3">
  <label class="form-label form-label-sm">{{ $label }}</label>
  <div class="d-flex align-items-center gap-1">
    <input type="text"
           id="{{ $uniqueId }}-start"
           name="{{ $startName }}"
           class="form-control form-control-sm"
           value="{{ $startValue }}"
           placeholder="{{ trans('common/base.date_start') }}"
           autocomplete="off"
           readonly>
    <span class="text-muted">{{ $separator }}</span>
    <input type="text"
           id="{{ $uniqueId }}-end"
           name="{{ $endName }}"
           class="form-control form-control-sm"
           value="{{ $endValue }}"
           placeholder="{{ trans('common/base.date_end') }}"
           autocomplete="off"
           readonly>
  </div>
</div>

@push('footer')
<script>
document.addEventListener('DOMContentLoaded', function() {
  var startElem = document.getElementById('{{ $uniqueId }}-start');
  var endElem = document.getElementById('{{ $uniqueId }}-end');
  if (!startElem || !endElem || typeof laydate === 'undefined') return;

  var currentLang = document.documentElement.lang === 'zh-cn' ? 'cn' : 'en';

  laydate.render({
    elem: '#{{ $uniqueId }}-start',
    type: '{{ $type }}',
    format: '{{ $format }}',
    lang: currentLang,
    done: function(value) {
      startElem.dispatchEvent(new Event('change', { bubbles: true }));
    }
  });

  laydate.render({
    elem: '#{{ $uniqueId }}-end',
    type: '{{ $type }}',
    format: '{{ $format }}',
    lang: currentLang,
    done: function(value) {
      endElem.dispatchEvent(new Event('change', { bubbles: true }));
    }
  });
});
</script>
@endpush
