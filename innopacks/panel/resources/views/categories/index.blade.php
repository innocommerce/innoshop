@extends('panel::layouts.app')
@section('body-class', 'page-category')

@section('title', __('panel/menu.categories'))

@section('page-title-right')
  <a href="{{ panel_route('categories.create') }}" class="btn btn-primary"><i
        class="bi bi-plus-square"></i> {{ __('panel/common.create') }}</a>
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
<script>
  const api = @json(panel_route('categories.index'));
  const categories = @json($categories);
  const categoryIndexUrl = @json(panel_route('categories.index'));

  function createAccordionItem(item, parentId, index) {
    const itemId = `${parentId}-${index}`;
    const collapseId = `collapse${itemId}`;
    const hasChildren = item.children && item.children.length > 0;

    let html = `
      <div class="accordion-item">
        <h2 class="accordion-header" id="heading${itemId}">
          <div class="d-flex justify-content-between align-items-center">
            <button class="accordion-button collapsed ${!hasChildren ? 'no-children' : ''}" type="button" ${hasChildren ? `data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="false" aria-controls="${collapseId}"` : ''}>
              <span>${item.name}</span>
            </button>
            <div class="d-flex align-items-center tool-btn" data-id="${item.id}">
              <div class="form-check form-switch list-switch ms-2">
                <input class="form-check-input" type="checkbox" role="switch" ${item.active ? 'checked' : ''}>
              </div>
              <a href="${categoryIndexUrl}/${item.id}/edit" class="btn btn-sm text-nowrap edit-category btn-outline-primary ms-3">{{ __('panel/common.edit')}}</a>
              <span class="btn btn-sm ms-2 text-nowrap btn-outline-danger btn-delete">{{ __('panel/common.delete')}}</span>
            </div>
          </div>
        </h2>`;

    if (hasChildren) {
      html += `
        <div id="${collapseId}" class="accordion-collapse collapse" aria-labelledby="heading${itemId}" data-bs-parent="#${parentId}">
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

  $(document).ready(function() {
    renderAccordion(categories, 'categories-top');
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