<div class="container">
  <div class="col-md-6 mx-auto">
    <div class="row">
      <x-common-form-switch-radio title="{{ __('panel/setting.email_enable') }}" name="email_enable"
                                  required
                                  value="{{ old('expand', system_setting('email_enable')) }}"></x-common-form-switch-radio>
    </div>
    @if(system_setting('email_enable'))
      <div class="row">
        <x-common-form-select title="{{ __('panel/setting.email_engine') }}" name="email_engine"
                              :options="$mail_engines" key="code" label="name" :emptyOption="false"
                              value="{{ old('email_engine', system_setting('email_engine')) }}" required
                              placeholder="{{ __('panel/setting.email_engine') }}"/>
      </div>
      <div class="row">
        <x-common-form-input title="{{ __('panel/setting.smtp_host') }}" name="smtp_host"
                             value="{{ old('smtp_host', system_setting('smtp_host')) }}"
                             placeholder="{{ __('panel/setting.smtp_host') }}" required/>
      </div>
      <div class="row">
        <x-common-form-input title="{{ __('panel/setting.smtp_username') }}"
                             name="smtp_username"
                             value="{{ old('smtp_username', system_setting('smtp_username')) }}"
                             placeholder="{{ __('panel/setting.smtp_username') }}" required/>
      </div>
      <div class="row">
        <x-common-form-input type="password" title="{{ __('panel/setting.smtp_password') }}"
                             name="smtp_password"
                             value="{{ old('smtp_password', system_setting('smtp_password')) }}"
                             placeholder="{{ __('panel/setting.smtp_password') }}" required/>
      </div>
      <div class="row">
        @php($emailEncryptionType=[['code'=>'SSL','name'=>'SSL','value'=>'SSL'],['code'=>'TLS','name'=>'TLS','value'=>'TLS'],])
        <x-common-form-select title="{{ __('panel/setting.smtp_encryption') }}"
                              name="smtp_encryption" :options="$emailEncryptionType" key="code"
                              label="name"
                              value="{{ old('smtp_encryption', system_setting('smtp_encryption')) }}"
                              required placeholder="{{ __('panel/setting.smtp_encryption') }}"/>
      </div>
      <div class="row">
        <x-common-form-input title="{{ __('panel/setting.smtp_port') }}" name="smtp_port"
                             value="{{ old('smtp_port', system_setting('smtp_port')) }}"
                             placeholder="{{ __('panel/setting.smtp_port') }}" required/>
      </div>
      <div class="row">
        <x-common-form-input title="{{ __('panel/setting.smtp_timeout') }}" name="smtp_timeout"
                             value="{{ old('smtp_timeout', system_setting('smtp_timeout', 5)) }}"
                             placeholder="{{ __('panel/setting.smtp_timeout') }}"/>
      </div>
    @endif
  </div>
</div>