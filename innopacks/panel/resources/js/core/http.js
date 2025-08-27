import Config from './config.js';
import axios from 'axios';

/**
 * HTTP configuration module.
 *
 * This module configures axios and jQuery AJAX settings for the application.
 */
const Http = {
  /**
   * 初始化 HTTP 配置
   */
  init() {
    // 配置 axios 默认设置
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    axios.defaults.headers.common['X-CSRF-TOKEN'] = Config.csrfToken;
    
    // 添加请求拦截器
    axios.interceptors.request.use(
      function (config) {
        // 显示加载层
        if (typeof layer !== 'undefined') {
          layer.load(2, { shade: [0.3, '#fff'] });
        }
        return config;
      },
      function (error) {
        return Promise.reject(error);
      }
    );

    // 添加响应拦截器
    axios.interceptors.response.use(
      function (response) {
        // 关闭加载层
        if (typeof layer !== 'undefined') {
          layer.closeAll('loading');
        }
        return response;
      },
      function (error) {
        // 关闭加载层
        if (typeof layer !== 'undefined') {
          layer.closeAll('loading');
        }
        return Promise.reject(error);
      }
    );

    // 配置 jQuery AJAX 默认设置
    $.ajaxSetup({
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': Config.csrfToken
      },
      beforeSend: function() {
        // 显示加载层
        if (typeof layer !== 'undefined') {
          layer.load(2, { shade: [0.3, '#fff'] });
        }
      },
      complete: function() {
        // 关闭加载层
        if (typeof layer !== 'undefined') {
          layer.closeAll('loading');
        }
      }
    });
  }
};

export default Http;