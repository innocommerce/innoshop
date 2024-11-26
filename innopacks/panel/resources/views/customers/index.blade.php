@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.customers'))
@section('page-title-right')
<a href="{{ panel_route('customers.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i> {{
  __('panel/common.create') }}</a>
@endsection

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-body">

    <x-panel-criteria :criteria="$criteria ?? []" :action="panel_route('customers.index')" />

    @if ($customers->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('panel/common.id')}}</td>
            <td>{{ __('panel/customer.avatar') }}</td>
            <td>{{ __('panel/customer.email') }}</td>
            <td>{{ __('panel/customer.name') }}</td>
            <td>{{ __('panel/customer.from') }}</td>
            <td>{{ __('panel/customer.group') }}</td>
            <td>{{ __('panel/customer.locale') }}</td>
            <td>{{ __('panel/common.created_at') }}</td>
            <td>{{ __('panel/common.active') }}</td>
            <td>{{ __('panel/common.actions') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($customers as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>
              <div class="wh-40 border d-flex justify-content-center align-items-center"><img
                  src="{{ image_resize($item->avatar) }}" class="img-fluid"></div>
            </td>
            <td>{{ $item->email }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ $item->from }}</td>
            <td>{{ $item->customerGroup->translation->name ?? '-' }}</td>
            <td>{{ $item->locale }}</td>
            <td>{{ $item->created_at }}</td>
            <td>
              @include('panel::shared.list_switch', ['value' => $item->active, 'url' => panel_route('customers.active',
              $item)])
            </td>
            <td>
              <div class="d-flex gap-1">
                <a href="{{ panel_route('customers.login', [$item->id]) }}">
                  <el-button size="small" plain type="primary">{{ __('panel/customer.login_frontend')}}</el-button>
                </a>
                <a href="{{ panel_route('customers.edit', [$item->id]) }}">
                  <el-button size="small" plain type="primary">{{ __('panel/common.edit')}}</el-button>
                </a>
                <form ref="deleteForm" action="{{ panel_route('customers.destroy', [$item->id]) }}" method="POST"
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
    {{ $customers->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
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
      const deleteUrl = urls.base_url+'/customers/'+index;
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