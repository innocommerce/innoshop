<div class="alert alert-{{ $type }} alert-dismissible mt-1">
  <i class="bi {{ $type=='success'? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
  {!! $msg !!}
  @if($close)
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  @endif
</div>