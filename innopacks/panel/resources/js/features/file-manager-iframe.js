import Utils from '../core/utils.js';

/**
 * File Manager Iframe module.
 *
 * This module handles file manager iframe functionality for file selection.
 */
const FileManagerIframe = {
  /**
   * 打开文件管理器（兼容旧的 init 方法）
   * @param {Function} callback - 选择文件后的回调函数
   * @param {Object} options - 配置选项
   */
  init(callback, options = {}) {
    return this.open(callback, options);
  },

  /**
   * 打开文件管理器
   * @param {Function} callback - 选择文件后的回调函数
   * @param {Object} options - 配置选项
   */
  open(callback, options = {}) {
    const defaultOptions = {
      type: 'file',
      multiple: false,
      accept: '*'
    };

    const finalOptions = { ...defaultOptions, ...options };

    // 注册全局回调函数
    window.fileManagerCallback = function(files) {
      let config;
      if (window !== window.parent) {
        config = window.fileManagerConfig;
      } else {
        const iframe = document.querySelector(".layui-layer-iframe iframe");
        if (iframe) {
          try {
            config = iframe.contentWindow.fileManagerConfig;
          } catch (e) {
            console.error("Failed to get config from iframe:", e);
          }
        }
      }

      if (!config) config = finalOptions.config || {};

      if (typeof callback === 'function') {
        if (Array.isArray(files)) {
          // 处理多个文件
          const processedFiles = files.map(file => ({
            ...file,
            url: Utils.processFileManagerUrl(file, config)
          }));
          callback(finalOptions.multiple ? processedFiles : processedFiles[0]);
        } else {
          // 处理单个文件
          const processedFile = {
            ...files,
            url: Utils.processFileManagerUrl(files, config)
          };
          callback(processedFile);
        }
      }
    };

    // 构建文件管理器 URL
    const params = new URLSearchParams({
      type: finalOptions.type,
      multiple: finalOptions.multiple ? '1' : '0',
      accept: finalOptions.accept
    });

    const fileManagerUrl = `${window.urls?.base_url || ''}/file-manager?${params.toString()}`;
    const fallbackUrl = `/panel/file_manager/iframe?type=${finalOptions.type}&multiple=${finalOptions.multiple ? '1' : '0'}`;

    // 打开文件管理器弹窗
    layer.open({
      type: 2,
      title: '文件管理器',
      shadeClose: false,
      shade: 0.8,
      area: ['90%', '90%'],
      content: window.urls?.base_url ? fileManagerUrl : fallbackUrl,
      success: function(layero, index) {
        // 可以在这里添加额外的初始化逻辑
      },
      end: function() {
        // 清理回调函数
        delete window.fileManagerCallback;
      }
    });
  },

  /**
   * 初始化文件管理器相关的事件监听
   */
  init() {
    // 监听文件管理器按钮点击事件
    $(document).on('click', '[data-file-manager]', function(e) {
      e.preventDefault();
      
      const $button = $(this);
      const target = $button.data('target');
      const type = $button.data('type') || 'file';
      const multiple = $button.data('multiple') === true;
      const accept = $button.data('accept') || '*';
      
      FileManagerIframe.open(function(files) {
        if (target) {
          const $target = $(target);
          if ($target.length) {
            if (multiple && Array.isArray(files)) {
              const urls = files.map(file => file.url).join(',');
              $target.val(urls);
            } else {
              $target.val(files.url || '');
            }
            $target.trigger('change');
          }
        }
      }, {
        type,
        multiple,
        accept
      });
    });
  }
};

export default FileManagerIframe;