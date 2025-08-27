import Config from './config.js';
import axios from 'axios';

/**
 * Utility functions module.
 *
 * This module provides a collection of utility functions that can be used across the application.
 */
const Utils = {
  /**
   * Processes the file manager URL to ensure it's a full URL.
   * @param {object} file - The file object from the file manager.
   * @param {object} config - The file manager configuration.
   * @returns {string} The full URL of the file.
   */
  processFileManagerUrl: (file, config) => {
    const isOss = config?.driver === "oss";
    if (file.url && file.url.startsWith("http")) {
      return file.url;
    }
    const filePath = file.path || file.url;

    if (isOss) {
      const endpoint = config.endpoint;
      const cleanEndpoint = endpoint.replace(/^https?:\/\//, "");
      return `https://${cleanEndpoint}/${filePath.replace(/^\//, "")}`;
    }
    return config.baseUrl + "/" + filePath.replace(/^\//, "");
  },

  /**
   * Sets up the API headers for axios and jQuery ajax.
   */
  setupApiHeaders: () => {
    axios.defaults.headers.common["Authorization"] = "Bearer " + Config.apiToken;
    axios.defaults.headers.common["locale"] = Config.locale;
    $.ajaxSetup({
      headers: {
        "X-CSRF-TOKEN": Config.csrfToken,
        Authorization: "Bearer " + Config.apiToken,
        locale: Config.locale,
      },
    });
    window.apiToken = $.apiToken = Config.apiToken;
  },

  /**
   * Gets a query string parameter by name.
   * @param {string} name - The name of the query string parameter.
   * @returns {string|null} The value of the query string parameter or null if not found.
   */
  getQueryString: (name) => {
    const reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    const r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]);
    return null;
  }
};

export default Utils;