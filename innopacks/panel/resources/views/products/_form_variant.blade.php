@push('header')
  <script src="{{ asset('vendor/vue/3.5/vue.global.prod.js') }}"></script>
  <script src="{{ asset('vendor/vuedraggable/sortable.min.js') }}"></script>
  <script src="{{ asset('vendor/vuedraggable/vuedraggable.umd.min.js') }}"></script>
  <style>
    .variant-skus-table th {
      padding-top: 0;
      vertical-align: bottom;
    }

    .variant-skus-table .batch-input-item {
      margin: 0;
      padding: 10px 0;
    }

    .variant-skus-table .batch-input-item .input-group {
      width: 100%;
      min-width: 120px;
    }

    .variant-skus-table .batch-input-item .form-control {
      height: 31px;
      font-size: 13px;
      border-right: 0;
    }

    .variant-skus-table .batch-input-item .btn {
      border-color: #ced4da;
      background: #fff;
      font-size: 13px;
      padding: 4px 8px;
    }

    .variant-skus-table .batch-input-item .btn:hover {
      background: #8446df;
      border-color: #8446df;
      color: #fff;
    }

    .variant-skus-table thead th {
      background: #f8f9fa;
      border-bottom: 2px solid #dee2e6;
    }

    .variant-skus-table tbody td .form-control {
      font-size: 13px;
    }

    /* Remove old batch input styles */
    .batch-input-area {
      display: none;
    }
  </style>
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
                  <div class="title">@{{ variant.name[defaultLocale] }}</div>
                  <div class="values">
                    <span v-for="(value, i) in variant.values" :key="i">@{{ value.name[defaultLocale] }}</span>
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
                        {{-- @keyup.enter="addVariantValue" --}}
                      </div>
                      <span class="text-12 text-danger" style="margin-left: 100px" v-if="value.error"><i class="bi bi-exclamation-circle"></i> {{ __('panel/common.verify_required') }}</span>
                    </div>
                    <div class="delete-icon" v-if="variant.values.length > 1" @click="variant.values.splice(index, 1)"><i class="bi bi-trash"></i></div>
                    <div class="delete-icon" v-else></div>
                  </div>
                  <div class="add-variant-btns">
                    {{-- <div class="text-secondary text-12 mb-2">按回车键新增一行</div> --}}
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

  const $locales = @json(locales());
  const localesFill = (text) => {
    var obj = {};
    $locales.map(e => {
      obj[e.code] = text
    })

    return obj;
  }

  let variantsBoxApp = createApp({
    components: {
      draggable,
    },

    setup() {
      const instance = getCurrentInstance();
      const locales = $locales;
      const defaultLocale = @json(panel_locale_code());
      const showAllVariant = ref(false)
      const mainVariantKey = ref(0)
      const variants = ref(@json(old('variants', $product->variables ?? [])))
      const skus = ref(@json(old('skus', $skus ?? [])))

      // 添加批量填写的数据
      const batchData = ref({
        skuPrefix: '',
        price: '',
        originPrice: '',
        model: '',
        quantity: ''
      });

      // SKU Code 批量填写
      const batchFillSkuCode = () => {
        if (!batchData.value.skuPrefix) {
          layer.msg('请输入SKU前缀', {icon: 2});
          return;
        }

        skus.value.forEach((sku, index) => {
          const suffix = String(index + 1).padStart(2, '0');
          sku.code = `${batchData.value.skuPrefix}-${suffix}`;
        });

        layer.msg('SKU Code 已批量填写', {icon: 1});
      };

      // 其他列的批量填写
      const batchFillColumn = (column) => {
        if (!batchData.value[column]) {
          layer.msg('请输入要填写的值', {icon: 2});
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

        layer.msg('批量填写成功', {icon: 1});
      };

      if (typeof variants.value === 'string') {
        variants.value = JSON.parse(variants.value);
      }

      const smallVariants = ref([])

      onMounted(() => {
        generateSku()
        smallVariantsFormat()
        $('#product-form').on('submit', function(e) {
          if (!validateForm()) {
            e.preventDefault();
            layer.msg('请至少填写单规格信息或添加多规格商品信息', {icon: 2});
            return false;
          }
        });
      })

      watch([mainVariantKey, variants.value], ([newValue1, newValue2], [oldValue1, oldValue2]) => {
        // 判断 variants.value 为空 .skus-single-box 显示
        if (!variants.value.length) {
          $('.skus-single-box').removeClass('d-none')
        } else {
          $('.skus-single-box').addClass('d-none')
        }

        // 判断 variants.value 只有一个规格的时候 并且 values 为空的时候，就不生成 sku
        if (variants.value.length == 1 && isObjectValuesEmpty(variants.value[0].values[0].name)) {
          return
        }

        generateSku()
        // 如果是 mainVariantKey 发生变化，就要完全重新生成 smallVariants
        smallVariantsFormat(newValue1 != oldValue1 ? true : false)
      });

      watch(skus, (newValue, oldValue) => {
        validateSkus()
      }, {deep: true})

      // 生成 通过...分组 的几个大规格
      const smallVariantsFormat = () => {
        if (variants.value.length == 0) {
          smallVariants.value = []
          return
        }

        // 直接使用 skus 数据,不再进行分组
        smallVariants.value = skus.value.map((sku, index) => ({
          ...sku,
          init_index: index,
          show_variant: false,
          sku_quantity: null
        }))
      }

      const addVariantValue = (index) => {
        variants.value[index].values.push({name: localesFill('')})
      }

// const showVariant = (init_index, index) => {
      // // skus 每 sku_quantity 个分组
      // let sku_quantity = smallVariants.value[index].sku_quantity
      // let tempSkus = skus.value.slice(init_index * sku_quantity, (init_index + 1) * sku_quantity)

// if (smallVariants.value[index].show_variant) {
      // smallVariants.value[index].show_variant = false
      // smallVariants.value.splice(index + 1, sku_quantity)
      // } else {
      // smallVariants.value[index].show_variant = true
      // smallVariants.value.splice(index + 1, 0, ...tempSkus)
      // }
      // }

      // 修改 sku值，分 批量修改 和 单个修改
      const modifySku = (init_index, index, type) => {
        let sku_quantity = smallVariants.value[index].sku_quantity
        // let sku = smallVariants.value[(init_index * sku_quantity) + init_index]
        let sku = smallVariants.value[index]
        let tempSkus = skus.value.slice(init_index * sku_quantity, (init_index + 1) * sku_quantity)

        tempSkus.forEach((e, i) => {
          e[type] = sku[type]
        })

        // 获取有多少个相同的sku,然后加下标, 判断一个 - 前的字符是否相同，如果相同就加上下标
        if (typeof init_index != 'undefined' ) {
          let sameSku = skus.value.filter((e, i) => e.code.split('-')[0] === sku.code)
          sameSku.forEach((e, i) => {
            e.code = sku.code + '-' + i
          })
        }
      }

      const addVariant = () => {
        variants.value.push({
          name: localesFill(''),
          error: false,
          variantFormShow: true,
          values: [{name: localesFill(''), error: false}],
        })
      }

      const deleteVariant = (index) => {
        variants.value.splice(index, 1)

        if (index < mainVariantKey.value) {
          mainVariantKey.value--
        } else if (index === mainVariantKey.value) {
          mainVariantKey.value = 0
        }
      }

      const saveVariant = (index) => {
        let isError = true

        variants.value.forEach((e, i) => {
          if (isObjectValuesEmpty(e.name)) {
            e.error = true
            isError = false
          } else {
            e.error = false
          }

          e.values.forEach((value, j) => {
            if (isObjectValuesEmpty(value.name)) {
              value.error = true
              isError = false
            } else {
              value.error = false
            }
          })
        })

        if (!isError) {
          return
        }

        variants.value[index].variantFormShow = false
        localStorage.setItem('variants', JSON.stringify(variants.value))
      }

      // 生成 sku 组合
      const generateSku = () => {
        if (variants.value.length === 0) {
          return
        }

        let mainVariant = variants.value[mainVariantKey.value]
        let tempVariants = [mainVariant, ...variants.value.filter((e, i) => i !== mainVariantKey.value)]
        let sku = []
        let skuVariants = []
        let skuVariantsLength = tempVariants.length
        let skuVariantsIndex = Array(skuVariantsLength).fill(0)
        let skuVariantsValues = tempVariants.map(e => e.values.length)

        for (let i = 0; i < skuVariantsValues.reduce((a, b) => a * b); i++) {
          // 如果sku中有值，就用原来的值，否则就用默认值
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
          }

          for (let j = 0; j < skuVariantsLength; j++) {
            skuItem.variants.push(skuVariantsIndex[j])
            skuItem.text += ' ' + tempVariants[j].values[skuVariantsIndex[j]].name[defaultLocale] + ' /'
          }

          skuItem.text = skuItem.text.slice(0, -1)
          sku.push(skuItem)

          // 递增 skuVariantsIndex
          for (let j = skuVariantsLength - 1; j >= 0; j--) {
            if (skuVariantsIndex[j] < skuVariantsValues[j] - 1) {
              skuVariantsIndex[j]++
              break
            } else {
              skuVariantsIndex[j] = 0
            }
          }
        }

        // 如果 is_default 都是0 那么就把第一个设置为主规格
        let isMaster = sku.filter((e, i) => e.is_default == 1)
        if (isMaster.length == 0) {
          sku[0].is_default = 1
        }

        skus.value = sku
      }

      const upVariantImage = (init_index, index) => {
        $('#form-upload').remove();
        $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" accept="image/*" name="file" /></form>');
        $('#form-upload input[name=\'file\']').trigger('click');
        $('#form-upload input[name=\'file\']').change(function () {
          let file = $(this).prop('files')[0];
          skuImgUploadAjax(file, init_index, index);
        });
      }

      const skuImgUploadAjax = (file, init_index, index) => {
        if (file.type.indexOf('image') === -1) {
          alert('请上传图片文件');
          return;
        }

        let formData = new FormData();
        formData.append('image', file);
        formData.append('type', 'common');
        axios.post('{{ front_root_route('upload.images') }}', formData, {}).then(function (res) {
          let val = res.data.value;

          // 如果 init_index 不等于 undefined，就要替换这个规格下的所有 sku 的图片
          if (typeof init_index != 'undefined') {
            skus.value.forEach((e, i) => {
              console.log(e.variants[0], init_index);
              if (e.variants[0] == init_index) {
                e.image = val
              }
            })

            smallVariants.value[index].image = val
          } else {
            getSkusItem(index).image = val
          }

        }).catch(function (err) {
          inno.msg(err.response.data.message);
        })
      }

      const dragVariantsEnd = (evt) => {
        // console.log(evt);
        const oldIndex = evt.oldIndex;
        const newIndex = evt.newIndex;
        // console.log(mainVariantKey.value);
        // 拖拽 Variants 之后要保证 mainVariantKey 的值是正确的

        if (oldIndex == mainVariantKey.value) {
          mainVariantKey.value = newIndex
        } else if (oldIndex < mainVariantKey.value && newIndex >= mainVariantKey.value) {
          mainVariantKey.value--
        } else if (oldIndex > mainVariantKey.value && newIndex <= mainVariantKey.value) {
          mainVariantKey.value++
        }
      }

      const thumbnail = (image) => {
        const asset = document.querySelector('meta[name="asset"]').content;
        if (!image) {
          return 'image/placeholder.png';
        }

        // 判断 image 是否以 http 开头
        if (image.indexOf('http') === 0) {
          return image;
        }

        return asset + image;
      }

      const setMasterSku = (index) => {
        skus.value.forEach((e, i) => {
          e.is_default = 0
        })

        getSkusItem(index).is_default = 1
      }

      // 找出 skus中的variants 与 smallVariants.value[index].variants 是一样的
      const getSkusItem = (index) => {
        return tempSkus = skus.value.find((e, i) => {
          return e.variants.toString() == smallVariants.value[index].variants.toString()
        })
      }

      // 检测 variants 内容是否有值
      const validateVariants = () => {
        variants.value.forEach((e, i) => {
          if (isObjectValuesEmpty(e.name)) {
            e.error = true
          } else {
            e.error = false
          }

          e.values.forEach((value, j) => {
            if (isObjectValuesEmpty(value.name)) {
              value.error = true
            } else {
              value.error = false
            }
          })
        })
      }

      // 检测 skus 中是否有重复的 code
      const validateSkus = () => {
        skus.value.forEach((e, i) => {
          let sameSku = skus.value.filter((s, j) => s.code == e.code)
          if (sameSku.length > 1) {
            e.error = true
          } else {
            e.error = false
          }
        })
      }

      const allVariantEC = () => {
        showAllVariant.value = !showAllVariant.value
        // 把 smallVariants 里面的 show_variant 全部设置为true
        smallVariants.value.forEach((e, i) => {
          e.show_variant = showAllVariant.value
        })

        smallVariantsFormat()
      }

      const validateForm = () => {
        // 检查单规格表单
        const singleSkuPrice = $('input[name="skus[0][price]"]').val();
        const singleSkuQuantity = $('input[name="skus[0][quantity]"]').val();
        const singleSkuCode = $('input[name="skus[0][code]"]').val();

        // 检查多规格
        const hasValidVariants = variants.value.length > 0 && skus.value.some(sku => {
          return sku.price && sku.quantity && (sku.is_default === 1);
        });

        // 确保至少有一个完整的 SKU 信息
        return hasValidVariants || (singleSkuPrice && singleSkuQuantity);
      }

      // 监听表单提交
      onMounted(() => {
        $('#product-form').on('submit', function(e) {
          if (!validateForm()) {
            e.preventDefault();
            layer.msg('请至少填写单规格信息或添加多规格商品信息', {icon: 2});
            return false;
          }
        });
      });

      // 在切换到多规格时提示用户
      watch(variants, (newValue) => {
        if (newValue.length > 0) {
          const singleSkuPrice = $('input[name="skus[0][price]"]').val();
          const singleSkuQuantity = $('input[name="skus[0][quantity]"]').val();
          const singleSkuCode = $('input[name="skus[0][code]"]').val();

          if (singleSkuPrice || singleSkuQuantity || singleSkuCode) {
            layer.confirm('切换到多规格后，单规格信息将被忽略，是否继续？', {
              btn: ['继续', '取消']
            }, function() {
              // 用户确认后，可以选择将单规格数据作为第一个多规格的默认值
              const firstSku = skus.value[0];
              if (firstSku) {
                firstSku.price = singleSkuPrice || '';
                firstSku.quantity = singleSkuQuantity || '';
                firstSku.code = singleSkuCode || '';
                firstSku.is_default = 1;
              }

              // 清空单规格表单
              $('input[name="skus[0][price]"]').val('');
              $('input[name="skus[0][quantity]"]').val('');
              $('input[name="skus[0][code]"]').val('');
              layer.closeAll();
            }, function() {
              // 用户取消，回滚多规格添加
              variants.value.pop();
            });
          }
        } else {
          // 切换回单规格模式时，将默认 SKU 的数据同步到单规格表单
          const defaultSku = skus.value.find(sku => sku.is_default === 1);
          if (defaultSku) {
            $('input[name="skus[0][price]"]').val(defaultSku.price);
            $('input[name="skus[0][quantity]"]').val(defaultSku.quantity);
            $('input[name="skus[0][code]"]').val(defaultSku.code);
          }
        }
      }, { deep: true });

      // Add validation methods
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
// showVariant,
        modifySku,
        upVariantImage,
        dragVariantsEnd,
        thumbnail,
        setMasterSku,
        showAllVariant,
        allVariantEC,
        // 添加新的返回值
        batchData,
        batchFillSkuCode,
        batchFillColumn,
      }
    }
  }).mount('#variants-box');

  // 将数组分割成指定长度的数组
  function chunkArray(array, chunkSize) {
    let chunks = [];
    for (let i = 0; i < array.length; i += chunkSize) {
      chunks.push(array.slice(i, i + chunkSize));
    }
    return chunks;
  }

  // 将数组分为指定组
  function splitArrayIntoGroups(array, groupCount) {
    if (groupCount <= 0) {
      throw new Error('组的数量必须大于 0');
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

  // 判断对象里面所有值是否为空
  function isObjectValuesEmpty(obj) {
    for (let key in obj) {
      if (obj[key] != '') {
        return false
      }
    }

    return true
  }
</script>
@endpush
