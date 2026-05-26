<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ panel_locale_direction() }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="{{ panel_route('home.index') }}">
  <title>ipanel CLI Login</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}">
  <link rel="stylesheet" href="{{ asset('build/panel/css/app.css') }}">
  <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
  <script src="{{ asset('build/panel/js/app.js') }}"></script>
  <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('vendor/layer/3.5.1/layer.js') }}"></script>
</head>
<body class="page-login">
  <div class="">
    <div class="container vh-100 pt-2 pt-sm-5 pb-4 pb-sm-5">
      <div class="login-wrap">
        <div class="card login-content">
            <div class="card-header">
              <h3 class="fw-bold text-center">ipanel CLI Login</h3>
              <p class="text-center text-muted mb-0">Authenticate to authorize CLI access</p>
            </div>

            <div class="card-body">
              <form action="{{ panel_route('cli_login.store') }}" method="post">
                @csrf

                <div class="form-floating mb-4">
                  <input type="text" name="email" class="form-control" id="email-input" value="{{ old('email', $admin_email ?? '') }}" placeholder="{{ __('panel/login.email') }}">
                  <label for="email-input">{{ __('panel/login.email') }}</label>
                  @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>

                <div class="form-floating mb-5">
                  <input type="password" name="password" class="form-control" id="password-input" value="{{ old('password', $admin_password ?? '') }}" placeholder="{{ __('panel/login.password') }}">
                  <label for="password-input">{{ __('panel/login.password') }}</label>
                  @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>

                @if (session('error'))
                  <div class="alert alert-danger">
                    {{ session('error') }}
                  </div>
                @endif

                <div class="d-grid mb-4"><button type="submit" class="btn btn-lg btn-primary">{{ __('panel/common.btn_submit') }}</button></div>
              </form>
            </div>
        </div>
        <p class="text-center text-secondary mt-5">
          {!! innoshop_brand_link() !!}
          {{ innoshop_version() }} &copy; {{ date('Y') }} All Rights Reserved
        </p>
      </div>

    </div>
  </div>
</body>
</html>
