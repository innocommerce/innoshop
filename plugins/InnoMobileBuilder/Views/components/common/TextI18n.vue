<template>
  <div class="text-i18n">
    <!-- 多语言标签页 -->
    <el-tabs
      v-if="languages.length > 1"
      v-model="activeTab"
      type="card"
      :stretch="languages.length > 5"
    >
      <el-tab-pane
        v-for="lang in languages"
        :key="lang.code"
        :label="lang.name"
        :name="lang.code"
      >
        <div class="i18n-inner">
          <el-input
            :type="type"
            :rows="4"
            :placeholder="lang.name"
            :size="size"
            v-model="internalValues[lang.code]"
            @input="handleInput"
          />
        </div>
      </el-tab-pane>
    </el-tabs>

    <!-- 单语言模式 -->
    <div v-else class="i18n-inner">
      <el-input
        :type="type"
        :rows="4"
        :placeholder="languages[0].name"
        :size="size"
        :value="value[languages[0].code]"
        @input="val => handleInput(val, languages[0].code)"
      />
    </div>
  </div>
</template>

<script>
export default {
  name: 'TextI18n',

  props: {
    value: {
      type: Object,
      required: true
    },
    size: {
      type: String,
      default: 'small'
    },
    type: {
      type: String,
      default: 'text'
    }
  },

  data() {
    return {
      activeTab: 'zh_cn',
      languages: window.$languages,
      internalValues: {}
    }
  },

  created() {
    this.initData()
  },

  methods: {
    handleInput(val, code) {
      this.internalValues[code || this.activeTab] = val
      this.$emit('input', {...this.internalValues})
    },

    initData() {
      this.languages.forEach(lang => {
        this.$set(this.internalValues, lang.code, this.value[lang.code] || '')
      })
    }
  }
}
</script>
