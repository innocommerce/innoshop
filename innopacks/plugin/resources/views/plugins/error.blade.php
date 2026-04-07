@extends('panel::layouts.app')

@section('title', __('panel/plugin.plugin_load_error_title'))

@section('page-title-right')
  <a href="{{ panel_route('plugins.index') }}" class="btn btn-outline-secondary me-2">{{ __('panel/plugin.back_to_plugin_list') }}</a>
  <a href="{{ panel_route('plugin-market.index') }}" class="btn btn-primary">{{ __('panel/common.get_more') }}</a>
@endsection

@section('content')
  <div class="card h-min-600 border-0 shadow-sm">
    <div class="card-body p-4 p-lg-5">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10 text-danger mb-3" style="width: 4rem; height: 4rem;">
              <i class="bi bi-puzzle-fill" style="font-size: 1.75rem;"></i>
            </div>
            <h5 class="fw-semibold mb-2">{{ __('panel/plugin.plugin_load_error_heading') }}</h5>
            <p class="text-secondary mb-0 small">{{ __('panel/plugin.plugin_load_error_description') }}</p>
          </div>

          @if (! empty($plugin_code))
            <div class="mb-4 text-center">
              <span class="text-muted small d-block mb-1">{{ __('panel/plugin.plugin_code_label') }}</span>
              <code class="px-3 py-2 rounded bg-light border d-inline-block">{{ $plugin_code }}</code>
            </div>
          @endif

          @if (session()->has('errors'))
            <x-common-alert type="danger" msg="{{ session('errors')->first() }}" class="mb-3"/>
          @endif

          @if (session('success'))
            <x-common-alert type="success" msg="{{ session('success') }}" class="mb-3"/>
          @endif

          <div class="alert alert-danger border-0 shadow-sm" role="alert">
            <div class="d-flex align-items-start gap-2">
              <i class="bi bi-exclamation-octagon-fill flex-shrink-0 mt-1"></i>
              <div class="flex-grow-1 min-w-0">
                <div class="fw-semibold mb-1">{{ __('panel/plugin.plugin_error_detail_label') }}</div>
                <pre class="mb-0 text-break small text-danger" style="white-space: pre-wrap;">{{ $error }}</pre>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
