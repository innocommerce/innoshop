<!-- Marketplace Settings -->
<div class="tab-pane fade" id="tab-setting-marketplace">
  <h5 class="mb-4">{{ __('panel/plugin.marketplace_settings') }}</h5>

  {{-- Domain Token Section --}}
  <div class="card mb-4">
    <div class="card-header">
      <h6 class="mb-0">{{ __('panel/plugin.domain_token') }}</h6>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-8 mb-3">
          <label class="form-label">{{ __('panel/plugin.domain_token') }}</label>
          <div class="input-group">
            <input type="text" 
                   class="form-control" 
                   name="domain_token" 
                   id="domain_token"
                   value="{{ old('domain_token', system_setting('domain_token', '')) }}"
                   placeholder="{{ __('panel/plugin.domain_token_placeholder') }}">
            <button type="button" 
                    class="btn btn-outline-primary" 
                    id="btnGetToken"
                    data-url="{{ panel_route('marketplaces.get_token') }}">
              <i class="bi bi-download me-1"></i>{{ __('panel/plugin.get_token') }}
            </button>
          </div>
          <small class="text-muted">{{ __('panel/plugin.domain_token_tip') }}</small>
        </div>
      </div>
    </div>
  </div>

  {{-- Cache Settings Section --}}
  <div class="card mb-4">
    <div class="card-header">
      <h6 class="mb-0">{{ __('panel/plugin.cache_settings') }}</h6>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6 mb-3">
          <x-common-form-switch-radio 
            title="{{ __('panel/plugin.enable_cache') }}" 
            name="marketplace_enable_cache"
            value="{{ old('marketplace_enable_cache', system_setting('marketplace_enable_cache', true)) }}" />
          <small class="text-muted">{{ __('panel/plugin.enable_cache_tip') }}</small>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">{{ __('panel/plugin.cache_ttl') }}</label>
          <div class="input-group">
            <input type="number" 
                   class="form-control" 
                   name="marketplace_cache_ttl" 
                   value="{{ old('marketplace_cache_ttl', system_setting('marketplace_cache_ttl', 3600)) }}"
                   min="60" 
                   step="60">
            <span class="input-group-text">{{ __('panel/plugin.seconds') }}</span>
          </div>
          <small class="text-muted">{{ __('panel/plugin.cache_ttl_tip') }}</small>
        </div>
        <div class="col-md-12 mb-3">
          <button type="button" 
                  class="btn btn-outline-danger" 
                  id="btnClearCache"
                  data-url="{{ panel_route('marketplaces.clear_cache') }}">
            <i class="bi bi-trash me-1"></i>{{ __('panel/plugin.clear_cache') }}
          </button>
          <small class="text-muted ms-2">{{ __('panel/plugin.clear_cache_tip') }}</small>
        </div>
      </div>
    </div>
  </div>

  {{-- Display Settings Section --}}
  <div class="card mb-4">
    <div class="card-header">
      <h6 class="mb-0">{{ __('panel/plugin.display_settings') }}</h6>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">{{ __('panel/plugin.plugins_per_page') }}</label>
          <input type="number" 
                 class="form-control" 
                 name="marketplace_plugins_per_page" 
                 value="{{ old('marketplace_plugins_per_page', system_setting('marketplace_plugins_per_page', 12)) }}"
                 min="1" 
                 max="100">
          <small class="text-muted">{{ __('panel/plugin.plugins_per_page_tip') }}</small>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">{{ __('panel/plugin.themes_per_page') }}</label>
          <input type="number" 
                 class="form-control" 
                 name="marketplace_themes_per_page" 
                 value="{{ old('marketplace_themes_per_page', system_setting('marketplace_themes_per_page', 12)) }}"
                 min="1" 
                 max="100">
          <small class="text-muted">{{ __('panel/plugin.themes_per_page_tip') }}</small>
        </div>
      </div>
    </div>
  </div>

  {{-- Logging Settings Section --}}
  <div class="card mb-4">
    <div class="card-header">
      <h6 class="mb-0">{{ __('panel/plugin.logging_settings') }}</h6>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6 mb-3">
          <x-common-form-switch-radio 
            title="{{ __('panel/plugin.enable_request_log') }}" 
            name="marketplace_enable_request_log"
            value="{{ old('marketplace_enable_request_log', system_setting('marketplace_enable_request_log', true)) }}" />
          <small class="text-muted">{{ __('panel/plugin.enable_request_log_tip') }}</small>
        </div>
      </div>
    </div>
  </div>
</div>

@push('footer')
<script>
  $(function() {
    // Get domain token
    $('#btnGetToken').on('click', function() {
      const $btn = $(this);
      const url = $btn.data('url');
      
      $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>{{ __('panel/common.loading') }}');
      
      axios.get(url)
        .then(function(res) {
          // axios interceptor already returns response.data, so res is the JSON object
          if (res && res.success) {
            var token = res.data?.token || res.data?.domain_token || '';
            if (token) {
              $('#domain_token').val(token);
            }
            layer.msg(res.message || '{{ __('panel/common.success') }}', {icon: 1});
          } else {
            layer.msg(res?.message || '{{ __('panel/common.error') }}', {icon: 2});
          }
        })
        .catch(function(err) {
          var errorMsg = '{{ __('panel/common.error') }}';
          if (err.response && err.response.data) {
            errorMsg = err.response.data.message || err.response.data.data?.message || errorMsg;
          } else if (err.message) {
            errorMsg = err.message;
          }
          layer.msg(errorMsg, {icon: 2});
        })
        .finally(function() {
          $btn.prop('disabled', false).html('<i class="bi bi-download me-1"></i>{{ __('panel/plugin.get_token') }}');
        });
    });

    // Clear cache
    $('#btnClearCache').on('click', function() {
      const $btn = $(this);
      const url = $btn.data('url');
      
      layer.confirm('{{ __('panel/plugin.clear_cache_confirm') }}', {icon: 3, title: '{{ __('panel/common.confirm') }}'}, function(index) {
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>{{ __('panel/common.loading') }}');
        
        axios.post(url)
          .then(function(res) {
            // axios interceptor already returns response.data, so res is the JSON object
            if (res && res.success) {
              layer.msg(res.message || '{{ __('panel/common.success') }}', {icon: 1});
            } else {
              layer.msg(res?.message || '{{ __('panel/common.error') }}', {icon: 2});
            }
          })
          .catch(function(err) {
            var errorMsg = '{{ __('panel/common.error') }}';
            if (err.response && err.response.data) {
              errorMsg = err.response.data.message || errorMsg;
            } else if (err.message) {
              errorMsg = err.message;
            }
            layer.msg(errorMsg, {icon: 2});
          })
          .finally(function() {
            $btn.prop('disabled', false).html('<i class="bi bi-trash me-1"></i>{{ __('panel/plugin.clear_cache') }}');
            layer.close(index);
          });
      });
    });
  });
</script>
@endpush

