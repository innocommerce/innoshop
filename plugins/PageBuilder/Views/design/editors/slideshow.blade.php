{{-- 幻灯片编辑模块 - 简洁版 --}}
<template id="module-editor-slideshow">
  <div class="slideshow-editor">
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
            :class="['segmented-btn', { active: module.width === 'narrow' }]" 
            @click="module.width = 'narrow'"
          >
            窄屏
          </div>
          <div 
            :class="['segmented-btn', { active: module.width === 'wide' }]" 
            @click="module.width = 'wide'"
          >
            宽屏
          </div>
          <div 
            :class="['segmented-btn', { active: module.width === 'full' }]" 
            @click="module.width = 'full'"
          >
            全屏
          </div>
        </div>
      </div>
    </div>

    {{-- 幻灯片内容 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-picture"></i>
        幻灯片管理
      </div>
      <div class="slideshow-list">
        <draggable
          ghost-class="dragabble-ghost"
          :list="module.images"
          :options="{animation: 330, handle: '.drag-handle'}"
        >
          <div class="slide-item" v-for="(item, index) in module.images" :key="index">
            {{-- 幻灯片头部 --}}
            <div class="slide-header" @click="toggleSlide(index)">
              <div class="slide-info">
                <div class="drag-handle">
                  <i class="el-icon-rank"></i>
                </div>
                <div class="slide-preview" @click.stop>
                  <img :src="thumbnail(item.image, 60, 40)" class="preview-img">
                  <div class="slide-number"># @{{ index + 1 }}</div>
                </div>
                <div class="slide-title">
                  <span v-if="getTitleText(item)">
                    @{{ getTitleText(item) }}
                  </span>
                  <span v-else>未设置标题</span>
                </div>
              </div>
              
              <div class="slide-actions">
                <el-button 
                  type="danger" 
                  size="mini" 
                  icon="el-icon-delete" 
                  circle
                  @click.stop="removeImage(index)"
                ></el-button>
                <i :class="'el-icon-arrow-' + (item.show ? 'up' : 'down') + ' toggle-icon'"></i>
              </div>
            </div>

            {{-- 幻灯片内容编辑 --}}
            <div :class="'slide-content ' + (item.show ? 'expanded' : '')">
              {{-- 图片设置 --}}
              <div class="content-section">
                <div class="section-subtitle">
                  <i class="el-icon-picture-outline"></i>
                  图片设置
                </div>
                <div class="image-selector-wrapper">
                  <single-image-selector v-model="item.image" @change="onChange"></single-image-selector>
                  <div class="image-tips">建议尺寸(宽x高): 1920 x 600</div>
                </div>
              </div>

              {{-- 链接设置 --}}
              <div class="content-section">
                <div class="section-subtitle">
                  <i class="el-icon-link"></i>
                  链接设置
                </div>
                <link-selector v-model="item.link" @change="onChange" ></link-selector>
              </div>

              {{-- 标题设置 --}}
              <div class="content-section">
                <div class="section-subtitle">
                  <i class="el-icon-edit"></i>
                  标题设置
                </div>
                <text-i18n v-model="item.title" @change="onChange" placeholder="请输入标题"></text-i18n>
                <div class="form-row">
                  <div class="form-group">
                    <label class="form-label">标题颜色</label>
                    <el-color-picker v-model="item.title_color" @change="onChange" show-alpha size="small"></el-color-picker>
                  </div>
                  <div class="form-group">
                    <label class="form-label">标题大小</label>
                    <el-input-number v-model="item.title_size" @change="onChange" :min="12" :max="72" :step="2" size="small"></el-input-number>
                  </div>
                </div>
              </div>

              {{-- 副标题设置 --}}
              <div class="content-section">
                <div class="section-subtitle">
                  <i class="el-icon-document"></i>
                  副标题设置
                </div>
                <text-i18n v-model="item.subtitle" @change="onChange" placeholder="请输入副标题"></text-i18n>
                <div class="form-row">
                  <div class="form-group">
                    <label class="form-label">副标题颜色</label>
                    <el-color-picker v-model="item.subtitle_color" @change="onChange" show-alpha size="small"></el-color-picker>
                  </div>
                  <div class="form-group">
                    <label class="form-label">副标题大小</label>
                    <el-input-number v-model="item.subtitle_size" @change="onChange" :min="12" :max="48" :step="2" size="small"></el-input-number>
                  </div>
                </div>
              </div>

              {{-- 按钮设置 --}}
              <div class="content-section">
                <div class="section-subtitle">
                  <i class="el-icon-mouse"></i>
                  按钮设置
                </div>
                <text-i18n v-model="item.button_text" @change="onChange" placeholder="请输入按钮文本"></text-i18n>
                <div class="setting-group mt-3">
                  <div class="section-subtitle">
                    <i class="el-icon-link"></i>
                    按钮链接
                  </div>
                  <link-selector v-model="item.button_link" @change="onChange"></link-selector>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label class="form-label">按钮背景色</label>
                    <el-color-picker v-model="item.button_color" @change="onChange" show-alpha size="small"></el-color-picker>
                  </div>
                  <div class="form-group">
                    <label class="form-label">按钮文字色</label>
                    <el-color-picker v-model="item.button_text_color" @change="onChange" show-alpha size="small"></el-color-picker>
                  </div>
                </div>
              </div>

              {{-- 位置设置 --}}
              <div class="content-section">
                <div class="section-subtitle">
                  <i class="el-icon-s-grid"></i>
                  位置设置
                </div>
                <div class="setting-group">
                  <label class="form-label">内容位置</label>
                  <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <el-button 
                      :type="item.title_align === 'left' ? 'primary' : 'default'"
                      size="small"
                      @click="item.title_align = 'left'; onChange()"
                      icon="el-icon-s-fold"
                    >
                      左侧
                    </el-button>
                    <el-button 
                      :type="item.title_align === 'center' ? 'primary' : 'default'"
                      size="small"
                      @click="item.title_align = 'center'; onChange()"
                      icon="el-icon-s-operation"
                    >
                      居中
                    </el-button>
                    <el-button 
                      :type="item.title_align === 'right' ? 'primary' : 'default'"
                      size="small"
                      @click="item.title_align = 'right'; onChange()"
                      icon="el-icon-s-unfold"
                    >
                      右侧
                    </el-button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </draggable>

        {{-- 空状态 --}}
        <div v-if="!module.images || module.images.length === 0" class="empty-state">
          <i class="el-icon-picture-outline"></i>
          <p>暂无幻灯片，点击下方按钮添加</p>
        </div>

        {{-- 添加按钮 --}}
        <div class="add-button-wrapper">
          <el-button type="primary" size="small" @click="addImage" icon="el-icon-plus">
            添加幻灯片
          </el-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script type="text/javascript">
Vue.component('module-editor-slideshow', {
  template: '#module-editor-slideshow',

  props: ['module'],

  data: function () {
    return {
      debounceTimer: null,
      currentLocale: '{{ locale_code() }}',
      isToggling: false
    }
  },

  watch: {
    module: {
      handler: function (val) {
        if (!this.isToggling) {
          this.onChange();
        }
      },
      deep: true,
    }
  },

  created: function () {
    // 初始化默认值
    if (!this.module.images) {
      this.module.images = [{
        image: this.getDefaultImage(),
        link: {
          type: 'product',
          value: ''
        },
        button_link: {
          type: 'product',
          value: ''
        },
        title: this.languagesFill(''),
        subtitle: this.languagesFill(''),
        button_text: this.languagesFill(''),
        title_color: '#ffffff',
        subtitle_color: '#ffffff',
        button_color: '#667eea',
        button_text_color: '#ffffff',
        title_size: 24,
        subtitle_size: 16,
        title_align: 'center',
        show: true
      }];
    } else {
      // 确保现有数据有正确的结构
      this.module.images.forEach(item => {
        if (!item.title) {
          this.$set(item, 'title', this.languagesFill(''));
        }
        if (!item.subtitle) {
          this.$set(item, 'subtitle', this.languagesFill(''));
        }
        if (!item.button_text) {
          this.$set(item, 'button_text', this.languagesFill(''));
        }
        if (!item.button_link) {
          this.$set(item, 'button_link', {
            type: 'product',
            value: ''
          });
        }
        if (!item.title_color) {
          this.$set(item, 'title_color', '#ffffff');
        }
        if (!item.subtitle_color) {
          this.$set(item, 'subtitle_color', '#ffffff');
        }
        if (!item.button_color) {
          this.$set(item, 'button_color', '#667eea');
        }
        if (!item.button_text_color) {
          this.$set(item, 'button_text_color', '#ffffff');
        }
        if (!item.title_size) {
          this.$set(item, 'title_size', 24);
        }
        if (!item.subtitle_size) {
          this.$set(item, 'subtitle_size', 16);
        }
        if (!item.title_align) {
          this.$set(item, 'title_align', 'center');
        }
        if (typeof item.show === 'undefined') {
          this.$set(item, 'show', false);
        }
      });
    }
    if (!this.module.width) {
      this.$set(this.module, 'width', 'wide');
    }
  },

  methods: {
    onChange() {
      // 清除之前的定时器
      if (this.debounceTimer) {
        clearTimeout(this.debounceTimer);
      }
      
      // 设置新的定时器
      this.debounceTimer = setTimeout(() => {
        this.$emit('on-changed', this.module);
      }, 300);
    },

    removeImage(index) {
      this.$confirm('确定要删除这个幻灯片吗？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.module.images.splice(index, 1);
        this.$message.success('删除成功');
      }).catch(() => {});
    },

    toggleSlide(index) {
      this.isToggling = true;
      
      // 关闭其他幻灯片
      this.module.images.forEach((item, key) => {
        if (key !== index) {
          this.$set(item, 'show', false);
        }
      });
      // 切换当前幻灯片
      const currentShow = this.module.images[index].show;
      this.$set(this.module.images[index], 'show', !currentShow);
      
      // 延迟重置标志，确保DOM更新完成
      this.$nextTick(() => {
        this.isToggling = false;
      });
    },

    addImage() {
      // 关闭所有幻灯片
      this.module.images.forEach(item => {
        item.show = false;
      });
      
      // 添加新幻灯片
      this.module.images.push({
        image: this.getDefaultImage(), 
        link: {
          type: 'product', 
          value: ''
        },
        button_link: {
          type: 'product',
          value: ''
        },
        title: this.languagesFill(''),
        subtitle: this.languagesFill(''),
        button_text: this.languagesFill(''),
        title_color: '#ffffff',
        subtitle_color: '#ffffff',
        button_color: '#667eea',
        button_text_color: '#ffffff',
        title_size: 24,
        subtitle_size: 16,
        title_align: 'center',
        show: true
      });
      
      this.$message.success('添加幻灯片成功');
    },
    
    languagesFill(text) {
      const obj = {};
      $languages.forEach(e => {
        obj[e.code] = text;
      });
      return obj;
    },

    getDefaultImage() {
      return PLACEHOLDER_IMAGE_PATH;
    },

    thumbnail(image, width = 60, height = 40) {
      if (!image) {
        return PLACEHOLDER_IMAGE_URL;
      }
      
      let imageUrl = '';
      
      if (typeof image === 'string') {
        imageUrl = image;
      } else if (typeof image === 'object') {
        const locale = this.currentLocale;
        imageUrl = image[locale] || Object.values(image)[0];
        if (!imageUrl) {
          return PLACEHOLDER_IMAGE_URL;
        }
      }
      
      // 如果是完整URL，直接返回
      if (imageUrl.indexOf('http') === 0) {
        return imageUrl;
      }
      
      // 如果是相对路径，添加asset前缀
      const fullUrl = asset + imageUrl;
      
      // 使用image_resize函数生成缩略图
      if (typeof image_resize === 'function') {
        return image_resize(fullUrl, width, height);
      }
      
      return fullUrl;
    },

    getTitleText(item) {
      if (!item.title) return '';
      if (typeof item.title === 'string') return item.title;
      if (typeof item.title === 'object' && item.title[this.currentLocale]) {
        return item.title[this.currentLocale].trim();
      }
      return '';
    }
  }
});
</script>
