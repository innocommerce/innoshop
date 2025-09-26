<!-- Logistics Information Settings -->
<div class="tab-pane fade" id="tab-setting-logistics-information">
  <div class="container">
    <div class="row">
      <div class="ml-4" id="logistics-form">
        <div class="row col-7">
          <table class="table table-response align-middle table-bordered">
            <thead>
              <tr>
                <th>{{ __('panel/setting.express_company') }}</th>
                <th>{{ __('panel/setting.express_code') }}</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item,index) in text" :key="index">
                <td data-title="State" class="col-5">
                  <el-input v-model="item.company" placeholder="{{ __('panel/setting.express_company') }}" />
                </td>
                <td data-title="Remark" class="col-5">
                  <el-input v-model="item.code" placeholder="{{ __('panel/setting.express_code_hint') }}" />
                </td>
                <td data-title="Update Time" class="col-1">
                  <i class="bi bi-x-circle text-danger cursor-pointer" @click="removeInput(index)"></i>
                </td>
              </tr>
              <tr>
                <td></td>
                <td></td>
                <td><i @click="addInput" class="bi bi-plus-circle cursor-pointer"></i></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <input type="hidden" name="logistics" :value="JSON.stringify(text)" />

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Ensure Vue and ElementPlus are available
      if (typeof Vue === 'undefined' || typeof ElementPlus === 'undefined') {
        console.error('Vue.js or ElementPlus is not loaded');
        return;
      }

      const {createApp, ref, watch, onMounted} = Vue;
      const {CirclePlusFilled} = ElementPlus;

      // Get logistics data with better error handling
      let logisticsData = [];
      try {
        const rawData = @json(system_setting('logistics'));
        logisticsData = Array.isArray(rawData) ? rawData : [];
      } catch (error) {
        console.error('Error loading logistics data:', error);
        logisticsData = [];
      }

      const logisticsApp = createApp({
        setup() {
          // Initialize with proper data structure
          const text = ref(logisticsData.map(logistic => ({
            company: logistic.company || '',
            code: logistic.code || ''
          })));

          // Ensure we have at least one empty row if no data exists
          if (text.value.length === 0) {
            text.value.push({
              company: '',
              code: ''
            });
          }

          const addInput = () => {
            text.value.push({
              company: '',
              code: ''
            });
          };

          const removeInput = (index) => {
            if (text.value.length > 1) {
              text.value.splice(index, 1);
            }
          };

          // Watch for changes and update hidden input
          watch(text, (newValue) => {
            try {
              const logisticsInput = document.querySelector('input[name="logistics"]');
              if (logisticsInput) {
                // Filter out empty entries before saving
                const filteredData = newValue.filter(item =>
                  item.company && item.company.trim() !== '' &&
                  item.code && item.code.trim() !== ''
                );
                logisticsInput.value = JSON.stringify(filteredData);
              }
            } catch (error) {
              console.error('Error updating logistics input:', error);
            }
          }, { deep: true });

          // Initialize the hidden input on mount
          onMounted(() => {
            try {
              const logisticsInput = document.querySelector('input[name="logistics"]');
              if (logisticsInput) {
                const filteredData = text.value.filter(item =>
                  item.company && item.company.trim() !== '' &&
                  item.code && item.code.trim() !== ''
                );
                logisticsInput.value = JSON.stringify(filteredData);
              }
            } catch (error) {
              console.error('Error initializing logistics input:', error);
            }
          });

          return {
            text,
            addInput,
            removeInput
          };
        }
      });

      try {
        logisticsApp.use(ElementPlus);
        logisticsApp.mount('#logistics-form');
      } catch (error) {
        console.error('Error mounting logistics Vue app:', error);
      }
    });
  </script>
</div>
