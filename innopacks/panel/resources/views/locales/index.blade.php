@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.locales'))

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-body">

    <x-panel-criteria :criteria="$criteria ?? []" :action="panel_route('locales.index')" />

    @if ($locales)
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('panel/common.id') }}</td>
            <td>{{ __('panel/common.logo') }}</td>
            <td>{{ __('panel/common.name') }}</td>
            <td>{{ __('panel/currency.code') }}</td>
            <td>{{ __('panel/common.position') }}</td>
            <td>{{ __('panel/common.status') }}</td>
            <td>{{ __('panel/common.actions') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($locales as $item)
          <tr>
            <td>{{ $item['id'] }}</td>
            <td><img src="{{ image_resize($item['image'], 90, 60) }}" class="border" style="width: 30px;"></td>
            <td>{{ $item['name'] }}</td>
            <td>{{ $item['code'] }}</td>
            <td>{{ $item['position'] }}</td>
            <td>
              @if ($item['id'])
              @include('panel::shared.list_switch', ['value' => $item['active'], 'url' => panel_route('locales.active',
              $item['id'])])
              @endif
            </td>
            <td>
              @if ($item['id'])
              <el-button size="small" plain type="danger" type="button" @click="open('{{ $item['code'] }}')">
                {{ __('panel/common.uninstall') }}
              </el-button button>
              @else
              <button style="min-width: 47.48px; height: 23.99px; line-height: 12px;" type="button"
                class="btn btn-sm btn-outline-primary leng-install" data-code="{{ $item['code'] }}">{{
                __('panel/common.install') }}</button>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @else
    <x-common-no-data />
    @endif
  </div>
</div>
@endsection

@push('footer')
<script>
  $(function () {
  $('.leng-install').click(function () {
  axios.post('{{ panel_name() }}/locales/install', {code: $(this).data('code')}).then(function (res) {
  window.location.reload()
  }).catch(function (err) {
  inno.msg(err.message, {icon: 2})
  })
  });

  $('.leng-unload').click(function () {
  axios.post(`{{ panel_name() }}/locales/${$(this).data('code')}/uninstall`).then(function (res) {
  window.location.reload()
  }).catch(function (err) {
  inno.msg(err.message, {icon: 2})
  })
  });
  });
  const { createApp, ref } = Vue;
  const { ElMessageBox, ElMessage } = ElementPlus;

  const app = createApp({
  setup() {
  const open = (itemId) => {
  ElMessageBox.confirm(
    '{{ __("common/base.hint_unload") }}',
    '{{ __("common/base.cancel") }}',
    {
    confirmButtonText: '{{ __("common/base.confirm")}}',
    cancelButtonText: '{{ __("common/base.cancel")}}',
    type: 'warning',
    }
  ).then(() => {
  axios.post(`{{ panel_name() }}/locales/${itemId}/uninstall`)
  .then(() => {
  setTimeout(() => {
    window.location.reload();
    }, 1000);
  })

  }).catch(() => {

  });
  };

  return { open };
  }
  });

  app.use(ElementPlus);
  app.mount('#app');
</script>
@endpush