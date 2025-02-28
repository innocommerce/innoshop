@extends('layouts.app')
@section('body-class', 'page-login')
@section('content')
    @if (!request('iframe'))
        <x-front-breadcrumb type="route" value="login.index" title="{{ __('front/account.login') }}"/>
    @endif

    @hookinsert('account.login.top')

    <div class="container">
        <div class="login-register-box {{ request('iframe') ? 'iframe' : '' }}">
            <div class="login-title">{{ __('front/login.login') }}</div>
            <div class="login-sub-title">{{ __('front/login.login_text') }}</div>

            <form action="{{ front_route('loginBySms.phone_login') }}" class="needs-validation form-wrap" novalidate>
                @csrf
                <div class="form-group mb-4">
                    <div style="display: flex">
                        <select class="form-select" style="width: 18%" name="telephone_code"
                                id="telephone_code">
                            @foreach($country_codes as $key => $country_code)
                                <option
                                    @if($key == 0)
                                    selected
                                    @endif
                                >
                                    {{ $country_code }}
                                </option>
                            @endforeach
                        </select>
                        <input id="telephone" type="telephone"
                               style="width: 80%;margin-left: 10px"
                               class="form-control @error('telephone') is-invalid @enderror"
                               name="telephone"
                               value="{{ old('telephone') }}" required autocomplete="telephone"
                               placeholder="{{ __('SmsBao::login.telephone') }}"/>
                    </div>
                    <span class="invalid-feedback"
                          role="alert"><strong>{{ __('front/login.email_required') }}</strong></span>
                </div>

                <div class="form-group mb-4">
                    <input id="password" type="password"
                           class="form-control @error('password') is-invalid @enderror"
                           name="password" required autocomplete="new-password"
                           placeholder="{{ __('front/login.password') }}"/>
                    <span class="invalid-feedback"
                          role="alert"><strong>{{ __('front/login.password_required') }}</strong></span>
                </div>
                @if (!request('iframe'))
                    <a href="{{ front_route('forgotten.index') }}"
                       class="text-secondary mt-n2 d-block">{{__('front/login.forget_password') }} <i
                            class="bi bi-question-circle"></i></a>
                @endif

                <div class="btn-submit">
                    <button type="button"
                            class="btn btn-primary form-submit btn-lg">{{ __('front/login.login_submit') }}</button>
                    <a href="{{ front_route('register.index') }}{{ request('iframe') ? '?iframe=true' : '' }}">{{__('front/login.no_account') }}
                        <i class="bi bi-arrow-up-right-square"></i></a>
                </div>
            </form>

        </div>


        @include('account/_social')

    </div>
    </div>

    @hookinsert('account.login.bottom')

@endsection

@push('footer')
    <script>
        const iframe = @json(request('iframe', false));

        inno.validateAndSubmitForm('.form-wrap', function (data) {
            layer.load(2, {shade: [0.3, '#fff']})
            axios.post($('.form-wrap').attr('action'), data).then(function (res) {
                if (res.code == 0) {
                    if (iframe) {
                        setTimeout(() => {
                            parent.layer.closeAll()
                            parent.window.location.reload()
                        }, 400);
                    } else {
                        layer.msg(res.message, {icon: 1})
                        if (res.data.redirect_uri) {
                            location.href = res.data.redirect_uri;
                        } else {
                            location.href = '{{ front_route('account.index') }}';
                        }
                    }
                } else {
                    layer.msg(res.message, {icon: 2});
                }
            }).finally(function () {
                layer.closeAll('loading')
            });
        });

        let interVal = null;


    </script>
@endpush
