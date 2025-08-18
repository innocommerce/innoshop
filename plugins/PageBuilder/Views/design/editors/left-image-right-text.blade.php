{{-- 左右图文编辑模块 - 现代化UI风格 --}}
<template id="module-editor-left-image-right-text-template">
  <div class="left-image-right-text-editor">
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
          <div class="setting-label">图片位置</div>
          <div class="option-buttons">
            <div 
              :class="['option-btn', { active: form.image_position === 'left' }]" 
              @click="form.image_position = 'left'"
            >
              <div class="preview-container">
                <div class="preview-image"></div>
                <div class="preview-text"></div>
              </div>
              <span>左图右文</span>
            </div>
            <div 
              :class="['option-btn', { active: form.image_position === 'right' }]" 
              @click="form.image_position = 'right'"
            >
              <div class="preview-container">
                <div class="preview-text"></div>
                <div class="preview-image"></div>
              </div>
              <span>右图左文</span>
            </div>
          </div>
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
          <div class="setting-label">模块标题</div>
          <text-i18n v-model="form.title" placeholder="请输入模块标题"></text-i18n>
        </div>
        
        <div class="setting-group">
          <div class="setting-label">副标题</div>
          <text-i18n v-model="form.subtitle" placeholder="请输入副标题"></text-i18n>
        </div>
        
        <div class="setting-group">
          <div class="setting-label">描述内容</div>
          <text-i18n v-model="form.description" placeholder="请输入描述内容"></text-i18n>
        </div>
        
        <div class="setting-group">
          <div class="setting-label">文字对齐方式</div>
          <div class="option-buttons">
            <div 
              :class="['option-btn', { active: form.text_align === 'left' }]" 
              @click="form.text_align = 'left'"
            >
              <i class="el-icon-s-fold"></i>
              <span>居左</span>
            </div>
            <div 
              :class="['option-btn', { active: form.text_align === 'center' }]" 
              @click="form.text_align = 'center'"
            >
              <i class="el-icon-s-operation"></i>
              <span>居中</span>
            </div>
            <div 
              :class="['option-btn', { active: form.text_align === 'end' }]" 
              @click="form.text_align = 'end'"
            >
              <i class="el-icon-s-unfold"></i>
              <span>居右</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- 边距设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-position"></i>
        边距设置
      </div>
      <div class="section-content">
        <div class="setting-group">
          <div class="setting-label">整体边距</div>
          <div class="control-group">
            <div class="control-row">
              <div class="control-item">
                <div class="control-label">左边距</div>
                <el-input-number 
                  v-model="form.content_margin_left" 
                  :min="0" 
                  :max="100"
                  size="small"
                  controls-position="right"
                ></el-input-number>
                <span class="control-unit">px</span>
              </div>
              <div class="control-item">
                <div class="control-label">右边距</div>
                <el-input-number 
                  v-model="form.content_margin_right" 
                  :min="0" 
                  :max="100"
                  size="small"
                  controls-position="right"
                ></el-input-number>
                <span class="control-unit">px</span>
              </div>
            </div>
            <div class="control-row">
              <div class="control-item">
                <div class="control-label">上边距</div>
                <el-input-number 
                  v-model="form.content_margin_top" 
                  :min="0" 
                  :max="100"
                  size="small"
                  controls-position="right"
                ></el-input-number>
                <span class="control-unit">px</span>
              </div>
              <div class="control-item">
                <div class="control-label">下边距</div>
                <el-input-number 
                  v-model="form.content_margin_bottom" 
                  :min="0" 
                  :max="100"
                  size="small"
                  controls-position="right"
                ></el-input-number>
                <span class="control-unit">px</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- 内容间距 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-s-grid"></i>
        内容间距
      </div>
      <div class="section-content">
        <div class="setting-group">
          <div class="control-group">
            <div class="control-item">
              <div class="control-label">标题间距</div>
              <el-input-number 
                v-model="form.title_spacing" 
                :min="0" 
                :max="50"
                size="small"
                controls-position="right"
              ></el-input-number>
              <span class="control-unit">px</span>
            </div>
            <div class="control-item">
              <div class="control-label">副标题间距</div>
              <el-input-number 
                v-model="form.subtitle_spacing" 
                :min="0" 
                :max="50"
                size="small"
                controls-position="right"
              ></el-input-number>
              <span class="control-unit">px</span>
            </div>
            <div class="control-item">
              <div class="control-label">描述间距</div>
              <el-input-number 
                v-model="form.description_spacing" 
                :min="0" 
                :max="50"
                size="small"
                controls-position="right"
              ></el-input-number>
              <span class="control-unit">px</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- 图片设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-picture"></i>
        图片设置
      </div>
      <div class="section-content">
        <div class="setting-group">
          <div class="setting-label">图片选择</div>
          <single-image-selector 
            v-model="form.image" 
            :aspectRatio="16 / 9" 
            :targetWidth="800"
            :targetHeight="450"
          ></single-image-selector>
          <div class="setting-tip">
            <i class="el-icon-info"></i>
            建议尺寸: 800 x 450，图片比例16:9
          </div>
        </div>
        
        <div class="setting-group">
          <div class="setting-label">图片内边距</div>
          <div class="control-group">
            <div class="control-row">
              <div class="control-item">
                <div class="control-label">左右内边距</div>
                <el-input-number 
                  v-model="form.image_padding_x" 
                  :min="0" 
                  :max="100"
                  size="small"
                  controls-position="right"
                ></el-input-number>
                <span class="control-unit">px</span>
              </div>
              <div class="control-item">
                <div class="control-label">上下内边距</div>
                <el-input-number 
                  v-model="form.image_padding_y" 
                  :min="0" 
                  :max="100"
                  size="small"
                  controls-position="right"
                ></el-input-number>
                <span class="control-unit">px</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- 按钮设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-link"></i>
        按钮设置
      </div>
      <div class="section-content">
        <div class="setting-group">
          <div class="setting-label">按钮文字</div>
          <text-i18n v-model="form.button_text" placeholder="请输入按钮文字"></text-i18n>
        </div>
        
        <div class="setting-group">
          <div class="setting-label">按钮链接</div>
          <link-selector 
            :hide-types="['catalog', 'static']" 
            v-model="form.link"
          ></link-selector>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
/* 左右图文编辑器特定样式 - 只保留真正特定的样式 */
.left-image-right-text-editor {
  padding: 0;
  background: #fff;
}

/* 布局选项特定样式 - 恢复预览效果 */
.option-btn .preview-container {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
  height: 24px;
  width: 100%;
}

.option-btn .preview-image {
  width: 16px;
  height: 16px;
  background: #667eea;
  border-radius: 2px;
  flex-shrink: 0;
}

.option-btn .preview-text {
  flex: 1;
  height: 8px;
  background: #dee2e6;
  border-radius: 4px;
}
</style>

<script type="text/javascript">
  Vue.component('module-editor-left-image-right-text', {
    template: '#module-editor-left-image-right-text-template',
    props: ['module'],
    data: function() {
      return {
        form: {
          image_position: 'left',
          title: '',
          subtitle: '',
          description: '',
          image: '',
          button_text: '',
          text_align: 'left',
          width: 'wide',
          content_margin_left: 0,
          content_margin_right: 0,
          content_margin_top: 0,
          content_margin_bottom: 0,
          title_spacing: 20,
          subtitle_spacing: 15,
          description_spacing: 20,
          image_padding_x: 0,
          image_padding_y: 0,
          link: {
            type: 'category',
            value: '',
            new_window: true
          }
        },
        source: {
          locale: $locale
        }
      }
    },
    created() {
      if (this.module && Object.keys(this.module).length) {
        this.form = Object.assign({}, this.form, this.module);
      }
      
      // 确保width有默认值
      if (!this.form.width) {
        this.$set(this.form, 'width', 'wide');
      }
    },
    watch: {
      form: {
        handler: function(val) {
          this.$emit('on-changed', val);
        },
        deep: true
      }
    }
  });
</script>
