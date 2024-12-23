@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.articles'))
@section('page-title-right')
  <a href="{{ panel_route('articles.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i> {{ __('panel/common.create') }}</a>
@endsection

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-body">

  <x-panel-criteria :criteria="$criteria ?? []" :action="panel_route('articles.index')" />

    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('panel/common.id')}}</td>
            <td>{{ __('panel/article.image') }}</td>
            <td>{{ __('panel/article.title') }}</td>
            <td>{{ __('panel/article.catalog') }}</td>
            <td>{{ __('panel/article.tag') }}</td>
            <td>{{ __('panel/common.slug') }}</td>
            <td>{{ __('panel/common.position') }}</td>
            <td>{{ __('panel/common.active') }}</td>
            <td>{{ __('panel/common.actions') }}</td>
          </tr>
        </thead>
        @if ($articles->count())
        <tbody>
        @foreach($articles as $item)
      <tr>
      <td>{{ $item->id }}</td>
      <td><img src="{{ image_resize($item->translation->image ?? '', 30, 30) }}" style="width: 30px; height: 30px" alt=""></td>
      <td>{{ sub_string($item->translation->title ?? '') }}</td>
      <td>{{ $item->catalog->translation->title ?? '-' }}</td>
      <td>{{ $item->tagNames ?? '' }}</td>
      <td>{{ sub_string($item->slug) }}</td>
      <td>{{ $item->position }}</td>
      <td>@include('panel::shared.list_switch', ['value' => $item->active, 'url' => panel_route('articles.active', $item->id)])</td>
      <td>
      <div class="d-flex gap-1">
      <a href="{{ panel_route('articles.edit', [$item->id]) }}">
      <el-button size="small" plain type="primary">{{ __('panel/common.edit')}}</el-button>
      </a>
      <form ref="deleteForm" action="{{ panel_route('articles.destroy', [$item->id]) }}" method="POST" class="d-inline">
      @csrf
      @method('DELETE')
      <el-button size="small" type="danger" plain @click="open({{$item->id}})">{{ __('panel/common.delete')}}</el-button>
      </form>
      </div>
      </td>
      </tr>
      @endforeach
        </tbody>
    @else
        <tbody><tr><td colspan="5"><x-common-no-data /></td></tr></tbody>
        @endif
      </table>
    </div>
    {{ $articles->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
  </div>
</div>
@endsection

@push('footer')
   <script>
    const {createApp, ref} = Vue;
    const {ElMessageBox, ElMessage} = ElementPlus;

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
      const deleteUrl =urls.base_url+'/articles/'+index;
      deleteForm.value.action=deleteUrl;
      deleteForm.value.submit();
    })
    .catch(() => {
    });
   };

    return {open, deleteForm}; 
     }
    });

    app.use(ElementPlus);
    app.mount('#app');
  </script>
@endpush