{{-- Theme preview image modal --}}
<div class="modal fade" id="themePreviewModal{{ $theme['code'] }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fw-bold">{{ $theme['name'] }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center" style="min-height: 300px; display: flex; align-items: center; justify-content: center; background: #f8f9fa; padding: 1.5rem;">
        <img src="{{ theme_image($theme['preview'], $theme['code'], 1200, 900) }}" 
             class="img-fluid theme-preview-large" 
             alt="{{ $theme['name'] }}"
             style="max-width: 100%; max-height: 65vh; height: auto; border-radius: 8px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);">
      </div>
    </div>
  </div>
</div>
