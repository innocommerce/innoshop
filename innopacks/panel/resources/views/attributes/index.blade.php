@extends('panel::layouts.app')
@section('body-class', 'page-attribute-management')

@section('title', __('panel/menu.attributes'))
@section('page-title-right')
<a href="{{ panel_route('attributes.create') }}" class="btn btn-primary">
  <i class="bi bi-plus-square"></i> {{ __('panel/common.create') }}
</a>
@endsection

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-body">
    <!-- Navigation links -->
    <ul class="nav nav-tabs mb-3">
      <li class="nav-item">
        <a class="nav-link active" href="{{ panel_route('attributes.index') }}">
          <i class="bi bi-tags"></i> {{ __('panel/menu.attributes') }}
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ panel_route('attribute_groups.index') }}">
          <i class="bi bi-collection"></i> {{ __('panel/menu.attribute_groups') }}
        </a>
      </li>
    </ul>

    <x-panel-data-criteria :criteria="$criteria ?? []" :action="panel_route('attributes.index')" />

    @if ($attributes->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('panel/common.id')}}</td>
            <td>{{ __('panel/common.name')}}</td>
            <td>{{ __('panel/menu.attribute_groups')}}</td>
            <td>{{ __('panel/common.position')}}</td>
            <td>{{ __('panel/common.created_at')}}</td>
            <td>{{ __('panel/common.actions')}}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($attributes as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->fallbackName() }}</td>
            <td>{{ $item->group ? $item->group->fallbackName() : '' }}</td>
            <td>{{ $item->position }}</td>
            <td>{{ $item->created_at }}</td>
            <td>
              <div class="d-flex gap-2">
                <div>
                  <a href="{{ panel_route('attributes.edit', [$item->id]) }}">
                    <el-button size="small" plain type="primary">{{
      __('panel/common.edit')}}</el-button>
                  </a>
                </div>
                <div>
                  <form ref="deleteForm" action="{{ panel_route('attributes.destroy', [$item->id]) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('DELETE')
                    <el-button size="small" type="danger" plain @click="open({{$item->id}})">{{ __('panel/common.delete')}}</el-button>
                  </form>
                </div>
              </div>
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
</div>
@endsection

@push('footer')
    <script>
       const { createApp, ref } = Vue;
       const { ElMessageBox, ElMessage } = ElementPlus;

       const app = createApp({
       setup() {
       const deleteForm = ref(null);

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
       const deleteUrl=urls.base_url+'/attributes/' +index;
       deleteForm.value.action=deleteUrl;
       deleteForm.value.submit();
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
