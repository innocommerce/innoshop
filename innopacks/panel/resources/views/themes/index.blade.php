@extends('panel::layouts.app')
@section('body-class', 'theme')

@section('title', __('panel/menu.themes'))

@section('page-title-right')
  <button type="button" class="btn btn-outline-info me-2" data-bs-toggle="modal" data-bs-target="#themeGuideModal">
    <i class="bi bi-info-circle me-1"></i>
    {{ __('panel/themes.theme_guide_title') }}
  </button>
  <a href="{{ panel_route('theme-market.index') }}" class="btn btn-primary">{{ __('panel/common.get_more') }}</a>
@endsection

@section('content')
@if(!empty($errors))
<div class="alert alert-warning alert-dismissible fade show" role="alert">
  <h6 class="alert-heading mb-2">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    {{ __('panel/themes.error_theme_validation') }}
  </h6>
  <ul class="mb-0 ps-3">
    @foreach($errors as $error)
    <li><strong>{{ $error['name'] }}</strong> ({{ $error['folder'] }}): {{ $error['error'] }}</li>
    @endforeach
  </ul>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card h-min-600">
  <div class="card-body p-4">
    <div class="d-flex flex-wrap align-items-center gap-2 gap-md-3 text-muted small mb-3 pb-3 border-bottom">
      <span>
        <i class="bi bi-palette me-1"></i>
        {{ __('panel/themes.available_themes_count', ['count' => $themes_count ?? 0]) }}
      </span>
      <span class="text-secondary">·</span>
      <span>{{ __('panel/themes.themes_stat_demo') }}: {{ $themes_with_demo_count }}</span>
      <span class="text-secondary">·</span>
      <span class="text-truncate" style="max-width: 100%;" title="{{ $selected_theme_name ?? '' }}">{{ __('panel/themes.themes_stat_current') }}: {{ $selected_theme_name ?? __('panel/themes.themes_stat_none') }}</span>
    </div>
    @if($themes)
      <div class="themes-wrap">
        <div class="row g-4">
          @foreach($themes as $theme)
          <div class="col-6 col-lg-4 col-xxl-3">
            <div class="card themes-item overflow-hidden h-100 border @if($theme['selected']) theme-current @endif">
              <div class="theme-image-wrapper position-relative">
                <img src="{{ theme_image($theme['preview'], $theme['code'], 900, 600, 'cover') }}"
                     class="theme-preview-image"
                     alt="{{ $theme['name'] }}"
                     data-preview-src="{{ theme_image($theme['preview'], $theme['code'], 1350, 900, 'cover') }}">
                <div class="theme-overlay">
                  <button type="button" 
                          class="btn btn-light btn-sm theme-preview-btn"
                          data-bs-toggle="modal" 
                          data-bs-target="#themePreviewModal{{ $theme['code'] }}">
                    <i class="bi bi-zoom-in me-1"></i>
                    {{ __('panel/common.preview') }}
                  </button>
                </div>
                @if($theme['selected'])
                  <div class="theme-current-badge">
                    <i class="bi bi-check-circle-fill me-1"></i>
                    {{ __('panel/themes.current_theme') }}
                  </div>
                @endif
              </div>
              <div class="card-body d-flex flex-column">
                <div class="theme-header mb-3">
                  <h6 class="theme-name mb-2 fw-semibold @if($theme['selected']) text-primary @endif d-flex align-items-center gap-2">
                    <span class="text-truncate" title="{{ $theme['name'] }}" data-bs-toggle="tooltip">{{ $theme['name'] }}</span>
                    @if(data_get($theme, 'has_demo'))
                      <span class="badge bg-warning text-dark flex-shrink-0 ms-auto">
                        <i class="bi bi-database me-1"></i>{{ __('panel/themes.theme_badge_demo') }}
                      </span>
                    @endif
                  </h6>
                  <div class="theme-meta d-flex text-muted small w-100">
                    <span class="flex-fill d-flex align-items-center text-truncate" title="{{ $theme['code'] }}">
                      <i class="bi bi-code-slash me-1" style="font-size: 0.7rem;"></i>
                      <span class="font-monospace text-truncate" style="max-width: 90px; display: inline-block;">{{ $theme['code'] }}</span>
                    </span>
                    @if(isset($theme['version']) && $theme['version'])
                      <span class="flex-fill d-flex align-items-center">
                        <i class="bi bi-tag-fill me-1" style="font-size: 0.7rem;"></i>
                        {{ $theme['version'] }}
                      </span>
                    @endif
                    @if(isset($theme['author']['name']) && $theme['author']['name'])
                      <span class="flex-fill d-flex align-items-center text-truncate" title="{{ $theme['author']['name'] }}">
                        <i class="bi bi-person-fill me-1" style="font-size: 0.7rem;"></i>
                        <span class="text-truncate" style="max-width: 90px; display: inline-block;">{{ $theme['author']['name'] }}</span>
                      </span>
                    @endif
                  </div>
                </div>
                <div class="mt-auto d-flex justify-content-between align-items-center">
                  <button type="button" 
                          class="btn btn-sm btn-outline-secondary"
                          data-bs-toggle="modal" 
                          data-bs-target="#themeDetail{{ $theme['code'] }}">
                    <i class="bi bi-eye me-1"></i>
                    {{ __('common/base.view') }}
                  </button>
                  @include('panel::shared.list_switch', [
                    'value' => $theme['selected'] ?? false,
                    'url' => panel_route('themes.active', $theme['code']),
                    'reload' => false,
                    'class' => 'theme-switch',
                    'data_code' => $theme['code'],
                  ])
                </div>
              </div>
            </div>
          </div>

          @include('panel::themes.modals.detail', ['theme' => $theme])
          @include('panel::themes.modals.preview', ['theme' => $theme])
          
          @endforeach
        </div>
      </div>
    @else
    <x-common-no-data :text="__('panel/themes.no_custom_theme')" />
    @endif
  </div>
</div>

{{-- Theme Guide Modal --}}
<div class="modal fade" id="themeGuideModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-info-circle-fill text-info me-2"></i>
          {{ __('panel/themes.theme_guide_title') }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted mb-3">{{ __('panel/themes.theme_guide_desc') }}</p>
        <div class="list-group list-group-flush">
          <div class="list-group-item px-0">
            <div class="d-flex align-items-start">
              <i class="bi bi-image text-primary me-3 mt-1"></i>
              <div>
                <strong>{{ __('panel/themes.theme_guide_preview_title') }}</strong>
                <p class="text-muted small mb-0 mt-1">{{ __('panel/themes.theme_guide_preview') }}</p>
              </div>
            </div>
          </div>
          <div class="list-group-item px-0">
            <div class="d-flex align-items-start">
              <i class="bi bi-file-image text-success me-3 mt-1"></i>
              <div>
                <strong>{{ __('panel/themes.theme_guide_icon_title') }}</strong>
                <p class="text-muted small mb-0 mt-1">{{ __('panel/themes.theme_guide_icon') }}</p>
              </div>
            </div>
          </div>
          <div class="list-group-item px-0 border-bottom-0">
            <div class="d-flex align-items-start">
              <i class="bi bi-file-code text-warning me-3 mt-1"></i>
              <div>
                <strong>{{ __('panel/themes.theme_guide_config_title') }}</strong>
                <p class="text-muted small mb-0 mt-1">{{ __('panel/themes.theme_guide_config') }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
          {{ __('common/base.confirm') }}
        </button>
      </div>
    </div>
  </div>
</div>

@push('footer')
<script>
$(function () {
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) { new bootstrap.Tooltip(el); });
  var demoInstalledMsg = @json(__('panel/themes.demo_installed'));
  var importFailedMsg = @json(__('panel/themes.import_failed'));
  var operationFailedMsg = @json(__('panel/common.operation_failed'));
  var currentThemeLabel = @json(__('panel/themes.current_theme'));
  var csrf = function () {
    return $('meta[name="csrf-token"]').attr('content');
  };

  function applyActiveTheme(activeCode) {
    var found = false;
    $('.themes-item').each(function () {
      var $card = $(this);
      var $wrap = $card.find('.theme-switch');
      var code = $wrap.attr('data-code');
      var isActive = (code === activeCode);

      if (isActive) found = true;

      $card.toggleClass('theme-current', isActive);
      $wrap.find('input[role="switch"]').prop('checked', isActive);

      var $badge = $card.find('.theme-current-badge');
      if (isActive) {
        if (!$badge.length) {
          $card.find('.theme-image-wrapper').append(
            '<div class="theme-current-badge"><i class="bi bi-check-circle-fill me-1"></i>' + currentThemeLabel + '</div>'
          );
        }
      } else {
        $badge.remove();
      }

      $card.find('.theme-name').toggleClass('text-primary', isActive);
    });
    console.log('[theme] applyActiveTheme:', activeCode || '(none)', 'found:', found);
  }

  function getErrMsg(err) {
    if (err.response && err.response.data) {
      return err.response.data.message || err.response.data.error || operationFailedMsg;
    }
    return operationFailedMsg;
  }

  $(document).on('click', '.theme-enable-btn', function (e) {
    var $btn = $(this);
    if (!$btn.closest('[id^="themeDetail"]').length) {
      return;
    }
    var url = $btn.data('url');
    var $modal = $btn.closest('.modal');
    if (!url || !$modal.length) {
      return;
    }
    e.preventDefault();
    layer.load(2, {shade: [0.3, '#fff']});
    $.ajax({
      url: url,
      type: 'PUT',
      contentType: 'application/json',
      data: JSON.stringify({status: 1}),
      processData: false,
      headers: {'X-CSRF-TOKEN': csrf()},
      success: function (res) {
        var activeCode = (res.data && res.data.active_code) || '';
        applyActiveTheme(activeCode);
        inno.msg(res.message);
        var inst = bootstrap.Modal.getInstance($modal[0]);
        if (inst) {
          inst.hide();
        }
      },
      error: function (xhr) {
        var msg = operationFailedMsg;
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }
        inno.msg(msg);
      },
      complete: function () {
        layer.closeAll('loading');
      },
    });
  });

  $(document).on('change', '.theme-switch > input[role="switch"]', function () {
    var $input = $(this);
    var checked = $input.prop('checked');
    var status = checked ? 1 : 0;
    var $wrap = $input.closest('.theme-switch');
    var url = $wrap.attr('data-url');

    layer.load(2, {shade: [0.3,'#fff']});
    axios.put(url, {status: status}, {
      headers: {'X-CSRF-TOKEN': csrf()}
    }).then(function (res) {
      var activeCode = (res.data && res.data.active_code) || '';
      applyActiveTheme(activeCode);
      inno.msg(res.message || '');
    }).catch(function (err) {
      $input.prop('checked', !checked);
      inno.msg(getErrMsg(err));
    }).finally(function () {
      layer.closeAll('loading');
    });
  });

  $(document).on('click', '.btn-import-demo-confirm', function (e) {
    e.preventDefault();
    var $btn = $(this);
    var $modal = $btn.closest('.import-demo-confirm-modal');
    var url = $btn.attr('data-demo-import-url');
    var $err = $modal.find('.import-demo-error-wrap');
    var $errMsg = $modal.find('.import-demo-error-msg');
    var $spin = $modal.find('.import-demo-spinner');
    if (!url) {
      return;
    }
    $err.addClass('d-none');
    $errMsg.text('');
    $spin.removeClass('d-none');
    $btn.prop('disabled', true);
    var clearCatalog = $modal.find('.import-demo-clear-catalog').is(':checked');
    $.ajax({
      url: url,
      type: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({clear_default_catalog: clearCatalog}),
      processData: false,
      headers: {'X-CSRF-TOKEN': csrf()},
      success: function (data) {
        if (data.success) {
          var inst = bootstrap.Modal.getInstance($modal[0]);
          if (inst) {
            inst.hide();
          }
          inno.msg(data.message || demoInstalledMsg);
        } else {
          $errMsg.text(data.message || data.error || importFailedMsg);
          $err.removeClass('d-none');
          $err[0].scrollIntoView({behavior: 'smooth', block: 'nearest'});
        }
      },
      error: function (xhr) {
        var msg = importFailedMsg;
        if (xhr.responseJSON) {
          msg = xhr.responseJSON.message || xhr.responseJSON.error || msg;
        }
        $errMsg.text(msg);
        $err.removeClass('d-none');
        $err[0].scrollIntoView({behavior: 'smooth', block: 'nearest'});
      },
      complete: function () {
        $spin.addClass('d-none');
        $btn.prop('disabled', false);
      },
    });
  });

  $(document).on('show.bs.modal', '.import-demo-confirm-modal', function () {
    $(this).find('.import-demo-clear-catalog').prop('checked', false);
    var code = $(this).attr('data-theme-code');
    var $detail = $('#themeDetail' + code);
    if ($detail.length && bootstrap.Modal.getInstance($detail[0])) {
      $detail.attr('data-bs-backdrop', 'static');
    }
  });

  $(document).on('hidden.bs.modal', '.import-demo-confirm-modal', function () {
    var code = $(this).attr('data-theme-code');
    $('#themeDetail' + code).removeAttr('data-bs-backdrop');
  });
});
</script>
@endpush
@endsection
