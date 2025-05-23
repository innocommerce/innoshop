@props(['item'])

@php
  $startName = $item['start']['name'] ?? $item['name'] . '_start';
  $endName = $item['end']['name'] ?? $item['name'] . '_end';
@endphp
<div class="col-md-3 col-sm-6 mb-3">
  <label class="form-label form-label-sm">{{ $item['label'] }}</label>
  <div class="input-group input-group-sm">
    <input type="date" name="{{ $startName }}" class="form-control"
           value="{{ request($startName) }}">
    <span class="input-group-text">-</span>
    <input type="date" name="{{ $endName }}" class="form-control"
           value="{{ request($endName) }}">
  </div>
</div> 