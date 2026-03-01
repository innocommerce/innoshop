@php
    $filterName = $filterGroup['name'];
    $startName  = $filterName . '_start';
    $endName    = $filterName . '_end';
    $startVal   = $startValue ?? '';
    $endVal     = $endValue ?? '';
@endphp

<div class="filter-group mb-2" data-filter-name="{{ $filterName }}" data-filter-type="range">
    <div class="d-flex align-items-center gap-2">
        <span class="filter-label fw-medium">{{ $filterGroup['label'] }}</span>
        <div class="filter-range-inputs d-flex align-items-center gap-1">
            <input type="number" name="{{ $startName }}" class="form-control form-control-sm"
                   style="width: 100px;" placeholder="{{ __('panel/common.min') }}" value="{{ $startVal }}">
            <span class="text-muted">-</span>
            <input type="number" name="{{ $endName }}" class="form-control form-control-sm"
                   style="width: 100px;" placeholder="{{ __('panel/common.max') }}" value="{{ $endVal }}">
            <button type="submit" class="btn btn-sm btn-primary">{{ __('panel/common.filter') }}</button>
        </div>
    </div>
</div>
