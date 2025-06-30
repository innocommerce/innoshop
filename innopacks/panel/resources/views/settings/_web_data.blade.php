<!-- Localization Settings -->
<div class="tab-pane fade" id="tab-setting-webdata">
<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/setting.localization_settings') }}</h5>
    <p class="text-muted small mb-0">{{ __('panel/setting.localization_settings_desc') }}</p>
  </div>
  <div class="card-body">
    <!-- Region Settings -->
    <div class="mb-4">
      <h6 class="mb-3">{{ __('panel/setting.region_settings') }}</h6>
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
            <span class="invalid-feedback" role="alert">{{ __('panel/setting.please_select_province') }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Language and Currency -->
    <div class="mb-4">
      <h6 class="mb-3">{{ __('panel/setting.language_currency') }}</h6>
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
</div>

<!-- Store Features -->
<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/setting.store_features') }}</h5>
    <p class="text-muted small mb-0">{{ __('panel/setting.store_features_desc') }}</p>
  </div>
  <div class="card-body">
    <!-- Store Functionality -->
    <div class="mb-4">
      <h6 class="mb-3">{{ __('panel/setting.store_functionality') }}</h6>
      <div class="row">
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
            <div class="text-secondary"><small>{{ __('panel/setting.title_to_tdk_desc') }}</small></div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="mb-4">
            <x-common-form-switch-radio title="{{ __('panel/setting.hide_url_locale') }}" name="hide_url_locale"
                                    value="{{ old('hide_url_locale', system_setting('hide_url_locale', false)) }}"/>
            <div class="text-secondary"><small>{{ __('panel/setting.hide_url_locale_desc') }}</small></div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-4">
            <x-common-form-switch-radio title="{{ __('panel/setting.disable_online_order') }}" name="disable_online_order" required
                                    value="{{ old('disable_online_order', system_setting('disable_online_order', false)) }}"/>
            <div class="text-secondary"><small>{{ __('panel/setting.disable_online_order_desc') }}</small></div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="mb-4">
            <x-common-form-switch-radio title="{{ __('panel/setting.login_checkout') }}" name="login_checkout" required
                                    value="{{ old('login_checkout', system_setting('login_checkout')) }}"/>
            <div class="text-secondary"><small>{{ __('panel/setting.login_checkout_desc') }}</small></div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-4">
            <x-common-form-switch-radio title="{{ __('panel/setting.bought_review') }}" name="bought_review" required
                                    value="{{ old('bought_review', system_setting('bought_review')) }}"/>
            <div class="text-secondary"><small>{{ __('panel/setting.bought_review_desc') }}</small></div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="mb-4">
            <x-common-form-switch-radio 
              title="{{ __('panel/setting.allow_out_of_stock') }}" 
              name="allow_out_of_stock" 
              value="{{ old('allow_out_of_stock', system_setting('allow_out_of_stock')) }}"
            />
            <div class="text-secondary">
              <small>{{ __('panel/setting.allow_out_of_stock_desc') }}</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- System Features -->
    <div class="mb-4">
      <h6 class="mb-3">{{ __('panel/setting.system_features') }}</h6>
      <div class="row">
        <div class="col-md-6">
          <div class="mb-4">
            <x-common-form-switch-radio title="{{ __('panel/setting.backstage_menu_expansion') }}" name="expand" required
                                    value="{{ old('expand', system_setting('expand')) }}"/>
            <div class="text-secondary"><small>{{ __('panel/setting.backstage_menu_expansion_desc') }}</small></div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-4">
            <x-common-form-switch-radio title="{{ __('panel/setting.debug') }}" name="debug" required
                                    value="{{ old('debug', system_setting('debug')) }}"/>
            <div class="text-secondary"><small>{{ __('panel/setting.debug_desc') }}</small></div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="mb-4">
            <x-common-form-switch-radio title="{{ __('panel/setting.maintenance_mode') }}" name="maintenance_mode" required
                                    value="{{ old('maintenance_mode', system_setting('maintenance_mode')) }}"/>
            <div class="text-secondary"><small>{{ __('panel/setting.maintenance_mode_desc') }}</small></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Media Management -->
<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/setting.media_management') }}</h5>
    <p class="text-muted small mb-0">{{ __('panel/setting.media_management_desc') }}</p>
  </div>
  <div class="card-body">
    <!-- File Manager -->
    <div class="mb-4">
      <h6 class="mb-3">{{ __('panel/setting.file_manager') }}</h6>
      <div class="row">
        <div class="col-md-6">
          <div class="mb-4">
            <x-common-form-switch-radio title="{{ __('panel/setting.file_manager_enable_crop') }}" name="file_manager_enable_crop" 
                                    value="{{ old('file_manager_enable_crop', system_setting('file_manager_enable_crop')) }}"/>
            <div class="text-secondary"><small>{{ __('panel/setting.file_manager_enable_crop_desc') }}</small></div>
          </div>
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
</div>
