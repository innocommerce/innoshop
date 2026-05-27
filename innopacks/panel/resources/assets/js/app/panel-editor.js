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

function addCharCounter(ed) {
  ed.on('init', function() {
    var original = ed.getElement();
    var max = parseInt(original.getAttribute('data-maxlength') || '0', 10);
    var container = ed.getContainer();
    if (!container) return;

    var statusbar = container.querySelector('.tox-statusbar');
    if (!statusbar) return;

    // Clean up stray ">" text nodes from statusbar
    var children = statusbar.childNodes;
    for (var i = children.length - 1; i >= 0; i--) {
      if (children[i].nodeType === 3 && children[i].textContent.trim() === '>') {
        statusbar.removeChild(children[i]);
      }
    }

    // Also check inside text-container for ">" text nodes
    var textContainer = statusbar.querySelector('.tox-statusbar__text-container');
    if (textContainer) {
      var tc = textContainer.childNodes;
      for (var j = tc.length - 1; j >= 0; j--) {
        if (tc[j].nodeType === 3 && tc[j].textContent.trim() === '>') {
          textContainer.removeChild(tc[j]);
        }
      }
    }

    var resizeHandle = statusbar.querySelector('.tox-statusbar__resize-handle');
    var counter = document.createElement('span');
    counter.className = 'tox-statusbar__char-count';
    counter.style.cssText = 'margin-left:8px;font-size:12px;white-space:nowrap;color:#666;';
    counter.textContent = '0' + (max ? '/' + max : ' chars');

    if (resizeHandle) {
      statusbar.insertBefore(counter, resizeHandle);
    } else {
      statusbar.appendChild(counter);
    }

    function update() {
      var text = ed.getContent({ format: 'text' }) || '';
      var len = text.length;
      counter.textContent = len + (max ? '/' + max : ' chars');
      if (max) {
        counter.style.color = len >= max ? '#dc3545' : (len > max * 0.9 ? '#e67e22' : '#666');
      }
    }

    ed.on('input keyup change setcontent paste', update);
    update();
  });
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
    setup: function(ed) {
      setupEditor(ed);
      addCharCounter(ed);
    },
  });
}
