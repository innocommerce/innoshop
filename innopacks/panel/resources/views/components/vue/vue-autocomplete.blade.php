<template id="autocomplete-template">
  <div class="vue-autocomplete-wrapper">
    <div class="vue-autocomplete-input-box">
      <el-autocomplete
          ref="autocompleteRef"
          v-model="keyword"
          value-key="name"
          :fetch-suggestions="querySearchAsync"
          placeholder="搜索"
          trigger-on-focus
          @select="handleSelect"
          @focus="handleFocus"
          @click="handleClick"
          style="width: 100%;"
      >
        <template #prepend><i class="bi bi-search"></i></template>
      </el-autocomplete>
    </div>
    <div class="vue-autocomplete-list" v-if="items.length">
      <div class="vue-autocomplete-item" v-for="(item, itemIndex) in items" :key="item.id">
        <span class="vue-autocomplete-name">@{{ item.name }}</span>
        <button type="button" class="btn-close" @click="removeItem(itemIndex)" aria-label="删除"></button>
      </div>
    </div>
  </div>
</template>

<script>
  const VueAutocomplete = {
    template: '#autocomplete-template',
    props: {
      type: {
        type: String,
        required: true
      },
      items: {
        type: Array,
        required: true
      }
    },
    setup(props, { emit }) {
      const keyword = ref('');
      const autocompleteRef = ref(null);

      const querySearchAsync = async (query, cb) => {
        const urlMap = {
          product: urls.panel_api + '/products/autocomplete?keyword=',
          category: urls.panel_api + '/categories/autocomplete?keyword=',
          brand: urls.panel_api + '/brands/autocomplete?keyword=',
          page: urls.panel_api + '/pages/autocomplete?keyword='
        };

        const url = urlMap[props.type];
        if (!url) return;

        try {
          const response = await fetch(url + encodeURIComponent(query || ''));
          const data = await response.json();
          cb(data.data || []);
        } catch (error) {
          console.error('Error fetching autocomplete data:', error);
          cb([]);
        }
      };

      const getInputElement = () => {
        if (!autocompleteRef.value?.$el) return null;
        
        const el = autocompleteRef.value.$el;
        // Try querySelector first, then getElementsByTagName
        return el.querySelector?.('input') || el.getElementsByTagName?.('input')?.[0] || null;
      };

      const blurInput = () => {
        const inputEl = getInputElement();
        if (inputEl?.blur) {
          inputEl.blur();
        }
        // Also blur active element and focus body to ensure input loses focus
        document.activeElement?.blur?.();
        document.body?.focus?.();
      };

      const handleSelect = (item) => {
        if (!props.items.find(v => v.id === item.id)) {
          emit('update:items', [...props.items, {id: item.id, name: item.name}]);
        }
        keyword.value = '';
        
        // Blur input to ensure next click triggers focus event
        blurInput();
        // Blur again after delays to prevent re-focus
        [10, 50, 100, 200].forEach(delay => setTimeout(blurInput, delay));
      };

      const showDropdown = () => {
        const inputEl = getInputElement();
        if (!inputEl) return;

        inputEl.focus?.();
        querySearchAsync('', (suggestions) => {
          if (suggestions.length > 0 && inputEl.dispatchEvent) {
            // Trigger input event to show dropdown
            inputEl.dispatchEvent(new Event('input', { bubbles: true }));
            inputEl.dispatchEvent(new Event('focus', { bubbles: true }));
          }
        });
      };

      const handleClick = () => showDropdown();
      const handleFocus = () => showDropdown();

      const removeItem = (index) => {
        emit('update:items', props.items.filter((_, i) => i !== index));
      };

      return {
        keyword,
        autocompleteRef,
        querySearchAsync,
        handleSelect,
        handleClick,
        handleFocus,
        removeItem
      };
    },
    emits: ['update:items']
  };
</script>
