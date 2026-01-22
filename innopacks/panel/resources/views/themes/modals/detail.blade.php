{{-- Theme detail modal --}}
<div class="modal fade" id="themeDetail{{ $theme['code'] }}" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <div class="d-flex align-items-center gap-3">
          <div class="flex-grow-1">
            <h5 class="modal-title fw-bold mb-0">{{ $theme['name'] }}</h5>
            @if($theme['selected'])
              <span class="badge bg-primary mt-1">
                <i class="bi bi-check-circle-fill me-1"></i>
                {{ __('panel/themes.current_theme') }}
              </span>
            @endif
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        {{-- Tab Navigation --}}
        <ul class="nav nav-tabs mb-4" id="themeDetailTabs{{ $theme['code'] }}" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" 
                    id="basic-info-tab{{ $theme['code'] }}" 
                    data-bs-toggle="tab" 
                    data-bs-target="#basic-info-pane{{ $theme['code'] }}" 
                    type="button" 
                    role="tab" 
                    aria-controls="basic-info-pane{{ $theme['code'] }}" 
                    aria-selected="true">
              <i class="bi bi-info-circle me-1"></i>
              {{ __('panel/common.basic_info') }}
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" 
                    id="data-tab{{ $theme['code'] }}" 
                    data-bs-toggle="tab" 
                    data-bs-target="#data-pane{{ $theme['code'] }}" 
                    type="button" 
                    role="tab" 
                    aria-controls="data-pane{{ $theme['code'] }}" 
                    aria-selected="false">
              <i class="bi bi-database me-1"></i>
              {{ __('panel/themes.import_export_data') }}
            </button>
          </li>
        </ul>

        {{-- Tab Content --}}
        <div class="tab-content" id="themeDetailTabContent{{ $theme['code'] }}">
          {{-- Basic Info Tab --}}
          <div class="tab-pane fade show active" 
               id="basic-info-pane{{ $theme['code'] }}" 
               role="tabpanel" 
               aria-labelledby="basic-info-tab{{ $theme['code'] }}">
            {{-- Description Section --}}
            @if(isset($theme['description']) && $theme['description'])
              <div class="mb-4">
                <div class="text-secondary small text-uppercase mb-2 fw-semibold">
                  <i class="bi bi-file-text me-1"></i>
                  {{ __('panel/themes.theme_description') }}
                </div>
                <div class="fs-6 text-muted theme-description-text">{{ $theme['description'] }}</div>
              </div>
            @endif

            {{-- Info Grid --}}
            <div class="row g-3">
              <div class="col-md-6">
                <div class="theme-info-item p-3 bg-light rounded">
                  <div class="text-secondary small text-uppercase mb-2 fw-semibold">
                    <i class="bi bi-tag me-1"></i>
                    {{ __('panel/themes.version') }}
                  </div>
                  <div class="fs-6 fw-medium">{{ $theme['version'] ?? 'N/A' }}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="theme-info-item p-3 bg-light rounded">
                  <div class="text-secondary small text-uppercase mb-2 fw-semibold">
                    <i class="bi bi-code-square me-1"></i>
                    {{ __('panel/common.code') }}
                  </div>
                  <div class="fs-6 fw-medium font-monospace">{{ $theme['code'] }}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="theme-info-item p-3 bg-light rounded">
                  <div class="text-secondary small text-uppercase mb-2 fw-semibold">
                    <i class="bi bi-person me-1"></i>
                    {{ __('panel/themes.author') }}
                  </div>
                  <div class="fs-6 fw-medium">
                    @if(isset($theme['author']['name']) && $theme['author']['name'])
                      {{ $theme['author']['name'] }}
                      @if(isset($theme['author']['email']) && $theme['author']['email'])
                        <span class="text-muted">({{ $theme['author']['email'] }})</span>
                      @endif
                    @else
                      N/A
                    @endif
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="theme-info-item p-3 bg-light rounded">
                  <div class="text-secondary small text-uppercase mb-2 fw-semibold">
                    <i class="bi bi-info-circle me-1"></i>
                    {{ __('panel/common.status') }}
                  </div>
                  <div>
                    @if($theme['selected'])
                      <span class="badge bg-success">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        {{ __('panel/common.active') }}
                      </span>
                    @else
                      <span class="badge bg-secondary">
                        <i class="bi bi-circle me-1"></i>
                        {{ __('panel/common.not_used') }}
                      </span>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Import/Export Data Tab --}}
          <div class="tab-pane fade" 
               id="data-pane{{ $theme['code'] }}" 
               role="tabpanel" 
               aria-labelledby="data-tab{{ $theme['code'] }}">
            @if($theme['has_demo'])
              {{-- Import Demo Data Section --}}
              <div class="alert alert-warning border mb-4">
                <div class="d-flex gap-3">
                  <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                  <div class="flex-grow-1">
                    <div class="fw-semibold mb-2">{{ __('panel/themes.demo_data_notice') }}</div>
                    <div class="text-secondary small">
                      {{ __('panel/themes.demo_data_warning') }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="text-center d-flex gap-3 justify-content-center">
                <button type="button" 
                        class="btn btn-danger" 
                        data-bs-toggle="modal"
                        data-bs-target="#importDemoConfirm{{ $theme['code'] }}">
                  <i class="bi bi-arrow-up me-2"></i>
                  {{ __('panel/themes.import_demo_data') }}
                </button>
                <button type="button" 
                        class="btn btn-primary theme-export-sql-btn"
                        data-code="{{ $theme['code'] }}">
                  <i class="bi bi-download me-2"></i>
                  {{ __('panel/themes.export_sql') }}
                </button>
              </div>
            @else
              {{-- No Demo Data Section --}}
              <div class="alert alert-info border mb-4">
                <div class="d-flex gap-3">
                  <i class="bi bi-info-circle-fill fs-4"></i>
                  <div class="flex-grow-1">
                    <div class="fw-semibold mb-2">{{ __('panel/themes.no_demo_data') }}</div>
                    <div class="text-secondary small">
                      {{ __('panel/themes.no_demo_data_description', ['code' => $theme['code']]) }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="text-center">
                <button type="button" 
                        class="btn btn-primary theme-export-sql-btn"
                        data-code="{{ $theme['code'] }}">
                  <i class="bi bi-download me-2"></i>
                  {{ __('panel/themes.export_sql') }}
                </button>
              </div>
            @endif
          </div>
        </div>
      </div>
      <div class="modal-footer border-top">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('panel/common.close') }}</button>
        @if(!$theme['selected'])
          <button type="button" 
                  class="btn btn-primary theme-enable-btn"
                  data-url="{{ panel_route('themes.active', $theme['code']) }}"
                  data-code="{{ $theme['code'] }}">
            <i class="bi bi-check-circle me-1"></i>
            {{ __('panel/common.active') }}
          </button>
        @endif
      </div>
    </div>
  </div>
</div>

@if($theme['has_demo'])
  @include('panel::themes.modals.import-confirm', ['theme' => $theme])
@endif

@push('footer')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Handle theme enable button click
  const enableBtn = document.querySelector('#themeDetail{{ $theme['code'] }} .theme-enable-btn');
  if (enableBtn) {
    enableBtn.addEventListener('click', function() {
      const url = this.getAttribute('data-url');
      const code = this.getAttribute('data-code');
      
      layer.load(2, {shade: [0.3, '#fff']});
      axios.put(url, {status: 1}).then((res) => {
        inno.msg(res.message);
        // Close modal and reload page
        const modal = bootstrap.Modal.getInstance(document.getElementById('themeDetail{{ $theme['code'] }}'));
        if (modal) {
          modal.hide();
        }
        location.reload();
      }).catch((err) => {
        inno.msg(err.response?.data?.message || '{{ __('panel/common.operation_failed') }}');
      }).finally(() => {
        layer.closeAll('loading');
      });
    });
  }

  // Handle theme export SQL button click
  const exportSqlBtn = document.querySelector('#themeDetail{{ $theme['code'] }} .theme-export-sql-btn');
  if (exportSqlBtn) {
    exportSqlBtn.addEventListener('click', function() {
      const code = this.getAttribute('data-code');
      
      layer.load(2, {shade: [0.3, '#fff']});
      
      // Create download link
      let url = '{{ panel_route('themes.export_sql', $theme['code']) }}';
      
      // Ensure URL uses the same protocol as current page
      if (url.startsWith('http://') && window.location.protocol === 'https:') {
        url = url.replace('http://', 'https://');
      } else if (url.startsWith('https://') && window.location.protocol === 'http:') {
        url = url.replace('https://', 'http://');
      }
      
      const link = document.createElement('a');
      link.href = url;
      link.download = code + '_demo.sql';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      
      // Close loading after a short delay to allow download to start
      setTimeout(() => {
        layer.closeAll('loading');
        inno.msg('{{ __('panel/themes.export_started') }}');
      }, 500);
    });
  }

});
</script>
@endpush
