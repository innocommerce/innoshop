@if(is_array($product) && $product)
  <a href="{{ panel_route('plugin-market.show', $product['product_id']) }}" class="text-decoration-none">
    <div class="card product-item-card product-item-card-plugin border-0 shadow-sm h-100 position-relative overflow-hidden bg-white">
      <div class="position-absolute top-0 start-0 w-100 product-gradient-bar"></div>
      <div class="card-body p-3 d-flex flex-column h-100">
        <!-- Logo and Title Row -->
        <div class="d-flex align-items-start gap-3 mb-3">
          <img src="{{ $product['image_small'] ?? ''}}" 
               class="rounded product-logo flex-shrink-0" 
               alt="{{ $product['name'] }}">
          <div class="flex-grow-1 min-w-0">
            <h5 class="card-title text-dark mb-1 fw-semibold product-title">
              {{ $product['name'] }}
            </h5>
            @if($product['seller_name'])
              <div class="d-flex align-items-center">
                <span class="text-muted small">{{ $product['seller_name'] }}</span>
              </div>
            @endif
          </div>
        </div>

        <!-- Meta Info -->
        <div class="mb-3 flex-grow-1">
          <div class="d-flex align-items-center gap-3 flex-wrap">
            @if($product['rating'] ?? 0)
              <div class="d-flex align-items-center">
                <div class="text-warning me-1 product-rating">
                  @for($i = 1; $i <= 5; $i++)
                    <i class="bi bi-star{{ $i <= $product['rating'] ? '-fill' : '' }}"></i>
                  @endfor
                </div>
                <span class="text-muted small ms-1">({{ number_format($product['reviews_count'] ?? 0) }})</span>
              </div>
            @elseif($product['reviews_count'] ?? 0)
              <div class="d-flex align-items-center">
                <span class="text-muted small">
                  <i class="bi bi-chat-left-text me-1"></i>{{ number_format($product['reviews_count']) }}
                </span>
              </div>
            @endif

            @if($product['active_installations'] ?? 0)
              <div class="d-flex align-items-center">
                <span class="text-muted small">
                  <i class="bi bi-download me-1"></i>{{ number_format($product['active_installations']) }}+
                </span>
              </div>
            @elseif($product['downloaded'] ?? 0)
              <div class="d-flex align-items-center">
                <span class="text-muted small">
                  <i class="bi bi-download me-1"></i>{{ number_format($product['downloaded']) }}
                </span>
              </div>
            @endif
          </div>
        </div>

        <!-- Price and Action -->
        <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
          <div class="fw-bold product-price">
            {{ $product['price_format'] }}
          </div>
          <span class="badge rounded px-3 py-1 product-view-badge">
            {{ __('panel/common.view') }}
          </span>
        </div>
      </div>
    </div>
  </a>
@endif
