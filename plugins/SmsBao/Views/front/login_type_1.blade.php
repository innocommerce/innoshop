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

            <!-- 导航区 -->

            <ul class="nav nav-tabs mb-2">
                <li class="nav-item">
                <li class="nav-item active" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab"
                            data-bs-target="#email"
                            type="button">{{ __('SmsBao::login.tab_email') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab"
                            data-bs-target="#sms"
                            type="button">{{ __('SmsBao::login.tab_sms') }}
                    </button>
                </li>
            </ul>

            <!-- 面板区 -->
            <div class="tab-content" style="margin-top: 25px">
                <div class="tab-pane fade active show" id="email" role="tabpanel">
                    <form action="{{ front_route('login.store') }}" class="needs-validation form-wrap" novalidate>
                        @csrf
                        <input type="hidden" name="login_type" value="email">
                        <div class="form-group mb-4">
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                   name="email"
                                   value="{{ old('email') }}" required autocomplete="email"
                                   placeholder="{{ __('front/login.email') }}"/>
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

                <div class="tab-pane fade show" id="sms" role="tabpanel">
                    <form action="{{ front_route('loginBySms') }}" class="needs-validation form-wrap2" novalidate>
                        @csrf
                        <input type="hidden" name="login_type" value="sms">
                        <div class="form-group mb-4">
                            <div style="display: flex">
                                <select class="form-select" style="width: 18%" name="telephone_code"
                                        id="telephone_code">
                                    @foreach($country_codes as $key=>$country_code)
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
                            <div style="display: flex">
                                <input id="code" type="text" class="form-control" name="code" value="{{ old('code') }}"
                                       required
                                       autocomplete="code" placeholder="{{ __('SmsBao::login.code') }}"
                                       style="width: 70%"/>
                                <button type="button" class="btn btn-primary btn-lg"
                                        style="margin-left: 10px" id="sendRegCode"
                                        onclick="postLoginSmsCodeSend()">{{ __('SmsBao::login.code') }}</button>
                            </div>
                            <span class="invalid-feedback"
                                  role="alert"><strong>{{ __('front/login.email_required') }}</strong></span>
                        </div>
                        <div class="btn-submit">
                            <button type="button"
                                    class="btn btn-primary form-submit btn-lg">{{ __('front/login.login_submit') }}</button>
                        </div>
                        </span>
                    </form>
                </div>
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
                if (res.success) {
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

        inno.validateAndSubmitForm('.form-wrap2', function (data) {
            layer.load(2, {shade: [0.3, '#fff']})
            axios.post($('.form-wrap2').attr('action'), data).then(function (res) {
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

        function postLoginSmsCodeSend() {
            layer.load(2, {shade: [0.3, '#fff']})
            let telephone = $("#telephone").val();
            let telephone_code = $("#telephone_code").val();
            //发送验证码
            axios.post("{{front_route('postSmsCode')}}", {
                telephone: telephone,
                telephone_code: telephone_code
            }).then((res) => {
                //console.log(res);
                if (res.success) {
                    layer.msg("{{__('common.success')}}");

                    //开始倒计时
                    let time = 60;
                    $("#sendRegCode").attr("disabled", true);
                    $("#sendRegCode").html(time + "s");
                    interVal = setInterval(function () {
                        time = time - 1;
                        $("#sendRegCode").html(time + "s");
                        console.log(time);
                        if (time <= 0) {
                            clearInterval(interVal);
                            $("#sendRegCode").html("{{ __('RegisterCaptcha::login.code') }}");
                            $("#sendRegCode").attr("disabled", false);
                        }
                    }, 1000);


                } else {
                    if (interVal != null) {
                        clearInterval(interVal);
                    }
                    $("#sendRegCode").html("{{ __('RegisterCaptcha::login.code') }}");
                    $("#sendRegCode").attr("disabled", false);
                    layer.msg(res.message)
                }
                layer.closeAll('loading')
            })


        }
    </script>
@endpush
