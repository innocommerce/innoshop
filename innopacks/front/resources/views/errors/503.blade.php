@extends('layouts.app')

@section('title', __('front/common.service_unavailable') . ' - ' . config('app.name'))

@section('body-class', 'page-error device-pc')

@section('content')
  <div class="container py-5">
    <div class="row align-items-center justify-content-center" style="min-height: calc(100vh - 400px);">
      <div class="col-md-6 text-center">
        <div class="mb-4">
          <div class="error-code" style="font-size: 120px; line-height: 1; color: var(--bs-warning, #ffc107); font-weight: 300;">
            503
          </div>
          <h2 class="h4 mb-3 mt-4">{{ __('front/common.service_unavailable') }}</h2>
          <p class="text-secondary mb-4">{{ __('front/common.service_unavailable_description') }}</p>
        </div>
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
      content: "503";
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

