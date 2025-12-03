<template id="input-lang-template">
 <div class="vue-input-lang-wrapper">
  @foreach (locales() as $locale)
  <div class="input-group mb-2">
   <span class="input-group-text flag-input-group-text">
    <img src="{{ asset('images/flag/'. $locale['code'].'.png') }}" 
     class="flag-icon" 
     alt="{{ $locale['name'] }}"
     onerror="this.style.display='none';">
   </span>

   <input type="text" v-if="!isTextarea" placeholder="{{ $locale['name'] }}" class="form-control"
    v-bind:value="modelValue['{{ $locale['code'] }}']"
    v-on:input="valueChanged('{{ $locale['code'] }}', $event.target.value)" />

   <textarea v-else placeholder="{{ $locale['name'] }}" class="form-control"
    rows="3"
    v-bind:value="modelValue['{{ $locale['code'] }}']"
    v-on:input="valueChanged('{{ $locale['code'] }}', $event.target.value)"></textarea>
  </div>
  @endforeach
 </div>
</template>

<style>
.vue-input-lang-wrapper {
  width: 100%;
}

.vue-input-lang-wrapper .input-group {
  width: 100%;
}

.vue-input-lang-wrapper .flag-input-group-text {
  width: 50px;
  padding: 0.375rem 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f8f9fa;
  border: 1px solid #ced4da;
  border-right: none;
}

.vue-input-lang-wrapper .flag-icon {
  width: 20px;
  height: 15px;
  object-fit: cover;
  border-radius: 2px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.vue-input-lang-wrapper .form-control {
  border-left: none;
}

.vue-input-lang-wrapper .form-control:focus {
  border-left: 1px solid #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
</style>

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