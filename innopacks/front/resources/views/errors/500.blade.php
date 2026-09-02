@extends('layouts.app')

@section('title', __('front/common.server_error') . ' - ' . config('app.name'))

@section('body-class', 'page-error device-pc')

@section('content')
  <div class="container d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 64px);">
    <div class="row justify-content-center w-100">
      <div class="col-md-6 text-center">
        <div class="error-code mb-3" style="font-size: 120px; line-height: 1; color: var(--bs-warning, #ffc107); font-weight: 300;">
          500
        </div>
        <h2 class="h4 mb-3">{{ __('front/common.server_error') }}</h2>
        <p class="text-secondary mb-4">{{ __('front/common.server_error_description') }}</p>
        <div class="d-flex gap-3 justify-content-center">
          <a href="javascript:;" onclick="location.reload();" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-clockwise me-1"></i> {{ __('front/common.refresh') }}
          </a>
          <a href="{{ front_route('home.index') }}" class="btn btn-primary">
            <i class="bi bi-house me-1"></i> {{ __('front/common.home') }}
          </a>
        </div>
      </div>
    </div>
  </div>

  <style>
    .error-code {
      text-shadow: 4px 4px 10px rgba(255, 193, 7, 0.1);
      position: relative;
    }

    .error-code::after {
      content: "500";
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
      font-size: 140px;
      opacity: 0.03;
      letter-spacing: 0.1em;
    }
  </style>
@endsection