<template id="single-image-selector">
  <div class="image-selector">
    <div class="image-preview" @click="openSelector">
      <img v-if="displayImage" :src="displayImage" class="preview-img" alt="预览图">
      <div v-else class="placeholder">
        <i class="el-icon-picture"></i>
        <span>点击选择图片</span>
      </div>
    </div>
    
    <div class="image-controls">
      <el-button type="primary" size="mini" @click="openSelector">
        <i class="el-icon-picture"></i> 选择图片
      </el-button>
      <el-button v-if="displayImage" type="danger" size="mini" @click="clearImage">
        <i class="el-icon-delete"></i> 清除
      </el-button>
    </div>
    
    <!-- 多语言图片选择 -->
    <div v-if="showLanguageTabs" class="language-tabs">
      <el-tabs v-model="activeLanguage" type="card" size="mini">
        <el-tab-pane v-for="lang in languages" :key="lang.code" :label="lang.name" :name="lang.code">
          <div class="lang-image-selector">
            <div class="image-preview" @click="openSelector(lang.code)">
              <img v-if="getLangImage(lang.code)" :src="getLangImage(lang.code)" class="preview-img" alt="预览图">
              <div v-else class="placeholder">
                <i class="el-icon-picture"></i>
                <span>点击选择图片</span>
              </div>
            </div>
            <div class="image-controls">
              <el-button type="primary" size="mini" @click="openSelector(lang.code)">
                <i class="el-icon-picture"></i> 选择图片
              </el-button>
              <el-button v-if="getLangImage(lang.code)" type="danger" size="mini" @click="clearLangImage(lang.code)">
                <i class="el-icon-delete"></i> 清除
              </el-button>
            </div>
          </div>
        </el-tab-pane>
      </el-tabs>
    </div>
  </div>
</template>

<script>
Vue.component('single-image-selector', {
  template: '#single-image-selector',
  props: {
    value: {
      type: [String, Object],
      default: ''
    },
    multiLanguage: {
      type: Boolean,
      default: false
    },
    aspectRatio: {
      type: Number,
      default: null
    },
    targetWidth: {
      type: Number,
      default: null
    },
    targetHeight: {
      type: Number,
      default: null
    }
  },
  data() {
    return {
      activeLanguage: $locale || 'zh_cn',
      languages: $languages || [],
      showLanguageTabs: false
    }
  },
  computed: {
    displayImage() {
      if (this.multiLanguage) {
        return this.getLangImage(this.activeLanguage);
      }
      
      if (typeof this.value === 'string') {
        return this.value;
      }
      
      if (typeof this.value === 'object' && this.value) {
        return this.value[this.activeLanguage] || Object.values(this.value)[0] || '';
      }
      
      return '';
    }
  },
  watch: {
    multiLanguage: {
      immediate: true,
      handler(val) {
        this.showLanguageTabs = val && this.languages.length > 1;
      }
    }
  },
  methods: {
    openSelector(langCode = null) {
      const targetLang = langCode || this.activeLanguage;
      
      // 使用InnoShop核心的文件管理器
      if (window.inno && window.inno.fileManagerIframe) {
        window.inno.fileManagerIframe((file) => {
          console.log("File selected:", file);
          
          // 修复URL
          let fileUrl = file.origin_url || file.path;
          if (fileUrl && !fileUrl.match(/^https?:\/\//)) {
            if (!fileUrl.startsWith("/")) {
              fileUrl = "/" + fileUrl;
            }
            fileUrl = window.location.origin + fileUrl;
          }
          
          this.setImage(fileUrl, targetLang);
        }, {
          type: 'image',
          multiple: false
        });
      } else {
        console.error('File manager not available');
        this.$message.error('文件管理器不可用');
      }
    },
    
    setImage(imagePath, langCode = null) {
      const targetLang = langCode || this.activeLanguage;
      
      if (this.multiLanguage) {
        // 多语言模式
        if (typeof this.value !== 'object') {
          this.$emit('input', {});
        }
        
        const newValue = { ...this.value };
        newValue[targetLang] = imagePath;
        this.$emit('input', newValue);
      } else {
        // 单语言模式
        this.$emit('input', imagePath);
      }
      
      this.$emit('change');
    },
    
    clearImage(langCode = null) {
      const targetLang = langCode || this.activeLanguage;
      
      if (this.multiLanguage) {
        const newValue = { ...this.value };
        delete newValue[targetLang];
        this.$emit('input', newValue);
      } else {
        this.$emit('input', '');
      }
      
      this.$emit('change');
    },
    
    getLangImage(langCode) {
      if (typeof this.value === 'object' && this.value) {
        return this.value[langCode] || '';
      }
      return '';
    },
    
    clearLangImage(langCode) {
      this.clearImage(langCode);
    }
  }
});
</script>

<style scoped>
.image-selector {
  width: 100%;
}

.image-preview {
  width: 100%;
  height: 120px;
  border: 2px dashed #d9d9d9;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s;
  margin-bottom: 10px;
}

.image-preview:hover {
  border-color: #409eff;
  background-color: #f0f9ff;
}

.preview-img {
  max-width: 100%;
  max-height: 100%;
  object-fit: cover;
  border-radius: 4px;
}

.placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: #909399;
}

.placeholder i {
  font-size: 24px;
  margin-bottom: 5px;
}

.placeholder span {
  font-size: 12px;
}

.image-controls {
  display: flex;
  gap: 8px;
  margin-bottom: 10px;
}

.language-tabs {
  margin-top: 15px;
}

.lang-image-selector {
  padding: 10px 0;
}

.el-tabs--card .el-tabs__header .el-tabs__item {
  border: 1px solid #e4e7ed;
  border-bottom: none;
  border-radius: 4px 4px 0 0;
  margin-right: 5px;
}

.el-tabs--card .el-tabs__header .el-tabs__item.is-active {
  border-bottom-color: #fff;
  background-color: #fff;
}
</style>
