@extends('panel::layouts.app')
@section('body-class', 'theme')

@section('title', __('panel/menu.themes'))

@section('page-title-right')
  <a href="{{ panel_route('theme_market.index') }}" class="btn btn-primary">{{ __('panel/common.get_more') }}</a>
@endsection

@section('content')
<div class="card h-min-600">
  <div class="card-body p-4">
    @if($themes)
      <div class="themes-wrap">
        <div class="row g-4">
          @foreach($themes as $theme)
          <div class="col-6 col-lg-4 col-xxl-3">
            <div class="card themes-item overflow-hidden h-100 border @if($theme['selected']) theme-current @endif">
              <div class="theme-image-wrapper position-relative">
                <img src="{{ theme_image($theme['preview'], $theme['code'], 800, 600) }}" 
                     class="theme-preview-image" 
                     alt="{{ $theme['name'] }}"
                     data-preview-src="{{ theme_image($theme['preview'], $theme['code'], 1200, 900) }}">
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
                    {{ __('panel/common.view') }}
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
@endsection
