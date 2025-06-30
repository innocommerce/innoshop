@extends('panel::layouts.app')
@section('body-class', 'page-product')
@section('title', __('panel/menu.products'))

@section('page-title-right')
  <a href="{{ panel_route('products.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i> {{
  __('panel/common.create') }}</a>
@endsection

@section('content')
  <div class="card h-min-600" id="app">
    <div class="card-body">

      <x-panel-data-criteria :criteria="$criteria ?? []" :action="panel_route('products.index')"/>

      <div class="mb-3 p-3 bg-light rounded border" id="products-toolbar">
        <div class="d-flex d-md-flex flex-column flex-md-row justify-content-md-between align-items-start gap-3">
          @include('panel::products.bulk.actions')

          <div class="toolbar-right d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2 gap-md-3">
            <x-panel-data-info :paginator="$products ?? null"/>
            <x-panel-data-sorter :options="$sortOptions ?? []"/>
          </div>
        </div>
      </div>

      @if ($products->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
            <tr>
              <th><input class="form-check-input" @click="checkAll" type="checkbox" ref="checkAllBox"></th>
              <th>{{ __('panel/common.id') }}</th>
              <th class="wp-100">{{ __('panel/common.image') }}</th>
              <th>{{ __('panel/common.name') }}</th>
              <th>{{ __('panel/product.price') }}</th>
              <th>{{ __('panel/product.quantity') }}</th>
              <th>{{ __('panel/common.created_at') }}</th>
              <th>{{ __('panel/common.active') }}</th>
              <th>{{ __('panel/common.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($products as $product)
              <tr>
                <td><input class="form-check-input" type="checkbox" :value="{{ $product->id }}" v-model="checkedIds">
                </td>
                <td>{{ $product->id }}</td>
                <td>
                  <div class="d-flex align-items-center justify-content-center wh-50 border">
                    <a href="{{ $product->url }}" target="_blank">
                      <img src="{{ $product->image_url }}" class="img-fluid"
                           alt="{{ $product->fallbackName() }}">
                    </a>
                  </div>
                </td>
                <td>
                  <a href="{{ $product->url }}" class="text-decoration-none" target="_blank" data-bs-toggle="tooltip"
                     title="{{ $product->fallbackName() }}">
                    {{ sub_string($product->fallbackName(),28) }}
                  </a>
                  @if($product->isMultiple())
                    &nbsp;<span class="text-bg-success px-1">M</span>
                  @endif
                </td>
                <td>{{ currency_format($product->masterSku->price ?? 0) }}</td>
                <td>{{ $product->totalQuantity() }}</td>
                <td>{{ $product->created_at }}</td>
                <td>@include('panel::shared.list_switch', ['value' => $product->active, 'url' =>panel_route('products.active', $product->id)])</td>
                <td>
                  <div class="d-flex gap-2">
                    <div>
                      <a href="{{ panel_route('products.edit', [$product->id]) }}">
                        <el-button size="small" plain type="primary">{{
                        __('panel/common.edit')}}</el-button>
                      </a>
                    </div>
                    <div>
                      <a href="{{ panel_route('products.copy', [$product->id]) }}">
                        <el-button size="small" plain type="warning">{{
                        __('panel/common.copy')}}</el-button>
                      </a>
                    </div>
                    <div>
                      <form ref="deleteForm" action="{{ panel_route('products.destroy', [$product->id]) }}"
                            method="POST"
                            class="d-inline">
                        @csrf
                        @method('DELETE')
                        <el-button size="small" type="danger" plain @click="open({{$product->id}})">{{
                        __('panel/common.delete')}}</el-button>
                      </form>
                    </div>
                  </div>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
        {{ $products->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
      @else
        <x-common-no-data/>
      @endif

      {{-- Bulk Actions Modals --}}
      @include('panel::products.bulk.modals')

    </div>
  @endsection

@push('footer')

  <script>
    const {createApp, ref, reactive, watch} = Vue;
    const {ElMessageBox, ElMessage, ElLoading} = ElementPlus;

    const app = createApp({
      setup() {
        const deleteForm = ref(null);
        const checkedIds = ref([]);
        const checkAllBox = ref(null);
        const categoryCascaderOptions = @json($categoryOptions);

        const dialogVisible = ref({
          price: false,
          categories: false,
          quantity: false,
          publish: false,
          unpublish: false,
        });

        const bulkFormData = ref({
          price: {mode: 'reset', value: null},
          categories: [],
          quantity: {mode: 'reset', value: null},
        });

        const open = (index) => {
          ElMessageBox.confirm(
            '{{ __("common/base.hint_delete") }}',
            '{{ __("common/base.cancel") }}',
            {
              confirmButtonText: '{{ __("common/base.confirm")}}',
              cancelButtonText: '{{ __("common/base.cancel")}}',
              type: 'warning',
            }
          ).then(() => {
            deleteForm.value.action = urls.base_url + '/products/' + index;
            deleteForm.value.submit();
          }).catch(() => {
          });
        };

        const deleteAll = () => {
          if (checkedIds.value.length === 0) {
            ElMessage({
              type: 'warning',
              message: '{{ __("panel/common.select_items") }}',
            });
            return;
          }

          ElMessageBox.confirm(
            `{{ __("panel/product.bulk_delete_confirm") }}（${checkedIds.value.length} {{ __("panel/product.items") }}）`,
            '{{ __("common/base.hint_delete") }}',
            {
              confirmButtonText: '{{ __("common/base.confirm")}}',
              cancelButtonText: '{{ __("common/base.cancel")}}',
              type: 'warning',
              dangerouslyUseHTMLString: true,
            }
          ).then(() => {
            const loading = ElLoading.service({
              lock: true,
              text: '{{ __("panel/product.deleting") }}',
              background: 'rgba(0, 0, 0, 0.7)'
            });

            axios.delete("{{ panel_route('products.destroy.batch') }}", {
              data: {
                ids: checkedIds.value
              }
            }).then(response => {
              ElMessage({
                type: 'success',
                message: response.message,
              });
              setTimeout(() => {
                window.location.reload();
              }, 1000);
            }).catch(error => {
              let errorMessage = '{{ __("common/base.error") }}';
              
              // Standard axios error handling
              if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
              } else if (error.message) {
                errorMessage = error.message;
              }
              
              ElMessage({
                type: 'error',
                message: errorMessage,
                duration: 5000
              });
            }).finally(() => {
              loading.close();
            });
          }).catch(() => {
            // User cancelled deletion
          });
        };

        // Debounce function
        const debounce = (func, wait) => {
          let timeout;
          return function executedFunction(...args) {
            const later = () => {
              clearTimeout(timeout);
              func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
          };
        };

        const bulkAction = debounce((action) => {
          if (checkedIds.value.length === 0) {
            ElMessage({
              type: 'warning',
              message: '{{ __("panel/common.select_items") }}',
            });
            return;
          }

          if (dialogVisible.value.hasOwnProperty(action)) {
            dialogVisible.value[action] = true;
          } else {
            console.log('Action not implemented:', action);
          }
        }, 300);

        const submitBulkUpdate = (action) => {
          // Basic validation
          if (checkedIds.value.length === 0) {
            ElMessage({
              type: 'warning',
              message: '{{ __("panel/common.select_items") }}',
            });
            return;
          }

          // Validate operations with numeric input
          if (['price', 'quantity'].includes(action)) {
            const formData = bulkFormData.value[action];
            if (!formData.value || formData.value <= 0) {
              ElMessage({
                type: 'warning',
                message: '{{ __("panel/product.enter_valid_value") }}',
              });
              return;
            }
          }

          const payload = {
            action: action,
            ids: checkedIds.value,
            data: bulkFormData.value[action] || {}
          };

          // Show loading state
          const loading = ElLoading.service({
            lock: true,
            text: '{{ __("panel/product.processing") }}',
            background: 'rgba(0, 0, 0, 0.7)'
          });

          axios.post("{{ panel_route('products.bulk.update') }}", payload).then(response => {
              if (response && response.success) {
                ElMessage({
                  type: 'success', 
                  message: response.message
                });
                dialogVisible.value[action] = false;
                
                // Reset form data
                if (bulkFormData.value[action] && typeof bulkFormData.value[action] === 'object') {
                  if (bulkFormData.value[action].mode) {
                    bulkFormData.value[action].mode = 'reset';
                  }
                  if (bulkFormData.value[action].value) {
                    bulkFormData.value[action].value = null;
                  }
                }
                setTimeout(() => {
                  window.location.reload();
                }, 1000);
              } else {
                throw new Error(response?.message || '响应格式错误');
              }
            }).catch(error => {
              let errorMessage = '{{ __("common/base.error") }}';
              
              // Standard axios error handling
              if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
              } else if (error.message) {
                errorMessage = error.message;
              }
              
              ElMessage({
                type: 'error',
                message: errorMessage,
                duration: 5000
              });
            })
            .finally(() => {
              loading.close();
            });
        };

        const checkAll = () => {
          if (checkAllBox.value.checked) {
            checkedIds.value = Array.from(document.querySelectorAll('input[type="checkbox"][value]')).map(el => parseInt(el.value));
          } else {
            checkedIds.value = [];
          }
        };

        watch(checkedIds, (newVal) => {
          const allCheckboxes = document.querySelectorAll('input[type="checkbox"][value]');
          if (!allCheckboxes.length) return;

          if (newVal.length === allCheckboxes.length) {
            checkAllBox.value.checked = true;
            checkAllBox.value.indeterminate = false;
          } else if (newVal.length > 0) {
            checkAllBox.value.indeterminate = true;
            checkAllBox.value.checked = false;
          } else {
            checkAllBox.value.indeterminate = false;
            checkAllBox.value.checked = false;
          }
        });

        return {
          open,
          deleteForm,
          checkAll,
          checkedIds,
          checkAllBox,
          deleteAll,
          bulkAction,
          dialogVisible,
          bulkFormData,
          submitBulkUpdate,
          categoryCascaderOptions
        };
      }
    });
    app.use(ElementPlus);
    app.mount('#app');
  </script>

@endpush
