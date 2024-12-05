<template id="input-lang-template">
 <div>
  @foreach (locales() as $locale)
  <div class="input-group wp-400">
   <span class="input-group-text wp-100 px-1">
    {{ $locale['name'] }}
   </span>

   <input type="text" v-if="!isTextarea" placeholder="{{ $locale['name'] }}" class="form-control wp-400"
    v-bind:value="modelValue['{{ $locale['code'] }}']"
    v-on:input="valueChanged('{{ $locale['code'] }}', $event.target.value)" />

   <textarea v-else placeholder="{{ $locale['name'] }}" class="form-control wp-400"
    v-bind:value="modelValue['{{ $locale['code'] }}']"
    v-on:input="valueChanged('{{ $locale['code'] }}', $event.target.value)"></textarea>
  </div>
  @endforeach
 </div>
</template>

<script>
 const VueInputLang = {
    template: '#input-lang-template',
    props: {
      modelValue: {  
        type: Object,
        default: () => ({})
      },
      isTextarea: {
        type: Boolean,
        default: false
      },
    },

    data() {
      return {
        languages: @json(locales()),
        internalValues: {}
      };
    },

    created() {
      this.languages.forEach(e => {
        this.internalValues[e.code] = this.modelValue[e.code] || '';  
      });

      this.$emit('update:modelValue', this.internalValues);
    },

    watch: {
      internalValues(newValue) {
        this.$emit('update:modelValue', newValue);
      }
    },

    methods: {
      valueChanged(code, newValue) {
        this.internalValues[code] = newValue;
        this.$emit('update:modelValue', this.internalValues);
      }
    }
  }
</script>