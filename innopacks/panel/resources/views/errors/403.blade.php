@extends('panel::layouts.app')

@section('content')
  <div class="container h-100">
    <div class="row align-items-center justify-content-center" style="min-height: calc(100vh - 200px);">
      <div class="col-md-6 text-center">
        <div class="mb-4">
          <div class="error-code" style="font-size: 100px; line-height: 1; color: var(--bs-primary); font-weight: 300;">
            403
          </div>
          <h2 class="h4 mb-3 mt-4">{{ __('panel/error.403_title') }}</h2>
          <p class="text-secondary mb-4">{{ __('panel/error.403_description') }}</p>
        </div>
        <div class="d-flex gap-3 justify-content-center">
          <a href="javascript:;" onclick="history.back();" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> {{ __('panel/error.403_back') }}
          </a>
          <a href="{{ panel_route('home.index') }}" class="btn btn-primary">
            <i class="bi bi-house me-1"></i> {{ __('panel/error.403_home') }}
          </a>
        </div>
      </div>
    </div>
  </div>

  <style>
    .error-code {
      text-shadow: 4px 4px 10px rgba(var(--bs-primary-rgb), 0.1);
      position: relative;
    }

    .error-code::after {
      content: "403";
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
      font-size: 120px;
      opacity: 0.03;
      letter-spacing: 0.1em;
    }
  </style>
@endsection
