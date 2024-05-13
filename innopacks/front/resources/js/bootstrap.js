import axios from 'axios';
window.axios = axios;

import $ from 'jquery';
window.$ = window.jquery = $;
window.jQuery = require('jquery');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

axios.interceptors.response.use(function (response) {
  return response.data;
}, function (error) {
  if (error.response && error.response.data && error.response.data.message) {
    inno.alert({msg: error.response.data.message, type: 'danger'});
  }
  return Promise.reject(error);
});
