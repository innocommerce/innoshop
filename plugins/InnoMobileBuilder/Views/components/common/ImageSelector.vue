<template>
  <div class="pb-image-selector">
    <!-- 多语言标签页 -->
    <el-tabs v-if="isLanguage" v-model="activeTab">
      <el-tab-pane
        v-for="lang in languages"
        :key="lang.code"
        :label="lang.name"
        :name="lang.code"
      >
        <div class="image-selector">
          <div class="preview">
            <el-image
              :src="value[lang.code]"
              @click="selectImage"
            />
          </div>
          <div class="actions">
            <el-button @click="selectImage">选择</el-button>
            <el-button @click="removeImage(lang.code)">删除</el-button>
          </div>
        </div>
      </el-tab-pane>
    </el-tabs>

    <!-- 单语言模式 -->
    <div v-else class="image-selector">
      <div class="preview">
        <el-image
          :src="value"
          @click="selectImage"
        />
      </div>
      <div class="actions">
        <el-button @click="selectImage">选择</el-button>
        <el-button @click="removeImage">删除</el-button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ImageSelector',

  props: {
    value: {
      required: true
    },
    isLanguage: {
      type: Boolean,
      default: true
    }
  },

  data() {
    return {
      activeTab: 'zh_cn',
      languages: window.$languages
    }
  },

  methods: {
    selectImage() {
      inno.fileManagerIframe(images => {
        if (this.isLanguage) {
          this.$emit('input', {
            ...this.value,
            [this.activeTab]: images[0].path
          });
        } else {
          this.$emit('input', images[0].path);
        }
      });
    },

    removeImage(lang) {
      if (this.isLanguage) {
        this.$emit('input', {
          ...this.value,
          [lang]: ''
        });
      } else {
        this.$emit('input', '');
      }
    }
  }
}
</script>
