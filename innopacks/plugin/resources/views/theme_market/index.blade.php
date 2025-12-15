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
      <div class="mb-4">
        <!-- Tab Navigation and Search Row -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
          <ul class="nav nav-tabs market-tabs border-0 flex-grow-1" role="tablist">
            <li class="nav-item" role="presentation">
              <a class="nav-link {{ empty(request()->get('tab')) || request()->get('tab') == 'all' ? 'active' : '' }}" 
                 href="{{ request()->fullUrlWithQuery(['tab' => 'all', 'page' => 1]) }}">
                {{ __('panel/common.all') }}
              </a>
            </li>
            <li class="nav-item" role="presentation">
              <a class="nav-link {{ request()->get('tab') == 'featured' ? 'active' : '' }}" 
                 href="{{ request()->fullUrlWithQuery(['tab' => 'featured', 'page' => 1]) }}">
                <i class="bi bi-star-fill me-1"></i>{{ __('panel/plugin.featured') }}
              </a>
            </li>
            <li class="nav-item" role="presentation">
              <a class="nav-link {{ request()->get('tab') == 'popular' ? 'active' : '' }}" 
                 href="{{ request()->fullUrlWithQuery(['tab' => 'popular', 'page' => 1]) }}">
                <i class="bi bi-fire me-1"></i>{{ __('panel/plugin.popular') }}
              </a>
            </li>
            <li class="nav-item" role="presentation">
              <a class="nav-link {{ request()->get('tab') == 'recommended' ? 'active' : '' }}" 
                 href="{{ request()->fullUrlWithQuery(['tab' => 'recommended', 'page' => 1]) }}">
                <i class="bi bi-heart-fill me-1"></i>{{ __('panel/plugin.recommended') }}
              </a>
            </li>
          </ul>

          <!-- Search Box -->
          <div class="flex-shrink-0">
            <form method="GET" action="{{ panel_route('theme-market.index') }}" class="d-flex" id="searchForm">
              <div class="input-group input-group-sm search-input-group">
                <span class="input-group-text bg-white border-end-0">
                  <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" 
                       class="form-control border-start-0" 
                       name="search" 
                       value="{{ request()->get('search') }}"
                       placeholder="{{ __('panel/common.search') }}..." 
                       id="searchInput"
                       autocomplete="off">
                @if(request()->get('search'))
                  <button type="button" class="btn btn-outline-secondary border-start-0" id="clearSearch" title="{{ __('panel/common.clear_selection') }}">
                    <i class="bi bi-x"></i>
                  </button>
                @endif
              </div>
              @foreach(request()->except(['search', 'page']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
              @endforeach
            </form>
          </div>
        </div>

        <!-- Category Filters Row -->
        <div class="mb-3">
          @if($categories['data'] ?? [])
            @php
              $categoryList = $categories['data'] ?? [];
              $maxVisible = 8; // 默认显示前8个分类
              $hasMore = count($categoryList) > $maxVisible;
            @endphp
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <span class="text-muted small fw-medium flex-shrink-0">{{ __('panel/common.category') }}:</span>
              <div class="d-flex gap-2 flex-wrap align-items-center">
                <a class="btn btn-sm {{ empty(request()->category) ? 'btn-primary' : 'btn-outline-secondary' }} rounded px-3"
                   href="{{ request()->fullUrlWithQuery(['category' => null, 'page' => 1]) }}">
                  {{ __('panel/common.all') }}
                </a>
                @foreach($categoryList as $index => $category)
                  <a class="btn btn-sm category-item {{ request()->category == $category['slug'] ? 'btn-primary' : 'btn-outline-secondary' }} rounded px-3 {{ $hasMore && $index >= $maxVisible ? 'd-none category-more' : '' }}"
                     href="{{ request()->fullUrlWithQuery(['category' => $category['slug'], 'page' => 1]) }}">
                    {{ $category['translation']['name'] ?? $category['name'] ?? '' }}
                  </a>
                @endforeach
                @if($hasMore)
                  <button type="button" class="btn btn-sm btn-outline-secondary rounded px-3" id="toggleMoreCategories">
                    <span class="show-more-text">{{ __('panel/common.view_more') }}</span>
                    <span class="show-less-text d-none">{{ __('panel/common.collapse') }}</span>
                  </button>
                @endif
              </div>
            </div>
          @endif
        </div>

        <!-- Record Count -->
        @if(isset($products['meta']))
          <div class="mb-3">
            <p class="text-muted small mb-0">
              {{ __('panel/common.total_records', ['total' => $products['meta']['total'] ?? 0, 'current' => $products['meta']['current_page'] ?? 1, 'last' => $products['meta']['last_page'] ?? 1]) }}
            </p>
          </div>
        @endif
      </div>

      @if(empty($products['data']))
        <div class="text-center py-5">
          <i class="bi bi-inbox fs-1 text-muted"></i>
          <p class="text-muted mt-3 mb-0">{{ __('panel/common.no_data') }}</p>
        </div>
      @else
        <div class="row g-4" id="marketItemsContent">
          @foreach ($products['data'] ?? [] as $product)
            <div class="col-6 col-md-4 col-lg-3">
              @include('plugin::theme_market._item')
            </div>
          @endforeach
        </div>

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
      --theme-shadow: rgba(155, 106, 255, 0.3);
      --border-color: #e9ecef;
      --bg-light: #f8f9fa;
      --text-muted: #6c757d;
    }

    /* Market Tabs Styling */
    .market-tabs {
      border-bottom: 1px solid var(--border-color);
    }
    .market-tabs .nav-link {
      color: var(--text-muted);
      border: none;
      border-bottom: 2px solid transparent;
      padding: 0.75rem 1.25rem;
      margin-bottom: -1px;
      transition: all 0.2s ease;
      font-weight: 400;
    }
    .market-tabs .nav-link:hover {
      color: var(--theme-primary);
      border-bottom-color: var(--border-color);
      background-color: transparent;
    }
    .market-tabs .nav-link.active {
      color: var(--theme-primary);
      border-bottom-color: var(--theme-primary);
      background-color: transparent;
      font-weight: 600;
    }
    .market-tabs .nav-link i {
      font-size: 0.9em;
    }

    /* Category Items */
    .category-item {
      transition: all 0.2s ease;
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

    /* Product Card Styles */
    .product-item-card {
      transition: all 0.2s ease;
    }
    .product-item-card:hover {
      transform: translateY(-4px);
    }

    /* Theme Market Specific Product Card Styles */
    .product-item-card-theme {
      border-color: var(--border-color) !important;
    }
    .product-item-card-theme .product-gradient-bar {
      height: 4px;
      background: linear-gradient(90deg, var(--theme-gradient-start) 0%, var(--theme-gradient-end) 100%);
      z-index: 1;
    }
    .product-item-card-theme:hover {
      box-shadow: 0 0.5rem 1rem var(--theme-shadow) !important;
      border-color: var(--theme-primary) !important;
    }
    .product-item-card-theme .product-price {
      color: var(--theme-primary);
      font-size: 1.1rem;
    }
    .product-item-card-theme .product-view-badge {
      background-color: var(--theme-primary);
      color: #ffffff;
      font-size: 0.85rem;
    }
    .product-item-card-theme .theme-badge {
      background-color: var(--theme-primary);
      color: #ffffff;
    }

    /* Common Product Card Styles */
    .product-image-container {
      height: 180px;
      overflow: hidden;
    }
    .product-image-container img {
      object-fit: cover;
      transition: transform 0.3s ease;
    }
    .product-item-card:hover .product-image-container img {
      transform: scale(1.05);
    }
    .product-title {
      font-size: 0.95rem;
      line-height: 1.4;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .product-rating {
      font-size: 0.75rem;
    }

    /* Search Input Group */
    .search-input-group {
      max-width: 280px;
    }
  </style>
  <script>
    $(function () {
      // Search form submission
      $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) {
          e.preventDefault();
          $('#searchForm').submit();
        }
      });

      // Clear search
      $('#clearSearch').on('click', function() {
        $('#searchInput').val('');
        var url = new URL(window.location.href);
        url.searchParams.delete('search');
        url.searchParams.delete('page');
        window.location.href = url.toString();
      });

      // Auto-submit search on input (debounced)
      var searchTimeout;
      $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        var $input = $(this);
        searchTimeout = setTimeout(function() {
          if ($input.val().length >= 2 || $input.val().length === 0) {
            $('#searchForm').submit();
          }
        }, 500);
      });

      // Toggle more categories
      $('#toggleMoreCategories').on('click', function() {
        $('.category-more').toggleClass('d-none');
        $('.show-more-text, .show-less-text').toggleClass('d-none');
      });
    });
  </script>
@endpush
