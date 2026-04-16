@extends('panel::layouts.app')

@section('title', __('panel/menu.attributes'))

@push('header')
  <script src="{{ asset('vendor/vuedraggable/sortable.min.js') }}"></script>
  <script src="{{ asset('vendor/vuedraggable/vuedraggable.umd.min.js') }}"></script>
@endpush

<x-panel::form.right-btns formid="app-form" />

@section('content')
  <form class="needs-validation" novalidate id="app-form"
        action="{{ $attribute->id ? panel_route('attributes.update', [$attribute->id]) : panel_route('attributes.store') }}"
        method="POST">
    @csrf
    @method($attribute->id ? 'PUT' : 'POST')

    <div id="attribute-form-app">
      <div class="row">
        {{-- Left column: Attribute details --}}
        <div class="col-12 col-md-6">
          <div class="card h-min-600">
            <div class="card-header">
              <h5 class="card-title mb-0">{{ __('panel/menu.attributes') }}</h5>
            </div>
            <div class="card-body">

              {{-- Attribute name (multi-language) --}}
              <div class="mb-3">
                <label class="form-label">{{ __('common/base.name') }}</label>
                <x-common-form-locale-input
                  name="name"
                  :translations="locale_field_data($attribute, 'name')"
                  type="input"
                  :required="true"
                  :label="__('common/base.name')"
                  :placeholder="__('common/base.name')"
                />
              </div>

              {{-- Attribute group dropdown + quick create --}}
              <div class="mb-3">
                <label class="form-label">{{ __('panel/menu.attribute_groups') }}</label>
                <div class="d-flex align-items-center gap-2">
                  <select class="form-select" name="attribute_group_id" v-model="selectedGroupId">
                    <option value="">{{ __('common/base.please_choose') }}</option>
                    <option v-for="g in groups" :key="g.id" :value="g.id">@{{ g.name }}</option>
                  </select>
                  <button type="button" class="btn btn-outline-primary btn-sm flex-shrink-0" @click="openGroupDialog" title="{{ __('common/base.create') }}">
                    <i class="bi bi-plus-lg"></i>
                  </button>
                </div>
              </div>

              {{-- Position --}}
              <div class="mb-3">
                <label class="form-label">{{ __('common/base.position') }}</label>
                <input type="number" class="form-control" name="position"
                       value="{{ old('position', $attribute->position ?? 0) }}"
                       placeholder="{{ __('common/base.position') }}">
              </div>
            </div>
          </div>
        </div>

        {{-- Right column: Attribute values --}}
        <div class="col-12 col-md-6">
          <div class="card h-min-600">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="card-title mb-0">{{ __('panel/attribute.attribute_value') }}</h5>
              <button type="button" class="btn btn-sm btn-outline-primary" @click="openValueDialog(-1)">
                <i class="bi bi-plus-lg me-1"></i>{{ __('common/base.add') }}
              </button>
            </div>
            <div class="card-body">
              <input type="hidden" name="values" :value="isNewAttribute ? JSON.stringify(getValuesForSubmit()) : ''">

              <div v-if="localValues.length">
                <draggable v-model="localValues" handle=".drag-handle" :animation="300" item-key="index">
                  <template #item="{ element: value, index }">
                    <div class="d-flex align-items-center mb-2 p-2 border rounded">
                      <div class="drag-handle me-2 text-muted cursor-pointer"><i class="bi bi-grip-vertical"></i></div>
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
                <i class="bi bi-tags fs-1 d-block mb-2"></i>
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

      {{-- Quick create group dialog --}}
      <div class="modal fade" id="groupCreateModal" tabindex="-1" aria-hidden="true" v-if="groupDialog.show">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">{{ __('panel/menu.attribute_groups') }}</h5>
              <button type="button" class="btn-close" @click="closeGroupDialog"></button>
            </div>
            <div class="modal-body">
              <div v-for="locale in locales" :key="locale.code" class="mb-2">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <div class="d-flex align-items-center wh-20">
                    <img :src="'/images/flag/'+ locale.code +'.png'" class="img-fluid" :alt="locale.name">
                  </div>
                  <span class="fw-medium">@{{ locale.name }}</span>
                </div>
                <input type="text" class="form-control"
                       v-model="groupDialog.form.translations[locale.code].name"
                       placeholder="{{ __('common/base.name') }}">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeGroupDialog">{{ __('common/base.cancel') }}</button>
              <button type="button" class="btn btn-primary" @click="saveGroupDialog">{{ __('panel/common.confirm') }}</button>
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
  const { createApp, ref, reactive, computed, nextTick, getCurrentInstance } = Vue;
  const draggable = window.vuedraggable;

  const $attrLocales = @json(locales());
  const attrLocalesFill = (text) => {
    let obj = {};
    $attrLocales.map(e => { obj[e.code] = text });
    return obj;
  };

  createApp({
    components: { draggable },

    setup() {
      const attributeId = {{ $attribute->id ?? 0 }};
      const isNewAttribute = !attributeId;

      const locales = $attrLocales;
      const defaultLocale = @json(panel_locale_code());
      const defaultLocaleName = locales.find(l => l.code === defaultLocale)?.name || defaultLocale;
      const otherLocales = computed(() => locales.filter(l => l.code !== defaultLocale));
      const hasTranslator = @json(has_translator());

      // LocaleModalHelper for value dialog
      const valueLocaleHelper = new LocaleModalHelper({
        getLocaleValue: (code) => valueDialog.value.form.name[code] || '',
        setLocaleValue: (code, val) => { valueDialog.value.form.name[code] = val; },
      });

      // Group dropdown
      const groups = ref(@json($attribute_groups));
      const selectedGroupId = ref('{{ old("attribute_group_id", $attribute->attribute_group_id ?? "") }}');

      // Local values array
      const localValues = ref(@json($attribute_values_json ?? []));

      // --- Value Dialog ---
      const valueDialog = ref({
        show: false,
        title: '',
        valueIndex: null,
        form: { name: {} }
      });

      const openValueDialog = (index) => {
        let name = {};
        let title = '';

        if (index === -1) {
          name = attrLocalesFill('');
          title = '{{ __("panel/attribute.add_value") }}';
        } else {
          name = JSON.parse(JSON.stringify(localValues.value[index].name));
          title = '{{ __("panel/attribute.edit_value") }}';
        }

        valueDialog.value = {
          show: true,
          title,
          valueIndex: index,
          form: { name }
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
        const index = valueDialog.value.valueIndex;

        // Validate primary locale
        if (!name[defaultLocale] || !name[defaultLocale].trim()) {
          layer.msg('{{ __("panel/common.verify_required") }}', {icon: 2});
          return;
        }

        if (index === -1) {
          // Add new value
          if (isNewAttribute) {
            localValues.value.push({ id: null, name });
            closeValueDialog();
          } else {
            // AJAX create
            closeValueDialog();
            axios.post(urls.panel_base + '/attribute_values', {
              attribute_id: attributeId,
              values: name
            }, { headers: { 'X-Skip-Loading': true } }).then(res => {
              localValues.value.push({ id: res.data?.id || null, name });
              layer.msg(res.message, {icon: 1});
            }).catch(err => {
              layer.msg(err.response?.data?.message || err.message, {icon: 2});
            });
          }
        } else {
          // Update existing value
          if (isNewAttribute || !localValues.value[index].id) {
            localValues.value[index].name = name;
            closeValueDialog();
          } else {
            // AJAX update
            closeValueDialog();
            axios.put(urls.panel_base + '/attribute_values/' + localValues.value[index].id, {
              values: name
            }, { headers: { 'X-Skip-Loading': true } }).then(res => {
              localValues.value[index].name = name;
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
        if (!isNewAttribute && value.id) {
          // AJAX delete
          axios.delete(urls.panel_base + '/attribute_values/' + value.id, {
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

      // Get values for form submit (new attributes only)
      const getValuesForSubmit = () => {
        return localValues.value.map(v => v.name);
      };

      // Translate value name - delegates to LocaleModalHelper
      const translateValueName = (targetLocale, event) => {
        valueLocaleHelper.translate(targetLocale, event?.currentTarget);
      };

      // Smart fill empty locales - delegates to LocaleModalHelper
      const fillEmptyValueLocales = () => {
        valueLocaleHelper.fillEmpty();
      };

      // --- Group Dialog ---
      const groupDialog = reactive({
        show: false,
        form: {
          translations: {},
          position: 0
        }
      });

      // Initialize group form translations
      locales.forEach(locale => {
        groupDialog.form.translations[locale.code] = { locale: locale.code, name: '' };
      });

      const openGroupDialog = () => {
        // Reset form
        locales.forEach(locale => {
          groupDialog.form.translations[locale.code].name = '';
        });
        groupDialog.form.position = 0;
        groupDialog.show = true;

        nextTick(() => {
          const modal = new bootstrap.Modal(document.getElementById('groupCreateModal'));
          modal.show();
        });
      };

      const closeGroupDialog = () => {
        groupDialog.show = false;
        const modal = bootstrap.Modal.getInstance(document.getElementById('groupCreateModal'));
        if (modal) modal.hide();
      };

      const saveGroupDialog = () => {
        // Build translations array
        const translations = locales.map(locale => ({
          locale: locale.code,
          name: groupDialog.form.translations[locale.code].name
        }));

        // Check if default locale has a name
        const defaultName = groupDialog.form.translations[defaultLocale]?.name || '';
        if (!defaultName.trim()) {
          layer.msg('{{ __("panel/common.verify_required") }}', {icon: 2});
          return;
        }

        axios.post(urls.panel_base + '/attribute_groups', {
          translations,
          position: groupDialog.form.position
        }, { headers: { 'X-Skip-Loading': true } }).then(res => {
          // Add new group to dropdown
          const newGroup = res.data;
          if (newGroup) {
            groups.value.push({
              id: newGroup.id,
              name: newGroup.translation?.name || defaultName
            });
            selectedGroupId.value = String(newGroup.id);
          }
          closeGroupDialog();
          layer.msg(res.message || '{{ __("common/base.saved_success") }}', {icon: 1});
        }).catch(err => {
          layer.msg(err.response?.data?.message || err.message, {icon: 2});
        });
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
        isNewAttribute,
        groups,
        selectedGroupId,
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

        // Group dialog
        groupDialog,
        openGroupDialog,
        closeGroupDialog,
        saveGroupDialog,

        // Helpers
        getFirstAvailableLocaleValue,
      };
    }
  }).mount('#attribute-form-app');
</script>
@endpush
