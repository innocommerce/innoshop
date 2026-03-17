@extends('panel::layouts.app')
@section('body-class', 'theme')

@section('title', __('panel/menu.themes'))

@section('page-title-right')
  <button type="button" class="btn btn-outline-info me-2" data-bs-toggle="modal" data-bs-target="#themeGuideModal">
    <i class="bi bi-info-circle me-1"></i>
    {{ __('panel/themes.theme_guide_title') }}
  </button>
  <a href="{{ panel_route('theme_market.index') }}" class="btn btn-primary">{{ __('panel/common.get_more') }}</a>
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
                  <h6 class="theme-name mb-2 fw-semibold @if($theme['selected']) text-primary @endif">
                    {{ $theme['name'] }}
                  </h6>
                  <div class="theme-meta d-flex align-items-center gap-3 text-muted small">
                    @if(isset($theme['version']) && $theme['version'])
                      <span class="theme-version d-flex align-items-center">
                        <i class="bi bi-tag-fill me-1" style="font-size: 0.7rem;"></i>
                        {{ $theme['version'] }}
                      </span>
                    @endif
                    @if(isset($theme['author']['name']) && $theme['author']['name'])
                      <span class="theme-author d-flex align-items-center">
                        <i class="bi bi-person-fill me-1" style="font-size: 0.7rem;"></i>
                        {{ $theme['author']['name'] }}
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
                    'reload' => true
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
@endsection
