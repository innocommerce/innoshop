@push('header')
  <link rel="stylesheet" href="{{ asset('vendor/cropper/cropper.min.css') }}">
  <script src="{{ asset('vendor/cropper/cropper.min.js') }}"></script>

  @php($enabledDrivers = $enabled_drivers ?? ['local'])
  <script>
    // Get config from URL params
    const urlParams = new URLSearchParams(window.location.search);
    window.fileManagerConfig = {
      multiple: urlParams.get('multiple') === '1',
      type: urlParams.get('type') || 'all',
      callback: window.parent.fileManagerCallback,
      driver: '{{ $config['driver'] }}',
      endpoint: '{{ $config['endpoint'] }}',
      bucket: '{{ $config['bucket'] }}',
      baseUrl: '{{ $config['baseUrl'] }}',
      enabledDrivers: @json($enabledDrivers),
      enableCrop: {{ system_setting('file_manager_enable_crop', false) ? 'true' : 'false' }},
      uploadMaxFileSize: '{{ $uploadMaxFileSize ?? "unknown" }}',
      postMaxSize: '{{ $postMaxSize ?? "unknown" }}'
    };
  </script>

  <script>
    // HTTP request wrapper
    (function(window) {
      'use strict';

      window.getApiToken = () => {
        const currentToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
        const parentToken = window.parent?.document.querySelector('meta[name="api-token"]')?.getAttribute('content');
        return currentToken || parentToken;
      };

      // Create axios instance
      const http = axios.create({
        baseURL: '/api/panel/',
        timeout: 30000,
        headers: {
          'Authorization': 'Bearer ' + window.getApiToken()
        }
      });

      // Request interceptor: refresh token on every request
      http.interceptors.request.use(config => {
        // Refresh token before each request
        config.headers.Authorization = 'Bearer ' + window.getApiToken();

        // Show loading spinner
        if (window.layer) {
          layer.load(2, {
            shade: [0.3, '#fff']
          });
        }
        return config;
      });

      // Response interceptor
      http.interceptors.response.use(
        response => {
          if (window.layer) {
            layer.closeAll('loading');
          }
          return response.data;
        },
        error => {
          if (window.layer) {
            layer.closeAll('loading');
          }

          // Error handling
          if (error.response) {
            const message = error.response.data.message || 'Request failed';
            // Use Element Plus message notification
            if (window.ElementPlus) {
              ElementPlus.ElMessage.error(message);
            }

            switch (error.response.status) {
              case 401:
                // Unauthorized
                break;
              case 403:
                // Forbidden
                break;
              case 404:
                // Not found
                break;
              default:
                // Other errors
                break;
            }
          }
          return Promise.reject(error);
        }
      );
      window.http = http; // Expose http on window
    })(window);
  </script>
@endpush
