@props(['item'])

<div class="col-md-3 col-sm-6 mb-3">
  <label for="{{ $item['name'] }}" class="form-label form-label-sm">{{ $item['label'] }}</label>
  <input type="date" name="{{ $item['name'] }}" id="{{ $item['name'] }}"
         class="form-control form-control-sm"
         value="{{ request($item['name']) }}"
         placeholder="{{ $item['label'] }}">
</div> 