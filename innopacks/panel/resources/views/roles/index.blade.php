@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.roles'))
@section('page-title-right')
<a href="{{ panel_route('roles.create') }}" class="btn btn-primary">
  <i class="bi bi-plus-square"></i> {{ __('panel/common.create') }}
</a>
@endsection

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-body">

    @if ($roles->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>{{ __('panel/common.id') }}</th>
            <th>{{ __('panel/common.name') }}</th>
            <th>{{ __('panel/common.created_at') }}</th>
            <th>{{ __('panel/common.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($roles as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ $item->created_at }}</td>
            <td>
              <div class="d-flex gap-1">
                <a href="{{ panel_route('roles.edit', [$item->id]) }}">
                  <el-button size="small" plain type="primary">{{ __('panel/common.edit')}}</el-button>
                </a>
                <form ref="deleteForm" action="{{ panel_route('roles.destroy', [$item->id]) }}" method="POST"
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
    {{ $roles->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
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
    const deletUrl =urls.base_url +'/roles/'+ itemId;
    deleteForm.value.action = deletUrl; 
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