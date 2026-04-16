@push('header')
  <script src="{{ asset('vendor/vue/2.7/vue.min.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('vendor/element-ui/element-ui.css') }}">
  <script src="{{ asset('vendor/element-ui/element-ui.js') }}"></script>
  <!-- Element UI 英文语言包（根据当前语言切换） -->
  <script src="https://unpkg.com/element-ui/lib/umd/locale/en.js"></script>
  <script>
    // 根据系统语言设置 Element UI 的语言
    (function(){
      var currentLocale = '{{ locale_code() }}';
      if (window.ELEMENT && currentLocale === 'en' && window.ELEMENT.lang && window.ELEMENT.lang.en) {
        window.ELEMENT.locale(window.ELEMENT.lang.en);
      }
    })();
  </script>
  <link rel="stylesheet" href="{{ asset('vendor/cropper/cropper.min.css') }}">
  <script src="{{ asset('vendor/cropper/cropper.min.js') }}"></script>
  <script src="{{ asset('vendor/vuedraggable/vuedraggable.umd.min.js') }}"></script>

  @php($enabledDrivers = $enabled_drivers ?? ['local'])
  <script>
    // 从 URL 参数获取配置
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
    // http 请求封装
    (function(window) {
      'use strict';

      window.getApiToken = () => {
        const currentToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
        const parentToken = window.parent?.document.querySelector('meta[name="api-token"]')?.getAttribute('content');
        return currentToken || parentToken;
      };

      // 创建 axios 实例
      const http = axios.create({
        baseURL: '/api/panel/',
        timeout: 30000,
        headers: {
          'Authorization': 'Bearer ' + window.getApiToken()
        }
      });

      // 添加请求拦截器，确保每次请求都使用最新的 token
      http.interceptors.request.use(config => {
        // 每次请求前重新获取 token
        config.headers.Authorization = 'Bearer ' + window.getApiToken();

        // 添加 loading
        if (window.layer) {
          layer.load(2, {
            shade: [0.3, '#fff']
          });
        }
        return config;
      });

      // 响应拦截器
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

          // 错误处理
          if (error.response) {
            const message = error.response.data.message || '请求失败';
            // 使用 Element UI 的消息提示
            if (window.Vue && window.ELEMENT) {
              ELEMENT.Message.error(message);
            }

            switch (error.response.status) {
              case 401:
                // 未授权处理
                break;
              case 403:
                // 禁止访问处理
                break;
              case 404:
                // 未找到处理
                break;
              default:
                // 其他错误
                break;
            }
          }
          return Promise.reject(error);
        }
      );
      window.http = http; // 确保 http 也被添加到 window 对象上
    })(window);
  </script>

  <link rel="stylesheet" href="{{ mix('build/panel/css/file-manager.css') }}">
@endpush
