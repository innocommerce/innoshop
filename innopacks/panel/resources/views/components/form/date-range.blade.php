@php
  $uniqueId = 'date-range-' . uniqid();
  $date_filter = $attributes->get('date_filter', '');
  $start_date = $attributes->get('start_date', '');
  $end_date = $attributes->get('end_date', '');
@endphp

<div class="card mb-4">
  <div class="card-body py-3">
    <form method="GET" action="{{ $action }}" id="{{ $uniqueId }}-form">
      <div class="row align-items-center g-3">
        <div class="col-auto">
          <label class="form-label mb-0 text-muted small">{{ __('panel/analytics.date_range') }}</label>
        </div>
        <div class="col-auto">
          <div class="btn-group" role="group">
            <input type="radio" class="btn-check" name="date_filter" id="{{ $uniqueId }}-filter-all" value="" {{ $date_filter == '' ? 'checked' : '' }}>
            <label class="btn btn-outline-secondary btn-sm" for="{{ $uniqueId }}-filter-all">{{ __('panel/analytics.filter_all') }}</label>

            <input type="radio" class="btn-check" name="date_filter" id="{{ $uniqueId }}-filter-today" value="today" {{ $date_filter == 'today' ? 'checked' : '' }}>
            <label class="btn btn-outline-secondary btn-sm" for="{{ $uniqueId }}-filter-today">{{ __('panel/analytics.filter_today') }}</label>

            <input type="radio" class="btn-check" name="date_filter" id="{{ $uniqueId }}-filter-yesterday" value="yesterday" {{ $date_filter == 'yesterday' ? 'checked' : '' }}">
            <label class="btn btn-outline-secondary btn-sm" for="{{ $uniqueId }}-filter-yesterday">{{ __('panel/analytics.filter_yesterday') }}</label>

            <input type="radio" class="btn-check" name="date_filter" id="{{ $uniqueId }}-filter-this-week" value="this_week" {{ $date_filter == 'this_week' ? 'checked' : '' }}>
            <label class="btn btn-outline-secondary btn-sm" for="{{ $uniqueId }}-filter-this-week">{{ __('panel/analytics.filter_this_week') }}</label>

            <input type="radio" class="btn-check" name="date_filter" id="{{ $uniqueId }}-filter-this-month" value="this_month" {{ $date_filter == 'this_month' ? 'checked' : '' }}">
            <label class="btn btn-outline-secondary btn-sm" for="{{ $uniqueId }}-filter-this-month">{{ __('panel/analytics.filter_this_month') }}</label>

            <input type="radio" class="btn-check" name="date_filter" id="{{ $uniqueId }}-filter-last-7" value="last_7_days" {{ $date_filter == 'last_7_days' ? 'checked' : '' }}">
            <label class="btn btn-outline-secondary btn-sm" for="{{ $uniqueId }}-filter-last-7">{{ __('panel/analytics.filter_last_7_days') }}</label>

            <input type="radio" class="btn-check" name="date_filter" id="{{ $uniqueId }}-filter-last-30" value="last_30_days" {{ $date_filter == 'last_30_days' ? 'checked' : '' }}">
            <label class="btn btn-outline-secondary btn-sm" for="{{ $uniqueId }}-filter-last-30">{{ __('panel/analytics.filter_last_30_days') }}</label>

            <input type="radio" class="btn-check" name="date_filter" id="{{ $uniqueId }}-filter-custom" value="custom" {{ $date_filter == 'custom' ? 'checked' : '' }}>
            <label class="btn btn-outline-secondary btn-sm" for="{{ $uniqueId }}-filter-custom">{{ __('panel/analytics.filter_custom') }}</label>
          </div>
        </div>
        <div class="col-auto {{ $uniqueId }}-custom-inputs" style="{{ $date_filter == 'custom' ? '' : 'display:none;' }}">
          <div class="input-group input-group-sm">
            <input type="text" class="form-control" name="date_range" id="{{ $uniqueId }}-input" style="width: 200px;" readonly>
            <button type="button" class="btn btn-primary" id="{{ $uniqueId }}-submit">{{ __('common/base.submit') }}</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

@push('footer')
<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof laydate === 'undefined') return;

  var form = document.getElementById('{{ $uniqueId }}-form');
  var customInputs = document.querySelector('.{{ $uniqueId }}-custom-inputs');
  var dateRangeInput = document.getElementById('{{ $uniqueId }}-input');
  var customSubmitBtn = document.getElementById('{{ $uniqueId }}-submit');

  var laydateRange;
  var currentLang = document.documentElement.lang === 'zh-cn' ? 'cn' : 'en';

  function initLaydate() {
    return laydate.render({
      elem: '#{{ $uniqueId }}-input',
      type: 'date',
      range: '~',
      lang: currentLang,
      format: 'yyyy-MM-dd'
    });
  }

  document.querySelectorAll('#{{ $uniqueId }}-form input[name="date_filter"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
      if (this.value === 'custom') {
        customInputs.style.display = '';
        dateRangeInput.disabled = false;
        if (!laydateRange) {
          laydateRange = initLaydate();
        }
      } else if (this.value === '') {
        customInputs.style.display = 'none';
        window.location.href = form.action;
      } else {
        customInputs.style.display = 'none';
        dateRangeInput.disabled = true;
        form.submit();
      }
    });
  });

  var checkedFilter = document.querySelector('#{{ $uniqueId }}-form input[name="date_filter"]:checked');
  if (checkedFilter && checkedFilter.value === 'custom') {
    laydateRange = initLaydate();
  } else {
    dateRangeInput.disabled = true;
  }

  customSubmitBtn.addEventListener('click', function() {
    var value = dateRangeInput.value;
    if (value) {
      var dates = value.split('~').map(function(d) { return d.trim(); });
      if (dates.length === 2) {
        window.location.href = form.action + '?date_filter=custom&start_date=' + dates[0] + '&end_date=' + dates[1];
      }
    }
  });
});
</script>
@endpush
