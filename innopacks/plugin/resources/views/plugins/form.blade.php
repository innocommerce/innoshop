@extends('panel::layouts.app')

@section('title', $plugin->getLocaleName())

@if($plugin->checkInstalled())
<x-panel::form.right-btns />
@endif

@section('content')
<div class="card h-min-600">
  <div class="card-body">
    <div class="row mb-4 align-items-center pb-3 plugin-header-bar">
      <div class="col-auto">
        @if($plugin->getIconUrl())
          <img src="{{ $plugin->getIconUrl() }}" alt="logo" class="img-fluid rounded me-4 plugin-header-logo">
        @endif
      </div>
      <div class="col ps-0">
        <div class="fw-bold fs-4 mb-1">
          {{ $plugin->getLocaleName() }}
          @if($plugin->getVersion())
            <small class="text-muted ms-2">{{ $plugin->getVersion() }}</small>
          @endif
        </div>
        @if($plugin->getLocaleDescription())
          <div class="mb-2 text-secondary plugin-header-desc">{{ $plugin->getLocaleDescription() }}</div>
        @endif
        <div class="d-flex flex-wrap gap-3 align-items-center">
          <div class="text-secondary small">
            <i class="bi bi-person"></i> {{ __('panel/plugin.author') }}: {{ $plugin->getAuthorName() }}
            @if($plugin->getAuthorEmail())
              <span class="ms-2"><i class="bi bi-envelope"></i> {{ __('panel/plugin.email') }}: <a href="mailto:{{ $plugin->getAuthorEmail() }}" class="text-decoration-none">{{ $plugin->getAuthorEmail() }}</a></span>
            @endif
          </div>
          @if($plugin->getType())
            <div class="text-secondary small"><i class="bi bi-tag"></i> {{ __('panel/plugin.type') }}：{{ $plugin->getTypeFormat() }}</div>
          @endif
        </div>
      </div>
    </div>

    @php
      $readmeHtml = $plugin->getReadmeHtml();
    @endphp

    @if(!$plugin->checkInstalled())
      <div class="text-center py-5">
        <p class="text-secondary mb-3">{{ trans('panel/plugin.not_installed_hint') }}</p>
        <button type="button" class="btn btn-primary btn-install-plugin" data-code="{{ $plugin->getCode() }}">
          <i class="bi bi-puzzle-fill"></i> {{ __('panel/common.install') }}
        </button>
      </div>
    @else
    <ul class="nav nav-tabs mt-4" role="tablist">
      <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#config-tab" role="tab">
          <i class="bi bi-gear"></i> {{ trans('panel/plugin.config_settings') }}
        </button>
      </li>
      @if(!empty($readmeHtml) && trim($readmeHtml) !== '')
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#readme-tab" role="tab">
          <i class="bi bi-book"></i> {{ trans('panel/plugin.usage_documentation') }}
        </button>
      </li>
      @endif
      <li class="nav-item ms-auto d-flex gap-2 align-items-start">
        @if($plugin->checkInstalled() && $plugin->getEnabled() && $plugin->getMenuUrl())
        <a href="{{ $plugin->getMenuUrl() }}" target="_blank" class="btn btn-success btn-sm mt-1"
           data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('panel/plugin.use_goto_page') }}">
          <i class="bi bi-box-arrow-up-right"></i> {{ __('common/base.use') }}
        </a>
        @endif
        @if($plugin->hasSeeders())
        <button type="button" class="btn btn-outline-primary btn-sm mt-1 plugin-action-btn"
                data-bs-toggle="tooltip" data-bs-placement="top"
                data-modal="seederConfirmModal" title="{{ __('panel/plugin.import_seed_data_hint') }}">
          <i class="bi bi-database-fill-add"></i> {{ __('panel/plugin.import_seed_data') }}
        </button>
        @endif
        <button type="button" class="btn btn-outline-warning btn-sm mt-1 plugin-action-btn"
                data-bs-toggle="tooltip" data-bs-placement="top"
                data-modal="resetConfirmModal" title="{{ __('panel/plugin.reset_hint') }}">
          <i class="bi bi-arrow-counterclockwise"></i> {{ __('panel/common.reset') }}
        </button>
      </li>
    </ul>

    <div class="tab-content mt-3">
      <div class="tab-pane fade show active" id="config-tab" role="tabpanel">
        @if(!empty($customView))
          @includeIf($customView, ['plugin' => $plugin, 'fields' => $fields ?? [], 'errors' => $errors ?? []])
        @else
          <form class="needs-validation" id="app-form" novalidate action="{{ panel_route('plugins.update', [$plugin->getCode()]) }}" method="POST">
            @csrf
            {{ method_field('put') }}
            <div class="row">
              <div class="col-12 col-md-8">
                @include('plugin::plugins.fields', ['fields' => $fields, 'errors' => $errors, 'plugin' => $plugin])
              </div>
            </div>
          </form>
        @endif
      </div>
      @if(!empty($readmeHtml) && trim($readmeHtml) !== '')
      <div class="tab-pane fade" id="readme-tab" role="tabpanel">
        <div class="markdown-body">
          {!! $readmeHtml !!}
        </div>
      </div>
      @endif
    </div>
    @endif
  </div>
</div>

@if($plugin->checkInstalled() && $plugin->hasSeeders())
<div class="modal fade" id="seederConfirmModal" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('panel/plugin.seed_confirm_title') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3">{{ __('panel/plugin.seed_confirm_message') }}</p>
        <div class="form-check mb-0">
          <input class="form-check-input seeder-clear-data" type="checkbox" value="1" id="seederClearData">
          <label class="form-check-label" for="seederClearData">
            {{ __('panel/plugin.seed_clear_data') }}
          </label>
        </div>
        <p class="text-muted small mt-2 mb-0">{{ __('panel/plugin.seed_clear_data_hint') }}</p>
        <div class="alert alert-danger mt-3 d-none seeder-error-wrap" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <span class="seeder-error-msg"></span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('panel/plugin.seed_btn_cancel') }}</button>
        <button type="button" class="btn btn-primary btn-confirm-seeder" data-code="{{ $plugin->getCode() }}">
          <span class="spinner-border spinner-border-sm d-none me-2 seeder-spinner" role="status" aria-hidden="true"></span>
          {{ __('panel/plugin.seed_btn_confirm') }}
        </button>
      </div>
    </div>
  </div>
</div>
@endif

@if($plugin->checkInstalled())
<div class="modal fade" id="resetConfirmModal" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('panel/common.reset') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3">{{ __('panel/common.reset_confirm') }}</p>
        @if($plugin->hasSeeders())
        <div class="form-check mb-0">
          <input class="form-check-input reset-clear-data" type="checkbox" value="1" id="resetClearData">
          <label class="form-check-label" for="resetClearData">
            {{ __('panel/plugin.seed_clear_data') }}
          </label>
        </div>
        <p class="text-muted small mt-2 mb-0">{{ __('panel/plugin.seed_clear_data_hint') }}</p>
        @endif
        <div class="alert alert-danger mt-3 d-none reset-error-wrap" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <span class="reset-error-msg"></span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('panel/plugin.seed_btn_cancel') }}</button>
        <button type="button" class="btn btn-warning btn-confirm-reset" data-code="{{ $plugin->getCode() }}">
          <span class="spinner-border spinner-border-sm d-none me-2 reset-spinner" role="status" aria-hidden="true"></span>
          {{ __('panel/common.reset') }}
        </button>
      </div>
    </div>
  </div>
</div>
@endif
@endsection

@push('header')
  <link rel="stylesheet" href="{{ asset('vendor/github/github-markdown.min.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/github/highlight-github.min.css') }}">
  <style>
    .markdown-body {
      max-width: 900px;
      margin: 0 auto;
      padding: 24px;
      background: #fff;
      border-radius: 8px;
      font-size: 14px;
    }
    .plugin-header-bar {
      border-bottom: 1.5px solid #eee;
    }
    .plugin-header-logo {
      max-height: 100px;
      border: 1px solid #e9e9e9;
      box-sizing: border-box;
    }
    .plugin-header-desc {
      font-size: 15px;
    }
  </style>
@endpush

@push('footer')
  <script>
    $(function () {
      new bootstrap.Tooltip(document.querySelector('.btn-success[data-bs-toggle="tooltip"]'));
      $('.plugin-action-btn').each(function () {
        new bootstrap.Tooltip(this);
      }).on('click', function () {
        var target = $(this).data('modal');
        if (target) {
          new bootstrap.Modal('#' + target).show();
        }
      });

      $('.btn-install-plugin').click(function () {
        var code = $(this).data('code');
        axios.post('/{{ panel_name() }}/plugins', {code: code}).then(function (res) {
          if (res && res.success) {
            window.location.reload();
          } else {
            inno.alert(res ? res.message : '{{ __("panel/common/install") }} failed');
          }
        }).catch(function (error) {
          var data = error.response ? error.response.data : {};
          layer.msg(data.message || error.message || '{{ __("panel/common.install") }} failed', { icon: 2 });
        });
      });

      var $seederModal = $('#seederConfirmModal');
      $seederModal.on('show.bs.modal', function () {
        $(this).find('.seeder-clear-data').prop('checked', false);
        $(this).find('.seeder-error-wrap').addClass('d-none');
      });

      $seederModal.on('click', '.btn-confirm-seeder', function () {
        var btn = $(this);
        var code = btn.data('code');
        var clearData = $seederModal.find('.seeder-clear-data').is(':checked');
        var spinner = btn.find('.seeder-spinner');

        btn.prop('disabled', true);
        spinner.removeClass('d-none');
        $seederModal.find('.seeder-error-wrap').addClass('d-none');

        axios.post('/{{ panel_name() }}/plugins/seeders', {code: code, clear_data: clearData}).then(function (res) {
          btn.prop('disabled', false);
          spinner.addClass('d-none');
          if (res && res.success) {
            $seederModal.modal('hide');
            inno.msg(res.message);
          } else {
            $seederModal.find('.seeder-error-msg').text((res && res.message) || 'Failed');
            $seederModal.find('.seeder-error-wrap').removeClass('d-none');
          }
        }).catch(function (error) {
          btn.prop('disabled', false);
          spinner.addClass('d-none');
          var resp = error.response ? error.response.data : {};
          $seederModal.find('.seeder-error-msg').text(resp.message || error.message || 'Failed');
          $seederModal.find('.seeder-error-wrap').removeClass('d-none');
        });
      });

      var $resetModal = $('#resetConfirmModal');
      $resetModal.on('show.bs.modal', function () {
        $(this).find('.reset-clear-data').prop('checked', false);
        $(this).find('.reset-error-wrap').addClass('d-none');
      });

      $resetModal.on('click', '.btn-confirm-reset', function () {
        var btn = $(this);
        var code = btn.data('code');
        var clearData = $resetModal.find('.reset-clear-data').is(':checked');
        var spinner = btn.find('.reset-spinner');

        btn.prop('disabled', true);
        spinner.removeClass('d-none');
        $resetModal.find('.reset-error-wrap').addClass('d-none');

        axios.post('/{{ panel_name() }}/plugins/reset', {code: code, clear_data: clearData}).then(function (res) {
          btn.prop('disabled', false);
          spinner.addClass('d-none');
          if (res && res.success) {
            $resetModal.modal('hide');
            inno.msg(res.message);
          } else {
            $resetModal.find('.reset-error-msg').text((res && res.message) || 'Failed');
            $resetModal.find('.reset-error-wrap').removeClass('d-none');
          }
        }).catch(function (error) {
          btn.prop('disabled', false);
          spinner.addClass('d-none');
          var resp = error.response ? error.response.data : {};
          $resetModal.find('.reset-error-msg').text(resp.message || error.message || 'Failed');
          $resetModal.find('.reset-error-wrap').removeClass('d-none');
        });
      });
    });
  </script>
@endpush
