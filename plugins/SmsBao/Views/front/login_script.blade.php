<script>
  //加入手机检测
  //app.$set(app, "loginForm", new Object())


  app.$set(app.loginForm, "getCodeLoadingTime", 60)
  app.$set(app.loginForm, "getCodeLoading", false)
  app.$set(app.loginForm, "telephone_code", "{{$country_codes[0]}}")
  app.$set(app.loginForm, "codes", @json($country_codes))

  app.$set(app.loginForm, "telephone", "")
  app.$set(app.loginForm, "code", "")
  @if($login_type == 1)
  app.$set(app.loginForm, "type", "email")
  @endif
  @if($login_type == 2)
  app.$set(app.loginForm, "type", "phone")
  @endif


  //加入检测
  let telephoneRules = [];
  telephoneRules.push({required: true, message: '{{ __('SmsBao::login.enter_telephone') }}', trigger: 'change'});
  //telephoneRules.push({type: 'telephone', message: '{{ __('SmsBao::login.telephone_err') }}', trigger: 'change'});
  app.$set(app.loginRules, "telephone", telephoneRules)

  //加入检测
  let telephoneCodeRules = [];
  telephoneCodeRules.push({required: true, message: '{{ __('SmsBao::login.enter_telephone') }}', trigger: 'change'});
  //telephoneRules.push({type: 'telephone', message: '{{ __('SmsBao::login.telephone_err') }}', trigger: 'change'});
  app.$set(app.loginRules, "telephone_code", telephoneCodeRules)

  let codeRules = [];
  codeRules.push({required: true, message: '{{ __('SmsBao::login.enter_code') }}', trigger: 'change'});
  // codeRules.push({type: 'code', message: '{{ __('SmsBao::login.code_err') }}', trigger: 'change'});
  app.$set(app.loginRules, "code", codeRules)


  //点击获取验证码
  app.startGetCode = function () {

    if (app.loginForm.telephone == null || app.loginForm.telephone.trim() == "") {
      return;
    }
    app.loginForm.getCodeLoading = true;
    $("#sendCode").attr("disabled", true);
    let that = this;
    @if($captcha_type == 1)

    initGeetest4({
      captchaId: '{{$captcha_id}}',
      product: 'bind'
    }, function (captchaObj) {
      // captcha为验证码实例
      captchaObj.onReady(function () {
        captchaObj.showCaptcha(); //显示验证码
      }).onSuccess(function () {
        //your code,结合您的业务逻辑重置验证码
        var result = captchaObj.getValidate();
        //console.log(result);
        that.postSend(result);
        //captchaObj.reset()
      }).onError(function () {
        //your code
        $("#sendCode").attr("disabled", false);
        app.loginForm.getCodeLoading = false;
      })
    });

    @endif

    @if($captcha_type == 2)

    // 定义验证码触发事件
    var captcha = new TencentCaptcha('{{$captcha_id}}', callback, {userLanguage: "{{locale()}}"});
    // 调用方法，显示验证码
    captcha.show();

    @endif

    @if($captcha_type == 0)

    that.postSend({});

    @endif
  }


  let interVal = null;
  //发送验证码
  app.postSend = function (result) {
    result['telephone_code'] = app.loginForm.telephone_code;
    result['telephone'] = app.loginForm.telephone;

    //发送验证码
    $http.post(`/smsbao/sms`, result).then((res) => {
      //console.log(res);
      if (res.code == 0) {
        //window.location.reload();
        //TODO 发送验证码
        layer.msg("success");
        //TODO 改变状态
      } else {
        if (interVal != null) {
          clearInterval(interVal);
        }
        app.loginForm.getCodeLoading = false;
        $("#sendCode").html("{{ __('SmsBao::login.code') }}");
        $("#sendCode").attr("disabled", false);
        layer.msg(res.msg)
      }
    })

    //开始倒计时
    let time = 60;
    $("#sendCode").html(time + "s");
    interVal = setInterval(function () {
      time = time - 1;
      $("#sendCode").html(time + "s");
      if (time <= 0) {
        clearInterval(interVal);
        app.loginForm.getCodeLoading = false;
        $("#sendCode").html("{{ __('SmsBao::login.code') }}");
        $("#sendCode").attr("disabled", false);
      }
    }, 1000);
  }
  let emailCheckedBtnLogin = app.checkedBtnLogin;
  //console.log(emailCheckedBtnLogin);

  //登录
  app.checkedBtnLogin = function (form) {


    console.log(app.$refs);

    app.$refs['loginForm'].clearValidate();
    app.$refs['registerForm'].clearValidate();

    if (form != 'loginForm') {//注册走旧的
      @if($login_type == 1)
      emailCheckedBtnLogin(form);
      @endif
      @if($login_type == 2)
      phoneCheckedBtnLogin(form);
      @endif
        return;
    }

    if (app.loginForm.type == 'email') {//邮箱走旧的
      //移除不相关的验证
      emailCheckedBtnLogin(form);
      return;
    }

    let _data = null;
      @if($login_type == 1)
        _data = this.loginForm, url = '/smsbao/login'
      @endif
      @if($login_type == 2)
      _data = this.loginForm, url = '/smsbao/phone/login'
      @endif
      this.$refs[form].validate((valid) => {
      if (!valid) {
        layer.msg('{{ __('shop/login.check_form') }}', () => {
        })
        return;
      }

      $http.post(url, _data).then((res) => {
        layer.msg(res.message)
        if (res.code == 0) {
          if (interVal != null) {
            clearInterval(interVal);
          }
          window.location.reload();
          @if (!request('iframe'))
            window.location.reload()
          @else
            var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
            setTimeout(() => {
              parent.layer.close(index); //再执行关闭
              parent.window.location.reload()
            }, 400);
          @endif
        }
      })

    });

  }

</script>
