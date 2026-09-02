@extends('layouts.app')
@section('body-class', 'page-login')

@section('content')
@hookinsert('account.register.top')

<div class="page-auth {{ request('iframe') ? 'iframe' : '' }}">
  <section class="auth-panel">
    <div class="auth-panel-inner">
      <div class="login-register-box {{ request('iframe') ? 'iframe' : '' }}">
        <h1 class="login-title">{{ __('front/register.register') }}</h1>
        <p class="login-sub-title">{{ __('front/register.register_text') }}</p>

        @if($authMethod === 'both')
          <div class="auth-method-switch" role="tablist">
            <button type="button" class="active" data-method="email" role="tab">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16v12H4z"/><path d="M4 6l8 7 8-7"/></svg>
              {{ __('front/register.register_by_email') }}
            </button>
            <button type="button" data-method="phone" role="tab">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.13.96.36 1.9.7 2.8a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.9.34 1.84.57 2.8.7A2 2 0 0122 16.92z"/></svg>
              {{ __('front/register.register_by_phone') }}
            </button>
          </div>
        @endif

        <form action="{{ front_route('register.store') }}" class="needs-validation form-wrap" novalidate>
          @csrf

          @if($authMethod === 'email_only' || $authMethod === 'both')
            <div class="auth-form auth-form-email" @if($authMethod === 'both') style="display: none;" @endif>
              @hookupdate('account.register.email')
              <div class="form-group">
                <label for="email">{{ __('front/login.email') }}</label>
                <div class="input-wrapper">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16v12H4z"/><path d="M4 6l8 7 8-7"/></svg>
                  <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}"
                         @if($authMethod === 'email_only') required @elseif($authMethod === 'both') data-required-with="email" @endif
                         autocomplete="email" placeholder="you@example.com"/>
                </div>
                <span class="invalid-feedback" role="alert"><strong>{{ __('front/login.email_required') }}</strong></span>
              </div>
              @endhookupdate
              @hookinsert('account.register.email.after')
              <div class="form-group">
                <label for="password">{{ __('front/login.password') }}</label>
                <div class="input-wrapper">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                  <input id="password" type="password" class="form-control" name="password"
                         @if($authMethod === 'email_only') required @elseif($authMethod === 'both') data-required-with="email" @endif
                         autocomplete="new-password" placeholder="At least 8 characters"/>
                  <input id="password_confirmation" type="password" class="d-none" name="password_confirmation"
                         autocomplete="new-password"/>
                  <button type="button" class="toggle-password" data-target="password" aria-label="Toggle password visibility">
                    <svg class="eye-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="eye-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8a19.93 19.93 0 014.22-5.18M9.9 4.24A10.94 10.94 0 0112 4c7 0 11 8 11 8a19.86 19.86 0 01-3.17 4.19M1 2l22 20"/></svg>
                  </button>
                </div>
                <span class="invalid-feedback" role="alert"><strong>{{ __('front/login.password_required') }}</strong></span>
              </div>
            </div>
          @endif

          @if($authMethod === 'phone_only' || $authMethod === 'both')
            <div class="auth-form auth-form-phone" @if($authMethod === 'both') style="display: none;" @endif>
              <div class="form-group">
                <label>{{ __('front/register.telephone') }}</label>
                <div class="phone-row">
                  <div class="input-wrapper">
                    <input type="text" class="form-control" name="calling_code"
                           @if($authMethod === 'phone_only') required @elseif($authMethod === 'both') data-required-with="phone" @endif
                           placeholder="+86" value="{{ old('calling_code', '+86') }}"/>
                  </div>
                  <div class="input-wrapper">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.13.96.36 1.9.7 2.8a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.9.34 1.84.57 2.8.7A2 2 0 0122 16.92z"/></svg>
                    <input type="tel" class="form-control" name="telephone"
                           @if($authMethod === 'phone_only') required @elseif($authMethod === 'both') data-required-with="phone" @endif
                           placeholder="138 0000 0000" value="{{ old('telephone') }}"/>
                  </div>
                </div>
              </div>
              <div class="form-group sms-row">
                <label for="code">{{ __('front/register.sms_code') }}</label>
                <div class="input-wrapper">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
                  <input id="code" type="text" class="form-control" name="code"
                         @if($authMethod === 'phone_only') required @elseif($authMethod === 'both') data-required-with="phone" @endif
                         placeholder="6-digit code" maxlength="6"/>
                  <div class="input-trailing">
                    <button type="button" class="sms-send-btn" id="send-sms-code"
                            @if($authMethod === 'both') data-required-with="phone" @endif>
                      {{ __('front/register.send_code') }}
                    </button>
                  </div>
                </div>
                <span class="invalid-feedback" role="alert"><strong>{{ __('front/register.code_required') }}</strong></span>
              </div>
            </div>
          @endif

          <div class="btn-submit">
            <button type="button" class="btn btn-primary form-submit">
              {{ __('front/register.register_submit') }}
            </button>
            <span class="switch-prompt">
              {{ __('front/register.have_account') }}
              <a href="{{ front_route('login.index') }}{{ request('iframe') ? '?iframe=true' : '' }}">{{ theme_trans('front.auth_sign_in_link') }}</a>
            </span>
          </div>

          <p class="auth-terms">
            {{ theme_trans('front.auth_terms_prefix') }}
            <a href="{{ front_route('pages.slug_show', ['slug' => 'terms']) }}">{{ theme_trans('front.auth_terms_of_service') }}</a>
            {{ theme_trans('front.auth_terms_and') }}
            <a href="{{ front_route('pages.slug_show', ['slug' => 'privacy']) }}">{{ theme_trans('front.auth_privacy_policy') }}</a>.
          </p>

          @include('account/_social')

        </form>
      </div>
    </div>
  </section>
</div>

@hookinsert('account.register.bottom')

@endsection

@push('footer')
<script>
  // Wrap in DOM ready: theme app.js loads with `defer` in <head>,
  // so window.inno is not yet defined when this inline script first runs.
  $(function () {
  const iframe = @json(request('iframe', false));
  const authMethod = @json($authMethod);

  @if($authMethod === 'both')
    // Switch between email and phone registration
    $('.auth-method-switch button').on('click', function() {
      const method = $(this).data('method');
      $('.auth-method-switch button').removeClass('active');
      $(this).addClass('active');

      $('.auth-form').hide();
      $('.auth-form-' + method).show();

      // Update required attributes
      $('.auth-form-' + method + ' [data-required-with]').attr('required', true);
      $('.auth-form').not('.auth-form-' + method + ' [data-required-with]').removeAttr('required');
    });

    // Set default to email
    $('.auth-form-email').show();
    $('.auth-form-email [data-required-with="email"]').attr('required', true);
  @endif

  // Toggle password visibility
  $('.toggle-password').on('click', function() {
    const targetId = $(this).data('target');
    const input = document.getElementById(targetId);
    if (!input) return;
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    $(this).find('.eye-show').toggle(!isPassword);
    $(this).find('.eye-hide').toggle(isPassword);
  });

  // Send SMS code
  $('#send-sms-code').on('click', function() {
    const callingCode = $('input[name="calling_code"]').val();
    const telephone = $('input[name="telephone"]').val();

    if (!callingCode || !telephone) {
      layer.msg('{{ __('front/register.please_enter_phone') }}', {icon: 2});
      return;
    }

    const btn = $(this);
    btn.prop('disabled', true);
    btn.text('{{ __('front/register.sending') }}...');

    axios.post('{{ front_route('register.sms-code') }}', {
      calling_code: callingCode,
      telephone: telephone
    }).then(function(res) {
      if (res.success) {
        layer.msg(res.message, {icon: 1});
        // Start countdown
        let countdown = 60;
        const timer = setInterval(function() {
          btn.text(countdown + 's');
          countdown--;
          if (countdown < 0) {
            clearInterval(timer);
            btn.prop('disabled', false);
            btn.text('{{ __('front/register.send_code') }}');
          }
        }, 1000);
      } else {
        layer.msg(res.message, {icon: 2});
        btn.prop('disabled', false);
        btn.text('{{ __('front/register.send_code') }}');
      }
    }).catch(function() {
      btn.prop('disabled', false);
      btn.text('{{ __('front/register.send_code') }}');
    });
  });

  inno.validateAndSubmitForm('.form-wrap', function(serializedData) {
    layer.load(2, {shade: [0.3,'#fff'] })

    // Parse serialized data to object
    const data = {};
    serializedData.split('&').forEach(function(item) {
      const parts = item.split('=');
      if (parts.length === 2) {
        data[decodeURIComponent(parts[0])] = decodeURIComponent(parts[1]);
      }
    });

    // Ensure password_confirmation matches password
    if (data.password) {
      data.password_confirmation = data.password;
    }

    // Remove hidden fields based on auth method
    if (authMethod === 'both') {
      const activeMethod = $('.auth-method-switch button.active').data('method');
      if (activeMethod === 'email') {
        delete data.calling_code;
        delete data.telephone;
        delete data.code;
      } else {
        delete data.email;
        delete data.password;
        delete data.password_confirmation;
      }
    }

    const params = new URLSearchParams(data);

    axios.post($('.form-wrap').attr('action'), params.toString()).then(function(res) {
      if (res.success) {
        if (iframe) {
          setTimeout(() => {
            parent.layer.closeAll()
            parent.window.location.reload()
          }, 400);
        } else {
          layer.msg(res.message, {icon: 1})
          location.href = '{{ front_route('account.index') }}';
        }
      } else {
        layer.msg(res.message, { icon: 2 });
      }
    }).finally(function() {layer.closeAll('loading')});
  });
  });
</script>
@endpush