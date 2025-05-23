@props(['item'])

@php
    $startName = $item['start']['name'] ?? $item['name'] . '_start';
    $endName = $item['end']['name'] ?? $item['name'] . '_end';
@endphp
<div class="col-md-3 col-sm-6 mb-3">
  <label class="form-label form-label-sm">{{ $item['label'] }}</label>
  <div class="input-group input-group-sm">
    <input type="text" name="{{ $startName }}" class="form-control"
           value="{{ request($startName) }}"
           placeholder="{{ $item['start']['label'] ?? $item['label'] }}">
    <span class="input-group-text">-</span>
    <input type="text" name="{{ $endName }}" class="form-control"
           value="{{ request($endName) }}"
           placeholder="{{ $item['end']['label'] ?? $item['label'] }}">
  </div>
</div> 