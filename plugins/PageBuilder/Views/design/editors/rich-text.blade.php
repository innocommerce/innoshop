<template id="module-editor-rich-text-template">
  <div class="rich-text-editor editor-container">
    <div class="top-spacing"></div>
    
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-monitor"></i>
        @{{ lang.module_width }}
      </div>
      <div class="section-content">
        <div class="segmented-buttons">
          <div 
            :class="['segmented-btn', { active: form.width === 'narrow' }]" 
            @click="form.width = 'narrow'"
          >
            @{{ lang.narrow_screen }}
          </div>
          <div 
            :class="['segmented-btn', { active: form.width === 'wide' }]" 
            @click="form.width = 'wide'"
          >
            @{{ lang.wide_screen }}
          </div>
          <div 
            :class="['segmented-btn', { active: form.width === 'full' }]" 
            @click="form.width = 'full'"
          >
            @{{ lang.full_screen }}
          </div>
        </div>
      </div>
    </div>

    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-setting"></i>
        @{{ lang.basic_settings }}
      </div>
      <div class="section-content">
        <div class="setting-group">
          <div class="setting-label">@{{ lang.module_title }}</div>
          <text-i18n v-model="form.title" :placeholder="lang.enter_module_title"></text-i18n>
        </div>
        
        <div class="setting-group">
          <div class="setting-label">@{{ lang.subtitle }}</div>
          <text-i18n v-model="form.subtitle" :placeholder="lang.enter_subtitle"></text-i18n>
        </div>
      </div>
    </div>

    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-edit"></i>
        @{{ lang.content_settings }}
      </div>
      <div class="section-content">
        <div class="setting-group">
          <div class="setting-label">@{{ lang.rich_text_content }}</div>
          
          <div class="content-editor">
            <div class="editor-preview">
              <div class="preview-header">
                <div class="preview-title">
                  <i class="el-icon-document"></i>
                  <span>@{{ lang.rich_text_content }}</span>
                </div>
                <div class="preview-actions">
                  <button class="edit-btn" @click="openFloatingEditor" :title="lang.edit_content">
                    <i class="el-icon-edit"></i>
                    <span>@{{ lang.edit_content }}</span>
                  </button>
                </div>
              </div>
              
              <div class="preview-content">
                <div v-if="form.content[currentLanguage] && form.content[currentLanguage].trim()" 
                  class="content-preview" v-html="getContentPreview(form.content[currentLanguage])"></div>
                <div v-else class="content-placeholder">
                  <div class="placeholder-icon">
                    <i class="el-icon-edit-outline"></i>
                  </div>
                  <div class="placeholder-text">
                    <h4>@{{ lang.no_content_click_edit }}</h4>
                    <p>@{{ lang.click_edit_start }}</p>
                  </div>
                </div>
              </div>
              
              <div class="preview-footer">
                <div class="language-indicator">
                  <img :src="'/images/flag/' + currentLanguage + '.png'" class="flag-icon" :alt="getLanguageName(currentLanguage)">
                  <span>@{{ getLanguageName(currentLanguage) }}</span>
                </div>
                <div class="content-status" v-if="form.content[currentLanguage] && form.content[currentLanguage].trim()">
                  <i class="el-icon-check"></i>
                  <span>@{{ lang.content_set }}</span>
                </div>
                <div class="content-status" v-else>
                  <i class="el-icon-warning"></i>
                  <span>@{{ lang.content_not_set }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script type="text/javascript">
  Vue.component('module-editor-rich-text', {
    template: '#module-editor-rich-text-template',
    props: ['module'],
    data: function() {
      return {
        form: {
          title: {},
          subtitle: {},
          content: {},
          width: 'wide',
          style: {
            background_color: ''
          }
        },
        languages: $languages,
        currentLanguage: $locale || 'zh-cn',
        floatingEditor: null
      }
    },
    watch: {
      form: {
        handler: function(val) {
          this.$emit('on-changed', JSON.parse(JSON.stringify(val)));
        },
        deep: true
      }
    },
    methods: {
      switchLanguage(lang) {
        this.currentLanguage = lang;
      },
      getLanguageName(code) {
        const lang = this.languages.find(l => l.code === code);
        return lang ? lang.name : code;
      },
      getContentPreview(content) {
        if (!content) return '';
        
        let text = content.replace(/<[^>]*>/g, '');
        text = text.replace(/&nbsp;/g, ' ');
        text = text.trim();
        
        if (text.length > 60) {
          text = text.substring(0, 60) + '...';
        }
        
        return text || lang.rich_text_content;
      },
      openFloatingEditor() {
        const self = this;
        if (document.getElementById('floatingEditorModal')) return;

        const editorContainer = document.createElement('div');
        editorContainer.id = 'floatingEditorModal';
        editorContainer.className = 'floating-editor-modal';
        editorContainer.innerHTML = `
          <div class="floating-editor-modal-bg"></div>
          <div class="floating-editor-modal-content">
            <div class="floating-editor-header">
              <div class="d-flex align-items-center">
                <span class="me-3">${lang.rich_text_editor}</span>
                <ul class="nav nav-tabs" role="tablist" style="margin-bottom:0;">
                  ${this.languages.map(lang => `
                    <li class="nav-item">
                      <button class="nav-link${lang.code === this.currentLanguage ? ' active' : ''}" data-lang="${lang.code}">${lang.name}</button>
                    </li>
                  `).join('')}
                </ul>
              </div>
              <div class="floating-editor-actions">
                <button class="floating-save-btn" id="saveFloatingContent">
                  <i class="el-icon-check"></i>
                  <span>${lang.save_content}</span>
                </button>
                <span class="floating-editor-close" id="closeFloatingEditor">×</span>
              </div>
            </div>
            <div class="floating-editor-body">
              <textarea id="floating-tinymce" class="tinymce"></textarea>
            </div>
          </div>
        `;
        document.body.appendChild(editorContainer);

        const editorConfig = {
          selector: '#floating-tinymce',
          height: '100%',
          language: this.currentLanguage === 'zh_cn' ? 'zh_CN' : 'en',
          branding: false,
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
          font_formats: "Microsoft YaHei='Microsoft YaHei';SimHei=SimHei;Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Georgia=georgia,palatino;Helvetica=helvetica;Times New Roman=times new roman,times;Verdana=verdana,geneva",
          fontsize_formats: "10px 12px 14px 16px 18px 24px 36px 48px 56px 72px 96px",
          lineheight_formats: "1 1.1 1.2 1.3 1.4 1.5 1.7 2.4 3 4",
          setup: function(editor) {
            self.floatingEditor = editor;
            
            editor.ui.registry.addButton("toolbarImageButton", {
              icon: "image",
              onAction: () => {
                if (window.inno && window.inno.fileManagerIframe) {
                  window.inno.fileManagerIframe(
                    (file) => {
                      const imageUrl = file.origin_url || file.url;
                      if (imageUrl) {
                        editor.insertContent(`<img src="${imageUrl}" class="img-fluid" alt="${file.name || ''}" />`);
                      }
                    },
                    { type: "image", multiple: false }
                  );
                } else {
                  layer.msg(lang.file_manager_not_loaded, { icon: 2 });
                }
              },
            });

            editor.ui.registry.addButton("toolbarImageUrlButton", {
              icon: "image",
              onAction: () => {
                if (window.inno && window.inno.fileManagerIframe) {
                  window.inno.fileManagerIframe(
                    (file) => {
                      const imageUrl = file.origin_url || file.url;
                      if (imageUrl) {
                        editor.insertContent(`<img src="${imageUrl}" class="img-fluid" alt="${file.name || ''}" />`);
                      }
                    },
                    { type: "image", multiple: false }
                  );
                } else {
                  layer.msg(lang.file_manager_not_loaded, { icon: 2 });
                }
              },
            });

            editor.on("paste", (e) => {
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
                      editor.insertContent(`<img src="${response.data.url}" class="img-fluid" />`);
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

            editor.on("input", () => {
              tinymce.triggerSave();
            });
          },
          init_instance_callback: function(editor) {
            editor.setContent(self.form.content[self.currentLanguage] || '');
          }
        };

        if (typeof tinymce === "undefined") {
          layer.msg(lang.editor_load_failed, { icon: 2 });
          return;
        }

        tinymce.init(editorConfig);

        editorContainer.querySelector('#closeFloatingEditor').onclick = function() {
          tinymce.get('floating-tinymce')?.destroy();
          editorContainer.remove();
          self.floatingEditor = null;
        };
        editorContainer.querySelector('.floating-editor-modal-bg').onclick = function() {
          tinymce.get('floating-tinymce')?.destroy();
          editorContainer.remove();
          self.floatingEditor = null;
        };

        const saveBtn = editorContainer.querySelector('#saveFloatingContent');
        saveBtn.onclick = function() {
          if (self.floatingEditor) {
            self.form.content[self.currentLanguage] = self.floatingEditor.getContent();
            self.$emit('on-changed', JSON.parse(JSON.stringify(self.form)));
            
            // 更新按钮状态
            saveBtn.classList.add('saved');
            saveBtn.innerHTML = '<i class="el-icon-check"></i><span>' + lang.content_saved + '</span>';
            
            setTimeout(() => {
              saveBtn.classList.remove('saved');
              saveBtn.innerHTML = '<i class="el-icon-check"></i><span>' + lang.save_content + '</span>';
            }, 1200);
          }
        };

        editorContainer.querySelectorAll('.nav-link[data-lang]').forEach(tab => {
          tab.onclick = function(e) {
            e.preventDefault();
            const lang = this.getAttribute('data-lang');
            self.currentLanguage = lang;
            editorContainer.querySelectorAll('.nav-link[data-lang]').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            setTimeout(() => {
              self.floatingEditor.setContent(self.form.content[lang] || '');
            }, 100);
          };
        });
      }
    },
    mounted: function() {
      if (this.module) {
        this.form = JSON.parse(JSON.stringify(this.module));
      }
      
      this.languages.forEach(lang => {
        if (!this.form.content[lang.code]) {
          this.$set(this.form.content, lang.code, '');
        }
      });
      
      if (!this.form.width) {
        this.$set(this.form, 'width', 'wide');
      }
      
      if (!this.form.title) {
        this.$set(this.form, 'title', {});
      }
      if (!this.form.subtitle) {
        this.$set(this.form, 'subtitle', {});
      }
      
      if (!this.currentLanguage) {
        this.currentLanguage = $locale || 'zh-cn';
      }
    },
    beforeDestroy() {
      if (this.floatingEditor) {
        this.floatingEditor.destroy();
      }
    }
  });
</script>

<style>
.rich-text-editor {
}

/* 内容编辑区域 */
.content-editor {
  border: 1px solid #e1e5e9;
  border-radius: 8px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.editor-preview {
  display: flex;
  flex-direction: column;
  height: 220px;
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f4 100%);
  border-bottom: 1px solid #e1e5e9;
}

.preview-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
  color: #495057;
}

.preview-title i {
  font-size: 16px;
  color: #667eea;
}

.preview-actions {
  display: flex;
  align-items: center;
}

.edit-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(102, 126, 234, 0.2);
}

.edit-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
}

.edit-btn i {
  font-size: 14px;
}

.preview-content {
  flex: 1;
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff;
  min-height: 0;
}

.content-preview {
  width: 100%;
  max-height: 80px;
  overflow: hidden;
  font-size: 13px;
  line-height: 1.5;
  color: #495057;
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  border-radius: 6px;
  padding: 10px;
  position: relative;
}

.content-preview::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 20px;
  background: linear-gradient(transparent, #f8f9fa);
  pointer-events: none;
}

.content-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  color: #6c757d;
  width: 100%;
}

.placeholder-icon {
  width: 48px;
  height: 48px;
  background: rgba(102, 126, 234, 0.1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 12px;
}

.placeholder-icon i {
  font-size: 24px;
  color: #667eea;
}

.placeholder-text h4 {
  margin: 0 0 4px 0;
  font-size: 14px;
  font-weight: 600;
  color: #495057;
}

.placeholder-text p {
  margin: 0;
  font-size: 12px;
  color: #6c757d;
}

.preview-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 20px;
  background: #f8f9fa;
  border-top: 1px solid #e1e5e9;
}

.language-indicator {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #6c757d;
}

.flag-icon {
  width: 16px;
  height: 16px;
  border-radius: 2px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.content-status {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  padding: 4px 8px;
  border-radius: 12px;
  font-weight: 500;
}

.content-status i {
  font-size: 12px;
}

.content-status:has(.el-icon-check) {
  background: rgba(40, 167, 69, 0.1);
  color: #28a745;
}

.content-status:has(.el-icon-warning) {
  background: rgba(255, 193, 7, 0.1);
  color: #856404;
}

.tox-tinymce-aux {
  z-index: 3000 !important;
}

.floating-editor-modal {
  position: fixed;
  left: 0;
  top: 0;
  right: 0;
  bottom: 0;
  z-index: 3000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.floating-editor-modal-bg {
  position: absolute;
  left: 0;
  top: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.25);
  z-index: 1;
}

.floating-editor-modal-content {
  position: relative;
  z-index: 2;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.18);
  width: 90vw;
  max-width: 1200px;
  height: 85vh;
  display: flex;
  flex-direction: column;
}

.floating-editor-header {
  padding: 16px 20px;
  border-bottom: 1px solid #e1e5e9;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8f9fa;
  border-radius: 8px 8px 0 0;
}

.floating-editor-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.floating-save-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.floating-save-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
  background: linear-gradient(135deg, #218838 0%, #1ea085 100%);
}

.floating-save-btn:active {
  transform: translateY(0);
  box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
}

.floating-save-btn i {
  font-size: 16px;
}

.floating-save-btn.saved {
  background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
  box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
}

.floating-save-btn.saved:hover {
  background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
  box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4);
}

.floating-editor-close {
  cursor: pointer;
  font-size: 20px;
  color: #6c757d;
  margin-left: 10px;
  transition: color 0.3s ease;
}

.floating-editor-close:hover {
  color: #dc3545;
}

.floating-editor-body {
  flex: 1;
  padding: 20px;
  overflow: hidden;
}

.floating-editor-modal .nav-tabs {
  margin-bottom: 0;
  border-bottom: none;
}

.floating-editor-modal .nav-link {
  padding: 8px 16px;
  cursor: pointer;
  border: none;
  background: transparent;
  color: #6c757d;
  transition: all 0.3s ease;
}

.floating-editor-modal .nav-link:hover {
  color: #667eea;
  background: rgba(102, 126, 234, 0.05);
}

.floating-editor-modal .nav-link.active {
  color: #667eea;
  background: rgba(102, 126, 234, 0.1);
  border-bottom: 2px solid #667eea;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .floating-editor-modal-content {
    width: 95vw;
    height: 90vh;
  }
  
  .floating-editor-header {
    padding: 12px 16px;
    flex-direction: column;
    gap: 12px;
  }
  
  .floating-editor-body {
    padding: 16px;
  }
  
  .preview-header {
    flex-direction: column;
    gap: 12px;
    align-items: stretch;
  }
  
  .preview-content {
    padding: 16px;
  }
  
  .edit-btn {
    justify-content: center;
    width: 100%;
  }
}
</style>
