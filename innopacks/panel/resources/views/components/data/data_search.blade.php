@props([
    'action'          => '',
    'searchFields'    => [],
    'filters'         => [],
    'enableDateRange' => false,
])

<div class="data-search-container mb-3" id="data-search-container">
    <form action="{{ $action }}" method="GET" id="search-form" class="search-form">
        <div class="search-filters-section bg-white rounded border">
            <div class="p-3">
                {{-- Search Row: filters left, optional slot (e.g. export) right --}}
                <div class="search-row g-2 align-items-center justify-content-between gap-2 flex-wrap w-100">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="text-secondary"><i class="bi bi-search"></i></span>
                        <select name="search_field" class="form-select form-select-sm" style="width: 120px;">
                            @foreach($searchFields as $field)
                                <option value="{{ $field['value'] }}" {{ $currentSearchField === $field['value'] ? 'selected' : '' }}>
                                    {{ $field['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <input type="text" name="keyword" class="form-control form-control-sm"
                               placeholder="{{ __('panel/common.enter_keyword') }}" value="{{ $currentKeyword }}" style="width: 200px;">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search"></i> {{ __('panel/common.search') }}
                        </button>
                    </div>
                    @isset($toolbarRight)
                        <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-auto">
                            {{ $toolbarRight }}
                        </div>
                    @endisset
                </div>

                @if(count($filters) > 0 || $enableDateRange)
                    <div class="filters-section mt-3 pt-3 border-top">
                        {{-- Date Range Filter --}}
                        @if($enableDateRange)
                            @include('panel::components.data.data_search._filter_date', [
                                'dateRangeOptions' => $dateRangeOptions,
                                'dateFilter'       => $dateFilter,
                                'startDate'        => $startDate,
                                'endDate'          => $endDate,
                            ])
                        @endif

                        {{-- Dynamic Filters --}}
                        @foreach($filters as $filterGroup)
                            @php $filterType = $filterGroup['type'] ?? 'button'; @endphp

                            @if($filterType === 'range')
                                @include('panel::components.data.data_search._filter_range', [
                                    'filterGroup' => $filterGroup,
                                    'startValue'  => request($filterGroup['name'] . '_start', ''),
                                    'endValue'    => request($filterGroup['name'] . '_end', ''),
                                ])
                            @else
                                @include('panel::components.data.data_search._filter_button', [
                                    'filterGroup'  => $filterGroup,
                                    'currentValue' => request($filterGroup['name'], ''),
                                ])
                            @endif
                        @endforeach
                    </div>
                @endif

                {{-- Selected Filters --}}
                @if($hasActiveFilters)
                    <div class="selected-filters-section pt-2 mt-2 border-top">
                        @include('panel::components.data.data_search._selected_filters', [
                            'currentKeyword'     => $currentKeyword,
                            'enableDateRange'    => $enableDateRange,
                            'dateFilter'         => $dateFilter,
                            'startDate'          => $startDate,
                            'endDate'            => $endDate,
                            'activeFilters'      => $activeFilters,
                            'activeRangeFilters' => $activeRangeFilters,
                        ])
                    </div>
                @endif
            </div>
        </div>
    </form>
</div>

@push('footer')
@include('panel::components.data.data_search._scripts', [
    'enableDateRange' => $enableDateRange,
    'filters'         => $filters,
])
@endpush
