{{-- 一行四图编辑模块 - 现代化风格 --}}
<template id="module-editor-four-image-template">
  <div class="four-image-editor">
    <div class="top-spacing"></div>
    
    {{-- 模块宽度设置 --}}
    <div class="editor-section">
      <div class="section-title">@{{ lang.module_width }}</div>
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

    {{-- 模块标题 --}}
    <div class="editor-section">
      <div class="section-title">@{{ lang.module_title }}</div>
      <div class="section-content">
        <text-i18n v-model="form.title" @change="onChange" :placeholder="lang.enter_module_title"></text-i18n>
      </div>
    </div>

    {{-- 副标题 --}}
    <div class="editor-section">
      <div class="section-title">@{{ lang.subtitle }}</div>
      <div class="section-content">
        <text-i18n v-model="form.subtitle" @change="onChange" :placeholder="lang.enter_subtitle"></text-i18n>
      </div>
    </div>

    {{-- 图片设置 --}}
    <div class="editor-section">
      <div class="section-title">@{{ lang.image_settings }}</div>
      <div class="section-content">
        <div class="setting-tip">
          <i class="el-icon-info"></i>
          @{{ lang.recommended_same_size }}
        </div>

        <draggable ghost-class="dragabble-ghost" :list="form.images"
          :options="{ animation: 330, handle: '.drag-handle' }">
          <div class="slide-item" v-for="(item, index) in form.images" :key="index">
            <div class="slide-header" @click="toggleImage(index)">
              <div class="slide-info">
                <div class="drag-handle">
                  <i class="el-icon-rank"></i>
                </div>
                <div class="slide-preview">
                  <img :src="thumbnail(item.image)" class="preview-img">
                  <div class="slide-number"># @{{ index + 1 }}</div>
                </div>
                <div class="slide-title">
                  @{{ lang.image }} @{{ index + 1 }}
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

            <div :class="'slide-content ' + (item.show ? 'expanded' : '')">
              <div class="content-section">
                <div class="section-subtitle">
                  <i class="el-icon-picture-outline"></i>
                  @{{ lang.image_settings }}
                </div>
                <single-image-selector v-model="item.image" :aspectRatio="1" :targetWidth="400"
                  :targetHeight="400"></single-image-selector>
                <div class="image-tips">@{{ lang.recommended_size_400 }}</div>
              </div>

              <div class="content-section">
                <div class="section-subtitle">
                  <i class="el-icon-edit"></i>
                  @{{ lang.image_description }}
                </div>
                <text-i18n v-model="item.description" @change="onChange" :placeholder="lang.enter_image_description"></text-i18n>
              </div>

              <div class="content-section">
                <div class="section-subtitle">
                  <i class="el-icon-link"></i>
                  @{{ lang.image_link }}
                </div>
                <link-selector :hide-types="['catalog', 'static']" v-model="item.link" @change="onChange"></link-selector>
              </div>
            </div>
          </div>
        </draggable>

        <div class="add-image-section" v-if="form.images.length < 4">
          <el-button type="primary" size="small" @click="addImage" icon="el-icon-circle-plus-outline">
            @{{ lang.add_image }} (@{{ form.images.length }}/4)
          </el-button>
        </div>
      </div>
    </div>
  </div>
</template>

{{-- 一行四图组件脚本 --}}
<script type="text/javascript">
  Vue.component('module-editor-four-image', {
    template: '#module-editor-four-image-template',
    props: ['module'],
    data: function() {
      return {
        debounceTimer: null,
        isToggling: false,
        form: {
          title: {},
          subtitle: {},
          images: [],
          width: 'wide'
        },
        source: {
          locale: $locale
        }
      }
    },
    watch: {
      form: {
        handler: function(val) {
          if (!this.isToggling) {
            this.onChange();
          }
        },
        deep: true
      }
    },
    created: function() {
      if (this.module) {
        this.form = JSON.parse(JSON.stringify(this.module));
      }

      if (!this.form.title) {
        this.$set(this.form, 'title', this.languagesFill(''));
      }

      if (!this.form.subtitle) {
        this.$set(this.form, 'subtitle', this.languagesFill(''));
      }

      if (!this.form.images) {
        this.$set(this.form, 'images', []);
      }

      // Ensure each image has a reactive 'show' property for collapse/expand
      // Default to false (collapsed) like slideshow - accordion style
      if (this.form.images && this.form.images.length > 0) {
        this.form.images.forEach(function(img) {
          if (img.show === undefined) {
            this.$set(img, 'show', false);
          }
        }.bind(this));
      }

      if (!this.form.width) {
        this.$set(this.form, 'width', 'wide');
      }

      this.$emit('on-changed', this.form);
    },
    methods: {
      onChange() {
        // 清除之前的定时器
        if (this.debounceTimer) {
          clearTimeout(this.debounceTimer);
        }
        
        // 设置新的定时器
        this.debounceTimer = setTimeout(() => {
          this.$emit('on-changed', this.form);
        }, 300);
      },

      languagesFill(text) {
        const obj = {};
        $languages.forEach(e => {
          obj[e.code] = text;
        });
        return obj;
      },

      thumbnail(image) {
        if (!image) {
          return PLACEHOLDER_IMAGE_URL;
        }
        if (typeof image === 'object') {
          const locale = this.source.locale;
          const img = image[locale] || Object.values(image).find(v => v);
          if (img) {
            if (img.indexOf('http') === 0) {
              return img;
            }
            return asset + img;
          }
          return PLACEHOLDER_IMAGE_URL;
        }
        if (typeof image === 'string') {
          if (image.indexOf('http') === 0) {
            return image;
          }
          return asset + image;
        }
        return PLACEHOLDER_IMAGE_URL;
      },
      addImage() {
        if (this.form.images.length >= 4) {
          this.$message.warning(lang.max_4_images);
          return;
        }

        // Close all existing images
        this.form.images.forEach(item => {
          this.$set(item, 'show', false);
        });

        this.form.images.push({
          image: '',
          description: this.languagesFill(''),
          link: {
            type: 'product',
            value: ''
          },
          show: true
        });
      },
      removeImage(index) {
        this.form.images.splice(index, 1);
      },
      toggleImage(index) {
        this.isToggling = true;

        // Close other images (accordion style)
        this.form.images.forEach((item, key) => {
          if (key !== index) {
            this.$set(item, 'show', false);
          }
        });

        // Toggle current image
        const currentShow = this.form.images[index].show;
        this.$set(this.form.images[index], 'show', !currentShow);

        this.$nextTick(() => {
          this.isToggling = false;
        });
      }
    }
  });
</script>
