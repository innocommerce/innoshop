@push('header')
  <script src="{{ asset('vendor/vue/3.5/vue.global.prod.js') }}"></script>
@endpush

<div class="tab-pane fade mt-4" id="options-tab-pane" role="tabpanel"
     aria-labelledby="options-tab" tabindex="3">
  <div class="row">
    <div class="col-12">
      <div class="card" id="product-options-app">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('panel/product.product_options') }}</h5>
          <small class="text-muted">{{ __('panel/product.product_options_description') }}</small>
        </div>
        <div class="card-body">
          <!-- Hidden field for form submission -->
          <input type="hidden" name="product_options" :value="JSON.stringify(getFormData())">
          
          <div class="row">
            <!-- Left side: Available options -->
            <div class="col-md-6">
              <div class="border rounded p-3 h-100">
                <h6 class="mb-3 d-flex justify-content-between align-items-center">
                  <span>
                    <i class="bi bi-list-ul me-2"></i>{{ __('panel/product.available_options') }}
                    <span class="badge bg-secondary ms-2">@{{ availableOptionsFiltered.length }}</span>
                  </span>
                  <a href="{{ panel_route('options.index') }}" class="btn btn-sm btn-outline-primary" target="_blank" title="{{ __('panel/options.option_management') }}">
                    <i class="bi bi-gear me-1"></i>{{ __('panel/options.option_management') }}
                  </a>
                </h6>
                
                <!-- Search box -->
                <div class="mb-3">
                  <div class="input-group">
                    <span class="input-group-text">
                      <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control" v-model="searchTerm" 
                           placeholder="{{ __('panel/product.search_placeholder_options') }}">
                  </div>
                </div>

                <!-- Available options list -->
                <div style="max-height: 500px; overflow-y: auto;">
                  <div v-if="loading" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                    <span>{{ __('common.loading') }}...</span>
                  </div>
                  
                  <div v-else-if="availableOptionsFiltered.length === 0" class="text-center py-4 text-muted">
                    <i class="bi bi-inbox display-6 d-block mb-2"></i>
                    <p class="mb-0">{{ __('panel/product.no_options_available') }}</p>
                  </div>
                  
                  <div v-else>
                    <div v-for="option in availableOptionsFiltered" :key="option.id" class="option-card mb-2">
                      <div class="card">
                        <div class="card-body p-3">
                          <div class="d-flex align-items-center">
                            <input type="checkbox" class="form-check-input me-3" 
                                   :id="`available-${option.id}`" 
                                   @change="selectOption(option)">
                            <div class="flex-grow-1">
                              <label class="form-check-label fw-medium" :for="`available-${option.id}`">
                                @{{ option.name }}
                              </label>
                              <div class="text-muted small">
                                @{{ option.type }} • @{{ option.option_values_count || 0 }} {{ __('panel/product.option_values_count') }}
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right side: Selected options -->
            <div class="col-md-6">
              <div class="border rounded p-3 h-100">
                <h6 class="mb-3">
                  <i class="bi bi-check-square me-2"></i>{{ __('panel/product.configured_options') }}
                  <span class="badge bg-primary ms-2">@{{ selectedOptions.length }}</span>
                </h6>

                <!-- Selected options list -->
                <div style="max-height: 500px; overflow-y: auto;">
                  <div v-if="selectedOptions.length === 0" class="text-center py-4 text-muted">
                    <i class="bi bi-inbox display-6 d-block mb-2"></i>
                    <p class="mb-0">{{ __('panel/common.no_data') }}</p>
                    <small>{{ __('panel/product.select_option') }}</small>
                  </div>
                  
                  <div v-else>
                    <div v-for="option in selectedOptions" :key="option.id" class="selected-option-card mb-3">
                      <div class="card border-primary">
                        <div class="card-header bg-light py-2">
                          <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">@{{ option.name }}</h6>
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                    @click="removeOption(option.id)">
                              <i class="bi bi-trash"></i>
                            </button>
                          </div>
                        </div>
                        <div class="card-body p-3">
                          <div class="option-values-config">
                            <div class="table-responsive">
                              <table class="table table-sm mb-0">
                                <thead>
                                  <tr>
                                    <th width="40">
                                      <input type="checkbox" class="form-check-input" 
                                             :checked="isAllValuesSelected(option)"
                                             :indeterminate="isSomeValuesSelected(option)"
                                             @change="toggleAllValues(option)">
                                    </th>
                                    <th>{{ __('panel/product.option_values') }}</th>
                                    <th width="120">{{ __('panel/product.price_adjustment') }}</th>
                                    <th width="100">{{ __('panel/product.stock_quantity') }}</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr v-for="value in option.values" :key="value.id">
                                    <td>
                                      <input type="checkbox" class="form-check-input" 
                                             v-model="value.selected"
                                             @change="toggleValueInputs(value)">
                                    </td>
                                    <td>
                                      <div class="d-flex align-items-center">
                                        <img v-if="value.image_url" :src="value.image_url" :alt="value.name" 
                                             class="me-2" style="width: 24px; height: 24px; object-fit: cover; border-radius: 3px;">
                                        <span>@{{ value.name }}</span>
                                      </div>
                                    </td>
                                    <td>
                                      <div class="input-group input-group-sm">
                                        <input type="number" step="0.01" 
                                               class="form-control" 
                                               v-model="value.price_adjustment"
                                               placeholder="0.00"
                                               :disabled="!value.selected">
                                        <span class="input-group-text">{{ system_setting('base.currency', 'USD') }}</span>
                                      </div>
                                    </td>
                                    <td>
                                      <input type="number" 
                                             class="form-control form-control-sm" 
                                             v-model="value.stock_quantity"
                                             placeholder="0"
                                             :disabled="!value.selected">
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('footer')
<script>
// Vue application for product options page - using unique app name to avoid conflicts
const { 
  createApp: createOptionsApp, 
  ref: optionsRef, 
  computed: optionsComputed, 
  onMounted: optionsOnMounted 
} = Vue;

createOptionsApp({
  setup() {
    // Reactive data
    const availableOptions = optionsRef([]);
    const selectedOptions = optionsRef([]);
    const searchTerm = optionsRef('');
    const loading = optionsRef(false);

    // Computed properties
    const availableOptionsFiltered = optionsComputed(() => {
      const selectedIds = selectedOptions.value.map(opt => opt.id);
      // Filter out selected options and options without values
      const filtered = availableOptions.value.filter(opt => 
        !selectedIds.includes(opt.id) && 
        opt.option_values_count > 0  // Only show options with values
      );
      
      if (!searchTerm.value) {
        return filtered;
      }
      
      return filtered.filter(opt => 
        opt.name.toLowerCase().includes(searchTerm.value.toLowerCase())
      );
    });

    // Initialize data
    optionsOnMounted(() => {
      loadAvailableOptions();
      loadSelectedOptions();
    });

    // Load available options
    const loadAvailableOptions = async () => {
      loading.value = true;
      try {
        const response = await fetch(urls.panel_base + '/options/available', {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });
        
        const data = await response.json();
        if (data.success) {
          availableOptions.value = data.data.options || [];
        } else {
          showError('{{ __("panel/product.load_options_error") }}');
        }
      } catch (error) {
        console.error('Error loading options:', error);
        showError('{{ __("panel/product.load_options_error") }}');
      } finally {
        loading.value = false;
      }
    };

    // Pre-prepared existing product options data (passed from controller)
    const existingProductOptions = @json($existingProductOptions ?? []);

    // Load selected options (from existing product data)
    const loadSelectedOptions = async () => {
      for (const productOption of existingProductOptions) {
        const option = {
          id: productOption.option_id,
          name: productOption.name,
          type: productOption.type,
          option_values_count: productOption.option_values_count,
          values: []
        };
        
        // Load option values
        await loadOptionValues(option);
        selectedOptions.value.push(option);
      }
    };

    // Pre-prepared existing option values configuration data (passed from controller)
    const existingOptionValues = @json($existingOptionValues ?? []);

    // Load option values
    const loadOptionValues = async (option) => {
      try {
        const response = await fetch(urls.panel_base + '/options/' + option.id + '/values', {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });
        
        const data = await response.json();
        if (data.success) {
          option.values = data.data.option_values.map(value => {
            // Check if existing option value configuration exists
            const existingValue = existingOptionValues.find(pov => 
              pov.option_value_id === value.id && pov.option_id === option.id
            );
            
            if (existingValue) {
              return {
                ...value,
                selected: true,
                price_adjustment: parseFloat(existingValue.price_adjustment),
                stock_quantity: parseInt(existingValue.stock_quantity)
              };
            }
            
            return {
              ...value,
              selected: false,
              price_adjustment: 0,
              stock_quantity: 0
            };
          });
        }
      } catch (error) {
        console.error('Error loading option values:', error);
      }
    };

    // Select option
    const selectOption = async (option) => {
      const newOption = { ...option, values: [] };
      await loadOptionValues(newOption);
      selectedOptions.value.push(newOption);
    };

    // Remove option
    const removeOption = (optionId) => {
      selectedOptions.value = selectedOptions.value.filter(opt => opt.id !== optionId);
    };

    // Toggle value input status
    const toggleValueInputs = (value) => {
      if (!value.selected) {
        value.price_adjustment = 0;
        value.stock_quantity = 0;
      }
    };

    // Check if all option values are selected
    const isAllValuesSelected = (option) => {
      return option.values.length > 0 && option.values.every(value => value.selected);
    };

    // Check if some option values are selected
    const isSomeValuesSelected = (option) => {
      const selectedCount = option.values.filter(value => value.selected).length;
      return selectedCount > 0 && selectedCount < option.values.length;
    };

    // Select all/deselect all option values
    const toggleAllValues = (option) => {
      const allSelected = isAllValuesSelected(option);
      option.values.forEach(value => {
        value.selected = !allSelected;
        if (!value.selected) {
          value.price_adjustment = 0;
          value.stock_quantity = 0;
        }
      });
    };

    // Get form data
    const getFormData = () => {
      return selectedOptions.value.map(option => ({
        option_id: option.id,
        values: option.values.filter(value => value.selected).map(value => ({
          option_value_id: value.id,
          price_adjustment: value.price_adjustment || 0,
          stock_quantity: value.stock_quantity || 0
        }))
      }));
    };

    // Show error message
    const showError = (message) => {
      if (typeof inno !== 'undefined' && inno.msg) {
        inno.msg(message, 'error');
      } else {
        alert(message);
      }
    };

    return {
      availableOptions,
      selectedOptions,
      searchTerm,
      loading,
      availableOptionsFiltered,
      selectOption,
      removeOption,
      toggleValueInputs,
      isAllValuesSelected,
      isSomeValuesSelected,
      toggleAllValues,
      getFormData
    };
  }
}).mount('#product-options-app');
</script>
@endpush