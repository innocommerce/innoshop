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
    // Show loading layer before sending request
    layer.load(2, { shade: [0.3, '#fff'] });
    return config;
  },
  error => {
    // Handle request errors
    layer.closeAll('loading'); // Ensure loading layer is closed when request fails
    return Promise.reject(error);
  }
);
axios.interceptors.response.use(
  response => {
    // Close loading layer on successful response
    layer.closeAll('loading');
    return response.data;
  },
  error => {
    // Also close loading layer on response failure
    layer.closeAll('loading');
    return Promise.reject(error);
  }
);
