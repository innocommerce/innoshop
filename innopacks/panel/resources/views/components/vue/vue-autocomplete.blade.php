<template id="autocomplete-template">
  <div style="position: relative; margin-top: 15px;">
    <div class="input-group wp-400">
      <el-autocomplete
          v-model="keyword"
          size="small"
          class="wp-400"
          value-key="name"
          :fetch-suggestions="querySearchAsync"
          placeholder="搜索"
          @select="handleSelect"
      >
        <template #prepend><i class="bi bi-search"></i></template>
      </el-autocomplete>
    </div>
    <div class="bg-light p-2 wp-400 border border-top-0" style="height: 150px; overflow: auto;" v-if="items.length">
      <div v-for="(item, itemIndex) in items" :key="item.id">
        <i class="bi bi-dash-circle-fill" @click="removeItem(itemIndex)"></i> @{{ item.name }}
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
    setup(props) {
      const keyword = ref('');

      const querySearchAsync = async (query, cb) => {
        let url = '';
        switch (props.type) {
          case 'product':
            url = urls.api_base + '/products/autocomplete?keyword=';
            break;
          case 'category':
            url = urls.api_base + '/categories/autocomplete?keyword=';
            break;
          case 'brand':
            url = urls.api_base + '/brands/autocomplete?keyword=';
            break;
          case 'page':
            url = urls.api_base + '/pages/autocomplete?keyword=';
            break;
          default:
            return;
        }

        try {
          const response = await fetch(url + encodeURIComponent(query));
          const data = await response.json();
          cb(data.data || []);
        } catch (error) {
          console.error('Error fetching autocomplete data:', error);
        }
      };

      const handleSelect = (item) => {
        if (!props.items.find(v => v.id === item.id)) {
          props.items.push({id: item.id, name: item.name});
        }
        keyword.value = '';
      };

      const removeItem = (index) => {
        props.items.splice(index, 1);
      };

      return {
        keyword,
        querySearchAsync,
        handleSelect,
        removeItem
      };
    }
  };
</script>