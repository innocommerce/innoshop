@hookwrapper('account.login.sms_plugin.telephone')
<el-form-item label="{{ __('SmsBao::login.telephone') }}" prop="telephone">
  @if(count($country_codes) > 1)
    <div class="el-input">
      <el-row>
        <el-col :span="8">
          <el-select v-model="registerForm.telephone_code" style="margin-right: 5px" filterable>
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
          <el-input @keyup.enter.native="checkedBtnLogin('registerForm')" v-model="registerForm.telephone"
                    placeholder="{{ __('SmsBao::login.telephone') }}"></el-input>
        </el-col>

      </el-row>
    </div>
  @endif
  @if(count($country_codes) == 1)
    <el-input @keyup.enter.native="checkedBtnLogin('registerForm')" v-model="registerForm.telephone"
              placeholder="{{ __('SmsBao::login.telephone') }}"></el-input>
  @endif
</el-form-item>
<el-form-item label="{{ __('SmsBao::login.code') }}" prop="code">
  <div class="el-input">
    <el-row>
      <el-col :span="16">
        <el-input @keyup.enter.native="checkedBtnLogin('registerForm')" v-model="registerForm.code"
                  placeholder="{{ __('SmsBao::login.code') }}"></el-input>
      </el-col>
      <el-col :span="8">
        <el-button type="primary" style="margin-left: 10px;margin-right: 20px" @click="rSendCode"
                   :loading="registerForm.getCodeLoading" id="rsendCode">{{ __('SmsBao::login.code') }}
        </el-button>
      </el-col>
    </el-row>
  </div>
</el-form-item>
@endhookwrapper
@push('login.vue.data')

@endpush
@push('login.vue.method')
  rSendCode(){
    console.log(this.registerForm.codes);
    if(this.registerForm.codes.length == 1){
        this.registerForm.telephone_code = this.registerForm.codes[0];
    }
    if(this.registerForm.telephone_code == ''){
          layer.msg('{{ __('shop/login.check_form') }}', () => {})
          return;
    }
    this.$refs["registerForm"].validateField('telephone', (val) => {
      if (!val) {
        this.rStartGetCode()
        return;
      } else {
        return;
      }
    })



  },
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

