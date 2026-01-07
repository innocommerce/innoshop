@extends('layouts.app')

@section('title', __('front/common.page_not_found') . ' - ' . config('app.name'))

@section('body-class', 'page-error device-pc')

@section('content')
  <div class="container py-5">
    <div class="row align-items-center justify-content-center" style="min-height: calc(100vh - 400px);">
      <div class="col-md-6 text-center">
        <div class="mb-4">
          <div class="error-code" style="font-size: 120px; line-height: 1; color: var(--bs-primary, #0d6efd); font-weight: 300;">
            404
          </div>
          <h2 class="h4 mb-3 mt-4">{{ __('front/common.page_not_found') }}</h2>
          <p class="text-secondary mb-4">{{ __('front/common.page_not_found_description') }}</p>
        </div>
        <div class="d-flex gap-3 justify-content-center">
          <a href="javascript:;" onclick="history.back();" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> {{ __('front/common.back_page') }}
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
      text-shadow: 4px 4px 10px rgba(13, 110, 253, 0.1);
      position: relative;
    }

    .error-code::after {
      content: "404";
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

