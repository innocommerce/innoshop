<template>
  <div class="slideshow-preview">
    <img
      v-if="module.content.images.length"
      :src="module.content.images[0].image[locale]"
      class="img-fluid"
    >
  </div>
</template>

<script>
export default {
  name: 'SlideShowPreview',

  props: {
    module: {
      type: Object,
      required: true
    }
  },

  data() {
    return {
      locale: window.$locale
    }
  }
}
</script>
