@extends('panel::layouts.blank')

@section('title', __('panel/menu.file_manager'))

@prepend('header')
  <meta name="api-token" content="{{ auth()->user()->api_token }}">
  <script>
    window.fileManagerConfig = Object.freeze({
      driver: '{{ $config['driver'] }}',
      endpoint: '{{ $config['endpoint'] }}',
      bucket: '{{ $config['bucket'] }}',
      baseUrl: '{{ $config['baseUrl'] }}',
      multiple: {{ $multiple ? 'true' : 'false' }},
      type: '{{ $type }}'
    });
    console.log('File manager config initialized in iframe:', window.fileManagerConfig);
  </script>
@endprepend

@section('page-bottom-btns')
  <div class="page-bottom-btns" id="bottom-btns">
    <button class="btn btn-primary" @click="handleConfirm">选择提交</button>
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

    /* 主内容区域 */
    .content-wrapper {
      overflow: hidden;
      position: relative;
    }

    /* 文件管理器内容区域 */
    .file-manager {
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    /* 文件列表区域 */
    .file-list {
      flex: 1;
      overflow-y: auto;
      padding: 20px;
    }

    /* 底部按钮固定在底部 */
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

    /* 左侧文件夹树 */
    .folder-tree {
      height: 100%;
      border-right: 1px solid #EBEEF5;
      overflow-y: auto;
    }

    /* 工具栏样式 */
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
    // 创建底部按钮的 Vue 实例
    new Vue({
      el: '#bottom-btns',
      methods: {
        handleConfirm() {
          // 获取主 Vue 实例并调用其方法
          const mainApp = document.querySelector('#app').__vue__;
          if (mainApp && typeof mainApp.confirmSelection === 'function') {
            mainApp.confirmSelection();
          }
        }
      }
    });

    // 从父窗口获取 token
    window.getApiToken = () => {
      const token = window.parent?.document.querySelector('meta[name="api-token"]')?.getAttribute('content');
      console.log('Parent token:', token);
      return token;
    };
  </script>
@endpush

<div class="content-wrapper">
  @include('panel::file_manager.main')
</div>
