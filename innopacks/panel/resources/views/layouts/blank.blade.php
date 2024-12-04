<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ panel_locale_direction() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <base href="{{ panel_route('home.index') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-token" content="{{ session('api_token') }}">
    <title>@yield('title')</title>

    <!-- 基础样式和脚本 -->
    <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/vue/2.7/vue.min.js') }}"></script>
    <link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
    <script src="https://unpkg.com/element-ui/lib/index.js"></script>
    <link rel="stylesheet" href="{{ mix('build/panel/css/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ mix('build/panel/css/app.css') }}">
    <script src="{{ asset('vendor/layer/3.5.1/layer.js') }}"></script>
    <script src="{{ mix('build/panel/js/app.js') }}"></script>

    @stack('header')
</head>
<body>
    <div class="main-content">
        @yield('content')
    </div>

    @stack('footer')
</body>
</html>
