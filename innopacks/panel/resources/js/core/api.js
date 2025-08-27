import axios from 'axios';
import Config from './config';

/**
 * API module for handling all network requests.
 *
 * This module centralizes API calls, providing a consistent way to handle
 * loading states, errors, and authentication headers.
 */
const Api = {
  /**
   * Initializes the API module by setting up default headers.
   */
  init: () => {
    axios.defaults.headers.common["Authorization"] = "Bearer " + Config.apiToken;
    axios.defaults.headers.common["X-CSRF-TOKEN"] = Config.csrfToken;
    axios.defaults.headers.common["locale"] = Config.locale;
    $.ajaxSetup({
      headers: {
        "X-CSRF-TOKEN": Config.csrfToken,
        Authorization: "Bearer " + Config.apiToken,
        locale: Config.locale,
      },
    });
  },

  /**
   * Performs a GET request.
   * @param {string} url - The URL to request.
   * @param {object} config - Axios request configuration.
   * @returns {Promise} - The axios promise.
   */
  get: (url, config = {}) => {
    return axios.get(url, config);
  },

  /**
   * Performs a POST request with loading indicators.
   * @param {string} url - The URL to post to.
   * @param {object} data - The data to post.
   * @param {object} config - Axios request configuration.
   * @returns {Promise} - The axios promise.
   */
  post: (url, data = {}, config = {}) => {
    layer.load(2, { shade: [0.3, "#fff"] });
    return axios.post(url, data, config)
      .catch(error => {
        const errorMessage = error.response?.data?.message || error.message;
        layer.msg(errorMessage, { icon: 2 });
        return Promise.reject(error);
      })
      .finally(() => {
        layer.closeAll("loading");
      });
  },

  /**
   * Performs a PUT request.
   * @param {string} url - The URL to put to.
   * @param {object} data - The data to put.
   * @param {object} config - Axios request configuration.
   * @returns {Promise} - The axios promise.
   */
  put: (url, data = {}, config = {}) => {
    layer.load(2, { shade: [0.3, "#fff"] });
    return axios.put(url, data, config)
      .catch(error => {
        const errorMessage = error.response?.data?.message || error.message;
        layer.msg(errorMessage, { icon: 2 });
        return Promise.reject(error);
      })
      .finally(() => {
        layer.closeAll("loading");
      });
  },

  /**
   * Performs a DELETE request.
   * @param {string} url - The URL to delete.
   * @param {object} config - Axios request configuration.
   * @returns {Promise} - The axios promise.
   */
  delete: (url, config = {}) => {
    layer.load(2, { shade: [0.3, "#fff"] });
    return axios.delete(url, config)
      .catch(error => {
        const errorMessage = error.response?.data?.message || error.message;
        layer.msg(errorMessage, { icon: 2 });
        return Promise.reject(error);
      })
      .finally(() => {
        layer.closeAll("loading");
      });
  }
};

export default Api;