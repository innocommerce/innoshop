@if(isset($paginator) && $paginator && $paginator->count())
<div class="d-flex align-items-center text-muted">
  <i class="bi bi-file-earmark-text me-1"></i>
  <small>{{ __('panel/common.total_records', ['total' => $paginator->total(), 'current' => $paginator->currentPage(), 'last' => $paginator->lastPage()]) }}</small>
</div>
@endif 