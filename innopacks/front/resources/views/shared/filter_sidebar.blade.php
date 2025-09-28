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

  <!-- Attribute filter -->
  @if(isset($attributes) && count($attributes) > 0)
  @foreach ($attributes as $attribute)
  <div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-light border-0">
      <h6 class="mb-0 text-dark fw-semibold">{{ $attribute['name'] }}</h6>
    </div>
    <div class="card-body">
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

  <!-- Clear filters -->
  <div class="d-grid">
    <button type="button" class="btn clear-filters-btn" onclick="clearAllFilters()">
      <i class="bi bi-x-circle me-2"></i>
      {{ __('front/product.clear_filters') }}
    </button>
  </div>
</div>

@push('footer')
<script>
  /**
   * Filter sidebar class
   * Handles all filter functionality including price range, brand, attributes, and stock status
   */
  class FilterSidebar {
    constructor() {
      this.maxPrice = 1000;
      this.init();
    }

    /**
     * Initialize the filter sidebar
     */
    init() {
      this.initDualRangeSlider();
      this.bindEvents();
      this.highlightActiveCategory();
    }

    /**
     * Get URL parameters
     */
    getUrlParams() {
      const url = new URL(window.location);
      const params = new URLSearchParams(url.search);
      return { url, params };
    }

    /**
     * Update URL
     */
    updateUrl(params) {
      const { url } = this.getUrlParams();
      url.search = params.toString();
      window.location.href = url.toString();
    }

    /**
     * Update filter parameters
     */
    updateFilter(paramName, values, isNested = false) {
      const { params } = this.getUrlParams();
      
      // Remove existing parameters
      if (isNested) {
        const keysToRemove = [];
        for (let key of params.keys()) {
          if (key.startsWith(`${paramName}[`)) {
            keysToRemove.push(key);
          }
        }
        keysToRemove.forEach(key => params.delete(key));
      } else {
        params.delete(paramName);
      }
      
      // Add new parameters
      if (values && (Array.isArray(values) ? values.length > 0 : Object.keys(values).length > 0)) {
        if (isNested) {
          Object.keys(values).forEach(key => {
            if (values[key].length > 0) {
              params.set(`${paramName}[${key}]`, values[key].join(','));
            }
          });
        } else {
          params.set(paramName, Array.isArray(values) ? values.join(',') : values);
        }
      }
      
      this.updateUrl(params);
    }

    /**
     * Initialize dual slider price range
     */
    initDualRangeSlider() {
      const slider = document.querySelector('.dual-range-slider');
      const minThumb = slider.querySelector('.slider-thumb-min');
      const maxThumb = slider.querySelector('.slider-thumb-max');
      const sliderRange = slider.querySelector('.slider-range');
      const minInput = document.getElementById('minPrice');
      const maxInput = document.getElementById('maxPrice');
      
      if (!slider || !minThumb || !maxThumb || !sliderRange || !minInput || !maxInput) return;
      
      // Get price values from URL parameters
      const urlParams = new URLSearchParams(window.location.search);
      let minValue = parseInt(urlParams.get('min_price')) || 0;
      let maxValue = parseInt(urlParams.get('max_price')) || this.maxPrice;
      
      // Ensure values are within valid range
      minValue = Math.max(0, Math.min(minValue, this.maxPrice));
      maxValue = Math.max(minValue, Math.min(maxValue, this.maxPrice));
      
      // Initialize input values
      minInput.value = minValue;
      maxInput.value = maxValue;
      
      // Update slider position and range display
      this.updateSliderDisplay(minValue, maxValue, minThumb, maxThumb, sliderRange);
      
      // Bind drag events
      this.bindDragEvents(minThumb, maxThumb, sliderRange, minInput, maxInput, slider);
      
      // Bind input events
      this.bindInputEvents(minInput, maxInput, minThumb, maxThumb, sliderRange);
    }
    
    /**
     * Update slider display
     */
    updateSliderDisplay(minValue, maxValue, minThumb, maxThumb, sliderRange) {
      const minPercent = (minValue / this.maxPrice) * 100;
      const maxPercent = (maxValue / this.maxPrice) * 100;
      
      minThumb.style.left = minPercent + '%';
      maxThumb.style.left = maxPercent + '%';
      sliderRange.style.left = minPercent + '%';
      sliderRange.style.right = (100 - maxPercent) + '%';
    }
    
    /**
     * Bind drag events
     */
    bindDragEvents(minThumb, maxThumb, sliderRange, minInput, maxInput, slider) {
      let isDragging = false;
      let currentThumb = null;
      
      const startDrag = (e, thumb) => {
        isDragging = true;
        currentThumb = thumb;
        document.addEventListener('mousemove', onDrag);
        document.addEventListener('mouseup', stopDrag);
        e.preventDefault();
      };
      
      const onDrag = (e) => {
        if (!isDragging || !currentThumb) return;
        
        const rect = slider.getBoundingClientRect();
        const percent = Math.max(0, Math.min(100, ((e.clientX - rect.left) / rect.width) * 100));
        const value = Math.round((percent / 100) * this.maxPrice);
        
        let minValue = parseInt(minInput.value);
        let maxValue = parseInt(maxInput.value);
        
        if (currentThumb === minThumb) {
          minValue = Math.min(value, maxValue);
          minInput.value = minValue;
        } else {
          maxValue = Math.max(value, minValue);
          maxInput.value = maxValue;
        }
        
        this.updateSliderDisplay(minValue, maxValue, minThumb, maxThumb, sliderRange);
      };
      
      const stopDrag = () => {
        isDragging = false;
        currentThumb = null;
        document.removeEventListener('mousemove', onDrag);
        document.removeEventListener('mouseup', stopDrag);
      };
      
      minThumb.addEventListener('mousedown', (e) => startDrag(e, minThumb));
      maxThumb.addEventListener('mousedown', (e) => startDrag(e, maxThumb));
      
      // Support touch devices
      minThumb.addEventListener('touchstart', (e) => {
        e.clientX = e.touches[0].clientX;
        startDrag(e, minThumb);
      });
      
      maxThumb.addEventListener('touchstart', (e) => {
        e.clientX = e.touches[0].clientX;
        startDrag(e, maxThumb);
      });
      
      document.addEventListener('touchmove', (e) => {
        if (isDragging) {
          e.clientX = e.touches[0].clientX;
          onDrag(e);
        }
      });
      
      document.addEventListener('touchend', stopDrag);
    }
    
    /**
     * Bind input events
     */
    bindInputEvents(minInput, maxInput, minThumb, maxThumb, sliderRange) {
      const updateFromInput = () => {
        let minValue = parseInt(minInput.value) || 0;
        let maxValue = parseInt(maxInput.value) || this.maxPrice;
        
        // Ensure values are within valid range
        minValue = Math.max(0, Math.min(minValue, this.maxPrice));
        maxValue = Math.max(minValue, Math.min(maxValue, this.maxPrice));
        
        // Update input display
        minInput.value = minValue;
        maxInput.value = maxValue;
        
        // Update slider display
        this.updateSliderDisplay(minValue, maxValue, minThumb, maxThumb, sliderRange);
      };
      
      minInput.addEventListener('input', updateFromInput);
      maxInput.addEventListener('input', updateFromInput);
      minInput.addEventListener('blur', updateFromInput);
      maxInput.addEventListener('blur', updateFromInput);
    }

    /**
     * Price filter
     */
    filterByPrice() {
      const minPrice = $('#minPrice').val();
      const maxPrice = $('#maxPrice').val();
      const { params } = this.getUrlParams();
      
      params.delete('min_price');
      params.delete('max_price');
      
      if (minPrice) params.set('min_price', minPrice);
      if (maxPrice) params.set('max_price', maxPrice);
      
      this.updateUrl(params);
    }

    /**
     * Brand filter
     */
    handleBrandFilter() {
      const selectedBrands = [];
      $('.brand-checkbox:checked').each(function() {
        selectedBrands.push($(this).val());
      });
      
      this.updateFilter('brands', selectedBrands);
    }

    /**
     * Attribute filter - Support multiple attribute groups filtering simultaneously
     */
    handleAttributeFilter(attributeId) {
      const { params } = this.getUrlParams();
      
      // Get selected values for current attribute group
      const selectedValues = [];
      $(`.attribute-checkbox[data-attribute-id="${attributeId}"]:checked`).each(function() {
        selectedValues.push($(this).val());
      });
      
      // Remove current attribute group parameters
      params.delete(`attributes[${attributeId}]`);
      
      // Add new parameters if there are selected values
      if (selectedValues.length > 0) {
        params.set(`attributes[${attributeId}]`, selectedValues.join(','));
      }
      
      this.updateUrl(params);
    }

    /**
     * Stock status filter
     */
    handleAvailabilityFilter() {
      const selectedAvailability = [];
      $('.availability-checkbox:checked').each(function() {
        selectedAvailability.push($(this).val());
      });
      
      this.updateFilter('availability', selectedAvailability);
    }

    /**
     * Clear all filters
     */
    clearAllFilters() {
      const { params } = this.getUrlParams();
      
      // Remove all filter parameters
      params.delete('min_price');
      params.delete('max_price');
      params.delete('brands');
      params.delete('availability');
      
      // Remove attribute filter parameters
      const keysToRemove = [];
      for (let key of params.keys()) {
        if (key.startsWith('attributes[')) {
          keysToRemove.push(key);
        }
      }
      keysToRemove.forEach(key => params.delete(key));
      
      this.updateUrl(params);
    }

    /**
     * Toggle sidebar display
     */
    toggleSidebar() {
      if ($(window).width() < 768) {
        $('#filterSidebar').css('transform', 'translateX(0)');
        $('#overlay').show();
      }
    }

    /**
     * Highlight current category
     */
    highlightActiveCategory() {
      const currentPath = window.location.pathname;
      
      $('#filter-category a').each(function() {
        const linkPath = new URL($(this).attr('href'), window.location.origin).pathname;
        
        if (linkPath === currentPath) {
          $(this).addClass('active');
          
          // Expand parent accordion
          $(this).parents('.accordion-item').each(function() {
            const collapseTarget = $(this).find('[data-bs-toggle="collapse"]').attr('data-bs-target');
            if (collapseTarget) {
              $(collapseTarget).addClass('show');
              $(this).find('[data-bs-toggle="collapse"]').removeClass('collapsed').attr('aria-expanded', 'true');
            }
          });
        }
      });
    }

    /**
     * Bind all events
     */
    bindEvents() {
      // Brand filter events
      $(document).on('change', '.brand-checkbox', () => {
        this.handleBrandFilter();
      });

      // Attribute filter events
      $(document).on('change', '.attribute-checkbox', (e) => {
        const attributeId = $(e.target).data('attribute-id');
        this.handleAttributeFilter(attributeId);
      });

      // Stock status filter events
      $(document).on('change', '.availability-checkbox', () => {
        this.handleAvailabilityFilter();
      });

      // Sidebar toggle events
      $('#toggleFilterSidebar').on('click', () => {
        this.toggleSidebar();
      });

      $('#overlay').on('click', () => {
        $('#filterSidebar').css('transform', 'translateX(100%)');
        $('#overlay').hide();
      });

      // Click outside to close sidebar
      $(document).on('click', (event) => {
        if ($(window).width() < 768 && !$(event.target).closest('#filterSidebar, #toggleFilterSidebar').length) {
          $('#filterSidebar').css('transform', 'translateX(100%)');
          $('#overlay').hide();
        }
      });

      // Window resize events
      $(window).resize(() => {
        if ($(window).width() >= 768) {
          $('#filterSidebar').css('transform', 'translateX(0)'); 
          $('#overlay').hide(); 
        } else {
          $('#filterSidebar').css('transform', 'translateX(100%)'); 
        }
      });
    }
  }

  // Global function, maintain backward compatibility
  let filterSidebar;

  function toggleSidebar() {
    filterSidebar.toggleSidebar();
  }

  function filterByPrice() {
    filterSidebar.filterByPrice();
  }

  function clearAllFilters() {
    filterSidebar.clearAllFilters();
  }

  // Initialize filter sidebar
  $(document).ready(function() {
    filterSidebar = new FilterSidebar();
  });
</script>
@endpush