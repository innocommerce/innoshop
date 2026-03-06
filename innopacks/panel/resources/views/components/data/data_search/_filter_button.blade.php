@php
    $collapse       = ($filterGroup['collapse'] ?? 0) > 0;
    $options        = $filterGroup['options'] ?? [];
    $showMore       = $collapse && count($options) > $collapse;
    $visibleOptions = $showMore ? array_slice($options, 0, $collapse) : $options;
    $hiddenOptions  = $showMore ? array_slice($options, $collapse) : [];
@endphp

<div class="filter-group mb-2" data-filter-name="{{ $filterGroup['name'] }}">
    <div class="d-flex align-items-center gap-2">
        <span class="filter-label fw-medium">{{ $filterGroup['label'] }}</span>
        <div class="filter-buttons d-flex flex-wrap gap-1">
            @foreach($visibleOptions as $option)
                @php $isActive = ($currentValue ?? '') === ($option['value'] ?? ''); @endphp
                <button type="button"
                        class="btn-filter btn btn-sm {{ $isActive ? 'btn-primary' : 'btn-outline-secondary' }} {{ ($option['value'] ?? '') === '' ? 'btn-all' : '' }}"
                        data-filter-name="{{ $filterGroup['name'] }}"
                        data-value="{{ $option['value'] ?? '' }}"
                        data-is-all="{{ ($option['value'] ?? '') === '' ? '1' : '0' }}">
                    {!! $option['label'] !!}
                </button>
            @endforeach

            @if($showMore)
                <button type="button" class="btn btn-sm btn-link text-decoration-none show-more-btn py-0 px-1">
                    <i class="bi bi-plus"></i> {{ __('panel/common.more') }}
                </button>
            @endif
        </div>
    </div>

    @if($showMore)
        <div class="filter-more-options d-none ms-5 mt-2">
            @foreach($hiddenOptions as $option)
                @php $isActive = ($currentValue ?? '') === ($option['value'] ?? ''); @endphp
                <button type="button"
                        class="btn-filter btn btn-sm {{ $isActive ? 'btn-primary' : 'btn-outline-secondary' }}"
                        data-filter-name="{{ $filterGroup['name'] }}"
                        data-value="{{ $option['value'] ?? '' }}">
                    {!! $option['label'] !!}
                </button>
            @endforeach
        </div>
    @endif
</div>
