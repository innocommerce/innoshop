@extends('panel::layouts.blank')

@section('title', __('panel/menu.media'))

@prepend('header')
  <script src="{{ asset('vendor/vue/3.5/vue.global' . (config('app.debug') ? '' : '.prod') . '.js') }}"></script>
  <script src="{{ asset('vendor/element-plus/index.full.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('vendor/element-plus/index.css') }}">
  <script src="{{ asset('vendor/element-plus/icons.min.js') }}"></script>
  @if(str_starts_with(panel_locale_code(), 'zh'))
  <script src="{{ asset('vendor/element-plus/zh-cn.js') }}"></script>
  @endif
@endprepend

@prepend('header')
  <meta name="api-token" content="{{ auth()->user()->api_token }}">
  @php($enabledDrivers = $enabled_drivers ?? ['local'])
  <script>
    window.mediaConfig = Object.freeze({
      driver: '{{ $config['driver'] }}',
      endpoint: '{{ $config['endpoint'] }}',
      bucket: '{{ $config['bucket'] }}',
      baseUrl: '{{ $config['baseUrl'] }}',
      enabledDrivers: @json($enabledDrivers),
      multiple: {{ $multiple ? 'true' : 'false' }},
      type: '{{ $type }}',
      uploadMaxFileSize: '{{ $uploadMaxFileSize ?? "unknown" }}',
      postMaxSize: '{{ $postMaxSize ?? "unknown" }}'
    });
  </script>
@endprepend

@section('page-bottom-btns')
  <div class="page-bottom-btns" id="bottom-btns">
    <button class="btn btn-primary" @click="handleConfirm">{{ __('panel/media.select_submit') }}</button>
  </div>
@endsection

@push('header')
  <style>
    body {
      display: flex;
      flex-direction: column;
      height: 100vh;
      margin: 0;
      padding: 0;
      overflow: hidden;
    }

    /* Main content area */
    .content-wrapper {
      overflow: hidden;
      position: relative;
    }

    /* File manager content area */
    .media {
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    /* File list area */
    .file-list {
      flex: 1;
      overflow-y: auto;
      padding: 0;
    }
    .file-list-content {
      padding: 20px;
    }

    /* Bottom buttons fixed */
    .page-bottom-btns {
      height: 60px;
      padding: 10px;
      background: #fff;
      border-top: 1px solid #EBEEF5;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      z-index: 10;
    }

    /* Left folder tree */
    .folder-tree {
      height: 100%;
      border-right: 1px solid #EBEEF5;
      overflow-y: auto;
    }

    /* Toolbar styles */
    .file-toolbar {
      padding: 15px 20px;
      border-bottom: 1px solid #EBEEF5;
      background: #fff;
      position: relative;
      z-index: 10;
    }

  </style>
@endpush

@push('footer')
  <script>
    // Create Vue instance for bottom buttons
    const __btnApp = Vue.createApp({
      methods: {
        handleConfirm() {
          // Get main Vue instance and call its method
          const appEl = document.querySelector('#app');
          if (appEl && appEl.__vue_app__) {
            const mainApp = appEl.__vue_app__._instance.proxy;
            if (mainApp && typeof mainApp.confirmSelection === 'function') {
              mainApp.confirmSelection();
            }
          }
        }
      }
    });
    __btnApp.use(ElementPlus, window.ElementPlusLocaleZhCn ? { locale: ElementPlusLocaleZhCn } : {});
    for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
      __btnApp.component(key, component);
    }
    __btnApp.mount('#bottom-btns');

    // Get token from parent window, falling back to this iframe's own meta token.
    window.getApiToken = () => {
      const parentToken = window.parent?.document.querySelector('meta[name="api-token"]')?.getAttribute('content');
      const currentToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
      return parentToken || currentToken;
    };
  </script>
@endpush

<div class="content-wrapper">
  @include('panel::media.main')
</div>
