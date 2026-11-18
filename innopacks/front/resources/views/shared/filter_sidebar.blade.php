@push('header')
<div id="overlay"></div>
@endpush

<div class="filter-sidebar" id="filterSidebar">
  <!-- Category filter -->
  <div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-light border-0">
      <h6 class="mb-0 text-dark fw-semibold">{{ __('front/category.category') }}</h6>
    </div>
    <div class="card-body p-0">
      <div class="accordion accordion-flush" id="filter-category">
        @foreach ($categories as $key => $category)
        <div class="accordion-item border-0">
          <div class="accordion-header">
            <div class="d-flex justify-content-between align-items-center p-3 category-item">
              <a href="{{ $category['url'] }}" class="category-link text-decoration-none text-dark fw-medium d-flex align-items-center">
                <i class="bi bi-grid-3x3-gap me-2 text-muted"></i>
                {{ $category['name'] }}
              </a>
              @if ($category['children'])
              <button class="btn btn-sm category-toggle collapsed" type="button" 
                      data-bs-toggle="collapse" data-bs-target="#filter-collapse-{{ $key }}" 
                      aria-expanded="false" aria-controls="filter-collapse-{{ $key }}">
                <i class="bi bi-chevron-down"></i>
              </button>
              @endif
            </div>
          </div>
          @if ($category['children'])
          <div id="filter-collapse-{{ $key }}" class="accordion-collapse collapse" data-bs-parent="#filter-category">
            <div class="accordion-body py-0">
              <div class="accordion accordion-flush" id="filter-category-{{ $key }}">
                @foreach ($category['children'] as $child)
                <div class="accordion-item border-0">
                  <div class="accordion-header">
                    <div class="d-flex justify-content-between align-items-center p-2 ps-4 subcategory-item">
                      <a href="{{ $child['url'] }}" class="subcategory-link text-decoration-none text-muted d-flex align-items-center">
                        <i class="bi bi-arrow-right-short me-2"></i>
                        {{ $child['name'] }}
                      </a>
                      @if (isset($child['children']) && $child['children'])
                      <button class="btn btn-sm subcategory-toggle collapsed" type="button" 
                              data-bs-toggle="collapse" data-bs-target="#filter-collapse-{{ $key }}-{{ $loop->index }}" 
                              aria-expanded="false" aria-controls="filter-collapse-{{ $key }}-{{ $loop->index }}">
                        <i class="bi bi-chevron-down"></i>
                      </button>
                      @endif
                    </div>
                  </div>
                  @if (isset($child['children']) && $child['children'])
                  <div id="filter-collapse-{{ $key }}-{{ $loop->index }}" class="accordion-collapse collapse" data-bs-parent="#filter-category-{{ $key }}">
                    <div class="accordion-body py-0">
                      @foreach ($child['children'] as $subChild)
                      <div class="ps-5 py-2 subsubcategory-item">
                        <a href="{{ $subChild['url'] }}" class="subsubcategory-link text-decoration-none text-muted d-flex align-items-center">
                          <i class="bi bi-circle me-1" style="font-size: 0.5rem;"></i>
                          {{ $subChild['name'] }}
                        </a>
                      </div>
                      @endforeach
                    </div>
                  </div>
                  @endif
                </div>
                @endforeach
              </div>
            </div>
          </div>
          @endif
        </div>
        @endforeach
      </div>
    </div>
  </div>

  <!-- Price filter -->
  <div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-light border-0">
      <h6 class="mb-0 text-dark fw-semibold">{{ __('front/product.price') }}</h6>
    </div>
    <div class="card-body">
      <div class="mb-3 d-flex flex-column">
        <div class="price-inputs-container">
          <div class="row g-2">
            <div class="col">
              <label class="form-label small">{{ __('front/product.from') }}</label>
              <input type="number" class="form-control form-control-sm" id="minPrice" placeholder="0" value="{{ $price_filters['min_price'] }}">
            </div>
            <div class="col">
              <label class="form-label small">{{ __('front/product.to') }}</label>
              <input type="number" class="form-control form-control-sm" id="maxPrice" placeholder="1000" value="{{ $price_filters['max_price'] }}">
            </div>
          </div>
        </div>
        
        <div class="dual-range-slider">
          <div class="slider-track"></div>
          <div class="slider-range"></div>
          <div class="slider-thumb slider-thumb-min" data-thumb="min"></div>
          <div class="slider-thumb slider-thumb-max" data-thumb="max"></div>
        </div>
        
        <button type="button" class="btn btn-primary btn-sm w-100" onclick="filterByPrice()">
          {{ __('front/product.filter') }}
        </button>
      </div>
    </div>
  </div>

  <!-- Brand filter -->
  @if(isset($brands) && count($brands) > 0)
  <div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-light border-0">
      <h6 class="mb-0 text-dark fw-semibold">{{ __('front/product.brand') }}</h6>
    </div>
    <div class="card-body">
      @foreach ($brands as $brand)
      <div class="form-check mb-2">
        <input class="form-check-input brand-checkbox" type="checkbox" value="{{ $brand['id'] }}" 
               id="brand-{{ $brand['id'] }}" {{ $brand['selected'] ? 'checked' : '' }}>
        <label class="form-check-label" for="brand-{{ $brand['id'] }}">
          {{ $brand['name'] }}
        </label>
      </div>
      @endforeach
    </div>
  </div>
  @endif

  <!-- Variant dimension filter (e.g. Size across products) -->
  @if(isset($variants) && count($variants) > 0)
  @foreach ($variants as $dimension)
  <div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-light border-0">
      <h6 class="mb-0 text-dark fw-semibold">{{ $dimension['name'] }}</h6>
    </div>
    <div class="card-body">
      @foreach ($dimension['values'] as $value)
      <div class="form-check mb-2">
        <input class="form-check-input variant-checkbox" type="checkbox"
               value="{{ $value['value_ids'] }}"
               data-dimension="{{ $dimension['name'] }}"
               id="variant-{{ $loop->parent->index }}-{{ $loop->index }}"
               {{ $value['selected'] ? 'checked' : '' }}>
        <label class="form-check-label" for="variant-{{ $loop->parent->index }}-{{ $loop->index }}">
          {{ $value['name'] }}
        </label>
      </div>
      @endforeach
    </div>
  </div>
  @endforeach
  @endif

  <!-- Attribute filter -->
  @if(isset($attributes) && count($attributes) > 0)
  @foreach ($attributes as $attribute)
  <div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-light border-0">
      <h6 class="mb-0 text-dark fw-semibold">{{ $attribute['name'] }}</h6>
    </div>
    <div class="card-body">
      @if (($attribute['type'] ?? 'select') === 'number')
      <div class="mb-2 d-flex flex-column">
        <div class="row g-2">
          <div class="col">
            <label class="form-label small">{{ __('front/product.from') }}</label>
            <input type="number" step="any" class="form-control form-control-sm attribute-range-min"
                   data-attribute-id="{{ $attribute['id'] }}" value="{{ $attribute['min'] }}">
          </div>
          <div class="col">
            <label class="form-label small">{{ __('front/product.to') }}</label>
            <input type="number" step="any" class="form-control form-control-sm attribute-range-max"
                   data-attribute-id="{{ $attribute['id'] }}" value="{{ $attribute['max'] }}">
          </div>
        </div>
        <button type="button" class="btn btn-primary btn-sm w-100 mt-2"
                onclick="filterByAttributeRange({{ $attribute['id'] }})">
          {{ __('front/product.filter') }}
        </button>
      </div>
      @else
      @foreach ($attribute['values'] as $value)
      <div class="form-check mb-2">
        <input class="form-check-input attribute-checkbox" type="checkbox"
               value="{{ $value['id'] }}"
               data-attribute-id="{{ $attribute['id'] }}"
               id="attribute-{{ $attribute['id'] }}-{{ $value['id'] }}"
               {{ $value['selected'] ? 'checked' : '' }}>
        <label class="form-check-label" for="attribute-{{ $attribute['id'] }}-{{ $value['id'] }}">
          {{ $value['name'] }}
        </label>
      </div>
      @endforeach
      @endif
    </div>
  </div>
  @endforeach
  @endif

  <!-- Stock status filter -->
  <div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-light border-0">
      <h6 class="mb-0 text-dark fw-semibold">{{ __('front/product.availability') }}</h6>
    </div>
    <div class="card-body">
      <div class="form-check mb-2">
        <input class="form-check-input availability-checkbox" type="checkbox" value="in_stock" 
               id="availability-in-stock" {{ $availability['in_stock'] ? 'checked' : '' }}>
        <label class="form-check-label" for="availability-in-stock">
          {{ __('front/product.in_stock') }}
        </label>
      </div>
      <div class="form-check mb-2">
        <input class="form-check-input availability-checkbox" type="checkbox" value="out_of_stock" 
               id="availability-out-of-stock" {{ $availability['out_of_stock'] ? 'checked' : '' }}>
        <label class="form-check-label" for="availability-out-of-stock">
          {{ __('front/product.out_of_stock') }}
        </label>
      </div>
    </div>
  </div>

  <!-- Clear filters (wrapper keeps spacing from drawer bottom / column end; see filter-sidebar.scss) -->
  <div class="d-grid filter-sidebar-clear-wrap">
    <button type="button" class="btn clear-filters-btn" onclick="clearAllFilters()">
      <i class="bi bi-x-circle me-2"></i>
      {{ __('front/product.clear_filters') }}
    </button>
  </div>
</div>

@include('shared.filter_scripts')