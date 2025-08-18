{{-- 图片选择器组件 - 参考WebBuilder实现 --}}
<template id="multi-image-selector">
  <div class="pb-image-selector">
    <el-tabs v-if="isLanguage" @tab-click="tabClick" :value="'language-' + tabActiveId"
      :stretch="languages.length > 5 ? true : false" type="card" :class="languages.length <= 1 ? 'languages-a' : ''">
      {{-- 查询所有语言 languages --}}
      <el-tab-pane v-for="(item, index) in languages" :key="index" :label="item.name"
        :name="'language-' + item.code">
        <span slot="label" class="selector-label">@{{ item.name }}</span>
        <div class="i18n-inner">
          <div class="img">
            {{-- 缩略图(value[item.code]) --}}
            <el-image :src="type == 'image' ? thumbnail(src) : 'image/video.png'" :id="'thumb-' + id"
              @click="selectButtonClicked">
              <div slot="error" class="image-slot">
                <i class="el-icon-picture-outline"></i>
              </div>
            </el-image>
          </div>
          <div class="btns">
            <el-button type="primary" size="mini" plain @click="selectButtonClicked">选择</el-button>
            <el-button size="mini" plain class="remove-btn" @click="removeImage">删除</el-button>
          </div>
          <input type="hidden" value="" v-model="src" :id="'input-' + id">
        </div>
      </el-tab-pane>
    </el-tabs>
    {{-- 单语言 --}}
    <div class="i18n-inner" v-else>
      <div class="img">
        <el-image :src="type == 'image' ? thumbnail(value) : 'image/video.png'" :id="'thumb-' + id"
          @click="selectButtonClicked">
          <div slot="error" class="image-slot">
            <i class="el-icon-picture-outline"></i>
          </div>
        </el-image>
      </div>

      <div class="btns">
        <el-button type="primary" size="mini" plain @click="selectButtonClicked">选择</el-button>
        <el-button size="mini" plain class="remove-btn" @click="removeImage">删除</el-button>
      </div>
      <input type="hidden" value="" v-model="src">
    </div>
  </div>
</template>

{{-- 图片选择器脚本 --}}
<script type="text/javascript">
  Vue.component('multi-image-selector', {
    template: '#multi-image-selector',
    props: {
      value: {
        default: null
      },
      type: {
        default: 'image'
      },
      isLanguage: {
        default: true
      },
    },
    data: function() {
      return {
        tabActiveId: $locale || (($languages && $languages.length > 0) ? $languages[0].code : 'zh_cn'),
        languages: $languages,
        internalValues: {},
        id: 'image-selector-' + inno.randomString(4),
        loading: null
      }
    },
    watch: {
      value: {
        handler: function(val) {
          if (this.isLanguage) {
            if (typeof val === 'object' && val) {
              this.internalValues = { ...val };
            } else {
              this.internalValues = {};
            }
          }
        },
        immediate: true,
        deep: true
      }
    },
    computed: {
      src: {
        get() {
          if (this.isLanguage) {
            // 确保不会因为undefined而报错
            if (!this.value || typeof this.value !== 'object') {
              return '';
            }
            return this.value[this.tabActiveId] || '';
          } else {
            return this.value;
          }
        },
        set(newValue) {
          if (this.isLanguage) {
            this.$set(this.value, this.tabActiveId, newValue);
            this.$emit('input', this.value);
          } else {
            this.$emit('input', newValue);
          }
        }
      }
    },
    methods: {
      removeImage() {
        if (this.isLanguage) {
          this.src = '';
        } else {
          this.src = '';
        }
      },
      tabClick(e) {
        if (this.languages && this.languages.length > 0 && e.index >= 0) {
          this.tabActiveId = this.languages[e.index * 1].code;
        }
      },
      selectButtonClicked() {
        // 使用InnoShop核心的文件管理器
        if (window.inno && window.inno.fileManagerIframe) {
          window.inno.fileManagerIframe((file) => {
            console.log("File selected:", file);

            // 修复URL
            let fileUrl = file.url || file.path;
            if (fileUrl && !fileUrl.match(/^https?:\/\//)) {
              if (!fileUrl.startsWith("/")) {
                fileUrl = "/" + fileUrl;
              }
              fileUrl = window.location.origin + fileUrl;
            }

            this.src = fileUrl;
            this.$emit('change');
          }, {
            type: 'image',
            multiple: false
          });
        } else {
          console.error('File manager not available');
          this.$message.error('文件管理器不可用');
        }
      },
    }
  });
</script>

{{-- 图片选择器样式 --}}
<style scoped>
  .pb-image-selector {}

  .languages-a .el-tabs__header {
    display: none;
  }

  .pb-image-selector .btns {
    margin-left: 10px;
  }

  .pb-image-selector .btns .el-button {
    padding: 7px 10px;
  }

  .pb-image-selector .el-tabs__nav {
    display: flex;
    border-color: #ebecf5;
  }

  .pb-image-selector .el-tabs__nav>div {
    background: #ebecf5;
    border-left: 1px solid #d7dbf7 !important;
    padding: 0 !important;
    flex: 1;
    height: 30px;
    line-height: 30px;
    min-width: 50px;
    text-align: center;
  }

  .pb-image-selector .el-tabs__nav>div:first-of-type {
    border-left: none !important;
  }

  .pb-image-selector .el-tabs__nav>div.is-active {
    background: #fff !important;
  }

  .pb-image-selector .i18n-inner {
    margin-top: 5px;
    display: flex;
    align-items: center;
    background: whitesmoke;
    padding: 5px;
    border-radius: 4px;
  }

  .pb-image-selector .i18n-inner .img {
    width: 46px;
    height: 46px;
    border: 1px solid #eee;
    padding: 2px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
  }

  .image-slot {
    font-size: 26px;
    color: #939ab3;
  }

  .pb-image-selector .i18n-inner .img img {
    max-width: 100%;
    height: auto;
  }

  .pb-image-selector .el-tabs__header {
    margin-bottom: 0;
  }

  .selector-label {
    padding: 0 4px;
    font-size: 12px;
  }

  .remove-btn {
    margin-left: 4px;
  }
</style> 