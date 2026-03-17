@if(is_array($product) && $product)
  <a href="{{ panel_route('plugin-market.show', $product['product_id']) }}" class="text-decoration-none h-100 d-block">
    <div class="card plugin-card h-100 border-0 shadow-sm overflow-hidden">
      {{-- Gradient Top Bar --}}
      <div class="plugin-card-gradient"></div>

      <div class="card-body p-3">
        {{-- Header: Logo + Info --}}
        <div class="d-flex gap-3 mb-3">
          <div class="plugin-logo-wrapper flex-shrink-0">
            <img src="{{ $product['image_small'] ?? ''}}"
                 class="plugin-logo-img"
                 alt="{{ $product['name'] }}"
                 onerror="this.src='{{ asset('images/default-plugin.png') }}'">
          </div>
          <div class="flex-grow-1 min-w-0">
            <h6 class="plugin-name mb-1 fw-semibold text-dark">
              {{ $product['name'] }}
            </h6>
            @if($product['seller_name'])
              <span class="plugin-author text-truncate d-block">
                <i class="bi bi-person-circle me-1"></i>{{ $product['seller_name'] }}
              </span>
            @endif
          </div>
        </div>

        {{-- Description --}}
        @if($product['summary'] ?? '')
          <p class="plugin-summary text-muted small mb-3">
            {{ Str::limit(strip_tags($product['summary']), 80) }}
          </p>
        @endif

        {{-- Stats Row --}}
        <div class="plugin-stats d-flex align-items-center gap-3 mb-3 flex-wrap">
          @if($product['rating'] ?? 0)
            <div class="d-flex align-items-center">
              <span class="plugin-rating-stars">
                @for($i = 1; $i <= 5; $i++)
                  <i class="bi bi-star{{ $i <= round($product['rating']) ? '-fill' : '' }}"></i>
                @endfor
              </span>
              <span class="plugin-rating-count ms-1">{{ number_format($product['reviews_count'] ?? 0) }}</span>
            </div>
          @endif

          @if($product['active_installations'] ?? 0)
            <div class="plugin-stat-item">
              <i class="bi bi-box-seam me-1"></i>
              <span>{{ $product['active_installations'] > 1000 ? round($product['active_installations']/1000, 1) . 'K' : $product['active_installations'] }}+</span>
            </div>
          @elseif($product['downloaded'] ?? 0)
            <div class="plugin-stat-item">
              <i class="bi bi-download me-1"></i>
              <span>{{ number_format($product['downloaded']) }}</span>
            </div>
          @endif
        </div>

        {{-- Tags/Category --}}
        @if($product['category_name'] ?? '')
          <div class="mb-3">
            <span class="plugin-category-tag">
              <i class="bi bi-tag me-1"></i>{{ $product['category_name'] }}
            </span>
          </div>
        @endif

        {{-- Footer: Price + Action --}}
        <div class="plugin-card-footer mt-auto pt-3">
          <div class="d-flex justify-content-between align-items-center">
            <div class="plugin-price">
              @if(($product['price'] ?? 0) == 0)
                <span class="price-free">
                  <i class="bi bi-gift me-1"></i>{{ __('panel/plugin.free') }}
                </span>
              @else
                <span class="price-paid">{{ $product['price_format'] }}</span>
              @endif
            </div>
            <span class="plugin-view-btn">
              {{ __('common/base.view') }}<i class="bi bi-arrow-right ms-1"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </a>
@endif
