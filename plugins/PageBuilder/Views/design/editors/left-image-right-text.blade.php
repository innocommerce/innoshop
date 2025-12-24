{{-- 左右图文编辑模块 - 现代化UI风格 --}}
<template id="module-editor-left-image-right-text-template">
  <div class="left-image-right-text-editor">
    <div class="top-spacing"></div>
    
    {{-- 模块宽度设置 --}}
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

    {{-- 基础设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-setting"></i>
        @{{ lang.basic_settings }}
      </div>
      <div class="section-content">
        <div class="setting-group">
          <div class="setting-label">@{{ lang.image_position }}</div>
          <div class="option-buttons">
            <div 
              :class="['option-btn', { active: form.image_position === 'left' }]" 
              @click="form.image_position = 'left'"
            >
              <div class="preview-container">
                <div class="preview-image"></div>
                <div class="preview-text"></div>
              </div>
              <span>@{{ lang.left_image_right_text }}</span>
            </div>
            <div 
              :class="['option-btn', { active: form.image_position === 'right' }]" 
              @click="form.image_position = 'right'"
            >
              <div class="preview-container">
                <div class="preview-text"></div>
                <div class="preview-image"></div>
              </div>
              <span>@{{ lang.right_image_left_text }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- 内容设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-edit"></i>
        @{{ lang.content_settings }}
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
        
        <div class="setting-group">
          <div class="setting-label">@{{ lang.description_content }}</div>
          <text-i18n v-model="form.description" :placeholder="lang.enter_description"></text-i18n>
        </div>
        
        <div class="setting-group">
          <div class="setting-label">@{{ lang.text_alignment }}</div>
          <div class="option-buttons">
            <div 
              :class="['option-btn', { active: form.text_align === 'left' }]" 
              @click="form.text_align = 'left'"
            >
              <i class="el-icon-s-fold"></i>
              <span>@{{ lang.align_left }}</span>
            </div>
            <div 
              :class="['option-btn', { active: form.text_align === 'center' }]" 
              @click="form.text_align = 'center'"
            >
              <i class="el-icon-s-operation"></i>
              <span>@{{ lang.align_center }}</span>
            </div>
            <div 
              :class="['option-btn', { active: form.text_align === 'end' }]" 
              @click="form.text_align = 'end'"
            >
              <i class="el-icon-s-unfold"></i>
              <span>@{{ lang.align_right }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- 边距设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-position"></i>
        @{{ lang.margin_settings }}
      </div>
      <div class="section-content">
        <div class="setting-group">
          <div class="setting-label">@{{ lang.overall_margin }}</div>
          <div class="control-group">
            <div class="control-row">
              <div class="control-item">
                <div class="control-label">@{{ lang.left_margin }}</div>
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
                <div class="control-label">@{{ lang.right_margin }}</div>
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
                <div class="control-label">@{{ lang.top_margin }}</div>
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
                <div class="control-label">@{{ lang.bottom_margin }}</div>
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
        @{{ lang.content_spacing }}
      </div>
      <div class="section-content">
        <div class="setting-group">
          <div class="control-group">
            <div class="control-item">
              <div class="control-label">@{{ lang.title_spacing }}</div>
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
              <div class="control-label">@{{ lang.subtitle_spacing }}</div>
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
              <div class="control-label">@{{ lang.description_spacing }}</div>
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
        @{{ lang.image_settings }}
      </div>
      <div class="section-content">
        <div class="setting-group">
          <div class="setting-label">@{{ lang.image_selection }}</div>
          <single-image-selector 
            v-model="form.image" 
            :aspectRatio="16 / 9" 
            :targetWidth="800"
            :targetHeight="450"
          ></single-image-selector>
          <div class="setting-tip">
            <i class="el-icon-info"></i>
            @{{ lang.recommended_size_800_450 }}
          </div>
        </div>
        
        <div class="setting-group">
          <div class="setting-label">@{{ lang.image_padding }}</div>
          <div class="control-group">
            <div class="control-row">
              <div class="control-item">
                <div class="control-label">@{{ lang.horizontal_padding }}</div>
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
                <div class="control-label">@{{ lang.vertical_padding }}</div>
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
        @{{ lang.button_settings }}
      </div>
      <div class="section-content">
        <div class="setting-group">
          <div class="setting-label">@{{ lang.button_text }}</div>
          <text-i18n v-model="form.button_text" :placeholder="lang.enter_button_text_placeholder"></text-i18n>
        </div>
        
        <div class="setting-group">
          <div class="setting-label">@{{ lang.button_link }}</div>
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
