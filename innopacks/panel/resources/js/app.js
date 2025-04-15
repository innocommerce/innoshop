import "./bootstrap";
import "./bootstrap-validation";
import "./autocomplete";
import common from "./common";
import dominateColor from "./dominate_color";

const Config = {
  base: document.querySelector("base").href,
  editorLanguage: document.querySelector('meta[name="editor_language"]')?.content || "zh_cn",
  apiToken: $('meta[name="api-token"]').attr("content") || 
            $(window.parent.document).find('meta[name="api-token"]').attr("content"),
  csrfToken: $('meta[name="csrf-token"]').attr("content"),
};

const Utils = {
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

  setupApiHeaders: () => {
    axios.defaults.headers.common["Authorization"] = "Bearer " + Config.apiToken;
    $.ajaxSetup({
      headers: {
        "X-CSRF-TOKEN": Config.csrfToken,
        Authorization: "Bearer " + Config.apiToken,
      },
    });
    window.apiToken = $.apiToken = Config.apiToken;
  }
};

const UI = {
  initTooltips: () => {
    $('[data-bs-toggle="tooltip"]').tooltip();
  },

  initTabNavigation: () => {
    $("a[data-bs-target], button[data-bs-target]").on("click", function () {
      const dataBsTarget = $(this).attr("data-bs-target");
      if ($(this).hasClass("nav-link")) {
        const url = new URL(window.location.href);
        url.searchParams.set("tab", dataBsTarget.replace("#", ""));
        window.history.pushState({}, "", url.toString());
      }
    });

    const tab = common.getQueryString("tab");
    if (tab) {
      const tabButton = $(`button[data-bs-target="#${tab}"]`);
      const tabLink = $(`a[data-bs-target="#${tab}"]`);
      if (tabButton.length) {
        tabButton[0].click();
      } else if (tabLink.length) {
        tabLink[0].click();
      }
    }
  },

  initHoverEffects: () => {
    $(".product-item-card")
      .mouseenter(function () {
        $(this)
          .css("transform", "translateY(-2%)")
          .removeClass("shadow-sm")
          .addClass("shadow-lg");
      })
      .mouseleave(function () {
        $(this)
          .css("transform", "translateY(0)")
          .removeClass("shadow-lg")
          .addClass("shadow-sm");
      });

    $(".plugin-market-nav-item")
      .mouseenter(function () {
        $(this).addClass("panel-item-hover");
      })
      .mouseleave(function () {
        $(this).removeClass("panel-item-hover");
      });
  },

  initAlerts: () => {
    $(document).on("click", ".is-alert .btn-close", function () {
      let top = 70;
      $(".is-alert").each(function () {
        $(this).animate({ top }, 100);
        top += $(this).outerHeight() + 10;
      });
    });
  },

  initSidebar: () => {
    $(document).on("click", ".mb-menu", function () {
      $(".sidebar-box").toggleClass("active");
    });

    $(".sidebar-box").on("click", function (e) {
      if (!$(e.target).parents(".sidebar-body").length) {
        $(".sidebar-box").removeClass("active");
      }
    });
  },

  initDatePickers: () => {
    $(document).on("focus", ".date input, .datetime input, .time input", function (event) {
      if (!$(this).prop("id")) {
        $(this).prop("id", Math.random().toString(36).substring(2));
      }

      $(this).attr("autocomplete", "off");

      laydate.render({
        elem: "#" + $(this).prop("id"),
        type: $(this).parent().hasClass("date")
          ? "date"
          : $(this).parent().hasClass("datetime")
          ? "datetime"
          : "time",
        trigger: "click",
        lang: $("html").prop("lang") == "zh-cn" ? "cn" : "en",
      });
    });
  },

  initAIGenerate: () => {
    $(".ai-generate").on("click", function (e) {
      let accordionBody = $(this).closest(".accordion-body");
      let formRow = $(this).closest(".form-row");
      let inputEle = formRow.find(":input");

      let formData = {
        locale_code: accordionBody.data("locale-code"),
        locale_name: accordionBody.data("locale-name"),
        column_name: $(this).data("column"),
        column_value: inputEle.val(),
      };

      layer.load(2, { shade: [0.3, "#fff"] });
      axios
        .post(urls.ai_generate, formData, {})
        .then(function (res) {
          let message = res.data.message;
          inputEle.val(message);
        })
        .catch(function (err) {
          layer.msg(err.response.data.message, { icon: 2 });
        })
        .finally(function () {
          layer.closeAll("loading");
        });
    });
  }
};

const FileManager = {
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
          url: Utils.processFileManagerUrl(f, config),
        }));

        if (typeof callback === "function") {
          callback(processedFiles);
        }
        return processedFiles;
      }

      const processedFile = {
        ...file,
        url: Utils.processFileManagerUrl(file, config),
      };

      if (typeof callback === "function") {
        callback(processedFile);
      }
      return processedFile;
    };

    layer.open({
      type: 2,
      title: "文件管理器",
      shadeClose: false,
      shade: 0.8,
      area: ["90%", "90%"],
      content: `/panel/file_manager/iframe?type=${finalOptions.type}&multiple=${finalOptions.multiple ? "1" : "0"}`,
    });
  }
};

const Editor = {
  init: () => {
    if (typeof tinymce === "undefined") return;

    tinymce.init({
      selector: ".tinymce",
      language: Config.editorLanguage,
      branding: false,
      height: 400,
      convert_urls: false,
      inline: false,
      relative_urls: false,
      plugins: "link lists fullscreen table hr wordcount image imagetools code",
      menubar: "",
      toolbar: "undo redo | toolbarImageButton | lineheight | bold italic underline strikethrough | forecolor backcolor | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify numlist bullist formatpainter removeformat charmap emoticons | preview template link anchor table toolbarImageUrlButton fullscreen code",
      toolbar_items_size: "small",
      image_caption: true,
      imagetools_toolbar: "",
      toolbar_mode: "wrap",
      font_formats: "微软雅黑='Microsoft YaHei';黑体=黑体;Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Georgia=georgia,palatino;Helvetica=helvetica;Times New Roman=times new roman,times;Verdana=verdana,geneva",
      fontsize_formats: "10px 12px 14px 16px 18px 24px 36px 48px 56px 72px 96px",
      lineheight_formats: "1 1.1 1.2 1.3 1.4 1.5 1.7 2.4 3 4",
      setup: Editor.setupEditor
    });
  },

  setupEditor: (ed) => {
    ed.ui.registry.addButton("toolbarImageButton", {
      icon: "image",
      onAction: () => {
        FileManager.init(
          (file) => {
            if (file.url) {
              ed.insertContent(`<img src="${file.url}" class="img-fluid" />`);
            }
          },
          { type: "image", multiple: false }
        );
      },
    });

    ed.on("paste", (e) => {
      const clipboardData = e.clipboardData;
      if (!clipboardData || !clipboardData.items) return;

      for (let i = 0; i < clipboardData.items.length; i++) {
        const item = clipboardData.items[i];
        if (item.type.indexOf("image") === -1) continue;

        e.preventDefault();
        const file = item.getAsFile();
        const formData = new FormData();
        formData.append("file", file);
        formData.append("path", "/");
        formData.append("type", "images");

        layer.load(2, { shade: [0.3, "#fff"] });

        axios
          .post("api/panel/file_manager/upload", formData)
          .then((response) => {
            if (response.data.url) {
              ed.insertContent(`<img src="${response.data.url}" class="img-fluid" />`);
            } else {
              throw new Error("Upload response missing URL");
            }
          })
          .catch((error) => {
            const errorMessage = error.response?.data?.message || 
                               error.response?.data?.error || 
                               error.message;
            layer.msg(errorMessage, { icon: 2 });
          })
          .finally(() => {
            layer.closeAll("loading");
          });

        break;
      }
    });

    ed.on("input", () => {
      tinymce.triggerSave();
    });
  }
};

$(function() {
  window.inno = common;
  window.dominateColor = dominateColor;
  window.inno.fileManagerIframe = FileManager.init;

  Utils.setupApiHeaders();

  UI.initTooltips();
  UI.initTabNavigation();
  UI.initHoverEffects();
  UI.initAlerts();
  UI.initSidebar();
  UI.initDatePickers();
  UI.initAIGenerate();

  Editor.init();

  inno.getTranslate();
});
