@if (!request('iframe'))
  <a class="text-muted forgotten-link" href="{{ shop_route('loginBySms.forgotten') }}"><i class="bi bi-question-circle"></i> {{ __('shop/login.forget_password') }}</a>
@endif
