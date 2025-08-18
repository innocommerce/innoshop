<!-- AI Settings -->
<div class="tab-pane fade" id="tab-setting-ai">
  <div class="container">
    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="aiSettingsTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="ai-models-tab" 
                type="button" role="tab" aria-controls="ai-models" aria-selected="true">
          {{ __('panel/setting.ai_model_config') }}
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="ai-content-tab" 
                type="button" role="tab" aria-controls="ai-content" aria-selected="false">
          {{ __('panel/setting.ai_content_generation') }}
        </button>
      </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="aiSettingsTabContent">
      <!-- AI Models Configuration Tab -->
      <div class="tab-pane fade show active" id="ai-models" role="tabpanel" aria-labelledby="ai-models-tab">
        <div class="row">
          <div class="col-lg-6">
            <x-common-form-select title="{{ __('panel/setting.ai_model') }}" name="ai_model"
                                :options="$ai_models" key="code" label="name" :emptyOption="false"
                                value="{{ old('ai_model', system_setting('ai_model')) }}" required />
          </div>

        </div>
        
        <!-- API Configuration Section -->
            <div class="row mt-4">
              <div class="col-12">
                <h5 class="mb-3">{{ __('panel/setting.api_configuration') }}</h5>
                
                <!-- OpenAI Configuration -->
                <div class="card mb-4">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">OpenAI</h6>
                    <x-common-form-model-switch name="openai_enabled" 
                                               :value="old('openai_enabled', system_setting('openai_enabled', false))" 
                                               id="openai_enabled" />
                  </div>
                  <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('panel/setting.openai_description') }} <a href="https://platform.openai.com/api-keys" target="_blank">{{ __('panel/setting.get_api_key') }}</a></p>
                    <div class="mb-3">
                      <x-common-form-input title="{{ __('panel/setting.openai_api_key') }}" 
                                         name="openai_api_key" 
                                         value="{{ old('openai_api_key', system_setting('openai_api_key')) }}"
                                         placeholder="sk-..." />
                    </div>
                    <div class="mb-3">
                      <x-common-form-input title="{{ __('panel/setting.openai_proxy_url') }}" 
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
                    <p class="text-muted small mb-3">{{ __('panel/setting.deepseek_description') }} <a href="https://platform.deepseek.com/api-keys" target="_blank">{{ __('panel/setting.get_api_key') }}</a></p>
                    <div class="mb-3">
                      <x-common-form-input title="{{ __('panel/setting.deepseek_api_key') }}" 
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
                    <p class="text-muted small mb-3">{{ __('panel/setting.kimi_description') }} <a href="https://platform.moonshot.cn/console/api-keys" target="_blank">{{ __('panel/setting.get_api_key') }}</a></p>
                    <div class="mb-3">
                      <x-common-form-input title="{{ __('panel/setting.kimi_api_key') }}" 
                                         name="kimi_api_key" 
                                         value="{{ old('kimi_api_key', system_setting('kimi_api_key')) }}"
                                         placeholder="sk-..." />
                    </div>
                  </div>
                </div>

                <!-- Doubao Configuration -->
                <div class="card mb-4">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">{{ __('panel/setting.doubao_title') }}</h6>
                    <x-common-form-model-switch name="doubao_enabled" 
                                               :value="old('doubao_enabled', system_setting('doubao_enabled', false))" 
                                               id="doubao_enabled" />
                  </div>
                  <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('panel/setting.doubao_description') }} <a href="https://console.volcengine.com/ark/region:ark+cn-beijing/apiKey" target="_blank">{{ __('panel/setting.get_api_key') }}</a></p>
                    <div class="mb-3">
                      <x-common-form-input title="{{ __('panel/setting.doubao_api_key') }}" 
                                         name="doubao_api_key" 
                                         value="{{ old('doubao_api_key', system_setting('doubao_api_key')) }}"
                                         placeholder="..." />
                    </div>
                  </div>
                </div>

                <!-- Qianwen Configuration -->
                <div class="card mb-4">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">{{ __('panel/setting.qianwen_title') }}</h6>
                    <x-common-form-model-switch name="qianwen_enabled" 
                                               :value="old('qianwen_enabled', system_setting('qianwen_enabled', false))" 
                                               id="qianwen_enabled" />
                  </div>
                  <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('panel/setting.qianwen_description') }} <a href="https://bailian.console.aliyun.com/?apiKey=1#/api-key" target="_blank">{{ __('panel/setting.get_api_key') }}</a></p>
                    <div class="mb-3">
                      <x-common-form-input title="{{ __('panel/setting.qianwen_api_key') }}" 
                                         name="qianwen_api_key" 
                                         value="{{ old('qianwen_api_key', system_setting('qianwen_api_key')) }}"
                                         placeholder="sk-..." />
                    </div>
                  </div>
                </div>

                <!-- Hunyuan Configuration -->
                <div class="card mb-4">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">{{ __('panel/setting.hunyuan_title') }}</h6>
                    <x-common-form-model-switch name="hunyuan_enabled" 
                                               :value="old('hunyuan_enabled', system_setting('hunyuan_enabled', false))" 
                                               id="hunyuan_enabled" />
                  </div>
                  <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('panel/setting.hunyuan_description') }} <a href="https://console.cloud.tencent.com/cam/capi" target="_blank">{{ __('panel/setting.get_api_key') }}</a></p>
                    <div class="mb-3">
                      <x-common-form-input title="{{ __('panel/setting.hunyuan_api_key') }}" 
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
                    <p class="text-muted small mb-3">{{ __('panel/setting.anthropic_description') }} <a href="https://console.anthropic.com/settings/keys" target="_blank">{{ __('panel/setting.get_api_key') }}</a></p>
                    <div class="mb-3">
                      <x-common-form-input title="{{ __('panel/setting.anthropic_api_key') }}" 
                                         name="anthropic_api_key" 
                                         value="{{ old('anthropic_api_key', system_setting('anthropic_api_key')) }}"
                                         placeholder="sk-ant-..." />
                    </div>
                  </div>
                </div>
              </div>
            </div>
      </div>

      <!-- AI Content Generation Tab -->
      <div class="tab-pane fade" id="ai-content" role="tabpanel" aria-labelledby="ai-content-tab">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title mb-0">{{ __('panel/setting.content_generation_settings') }}</h5>
              </div>
              <div class="card-body">
                @foreach($ai_prompts as $prompt)
                  <x-common-form-textarea title="{{ __('panel/setting.'.$prompt) }}" name="{{ $prompt }}"
                                      value="{{ old($prompt, system_setting($prompt)) }}"
                                      placeholder="{{ __('panel/setting.'.$prompt) }}"
                                      rows="4"/>
                @endforeach
              </div>
            </div>
          </div>
        </div>

        <!-- Content Quality Settings -->
        <div class="row mt-4">
          <div class="col-lg-6">
            <x-common-form-select title="{{ __('panel/setting.content_quality') }}" name="ai_content_quality"
                                :options="[
                                  ['value' => 'low', 'label' => __('panel/setting.quality_low')],
                                  ['value' => 'medium', 'label' => __('panel/setting.quality_medium')],
                                  ['value' => 'high', 'label' => __('panel/setting.quality_high')]
                                ]" key="value" label="label" :emptyOption="false"
                                value="{{ old('ai_content_quality', system_setting('ai_content_quality', 'medium')) }}" />
          </div>
          <div class="col-lg-6">
            <x-common-form-input title="{{ __('panel/setting.max_tokens') }}" 
                               name="ai_max_tokens" type="number"
                               value="{{ old('ai_max_tokens', system_setting('ai_max_tokens', 1000)) }}"
                               placeholder="1000" />
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// AI Settings Tab 切换逻辑
document.addEventListener('DOMContentLoaded', function() {
    const aiModelsTab = document.getElementById('ai-models-tab');
    const aiContentTab = document.getElementById('ai-content-tab');
    const aiModelsPane = document.getElementById('ai-models');
    const aiContentPane = document.getElementById('ai-content');
    
    function switchTab(activeTab, activePane, inactiveTab, inactivePane) {
        // 更新 tab 状态
        activeTab.classList.add('active');
        activeTab.setAttribute('aria-selected', 'true');
        inactiveTab.classList.remove('active');
        inactiveTab.setAttribute('aria-selected', 'false');
        
        // 更新 pane 状态
        activePane.classList.add('show', 'active');
        inactivePane.classList.remove('show', 'active');
    }
    
    aiModelsTab.addEventListener('click', function(e) {
        e.preventDefault();
        switchTab(aiModelsTab, aiModelsPane, aiContentTab, aiContentPane);
    });
    
    aiContentTab.addEventListener('click', function(e) {
        e.preventDefault();
        switchTab(aiContentTab, aiContentPane, aiModelsTab, aiModelsPane);
    });
});
</script>
