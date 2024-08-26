@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.regions'))

@push('header')
<script src="{{ asset('vendor/vue/vue.global' . (!config('app.debug') ? '.prod' : '') . '.js') }}"></script>
<script src="{{ asset('vendor/element-plus/index.full.js') }}"></script>
<script src="{{ asset('vendor/element-plus/icons.min.js') }}"></script>
@endpush

@section('page-title-right')
  <button type="button" class="btn btn-primary btn-add" onclick="app.create()"><i class="bi bi-plus-square"></i> {{ __('panel/common.create') }}</button>
@endsection

@section('content')
  <div class="card h-min-600" id="app">
    <div class="card-body">
      @if ($regions)
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
          <tr>
            <td>{{ __('panel/common.id')}}</td>
            <td>{{ __('panel/region.name')}}</td>
            <td>{{ __('panel/region.description')}}</td>
            <td>{{ __('panel/common.active')}}</td>
            <td>{{ __('panel/common.actions')}}</td>
          </tr>
          </thead>
          <tbody>
          @foreach($regions as $item)
            <tr>
              <td>{{ $item['id'] }}</td>
              <td>{{ $item['name'] }}</td>
              <td>{{ $item['description'] }}</td>
              <td>
                @include('panel::shared.list_switch', ['value' => $item->active, 'url' => panel_route('regions.active', $item->id)])
              </td>
              <td>
                <button type="button" class="btn btn-sm btn-outline-primary" @click="edit({{ $item->id }})">{{ __('panel/common.edit')}}</button>
                <form action="{{ panel_route('regions.destroy', [$item->id]) }}" method="POST" class="d-inline">
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
      {{ $regions->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
      @else
      <x-common-no-data />
      @endif
    </div>

    <el-drawer v-model="drawer" size="500" @close="close">
      <template #header><div class="text-dark fs-4">{{ __('panel/menu.regions') }}</div></template>
      <el-form
        ref="formRef"
        label-position="top"
        :model="form"
        :rules="rules"
        label-width="auto"
        status-icon
      >
        <el-form-item label="{{ __('panel/region.name') }}" prop="name">
          <el-input v-model="form.name" placeholder="{{ __('panel/region.name') }}"></el-input>
        </el-form-item>
        <el-form-item label="{{ __('panel/region.description') }}" prop="description">
          <el-input v-model="form.description" placeholder="{{ __('panel/region.description') }}"></el-input>
        </el-form-item>
        <el-form-item label="{{ __('panel/region.position') }}" prop="position">
          <el-input v-model="form.position" placeholder="{{ __('panel/region.position') }}"></el-input>
        </el-form-item>
        <el-form-item label="{{ __('panel/region.region_states') }}" prop="region_states">
          <table class="table table-bordered regions-table">
            <thead>
              <tr>
                <th width="40%">国家/地区</th>
                <th width="40%">省份</th>
                <th width="20%" class="text-end"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in form.region_states" :key="index">
                <td>
                  <select class="form-select form-select-sm country-select" v-model="item.country_id" @change="getZones(item.country_id, index)" required>
                    <option v-for="country in countries" :key="country.id" :value="country.id">@{{ country.name }}</option>
                  </select>
                </td>
                <td>
                  <select class="form-select form-select-sm" v-model="item.state_id" required>
                    <option v-for="state in item.states" :key="state.id" :value="state.id">@{{ state.name }}</option>
                  </select>
                </td>
                <td class="text-end">
                  <el-button type="danger" @click="form.region_states.splice(index, 1)">{{ __('panel/common.delete')}}</el-button>
                </td>
              </tr>
            </tbody>
            <tfoot>
            <tr>
              <td colspan="3" class="text-end">
                <el-button type="primary" @click="addRegionState">{{ __('panel/common.add') }}</el-button>
              </td>
            </tr>
            </tfoot>
          </table>
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
    const { createApp, ref, reactive, onMounted, getCurrentInstance } = Vue
    const listApp = createApp({
      setup() {
        const countryCode = @json(old('country_code', system_setting('country_code')));
        const stateCode = @json(old('state_code', system_setting('state_code')));
        const drawer = ref(false)
        const { proxy } = getCurrentInstance();
        const countries = ref([])
        let states = []
        const form = reactive({
          id: 0,
          name: '',
          description: '',
          position: 0,
          region_states: []
        })

        const rules = {

        }

        onMounted(() => {
          getCountries()
        })

        const edit = (id) => {
          drawer.value = true
          axios.get(`/{{ panel_name() }}/regions/${id}`).then((res) => {
            Object.keys(res).forEach(key => form.hasOwnProperty(key) && (form[key] = res[key]));

            form.region_states.forEach((item, index) => {
              getZones(item.country_id, index, item.state_id)
            })
          })
        }

        const addRegionState = () => {
          form.region_states.push({
            country_id: countries.value[0].id,
            state_id: states[0].id,
            states: states
          })
        }

        const getCountries = () => {
          axios.get('{{ front_route('countries.index') }}').then(function(res) {
            countries.value = res.data;

            axios.get('{{ front_route('countries.index') }}/' + res.data[0].id).then(function(res) {
              states = res.data;
            });
          });
        }

        const getZones = (countryId, index, id = null) => {
          axios.get('{{ front_route('countries.index') }}/' + countryId).then(function(res) {
            var data = res.data;
            if (!data.length) {
              data = [{ id: '', name: '无' }]
            }

            form.region_states[index].states = data;
            form.region_states[index].state_id = id || data[0].id
          });
        }

        const submit = () => {
          const url = form.id ? `/{{ panel_name() }}/regions/${form.id}` : '{{ panel_route('regions.store') }}'
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

        const exportFuns = {
          drawer,
          form,
          edit,
          rules,
          close,
          submit,
          create,
          getZones,
          countries,
          addRegionState,
        }

        window.app = exportFuns
        return exportFuns;
      }
    })

    listApp.use(ElementPlus);
    listApp.mount('#app');
  </script>
@endpush