<template>
  <div class="perview-wrap">
    <div class="c-title">效果预览</div>
    <div class="perview-content">
      <!-- 顶部图片 -->
      <div class="head">
        <img src="@/assets/images/builder-mb-bg.png" class="img-fluid">
      </div>

      <!-- 空状态提示 -->
      <div v-if="!modules.length" class="hint">
        <i class="bi bi-brightness-high fs-2"></i>
        <div class="mt-2">请从左边模块列表拖动模块到这里</div>
      </div>

      <!-- 模块列表 -->
      <draggable
        v-else
        class="view-modules-list dragArea"
        :options="dragOptions"
        :list="modules"
        @end="handleReorder"
      >
        <div
          v-for="(module, index) in modules"
          :key="index"
          :class="['list-item', {'active': editingIndex === index}]"
          @click="handleModuleClick(index)"
        >
          <!-- 模块工具栏 -->
          <div class="module-tool">
            <div class="module-delete" @click.stop="handleDelete(index)">
              <i class="bi bi-trash"></i>
            </div>
          </div>

          <!-- 模块内容预览 -->
          <component
            :is="getPreviewComponent(module.code)"
            :module="module"
          />
        </div>
      </draggable>
    </div>
  </div>
</template>

<script>
import SlideShowPreview from './previews/SlideShow.vue'
import Image100Preview from './previews/Image100.vue'
import IconsPreview from './previews/Icons.vue'
import ProductPreview from './previews/Product.vue'
import CategoryPreview from './previews/Category.vue'
import LatestPreview from './previews/Latest.vue'

export default {
  name: 'ModulePreview',

  components: {
    SlideShowPreview,
    Image100Preview,
    IconsPreview,
    ProductPreview,
    CategoryPreview,
    LatestPreview
  },

  props: {
    modules: {
      type: Array,
      required: true
    },
    editingIndex: {
      type: Number,
      required: true
    }
  },

  data() {
    return {
      dragOptions: {
        animation: 300,
        group: 'modules'
      }
    }
  },

  methods: {
    getPreviewComponent(code) {
      const map = {
        slideshow: 'SlideShowPreview',
        image100: 'Image100Preview',
        icons: 'IconsPreview',
        product: 'ProductPreview',
        category: 'CategoryPreview',
        latest: 'LatestPreview'
      }
      return map[code]
    },

    handleModuleClick(index) {
      this.$emit('module-selected', index)
    },

    handleDelete(index) {
      this.$emit('module-deleted', index)
    },

    handleReorder(evt) {
      this.$emit('modules-reordered', {
        oldIndex: evt.oldIndex,
        newIndex: evt.newIndex
      })
    }
  }
}
</script>
