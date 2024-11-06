<template>
  <div class="slideshow-editor">
    <div class="module-editor-row">内容</div>
    <div class="module-edit-group">
      <div class="module-edit-title">选择图片</div>

      <!-- 图片列表 -->
      <draggable
        v-model="form.images"
        :options="dragOptions"
        class="image-list"
      >
        <div
          v-for="(item, index) in form.images"
          :key="index"
          class="image-item"
        >
          <!-- 图片项头部 -->
          <div class="item-header" @click="toggleItem(index)">
            <div class="left">
              <i class="el-icon-rank handle"></i>
              <img :src="getThumb(item.image)" class="thumb">
            </div>
            <div class="right">
              <i class="el-icon-delete" @click.stop="removeImage(index)"></i>
              <i :class="'el-icon-arrow-' + (item.show ? 'up' : 'down')"></i>
            </div>
          </div>

          <!-- 图片项内容 -->
          <div :class="['item-content', {'active': item.show}]">
            <!-- 图片选择器 -->
            <div class="image-selector">
              <image-selector v-model="item.image" />
              <div class="hint">建议尺寸(宽x高): 1000 x 500</div>
            </div>

            <!-- 链接选择器 -->
            <link-selector
              v-model="item.link"
              :hide-types="['page_category', 'static']"
            />
          </div>
        </div>
      </draggable>

      <!-- 添加按钮 -->
      <div class="add-button">
        <el-button
          type="primary"
          size="small"
          icon="el-icon-plus"
          @click="addImage"
        >
          添加图片
        </el-button>
      </div>
    </div>
  </div>
</template>

<script>
import ImageSelector from '../common/ImageSelector.vue'
import LinkSelector from '../common/LinkSelector.vue'

export default {
  name: 'SlideShowEditor',

  components: {
    ImageSelector,
    LinkSelector
  },

  props: {
    module: {
      type: Object,
      required: true
    }
  },

  data() {
    return {
      form: JSON.parse(JSON.stringify(this.module)),
      dragOptions: {
        animation: 300,
        handle: '.handle'
      }
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

    removeImage(index) {
      this.form.images.splice(index, 1)
    },

    addImage() {
      // 关闭其他展开项
      this.form.images.forEach(item => item.show = false)

      // 添加新图片
      this.form.images.push({
        image: this.createMultiLangObject(''),
        show: true,
        link: {
          type: 'product',
          value: ''
        }
      })
    },

    getThumb(image) {
      return image[window.$locale] || ''
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
