<!-- Localization Settings -->
<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/setting.localization_settings') }}</h5>
    <p class="text-muted small mb-0">{{ __('panel/setting.localization_settings_desc') }}</p>
  </div>
  <div class="card-body">
    <div class="row mb-3">
      <div class="col-md-6">
        <div class="form-group">
          <label class="form-label" for="country_code">{{ __('panel/setting.default_country') }}</label>
          <select class="form-select" name="country_code" required></select>
          <span class="invalid-feedback" role="alert">{{ __('panel/setting.please_select_country') }}</span>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label class="form-label" for="state">{{ __('panel/setting.default_province') }}</label>
          <select class="form-select" name="state_code" disabled required></select>
          <span class="invalid-feedback" role="alert">">{{ __('panel/setting.please_select_province') }}</span>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-4">
        <x-common-form-select title="{{ __('panel/setting.front_default_language') }}" name="front_locale"
                            :options="$locales" key="code" label="name" :empty-option="false"
                            value="{{ old('front_locale', system_setting('front_locale')) }}" required
                            placeholder="{{ __('panel/setting.front_default_language') }}"/>
      </div>
      <div class="col-md-4">
        <x-common-form-select title="{{ __('panel/setting.default_currency') }}" name="currency"
                            :options="$currencies" key="code" label="name" :empty-option="false"
                            value="{{ old('currency', system_setting('currency')) }}" required
                            placeholder="{{ __('panel/setting.default_currency') }}"/>
      </div>
      <div class="col-md-4">
        <x-common-form-select title="{{ __('panel/setting.default_weight_class') }}" name="weight_class"
                            :options="$weight_classes" key="code" label="name" :empty-option="false"
                            value="{{ old('weight_class', system_setting('weight_class')) }}" required
                            placeholder="{{ __('panel/setting.default_weight_class') }}"/>
      </div>
    </div>
  </div>
</div>

<!-- Store Features -->
<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/setting.store_features') }}</h5>
    <p class="text-muted small mb-0">{{ __('panel/setting.store_features_desc') }}</p>
  </div>
  <div class="card-body">
    <div class="row mb-4">
      <div class="col-md-6">
        <div class="mb-4">
          <x-common-form-switch-radio title="{{ __('panel/setting.auto_fill_lang') }}" name="auto_fill_lang" 
                                  value="{{ old('auto_fill_lang', system_setting('auto_fill_lang', false)) }}"/>
          <p class="text-muted small mt-1">{{ __('panel/setting.auto_fill_lang_desc') }}</p>
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-4">
          <x-common-form-switch-radio title="{{ __('panel/setting.title_to_tdk') }}" name="title_to_tdk" 
                                  value="{{ old('title_to_tdk', system_setting('title_to_tdk', false)) }}"/>
          <p class="text-muted small mt-1">{{ __('panel/setting.title_to_tdk_desc') }}</p>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-md-6">
        <div class="mb-4">
          <x-common-form-switch-radio title="{{ __('panel/setting.online_order') }}" name="online_order" required
                                  value="{{ old('online_order', system_setting('online_order', true)) }}"/>
          <p class="text-muted small mt-1">{{ __('panel/setting.online_order_desc') }}</p>
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-4">
          <x-common-form-switch-radio title="{{ __('panel/setting.login_checkout') }}" name="login_checkout" required
                                  value="{{ old('login_checkout', system_setting('login_checkout')) }}"/>
          <p class="text-muted small mt-1">{{ __('panel/setting.login_checkout_desc') }}</p>
        </div>
      </div>
    </div>
    
    <div class="row mb-4">
      <div class="col-md-6">
        <div class="mb-4">
          <x-common-form-switch-radio title="{{ __('panel/setting.bought_review') }}" name="bought_review" required
                                  value="{{ old('bought_review', system_setting('bought_review')) }}"/>
          <p class="text-muted small mt-1">{{ __('panel/setting.bought_review_desc') }}</p>
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-4">
          <x-common-form-switch-radio title="{{ __('panel/setting.backstage_menu_expansion') }}" name="expand" required
                                  value="{{ old('expand', system_setting('expand')) }}"/>
          <p class="text-muted small mt-1">{{ __('panel/setting.backstage_menu_expansion_desc') }}</p>
        </div>
      </div>
    </div>
    
    <div class="row">
      <div class="col-md-6">
        <div class="mb-4">
          <x-common-form-switch-radio title="{{ __('panel/setting.debug') }}" name="debug" required
                                  value="{{ old('debug', system_setting('debug')) }}"/>
          <p class="text-muted small mt-1">{{ __('panel/setting.debug_desc') }}</p>
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-4">
          <x-common-form-switch-radio title="{{ __('panel/setting.maintenance_mode') }}" name="maintenance_mode" required
                                  value="{{ old('maintenance_mode', system_setting('maintenance_mode')) }}"/>
          <p class="text-muted small mt-1">{{ __('panel/setting.maintenance_mode_desc') }}</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Advanced Settings -->
<div class="card">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/setting.advanced_settings') }}</h5>
    <p class="text-muted small mb-0">{{ __('panel/setting.advanced_settings_desc') }}</p>
  </div>
  <div class="card-body">
    <x-common-form-input title="{{ __('panel/setting.panel_name') }}" name="panel_name"
                       value="{{ old('panel_name', system_setting('panel_name')) }}"/>

    <x-common-form-input title="{{ __('panel/setting.icp_number') }}" name="icp_number"
                       value="{{ old('icp_number', system_setting('icp_number')) }}"/>

    <x-common-form-textarea title="{{ __('panel/setting.js_code') }}" name="js_code"
                          value="{{ old('js_code', system_setting('js_code')) }}"
                          placeholder="{{ __('panel/setting.js_code') }}"/>
  </div>
</div>