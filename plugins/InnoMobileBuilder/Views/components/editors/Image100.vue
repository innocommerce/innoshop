<template>
  <div class="image100-editor">
    <div class="module-editor-row">内容</div>
    <div class="module-edit-group">
      <div class="module-edit-title">选择图片</div>
      <div class="image-edit-content">
        <!-- 图片选择器 -->
        <div class="pb-images-top">
          <image-selector v-model="form.images[0].image" />
          <div class="tag">建议尺寸: 1000 x 480</div>
        </div>

        <!-- 链接选择器 -->
        <link-selector
          :hide-types="['page_category', 'static']"
          v-model="form.images[0].link"
        />
      </div>
    </div>
  </div>
</template>

<script>
import ImageSelector from '../common/ImageSelector.vue'
import LinkSelector from '../common/LinkSelector.vue'

export default {
  name: 'Image100Editor',

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
  }
}
</script>
