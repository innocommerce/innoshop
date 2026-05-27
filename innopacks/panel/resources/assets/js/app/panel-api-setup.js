/**
 * Panel API auth headers (axios + jQuery) and file-manager URL normalization.
 */
import { getPanelConfig } from './panel-config';

export function processFileManagerUrl(file, config) {
  const isOss = config?.driver === 'oss';
  if (file.url && file.url.startsWith('http')) {
    return file.url;
  }
  const filePath = file.path || file.url;

  if (isOss) {
    const endpoint = config.endpoint;
    const cleanEndpoint = endpoint.replace(/^https?:\/\//, '');
    return `https://${cleanEndpoint}/${filePath.replace(/^\//, '')}`;
  }
  return config.baseUrl + '/' + filePath.replace(/^\//, '');
}

export function setupApiHeaders() {
  const Config = getPanelConfig();
  axios.defaults.headers.common.Authorization = 'Bearer ' + Config.apiToken;
  axios.defaults.headers.common.locale = Config.locale;
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': Config.csrfToken,
      Authorization: 'Bearer ' + Config.apiToken,
      locale: Config.locale,
    },
  });
  window.apiToken = $.apiToken = Config.apiToken;
}
