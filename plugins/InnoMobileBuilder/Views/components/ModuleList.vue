<template>
  <div class="module-wrap">
    <div class="c-title">模块列表</div>
    <draggable
      class="modules-list dragArea"
      :options="dragOptions"
      :list="modules"
      @end="handleDragEnd"
    >
      <div
        v-for="(module, index) in modules"
        :key="index"
        class="list-item"
      >
        <div class="icon">
          <i class="ds-icon" v-html="module.icon"></i>
        </div>
        <div class="name">{{ module.title }}</div>
      </div>
    </draggable>
  </div>
</template>

<script>
export default {
  name: 'ModuleList',

  props: {
    modules: {
      type: Array,
      required: true
    }
  },

  data() {
    return {
      dragOptions: {
        group: {
          name: 'modules',
          pull: 'clone',
          put: false
        }
      }
    }
  },

  methods: {
    handleDragEnd(evt) {
      this.$emit('module-selected', evt.newIndex);
    }
  }
}
</script>
