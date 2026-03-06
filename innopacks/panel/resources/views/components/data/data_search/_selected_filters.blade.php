<div class="d-flex align-items-center flex-wrap gap-2">
    <span class="text-muted small">
        <i class="bi bi-check-circle"></i> {{ __('panel/common.selected_filters') }}：
    </span>

    @if($currentKeyword !== '')
        <span class="badge bg-light text-dark border selected-filter-tag">
            {{ __('panel/common.search') }}: {{ $currentKeyword }}
            <button type="button" class="btn-close-xs ms-1 remove-filter" data-filter-type="search" data-field="keyword">×</button>
        </span>
    @endif

    @if($enableDateRange && $dateFilter !== 'all')
        <span class="badge bg-light text-dark border selected-filter-tag">
            {{ __('panel/common.created_at') }}: {{ $dateFilter === 'custom' ? ($startDate . ' ~ ' . $endDate) : __("panel/analytics.filter_{$dateFilter}") }}
            <button type="button" class="btn-close-xs ms-1 remove-filter" data-filter-type="date" data-field="date_filter">×</button>
        </span>
    @endif

    @foreach($activeFilters as $name => $filter)
        <span class="badge bg-light text-dark border selected-filter-tag">
            {{ $filter['label'] }}: {!! $filter['valueLabel'] !!}
            <button type="button" class="btn-close-xs ms-1 remove-filter" data-filter-type="filter" data-field="{{ $name }}">×</button>
        </span>
    @endforeach

    @foreach($activeRangeFilters as $name => $filter)
        <span class="badge bg-light text-dark border selected-filter-tag">
            {{ $filter['label'] }}: {{ $filter['start'] ?? '~' }} ~ {{ $filter['end'] ?? '~' }}
            <button type="button" class="btn-close-xs ms-1 remove-filter" data-filter-type="range" data-field="{{ $name }}">×</button>
        </span>
    @endforeach

    <button type="button" class="btn btn-sm btn-outline-secondary clear-all-btn">
        <i class="bi bi-x-circle"></i> {{ __('panel/common.clear_all') }}
    </button>
</div>
