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

@push('footer')
  <script>
    // ========== Slideshow ==========
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

    // Slideshow: override image upload to accept all media (image + video)
    $(document).on('click', '#tab-setting-slideshow .single-image-upload-wrapper .is-up-file .img-upload-item', function (e) {
      if ($(e.target).closest('.delete-img, .show-img').length) return;
      e.stopPropagation();
      var _self = $(this);
      inno.mediaIframe(function(file) {
        var val = file.path;
        var url = file.url;
        var originUrl = file.origin_url || file.url;
        _self.find('input').val(val);
        _self.find('.tool-wrap').removeClass('d-none');
        if (/\.(mp4|webm|ogg)(\?|$)/i.test(val)) {
          _self.find('.img-info').html('<i class="bi bi-play-circle fs-3 text-primary"></i>');
        } else {
          _self.find('.img-info').html('<img src="' + url + '" class="img-fluid" data-origin-img="' + originUrl + '">');
        }
        _self.find('input').trigger('change');
      }, { multiple: false, type: 'file' });
    });
  </script>
@endpush
