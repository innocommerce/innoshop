@extends('panel::layouts.app')
@section('body-class', 'page-themes-market')

@section('title', __('panel/common.market_theme'))

@section('content')
  @include('plugin::shared._token_info')

  @if(session('error') || isset($error))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <strong>{{ __('panel/common.error') }}:</strong> {{ session('error') ?? $error ?? '' }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
      {{-- Search and Filters Section --}}
      <div class="data-search-container mb-4" id="data-search-container">
        <form method="GET" action="{{ panel_route('theme-market.index') }}" id="search-form" class="search-form">
          <div class="search-filters-section bg-white rounded border">
            <div class="p-3">
              {{-- Search Row --}}
              <div class="search-row d-flex align-items-center gap-2">
                <span class="text-secondary"><i class="bi bi-search"></i></span>
                @if(!empty($searchFields))
                  <select name="search_field" class="form-select form-select-sm" style="width: 120px;">
                    @foreach($searchFields as $field)
                      <option value="{{ $field['value'] }}" {{ request()->get('search_field', 'all') === $field['value'] ? 'selected' : '' }}>
                        {{ $field['label'] }}
                      </option>
                    @endforeach
                  </select>
                @else
                  <input type="hidden" name="search_field" value="all">
                @endif
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="{{ __('panel/plugin.search_theme_placeholder', ['default' => __('common/base.search')]) }}..."
                       value="{{ request()->get('search') }}" style="width: 220px;" id="searchInput">
                <button type="submit" class="btn btn-primary btn-sm">
                  <i class="bi bi-search"></i> {{ __('panel/common.search') }}
                </button>
                @if(request()->get('search'))
                  <button type="button" class="btn btn-outline-secondary btn-sm" id="clearSearch">
                    <i class="bi bi-x-circle"></i> {{ __('panel/common.clear_selection') }}
                  </button>
                @endif
              </div>

              {{-- Filters Section --}}
              @if(!empty($filterButtons))
                <div class="filters-section mt-3 pt-3 border-top">
                  @foreach($filterButtons as $filterGroup)
                    @php
                      $options = $filterGroup['options'] ?? [];
                      $currentValue = request()->get($filterGroup['name'], '');
                    @endphp
                    <div class="filter-group {{ !$loop->last ? 'mb-3' : '' }}" data-filter-name="{{ $filterGroup['name'] }}">
                      <div class="d-flex align-items-center gap-2">
                        <span class="filter-label fw-medium text-muted">{{ $filterGroup['label'] }}</span>
                        <div class="filter-buttons d-flex flex-wrap gap-1">
                          @foreach($options as $option)
                            @php $isActive = $currentValue === ($option['value'] ?? ''); @endphp
                            <a href="{{ request()->fullUrlWithQuery([$filterGroup['name'] => $option['value'] ?: null, 'page' => 1]) }}"
                               class="btn btn-sm {{ $isActive ? 'btn-primary' : 'btn-outline-secondary' }} {{ ($option['value'] ?? '') === '' ? 'btn-all' : '' }}">
                              {!! $option['label'] !!}
                            </a>
                          @endforeach
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              @endif

              {{-- Selected Filters Tags --}}
              @if(request()->get('search') || request()->get('tab') || request()->get('category'))
                <div class="selected-filters-section pt-2 mt-2 border-top">
                  <div class="d-flex align-items-center flex-wrap gap-2">
                    <span class="text-muted small">
                      <i class="bi bi-check-circle"></i> {{ __('panel/common.selected_filters') }}：
                    </span>

                    @if(request()->get('search'))
                      <span class="badge bg-light text-dark border selected-filter-tag">
                        {{ __('panel/common.search') }}: {{ request()->get('search') }}
                        <button type="button" class="btn-close-xs ms-1 remove-filter" data-filter-type="search">×</button>
                      </span>
                    @endif

                    @if(request()->get('tab'))
                      @php
                        $tabLabels = [
                          'featured' => __('panel/plugin.featured'),
                          'popular' => __('panel/plugin.popular'),
                          'recommended' => __('panel/plugin.recommended'),
                        ];
                      @endphp
                      <span class="badge bg-light text-dark border selected-filter-tag">
                        {{ __('panel/plugin.filter_type') }}: {{ $tabLabels[request()->get('tab')] ?? request()->get('tab') }}
                        <button type="button" class="btn-close-xs ms-1 remove-filter" data-filter-type="tab">×</button>
                      </span>
                    @endif

                    @if(request()->get('category'))
                      @php
                        $categoryName = '';
                        $categoryList = $categories['data'] ?? [];
                        foreach($categoryList as $cat) {
                          if (($cat['slug'] ?? '') == request()->get('category')) {
                            $categoryName = $cat['translation']['name'] ?? $cat['name'] ?? '';
                            break;
                          }
                        }
                      @endphp
                      <span class="badge bg-light text-dark border selected-filter-tag">
                        {{ __('panel/common.category') }}: {{ $categoryName }}
                        <button type="button" class="btn-close-xs ms-1 remove-filter" data-filter-type="category">×</button>
                      </span>
                    @endif

                    <a href="{{ panel_route('theme-market.index') }}" class="btn btn-sm btn-outline-secondary clear-all-btn">
                      <i class="bi bi-x-circle"></i> {{ __('panel/common.clear_all') }}
                    </a>
                  </div>
                </div>
              @endif
            </div>
          </div>
        </form>
      </div>

      {{-- Record Count Info --}}
      @if(isset($products['meta']))
        <div class="mb-3">
          <p class="text-muted small mb-0">
            {{ __('panel/common.total_records', ['total' => $products['meta']['total'] ?? 0, 'current' => $products['meta']['current_page'] ?? 1, 'last' => $products['meta']['last_page'] ?? 1]) }}
          </p>
        </div>
      @endif

      {{-- Products Grid --}}
      @if(empty($products['data']))
        <div class="text-center py-5">
          <i class="bi bi-inbox fs-1 text-muted"></i>
          <p class="text-muted mt-3 mb-0">{{ __('common/base.no_data') }}</p>
        </div>
      @else
        <div class="row g-4" id="marketItemsContent">
          @foreach ($products['data'] ?? [] as $product)
            <div class="col-6 col-md-4 col-lg-3">
              @include('plugin::theme_market._item')
            </div>
          @endforeach
        </div>

        {{-- Pagination --}}
        @if(isset($products['meta']) && $products['meta']['last_page'] > 1)
          <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center mb-0">
              <li class="page-item {{ $products['meta']['current_page'] <= 1 ? 'disabled' : '' }}">
                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $products['meta']['current_page'] - 1]) }}" aria-label="Previous">
                  <i class="bi bi-chevron-left"></i>
                </a>
              </li>

              @php
                $currentPage = $products['meta']['current_page'];
                $lastPage = $products['meta']['last_page'];
                $startPage = max(1, $currentPage - 2);
                $endPage = min($lastPage, $currentPage + 2);
              @endphp

              @if($startPage > 1)
                <li class="page-item">
                  <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => 1]) }}">1</a>
                </li>
                @if($startPage > 2)
                  <li class="page-item disabled">
                    <span class="page-link">...</span>
                  </li>
                @endif
              @endif

              @for($i = $startPage; $i <= $endPage; $i++)
                <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                  <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                </li>
              @endfor

              @if($endPage < $lastPage)
                @if($endPage < $lastPage - 1)
                  <li class="page-item disabled">
                    <span class="page-link">...</span>
                  </li>
                @endif
                <li class="page-item">
                  <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $lastPage]) }}">{{ $lastPage }}</a>
                </li>
              @endif

              <li class="page-item {{ $currentPage >= $lastPage ? 'disabled' : '' }}">
                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}" aria-label="Next">
                  <i class="bi bi-chevron-right"></i>
                </a>
              </li>
            </ul>
          </nav>
        @endif
      @endif
    </div>
  </div>
@endsection

@push('footer')
  <style>
    /* Theme Market Color Variables */
    :root {
      --theme-primary: #9b6aff;
      --theme-primary-hover: #8b5aff;
      --theme-primary-border: #7b4aff;
      --theme-gradient-start: #9b6aff;
      --theme-gradient-end: #bb8aff;
      --theme-shadow: rgba(155, 106, 255, 0.25);
      --border-color: #e9ecef;
      --bg-light: #f8f9fa;
      --text-muted: #6c757d;
      --success-color: #10b981;
    }

    /* Data Search Container */
    .data-search-container .search-filters-section {
      background-color: #fafbfc;
    }

    /* Filter Styles */
    .filter-label {
      min-width: 60px;
      font-size: 0.875rem;
    }
    .filter-buttons .btn {
      font-size: 0.8125rem;
      padding: 0.25rem 0.75rem;
    }
    .filter-buttons .btn.active,
    .filter-buttons .btn-primary {
      font-weight: 500;
    }

    /* Selected Filter Tags */
    .selected-filter-tag {
      font-size: 0.8125rem;
      padding: 0.35rem 0.5rem;
    }
    .btn-close-xs {
      background: none;
      border: none;
      font-size: 1rem;
      line-height: 1;
      padding: 0;
      margin-left: 0.25rem;
      cursor: pointer;
      opacity: 0.5;
    }
    .btn-close-xs:hover {
      opacity: 1;
    }
    .remove-filter {
      color: inherit;
    }

    /* Theme Market Specific Colors */
    .page-themes-market .btn-primary {
      background-color: var(--theme-primary);
      border-color: var(--theme-primary);
    }
    .page-themes-market .btn-primary:hover {
      background-color: var(--theme-primary-hover);
      border-color: var(--theme-primary-border);
    }

    /* ========== Theme Card Styles ========== */
    .theme-card {
      border-radius: 16px;
      overflow: hidden;
      transition: all 0.3s ease;
      border: 1px solid var(--border-color) !important;
      background: #ffffff;
    }
    .theme-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 12px 24px var(--theme-shadow) !important;
      border-color: var(--theme-primary) !important;
    }

    /* Preview Image */
    .theme-preview-wrapper {
      height: 140px;
      overflow: hidden;
      position: relative;
    }
    .theme-preview-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.4s ease;
    }
    .theme-card:hover .theme-preview-img {
      transform: scale(1.08);
    }
    .theme-preview-overlay {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 60%;
      background: linear-gradient(to top, rgba(0,0,0,0.6) 0%, transparent 100%);
      pointer-events: none;
    }

    /* Theme Badge */
    .theme-badge-overlay {
      position: absolute;
      top: 8px;
      right: 8px;
      background: var(--theme-primary);
      color: #ffffff;
      padding: 0.25rem 0.6rem;
      border-radius: 20px;
      font-size: 0.7rem;
      font-weight: 500;
      z-index: 2;
    }

    /* Author Badge */
    .theme-author-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 0.35rem 0.75rem;
      background: rgba(255,255,255,0.95);
      border-radius: 20px;
      font-size: 0.75rem;
      color: var(--text-muted);
      position: absolute;
      bottom: 8px;
      left: 8px;
      z-index: 2;
    }
    .theme-author-badge img {
      width: 20px;
      height: 20px;
      border-radius: 50%;
      object-fit: cover;
    }

    /* Theme Info */
    .theme-name {
      font-size: 0.95rem;
      line-height: 1.3;
      font-weight: 600;
      color: #333;
      display: -webkit-box;
      -webkit-line-clamp: 1;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    /* Stats */
    .theme-stats {
      font-size: 0.8rem;
      color: var(--text-muted);
    }
    .theme-rating-stars {
      color: #f59e0b;
      font-size: 0.7rem;
    }
    .theme-rating-count {
      font-size: 0.75rem;
      color: var(--text-muted);
    }
    .theme-stat-item {
      display: flex;
      align-items: center;
      gap: 4px;
    }

    /* Footer */
    .theme-card-footer {
      border-top: 1px solid var(--border-color);
    }
    .theme-price .price-free {
      color: var(--success-color);
      font-weight: 600;
      font-size: 0.9rem;
    }
    .theme-price .price-paid {
      color: var(--theme-primary);
      font-weight: 600;
      font-size: 0.95rem;
    }
    .theme-view-btn {
      color: var(--theme-primary);
      font-size: 0.85rem;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    .theme-card:hover .theme-view-btn {
      transform: translateX(4px);
    }
  </style>
  <script>
    $(function () {
      // Clear search button
      $('#clearSearch').on('click', function() {
        var url = new URL(window.location.href);
        url.searchParams.delete('search');
        url.searchParams.delete('search_field');
        url.searchParams.delete('page');
        window.location.href = url.toString();
      });

      // Remove individual filter tags
      $('.remove-filter').on('click', function() {
        var filterType = $(this).data('filter-type');
        var url = new URL(window.location.href);

        if (filterType === 'search') {
          url.searchParams.delete('search');
          url.searchParams.delete('search_field');
        } else if (filterType === 'tab') {
          url.searchParams.delete('tab');
        } else if (filterType === 'category') {
          url.searchParams.delete('category');
        }

        url.searchParams.delete('page');
        window.location.href = url.toString();
      });
    });
  </script>
@endpush
