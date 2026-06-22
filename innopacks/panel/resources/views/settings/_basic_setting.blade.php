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
	    <x-common-form-input title="{{ __('panel/setting.store_name') }}" name="store_name"
                       :value="old('store_name', system_setting('store_name'))"
                       :multiple="true"
                       placeholder="{{ __('panel/setting.store_name') }}"
                       description="{{ __('panel/setting.store_name_desc') }}" />
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
                       :value="old('meta_title', system_setting('meta_title'))"
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

	<!-- Robots & Llms Settings -->
	<div class="card mb-4">
	  <div class="card-header">
	    <h5 class="card-title mb-0">{{ __('panel/setting.robots_llms_settings') }}</h5>
	    <p class="text-muted small mb-0">{{ __('panel/setting.robots_llms_settings_desc') }}</p>
	  </div>
	  <div class="card-body">
	    <div class="mb-3">
	      <label class="form-label">{{ __('panel/setting.robots_custom_rules') }}</label>
	      <div class="input-group mb-1">
	        <textarea rows="6" name="robots_custom_rules" class="form-control" id="robots_custom_rules">{{ old('robots_custom_rules', system_setting('robots_custom_rules')) }}</textarea>
	        <button type="button" class="btn btn-outline-primary" onclick="generateTxtPreview('robots')">
	          <i class="bi bi-eye me-1"></i>{{ __('panel/setting.preview_generate') }}
	        </button>
	      </div>
	      <div class="mt-2 d-flex flex-wrap gap-1 align-items-center">
	        <span class="text-muted small me-1">{{ __('panel/setting.robots_block_crawler') }}:</span>
	        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertCrawlerBlock('Baiduspider')">百度</button>
	        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertCrawlerBlock('Googlebot')">Google</button>
	        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertCrawlerBlock('bingbot')">Bing</button>
	        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertCrawlerBlock('YandexBot')">Yandex</button>
	        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertCrawlerBlock('SemrushBot')">Semrush</button>
	        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertCrawlerBlock('AhrefsBot')">Ahrefs</button>
	        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertCrawlerBlock('Bytespider')">字节跳动</button>
	      </div>
	      <div class="mt-2 text-muted small"><i class="bi bi-info-circle me-1"></i>{!! __('panel/setting.robots_custom_rules_desc') !!}</div>
	    </div>

	    <div class="mb-3">
	      <label class="form-label">{{ __('panel/setting.llms_custom_content') }}</label>
	      <div class="input-group mb-1">
	        <textarea rows="4" name="llms_custom_content" class="form-control" id="llms_custom_content">{{ old('llms_custom_content', system_setting('llms_custom_content')) }}</textarea>
	        <button type="button" class="btn btn-outline-primary" onclick="generateTxtPreview('llms')">
	          <i class="bi bi-eye me-1"></i>{{ __('panel/setting.preview_generate') }}
	        </button>
	      </div>
	      <div class="mt-2 text-muted small"><i class="bi bi-info-circle me-1"></i>{!! __('panel/setting.llms_custom_content_desc') !!}</div>
	    </div>
	  </div>
	</div>

	<!-- Preview Modal -->
	<div class="modal fade" id="txtPreviewModal" tabindex="-1">
	  <div class="modal-dialog modal-lg modal-dialog-centered">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h5 class="modal-title" id="txtPreviewTitle"></h5>
	        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
	      </div>
	      <div class="modal-body">
	        <pre id="txtPreviewContent" class="bg-light p-3 rounded" style="max-height:60vh;overflow:auto;white-space:pre-wrap;word-break:break-all;font-size:13px;"></pre>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('panel/setting.preview_close') }}</button>
	        <button type="button" class="btn btn-primary" id="txtPreviewUse">{{ __('panel/setting.preview_use') }}</button>
	      </div>
	    </div>
	  </div>
	</div>
</div>

@push('footer')
<script>
function generateTxtPreview(type) {
  var loadIdx = layer.load(2, {shade: [0.3, '#fff']});
  axios.get('{{ panel_route("settings.generate_txt") }}?type=' + type)
    .then(function(res) {
      layer.close(loadIdx);
      if (res.success) {
        document.getElementById('txtPreviewContent').textContent = res.data.content;
        document.getElementById('txtPreviewTitle').textContent = type === 'robots' ? 'robots.txt' : 'llms.txt';
        document.getElementById('txtPreviewUse').onclick = function() {
          var fieldId = type === 'robots' ? 'robots_custom_rules' : 'llms_custom_content';
          document.getElementById(fieldId).value = res.data.content;
          bootstrap.Modal.getInstance(document.getElementById('txtPreviewModal')).hide();
        };
        new bootstrap.Modal(document.getElementById('txtPreviewModal')).show();
      }
    })
    .catch(function(err) {
      layer.close(loadIdx);
      inno.alert(err.response?.data?.message || 'Error');
    });
}

function insertCrawlerBlock(agent) {
  var ta = document.getElementById('robots_custom_rules');
  var val = ta.value.trimEnd();
  var block = (val ? '\n\n' : '') + 'User-agent: ' + agent + '\nDisallow: /';
  ta.value = val + block;
}
</script>
@endpush
