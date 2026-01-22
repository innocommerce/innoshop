<!-- SMS Settings -->
<div class="tab-pane fade" id="tab-setting-sms">
  <!-- Authentication Method Settings -->
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('panel/setting.authentication_settings') }}</h5>
      <p class="text-muted small mb-0">{{ __('panel/setting.authentication_settings_desc') }}</p>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-12">
          <x-common-form-select title="{{ __('panel/setting.auth_method') }}" name="auth_method" required
                              :options="[
                                ['key' => 'email_only', 'value' => __('panel/setting.auth_method_email_only')],
                                ['key' => 'phone_only', 'value' => __('panel/setting.auth_method_phone_only')],
                                ['key' => 'both', 'value' => __('panel/setting.auth_method_both')]
                              ]" key="key" label="value" :empty-option="false"
                              value="{{ old('auth_method', auth_method()) }}"/>
          <div class="text-secondary"><small>{{ __('panel/setting.auth_method_desc') }}</small></div>
        </div>
      </div>
    </div>
  </div>

  <!-- SMS Gateway Settings -->
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('panel/setting.sms_settings') }}</h5>
      <p class="text-muted small mb-0">{{ __('panel/setting.sms_settings_desc') }}</p>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-12">
          <x-common-form-select title="{{ __('panel/setting.sms_gateway') }}" name="sms_gateway"
                              :options="$sms_gateways" key="code" label="name" :empty-option="true"
                              value="{{ old('sms_gateway', system_setting('sms_gateway', '')) }}"
                              placeholder="{{ __('panel/setting.sms_gateway') }}"/>
          <div class="text-secondary"><small>{{ __('panel/setting.sms_gateway_desc') }}</small></div>
        </div>
      </div>

      <!-- Yunpian (云片) -->
      <div class="gateway yunpian d-none">
        <div class="row mt-3">
          <div class="col-md-12">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i> {{ __('panel/setting.sms_register_tip') }}: 
              <a href="{{ $sms_repo->getGatewayRegisterUrl('yunpian') }}" target="_blank" rel="noopener noreferrer">{{ $sms_repo->getGatewayRegisterUrl('yunpian') }}</a>
            </div>
            <x-common-form-input title="{{ __('panel/setting.sms_yunpian_api_key') }}" name="sms_yunpian_api_key"
                               value="{{ old('sms_yunpian_api_key', system_setting('sms_yunpian_api_key', '')) }}"
                               placeholder="{{ __('panel/setting.sms_yunpian_api_key') }}"/>
            <x-common-form-input title="{{ __('panel/setting.sms_yunpian_sign') }}" name="sms_yunpian_sign"
                               value="{{ old('sms_yunpian_sign', system_setting('sms_yunpian_sign', '')) }}"
                               placeholder="{{ __('panel/setting.sms_yunpian_sign_placeholder') }}"/>
            <div class="text-secondary"><small>{{ __('panel/setting.sms_yunpian_sign_desc') }}</small></div>
          </div>
        </div>
      </div>

      <!-- Aliyun (阿里云) -->
      <div class="gateway aliyun d-none">
        <div class="row mt-3">
          <div class="col-md-12">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i> {{ __('panel/setting.sms_register_tip') }}: 
              <a href="{{ $sms_repo->getGatewayRegisterUrl('aliyun') }}" target="_blank" rel="noopener noreferrer">{{ $sms_repo->getGatewayRegisterUrl('aliyun') }}</a>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <x-common-form-input title="{{ __('panel/setting.sms_aliyun_access_key_id') }}" name="sms_aliyun_access_key_id"
                               value="{{ old('sms_aliyun_access_key_id', system_setting('sms_aliyun_access_key_id', '')) }}"
                               placeholder="{{ __('panel/setting.sms_aliyun_access_key_id') }}"/>
          </div>
          <div class="col-md-6">
            <x-common-form-input type="password" title="{{ __('panel/setting.sms_aliyun_access_key_secret') }}" name="sms_aliyun_access_key_secret"
                               value="{{ old('sms_aliyun_access_key_secret', system_setting('sms_aliyun_access_key_secret', '')) }}"
                               placeholder="{{ __('panel/setting.sms_aliyun_access_key_secret') }}"/>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <x-common-form-input title="{{ __('panel/setting.sms_aliyun_sign_name') }}" name="sms_aliyun_sign_name"
                               value="{{ old('sms_aliyun_sign_name', system_setting('sms_aliyun_sign_name', '')) }}"
                               placeholder="{{ __('panel/setting.sms_aliyun_sign_name') }}"/>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <h6 class="mt-3 mb-2">{{ __('panel/setting.sms_template_settings') }}</h6>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_template_register') }}" name="sms_aliyun_template_register"
                               value="{{ old('sms_aliyun_template_register', system_setting('sms_aliyun_template_register', '')) }}"
                               placeholder="{{ __('panel/setting.sms_template_register_placeholder') }}"/>
          </div>
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_template_login') }}" name="sms_aliyun_template_login"
                               value="{{ old('sms_aliyun_template_login', system_setting('sms_aliyun_template_login', '')) }}"
                               placeholder="{{ __('panel/setting.sms_template_login_placeholder') }}"/>
          </div>
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_template_reset') }}" name="sms_aliyun_template_reset"
                               value="{{ old('sms_aliyun_template_reset', system_setting('sms_aliyun_template_reset', '')) }}"
                               placeholder="{{ __('panel/setting.sms_template_reset_placeholder') }}"/>
          </div>
        </div>
      </div>

      <!-- Tencent (腾讯云) -->
      <div class="gateway tencent d-none">
        <div class="row mt-3">
          <div class="col-md-12">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i> {{ __('panel/setting.sms_register_tip') }}: 
              <a href="{{ $sms_repo->getGatewayRegisterUrl('tencent') }}" target="_blank" rel="noopener noreferrer">{{ $sms_repo->getGatewayRegisterUrl('tencent') }}</a>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <x-common-form-input title="{{ __('panel/setting.sms_tencent_sdk_app_id') }}" name="sms_tencent_sdk_app_id"
                               value="{{ old('sms_tencent_sdk_app_id', system_setting('sms_tencent_sdk_app_id', '')) }}"
                               placeholder="{{ __('panel/setting.sms_tencent_sdk_app_id') }}"/>
          </div>
          <div class="col-md-6">
            <x-common-form-input title="{{ __('panel/setting.sms_tencent_secret_id') }}" name="sms_tencent_secret_id"
                               value="{{ old('sms_tencent_secret_id', system_setting('sms_tencent_secret_id', '')) }}"
                               placeholder="{{ __('panel/setting.sms_tencent_secret_id') }}"/>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <x-common-form-input type="password" title="{{ __('panel/setting.sms_tencent_secret_key') }}" name="sms_tencent_secret_key"
                               value="{{ old('sms_tencent_secret_key', system_setting('sms_tencent_secret_key', '')) }}"
                               placeholder="{{ __('panel/setting.sms_tencent_secret_key') }}"/>
          </div>
          <div class="col-md-6">
            <x-common-form-input title="{{ __('panel/setting.sms_tencent_sign_name') }}" name="sms_tencent_sign_name"
                               value="{{ old('sms_tencent_sign_name', system_setting('sms_tencent_sign_name', '')) }}"
                               placeholder="{{ __('panel/setting.sms_tencent_sign_name') }}"/>
          </div>
        </div>
      </div>

      <!-- Huawei (华为云) -->
      <div class="gateway huawei d-none">
        <div class="row mt-3">
          <div class="col-md-12">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i> {{ __('panel/setting.sms_register_tip') }}: 
              <a href="{{ $sms_repo->getGatewayRegisterUrl('huawei') }}" target="_blank" rel="noopener noreferrer">{{ $sms_repo->getGatewayRegisterUrl('huawei') }}</a>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <x-common-form-input title="{{ __('panel/setting.sms_huawei_endpoint') }}" name="sms_huawei_endpoint"
                               value="{{ old('sms_huawei_endpoint', system_setting('sms_huawei_endpoint', '')) }}"
                               placeholder="{{ __('panel/setting.sms_huawei_endpoint') }}"/>
          </div>
          <div class="col-md-6">
            <x-common-form-input title="{{ __('panel/setting.sms_huawei_app_key') }}" name="sms_huawei_app_key"
                               value="{{ old('sms_huawei_app_key', system_setting('sms_huawei_app_key', '')) }}"
                               placeholder="{{ __('panel/setting.sms_huawei_app_key') }}"/>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <x-common-form-input type="password" title="{{ __('panel/setting.sms_huawei_app_secret') }}" name="sms_huawei_app_secret"
                               value="{{ old('sms_huawei_app_secret', system_setting('sms_huawei_app_secret', '')) }}"
                               placeholder="{{ __('panel/setting.sms_huawei_app_secret') }}"/>
          </div>
          <div class="col-md-6">
            <x-common-form-input title="{{ __('panel/setting.sms_huawei_from') }}" name="sms_huawei_from"
                               value="{{ old('sms_huawei_from', system_setting('sms_huawei_from', '')) }}"
                               placeholder="{{ __('panel/setting.sms_huawei_from') }}"/>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <h6 class="mt-3 mb-2">{{ __('panel/setting.sms_template_settings') }}</h6>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_template_register') }}" name="sms_huawei_template_register"
                               value="{{ old('sms_huawei_template_register', system_setting('sms_huawei_template_register', '')) }}"
                               placeholder="{{ __('panel/setting.sms_template_register_placeholder') }}"/>
          </div>
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_template_login') }}" name="sms_huawei_template_login"
                               value="{{ old('sms_huawei_template_login', system_setting('sms_huawei_template_login', '')) }}"
                               placeholder="{{ __('panel/setting.sms_template_login_placeholder') }}"/>
          </div>
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_template_reset') }}" name="sms_huawei_template_reset"
                               value="{{ old('sms_huawei_template_reset', system_setting('sms_huawei_template_reset', '')) }}"
                               placeholder="{{ __('panel/setting.sms_template_reset_placeholder') }}"/>
          </div>
        </div>
      </div>

      <!-- Qiniu (七牛云) -->
      <div class="gateway qiniu d-none">
        <div class="row mt-3">
          <div class="col-md-12">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i> {{ __('panel/setting.sms_register_tip') }}: 
              <a href="{{ $sms_repo->getGatewayRegisterUrl('qiniu') }}" target="_blank" rel="noopener noreferrer">{{ $sms_repo->getGatewayRegisterUrl('qiniu') }}</a>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <x-common-form-input title="{{ __('panel/setting.sms_qiniu_access_key') }}" name="sms_qiniu_access_key"
                               value="{{ old('sms_qiniu_access_key', system_setting('sms_qiniu_access_key', '')) }}"
                               placeholder="{{ __('panel/setting.sms_qiniu_access_key') }}"/>
          </div>
          <div class="col-md-6">
            <x-common-form-input type="password" title="{{ __('panel/setting.sms_qiniu_secret_key') }}" name="sms_qiniu_secret_key"
                               value="{{ old('sms_qiniu_secret_key', system_setting('sms_qiniu_secret_key', '')) }}"
                               placeholder="{{ __('panel/setting.sms_qiniu_secret_key') }}"/>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <h6 class="mt-3 mb-2">{{ __('panel/setting.sms_template_settings') }}</h6>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_template_register') }}" name="sms_qiniu_template_register"
                               value="{{ old('sms_qiniu_template_register', system_setting('sms_qiniu_template_register', '')) }}"
                               placeholder="{{ __('panel/setting.sms_template_register_placeholder') }}"/>
          </div>
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_template_login') }}" name="sms_qiniu_template_login"
                               value="{{ old('sms_qiniu_template_login', system_setting('sms_qiniu_template_login', '')) }}"
                               placeholder="{{ __('panel/setting.sms_template_login_placeholder') }}"/>
          </div>
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_template_reset') }}" name="sms_qiniu_template_reset"
                               value="{{ old('sms_qiniu_template_reset', system_setting('sms_qiniu_template_reset', '')) }}"
                               placeholder="{{ __('panel/setting.sms_template_reset_placeholder') }}"/>
          </div>
        </div>
      </div>

      <!-- Juhe (聚合数据) -->
      <div class="gateway juhe d-none">
        <div class="row mt-3">
          <div class="col-md-12">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i> {{ __('panel/setting.sms_register_tip') }}: 
              <a href="{{ $sms_repo->getGatewayRegisterUrl('juhe') }}" target="_blank" rel="noopener noreferrer">{{ $sms_repo->getGatewayRegisterUrl('juhe') }}</a>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <x-common-form-input title="{{ __('panel/setting.sms_juhe_key') }}" name="sms_juhe_key"
                               value="{{ old('sms_juhe_key', system_setting('sms_juhe_key', '')) }}"
                               placeholder="{{ __('panel/setting.sms_juhe_key') }}"/>
          </div>
        </div>
      </div>

      <!-- Yunzhixun (云之讯) -->
      <div class="gateway yunzhixun d-none">
        <div class="row mt-3">
          <div class="col-md-12">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i> {{ __('panel/setting.sms_register_tip') }}: 
              <a href="{{ $sms_repo->getGatewayRegisterUrl('yunzhixun') }}" target="_blank" rel="noopener noreferrer">{{ $sms_repo->getGatewayRegisterUrl('yunzhixun') }}</a>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_yunzhixun_sid') }}" name="sms_yunzhixun_sid"
                               value="{{ old('sms_yunzhixun_sid', system_setting('sms_yunzhixun_sid', '')) }}"
                               placeholder="{{ __('panel/setting.sms_yunzhixun_sid') }}"/>
          </div>
          <div class="col-md-4">
            <x-common-form-input type="password" title="{{ __('panel/setting.sms_yunzhixun_token') }}" name="sms_yunzhixun_token"
                               value="{{ old('sms_yunzhixun_token', system_setting('sms_yunzhixun_token', '')) }}"
                               placeholder="{{ __('panel/setting.sms_yunzhixun_token') }}"/>
          </div>
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_yunzhixun_app_id') }}" name="sms_yunzhixun_app_id"
                               value="{{ old('sms_yunzhixun_app_id', system_setting('sms_yunzhixun_app_id', '')) }}"
                               placeholder="{{ __('panel/setting.sms_yunzhixun_app_id') }}"/>
          </div>
        </div>
      </div>

      <!-- Huyi (互亿无线) -->
      <div class="gateway huyi d-none">
        <div class="row mt-3">
          <div class="col-md-12">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i> {{ __('panel/setting.sms_register_tip') }}: 
              <a href="{{ $sms_repo->getGatewayRegisterUrl('huyi') }}" target="_blank" rel="noopener noreferrer">{{ $sms_repo->getGatewayRegisterUrl('huyi') }}</a>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <x-common-form-input title="{{ __('panel/setting.sms_huyi_api_id') }}" name="sms_huyi_api_id"
                               value="{{ old('sms_huyi_api_id', system_setting('sms_huyi_api_id', '')) }}"
                               placeholder="{{ __('panel/setting.sms_huyi_api_id') }}"/>
          </div>
          <div class="col-md-6">
            <x-common-form-input type="password" title="{{ __('panel/setting.sms_huyi_api_key') }}" name="sms_huyi_api_key"
                               value="{{ old('sms_huyi_api_key', system_setting('sms_huyi_api_key', '')) }}"
                               placeholder="{{ __('panel/setting.sms_huyi_api_key') }}"/>
          </div>
        </div>
      </div>

      <!-- Luosimao (螺丝帽) -->
      <div class="gateway luosimao d-none">
        <div class="row mt-3">
          <div class="col-md-12">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i> {{ __('panel/setting.sms_register_tip') }}: 
              <a href="{{ $sms_repo->getGatewayRegisterUrl('luosimao') }}" target="_blank" rel="noopener noreferrer">{{ $sms_repo->getGatewayRegisterUrl('luosimao') }}</a>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <x-common-form-input type="password" title="{{ __('panel/setting.sms_luosimao_api_key') }}" name="sms_luosimao_api_key"
                               value="{{ old('sms_luosimao_api_key', system_setting('sms_luosimao_api_key', '')) }}"
                               placeholder="{{ __('panel/setting.sms_luosimao_api_key') }}"/>
          </div>
        </div>
      </div>

      <!-- Yuntongxun (容联云通讯) -->
      <div class="gateway yuntongxun d-none">
        <div class="row mt-3">
          <div class="col-md-12">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i> {{ __('panel/setting.sms_register_tip') }}: 
              <a href="{{ $sms_repo->getGatewayRegisterUrl('yuntongxun') }}" target="_blank" rel="noopener noreferrer">{{ $sms_repo->getGatewayRegisterUrl('yuntongxun') }}</a>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_yuntongxun_app_id') }}" name="sms_yuntongxun_app_id"
                               value="{{ old('sms_yuntongxun_app_id', system_setting('sms_yuntongxun_app_id', '')) }}"
                               placeholder="{{ __('panel/setting.sms_yuntongxun_app_id') }}"/>
          </div>
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_yuntongxun_account_sid') }}" name="sms_yuntongxun_account_sid"
                               value="{{ old('sms_yuntongxun_account_sid', system_setting('sms_yuntongxun_account_sid', '')) }}"
                               placeholder="{{ __('panel/setting.sms_yuntongxun_account_sid') }}"/>
          </div>
          <div class="col-md-4">
            <x-common-form-input type="password" title="{{ __('panel/setting.sms_yuntongxun_account_token') }}" name="sms_yuntongxun_account_token"
                               value="{{ old('sms_yuntongxun_account_token', system_setting('sms_yuntongxun_account_token', '')) }}"
                               placeholder="{{ __('panel/setting.sms_yuntongxun_account_token') }}"/>
          </div>
        </div>
      </div>

      <!-- Rongcloud (融云) -->
      <div class="gateway rongcloud d-none">
        <div class="row mt-3">
          <div class="col-md-12">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i> {{ __('panel/setting.sms_register_tip') }}: 
              <a href="{{ $sms_repo->getGatewayRegisterUrl('rongcloud') }}" target="_blank" rel="noopener noreferrer">{{ $sms_repo->getGatewayRegisterUrl('rongcloud') }}</a>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <x-common-form-input title="{{ __('panel/setting.sms_rongcloud_app_key') }}" name="sms_rongcloud_app_key"
                               value="{{ old('sms_rongcloud_app_key', system_setting('sms_rongcloud_app_key', '')) }}"
                               placeholder="{{ __('panel/setting.sms_rongcloud_app_key') }}"/>
          </div>
          <div class="col-md-6">
            <x-common-form-input type="password" title="{{ __('panel/setting.sms_rongcloud_app_secret') }}" name="sms_rongcloud_app_secret"
                               value="{{ old('sms_rongcloud_app_secret', system_setting('sms_rongcloud_app_secret', '')) }}"
                               placeholder="{{ __('panel/setting.sms_rongcloud_app_secret') }}"/>
          </div>
        </div>
      </div>

      <!-- Avatardata (阿凡达数据) -->
      <div class="gateway avatardata d-none">
        <div class="row mt-3">
          <div class="col-md-12">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i> {{ __('panel/setting.sms_register_tip') }}: 
              <a href="{{ $sms_repo->getGatewayRegisterUrl('avatardata') }}" target="_blank" rel="noopener noreferrer">{{ $sms_repo->getGatewayRegisterUrl('avatardata') }}</a>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <x-common-form-input title="{{ __('panel/setting.sms_avatardata_app_key') }}" name="sms_avatardata_app_key"
                               value="{{ old('sms_avatardata_app_key', system_setting('sms_avatardata_app_key', '')) }}"
                               placeholder="{{ __('panel/setting.sms_avatardata_app_key') }}"/>
          </div>
        </div>
      </div>

      <!-- Baiwu (百悟科技) -->
      <div class="gateway baiwu d-none">
        <div class="row mt-3">
          <div class="col-md-12">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i> {{ __('panel/setting.sms_register_tip') }}: 
              <a href="{{ $sms_repo->getGatewayRegisterUrl('baiwu') }}" target="_blank" rel="noopener noreferrer">{{ $sms_repo->getGatewayRegisterUrl('baiwu') }}</a>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <x-common-form-input title="{{ __('panel/setting.sms_baiwu_username') }}" name="sms_baiwu_username"
                               value="{{ old('sms_baiwu_username', system_setting('sms_baiwu_username', '')) }}"
                               placeholder="{{ __('panel/setting.sms_baiwu_username') }}"/>
          </div>
          <div class="col-md-6">
            <x-common-form-input type="password" title="{{ __('panel/setting.sms_baiwu_password') }}" name="sms_baiwu_password"
                               value="{{ old('sms_baiwu_password', system_setting('sms_baiwu_password', '')) }}"
                               placeholder="{{ __('panel/setting.sms_baiwu_password') }}"/>
          </div>
        </div>
      </div>

      <!-- Huaxin (华信) -->
      <div class="gateway huaxin d-none">
        <div class="row mt-3">
          <div class="col-md-12">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i> {{ __('panel/setting.sms_register_tip') }}: 
              <a href="{{ $sms_repo->getGatewayRegisterUrl('huaxin') }}" target="_blank" rel="noopener noreferrer">{{ $sms_repo->getGatewayRegisterUrl('huaxin') }}</a>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_huaxin_user_id') }}" name="sms_huaxin_user_id"
                               value="{{ old('sms_huaxin_user_id', system_setting('sms_huaxin_user_id', '')) }}"
                               placeholder="{{ __('panel/setting.sms_huaxin_user_id') }}"/>
          </div>
          <div class="col-md-4">
            <x-common-form-input type="password" title="{{ __('panel/setting.sms_huaxin_password') }}" name="sms_huaxin_password"
                               value="{{ old('sms_huaxin_password', system_setting('sms_huaxin_password', '')) }}"
                               placeholder="{{ __('panel/setting.sms_huaxin_password') }}"/>
          </div>
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_huaxin_account') }}" name="sms_huaxin_account"
                               value="{{ old('sms_huaxin_account', system_setting('sms_huaxin_account', '')) }}"
                               placeholder="{{ __('panel/setting.sms_huaxin_account') }}"/>
          </div>
        </div>
      </div>

      <!-- Chuanglan (创蓝) -->
      <div class="gateway chuanglan d-none">
        <div class="row mt-3">
          <div class="col-md-12">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i> {{ __('panel/setting.sms_register_tip') }}: 
              <a href="{{ $sms_repo->getGatewayRegisterUrl('chuanglan') }}" target="_blank" rel="noopener noreferrer">{{ $sms_repo->getGatewayRegisterUrl('chuanglan') }}</a>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <x-common-form-input title="{{ __('panel/setting.sms_chuanglan_username') }}" name="sms_chuanglan_username"
                               value="{{ old('sms_chuanglan_username', system_setting('sms_chuanglan_username', '')) }}"
                               placeholder="{{ __('panel/setting.sms_chuanglan_username') }}"/>
          </div>
          <div class="col-md-6">
            <x-common-form-input type="password" title="{{ __('panel/setting.sms_chuanglan_password') }}" name="sms_chuanglan_password"
                               value="{{ old('sms_chuanglan_password', system_setting('sms_chuanglan_password', '')) }}"
                               placeholder="{{ __('panel/setting.sms_chuanglan_password') }}"/>
          </div>
        </div>
      </div>

      <!-- Sendcloud (SendCloud) -->
      <div class="gateway sendcloud d-none">
        <div class="row mt-3">
          <div class="col-md-12">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i> {{ __('panel/setting.sms_register_tip') }}: 
              <a href="{{ $sms_repo->getGatewayRegisterUrl('sendcloud') }}" target="_blank" rel="noopener noreferrer">{{ $sms_repo->getGatewayRegisterUrl('sendcloud') }}</a>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <x-common-form-input title="{{ __('panel/setting.sms_sendcloud_sms_user') }}" name="sms_sendcloud_sms_user"
                               value="{{ old('sms_sendcloud_sms_user', system_setting('sms_sendcloud_sms_user', '')) }}"
                               placeholder="{{ __('panel/setting.sms_sendcloud_sms_user') }}"/>
          </div>
          <div class="col-md-6">
            <x-common-form-input type="password" title="{{ __('panel/setting.sms_sendcloud_sms_key') }}" name="sms_sendcloud_sms_key"
                               value="{{ old('sms_sendcloud_sms_key', system_setting('sms_sendcloud_sms_key', '')) }}"
                               placeholder="{{ __('panel/setting.sms_sendcloud_sms_key') }}"/>
          </div>
        </div>
      </div>

      <!-- Baidu (百度云) -->
      <div class="gateway baidu d-none">
        <div class="row mt-3">
          <div class="col-md-12">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i> {{ __('panel/setting.sms_register_tip') }}: 
              <a href="{{ $sms_repo->getGatewayRegisterUrl('baidu') }}" target="_blank" rel="noopener noreferrer">{{ $sms_repo->getGatewayRegisterUrl('baidu') }}</a>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_baidu_ak') }}" name="sms_baidu_ak"
                               value="{{ old('sms_baidu_ak', system_setting('sms_baidu_ak', '')) }}"
                               placeholder="{{ __('panel/setting.sms_baidu_ak') }}"/>
          </div>
          <div class="col-md-4">
            <x-common-form-input type="password" title="{{ __('panel/setting.sms_baidu_sk') }}" name="sms_baidu_sk"
                               value="{{ old('sms_baidu_sk', system_setting('sms_baidu_sk', '')) }}"
                               placeholder="{{ __('panel/setting.sms_baidu_sk') }}"/>
          </div>
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_baidu_invoke_id') }}" name="sms_baidu_invoke_id"
                               value="{{ old('sms_baidu_invoke_id', system_setting('sms_baidu_invoke_id', '')) }}"
                               placeholder="{{ __('panel/setting.sms_baidu_invoke_id') }}"/>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <h6 class="mt-3 mb-2">{{ __('panel/setting.sms_template_settings') }}</h6>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_template_register') }}" name="sms_baidu_template_register"
                               value="{{ old('sms_baidu_template_register', system_setting('sms_baidu_template_register', '')) }}"
                               placeholder="{{ __('panel/setting.sms_template_register_placeholder') }}"/>
          </div>
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_template_login') }}" name="sms_baidu_template_login"
                               value="{{ old('sms_baidu_template_login', system_setting('sms_baidu_template_login', '')) }}"
                               placeholder="{{ __('panel/setting.sms_template_login_placeholder') }}"/>
          </div>
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_template_reset') }}" name="sms_baidu_template_reset"
                               value="{{ old('sms_baidu_template_reset', system_setting('sms_baidu_template_reset', '')) }}"
                               placeholder="{{ __('panel/setting.sms_template_reset_placeholder') }}"/>
          </div>
        </div>
      </div>

      <!-- Ucloud (UCloud) -->
      <div class="gateway ucloud d-none">
        <div class="row mt-3">
          <div class="col-md-12">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i> {{ __('panel/setting.sms_register_tip') }}: 
              <a href="{{ $sms_repo->getGatewayRegisterUrl('ucloud') }}" target="_blank" rel="noopener noreferrer">{{ $sms_repo->getGatewayRegisterUrl('ucloud') }}</a>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <x-common-form-input type="password" title="{{ __('panel/setting.sms_ucloud_private_key') }}" name="sms_ucloud_private_key"
                               value="{{ old('sms_ucloud_private_key', system_setting('sms_ucloud_private_key', '')) }}"
                               placeholder="{{ __('panel/setting.sms_ucloud_private_key') }}"/>
          </div>
          <div class="col-md-6">
            <x-common-form-input title="{{ __('panel/setting.sms_ucloud_public_key') }}" name="sms_ucloud_public_key"
                               value="{{ old('sms_ucloud_public_key', system_setting('sms_ucloud_public_key', '')) }}"
                               placeholder="{{ __('panel/setting.sms_ucloud_public_key') }}"/>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <x-common-form-input title="{{ __('panel/setting.sms_ucloud_sig_content') }}" name="sms_ucloud_sig_content"
                               value="{{ old('sms_ucloud_sig_content', system_setting('sms_ucloud_sig_content', '')) }}"
                               placeholder="{{ __('panel/setting.sms_ucloud_sig_content') }}"/>
          </div>
          <div class="col-md-6">
            <x-common-form-input title="{{ __('panel/setting.sms_ucloud_project_id') }}" name="sms_ucloud_project_id"
                               value="{{ old('sms_ucloud_project_id', system_setting('sms_ucloud_project_id', '')) }}"
                               placeholder="{{ __('panel/setting.sms_ucloud_project_id') }}"/>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <h6 class="mt-3 mb-2">{{ __('panel/setting.sms_template_settings') }}</h6>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_template_register') }}" name="sms_ucloud_template_register"
                               value="{{ old('sms_ucloud_template_register', system_setting('sms_ucloud_template_register', '')) }}"
                               placeholder="{{ __('panel/setting.sms_template_register_placeholder') }}"/>
          </div>
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_template_login') }}" name="sms_ucloud_template_login"
                               value="{{ old('sms_ucloud_template_login', system_setting('sms_ucloud_template_login', '')) }}"
                               placeholder="{{ __('panel/setting.sms_template_login_placeholder') }}"/>
          </div>
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_template_reset') }}" name="sms_ucloud_template_reset"
                               value="{{ old('sms_ucloud_template_reset', system_setting('sms_ucloud_template_reset', '')) }}"
                               placeholder="{{ __('panel/setting.sms_template_reset_placeholder') }}"/>
          </div>
        </div>
      </div>

      <!-- Smsbao (短信宝) -->
      <div class="gateway smsbao d-none">
        <div class="row mt-3">
          <div class="col-md-12">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i> {{ __('panel/setting.sms_register_tip') }}: 
              <a href="{{ $sms_repo->getGatewayRegisterUrl('smsbao') }}" target="_blank" rel="noopener noreferrer">{{ $sms_repo->getGatewayRegisterUrl('smsbao') }}</a>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <x-common-form-input title="{{ __('panel/setting.sms_smsbao_user') }}" name="sms_smsbao_user"
                               value="{{ old('sms_smsbao_user', system_setting('sms_smsbao_user', '')) }}"
                               placeholder="{{ __('panel/setting.sms_smsbao_user') }}"/>
          </div>
          <div class="col-md-6">
            <x-common-form-input type="password" title="{{ __('panel/setting.sms_smsbao_password') }}" name="sms_smsbao_password"
                               value="{{ old('sms_smsbao_password', system_setting('sms_smsbao_password', '')) }}"
                               placeholder="{{ __('panel/setting.sms_smsbao_password') }}"/>
          </div>
        </div>
      </div>

      <!-- Moduyun (摩杜云) -->
      <div class="gateway moduyun d-none">
        <div class="row mt-3">
          <div class="col-md-12">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i> {{ __('panel/setting.sms_register_tip') }}: 
              <a href="{{ $sms_repo->getGatewayRegisterUrl('moduyun') }}" target="_blank" rel="noopener noreferrer">{{ $sms_repo->getGatewayRegisterUrl('moduyun') }}</a>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_moduyun_accesskey') }}" name="sms_moduyun_accesskey"
                               value="{{ old('sms_moduyun_accesskey', system_setting('sms_moduyun_accesskey', '')) }}"
                               placeholder="{{ __('panel/setting.sms_moduyun_accesskey') }}"/>
          </div>
          <div class="col-md-4">
            <x-common-form-input type="password" title="{{ __('panel/setting.sms_moduyun_secretkey') }}" name="sms_moduyun_secretkey"
                               value="{{ old('sms_moduyun_secretkey', system_setting('sms_moduyun_secretkey', '')) }}"
                               placeholder="{{ __('panel/setting.sms_moduyun_secretkey') }}"/>
          </div>
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_moduyun_sign_id') }}" name="sms_moduyun_sign_id"
                               value="{{ old('sms_moduyun_sign_id', system_setting('sms_moduyun_sign_id', '')) }}"
                               placeholder="{{ __('panel/setting.sms_moduyun_sign_id') }}"/>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            @php($smsModuyunType=[["code"=>"0","name"=>"普通短信","value"=>"0"],["code"=>"1","name"=>"营销短信","value"=>"1"],])
            <x-common-form-select title="{{ __('panel/setting.sms_moduyun_type') }}"
                                name="sms_moduyun_type" :options="$smsModuyunType" key="code"
                                label="name"
                                value="{{ old('sms_moduyun_type', system_setting('sms_moduyun_type', 0)) }}"
                                placeholder="{{ __('panel/setting.sms_moduyun_type') }}"/>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <h6 class="mt-3 mb-2">{{ __('panel/setting.sms_template_settings') }}</h6>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_template_register') }}" name="sms_moduyun_template_register"
                               value="{{ old('sms_moduyun_template_register', system_setting('sms_moduyun_template_register', '')) }}"
                               placeholder="{{ __('panel/setting.sms_template_register_placeholder') }}"/>
          </div>
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_template_login') }}" name="sms_moduyun_template_login"
                               value="{{ old('sms_moduyun_template_login', system_setting('sms_moduyun_template_login', '')) }}"
                               placeholder="{{ __('panel/setting.sms_template_login_placeholder') }}"/>
          </div>
          <div class="col-md-4">
            <x-common-form-input title="{{ __('panel/setting.sms_template_reset') }}" name="sms_moduyun_template_reset"
                               value="{{ old('sms_moduyun_template_reset', system_setting('sms_moduyun_template_reset', '')) }}"
                               placeholder="{{ __('panel/setting.sms_template_reset_placeholder') }}"/>
          </div>
        </div>
      </div>

      @hookinsert('panel.settings.sms_gateways')
    </div>
  </div>

  <!-- SMS Template Content Settings -->
  <div class="card">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('panel/setting.sms_template_content_settings') }}</h5>
      <p class="text-muted small mb-0">{{ __('panel/setting.sms_template_content_settings_desc') }}</p>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-12 mb-3">
          <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> {{ __('panel/setting.sms_template_content_tip') }}
          </div>
        </div>
      </div>
      
      <!-- SMS Test Section -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">
            <i class="bi bi-send"></i> {{ __('panel/setting.sms_test') }}
          </h5>
          <button type="button" class="btn btn-primary btn-sm" id="sms-test-btn" onclick="testSms()">
            <i class="bi bi-send"></i> {{ __('panel/setting.sms_test_send') }}
          </button>
        </div>
        <div class="card-body">
          <div class="row align-items-end">
            <div class="col-md-6">
              <label class="form-label">{{ __('panel/setting.sms_test_phone_label') }}</label>
              <div class="input-group">
                <span class="input-group-text" style="width: 60px; font-size: 0.875rem;">{{ __('panel/setting.sms_test_calling_code') }}</span>
                <input type="text" class="form-control" id="sms-test-calling-code" placeholder="+86" maxlength="10" style="max-width: 90px;">
                <input type="text" class="form-control" id="sms-test-telephone" placeholder="{{ __('panel/setting.sms_test_phone_placeholder') }}" maxlength="20">
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">{{ __('panel/setting.sms_test_type_label') }}</label>
              <select class="form-select" id="sms-test-type">
                <option value="register">{{ __('panel/setting.sms_test_type_verification_code') }}</option>
              </select>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-md-12">
              <div id="sms-test-result"></div>
            </div>
          </div>
        </div>
      </div>
      <!-- Common Template (Shared by all types) -->
      <div class="row mb-4">
        <div class="col-md-12">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label mb-0">{{ __('panel/setting.sms_template_common_content') }}</label>
            <div id="common-template-actions">
              <button type="button" class="btn btn-sm btn-outline-primary" onclick="importTemplate('common')">
                <i class="bi bi-download"></i> {{ __('panel/setting.sms_template_import') }}
              </button>
            </div>
          </div>
          <ul class="nav nav-tabs mb-2" role="tablist">
            @foreach($locales as $locale)
              <li class="nav-item" role="presentation">
                <button class="nav-link d-flex {{ $loop->first ? 'active' : '' }}" id="common-{{ $locale['code'] }}-tab" data-bs-toggle="tab" data-bs-target="#common-{{ $locale['code'] }}-pane" type="button">
                  <div class="wh-20 me-2"><img src="{{ asset('images/flag/'. $locale['code'].'.png') }}" class="img-fluid"></div>
                  {{ $locale['name'] }}
                </button>
              </li>
            @endforeach
          </ul>
          <div class="tab-content">
            @foreach($locales as $locale)
              <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="common-{{ $locale['code'] }}-pane" role="tabpanel">
                <textarea name="sms_template[{{ $locale['code'] }}]" rows="3" class="form-control template-textarea" data-type="common" data-locale="{{ $locale['code'] }}" placeholder="{{ __('panel/setting.sms_template_common_content_placeholder') }}">{{ old('sms_template.'.$locale['code'], system_setting('sms_template.'.$locale['code'], '')) }}</textarea>
              </div>
            @endforeach
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
  $(document).ready(function () {
    let elm = $('select[name="sms_gateway"]');
    elm.change(function () {
      let selectedValue = $(this).val();
      $('.gateway').addClass('d-none');
      if (selectedValue) {
        $('.' + selectedValue).removeClass('d-none');
      }
    });
    elm.trigger('change');
  });

  // Default templates by locale
  const defaultTemplates = {
    'common': {
      'zh-cn': '您的验证码是：:code，10分钟内有效。',
      'en': 'Your verification code is: :code, valid for 10 minutes.',
    },
  };

  // Import template from default
  function importTemplate(type) {
    const activeTab = $('#' + type + '-template-actions').closest('.row').find('.nav-link.active');
    const locale = activeTab.attr('id').replace(type + '-', '').replace('-tab', '');
    const template = defaultTemplates[type] && defaultTemplates[type][locale] 
      ? defaultTemplates[type][locale] 
      : defaultTemplates[type]['zh-cn'];
    
    if (template) {
      $('textarea[data-type="' + type + '"][data-locale="' + locale + '"]').val(template);
    }
  }

  // SMS Test Function
  function testSms() {
    const callingCode = $('#sms-test-calling-code').val().trim();
    const telephone = $('#sms-test-telephone').val().trim();
    const type = $('#sms-test-type').val();
    const $btn = $('#sms-test-btn');
    const $result = $('#sms-test-result');

    // Validation
    if (!callingCode) {
      $result.html('<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-circle"></i> {{ __('panel/setting.sms_test_calling_code_required') }}</div>');
      return;
    }

    if (!telephone) {
      $result.html('<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-circle"></i> {{ __('panel/setting.sms_test_telephone_required') }}</div>');
      return;
    }

    // Disable button and show loading
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> {{ __('panel/setting.sms_test_sending') }}');
    $result.html('');

    // Send test SMS
    $.ajax({
      url: '{{ panel_route("settings.test_sms") }}',
      method: 'POST',
      data: {
        calling_code: callingCode,
        telephone: telephone,
        type: type,
        _token: '{{ csrf_token() }}'
      },
      success: function(response) {
        if (response.success) {
          $result.html('<div class="alert alert-success mb-0"><i class="bi bi-check-circle"></i> ' + response.message + '</div>');
        } else {
          $result.html('<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-circle"></i> ' + response.message + '</div>');
        }
      },
      error: function(xhr) {
        let message = '{{ __('panel/setting.sms_test_failed') }}';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          message = xhr.responseJSON.message;
        }
        $result.html('<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-circle"></i> ' + message + '</div>');
      },
      complete: function() {
        // Re-enable button
        $btn.prop('disabled', false).html('<i class="bi bi-send"></i> {{ __('panel/setting.sms_test_send') }}');
      }
    });
  }
</script>

