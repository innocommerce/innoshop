@extends('panel::layouts.app')
@section('body-class', 'page-attribute-management')

@section('title', __('panel/menu.attribute_groups'))
@section('page-title-right')
<button type="button" class="btn btn-primary" onclick="openCreateDialog()">
  <i class="bi bi-plus-square"></i> {{ __('common/base.create') }}
</button>
@endsection

@section('content')
<div class="card h-min-600">
  <div class="card-body">
    <!-- Navigation links -->
    <ul class="nav nav-tabs mb-3">
      <li class="nav-item">
        <a class="nav-link" href="{{ panel_route('attributes.index') }}">
          <i class="bi bi-tags"></i> {{ __('panel/menu.attributes') }}
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link active" href="{{ panel_route('attribute_groups.index') }}">
          <i class="bi bi-collection"></i> {{ __('panel/menu.attribute_groups') }}
        </a>
      </li>
    </ul>

    <x-panel-data-data-search
      :action="panel_route('attribute_groups.index')"
      :searchFields="$searchFields ?? []"
      :filters="$filterButtons ?? []"
      :enableDateRange="false"
    />

    @if ($attributes->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('common/base.id') }}</td>
            <td>{{ __('common/base.name') }}</td>
            <td>{{ __('common/base.position') }}</td>
            <td>{{ __('common/base.created_at') }}</td>
            <td>{{ __('panel/common.actions') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($attributes as $item)
          <tr id="group-row-{{ $item->id }}">
            <td>{{ $item->id }}</td>
            <td>{{ $item->translation->name ?? '' }}</td>
            <td>{{ $item->position }}</td>
            <td>{{ $item->created_at }}</td>
            <td>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary"
                        onclick="openEditDialog({{ $item->id }})">
                  <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger"
                        onclick="deleteGroup({{ $item->id }})">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    {{ $attributes->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
    @else
    <x-common-no-data />
    @endif
  </div>
</div>

{{-- Shared locale modal --}}
<x-common-form-locale-modal
  modalId="groupEditModal"
  inputPrefix="group-name"
  :title="__('panel/menu.attribute_groups')">
  <div class="mb-3 mt-3">
    <label class="form-label">{{ __('common/base.position') }}</label>
    <input type="number" class="form-control" id="group-position"
           placeholder="{{ __('common/base.position') }}" value="0">
  </div>
</x-common-form-locale-modal>
@endsection

@push('footer')
<script>
  const groupApiBase = '{{ panel_route("attribute_groups.index") }}';
  let editingGroupId = null;
  let groupModal = null;
  let groupHelper = null;

  document.addEventListener('DOMContentLoaded', function() {
    groupModal = new bootstrap.Modal(document.getElementById('groupEditModal'));
    groupHelper = new LocaleModalHelper({
      modalId: 'groupEditModal',
      inputPrefix: 'group-name',
    });

    document.getElementById('groupEditModal-confirm').addEventListener('click', submitGroup);
  });

  function resetGroupForm() {
    editingGroupId = null;
    document.getElementById('groupEditModal-title').textContent = '{{ __("common/base.create") }}';
    document.getElementById('group-position').value = 0;
    document.querySelectorAll('#groupEditModal input[id^="group-name-"]').forEach(el => el.value = '');
  }

  function fillGroupForm(data) {
    editingGroupId = data.id;
    document.getElementById('groupEditModal-title').textContent = '{{ __("common/base.edit") }}';
    document.getElementById('group-position').value = data.position ?? 0;
    document.querySelectorAll('#groupEditModal input[id^="group-name-"]').forEach(el => el.value = '');
    if (data.translations) {
      data.translations.forEach(t => {
        const input = document.getElementById('group-name-' + t.locale);
        if (input) input.value = t.name;
      });
    }
  }

  function openCreateDialog() {
    resetGroupForm();
    groupModal.show();
  }

  function openEditDialog(id) {
    resetGroupForm();
    axios.get(groupApiBase + '/' + id, { headers: { 'X-Skip-Loading': true } })
      .then(res => {
        fillGroupForm(res.data);
        groupModal.show();
      })
      .catch(err => {
        layer.msg(err.response?.data?.message || err.message, {icon: 2});
      });
  }

  function submitGroup() {
    const primaryInput = document.getElementById('group-name-' + groupHelper.defaultLocale);
    if (!primaryInput || !primaryInput.value.trim()) {
      layer.msg('{{ __("panel/common.verify_required") }}', {icon: 2});
      return;
    }

    const translations = groupHelper.locales.map(l => ({
      locale: l.code,
      name: document.getElementById('group-name-' + l.code)?.value || ''
    }));
    const position = parseInt(document.getElementById('group-position').value) || 0;

    const payload = { translations, position };
    const url = editingGroupId ? groupApiBase + '/' + editingGroupId : groupApiBase;
    const method = editingGroupId ? 'put' : 'post';

    axios[method](url, payload, { headers: { 'X-Skip-Loading': true } }).then(res => {
      groupModal.hide();
      layer.msg(res.message, {icon: 1});
      setTimeout(() => window.location.reload(), 500);
    }).catch(err => {
      layer.msg(err.response?.data?.message || err.message, {icon: 2});
    });
  }

  function deleteGroup(id) {
    if (!confirm('{{ __("panel/common.confirm_delete") }}')) return;

    axios.delete(groupApiBase + '/' + id, { headers: { 'X-Skip-Loading': true } }).then(res => {
      layer.msg(res.message, {icon: 1});
      const row = document.getElementById('group-row-' + id);
      if (row) row.remove();
      const tbody = document.querySelector('table tbody');
      if (tbody && !tbody.children.length) {
        window.location.reload();
      }
    }).catch(err => {
      layer.msg(err.response?.data?.message || err.message, {icon: 2});
    });
  }
</script>
@endpush
