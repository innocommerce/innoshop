import { processFileManagerUrl } from './panel-api-setup';

export default {
  init: function(callback, options = {}) {
    const defaultOptions = {
      type: "image",
      multiple: false,
    };

    const finalOptions = { ...defaultOptions, ...options };

    window.fileManagerCallback = function(file) {
      let config;
      if (window !== window.parent) {
        config = window.fileManagerConfig;
      } else {
        const iframe = document.querySelector(".layui-layer-iframe iframe");
        if (iframe) {
          try {
            config = iframe.contentWindow.fileManagerConfig;
          } catch (e) {
            console.error("Failed to get config from iframe:", e);
          }
        }
      }

      if (!config) return file;

      if (Array.isArray(file)) {
        const processedFiles = file.map(f => ({
          ...f,
          url: processFileManagerUrl(f, config),
        }));

        if (typeof callback === "function") {
          callback(processedFiles);
        }
        return processedFiles;
      }

      const processedFile = {
        ...file,
        url: processFileManagerUrl(file, config),
      };

      if (typeof callback === "function") {
        callback(processedFile);
      }
      return processedFile;
    };

    layer.open({
      type: 2,
      title: urls.file_manager_title || "File Manager",
      shadeClose: false,
      shade: 0.8,
      area: ["90%", "90%"],
      content: `${urls.panel_base}/file_manager/iframe?type=${finalOptions.type}&multiple=${finalOptions.multiple ? "1" : "0"}`,
    });
  }
};
