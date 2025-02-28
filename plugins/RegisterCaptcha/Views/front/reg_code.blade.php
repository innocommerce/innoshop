<div class="form-group mb-4">
    <div style="display: flex">
        <input id="code" type="text" class="form-control" name="code" value="{{ old('code') }}" required
               autocomplete="code" placeholder="{{ __('RegisterCaptcha::login.code') }}" style="width: 70%"/>
        <button type="button" class="btn btn-primary btn-lg"
                style="margin-left: 10px" id="sendRegCode"
                onclick="sendRegCode1()">{{ __('RegisterCaptcha::login.send_btn') }}</button>
    </div>
</div>

@push("footer")
    @if(!empty($js))
        {!! $js !!}
    @endif
    <script>
        @if($captcha_type == 2 && !empty($js))

        function regCallback(res) {
            console.log('callback:', res);
            if (res.ret === 0) {
                //验证是否通过
                postRegSend({ticket: res.ticket, randstr: res.randstr});

            } else {
                layer.msg(res.errorMessage)
            }
        }

        // 定义验证码js加载错误处理函数
        function loadErrorCallback() {
            var appid = '{{$captcha_id}}'
            // 生成容灾票据或自行做其它处理
            var ticket = 'terror_1001_' + appid + Math.floor(new Date().getTime() / 1000);
            regCallback({
                ret: 0,
                randstr: '@' + Math.random().toString(36).substr(2),
                ticket: ticket,
                errorCode: 1001,
                errorMessage: 'jsload_error'
            });
        }


        @endif
        function sendRegCode1() {
            //验证是否填写了邮件
            let email = $("#email").val();
            if (email.trim() == '') {
                layer.msg("{{ __('front/login.email_required') }}", {icon: 2})
                return;
            }
            startGetRegCode();
        }

        function startGetRegCode() {
            @if($captcha_type == 2)

            // 定义验证码触发事件
            var captcha = new TencentCaptcha('{{$captcha_id}}', regCallback, {userLanguage: "{{current_locale()}}"});
            // 调用方法，显示验证码
            captcha.show();
            return;
            @endif
            @if($captcha_type == 0)

            postRegSend({});

            @endif
        }

        let interVal = null;

        function postRegSend(result) {
            layer.load(2, {shade: [0.3, '#fff']})
            result.email = $("#email").val();
            //发送验证码
            axios.post("{{front_route('regCheckCaptcha')}}", result).then((res) => {
                //console.log(res);
                if (res.code == 0) {
                    layer.msg("Success");

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
                            $("#sendRegCode").html("{{ __('RegisterCaptcha::login.send_btn') }}");
                            $("#sendRegCode").attr("disabled", false);
                        }
                    }, 1000);


                } else {
                    if (interVal != null) {
                        clearInterval(interVal);
                    }
                    $("#sendRegCode").html("{{ __('RegisterCaptcha::login.send_btn') }}");
                    $("#sendRegCode").attr("disabled", false);
                    layer.msg(res.msg)
                }
            }).finally(() => {
                layer.closeAll('loading');
            });


        }
    </script>
@endpush



