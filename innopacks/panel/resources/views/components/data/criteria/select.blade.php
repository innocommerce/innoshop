@props(['item'])

<div class="col-md-3 col-sm-6 mb-3">
  <label for="{{ $item['name'] }}" class="form-label form-label-sm">{{ $item['label'] }}</label>
  <select name="{{ $item['name'] }}" id="{{ $item['name'] }}" class="form-select form-select-sm">
    <option value="">{{ panel_trans('common.please_choose') }}</option>
    @foreach($item['options'] as $option)
      <option
          value="{{ isset($item['options_key']) ? $option[$item['options_key']] : $option['value'] }}" {{ request($item['name'])==(isset($item['options_key']) ? $option[$item['options_key']] : $option['value']) ? 'selected' : '' }}>{{
          isset($option['label_key']) ? trans($option['label_key']) : (isset($item['options_label']) ? $option[$item['options_label']] : $option['label']) }}</option>
    @endforeach
  </select>
</div> 