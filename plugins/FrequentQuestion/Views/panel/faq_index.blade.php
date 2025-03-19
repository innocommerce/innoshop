@extends('panel::layouts.app')
@section('body-class', '')

@section('title',  __('FrequentQuestion::common.faq') )

@section('page-title-right')
  <a href="{{ panel_route('faqs.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i> {{
  __('panel/common.create') }}</a>
@endsection

@section('content')
  <div class="card h-min-600" id="app">
    <div class="card-body">
      <!-- 导航按钮 - 使用标准 Bootstrap nav-tabs 样式 -->
      <ul class="nav nav-tabs mb-4" id="faqNavTabs">
        <li class="nav-item">
          <a class="nav-link active" href="{{ url('/panel/faqs') }}">
            常见问题
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ url('/panel/faq_categories') }}">
            常见问题分类
          </a>
        </li>
      </ul>
      
      <x-panel-criteria :criteria="$criteria ?? []" :action="panel_route('faqs.index')"/>
      
      @if ($faqs->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
            <tr>
              <td>{{ __('panel/common.id')}}</td>
              <td>所属分类</td>
              <td>{{ __('FrequentQuestion::common.question') }}</td>
              <td>{{ __('FrequentQuestion::common.answer') }}</td>
              <td>{{ __('panel/common.active') }}</td>
              <td>{{ __('panel/common.created_at') }}</td>
              <td>{{ __('panel/common.actions') }}</td>
            </tr>
            </thead>
            <tbody>
            @foreach($faqs as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->category->title ?? '' }}</td>
                <td>{{ sub_string($item->translation->question ?? '', 24) }}</td>
                <td>{{ sub_string($item->translation->answer ?? '', 64) }}</td>
                <td>@include('panel::shared.list_switch', ['value' => $item->active,'url' => panel_route('faqs.active',$item->id)])</td>
                <td>{{ $item->created_at }}</td>
                <td>
                  <div class="d-flex gap-2">
                    <div>
                      <a href="{{ panel_route('faqs.edit', [$item->id]) }}">
                        <el-button size="small" plain type="primary">{{
                      __('panel/common.edit')}}</el-button>
                      </a>
                    </div>
                    <div>
                      <form ref="deleteForm" action="{{ panel_route('faqs.destroy', [$item->id]) }}" method="POST">
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
        {{ $faqs->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
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
        const deleteCategoryForm = ref(null);
        
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
            deleteForm.value.action = urls.base_url + '/faqs/' + itemId;
            deleteForm.value.submit()
          }).catch(() => {
          });
        };
        
        const openCategory = (categoryId) => {
          ElMessageBox.confirm(
            '{{ __("common/base.hint_delete") }}',
            '{{ __("common/base.cancel") }}',
            {
              confirmButtonText: '{{ __("common/base.confirm")}}',
              cancelButtonText: '{{ __("common/base.cancel")}}',
              type: 'warning',
            }
          ).then(() => {
            deleteCategoryForm.value.action = urls.base_url + '/faq-categories/' + categoryId;
            deleteCategoryForm.value.submit()
          }).catch(() => {
          });
        };

        return {open, openCategory, deleteForm, deleteCategoryForm};
      }
    });
    app.use(ElementPlus);
    app.mount('#app');

    // 为tab添加点击事件处理
    document.addEventListener('DOMContentLoaded', function() {
      const tabButtons = document.querySelectorAll('#faqNavTabs button[data-url]');
      
      tabButtons.forEach(button => {
        button.addEventListener('click', function(event) {
          event.preventDefault();
          const url = this.getAttribute('data-url');
          if (url) {
            window.location.href = url;
          }
        });
      });
    });
  </script>
@endpush
