{{-- Import demo data confirmation modal --}}
<div class="modal fade" id="importDemoConfirm{{ $theme['code'] }}" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('panel/themes.confirm_import') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">{{ __('panel/themes.confirm_import_warning') }}</p>
        <div id="importError{{ $theme['code'] }}" class="alert alert-danger mt-3 d-none" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <span id="importErrorMsg{{ $theme['code'] }}"></span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('panel/common.cancel') }}</button>
        <button type="button" class="btn btn-primary" id="confirmImportDemo{{ $theme['code'] }}">
          <span class="spinner-border spinner-border-sm d-none me-2" id="importSpinner{{ $theme['code'] }}" role="status" aria-hidden="true"></span>
          {{ __('panel/themes.confirm_import_button') }}
        </button>
      </div>
    </div>
  </div>
</div>

@push('footer')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Get confirm button
  const confirmBtn = document.getElementById('confirmImportDemo{{ $theme['code'] }}');
  const errorDiv = document.getElementById('importError{{ $theme['code'] }}');
  const errorMsg = document.getElementById('importErrorMsg{{ $theme['code'] }}');
  const spinner = document.getElementById('importSpinner{{ $theme['code'] }}');
  const confirmModal = document.getElementById('importDemoConfirm{{ $theme['code'] }}');
  
  if (confirmBtn) {
    confirmBtn.addEventListener('click', function() {
      // Hide error message
      errorDiv.classList.add('d-none');
      errorMsg.textContent = '';
      
      // Show loading spinner
      spinner.classList.remove('d-none');
      confirmBtn.disabled = true;
      
      // Send import request
      fetch('{{ panel_route('themes.import_demo', $theme['code']) }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      }).then(response => {
        return response.json().then(data => {
          if (response.ok && data.success) {
            // Close confirmation modal
            const modalInstance = bootstrap.Modal.getInstance(confirmModal);
            if (modalInstance) {
              modalInstance.hide();
            }
            // Show success message
            inno.msg(data.message || '{{ __('panel/themes.demo_installed') }}');
            // Reload page after a short delay
            setTimeout(() => {
              window.location.reload();
            }, 1000);
          } else {
            // Show error message
            const errorText = data.message || data.error || '{{ __('panel/themes.import_failed') }}';
            errorMsg.textContent = errorText;
            errorDiv.classList.remove('d-none');
            // Scroll to error
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          }
        });
      }).catch(error => {
        // Show error message
        const errorText = error.message || '{{ __('panel/themes.import_failed') }}';
        errorMsg.textContent = errorText;
        errorDiv.classList.remove('d-none');
        // Scroll to error
        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }).finally(() => {
        // Hide loading spinner
        spinner.classList.add('d-none');
        confirmBtn.disabled = false;
      });
    });
  }
  
  // When confirmation modal is shown, keep the detail modal open
  if (confirmModal) {
    confirmModal.addEventListener('show.bs.modal', function() {
      // Prevent the detail modal from closing
      const detailModal = document.getElementById('themeDetail{{ $theme['code'] }}');
      if (detailModal) {
        const detailModalInstance = bootstrap.Modal.getInstance(detailModal);
        if (detailModalInstance) {
          // Store the current backdrop state
          detailModal.setAttribute('data-bs-backdrop', 'static');
        }
      }
    });
    
    // When confirmation modal is hidden, restore detail modal backdrop
    confirmModal.addEventListener('hidden.bs.modal', function() {
      const detailModal = document.getElementById('themeDetail{{ $theme['code'] }}');
      if (detailModal) {
        detailModal.removeAttribute('data-bs-backdrop');
      }
    });
  }
});
</script>
@endpush
