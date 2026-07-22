@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.catalogs'))
@section('page-title-right')
  <a href="{{ panel_route('catalogs.create') }}" class="btn btn-primary"><i
        class="bi bi-plus-square"></i> {{ __('common/base.create') }}</a>
@endsection

@section('content')
  <div class="card h-min-600" id="app">
    <div class="card-body">

    <x-panel-data-data-search
      :action="panel_route('catalogs.index')"
      :searchFields="$searchFields ?? []"
      :filters="$filterButtons ?? []"
      :enableDateRange="false"
    />

      @if ($catalogs->count())
      <div class="table-responsive">
        <table class="table align-middle catalog-list-table">
        <thead>
        <tr>
        <td class="col-drag"></td>
        <td>{{ __('common/base.id') }}</td>
        <td>{{ __('panel/catalog.title') }}</td>
        <td>{{ __('panel/catalog.parent') }}</td>
        <td>{{ __('panel/common.slug') }}</td>
        <td>{{ __('common/base.position') }}</td>
        <td>{{ __('panel/common.active') }}</td>
        <td>{{ __('panel/common.actions') }}</td>
        </tr>
        </thead>
        <tbody>
        @foreach($catalogs as $item)
        <tr data-id="{{ $item->id }}">
        <td class="col-drag text-center">
          <i class="bi bi-grip-vertical drag-handle text-muted" title="{{ __('panel/common.drag_sort_hint') }}"></i>
        </td>
        <td>{{ $item->id }}</td>
        <td>{{ $item->fallbackName('title') }}</td>
        <td>{{ $item->parent ? $item->parent->fallbackName('title') : '-' }}</td>
        <td>{{ $item->slug }}</td>
        <td>{{ $item->position }}</td>
        <td>@include('panel::shared.list_switch', ['value' => $item->active, 'url' => panel_route('catalogs.active', $item->id)])</td>
        <td>
         <div class="d-flex gap-1">
        <a href="{{ panel_route('catalogs.edit', [$item->id]) }}">
        <el-button size="small" plain type="primary">{{ __('common/base.edit')}}</el-button>
        </a>
        <form ref="deleteForm" action="{{ panel_route('catalogs.destroy', [$item->id]) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <el-button size="small" type="danger" plain @click="open({{$item->id}})">{{ __('common/base.delete')}}</el-button>
        </form>
        </div>
        </td>
        </tr>
      @endforeach
        </tbody>
        </table>
      </div>
      {{ $catalogs->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
    @else
        <x-common-no-data/>
      @endif
    </div>
  </div>
@endsection

@push('footer')
    <script src="{{ asset('vendor/vuedraggable/sortable.min.js') }}"></script>
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
       const deleteUrl = urls.panel_base + '/catalogs/' + itemId;
       deleteForm.value.action = deleteUrl;
       deleteForm.value.submit();
      })
      .catch(() => {
      // 取消删除
      });
    };

    return { open, deleteForm };
    }
    });

    app.use(ElementPlus);
    app.mount('#app');

    function initCatalogSortable() {
      if (typeof Sortable === 'undefined') return;
      const tbody = document.querySelector('table tbody');
      if (!tbody || tbody.dataset.sortableInit) return;
      tbody.dataset.sortableInit = '1';
      new Sortable(tbody, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'sortable-ghost',
        onEnd: function(evt) {
          const ids = [];
          Array.prototype.forEach.call(evt.to.querySelectorAll(':scope > tr'), function(tr) {
            const id = tr.getAttribute('data-id');
            if (id) ids.push(parseInt(id));
          });
          if (ids.length < 1) return;
          axios.post(@json(panel_route('catalogs.reorder')), { ids: ids })
            .then(function(res) { inno.msg(res.message); })
            .catch(function(err) {
              if (err.response && err.response.data && err.response.data.message) {
                inno.msg(err.response.data.message);
              }
            });
        }
      });
    }
    initCatalogSortable();
    </script>
@endpush
