@extends('layout.master')

@section('body-class', 'page-forgotten')

@push('header')
  <script src="{{ asset('vendor/vue/2.7/vue' . (!config('app.debug') ? '.min' : '') . '.js') }}"></script>
  <script src="{{ asset('vendor/element-ui/index.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('vendor/element-ui/index.css') }}">
@endpush


@section('content')
  <x-shop-breadcrumb type="static" value="forgotten.index"/>

  <div class="container" id="page-forgotten" v-cloak>
    <div class="row my-5 justify-content-md-center">
      <div class="col-lg-5 col-xxl-4">
        <div class="card">
          <el-form ref="forgottenForm" :model="forgottenForm" :rules="rules">
            <div class="card-body p-0">
              <h4 class="fw-bold">{{ __('shop/forgotten.follow_prompt') }}</h4>

              <el-form-item label="{{ __('SmsBao::login.telephone') }}" prop="telephone">
                @if(count($country_codes) > 1)
                  <div class="el-input">
                    <el-row>
                      <el-col :span="8">
                        <el-select v-model="forgottenForm.telephone_code" style="margin-right: 5px" filterable>
                          @foreach($country_codes as $code)
                            <el-option
                              key="{{$code}}"
                              label="{{$code}}"
                              value="{{$code}}">
                            </el-option>
                          @endforeach
                        </el-select>
                      </el-col>
                      <el-col :span="16">
                        <el-input @keyup.enter.native="checkedBtnLogin('forgottenForm')"
                                  v-model="forgottenForm.telephone"
                                  placeholder="{{ __('SmsBao::login.telephone') }}"></el-input>
                      </el-col>

                    </el-row>
                  </div>
                @endif
                @if(count($country_codes) == 1)
                  <el-input @keyup.enter.native="checkedBtnLogin('forgottenForm')" v-model="forgottenForm.telephone"
                            placeholder="{{ __('SmsBao::login.telephone') }}"></el-input>
                @endif
              </el-form-item>


              <el-form-item label="{{ __('SmsBao::login.code') }}" prop="code">
                <div class="el-input">
                  <el-row>
                    <el-col :span="16">
                      <el-input @keyup.enter.native="checkedBtnLogin('forgottenForm')" v-model="forgottenForm.code"
                                placeholder="{{ __('SmsBao::login.code') }}"></el-input>
                    </el-col>
                    <el-col :span="8">
                      <el-button type="primary" style="margin-left: 10px;margin-right: 20px" @click="rSendCode"
                                 :loading="forgottenForm.getCodeLoading" id="rsendCode">{{ __('SmsBao::login.code') }}
                      </el-button>
                    </el-col>
                  </el-row>
                </div>
              </el-form-item>

              <el-form-item label="{{ __('shop/forgotten.password') }}" prop="password" class="mb-3">
                <el-input type="password" v-model="forgottenForm.password"
                          placeholder="{{ __('shop/forgotten.password') }}"></el-input>
              </el-form-item>

              <el-form-item label="{{ __('shop/forgotten.confirm_password') }}" prop="password_confirmation">
                <el-input type="password" v-model="forgottenForm.password_confirmation"
                          placeholder="{{ __('shop/forgotten.confirm_password') }}"></el-input>
              </el-form-item>

              <div class="mt-5 mb-3 d-flex justify-content-between">
                <button type="button" @click="phoneCheckedBtnLogin('checkedBtnLogin')" class="btn w-50 btn-dark">
                  {{ __('common.submit') }}
                </button>
              </div>
              <a href="/login"
                 class="text-muted">{{ __('shop/forgotten.to_back') }}</a>
            </div>
          </el-form>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('add-scripts')
  <script>
    var validatePass = (rule, value, callback) => {
      if (value === '') {
        callback(new Error('{{ __('shop/forgotten.enter_password') }}'));
      } else {
        if (value !== '') {
          app.$refs.forgottenForm.validateField('password_confirmation');
        }
        callback();
      }
    };

    var validatePass2 = (rule, value, callback) => {
      if (value === '') {
        callback(new Error('{{ __('shop/forgotten.please_confirm') }}'));
      } else if (value !== app.forgottenForm.password) {
        callback(new Error('{{ __('shop/forgotten.password_err') }}'));
      } else {
        callback();
      }
    };

    let app = new Vue({
      el: '#page-forgotten',

      data: {
        forgottenForm: {
          getCodeLoadingTime: 60,
          getCodeLoading: false,
          telephone_code: "{{$country_codes[0]}}",
          codes: @json($country_codes),
          telephone: '',
          code: '',
          password: '',
          password_confirmation: '',
          type: 'phone',
        },
        rInterVal: null,
        rules: {
          telephone: [
            {required: true, message: '{{ __('SmsBao::login.enter_telephone') }}', trigger: 'change'}
          ],
          telephone_code: [
            {required: true, message: '{{ __('SmsBao::login.enter_telephone') }}', trigger: 'change'}
          ],
          code: [
            {required: true, message: '{{ __('shop/forgotten.enter_code') }}', trigger: 'blur'}
          ],
          password: [
            {required: true, validator: validatePass, trigger: 'blur'}
          ],
          password_confirmation: [
            {required: true, validator: validatePass2, trigger: 'blur'}
          ]
        }
        ,
      },

      mounted() {
      }
      ,

      methods: {
        phoneCheckedBtnLogin(form) {
          this.$refs['forgottenForm'].clearValidate();
          let that = this;
          //短信走新的
          this.$refs['forgottenForm'].validate((valid) => {
            if (!valid) {
              layer.msg('{{ __('shop/login.check_form') }}', () => {
              })
              return;
            }

            let _data = that.forgottenForm, url = "{{shop_route('loginBySms.forgotten_update')}}"
            $http.post(url, _data).then((res) => {
              layer.msg(res.message)
              if (res.code == 0) {
                if (that.interVal != null) {
                  clearInterval(that.interVal);
                }
                location = "{{ shop_route('login.index') }}"


              }
            })

          });

        },
        rStartGetCode() {

          if (this.forgottenForm.telephone == null || this.forgottenForm.telephone.trim() == "") {
            return;
          }
          this.forgottenForm.getCodeLoading = true;
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
              that.forgottenForm.getCodeLoading = false;
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

          that.rPostSend({});

          @endif
        },
        //发送验证码
        rPostSend(result) {
          result['telephone_code'] = this.forgottenForm.telephone_code;
          result['telephone'] = this.forgottenForm.telephone;
          let that = this;
          //发送验证码
          $http.post(`/smsbao/sms`, result).then((res) => {
            //console.log(res);
            if (res.code == 0) {
              //window.location.reload();
              //TODO 发送验证码
              layer.msg("success");
              //TODO 改变状态
            } else {
              if (that.rInterVal != null) {
                clearInterval(that.rInterVal);
              }
              that.forgottenForm.getCodeLoading = false;
              $("#rsendCode").html("{{ __('SmsBao::login.code') }}");
              $("#rsendCode").attr("disabled", false);
              layer.msg(res.msg)
            }
          })

          //开始倒计时
          let time = 60;
          $("#rsendCode").html(time + "s");
          that.rInterVal = setInterval(function () {
            time = time - 1;
            $("#rsendCode").html(time + "s");
            if (time <= 0) {
              clearInterval(that.rInterVal);
              that.forgottenForm.getCodeLoading = false;
              $("#rsendCode").html("{{ __('SmsBao::login.code') }}");
              $("#rsendCode").attr("disabled", false);
            }
          }, 1000);
        },
        rCheckedBtnLogin(form) {
          this.$refs['forgottenForm'].clearValidate();


          //短信走新的
          let _data = this.forgottenForm, url = '/smsbao/login'
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
        },
        rSendCode() {
          console.log(this.forgottenForm.codes);
          if (this.forgottenForm.codes.length == 1) {
            this.forgottenForm.telephone_code = this.forgottenForm.codes[0];
          }
          if (this.forgottenForm.telephone_code == '') {
            layer.msg('{{ __('shop/login.check_form') }}', () => {
            })
            return;
          }
          this.$refs["forgottenForm"].validateField('telephone', (val) => {
            if (!val) {
              this.rStartGetCode()
              return;
            } else {
              return;
            }
          })


        },

      }
    })
  </script>

@endpush

@push("add-scripts")
  @if($captcha_type == 1)
    <script src="https://static.geetest.com/v4/gt4.js"></script>
  @endif
  @if($captcha_type == 2)
    <script src="https://turing.captcha.qcloud.com/TCaptcha.js"></script>

    <script>
      // 定义回调函数
      function callback(res) {
        // 第一个参数传入回调结果，结果如下：
        // ret         Int       验证结果，0：验证成功。2：用户主动关闭验证码。
        // ticket      String    验证成功的票据，当且仅当 ret = 0 时 ticket 有值。
        // CaptchaAppId       String    验证码应用ID。
        // bizState    Any       自定义透传参数。
        // randstr     String    本次验证的随机串，后续票据校验时需传递该参数。
        console.log('callback:', res);


        // res（用户主动关闭验证码）= {ret: 2, ticket: null}
        // res（验证成功） = {ret: 0, ticket: "String", randstr: "String"}
        // res（请求验证码发生错误，验证码自动返回terror_前缀的容灾票据） = {ret: 0, ticket: "String", randstr: "String",  errorCode: Number, errorMessage: "String"}
        // 此处代码仅为验证结果的展示示例，真实业务接入，建议基于ticket和errorCode情况做不同的业务处理
        if (res.ret === 0) {
          app.rPostSend(res);
        }
      }

      // 定义验证码js加载错误处理函数
      function loadErrorCallback() {
        var appid = '{{$captcha_id}}'
        // 生成容灾票据或自行做其它处理
        var ticket = 'terror_1001_' + appid + Math.floor(new Date().getTime() / 1000);
        callback({
          ret: 0,
          randstr: '@' + Math.random().toString(36).substr(2),
          ticket: ticket,
          errorCode: 1001,
          errorMessage: 'jsload_error'
        });
      }
    </script>

  @endif
  <script type="text/javascript">
    //发送验证码
    $(function () {
      $("#sendCodeBtn").click(function () {
        console.log("sendCode");
        app.rStartGetCode()
      });
    });
  </script>

@endpush
