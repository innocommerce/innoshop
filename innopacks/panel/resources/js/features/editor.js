import Config from "../core/config";
import FileManagerIframe from "./file-manager-iframe";
import Api from "../core/api";

/**
 * Editor feature.
 *
 * Initializes and configures the TinyMCE editor.
 */
const Editor = {
  /**
   * Initializes the TinyMCE editor on all .tinymce elements.
   */
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

  /**
   * Sets up custom editor buttons and event handlers.
   * @param {object} ed - The editor instance.
   */
  setupEditor: (ed) => {
    ed.ui.registry.addButton("toolbarImageButton", {
      icon: "image",
      onAction: () => {
        FileManagerIframe.init(
          (files) => {
            // Support batch image insertion
            if (Array.isArray(files)) {
              // Insert multiple images in batch
              files.forEach(file => {
                const imageUrl = file.origin_url || file.url;
                if (imageUrl) {
                  ed.insertContent(`<img src="${imageUrl}" class="img-fluid" alt="${file.name || ''}" />`);
                }
              });
            } else {
              // Insert single image
              const imageUrl = files.origin_url || files.url;
              if (imageUrl) {
                ed.insertContent(`<img src="${imageUrl}" class="img-fluid" alt="${files.name || ''}" />`);
              }
            }
          },
          { type: "image", multiple: true }
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

        Api.post("api/panel/file_manager/upload", formData)
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
          });

        break;
      }
    });

    ed.on("input", () => {
      tinymce.triggerSave();
    });
  }
};

export default Editor;