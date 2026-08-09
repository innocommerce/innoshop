<!-- AI Tools Tab -->
<div class="tab-pane fade show active" id="ai-tools" role="tabpanel" aria-labelledby="ai-tools-tab">

  <!-- AI Provider Management -->
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">{{ __('aicore::setting_ai.provider_management') }}</h5>
      <button type="button" class="btn btn-sm btn-outline-primary" onclick="aiAddProvider()">
        <i class="bi bi-plus-lg me-1"></i>{{ __('aicore::setting_ai.add_provider') }}
      </button>
    </div>
    <div class="card-body">
      <p class="text-muted small mb-3">{{ __('aicore::setting_ai.provider_management_desc') }}</p>
      <style>
        #ai-providers-list .ai-provider-name-input { max-width:260px; font-weight:600; }
        #ai-providers-list .ai-provider-name-input::placeholder { font-weight:400; color:#94a3b8; }
      </style>
      <div id="ai-providers-list">
        <!-- Rendered by JS -->
      </div>
      <input type="hidden" name="ai_providers" id="ai_providers_input" value="{{ old('ai_providers', is_string($ai_providers ?? '') ? $ai_providers : json_encode($ai_providers ?? [])) }}" />
    </div>
  </div>

  <!-- Capability Selection -->
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('aicore::setting_ai.capability_selection') }}</h5>
    </div>
    <div class="card-body">
      <p class="text-muted small mb-3">{{ __('aicore::setting_ai.capability_selection_desc') }}</p>
      <div class="row">
        <div class="col-lg-6">
          <x-common-form-select title="{{ __('aicore::setting_ai.text_generation_provider') }}" name="ai_text_provider"
                              :options="$ai_models ?? []" key="code" label="name" :emptyOption="true"
                              value="{{ old('ai_text_provider', system_setting('ai_text_provider') ?: system_setting('ai_model')) }}" />
        </div>
        <div class="col-lg-6">
          <x-common-form-input title="{{ __('aicore::setting_ai.text_model_override') }}"
                             name="ai_text_model"
                             value="{{ old('ai_text_model', system_setting('ai_text_model')) }}"
                             placeholder="{{ __('aicore::setting_ai.model_override_placeholder') }}" />
        </div>
      </div>
      <div class="row mt-3">
        <div class="col-lg-6">
          <x-common-form-select title="{{ __('aicore::setting_ai.image_generation_provider') }}" name="ai_image_provider"
                              :options="$ai_models ?? []" key="code" label="name" :emptyOption="true"
                              value="{{ old('ai_image_provider', system_setting('ai_image_provider')) }}" />
        </div>
        <div class="col-lg-6">
          <x-common-form-input title="{{ __('aicore::setting_ai.image_model_override') }}"
                             name="ai_image_model"
                             value="{{ old('ai_image_model', system_setting('ai_image_model')) }}"
                             placeholder="{{ __('aicore::setting_ai.model_override_placeholder') }}" />
        </div>
      </div>
    </div>
  </div>

  @includeWhen(view()->exists('mcp::settings._tools'), 'mcp::settings._tools')
</div>

@push('footer')
<script>
// Provider Management
const aiPresets = {!! json_encode(($ai_presets ?? []) ?: app(\InnoShop\Aicore\Services\ProviderRegistry::class)->getPresets()) !!};
const aiPluginProviders = {!! json_encode($ai_plugin_providers ?? []) !!};
let aiProviders = [];

// HTML attribute escape (name field can contain quotes)
function escapeAttr(s) {
  return String(s == null ? '' : s)
    .replace(/&/g, '&amp;').replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

// Friendly driver badge label
function aiDriverLabel(driver) {
  if (driver === 'anthropic') return '{{ __("aicore::setting_ai.driver_anthropic") }}';
  if (driver === 'deepseek')  return '{{ __("aicore::setting_ai.driver_deepseek") }}';
  return '{{ __("aicore::setting_ai.driver_openai") }}';
}

// Tooltip explaining the driver's underlying HTTP protocol
function aiDriverTitle(driver) {
  if (driver === 'anthropic') return '{{ __("aicore::setting_ai.driver_anthropic_tip") }}';
  if (driver === 'deepseek')  return '{{ __("aicore::setting_ai.driver_deepseek_tip") }}';
  return '{{ __("aicore::setting_ai.driver_openai_tip") }}';
}

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
    list.innerHTML = '<div class="text-muted text-center py-3">{{ __("aicore::setting_ai.no_providers") }}</div>';
    return;
  }

  let html = '';

  // Plugin providers (read-only)
  if (hasPlugin) {
    html += '<div class="mb-3"><small class="text-muted fw-bold">{{ __("aicore::setting_ai.plugin_providers") }}</small></div>';
    aiPluginProviders.forEach(function(p) {
      html += '<div class="card mb-3 border border-info">';
      html += '<div class="card-header d-flex justify-content-between align-items-center py-2">';
      html += '<span class="fw-bold d-flex align-items-center">';
      html += '<i class="bi bi-puzzle me-2 text-info"></i>';
      html += (p.name || p.code) + '</span>';
      html += '<div>';
      html += '<span class="badge bg-light text-dark me-2" title="' + aiDriverTitle(p.driver) + '">' + aiDriverLabel(p.driver) + '</span>';
      html += '<span class="badge bg-info text-dark">{{ __("aicore::setting_ai.from_plugin") }}</span>';
      html += '</div></div>';
      html += '<div class="card-body"><div class="row">';
      html += '<div class="col-lg-6 mb-2"><label class="form-label small">{{ __("aicore::setting_ai.provider_base_url") }}</label>';
      html += '<input type="text" class="form-control form-control-sm" value="' + (p.base_url || '') + '" disabled /></div>';
      html += '<div class="col-lg-6 mb-2"><label class="form-label small">{{ __("aicore::setting_ai.text_model") }}</label>';
      html += '<input type="text" class="form-control form-control-sm" value="' + (p.models?.text || '') + '" disabled /></div>';
      if (p.models?.image) {
        html += '<div class="col-lg-6 mb-2"><label class="form-label small">{{ __("aicore::setting_ai.image_model") }}</label>';
        html += '<input type="text" class="form-control form-control-sm" value="' + p.models.image + '" disabled /></div>';
      }
      html += '</div></div></div>';
    });
  }

  // User providers (editable)
  if (hasUser) {
    if (hasPlugin) {
      html += '<div class="mb-3 mt-4"><small class="text-muted fw-bold">{{ __("aicore::setting_ai.user_providers") }}</small></div>';
    }
    aiProviders.forEach(function(p, idx) {
      const isPreset = aiPresets.find(function(pr) { return pr.code === p.code; });
      html += '<div class="card mb-3 border ' + (p.api_key ? 'border-success' : 'border-warning') + '">';
      html += '<div class="card-header d-flex justify-content-between align-items-center py-2">';
      html += '<span class="d-flex align-items-center flex-grow-1 me-2">';
      if (isPreset && isPreset.logo) {
        html += '<img src="' + isPreset.logo + '" alt="" width="20" height="20" class="me-2 rounded" style="object-fit:contain;" onerror="this.style.display=\'none\'">';
      } else {
        html += '<i class="bi bi-cpu me-2"></i>';
      }
      html += '<input type="text" class="form-control form-control-sm ai-provider-name-input" value="' + escapeAttr(p.name || p.code || '') + '" onchange="aiUpdateField(' + idx + ', \'name\', this.value)" placeholder="{{ __("aicore::setting_ai.provider_name_placeholder") }}" />';
      html += '</span>';
      html += '<div class="d-flex align-items-center">';
      html += '<span class="badge bg-light text-dark me-2" title="' + aiDriverTitle(p.driver) + '">' + aiDriverLabel(p.driver) + '</span>';
      html += '<button type="button" class="btn btn-sm btn-outline-danger" onclick="aiRemoveProvider(' + idx + ')"><i class="bi bi-trash"></i></button>';
      html += '</div></div>';
      html += '<div class="card-body">';

      // API Key + Base URL
      html += '<div class="row">';
      html += '<div class="col-lg-6 mb-2">';
      html += '<label class="form-label small">{{ __("aicore::setting_ai.provider_api_key") }}</label>';
      html += '<input type="password" class="form-control form-control-sm" value="' + (p.api_key || '') + '" onchange="aiUpdateField(' + idx + ', \'api_key\', this.value)" placeholder="sk-..." />';
      html += '</div>';
      // Base URL
      html += '<div class="col-lg-6 mb-2">';
      html += '<label class="form-label small">{{ __("aicore::setting_ai.provider_base_url") }}</label>';
      html += '<input type="text" class="form-control form-control-sm" value="' + (p.base_url || '') + '" onchange="aiUpdateField(' + idx + ', \'base_url\', this.value)" placeholder="https://api.openai.com/v1" />';
      html += '</div>';
      html += '</div>';

      // Models
      html += '<div class="row">';
      html += '<div class="col-lg-6 mb-2">';
      html += '<label class="form-label small">{{ __("aicore::setting_ai.text_model") }}</label>';
      html += '<div class="input-group input-group-sm">';
      html += '<input type="text" class="form-control form-control-sm" id="ai_text_model_' + idx + '" value="' + (p.models?.text || '') + '" onchange="aiUpdateModel(' + idx + ', \'text\', this.value)" placeholder="gpt-4o / deepseek-v4-flash" />';
      html += '<button type="button" class="btn btn-outline-secondary" onclick="aiFetchModels(' + idx + ', \'text\', this)" title="{{ __("aicore::setting_ai.fetch_models") }}"><i class="bi bi-arrow-down-circle"></i></button>';
      html += '</div>';
      html += '</div>';
      html += '<div class="col-lg-6 mb-2">';
      html += '<label class="form-label small">{{ __("aicore::setting_ai.image_model") }}</label>';
      html += '<div class="input-group input-group-sm">';
      html += '<input type="text" class="form-control form-control-sm" id="ai_image_model_' + idx + '" value="' + (p.models?.image || '') + '" onchange="aiUpdateModel(' + idx + ', \'image\', this.value)" placeholder="gpt-image-1" />';
      html += '<button type="button" class="btn btn-outline-secondary" onclick="aiFetchModels(' + idx + ', \'image\', this)" title="{{ __("aicore::setting_ai.fetch_models") }}"><i class="bi bi-arrow-down-circle"></i></button>';
      html += '</div>';
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

function aiFetchModels(idx, type, btn) {
  const provider = aiProviders[idx];
  if (!provider) return;
  if (!provider.api_key) {
    alert('{{ __("aicore::setting_ai.fetch_models_need_key") }}');
    return;
  }
  if (!provider.base_url) {
    alert('{{ __("aicore::setting_ai.fetch_models_need_base_url") }}');
    return;
  }

  const $btn = $(btn);
  const originalHtml = $btn.html();
  $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

  axios.post('{{ panel_route("content_ai.list_models") }}', {
    provider_code: provider.code,
    api_key: provider.api_key,
    base_url: provider.base_url,
  }).then(res => {
    const models = res?.data?.models || [];
    if (models.length === 0) {
      alert('{{ __("aicore::setting_ai.fetch_models_empty") }}');
      return;
    }

    const inputId = 'ai_' + type + '_model_' + idx;
    const $old = $('#' + inputId);
    const currentValue = $old.val() || '';
    const $select = $('<select></select>')
      .attr('id', inputId)
      .addClass('form-control form-control-sm')
      .on('change', function () { aiUpdateModel(idx, type, this.value); });

    models.forEach(m => {
      const $opt = $('<option></option>').val(m).text(m);
      if (m === currentValue) $opt.prop('selected', true);
      $select.append($opt);
    });
    if (currentValue && !models.includes(currentValue)) {
      const $opt = $('<option></option>').val(currentValue).text(currentValue + ' (custom)');
      $opt.prop('selected', true);
      $select.append($opt);
    }

    $old.replaceWith($select);
    if (currentValue !== $select.val()) {
      aiUpdateModel(idx, type, $select.val());
    }
  }).catch(err => {
    alert('{{ __("aicore::setting_ai.fetch_models_failed") }}' + (err.response?.data?.message || err.message));
  }).finally(() => {
    $btn.prop('disabled', false).html(originalHtml);
  });
}

function aiRemoveProvider(idx) {
  if (!confirm('{{ __("aicore::setting_ai.confirm_remove_provider") }}')) return;
  aiProviders.splice(idx, 1);
  aiRenderProviders();
  aiSyncHidden();
}

function aiAddProvider() {
  // Build preset picker modal
  let html = '<div class="modal fade" id="aiAddModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">';
  html += '<div class="modal-header"><h5 class="modal-title">{{ __("aicore::setting_ai.add_provider") }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>';
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
  html += '<strong class="small">{{ __("aicore::setting_ai.preset_custom") }}</strong>';
  html += '</div></div></div>';
  html += '<div class="col-6 col-md-4">';
  html += '<div class="card text-center h-100 provider-card" style="cursor:pointer;" onclick="aiSelectPreset(\'custom_anthropic\')">';
  html += '<div class="card-body d-flex flex-column align-items-center justify-content-center py-3">';
  html += '<div class="mb-2 rounded bg-light d-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="bi bi-plus-lg text-secondary"></i></div>';
  html += '<strong class="small">{{ __("aicore::setting_ai.preset_custom_anthropic") }}</strong>';
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
</script>
@endpush
