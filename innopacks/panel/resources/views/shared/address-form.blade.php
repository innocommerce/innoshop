<form  class="needs-validation address-form mb-4" novalidate>
  <input type="hidden" name="id" value="">
  <div class="form-group mb-4">
    <label class="form-label" for="name">{{ __('common/address.name') }}</label>
    <input type="text" class="form-control" name="name" value="" required placeholder="{{ __('common/address.name') }}" />
    <span class="invalid-feedback" role="alert">{{ __('front/common.error_required', ['name' => __('common/address.name')]) }}</span>
  </div>
  <div class="form-group mb-4">
    <label class="form-label" for="email">{{ __('common/address.address_1') }}</label>
    <input type="text" class="form-control" name="address_1" value="" required placeholder="{{ __('common/address.address_1') }}" />
    <span class="invalid-feedback" role="alert">{{ __('front/common.error_required', ['name' => __('common/address.address_1')]) }}</span>
  </div>
  <div class="row gx-2">
    <div class="col-6">
      <div class="form-group mb-4">
        <label class="form-label" for="Address_1">{{ __('common/address.address_2') }}</label>
        <input type="text" class="form-control" name="address_2" value="" placeholder="{{ __('common/address.address_2') }}" />
      </div>
    </div>
    <div class="col-6">
      <div class="form-group mb-4">
        <label class="form-label" for="zipcode">{{ __('common/address.zipcode') }}</label>
        <input type="text" class="form-control" name="zipcode" value="" required placeholder="{{ __('common/address.zipcode') }}" />
        <span class="invalid-feedback" role="alert">{{ __('front/common.error_required', ['name' => __('common/address.zipcode')]) }}</span>
      </div>
    </div>
    <div class="col-6">
      <div class="form-group mb-4">
        <label class="form-label" for="city">{{ __('common/address.city') }}</label>
        <input type="text" class="form-control" name="city" value="" required placeholder="City" />
        <span class="invalid-feedback" role="alert">{{ __('front/common.error_required', ['name' => __('common/address.city')]) }}</span>
      </div>
    </div>
    <div class="col-6">
      <div class="form-group mb-4">
        <label class="form-label" for="country_code">{{ __('common/address.country') }}</label>
        <select class="form-select" name="country_code" required></select>
        <span class="invalid-feedback" role="alert">{{ __('front/common.error_required', ['name' => __('common/address.country')]) }}</span>
      </div>
    </div>
    <div class="col-6">
      <div class="form-group mb-4">
        <label class="form-label" for="state">{{ __('common/address.state') }}</label>
        <select class="form-select" name="state_code" required disabled></select>
        <span class="invalid-feedback" role="alert">{{ __('front/common.error_required', ['name' => __('common/address.state')]) }}</span>
      </div>
    </div>
    <div class="col-6">
      <div class="form-group mb-4">
        <label class="form-label" for="phone">{{ __('common/address.phone') }}</label>
        <input type="text" class="form-control" name="phone" value="" placeholder="{{ __('common/address.phone') }}" />
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-center">
    <button type="button" class="btn btn-primary btn-lg form-submit w-50">{{ __('front/common.submit') }}</button>
  </div>
</form>

@push('footer')
<script>
  const settingCountryCode = @json(system_setting('country_code') ?? '');
  const settingStateCode = @json(system_setting('state_code') ?? '');

  inno.validateAndSubmitForm('.address-form', function(data) {
    if (typeof updataAddress === 'function') {
      updataAddress(data);
    }
  })

  getCountries()

  if (settingCountryCode) {
    $('select[name="country_code"]').val(settingCountryCode);
    getZones(settingCountryCode);
  }

  $(document).on('change', 'select[name="country_code"]', function() {
    var countryId = $(this).val();
    getZones(countryId);
  });

  // 获取所有国家数据
  function getCountries() {
    axios.get('{{ front_route('countries.index') }}').then(function(res) {
      var countries = res.data;
      var countrySelect = $('select[name="country_code"]');
      countrySelect.empty();
      countrySelect.append('<option value="">{{ __('panel/common.please_choose') }}</option>');
      countries.forEach(function(country) {
        countrySelect.append('<option value="' + country.code + '"' + (country.code == settingCountryCode ? ' selected' : '') + '>' + country.name + '</option>');
      });
    });
  }

  // 获取对应国家的省份数据 countries/72
  function getZones(countryId, callback = null) {
    axios.get('{{ front_route('countries.index') }}/' + countryId).then(function(res) {
      var zones = res.data;
      var zoneSelect = $('select[name="state_code"]');
      zoneSelect.empty().prop('disabled', false);
      zoneSelect.append('<option value="">{{ __('panel/common.please_choose') }}</option>');
      zones.forEach(function(zone) {
        zoneSelect.append('<option value="' + zone.code + '"' + (zone.code == settingStateCode ? ' selected' : '') + '>' + zone.name + '</option>');
      });

      if (typeof callback === 'function') {
        callback();
      }
    });
  }

  function clearForm() {
    $('.address-form')[0].reset(); // 重置表单到初始值
    $('.address-form').removeClass('was-validated'); // 移除验证状态

    // 清空所有验证反馈
    $('.address-form').find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
  }
</script>
@endpush