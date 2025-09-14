@push('header')
  <script src="{{ asset('vendor/vue/3.5/vue.global.prod.js') }}"></script>
  <script src="{{ asset('vendor/vuedraggable/sortable.min.js') }}"></script>
  <script src="{{ asset('vendor/vuedraggable/vuedraggable.umd.min.js') }}"></script>
  <style>
    .variant-values-container {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .variant-values-list {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }
    .variant-value-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 8px;
      border: 1px solid #dee2e6;
      border-radius: 6px;
      background: #f8f9fa;
      cursor: pointer;
      transition: all 0.2s;
      min-width: 80px;
    }
    .variant-value-item:hover {
      border-color: #0d6efd;
      background: #e7f1ff;
    }
    .variant-image-container {
      width: 40px;
      height: 40px;
      border: 2px dashed #dee2e6;
      border-radius: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 4px;
      cursor: pointer;
      transition: border-color 0.2s;
    }
    .variant-image-container:hover {
      border-color: #0d6efd;
    }
    .variant-value-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 2px;
    }
    .variant-placeholder-icon {
      font-size: 18px;
      color: #6c757d;
    }
    .variant-value-name {
      font-size: 12px;
      text-align: center;
      word-break: break-all;
      max-width: 80px;
    }
    .open-file-manager {
      cursor: pointer;
    }
    .add-value-btn {
      border: 2px dashed #dee2e6;
      background-color: #f8f9fa;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }
    .add-value-btn:hover {
      border-color: #007bff;
      background-color: #e7f3ff;
      color: #007bff;
    }
    .add-value-btn i {
      font-size: 16px;
      margin-bottom: 2px;
    }
    .variant-value-delete-btn {
      position: absolute;
      top: -5px;
      right: -5px;
      background: #dc3545;
      border-radius: 50%;
      width: 18px;
      height: 18px;
      display: none;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      z-index: 10;
    }
    .variant-value-delete-btn i {
      color: white;
      font-size: 12px;
    }
    .variant-value-item:hover .variant-value-delete-btn {
      display: flex;
    }
    
    /* 批量设置区域样式 */
    .variant-selector-container {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;
    }
    
    .form-check {
      margin-right: 15px;
      margin-bottom: 8px;
    }
    
    .form-check-input:checked {
      background-color: #0d6efd;
      border-color: #0d6efd;
    }
    
    .batch-settings-panel .card-header {
      border-bottom: 2px solid #dee2e6;
    }
    
    .modal-dialog.modal-lg {
      max-width: 900px;
    }
    
    .variant-selector-container .row {
      align-items: center;
      margin-bottom: 10px;
      padding: 8px;
      border-radius: 6px;
      transition: background-color 0.2s;
    }
    
    .variant-selector-container .row:hover {
      background-color: #e9ecef;
    }
    
    .btn-outline-secondary.btn-sm {
      font-size: 0.75rem;
      padding: 0.25rem 0.5rem;
    }
    .batch-settings-panel .card {
      border: 1px solid #e3e6f0;
      box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    
    .batch-settings-panel .card-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-bottom: none;
      color: white;
    }
    
    .batch-settings-panel .card-header h6 {
      color: white;
      font-weight: 600;
    }
    
    .batch-settings-panel .form-label {
      font-weight: 500;
      margin-bottom: 0.25rem;
      font-size: 0.875rem;
    }
    
    .batch-settings-panel .form-control-sm {
      font-size: 0.875rem;
      padding: 0.375rem 0.5rem;
    }
    
    .batch-settings-panel .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    
    .batch-settings-panel .btn-primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
      background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    }
    
    .batch-settings-panel .row {
      margin: 0;
    }
    
    .batch-settings-panel .col-md-2 {
      padding-left: 0.5rem;
      padding-right: 0.5rem;
    }
    
    /* 表格样式优化 */
    .variant-skus-table .table-bordered {
      border: 1px solid #e3e6f0;
    }
    
    .variant-skus-table .table-light {
      background-color: #f8f9fc;
      border-color: #e3e6f0;
    }
    
    .variant-skus-table th {
      font-weight: 600;
      font-size: 0.875rem;
      color: #5a5c69;
      border-color: #e3e6f0;
    }
  </style>
@endpush

<div class="card variants-box mb-3" id="variants-box">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/product.variant') }}</h5>
  </div>

  <div class="card-body py-0">
    <div class="variant-wrap" v-if="variants.length">
      <input type="hidden" name="variants" :value="JSON.stringify(variants)">
      <input type="hidden" name="skus" :value="JSON.stringify(skus)">
      <draggable
        v-model="variants"
        handle=".drag-variants-handle"
        :animation="300"
        @end="dragVariantsEnd"
        item-key="index">
        <template #item="{element: variant, index}">
          <div class="variant-item">
            <div class="variant-data" v-if="!variant.variantFormShow">
              <div class="variant-header d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center">
                  <div class="icon drag-variants-handle me-2"><i class="bi bi-grip-vertical"></i></div>
                  <div class="title">@{{ variant.name[defaultLocale] || getFirstAvailableLocaleValue(variant.name) }}</div>
                  <label class="form-check-label ms-3">
                    <input type="checkbox" class="form-check-input" v-model="variant.isImage" @change="toggleVariantImage(index)">
                    {{ __('panel/product.is_image_variant') }}
                  </label>
                </div>
                <div class="action-buttons">
                  <button type="button" class="btn btn-outline-primary btn-sm" @click="openVariantDialog(index, null)">{{ __('panel/common.edit') }}</button>
                  <button type="button" class="btn btn-outline-danger btn-sm ms-2" @click="deleteVariant(index)">{{ __('panel/common.delete') }}</button>
                </div>
              </div>
              <div class="variant-values">
                <div class="variant-values-container">
                  <div class="variant-values-list d-flex flex-wrap">
                    <div v-for="(value, valueIndex) in variant.values" :key="valueIndex" 
                         class="variant-value-item me-2 mb-2 position-relative" 
                         @dblclick="openVariantDialog(index, valueIndex)">
                      <div class="variant-value-delete-btn" @click="deleteVariantValue(index, valueIndex)">
                        <i class="bi bi-x-circle-fill"></i>
                      </div>
                      <div v-if="variant.isImage" class="variant-image-container open-file-manager" 
                           @click="selectVariantValueImage(index, valueIndex)">
                        <img v-if="value.image" :src="thumbnail(value.image)" class="variant-value-image">
                        <i v-else class="bi bi-image variant-placeholder-icon"></i>
                      </div>
                      <span class="variant-value-name">@{{ value.name[defaultLocale] || getFirstAvailableLocaleValue(value.name) }}</span>
                    </div>
                    <div class="variant-value-item me-2 mb-2 add-value-btn" @click="openVariantDialog(index, -1)">
                      <i class="bi bi-plus-circle"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="add-variant-form" v-else>
              <div class="mb-3 add-variant-title">
                <div class="variant-label">
                  <label class="form-label">{{ __('panel/product.variant_name') }}</label>
                  <div class="v-locales-input">
                    <div v-for="locale in locales" class="input-group" :key="locale.code">
                      <span class="input-group-text"><img :src="'/images/flag/'+ locale.code +'.png'" class="img-fluid">@{{ locale.name }}</span>
                      <input type="text" class="form-control" v-model="variant.name[locale.code]" placeholder="{{ __('panel/product.variant_name_help') }}">
                    </div>
                    <span class="text-12 text-danger" style="margin-left: 100px" v-if="variant.error"><i class="bi bi-exclamation-circle"></i> {{ __('panel/common.verify_required') }}</span>
                  </div>
                    @hookinsert('panel.product.edit.variant_name.after')
                </div>
              </div>
              <div class="add-variant-values">
                <label class="form-label">{{ __('panel/product.variant_value') }}</label>
                <div class="add-variant-value">
                  <div class="add-variant-value-item" v-for="(value, index) in variant.values" :key="index">
                    <div class="icon"><i class="bi bi-grip-vertical"></i></div>
                    <div class="v-locales-input variant-value">
                      <div v-for="locale in locales" class="input-group" :key="locale.code">
                        <span class="input-group-text"><img :src="'/images/flag/'+ locale.code +'.png'" class="img-fluid">@{{ locale.name }}</span>
                        <input type="text" class="form-control" v-model="value.name[locale.code]" placeholder="{{ __('panel/product.variant_value_help') }}" ref="variantValue">
                      </div>
                      <span class="text-12 text-danger" style="margin-left: 100px" v-if="value.error"><i class="bi bi-exclamation-circle"></i> {{ __('panel/common.verify_required') }}</span>
                    </div>
                    <div class="delete-icon" v-if="variant.values.length > 1" @click="variant.values.splice(index, 1)"><i class="bi bi-trash"></i></div>
                    <div class="delete-icon" v-else></div>
                  </div>
                  <div class="add-variant-btns">
                    <div class="text-primary text-12 mb-3">
                      <div class="d-inline-block cursor-pointer" @click="addVariantValue(index)"><i class="bi bi-plus-lg"></i> {{ __('panel/product.add_variant_value') }}</div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                      <button type="button" class="btn btn-outline-danger" @click="deleteVariant(index)">{{ __('panel/common.delete') }}</button>
                      <button type="button" class="btn btn-outline-primary" @click="saveVariant(index)">{{ __('panel/common.btn_save') }}</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
      </draggable>
    </div>
    <div :class="['text-primary add-variant', !variants.length ? 'no-variants' : '']">
      <div class="d-inline-block cursor-pointer" @click="openVariantDialog(-1, null)"><i class="bi bi-plus-square me-1"></i> {{ __('panel/product.add_variant') }}</div>
    </div>
    <div class="variant-skus-wrap" v-if="smallVariants.length">
      <div class="batch-settings-panel mb-3">
        <div class="card shadow-sm" style="border: none;">
          <div class="card-body py-3">
            <div class="mb-2" v-if="variants.length > 0">
              <label class="form-label small fw-bold mb-2">{{ __('panel/product.sku_batch_setting') }}</label>
              <div class="variant-selector-container">
                <div class="row g-2 mb-2" v-for="(variant, vIndex) in variants" :key="vIndex">
                  <div class="col-md-2">
                    <label class="form-label small mb-1">@{{ getFirstAvailableLocaleValue(variant.name) }}</label>
                  </div>
                  <div class="col-md-10">
                    <div class="d-flex flex-wrap gap-1 align-items-center">
                      <div class="form-check me-2" v-for="(value, valueIndex) in variant.values" :key="valueIndex">
                        <input class="form-check-input" type="checkbox" 
                               :id="`variant_${vIndex}_${valueIndex}`"
                               v-model="batchData.selectedVariants[vIndex]"
                               :value="valueIndex">
                        <label class="form-check-label small" :for="`variant_${vIndex}_${valueIndex}`">
                          @{{ getFirstAvailableLocaleValue(value.name) }}
                        </label>
                      </div>
                      <button type="button" class="btn btn-outline-primary btn-sm ms-2" 
                              @click="selectAllVariantValues(vIndex)">
                        {{ __('panel/product.select_all') }}
                      </button>
                      <button type="button" class="btn btn-outline-secondary btn-sm" 
                              @click="clearVariantSelection(vIndex)">
                        {{ __('panel/product.clear') }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="row g-2">
              <!-- SKU编码前缀 -->
              <div class="col-md-2">
                <label class="form-label small mb-1">SKU {{ __('panel/product.bulk_fill') }}</label>
                <input type="text" class="form-control form-control-sm" v-model="batchData.skuPrefix" 
                       placeholder="{{ __('panel/product.bulk_fill_sku') }}" style="height: 31px;">
              </div>
              
              <!-- 价格 -->
              <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('panel/product.price') }}</label>
                <input type="number" class="form-control form-control-sm" v-model="batchData.price" 
                       placeholder="{{ __('panel/product.bulk_fill_price') }}" min="0" @input="validateBatchPrice" style="height: 31px;">
              </div>
              
              <!-- 原价 -->
              <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('panel/product.origin_price') }}</label>
                <input type="number" class="form-control form-control-sm" v-model="batchData.originPrice" 
                       placeholder="{{ __('panel/product.bulk_fill_origin_price') }}" min="0" @input="validateBatchOriginPrice" style="height: 31px;">
              </div>
              
              <!-- 型号 -->
              <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('panel/product.model') }}</label>
                <input type="text" class="form-control form-control-sm" v-model="batchData.model" 
                       placeholder="{{ __('panel/product.bulk_fill_model') }}" style="height: 31px;">
              </div>
              
              <!-- 数量 -->
              <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('panel/product.quantity') }}</label>
                <input type="number" class="form-control form-control-sm" v-model="batchData.quantity" 
                       placeholder="{{ __('panel/product.bulk_fill_quantity') }}" min="0" @input="validateBatchQuantity" style="height: 31px;">
              </div>
              
              <!-- SKU图片 -->
              <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('panel/product.sku_image') }}</label>
                <div class="d-flex align-items-center" style="height: 31px;">
                  <input type="hidden" v-model="batchData.image">
                  <div class="image-preview me-2" v-if="batchData.image" style="width: 25px; height: 25px; border-radius: 3px; overflow: hidden; border: 1px solid #ddd;">
                    <img :src="batchData.image" style="width: 100%; height: 100%; object-fit: cover;">
                  </div>
                  <button type="button" class="btn btn-outline-secondary btn-sm" @click="selectBatchImage" style="font-size: 11px; padding: 2px 8px;">
                    <i class="bi bi-image me-1"></i>{{ __('panel/product.select_image') }}
                  </button>
                  <button type="button" class="btn btn-outline-danger btn-sm ms-1" @click="clearBatchImage" v-if="batchData.image" style="font-size: 11px; padding: 2px 6px;">
                    <i class="bi bi-x"></i>
                  </button>
                </div>
              </div>
              
              <!-- 批量设置按钮 -->
              <div class="col-md-2">
                <label class="form-label small mb-1" style="visibility: hidden;">占位</label>
                <button type="button" class="btn btn-success w-100 fw-bold" @click="batchApplySelected" style="height: 31px; font-size: 12px;">
                  <i class="bi bi-lightning-charge-fill me-1"></i>{{ __('panel/product.bulk_fill') }}
                </button>
              </div>
              
              @hookinsert('panel.product.edit.sku.batch.input.item.after')
            </div>
          </div>
        </div>
      </div>
      
      <!-- SKU数据表格 -->
      <div class="variant-skus-table table-responsive">
        <table class="table align-middle table-bordered">
          <thead class="table-light">
            <tr>
              <th style="min-width: 220px">{{ __('panel/product.variant') }}</th>
              <th>SKU Code</th>
              <th>{{ __('panel/product.price') }}</th>
              <th>{{ __('panel/product.origin_price') }}</th>
              <th>{{ __('panel/product.model') }}</th>
              <th>{{ __('panel/product.quantity') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(sku, index) in skus" :key="index">
              <td>
                <div class="sku-image-name">
                  <div class="up-variant-image" @click="upVariantImage(null, index)">
                    <img :src="thumbnail(sku.image, 50, 50)" v-if="sku.image" class="img-fluid">
                    <i class="bi bi-folder-plus" v-else></i>
                  </div>
                  <div>
                    <div class="sku-text">@{{ sku.text }}</div>
                    <div class="up-master text-12">
                      <span v-if="sku.is_default" class="text-success">
                        <i class="bi bi-check-circle-fill"></i> {{ __('panel/product.main_variant') }}
                      </span>
                      <span class="opacity-50 cursor-pointer" v-else @click="setMasterSku(index)">
                        <i class="bi bi-circle"></i> {{ __('panel/product.main_variant') }}
                      </span>
                    </div>
                  </div>
                </div>
              </td>
              <td>
                <input type="text" :class="['form-control form-control-sm', sku.error ? 'is-invalid other-error' : '']"
                  v-model="sku.code" placeholder="SKU Code">
                <div class="invalid-feedback">{{ __('panel/product.error_sku_repeat') }}</div>
              </td>
              <td>
                <input type="text" class="form-control form-control-sm"
                  v-model="sku.price" placeholder="{{ __('panel/product.price') }}"
                  @input="validatePrice(sku)">
                  @hookinsert('panel.product.edit.sku.input.item.price.after')
              </td>
              <td>
                <input type="text" class="form-control form-control-sm"
                  v-model="sku.origin_price" placeholder="{{ __('panel/product.origin_price') }}"
                  @input="validateOriginPrice(sku)">
              </td>
              <td>
                <input type="text" class="form-control form-control-sm"
                  v-model="sku.model" placeholder="{{ __('panel/product.model') }}">
              </td>
              <td>
                <input type="text" class="form-control form-control-sm"
                  v-model="sku.quantity" placeholder="{{ __('panel/product.quantity') }}"
                  @input="validateQuantity(sku)">
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
    @hookinsert('panel.product.edit.variant.after')

    <!-- 规格/规格值编辑弹窗 -->
    <div class="modal fade" id="variantEditModal" tabindex="-1" aria-hidden="true" v-if="dialogVariables.show">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">@{{ dialogVariables.title }}</h5>
            <button type="button" class="btn-close" @click="closeVariantDialog"></button>
          </div>
          <div class="modal-body">
            <form ref="variantForm">
              <div class="mb-3">
                <div v-for="locale in locales" :key="locale.code" class="input-group mb-2">
                  <div class="input-group-text">
                    <div class="d-flex align-items-center wh-20">
                      <img :src="'/images/flag/'+ locale.code +'.png'" 
                           class="img-fluid" 
                           :alt="locale.name">
                    </div>
                  </div>
                  <input type="text" class="form-control" 
                         v-model="dialogVariables.form.name[locale.code]" 
                         :placeholder="'{{ __('panel/product.name') }}'"
                         :aria-label="locale.name"
                         :data-locale="locale.code">
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeVariantDialog">{{ __('panel/common.cancel') }}</button>
            <button type="button" class="btn btn-primary" @click="saveVariantDialog">{{ __('panel/common.save') }}</button>
          </div>
        </div>
      </div>
    </div>
</div>

@push('footer')
<script>
  const { createApp, ref, watch, watchEffect, onMounted, getCurrentInstance, nextTick, computed } = Vue
  const draggable = window.vuedraggable;

  // Initialize locale utilities
  const $locales = @json(locales());
  const localesFill = (text) => {
    let obj = {};
    $locales.map(e => {
      obj[e.code] = text
    })
    return obj;
  }

  // Create Vue application for variants management
  let variantsBoxApp = createApp({
    components: {
      draggable,
    },

    setup() {

      // Core state variables
      const instance = getCurrentInstance();
      const locales = $locales;
      const defaultLocale = @json(panel_locale_code());
      const showAllVariant = ref(false);
      const mainVariantKey = ref(0);
      
      // Initialize variants from product data
      const variants = ref(@json(old('variants', $product->variables ?? [])));
      if (typeof variants.value === 'string') {
        variants.value = JSON.parse(variants.value);
      }
      
      // Initialize SKUs with proper parsing
      const skus = ref((() => {
        let rawSkus = @json(old('skus', $skus ?? []));
        if (typeof rawSkus === 'string') {
          try {
            rawSkus = JSON.parse(rawSkus);
          } catch (e) {
            rawSkus = [];
          }
        }
        return rawSkus;
      })());
      
      // Initialize small variants array for UI display
      const smallVariants = ref([]);

      // Batch data for bulk operations
      const batchData = ref({
        skuPrefix: '',
        price: '',
        originPrice: '',
        model: '',
        quantity: '',
        image: '',
        selectedVariants: [] // 存储每个规格的选中值索引数组
      });

      // Dialog variables for variant/value editing
      const dialogVariables = ref({
        show: false,
        variantIndex: null,
        variantValueIndex: null,
        title: '',
        form: {
          name: {}
        }
      });

      // Watch for changes in main variant key or variants array
      watch([mainVariantKey, variants.value], ([newValue1, newValue2], [oldValue1, oldValue2]) => {
        // Show single SKU box if no variants exist
        if (!variants.value.length) {
          $('.skus-single-box').removeClass('d-none');
        } else {
          $('.skus-single-box').addClass('d-none');
        }

        // Don't generate SKUs if only one empty variant exists
        if (variants.value.length == 1 && isObjectValuesEmpty(variants.value[0].values[0].name)) {
          return;
        }

        // Generate SKUs and update small variants
        generateSku();
        smallVariantsFormat(newValue1 != oldValue1);
      });

      // Watch for changes in SKUs to validate
      watch(skus, () => {
        validateSkus();
      }, {deep: true});

      // Watch for changes in variants to sync with single SKU
      watch(variants, (newValue) => {
        if (newValue.length > 0) {
          // Get existing single SKU data
          const singleSkuPrice = $('input[name="skus[0][price]"]').val();
          const singleSkuQuantity = $('input[name="skus[0][quantity]"]').val();
          const singleSkuCode = $('input[name="skus[0][code]"]').val();

          if (singleSkuPrice || singleSkuQuantity || singleSkuCode) {
            // Transfer single SKU data to the first variant SKU
            const firstSku = skus.value[0];
            if (firstSku) {
              firstSku.price = singleSkuPrice || '';
              firstSku.quantity = singleSkuQuantity || '';
              firstSku.code = singleSkuCode || '';
              firstSku.is_default = 1;
            }

            // Clear single SKU form
            $('input[name="skus[0][price]"]').val('');
            $('input[name="skus[0][quantity]"]').val('');
            $('input[name="skus[0][code]"]').val('');
          }
        } else {
          // Sync default SKU back to single SKU form when removing variants
          const defaultSku = skus.value.find(sku => sku.is_default === 1);
          if (defaultSku) {
            $('input[name="skus[0][price]"]').val(defaultSku.price);
            $('input[name="skus[0][quantity]"]').val(defaultSku.quantity);
            $('input[name="skus[0][code]"]').val(defaultSku.code);
          }
        }
      }, { deep: true });

      // Initialize on component mount
      onMounted(() => {
        generateSku();
        smallVariantsFormat();
        
        // Validate form on submit
        $('#product-form').on('submit', function(e) {
          if (!validateForm()) {
            e.preventDefault();
            layer.msg('Please fill in single specification information or add multiple specification product information', {icon: 2});
            return false;
          }
        });
      });

      // Format small variants for display
      const smallVariantsFormat = () => {
        if (variants.value.length === 0) {
          smallVariants.value = [];
          return;
        }

        // Map SKUs to small variants with additional properties
        smallVariants.value = skus.value.map((sku, index) => ({
          ...sku,
          init_index: index,
          show_variant: false,
          sku_quantity: null
        }));
      }

      // Add a new value to a variant
      const addVariantValue = (index) => {
        variants.value[index].values.push({name: localesFill(''), error: false, image: ''});
      }

      // Add a new variant
      const addVariant = () => {
        variants.value.push({
          name: localesFill(''),
          error: false,
          variantFormShow: true,
          isImage: false,
          values: [{name: localesFill(''), error: false, image: ''}],
        });
        
        // 更新规格选择器数据结构
        batchData.value.selectedVariants.push([]);
      }

      // Delete a variant and update main variant key
      const deleteVariant = (index) => {
        variants.value.splice(index, 1);
        
        // 同步删除规格选择器对应的数据
        batchData.value.selectedVariants.splice(index, 1);

        if (index < mainVariantKey.value) {
          mainVariantKey.value--;
        } else if (index === mainVariantKey.value) {
          mainVariantKey.value = 0;
        }
      }

      // Save variant after validation
      const saveVariant = (index) => {
        let isError = true;

        // Validate all variants and values
        variants.value.forEach((e, i) => {
          if (isObjectValuesEmpty(e.name)) {
            e.error = true;
            isError = false;
          } else {
            e.error = false;
          }

          e.values.forEach((value, j) => {
            if (isObjectValuesEmpty(value.name)) {
              value.error = true;
              isError = false;
            } else {
              value.error = false;
            }
          });
        });

        if (!isError) {
          return;
        }

        // Hide form and save variants to localStorage
        variants.value[index].variantFormShow = false;
        localStorage.setItem('variants', JSON.stringify(variants.value));
      }

      // Get first available locale value for display
      const getFirstAvailableLocaleValue = (localeObject) => {
        if (!localeObject) return '';

        // First try the default system locale
        const systemDefaultLocale = @json(setting_locale_code());
        if (localeObject[systemDefaultLocale]) return localeObject[systemDefaultLocale];

        // Otherwise get the first non-empty value
        for (const locale of locales) {
          if (localeObject[locale.code] && localeObject[locale.code].trim() !== '') {
            return localeObject[locale.code];
          }
        }

        return '';
      };

      // Generate SKU combinations based on variants
      const generateSku = () => {
        if (variants.value.length === 0) {
          return;
        }

        // Prepare variants for SKU generation (main variant first)
        let mainVariant = variants.value[mainVariantKey.value];
        let tempVariants = [mainVariant, ...variants.value.filter((e, i) => i !== mainVariantKey.value)];
        
        // Initialize SKU generation variables
        let sku = [];
        let skuVariantsLength = tempVariants.length;
        let skuVariantsIndex = Array(skuVariantsLength).fill(0);
        let skuVariantsValues = tempVariants.map(e => e.values.length);
        
        // Calculate total number of combinations
        const totalCombinations = skuVariantsValues.reduce((a, b) => a * b);

        // Generate each SKU combination
        for (let i = 0; i < totalCombinations; i++) {
          // Create SKU item (preserve existing values if available)
          let skuItem = {
            code: skus.value[i] ? skus.value[i].code : '',
            price: skus.value[i] ? skus.value[i].price : '',
            quantity: skus.value[i] ? skus.value[i].quantity : '',
            image: skus.value[i] ? skus.value[i].image : '',
            image_url: skus.value[i] ? skus.value[i].image_url : '',
            model: skus.value[i] ? skus.value[i].model : '',
            origin_price: skus.value[i] ? skus.value[i].origin_price : '',
            is_default: skus.value[i] ? skus.value[i].is_default : 0,
            error: false,
            text: '',
            variants: []
          };

          // Build SKU text and variants array
          for (let j = 0; j < skuVariantsLength; j++) {
            skuItem.variants.push(skuVariantsIndex[j]);
            const valueName = tempVariants[j].values[skuVariantsIndex[j]].name[defaultLocale] ||
                            getFirstAvailableLocaleValue(tempVariants[j].values[skuVariantsIndex[j]].name);
            skuItem.text += ' ' + valueName + ' /';
          }

          // Clean up text and add SKU to array
          skuItem.text = skuItem.text.slice(0, -1);
          sku.push(skuItem);

          // Increment indices for next combination
          for (let j = skuVariantsLength - 1; j >= 0; j--) {
            if (skuVariantsIndex[j] < skuVariantsValues[j] - 1) {
              skuVariantsIndex[j]++;
              break;
            } else {
              skuVariantsIndex[j] = 0;
            }
          }
        }

        // Set first SKU as default if none is marked
        let isMaster = sku.filter((e, i) => e.is_default == 1);
        if (isMaster.length === 0) {
          sku[0].is_default = 1;
        }

        skus.value = sku;
        
        // 初始化规格选择器数据结构
        initializeVariantSelectors();
      }

      // Modify SKU values in batch or individually
      const modifySku = (init_index, index, type) => {
        let sku_quantity = smallVariants.value[index].sku_quantity;
        let sku = smallVariants.value[index];
        let tempSkus = skus.value.slice(init_index * sku_quantity, (init_index + 1) * sku_quantity);

        // Apply value to all SKUs in the group
        tempSkus.forEach((e) => {
          e[type] = sku[type];
        });

        // Update SKU codes with sequential numbering
        if (typeof init_index != 'undefined') {
          let sameSku = skus.value.filter((e) => e.code.split('-')[0] === sku.code);
          sameSku.forEach((e, i) => {
            e.code = sku.code + '-' + i;
          });
        }
      }

      // Open file manager to upload variant image
      const upVariantImage = (init_index, index) => {
        inno.fileManagerIframe((file) => {
          if (file.path) {
            skus.value[index].image = file.path;
          }
        }, {
          type: 'image',
          multiple: false
        });
      }

      // Update main variant key after drag and drop
      const dragVariantsEnd = (evt) => {
        const oldIndex = evt.oldIndex;
        const newIndex = evt.newIndex;

        // Adjust main variant key based on drag direction
        if (oldIndex === mainVariantKey.value) {
          mainVariantKey.value = newIndex;
        } else if (oldIndex < mainVariantKey.value && newIndex >= mainVariantKey.value) {
          mainVariantKey.value--;
        } else if (oldIndex > mainVariantKey.value && newIndex <= mainVariantKey.value) {
          mainVariantKey.value++;
        }
      }

      // Generate thumbnail URL for images
      const thumbnail = (image) => {
        const asset = document.querySelector('meta[name="asset"]').content;
        if (!image) {
          return 'image/placeholder.png';
        }

        // Return URL directly if it's absolute
        if (image.indexOf('http') === 0) {
          return image;
        }

        return asset + image;
      }

      // Set a SKU as the master/default
      const setMasterSku = (index) => {
        // Reset all SKUs
        skus.value.forEach((e) => {
          e.is_default = 0;
        });

        // Set the selected SKU as default
        getSkusItem(index).is_default = 1;
      }

      // Find matching SKU from smallVariants
      const getSkusItem = (index) => {
        return skus.value.find((e) => {
          return e.variants.toString() === smallVariants.value[index].variants.toString();
        });
      }

      // Validate variants have values
      const validateVariants = () => {
        variants.value.forEach((e) => {
          e.error = isObjectValuesEmpty(e.name);

          e.values.forEach((value) => {
            value.error = isObjectValuesEmpty(value.name);
          });
        });
      }

      // Check for duplicate SKU codes
      const validateSkus = () => {
        skus.value.forEach((e) => {
          const sameSku = skus.value.filter((s) => s.code === e.code);
          e.error = sameSku.length > 1;
        });
      }

      // Toggle expanded/collapsed view for all variants
      const allVariantEC = () => {
        showAllVariant.value = !showAllVariant.value;
        
        // Update all small variants
        smallVariants.value.forEach((e) => {
          e.show_variant = showAllVariant.value;
        });

        smallVariantsFormat();
      }

      // Validate form before submission
      const validateForm = () => {
        // Check single SKU form
        const singleSkuPrice = $('input[name="skus[0][price]"]').val();
        const singleSkuQuantity = $('input[name="skus[0][quantity]"]').val();

        // Check multi-variant SKUs
        const hasValidVariants = variants.value.length > 0 && skus.value.some(sku => {
          return sku.price && sku.quantity && (sku.is_default === 1);
        });

        // Ensure at least one complete SKU exists
        return hasValidVariants || (singleSkuPrice && singleSkuQuantity);
      }

      // Batch data validation methods
      const validateBatchPrice = () => {
        if (batchData.value.price < 0) {
          batchData.value.price = 0;
        }
        if (batchData.value.originPrice && parseFloat(batchData.value.price) > parseFloat(batchData.value.originPrice)) {
          batchData.value.price = batchData.value.originPrice;
        }
      }

      const validateBatchOriginPrice = () => {
        if (batchData.value.originPrice < 0) {
          batchData.value.originPrice = 0;
        }
        if (batchData.value.price && parseFloat(batchData.value.originPrice) < parseFloat(batchData.value.price)) {
          batchData.value.originPrice = batchData.value.price;
        }
      }

      const validateBatchQuantity = () => {
        if (batchData.value.quantity < 0) {
          batchData.value.quantity = 0;
        }
      }

      // SKU field validation methods
      const validatePrice = (sku) => {
        let price = parseFloat(sku.price);
        if (isNaN(price) || price < 0) {
          sku.price = '0';
        }
        if (sku.origin_price && price > parseFloat(sku.origin_price)) {
          sku.price = sku.origin_price;
        }
      }

      const validateOriginPrice = (sku) => {
        let originPrice = parseFloat(sku.origin_price);
        if (isNaN(originPrice) || originPrice < 0) {
          sku.origin_price = '0';
        }
        if (sku.price && originPrice < parseFloat(sku.price)) {
          sku.origin_price = sku.price;
        }
      }

      const validateQuantity = (sku) => {
        let quantity = parseInt(sku.quantity);
        if (isNaN(quantity) || quantity < 0) {
          sku.quantity = '0';
        }
      }

      // Batch fill SKU codes with prefix and sequential numbers
      const batchFillSkuCode = () => {
        if (!batchData.value.skuPrefix) {
          layer.msg('Please enter SKU prefix', {icon: 2});
          return;
        }

        skus.value.forEach((sku, index) => {
          const suffix = String(index + 1).padStart(2, '0');
          sku.code = `${batchData.value.skuPrefix}-${suffix}`;
        });

        layer.msg('SKU codes have been filled', {icon: 1});
      };

      // Batch fill values for a specific column
      const batchFillColumn = (column) => {
        if (!batchData.value[column]) {
          layer.msg('Please enter a value to fill', {icon: 2});
          return;
        }

        const columnMap = {
          price: 'price',
          originPrice: 'origin_price',
          model: 'model',
          quantity: 'quantity'
        };

        skus.value.forEach(sku => {
          sku[columnMap[column]] = batchData.value[column];
        });

        layer.msg('Batch fill completed', {icon: 1});
      };

      // 初始化规格选择器数据结构
      const initializeVariantSelectors = () => {
        // 初始化批量设置的规格选择器
        batchData.value.selectedVariants = variants.value.map(() => []);
      };
      
      // 规格选择器相关方法
      const selectAllVariantValues = (variantIndex) => {
        if (!batchData.value.selectedVariants[variantIndex]) {
          batchData.value.selectedVariants[variantIndex] = [];
        }
        const allValues = variants.value[variantIndex].values.map((_, index) => index);
        batchData.value.selectedVariants[variantIndex] = [...allValues];
      };
      
      const clearVariantSelection = (variantIndex) => {
        if (batchData.value.selectedVariants[variantIndex]) {
          batchData.value.selectedVariants[variantIndex] = [];
        }
      };
      
      // 检查SKU是否匹配选中的规格组合
      const isSkuMatchingSelection = (sku) => {
        if (!batchData.value.selectedVariants.length) return true;
        
        return batchData.value.selectedVariants.every((selectedValues, variantIndex) => {
          if (!selectedValues || selectedValues.length === 0) return true;
          return selectedValues.includes(sku.variants[variantIndex]);
        });
      };
      
      // 获取匹配选中规格的SKU列表
      const getMatchingSKUs = () => {
        return skus.value.filter(isSkuMatchingSelection);
      };
      
      // 批量设置选中的SKU
      const batchApplySelected = () => {
        const matchingSKUs = getMatchingSKUs();
        let appliedCount = 0;
        
        if (matchingSKUs.length === 0) {
          layer.msg('没有匹配的SKU，请检查规格选择', {icon: 2});
          return;
        }
        
        // 批量设置SKU编码
        if (batchData.value.skuPrefix) {
          matchingSKUs.forEach((sku, index) => {
            const suffix = String(index + 1).padStart(2, '0');
            sku.code = `${batchData.value.skuPrefix}-${suffix}`;
          });
          appliedCount++;
        }
        
        // 批量设置价格
        if (batchData.value.price) {
          matchingSKUs.forEach(sku => {
            sku.price = batchData.value.price;
          });
          appliedCount++;
        }
        
        // 批量设置原价
        if (batchData.value.originPrice) {
          matchingSKUs.forEach(sku => {
            sku.origin_price = batchData.value.originPrice;
          });
          appliedCount++;
        }
        
        // 批量设置型号
        if (batchData.value.model) {
          matchingSKUs.forEach(sku => {
            sku.model = batchData.value.model;
          });
          appliedCount++;
        }
        
        // 批量设置数量
        if (batchData.value.quantity) {
          matchingSKUs.forEach(sku => {
            sku.quantity = batchData.value.quantity;
          });
          appliedCount++;
        }
        
        // 批量设置图片
        if (batchData.value.image) {
          matchingSKUs.forEach(sku => {
            sku.image = batchData.value.image;
          });
          appliedCount++;
        }
        
        if (appliedCount === 0) {
          layer.msg('请至少填写一个字段进行批量设置', {icon: 2});
          return;
        }
        
        layer.msg(`批量设置完成，已应用 ${appliedCount} 个字段到 ${matchingSKUs.length} 个SKU`, {icon: 1});
      };
      

      
      // 选择批量设置图片
      const selectBatchImage = () => {
        inno.fileManagerIframe((file) => {
          if (file.path) {
            batchData.value.image = file.path;
          }
        }, {
          type: 'image',
          multiple: false
        });
      };
      
      // 清除批量设置图片
      const clearBatchImage = () => {
        batchData.value.image = '';
      };
      

      


      // Open variant/value edit dialog
      const openVariantDialog = (variantIndex, valueIndex = null) => {
        dialogVariables.value.variantIndex = variantIndex;
        dialogVariables.value.variantValueIndex = valueIndex;
        
        let name = {};
        let title = '';
        
        // Initialize form data and title based on what we're editing
        if (variantIndex === -1) {
          // Creating new variant
          name = localesFill('');
          title = '{{ __('panel/product.add_variant') }}';
        } else if (valueIndex === null) {
          // Editing existing variant name
          if (variants.value[variantIndex] && variants.value[variantIndex].name) {
            name = variants.value[variantIndex].name;
          } else {
            name = localesFill('');
          }
          title = '{{ __('panel/product.edit_variant') }}';
        } else if (valueIndex === -1) {
          // Creating new variant value
          name = localesFill('');
          title = '{{ __('panel/product.add_variant_value') }}';
        } else {
          // Editing existing variant value
          if (variants.value[variantIndex] && variants.value[variantIndex].values[valueIndex] && variants.value[variantIndex].values[valueIndex].name) {
            name = variants.value[variantIndex].values[valueIndex].name;
          } else {
            name = localesFill('');
          }
          title = '{{ __('panel/product.edit_variant_value') }}';
        }
        
        dialogVariables.value.form.name = JSON.parse(JSON.stringify(name));
        dialogVariables.value.title = title;
        dialogVariables.value.show = true;
        
        // Show Bootstrap modal
        nextTick(() => {
          const modal = new bootstrap.Modal(document.getElementById('variantEditModal'));
          modal.show();
        });
      };

      // Close variant dialog
      const closeVariantDialog = () => {
        dialogVariables.value.show = false;
        dialogVariables.value.variantIndex = null;
        dialogVariables.value.variantValueIndex = null;
        dialogVariables.value.title = '';
        dialogVariables.value.form.name = {};
        
        // Hide Bootstrap modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('variantEditModal'));
        if (modal) {
          modal.hide();
        }
      };

      // Save variant dialog changes
      const saveVariantDialog = () => {
        const name = JSON.parse(JSON.stringify(dialogVariables.value.form.name));
        const variantIndex = dialogVariables.value.variantIndex;
        const valueIndex = dialogVariables.value.variantValueIndex;
        
        // Validate name is not empty
        if (isObjectValuesEmpty(name)) {
          layer.msg('{{ __('panel/common.verify_required') }}', {icon: 2});
          return;
        }
        
        if (valueIndex !== null) {
          if (valueIndex === -1) {
            // Creating new variant value
            variants.value[variantIndex].values.push({name, image: ''});
          } else {
            // Update existing variant value
            variants.value[variantIndex].values[valueIndex].name = name;
          }
        } else {
          if (variantIndex === -1) {
            // Creating new variant
            variants.value.push({name, values: [], isImage: false});
          } else {
            // Update existing variant
            variants.value[variantIndex].name = name;
          }
        }
        
        closeVariantDialog();
        layer.msg('{{ __('panel/common.saved_success') }}', {icon: 1});
      };

      // Toggle variant image mode
      const toggleVariantImage = (variantIndex) => {
        const variant = variants.value[variantIndex];
        if (!variant.isImage) {
          // Clear all images when disabling image mode
          variant.values.forEach(value => {
            value.image = '';
          });
        } else {
          // Initialize image property for all values
          variant.values.forEach(value => {
            if (!value.image) {
              value.image = '';
            }
          });
        }
      };

      // Select image for variant value
      const selectVariantValueImage = (variantIndex, valueIndex) => {
        inno.fileManagerIframe((file) => {
          if (file.path) {
            variants.value[variantIndex].values[valueIndex].image = file.path;
          }
        }, {
          type: 'image',
          multiple: false
        });
      };

      // Delete variant value
      const deleteVariantValue = (variantIndex, valueIndex) => {
        if (confirm('{{ __('panel/common.confirm_delete') }}')) {
          variants.value[variantIndex].values.splice(valueIndex, 1);
          layer.msg('{{ __('panel/common.deleted_success') }}', {icon: 1});
        }
      };

      // Expose methods and state to the template
      return {
        skus,
        variants,
        addVariant,
        addVariantValue,
        deleteVariant,
        saveVariant,
        locales,
        defaultLocale,
        mainVariantKey,
        smallVariants,
        modifySku,
        upVariantImage,
        dragVariantsEnd,
        thumbnail,
        setMasterSku,
        showAllVariant,
        allVariantEC,
        batchData,
        batchFillSkuCode,
        batchFillColumn,
        getFirstAvailableLocaleValue,
        validateBatchPrice,
        validateBatchOriginPrice,
        validateBatchQuantity,
        validatePrice,
        validateOriginPrice,
        validateQuantity,
        // New dialog and image methods
        dialogVariables,
        openVariantDialog,
        closeVariantDialog,
        saveVariantDialog,
        toggleVariantImage,
        selectVariantValueImage,
        deleteVariantValue,
        // 批量设置相关方法
        initializeVariantSelectors,
        selectAllVariantValues,
        clearVariantSelection,
        batchApplySelected,
        selectBatchImage,
        clearBatchImage
      }
    }
  }).mount('#variants-box');

  // Split an array into chunks of specified size
  function chunkArray(array, chunkSize) {
    let chunks = [];
    for (let i = 0; i < array.length; i += chunkSize) {
      chunks.push(array.slice(i, i + chunkSize));
    }
    return chunks;
  }

  // Split an array into a specified number of groups
  function splitArrayIntoGroups(array, groupCount) {
    if (groupCount <= 0) {
      throw new Error('Group count must be greater than 0');
    }

    const result = [];
    const groupSize = Math.ceil(array.length / groupCount);

    for (let i = 0; i < groupCount; i++) {
      const start = i * groupSize;
      const end = start + groupSize;
      result.push(array.slice(start, end));
    }

    return result;
  }

  // Check if all values in an object are empty
  function isObjectValuesEmpty(obj) {
    for (let key in obj) {
      if (obj[key] != '') {
        return false;
      }
    }
    return true;
  }
</script>
@endpush
