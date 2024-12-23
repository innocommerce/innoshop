<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ panel_locale_direction() }}">

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
  <meta name="api-token" content="{{ session('api_token') }}">
  <link rel="shortcut icon" href="{{ image_origin(system_setting('favicon', 'images/favicon.png')) }}">

  <!-- 基础样式和脚本 -->
  <link rel="stylesheet" href="{{ mix('build/panel/css/bootstrap.css') }}">
  <link rel="stylesheet" href="{{ mix('build/panel/css/app.css') }}">
  <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
  <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('vendor/layer/3.5.1/layer.js') }}"></script>
  <script src="{{ mix('build/panel/js/app.js') }}"></script>
  <script>
    let urls = {
      base_url: '{{ panel_route('home.index') }}',
      upload_images: '{{ panel_route('upload.images') }}',
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
  <div class="container-fluid">
    @yield('content')
      <div class="page-bottom-btns my-4">
          @yield('page-bottom-btns')
      </div>
  </div>
  @stack('footer')
</body>

</html>
