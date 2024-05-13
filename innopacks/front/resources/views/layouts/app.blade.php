<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="{{ route('home.index') }}">
  <title>@yield('title', system_setting('meta_title', 'Innoshop - 创新的开源电子商务 | Laravel 11，多语言和多货币支持，基于Hook的强大插件架构'))</title>
  <meta name="keywords" content="@yield('keywords', system_setting('meta_keywords', 'innoshop, 创新, 开源, 电子商务, Laravel 11, 多语言, 多货币, Hook, 插件架构, 灵活, 强大'))">
  <meta name="description" content="@yield('description', system_setting('meta_description', 'innoshop是一款创新的开源电子商务平台，基于Laravel 11开发，具有多语言和多货币支持的特性。它采用了基于Hook的强大而灵活的插件架构，为用户提供了丰富的定制和扩展功能。欢迎体验innoshop，打造属于您自己的电子商务平台！'))">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="shortcut icon" href="{{ image_origin(system_setting('favicon', 'images/favicon.png')) }}">
  <link rel="stylesheet" href="{{ mix('build/css/bootstrap.css') }}">
  <link rel="stylesheet" href="{{ mix('build/css/app.css') }}">
  <script src="{{ mix('build/js/app.js') }}"></script>
  <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  @stack('header')
</head>

<body class="@yield('body-class')">
  @include('front::layouts.header')

  @yield('content')

  @include('front::layouts.footer')

  @stack('footer')
</body>

</html>