{{-- Subcategories section --}}
@if($category->activeChildren && $category->activeChildren->count() > 0)
  <div class="subcategories-section mb-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-3">
        <h4 class="h6 fw-bold mb-2 text-secondary">
          <i class="bi bi-grid-3x3-gap me-2"></i>
          {{ __('front/category.subcategories') }}
        </h4>
        
        <div class="row g-2">
          @foreach($category->activeChildren as $subcategory)
            <div class="col-6 col-md-4 col-lg-3">
              <a href="{{ $subcategory->url }}" class="text-decoration-none">
                <div class="subcategory-card h-100 border rounded-2 p-2 hover-shadow transition-all d-flex align-items-center">
                  @if($subcategory->image)
                    <div class="subcategory-image me-3 flex-shrink-0">
                      <img src="{{ image_resize($subcategory->image, 48, 48) }}" 
                           alt="{{ $subcategory->fallbackName('name') }}"
                           class="img-fluid rounded-2" 
                           style="width: 48px; height: 48px; object-fit: cover;">
                    </div>
                  @endif
                  
                  <div class="subcategory-content flex-grow-1 {{ $subcategory->image ? 'text-start' : 'text-center' }}">
                    <div class="subcategory-name">
                      <span class="fw-medium text-dark" style="font-size: 0.85rem; line-height: 1.3;">{{ $subcategory->fallbackName('name') }}</span>
                    </div>
                    
                    @if($subcategory->products_count > 0)
                      <div class="subcategory-count mt-1">
                        <small class="text-muted" style="font-size: 0.75rem;">
                          <i class="bi bi-box-seam me-1" style="font-size: 0.7rem;"></i>
                          {{ $subcategory->products_count }} {{ __('front/category.products_count') }}
                        </small>
                      </div>
                    @endif
                  </div>
                </div>
              </a>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
@endif

<style>
.subcategory-card {
  transition: all 0.3s ease;
  background: #fff;
}

.subcategory-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
  border-color: var(--bs-primary) !important;
}

.hover-shadow {
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.transition-all {
  transition: all 0.3s ease;
}
</style>