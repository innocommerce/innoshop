import { processMediaUrl } from './panel-api-setup';

export default {
  init: function(callback, options = {}) {
    const defaultOptions = {
      type: "image",
      multiple: false,
    };

    const finalOptions = { ...defaultOptions, ...options };

    window.mediaCallback = function(file) {
      let config;
      if (window !== window.parent) {
        config = window.mediaConfig;
      } else {
        const iframe = document.querySelector(".layui-layer-iframe iframe");
        if (iframe) {
          try {
            config = iframe.contentWindow.mediaConfig;
          } catch (e) {
            console.error("Failed to get config from iframe:", e);
          }
        }
      }

      if (!config) return file;

      if (Array.isArray(file)) {
        const processedFiles = file.map(f => ({
          ...f,
          original_path: f.path,
          url: processMediaUrl(f, config),
          // Override path with the stable media:// reference so business tables
          // (products.images, brands.logo, etc.) store a handle that survives
          // rename/move/delete on the file manager. Falls back to raw storage_key
          // for legacy installs or files without a media_files row.
          path: f.media_reference || f.path,
        }));

        if (typeof callback === "function") {
          callback(processedFiles);
        }
        return processedFiles;
      }

      const processedFile = {
        ...file,
        original_path: file.path,
        url: processMediaUrl(file, config),
        path: file.media_reference || file.path,
      };

      if (typeof callback === "function") {
        callback(processedFile);
      }
      return processedFile;
    };

    layer.open({
      type: 2,
      title: urls.media_title || "File Manager",
      shadeClose: false,
      shade: 0.8,
      area: ["90%", "90%"],
      content: `${urls.panel_base}/media/iframe?type=${finalOptions.type}&multiple=${finalOptions.multiple ? "1" : "0"}`,
    });
  }
};
