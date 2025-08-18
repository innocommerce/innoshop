{{-- Category intro section (before product list) --}}
@if($category->fallbackName('summary'))
  <div class="category-intro-section mb-4">
    <div class="card border-0 shadow-sm bg-gradient">
      <div class="card-body p-4">
        <div class="row align-items-center">
          <div class="col-md-3 text-center mb-3 mb-md-0">
            <div class="category-image-wrapper position-relative">
              <img src="{{ image_resize($category->image ?? '', 200, 200) }}" 
                   alt="{{ $category->fallbackName('name') }}" 
                   class="img-fluid rounded-3 shadow category-hero-image">
              <div class="image-overlay"></div>
            </div>
          </div>
          <div class="col-md-9">
            <div class="category-content-wrapper">
              <h1 class="category-title display-6 fw-bold mb-3 text-primary">{{ $category->fallbackName('name') }}</h1>
              
              {{-- Category summary --}}
              @if($category->fallbackName('summary'))
                <div class="category-summary mb-4">
                  <div class="summary-content text-muted fs-5 lh-lg">
                    {!! $category->fallbackName('summary') !!}
                  </div>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endif