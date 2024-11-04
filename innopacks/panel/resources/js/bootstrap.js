import axios from 'axios';
window.axios = axios;

// import $ from 'jquery';
// window.$ = window.jquery = $;


window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

axios.interceptors.request.use(
  config => {
    // 在发送请求之前显示加载层
    layer.load(2, { shade: [0.3, '#fff'] });
    return config;
  },
  error => {
    // 对请求错误做些什么
    layer.closeAll('loading'); // 确保请求失败时关闭加载层
    return Promise.reject(error);
  }
);

axios.interceptors.response.use(
  response => {
    // 在响应成功时关闭加载层
    layer.closeAll('loading');
    return response.data;
  },
  error => {
    // 在响应失败时也关闭加载层
    layer.closeAll('loading');
    return Promise.reject(error);
  }
);
