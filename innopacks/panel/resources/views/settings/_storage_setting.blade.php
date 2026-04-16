<!-- Storage Settings -->
<div class="tab-pane fade" id="tab-setting-storage">
<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/setting.storage_settings') }}</h5>
    <p class="text-muted small mb-0">{{ __('panel/setting.storage_settings_desc') }}</p>
  </div>
  <div class="card-body">
    <x-panel::form.row :title="__('panel/setting.storage_driver')">
      <select class="form-select me-3" id="storage_driver_select" style="max-width: 400px;">
        <option value="local" selected>{{ __('panel/setting.storage_driver_local') }}</option>
        <option value="oss">{{ __('panel/setting.storage_driver_oss') }}</option>
        <option value="cos">{{ __('panel/setting.storage_driver_cos') }}</option>
        <option value="qiniu">{{ __('panel/setting.storage_driver_qiniu') }}</option>
        <option value="s3">{{ __('panel/setting.storage_driver_s3') }}</option>
        <option value="obs">{{ __('panel/setting.storage_driver_obs') }}</option>
        <option value="r2">{{ __('panel/setting.storage_driver_r2') }}</option>
        <option value="minio">{{ __('panel/setting.storage_driver_minio') }}</option>
      </select>
    </x-panel::form.row>
    <div class="text-secondary mb-4">
      <small>{{ __('panel/setting.storage_driver_desc') }}</small>
    </div>

    <!-- Per-driver enabled hidden inputs (always present so form submits correct values) -->
    @foreach (['oss', 'cos', 'qiniu', 's3', 'obs', 'r2', 'minio'] as $drv)
      <input type="hidden" name="storage_{{ $drv }}_enabled" value="{{ system_setting("storage_{$drv}_enabled", '0') }}">
    @endforeach

    <!-- Cloud Storage Credentials (shown when driver is not local) -->
    <div id="cloudStorageFields" class="mt-4">
      <!-- Driver-specific hint -->
      <div id="storageDriverHint" class="alert alert-info small py-2 px-3 mb-3" style="display:none;">
        <span id="storageHintText"></span>
        <a id="storageHintLink" href="#" target="_blank" rel="noopener noreferrer" class="alert-link"></a>
      </div>

      <x-panel::form.row :title="__('panel/setting.storage_access_key')">
        <input type="text" id="storageCredentialKey"
          class="form-control" style="max-width: 400px;">
      </x-panel::form.row>

      <x-panel::form.row :title="__('panel/setting.storage_secret_key')">
        <input type="password" id="storageCredentialSecret"
          class="form-control" style="max-width: 400px;">
      </x-panel::form.row>

      <x-panel::form.row :title="__('panel/setting.storage_endpoint')">
        <input type="text" id="storageCredentialEndpoint"
          class="form-control" style="max-width: 400px;"
          placeholder="{{ __('panel/setting.storage_endpoint_placeholder') }}">
        <div class="text-secondary mt-1"><small id="storageEndpointHint"></small></div>
      </x-panel::form.row>

      <x-panel::form.row :title="__('panel/setting.storage_bucket')">
        <input type="text" id="storageCredentialBucket"
          class="form-control" style="max-width: 400px;">
      </x-panel::form.row>

      <x-panel::form.row :title="__('panel/setting.storage_region')">
        <input type="text" id="storageCredentialRegion"
          class="form-control" style="max-width: 400px;"
          placeholder="{{ __('panel/setting.storage_region_placeholder') }}">
        <div class="text-secondary mt-1"><small id="storageRegionHint"></small></div>
      </x-panel::form.row>

      <x-panel::form.row :title="__('panel/setting.storage_cdn_domain')">
        <input type="text" id="storageCredentialCdnDomain"
          class="form-control" style="max-width: 400px;"
          placeholder="{{ __('panel/setting.storage_cdn_domain_placeholder') }}">
      </x-panel::form.row>

      <x-panel::form.row :title="__('panel/setting.storage_enable_in_file_manager')">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" role="switch"
            id="storage_driver_enabled"
            value="1">
        </div>
      </x-panel::form.row>
      <div class="text-secondary mb-3">
        <small>{{ __('panel/setting.storage_enable_in_file_manager_desc') }}</small>
      </div>
    </div>
  </div>
</div>
</div>

@push('footer')
<script>
  $(function() {
    // All driver credentials from server (driver-prefixed keys)
    var driverCredentials = {
      oss: {
        key: '{{ system_setting("storage_oss_key", system_setting("storage_key", "")) }}',
        secret: '{{ system_setting("storage_oss_secret", system_setting("storage_secret", "")) }}',
        endpoint: '{{ system_setting("storage_oss_endpoint", system_setting("storage_endpoint", "")) }}',
        bucket: '{{ system_setting("storage_oss_bucket", system_setting("storage_bucket", "")) }}',
        region: '{{ system_setting("storage_oss_region", system_setting("storage_region", "")) }}',
        cdn_domain: '{{ system_setting("storage_oss_cdn_domain", system_setting("storage_cdn_domain", "")) }}',
        enabled: '{{ system_setting("storage_oss_enabled", "0") }}' === '1',
      },
      cos: {
        key: '{{ system_setting("storage_cos_key", "") }}',
        secret: '{{ system_setting("storage_cos_secret", "") }}',
        endpoint: '{{ system_setting("storage_cos_endpoint", "") }}',
        bucket: '{{ system_setting("storage_cos_bucket", "") }}',
        region: '{{ system_setting("storage_cos_region", "") }}',
        cdn_domain: '{{ system_setting("storage_cos_cdn_domain", "") }}',
        enabled: '{{ system_setting("storage_cos_enabled", "0") }}' === '1',
      },
      qiniu: {
        key: '{{ system_setting("storage_qiniu_key", "") }}',
        secret: '{{ system_setting("storage_qiniu_secret", "") }}',
        endpoint: '{{ system_setting("storage_qiniu_endpoint", "") }}',
        bucket: '{{ system_setting("storage_qiniu_bucket", "") }}',
        region: '{{ system_setting("storage_qiniu_region", "") }}',
        cdn_domain: '{{ system_setting("storage_qiniu_cdn_domain", "") }}',
        enabled: '{{ system_setting("storage_qiniu_enabled", "0") }}' === '1',
      },
      s3: {
        key: '{{ system_setting("storage_s3_key", "") }}',
        secret: '{{ system_setting("storage_s3_secret", "") }}',
        endpoint: '{{ system_setting("storage_s3_endpoint", "") }}',
        bucket: '{{ system_setting("storage_s3_bucket", "") }}',
        region: '{{ system_setting("storage_s3_region", "") }}',
        cdn_domain: '{{ system_setting("storage_s3_cdn_domain", "") }}',
        enabled: '{{ system_setting("storage_s3_enabled", "0") }}' === '1',
      },
      obs: {
        key: '{{ system_setting("storage_obs_key", "") }}',
        secret: '{{ system_setting("storage_obs_secret", "") }}',
        endpoint: '{{ system_setting("storage_obs_endpoint", "") }}',
        bucket: '{{ system_setting("storage_obs_bucket", "") }}',
        region: '{{ system_setting("storage_obs_region", "") }}',
        cdn_domain: '{{ system_setting("storage_obs_cdn_domain", "") }}',
        enabled: '{{ system_setting("storage_obs_enabled", "0") }}' === '1',
      },
      r2: {
        key: '{{ system_setting("storage_r2_key", "") }}',
        secret: '{{ system_setting("storage_r2_secret", "") }}',
        endpoint: '{{ system_setting("storage_r2_endpoint", "") }}',
        bucket: '{{ system_setting("storage_r2_bucket", "") }}',
        region: '{{ system_setting("storage_r2_region", "") }}',
        cdn_domain: '{{ system_setting("storage_r2_cdn_domain", "") }}',
        enabled: '{{ system_setting("storage_r2_enabled", "0") }}' === '1',
      },
      minio: {
        key: '{{ system_setting("storage_minio_key", "") }}',
        secret: '{{ system_setting("storage_minio_secret", "") }}',
        endpoint: '{{ system_setting("storage_minio_endpoint", "") }}',
        bucket: '{{ system_setting("storage_minio_bucket", "") }}',
        region: '{{ system_setting("storage_minio_region", "") }}',
        cdn_domain: '{{ system_setting("storage_minio_cdn_domain", "") }}',
        enabled: '{{ system_setting("storage_minio_enabled", "0") }}' === '1',
      },
    };

    var driverHints = {
      oss: {
        text: '{{ __("panel/setting.storage_hint_oss") }}',
        link: 'https://help.aliyun.com/document_detail/64919.html',
        endpointHint: '{{ __("panel/setting.storage_endpoint_hint_oss") }}',
        regionHint: '{{ __("panel/setting.storage_region_hint_oss") }}',
      },
      cos: {
        text: '{{ __("panel/setting.storage_hint_cos") }}',
        link: 'https://cloud.tencent.com/document/product/436/37421',
        endpointHint: '{{ __("panel/setting.storage_endpoint_hint_cos") }}',
        regionHint: '{{ __("panel/setting.storage_region_hint_cos") }}',
      },
      qiniu: {
        text: '{{ __("panel/setting.storage_hint_qiniu") }}',
        link: 'https://developer.qiniu.com/kodo/4088/s3-access',
        endpointHint: '{{ __("panel/setting.storage_endpoint_hint_qiniu") }}',
        regionHint: '{{ __("panel/setting.storage_region_hint_qiniu") }}',
      },
      s3: {
        text: '{{ __("panel/setting.storage_hint_s3") }}',
        link: 'https://docs.aws.amazon.com/AmazonS3/latest/API/Welcome.html',
        endpointHint: '{{ __("panel/setting.storage_endpoint_hint_s3") }}',
        regionHint: '{{ __("panel/setting.storage_region_hint_s3") }}',
      },
      obs: {
        text: '{{ __("panel/setting.storage_hint_obs") }}',
        link: 'https://support.huaweicloud.com/intl/en-us/obs/index.html',
        endpointHint: '{{ __("panel/setting.storage_endpoint_hint_obs") }}',
        regionHint: '{{ __("panel/setting.storage_region_hint_obs") }}',
      },
      r2: {
        text: '{{ __("panel/setting.storage_hint_r2") }}',
        link: 'https://developers.cloudflare.com/r2/',
        endpointHint: '{{ __("panel/setting.storage_endpoint_hint_r2") }}',
        regionHint: '{{ __("panel/setting.storage_region_hint_r2") }}',
      },
      minio: {
        text: '{{ __("panel/setting.storage_hint_minio") }}',
        link: 'https://min.io/docs/minio/linux/index.html',
        endpointHint: '{{ __("panel/setting.storage_endpoint_hint_minio") }}',
        regionHint: '{{ __("panel/setting.storage_region_hint_minio") }}',
      },
    };

    function populateFields(driver) {
      var creds = driverCredentials[driver];
      if (!creds) {
        creds = { key: '', secret: '', endpoint: '', bucket: '', region: '', cdn_domain: '', enabled: false };
      }
      $('#storageCredentialKey').val(creds.key).attr('name', 'storage_' + driver + '_key');
      $('#storageCredentialSecret').val(creds.secret).attr('name', 'storage_' + driver + '_secret');
      $('#storageCredentialEndpoint').val(creds.endpoint).attr('name', 'storage_' + driver + '_endpoint');
      $('#storageCredentialBucket').val(creds.bucket).attr('name', 'storage_' + driver + '_bucket');
      $('#storageCredentialRegion').val(creds.region).attr('name', 'storage_' + driver + '_region');
      $('#storageCredentialCdnDomain').val(creds.cdn_domain).attr('name', 'storage_' + driver + '_cdn_domain');

      // Restore this driver's enabled state from its own setting
      $('#storage_driver_enabled').prop('checked', creds.enabled);
    }

    function toggleCloudFields() {
      var driver = $('#storage_driver_select').val();
      var hintConfig = driverHints[driver];

      if (driver === 'local') {
        $('#cloudStorageFields').hide();
        $('#storageDriverHint').hide();
      } else {
        populateFields(driver);
        $('#cloudStorageFields').show();
        if (hintConfig) {
          $('#storageHintText').text(hintConfig.text);
          $('#storageHintLink').attr('href', hintConfig.link).text(hintConfig.link);
          $('#storageEndpointHint').text(hintConfig.endpointHint);
          $('#storageRegionHint').text(hintConfig.regionHint);
          $('#storageDriverHint').show();
        } else {
          $('#storageDriverHint').hide();
          $('#storageEndpointHint').text('');
          $('#storageRegionHint').text('');
        }
      }
    }

    // When enable toggle changes, update the per-driver hidden input
    $('#storage_driver_enabled').on('change', function() {
      var driver = $('#storage_driver_select').val();
      if (driver === 'local') return;
      var checked = $(this).is(':checked');
      $('input[name="storage_' + driver + '_enabled"]').val(checked ? '1' : '0');
    });

    toggleCloudFields();
    $('#storage_driver_select').on('change', toggleCloudFields);
  });
</script>
@endpush
