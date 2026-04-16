/**
 * TinyMCE setup for panel (rich text + file manager image insertion).
 */
import { getPanelConfig } from './panel-config';
import fileManager from './panel-file-manager';

function isVideoFile(file) {
  const url = (file.origin_url || file.url || '').toLowerCase();
  const videoExts = ['.mp4', '.webm', '.ogg', '.mov'];
  return videoExts.some((ext) => url.endsWith(ext));
}

function insertMediaFile(ed, file) {
  const mediaUrl = file.origin_url || file.url;
  if (!mediaUrl) return;

  if (isVideoFile(file)) {
    ed.insertContent(
      `<video controls class="img-fluid" style="max-width:100%;" src="${mediaUrl}">` +
        `<source src="${mediaUrl}" type="video/mp4" />` +
        `</video>`,
    );
  } else {
    ed.insertContent(`<img src="${mediaUrl}" class="img-fluid" alt="${file.name || ''}" />`);
  }
}

function setupEditor(ed) {
  ed.ui.registry.addButton('toolbarImageButton', {
    icon: 'image',
    onAction: () => {
      fileManager.init(
        (files) => {
          if (Array.isArray(files)) {
            files.forEach((file) => {
              insertMediaFile(ed, file);
            });
          } else {
            insertMediaFile(ed, files);
          }
        },
        { type: 'image', multiple: true },
      );
    },
  });

  ed.on('paste', (e) => {
    const clipboardData = e.clipboardData;
    if (!clipboardData || !clipboardData.items) {
      return;
    }

    for (let i = 0; i < clipboardData.items.length; i++) {
      const item = clipboardData.items[i];
      if (item.type.indexOf('image') === -1) {
        continue;
      }

      e.preventDefault();
      const file = item.getAsFile();
      const formData = new FormData();
      formData.append('file', file);
      formData.append('path', '/');
      formData.append('type', 'images');

      layer.load(2, { shade: [0.3, '#fff'] });

      axios
        .post(`${urls.panel_api}/file_manager/upload`, formData)
        .then((response) => {
          if (response.data.url) {
            ed.insertContent(`<img src="${response.data.url}" class="img-fluid" />`);
          } else {
            throw new Error('Upload response missing URL');
          }
        })
        .catch((error) => {
          const errorMessage =
            error.response?.data?.message || error.response?.data?.error || error.message;
          layer.msg(errorMessage, { icon: 2 });
        })
        .finally(() => {
          layer.closeAll('loading');
        });

      break;
    }
  });

  ed.on('input', () => {
    tinymce.triggerSave();
  });
}

export function initEditor() {
  if (typeof tinymce === 'undefined') {
    return;
  }

  const cfg = getPanelConfig();

  tinymce.init({
    selector: '.tinymce',
    language: cfg.editorLanguage,
    branding: false,
    height: 400,
    convert_urls: false,
    inline: false,
    relative_urls: false,
    plugins: 'link lists fullscreen table hr wordcount image imagetools code',
    menubar: '',
    toolbar:
      'undo redo | toolbarImageButton | lineheight | bold italic underline strikethrough | forecolor backcolor | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify numlist bullist formatpainter removeformat charmap emoticons | preview template link anchor table toolbarImageUrlButton fullscreen code',
    toolbar_items_size: 'small',
    image_caption: true,
    imagetools_toolbar: '',
    toolbar_mode: 'wrap',
    font_formats:
      "微软雅黑='Microsoft YaHei';黑体=黑体;Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Georgia=georgia,palatino;Helvetica=helvetica;Times New Roman=times new roman,times;Verdana=verdana,geneva",
    fontsize_formats: '10px 12px 14px 16px 18px 24px 36px 48px 56px 72px 96px',
    lineheight_formats: '1 1.1 1.2 1.3 1.4 1.5 1.7 2.4 3 4',
    setup: setupEditor,
  });
}
