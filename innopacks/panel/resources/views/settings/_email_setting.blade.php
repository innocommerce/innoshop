<div class="container">
  <div class="col-12 mx-auto">
  <div class="row">
        <x-common-form-select title="{{ __('panel/setting.email_engine') }}" name="email_engine"
                              :options="$mail_engines" key="code" label="name" :emptyOption="false"
                              value="{{ old('email_engine', system_setting('email_engine')) }}" required
                              placeholder="{{ __('panel/setting.email_engine') }}"/>
      </div>

     <div class="engine smtp d-none">
      <div class="row">
        <x-common-form-input title="{{ __('panel/setting.smtp_host') }}" name="smtp_host"
                             value="{{ old('smtp_host', system_setting('smtp_host')) }}"
                             placeholder="{{ __('panel/setting.smtp_host') }}"/>
      </div>
      <div class="row">
        <x-common-form-input title="{{ __('panel/setting.smtp_username') }}"
                             name="smtp_username"
                             value="{{ old('smtp_username', system_setting('smtp_username')) }}"
                             placeholder="{{ __('panel/setting.smtp_username') }}"/>
      </div>
      <div class="row">
        <x-common-form-input type="password" title="{{ __('panel/setting.smtp_password') }}"
                             name="smtp_password"
                             value="{{ old('smtp_password', system_setting('smtp_password')) }}"
                             placeholder="{{ __('panel/setting.smtp_password') }}"/>
      </div>
      <div class="row">
        @php($emailEncryptionType=[['code'=>'SSL','name'=>'SSL','value'=>'SSL'],['code'=>'TLS','name'=>'TLS','value'=>'TLS'],])
        <x-common-form-select title="{{ __('panel/setting.smtp_encryption') }}"
                              name="smtp_encryption" :options="$emailEncryptionType" key="code"
                              label="name"
                              value="{{ old('smtp_encryption', system_setting('smtp_encryption')) }}"
                              placeholder="{{ __('panel/setting.smtp_encryption') }}"/>
      </div>
      <div class="row">
        <x-common-form-input title="{{ __('panel/setting.smtp_port') }}" name="smtp_port"
                             value="{{ old('smtp_port', system_setting('smtp_port')) }}"
                             placeholder="{{ __('panel/setting.smtp_port') }}"/>
      </div>
      <div class="row">
        <x-common-form-input title="{{ __('panel/setting.smtp_timeout') }}" name="smtp_timeout"
                             value="{{ old('smtp_timeout', system_setting('smtp_timeout', 5)) }}"
                             placeholder="{{ __('panel/setting.smtp_timeout') }}"/>
      </div>
     </div>

     <div class="engine log d-none">
       {{ __('panel/setting.log_description') }}
     </div>

     @hookinsert('panel.settings.mail_engines')

  </div>
</div>
<script>
  $('document').ready(function () {
    let elm = $('select[name="email_engine"]');
    elm.change(function () {
      let selectedValue = $(this).val();
      $('.engine').addClass('d-none');
      if (selectedValue) {
       $('.'+selectedValue).removeClass('d-none');
      }
    });
    elm.trigger('change');
  });
</script>