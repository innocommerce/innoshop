@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.settings'))

<x-panel::form.right-btns />

@section('content')
<form class="needs-validation" novalidate action="{{ panel_route('settings.update') }}" method="POST" id="app-form">
  @csrf
  @method('put')
  <div class="row">
    <div class="col-3">
      <div class="card h-min-600" id="setting-menu">
        <div class="card-header">{{ __('panel/menu.settings') }}</div>
        <div class="card-body">
          <ul class="nav flex-column settings-nav">
            <a class="nav-link active" href="#" data-bs-toggle="tab" data-bs-target="#tab-setting-basics">{{ __('panel/setting.basic') }}</a>
            <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#tab-setting-webdata">{{ __('panel/setting.website_data') }}</a>
            <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#tab-setting-email">{{ __('panel/setting.email_setting') }}</a>
            <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#tab-setting-content-ai">{{ __('panel/setting.content_ai') }}</a>
          </ul>
        </div>
      </div>
    </div>
    <div class="col-9">
      <div class="card h-min-600">
        <div class="card-header setting-header">{{ __('panel/setting.basic') }}</div>
        <div class="card-body">
          <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-setting-basics">
              @include('panel::settings._basic_setting')
            </div>

            <div class="tab-pane fade" id="tab-setting-webdata">
              @include('panel::settings._web_data')
            </div>

            <div class="tab-pane fade" id="tab-setting-email">
              @include('panel::settings._email_setting')
            </div>

            <div class="tab-pane fade" id="tab-setting-content-ai">
              @include('panel::settings._content_ai')
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
  const locales = @json($locales);

  getCountries()
  if (countryCode) {
    getZones(countryCode)
  }

  $('select[name="country_code"]').on('change', function() {
    var countryId = $(this).val();
    getZones(countryId);
  });

  // 获取所有国家数据
  function getCountries() {
    axios.get('{{ front_route('countries.index') }}').then(function(res) {
      var countries = res.data;
      var countrySelect = $('select[name="country_code"]');
      countrySelect.empty();
      countrySelect.append('<option value="">请选择国家</option>');
      countries.forEach(function(country) {
        countrySelect.append('<option value="' + country.code + '"' + (country.code == countryCode ? ' selected' : '') + '>' + country.name + '</option>');
      });
    });
  }

  // 获取对应国家的省份数据 countries/72
  function getZones(countryId) {
    axios.get('{{ front_route('countries.index') }}/' + countryId).then(function(res) {
      var zones = res.data;
      var zoneSelect = $('select[name="state_code"]');
      zoneSelect.prop('disabled', false).empty();
      zoneSelect.append('<option value="">请选择省份</option>');
      zones.forEach(function(zone) {
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

  $('.settings-nav').on('click', 'a', function() {
    var text = $(this).text();
    $('.setting-header').text(text);
  });
</script>
@endpush
