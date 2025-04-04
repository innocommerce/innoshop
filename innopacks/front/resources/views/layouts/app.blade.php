<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ front_locale_direction() }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="{{ front_route('home.index') }}">
  <title>@yield('title', system_setting_locale('meta_title', 'InnoShop - 创新的开源电商系统 | 开源独立站系统 | Laravel 12，多语言和多货币支持'))</title>
  <meta name="description" content="@yield('description', system_setting_locale('meta_description', 'innoshop是一款创新的开源电子商务平台，基于Laravel 12开发，具有多语言和多货币支持的特性。它采用了基于Hook的强大而灵活的插件架构，为用户提供了丰富的定制和扩展功能。欢迎体验innoshop，打造属于您自己的电子商务平台！'))">
  <meta name="keywords" content="@yield('keywords', system_setting_locale('meta_keywords', 'innoshop, 创新, 开源, 电商, 跨境电商, 开源独立站, Laravel 12, 多语言, 多货币, Hook, 插件架构, 灵活, 强大'))">
  <meta name="generator" content="InnoShop {{ innoshop_version() }}">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="api-token" content="{{ session('front_api_token') }}">
  <link rel="shortcut icon" href="{{ image_origin(system_setting('favicon', 'images/favicon.png')) }}">
  <link rel="stylesheet" href="{{ mix('build/front/css/bootstrap.css') }}">
  <script src="{{ mix('build/front/js/app.js') }}"></script>
  <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
  <script src="{{ asset('vendor/layer/3.5.1/layer.js') }}"></script>
  <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <link rel="stylesheet" href="{{ mix('build/front/css/app.css') }}">
  <script>
    let urls = {
      api_base: '{{ route('api.home.base') }}',
      base_url: '{{ front_route('home.index') }}',
      upload_images: '{{ front_root_route('upload.images') }}',
      cart_add: '{{ front_route('carts.store') }}',
      cart_mini: '{{ front_route('carts.mini') }}',
      cart: '{{ front_route('carts.index') }}',
      checkout: '{{ front_route('checkout.index') }}',
      login: '{{ front_route('login.index') }}',
      favorites: '{{ account_route('favorites.index') }}',
      favorite_cancel: '{{ account_route('favorites.cancel') }}',
    }

    let config = {
      isLogin: !!{{ current_customer()->id ?? 'null' }},
    }

    let translations = {
      empty_cart: '{{ __('front/cart.empty_cart') }}',
      continue: '{{ __('front/cart.continue') }}',
      total: '{{ __('front/cart.total') }}',
      go_checkout: '{{ __('front/cart.go_checkout') }}',
      view_cart: '{{ __('front/cart.view_cart') }}',
    }

    let asset_url = '{{ asset('') }}';
  </script>
  @stack('header')
  @hookinsert('front.layout.app.head.bottom')
</head>

<body class="@yield('body-class')">
  @if (!request('iframe'))
    @include('layouts.header')
  @endif

  <div class="m-0 p-0" id="appContent">
      @yield('content')
  </div>

  @if (!request('iframe'))
    @include('layouts.footer')
  @endif

  <!-- Cart Offcanvas (Desktop only) -->
  @if (!request('iframe'))
    @php
      $cartData = \InnoShop\Common\Services\CartService::getInstance()->getCartList();
      $list = $cartData['list'] ?? [];
      $amount_format = $cartData['amount_format'] ?? '0.00';
    @endphp
    @include('components.cart-offcanvas', ['list' => $list, 'amount_format' => $amount_format])
  @endif

  @stack('footer')
</body>

</html>
