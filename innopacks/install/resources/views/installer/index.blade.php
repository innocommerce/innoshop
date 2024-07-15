<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="{{ url('/') }}">
  <title>{{ __('install::common.install_wizard') }}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="shortcut icon" href="{{ image_origin(system_setting('favicon', 'images/favicon.png')) }}">
  <link rel="stylesheet" href="{{ asset('build/css/bootstrap.css') }}">
  <link rel="stylesheet" href="{{ asset('build/install/css/app.css') }}">
  <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
  <script src="{{ asset('vendor/layer/3.5.1/layer.js') }}"></script>
  <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  @stack('header')
</head>

<body>
<header>
  <div class="container d-flex justify-content-between">
    <div class="logo"><img src="{{ asset('images/logo.png') }}" class="img-fluid"></div>

    <div class="dropdown">
      <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
              aria-expanded="false">
        {{ __('install::common.'.$locale) }}
      </button>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="{{ route('install.install.index', ['locale' => 'zh_cn']) }}">中文</a></li>
        <li><a class="dropdown-item" href="{{ route('install.install.index', ['locale' => 'en']) }}">English</a></li>
        <li><a class="dropdown-item" href="{{ route('install.install.index', ['locale' => 'es']) }}">Español</a></li>
      </ul>
    </div>
  </div>
</header>
<div class="container">
  <div class="install-box">
    <ul class="progress-wrap">
      <li class="active">
        <div class="icon"><span>1</span></div>
        <div class="text">{{ __('install::common.license') }}</div>
      </li>
      <li>
        <div class="icon"><span>2</span></div>
        <div class="text">{{ __('install::common.environment') }}</div>
      </li>
      <li>
        <div class="icon"><span>3</span></div>
        <div class="text">{{ __('install::common.configuration') }}</div>
      </li>
      <li>
        <div class="icon"><span>4</span></div>
        <div class="text">{{ __('install::common.completed') }}</div>
      </li>
    </ul>
    <div class="install-wrap">
      <div class="install-1 install-item active">
        <div class="head-title">{{ __('install::common.open_source') }}</div>
        <div class="install-content" id="content">
          @include("install::license.".$locale)
        </div>

        <div class="d-flex justify-content-center mt-4">
          <button type="button" class="btn btn-primary btn-lg next-btn">{{ __('install::common.i_agree') }}</button>
        </div>
      </div>

      <div class="install-2 install-item d-none">
        <div class="head-title">{{ __('install::common.env_detection') }}</div>
        <div class="install-content">
          <table class="table">
            <thead>
            <tr>
              <th colspan="3" class="bg-light">{{ __('install::common.env_detection') }}</th>
            </tr>
            </thead>
            <tbody>
            <tr>
              <td>{{ __('install::common.environment') }}</td>
              <td>{{ __('install::common.current') }}</td>
              <td>{{ __('install::common.status') }}</td>
            </tr>
            <tr>
              <td>{{ __('install::common.php_version') }}(8.2+)</td>
              <td>{{ $php_version }}</td>
              <td><i
                    class="bi {{ $php_env ? 'text-success bi-check-circle-fill' : 'bi-x-circle-fill text-danger' }}"></i>
              </td>
            </tr>
            @foreach($extensions as $key => $value)
              <tr>
                <td>{{ $key }}</td>
                <td></td>
                <td><i
                      class="bi {{ $value ? 'text-success bi-check-circle-fill' : 'bi-x-circle-fill text-danger' }}"></i>
                </td>
              </tr>
            @endforeach

            </tbody>

            <thead>
            <tr>
              <th colspan="3" class="bg-light">{{ __('install::common.perm_detection') }}</th>
            </tr>
            </thead>
            <tbody>
            <tr>
              <td>{{ __('install::common.dir_file') }}</td>
              <td>{{ __('install::common.config') }}</td>
              <td>{{ __('install::common.status') }}</td>
            </tr>
            @foreach($permissions as $key => $value)
              <tr>
                <td>{{ $key }}</td>
                <td>755</td>
                <td><i
                      class="bi {{ $value ? 'text-success bi-check-circle-fill' : 'bi-x-circle-fill text-danger' }}"></i>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-center mt-4">
          <button type="button" class="btn btn-outline-secondary prev-btn me-3">{{ __('install::common.previous_step') }}</button>
          <button type="button" disabled class="btn btn-primary next-btn">{{ __('install::common.next_step') }}</button>
        </div>
      </div>

      <div class="install-3 install-item d-none">
        <div class="head-title">{{ __('install::common.param_config') }}</div>
        <div class="install-content">
          <form class="needs-validation" novalidate>
            <div class="bg-light py-2 mb-2 text-center fw-bold">{{ __('install::common.db_config') }}</div>
            <div class="row gx-2">
              <div class="col-6">
                <div class="mb-3">
                  <label for="type" class="form-label">{{ __('install::common.db_type') }}</label>
                  <select class="form-select sql-type" id="type" name="type" required>
                    <option value="sqlite">SQLite</option>
                    <option value="mysql">MySQL</option>
                  </select>
                  <div class="invalid-feedback">{{ __('install::common.select_db_type') }}</div>
                </div>
              </div>
              <div class="col-6 mysql-item">
                <div class="mb-3">
                  <label for="host" class="form-label">{{ __('install::common.host_address') }}</label>
                  <input type="text" class="form-control" id="host" name="db_hostname" required placeholder="{{ __('install::common.host') }}"
                         value="127.0.0.1">
                  <div class="invalid-feedback">{{ __('install::common.host') }}</div>
                </div>
              </div>
              <div class="col-6 mysql-item">
                <div class="mb-3">
                  <label for="port" class="form-label">{{ __('install::common.port_number') }}</label>
                  <input type="text" class="form-control" id="port" name="db_port" required placeholder="{{ __('install::common.port') }}"
                         value="3306">
                  <div class="invalid-feedback">{{ __('install::common.port') }}</div>
                </div>
              </div>
              <div class="col-6 mysql-item">
                <div class="mb-3">
                  <label for="database" class="form-label">{{ __('install::common.db_name') }}</label>
                  <input type="text" class="form-control" id="database" name="db_name" required value="innoshop"
                         placeholder="{{ __('install::common.db_name') }}">
                  <div class="invalid-feedback">{{ __('install::common.db_name') }}</div>
                </div>
              </div>
              <div class="col-6 mysql-item">
                <div class="mb-3">
                  <label for="database" class="form-label">{{ __('install::common.table_prefix') }}</label>
                  <input type="text" class="form-control" id="db_prefix" name="db_prefix" value="inno_" required
                         placeholder="{{ __('install::common.table_prefix') }}">
                  <div class="invalid-feedback">{{ __('install::common.table_prefix') }}</div>
                </div>
              </div>
              <div class="col-6 mysql-item">
                <div class="mb-3">
                  <label for="username" class="form-label">{{ __('install::common.db_account') }}</label>
                  <input type="text" class="form-control" id="username" name="db_username" required
                         placeholder="{{ __('install::common.db_account') }}">
                  <div class="invalid-feedback">{{ __('install::common.db_account') }}</div>
                </div>
              </div>
              <div class="col-6 mysql-item">
                <div class="mb-3">
                  <label for="password" class="form-label">{{ __('install::common.db_password') }}</label>
                  <input type="password" class="form-control" id="password" name="db_password"
                         placeholder="{{ __('install::common.db_password') }}">
                  <div class="invalid-feedback">{{ __('install::common.db_password') }}</div>
                </div>
              </div>
            </div>

            <div class="admin-setting d-none">
              <div class="bg-light py-2 mb-2 text-center fw-bold">{{ __('install::common.admin_config') }}</div>
              <div class="row">
                <div class="col-6">
                  <div class="mb-3">
                    <label for="admin_email" class="form-label">{{ __('install::common.admin_account') }}</label>
                    <input type="text" class="form-control" id="admin_email" name="admin_email" required
                           placeholder="{{ __('install::common.admin_account') }}" value="root@innoshop.com">
                    <div class="invalid-feedback">{{ __('install::common.admin_account') }}</div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="mb-3">
                    <label for="admin_password" class="form-label">{{ __('install::common.admin_password') }}</label>
                    <input type="password" class="form-control" id="admin_password" name="admin_password" required
                           placeholder="{{ __('install::common.admin_password') }}">
                    <div class="invalid-feedback">{{ __('install::common.admin_password') }}</div>
                  </div>
                </div>
              </div>
            </div>
            <button type="submit" class="d-none">{{ __('install::common.next_step') }}</button>
          </form>
        </div>

        <div class="d-flex justify-content-center mt-4">
          <button type="button" class="btn btn-outline-secondary prev-btn me-3">{{ __('install::common.previous_step') }}</button>
          <button type="button" disabled class="btn btn-primary next-btn">{{ __('install::common.next_step') }}</button>
        </div>
      </div>

      <div class="install-4 install-item install-success d-none">
        <div class="head-title">{{ __('install::common.install_complete') }}</div>
        <div class="install-content">
          <div class="icon"><img src="{{ asset('icon/install-icon-4.svg') }}" class="img-fluid"></div>
          <div class="success-text">
            {{ __('install::common.congratulations') }}
          </div>
        </div>
        <div class="d-flex justify-content-center mt-4">
          <a href="{{ url('/') }}" class="btn btn-primary me-3">{{ __('install::common.visit_frontend') }}</a>
          <a href="{{ url('/panel') }}" class="btn btn-primary">{{ __('install::common.visit_backend') }}</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  $('.next-btn').click(function () {
    var current = $('.install-item').filter('.active');
    var next = current.next('.install-item');
    if (next.length === 0) {
      return;
    }

    if (next.hasClass('install-2')) {
      checkStatus();
    }

    if (next.hasClass('install-3')) {
      $('.sql-type').trigger('change');
    }

    if (current.hasClass('install-3')) {
      var form = current.find('form');
      form.removeClass('was-validated');
      if (form[0].checkValidity() === false) {
        form.addClass('was-validated');
        return;
      }

      var data = form.serialize();
      checkComplete(data, function (res) {
        activeStep(current, next);
      })
      return
    }

    activeStep(current, next);
  });

  // 上一步
  $('.prev-btn').click(function () {
    var current = $('.install-item').filter('.active');
    var prev = current.prev('.install-item');
    $('.next-btn').prop('disabled', false);
    if (prev.length === 0) {
      return;
    }

    activeStep(current, prev);
  });

  $('.sql-type').change(function () {
    var type = $(this).val();
    if (type === 'sqlite') {
      $('.mysql-item').find('input').prop('required', false).prop('disabled', true);
      $('.admin-setting').removeClass('d-none');
      $('.next-btn').prop('disabled', false);
    } else {
      $('.mysql-item').find('input').prop('required', true).prop('disabled', false);
      $('.admin-setting').addClass('d-none');
      $('.next-btn').prop('disabled', true);
    }
    if (type === 'sqlite') {
      checkConnect();
    }
  });

  var timer = null;
  $('.mysql-item input').on('input', function () {
    var flag = true;
    $('.mysql-item input').each(function () {
      if ($(this).val() === '' && $(this).attr('id') !== 'password') {
        flag = false;
      }
    });

    if (flag) {
      clearTimeout(timer);
      timer = setTimeout(() => {
        checkConnect();
      }, 500);
    }
  });

  function checkConnect() {
    $.ajax({
      url: '/install/connected',
      type: 'POST',
      data: {
        _token: '{{ csrf_token() }}',
        type: $('#type').val(),
        db_hostname: $('#host').val(),
        db_port: $('#port').val(),
        db_name: $('#database').val(),
        db_prefix: $('#db_prefix').val(),
        db_username: $('#username').val(),
        db_password: $('#password').val(),
      },
      success: function (res) {
        if (res.db_success) {
          $('.is-invalid').removeClass('is-invalid').next().text('');

          $('.admin-setting').removeClass('d-none');
          $('.next-btn').prop('disabled', false);
          setTimeout(() => {
            $('.install-3 .install-content').animate({scrollTop: $('.install-3 .install-content')[0].scrollHeight}, 400);
          }, 200);
        } else {
          for (var key in res) {
            $('input[name="' + key + '"]').addClass('is-invalid').next().text(res[key]);
          }
        }
      }
    });
  }

  function checkComplete(data, callback) {
    layer.load(2, {shade: [0.3,'#fff'] })
    $('.is-invalid').removeClass('is-invalid').next('.invalid-feedback').text('');
    $.ajax({
      url: '/install/complete',
      type: 'POST',
      data: data,
      success: function(res) {
        if (res.success) {
          callback(res);
        }
      },
      error: function(res) {
        const errors = res.responseJSON.errors;
        Object.keys(errors).forEach(function(key) {
          $('input[name="' + key + '"]').addClass('is-invalid').next('.invalid-feedback').text(errors[key][0]);
        });
        layer.msg(res.responseJSON.message);
      },
      complete: function() {
        layer.closeAll('loading');
      }
    });
  }

  function checkStatus() {
    var flag = true;
    $('.install-2 table .bi').each(function() {
      if ($(this).hasClass('text-danger')) {
        flag = false;
      }
    });

    if (flag) {
      $('.install-2 .next-btn').prop('disabled', false);
    } else {
      layer.msg('{{ __('install::common.check_system') }}');
    }
  }

  // 激活状态
  function activeStep(current, next) {
    var index = next.index();
    // 删除所有步骤的 active 状态
    $('.progress-wrap li').removeClass('complete active');
    $('.install-wrap .install-item').removeClass('active').addClass('d-none');

    // index 步骤之前的步骤添加 complete 状态
    $('.progress-wrap li').each(function (i) {
      if (i < index) {
        $(this).addClass('complete active');
      }
    });

    $('.progress-wrap li').eq(next.index()).addClass('active');

    $('.install-wrap .install-' + (index + 1)).removeClass('d-none').addClass('active');
  }

  $('form').on('keypress', function (e) {
    if (e.keyCode === 13) {
      e.preventDefault();
    }
  });
</script>
</body>
</html>