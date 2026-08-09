<!-- MCP Service Card -->
<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('mcp::setting.mcp_service') }}</h5>
    <p class="text-muted small mb-0">{{ __('mcp::setting.mcp_service_desc') }}</p>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-md-6">
        <x-common-form-switch-radio title="{{ __('mcp::setting.enable_mcp') }}" name="mcp_enabled"
                                    value="{{ old('mcp_enabled', system_setting('mcp_enabled', false)) }}"/>
      </div>
      <div class="col-md-6">
        <x-common-form-switch-radio title="{{ __('mcp::setting.enable_mcp_write') }}" name="mcp_write_enabled"
                                    value="{{ old('mcp_write_enabled', system_setting('mcp_write_enabled', false)) }}"/>
        <div class="text-muted small mt-1">{{ __('mcp::setting.write_hint') }}</div>
      </div>
    </div>
    <div class="row mt-3">
      <div class="col-md-6">
        <label class="form-label small text-muted mb-1">{{ __('mcp::setting.endpoint_url') }}</label>
        <code class="d-block small">{{ url('/mcp') }}</code>
      </div>
      <div class="col-md-6">
        <label class="form-label small text-muted mb-1">{{ __('mcp::setting.auth_header') }}</label>
        <code class="d-block small">Authorization: Bearer &lt;token&gt;</code>
        <div class="text-muted small mt-1">{{ __('mcp::setting.token_hint', ['url' => url('/api/panel/login')]) }}</div>
      </div>
    </div>

    <hr class="my-3">
    <h6 class="mb-2">{{ __('mcp::setting.usage_title') }}</h6>
    <p class="text-muted small mb-2">{{ __('mcp::setting.usage_cursor') }}</p>
    <pre class="bg-light border rounded p-2 small mb-3"><code>{
  "mcpServers": {
    "innoshop": {
      "url": "{{ url('/mcp') }}",
      "headers": {
        "Authorization": "Bearer &lt;token&gt;"
      }
    }
  }
}</code></pre>
    <p class="text-muted small mb-2">{{ __('mcp::setting.usage_claude_code') }}</p>
    <pre class="bg-light border rounded p-2 small mb-0"><code>claude mcp add --transport http innoshop {{ url('/mcp') }} \
  --header "Authorization: Bearer &lt;token&gt;"</code></pre>
  </div>
</div>
