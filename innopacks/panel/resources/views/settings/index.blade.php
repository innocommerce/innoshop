@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.settings'))

<x-panel::form.right-btns />

@section('content')
<form class="needs-validation" novalidate action="{{ panel_route('settings.update') }}" method="POST" id="app-form">
  @csrf
  @method('put')
  <div class="row">
    <div class="col-md-3">
      <div class="card" id="setting-menu">
        <div class="card-header">{{ __('panel/menu.settings') }}</div>
        <div class="card-body">
          <ul class="nav flex-column settings-nav">
            <!-- Core Settings (High Frequency + High Importance) -->
            <a class="nav-link active" href="#" data-bs-toggle="tab" data-bs-target="#tab-setting-basics">{{ __('panel/setting.basic') }}</a>
            <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#tab-setting-localization">{{ __('panel/setting.localization_settings') }}</a>
            <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#tab-setting-logistics-information">{{ __('panel/setting.express_company') }}</a>
            
            <!-- Business Settings (Medium-High Frequency + High Importance) -->
            <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#tab-setting-store-system">{{ __('panel/setting.store_system_setting') }}</a>
            <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#tab-setting-email">{{ __('panel/setting_email.email_setting') }}</a>
            <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#tab-setting-sms">{{ __('panel/setting_sms.sms_setting') }}</a>
            
            <!-- Feature Settings (Medium Frequency + Medium Importance) -->
            <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#tab-setting-image">{{ __('panel/setting_image.image_settings') }}</a>

            <!-- Storage Settings -->
            <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#tab-setting-storage">{{ __('panel/setting.storage_settings') }}</a>

            <!-- Tool Settings (Low Frequency + Low Importance) -->
            <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#tab-setting-tools">{{ __('panel/setting.tools_setting') }}</a>
            
            @hookinsert('panel.settings.tab.nav.bottom')
        </ul>
        </div>
      </div>
    </div>
    <div class="col-md-9">
      <div class="card h-min-600">
        <div class="card-header setting-header">{{ __('panel/setting.basic') }}</div>
        <div class="card-body">
          <div class="tab-content">
            @include('panel::settings._basic_setting')
            @include('panel::settings._localization_setting')
            @include('panel::settings._store_system_setting')
            @include('panel::settings._image_setting')
            @include('panel::settings._storage_setting')
            @include('panel::settings._email_setting')
            @include('panel::settings._sms_setting')
            @include('panel::settings._tools_setting')
            @include('panel::settings._logistics_information')
            @hookinsert('panel.settings.tab.pane.bottom')
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

  // Switch to tab from URL query param
  var tabParam = new URLSearchParams(window.location.search).get('tab');
  if (tabParam) {
    $('a[data-bs-target="#' + tabParam + '"]').tab('show');
  }

  // Get all countries data
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

  // Get zones/provinces data for the specified country
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
</script>
@endpush
