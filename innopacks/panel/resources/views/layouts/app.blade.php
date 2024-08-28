<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="{{ panel_route('home.index') }}">
  <title>@yield('title', '') - InnoShop</title>
  <meta name="keywords" content="@yield('keywords', 'InnoShop, 创新, 开源, CMS, Laravel 11, 多语言, 多货币, Hook, 插件架构, 灵活, 强大')">
  <meta name="generator" content="InnoShop {{ innoshop_version() }}">
  <meta name="asset" content="{{ asset('/') }}">
  <meta name="description" content="@yield('description', 'InnoShop')">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="shortcut icon" href="{{ image_origin(system_setting('favicon', 'images/favicon.png')) }}">
  <link rel="stylesheet" href="{{ mix('build/panel/css/bootstrap.css') }}">
  <link rel="stylesheet" href="{{ mix('build/panel/css/app.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/element-plus/index.css') }}">
  <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
  <script src="{{ mix('build/panel/js/app.js') }}"></script>
  <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('vendor/layer/3.5.1/layer.js') }}"></script>
  <script>
    let urls = {
      upload_images: '{{ front_route('upload.images') }}',
      ai_generate: '{{ panel_route('content_ai.generate') }}',
    }

    const lang = {
      hint: '{{ __('panel/common.hint') }}',
      delete_confirm: '{{ __('panel/common.delete_confirm') }}',
      confirm: '{{ __('panel/common.confirm') }}',
      cancel: '{{ __('panel/common.cancel') }}',
    }
  </script>
  @stack('header')
</head>

<body class="@yield('body-class')">
@include('panel::layouts.header')
<div class="main-content">
  <aside class="sidebar-box navbar-expand-xs border-radius-xl">
    <div class="sidebar-body">
      <x-panel-sidebar></x-panel-sidebar>
    </div>
    <div class="mb-menu-close"><i class="bi bi-chevron-left"></i></div>
  </aside>

  <div id="content">
    <div class="page-title-box py-1 d-flex align-items-center justify-content-between">
      <div class="d-flex">
        <h4 class="page-title mb-0">@yield('title')</h4>
        <div class="ms-4 text-danger">@yield('page-title-after')</div>
      </div>
      <div class="text-nowrap">@yield('page-title-right')</div>
    </div>

    <div class="container-fluid p-0 mt-3">
      <div class="content-info">
        @if (session()->has('errors'))
          <x-common-alert type="danger" msg="{{ session('errors')->first() }}" class="mt-4"/>
        @endif
        @if (session('success'))
          <x-common-alert type="success" msg="{{ session('success') }}" class="mt-4"/>
        @endif
        @yield('content')
      </div>

      <div class="page-bottom-btns">
        @yield('page-bottom-btns')
      </div>

      <p class="text-center text-secondary mt-5">
        <a href="https://www.innoshop.com" class="ms-2" target="_blank">InnoShop</a>
        {{ innoshop_version() }} &copy; {{ date('Y') }} All Rights
        Reserved
      </p>
    </div>
  </div>
</div>

@include('panel::layouts.footer')

@stack('footer')
</body>

</html>