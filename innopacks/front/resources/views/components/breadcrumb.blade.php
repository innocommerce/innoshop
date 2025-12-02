<div class="breadcrumb-wrap">
  <div class="container {{ count($breadcrumbs) > 0 ? 'd-flex justify-content-start align-items-center' : 'justify-content-center' }}">
    <ul class="breadcrumb mb-0">
      @foreach ($breadcrumbs as $index=>$breadcrumb)
        @if (isset($breadcrumb['url']) && $breadcrumb['url'])
          <li>
            @if($index == 0)
              <i class="bi bi-house-door-fill home-icon"></i>
            @endif
            <a href="{{ $breadcrumb['url'] }}" @if(isset($breadcrumb['full_title'])) title="{{ $breadcrumb['full_title'] }}" @endif>
              {{ $breadcrumb['display_title'] ?? $breadcrumb['title'] }}
            </a>
          </li>
        @else
          <li class="breadcrumb-item active" aria-current="page" @if(isset($breadcrumb['full_title'])) title="{{ $breadcrumb['full_title'] }}" @endif>
            {{ $breadcrumb['display_title'] ?? $breadcrumb['title'] }}
          </li>
        @endif
      @endforeach
    </ul>

    @if (count($breadcrumbs) > 0 && isset($showFilter) && $showFilter)
      <div class="breadcrumb-filter-btn d-block d-md-none">
        <button class="btn btn-outline-primary btn-sm d-flex align-items-center" id="toggleFilterSidebar">
          <i class="bi bi-funnel me-1"></i>
          <span class="filter-text">{{ __('front/common.filter') }}</span>
        </button>
      </div>
    @endif
  </div>
</div>
