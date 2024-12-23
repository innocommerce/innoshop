@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.states'))

@push('header')
@endpush

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-body">

    <x-panel-criteria :criteria="$criteria ?? []" :action="panel_route('states.index')" />

    @if ($states)
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>{{ __('panel/common.id') }}</th>
            <th>{{ __('panel/state.name') }}</th>
            <th>{{ __('panel/state.code') }}</th>
            <th>{{ __('panel/state.country_code') }}</th>
            <th>{{ __('panel/state.position') }}</th>
            <th>{{ __('panel/state.active') }}</th>
            <th>{{ __('panel/common.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach($states as $item)
          <tr>
            <td>{{ $item['id'] }}</td>
            <td>{{ $item['name'] }}</td>
            <td>{{ $item['code'] }}</td>
            <td>{{ $item['country_code'] }}</td>
            <td>{{ $item['position'] }}</td>
            <td>
              @include('panel::shared.list_switch', ['value' => $item->active, 'url' => panel_route('states.active',
              $item->id)])
            </td>
            <td>
              <div class="d-flex gap-1">
                <el-button size="small" @click="edit({{ $item->id }})" plain type="primary">{{ __('panel/common.edit')}}
                </el-button>
                <form ref="deleteForm" action="{{ panel_route('states.destroy', [$item->id]) }}" method="POST"
                  class="d-inline">
                  @csrf
                  @method('DELETE')
                  <el-button size="small" type="danger" plain @click="open({{$item->id}})">{{
                    __('panel/common.delete')}}</el-button>
                </form>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    {{ $states->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
    @else
    <x-common-no-data />
    @endif
  </div>

  <el-drawer v-model="drawer" size="500" @close="close">
    <template #header>
      <div class="text-dark fs-4">{{ __('panel/menu.states') }}</div>
    </template>
    <el-form ref="formRef" label-position="top" :model="form" :rules="rules" label-width="auto" status-icon>
      <el-form-item label="{{ __('panel/common.name') }}" prop="name">
        <el-input v-model="form.name" placeholder="名称"></el-input>
      </el-form-item>

      <el-form-item label="编码" prop="code">
        <el-input v-model="form.code" placeholder="编码"></el-input>
      </el-form-item>

      <el-form-item label="国家代码" prop="country_id">
        <select v-model="form.country_id" class="form-control"
          @change="form.country_code = countries.find(item => item.id == form.country_id).code">
          <option v-for="item in countries" :value="item . id">@{{ item.name }}</option>
        </select>
      </el-form-item>

      <el-form-item label="排序" prop="position">
        <el-input v-model="form.position" placeholder="排序"></el-input>
      </el-form-item>

      <el-form-item label="状态" prop="active">
        <el-switch v-model="form.active" :active-value="1" :inactive-value="0"></el-switch>
      </el-form-item>
    </el-form>

    <template #footer>
      <div style="flex: auto">
        <el-button @click="drawer = false">{{ __('panel/common.close') }}</el-button>
        <el-button type="primary" @click="submit">{{ __('panel/common.btn_save') }}</el-button>
      </div>
    </template>
  </el-drawer>
</div>
@endsection
@push('footer')
<script>
  const api = @json(panel_route('states.index'));
  const { createApp, ref, reactive, onMounted, getCurrentInstance } = Vue;
  const { ElMessageBox, ElMessage } = ElementPlus;
  const listApp = createApp({
  setup() {
  const countries = ref([])
  const drawer = ref(false)
  const { proxy } = getCurrentInstance();
  const form = reactive({
  id: 0,
  name: '',
  code: '',
  country_code: '',
  country_id: '',
  position: '0',
  active: 1
  })

  const rules = {

  }

  onMounted(() => {
  getCountries()
  })

  const edit = (id) => {
  drawer.value = true
  axios.get(`${api}/${id}`).then((res) => {
  Object.keys(res).forEach(key => form.hasOwnProperty(key) && (form[key] = res[key]));
  })
  }

  const submit = () => {
  const url = form.id ? `${api}/${form.id}` : api
  const method = form.id ? 'put' : 'post'
  axios[method](url, form).then((res) => {
  drawer.value = false
  inno.msg(res.message)
  window.location.reload()
  })
  }

  const close = () => {
  proxy.$refs.formRef.resetFields()
  }

  const create = () => {
  drawer.value = true
  }

  const getCountries = () => {
  axios.get('{{ front_route('countries.index') }}').then(function(res) {
  countries.value = res.data;
  });
  }
  const deleteForm = ref(null);

  const open = (itemId) => {
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
  const deletUrl = urls.base_url + '/states/' + itemId;
  deleteForm.value.action = deletUrl;
  deleteForm.value.submit();
  })
  .catch(() => {

  });
  };
  const exportFuns = {
  drawer,
  form,
  edit,
  rules,
  close,
  submit,
  create,
  countries,
  deleteForm,
  open
  }

  window.app = exportFuns
  return exportFuns;
  }
  })

  listApp.use(ElementPlus);
  listApp.mount('#app');
</script>
@endpush