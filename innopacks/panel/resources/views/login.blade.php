<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="{{ route('home.index') }}">
  <title>@yield('title', 'InnoShop 后台管理')</title>
  <meta name="keywords"
        content="@yield('keywords', 'InnoShop, 创新, 开源, CMS, Laravel 11, 多语言, 多货币, Hook, 插件架构, 灵活, 强大')">
  <meta name="description" content="@yield('description', 'InnoShop')">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}">
  <link rel="stylesheet" href="{{ mix('build/panel/css/bootstrap.css') }}">
  <link rel="stylesheet" href="{{ mix('build/panel/css/app.css') }}">
  <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
  <script src="{{ mix('build/panel/js/app.js') }}"></script>
  <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('vendor/layer/3.5.1/layer.js') }}"></script>
  @stack('header')
</head>
<body class="page-login">
  <div class="d-flex align-items-center vh-100 pt-2 pt-sm-5 pb-4 pb-sm-5">
    <div class="container">
      <div class="card login-content">
          <div class="card-header">
            <h3 class="fw-bold text-center">登录后台</h3>
          </div>

          <div class="card-body">
            <form action="{{ panel_route('login.store') }}" method="post">
              @csrf

              <div class="form-floating mb-4">
                <input type="text" name="email" class="form-control" id="email-input" value="{{ old('email', $admin_email ?? '') }}" placeholder="{{ __('common.email') }}">
                <label for="email-input">账号</label>
                @error('email')
                  <x-panel-alert :msg="@error('email')" />
                @enderror
              </div>

              <div class="form-floating mb-5">
                <input type="password" name="password" class="form-control" id="password-input" value="{{ old('password', $admin_password ?? '') }}" placeholder="{{ __('shop/login.password') }}">
                <label for="password-input">密码</label>
                @error('password')
                  <x-panel-alert :msg="@error('password')" />
                @enderror
              </div>

              @if (session('error'))
                <div class="alert alert-danger">
                  {{ session('error') }}
                </div>
              @endif

              <div class="d-grid mb-4"><button type="submit" class="btn btn-lg btn-primary">提交</button></div>
            </form>
          </div>
      </div>

      <p class="text-center text-secondary mt-5">
        <a href="https://www.innoshop.com" class="ms-2" target="_blank">InnoShop</a> &copy; {{ date('Y') }} All Rights
        Reserved</p>
    </div>
  </div>
</body>
</html>
