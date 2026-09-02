@extends('layouts.app')
@section('body-class', 'page-login')

@section('content')
  @hookinsert('account.forgotten.top')

  <div class="page-auth">
    <section class="auth-panel">
      <div class="auth-panel-inner">
        <div class="login-register-box">

          {{-- Step 1: Send verification code --}}
          <div id="sendCardContent">
            <h1 class="login-title">{{ __('front/forgotten.title') }}</h1>
            <p class="login-sub-title">{{ __('front/forgotten.subtitle_send') }}</p>

            <form class="form-wrap needs-validation" novalidate>
              <div class="form-group">
                <label for="forgotEmail">{{ __('front/forgotten.email') }}</label>
                <div class="input-wrapper">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16v12H4z"/><path d="M4 6l8 7 8-7"/></svg>
                  <input id="forgotEmail" type="email" class="form-control" name="email" required
                         autocomplete="email" placeholder="you@example.com"/>
                </div>
                <span class="invalid-feedback" role="alert"><strong>{{ __('front/forgotten.email_required') }}</strong></span>
              </div>

              <div class="btn-submit">
                <button type="button" id="btnSend" class="btn btn-primary form-submit">
                  {{ __('front/forgotten.send_code') }}
                </button>
                <span class="switch-prompt">
                  {{ __('front/forgotten.remembered') }}
                  <a href="{{ front_route('login.index') }}">{{ __('front/forgotten.back_to_login') }}</a>
                </span>
              </div>
            </form>
          </div>

          {{-- Step 2: Verify code + set new password --}}
          <div id="verifyCardContent" class="d-none">
            <h1 class="login-title">{{ __('front/forgotten.title_confirm') }}</h1>
            <p class="login-sub-title">{{ __('front/forgotten.subtitle_confirm') }}</p>

            <form action="{{ front_route('forgotten.password') }}" class="form-wrap needs-validation" novalidate method="POST">
              @csrf
              <input type="hidden" name="email" value="" id="inputEmail">

              <div class="form-group">
                <label for="forgotCode">{{ __('front/forgotten.verification_code') }}</label>
                <div class="input-wrapper">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
                  <input id="forgotCode" type="text" class="form-control" name="code" required
                         inputmode="numeric" pattern="[0-9]*" maxlength="6" placeholder="6-digit code"/>
                </div>
                <span class="invalid-feedback" role="alert"><strong>{{ __('front/forgotten.code_required') }}</strong></span>
              </div>

              <div class="form-group">
                <label for="forgotPassword">{{ __('front/forgotten.new_password') }}</label>
                <div class="input-wrapper">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                  <input id="forgotPassword" type="password" class="form-control" name="password" required
                         minlength="8" autocomplete="new-password" placeholder="At least 8 characters"/>
                  <button type="button" class="toggle-password" data-target="forgotPassword" aria-label="Toggle password visibility">
                    <svg class="eye-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="eye-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8a19.93 19.93 0 014.22-5.18M9.9 4.24A10.94 10.94 0 0112 4c7 0 11 8 11 8a19.86 19.86 0 01-3.17 4.19M1 2l22 20"/></svg>
                  </button>
                </div>
                <span class="invalid-feedback" role="alert"><strong>{{ __('front/forgotten.password_required') }}</strong></span>
              </div>

              <div class="form-group">
                <label for="forgotPasswordConfirm">{{ __('front/forgotten.confirm_password') }}</label>
                <div class="input-wrapper">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                  <input id="forgotPasswordConfirm" type="password" class="form-control" name="password_confirmation" required
                         minlength="8" autocomplete="new-password" placeholder="Confirm your new password"/>
                  <button type="button" class="toggle-password" data-target="forgotPasswordConfirm" aria-label="Toggle password visibility">
                    <svg class="eye-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="eye-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8a19.93 19.93 0 014.22-5.18M9.9 4.24A10.94 10.94 0 0112 4c7 0 11 8 11 8a19.86 19.86 0 01-3.17 4.19M1 2l22 20"/></svg>
                  </button>
                </div>
                <span class="invalid-feedback" role="alert"><strong>{{ __('front/forgotten.password_match') }}</strong></span>
              </div>

              <div class="btn-submit">
                <button type="button" id="btnSubmit" class="btn btn-primary form-submit">
                  {{ __('common/base.submit') }}
                </button>
              </div>
            </form>
          </div>

        </div>
      </div>
    </section>
  </div>

  {{-- Hint modal --}}
  <div class="modal fade" data-bs-backdrop="static" tabindex="-1" id="modalHint">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ __('front/forgotten.hint') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>{{ __('front/forgotten.verification_code_sent') }}</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.forgotten.bottom')

@endsection

@push('footer')
  <script>
    // Wait for DOM ready — theme app.js loads with `defer` so window.inno
    // is set after our inline script first runs.
    $(function () {
      // Toggle password visibility (matches login/register behavior).
      $('.toggle-password').on('click', function () {
        const targetId = $(this).data('target');
        const input = document.getElementById(targetId);
        if (!input) return;
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        $(this).find('.eye-show').toggle(!isPassword);
        $(this).find('.eye-hide').toggle(isPassword);
      });

      // URL params: when user follows email link with ?code=&email=, jump to step 2.
      $.getUrlParam = function (name) {
        const reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)');
        const r = window.location.search.substr(1).match(reg);
        if (r != null) return unescape(r[2]);
        return null;
      };

      const url = window.location.href;
      if ($.getUrlParam('code') && $.getUrlParam('email')) {
        $('#sendCardContent').addClass('d-none');
        $('#verifyCardContent').removeClass('d-none');
        $('input[name="code"]').val($.getUrlParam('code'));
        $('#inputEmail').val($.getUrlParam('email'));
      }

      const modalHint = new bootstrap.Modal('#modalHint', { keyboard: false });

      $('#btnSend').on('click', function () {
        const emailInput = $('input[name="email"]')[0];
        if (!emailInput.checkValidity()) {
          emailInput.reportValidity();
          return;
        }

        layer.load(2, { shade: [0.3, '#fff'] });
        axios.post('{{ front_route('forgotten.verify_code') }}', {
          _token: '{{ csrf_token() }}',
          email: $('input[name="email"]').val()
        }).then(function (res) {
          parent.layer.closeAll();
          if (res.success === true) {
            modalHint.show();
            $('#inputEmail').val($('input[name="email"]').val());
            $('#sendCardContent').addClass('d-none');
            $('#verifyCardContent').removeClass('d-none');
          } else {
            layer.msg(res.message || '{{ __('front/forgotten.send_failed') }}', { icon: 2 });
          }
        }).catch(function (err) {
          parent.layer.closeAll();
          layer.msg(err.response?.data?.message || err.message, { icon: 2 });
        });
      });

      $('#btnSubmit').on('click', function () {
        const form = $(this).closest('form')[0];
        if (!form.checkValidity()) {
          form.reportValidity();
          return;
        }

        const password = $('input[name="password"]').val();
        const confirmation = $('input[name="password_confirmation"]').val();
        if (password !== confirmation) {
          layer.msg('{{ __('front/forgotten.password_match') }}', { icon: 2 });
          return;
        }

        layer.load(2, { shade: [0.3, '#fff'] });
        axios.post('{{ front_route('forgotten.password') }}', {
          _token: '{{ csrf_token() }}',
          code: $('input[name="code"]').val(),
          email: $('input[name="email"]').val(),
          password: password,
          password_confirmation: confirmation,
        }).then(function (res) {
          parent.layer.closeAll();
          if (res.success === true) {
            layer.msg(res.message || '{{ __('front/forgotten.password_updated') }}', { icon: 1 });
            window.location.href = '{{ front_route('login.index') }}';
          } else {
            layer.msg(res.message || 'Failed', { icon: 2 });
          }
        }).catch(function (err) {
          parent.layer.closeAll();
          layer.msg(err.response?.data?.message || err.message, { icon: 2 });
        });
      });
    });
  </script>
@endpush