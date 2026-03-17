@if(is_array($product) && $product)
  <a href="{{ panel_route('theme-market.show', $product['product_id']) }}" class="text-decoration-none h-100 d-block">
    <div class="card theme-card h-100 border-0 shadow-sm overflow-hidden">
      {{-- Preview Image --}}
      <div class="theme-preview-wrapper position-relative">
        <img src="{{ $product['image_big'] ?? ''}}"
             class="theme-preview-img"
             alt="{{ $product['name'] }}"
             onerror="this.src='{{ asset('images/default-theme.png') }}'">

        {{-- Gradient Overlay --}}
        <div class="theme-preview-overlay"></div>

        {{-- Theme Badge --}}
        <div class="position-absolute top-0 end-0 m-2">
          <span class="theme-type-badge">
            <i class="bi bi-palette"></i>
          </span>
        </div>

        {{-- Quick Info Overlay --}}
        <div class="theme-quick-info">
          @if(($product['price'] ?? 0) == 0)
            <span class="theme-free-badge">
              <i class="bi bi-gift me-1"></i>{{ __('panel/plugin.free') }}
            </span>
          @endif
        </div>
      </div>

      <div class="card-body p-3">
        {{-- Title & Author --}}
        <div class="mb-2">
          <h6 class="theme-name mb-1 fw-semibold text-dark">
            {{ $product['name'] }}
          </h6>
          @if($product['seller_name'])
            <span class="theme-author">
              <i class="bi bi-person-circle me-1"></i>{{ $product['seller_name'] }}
            </span>
          @endif
        </div>

        {{-- Stats --}}
        <div class="theme-stats d-flex align-items-center gap-3 mb-3">
          @if($product['rating'] ?? 0)
            <div class="d-flex align-items-center">
              <span class="theme-rating-stars">
                @for($i = 1; $i <= 5; $i++)
                  <i class="bi bi-star{{ $i <= round($product['rating']) ? '-fill' : '' }}"></i>
                @endfor
              </span>
              <span class="theme-rating-count ms-1">{{ number_format($product['reviews_count'] ?? 0) }}</span>
            </div>
          @endif

          @if($product['active_installations'] ?? 0)
            <div class="theme-stat-item">
              <i class="bi bi-box-seam me-1"></i>
              <span>{{ $product['active_installations'] > 1000 ? round($product['active_installations']/1000, 1) . 'K' : $product['active_installations'] }}+</span>
            </div>
          @elseif($product['downloaded'] ?? 0)
            <div class="theme-stat-item">
              <i class="bi bi-download me-1"></i>
              <span>{{ number_format($product['downloaded']) }}</span>
            </div>
          @endif
        </div>

        {{-- Footer --}}
        <div class="theme-card-footer mt-auto pt-3">
          <div class="d-flex justify-content-between align-items-center">
            <div class="theme-price">
              @if(($product['price'] ?? 0) == 0)
                <span class="price-free">
                  <i class="bi bi-gift me-1"></i>{{ __('panel/plugin.free') }}
                </span>
              @else
                <span class="price-paid">{{ $product['price_format'] }}</span>
              @endif
            </div>
            <span class="theme-view-btn">
              {{ __('common/base.view') }}<i class="bi bi-arrow-right ms-1"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </a>
@endif
