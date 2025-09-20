// Import axios HTTP client library
import axios from 'axios';
window.axios = axios;

// Import jQuery library and set global variables
import $ from 'jquery';
window.$ = window.jquery = $;
window.jQuery = require('jquery');

// Set axios default request headers
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

// Configure axios response interceptor
axios.interceptors.response.use(function (response) {
  // Return response data directly
  return response.data;
}, function (error) {
  // Handle error response, display error message
  if (error.response && error.response.data && error.response.data.message) {
    inno.alert({msg: error.response.data.message, type: 'danger'});
  }
  return Promise.reject(error);
});
