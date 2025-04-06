@props(['id', 'route'])

<button type="button" class="btn btn-sm btn-outline-danger px-2" onclick="confirmDelete('{{ $id }}', '{{ $route }}')">
  <i class="bi bi-trash me-1"></i>{{ __('panel/common.delete') }}
</button>

@once
<!-- Delete Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('panel/common.hint') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        {{ __('panel/common.delete_confirm') }}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('panel/common.cancel') }}</button>
        <form id="deleteForm" method="POST" class="d-inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">{{ __('panel/common.confirm') }}</button>
        </form>
      </div>
    </div>
  </div>
</div>

@push('footer')
<script>
  const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
  const deleteForm = document.getElementById('deleteForm');

  function confirmDelete(id, route) {
    deleteForm.action = route;
    deleteModal.show();
  }
</script>
@endpush
@endonce