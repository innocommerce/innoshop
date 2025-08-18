@verbatim
<template id="text-i18n">
  <div class="text-i18n">
    <!-- 多语言模式 -->
    <div v-if="showLanguageTabs">
      <div class="language-inputs">
        <div 
          v-for="lang in languages" 
          :key="lang.code"
          class="language-input-group"
          :class="{ 'required': lang.code === 'en' || lang.code === 'zh_cn' }">
          
          <!-- 国旗和输入框水平排列 -->
          <div class="language-input-row">
            <img :src="'/images/flag/' + lang.code + '.png'" class="flag-icon" :alt="lang.name" @error="handleFlagError">
            <div class="input-wrapper">
            <el-input 
              v-if="type === 'text'" 
              v-model="langValues[lang.code]" 
              :placeholder="placeholder"
              size="small"
              @input="onInput"
              @change="onChange">
            </el-input>
            
            <el-input 
              v-else-if="type === 'textarea'" 
              v-model="langValues[lang.code]" 
              type="textarea"
              :rows="rows"
              :placeholder="placeholder"
              size="small"
              @input="onInput"
              @change="onChange">
            </el-input>
            
            <el-input-number 
              v-else-if="type === 'number'" 
              v-model="langValues[lang.code]" 
              :min="min"
              :max="max"
              :step="step"
              size="small"
              @change="onChange">
            </el-input-number>
            
            <el-color-picker 
              v-else-if="type === 'color'" 
              v-model="langValues[lang.code]" 
              show-alpha
              @change="onChange">
            </el-color-picker>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- 单语言模式 -->
    <div v-else>
      <el-input 
        v-if="type === 'text'" 
        v-model="singleValue" 
        :placeholder="placeholder"
        size="small"
        @input="onInput"
        @change="onChange">
      </el-input>
      
      <el-input 
        v-else-if="type === 'textarea'" 
        v-model="singleValue" 
        type="textarea"
        :rows="rows"
        :placeholder="placeholder"
        size="small"
        @input="onInput"
        @change="onChange">
      </el-input>
      
      <el-input-number 
        v-else-if="type === 'number'" 
        v-model="singleValue" 
        :min="min"
        :max="max"
        :step="step"
        size="small"
        @change="onChange">
      </el-input-number>
      
      <el-color-picker 
        v-else-if="type === 'color'" 
        v-model="singleValue" 
        show-alpha
        @change="onChange">
      </el-color-picker>
    </div>
  </div>
</template>
@endverbatim

<script>
Vue.component('text-i18n', {
  template: '#text-i18n',
  props: {
    value: {
      type: [String, Object, Number],
      default: ''
    },
    type: {
      type: String,
      default: 'text'
    },
    placeholder: {
      type: String,
      default: '请输入内容'
    },
    rows: {
      type: Number,
      default: 3
    },
    min: {
      type: Number,
      default: 0
    },
    max: {
      type: Number,
      default: 999999
    },
    step: {
      type: Number,
      default: 1
    },
    multiLanguage: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      activeLanguage: $locale || 'zh_cn',
      languages: $languages || [],
      langValues: {},
      singleValue: ''
    }
  },
  computed: {
    showLanguageTabs() {
      return this.multiLanguage && this.languages.length > 1;
    }
  },
  watch: {
    value: {
      immediate: true,
      handler(val) {
        this.initializeValues(val);
      }
    }
  },
  methods: {
    initializeValues(val) {
      if (this.showLanguageTabs) {
        // 多语言模式
        this.langValues = {};
        this.languages.forEach(lang => {
          if (typeof val === 'object' && val !== null) {
            this.langValues[lang.code] = val[lang.code] || '';
          } else {
            this.langValues[lang.code] = val || '';
          }
        });
      } else {
        // 单语言模式
        if (typeof val === 'object' && val !== null) {
          this.singleValue = val[this.activeLanguage] || Object.values(val)[0] || '';
        } else {
          this.singleValue = val || '';
        }
      }
    },
    
    onInput() {
      this.emitChange();
    },
    
    onChange() {
      this.emitChange();
    },
    
    emitChange() {
      let result;
      
      if (this.showLanguageTabs) {
        // 多语言模式
        result = { ...this.langValues };
      } else {
        // 单语言模式
        result = this.singleValue;
      }
      
      this.$emit('input', result);
      this.$emit('change', result);
    },
    
    handleFlagError(event) {
      // 处理国旗图片加载失败的情况，隐藏图片
      event.target.style.display = 'none';
    }
  }
});
</script>

<style scoped>
.text-i18n {
  width: 100%;
}

/* 多语言输入组样式 */
.language-inputs {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.language-input-group {
  padding: 4px;
  background-color: transparent;
  transition: all 0.3s ease;
}

/* 国旗和输入框水平排列 */
.language-input-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.flag-icon {
  width: 20px;
  height: 15px;
  border-radius: 2px;
  object-fit: cover;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
  flex-shrink: 0;
}

/* 输入框包装器 */
.input-wrapper {
  flex: 1;
  min-width: 0;
}

/* 输入框样式 */
.el-input,
.el-input-number {
  width: 100%;
}

.el-input .el-input__inner {
  border-radius: 6px;
  border: 1px solid #e4e7ed;
  background-color: #fff;
  color: #606266;
  font-size: 13px;
  height: 36px;
  line-height: 36px;
  padding: 0 12px;
  transition: all 0.3s ease;
}

.el-input .el-input__inner:hover {
  border-color: #c0c4cc;
}

.el-input .el-input__inner:focus {
  border-color: #409eff;
  box-shadow: 0 0 0 2px rgba(64, 158, 255, 0.1);
}

/* 文本域样式 */
.el-textarea .el-textarea__inner {
  border-radius: 6px;
  border: 1px solid #e4e7ed;
  background-color: #fff;
  color: #606266;
  font-size: 13px;
  padding: 10px 12px;
  resize: vertical;
  transition: all 0.3s ease;
}

.el-textarea .el-textarea__inner:hover {
  border-color: #c0c4cc;
}

.el-textarea .el-textarea__inner:focus {
  border-color: #409eff;
  box-shadow: 0 0 0 2px rgba(64, 158, 255, 0.1);
}

/* 数字输入框样式 */
.el-input-number .el-input__inner {
  border-radius: 6px;
  border: 1px solid #e4e7ed;
  background-color: #fff;
  color: #606266;
  font-size: 13px;
  height: 36px;
  line-height: 36px;
  padding: 0 12px;
  transition: all 0.3s ease;
}

.el-input-number .el-input__inner:hover {
  border-color: #c0c4cc;
}

.el-input-number .el-input__inner:focus {
  border-color: #409eff;
  box-shadow: 0 0 0 2px rgba(64, 158, 255, 0.1);
}

/* 小尺寸数字输入框样式 */
.el-input-number--small {
  line-height: 34px;
}

/* 颜色选择器样式 */
.el-color-picker {
  width: 100%;
}

.el-color-picker .el-color-picker__trigger {
  border-radius: 6px;
  border: 1px solid #e4e7ed;
  background-color: #fff;
  height: 36px;
  width: 100%;
  transition: all 0.3s ease;
}

.el-color-picker .el-color-picker__trigger:hover {
  border-color: #c0c4cc;
}

</style>
