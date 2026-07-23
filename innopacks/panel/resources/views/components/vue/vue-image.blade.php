<template id="vue-image">
  <div class="set-product-img wh-70 image-upload-container" @click="updateImages">
    <img v-if="thumbnail(modelValue)" :src="thumbnail(modelValue)" class="img-fluid">
    <i v-else-if="modelValue" class="bi bi-image fs-1 text-success" :title="modelValue"></i>
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
        cachedThumb: ''
      }
    },

    methods: {
      updateImages() {
        const self = this;
        inno.mediaIframe(function(file) {
          if (file) {
            // Prefer the media:// reference so future renames/moves stay in sync;
            // fall back to raw path for legacy installs without a media_files row.
            const ref = file.media_reference || file.path;
            self.cachedThumb = file.thumb || file.url || '';
            self.$emit('update:model-value', ref);
          }
        }, {
          type: "image",
          multiple: false
        });
      },
      thumbnail(path) {
        if (this.cachedThumb) return this.cachedThumb;
        if (!path) return '';
        if (path.startsWith('http')) return path;
        // media://{id} references cannot be resolved client-side; show a checkmark
        // placeholder via v-else-if and let the backend render the real <img> on the storefront.
        if (path.startsWith('media://')) return '';
        const base = document.querySelector('meta[name="storage-base-url"]')?.content || '';
        return base ? base + '/' + path.replace(/^\/+/, '') : path;
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
