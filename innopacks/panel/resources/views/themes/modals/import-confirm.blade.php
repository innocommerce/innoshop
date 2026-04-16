{{-- Import demo data confirmation modal --}}
<div class="modal fade import-demo-confirm-modal"
     id="importDemoConfirm{{ $theme['code'] }}"
     tabindex="-1"
     data-bs-backdrop="static"
     data-theme-code="{{ $theme['code'] }}">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('panel/themes.confirm_import') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3">{{ __('panel/themes.confirm_import_warning') }}</p>
        <div class="form-check mb-0">
          <input class="form-check-input import-demo-clear-catalog" type="checkbox" value="1" id="importDemoClearCatalog{{ $theme['code'] }}">
          <label class="form-check-label" for="importDemoClearCatalog{{ $theme['code'] }}">
            {{ __('panel/themes.import_clear_default_catalog') }}
          </label>
        </div>
        <p class="text-muted small mt-2 mb-0">{{ __('panel/themes.import_clear_default_catalog_help') }}</p>
        <div class="alert alert-danger mt-3 d-none import-demo-error-wrap" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <span class="import-demo-error-msg"></span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('common/base.cancel') }}</button>
        <button type="button"
                class="btn btn-primary btn-import-demo-confirm"
                data-demo-import-url="{{ panel_route('themes.import_demo', $theme['code']) }}">
          <span class="spinner-border spinner-border-sm d-none me-2 import-demo-spinner" role="status" aria-hidden="true"></span>
          {{ __('panel/themes.confirm_import_button') }}
        </button>
      </div>
    </div>
  </div>
</div>
