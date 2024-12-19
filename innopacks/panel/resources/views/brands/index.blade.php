@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.brands'))
@section('page-title-right')
<a href="{{ panel_route('brands.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i> {{
  __('panel/common.create') }}</a>
@endsection

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-body">

    <x-panel-criteria :criteria="$criteria ?? []" :action="panel_route('brands.index')" />

    @if ($brands->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('panel/common.id')}}</td>
            <td>{{ __('panel/brand.logo') }}</td>
            <td>{{ __('panel/brand.name') }}</td>
            <td>{{ __('panel/brand.first') }}</td>
            <td>{{ __('panel/common.slug') }}</td>
            <td>{{ __('panel/common.position') }}</td>
            <td>{{ __('panel/common.active') }}</td>
            <td>{{ __('panel/common.actions') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($brands as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>
              <a href="{{ $item->url }}" target="_blank">
                <img src="{{ image_resize($item->logo) }}" class="img-fluid wh-40">
              </a>
            </td>
            <td><a href="{{ $item->url }}" class="text-decoration-none" target="_blank">{{ $item->name ?? '' }}</a></td>
            <td>{{ $item->first }}</td>
            <td>{{ $item->slug }}</td>
            <td>{{ $item->position }}</td>
            <td>@include('panel::shared.list_switch', [
              'value' => $item->active,
              'url' => panel_route(
              'brands.active',
              $item->id
              )
              ])</td>
            <td>
              <div class="d-flex gap-2">
                <div>
                  <a href="{{ panel_route('brands.edit', [$item->id]) }}">
                    <el-button size="small" plain type="primary">{{
                      __('panel/common.edit')}}</el-button>
                  </a>
                </div>
                <div>
                  <form ref="deleteForm" action="{{ panel_route('brands.destroy', [$item->id]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <el-button size="small" type="danger" plain @click="open({{ $item->id }})">{{
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
    {{ $brands->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
    @else
    <x-common-no-data />
    @endif
  </div>
</div>
@endsection
@push('footer')
<script>
  const { createApp, ref } = Vue;
    const { ElMessageBox, ElMessage } = ElementPlus;
    const app = createApp({
    setup() {
    const deleteForm = ref(null);
    const open = (itemId) => {
    console.log(itemId);
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
             const deleteUrl = urls.base_url + '/brands/' + itemId;
               deleteForm.value.action = deleteUrl;
              deleteForm.value.submit()
    })
    .catch(() => {
    });
    };

    return { open, deleteForm };
    }
    });
    app.use(ElementPlus);
    app.mount('#app');
</script>
@endpush