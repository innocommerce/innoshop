const base = document.querySelector("base").href;
const editor_language =
  document.querySelector('meta[name="editor_language"]')?.content || "zh_cn";

import "./bootstrap";
import "./bootstrap-validation";
import "./autocomplete";

import common from "./common";
window.inno = common;
import dominateColor from "./dominate_color";
window.dominateColor = dominateColor;

const apiToken =
  $('meta[name="api-token"]').attr("content") ||
  $(window.parent.document).find('meta[name="api-token"]').attr("content");
axios.defaults.headers.common["Authorization"] = "Bearer " + apiToken;
$.ajaxSetup({
  headers: {
    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    Authorization: "Bearer " + apiToken,
  },
});
window.apiToken = $.apiToken = apiToken;
if (window === window.parent) {
  //console.log('apiToken:' + apiToken);
}

const processFileManagerUrl = (file, config) => {
  const isOss = config?.driver === "oss";

  // 优先使用文件的完整 URL
  if (file.url && file.url.startsWith("http")) {
    console.log("Using original URL:", file.url);
    return file.url;
  }

  // 使用 path 构建 URL
  const filePath = file.path || file.url;

  if (isOss) {
    const endpoint = config.endpoint;
    // 确保 endpoint 不包含协议前缀
    const cleanEndpoint = endpoint.replace(/^https?:\/\//, "");
    const newUrl = `https://${cleanEndpoint}/${filePath.replace(/^\//, "")}`;
    console.log("Generated OSS URL:", newUrl);
    return newUrl;
  } else {
    const newUrl = config.baseUrl + "/" + filePath.replace(/^\//, "");
    console.log("Generated local URL:", newUrl);
    return newUrl;
  }
};

$(function () {
  tinymceInit();
  $("button[data-bs-target]").on("click", function () {
    const dataBsTarget = $(this).attr("data-bs-target");
    const url = new URL(window.location.href);
    url.searchParams.set("tab", dataBsTarget.replace("#", ""));
    window.history.pushState({}, "", url.toString());
  });

  const tab = inno.getQueryString("tab");
  if (tab) {
    if ($(`a[href="#${tab}"]`).length) {
      $(`a[href="#${tab}"]`)[0].click();
    } else if ($(`button[data-bs-target="#${tab}"]`).length) {
      $(`button[data-bs-target="#${tab}"]`)[0].click();
    }
  }

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

  $(document).on("click", ".is-alert .btn-close", function () {
    let top = 70;
    $(".is-alert").each(function () {
      $(this).animate({ top }, 100);
      top += $(this).outerHeight() + 10;
    });
  });

  $(document).on("click", ".mb-menu", function () {
    $(".sidebar-box").toggleClass("active");
  });

  $(".sidebar-box").on("click", function (e) {
    if (!$(e.target).parents(".sidebar-body").length) {
      $(".sidebar-box").removeClass("active");
    }
  });

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

  $(document).on(
    "focus",
    ".date input, .datetime input, .time input",
    function (event) {
      if (!$(this).prop("id"))
        $(this).prop("id", Math.random().toString(36).substring(2));

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
    }
  );

  // 添加文件管理器 iframe 方法到 inno 对象
  window.inno.fileManagerIframe = function (callback, options = {}) {
    const defaultOptions = {
      type: "image",
      multiple: false,
    };

    const finalOptions = { ...defaultOptions, ...options };

    // 设置回调函数
    window.fileManagerCallback = function (file) {
      // 获取配置 - 从当前 iframe 或父窗口获取
      let config;

      // 如果是在 iframe 中
      if (window !== window.parent) {
        config = window.fileManagerConfig;
      } else {
        // 如果是在父窗口中，尝试从打开的 iframe 获取配置
        const iframe = document.querySelector(".layui-layer-iframe iframe");
        if (iframe) {
          try {
            config = iframe.contentWindow.fileManagerConfig;
          } catch (e) {
            console.error("Failed to get config from iframe:", e);
          }
        }
      }

      console.log(
        "Current window is:",
        window === window.parent ? "parent" : "iframe"
      );
      console.log("File manager config:", config);

      // 如果没有配置，记录错误并返回原始文件
      if (!config) {
        console.error("No file manager config found!");
        return file;
      }

      // 如果是数组（多选模式）
      if (Array.isArray(file)) {
        const processedFiles = file.map((f) => ({
          ...f,
          url: processFileManagerUrl(f, config),
        }));

        if (typeof callback === "function") {
          callback(processedFiles);
        }
        return processedFiles;
      }

      // 单个文件处理
      const processedFile = {
        ...file,
        url: processFileManagerUrl(file, config),
      };

      // 调用原始回调函数并返回处理后的文件
      if (typeof callback === "function") {
        callback(processedFile);
      }
      return processedFile;
    };

    // 打开文件管理器
    layer.open({
      type: 2,
      title: "文件管理器",
      shadeClose: false,
      shade: 0.8,
      area: ["90%", "90%"],
      content:
        "/panel/file_manager/iframe?type=" +
        finalOptions.type +
        "&multiple=" +
        (finalOptions.multiple ? "1" : "0"),
    });
  };
});

const tinymceInit = () => {
  if (typeof tinymce == "undefined") {
    return;
  }

  tinymce.init({
    selector: ".tinymce",
    language: editor_language,
    branding: false,
    height: 400,
    convert_urls: false,
    // document_base_url: 'ssssss',
    inline: false,
    relative_urls: false,
    plugins: "link lists fullscreen table hr wordcount image imagetools code",
    menubar: "",
    toolbar:
      "undo redo | toolbarImageButton | lineheight | bold italic underline strikethrough | forecolor backcolor | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify numlist bullist formatpainter removeformat charmap emoticons | preview template link anchor table toolbarImageUrlButton fullscreen code",
    // contextmenu: "link image imagetools table",
    toolbar_items_size: "small",
    image_caption: true,
    imagetools_toolbar: "",
    toolbar_mode: "wrap",
    font_formats:
      "微软雅黑='Microsoft YaHei';黑体=黑体;Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Georgia=georgia,palatino;Helvetica=helvetica;Times New Roman=times new roman,times;Verdana=verdana,geneva",
    fontsize_formats: "10px 12px 14px 16px 18px 24px 36px 48px 56px 72px 96px",
    lineheight_formats: "1 1.1 1.2 1.3 1.4 1.5 1.7 2.4 3 4",
    setup: function (ed) {
      ed.ui.registry.addButton("toolbarImageButton", {
        icon: "image",
        onAction: function () {
          inno.fileManagerIframe(
            (file) => {
              if (file.url) {
                ed.insertContent(
                  '<img src="' + file.url + '" class="img-fluid" />'
                );
              }
            },
            {
              type: "image",
              multiple: false,
            }
          );
        },
      });
      ed.on("input", function () {
        tinymce.triggerSave();
        // console.log('Current content:', ed.getContent());
      });
    },
  });
};
