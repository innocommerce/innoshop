{{-- Brand intro section (before product list) --}}
@if($brand)
  <div class="brand-intro-section mb-4">
    <div class="card border-0 shadow-sm bg-gradient">
      <div class="card-body p-4">
        <div class="row align-items-center">
          <div class="col-md-3 text-center mb-3 mb-md-0">
            <div class="brand-image-wrapper position-relative">
              <img src="{{ image_resize($brand->logo ?? '', 200, 200) }}" 
                   alt="{{ $brand->name }}" 
                   class="img-fluid rounded-3 shadow brand-hero-image">
              <div class="image-overlay"></div>
            </div>
          </div>
          <div class="col-md-9">
            <div class="brand-content-wrapper">
              <h1 class="brand-title display-6 fw-bold mb-3 text-primary">{{ $brand->name }}</h1>
              
              {{-- Brand description or summary if available --}}
              @if($brand->description ?? false)
                <div class="brand-summary mb-4">
                  <div class="summary-content text-muted fs-5 lh-lg">
                    {!! $brand->description !!}
                  </div>
                </div>
              @endif

              {{-- Brand additional info --}}
              <div class="brand-meta d-flex flex-wrap gap-3">
                @if($brand->first)
                  <span class="badge bg-light text-dark fs-6">
                    <i class="bi bi-tag-fill me-1"></i>{{ __('front/brand.first_letter') }}: {{ $brand->first }}
                  </span>
                @endif
                
                <span class="badge bg-primary fs-6">
                  <i class="bi bi-shop me-1"></i>{{ __('front/brand.brand_products') }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endif