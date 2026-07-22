<div class="modal fade" id="aiGenerateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-stars me-1"></i>{{ __('panel/common.ai_generate') }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <div class="mb-3" id="aiModalLocaleWrapper">
          <label class="form-label small fw-medium">{{ __('panel/common.ai_target_language') }}</label>
          <select id="aiModalLocale" class="form-select form-select-sm">
            <option value="">{{ __('panel/common.ai_all_languages') }}</option>
            @foreach (locales() as $locale)
              <option value="{{ $locale->code }}">{{ $locale->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="mb-2">
          <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-primary ai-modal-mode-btn active" data-mode="simple">
              {{ __('panel/common.ai_mode_simple') }}
            </button>
            <button type="button" class="btn btn-outline-secondary ai-modal-mode-btn" data-mode="pro">
              {{ __('panel/common.ai_mode_pro') }}
            </button>
          </div>
        </div>

        <div class="mb-2">
          <label class="form-label small fw-medium">{{ __('panel/common.ai_prompt') }}</label>
          <textarea id="aiModalPrompt" class="form-control" rows="3"></textarea>
        </div>

        <div id="aiModalFinalHint" class="mb-2 text-muted small">
          <div class="mb-1">{{ __('panel/common.ai_final_prompt') }}</div>
          <div id="aiModalFinalText" class="p-2 bg-light rounded border"></div>
        </div>

        <button type="button" class="btn btn-primary" id="aiModalGenerateBtn">
          <i class="bi bi-stars me-1"></i>{{ __('panel/common.ai_generate') }}
        </button>

        <div class="mt-3">
          <label class="form-label small fw-medium">{{ __('panel/common.ai_result') }}</label>
          <textarea id="aiModalResult" class="form-control" rows="8" placeholder="{{ __('panel/common.ai_result_placeholder') }}"></textarea>
          <div id="aiModalResultMulti" class="mt-2" style="display:none"></div>
          <div class="text-muted small mt-1">{{ __('panel/common.ai_result_hint') }}</div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('common/base.cancel') }}</button>
        <button type="button" class="btn btn-primary" id="aiModalApplyBtn">{{ __('common/base.confirm') }}</button>
      </div>
    </div>
  </div>
</div>

