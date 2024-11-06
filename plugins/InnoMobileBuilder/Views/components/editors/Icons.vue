<template>
  <div class="icons-editor">
    <div class="module-editor-row">设置</div>
    <div class="module-edit-group">
      <div class="module-edit-title">添加图标</div>

      <!-- 图标列表 -->
      <div
        v-for="(item, index) in form.images"
        :key="index"
        class="icon-item"
      >
        <!-- 图标项头部 -->
        <div class="item-header" @click="toggleItem(index)">
          <div class="left">
            <img :src="item.image" class="thumb">
          </div>
          <div class="right">
            <i class="el-icon-delete" @click.stop="removeIcon(index)"></i>
            <i :class="'el-icon-arrow-' + (item.show ? 'up' : 'down')"></i>
          </div>
        </div>

        <!-- 图标项内容 -->
        <div :class="['item-content', {'active': item.show}]">
          <!-- 图片选择器 -->
          <div class="image-selector">
            <image-selector
              v-model="item.image"
              :is-language="false"
            />
            <div class="hint">建议尺寸: 200 x 200</div>
          </div>

          <!-- 图标文字 -->
          <div class="module-edit-title">配置标题</div>
          <text-i18n v-model="item.text" />

          <!-- 链接选择器 -->
          <link-selector
            :hide-types="['page_category', 'static']"
            v-model="item.link"
          />
        </div>
      </div>

      <!-- 添加按钮 -->
      <div class="add-button">
        <el-button
          type="primary"
          size="small"
          icon="el-icon-plus"
          plain
          @click="addIcon"
        >
          添加图标
        </el-button>
      </div>
    </div>
  </div>
</template>

<script>
import ImageSelector from '../common/ImageSelector.vue'
import LinkSelector from '../common/LinkSelector.vue'
import TextI18n from '../common/TextI18n.vue'

export default {
  name: 'IconsEditor',

  components: {
    ImageSelector,
    LinkSelector,
    TextI18n
  },

  props: {
    module: {
      type: Object,
      required: true
    }
  },

  data() {
    return {
      form: JSON.parse(JSON.stringify(this.module))
    }
  },

  watch: {
    form: {
      handler(val) {
        this.$emit('on-changed', val)
      },
      deep: true
    }
  },

  methods: {
    toggleItem(index) {
      this.form.images.forEach((item, i) => {
        if (i !== index) item.show = false
      })
      this.form.images[index].show = !this.form.images[index].show
    },

    removeIcon(index) {
      this.form.images.splice(index, 1)
    },

    addIcon() {
      // 关闭其他展开项
      this.form.images.forEach(item => item.show = false)

      // 添加新图标
      this.form.images.push({
        image: '',
        show: true,
        text: this.createMultiLangObject(''),
        link: {
          type: 'product',
          value: ''
        }
      })
    },

    createMultiLangObject(value) {
      const obj = {}
      window.$languages.forEach(lang => {
        obj[lang.code] = value
      })
      return obj
    }
  }
}
</script>
