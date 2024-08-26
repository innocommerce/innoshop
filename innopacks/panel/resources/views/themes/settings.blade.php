@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.themes_settings'))

<x-panel::form.right-btns/>

@section('content')
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
                 data-bs-target="#tab-setting-slideshow">{{ __('panel/setting.slideshow') }}</a>
              <a class="nav-link" href="#" data-bs-toggle="tab"
                 data-bs-target="#tab-setting-header-menu">{{ __('panel/setting.header_menu') }}</a>
              <a class="nav-link" href="#" data-bs-toggle="tab"
                 data-bs-target="#tab-setting-footer-menu">{{ __('panel/setting.footer_menu') }}</a>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-9">
        <div class="card h-min-600">
          <div class="card-header setting-header">{{ __('panel/setting.slideshow') }}</div>
          <div class="card-body">
            <div class="tab-content">
              <div class="tab-pane fade show active" id="tab-setting-slideshow">
                <table class="table table-bordered align-middle">
                  <thead>
                  <th>{{ __('panel/common.image') }}</th>
                  <th>{{ __('panel/common.link') }}</th>
                  <th class="text-end" width="100"></th>
                  </thead>
                  <tbody>
                  @foreach (old('slideshow', system_setting('slideshow', [])) as $slide_index => $slide)
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
                                </div>
                              </div>
                            </div>
                          @endforeach
                        </div>
                      </td>
                      <td>
                        <input type="text" name="slideshow[{{ $slide_index }}][link]" value="{{ $slide['link'] }}"
                               class="form-control">
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

              <div class="tab-pane fade" id="tab-setting-header-menu">
                <div class="row">
                  <div class="col-4">
                    <div class="card">
                      <div class="card-header">{{ __('panel/menu.categories') }}</div>
                      <div class="card-body hp-400 overflow-y-auto">
                        <input class="d-none" name="menu_categories" value="">
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
                  <div class="col-4">
                    <div class="card">
                      <div class="card-header">{{ __('panel/setting.catalogs') }}</div>
                      <div class="card-body hp-400 overflow-y-auto">
                        <input class="d-none" name="menu_catalogs" value="">
                        @foreach ($catalogs as $item)
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="menu_header_catalogs[]"
                                   value="{{ $item->id }}"
                                   id="header-catalog-{{ $item->id }}" {{ in_array($item->id, old('menu_header_catalogs', system_setting('menu_header_catalogs', []) ?: [])) ? 'checked' : '' }}>
                            <label class="form-check ps-0"
                                   for="header-catalog-{{ $item->id }}">{{ $item->translation->title }}</label>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="card">
                      <div class="card-header">{{ __('panel/setting.page') }}</div>
                      <div class="card-body hp-400 overflow-y-auto">
                        <input class="d-none" name="menu_pages" value="">
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
                </div>
              </div>

              <div class="tab-pane fade" id="tab-setting-footer-menu">
                <div class="row">
                  <div class="col-4">
                    <div class="card">
                      <div class="card-header">{{ __('panel/menu.categories') }}</div>
                      <div class="card-body hp-400 overflow-y-auto">
                        <input class="d-none" name="footer_categories" value="">
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
                  <div class="col-4">
                    <div class="card">
                      <div class="card-header">{{ __('panel/setting.catalogs') }}</div>
                      <div class="card-body hp-400 overflow-y-auto">
                        <input class="d-none" name="footer_catalogs" value="">
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
                  <div class="col-4">
                    <div class="card">
                      <div class="card-header">{{ __('panel/setting.page') }}</div>
                      <div class="card-body hp-400 overflow-y-auto">
                        <input class="d-none" name="footer_pages" value="">
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
                </div>
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
    const countryCode = @json(old('country_code', system_setting('country_code')));
    const stateCode = @json(old('state_code', system_setting('state_code')));
    const locales = @json(locales());

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
                    <div class="is-up-file slideshow-img">
                      <div class="img-upload-item wh-80 position-relative d-flex justify-content-center rounded overflow-hidden align-items-center border border-1 mb-1 me-1">
                        <div class="position-absolute bg-white d-none img-loading"><div class="spinner-border opacity-50"></div></div>
                        <div class="img-info d-flex justify-content-center align-items-center h-100 w-80 cursor-pointer">
                          <i class="bi bi-plus fs-1 text-secondary opacity-75"></i>
                        </div>
                        <input class="d-none" name="slideshow[${index}][image][${locale.code}]" value="">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            `).join('')}
          </div>
        </td>
        <td>
          <input type="text" name="slideshow[${index}][link]" class="form-control">
        </td>
        <td class="text-end">
          <button type="button" class="btn btn-danger" onclick="this.closest('tr').remove()">删除</button>
        </td>
      </tr>
    `;
      tbody.append(tr);
    }

    $(document).on('click', '.is-up-file.slideshow-img .img-upload-item', function () {
      const _self = $(this);
      $('#form-upload').remove();
      $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" accept="image/*" name="file" /></form>');
      $('#form-upload input[name=\'file\']').trigger('click');
      $('#form-upload input[name=\'file\']').change(function () {
        let file = $(this).prop('files')[0];
        inno.imgUploadAjax(file, _self, (data) => {
          _self.find('input').val(data.data.value);
          _self.find('.img-info').html('<img src="' + data.data.url + '" class="img-fluid">');
        })
      });
    })

    $('.settings-nav').on('click', 'a', function () {
      var text = $(this).text();
      $('.setting-header').text(text);
    });
  </script>
@endpush