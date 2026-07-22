/**
 * TinyMCE setup for panel (rich text + file manager image insertion).
 */
import { getPanelConfig } from './panel-config';
import fileManager from './panel-file-manager';
import aiModal from './ai-modal';

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
  // AI generate toolbar button (only shown for editors with data-column)
  ed.ui.registry.addIcon('ai', '<svg width="24" height="24" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M7.657 6.247c.11-.33.576-.33.686 0l.645 1.937a2.89 2.89 0 0 0 1.829 1.828l1.936.645c.33.11.33.576 0 .686l-1.937.645a2.89 2.89 0 0 0-1.828 1.829l-.645 1.936a.361.361 0 0 1-.686 0l-.645-1.937a2.89 2.89 0 0 0-1.829-1.828l-1.936-.645a.361.361 0 0 1 0-.686l1.937-.645a2.89 2.89 0 0 0 1.828-1.829l.645-1.936z"/><path d="M3.794 1.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387A1.734 1.734 0 0 0 4.593 5.69l-.387 1.162a.217.217 0 0 1-.412 0L3.407 5.69A1.734 1.734 0 0 0 2.31 4.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387A1.734 1.734 0 0 0 3.407 2.31l.387-1.162zM10.97.278a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 3.407l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387A1.734 1.734 0 0 0 9.31 1.31l.387-1.162z"/></svg>');
  ed.ui.registry.addButton('aiGenerateButton', {
    icon: 'ai',
    tooltip: 'AI Generate',
    onAction: () => {
      const el = ed.getElement();
      const column = el.getAttribute('data-column') || '';
      if (!column) return;

      const editorId = ed.id;
      const m = editorId.match(/^content-(.+)$/);
      const sourceLocale = m ? m[1] : '';

      aiModal.openFromState({
        column,
        field: '',
        entityType: el.getAttribute('data-entity-type') || '',
        entityId: parseInt(el.getAttribute('data-entity-id'), 10) || 0,
        isRichText: true,
        isMultilingual: !['product_slug', 'article_slug'].includes(column),
        editorId,
        sourceLocale,
      });
    },
    onSetup: (api) => {
      const hasColumn = !!(ed.getElement() && ed.getElement().getAttribute('data-column'));
      api.setDisabled(!hasColumn);
    },
  });

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
      'undo redo | toolbarImageButton | lineheight | bold italic underline strikethrough | forecolor backcolor | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify numlist bullist formatpainter removeformat charmap emoticons | preview template link anchor table toolbarImageUrlButton fullscreen code | aiGenerateButton',
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
