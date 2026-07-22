@extends('panel::layouts.app')
@section('body-class', 'page-category')

@section('title', __('panel/menu.categories'))

@section('page-title-right')
  <a href="{{ panel_route('categories.create') }}" class="btn btn-primary"><i
        class="bi bi-plus-square"></i> {{ __('common/base.create') }}</a>
  @hookinsert('panel.categories.index.page-title-right')
@endsection

@section('content')
  <div class="card h-min-600">
    <div class="card-body">
      @if (count($categories))
        <div class="accordion" id="categories-top"></div>
      @else
        <x-common-no-data/>
      @endif
    </div>
  </div>
@endsection

@push('footer')
<script src="{{ asset('vendor/vuedraggable/sortable.min.js') }}"></script>
<script>
  const api = @json(panel_route('categories.index'));
  const categories = @json($categories);
  const categoryIndexUrl = @json(panel_route('categories.index'));
  const reorderUrl = @json(panel_route('categories.reorder'));
  const dragSortHint = @json(__('panel/common.drag_sort_hint'));

  function createAccordionItem(item, parentId, index) {
    const itemId = `${parentId}-${index}`;
    const collapseId = `collapse${itemId}`;
    const hasChildren = item.children && item.children.length > 0;

    let html = `
      <div class="accordion-item">
        <h2 class="accordion-header" id="heading${itemId}">
          <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center flex-grow-1">
              <i class="bi bi-grip-vertical drag-handle text-muted me-2 flex-shrink-0" title="${dragSortHint}"></i>
              <button class="accordion-button collapsed ${!hasChildren ? 'no-children' : ''}" type="button" ${hasChildren ? `data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="false" aria-controls="${collapseId}"` : ''}>
                <span>${item.name}</span>
              </button>
            </div>
            <div class="d-flex align-items-center tool-btn" data-id="${item.id}">
              <div class="form-check form-switch list-switch ms-2">
                <input class="form-check-input" type="checkbox" role="switch" ${item.active ? 'checked' : ''}>
              </div>
              <a href="${categoryIndexUrl}/${item.id}/edit" class="btn btn-sm text-nowrap edit-category btn-outline-primary ms-3">{{ __('common/base.edit')}}</a>
              <span class="btn btn-sm ms-2 text-nowrap btn-outline-danger btn-delete">{{ __('common/base.delete')}}</span>
            </div>
          </div>
        </h2>`;

    if (hasChildren) {
      html += `
        <div id="${collapseId}" class="accordion-collapse collapse" aria-labelledby="heading${itemId}">
          <div class="accordion-body">
            <div class="accordion" id="accordion${itemId}">`;

      item.children.forEach((child, i) => {
        html += createAccordionItem(child, `accordion${itemId}`, i);
      });

      html += `</div></div></div>`;
    }

    html += `</div>`;
    return html;
  }

  function renderAccordion(data, parentId) {
    let html = '';
    data.forEach((item, index) => {
      html += createAccordionItem(item, parentId, index);
    });
    $(`#${parentId}`).html(html);
  }

  function initCategorySortables() {
    if (typeof Sortable === 'undefined') return;
    $('#categories-top, #categories-top .accordion').each(function() {
      const container = this;
      if (container.dataset.sortableInit) return;
      container.dataset.sortableInit = '1';
      new Sortable(container, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'sortable-ghost',
        onEnd: function(evt) {
          const ids = [];
          $(evt.to).children('.accordion-item').each(function() {
            const id = $(this).find('.tool-btn').first().data('id');
            if (id) ids.push(parseInt(id));
          });
          if (ids.length < 1) return;
          axios.post(reorderUrl, { ids: ids }).then(function(res) {
            inno.msg(res.message);
          }).catch(function(err) {
            if (err.response && err.response.data && err.response.data.message) {
              inno.msg(err.response.data.message);
            }
          });
        }
      });
    });
  }

  $(document).ready(function() {
    renderAccordion(categories, 'categories-top');
    initCategorySortables();
  });

  $(document).on('change', '.form-check-input', function() {
    const id = $(this).closest('.d-flex').data('id');
    const status = $(this).prop('checked');

    layer.load(2, {shade: [0.3,'#fff'] })
    axios.put(`${api}/${id}/active`, {status}).then((res) => {
      inno.msg(res.message)
    }).catch((err) => {
      $(this).prop('checked', !status);
      inno.msg(err.response.data.message)
    }).finally(() => {
      layer.closeAll('loading');
    });
  });


  $(document).on('click', '.btn-delete', function() {
    const id = $(this).closest('.tool-btn').data('id');
    inno.confirmDelete(`${api}/${id}`)
  });
</script>
@endpush