<div class="row">
  <div class="col-6">
    <div class="form-group mb-4">
      <label class="form-label" for="country_code">{{ __('panel/setting.default_country') }}</label>
      <select class="form-select" name="country_code" required></select>
      <span class="invalid-feedback" role="alert">{{ __('panel/setting.please_select_country') }}</span>
    </div>
  </div>
  <div class="col-6">
    <div class="form-group mb-4">
      <label class="form-label" for="state">{{ __('panel/setting.default_province') }}</label>
      <select class="form-select" name="state_code" disabled required></select>
      <span class="invalid-feedback" role="alert">">{{ __('panel/setting.please_select_province') }}</span>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-6">
    <x-common-form-select title="{{ __('panel/setting.front_default_language') }}" name="front_locale" :options="$locales" key="code" label="name" value="{{ old('front_locale', system_setting('front_locale')) }}" required placeholder="{{ __('panel/setting.front_default_language') }}"/>
  </div>
  <div class="col-6">
    <x-common-form-select title="{{ __('panel/setting.default_currency') }}" name="currency" :options="$currencies" key="code" label="name" value="{{ old('currency', system_setting('currency')) }}" required placeholder="{{ __('panel/setting.default_currency') }}"/>
  </div>
</div>

<div class="row">
  <div class="col-6">
    <x-common-form-switch-radio title="{{ __('panel/setting.login_checkout') }}" name="login_checkout" required value="{{ old('login_checkout', system_setting('login_checkout')) }}"/>
  </div>
  <div class="col-6">
    <x-common-form-switch-radio title="{{ __('panel/setting.backstage_menu_expansion') }}" name="expand" required value="{{ old('expand', system_setting('expand')) }}"/>
  </div>
</div>


<x-common-form-input title="{{ __('panel/setting.panel_name') }}" name="panel_name" value="{{ old('panel_name', system_setting('panel_name')) }}" />

<x-common-form-input title="{{ __('panel/setting.icp_number') }}" name="icp_number" value="{{ old('icp_number', system_setting('icp_number')) }}" />

<x-common-form-textarea title="{{ __('panel/setting.js_code') }}" name="js_code" value="{{ old('js_code', system_setting('js_code')) }}" placeholder="{{ __('panel/setting.js_code') }}" />