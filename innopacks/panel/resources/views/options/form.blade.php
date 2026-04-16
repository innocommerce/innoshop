@extends('panel::layouts.app')

@section('title', panel_trans('options.option_management'))

@push('header')
  <script src="{{ asset('vendor/vuedraggable/sortable.min.js') }}"></script>
  <script src="{{ asset('vendor/vuedraggable/vuedraggable.umd.min.js') }}"></script>
@endpush

<x-panel::form.right-btns formid="option-form" />

@section('content')
  <form class="needs-validation" novalidate id="option-form"
        action="{{ $option->id ? panel_route('options.update', [$option->id]) : panel_route('options.store') }}"
        method="POST">
    @csrf
    @method($option->id ? 'PUT' : 'POST')

    <div id="option-form-app">
      <div class="row">
        {{-- Left column: Basic info + multilingual --}}
        <div class="col-12 col-md-6">
          <div class="card h-min-600">
            <div class="card-header">
              <h5 class="card-title mb-0">{{ panel_trans('options.basic_info') }}</h5>
            </div>
            <div class="card-body">

              {{-- Option name (multi-language) --}}
              <div class="mb-3">
                <label class="form-label">{{ panel_trans('options.option_group_name') }}</label>
                <x-common-form-locale-input
                  name="name"
                  :translations="json_field_data($option, 'name')"
                  type="input"
                  :required="true"
                  :label="panel_trans('options.option_group_name')"
                  :placeholder="panel_trans('options.option_group_name')"
                />
              </div>

              {{-- Option description (multi-language) --}}
              <div class="mb-3">
                <label class="form-label">{{ panel_trans('options.option_group_description') }}</label>
                <x-common-form-locale-input
                  name="description"
                  :translations="json_field_data($option, 'description')"
                  type="textarea"
                  :label="panel_trans('options.option_group_description')"
                  :placeholder="panel_trans('options.option_group_description_placeholder')"
                  :rows="3"
                />
              </div>

              {{-- Option type --}}
              <div class="mb-3">
                <label class="form-label">{{ panel_trans('options.option_type') }} <span class="text-danger">*</span></label>
                <select class="form-select" name="type" required>
                  <option value="select" {{ old('type', $option->type ?? 'select') === 'select' ? 'selected' : '' }}>{{ panel_trans('options.dropdown_select') }}</option>
                  <option value="radio" {{ old('type', $option->type ?? '') === 'radio' ? 'selected' : '' }}>{{ panel_trans('options.radio_button') }}</option>
                  <option value="checkbox" {{ old('type', $option->type ?? '') === 'checkbox' ? 'selected' : '' }}>{{ panel_trans('options.checkbox') }}</option>
                </select>
              </div>

              {{-- Sort, Required & Active on same row --}}
              <div class="row mb-3">
                <div class="col-4">
                  <label class="form-label">{{ panel_trans('options.sort') }}</label>
                  <input type="number" class="form-control" name="position"
                         value="{{ old('position', $option->position ?? 0) }}"
                         min="0">
                </div>
                <div class="col-4">
                  <label class="form-label">{{ panel_trans('options.is_required') }}</label>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="required" value="1"
                           {{ old('required', $option->required ?? false) ? 'checked' : '' }}>
                  </div>
                </div>
                <div class="col-4">
                  <label class="form-label">{{ panel_trans('options.is_enabled') }}</label>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="active" value="1"
                           {{ old('active', ($option->id ? $option->active : true)) ? 'checked' : '' }}>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Right column: Option values --}}
        <div class="col-12 col-md-6">
          <div class="card h-min-600">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="card-title mb-0">{{ panel_trans('options.option_value_management') }}</h5>
              <button type="button" class="btn btn-sm btn-outline-primary" @click="openValueDialog(-1)">
                <i class="bi bi-plus-lg me-1"></i>{{ __('common/base.add') }}
              </button>
            </div>
            <div class="card-body">
              <input type="hidden" name="values" :value="isNewOption ? JSON.stringify(getValuesForSubmit()) : ''">

              <div v-if="localValues.length">
                <draggable v-model="localValues" handle=".drag-handle" :animation="300" item-key="index">
                  <template #item="{ element: value, index }">
                    <div class="d-flex align-items-center mb-2 p-2 border rounded">
                      <div class="drag-handle me-2 text-muted cursor-pointer"><i class="bi bi-grip-vertical"></i></div>
                      <div class="me-2">
                        <img v-if="value.imageThumb" :src="value.imageThumb" class="rounded" style="width:32px;height:32px;object-fit:cover;">
                        <i v-else class="bi bi-image text-muted" style="font-size:1.2rem;"></i>
                      </div>
                      <span class="flex-grow-1">@{{ value.name[defaultLocale] || getFirstAvailableLocaleValue(value.name) || '...' }}</span>
                      <button type="button" class="btn btn-sm btn-outline-primary me-1" @click="openValueDialog(index)">
                        <i class="bi bi-pencil"></i>
                      </button>
                      <button type="button" class="btn btn-sm btn-outline-danger" @click="deleteValue(index)">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </template>
                </draggable>
              </div>
              <div v-else class="text-muted text-center py-4">
                <i class="bi bi-list-ul fs-1 d-block mb-2"></i>
                <span>{{ __('panel/attribute.no_values') }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Value locale edit dialog --}}
      <div class="modal fade" id="valueEditModal" tabindex="-1" aria-hidden="true" v-if="valueDialog.show">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">@{{ valueDialog.title }}</h5>
              <button type="button" class="btn-close" @click="closeValueDialog"></button>
            </div>
            <div class="modal-body">
              {{-- Image upload --}}
              <div class="mb-3">
                <label class="form-label">{{ panel_trans('options.option_value_image') }}</label>
                <div class="bg-light wh-80 rounded border d-flex justify-content-center align-items-center me-2 mb-2 position-relative cursor-pointer overflow-hidden"
                     @click="pickValueImage">
                  <div v-if="valueDialog.form.image" class="position-absolute top-0 start-0 w-100 bg-primary bg-opacity-75 d-flex" style="height:100%;">
                    <div class="w-100 text-center" @click.stop="previewValueImage"><i class="bi bi-eye text-white"></i></div>
                    <div class="w-100 text-center" @click.stop="removeValueImage"><i class="bi bi-trash text-white"></i></div>
                  </div>
                  <div class="rounded h-100 w-100 d-flex justify-content-center align-items-center">
                    <i v-if="!valueDialog.form.image" class="bi bi-plus fs-1 text-secondary opacity-75"></i>
                    <img v-else :src="valueDialog.form.imageThumb" class="img-fluid">
                  </div>
                </div>
                <div class="form-text">{{ panel_trans('options.optional_option_value_image') }}</div>
              </div>

              {{-- Primary locale row --}}
              <div class="locale-modal-row mb-3 p-2 rounded border border-primary">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <div class="d-flex align-items-center wh-20">
                    <img :src="'/images/flag/'+ defaultLocale +'.png'" class="img-fluid" :alt="defaultLocaleName">
                  </div>
                  <span class="fw-medium">@{{ defaultLocaleName }}</span>
                  <span class="badge bg-primary">{{ __('panel/common.panel_locale') }}</span>
                </div>
                <input type="text" class="form-control"
                       v-model="valueDialog.form.name[defaultLocale]"
                       :placeholder="'{{ __('common/base.name') }}'">
              </div>

              {{-- Other locale rows --}}
              <div v-for="locale in otherLocales" :key="locale.code"
                   class="locale-modal-row mb-3 p-2 rounded border">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <div class="d-flex align-items-center wh-20">
                    <img :src="'/images/flag/'+ locale.code +'.png'" class="img-fluid" :alt="locale.name">
                  </div>
                  <span class="fw-medium">@{{ locale.name }}</span>
                  @if(has_translator())
                    <button type="button" class="btn btn-sm btn-outline-primary ms-auto"
                            @click.prevent="translateValueName(locale.code, $event)"
                            :title="defaultLocaleName + ' \u2192 ' + locale.name">
                      <i class="bi bi-translate"></i>
                    </button>
                  @endif
                </div>
                <input type="text" class="form-control"
                       v-model="valueDialog.form.name[locale.code]"
                       :placeholder="'{{ __('common/base.name') }}'">
              </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
              @php($hasTranslator = has_translator())
              <button type="button" class="btn btn-outline-secondary" @click="fillEmptyValueLocales">
                @if($hasTranslator)
                  <i class="bi bi-translate me-1"></i>{{ __('panel/common.translate_empty') }}
                @else
                  <i class="bi bi-arrow-right-circle me-1"></i>{{ __('panel/common.copy_empty') }}
                @endif
              </button>
              <div>
                <button type="button" class="btn btn-secondary" @click="closeValueDialog">{{ __('common/base.cancel') }}</button>
                <button type="button" class="btn btn-primary ms-2" @click="saveValueDialog">{{ __('panel/common.confirm') }}</button>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <button type="submit" class="d-none"></button>
  </form>
@endsection

@push('footer')
<script>
  const { createApp, ref, computed, nextTick } = Vue;
  const draggable = window.vuedraggable;

  const $optLocales = @json(locales());
  const optLocalesFill = (text) => {
    let obj = {};
    $optLocales.map(e => { obj[e.code] = text });
    return obj;
  };

  createApp({
    components: { draggable },

    setup() {
      const optionId = {{ $option->id ?? 0 }};
      const isNewOption = !optionId;

      const locales = $optLocales;
      const defaultLocale = @json(panel_locale_code());
      const defaultLocaleName = locales.find(l => l.code === defaultLocale)?.name || defaultLocale;
      const otherLocales = computed(() => locales.filter(l => l.code !== defaultLocale));

      // LocaleModalHelper for value dialog
      const valueLocaleHelper = new LocaleModalHelper({
        getLocaleValue: (code) => valueDialog.value.form.name[code] || '',
        setLocaleValue: (code, val) => { valueDialog.value.form.name[code] = val; },
      });

      // Local values array
      const localValues = ref(@json($option_values_json ?? []));

      // --- Value Dialog ---
      const valueDialog = ref({
        show: false,
        title: '',
        valueIndex: null,
        form: { name: {}, image: '', imageThumb: '' }
      });

      const openValueDialog = (index) => {
        let name = {};
        let image = '';
        let imageThumb = '';
        let title = '';

        if (index === -1) {
          name = optLocalesFill('');
          title = '{{ panel_trans("options.add_option_value") }}';
        } else {
          const v = localValues.value[index];
          name = JSON.parse(JSON.stringify(v.name));
          image = v.image || '';
          imageThumb = v.imageThumb || (v.image ? (v.image.indexOf('http') === 0 ? v.image : '{{ asset("") }}' + v.image) : '');
          title = '{{ panel_trans("options.edit_option_value") }}';
        }

        valueDialog.value = {
          show: true,
          title,
          valueIndex: index,
          form: { name, image, imageThumb },
        };

        nextTick(() => {
          const modal = new bootstrap.Modal(document.getElementById('valueEditModal'));
          modal.show();
        });
      };

      const closeValueDialog = () => {
        valueDialog.value.show = false;
        const modal = bootstrap.Modal.getInstance(document.getElementById('valueEditModal'));
        if (modal) modal.hide();
      };

      const saveValueDialog = () => {
        const name = JSON.parse(JSON.stringify(valueDialog.value.form.name));
        const image = valueDialog.value.form.image;
        const imageThumb = valueDialog.value.form.imageThumb;
        const index = valueDialog.value.valueIndex;

        // Validate primary locale
        if (!name[defaultLocale] || !name[defaultLocale].trim()) {
          layer.msg('{{ __("panel/common.verify_required") }}', {icon: 2});
          return;
        }

        if (index === -1) {
          // Add new value
          if (isNewOption) {
            localValues.value.push({ id: null, name, image, imageThumb });
            closeValueDialog();
          } else {
            // AJAX create
            closeValueDialog();
            axios.post(urls.panel_base + '/option_values', {
              option_id: optionId,
              name,
              image,
              position: localValues.value.length,
              active: 1,
            }, { headers: { 'X-Skip-Loading': true } }).then(res => {
              localValues.value.push({ id: res.data?.id || null, name, image, imageThumb });
              layer.msg(res.message, {icon: 1});
            }).catch(err => {
              layer.msg(err.response?.data?.message || err.message, {icon: 2});
            });
          }
        } else {
          // Update existing value
          if (isNewOption || !localValues.value[index].id) {
            localValues.value[index].name = name;
            localValues.value[index].image = image;
            localValues.value[index].imageThumb = imageThumb;
            closeValueDialog();
          } else {
            // AJAX update
            closeValueDialog();
            axios.put(urls.panel_base + '/option_values/' + localValues.value[index].id, {
              option_id: optionId,
              name,
              image,
            }, { headers: { 'X-Skip-Loading': true } }).then(res => {
              localValues.value[index].name = name;
              localValues.value[index].image = image;
              localValues.value[index].imageThumb = imageThumb;
              layer.msg(res.message, {icon: 1});
            }).catch(err => {
              layer.msg(err.response?.data?.message || err.message, {icon: 2});
            });
          }
        }
      };

      const deleteValue = (index) => {
        if (!confirm('{{ __("panel/common.confirm_delete") }}')) return;

        const value = localValues.value[index];
        if (!isNewOption && value.id) {
          // AJAX delete
          axios.delete(urls.panel_base + '/option_values/' + value.id, {
            headers: { 'X-Skip-Loading': true }
          }).then(res => {
            localValues.value.splice(index, 1);
            layer.msg(res.message, {icon: 1});
          }).catch(err => {
            layer.msg(err.response?.data?.message || err.message, {icon: 2});
          });
        } else {
          localValues.value.splice(index, 1);
        }
      };

      // Get values for form submit (new options only)
      const getValuesForSubmit = () => {
        return localValues.value.map(v => ({
          name: v.name,
          image: v.image || '',
        }));
      };

      // Translate value name
      const translateValueName = (targetLocale, event) => {
        valueLocaleHelper.translate(targetLocale, event?.currentTarget);
      };

      // Smart fill empty locales
      const fillEmptyValueLocales = () => {
        valueLocaleHelper.fillEmpty();
      };

      // --- Image handling ---
      const pickValueImage = () => {
        window.inno.fileManagerIframe((file) => {
          valueDialog.value.form.image = file.path;
          valueDialog.value.form.imageThumb = file.url || file.origin_url;
        }, { multiple: false, type: 'image' });
      };

      const removeValueImage = () => {
        valueDialog.value.form.image = '';
        valueDialog.value.form.imageThumb = '';
      };

      const previewValueImage = () => {
        const src = valueDialog.value.form.imageThumb;
        if (!src) return;
        let $preview = document.getElementById('modal-show-img');
        if (!$preview) {
          $preview = document.createElement('div');
          $preview.id = 'modal-show-img';
          $preview.className = 'modal fade';
          $preview.innerHTML = `
            <div class="modal-dialog modal-dialog-centered modal-lg">
              <div class="modal-content">
                <div class="modal-body text-center p-4" style="min-height:200px;display:flex;align-items:center;justify-content:center;"></div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ panel_trans('options.close') }}</button>
                </div>
              </div>
            </div>`;
          document.body.appendChild($preview);
        }
        $preview.querySelector('.modal-body').innerHTML = '<img src="' + src + '" class="img-fluid" style="max-width:100%;max-height:70vh;border-radius:4px;">';
        new bootstrap.Modal($preview).show();
      };

      // --- Helpers ---
      const getFirstAvailableLocaleValue = (localeObject) => {
        if (!localeObject) return '';
        for (const locale of locales) {
          if (localeObject[locale.code] && localeObject[locale.code].trim() !== '') {
            return localeObject[locale.code];
          }
        }
        return '';
      };

      return {
        isNewOption,
        localValues,
        locales,
        defaultLocale,
        defaultLocaleName,
        otherLocales,

        // Value dialog
        valueDialog,
        openValueDialog,
        closeValueDialog,
        saveValueDialog,
        deleteValue,
        getValuesForSubmit,
        translateValueName,
        fillEmptyValueLocales,

        // Image
        pickValueImage,
        removeValueImage,
        previewValueImage,

        // Helpers
        getFirstAvailableLocaleValue,
      };
    }
  }).mount('#option-form-app');
</script>
@endpush
