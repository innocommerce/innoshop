@push('header')
  <script src="{{ asset('vendor/vue/3.5/vue.global.prod.js') }}"></script>
  <script src="{{ asset('vendor/vuedraggable/sortable.min.js') }}"></script>
  <script src="{{ asset('vendor/vuedraggable/vuedraggable.umd.min.js') }}"></script>
@endpush

<div class="card variants-box mb-3" id="variants-box">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/product.variant') }}</h5>
  </div>

  <div class="card-body py-0">
    <div class="variant-wrap" v-if="variants.length">
      <input type="hidden" name="variants" :value="JSON.stringify(variants)"><br>
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
              <div class="left">
                <div class="icon drag-variants-handle"><i class="bi bi-grip-vertical"></i></div>
                <div class="info">
                  <div class="title">@{{ variant.name[defaultLocale] || getFirstAvailableLocaleValue(variant.name) }}</div>
                  <div class="values">
                    <span v-for="(value, i) in variant.values" :key="i">@{{ value.name[defaultLocale] || getFirstAvailableLocaleValue(value.name) }}</span>
                  </div>
                </div>
              </div>
              <div class="right"><button type="button" class="btn btn-outline-secondary btn-sm" @click="variant.variantFormShow = true">{{ __('panel/common.edit') }}</button></div>
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
    <div :class="['text-primary add-variant', !variants.length ? 'no-variants' : '']" v-if="variants.length < 3">
      <div class="d-inline-block cursor-pointer" @click="addVariant"><i class="bi bi-plus-square me-1"></i> {{ __('panel/product.add_variant') }}</div>
    </div>
    <div class="variant-skus-wrap" v-if="smallVariants.length">
      <div class="variant-skus-table table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th style="min-width: 220px">
                  <div class="batch-input-item mb-2">
                      {{ __('panel/product.batch_fill') }}
                  </div>
                  {{ __('panel/product.variant') }}
              </th>
              <th>
                <div class="batch-input-item mb-2">
                  <div class="input-group input-group-sm">
                    <input type="text" class="form-control" v-model="batchData.skuPrefix" placeholder="{{ __('panel/product.batch_fill_sku') }}">
                    <button class="btn btn-outline-primary" type="button" @click="batchFillSkuCode">{{ __('panel/product.batch_fill_apply') }}</button>
                  </div>
                </div>
                SKU Code
              </th>
              <th>
                <div class="batch-input-item mb-2">
                  <div class="input-group input-group-sm">
                    <input type="number" class="form-control" v-model="batchData.price" placeholder="{{ __('panel/product.batch_fill_price') }}"
                      min="0" @input="validateBatchPrice">
                    <button class="btn btn-outline-primary" type="button" @click="batchFillColumn('price')">{{ __('panel/product.batch_fill_apply') }}</button>
                  </div>
                </div>
                {{ __('panel/product.price') }}
              </th>
              <th>
                <div class="batch-input-item mb-2">
                  <div class="input-group input-group-sm">
                    <input type="number" class="form-control" v-model="batchData.originPrice" placeholder="{{ __('panel/product.batch_fill_origin_price') }}"
                      min="0" @input="validateBatchOriginPrice">
                    <button class="btn btn-outline-primary" type="button" @click="batchFillColumn('originPrice')">{{ __('panel/product.batch_fill_apply') }}</button>
                  </div>
                </div>
                {{ __('panel/product.origin_price') }}
              </th>
              <th>
                <div class="batch-input-item mb-2">
                  <div class="input-group input-group-sm">
                    <input type="text" class="form-control" v-model="batchData.model" placeholder="{{ __('panel/product.batch_fill_model') }}">
                    <button class="btn btn-outline-primary" type="button" @click="batchFillColumn('model')">{{ __('panel/product.batch_fill_apply') }}</button>
                  </div>
                </div>
                {{ __('panel/product.model') }}
              </th>
              <th>
                <div class="batch-input-item mb-2">
                  <div class="input-group input-group-sm">
                    <input type="number" class="form-control" v-model="batchData.quantity" placeholder="{{ __('panel/product.batch_fill_quantity') }}"
                      min="0" @input="validateBatchQuantity">
                    <button class="btn btn-outline-primary" type="button" @click="batchFillColumn('quantity')">{{ __('panel/product.batch_fill_apply') }}</button>
                  </div>
                </div>
                {{ __('panel/product.quantity') }}
              </th>
                @hookinsert('panel.product.edit.sku.batch.input.item.after')
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
        quantity: ''
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
        variants.value[index].values.push({name: localesFill('')});
      }

      // Add a new variant
      const addVariant = () => {
        variants.value.push({
          name: localesFill(''),
          error: false,
          variantFormShow: true,
          values: [{name: localesFill(''), error: false}],
        });
      }

      // Delete a variant and update main variant key
      const deleteVariant = (index) => {
        variants.value.splice(index, 1);

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
          if (file.url) {
            skus.value[index].image = file.url;
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
        validateQuantity
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
