<!-- Tools Settings -->
<div class="tab-pane fade" id="tab-setting-tools">
  <div class="container">
    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="toolsSettingsTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="ai-tools-tab" 
                type="button" role="tab" aria-controls="ai-tools" aria-selected="true"
                data-target="#ai-tools">
          {{ __('panel/setting_ai.ai_setting') }}
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="geolite2-tools-tab" 
                type="button" role="tab" aria-controls="geolite2-tools" aria-selected="false"
                data-target="#geolite2-tools">
          {{ __('panel/setting_geolite2.geolite2_setting') }}
        </button>
      </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="toolsSettingsTabContent">
      <!-- AI Tools Tab -->
      <div class="tab-pane fade show active" id="ai-tools" role="tabpanel" aria-labelledby="ai-tools-tab">
        <!-- AI Models Configuration -->
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">{{ __('panel/setting_ai.ai_model_config') }}</h5>
          </div>
          <div class="card-body">
            @if(empty($ai_models))
              <div class="alert alert-warning" role="alert">
                <h5 class="alert-heading">
                  <i class="bi bi-exclamation-triangle me-2"></i>{{ __('panel/setting_ai.ai_model_no_available') }}
                </h5>
                <p class="mb-0">{{ __('panel/setting_ai.ai_model_no_available_desc') }}</p>
              </div>
            @else
              <div class="row">
                <div class="col-lg-6">
                  <x-common-form-select title="{{ __('panel/setting_ai.ai_model') }}" name="ai_model"
                                      :options="$ai_models" key="code" label="name" :emptyOption="false"
                                      value="{{ old('ai_model', system_setting('ai_model')) }}" required />
                </div>
              </div>
            @endif
            
            <!-- API Configuration Section -->
            <div class="row mt-4">
              <div class="col-12">
                <h5 class="mb-3">{{ __('panel/setting_ai.api_configuration') }}</h5>
                
                <!-- OpenAI Configuration -->
                <div class="card mb-4">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">OpenAI</h6>
                    <x-common-form-model-switch name="openai_enabled" 
                                               :value="old('openai_enabled', system_setting('openai_enabled', false))" 
                                               id="openai_enabled" />
                  </div>
                  <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('panel/setting_ai.openai_description') }} <a href="https://platform.openai.com/api-keys" target="_blank">{{ __('panel/setting_ai.get_api_key') }}</a></p>
                    <div class="mb-3">
                      <x-common-form-input title="{{ __('panel/setting_ai.openai_api_key') }}" 
                                         name="openai_api_key" 
                                         value="{{ old('openai_api_key', system_setting('openai_api_key')) }}"
                                         placeholder="sk-..." />
                    </div>
                    <div class="mb-3">
                      <x-common-form-input title="{{ __('panel/setting_ai.openai_proxy_url') }}" 
                                         name="openai_proxy_url" 
                                         value="{{ old('openai_proxy_url', system_setting('openai_proxy_url')) }}"
                                         placeholder="https://api.openai.com/v1/" />
                    </div>
                  </div>
                </div>

                <!-- DeepSeek Configuration -->
                <div class="card mb-4">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">DeepSeek</h6>
                    <x-common-form-model-switch name="deepseek_enabled" 
                                               :value="old('deepseek_enabled', system_setting('deepseek_enabled', false))" 
                                               id="deepseek_enabled" />
                  </div>
                  <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('panel/setting_ai.deepseek_description') }} <a href="https://platform.deepseek.com/api-keys" target="_blank">{{ __('panel/setting_ai.get_api_key') }}</a></p>
                    <div class="mb-3">
                      <x-common-form-input title="{{ __('panel/setting_ai.deepseek_api_key') }}" 
                                         name="deepseek_api_key" 
                                         value="{{ old('deepseek_api_key', system_setting('deepseek_api_key')) }}"
                                         placeholder="sk-..." />
                    </div>
                  </div>
                </div>

                <!-- Kimi Configuration -->
                <div class="card mb-4">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">Kimi (Moonshot)</h6>
                    <x-common-form-model-switch name="kimi_enabled" 
                                               :value="old('kimi_enabled', system_setting('kimi_enabled', false))" 
                                               id="kimi_enabled" />
                  </div>
                  <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('panel/setting_ai.kimi_description') }} <a href="https://platform.moonshot.cn/console/api-keys" target="_blank">{{ __('panel/setting_ai.get_api_key') }}</a></p>
                    <div class="mb-3">
                      <x-common-form-input title="{{ __('panel/setting_ai.kimi_api_key') }}" 
                                         name="kimi_api_key" 
                                         value="{{ old('kimi_api_key', system_setting('kimi_api_key')) }}"
                                         placeholder="sk-..." />
                    </div>
                  </div>
                </div>

                <!-- Doubao Configuration -->
                <div class="card mb-4">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">{{ __('panel/setting_ai.doubao_title') }}</h6>
                    <x-common-form-model-switch name="doubao_enabled" 
                                               :value="old('doubao_enabled', system_setting('doubao_enabled', false))" 
                                               id="doubao_enabled" />
                  </div>
                  <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('panel/setting_ai.doubao_description') }} <a href="https://console.volcengine.com/ark/region:ark+cn-beijing/apiKey" target="_blank">{{ __('panel/setting_ai.get_api_key') }}</a></p>
                    <div class="mb-3">
                      <x-common-form-input title="{{ __('panel/setting_ai.doubao_api_key') }}" 
                                         name="doubao_api_key" 
                                         value="{{ old('doubao_api_key', system_setting('doubao_api_key')) }}"
                                         placeholder="..." />
                    </div>
                  </div>
                </div>

                <!-- Qianwen Configuration -->
                <div class="card mb-4">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">{{ __('panel/setting_ai.qianwen_title') }}</h6>
                    <x-common-form-model-switch name="qianwen_enabled" 
                                               :value="old('qianwen_enabled', system_setting('qianwen_enabled', false))" 
                                               id="qianwen_enabled" />
                  </div>
                  <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('panel/setting_ai.qianwen_description') }} <a href="https://bailian.console.aliyun.com/?apiKey=1#/api-key" target="_blank">{{ __('panel/setting_ai.get_api_key') }}</a></p>
                    <div class="mb-3">
                      <x-common-form-input title="{{ __('panel/setting_ai.qianwen_api_key') }}" 
                                         name="qianwen_api_key" 
                                         value="{{ old('qianwen_api_key', system_setting('qianwen_api_key')) }}"
                                         placeholder="sk-..." />
                    </div>
                  </div>
                </div>

                <!-- Hunyuan Configuration -->
                <div class="card mb-4">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">{{ __('panel/setting_ai.hunyuan_title') }}</h6>
                    <x-common-form-model-switch name="hunyuan_enabled" 
                                               :value="old('hunyuan_enabled', system_setting('hunyuan_enabled', false))" 
                                               id="hunyuan_enabled" />
                  </div>
                  <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('panel/setting_ai.hunyuan_description') }} <a href="https://console.cloud.tencent.com/cam/capi" target="_blank">{{ __('panel/setting_ai.get_api_key') }}</a></p>
                    <div class="mb-3">
                      <x-common-form-input title="{{ __('panel/setting_ai.hunyuan_api_key') }}" 
                                         name="hunyuan_api_key" 
                                         value="{{ old('hunyuan_api_key', system_setting('hunyuan_api_key')) }}"
                                         placeholder="..." />
                    </div>
                  </div>
                </div>

                <!-- Anthropic Configuration -->
                <div class="card mb-4">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">Anthropic (Claude)</h6>
                    <x-common-form-model-switch name="anthropic_enabled" 
                                               :value="old('anthropic_enabled', system_setting('anthropic_enabled', false))" 
                                               id="anthropic_enabled" />
                  </div>
                  <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('panel/setting_ai.anthropic_description') }} <a href="https://console.anthropic.com/settings/keys" target="_blank">{{ __('panel/setting_ai.get_api_key') }}</a></p>
                    <div class="mb-3">
                      <x-common-form-input title="{{ __('panel/setting_ai.anthropic_api_key') }}" 
                                         name="anthropic_api_key" 
                                         value="{{ old('anthropic_api_key', system_setting('anthropic_api_key')) }}"
                                         placeholder="sk-ant-..." />
                    </div>
                  </div>
                </div>

                <!-- GLM Configuration -->
                <div class="card mb-4">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">GLM ({{ __('panel/setting_ai.zhipu') }})</h6>
                    <x-common-form-model-switch name="glm_enabled" 
                                               :value="old('glm_enabled', system_setting('glm_enabled', false))" 
                                               id="glm_enabled" />
                  </div>
                  <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('panel/setting_ai.glm_description') }} <a href="https://open.bigmodel.cn/usercenter/apikeys" target="_blank">{{ __('panel/setting_ai.get_api_key') }}</a></p>
                    <div class="mb-3">
                      <x-common-form-input title="{{ __('panel/setting_ai.glm_api_key') }}" 
                                         name="glm_api_key" 
                                         value="{{ old('glm_api_key', system_setting('glm_api_key')) }}"
                                         placeholder="..." />
                    </div>
                  </div>
                </div>

                <!-- MiniMax Configuration -->
                <div class="card mb-4">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">MiniMax</h6>
                    <x-common-form-model-switch name="minimax_enabled" 
                                               :value="old('minimax_enabled', system_setting('minimax_enabled', false))" 
                                               id="minimax_enabled" />
                  </div>
                  <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('panel/setting_ai.minimax_description') }} <a href="https://platform.minimaxi.com/document/Key%20management" target="_blank">{{ __('panel/setting_ai.get_api_key') }}</a></p>
                    <div class="mb-3">
                      <x-common-form-input title="{{ __('panel/setting_ai.minimax_api_key') }}" 
                                         name="minimax_api_key" 
                                         value="{{ old('minimax_api_key', system_setting('minimax_api_key')) }}"
                                         placeholder="..." />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- AI Content Generation -->
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">{{ __('panel/setting_ai.content_generation_settings') }}</h5>
          </div>
          <div class="card-body">
            @foreach($ai_prompts as $prompt)
              <x-common-form-textarea title="{{ __('panel/setting_ai.'.$prompt) }}" name="{{ $prompt }}"
                                  value="{{ old($prompt, system_setting($prompt)) }}"
                                  placeholder="{{ __('panel/setting_ai.'.$prompt) }}"
                                  rows="4"/>
            @endforeach

            <!-- Content Quality Settings -->
            <div class="row mt-4">
              <div class="col-lg-6">
                <x-common-form-select title="{{ __('panel/setting_ai.content_quality') }}" name="ai_content_quality"
                                    :options="[
                                      ['value' => 'low', 'label' => __('panel/setting_ai.quality_low')],
                                      ['value' => 'medium', 'label' => __('panel/setting_ai.quality_medium')],
                                      ['value' => 'high', 'label' => __('panel/setting_ai.quality_high')]
                                    ]" key="value" label="label" :emptyOption="false"
                                    value="{{ old('ai_content_quality', system_setting('ai_content_quality', 'medium')) }}" />
              </div>
              <div class="col-lg-6">
                <x-common-form-input title="{{ __('panel/setting_ai.max_tokens') }}" 
                                   name="ai_max_tokens" type="number"
                                   value="{{ old('ai_max_tokens', system_setting('ai_max_tokens', 1000)) }}"
                                   placeholder="1000" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- GeoLite2 Tools Tab -->
      <div class="tab-pane fade" id="geolite2-tools" role="tabpanel" aria-labelledby="geolite2-tools-tab">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">{{ __('panel/setting_geolite2.geolite2_setting') }}</h5>
            <p class="text-muted small mb-0">{{ __('panel/setting_geolite2.geolite2_setting_desc') }}</p>
          </div>
          <div class="card-body">
            <!-- Database Info -->
            <div class="row mb-3">
              <div class="col-md-12">
                <div class="card bg-light">
                  <div class="card-body">
                    <h6 class="mb-3">{{ __('panel/setting_geolite2.geolite2_database_info') }}</h6>
                    <div class="row mb-2">
                      <div class="col-md-3">
                        <strong>{{ __('panel/setting_geolite2.database_status') }}:</strong>
                        <span id="geolite2-status" class="ms-2">
                          @if($geolite2_info['exists'])
                            <span class="badge bg-success">{{ __('panel/setting_geolite2.database_exists') }}</span>
                          @else
                            <span class="badge bg-warning">{{ __('panel/setting_geolite2.database_not_exists') }}</span>
                          @endif
                        </span>
                      </div>
                      <div class="col-md-3">
                        <strong>{{ __('panel/setting_geolite2.database_size') }}:</strong>
                        <span id="geolite2-size" class="ms-2">{{ $geolite2_info['size_formatted'] }}</span>
                      </div>
                      <div class="col-md-3">
                        <strong>{{ __('panel/setting_geolite2.database_updated') }}:</strong>
                        <span id="geolite2-modified" class="ms-2">{{ $geolite2_info['modified_formatted'] }}</span>
                      </div>
                      <div class="col-md-3">
                        <strong>{{ __('panel/setting_geolite2.database_version') }}:</strong>
                        <span id="geolite2-version" class="ms-2">{{ $geolite2_info['version'] ?: '-' }}</span>
                      </div>
                    </div>
                    <div class="row mt-3">
                      <div class="col-md-12">
                        <strong>{{ __('panel/setting_geolite2.database_path') }}:</strong>
                        <code id="geolite2-path" class="ms-2 small d-block mt-1">{{ $geolite2_info['path'] }}</code>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Download Section -->
            <div class="row">
              <div class="col-md-12">
                <h6 class="mb-3">{{ __('panel/setting_geolite2.geolite2_download') }}</h6>
                <div class="input-group mb-3">
                  <input
                    type="text"
                    class="form-control"
                    id="geolite2-download-url"
                    value="https://res.innoshop.net/GeoLite2-City.mmdb"
                    placeholder="{{ __('panel/setting_geolite2.geolite2_download_url_placeholder') }}"
                  />
                  <button type="button" class="btn btn-primary" id="download-geolite2-btn" onclick="downloadGeoLite2()">
                    <i class="bi bi-download"></i> {{ __('panel/setting_geolite2.download_geolite2_database') }}
                  </button>
                </div>
                <div class="text-secondary">
                  <small>{{ __('panel/setting_geolite2.geolite2_download_desc') }}</small>
                </div>
              </div>
            </div>

            <!-- Refresh Button -->
            <div class="row mt-3">
              <div class="col-md-12">
                <button type="button" class="btn btn-secondary" onclick="refreshGeoLite2Info()">
                  <i class="bi bi-arrow-clockwise"></i> {{ __('panel/setting_geolite2.refresh_info') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('footer')
<script>
function downloadGeoLite2() {
  const btn = document.getElementById('download-geolite2-btn');
  const originalText = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="bi bi-hourglass-split"></i> {{ __('panel/setting_geolite2.downloading') }}...';

  const url = document.getElementById('geolite2-download-url').value;

  axios.post('{{ panel_route("settings.download_geolite2") }}', {
    url: url
  })
  .then(function(response) {
    if (response.data.success) {
      alert(response.data.message);
      refreshGeoLite2Info();
    } else {
      const errorMsg = response.data.message || '{{ __('panel/setting_geolite2.download_failed') }}';
      const message = errorMsg.replace(/\n/g, '<br>');
      const alertDiv = document.createElement('div');
      alertDiv.className = 'alert alert-danger';
      alertDiv.innerHTML = '<strong>{{ __('panel/setting_geolite2.download_failed') }}</strong><br>' + message;
      alertDiv.style.position = 'fixed';
      alertDiv.style.top = '20px';
      alertDiv.style.right = '20px';
      alertDiv.style.zIndex = '9999';
      alertDiv.style.minWidth = '400px';
      alertDiv.style.maxWidth = '600px';
      document.body.appendChild(alertDiv);
      setTimeout(function() {
        alertDiv.remove();
      }, 10000);
    }
  })
  .catch(function(error) {
    const errorMsg = error.response?.data?.message || error.message;
    const message = errorMsg.replace(/\n/g, '<br>');
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger';
    alertDiv.innerHTML = '<strong>{{ __('panel/setting_geolite2.download_failed') }}</strong><br>' + message;
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '400px';
    alertDiv.style.maxWidth = '600px';
    document.body.appendChild(alertDiv);
    setTimeout(function() {
      alertDiv.remove();
    }, 10000);
  })
  .finally(function() {
    btn.disabled = false;
    btn.innerHTML = originalText;
  });
}

function refreshGeoLite2Info() {
  axios.get('{{ panel_route("settings.geolite2_info") }}', {
    params: {
      _t: new Date().getTime()
    }
  })
  .then(function(response) {
    if (response.data.success) {
      const info = response.data.data;
      document.getElementById('geolite2-status').innerHTML = info.exists
        ? '<span class="badge bg-success">{{ __('panel/setting_geolite2.database_exists') }}</span>'
        : '<span class="badge bg-warning">{{ __('panel/setting_geolite2.database_not_exists') }}</span>';
      document.getElementById('geolite2-size').textContent = info.size_formatted;
      document.getElementById('geolite2-modified').textContent = info.modified_formatted;
      document.getElementById('geolite2-version').textContent = info.version || '-';
      document.getElementById('geolite2-path').textContent = info.path;
    }
  })
  .catch(function(error) {
    console.error('Failed to refresh GeoLite2 info:', error);
  });
}
</script>
@endpush

