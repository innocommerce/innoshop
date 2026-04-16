@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.themes_settings'))

<x-panel::form.right-btns/>

@push('header')
  <script src="{{ asset('vendor/vuedraggable/sortable.min.js') }}"></script>
@endpush

@section('content')
  @php
    $slideshowLinkTypeOptions = [
      ['value' => 'custom', 'label' => __('panel/setting.link_type_custom')],
      ['value' => 'product', 'label' => __('panel/setting.link_type_product')],
      ['value' => 'category', 'label' => __('panel/setting.link_type_category')],
      ['value' => 'brand', 'label' => __('panel/setting.link_type_brand')],
      ['value' => 'page', 'label' => __('panel/setting.link_type_page')],
      ['value' => 'article', 'label' => __('panel/setting.link_type_article')],
      ['value' => 'catalog', 'label' => __('panel/setting.link_type_catalog')],
    ];
  @endphp
  <form class="needs-validation" novalidate action="{{ panel_route('themes_settings.update') }}" method="POST"
        id="app-form">
    @csrf
    @method('put')
    <div class="row">
      <div class="col-3">
        <div class="card h-min-600" id="setting-menu">
          <div class="card-header">{{ __('panel/menu.themes_settings') }}</div>
          <div class="card-body">
            <ul class="nav flex-column settings-nav">
              <a class="nav-link active" href="#" data-bs-toggle="tab"
                 data-bs-target="#tab-setting-header-menu">{{ __('panel/setting.header_menu') }}</a>
              <a class="nav-link" href="#" data-bs-toggle="tab"
                 data-bs-target="#tab-setting-footer-menu">{{ __('panel/setting.footer_menu') }}</a>
              <a class="nav-link" href="#" data-bs-toggle="tab"
                 data-bs-target="#tab-setting-slideshow">{{ __('panel/setting.slideshow') }}</a>
              <a class="nav-link" href="#" data-bs-toggle="tab"
                 data-bs-target="#tab-setting-hot-products">{{ __('panel/setting.hot_products') }}</a>
              <a class="nav-link" href="#" data-bs-toggle="tab"
                 data-bs-target="#tab-setting-home-categories">{{ __('panel/setting.home_categories') }}</a>
              @hookinsert('panel.themes.settings.hot_products.tab')
            </ul>
          </div>
        </div>
      </div>
      <div class="col-9">
        <div class="card h-min-600">
          <div class="card-header setting-header">{{ __('panel/setting.header_menu') }}</div>
          <div class="card-body">
            <div class="tab-content">
              <div class="tab-pane fade show active" id="tab-setting-header-menu">
                <div class="row">
                  <div class="col-3">
                    <div class="card">
                      <div class="card-header">{{ __('panel/menu.categories') }}</div>
                      <div class="card-body hp-400 overflow-y-auto">
                        @foreach ($categories as $item)
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="menu_header_categories[]"
                                   value="{{ $item['id'] }}"
                                   id="header-category-{{ $item['id'] }}" {{ in_array($item['id'], old('menu_header_categories', system_setting('menu_header_categories', []) ?: [])) ? 'checked' : '' }}>
                            <label class="form-check ps-0"
                                   for="header-category-{{ $item['id'] }}">{{ $item['name'] }}</label>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="card">
                      <div class="card-header">{{ __('panel/setting.catalogs') }}</div>
                      <div class="card-body hp-400 overflow-y-auto">
                        @foreach ($catalogs as $item)
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="menu_header_catalogs[]"
                                   value="{{ $item->id }}"
                                   id="header-catalog-{{ $item->id }}" {{ in_array($item->id, old('menu_header_catalogs', system_setting('menu_header_catalogs', []) ?: [])) ? 'checked' : '' }}>
                            <label class="form-check ps-0"
                                   for="header-catalog-{{ $item->id }}">{{ $item->fallbackName('title') }}</label>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="card">
                      <div class="card-header">{{ __('panel/setting.page') }}</div>
                      <div class="card-body hp-400 overflow-y-auto">
                        @foreach ($pages as $item)
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="menu_header_pages[]"
                                   value="{{ $item->id }}"
                                   id="header-page-{{ $item->id }}" {{ in_array($item->id, old('menu_header_pages', system_setting('menu_header_pages', []) ?: [])) ? 'checked' : '' }}>
                            <label class="form-check ps-0"
                                   for="header-page-{{ $item->id }}">{{ $item->translation->title }}</label>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="card">
                      <div class="card-header">{{ __('panel/setting.specials') }}</div>
                      <div class="card-body hp-400 overflow-y-auto">
                        @foreach ($specials as $item)
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="menu_header_specials[]"
                                   value="{{ $item['type'] }}"
                                   id="header-page-{{ $item['type'] }}" {{ in_array($item['type'], old('menu_header_specials', system_setting('menu_header_specials', []) ?: [])) ? 'checked' : '' }}>
                            <label class="form-check ps-0"
                                   for="header-page-{{ $item['type'] }}">{{ $item['title'] }}</label>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="tab-pane fade" id="tab-setting-footer-menu">
                <div class="row">
                  <div class="col-3">
                    <div class="card">
                      <div class="card-header">{{ __('panel/menu.categories') }}</div>
                      <div class="card-body hp-400 overflow-y-auto">
                        @foreach ($categories as $item)
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="menu_footer_categories[]"
                                   value="{{ $item['id'] }}"
                                   id="footer-category-{{ $item['id'] }}" {{ in_array($item['id'], old('menu_footer_categories', system_setting('menu_footer_categories', []) ?: [])) ? 'checked' : '' }}>
                            <label class="form-check ps-0"
                                   for="footer-category-{{ $item['id'] }}">{{ $item['name'] }}</label>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="card">
                      <div class="card-header">{{ __('panel/setting.catalogs') }}</div>
                      <div class="card-body hp-400 overflow-y-auto">
                        @foreach ($catalogs as $item)
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="menu_footer_catalogs[]"
                                   value="{{ $item->id }}"
                                   id="footer-catalog-{{ $item->id }}" {{ in_array($item->id, old('menu_footer_catalogs', system_setting('menu_footer_catalogs', []) ?: [])) ? 'checked' : '' }}>
                            <label class="form-check ps-0"
                                   for="footer-catalog-{{ $item->id }}">{{ $item->translation->title }}</label>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="card">
                      <div class="card-header">{{ __('panel/setting.page') }}</div>
                      <div class="card-body hp-400 overflow-y-auto">
                        @foreach ($pages as $item)
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="menu_footer_pages[]"
                                   value="{{ $item->id }}"
                                   id="footer-page-{{ $item->id }}" {{ in_array($item->id, old('menu_footer_pages', system_setting('menu_footer_pages', []) ?: [])) ? 'checked' : '' }}>
                            <label class="form-check ps-0"
                                   for="footer-page-{{ $item->id }}">{{ $item->translation->title }}</label>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="card">
                      <div class="card-header">{{ __('panel/setting.specials') }}</div>
                      <div class="card-body hp-400 overflow-y-auto">
                        @foreach ($specials as $item)
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="menu_footer_specials[]"
                                   value="{{ $item['type'] }}"
                                   id="footer-page-{{ $item['type'] }}" {{ in_array($item['type'], old('menu_footer_specials', system_setting('menu_footer_specials', []) ?: [])) ? 'checked' : '' }}>
                            <label class="form-check ps-0"
                                   for="footer-page-{{ $item['type'] }}">{{ $item['title'] }}</label>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="tab-pane fade" id="tab-setting-slideshow">
                <table class="table table-bordered align-middle">
                  <thead>
                  <th>{{ __('common/base.image') }}</th>
                  <th>{{ __('panel/common.link') }}</th>
                  <th class="text-end" width="100"></th>
                  </thead>
                  <tbody>
                  @foreach (old('slideshow', system_setting('slideshow', [])) as $slide_index => $slide)
                    @php
                      $slideLinkStored = old('slideshow.'.$slide_index.'.link', $slide['link'] ?? '');
                      $slideLinkForm = panel_link_parse($slideLinkStored);
                    @endphp
                    <tr>
                      <td>
                        <div class="accordion accordion-sm" id="accordion-slideshow-{{ $slide_index }}">
                          @foreach (locales() as $locale)
                            <div class="accordion-item">
                              <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#data-locale-{{ $slide_index }}-{{ $locale->code }}"
                                        aria-expanded="false"
                                        aria-controls="data-locale-{{ $slide_index }}-{{ $locale->code }}">
                                  <div class="wh-20 me-2"><img src="{{ image_origin($locale->image) }}"
                                                               class="img-fluid"></div>
                                  {{ $locale->name }}
                                </button>
                              </h2>
                              <div id="data-locale-{{ $slide_index }}-{{ $locale->code }}"
                                   class="accordion-collapse collapse"
                                   data-bs-parent="#accordion-slideshow-{{ $slide_index }}">
                                <div class="accordion-body">
                                  <x-common-form-image title=""
                                                       name="slideshow[{{ $slide_index }}][image][{{ $locale->code }}]"
                                                       value="{{ $slide['image'][$locale->code] ?? '' }}"/>
                                  <p class="text-muted small mb-2 mt-2">{{ __('panel/setting.slideshow_slide_text_hint') }}</p>
                                  <div class="mb-2">
                                    <label class="form-label small mb-0">{{ __('panel/setting.slideshow_slide_title') }}</label>
                                    <input type="text" class="form-control form-control-sm"
                                           name="slideshow[{{ $slide_index }}][title][{{ $locale->code }}]"
                                           value="{{ old('slideshow.'.$slide_index.'.title.'.$locale->code, $slide['title'][$locale->code] ?? '') }}">
                                  </div>
                                  <div class="mb-0">
                                    <label class="form-label small mb-0">{{ __('panel/setting.slideshow_slide_subtitle') }}</label>
                                    <textarea class="form-control form-control-sm" rows="2"
                                              name="slideshow[{{ $slide_index }}][subtitle][{{ $locale->code }}]">{{ old('slideshow.'.$slide_index.'.subtitle.'.$locale->code, $slide['subtitle'][$locale->code] ?? '') }}</textarea>
                                  </div>
                                </div>
                              </div>
                            </div>
                          @endforeach
                        </div>
                      </td>
                      <td class="align-top" style="min-width: 280px;">
                        <input type="hidden" name="slideshow[{{ $slide_index }}][link]"
                               id="panel-link-input-{{ $slide_index }}"
                               value='@json($slideLinkForm)'>
                        <div id="panel-link-vue-{{ $slide_index }}" class="panel-inno-link-mount"></div>
                      </td>
                      <td class="text-end">
                        <button type="button" class="btn btn-danger" onclick="this.closest('tr').remove()">删除</button>
                      </td>
                    </tr>
                  @endforeach
                  </tbody>
                  <tfoot>
                  <tr>
                    <td colspan="3" class="text-end">
                      <button type="button" class="btn btn-primary" onclick="addSlide(this)">添加</button>
                    </td>
                  </tr>
                </table>
              </div>

              <div class="tab-pane fade" id="tab-setting-hot-products">
                <!-- 推荐商品设置 -->
                <div class="mb-4">
                  <h5 class="mb-3">{{ __('panel/setting.hot_products') }}</h5>
                  
                  <!-- 按分类组织商品 -->
                  <div class="mb-3">
                    <button type="button" class="btn btn-success" id="addCategoryGroupBtn">
                      <i class="bi bi-plus-circle"></i> {{ __('panel/setting.add_category') }}
                    </button>
                  </div>
                  
                  <div id="categoryGroupsList"></div>
                  
                  @php
                    $hotProductsValue = old('home_hot_products');
                    if (empty($hotProductsValue)) {
                      $settingValue = system_setting('home_hot_products', '{}');
                      $hotProductsValue = is_string($settingValue) ? $settingValue : json_encode($settingValue ?: []);
                    }
                  @endphp
                  <input type="hidden" name="home_hot_products" id="home_hot_products" value="{{ $hotProductsValue }}">
                </div>
              </div>

              <div class="tab-pane fade" id="tab-setting-home-categories">
                <!-- 首页分类设置 -->
                <div class="mb-4">
                  <h5 class="mb-3">{{ __('panel/setting.home_categories') }}</h5>
                  <p class="text-muted small mb-3">{{ __('panel/setting.home_categories_desc') }}</p>
                  
                  <div class="row">
                    <div class="col-6">
                      <div class="card">
                        <div class="card-header">{{ __('panel/menu.categories') }}</div>
                        <div class="card-body hp-400 overflow-y-auto">
                          @foreach ($categories as $item)
                            <div class="form-check">
                              <input class="form-check-input home-category-checkbox" type="checkbox" 
                                     name="home_categories[]" value="{{ $item['id'] }}"
                                     id="home-category-{{ $item['id'] }}" 
                                     {{ in_array($item['id'], old('home_categories', system_setting('home_categories', []) ?: [])) ? 'checked' : '' }}>
                              <label class="form-check ps-0" for="home-category-{{ $item['id'] }}">
                                {{ $item['name'] }}
                              </label>
                            </div>
                          @endforeach
                        </div>
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="card">
                        <div class="card-header">{{ __('panel/setting.selected_categories') }}</div>
                        <div class="card-body hp-400 overflow-y-auto">
                          <div id="selectedHomeCategories" class="list-group">
                            <!-- Selected categories will be displayed here -->
                          </div>
                          <p class="text-muted small mt-3">{{ __('panel/setting.home_categories_hint') }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              @hookinsert('panel.themes.settings.hot_products.tab_content')
            </div>
          </div>
        </div>
      </div>
    </div>

    <button type="submit" class="d-none"></button>
  </form>

  @once
    @if (empty(old('slideshow', system_setting('slideshow', []))))
      <div class="d-none" aria-hidden="true">
        <x-common-form-image title="" name="_slideshow_filemanager_asset_placeholder" value="" />
      </div>
    @endif
  @endonce
@endsection

@push('footer')
  <script>
    const countryCode = @json(old('country_code', system_setting('country_code')));
    const stateCode = @json(old('state_code', system_setting('state_code')));
    const locales = @json(locales());

    const panelLinkEmpty = { type: 'page', value: '', entity_label: '', link: '', entity_image: '', entity_price: '' };
    const hfLinkTypeOptions = @json($slideshowLinkTypeOptions);
    const linkTypeSelectPlaceholder = @json(__('panel/setting.link_type'));
    const urlLabel = @json(__('panel/setting.slideshow_url_or_path'));
    const linkPickerHint = @json(__('panel/setting.slideshow_link_picker_hint'));
    const linkPickerPlaceholder = @json(__('panel/setting.slideshow_search_placeholder'));
    const linkPickerTitleTemplate = @json(__('panel/setting.slideshow_link_picker_title'));
    const linkChooseLabel = @json(__('panel/setting.slideshow_choose_target'));
    const linkChangeLabel = @json(__('panel/setting.slideshow_change_target'));
    const linkClearLabel = @json(__('panel/setting.slideshow_clear_target'));
    const slideTextHint = @json(__('panel/setting.slideshow_slide_text_hint'));
    const slideTitleLabel = @json(__('panel/setting.slideshow_slide_title'));
    const slideSubtitleLabel = @json(__('panel/setting.slideshow_slide_subtitle'));

    function pickerValueFromLink(link) {
      if (!link) return null;
      if (link.type === 'custom') {
        return { type: 'custom', id: null, name: null, url: (link.link || '').trim() };
      }
      const v = link.value;
      if (v === undefined || v === null || v === '') {
        return {
          type: link.type || 'page',
          id: null,
          name: null,
          url: '',
          image: link.entity_image || '',
          price_label: link.entity_price || '',
        };
      }
      return {
        type: link.type,
        id: v,
        name: link.entity_label || '',
        url: '',
        image: link.entity_image || '',
        price_label: link.entity_price || '',
      };
    }

    function applyPickerToLink(link, val) {
      if (!link) return;
      if (!val) {
        link.type = 'page';
        link.value = '';
        link.entity_label = '';
        link.link = '';
        link.entity_image = '';
        link.entity_price = '';
        return;
      }
      if (val.type === 'custom') {
        link.type = 'custom';
        link.link = val.url || '';
        link.value = '';
        link.entity_label = '';
        link.entity_image = '';
        link.entity_price = '';
        return;
      }
      link.type = val.type;
      link.link = '';
      const id = val.id;
      const hasId = id !== undefined && id !== null && id !== '';
      if (!hasId) {
        link.value = '';
        link.entity_label = '';
        link.entity_image = '';
        link.entity_price = '';
        return;
      }
      link.value = String(id);
      link.entity_label = val.name || '';
      link.entity_image = val.image || '';
      link.entity_price = val.price_label || '';
    }

    function mountPanelLinkPicker(index) {
      const mountEl = document.getElementById('panel-link-vue-' + index);
      const inputEl = document.getElementById('panel-link-input-' + index);
      if (!mountEl || !inputEl || mountEl.dataset.vueMounted) {
        return;
      }
      mountEl.dataset.vueMounted = '1';
      let initial = {};
      try {
        initial = JSON.parse(inputEl.value || '{}');
      } catch (e) {
        initial = {};
      }
      const link = Vue.reactive(Object.assign({}, panelLinkEmpty, initial));
      function syncInput() {
        inputEl.value = JSON.stringify({
          type: link.type,
          value: link.value,
          entity_label: link.entity_label,
          link: link.link,
          entity_image: link.entity_image,
          entity_price: link.entity_price,
        });
      }
      Vue.watch(link, syncInput, { deep: true });
      const app = Vue.createApp({
        setup() {
          const pickerModel = Vue.computed(function () {
            return pickerValueFromLink(link);
          });
          return {
            link,
            pickerModel,
            applyPickerToLink,
            hfLinkTypeOptions,
            linkTypeSelectPlaceholder,
            urlLabel,
            linkPickerHint,
            linkPickerPlaceholder,
            linkPickerTitleTemplate,
            linkChooseLabel,
            linkChangeLabel,
            linkClearLabel,
          };
        },
        template:
          '<inno-link-picker :model-value="pickerModel" @update:model-value="(v) => applyPickerToLink(link, v)" :link-type-options="hfLinkTypeOptions" :placeholder-type="linkTypeSelectPlaceholder" :placeholder-custom-url="urlLabel" :picker-hint="linkPickerHint" :picker-placeholder="linkPickerPlaceholder" :picker-title-template="linkPickerTitleTemplate" :choose-entity-label="linkChooseLabel" :change-entity-label="linkChangeLabel" :clear-entity-label="linkClearLabel" />',
      });
      app.use(ElementPlus);
      if (window.InnoPanel && typeof window.InnoPanel.installVue === 'function') {
        window.InnoPanel.installVue(app);
      }
      app.mount(mountEl);
      syncInput();
    }

    function initPanelLinkPickers() {
      const prefix = 'panel-link-vue-';
      document.querySelectorAll('.panel-inno-link-mount').forEach(function (el) {
        const id = el.id || '';
        if (id.indexOf(prefix) !== 0) {
          return;
        }
        const idx = parseInt(id.slice(prefix.length), 10);
        if (!isNaN(idx)) {
          mountPanelLinkPicker(idx);
        }
      });
    }

    $(function () {
      initPanelLinkPickers();
    });

    getCountries()
    if (countryCode) {
      getZones(countryCode)
    }

    $('select[name="country_code"]').on('change', function () {
      var countryId = $(this).val();
      getZones(countryId);
    });

    // 获取所有国家数据
    function getCountries() {
      axios.get('{{ front_route('countries.index') }}').then(function (res) {
        var countries = res.data;
        var countrySelect = $('select[name="country_code"]');
        countrySelect.empty();
        countrySelect.append('<option value="">请选择国家</option>');
        countries.forEach(function (country) {
          countrySelect.append('<option value="' + country.code + '"' + (country.code == countryCode ? ' selected' : '') + '>' + country.name + '</option>');
        });
      });
    }

    // 获取对应国家的省份数据 countries/72
    function getZones(countryId) {
      axios.get('{{ front_route('countries.index') }}/' + countryId).then(function (res) {
        var zones = res.data;
        var zoneSelect = $('select[name="state_code"]');
        zoneSelect.prop('disabled', false).empty();
        zoneSelect.append('<option value="">请选择省份</option>');
        zones.forEach(function (zone) {
          zoneSelect.append('<option value="' + zone.code + '"' + (zone.code == stateCode ? ' selected' : '') + '>' + zone.name + '</option>');
        });
      });
    }

    function addSlide(btn) {
      var tbody = $(btn).closest('table').find('tbody');
      var index = tbody.find('tr').length;
      var tr = `
      <tr>
        <td>
          <div class="accordion accordion-sm" id="accordion-slideshow-${index}">
            ${locales.map((locale, locale_index) => `
              <div class="accordion-item">
                <h2 class="accordion-header">
                  <button class="accordion-button py-2 ${locale_index === 0 ? '' : 'collapsed'}" type="button" data-bs-toggle="collapse" data-bs-target="#data-locale-${index}-${locale.code}" aria-expanded="false" aria-controls="data-locale-${index}-${locale.code}">
                    <div class="wh-20 me-2"><img src="${locale.image}" class="img-fluid"></div>
                    ${locale.name}
                  </button>
                </h2>
                <div id="data-locale-${index}-${locale.code}" class="accordion-collapse collapse ${locale_index === 0 ? 'show' : ''}" data-bs-parent="#accordion-slideshow-${index}">
                  <div class="accordion-body">
                    <div class="single-image-upload-wrapper">
                      <div class="is-up-file" data-type="image">
                        <div class="img-upload-item bg-light wh-80 rounded border d-flex justify-content-center align-items-center me-2 mb-2 position-relative cursor-pointer overflow-hidden">
                          <div class="position-absolute tool-wrap d-none d-flex top-0 start-0 w-100 bg-primary bg-opacity-75"><div class="show-img w-100 text-center"><i class="bi bi-eye text-white"></i></div><div class="w-100 delete-img text-center"><i class="bi bi-trash text-white"></i></div></div>
                          <div class="position-absolute bg-white d-none img-loading"><div class="spinner-border opacity-50"></div></div>
                          <div class="img-info rounded h-100 w-100 d-flex justify-content-center align-items-center">
                            <i class="bi bi-plus fs-1 text-secondary opacity-75"></i>
                          </div>
                          <input type="hidden" value="" name="slideshow[${index}][image][${locale.code}]">
                        </div>
                      </div>
                    </div>
                    <p class="text-muted small mb-2 mt-2">${slideTextHint}</p>
                    <div class="mb-2">
                      <label class="form-label small mb-0">${slideTitleLabel}</label>
                      <input type="text" class="form-control form-control-sm" name="slideshow[${index}][title][${locale.code}]" value="">
                    </div>
                    <div class="mb-0">
                      <label class="form-label small mb-0">${slideSubtitleLabel}</label>
                      <textarea class="form-control form-control-sm" rows="2" name="slideshow[${index}][subtitle][${locale.code}]"></textarea>
                    </div>
                  </div>
                </div>
              </div>
            `).join('')}
          </div>
        </td>
        <td class="align-top" style="min-width: 280px;">
          <input type="hidden" name="slideshow[${index}][link]" id="panel-link-input-${index}" value='${JSON.stringify(panelLinkEmpty)}'>
          <div id="panel-link-vue-${index}" class="panel-inno-link-mount"></div>
        </td>
        <td class="text-end">
          <button type="button" class="btn btn-danger" onclick="this.closest('tr').remove()">删除</button>
        </td>
      </tr>
    `;
      tbody.append(tr);
      mountPanelLinkPicker(index);
    }

    $('.settings-nav').on('click', 'a', function () {
      var text = $(this).text();
      $('.setting-header').text(text);
    });

    // 首页推荐商品管理（按分类组织）
    const hotProductsData = {}; // 存储产品详细信息 {id: {name, ...}}
    const categories = @json($categories ?? []);
    
    // 数据结构：{categories: [{category_id, category_name, products: [id1, id2, ...]}, ...]}
    let categoryGroups = JSON.parse($('#home_hot_products').val() || '{}');
    if (!categoryGroups.categories || !Array.isArray(categoryGroups.categories)) {
      categoryGroups = {categories: []};
    }

    // 获取分类名称
    function getCategoryName(categoryId) {
      const findCategory = (cats, id) => {
        for (let cat of cats) {
          if (cat.id == id) return cat.name;
          if (cat.children && cat.children.length) {
            const found = findCategory(cat.children, id);
            if (found) return found;
          }
        }
        return null;
      };
      return findCategory(categories, categoryId) || `分类 ID: ${categoryId}`;
    }

    // 渲染分类组列表
    function renderCategoryGroups() {
      const $container = $('#categoryGroupsList');
      $container.empty();
      
      categoryGroups.categories.forEach((group, groupIndex) => {
        const categoryName = getCategoryName(group.category_id);
        const $groupCard = $(`
          <div class="card mb-3 category-group-card" data-group-index="${groupIndex}">
            <div class="card-header d-flex justify-content-between align-items-center">
              <strong>${categoryName}</strong>
              <button type="button" class="btn btn-sm btn-danger remove-category-group">
                <i class="bi bi-trash"></i> 删除分类
              </button>
            </div>
            <div class="card-body">
              <div class="mb-2">
                <button type="button" class="btn btn-sm btn-primary add-product-to-category" data-group-index="${groupIndex}">
                  <i class="bi bi-plus-circle"></i> 添加商品到此分类
                </button>
              </div>
              <ul class="list-group sortable-products-${groupIndex}" data-category-id="${group.category_id}">
                <!-- 商品列表将通过 JS 动态插入 -->
              </ul>
            </div>
          </div>
        `);
        $container.append($groupCard);
        
        // 渲染该分类下的商品
        renderProductsInCategory(groupIndex, group.products || []);
      });
      
      updateHotProductsInput();
    }

    // 渲染指定分类下的商品列表
    function renderProductsInCategory(groupIndex, productIds) {
      const $list = $(`.sortable-products-${groupIndex}`);
      $list.empty();
      
      productIds.forEach((productId) => {
        const productInfo = hotProductsData[productId] || {};
        const productName = productInfo.name || productInfo.product_name || `商品 ID: ${productId}`;
        const productImage = productInfo.image || productInfo.image_url || '';
        const imageUrl = productImage || '{{ asset("vendor/innoshop/images/placeholder.png") }}';
        
        $list.append(`
          <li class="list-group-item d-flex justify-content-between align-items-center" data-product-id="${productId}">
            <div class="d-flex align-items-center flex-grow-1" style="min-width: 0;">
              <i class="bi bi-grip-vertical text-muted me-2" style="cursor: move; flex-shrink: 0;"></i>
              <div class="me-2" style="flex-shrink: 0;">
                <img src="${imageUrl}" alt="${productName}" class="rounded" style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #dee2e6;">
              </div>
              <div class="flex-grow-1" style="min-width: 0;">
                <div class="fw-bold text-truncate" style="max-width: 100%;" title="${productName}">${productName}</div>
                ${productInfo.code ? `<div class="text-muted small text-truncate" style="max-width: 100%;">SKU: ${productInfo.code}</div>` : ''}
              </div>
            </div>
            <button type="button" class="btn btn-sm btn-danger remove-product-from-category" data-group-index="${groupIndex}" data-product-id="${productId}" style="flex-shrink: 0;">
              <i class="bi bi-x"></i>
            </button>
          </li>
        `);
      });
      
      // 初始化拖拽排序
      if (typeof Sortable !== 'undefined') {
        new Sortable($list[0], {
          handle: '.bi-grip-vertical',
          animation: 150,
          onEnd: function(evt) {
            const group = categoryGroups.categories[groupIndex];
            const item = group.products.splice(evt.oldIndex, 1)[0];
            group.products.splice(evt.newIndex, 0, item);
            updateHotProductsInput();
          }
        });
      }
    }

    // 更新隐藏输入框
    function updateHotProductsInput() {
      $('#home_hot_products').val(JSON.stringify(categoryGroups));
    }

    // 添加分类组
    $('#addCategoryGroupBtn').on('click', function() {
      // 创建分类选择对话框
      const categoryOptions = formatCategoriesForSelect(categories);
      let selectedCategoryId = null;
      
      const content = `
        <div class="p-3">
          <label class="form-label">选择分类：</label>
          <select class="form-select" id="categorySelect" style="width: 100%;">
            <option value="">请选择分类</option>
            ${categoryOptions}
          </select>
        </div>
      `;
      
      layer.open({
        type: 1,
        title: "选择分类",
        shadeClose: false,
        shade: 0.8,
        area: ["500px", "200px"],
        content: content,
        btn: ['确定', '取消'],
        yes: function(index) {
          selectedCategoryId = $('#categorySelect').val();
          if (!selectedCategoryId) {
            layer.msg('请选择分类', {icon: 2});
            return;
          }
          
          // 检查是否已存在该分类
          const exists = categoryGroups.categories.some(g => g.category_id == selectedCategoryId);
          if (exists) {
            layer.msg('该分类已添加', {icon: 2});
            return;
          }
          
          // 添加新分类组
          categoryGroups.categories.push({
            category_id: parseInt(selectedCategoryId),
            category_name: getCategoryName(selectedCategoryId),
            products: []
          });
          
          renderCategoryGroups();
          layer.close(index);
        }
      });
    });

    // 格式化分类为选择框选项
    function formatCategoriesForSelect(cats, level = 0) {
      let html = '';
      cats.forEach(cat => {
        const indent = '&nbsp;'.repeat(level * 2);
        html += `<option value="${cat.id}">${indent}${cat.name}</option>`;
        if (cat.children && cat.children.length > 0) {
          html += formatCategoriesForSelect(cat.children, level + 1);
        }
      });
      return html;
    }

    // 产品选择器回调（需要知道添加到哪个分类）
    window.productSelectorCallback = function(products) {
      const groupIndex = window.currentProductSelectorGroupIndex;
      if (groupIndex === undefined || !categoryGroups.categories[groupIndex]) {
        layer.msg('请先选择分类', {icon: 2});
        return;
      }
      
      const group = categoryGroups.categories[groupIndex];
      products.forEach(product => {
        const productId = product.product_id || product.id;
        if (!group.products.includes(productId)) {
          group.products.push(productId);
          // 保存产品详细信息
          hotProductsData[productId] = {
            id: productId,
            name: product.name || product.product_name,
            code: product.code,
            image: product.image || product.image_url || product.image_small || product.image_big,
            image_url: product.image_url || product.image || product.image_small || product.image_big,
          };
        }
      });
      renderCategoryGroups();
    };

    // 添加商品到分类
    $(document).on('click', '.add-product-to-category', function() {
      const groupIndex = parseInt($(this).data('group-index'));
      window.currentProductSelectorGroupIndex = groupIndex;
      
      layer.open({
        type: 2,
        title: "选择商品",
        shadeClose: false,
        shade: 0.8,
        area: ["800px", "800px"],
        content: urls.panel_base + '/products/selector',
      });
    });

    // 从分类中删除商品
    $(document).on('click', '.remove-product-from-category', function() {
      const groupIndex = parseInt($(this).data('group-index'));
      const productId = parseInt($(this).data('product-id'));
      const group = categoryGroups.categories[groupIndex];
      
      if (group && group.products) {
        const index = group.products.indexOf(productId);
        if (index > -1) {
          group.products.splice(index, 1);
          delete hotProductsData[productId];
        }
      }
      renderCategoryGroups();
    });

    // 删除分类组
    $(document).on('click', '.remove-category-group', function() {
      const $card = $(this).closest('.category-group-card');
      const groupIndex = parseInt($card.data('group-index'));
      const group = categoryGroups.categories[groupIndex];
      
      // 删除该分类下的所有商品数据
      if (group && group.products) {
        group.products.forEach(productId => {
          delete hotProductsData[productId];
        });
      }
      
      categoryGroups.categories.splice(groupIndex, 1);
      renderCategoryGroups();
    });

    // 加载已选商品的详细信息（包括图片）
    function loadHotProductsInfo() {
      // 收集所有商品ID
      const allProductIds = [];
      categoryGroups.categories.forEach(group => {
        if (group.products && Array.isArray(group.products)) {
          allProductIds.push(...group.products);
        }
      });
      
      if (allProductIds.length === 0) {
        renderCategoryGroups();
        return;
      }
      
      // 去重
      const uniqueProductIds = [...new Set(allProductIds)];
      
      // 通过API获取商品详细信息
      $.ajax({
        url: urls.panel_api + '/products/names',
        type: 'GET',
        data: {
          ids: uniqueProductIds.join(',')
        },
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'Authorization': 'Bearer ' + (document.querySelector('meta[name="api-token"]')?.content || '')
        },
        success: function(res) {
          if (res.data && Array.isArray(res.data)) {
            res.data.forEach(product => {
              const productId = product.id;
              hotProductsData[productId] = {
                id: productId,
                name: product.name,
                image: product.image_small || product.image_big,
                image_url: product.image_small || product.image_big,
                price_format: product.price_format,
              };
            });
          }
          renderCategoryGroups();
        },
        error: function(xhr) {
          console.warn('Failed to load product info:', xhr);
          renderCategoryGroups();
        }
      });
    }

    // 初始化渲染
    loadHotProductsInfo();

    // 首页分类管理
    const homeCategories = @json(old('home_categories', system_setting('home_categories', []) ?: []));
    const allCategories = @json($categories ?? []);

    // 渲染已选分类
    function renderSelectedHomeCategories() {
      const selectedDiv = $('#selectedHomeCategories');
      selectedDiv.empty();
      
      const selectedIds = [];
      $('.home-category-checkbox:checked').each(function() {
        selectedIds.push(parseInt($(this).val()));
      });

      if (selectedIds.length === 0) {
        selectedDiv.html('<p class="text-muted small">' + '{{ __('panel/setting.no_categories_selected') ?? "未选择分类" }}' + '</p>');
        return;
      }

      selectedIds.forEach(function(categoryId) {
        const category = allCategories.find(c => c.id == categoryId);
        if (category) {
          const item = $('<div class="list-group-item d-flex justify-content-between align-items-center"></div>');
          item.html('<span>' + category.name + '</span><button type="button" class="btn btn-sm btn-outline-danger remove-category" data-id="' + categoryId + '"><i class="bi bi-x"></i></button>');
          selectedDiv.append(item);
        }
      });
    }

    // 初始化已选分类显示
    renderSelectedHomeCategories();

    // 监听分类选择变化
    $(document).on('change', '.home-category-checkbox', function() {
      renderSelectedHomeCategories();
    });

    // 移除分类
    $(document).on('click', '.remove-category', function() {
      const categoryId = $(this).data('id');
      $('#home-category-' + categoryId).prop('checked', false);
      renderSelectedHomeCategories();
    });
  </script>
  <!-- 供应商相关脚本（通过 Hook 扩展） -->
  @hookinsert('panel.themes.settings.script.after')
@endpush