/**
 * Global configuration module.
 *
 * This module centralizes all global configuration variables for the application.
 * It retrieves configuration from DOM elements and makes them available to other modules.
 */
const Config = {
  base: document.querySelector("base")?.href || window.location.origin,
  editorLanguage: document.querySelector('meta[name="editor_language"]')?.content || "zh_cn",
  apiToken: $('meta[name="api-token"]').attr("content") ||
            $(window.parent.document).find('meta[name="api-token"]').attr("content"),
  csrfToken: $('meta[name="csrf-token"]').attr("content"),
  locale: $('html').attr("lang"),
};

export default Config;