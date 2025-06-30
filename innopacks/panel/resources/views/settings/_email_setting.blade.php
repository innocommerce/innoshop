<!-- Email Settings -->
<div class="tab-pane fade" id="tab-setting-email">
  <!-- Email Engine Settings -->
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('panel/setting.email_settings') }}</h5>
      <p class="text-muted small mb-0">{{ __('panel/setting.email_settings_desc') }}</p>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-12">
          <x-common-form-select title="{{ __('panel/setting.email_engine') }}" name="email_engine"
                              :options="$mail_engines" key="code" label="name" :emptyOption="false"
                              value="{{ old('email_engine', system_setting('email_engine')) }}" required
                              placeholder="{{ __('panel/setting.email_engine') }}"/>
        </div>
      </div>

      <div class="engine smtp d-none">
        <div class="row">
          <div class="col-md-6">
            <x-common-form-input title="{{ __('panel/setting.smtp_host') }}" name="smtp_host"
                               value="{{ old('smtp_host', system_setting('smtp_host')) }}"
                               placeholder="{{ __('panel/setting.smtp_host') }}"/>
          </div>
          <div class="col-md-6">
            <x-common-form-input title="{{ __('panel/setting.smtp_port') }}" name="smtp_port"
                               value="{{ old('smtp_port', system_setting('smtp_port')) }}"
                               placeholder="{{ __('panel/setting.smtp_port') }}"/>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <x-common-form-input title="{{ __('panel/setting.smtp_username') }}"
                               name="smtp_username"
                               value="{{ old('smtp_username', system_setting('smtp_username')) }}"
                               placeholder="{{ __('panel/setting.smtp_username') }}"/>
          </div>
          <div class="col-md-6">
            <x-common-form-input type="password" title="{{ __('panel/setting.smtp_password') }}"
                               name="smtp_password"
                               value="{{ old('smtp_password', system_setting('smtp_password')) }}"
                               placeholder="{{ __('panel/setting.smtp_password') }}"/>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            @php($emailEncryptionType=[["code"=>"SSL","name"=>"SSL","value"=>"SSL"],["code"=>"TLS","name"=>"TLS","value"=>"TLS"],])
            <x-common-form-select title="{{ __('panel/setting.smtp_encryption') }}"
                                name="smtp_encryption" :options="$emailEncryptionType" key="code"
                                label="name"
                                value="{{ old('smtp_encryption', system_setting('smtp_encryption')) }}"
                                placeholder="{{ __('panel/setting.smtp_encryption') }}"/>
          </div>
          <div class="col-md-6">
            <x-common-form-input title="{{ __('panel/setting.smtp_timeout') }}" name="smtp_timeout"
                               value="{{ old('smtp_timeout', system_setting('smtp_timeout', 5)) }}"
                               placeholder="{{ __('panel/setting.smtp_timeout') }}"/>
          </div>
        </div>
      </div>

      <div class="engine log d-none">
        {{ __('panel/setting.log_description') }}
      </div>

      @hookinsert('panel.settings.mail_engines')
    </div>
  </div>

  <!-- Email Notifications -->
  <div class="card">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('panel/setting.email_notifications') }}</h5>
      <p class="text-muted small mb-0">{{ __('panel/setting.email_notifications_desc') }}</p>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div class="mb-4">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="email_notifications[]" value="registration" id="registration" {{ in_array('registration', old('email_notifications', system_setting('email_notifications', []))) ? 'checked' : '' }}>
              <label class="form-check-label" for="registration">{{ __('panel/setting.registration') }}</label>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-4">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="email_notifications[]" value="password_reset" id="password_reset" {{ in_array('password_reset', old('email_notifications', system_setting('email_notifications', []))) ? 'checked' : '' }}>
              <label class="form-check-label" for="password_reset">{{ __('panel/setting.password_reset') }}</label>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="mb-4">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="email_notifications[]" value="new_order" id="new_order" {{ in_array('new_order', old('email_notifications', system_setting('email_notifications', []))) ? 'checked' : '' }}>
              <label class="form-check-label" for="new_order">{{ __('panel/setting.new_order') }}</label>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-4">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="email_notifications[]" value="order_status_update" id="order_status_update" {{ in_array('order_status_update', old('email_notifications', system_setting('email_notifications', []))) ? 'checked' : '' }}>
              <label class="form-check-label" for="order_status_update">{{ __('panel/setting.order_status_update') }}</label>
            </div>
          </div>
        </div>
      </div>
    </div>
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