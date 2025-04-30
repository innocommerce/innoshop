<template id="vue-image">
  <div class="set-product-img wh-70 image-upload-container" @click="updateImages">
    <img v-if="modelValue" :src="thumbnail(modelValue)" class="img-fluid">
    <i v-else class="bi bi-plus fs-1 text-muted"></i>
  </div>
</template>

<script>
  const VueImage = {
    template: '#vue-image',

    props: {
      modelValue: {
        type: String,
        default: ''
      }
    },

    emits: ['update:model-value'],

    data: function () {
      return {
        //
      }
    },

    methods: {
      updateImages() {
        const self = this;
        inno.fileManagerIframe(function(file) {
          if (file) {
            const imagePath = file.url || file.path;
            self.$emit('update:model-value', imagePath);
          }
        }, {
          type: "image",
          multiple: false
        });
      },

      thumbnail(path) {
        if (!path) return '';
        if (path.startsWith('http')) return path;
        if (path.startsWith('/static')) return path;
        return `/storage/${path.replace(/^\//, '')}`;
      }
    }
  };
</script>

<style>
  .image-upload-container {
    border: 2px dashed #ccc;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 5px;
    min-height: 70px;
    min-width: 70px;
  }

  .image-upload-container:hover {
    border-color: #6c757d;
    background-color: #f8f9fa;
  }

  .image-upload-container img {
    max-height: 100%;
    max-width: 100%;
    border-radius: 8px;
    object-fit: contain;
  }
</style>
