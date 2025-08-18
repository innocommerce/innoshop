<!-- 富文本编辑器模块模板 -->
<template id="module-editor-rich-text-template">
  <div class="rich-text-editor editor-container">
    <div class="top-spacing"></div>
    
    {{-- 模块宽度设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-monitor"></i>
        模块宽度
      </div>
      <div class="section-content">
        <div class="segmented-buttons">
          <div 
            :class="['segmented-btn', { active: form.width === 'narrow' }]" 
            @click="form.width = 'narrow'"
          >
            窄屏
          </div>
          <div 
            :class="['segmented-btn', { active: form.width === 'wide' }]" 
            @click="form.width = 'wide'"
          >
            宽屏
          </div>
          <div 
            :class="['segmented-btn', { active: form.width === 'full' }]" 
            @click="form.width = 'full'"
          >
            全屏
          </div>
        </div>
      </div>
    </div>

    {{-- 基础设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-setting"></i>
        基础设置
      </div>
      <div class="section-content">
        <div class="setting-group">
          <div class="setting-label">模块标题</div>
          <text-i18n v-model="form.title" placeholder="请输入模块标题"></text-i18n>
        </div>
        
        <div class="setting-group">
          <div class="setting-label">副标题</div>
          <text-i18n v-model="form.subtitle" placeholder="请输入副标题"></text-i18n>
        </div>
      </div>
    </div>

    {{-- 内容设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-edit"></i>
        内容设置
      </div>
      <div class="section-content">
        <div class="setting-group">
          <div class="setting-label">富文本内容</div>
          
          {{-- 内容编辑区域 --}}
          <div class="content-editor">
            <div class="editor-preview">
              <div class="preview-header">
                <div class="preview-title">
                  <i class="el-icon-document"></i>
                  <span>富文本内容</span>
                </div>
                <div class="preview-actions">
                  <button class="edit-btn" @click="openFloatingEditor" title="编辑内容">
                    <i class="el-icon-edit"></i>
                    <span>编辑内容</span>
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
                    <h4>暂无内容</h4>
                    <p>点击"编辑内容"开始编写富文本</p>
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
                  <span>已设置内容</span>
                </div>
                <div class="content-status" v-else>
                  <i class="el-icon-warning"></i>
                  <span>未设置内容</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<!-- 富文本编辑器模块脚本 -->
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
        
        // 移除HTML标签，只保留文本内容
        let text = content.replace(/<[^>]*>/g, '');
        text = text.replace(/&nbsp;/g, ' ');
        text = text.trim();
        
        // 限制预览长度，确保只显示一行
        if (text.length > 60) {
          text = text.substring(0, 60) + '...';
        }
        
        return text || '富文本内容';
      },
      openFloatingEditor() {
        const self = this;
        // 防止重复弹出
        if (document.getElementById('floatingEditorModal')) return;

        // 创建悬浮编辑器容器
        const editorContainer = document.createElement('div');
        editorContainer.id = 'floatingEditorModal';
        editorContainer.className = 'floating-editor-modal';
        editorContainer.innerHTML = `
          <div class="floating-editor-modal-bg"></div>
          <div class="floating-editor-modal-content">
            <div class="floating-editor-header">
              <div class="d-flex align-items-center">
                <span class="me-3">富文本编辑器</span>
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
                  <span>保存内容</span>
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

        // 使用系统后台的编辑器配置
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
          font_formats: "微软雅黑='Microsoft YaHei';黑体=黑体;Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Georgia=georgia,palatino;Helvetica=helvetica;Times New Roman=times new roman,times;Verdana=verdana,geneva",
          fontsize_formats: "10px 12px 14px 16px 18px 24px 36px 48px 56px 72px 96px",
          lineheight_formats: "1 1.1 1.2 1.3 1.4 1.5 1.7 2.4 3 4",
          setup: function(editor) {
            self.floatingEditor = editor;
            
            // 添加图片按钮
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
                  layer.msg('文件管理器未加载，请刷新页面重试', { icon: 2 });
                }
              },
            });

            // 添加URL图片按钮
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
                  layer.msg('文件管理器未加载，请刷新页面重试', { icon: 2 });
                }
              },
            });

            // 移除实时内容更新，只在保存时更新

            // 粘贴图片处理
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

        // 确保TinyMCE已加载
        if (typeof tinymce === "undefined") {
          layer.msg('编辑器加载失败，请刷新页面重试', { icon: 2 });
          return;
        }

        // 初始化编辑器
        tinymce.init(editorConfig);

        // 关闭事件
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

        // 保存按钮事件
        const saveBtn = editorContainer.querySelector('#saveFloatingContent');
        saveBtn.onclick = function() {
          if (self.floatingEditor) {
            self.form.content[self.currentLanguage] = self.floatingEditor.getContent();
            self.$emit('on-changed', JSON.parse(JSON.stringify(self.form)));
            
            // 更新按钮状态
            saveBtn.classList.add('saved');
            saveBtn.innerHTML = '<i class="el-icon-check"></i><span>已保存</span>';
            
            setTimeout(() => {
              saveBtn.classList.remove('saved');
              saveBtn.innerHTML = '<i class="el-icon-check"></i><span>保存内容</span>';
            }, 1200);
          }
        };

        // 多语言切换
        editorContainer.querySelectorAll('.nav-link[data-lang]').forEach(tab => {
          tab.onclick = function(e) {
            e.preventDefault();
            const lang = this.getAttribute('data-lang');
            // 切换语言
            self.currentLanguage = lang;
            // 切换tab激活样式
            editorContainer.querySelectorAll('.nav-link[data-lang]').forEach(t => t.classList.remove(
              'active'));
            this.classList.add('active');
            // 切换内容
            setTimeout(() => {
              self.floatingEditor.setContent(self.form.content[lang] || '');
            }, 100);
          };
        });
      }
    },
    mounted: function() {
      // 初始化form数据
      if (this.module) {
        this.form = JSON.parse(JSON.stringify(this.module));
      }
      
      // 确保每个语言都有初始值
      this.languages.forEach(lang => {
        if (!this.form.content[lang.code]) {
          this.$set(this.form.content, lang.code, '');
        }
      });
      
      // 确保width有默认值
      if (!this.form.width) {
        this.$set(this.form, 'width', 'wide');
      }
      
      // 确保title和subtitle有默认值
      if (!this.form.title) {
        this.$set(this.form, 'title', {});
      }
      if (!this.form.subtitle) {
        this.$set(this.form, 'subtitle', {});
      }
      
      // 确保当前语言有值
      if (!this.currentLanguage) {
        this.currentLanguage = $locale || 'zh-cn';
      }
    },
    beforeDestroy() {
      // 清理编辑器实例
      if (this.floatingEditor) {
        this.floatingEditor.destroy();
      }
    }
  });
</script>

<style>
/* 富文本编辑器特定样式 - 只保留真正特定的样式 */
.rich-text-editor {
  /* 继承基础编辑器样式，无需重复定义 */
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

/* TinyMCE 对话框样式优化 - 使用系统后台样式 */
.tox-tinymce-aux {
  z-index: 3000 !important;
}

/* 悬浮模态样式 */
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
