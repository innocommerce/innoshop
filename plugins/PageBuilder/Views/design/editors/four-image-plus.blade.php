{{-- 一行四图Plus编辑模块 - 现代化风格 --}}
<template id="module-editor-four-image-plus-template">
  <div class="four-image-plus-editor">
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
          @{{ lang.recommended_same_size_drag }}
        </div>

        <draggable ghost-class="dragabble-ghost" :list="form.images"
          :options="{ animation: 330, handle: '.icon-rank' }">
          <div class="image-item" v-for="(item, index) in form.images" :key="index">
            <div class="image-header" @click="itemShow(index)">
              <div class="image-info">
                <el-tooltip class="drag-handle" effect="dark" :content="lang.drag_sort" placement="left">
                  <i class="el-icon-rank"></i>
                </el-tooltip>
                <img :src="thumbnail(item.image)" class="image-preview">
                <span class="image-label">@{{ lang.image }} @{{ index + 1 }}</span>
              </div>
              <div class="image-actions">
                <el-tooltip effect="dark" :content="lang.delete" placement="left">
                  <div class="remove-btn" @click.stop="removeImage(index)">
                    <i class="el-icon-delete"></i>
                  </div>
                </el-tooltip>
                <i :class="'el-icon-arrow-' + (item.show ? 'up' : 'down')"></i>
              </div>
            </div>
            <div :class="'image-content ' + (item.show ? 'active' : '')">
              <div class="image-upload-section">
                <single-image-selector v-model="item.image" :aspectRatio="1" :targetWidth="400"
                  :targetHeight="400" @change="onChange"></single-image-selector>
                <div class="upload-tip">@{{ lang.recommended_size_400 }}</div>
              </div>
              <div class="image-settings">
                <div class="setting-group">
                  <div class="setting-label">@{{ lang.image_description }}</div>
                  <text-i18n v-model="item.description" @change="onChange" :placeholder="lang.enter_image_description"></text-i18n>
                </div>
                <div class="setting-group">
                  <div class="setting-label">@{{ lang.image_link }}</div>
                  <link-selector :hide-types="['catalog', 'static']" v-model="item.link" @change="onChange"></link-selector>
                </div>
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

{{-- 一行四图Plus组件脚本 --}}
<script type="text/javascript">
  Vue.component('module-editor-four-image-plus', {
    template: '#module-editor-four-image-plus-template',
    props: ['module'],
    data: function() {
      return {
        debounceTimer: null,
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
          this.onChange();
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
          this.$message.warning(lang.max_4_images_warning);
          return;
        }
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
      itemShow(index) {
        this.form.images[index].show = !this.form.images[index].show;
      }
    }
  });
</script>
