/**
 * Axios defaults, CSRF, and layer loading interceptors for the panel.
 * Role matches front `inno-bootstrap.js` (HTTP client setup); not Bootstrap CSS/JS.
 */
import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

axios.interceptors.request.use(
  (config) => {
    const skipLoading =
      config.hload === true || (config.headers && config.headers['X-Skip-Loading']);

    if (!skipLoading) {
      layer.load(2, { shade: [0.3, '#fff'] });
    }
    return config;
  },
  (error) => {
    layer.closeAll('loading');
    return Promise.reject(error);
  },
);

axios.interceptors.response.use(
  (response) => {
    layer.closeAll('loading');
    return response.data;
  },
  (error) => {
    layer.closeAll('loading');
    return Promise.reject(error);
  },
);
