<div class="card variants-box mb-3" id="attributes-box">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/attribute.attribute') }}</h5>
  </div>
  
  <div class="card-body" v-if="showAttribute">
    <table class="table table-condensed table-bordered">
      <tr>
        <td>{{ __('panel/attribute.attribute') }}</td>
        <td>{{ __('panel/attribute.attribute_value') }}</td>
        <td class="text-center align-middle">
          <button class="btn btn-primary btn-sm" type="button" @click="addAttribute">
            <i class="bi-plus-lg"></i>
          </button>
        </td>
      </tr>
      <tr v-for="(attr, index) in attributes" :key="index">
        <td class="col-5">
          <select class="form-control" v-model="attr.attribute_id" @change="onAttributeChange(index)">
            <option value="">{{ __('panel/common.please_choose') }}</option>
            <option v-for="item in allAttributes" :key="item.id" :value="item.id">@{{ item.name }}</option>
          </select>
        </td>
        <td class="col-5">
          <select class="form-control" v-model="attr.attribute_value_id">
            <option value="">{{ __('panel/common.please_choose') }}</option>
            <option v-for="value in getAttributeValues(attr.attribute_id)" :key="value.id" :value="value.id">@{{ value.name }}</option>
          </select>
        </td>
        <td class="col-1 text-center align-middle">
          <button type="button" class="btn btn-link text-danger" @click="deleteAttribute(index)">
            <i class="bi-trash"></i>
          </button>
        </td>
      </tr>
    </table>
  </div>

  <div v-else class="cursor-pointer m-3 text-primary" @click="showAttribute = true">
    <i class="bi bi-plus-square me-1"></i> {{ __('panel/attribute.set_attribute') }}
  </div>

  <input type="hidden" name="attributes" :value="JSON.stringify(attributes)">
</div>

@push('footer')
<script>
// Use data directly from backend
const attributesData = @json($all_attributes);

const attributesApp = createApp({
  setup() {
    // Initialize data
    const showAttribute = ref({{ $attribute_count ? 'true' : 'false' }});
    const attributes = ref((() => {
      let rawData = @json(old('attributes', $product->productAttributes ?? []));
      if (typeof rawData === 'string') {
        try {
          rawData = JSON.parse(rawData);
        } catch (e) {
          console.error('Error parsing attribute data:', e);
          rawData = [];
        }
      }
      return rawData.map(attr => ({
        attribute_id: attr.attribute_id || '',
        attribute_value_id: attr.attribute_value_id || ''
      }));
    })());

    // All available attributes
    const allAttributes = ref(attributesData);

    // Add new attribute
    const addAttribute = () => {
      attributes.value.push({
        attribute_id: '',
        attribute_value_id: ''
      });
    };

    // Delete attribute
    const deleteAttribute = (index) => {
      attributes.value.splice(index, 1);
      if (attributes.value.length === 0) {
        showAttribute.value = false;
      }
    };

    // Get available values for specified attribute
    const getAttributeValues = (attributeId) => {
      const attribute = allAttributes.value.find(attr => attr.id == attributeId);
      return attribute ? attribute.values : [];
    };

    // Reset attribute value when attribute changes
    const onAttributeChange = (index) => {
      const attr = attributes.value[index];
      if (!attr.attribute_id) {
        attr.attribute_value_id = '';
        return;
      }

      // Reset selection if current value is not in available values list
      const availableValues = getAttributeValues(attr.attribute_id);
      if (!availableValues.find(v => v.id == attr.attribute_value_id)) {
        attr.attribute_value_id = '';
      }
    };

    return {
      showAttribute,
      attributes,
      allAttributes,
      addAttribute,
      deleteAttribute,
      getAttributeValues,
      onAttributeChange
    };
  }
}).mount('#attributes-box');
</script>
@endpush