@push('header')
  <script src="{{ asset('vendor/vue/3.5/vue.global.prod.js') }}"></script>
  <script src="{{ asset('vendor/vuedraggable/sortable.min.js') }}"></script>
  <script src="{{ asset('vendor/vuedraggable/vuedraggable.umd.min.js') }}"></script>
@endpush

<div class="card variants-box mb-3" id="variants-box">
  @php($weightUnit = $product->weight_class ?: system_setting('weight_class', 'kg'))
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">{{ __('panel/product.variant') }}</h5>
    <div>
      <button type="button" class="btn btn-outline-primary btn-sm" @click="openSaveTemplateModal">
        {{ __('panel/product.save_as_template') }}
      </button>
      <button type="button" class="btn btn-outline-secondary btn-sm ms-2" @click="openLoadTemplateModal" :disabled="templateDialog.loading">
        <span v-if="templateDialog.loading" class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
        {{ __('panel/product.load_template') }}
      </button>
    </div>
  </div>

  <div class="card-body py-0">
    <div class="variant-wrap" v-if="variants.length">
      <input type="hidden" name="variants" :value="JSON.stringify(variants)">
      <input type="hidden" name="skus" :value="JSON.stringify(skus)">
      <draggable
        v-model="variants"
        handle=".drag-variants-handle"
        :animation="300"
        item-key="id">
        <template #item="{element: variant}">
          <div class="variant-item">
            <div class="variant-data">
              <div class="variant-header d-flex justify-content-between align-items-center mb-3">
                <div class="left d-flex align-items-center">
                  <div class="icon drag-variants-handle me-2"><i class="bi bi-grip-vertical"></i></div>
                  <div class="title">@{{ variant.name[defaultLocale] || getFirstAvailableLocaleValue(variant.name) }}</div>
                  <div class="form-check form-switch ms-3 mb-0">
                    <input class="form-check-input" type="checkbox" role="switch"
                           v-model="variant.isImage" :id="`variant-is-image-${variant.id}`">
                    <label class="form-check-label" :for="`variant-is-image-${variant.id}`">
                      {{ __('panel/product.is_image_variant') }}
                    </label>
                  </div>
                </div>
                <div class="action-buttons right">
                  <button type="button" class="btn btn-outline-primary btn-sm" @click="openVariantDialog(variant.id, null)">{{ __('common/base.edit') }}</button>
                  <button type="button" class="btn btn-outline-danger btn-sm ms-2" @click="deleteVariant(variant.id)">{{ __('common/base.delete') }}</button>
                </div>
              </div>
              <div class="variant-values">
                <div class="variant-values-container">
                  <draggable
                    v-model="variant.values"
                    handle=".value-grip"
                    :animation="200"
                    item-key="id"
                    class="variant-values-list d-flex flex-wrap align-items-center">
                    <template #item="{element: value}">
                      <div class="variant-value-item"
                           :class="{ 'has-image': variant.isImage, 'is-editing': inlineEdit.variantId === variant.id && inlineEdit.valueId === value.id }"
                           @dblclick="openVariantDialog(variant.id, value.id)">
                        <div class="value-grip" @click.stop @mousedown.stop title="{{ __('panel/product.drag_to_reorder') }}">
                          <i class="bi bi-grip-vertical"></i>
                        </div>
                        <div v-if="variant.isImage" class="value-image open-media"
                             @click.stop="selectVariantValueImage(variant.id, value.id)"
                             title="{{ __('panel/product.sku_image') }}">
                          <img v-if="value.image" :src="thumbnail(value.image)">
                          <i v-else class="bi bi-image"></i>
                        </div>
                        <div v-if="inlineEdit.variantId === variant.id && inlineEdit.valueId === value.id"
                             class="value-name-edit" @click.stop>
                          <input type="text"
                                 :data-inline-edit="value.id"
                                 v-model="inlineEdit.draft"
                                 @keydown.enter.prevent="commitInlineEdit"
                                 @keydown.esc.prevent="cancelInlineEdit"
                                 @blur="commitInlineEdit">
                        </div>
                        <span v-else class="value-name"
                              @click="startInlineEdit(variant.id, value.id)"
                              title="{{ __('panel/product.click_to_edit') }}">
                          @{{ value.name[defaultLocale] || getFirstAvailableLocaleValue(value.name) }}
                        </span>
                        <div class="value-toolbar">
                          <button type="button" class="value-toolbar-btn"
                                  @click.stop="startInlineEdit(variant.id, value.id)"
                                  title="{{ __('panel/product.click_to_edit') }}">
                            <i class="bi bi-pencil"></i>
                          </button>
                          <button type="button" class="value-toolbar-btn"
                                  @click.stop="openVariantDialog(variant.id, value.id)"
                                  title="{{ __('panel/product.edit_translations') }}">
                            <i class="bi bi-translate"></i>
                          </button>
                          <button type="button" class="value-toolbar-btn value-toolbar-btn-danger"
                                  @click.stop="deleteVariantValue(variant.id, value.id)"
                                  title="{{ __('common/base.delete') }}">
                            <i class="bi bi-trash3"></i>
                          </button>
                        </div>
                      </div>
                    </template>
                    <template #footer>
                      <button type="button" class="add-value-btn" @click="openVariantDialog(variant.id, '__NEW__')">
                        <i class="bi bi-plus-lg"></i>
                        <span>{{ __('panel/product.add_variant_value') }}</span>
                      </button>
                    </template>
                  </draggable>
                </div>
              </div>
            </div>
          </div>
        </template>
      </draggable>
    </div>
    <div :class="['text-primary add-variant', !variants.length ? 'no-variants' : '']">
      <div class="d-inline-block cursor-pointer" @click="openVariantDialog('__NEW__', null)"><i class="bi bi-plus-square me-1"></i> {{ __('panel/product.add_variant') }}</div>
    </div>
    <div class="variant-skus-wrap" v-if="skus.length">
      <div class="batch-settings-panel mb-3">
        <div class="card shadow-sm" style="border: none;">
          <div class="card-body py-3">
            <div class="mb-2" v-if="variants.length > 0">
              <label class="form-label small fw-bold mb-2">{{ __('panel/product.sku_batch_setting') }}</label>
              <div class="variant-selector-container">
                <div class="row g-2 mb-2" v-for="variant in variants" :key="variant.id">
                  <div class="col-md-2">
                    <label class="form-label small mb-1">@{{ variant.name[defaultLocale] || getFirstAvailableLocaleValue(variant.name) }}</label>
                  </div>
                  <div class="col-md-10">
                    <div class="d-flex flex-wrap gap-1 align-items-center">
                      <div class="form-check me-2" v-for="value in variant.values" :key="value.id">
                        <input class="form-check-input" type="checkbox"
                               :id="`variant_${variant.id}_${value.id}`"
                               :value="value.id"
                               v-model="batchData.selectedValueIds">
                        <label class="form-check-label" :for="`variant_${variant.id}_${value.id}`">
                          @{{ value.name[defaultLocale] || getFirstAvailableLocaleValue(value.name) }}
                        </label>
                      </div>
                      <button type="button" class="btn btn-outline-primary btn-sm ms-2"
                              @click="selectAllVariantValues(variant.id)">
                        {{ __('panel/product.select_all') }}
                      </button>
                      <button type="button" class="btn btn-outline-secondary btn-sm"
                              @click="clearVariantSelection(variant.id)">
                        {{ __('panel/product.clear') }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row g-2">
              <div class="col">
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

              <div class="col">
                <label class="form-label small mb-1">SKU {{ __('panel/product.bulk_fill') }}</label>
                <input type="text" class="form-control form-control-sm" v-model="batchData.skuPrefix"
                       placeholder="{{ __('panel/product.bulk_fill_sku') }}" style="height: 31px;">
              </div>

              <div class="col">
                <label class="form-label small mb-1">{{ __('panel/product.price') }}</label>
                <input type="number" class="form-control form-control-sm" v-model="batchData.price"
                       placeholder="{{ __('panel/product.bulk_fill_price') }}" min="0" @change="validateBatchPrice" style="height: 31px;">
              </div>

              <div class="col">
                <label class="form-label small mb-1">{{ __('panel/product.origin_price') }}</label>
                <input type="number" class="form-control form-control-sm" v-model="batchData.originPrice"
                       placeholder="{{ __('panel/product.bulk_fill_origin_price') }}" min="0" @change="validateBatchOriginPrice" style="height: 31px;">
              </div>

              <div class="col">
                <label class="form-label small mb-1">{{ __('panel/product.model') }}</label>
                <input type="text" class="form-control form-control-sm" v-model="batchData.model"
                       placeholder="{{ __('panel/product.bulk_fill_model') }}" style="height: 31px;">
              </div>

              <div class="col">
                <label class="form-label small mb-1">{{ __('panel/product.quantity') }}</label>
                <input type="number" class="form-control form-control-sm" v-model="batchData.quantity"
                       placeholder="{{ __('panel/product.bulk_fill_quantity') }}" min="0" @input="validateBatchQuantity" style="height: 31px;">
              </div>

              <div class="col">
                <label class="form-label small mb-1">{{ __('panel/product.weight') }} ({{ $weightUnit }})</label>
                <input type="number" class="form-control form-control-sm" v-model="batchData.weight"
                       placeholder="{{ __('panel/product.weight') }}" min="0" style="height: 31px;">
              </div>

              @hookinsert('panel.product.edit.sku.batch.input.item.after')
            </div>

            <div class="row g-2 mt-2">
              <div class="col-12 text-end">
                <button type="button" class="btn btn-success fw-bold" @click="batchApplySelected" style="height: 31px; font-size: 12px;">
                  <i class="bi bi-lightning-charge-fill me-1"></i>{{ __('panel/product.bulk_fill') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

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
              <th>{{ __('panel/product.weight') }} ({{ $weightUnit }})</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(sku, index) in skus" :key="sku.id">
              <td>
                <div class="sku-image-name">
                  <div class="up-variant-image" @click="upVariantImage(index)">
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
                  @change="validatePrice(sku)">
                @hookinsert('panel.product.edit.sku.input.item.price.after')
              </td>
              <td>
                <input type="text" class="form-control form-control-sm"
                  v-model="sku.origin_price" placeholder="{{ __('panel/product.origin_price') }}"
                  @change="validateOriginPrice(sku)">
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
              <td>
                <input type="number" class="form-control form-control-sm"
                  v-model="sku.weight" placeholder="{{ __('panel/product.weight') }}"
                  min="0">
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  @hookinsert('panel.product.edit.variant.after')

  <!-- variant/value edit modal -->
  <div class="modal fade" id="variantEditModal" tabindex="-1" aria-hidden="true" v-if="dialogVariables.show">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">@{{ dialogVariables.title }}</h5>
          <button type="button" class="btn-close" @click="closeVariantDialog"></button>
        </div>
        <div class="modal-body">
          <form ref="variantForm">
            <div class="locale-modal-row mb-3 p-2 rounded border border-primary" :data-locale="defaultLocale">
              <div class="d-flex align-items-center gap-2 mb-1">
                <div class="d-flex align-items-center wh-20">
                  <img :src="'/images/flags/'+ defaultLocale +'.svg'" class="img-fluid" :alt="defaultLocaleName">
                </div>
                <span class="fw-medium">@{{ defaultLocaleName }}</span>
                <span class="badge bg-primary">{{ __('panel/common.panel_locale') }}</span>
              </div>
              <input type="text" class="form-control"
                     v-model="dialogVariables.form.name[defaultLocale]"
                     :placeholder="'{{ __('panel/product.name') }}'"
                     :data-locale="defaultLocale">
            </div>

            <div v-for="locale in otherLocales" :key="locale.code"
                 class="locale-modal-row mb-3 p-2 rounded border" :data-locale="locale.code">
              <div class="d-flex align-items-center gap-2 mb-1">
                <div class="d-flex align-items-center wh-20">
                  <img :src="'/images/flags/'+ locale.code +'.svg'" class="img-fluid" :alt="locale.name">
                </div>
                <span class="fw-medium">@{{ locale.name }}</span>
                @if(has_translator())
                  <button type="button" class="btn btn-sm btn-outline-primary ms-auto variant-translate-btn"
                          :data-locale-target="locale.code"
                          @click.prevent="translateVariantName(locale.code, $event)"
                          :title="defaultLocaleName + ' → ' + locale.name">
                    <i class="bi bi-translate"></i>
                  </button>
                @endif
              </div>
              <input type="text" class="form-control"
                     v-model="dialogVariables.form.name[locale.code]"
                     :placeholder="'{{ __('panel/product.name') }}'"
                     :data-locale="locale.code">
            </div>
          </form>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          @php($hasTranslator = has_translator())
          <button type="button" class="btn btn-outline-secondary" @click="fillEmptyLocales">
            @if($hasTranslator)
              <i class="bi bi-translate me-1"></i>{{ __('panel/common.translate_empty') }}
            @else
              <i class="bi bi-arrow-right-circle me-1"></i>{{ __('panel/common.copy_empty') }}
            @endif
          </button>
          <div>
            <button type="button" class="btn btn-secondary" @click="closeVariantDialog">{{ __('common/base.cancel') }}</button>
            <button type="button" class="btn btn-primary ms-2" @click="saveVariantDialog">{{ __('panel/common.confirm') }}</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="saveTemplateModal" tabindex="-1" aria-hidden="true" v-if="templateDialog.saveShow">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ __('panel/product.save_as_template') }}</h5>
          <button type="button" class="btn-close" @click="closeSaveTemplateModal"></button>
        </div>
        <div class="modal-body">
          <label class="form-label">{{ __('panel/product.template_name') }}</label>
          <input type="text" class="form-control" v-model="templateDialog.saveName" :placeholder="'{{ __('panel/product.template_name') }}'">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeSaveTemplateModal">{{ __('common/base.cancel') }}</button>
          <button type="button" class="btn btn-primary" @click="saveTemplate" :disabled="templateDialog.loading">
            {{ __('panel/common.confirm') }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="loadTemplateModal" tabindex="-1" aria-hidden="true" v-if="templateDialog.loadShow">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content position-relative">
        <div v-if="templateDialog.loading" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center bg-white bg-opacity-75" style="z-index: 1055;">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <div class="mt-2 text-primary">{{ __('panel/product.loading_template') }}</div>
        </div>
        <div class="modal-header">
          <h5 class="modal-title">{{ __('panel/product.load_template') }}</h5>
          <button type="button" class="btn-close" @click="closeLoadTemplateModal"></button>
        </div>
        <div class="modal-body">
          <div v-if="templateDialog.templates.length === 0" class="text-muted py-3 text-center">
            {{ __('panel/product.no_templates') }}
          </div>
          <div v-else class="list-group">
            <label v-for="template in templateDialog.templates" :key="template.id" class="list-group-item d-flex justify-content-between align-items-center">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="variant_template_id" :value="template.id" v-model="templateDialog.selectedId" :disabled="templateDialog.loading">
                <span class="form-check-label ms-2">@{{ template.name }}</span>
              </div>
              <button type="button" class="btn btn-link btn-sm text-danger text-decoration-none" @click.stop="deleteTemplate(template.id)" :disabled="templateDialog.loading">
                {{ __('common/base.delete') }}
              </button>
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeLoadTemplateModal" :disabled="templateDialog.loading">{{ __('common/base.cancel') }}</button>
          <button type="button" class="btn btn-primary" @click="applyTemplate" :disabled="!templateDialog.selectedId || templateDialog.loading">
            <span v-if="templateDialog.loading" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
            {{ __('panel/common.confirm') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

@push('footer')
<script>
  const { createApp, ref, watch, onMounted, getCurrentInstance, nextTick, computed } = Vue
  const draggable = window.vuedraggable;

  const $locales = @json(locales());
  const localesFill = (text) => {
    let obj = {};
    $locales.map(e => { obj[e.code] = text; })
    return obj;
  }

  const productWeight = @json($product->weight ?? 0);

  @php($variantTemplateRoutes = [
    'index'   => route('panel.variant_templates.index'),
    'store'   => route('panel.variant_templates.store'),
    'show'    => route('panel.variant_templates.show', ['variant_template' => '__ID__']),
    'destroy' => route('panel.variant_templates.destroy', ['variant_template' => '__ID__']),
  ])
  const variantTemplateRoutes = @json($variantTemplateRoutes);
  const csrfToken = @json(csrf_token());

  // Client-side id generator. Used for newly created variants/values that
  // don't yet have a DB id; persisted ones echo back the DB id (string) on
  // load. Collisions are practically impossible with this prefix scheme.
  const newId = (prefix) => `${prefix}_${Date.now().toString(36)}_${Math.random().toString(36).slice(2, 8)}`;

  let variantsBoxApp = createApp({
    components: { draggable },

    setup() {
      const instance = getCurrentInstance();
      const locales = $locales;
      const defaultLocale = @json(panel_locale_code());
      const otherLocales = computed(() => locales.filter(l => l.code !== defaultLocale));
      const defaultLocaleName = locales.find(l => l.code === defaultLocale)?.name || defaultLocale;

      const variantLocaleHelper = new LocaleModalHelper({
        getLocaleValue: (code) => dialogVariables.value.form.name[code] || '',
        setLocaleValue: (code, val) => { dialogVariables.value.form.name[code] = val; },
      });
      const translateVariantName = (targetLocale, event) => {
        variantLocaleHelper.translate(targetLocale, event?.currentTarget);
      };
      const fillEmptyLocales = () => { variantLocaleHelper.fillEmpty(); };

      // Normalize incoming variant definitions (DB-loaded or user-resubmitted)
      // into the canonical editor shape. Accepts both the API shape
      // (is_image snake_case, DB id as string) and the legacy editor shape
      // (isImage camelCase, no id).
      const normalizeVariants = (raw) => {
        if (!Array.isArray(raw)) return [];
        return raw.map((v, vIdx) => ({
          id: v.id || newId('var'),
          name: v.name || localesFill(''),
          isImage: !!(v.isImage ?? v.is_image),
          error: false,
          values: (v.values || []).map((val, valIdx) => ({
            id: val.id || newId('val'),
            name: val.name || localesFill(''),
            image: val.image || '',
            error: false,
          })),
        }));
      };

      // Normalize SKUs into the canonical editor shape, accepting whichever
      // payload format the controller happened to send:
      //   - SkuListItem resource: { image, variant_value_ids }
      //   - Eloquent model toArray: { images: [...], variant_values: [{pivot:{value_id}}] }
      //   - Legacy positional: { variants: [0, 1] }
      const normalizeSkus = (raw, normalizedVariants) => {
        if (!Array.isArray(raw)) return [];
        return raw.map((s, idx) => {
          // 1. Resolve image: SkuListItem uses `image` (string), Eloquent uses `images` (array)
          let image = s.image ?? '';
          if (!image && Array.isArray(s.images) && s.images.length > 0) {
            image = s.images[0] ?? '';
          }
          let imageUrl = s.image_url ?? '';
          if (!imageUrl && image) {
            imageUrl = image.indexOf('http') === 0 ? image : '';
          }

          // 2. Resolve variant_value_ids — try multiple sources
          let valueIds = null;
          if (Array.isArray(s.variant_value_ids)) {
            // SkuListItem shape — already DB ids as strings
            valueIds = s.variant_value_ids.map(String);
          } else if (Array.isArray(s.variant_values) && s.variant_values.length > 0) {
            // Eloquent shape — extract from pivot or direct value_id
            valueIds = s.variant_values
              .map(v => v.pivot?.value_id ?? v.value_id)
              .filter(Boolean)
              .map(String);
          } else if (Array.isArray(s.variants)) {
            // Legacy positional index — translate via normalized variants
            valueIds = s.variants
              .map((valIdx, vIdx) => normalizedVariants[vIdx]?.values[valIdx]?.id)
              .filter(Boolean)
              .map(String);
          }

          return {
            id: s.id || newId('sku'),
            code: s.code ?? '',
            price: s.price ?? '',
            origin_price: s.origin_price ?? '',
            quantity: s.quantity ?? '',
            weight: s.weight ?? productWeight,
            model: s.model ?? '',
            image: image,
            image_url: imageUrl,
            is_default: s.is_default ?? (idx === 0 ? 1 : 0),
            variant_value_ids: valueIds || [],
            error: false,
            text: '',
          };
        });
      };

      // Load existing variant definitions with their stable DB ids so SKUs
      // can match by variant_value_ids during regenerateSkus(). On a failed
      // form submission, `old('variants')` repopulates the user's last edit.
      const variants = ref(normalizeVariants(@json(old('variants', $product->variant_dimensions ?? []))));
      if (typeof variants.value === 'string') {
        variants.value = normalizeVariants(JSON.parse(variants.value));
      }

      const skus = ref(normalizeSkus(@json(old('skus', $skus ?? [])), variants.value));
      if (typeof skus.value === 'string') {
        try { skus.value = normalizeSkus(JSON.parse(skus.value), variants.value); }
        catch (e) { skus.value = []; }
      }

      const batchData = ref({
        skuPrefix: '',
        price: '',
        originPrice: '',
        model: '',
        quantity: '',
        weight: '',
        image: '',
        selectedValueIds: [],   // Set<value.id> — replaces 2D selectedVariants index array
      });

      const dialogVariables = ref({
        show: false,
        variantId: null,        // variant client/DB id, or '__NEW__'
        valueId: null,          // value client/DB id, '__NEW__', or null (editing variant itself)
        title: '',
        form: { name: {} },
      });

      // Inline edit state for a value's default-locale name. Only one value
      // is editable at a time; commit on blur/Enter, abort on Esc. Other
      // locales are still edited through the modal (double-click the chip).
      const inlineEdit = ref({ variantId: null, valueId: null, draft: '' });

      const templateDialog = ref({
        saveShow: false,
        loadShow: false,
        saveName: '',
        templates: [],
        selectedId: null,
        loading: false,
      });

      // Regenerate SKU cartesian product whenever variants change. Existing
      // SKUs are matched by sorted value-id set so user-entered data (price,
      // code, image, ...) is preserved across reorder/delete/add operations.
      const regenerateSkus = () => {
        if (variants.value.length === 0) {
          skus.value = [];
          return;
        }
        if (variants.value.some(v => v.values.length === 0)) {
          return;
        }

        const existingByKey = {};
        skus.value.forEach(s => {
          const key = [...(s.variant_value_ids || [])].sort().join('|');
          existingByKey[key] = s;
        });

        const newSkus = [];
        const indices = variants.value.map(() => 0);

        while (true) {
          const valueIds = variants.value.map((v, i) => v.values[indices[i]].id);
          const key = [...valueIds].sort().join('|');
          const text = variants.value
            .map((v, i) => v.values[indices[i]].name[defaultLocale] || getFirstAvailableLocaleValue(v.values[indices[i]].name))
            .join(' / ');

          const existing = existingByKey[key];
          if (existing) {
            existing.text = ' ' + text;
            existing.variant_value_ids = valueIds;
            newSkus.push(existing);
          } else {
            newSkus.push({
              id: newId('sku'),
              code: '',
              price: '',
              origin_price: '',
              quantity: '',
              weight: productWeight,
              model: '',
              image: '',
              image_url: '',
              is_default: 0,
              variant_value_ids: valueIds,
              error: false,
              text: ' ' + text,
            });
          }

          let i = variants.value.length - 1;
          while (i >= 0) {
            indices[i]++;
            if (indices[i] < variants.value[i].values.length) break;
            indices[i] = 0;
            i--;
          }
          if (i < 0) break;
        }

        if (!newSkus.some(s => s.is_default == 1)) {
          newSkus[0].is_default = 1;
        }

        skus.value = newSkus;
      };

      // Show/hide single-SKU box based on variants presence.
      watch(variants, () => {
        if (!variants.value.length) {
          $('.skus-single-box').removeClass('d-none');
        } else {
          $('.skus-single-box').addClass('d-none');
        }

        const firstVariant = variants.value[0];
        if (
          variants.value.length === 1 &&
          firstVariant?.values?.[0] &&
          isObjectValuesEmpty(firstVariant.values[0].name)
        ) {
          return;
        }

        regenerateSkus();
      }, { deep: true });

      // Single-SKU box sync. Variants present → pull single-SKU form values
      // into the first variant SKU; no variants → push default SKU back.
      watch(() => variants.value.length, (len) => {
        if (len > 0) {
          const singleSkuPrice = $('input[name="skus[0][price]"]').val();
          const singleSkuQuantity = $('input[name="skus[0][quantity]"]').val();
          const singleSkuCode = $('input[name="skus[0][code]"]').val();
          if (singleSkuPrice || singleSkuQuantity || singleSkuCode) {
            const firstSku = skus.value[0];
            if (firstSku) {
              firstSku.price = singleSkuPrice || '';
              firstSku.quantity = singleSkuQuantity || '';
              firstSku.code = singleSkuCode || '';
              firstSku.weight = $('input[name="skus[0][weight]"]').val() || firstSku.weight;
              firstSku.is_default = 1;
            }
            $('input[name="skus[0][price]"]').val('');
            $('input[name="skus[0][quantity]"]').val('');
            $('input[name="skus[0][code]"]').val('');
            $('input[name="skus[0][weight]"]').val('');
          }
        } else {
          const defaultSku = skus.value.find(s => s.is_default == 1);
          if (defaultSku) {
            $('input[name="skus[0][price]"]').val(defaultSku.price);
            $('input[name="skus[0][quantity]"]').val(defaultSku.quantity);
            $('input[name="skus[0][code]"]').val(defaultSku.code);
            $('input[name="skus[0][weight]"]').val(defaultSku.weight);
          }
        }
      });

      watch(skus, () => { validateSkus(); }, { deep: true });

      onMounted(() => {
        regenerateSkus();
        $('#product-form').on('submit', function(e) {
          if (!validateForm()) {
            e.preventDefault();
            layer.msg('{{ trans("panel/product.sku_validation_error") }}', {icon: 2});
            return false;
          }
        });
      });

      const getFirstAvailableLocaleValue = (localeObject) => {
        if (!localeObject) return '';
        const systemDefaultLocale = @json(setting_locale_code());
        if (localeObject[systemDefaultLocale]) return localeObject[systemDefaultLocale];
        for (const locale of locales) {
          if (localeObject[locale.code] && localeObject[locale.code].trim() !== '') {
            return localeObject[locale.code];
          }
        }
        return '';
      };

      const findVariantIndex = (variantId) => variants.value.findIndex(v => v.id === variantId);
      const findValueIndex = (variant, valueId) => variant.values.findIndex(val => val.id === valueId);

      const deleteVariant = (variantId) => {
        const idx = findVariantIndex(variantId);
        if (idx < 0) return;
        variants.value.splice(idx, 1);
      };

      const upVariantImage = (skuIndex) => {
        inno.mediaIframe((file) => {
          if (file.path) skus.value[skuIndex].image = file.path;
        }, { type: 'image', multiple: false });
      };

      const thumbnail = (image) => {
        const asset = document.querySelector('meta[name="asset"]').content;
        if (!image) return 'image/placeholder.png';
        if (image.indexOf('http') === 0) return image;
        return asset + image;
      };

      const setMasterSku = (index) => {
        skus.value.forEach(s => { s.is_default = 0; });
        skus.value[index].is_default = 1;
      };

      const validateVariants = () => {
        variants.value.forEach(e => {
          e.error = isObjectValuesEmpty(e.name);
          e.values.forEach(value => { value.error = isObjectValuesEmpty(value.name); });
        });
      };

      const validateSkus = () => {
        skus.value.forEach(e => {
          const sameSku = skus.value.filter(s => s.code === e.code);
          e.error = sameSku.length > 1;
        });
      };

      const validateForm = () => {
        const singleSkuPrice = $('input[name="skus[0][price]"]').val();
        const singleSkuQuantity = $('input[name="skus[0][quantity]"]').val();
        const hasValidVariants = variants.value.length > 0 && skus.value.some(sku => {
          return sku.price && sku.quantity && (sku.is_default === 1);
        });
        return hasValidVariants || (singleSkuPrice && singleSkuQuantity);
      };

      const validateBatchPrice = () => {
        if (batchData.value.price < 0) batchData.value.price = 0;
        if (batchData.value.originPrice && parseFloat(batchData.value.price) > parseFloat(batchData.value.originPrice)) {
          batchData.value.price = batchData.value.originPrice;
        }
      };
      const validateBatchOriginPrice = () => {
        if (batchData.value.originPrice < 0) batchData.value.originPrice = 0;
        if (batchData.value.price && parseFloat(batchData.value.originPrice) < parseFloat(batchData.value.price)) {
          batchData.value.originPrice = batchData.value.price;
        }
      };
      const validateBatchQuantity = () => {
        if (batchData.value.quantity < 0) batchData.value.quantity = 0;
      };
      const validatePrice = (sku) => {
        let price = parseFloat(sku.price);
        if (isNaN(price) || price < 0) sku.price = '0';
        if (sku.origin_price && price > parseFloat(sku.origin_price)) sku.price = sku.origin_price;
      };
      const validateOriginPrice = (sku) => {
        let originPrice = parseFloat(sku.origin_price);
        if (isNaN(originPrice) || originPrice < 0) sku.origin_price = '0';
        if (sku.price && originPrice < parseFloat(sku.price)) sku.origin_price = sku.price;
      };
      const validateQuantity = (sku) => {
        let quantity = parseInt(sku.quantity);
        if (isNaN(quantity) || quantity < 0) sku.quantity = '0';
      };

      // Batch selection helpers — selectedValueIds is a flat Set<value.id>.
      const selectAllVariantValues = (variantId) => {
        const variant = variants.value.find(v => v.id === variantId);
        if (!variant) return;
        variant.values.forEach(val => {
          if (!batchData.value.selectedValueIds.includes(val.id)) {
            batchData.value.selectedValueIds.push(val.id);
          }
        });
      };
      const clearVariantSelection = (variantId) => {
        const variant = variants.value.find(v => v.id === variantId);
        if (!variant) return;
        const ids = new Set(variant.values.map(v => v.id));
        batchData.value.selectedValueIds = batchData.value.selectedValueIds.filter(id => !ids.has(id));
      };

      const isSkuMatchingSelection = (sku) => {
        const selected = batchData.value.selectedValueIds;
        if (!selected || selected.length === 0) return true;
        return selected.every(id => sku.variant_value_ids.includes(id));
      };

      const batchApplySelected = () => {
        const matching = skus.value.filter(isSkuMatchingSelection);
        if (matching.length === 0) {
          layer.msg('{{ __("panel/product.no_matching_sku") }}', {icon: 2});
          return;
        }
        let applied = 0;
        if (batchData.value.skuPrefix) {
          matching.forEach((sku, i) => {
            const suffix = String(i + 1).padStart(2, '0');
            sku.code = `${batchData.value.skuPrefix}-${suffix}`;
          });
          applied++;
        }
        if (batchData.value.price)     { matching.forEach(s => s.price = batchData.value.price); applied++; }
        if (batchData.value.originPrice){ matching.forEach(s => s.origin_price = batchData.value.originPrice); applied++; }
        if (batchData.value.model)     { matching.forEach(s => s.model = batchData.value.model); applied++; }
        if (batchData.value.quantity)  { matching.forEach(s => s.quantity = batchData.value.quantity); applied++; }
        if (batchData.value.weight)    { matching.forEach(s => s.weight = batchData.value.weight); applied++; }
        if (batchData.value.image)     { matching.forEach(s => s.image = batchData.value.image); applied++; }

        if (applied === 0) {
          layer.msg('{{ __("panel/product.batch_fill_required") }}', {icon: 2});
          return;
        }
        layer.msg('{{ __("panel/product.batch_applied") }}'.replace(':count', matching.length).replace(':fields', applied), {icon: 1});
      };

      const selectBatchImage = () => {
        inno.mediaIframe((file) => {
          if (file.path) batchData.value.image = file.path;
        }, { type: 'image', multiple: false });
      };
      const clearBatchImage = () => { batchData.value.image = ''; };

      // variant/value dialog. IDs are stable so we look up by id rather than
      // passing indices around — robust against reordering.
      const openVariantDialog = (variantId, valueId) => {
        // Close any active inline edit so the modal and the inline input
        // can't both be editing the same value at once.
        cancelInlineEdit();

        dialogVariables.value.variantId = variantId;
        dialogVariables.value.valueId = valueId;

        let name = {};
        let title = '';
        const NEW = '__NEW__';

        if (variantId === NEW) {
          name = localesFill('');
          title = '{{ __('panel/product.add_variant') }}';
        } else {
          const variant = variants.value.find(v => v.id === variantId);
          if (valueId === null) {
            name = variant?.name || localesFill('');
            title = '{{ __('panel/product.edit_variant') }}';
          } else if (valueId === NEW) {
            name = localesFill('');
            title = '{{ __('panel/product.add_variant_value') }}';
          } else {
            const value = variant?.values.find(val => val.id === valueId);
            name = value?.name || localesFill('');
            title = '{{ __('panel/product.edit_variant_value') }}';
          }
        }

        dialogVariables.value.form.name = JSON.parse(JSON.stringify(name));
        dialogVariables.value.title = title;
        dialogVariables.value.show = true;

        nextTick(() => {
          const modal = new bootstrap.Modal(document.getElementById('variantEditModal'));
          modal.show();
        });
      };

      const closeVariantDialog = () => {
        dialogVariables.value.show = false;
        dialogVariables.value.variantId = null;
        dialogVariables.value.valueId = null;
        dialogVariables.value.title = '';
        dialogVariables.value.form.name = {};
        const modal = bootstrap.Modal.getInstance(document.getElementById('variantEditModal'));
        if (modal) modal.hide();
      };

      const saveVariantDialog = () => {
        const name = JSON.parse(JSON.stringify(dialogVariables.value.form.name));
        const { variantId, valueId } = dialogVariables.value;
        const NEW = '__NEW__';

        if (isObjectValuesEmpty(name)) {
          layer.msg('{{ __('panel/common.verify_required') }}', {icon: 2});
          return;
        }

        if (valueId !== null) {
          const variant = variants.value.find(v => v.id === variantId);
          if (!variant) return;
          if (valueId === NEW) {
            variant.values.push({ id: newId('val'), name, image: '', error: false });
          } else {
            const value = variant.values.find(val => val.id === valueId);
            if (value) value.name = name;
          }
        } else {
          if (variantId === NEW) {
            variants.value.push({
              id: newId('var'),
              name,
              values: [{ id: newId('val'), name: localesFill(''), error: false, image: '' }],
              isImage: false,
              error: false,
            });
          } else {
            const variant = variants.value.find(v => v.id === variantId);
            if (variant) variant.name = name;
          }
        }

        closeVariantDialog();
        layer.msg('{{ __('common/base.saved_success') }}', {icon: 1});
      };

      const selectVariantValueImage = (variantId, valueId) => {
        inno.mediaIframe((file) => {
          if (!file.path) return;
          const variant = variants.value.find(v => v.id === variantId);
          const value = variant?.values.find(val => val.id === valueId);
          if (value) value.image = file.path;
        }, { type: 'image', multiple: false });
      };

      // Inline edit handlers — single click on the value name swaps the span
      // for an input preloaded with the default-locale text. Enter/blur
      // commits, Esc aborts. Empty submission is allowed; form validation
      // will catch truly empty values on save.
      const startInlineEdit = (variantId, valueId) => {
        const variant = variants.value.find(v => v.id === variantId);
        const value = variant?.values.find(val => val.id === valueId);
        if (!value) return;
        inlineEdit.value = { variantId, valueId, draft: value.name[defaultLocale] || '' };
        nextTick(() => {
          const input = document.querySelector(`[data-inline-edit="${valueId}"]`);
          input?.focus();
          input?.select();
        });
      };

      const commitInlineEdit = () => {
        const { variantId, valueId, draft } = inlineEdit.value;
        if (!variantId || !valueId) return;
        const variant = variants.value.find(v => v.id === variantId);
        const value = variant?.values.find(val => val.id === valueId);
        if (value) {
          value.name[defaultLocale] = (draft || '').trim();
        }
        inlineEdit.value = { variantId: null, valueId: null, draft: '' };
      };

      const cancelInlineEdit = () => {
        inlineEdit.value = { variantId: null, valueId: null, draft: '' };
      };

      const deleteVariantValue = (variantId, valueId) => {
        if (!confirm('{{ __('panel/common.confirm_delete') }}')) return;
        const variant = variants.value.find(v => v.id === variantId);
        if (!variant) return;
        const idx = variant.values.findIndex(val => val.id === valueId);
        if (idx < 0) return;
        variant.values.splice(idx, 1);
        layer.msg('{{ __('common/base.deleted_success') }}', {icon: 1});
      };

      const openSaveTemplateModal = () => {
        templateDialog.value.saveName = '';
        templateDialog.value.saveShow = true;
        nextTick(() => {
          const modal = new bootstrap.Modal(document.getElementById('saveTemplateModal'));
          modal.show();
        });
      };
      const closeSaveTemplateModal = () => {
        templateDialog.value.saveShow = false;
        const modal = bootstrap.Modal.getInstance(document.getElementById('saveTemplateModal'));
        if (modal) modal.hide();
      };

      const saveTemplate = async () => {
        const name = templateDialog.value.saveName.trim();
        if (!name) {
          layer.msg('{{ __('panel/common.verify_required') }}', {icon: 2});
          return;
        }
        if (variants.value.length === 0) {
          layer.msg('{{ __('panel/product.sku_validation_error') }}', {icon: 2});
          return;
        }
        templateDialog.value.loading = true;
        try {
          const response = await fetch(variantTemplateRoutes.store, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json',
            },
            body: JSON.stringify({
              name: name,
              variables: variants.value,
              sku_matrix: skus.value,
            }),
          });
          const data = await response.json();
          if (!response.ok) throw new Error(data.message || 'Save failed');
          layer.msg(data.message || '{{ __('panel/product.template_saved') }}', {icon: 1});
          closeSaveTemplateModal();
        } catch (error) {
          layer.msg(error.message, {icon: 2});
        } finally {
          templateDialog.value.loading = false;
        }
      };

      const openLoadTemplateModal = async () => {
        templateDialog.value.loading = true;
        templateDialog.value.loadShow = true;
        templateDialog.value.selectedId = null;
        try {
          await fetchTemplates();
          nextTick(() => {
            const modal = new bootstrap.Modal(document.getElementById('loadTemplateModal'));
            modal.show();
          });
        } finally {
          templateDialog.value.loading = false;
        }
      };
      const closeLoadTemplateModal = () => {
        templateDialog.value.loadShow = false;
        const modal = bootstrap.Modal.getInstance(document.getElementById('loadTemplateModal'));
        if (modal) modal.hide();
      };

      const fetchTemplates = async () => {
        try {
          const response = await fetch(variantTemplateRoutes.index, {
            headers: { 'Accept': 'application/json' },
          });
          const data = await response.json();
          if (!response.ok) throw new Error(data.message || 'Load failed');
          templateDialog.value.templates = data;
        } catch (error) {
          layer.msg(error.message, {icon: 2});
        }
      };

      const applyTemplate = async () => {
        const id = templateDialog.value.selectedId;
        if (!id) return;
        if (!confirm('{{ __('panel/product.confirm_load_template') }}')) return;

        templateDialog.value.loading = true;
        try {
          const response = await fetch(variantTemplateRoutes.show.replace('__ID__', id), {
            headers: { 'Accept': 'application/json' },
          });
          const data = await response.json();
          if (!response.ok) throw new Error(data.message || 'Load failed');

          // Template values may lack ids (legacy) — normalize to assign fresh
          // client ids so SKU matching works after load.
          const newVars = normalizeVariants(data.variables || []);
          const newSkusRaw = (data.sku_matrix || []).map((sku, index) => ({
            ...sku,
            code: sku.code ? `${sku.code}-${String(Math.floor(Math.random() * 90000) + 10000)}` : sku.code,
            is_default: index === 0 ? 1 : (sku.is_default ? 1 : 0),
          }));

          variants.value = newVars;
          skus.value = normalizeSkus(newSkusRaw, newVars);
          regenerateSkus();

          closeLoadTemplateModal();
          layer.msg('{{ __('panel/product.template_loaded') }}', {icon: 1});
        } catch (error) {
          layer.msg(error.message, {icon: 2});
        } finally {
          templateDialog.value.loading = false;
        }
      };

      const deleteTemplate = async (id) => {
        if (!confirm('{{ __('panel/product.delete_template_confirm') }}')) return;
        try {
          const response = await fetch(variantTemplateRoutes.destroy.replace('__ID__', id), {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json',
            },
          });
          const data = await response.json();
          if (!response.ok) throw new Error(data.message || 'Delete failed');
          await fetchTemplates();
        } catch (error) {
          layer.msg(error.message, {icon: 2});
        }
      };

      return {
        skus, variants,
        deleteVariant,
        locales, defaultLocale, otherLocales, defaultLocaleName,
        thumbnail, setMasterSku,
        batchData,
        getFirstAvailableLocaleValue,
        validateBatchPrice, validateBatchOriginPrice, validateBatchQuantity,
        validatePrice, validateOriginPrice, validateQuantity,
        dialogVariables, openVariantDialog, closeVariantDialog, saveVariantDialog,
        selectVariantValueImage, deleteVariantValue,
        inlineEdit, startInlineEdit, commitInlineEdit, cancelInlineEdit,
        upVariantImage,
        selectAllVariantValues, clearVariantSelection, batchApplySelected,
        selectBatchImage, clearBatchImage,
        translateVariantName, fillEmptyLocales,
        templateDialog, openSaveTemplateModal, closeSaveTemplateModal,
        saveTemplate, openLoadTemplateModal, closeLoadTemplateModal,
        applyTemplate, deleteTemplate,
      };
    }
  }).mount('#variants-box');

  function isObjectValuesEmpty(obj) {
    for (let key in obj) {
      if (obj[key] != '') return false;
    }
    return true;
  }
</script>
@endpush
