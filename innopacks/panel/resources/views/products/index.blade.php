@extends('panel::layouts.app')
@section('body-class', 'page-product')
@section('title', __('panel/menu.products'))

@section('page-title-right')
<a href="{{ panel_route('products.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i> {{
  __('panel/common.create') }}</a>
@endsection

@section('content')
<div class="card h-min-600 " id="app">
  <div class="card-body">

    <x-panel-criteria :criteria="$criteria ?? []" :action="panel_route('products.index')" />

    @if ($products->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>{{ __('panel/common.id') }}</th>
            <th class="wp-100">{{ __('panel/common.image') }}</th>
            <th>{{ __('panel/common.name') }}</th>
            <th>{{ __('panel/product.price') }}</th>
            <th>{{ __('panel/product.quantity') }}</th>
            <th>{{ __('panel/common.created_at') }}</th>
            <th>{{ __('panel/common.active') }}</th>
            <th>{{ __('panel/common.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($products as $product)
          <tr>
            <td>{{ $product->id }}</td>
            <td>
              <div class="d-flex align-items-center justify-content-center wh-50 border">
                <a href="{{ $product->url }}" target="_blank">
                  <img src="{{ image_resize($product->images->first()->path ?? '') }}" class="img-fluid"
                    alt="{{ $product->translation->name ?? '' }}">
                </a>
              </div>
            </td>
            <td><a href="{{ $product->url }}" class="text-decoration-none" target="_blank">{{
                $product->translation->name ?? '' }}</a>
              @if($product->isMultiple()) &nbsp;<span class="text-bg-success px-1">M</span>@endif
            </td>
            <td>{{ currency_format($product->masterSku->price ?? 0) }}</td>
            <td>{{ $product->masterSku->quantity ?? 0 }}</td>
            <td>{{ $product->created_at }}</td>
            <td>@include('panel::shared.list_switch', ['value' => $product->active, 'url' =>
              panel_route('products.active', $product->id)])</td>
            <td>
              <div class="d-flex gap-2">
                <div>
                  <a href="{{ panel_route('products.edit', [$product->id]) }}">
                    <el-button size="small" plain type="primary">{{
                      __('panel/common.edit')}}</el-button>
                  </a>
                </div>
                <div>
                  <a href="{{ panel_route('products.copy', [$product->id]) }}">
                    <el-button size="small" plain type="warning">{{
                      __('panel/common.copy')}}</el-button>
                  </a>
                </div>
                <div>
                  <form ref="deleteForm" action="{{ panel_route('products.destroy', [$product->id]) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('DELETE')
                    <el-button size="small" type="danger" plain @click="open({{$product->id}})">{{
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
    {{ $products->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
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
      const deleteUrl =urls.base_url+'/products/'+index;
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