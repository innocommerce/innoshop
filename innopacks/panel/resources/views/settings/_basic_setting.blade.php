<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
?>

<!-- Frontend Settings -->
<div class="tab-pane fade show active" id="tab-setting-basics">
	<div class="card mb-4">
	  <div class="card-header">
	    <h5 class="card-title mb-0">{{ __('panel/setting.frontend_settings') }}</h5>
	    <p class="text-muted small mb-0">{{ __('panel/setting.frontend_settings_desc') }}</p>
	  </div>
	  <div class="card-body">
	    <div class="row">
	      <div class="col-6 col-md-4">
	        <x-common-form-image title="{{ __('panel/setting.front_logo') }}" name="front_logo"
	                           value="{{ old('front_logo', system_setting('front_logo')) }}"
	                           description="{{ __('panel/setting.front_logo_desc') }}" />
	      </div>
	      <div class="col-6 col-md-4">
	        <x-common-form-image title="{{ __('panel/setting.placeholder') }}" name="placeholder"
	                           value="{{ old('placeholder', system_setting('placeholder')) }}"
	                           description="{{ __('panel/setting.placeholder_desc') }}" />
	      </div>
	      <div class="col-6 col-md-4">
	        <x-common-form-image title="{{ __('panel/setting.favicon') }}" name="favicon"
	                           value="{{ old('favicon', system_setting('favicon')) }}"
	                           description="{{ __('panel/setting.favicon_desc') }}" />
	      </div>
	    </div>
	  </div>
	</div>

	<!-- Backend Settings -->
	<div class="card mb-4">
	  <div class="card-header">
	    <h5 class="card-title mb-0">{{ __('panel/setting.backend_settings') }}</h5>
	    <p class="text-muted small mb-0">{{ __('panel/setting.backend_settings_desc') }}</p>
	  </div>
	  <div class="card-body">
	    <div class="row">
	      <div class="col-6 col-md-6">
	        <x-common-form-image title="{{ __('panel/setting.backend_logo') }}" name="panel_logo"
	                           value="{{ old('panel_logo', system_setting('panel_logo')) }}"
	                           description="{{ __('panel/setting.backend_logo_desc') }}" />
	      </div>
	      <div class="col-6 col-md-6">
	        <x-common-form-image title="{{ __('panel/setting.backend_icon_logo') }}" name="panel_icon_logo"
	                           value="{{ old('panel_icon_logo', system_setting('panel_icon_logo')) }}"
	                           description="{{ __('panel/setting.backend_icon_logo_desc') }}" />
	      </div>
	    </div>
	  </div>
	</div>

	<!-- Contact Info -->
	<div class="card mb-4">
	  <div class="card-header">
	    <h5 class="card-title mb-0">{{ __('panel/setting.contact_info') }}</h5>
	    <p class="text-muted small mb-0">{{ __('panel/setting.contact_info_desc') }}</p>
	  </div>
	  <div class="card-body">
	    <x-common-form-input title="{{ __('panel/setting.shop_address') }}" name="address"
                       :value="old('address', system_setting('address'))"
                       :multiple="true"
                       placeholder="{{ __('panel/setting.shop_address') }}" />

	    <x-common-form-input title="{{ __('panel/setting.telephone') }}" name="telephone"
                       value="{{ old('telephone', system_setting('telephone')) }}"
                       placeholder="{{ __('panel/setting.telephone') }}" />

	    <x-common-form-input title="{{ __('panel/setting.email') }}" name="email"
                       value="{{ old('email', system_setting('email')) }}"
                       placeholder="{{ __('panel/setting.email') }}" />
	  </div>
	</div>

	<!-- SEO Settings -->
	<div class="card mb-4">
	  <div class="card-header">
	    <h5 class="card-title mb-0">{{ __('panel/setting.seo_settings') }}</h5>
	    <p class="text-muted small mb-0">{{ __('panel/setting.seo_settings_desc') }}</p>
	  </div>
	  <div class="card-body">
	    <x-common-form-input title="{{ __('panel/setting.meta_title') }}" name="meta_title"
                       :value="old('meta_keywords', system_setting('meta_title'))"
                       :multiple="true" />

	    <x-common-form-input title="{{ __('panel/setting.meta_keywords') }}" :multiple="true"
                       name="meta_keywords"
                       :value="old('meta_keywords', system_setting('meta_keywords'))"
                       placeholder="{{ __('panel/setting.meta_keywords') }}" />

	    <x-common-form-textarea title="{{ __('panel/setting.meta_description') }}" name="meta_description" :multiple="true"
                          :value="old('meta_description', system_setting('meta_description'))"
                          placeholder="{{ __('panel/setting.meta_description') }}" />
	  </div>
	</div>
</div>
