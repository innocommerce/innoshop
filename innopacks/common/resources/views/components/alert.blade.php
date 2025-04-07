<div class="alert alert-{{ $type }} alert-dismissible">
  <i class="bi {{ $type == 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' }}"></i>
  {!! $msg !!}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>