@extends('panel::layouts.app')
@section('body-class', '')

@section('title',  '常见问题' )

@section('page-title-right')
  <a href="{{ panel_route('faq_categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i> {{
  __('panel/common.create') }}</a>
@endsection

@section('content')
  <div class="card h-min-600" id="app">
     <div class="card-body">
      <ul class="nav nav-tabs mb-4" id="faqNavTabs">
        <li class="nav-item">
          <a class="nav-link" href="{{ url('/panel/faqs') }}">
            常见问题
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="{{ url('/panel/faq_categories') }}">
            常见问题分类
          </a>
        </li>
      </ul>

      <x-panel-criteria :criteria="$criteria ?? []" :action="panel_route('faq_categories.index')"/>

      @if ($faq_categories->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
            <tr>
              <td>{{ __('panel/common.id')}}</td>
              <td>分类名</td>
              <td>关联产品</td>
              <td>关联文章</td>
              <td>{{ __('panel/common.active') }}</td>
              <td>{{ __('panel/common.created_at') }}</td>
              <td>{{ __('panel/common.actions') }}</td>
            </tr>
            </thead>
            <tbody>
            @foreach($faq_categories as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->title }}</td>
                <td>
                  <a href="{{ $item->product->url ?? '' }}" class="text-decoration-none" target="_blank">
                    {{ sub_string($item->product->translation->name ?? '') }}
                  </a>
                </td>
                <td>
                  <a href="{{ $item->article->url ?? '' }}" class="text-decoration-none" target="_blank">
                    {{ sub_string($item->article->translation->title ?? '') }}
                  </a>
                </td>
                <td>@include('panel::shared.list_switch', ['value' => $item->active,'url' => panel_route('faq_categories.active',$item->id)])</td>
                <td>{{ $item->created_at }}</td>
                <td>
                  <div class="d-flex gap-2">
                    <div>
                      <a class="btn btn-primary btn-sm" href="{{ panel_route('faq_categories.edit', [$item->id]) }}">{{ __('panel/common.edit')}}</a>
                    </div>
                    <div>
                      <a class="btn btn-primary btn-sm" href="{{ panel_route('faqs.index', ['faq_category_id'=>$item->id]) }}">该分类下问题</a>
                    </div>
                    <div>
                      <form ref="deleteForm" action="{{ panel_route('faq_categories.destroy', [$item->id]) }}" method="POST">
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
        {{ $faq_categories->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
      @else
        <x-common-no-data/>
      @endif
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
          ).then(() => {
            deleteForm.value.action = urls.base_url + '/faq_categories/' + itemId;
            deleteForm.value.submit()
          }).catch(() => {
          });
        };

        return {open, deleteForm};
      }
    });
    app.use(ElementPlus);
    app.mount('#app');
  </script>
@endpush
