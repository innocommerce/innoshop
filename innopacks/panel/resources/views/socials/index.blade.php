@extends('panel::layouts.app')
@section('body-class', 'sns')

@section('title', __('panel/menu.sns'))

@push('header')

@endpush

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-header  d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">{{__('panel/sns.pl_config')}}</h5>
    <button @click.prevent="verification" type="submit" class="btn btn-primary">{{__('panel/sns.save')}}</button>
  </div>
  <div class="card-body">
    <form class="needs-validation" novalidate id="app-form" action="{{ panel_route('sns.index') }}" method="POST">
      @csrf

      <div class="container mt-1">
        <table class="table table-bordered">
          <thead>
            <tr class="bg-dark-subtle">
              <th scope="col">{{__('panel/sns.type')}}</th>
              <th scope="col">{{__('panel/sns.status')}}</th>
              <th scope='col'>{{__('panel/sns.client_id')}}</th>
              <th scope="col">{{__('panel/sns.client_secret')}}</th>
              <th scope="col">{{__('panel/sns.callback')}}</th>
              <th scope="col">{{__('panel/common.position')}}</th>
              <th scope="col">{{__('panel/common.actions')}}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in items" :key="index">
              <th scope="row">
                <select class="form-select" aria-label="Default select example" v-model="item.provider"
                  @change.prevent="updateCallbackUrl(index)">
                  <option v-for="option in availableOptions(index)" :value="option.code"
                    :selected="item.provider === option.code">
                    @{{ option.label }}
                  </option>
                </select>
                <input type="hidden" :name="'sns_type[' + index + ']'" :value="item.provider">
              </th>
              <td class="align-middle">
                <div class="form-check form-switch">
                  <input class="form-check-input " v-model="item.active" type="checkbox" role="switch"
                    :id="'switch-' + index" checked>
                  <input type="hidden" :name="'active[' + index + ']'" :value="item.active ? 'on' : 'off'">
                </div>
              </td>
              <td>
                <input :name="'client_id[' + index + ']'" type="text" class="form-control"
                  :id="'validationValue' + index" v-model="item.client_id" :class="{ 'is-invalid': item.isError }">
                <div v-if="item.isError" style="color: #dc3545">
                  {{__('panel/sns.please_enterid')}}
                </div>
              </td>
              <td>
                <input :name="'client_secret[' + index + ']'" type="text" class="form-control"
                  :id="'validationValue1' + index" v-model="item.client_secret"
                  :class="{ 'is-invalid': item.isError2 }">
                <div v-if="item.isError2" style="color: #dc3545">
                  {{__('panel/sns.please_secret')}}
                </div>
              </td>
              <td>
                <div class="input-group" style="display: flex; width: 100%;">
                  <input type="text" class="form-control" :name="'callback_url[' + index + ']'"
                    v-model="item.callback_url" style="flex-grow: 1; min-width: 280px;">
                  <button class="btn btn-light" @click.prevent="copyCallbackUrl(index)" title="复制"
                    style="flex-shrink: 0;">
                    <i class="bi bi-back" style="font-size: 16px;"></i>
                  </button>
                </div>
              </td>
              <td>
                <input style="max-width: 50px;" class="form-control" v-model="item.position" />
              </td>
              <td class="align-middle text-center">
                <el-button size="small" type="danger" plain @click="open(index)">{{__('panel/sns.delete')}}</el-button>
              </td>
            </tr>
            <tr v-if="items.length > 0 && items.length < 3">
              <td class="bordernone" style="border-left: 1px solid #dee2e6"></td>
              <td class="bordernone"></td>
              <td class="bordernone"></td>
              <td class="bordernone"></td>
              <td class="bordernone"></td>
              <td class="bordernone"></td>
              <td colspan="7" class="text-center" style="border: 1px solid #dee2e6 !important;">
                <el-button size="small" type="primary" @click.prevent="addAfter(items.length - 1)">
                  {{__('panel/sns.add')}}
                </el-button>
                <div style="clear: both;"></div>
              </td>
            </tr>
            <tr v-if="items.length === 0">
              <td colspan="7" style="text-align: center;">
                {{__('panel/sns.no_data')}}
                <el-button size="small" type="primary" @click.prevent="add">{{__('panel/sns.add')}}</el-button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </form>
  </div>
</div>
@endsection

@push('footer')
<script>
  const { createApp, ref, computed, onMounted, watch, nextTick } = Vue;
const { ElMessageBox, ElMessage } = ElementPlus;

let app = createApp({
  setup() {
    const successfully = '{{ __('panel/sns.copy_successfully') }}';
    const fields = '{{ __('panel/sns.required_fields') }}';
    const failure = '{{ __('panel/sns.failure') }}';
    const success = '{{ __('panel/sns.success') }}';
    const items = ref(@json($socials));  
    const providers = @json($providers); 

    const availableOptions = (index) => {
    const usedOptions = items.value.map(item => item.provider).filter((_, idx) => idx !== index);
    return providers.filter(option => !usedOptions.includes(option.code));
    };

    const add = () => {
      if (items.value.length < 3) {
        const nextIndex = items.value.length;
        const newOption = availableOptions(nextIndex)[0] || { code: 'facebook', label: 'Facebook' };
        const callbackUrl = `{{ panel_route('home.index') }}/${newOption.code}/callback`;
        items.value.push(createItem(newOption, nextIndex, callbackUrl));
      }
    };

    const addAfter = (index) => {
      if (items.value.length < 3) {
        const newOption = availableOptions(index + 1)[0] || { code: 'facebook', label: 'Facebook' };
        const callbackUrl = `{{ panel_route('home.index') }}/${newOption.code}/callback`;
        items.value.splice(index + 1, 0, createItem(newOption, index + 1, callbackUrl));
      }
    };

    const deleteRow = (index) => {
      items.value.splice(index, 1);
      items.value.forEach((item, newIndex) => {
        item.position = newIndex + 1;
      });
    };

    const copyCallbackUrl = async (index) => {
      const input = document.querySelector(`#app input[name='callback_url[${index}]']`);
      if (!input) return;
      const textToCopy = input.value;
      try {
        if (navigator.clipboard) {
          await navigator.clipboard.writeText(textToCopy);
        } else {
          const textarea = document.createElement('textarea');
          textarea.value = textToCopy;
          document.body.appendChild(textarea);
          textarea.select();
          document.execCommand('copy');
          document.body.removeChild(textarea);
        }
        inno.msg(successfully);
      } catch (error) {
        console.error('Failed to copy text: ', error);
      }
    };

    const updateCallbackUrl = (index) => {
      const item = items.value[index];
      item.callback_url = `{{ panel_route('home.index') }}/${item.provider}/callback`;
    };

    const isValid = computed(() => {
      return items.value.every(item => item.client_id && item.client_secret);
    });

    const verification = async () => {
      if (!isValid.value) {
        inno.msg(fields);
        return;
      }

      try {
        const formData = items.value.map(item => ({
          provider: item.provider,
          active: item.active,
          client_id: item.client_id,
          client_secret: item.client_secret,
          callback_url: item.callback_url,
          position: item.position
        }));
        await axios.post('{{ panel_route('socials.store') }}', formData);
        inno.msg(success);
      } catch (error) {
        inno.msg(failure);
        console.error(error);
      }
    };

    const createItem = (newOption, nextIndex, callbackUrl) => ({
      provider: newOption.code,
      active: true,
      client_id: '',
      client_secret: '',
      callback_url: callbackUrl,
      position: nextIndex + 1,
      isError: false,
      isError2: false
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
      )
      .then(() => {
        deleteRow(index);
      })
      .catch(() => {
      });
    };

    return {
      items,
      add,
      addAfter,
      deleteRow,
      copyCallbackUrl,
      availableOptions,
      updateCallbackUrl,
      verification,
      isValid,
      open
    };
  }
});

app.use(ElementPlus);
app.mount('#app');

</script>
@endpush