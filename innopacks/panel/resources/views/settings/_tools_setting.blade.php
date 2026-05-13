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

        <!-- AI Provider Management -->
        <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('panel/setting_ai.provider_management') }}</h5>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="aiAddProvider()">
              <i class="bi bi-plus-lg me-1"></i>{{ __('panel/setting_ai.add_provider') }}
            </button>
          </div>
          <div class="card-body">
            <p class="text-muted small mb-3">{{ __('panel/setting_ai.provider_management_desc') }}</p>
            <div id="ai-providers-list">
              <!-- Rendered by JS -->
            </div>
            <input type="hidden" name="ai_providers" id="ai_providers_input" value="{{ old('ai_providers', is_string($ai_providers ?? '') ? $ai_providers : json_encode($ai_providers ?? [])) }}" />
          </div>
        </div>

        <!-- Capability Selection -->
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">{{ __('panel/setting_ai.capability_selection') }}</h5>
          </div>
          <div class="card-body">
            <p class="text-muted small mb-3">{{ __('panel/setting_ai.capability_selection_desc') }}</p>
            <div class="row">
              <div class="col-lg-6">
                <x-common-form-select title="{{ __('panel/setting_ai.text_generation_provider') }}" name="ai_text_provider"
                                    :options="$ai_models ?? []" key="code" label="name" :emptyOption="true"
                                    value="{{ old('ai_text_provider', system_setting('ai_text_provider') ?: system_setting('ai_model')) }}" />
              </div>
              <div class="col-lg-6">
                <x-common-form-input title="{{ __('panel/setting_ai.text_model_override') }}"
                                   name="ai_text_model"
                                   value="{{ old('ai_text_model', system_setting('ai_text_model')) }}"
                                   placeholder="{{ __('panel/setting_ai.model_override_placeholder') }}" />
              </div>
            </div>
            <div class="row mt-3">
              <div class="col-lg-6">
                <x-common-form-select title="{{ __('panel/setting_ai.image_generation_provider') }}" name="ai_image_provider"
                                    :options="$ai_models ?? []" key="code" label="name" :emptyOption="true"
                                    value="{{ old('ai_image_provider', system_setting('ai_image_provider')) }}" />
              </div>
              <div class="col-lg-6">
                <x-common-form-input title="{{ __('panel/setting_ai.image_model_override') }}"
                                   name="ai_image_model"
                                   value="{{ old('ai_image_model', system_setting('ai_image_model')) }}"
                                   placeholder="{{ __('panel/setting_ai.model_override_placeholder') }}" />
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
// Provider Management
const aiPresets = {!! json_encode(($ai_presets ?? []) ?: app(\InnoShop\Common\Services\AI\ProviderRegistry::class)->getPresets()) !!};
const aiPluginProviders = {!! json_encode($ai_plugin_providers ?? []) !!};
let aiProviders = [];

function aiInitProviders() {
  const input = document.getElementById('ai_providers_input');
  try {
    aiProviders = JSON.parse(input.value || '[]');
  } catch(e) {
    aiProviders = [];
  }
  aiRenderProviders();
}

function aiRenderProviders() {
  const list = document.getElementById('ai-providers-list');
  if (!list) return;

  const hasUser = aiProviders.length > 0;
  const hasPlugin = aiPluginProviders.length > 0;

  if (!hasUser && !hasPlugin) {
    list.innerHTML = '<div class="text-muted text-center py-3">{{ __("panel/setting_ai.no_providers") }}</div>';
    return;
  }

  let html = '';

  // Plugin providers (read-only)
  if (hasPlugin) {
    html += '<div class="mb-3"><small class="text-muted fw-bold">{{ __("panel/setting_ai.plugin_providers") }}</small></div>';
    aiPluginProviders.forEach(function(p) {
      html += '<div class="card mb-3 border border-info">';
      html += '<div class="card-header d-flex justify-content-between align-items-center py-2">';
      html += '<span class="fw-bold d-flex align-items-center">';
      html += '<i class="bi bi-puzzle me-2 text-info"></i>';
      html += (p.name || p.code) + '</span>';
      html += '<div>';
      html += '<span class="badge bg-light text-dark me-2">' + (p.driver || 'openai') + '</span>';
      html += '<span class="badge bg-info text-dark">{{ __("panel/setting_ai.from_plugin") }}</span>';
      html += '</div></div>';
      html += '<div class="card-body"><div class="row">';
      html += '<div class="col-lg-6 mb-2"><label class="form-label small">{{ __("panel/setting_ai.provider_base_url") }}</label>';
      html += '<input type="text" class="form-control form-control-sm" value="' + (p.base_url || '') + '" disabled /></div>';
      html += '<div class="col-lg-6 mb-2"><label class="form-label small">{{ __("panel/setting_ai.text_model") }}</label>';
      html += '<input type="text" class="form-control form-control-sm" value="' + (p.models?.text || '') + '" disabled /></div>';
      if (p.models?.image) {
        html += '<div class="col-lg-6 mb-2"><label class="form-label small">{{ __("panel/setting_ai.image_model") }}</label>';
        html += '<input type="text" class="form-control form-control-sm" value="' + p.models.image + '" disabled /></div>';
      }
      html += '</div></div></div>';
    });
  }

  // User providers (editable)
  if (hasUser) {
    if (hasPlugin) {
      html += '<div class="mb-3 mt-4"><small class="text-muted fw-bold">{{ __("panel/setting_ai.user_providers") }}</small></div>';
    }
    aiProviders.forEach(function(p, idx) {
      const isPreset = aiPresets.find(function(pr) { return pr.code === p.code; });
      html += '<div class="card mb-3 border ' + (p.api_key ? 'border-success' : 'border-warning') + '">';
      html += '<div class="card-header d-flex justify-content-between align-items-center py-2">';
      html += '<span class="fw-bold d-flex align-items-center">';
      if (isPreset && isPreset.logo) {
        html += '<img src="' + isPreset.logo + '" alt="" width="20" height="20" class="me-2 rounded" style="object-fit:contain;" onerror="this.style.display=\'none\'">';
      } else {
        html += '<i class="bi bi-cpu me-2"></i>';
      }
      html += (p.name || p.code) + '</span>';
      html += '<div>';
      html += '<span class="badge bg-light text-dark me-2">' + (p.driver || 'openai') + '</span>';
      html += '<button type="button" class="btn btn-sm btn-outline-danger" onclick="aiRemoveProvider(' + idx + ')"><i class="bi bi-trash"></i></button>';
      html += '</div></div>';
      html += '<div class="card-body">';

      // API Key
      html += '<div class="row">';
      html += '<div class="col-lg-6 mb-2">';
      html += '<label class="form-label small">{{ __("panel/setting_ai.provider_api_key") }}</label>';
      html += '<input type="password" class="form-control form-control-sm" value="' + (p.api_key || '') + '" onchange="aiUpdateField(' + idx + ', \'api_key\', this.value)" placeholder="sk-..." />';
      html += '</div>';
      // Base URL
      html += '<div class="col-lg-6 mb-2">';
      html += '<label class="form-label small">{{ __("panel/setting_ai.provider_base_url") }}</label>';
      html += '<input type="text" class="form-control form-control-sm" value="' + (p.base_url || '') + '" onchange="aiUpdateField(' + idx + ', \'base_url\', this.value)" placeholder="https://api.openai.com/v1" />';
      html += '</div>';
      html += '</div>';

      // Models
      html += '<div class="row">';
      html += '<div class="col-lg-6 mb-2">';
      html += '<label class="form-label small">{{ __("panel/setting_ai.text_model") }}</label>';
      html += '<input type="text" class="form-control form-control-sm" value="' + (p.models?.text || '') + '" onchange="aiUpdateModel(' + idx + ', \'text\', this.value)" placeholder="gpt-4o" />';
      html += '</div>';
      html += '<div class="col-lg-6 mb-2">';
      html += '<label class="form-label small">{{ __("panel/setting_ai.image_model") }}</label>';
      html += '<input type="text" class="form-control form-control-sm" value="' + (p.models?.image || '') + '" onchange="aiUpdateModel(' + idx + ', \'image\', this.value)" placeholder="gpt-image-1" />';
      html += '</div>';
      html += '</div>';

      html += '</div></div>';
    });
  }

  list.innerHTML = html;
}

function aiUpdateField(idx, field, value) {
  aiProviders[idx][field] = value;
  aiSyncHidden();
}

function aiUpdateModel(idx, type, value) {
  if (!aiProviders[idx].models) aiProviders[idx].models = {};
  aiProviders[idx].models[type] = value;
  aiSyncHidden();
}

function aiRemoveProvider(idx) {
  if (!confirm('{{ __("panel/setting_ai.confirm_remove_provider") }}')) return;
  aiProviders.splice(idx, 1);
  aiRenderProviders();
  aiSyncHidden();
}

function aiAddProvider() {
  // Build preset picker modal
  let html = '<div class="modal fade" id="aiAddModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">';
  html += '<div class="modal-header"><h5 class="modal-title">{{ __("panel/setting_ai.add_provider") }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>';
  html += '<div class="modal-body"><style>.provider-card:hover{border-color:var(--bs-primary);box-shadow:0 0 0 1px var(--bs-primary)}</style><div class="row g-3">';
  aiPresets.forEach(function(p) {
    const exists = aiProviders.some(function(ap) { return ap.code === p.code; });
    if (!exists) {
      html += '<div class="col-6 col-md-4">';
      html += '<div class="card text-center h-100 provider-card" style="cursor:pointer;" onclick="aiSelectPreset(\'' + p.code + '\')">';
      html += '<div class="card-body d-flex flex-column align-items-center justify-content-center py-3">';
      if (p.logo) {
        html += '<img src="' + p.logo + '" alt="" width="36" height="36" class="mb-2 rounded" style="object-fit:contain;" onerror="this.style.display=\'none\'">';
      }
      html += '<strong class="small">' + p.name + '</strong>';
      html += '</div></div></div>';
    }
  });
  html += '<div class="col-6 col-md-4">';
  html += '<div class="card text-center h-100 provider-card" style="cursor:pointer;" onclick="aiSelectPreset(\'custom\')">';
  html += '<div class="card-body d-flex flex-column align-items-center justify-content-center py-3">';
  html += '<div class="mb-2 rounded bg-light d-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="bi bi-plus-lg text-secondary"></i></div>';
  html += '<strong class="small">{{ __("panel/setting_ai.preset_custom") }}</strong>';
  html += '</div></div></div>';
  html += '<div class="col-6 col-md-4">';
  html += '<div class="card text-center h-100 provider-card" style="cursor:pointer;" onclick="aiSelectPreset(\'custom_anthropic\')">';
  html += '<div class="card-body d-flex flex-column align-items-center justify-content-center py-3">';
  html += '<div class="mb-2 rounded bg-light d-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="bi bi-plus-lg text-secondary"></i></div>';
  html += '<strong class="small">{{ __("panel/setting_ai.preset_custom_anthropic") }}</strong>';
  html += '</div></div></div>';
  html += '</div></div></div></div></div>';

  document.body.insertAdjacentHTML('beforeend', html);
  const modal = new bootstrap.Modal(document.getElementById('aiAddModal'));
  modal.show();
  document.getElementById('aiAddModal').addEventListener('hidden.bs.modal', function() {
    this.remove();
  });
}

function aiSelectPreset(code) {
  const modal = bootstrap.Modal.getInstance(document.getElementById('aiAddModal'));
  modal.hide();

  if (code === 'custom') {
    aiProviders.push({
      code: 'custom_' + Date.now(),
      name: 'Custom Provider',
      driver: 'openai',
      api_key: '',
      base_url: '',
      models: { text: '', image: '' }
    });
  } else if (code === 'custom_anthropic') {
    aiProviders.push({
      code: 'custom_' + Date.now(),
      name: 'Custom Provider (Anthropic)',
      driver: 'anthropic',
      api_key: '',
      base_url: '',
      models: { text: 'claude-sonnet-4-6', image: '' }
    });
  } else {
    const preset = aiPresets.find(function(p) { return p.code === code; });
    if (preset) {
      aiProviders.push({
        code: preset.code,
        name: preset.name,
        driver: preset.driver,
        api_key: '',
        base_url: preset.base_url,
        models: preset.models || { text: '', image: '' }
      });
    }
  }
  aiRenderProviders();
  aiSyncHidden();
}

function aiSyncHidden() {
  document.getElementById('ai_providers_input').value = JSON.stringify(aiProviders);
}

// Sync on form submit
document.addEventListener('DOMContentLoaded', function() {
  aiInitProviders();
  const form = document.getElementById('app-form');
  if (form) {
    form.addEventListener('submit', function() {
      // Sync current input values before submit
      aiSyncHidden();
    });
  }
});

function downloadGeoLite2() {
  const btn = document.getElementById('download-geolite2-btn');
  const originalText = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="bi bi-hourglass-split"></i> {{ __("panel/setting_geolite2.downloading") }}...';

  const url = document.getElementById('geolite2-download-url').value;

  axios.post('{{ panel_route("settings.download_geolite2") }}', {
    url: url
  })
  .then(function(response) {
    if (response.data.success) {
      alert(response.data.message);
      refreshGeoLite2Info();
    } else {
      const errorMsg = response.data.message || '{{ __("panel/setting_geolite2.download_failed") }}';
      const message = errorMsg.replace(/\n/g, '<br>');
      const alertDiv = document.createElement('div');
      alertDiv.className = 'alert alert-danger';
      alertDiv.innerHTML = '<strong>{{ __("panel/setting_geolite2.download_failed") }}</strong><br>' + message;
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
    alertDiv.innerHTML = '<strong>{{ __("panel/setting_geolite2.download_failed") }}</strong><br>' + message;
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
        ? '<span class="badge bg-success">{{ __("panel/setting_geolite2.database_exists") }}</span>'
        : '<span class="badge bg-warning">{{ __("panel/setting_geolite2.database_not_exists") }}</span>';
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
