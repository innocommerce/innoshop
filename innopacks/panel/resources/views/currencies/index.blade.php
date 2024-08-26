@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.currencies'))

@push('header')
<script src="{{ asset('vendor/vue/vue.global' . (!config('app.debug') ? '.prod' : '') . '.js') }}"></script>
<script src="{{ asset('vendor/element-plus/index.full.js') }}"></script>
<script src="{{ asset('vendor/element-plus/icons.min.js') }}"></script>
@endpush

@section('page-title-right')
  <button type="button" class="btn btn-primary btn-add" onclick="app.create()">
    <i class="bi bi-plus-square"></i> {{ __('panel/common.create') }}
  </button>
@endsection

@section('content')
  <div class="card h-min-600" id="app">
    <div class="card-body">
      @if ($currencies->count())
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
          <tr>
            <th>{{ __('panel/common.id') }}</th>
            <th>{{ __('panel/currency.name') }}</th>
            <th>{{ __('panel/currency.code') }}</th>
            <th>{{ __('panel/currency.symbol_left') }}</th>
            <th>{{ __('panel/currency.symbol_right') }}</th>
            <th>{{ __('panel/currency.decimal_place') }}</th>
            <th>{{ __('panel/currency.value') }}</th>
            <th>{{ __('panel/common.active') }}</th>
            <th>{{ __('panel/common.actions') }}</th>
          </tr>
          </thead>
          <tbody>
          @foreach($currencies as $item)
            <tr>
              <td>{{ $item->id }}</td>
              <td>{{ $item->name }}</td>
              <td>{{ $item->code }}</td>
              <td>{{ $item->symbol_left }}</td>
              <td>{{ $item->symbol_right }}</td>
              <td>{{ $item->decimal_place }}</td>
              <td>{{ $item->value }}</td>
              <td>
                @include('panel::shared.list_switch', ['value' => $item->active, 'url' => panel_route('currencies.active', $item->id)])
              </td>
              <td>
                <button type="button" class="btn btn-sm btn-outline-primary" @click="edit({{ $item->id }})">{{ __('panel/common.edit')}}</button>
                <form action="{{ panel_route('currencies.destroy', [$item->id]) }}" method="POST" class="d-inline">
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
      {{ $currencies->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
      @else
      <x-common-no-data />
      @endif
    </div>

    <el-drawer v-model="drawer" size="500" @close="close">
      <template #header><div class="text-dark fs-4">{{ __('panel/menu.currencies') }}</div></template>
      <el-form
        ref="formRef"
        label-position="top"
        :model="form"
        :rules="rules"
        label-width="auto"
        status-icon
      >
        <el-form-item label="{{ __('panel/currency.name') }}" prop="name">
          <el-input size="large" v-model="form.name" placeholder="{{ __('panel/currency.name') }}"></el-input>
        </el-form-item>
        <el-form-item label="{{ __('panel/currency.code') }}" prop="code">
          <el-input size="large" v-model="form.code" placeholder="{{ __('panel/currency.code') }}"></el-input>
        </el-form-item>
        <el-form-item label="{{ __('panel/currency.symbol_left') }}" prop="symbol_left">
          <el-input size="large" v-model="form.symbol_left" placeholder="{{ __('panel/currency.symbol_left') }}"></el-input>
        </el-form-item>
        <el-form-item label="{{ __('panel/currency.symbol_right') }}" prop="symbol_right">
          <el-input size="large" v-model="form.symbol_right" placeholder="{{ __('panel/currency.symbol_right') }}"></el-input>
        </el-form-item>
        <el-form-item label="{{ __('panel/currency.decimal_place') }}" prop="decimal_place">
          <el-input size="large" v-model="form.decimal_place" placeholder="{{ __('panel/currency.decimal_place') }}"></el-input>
        </el-form-item>
        <el-form-item label="{{ __('panel/currency.value') }}" prop="value">
          <el-input size="large" v-model="form.value" placeholder="{{ __('panel/currency.value') }}"></el-input>
        </el-form-item>
        <el-form-item label="{{ __('panel/currency.active') }}" prop="active">
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
    const { createApp, ref, reactive, onMounted, getCurrentInstance } = Vue
    const api = @json(panel_route('currencies.index'));
    const listApp = createApp({
      setup() {
        const drawer = ref(false)
        const { proxy } = getCurrentInstance();
        const form = reactive({
          id: 0,
          name: '',
          code: '',
          symbol_left: '',
          symbol_right: '',
          decimal_place: '',
          value: '',
          active: 1,
        })

        const rules = {

        }

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

        const exportFuns = {
          drawer,
          form,
          edit,
          rules,
          close,
          submit,
          create,
        }

        window.app = exportFuns
        return exportFuns;
      }
    })

    listApp.use(ElementPlus);
    listApp.mount('#app');

    $(function () {
      $('.btn-add').click(function () {
        app.drawer.value = true
      })
    })
  </script>
@endpush