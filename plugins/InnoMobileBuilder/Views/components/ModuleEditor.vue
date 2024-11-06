<template>
  <div class="module-edit">
    <div class="c-title">
      模块编辑 - <span v-if="module">{{ module.title }}</span>
    </div>
    <div v-if="module" class="component-wrap">
      <component
        :is="editorComponent"
        :module="module.content"
        @on-changed="handleUpdate"
      />
    </div>
  </div>
</template>

<script>
import SlideShowEditor from './editors/SlideShow.vue'
import Image100Editor from './editors/Image100.vue'
import IconsEditor from './editors/Icons.vue'
import ProductEditor from './editors/Product.vue'
import CategoryEditor from './editors/Category.vue'
import LatestEditor from './editors/Latest.vue'

export default {
  name: 'ModuleEditor',

  components: {
    SlideShowEditor,
    Image100Editor,
    IconsEditor,
    ProductEditor,
    CategoryEditor,
    LatestEditor
  },

  props: {
    module: {
      type: Object,
      required: true
    }
  },

  computed: {
    editorComponent() {
      const map = {
        slideshow: 'SlideShowEditor',
        image100: 'Image100Editor',
        icons: 'IconsEditor',
        product: 'ProductEditor',
        category: 'CategoryEditor',
        latest: 'LatestEditor'
      }
      return map[this.module.code]
    }
  },

  methods: {
    handleUpdate(content) {
      this.$emit('updated', {
        ...this.module,
        content
      })
    }
  }
}
</script>
