<div class="d-flex align-items-center flex-column py-5">
  <img src="{{ asset('icon/no-data-3.svg') }}" class="img-fluid mb-4" style="width: {{ $width }}px">
  <span class="fs-4 text-secondary">{{ $text ?: __('panel/common.no_data') . ' ~' }}</span>
</div>