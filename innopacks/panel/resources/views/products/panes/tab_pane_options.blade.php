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
          <!-- 隐藏字段用于表单提交 -->
          <input type="hidden" name="product_options" :value="JSON.stringify(getFormData())">
          
          <div class="row">
            <!-- 左侧：可用选项 -->
            <div class="col-md-6">
              <div class="border rounded p-3 h-100">
                <h6 class="mb-3">
                  <i class="bi bi-list-ul me-2"></i>可用选项
                  <span class="badge bg-secondary ms-2">@{{ availableOptionsFiltered.length }}</span>
                </h6>
                
                <!-- 搜索框 -->
                <div class="mb-3">
                  <div class="input-group">
                    <span class="input-group-text">
                      <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control" v-model="searchTerm" 
                           placeholder="搜索选项...">
                  </div>
                </div>

                <!-- 可用选项列表 -->
                <div style="max-height: 500px; overflow-y: auto;">
                  <div v-if="loading" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                    <span>加载中...</span>
                  </div>
                  
                  <div v-else-if="availableOptionsFiltered.length === 0" class="text-center py-4 text-muted">
                    <i class="bi bi-inbox display-6 d-block mb-2"></i>
                    <p class="mb-0">暂无可用选项</p>
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
                                @{{ option.type }} • @{{ option.option_values_count || 0 }} 个选项值
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

            <!-- 右侧：已选项 -->
            <div class="col-md-6">
              <div class="border rounded p-3 h-100">
                <h6 class="mb-3">
                  <i class="bi bi-check-square me-2"></i>已选项
                  <span class="badge bg-primary ms-2">@{{ selectedOptions.length }}</span>
                </h6>

                <!-- 已选项列表 -->
                <div style="max-height: 500px; overflow-y: auto;">
                  <div v-if="selectedOptions.length === 0" class="text-center py-4 text-muted">
                    <i class="bi bi-inbox display-6 d-block mb-2"></i>
                    <p class="mb-0">暂无选项</p>
                    <small>从左侧选择选项</small>
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
                                    <th>选项值</th>
                                    <th width="120">加价金额</th>
                                    <th width="100">库存</th>
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
// 产品选项页面的Vue应用 - 使用唯一的应用名称避免冲突
const { 
  createApp: createOptionsApp, 
  ref: optionsRef, 
  computed: optionsComputed, 
  onMounted: optionsOnMounted 
} = Vue;

createOptionsApp({
  setup() {
    // 响应式数据
    const availableOptions = optionsRef([]);
    const selectedOptions = optionsRef([]);
    const searchTerm = optionsRef('');
    const loading = optionsRef(false);

    // 计算属性
    const availableOptionsFiltered = optionsComputed(() => {
      const selectedIds = selectedOptions.value.map(opt => opt.id);
      // 过滤掉已选择的选项和没有选项值的选项
      const filtered = availableOptions.value.filter(opt => 
        !selectedIds.includes(opt.id) && 
        opt.option_values_count > 0  // 只显示有选项值的选项
      );
      
      if (!searchTerm.value) {
        return filtered;
      }
      
      return filtered.filter(opt => 
        opt.name.toLowerCase().includes(searchTerm.value.toLowerCase())
      );
    });

    // 初始化数据
    optionsOnMounted(() => {
      loadAvailableOptions();
      loadSelectedOptions();
    });

    // 加载可用选项
    const loadAvailableOptions = async () => {
      loading.value = true;
      try {
        const response = await fetch(urls.base_url + '/options/available', {
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
          showError('加载选项失败');
        }
      } catch (error) {
        console.error('Error loading options:', error);
        showError('加载选项失败');
      } finally {
        loading.value = false;
      }
    };

    // 预先准备已存在的产品选项数据（从控制器传递）
    const existingProductOptions = @json($existingProductOptions ?? []);

    // 加载已选项（从现有产品数据）
    const loadSelectedOptions = async () => {
      for (const productOption of existingProductOptions) {
        const option = {
          id: productOption.option_id,
          name: productOption.name,
          type: productOption.type,
          option_values_count: productOption.option_values_count,
          values: []
        };
        
        // 加载选项值
        await loadOptionValues(option);
        selectedOptions.value.push(option);
      }
    };

    // 预先准备已存在的选项值配置数据（从控制器传递）
    const existingOptionValues = @json($existingOptionValues ?? []);

    // 加载选项值
    const loadOptionValues = async (option) => {
      try {
        const response = await fetch(urls.base_url + '/options/' + option.id + '/values', {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });
        
        const data = await response.json();
        if (data.success) {
          option.values = data.data.option_values.map(value => {
            // 检查是否已存在的选项值配置
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

    // 选择选项
    const selectOption = async (option) => {
      const newOption = { ...option, values: [] };
      await loadOptionValues(newOption);
      selectedOptions.value.push(newOption);
    };

    // 移除选项
    const removeOption = (optionId) => {
      selectedOptions.value = selectedOptions.value.filter(opt => opt.id !== optionId);
    };

    // 切换选项值输入框状态
    const toggleValueInputs = (value) => {
      if (!value.selected) {
        value.price_adjustment = 0;
        value.stock_quantity = 0;
      }
    };

    // 检查是否所有选项值都被选中
    const isAllValuesSelected = (option) => {
      return option.values.length > 0 && option.values.every(value => value.selected);
    };

    // 检查是否部分选项值被选中
    const isSomeValuesSelected = (option) => {
      const selectedCount = option.values.filter(value => value.selected).length;
      return selectedCount > 0 && selectedCount < option.values.length;
    };

    // 全选/取消全选选项值
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

    // 获取表单数据
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

    // 显示错误信息
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