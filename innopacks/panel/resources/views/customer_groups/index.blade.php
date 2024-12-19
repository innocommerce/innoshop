@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.customer_groups'))
@section('page-title-right')
<a href="{{ panel_route('customer_groups.create') }}" class="btn btn-primary">
  <i class="bi bi-plus-square"></i> {{ __('panel/common.create') }}</a>
@endsection

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-body">

    <x-panel-criteria :criteria="$criteria ?? []" :action="panel_route('customer_groups.index')" />

    @if ($groups->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('panel/common.id') }}</td>
            <td>{{ __('panel/common.name') }}</td>
            <td>{{ __('panel/customer.level') }}</td>
            <td>{{ __('panel/customer.mini_cost') }}</td>
            <td>{{ __('panel/customer.discount_rate') }}</td>
            <td>{{ __('panel/common.actions') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($groups as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->translation->name ?? '' }}</td>
            <td>{{ $item->level }}</td>
            <td>{{ currency_format($item->mini_cost, system_setting('currency')) }}</td>
            <td>{{ $item->discount_rate }}</td>
            <td>
              <div class="d-flex gap-1">
                <a href="{{ panel_route('customer_groups.edit', [$item->id]) }}">
                  <el-button size="small" plain type="primary">{{ __('panel/common.edit')}}</el-button>
                </a>
                <form ref="deleteForm" action="{{ panel_route('customer_groups.destroy', [$item->id]) }}" method="POST"
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
    {{ $groups->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
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
    const deletUrl =urls.base_url +'/customer_groups/'+ itemId;
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