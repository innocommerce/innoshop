@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.attribute_groups'))

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
      @if ($attributes->count())
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <td>{{ __('panel/common.id')}}</td>
              <td>{{ __('panel/common.name')}}</td>
              <td>{{ __('panel/common.created_at')}}</td>
              <td>{{ __('panel/common.actions')}}</td>
            </tr>
          </thead>
          <tbody>
          @foreach($attributes as $item)
            <tr>
              <td>{{ $item->id }}</td>
              <td>{{ $item->translation->name ?? '' }}</td>
              <td>{{ $item->created_at }}</td>
              <td>
                <button type="button" class="btn btn-sm btn-outline-primary" @click="edit({{ $item->id }})">{{ __('panel/common.edit')}}</button>
                <form action="{{ panel_route('attribute_groups.destroy', [$item->id]) }}" method="POST" class="d-inline">
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
      {{ $attributes->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
      @else
      <x-common-no-data />
      @endif
    </div>

    <el-drawer v-model="drawer" size="500" @close="close">
      <template #header><div class="text-dark fs-4">{{ __('panel/menu.attribute_groups') }}</div></template>
      <el-form
        ref="formRef"
        label-position="top"
        :model="form"
        :rules="rules"
        label-width="auto"
        status-icon
      >
        <el-form-item label="" prop="name">
          <div class="w-100">
            <ul class="nav nav-tabs mb-2" role="tablist">
              <li class="nav-item" role="presentation" v-for="(locale, index) in locales" :key="locale.code">
                <button :class="['nav-link py-1', !index ? 'active' : '']" :id="locale.code" data-bs-toggle="tab" :data-bs-target="'#values-' + locale.code + '-pane'" type="button">
                  <img :src="'images/flag/' + locale.code +'.png'" class="me-2" style="width: 20px;">
                  @{{ locale.name }}
                </button>
              </li>
            </ul>
            <div class="tab-content pb-3">
              <div :class="['tab-pane fade', !index ? 'show active' : '']" :id="'values-'+ locale.code +'-pane'" role="tabpanel" v-for="(locale, index) in locales" :key="locale.code">
                <el-input size="large" v-model="form.translations[locale.code].name" placeholder="{{ __('panel/common.name')}}"></el-input>
              </div>
            </div>
          </div>
        </el-form-item>

        <el-form-item label="{{ __('panel/common.position')}}" prop="position">
          <el-input size="large" v-model="form.position" placeholder="{{ __('panel/common.position')}}"></el-input>
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
    const api = @json(panel_route('attribute_groups.index'));
    const listApp = createApp({
      setup() {
        const drawer = ref(false)
        const locales = ref(@json(locales()))
        const { proxy } = getCurrentInstance();
        const attributes = @json($attributes ?? []);

        const translationsKey = ['name']

        const form = reactive({
          id: 0,
          translations: {},
          position: '',
        })

        locales.value.forEach(locale => {
          form.translations[locale.code] = {}
          translationsKey.forEach(key => {
            form.translations[locale.code]['locale'] = locale.code
            form.translations[locale.code][key] = ''
          })
        })

        const rules = {

        }

        const edit = (id) => {
          drawer.value = true
          let attribute = attributes.data.find(item => item.id === id)
          form.id = attribute.id
          form.position = attribute.position
          attribute.translations.forEach(item => {
            form.translations[item.locale].name = item.name
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
          locales,
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