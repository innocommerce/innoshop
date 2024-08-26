@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.states'))

@push('header')
<script src="{{ asset('vendor/vue/vue.global' . (!config('app.debug') ? '.prod' : '') . '.js') }}"></script>
<script src="{{ asset('vendor/element-plus/index.full.js') }}"></script>
<script src="{{ asset('vendor/element-plus/icons.min.js') }}"></script>
@endpush

@section('content')
  <div class="card h-min-600" id="app">
    <div class="card-body">
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
                @include('panel::shared.list_switch', ['value' => $item->active, 'url' => panel_route('states.active', $item->id)])
              </td>
              <td>
                <button type="button" class="btn btn-sm btn-outline-primary" @click="edit({{ $item->id }})">{{ __('panel/common.edit')}}</button>
                <form action="{{ panel_route('states.destroy', [$item->id]) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('panel/common.delete')}}</button>
                </form>
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
      <template #header><div class="text-dark fs-4">{{ __('panel/menu.states') }}</div></template>
      <el-form
        ref="formRef"
        label-position="top"
        :model="form"
        :rules="rules"
        label-width="auto"
        status-icon
      >
        <el-form-item label="{{ __('panel/common.name') }}" prop="name">
          <el-input v-model="form.name" placeholder="名称"></el-input>
        </el-form-item>

        <el-form-item label="编码" prop="code">
          <el-input v-model="form.code" placeholder="编码"></el-input>
        </el-form-item>

        <el-form-item label="国家代码" prop="country_id">
          <select v-model="form.country_id" class="form-control" @change="form.country_code = countries.find(item => item.id == form.country_id).code">
            <option v-for="item in countries" :value="item.id">@{{ item.name }}</option>
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
    const { createApp, ref, reactive, onMounted, getCurrentInstance } = Vue
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

        const exportFuns = {
          drawer,
          form,
          edit,
          rules,
          close,
          submit,
          create,
          countries,
        }

        window.app = exportFuns
        return exportFuns;
      }
    })

    listApp.use(ElementPlus);
    listApp.mount('#app');
  </script>
@endpush
