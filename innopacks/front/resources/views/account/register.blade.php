@extends('layouts.app')
@section('body-class', 'page-login')

@section('content')
@if (!request('iframe'))
  <x-front-breadcrumb type="route" value="register.index" title="{{ __('front/account.register') }}" />
@endif

@hookinsert('account.register.top')

<div class="container">
  <div class="login-register-box {{ request('iframe') ? 'iframe' : '' }}">
    <div class="login-title">{{ __('front/register.register') }}</div>
    <div class="login-sub-title">{{ __('front/register.register_text') }}</div>
    <form action="{{ front_route('register.store') }}" class="needs-validation form-wrap" novalidate>
      @csrf
      <div class="form-group mb-4">
        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="{{ __('front/login.email') }}" />
        <span class="invalid-feedback" role="alert"><strong>{{ __('front/login.email_required') }}</strong></span>
      </div>

      <div class="form-group mb-4">
        <input id="password" type="password" class="form-control" name="password" required autocomplete="new-password" placeholder="{{ __('front/login.password') }}" />
        <input class="d-none" name="password_confirmation" />
        <span class="invalid-feedback" role="alert"><strong>{{ __('front/login.password_required') }}</strong></span>
      </div>

      <div class="btn-submit">
        <button type="button" class="btn btn-primary form-submit btn-lg">{{ __('front/register.register_submit') }}</button>
        <a href="{{ front_route('login.index') }}{{ request('iframe') ? '?iframe=true' : '' }}">{{ __('front/register.have_account') }} <i class="bi bi-arrow-up-right-square"></i></a>
      </div>
    </form>
  </div>
</div>

@hookinsert('account.register.bottom')

@endsection

@push('footer')
<script>
  const iframe = @json(request('iframe', false));

  inno.validateAndSubmitForm('.form-wrap', function(data) {
    layer.load(2, {shade: [0.3,'#fff'] })
    const params = new URLSearchParams(data);
    params.set('password_confirmation', $('input[name="password"]').val());

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
</script>
@endpush