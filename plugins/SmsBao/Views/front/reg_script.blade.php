
<script>
  //加入手机检测
  //app.$set(app, "registerForm", new Object())


   app.$set(app.registerForm, "getCodeLoadingTime", 60)
   app.$set(app.registerForm, "getCodeLoading", false)
   app.$set(app.registerForm, "telephone_code", "{{$country_codes[0]}}")
   app.$set(app.registerForm, "codes", @json($country_codes))

   app.$set(app.registerForm, "telephone", "")
   app.$set(app.registerForm, "code", "")
   app.$set(app.registerForm, "type", "phone")


    //加入检测
   let rtelephoneRules = [];
   rtelephoneRules.push({required: true, message: '{{ __('SmsBao::login.enter_telephone') }}', trigger: 'change'});
   //telephoneRules.push({type: 'telephone', message: '{{ __('SmsBao::login.telephone_err') }}', trigger: 'change'});
   app.$set(app.registeRules, "telephone", rtelephoneRules)

  //加入检测
  let rtelephoneCodeRules = [];
  rtelephoneCodeRules.push({required: true, message: '{{ __('SmsBao::login.enter_telephone') }}', trigger: 'change'});
  //telephoneRules.push({type: 'telephone', message: '{{ __('SmsBao::login.telephone_err') }}', trigger: 'change'});
  app.$set(app.registeRules, "telephone_code", rtelephoneCodeRules)

   let rcodeRules = [];
   rcodeRules.push({required: true, message: '{{ __('SmsBao::login.enter_code') }}', trigger: 'change'});
   // codeRules.push({type: 'code', message: '{{ __('SmsBao::login.code_err') }}', trigger: 'change'});
   app.$set(app.registeRules, "code", rcodeRules)

  function phoneCheckedBtnLogin(form){
    app.$refs['loginForm'].clearValidate();
    app.$refs['registerForm'].clearValidate();

    //短信走新的
    let _data = app.registerForm, url = "{{shop_route('loginBySms.register')}}"
    app.$refs['registerForm'].validate((valid) => {
      if (!valid) {
        layer.msg('{{ __('shop/login.check_form') }}', () => {})
        return;
      }

      $http.post(url, _data).then((res) => {
        layer.msg(res.message)
        if(res.code == 0) {
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

   //点击获取验证码
   app.rStartGetCode = function () {

    if (app.registerForm.telephone == null || app.registerForm.telephone.trim() == "") {
      return;
    }
    app.registerForm.getCodeLoading = true;
    $("#rsendCode").attr("disabled", true);
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
              that.rPostSend(result);
              //captchaObj.reset()
            }).onError(function () {
              //your code
              $("#rsendCode").attr("disabled", false);
              app.registerForm.getCodeLoading = false;
            })
          });

    @endif

    @if($captcha_type == 2)

       // 定义验证码触发事件
       var captcha = new TencentCaptcha('{{$captcha_id}}', callback, {userLanguage:"{{locale()}}"});
       // 调用方法，显示验证码
       captcha.show();

    @endif

    @if($captcha_type == 0)

      that.rPostSend({});

    @endif
  }


   let rInterVal = null;
   //发送验证码
   app.rPostSend = function (result) {
    result['telephone_code'] = app.registerForm.telephone_code;
    result['telephone'] = app.registerForm.telephone;

    //发送验证码
    $http.post(`/smsbao/sms`, result).then((res) => {
      //console.log(res);
      if (res.code == 0) {
        //window.location.reload();
        //TODO 发送验证码
        layer.msg("success");
        //TODO 改变状态
      } else {
        if (rInterVal != null) {
          clearInterval(rInterVal);
        }
        app.registerForm.getCodeLoading = false;
        $("#rsendCode").html("{{ __('SmsBao::login.code') }}");
        $("#rsendCode").attr("disabled", false);
        layer.msg(res.msg)
      }
    })

    //开始倒计时
    let time = 60;
    $("#rsendCode").html(time + "s");
     rInterVal = setInterval(function () {
      time = time - 1;
      $("#rsendCode").html(time + "s");
      if (time <= 0) {
        clearInterval(rInterVal);
        app.registerForm.getCodeLoading = false;
        $("#rsendCode").html("{{ __('SmsBao::login.code') }}");
        $("#rsendCode").attr("disabled", false);
      }
    }, 1000);
  }


   //登录
   app.rCheckedBtnLogin = function (form) {


     console.log(app.$refs);

     app.$refs['registerForm'].clearValidate();


    //短信走新的
    let _data = this.registerForm, url = '/smsbao/login'
     this.$refs[form].validate((valid) => {
       if (!valid) {
         layer.msg('{{ __('shop/login.check_form') }}', () => {})
         return;
       }

       $http.post(url, _data).then((res) => {
         layer.msg(res.message)
         if(res.code == 0) {
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
