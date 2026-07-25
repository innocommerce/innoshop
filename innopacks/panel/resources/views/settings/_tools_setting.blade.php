<!-- Tools Settings -->
<div class="tab-pane fade" id="tab-setting-tools">
  <div class="container">
    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="toolsSettingsTabs" role="tablist">
      @if(ai_enabled())
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="ai-tools-tab"
                type="button" role="tab" aria-controls="ai-tools" aria-selected="true"
                data-target="#ai-tools">
          {{ __('ai::setting_ai.ai_setting') }}
        </button>
      </li>
      @endif
      <li class="nav-item" role="presentation">
        <button class="nav-link @if(!ai_enabled()) active @endif" id="geolite2-tools-tab"
                type="button" role="tab" aria-controls="geolite2-tools" aria-selected="false"
                data-target="#geolite2-tools">
          {{ __('panel/setting_geolite2.geolite2_setting') }}
        </button>
      </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="toolsSettingsTabContent">
      @if(ai_enabled())
      <!-- AI Tools Tab -->
      @include('ai::settings._tools')
      @endif

      <!-- GeoLite2 Tools Tab -->
      <div class="tab-pane fade @if(!ai_enabled()) show active @endif" id="geolite2-tools" role="tabpanel" aria-labelledby="geolite2-tools-tab">
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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <h6 class="mb-0">{{ __('panel/setting_geolite2.geolite2_database_info') }}</h6>
                      <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshGeoLite2Info()">
                        <i class="bi bi-arrow-clockwise"></i> {{ __('panel/setting_geolite2.refresh_info') }}
                      </button>
                    </div>
                    <div class="row g-3 mb-3">
                      <div class="col-md-3 col-sm-6">
                        <div class="text-muted small mb-1">{{ __('panel/setting_geolite2.database_status') }}</div>
                        <div id="geolite2-status">
                          @if($geolite2_info['exists'])
                            <span class="badge bg-success">{{ __('panel/setting_geolite2.database_exists') }}</span>
                          @else
                            <span class="badge bg-warning">{{ __('panel/setting_geolite2.database_not_exists') }}</span>
                          @endif
                        </div>
                      </div>
                      <div class="col-md-3 col-sm-6">
                        <div class="text-muted small mb-1">{{ __('panel/setting_geolite2.database_size') }}</div>
                        <div id="geolite2-size" class="fw-medium">{{ $geolite2_info['size_formatted'] }}</div>
                      </div>
                      <div class="col-md-3 col-sm-6">
                        <div class="text-muted small mb-1">{{ __('panel/setting_geolite2.database_updated') }}</div>
                        <div id="geolite2-modified" class="fw-medium">{{ $geolite2_info['modified_formatted'] }}</div>
                      </div>
                      <div class="col-md-3 col-sm-6">
                        <div class="text-muted small mb-1">{{ __('panel/setting_geolite2.database_version') }}</div>
                        <div id="geolite2-version" class="fw-medium">{{ $geolite2_info['version'] ?: '-' }}</div>
                      </div>
                    </div>
                    <hr class="my-3">
                    <div class="row g-2 align-items-center">
                      <div class="col-md-2 col-sm-3 text-muted small">{{ __('panel/setting_geolite2.database_path') }}</div>
                      <div class="col-md-10 col-sm-9">
                        <code id="geolite2-path" class="small">{{ $geolite2_info['path'] }}</code>
                      </div>
                      <div class="col-md-2 col-sm-3 text-muted small">{{ __('panel/setting_geolite2.download_url') }}</div>
                      <div class="col-md-10 col-sm-9">
                        <a id="geolite2-download-url" href="{{ $geolite2_info['download_url'] }}" target="_blank" class="small text-break">{{ $geolite2_info['download_url'] }}</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Download Section -->
            <div class="row align-items-center">
              <div class="col">
                <h6 class="mb-1">{{ __('panel/setting_geolite2.geolite2_download') }}</h6>
                <p class="text-secondary small mb-0">{{ __('panel/setting_geolite2.geolite2_download_desc') }}</p>
              </div>
              <div class="col-auto">
                <button type="button" class="btn btn-primary" id="download-geolite2-btn" onclick="downloadGeoLite2()">
                  <i class="bi bi-download"></i> {{ __('panel/setting_geolite2.download_geolite2_database') }}
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
  btn.innerHTML = '<i class="bi bi-hourglass-split"></i> {{ __("panel/setting_geolite2.downloading") }}...';

  axios.post('{{ panel_route("settings.download_geolite2") }}')
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

      const downloadUrlEl = document.getElementById('geolite2-download-url');
      if (downloadUrlEl) {
        downloadUrlEl.href = info.download_url;
        downloadUrlEl.textContent = info.download_url;
      }
    }
  })
  .catch(function(error) {
    console.error('Failed to refresh GeoLite2 info:', error);
  });
}
</script>
@endpush
