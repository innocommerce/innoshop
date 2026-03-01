<div class="filter-group mb-2" data-filter-name="date_filter">
    <div class="d-flex align-items-center gap-2">
        <span class="filter-label fw-medium">{{ __('panel/common.created_at') }}</span>
        <div class="filter-buttons d-flex flex-wrap gap-1 align-items-center">
            @foreach($dateRangeOptions as $option)
                @php
                    $isActive = $dateFilter === $option['value'];
                    $isAll    = $option['value'] === 'all';
                @endphp
                <button type="button"
                        class="btn-filter btn btn-sm {{ $isActive ? 'btn-primary' : 'btn-outline-secondary' }} {{ $isAll ? 'btn-all' : '' }}"
                        data-filter-name="date_filter"
                        data-value="{{ $option['value'] }}"
                        data-is-all="{{ $isAll ? '1' : '0' }}">
                    {{ $option['label'] }}
                </button>
            @endforeach
            <span class="custom-date-inputs {{ $dateFilter === 'custom' ? '' : 'd-none' }}">
                <input type="text" class="form-control form-control-sm" name="start_date" id="start-date"
                       placeholder="{{ __('common/base.date_start') }}" value="{{ $startDate }}" readonly>
                <span class="text-muted">-</span>
                <input type="text" class="form-control form-control-sm" name="end_date" id="end-date"
                       placeholder="{{ __('common/base.date_end') }}" value="{{ $endDate }}" readonly>
                <button type="submit" class="btn btn-sm btn-primary">{{ __('common/base.confirm') }}</button>
            </span>
        </div>
    </div>
</div>
