<div class="row">
  <div class="col-6 col-md-3">
    <x-common-form-image title="{{ __('panel/setting.front_logo') }}" name="front_logo"
                         value="{{ old('front_logo', system_setting('front_logo')) }}" />
  </div>
  <div class="col-6 col-md-3">
    <x-common-form-image title="{{ __('panel/setting.backend_logo') }}" name="panel_logo"
                         value="{{ old('panel_logo', system_setting('panel_logo')) }}" />
  </div>
  <div class="col-6 col-md-3">
    <x-common-form-image title="{{ __('panel/setting.placeholder') }}" name="placeholder"
                         value="{{ old('placeholder', system_setting('placeholder')) }}" />
  </div>
  <div class="col-6 col-md-3">
    <x-common-form-image title="{{ __('panel/setting.favicon') }}" name="favicon"
                         value="{{ old('favicon', system_setting('favicon')) }}" />
  </div>
</div>

<x-common-form-input title="{{ __('panel/setting.shop_address') }}" name="address" value="{{ old('address', system_setting('address')) }}" placeholder="{{ __('panel/setting.shop_address') }}" />

<x-common-form-input title="{{ __('panel/setting.telephone') }}" name="telephone" value="{{ old('telephone', system_setting('telephone')) }}" placeholder="{{ __('panel/setting.telephone') }}" />

<x-common-form-input title="{{ __('panel/setting.email') }}" name="email" value="{{ old('email', system_setting('email')) }}" placeholder="{{ __('panel/setting.email') }}" />

<x-common-form-input title="{{ __('panel/setting.meta_title') }}" name="meta_title" :value="old('meta_keywords', system_setting('meta_title'))" :multiple="true"  />

<x-common-form-input title="{{ __('panel/setting.meta_keywords') }}" :multiple="true" name="meta_keywords" :value="old('meta_keywords', system_setting('meta_keywords'))" placeholder="{{ __('panel/setting.meta_keywords') }}" />

<x-common-form-textarea title="{{ __('panel/setting.meta_description') }}" :multiple="true" name="meta_description" :value="old('meta_description', system_setting('meta_description'))" placeholder="{{ __('panel/setting.meta_description') }}" />
