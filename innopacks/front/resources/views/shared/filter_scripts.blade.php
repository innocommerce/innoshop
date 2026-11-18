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
     * Variant dimension filter - rebuilds the variant_values groups:
     * one group per dimension (OR within), groups joined by | (AND across)
     */
    handleVariantFilter() {
      const groups = {};
      $('.variant-checkbox:checked').each(function() {
        const dimension = $(this).data('dimension');
        const ids = ($(this).val() || '').split(',').filter(Boolean);
        groups[dimension] = [...new Set([...(groups[dimension] || []), ...ids])];
      });

      const value = Object.values(groups).map(group => group.join(',')).filter(group => group).join('|');
      this.updateFilter('variant_values', value);
    }

    /**
     * Number attribute range filter - attribute_ranges[ID]=min,max (either side optional)
     */
    filterByAttributeRange(attributeId) {
      const min = $(`.attribute-range-min[data-attribute-id="${attributeId}"]`).val();
      const max = $(`.attribute-range-max[data-attribute-id="${attributeId}"]`).val();
      const { params } = this.getUrlParams();

      params.delete(`attribute_ranges[${attributeId}]`);
      if (min !== '' || max !== '') {
        params.set(`attribute_ranges[${attributeId}]`, `${min},${max}`);
      }

      this.updateUrl(params);
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
      params.delete('variant_values');

      // Remove nested filter parameters (attributes / attribute ranges)
      const keysToRemove = [];
      for (let key of params.keys()) {
        if (key.startsWith('attributes[') || key.startsWith('attribute_ranges[')) {
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

      // Variant dimension filter events
      $(document).on('change', '.variant-checkbox', () => {
        this.handleVariantFilter();
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

  function filterByAttributeRange(attributeId) {
    filterSidebar.filterByAttributeRange(attributeId);
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
